<?php
require_once(__DIR__."/../../config/config.php");
require_once(__DIR__."/../ajax/dbfuncs.php");

class Run
{
    private $dbhost = "";
    private $db = "";
    private $dbuser = "";
    private $dbpass = "";
    private $BASE_PATH = "";
    private $SSO_URL = "";
    private $CLIENT_ID = "";
    private $CLIENT_SECRET = "";

    function readINI()
    {
        $this->dbhost     = DBHOST;
        $this->db         = DB;
        $this->dbpass     = DBPASS;
        $this->dbuser     = DBUSER;
        $this->BASE_PATH  = BASE_PATH;
        $this->SSO_URL    = SSO_URL;
        $this->CLIENT_ID  = CLIENT_ID;
        $this->CLIENT_SECRET  = CLIENT_SECRET;
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

    // use access&refresh tokens (which is delivered from SSO server) to get userINFO.
    // use userINFO to update user data in the database.
    // save tokens to database.
    // on success: return updated user Object.
    // on fail: return null
    function saveAccessRefreshToken($accessToken, $refreshToken, $expiresIn){
        $expirationDate= !empty($expiresIn) ? date('Y-m-d H:i:s', strtotime("+$expiresIn seconds")) : null;
        $dbfuncs = new dbfuncs();
        $this->readINI();
        // GET USER INFO
        $url = "{$this->SSO_URL}/api/v1/users/info";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json", "Authorization: Bearer $accessToken"));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $body = curl_exec($curl);
        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlErr = curl_errno($curl);
        curl_close($curl);
        if ($curlErr && $statusCode != 200){
            return null;
        }
        $currentUser = json_decode($body, true);
        if (!empty($currentUser["_id"]) && !empty($currentUser["email"]) && !empty($currentUser["username"])) {
            $sso_user_id = $currentUser["_id"];
            $name = $currentUser["name"];
            $scope = $currentUser["scope"];
            $email = $currentUser["email"];
            $username = $currentUser["username"];

            $checkUserData = json_decode($dbfuncs->getUserByEmailorUsername($email));
            $db_id = isset($checkUserData[0]) ? $checkUserData[0]->{'id'} : "";
            $db_role = isset($checkUserData[0]) ? $checkUserData[0]->{'role'} : "";
            if (!empty($db_id)){
                // User exist in table 
                // Update db with recent information
                $sql = "UPDATE users SET sso_id='$sso_user_id', name='$name', scope='$scope', username='$username',  date_modified= now(), last_modified_user ='$db_id'  WHERE id = '$db_id'";
                $this->runSQL($sql);
            } else if (!empty($email)){
                // insert user
                // Update db with latest information
                $email_clean = str_replace("'", "''", $email);
                $role = "user";
                $active = 1;
                $sql = "INSERT INTO users(name, email, username, role, active, memberdate, date_created, date_modified, perms, sso_id, scope) VALUES ('$name', '$email_clean', '$username', '$role', $active, now(),now(), now(), '3', '$sso_user_id', '$scope')";
                $inUser = $dbfuncs->insTable($sql);
                $idArray = json_decode($inUser,true);
                $db_id = $idArray["id"];
            }
            if (!empty($db_id)){
                $dbfuncs->insertAccessToken($accessToken, $expirationDate, $sso_user_id, $this->CLIENT_ID, $scope, $db_id);
                if (!empty ($refreshToken)){
                    $dbfuncs->insertRefreshToken($refreshToken, $sso_user_id, $this->CLIENT_ID, $scope, $db_id);
                }
                $currentUser["id"] =$db_id;
                $currentUser["role"] =$db_role;
                $currentUser["sso_id"] =$currentUser["_id"];
                $currentUser["accessToken"] =$accessToken;
                $currentUser["refreshToken"] =$refreshToken;
                return $currentUser;
            }
        }
        return null;
    }


    function verifyBearerToken($headers){
        error_log("verifyBearerToken");
        $dbfuncs = new dbfuncs();
        $this->readINI();
        $token = null;
        if(isset($headers['Authorization'])){
            $matches = array();
            preg_match('/Bearer (.*)/', $headers['Authorization'], $matches);
            if(isset($matches[1])){
                $token = $matches[1];
            }
        }
        if (empty($token)) return null;
        $tokenInfo = json_decode($dbfuncs->getSSOAccessToken($token),true);
        if (!empty($tokenInfo[0]) &&  $tokenInfo[0]["expirationDate"] && date("Y-m-d H:i:s") > date($tokenInfo[0]["expirationDate"])){
            $dbfuncs->removeSSOAccessToken($token);
            return null;
        } 
        if (empty($tokenInfo[0])){
            $tokeninfoURL = "{$this->SSO_URL}/api/v1/tokens/info?access_token={$token}";
            $curl = curl_init($tokeninfoURL);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
            // secure it:
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            $body = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlerr = curl_errno($curl);
            curl_close($curl);
            if ($curlerr && $statusCode != 200 && !empty($msg["user_id"])) {
                return null;
            }
            $msg = json_decode($body, true);
            $expiresIn = $msg["expires_in"];
            $user_id = $msg["user_id"];
            $currentUser = $this->saveAccessRefreshToken($token, null, $expiresIn);
        } else if (!empty($tokenInfo[0]["sso_user_id"])){
            $currentUser = json_decode($dbfuncs->getUserBySSOId($tokenInfo[0]["sso_user_id"]),true);
            if (isset($currentUser[0])) $currentUser = $currentUser[0];
        }
        if (!empty($currentUser)){
            return $currentUser;
        } 
        return null;
    }

    function startRun($body, $params, $user){
        $ownerID = $user["id"];
        $dbfuncs = new dbfuncs();
        $inputs= $body["in"]; // run inputs e.g. $inputs["reads"]
        $name= $body["name"]; // test_run
        $tmplt_run_id= $body["tmplt_id"]; //template run id e.g. 140
        // get template run path: e.g. ../tmp/runtmplt/140.tar.gz
        $tmplt_run_targz = $dbfuncs->getServerRunTemplatePath($tmplt_run_id); 
        //tar xf $tmplt_run_targz -C $dolphin_path_real
        // get $tmplt_run_id run id info
        // update name and insert
        $type = "dmeta";
        $project_pipeline_id = $dbfuncs->duplicateProjectPipeline($type, $tmplt_run_id, $ownerID, $inputs);
        error_log($project_pipeline_id);
        if (empty($project_pipeline_id)) return null;
        
        
        
//        $project_pipeline_id = $_REQUEST['project_pipeline_id'];
//        $profileType = $_REQUEST['profileType'];
//        $profileId = $_REQUEST['profileId'];
//        $docker_check = $_REQUEST['docker_check'];
//        $amazon_cre_id = $_REQUEST['amazon_cre_id'];
//        $google_cre_id = $_REQUEST['google_cre_id'];
//        $nextText = urldecode($_REQUEST['nextText']);
//        $runConfig = urldecode($_REQUEST['configText']);
//        $proVarObj = json_decode(urldecode($_REQUEST['proVarObj']));
//        $initRunOptions = urldecode($_REQUEST['initRunOptions']);
//        $runType = $_REQUEST['runType'];
//        $manualRun = isset($_REQUEST['manualRun']) ? $_REQUEST['manualRun'] : ""; //"true" or "false"
//        $uuid = $_REQUEST['uuid'];
//        $permCheck = 1;
//        //don't allow to update if user not own the project_pipeline.
//        $curr_ownerID= $db->queryAVal("SELECT owner_id FROM project_pipeline WHERE id='$project_pipeline_id'");
//        $permCheck = $db->checkUserOwnPerm($curr_ownerID, $ownerID);
//        if (!empty($permCheck)){
//            $db->updateProPipeLastRunUUID($project_pipeline_id,$uuid);
//            $db->updateProjectPipelineNewRun($project_pipeline_id,0,$ownerID);
//            $attemptData = json_decode($db->getRunAttempt($project_pipeline_id));
//            $attempt = isset($attemptData[0]->{'attempt'}) ? $attemptData[0]->{'attempt'} : "";
//            if (empty($attempt) || $attempt == 0 || $attempt == "0"){
//                $attempt = "0";
//            }
//            $proPipeAll = json_decode($db->getProjectPipelines($project_pipeline_id,"",$ownerID,""));
//            $db->saveRunLogOpt($project_pipeline_id, $proPipeAll,$uuid,$ownerID);
//            $amzConfigText = $db->getAmazonConfig($amazon_cre_id, $ownerID);
//            list($initialConfigText,$initialRunParams) = $db->getInitialRunConfig($proPipeAll, $project_pipeline_id, $attempt, $profileType,$profileId, $docker_check, $initRunOptions, $ownerID);
//            $mainConfigText = $db->getMainRunConfig($proPipeAll, $runConfig, $project_pipeline_id, $profileId, $profileType, $proVarObj, $ownerID);
//            $getCloudConfigFileDir = $db->getCloudConfig($project_pipeline_id, $attempt, $ownerID);
//            //create file and folders
//            list($targz_file, $dolphin_path_real, $runCmdAll) = $db->initRun($proPipeAll, $project_pipeline_id, $initialConfigText, $mainConfigText, $nextText, $profileType, $profileId, $uuid, $initialRunParams, $getCloudConfigFileDir, $amzConfigText, $attempt, $runType, $ownerID);
//            if ($manualRun == "true"){
//                $data = $db->getManualRunCmd($targz_file, $uuid, $dolphin_path_real);
//            } else {
//                //run the script in remote machine
//                $data = $db->runCmd($project_pipeline_id, $profileType, $profileId, $uuid, $targz_file, $dolphin_path_real, $runCmdAll, $ownerID);
//                //activate autoshutdown feature for cloud
//                if  ($profileType == "amazon" || $profileType == "google"){
//                    $autoshutdown_active = "true";
//                    $db->updateCloudShutdownActive($profileId, $autoshutdown_active, $profileType, $ownerID);
//                    $db->updateCloudShutdownDate($profileId, NULL, $profileType, $ownerID);
//                } 
//            }
//        }

        return json_encode("run started");
    }


}

?>