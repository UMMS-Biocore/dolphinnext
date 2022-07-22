<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/../ajax/dbfuncs.php");

class updates
{
    public $dbhost = "";
    public $db = "";
    public $dbuser = "";
    public $dbpass = "";
    private $BASE_PATH = "";


    function readINI()
    {
        $this->dbhost     = DBHOST;
        $this->db         = DB;
        $this->dbpass     = DBPASS;
        $this->dbuser     = DBUSER;
        $this->BASE_PATH  = BASE_PATH;
    }
    function getINI()
    {
        $this->readINI();
        return $this;
    }

    function runSQL($sql)
    {
        $this->readINI();
        $link = new mysqli($this->dbhost, $this->dbuser, $this->dbpass, $this->db);
        // check connection
        if (mysqli_connect_errno()) {
            exit('Connect failed: ' . mysqli_connect_error());
        }
        $result = $link->query($sql);
        if ($result) {
            $link->close();
            return $result;
        }
        $link->close();
        return "";
    }


    function queryTable($sql)
    {
        $data = array();
        $res = $this->runSQL($sql);
        if (!empty($res)) {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
            $res->close();
        }
        return $data;
    }

    function verifyToken($token)
    {
        $cmd = "cd ../../scripts && python decode.py VERIFY $token";
        $ret = popen($cmd, "r");
        $ok = fread($ret, 2096);
        pclose($ret);
        if (trim($ok) == "OK") {
            return "true";
        } else {
            return "false";
        }
    }

    //http://localhost:8080/dolphinnext/api/service.php?upd=updateCloudInst
    function updateCloudInst()
    {
        $this->readINI();
        $ret = "";
        $clouds = ["amazon", "google"];
        for ($i = 0; $i < count($clouds); $i++) {
            $cloud = $clouds[$i];
            //autoshutdown_active profiles 
            $sql = "SELECT DISTINCT a.id, a.owner_id, a.status
            FROM $this->db.profile_{$cloud} a
            INNER JOIN $this->db.project_pipeline pp
            WHERE a.autoshutdown_active = 'true'
            AND (a.status = 'initiated' OR a.status = 'running')
            AND pp.profile = CONCAT('{$cloud}-',a.id) 
            AND pp.deleted=0";
            $shutActiveProfiles = $this->queryTable($sql);
            //autoshutdown_active profiles that has active runs
            $sql = "SELECT DISTINCT a.id, a.owner_id, a.status
            FROM $this->db.profile_{$cloud} a
            INNER JOIN $this->db.project_pipeline pp
            INNER JOIN $this->db.run r
            WHERE pp.id = r.project_pipeline_id
            AND a.autoshutdown_active = 'true'
            AND (a.status = 'initiated' OR a.status = 'running')
            AND pp.profile = CONCAT('{$cloud}-',a.id) 
            AND pp.deleted=0 
            AND (r.run_status = 'init' OR r.run_status = 'Waiting' OR r.run_status = 'NextRun' OR r.run_status = 'Aborted')";
            $activeRunProfile = $this->queryTable($sql);

            $keepProfile = array();
            foreach ($activeRunProfile as $actDat) :
                $keepProfile[] = $actDat["id"];
            endforeach;

            $closeProfile = array();
            foreach ($shutActiveProfiles as $shutDat) :
                if (!in_array($shutDat["id"], $keepProfile)) {
                    $closeProfile[] = $shutDat;
                }
            endforeach;

            $time = date("M-d-Y H:i:s");
            if (!count($closeProfile) > 0) {
                $ret .= "$time There is no instance to trigger autoshutdown.\n";
            } else {
                $dbfun = new dbfuncs();
                foreach ($closeProfile as $profileData) :
                    $ownerID = $profileData["owner_id"];
                    $profileId = $profileData["id"];
                    $profileStatus = $profileData["status"];
                    error_log("triggerShutdown slow");
                    $triggerShutdown = $dbfun->triggerShutdown($profileId, $cloud, $ownerID, "slow");
                    $ret .= "$time profileId:$profileId status:$profileStatus shutdownLog:$triggerShutdown\n";
                endforeach;
            }
        }
        return $ret;
    }

    function startCronRun($tmplt_run_id, $ownerID, $run_name)
    {
        $ret = array();
        // update name and insert
        $inputs = null;
        $run_env = null;
        $work_dir = null;
        $process_opt = null;
        $project_id = null;
        $description = null;
        $dmeta = null;
        $dbfun = new dbfuncs();
        $type = "auto";

        $project_pipeline_id = $dbfun->duplicateProjectPipeline($tmplt_run_id, $ownerID, $inputs, $dmeta, $run_name, $run_env, $work_dir, $process_opt, $project_id, $description, $type);
        if (empty($project_pipeline_id)) {
            error_log("duplicateProjectPipeline failed.");
            return null;
        }

        $manualRun = "false";
        $runType = "newrun"; //"resumerun" or "newrun"
        $uuid = $dbfun->updateRunAttemptLog($manualRun, $project_pipeline_id, $ownerID);
        $nextText = $dbfun->getServerRunTemplateNFFile($tmplt_run_id, $uuid);
        if (empty($nextText)) {
            error_log("getServerRunTemplateNFFile failed.");
            return null;
        }
        if (empty($uuid)) {
            error_log("updateRunAttemptLog failed.");
            return null;
        }

        $temp_run_uuid = $dbfun->getProPipeLastRunUUID($tmplt_run_id);
        $runOpt = json_decode($dbfun->getRunLogOpt($temp_run_uuid));
        $proVarObj = htmlspecialchars_decode($runOpt[0]->{'pro_var_obj'}, ENT_QUOTES);
        if (!empty($nextText) && !empty($uuid)) {
            $data = $dbfun->saveRun($project_pipeline_id, $nextText, $runType, $manualRun, $uuid, $proVarObj, $ownerID);
            if (!empty($data)) {
                $run_url = "{$this->BASE_PATH}/index.php?np=3&id=" . $project_pipeline_id;
                $ret["status"] = "initiated";
                $ret["log"] = "Run submitted.";
                $ret["run_url"] = $run_url;
                $ret["run_id"] = $project_pipeline_id;
                return $ret;
            }
        }
        return null;
    }


    //http://localhost:8080/dolphinnext/api/service.php?upd=submitCronJobs
    // f
    function submitCronJobs()
    {
        $this->readINI();
        $dbfun = new dbfuncs();
        // $cron_min, $cron_hour, $cron_day, $cron_week, $cron_month,
        // find cron_target_date and cron_check == "true"
        $sql = "SELECT DISTINCT pp.id, pp.name, pp.owner_id, pp.cron_target_date, pp.cron_prefix, pp.cron_min, pp.cron_hour, pp.cron_day, pp.cron_week, pp.cron_month
        FROM $this->db.project_pipeline pp
        WHERE  pp.deleted=0 AND pp.cron_check='true' AND pp.cron_target_date IS NOT NULL ";
        $data = $this->queryTable($sql);

        $time = date("M-d-Y H:i:s");
        $run_name_time = date("Y-m-d H:i:s", strtotime("now"));
        $ret = "";
        if (!count($data) > 0) {
            $ret = "$time Active Cronjob is not found.";
        } else {

            foreach ($data as $runData) :
                $cron_target_date = $runData["cron_target_date"];
                $cron_min = $runData["cron_min"];
                $cron_hour = $runData["cron_hour"];
                $cron_day = $runData["cron_day"];
                $cron_week = $runData["cron_week"];
                $cron_month = $runData["cron_month"];
                $ownerID = $runData["owner_id"];
                $project_pipeline_id = $runData["id"];
                $name = $runData["name"];
                $cron_prefix =  htmlspecialchars_decode($runData['cron_prefix'], ENT_QUOTES);

                //if timer is set then check if time elapsed -> cron_target_date
                $expected_date = strtotime($cron_target_date);
                $remaining = $expected_date - time();
                error_log("expected_date_cron:" . $expected_date);
                error_log("time_cron:" . time());
                error_log("remaining_cron:" . $remaining);
                if ($remaining < 1) {
                    $ret .= "$time Submitting using template run: $project_pipeline_id.\n";
                    if (!empty($cron_prefix)) {
                        $run_name = "{$cron_prefix}-{$run_name_time}";
                    } else {
                        $run_name = "{$name}-{$run_name_time}";
                    }
                    $runSub = $this->startCronRun($project_pipeline_id, $ownerID, $run_name);
                    error_log(print_r($runSub, TRUE));


                    $new_run_id = $runSub["run_id"];
                    $status = $runSub["status"];
                    $ret .= "$time Cronjob(run id: $new_run_id) is $status.\n";
                    if (empty($cron_min) && empty($cron_hour)  && empty($cron_day)  && empty($cron_week)  && empty($cron_month)) {
                        $dbfun->resetProjectPipelineCron($project_pipeline_id);
                        $new_cron_target_date = "";
                    } else {
                        $cron_first = NULL;
                        $new_cron_target_date = json_decode($dbfun->updateProjectPipelineCron($project_pipeline_id, $cron_min, $cron_hour, $cron_day, $cron_week, $cron_month, $cron_prefix, $cron_first, $ownerID));
                    }

                    $ret .= "$time Next CronJob date: $new_cron_target_date\n";
                }
                error_log($ret);
            endforeach;
        }
        return $ret;
    }



    //http://localhost:8080/dolphinnext/api/service.php?upd=updateRunStat
    function updateRunStat()
    {
        $this->readINI();
        // get active runs //Available Run_status States: NextErr,NextSuc,NextRun,Error,Waiting,init,Terminated, Aborted
        // if runStatus equal to  Terminated, NextSuc, Error,NextErr, it means run already stopped. 
        $sql = "SELECT DISTINCT pp.id, pp.output_dir, pp.profile, pp.last_run_uuid, pp.publish_dir_check, pp.publish_dir, pp.date_modified, pp.owner_id, pp.pipeline_id, r.run_status
            FROM $this->db.project_pipeline pp
            INNER JOIN $this->db.run_log r
            WHERE pp.last_run_uuid = r.run_log_uuid AND pp.deleted=0 AND (r.run_status = 'init' OR r.run_status = 'Waiting' OR r.run_status = 'NextRun')";
        $data = $this->queryTable($sql);
        $time = date("M-d-Y H:i:s");
        $ret = "";
        if (!count($data) > 0) {
            $ret = "$time Active run is not found.";
        } else {
            $dbfun = new dbfuncs();
            foreach ($data as $runData) :
                $ownerID = $runData["owner_id"];
                $project_pipeline_id = $runData["id"];
                $profile = $runData["profile"];
                $profileAr = explode("-", $profile);
                $profileType = $profileAr[0];
                $profileId = $profileAr[1];
                $pipeline_id = $runData["pipeline_id"];
                $loadtype = "slow";
                $accTk = json_decode($dbfun->getSSOAccessTokenByUserID($ownerID), true);
                $accessToken = "";
                if (!empty($accTk) && !empty($accTk[0]) && !empty($accTk[0]["accessToken"])) {
                    $accessToken = $accTk[0]["accessToken"];
                }
                $process_id = "";
                $outJS = $dbfun->updateProPipeStatus($project_pipeline_id, $process_id, $loadtype, $ownerID);
                $out = json_decode($outJS, true);
                $finalRunStatus = $out["runStatus"];
                $ret .= "$time runId:$project_pipeline_id status:$finalRunStatus\n";

                if (!empty($profile) && !empty($project_pipeline_id)) {
                    $data = $dbfun->savePubWeb($project_pipeline_id, $profileType, $profileId, $pipeline_id, $ownerID, $accessToken);
                }
            endforeach;
        }
        return $ret;
    }

    //http://localhost:8080/dolphinnext/api/service.php?upd=cleanTempDir&&token=..
    function cleanTempDir()
    {
        $time = date("M-d-Y H:i:s");
        $dbfun = new dbfuncs();
        $data = $dbfun->cleanTempDir();
        if (!empty($data)) {
            $data = str_replace("\n", " ", $data);
            $ret = "$time Temp directory cleanup: $data";
        } else {
            $ret = "$time No temp directory found to clean.";
        }
        return $ret;
    }


    //http://localhost:8080/dolphinnext/api/service.php?upd=tagAmzInst
    //this feature is not finalized
    //    function tagAmzInst(){
    //        $sql = "SELECT DISTINCT a.id, a.owner_id, a.status
    //            FROM profile_amazon a
    //            WHERE (a.status = 'initiated' OR a.status = 'running')";
    //        $data=$this->queryTable($sql);
    //        $time = date("M-d-Y H:i:s");
    //        if (!count($data) > 0){ 
    //            //replace return with write log to file.
    //            return "$time There is no instance to tag."; 
    //        } else {
    //            $dbfun = new dbfuncs();
    //            foreach ($data as $profileData):
    //            $ownerID = $profileData["owner_id"];
    //            $profileId = $profileData["id"];
    //            $profileStatus = $profileData["status"];
    //            $tagAmazonInst = $dbfun -> tagAmazonInst($profileId,$ownerID);
    //            //replace return with write log to file.
    //            return "$time profileId:$profileId status:$profileStatus tagAmzLog:$tagAmazonInst\n";
    //            endforeach;
    //        }
    //    }

}
