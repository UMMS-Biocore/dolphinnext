<?php
require_once(__DIR__."/../../config/config.php");
require_once(__DIR__."/../ajax/dbfuncs.php");

class updates
{
    public $dbhost = "";
    public $db = "";
    public $dbuser = "";
    public $dbpass = "";

    function readINI()
    {
        $this->dbhost     = DBHOST;
        $this->db         = DB;
        $this->dbpass     = DBPASS;
        $this->dbuser     = DBUSER;
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
        return $sql;
    }


    function queryTable($sql)
    {
        $data = array();
        if ($res = $this->runSQL($sql)) {
            while ($row = $res->fetch_assoc()) {
                $data[] = $row;
            }
            $res->close();
        }
        return $data;
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

    //http://localhost:8080/dolphinnext/api/service.php?upd=updateAmzInst
    function updateAmzInst(){
        $sql = "SELECT DISTINCT a.id, a.owner_id, a.status
            FROM profile_amazon a
            INNER JOIN project_pipeline pp
            INNER JOIN run_log r
            WHERE pp.last_run_uuid = r.run_log_uuid
            AND a.autoshutdown_active = 'true'
            AND (a.status = 'initiated' OR a.status = 'running')
            AND pp.profile = CONCAT('amazon-',a.id) 
            AND pp.deleted=0 
            AND r.run_status != 'init' 
            AND r.run_status != 'Waiting' 
            AND r.run_status != 'NextRun'";
        $data=$this->queryTable($sql);
        $time = date("M-d-Y H:i:s");
        $ret = "";
        if (!count($data) > 0){ 
            $ret = "$time There is no instance to trigger autoshutdown."; 
        } else {
            $dbfun = new dbfuncs();
            foreach ($data as $profileData):
            $ownerID = $profileData["owner_id"];
            $profileId = $profileData["id"];
            $profileStatus = $profileData["status"];
            $triggerShutdown = $dbfun -> triggerShutdown($profileId,$ownerID, "slow");
            $ret .= "$time profileId:$profileId status:$profileStatus shutdownLog:$triggerShutdown\n";
            endforeach;
        }
        return $ret;
    }

    //http://localhost:8080/dolphinnext/api/service.php?upd=updateRunStat
    function updateRunStat (){
        // get active runs //Available Run_status States: NextErr,NextSuc,NextRun,Error,Waiting,init,Terminated, Aborted
        // if runStatus equal to  Terminated, NextSuc, Error,NextErr, it means run already stopped. 
        $sql = "SELECT DISTINCT pp.id, pp.output_dir, pp.profile, pp.last_run_uuid, pp.date_modified, pp.owner_id, r.run_status
            FROM project_pipeline pp
            INNER JOIN run_log r
            WHERE pp.last_run_uuid = r.run_log_uuid AND pp.deleted=0 AND (r.run_status = 'Waiting' OR r.run_status = 'NextRun')";
        $data=$this->queryTable($sql);
        $time = date("M-d-Y H:i:s");
        $ret = "";
        if (!count($data) > 0){ 
            $ret = "$time Active run is not found."; 
        } else {
            $dbfun = new dbfuncs();
            foreach ($data as $runData):
            $ownerID = $runData["owner_id"];
            $project_pipeline_id = $runData["id"];
            $loadtype = "slow";
            $outJS = $dbfun -> updateProPipeStatus ($project_pipeline_id, $loadtype, $ownerID);
            $out = json_decode($outJS,true);
            $finalRunStatus = $out["runStatus"];
            $ret .= "$time runId:$project_pipeline_id status:$finalRunStatus\n";
            endforeach;
        }
        return $ret;
    }
}

?>