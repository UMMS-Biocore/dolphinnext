<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../ajax/dbfuncs.php");
$db = new dbfuncs();

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
$email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : "";
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : "";
if (!empty($username)){
    $usernameCl = str_replace(".","__",$username);   
}
session_write_close();
$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
$p = isset($_REQUEST["p"]) ? $_REQUEST["p"] : "";

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
}



if ($p=="saveRun"){
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $profileType = $_REQUEST['profileType'];
    $profileId = $_REQUEST['profileId'];
    $amazon_cre_id = $_REQUEST['amazon_cre_id'];
    $nextText = urldecode($_REQUEST['nextText']);
    $runConfig = urldecode($_REQUEST['configText']);
    $proVarObj = json_decode(urldecode($_REQUEST['proVarObj']));

    $runType = $_REQUEST['runType'];
    $uuid = $_REQUEST['uuid'];
    $db->updateProPipeLastRunUUID($project_pipeline_id,$uuid);
    $attemptData = json_decode($db->getRunAttempt($project_pipeline_id));
    $attempt = isset($attemptData[0]->{'attempt'}) ? $attemptData[0]->{'attempt'} : "";
    if (empty($attempt) || $attempt == 0 || $attempt == "0"){
        $attempt = "0";
    }
    //create initialrun script
    $initialrun_img = "https://galaxyweb.umassmed.edu/pub/dolphinnext_singularity/UMMS-Biocore-initialrun-24.07.2019.simg";
    $amzConfigText = $db->getAmazonConfig($amazon_cre_id);
    list($initialConfigText,$initialRunParams) = $db->getInitialRunConfig($project_pipeline_id, $attempt, $amzConfigText.$runConfig, $profileType,$profileId, $initialrun_img, $ownerID);
    $mainConfigText = $db->getMainRunConfig($amzConfigText.$runConfig, $project_pipeline_id, $profileId, $profileType, $proVarObj, $ownerID);
    $s3configFileDir = $db->getS3config($project_pipeline_id, $attempt, $ownerID);
    //create file and folders
    $log_array = $db->initRun($project_pipeline_id, $initialConfigText, $mainConfigText, $nextText, $profileType, $profileId, $amazon_cre_id, $uuid, $initialRunParams, $s3configFileDir, $ownerID);
    //run the script
    $data = $db->runCmd($project_pipeline_id, $profileType, $profileId, $log_array, $runType, $uuid, $initialRunParams, $attempt, $initialrun_img, $ownerID);
    //activate autoshutdown feature for amazon
    if  ($profileType == "amazon"){
        $autoshutdown_active = "true";
        $db->updateAmzShutdownActive($profileId, $autoshutdown_active, $ownerID);
        $db->updateAmzShutdownDate($profileId, NULL, $ownerID);
    }
}
else if ($p=="updateRunAttemptLog") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $res= $db->getUUIDLocal("run_log");
    $uuid = $res->rev_uuid;
    //add run into run table and increase the run attempt. $status = "init";
    $db->updateRunAttemptLog("init", $project_pipeline_id, $uuid, $ownerID);
    $data = json_encode($uuid);
}
else if ($p=="updateProPipeStatus") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $loadtype = "fast";
    $data = $db->updateProPipeStatus($project_pipeline_id, $loadtype, $ownerID);
}
else if ($p=="getFileContent"){
    $filename = $_REQUEST['filename'];
    if (isset($_REQUEST['project_pipeline_id'])){
        $project_pipeline_id = $_REQUEST['project_pipeline_id'];
        $uuid = $db->getProPipeLastRunUUID($project_pipeline_id);
        //fix for old runs 
        if (empty($uuid)){
            $uuid = "run".$project_pipeline_id;
            $filename = preg_replace('/^run/', '', $filename);
        }
    } else if (isset($_REQUEST['uuid'])){
        $uuid = $_REQUEST['uuid'];
    }
    $data = $db -> getFileContent($uuid,$filename,$ownerID);
}
else if ($p=="saveFileContent"){
    $textRaw = $_REQUEST['text'];
    $text = urldecode($textRaw);
    $filename = $_REQUEST['filename'];
    $uuid = $_REQUEST['uuid'];
    $data = $db -> saveFileContent($text,$uuid,$filename,$ownerID);
}

else if ($p=="getFileList"){
    $uuid  = $_REQUEST['uuid'];
    $path = $_REQUEST['path'];
    $data = $db->getFileList($uuid, $path, "filedir");
}
else if ($p=="getRsyncStatus"){
    $filename  = $_REQUEST['filename'];
    $data = $db->getRsyncStatus($filename, $email, $ownerID);
}
else if ($p=="resetUpload"){
    $filename  = $_REQUEST['filename'];
    $data = $db->resetUpload($filename, $email, $ownerID);
}
else if ($p=="retryRsync"){
    $fileName  = $_REQUEST['filename'];
    $target_dir = $_REQUEST['dir'];
    $run_env = $_REQUEST['run_env'];
    $data = $db->retryRsync($fileName, $target_dir, $run_env, $email, $ownerID);

}
else if ($p=="getReportData"){
    $uuid  = $_REQUEST['uuid'];
    $path = $_REQUEST['path']; //pubweb, run
    $pipeline_id = $_REQUEST['pipeline_id'];
    $pipe = $db->loadPipeline($pipeline_id,$ownerID);
    $pipeData = json_decode($pipe,true);
    $pubWebDir = $pipeData[0]['publish_web_dir'];
    $data = array();
    if (!empty($pubWebDir)){
        if (!empty($pipeData[0]["nodes"])){
            $nodes = json_decode($pipeData[0]["nodes"]);
            foreach ($nodes as $gNum => $item):
            $out = array();
            if ($item[2] == "outPro"){
                $push = false;
                $name = $item[3];
                $processOpt = $item[4];
                $out["id"] = $gNum;
                $out["name"] = $name;
                foreach ($processOpt as $key => $feature):
                if ($key == "pubWeb"){
                    $push = true;
                    $pubWebAr = explode(",", $feature);
                }
                $out[$key] = $feature;
                endforeach;
                if ($push == true){
                    $fileList = array_values((array)json_decode($db->getFileList($uuid, "$path/$name", "onlyfile")));
                    $fileList = array_filter($fileList);
                    if (!empty($fileList)){
                        $out["fileList"] = $fileList;
                        //split each view method into new array
                        foreach ($pubWebAr as $eachPubWeb):
                        $out["pubWeb"] = $eachPubWeb;
                        $out["id"] = $out["id"]."_".$eachPubWeb;
                        if (strtolower($name) == "summary"  || strtolower($name) == "multiqc"){
                            array_unshift($data , $out); //push to the top of the array
                        } else {
                            $data[] = $out; //push $out object into array
                        }
                        endforeach;
                    }
                }
            }
            endforeach;
        }
    }
    $data = json_encode($data);
}
else if ($p=="savePubWeb"){
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $profileType = $_REQUEST['profileType'];
    $profileId = $_REQUEST['profileId'];
    $pipeline_id = $_REQUEST['pipeline_id'];
    $uuid = $db->getProPipeLastRunUUID($project_pipeline_id);
    //get pubWebDir
    $pipeData = json_decode($db->loadPipeline($pipeline_id,$ownerID));
    $pubWebDir = $pipeData[0]->{'publish_web_dir'};
    if (!empty($pubWebDir)){
        // get outputdir
        $proPipeAll = json_decode($db->getProjectPipelines($project_pipeline_id,"",$ownerID,""));
        $outdir = $proPipeAll[0]->{'output_dir'};
        $publish_dir = isset($proPipeAll[0]->{'publish_dir'}) ? $proPipeAll[0]->{'publish_dir'} : "";
        $publish_dir_check = isset($proPipeAll[0]->{'publish_dir_check'}) ? $proPipeAll[0]->{'publish_dir_check'} : "";
        if ($publish_dir_check == "true" && !empty($publish_dir)){
            $outdir = $publish_dir;
        }
        $down_file_list = explode(',', $pubWebDir);
        foreach ($down_file_list as &$value) {
            $value = $outdir."/".$value;
        }
        unset($value);
        $data = $db -> saveNextflowLog($down_file_list,  $uuid, "pubweb", $profileType, $profileId, $project_pipeline_id, $ownerID);
    } else {
        $data = json_encode("pubweb is not defined");
    }

}
else if ($p=="saveNextflowLog"){
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $profileType = $_REQUEST['profileType'];
    $profileId = $_REQUEST['profileId'];
    $uuid = $db->getProPipeLastRunUUID($project_pipeline_id);
    $data = "";
    if (!empty($uuid)){
        // get outputdir
        $proPipeAll = json_decode($db->getProjectPipelines($project_pipeline_id,"",$ownerID,""));
        $outdir = $proPipeAll[0]->{'output_dir'};
        $run_path_real = "$outdir/run{$project_pipeline_id}";
        $down_file_list=array("log.txt",".nextflow.log","report.html", "timeline.html", "trace.txt","dag.html","err.log", "initialrun/initial.log");
        foreach ($down_file_list as &$value) {
            $value = $run_path_real."/".$value;
        }
        unset($value);
        $data = $db -> saveNextflowLog($down_file_list, $uuid, "run", $profileType, $profileId, $project_pipeline_id, $ownerID);
    }
}
else if ($p=="getLsDir"){
    $dir = $_REQUEST['dir'];
    $profileType = $_REQUEST['profileType'];
    $profileId = $_REQUEST['profileId'];
    $amazon_cre_id = isset($_REQUEST['amazon_cre_id']) ? $_REQUEST['amazon_cre_id'] : "";
    $data = $db -> getLsDir($dir, $profileType, $profileId, $amazon_cre_id, $ownerID);
}
else if ($p=="chkRmDirWritable"){
    $dir = $_REQUEST['dir'];
    $run_env = $_REQUEST['run_env'];
    $profileAr = explode("-", $run_env);
    $profileType = $profileAr[0];
    $profileId = $profileAr[1];
    $data = $db -> chkRmDirWritable($dir, $profileType, $profileId, $ownerID);
}

else if ($p=="getGeoData"){
    $geo_id = $_REQUEST['geo_id'];
    $data = $db -> getGeoData($geo_id, $ownerID);
}
else if ($p=="getRun"){
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $data = $db -> getRun($project_pipeline_id,$ownerID);
}
else if ($p=="terminateRun"){
    $commandType = "terminateRun";
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $profileType = $_REQUEST['profileType'];
    $profileId = $_REQUEST['profileId'];
    $executor = $_REQUEST['executor'];
    if ($executor != 'local') {
        $pid = json_decode($db -> getRunPid($project_pipeline_id))[0]->{'pid'};
        if (!empty($pid)){
            $data = $db -> sshExeCommand($commandType, $pid, $profileType, $profileId, $project_pipeline_id, $ownerID);
        } else {
            $data = json_encode("pidNotExist");	
        }
    } else if ($executor == 'local'){
        $data = $db -> sshExeCommand($commandType, "", $profileType, $profileId, $project_pipeline_id, $ownerID);
    }
}
else if ($p=="checkRunPid"){
    $commandType = "checkRunPid";
    $pid = $_REQUEST['pid'];
    $profileType = $_REQUEST['profileType'];
    $profileId = $_REQUEST['profileId'];
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    if ($profileType == 'cluster') {
        $data = $db -> sshExeCommand($commandType, $pid, $profileType, $profileId, $project_pipeline_id, $ownerID);
    }
}
else if ($p=="updateRunPid"){
    $pid = $_REQUEST['pid'];
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $data = $db -> updateRunPid($project_pipeline_id, $pid, $ownerID);
}
else if ($p=="updateRunStatus"){
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $run_status = $_REQUEST['run_status'];
    $duration = isset($_REQUEST['duration']) ? $_REQUEST['duration'] : "";
    $db -> updateRunLog($project_pipeline_id, $run_status, $duration, $ownerID);
    $data = $db -> updateRunStatus($project_pipeline_id, $run_status, $ownerID);
    // amazon check triggerShutdown
    $runDataJS = $db->getLastRunData($project_pipeline_id,$ownerID);
    $runData = json_decode($runDataJS,true)[0];
    $profile = $runData["profile"];
    if (!empty($profile)){
        $profileAr = explode("-", $profile);
        $profileType = $profileAr[0];
        $profileId = $profileAr[1];
        if ($profileType == "amazon" && ($run_status =="Terminated" || $run_status == "Aborted")){
            $db->triggerShutdown($profileId,$ownerID, "fast");
        }
    }
}
else if ($p=="getRunStatus"){
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $data = $db -> getRunStatus($project_pipeline_id, $ownerID);
}
else if ($p=="startProAmazon"){
    $nodes = $_REQUEST['nodes'];
    $autoscale_check = $_REQUEST['autoscale_check'];
    $autoscale_maxIns = $_REQUEST['autoscale_maxIns'];
    $autoscale_minIns = isset($_REQUEST['autoscale_minIns']) ? $_REQUEST['autoscale_minIns'] : "";
    $autoshutdown_check = $_REQUEST['autoshutdown_check'];
    //reset on startup
    $autoshutdown_active = "";
    $autoshutdown_date = NULL;
    $db -> updateProfileAmazonOnStart($id,$nodes,$autoscale_check, $autoscale_maxIns,$autoscale_minIns, $autoshutdown_date, $autoshutdown_active, $autoshutdown_check, $ownerID);
    $data = $db -> startProAmazon($id,$ownerID,$usernameCl);
}
else if ($p=="stopProAmazon"){
    $data = $db -> stopProAmazon($id,$ownerID, $usernameCl);
}
else if ($p=="checkAmzStopLog"){
    $data = $db -> checkAmzStopLog($id,$ownerID,$usernameCl);
}
else if ($p=="checkAmazonStatus"){
    $profileId = $_REQUEST['profileId'];
    $data = $db -> checkAmazonStatus($profileId,$ownerID,$usernameCl);
}
else if ($p=="runAmazonCloudCheck"){
    $profileId = $_REQUEST['profileId'];
    $data = $db -> runAmazonCloudCheck($profileId,$ownerID, $usernameCl);
}
else if ($p=="getAllParameters"){
    $data = $db -> getAllParameters($ownerID);
}
else if ($p=="getEditDelParameters"){
    $data = $db -> getEditDelParameters($ownerID);
}
else if ($p=="savefeedback"){
    $email = $_REQUEST['email'];
    $message = $_REQUEST['message'];
    $url = $_REQUEST['url'];
    $data = $db -> savefeedback($email,$message,$url);
}
else if ($p=="getUpload"){
    $name = $_REQUEST['name'];
    $data = $db -> getUpload($name,$email);
}
else if ($p=="removeUpload"){
    $name = $_REQUEST['name'];
    $data = $db -> removeUpload($name,$email);
}
else if ($p=="getAllGroups"){
    $data = $db -> getAllGroups();
}
else if ($p=="viewGroupMembers"){
    $g_id = $_REQUEST['g_id'];
    $data = $db -> viewGroupMembers($g_id);
}
else if ($p=="getMemberAdd"){
    $g_id = $_REQUEST['g_id'];
    $data = $db -> getMemberAdd($g_id);
}
else if ($p=="getProjects"){
    $data = $db -> getProjects($id,$ownerID);
}
else if ($p=="getGroups"){
    $data = $db -> getGroups($id,$ownerID);
}
else if ($p=="getAllUsers"){
    $data = $db -> getAllUsers($ownerID);
}
else if ($p=="getUserById"){
    $data = $db -> getUserById($id);
}
else if ($p=="changeActiveUser"){
    $user_id = $_REQUEST['user_id'];
    $type = $_REQUEST['type'];
    $data = $db->changeActiveUser($user_id, $type); 
    if ($type == "activateSendUser"){
        $userData = json_decode($db->getUserById($user_id))[0];
        if (!empty($userData)){
            $email = $userData->{'email'};
            $name = $userData->{'name'};
            $logintype = $userData->{'logintype'};
            if ($email != ""){
                $subject = "DolphinNext Account Activation";
                $loginText = "You can start browsing at ".BASE_PATH;
                if ($logintype == "google"){
                    $loginText = "Please use <b>Sign-In with Google</b> button to enter your account at ".BASE_PATH;
                } else if ($logintype == "ldap"){
                    $loginText = "Please use your e-mail address($email) and e-mail password to enter your account at ".BASE_PATH;
                } else if ($logintype == "password"){
                    $password_val = $db->randomPassword();
                    $pass_hash = hash('md5', $password_val . SALT) . hash('sha256', $password_val . PEPPER);
                    $db->updateUserPassword($user_id, $pass_hash, $ownerID);
                    $loginText = "Please use following e-mail address and password to enter your account at ".BASE_PATH;
                    $loginText .= "<br><br>E-mail: $email<br>";
                    $loginText .= "Password: $password_val";
                }
                $from = EMAIL_SENDER;
                $from_name = "DolphinNext Team";
                $to  = $email;
                $message = "Dear $name,<br><br>Your DolphinNext account is now active!<br>$loginText<br><br>Best Regards,<br><br>".COMPANY_NAME." DolphinNext Team";
                $db->sendEmail($from, $from_name, $to, $subject, $message);
            }
        }
    }
}
else if ($p=="changeRoleUser"){
    $user_id = $_REQUEST['user_id'];
    $type = $_REQUEST['type'];
    $data = $db->changeRoleUser($user_id, $type);  
}
else if ($p=="changePassword"){
    $error = array();
    $password0 = $_REQUEST['password0'];
    $password1 = $_REQUEST['password1'];
    $password2 = $_REQUEST['password2'];
    $pass_hash0 = hash('md5', $password0 . SALT) . hash('sha256', $password0 . PEPPER);
    $pass_hash1 = "";
    if ($password1 == $password2 && !empty($password1)){
        $pass_hash1 = hash('md5', $password1 . SALT) . hash('sha256', $password1 . PEPPER);
    } else {
        $error['password1'] ="New password is not match";
    }
    $pass_hash0DB = $db->queryAVal("SELECT pass_hash FROM users WHERE id = '$ownerID'");
    if ($pass_hash0DB !=  $pass_hash0 && !empty($pass_hash0DB)){
        $error['password0'] ="Old password is not correct.";
    } else if  (!empty($pass_hash1)){
        $data =  $db->updateUserPassword($ownerID, $pass_hash1, $ownerID);
    }
    if (!empty($error)){
        $data  = json_encode($error);
    } 
}
else if ($p=="saveUserManual"){
    $name = str_replace("'", "", $_REQUEST['name']);
    $email = $_REQUEST['email'];
    $username = str_replace("'", "", $_REQUEST['username']);
    $institute = str_replace("'", "", $_REQUEST['institute']);
    $lab = str_replace("'", "", $_REQUEST['lab']);
    $logintype = $_REQUEST['logintype'];
    $error = $db->checkExistUser($id,$username,$email);
    if (!empty($error)){    
        $data = json_encode($error);
    } else {
        if (!empty($id)) {
            $data = $db->updateUserManual($id, $name, $email, $username, $institute, $lab, $logintype, $ownerID);  
        } else {
            $data = $db->insertUserManual($name, $email, $username, $institute, $lab, $logintype); 
            $ownerIDarr = json_decode($data,true); 
        }
    }
}
else if ($p=="saveGoogleUser"){
    $google_id = $_REQUEST['google_id'];
    $name = $_REQUEST['name'];
    $email = $_REQUEST['email'];
    $google_image = $_REQUEST['google_image'];
    //check if Google ID already exits
    $checkUserData = json_decode($db->getUserByEmail($email));
    $id = isset($checkUserData[0]) ? $checkUserData[0]->{'id'} : "";
    $role = isset($checkUserData[0]) ? $checkUserData[0]->{'role'} : "";
    $username = isset($checkUserData[0]) ? $checkUserData[0]->{'username'} : "";
    //travis fix
    if (!headers_sent()) {
        session_start();
    }
    if ($username != ""){
        $_SESSION['username'] = $username;
    }
    $_SESSION['google_login'] = true;
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;
    $_SESSION['google_image'] = $google_image;
    if (!empty($id)) {
        $_SESSION['ownerID'] = $id;
        $_SESSION['role'] = $role;
        $data = $db->updateGoogleUser($id, $google_id, $email, $google_image);  
    } else {
        $data = $db->insertGoogleUser($google_id, $email, $google_image);  
        $ownerIDarr = json_decode($data,true); 
        $_SESSION['ownerID'] = $ownerIDarr['id'];
        $_SESSION['role'] = "";
        //first user will be admin
        if ($ownerIDarr['id'] == "1"){
            $db->changeRoleUser($ownerIDarr['id'], "admin");
        } else {
            $db->changeRoleUser($ownerIDarr['id'], "user");
        }
    }
    session_write_close();
} else if ($p=="impersonUser"){
    $user_id = $_REQUEST['user_id'];
    if (!empty($_SESSION['admin_id'])){
        $admin_id = $_SESSION['admin_id'];
    } else {
        $admin_id = $_SESSION['ownerID'];
    }
    if (!empty($admin_id)){
        session_destroy();
        session_start();
        $userData = json_decode($db->getUserById($user_id))[0];
        $username = $userData->{'username'};
        $email = $userData->{'email'};
        $name = $userData->{'name'};
        $role = $userData->{'role'};
        $_SESSION['username'] = $username;
        $_SESSION['name'] = $name;
        $_SESSION['email'] = $email;
        $_SESSION['ownerID'] = $user_id;
        $_SESSION['admin_id'] = $admin_id;
        $_SESSION['role'] = $role;
        session_write_close();
        $impersonAr = array('imperson' => 1);
        $data = json_encode($impersonAr);
    }

} 
else if ($p=="getUserGroups"){
    $data = $db -> getUserGroups($ownerID);
}
else if ($p=="getUserRole"){
    $data = $db -> getUserRole($ownerID);
}
else if ($p=="getExistProjectPipelines"){
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db -> getExistProjectPipelines($pipeline_id,$ownerID);
}
else if ($p=="getProjectPipelines"){
    $project_id = isset($_REQUEST['project_id']) ? $_REQUEST['project_id'] : "";
    $data = $db -> getProjectPipelines($id,$project_id,$ownerID,$userRole);
}
else if ($p=="getRunLog"){
    $project_pipeline_id = isset($_REQUEST['project_pipeline_id']) ? $_REQUEST['project_pipeline_id'] : "";
    $data = $db -> getRunLog($project_pipeline_id);
}
else if ($p=="sendEmail"){
    $adminemail = $_REQUEST['adminemail'];
    $useremail = $_REQUEST['useremail'];
    $message = $_REQUEST['message'];
    $subject = $_REQUEST['subject'];
    $checkAdminData = json_decode($db->getUserByEmail($adminemail));
    $adminName = isset($checkAdminData[0]) ? $checkAdminData[0]->{'name'} : "";
    $data = $db -> sendEmail($adminemail,$adminName,$useremail,$subject,$message);
}
else if ($p=="getProjectInputs"){
    $project_id = $_REQUEST['project_id'];
    $data = $db -> getProjectInputs($project_id,$ownerID);
}
else if ($p=="getProjectFiles"){
    $project_id = $_REQUEST['project_id'];
    $data = $db -> getProjectFiles($project_id,$ownerID);
}
else if ($p=="getPublicInputs"){
    $data = $db -> getPublicInputs($id);
}
else if ($p=="getPublicFiles"){
    $host = $_REQUEST['host'];
    $data = $db -> getPublicFiles($host);
}
else if ($p=="getPublicValues"){
    $host = $_REQUEST['host'];
    $data = $db -> getPublicValues($host);
}
else if ($p=="getProjectValues"){
    $project_id = $_REQUEST['project_id'];
    $data = $db -> getProjectValues($project_id,$ownerID);
}
else if ($p=="getProjectInput"){
    $data = $db -> getProjectInput($id,$ownerID);
}
else if ($p=="getProjectPipelineInputs"){
    $project_pipeline_id = isset($_REQUEST['project_pipeline_id']) ? $_REQUEST['project_pipeline_id'] : "";
    if (!empty($id)) {
        $data = $db->getProjectPipelineInputsById($id,$ownerID);
    } else {
        $data = $db->getProjectPipelineInputs($project_pipeline_id,$ownerID);
    }
}
else if ($p=="getInputs"){
    $data = $db -> getInputs($id,$ownerID);
}
else if ($p=="getCollection"){
    if (!empty($id)) {
        $data = $db->getCollectionById($id,$ownerID);
    } else {
        $data = $db->getCollections($ownerID);
    }
}
else if ($p=="getCollectionFiles"){
    $data = $db->getCollectionFiles($id,$ownerID);
}
else if ($p=="getFile"){
    if (!empty($id)) {
        //        $data = $db->getFileById($id,$ownerID);
    } else {
        $data = $db->getFiles($ownerID);
    }
}
else if ($p=="getPipelineGroup"){
    $data = $db -> getPipelineGroup($ownerID);
}
else if ($p=="getAllProcessGroups"){
    $data = $db -> getAllProcessGroups($ownerID);
}
else if ($p=="getEditDelProcessGroups"){
    $data = $db -> getEditDelProcessGroups($ownerID);
}
else if ($p=="getEditDelPipelineGroups"){
    $data = $db -> getEditDelPipelineGroups($ownerID);
}
else if ($p=="removeParameter"){
    $db->removeProcessParameterByParameterID($id);
    $data = $db->removeParameter($id);
}
else if ($p=="removeProcessGroup"){
    $db->removeProcessParameterByProcessGroupID($id);
    $db->removeProcessByProcessGroupID($id);
    $data = $db->removeProcessGroup($id);
}
else if ($p=="removePipelineGroup"){
    $data = $db->removePipelineGroup($id);
}
else if ($p=="removePipelineById"){   
    $data = $db -> removePipelineById($id);
}
else if ($p=="removeProcess"){   
    $db->removeProcessParameterByProcessID($id);
    $data = $db -> removeProcess($id);
}
else if ($p=="removeProject"){   
    $db -> removeProjectPipelineInputbyProjectID($id);
    $db -> removeRunByProjectID($id);
    $db -> removeProjectPipelinebyProjectID($id);
    $db -> removeProjectInputbyProjectID($id);
    $data = $db -> removeProject($id);
}
else if ($p=="removeGroup"){   
    $db -> removeUserGroup($id);
    $data = $db -> removeGroup($id);
}
else if ($p=="removeProjectPipeline"){  
    $db -> removeRun($id);
    $db -> removeProjectPipelineInputByPipe($id);
    $data = $db -> removeProjectPipeline($id);
}
else if ($p=="removeProjectInput"){   
    $data = $db -> removeProjectInput($id);
}
else if ($p=="removeInput"){   
    $data = $db -> removeInput($id);
}
else if ($p=="removeFile"){
    $file_array = $_REQUEST['file_array'];
    $collection_arr = array();
    foreach ($file_array as $file_id):
    //   Get all collections into array
    $colsOfFile= json_decode($db->getCollectionsOfFile($file_id, $ownerID));
    for ($i = 0; $i < count($colsOfFile); $i++) {
        $c_id = $colsOfFile[$i]->{'c_id'};
        if (!in_array($c_id, $collection_arr))
        {
            $collection_arr[] = $c_id; 
        }
    }
    $removeFileCollection = $db -> removeFileCollection($file_id, $ownerID);
    $removeFileProject = $db -> removeFileProject($file_id, $ownerID);
    $db -> removeFile($file_id, $ownerID);
    endforeach;
    //check if these collections have any files, if not delete collection
    $removedCollection = array();
    for ($i = 0; $i < count($collection_arr); $i++) {
        $allfiles= json_decode($db->getCollectionFiles($collection_arr[$i], $ownerID));
        if (empty($allfiles[0])){
            $db -> removeCollection($collection_arr[$i], $ownerID);
            $db -> removeProjectPipelineInputByCollection($collection_arr[$i]);
            $removedCollection[] = $collection_arr[$i]; 
        } 
    }
    $data = json_encode($removedCollection);
}
else if ($p=="removeProLocal"){   
    $data = $db -> removeProLocal($id);
}
else if ($p=="removeProCluster"){  
    $data = $db -> removeProCluster($id);
}
else if ($p=="removeProAmazon"){   
    $data = $db -> removeProAmazon($id);
}
else if ($p=="removeProjectPipelineInput"){   
    $data = $db -> removeProjectPipelineInput($id);
}
else if ($p=="removeProcessParameter"){   
    $data = $db -> removeProcessParameter($id);
}
else if ($p=="saveParameter"){
    $name = $_REQUEST['name'];
    $qualifier = $_REQUEST['qualifier'];
    $file_type = $_REQUEST['file_type'];
    $parData = $db->getParameterByName($name,$qualifier,$file_type);
    $parData = json_decode($parData,true);
    if (isset($parData[0])){
        $parId = $parData[0]["id"];
    } else {
        $parId = "";
    }
    settype($id, 'integer');
    if (!empty($id)) {
        $data = $db->updateParameter($id, $name, $qualifier, $file_type, $ownerID);
    } else {
        if (empty($parId)){
            $data = $db->insertParameter($name, $qualifier, $file_type, $ownerID);
        } else {
            if ($userRole == "admin"){
                $db->updateParameter($parId, $name, $qualifier, $file_type, $ownerID);
            }
            $data = json_encode(array('id' => $parId));
        }
    }
}
else if ($p=="getAmz")
{
    if (!empty($id)) {
        $data = json_decode($db->getAmzbyID($id, $ownerID));
        foreach($data as $d){
            $access = $d->amz_acc_key;
            $d->amz_acc_key = trim($db->amazonDecode($access));
            $secret = $d->amz_suc_key;
            $d->amz_suc_key = trim($db->amazonDecode($secret));
        }
        $data=json_encode($data);
    } else {
        $data = $db->getAmz($ownerID);
    }
}
else if ($p=="getSSH")
{
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
    if (!empty($id)) {
        $data = json_decode($db->getSSHbyID($id, $userRole, $admin_id, $ownerID));
        foreach($data as $d){
            $d->prikey = $db->readKey($id, 'ssh_pri', $ownerID);
            $d->pubkey = $db->readKey($id, 'ssh_pub', $ownerID);
        }
        $data=json_encode($data);
    } else {
        $data = $db->getSSH($userRole, $admin_id, $type, $ownerID);
    }
}
else if ($p=="removeSSH")
{
    $db->delKey($id, "ssh_pri", $ownerID);
    $db->delKey($id, "ssh_pub", $ownerID);
    $data = $db->removeSSH($id);
}
else if ($p=="removeAmz")
{
    $data = $db->removeAmz($id);
}
else if ($p=="removeUser")
{
    $data = $db->removeUser($id,$ownerID);
}
else if ($p=="removeGithub")
{
    $data = $db->removeGithub($id,$ownerID);
}
else if ($p=="generateKeys")
{
    $data = $db->generateKeys($ownerID);
}

else if ($p=="getProfileVariables"){
    $proType = isset($_REQUEST['proType']) ? $_REQUEST['proType'] : "";
    if (!empty($id) && !empty($proType)) {
        if ($proType == "cluster"){
            $data = $db->getProfileClusterbyID($id, $ownerID);
        } else if ($proType == "amazon"){
            $data = $db->getProfileAmazonbyID($id, $ownerID);
        }
    } else {
        $proClu = $db->getProfileCluster($ownerID);
        $proAmz = $db->getProfileAmazon($ownerID);
        $clu_obj = json_decode($proClu,true);
        $amz_obj = json_decode($proAmz,true);
        $merged_obj = array_merge($clu_obj, $amz_obj);
        $new_obj = array();
        if (isset($merged_obj)){
            if (!empty($merged_obj[0])){
                for ($i = 0; $i < count($merged_obj); $i++) {
                    $variable = isset($merged_obj[$i]["variable"]) ? $merged_obj[$i]["variable"] : "";
                    $hostname = isset($merged_obj[$i]["hostname"]) ? $merged_obj[$i]["hostname"] : "";
                    if (!empty($variable) && !empty($hostname)){
                        $tmpObj = array();
                        $tmpObj["variable"]=$variable;
                        $tmpObj["hostname"]=$hostname;
                        $new_obj[] = $tmpObj; //push $out object into array
                    }
                }
            }
        }
        $data= json_encode($new_obj);  
    }

}
else if ($p=="getProfiles")
{
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
    if (empty($type)){
        $proClu = $db->getProfileCluster($ownerID);
        $proAmz = $db->getProfileAmazon($ownerID);
    } else {
        $proClu = $db->getPublicProfileCluster($ownerID);
        $proAmz = $db->getPublicProfileAmazon($ownerID);
    }
    $clu_obj = json_decode($proClu,true);
    $amz_obj = json_decode($proAmz,true);
    $result = array_merge($clu_obj, $amz_obj);
    $data = json_encode($result);
}
else if ($p=="getProfileCluster")
{
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
    if (!empty($id)) {
        $data = $db->getProfileClusterbyID($id, $ownerID);
    } else {
        if (empty($type)){
            $data = $db->getProfileCluster($ownerID);
        } else {
            $data = $db->getPublicProfileCluster($ownerID);
        }
    }
}
else if ($p=="getProfileAmazon")
{
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
    if (!empty($id)) {
        $data = $db->getProfileAmazonbyID($id, $ownerID);
    } else {
        if (empty($type)){
            $data = $db->getProfileAmazon($ownerID); 
        } else {
            $data = $db->getPublicProfileAmazon($ownerID);
        }
    }
    // convert autoshutdown_date time to seconds
    $new_obj = json_decode($data,true);
    if (!empty($new_obj)){
        for ($i = 0; $i < count($new_obj); $i++) {
            $autoshutdown_date = isset($new_obj[$i]["autoshutdown_date"]) ? $new_obj[$i]["autoshutdown_date"] : "";
            if (!empty($autoshutdown_date)){
                $expected_date = strtotime($autoshutdown_date);
                $remaining = $expected_date - time();
                $new_obj[$i]["autoshutdown_date"]=$remaining;
            }
        }
        $data= json_encode($new_obj); 
    }
}
else if ($p=="updateAmazonProStatus"){
    $status = $_REQUEST['status'];
    $data = $db->updateAmazonProStatus($id, $status, $ownerID);
}
else if ($p=="updateAmzShutdownCheck"){
    $autoshutdown_check = $_REQUEST['autoshutdown_check'];
    if ($autoshutdown_check == "false"){
        $db->updateAmzShutdownDate($id, NULL, $ownerID);
    }
    $data = $db->updateAmzShutdownCheck($id, $autoshutdown_check, $ownerID);
    if ($autoshutdown_check == "true"){
        //to set timer
        $db->triggerShutdown($id,$ownerID, "fast");
    }
}
else if ($p=="saveSSHKeys"){
    $name = $_REQUEST['name'];
    $hide = $_REQUEST['hide'];
    $check_userkey = isset($_REQUEST['check_userkey']) ? $_REQUEST['check_userkey'] : "";
    $check_ourkey = isset($_REQUEST['check_ourkey']) ? $_REQUEST['check_ourkey'] : "";
    $prikeyRaw = $_REQUEST['prikey'];
    $pubkeyRaw = $_REQUEST['pubkey'];
    $prikey = urldecode($prikeyRaw);
    $pubkey = urldecode($pubkeyRaw);

    if (!empty($id)) {
        $data = $db->updateSSH($id, $name, $hide, $check_userkey,$check_ourkey, $ownerID);
        $db->insertKey($id, $prikey, "ssh_pri", $ownerID);
        $db->insertKey($id, $pubkey, "ssh_pub", $ownerID);
    } else {
        $data = $db->insertSSH($name, $hide, $check_userkey,$check_ourkey, $ownerID);
        $idArray = json_decode($data,true);
        $id = $idArray["id"];
        $db->insertKey($id, $prikey, "ssh_pri", $ownerID);
        $db->insertKey($id, $pubkey, "ssh_pub", $ownerID);
    }
}
else if ($p=="saveAmzKeys"){
    $name = $_REQUEST['name'];
    $amz_def_reg = $_REQUEST['amz_def_reg'];
    $amz_acc_keyRaw = $_REQUEST['amz_acc_key'];
    $amz_suc_keyRaw = $_REQUEST['amz_suc_key'];
    $amz_acc_key = $db->amazonEncode($amz_acc_keyRaw);
    $amz_suc_key = $db->amazonEncode($amz_suc_keyRaw);
    if (!empty($id)) {
        $data = $db->updateAmz($id, $name, $amz_def_reg,$amz_acc_key,$amz_suc_key, $ownerID);
    } else {
        $data = $db->insertAmz($name, $amz_def_reg,$amz_acc_key,$amz_suc_key, $ownerID);
    }
}
if ($p=="publishGithub"){
    $data = json_encode("");
    $username_id = isset($_REQUEST['username']) ? $_REQUEST['username'] : "";
    $github_repo = isset($_REQUEST['github_repo']) ? $_REQUEST['github_repo'] : "";
    $github_branch = isset($_REQUEST['github_branch']) ? $_REQUEST['github_branch'] : "";
    $proVarObj = isset($_REQUEST['proVarObj']) ? json_decode(urldecode($_REQUEST['proVarObj'])) : "";
    $type = $_REQUEST['type']; //downPack, pushGithub
    $pipeline_id = $_REQUEST['pipeline_id']; 
    $pipeData = $db->loadPipeline($pipeline_id,$ownerID);
    $pipe_obj = json_decode($pipeData,true);
    if (!empty($pipe_obj[0])){
        if ($pipe_obj[0]["own"] == "1"){
            $pipeline_name = $db->cleanName($pipe_obj[0]["name"], 30);
            $script_pipe_config = isset($pipe_obj[0]["script_pipe_config"]) ? $pipe_obj[0]["script_pipe_config"] : "";
            $description = htmlspecialchars_decode($pipe_obj[0]["summary"], ENT_QUOTES); 
            $configText = "";
            $configText = $db->getProcessParams($proVarObj, $configText);
            
            if (!empty($script_pipe_config)){
                $configText .= "\n// Pipeline Config:\n";
                $configText .= htmlspecialchars_decode($script_pipe_config , ENT_QUOTES); 
            }
            $nfData = urldecode($_REQUEST['nfData']); 
            $dnData = urldecode($_REQUEST['dnData']); 
            $initGitRepo = $db->initGitRepo($description, $pipeline_id, $pipeline_name, $username_id, $github_repo, $github_branch, $configText, $nfData, $dnData, $type, $ownerID);
            $data= json_encode($initGitRepo);
        }
    }
}
else if ($p=="saveGithub"){
    $username = $_REQUEST['username'];
    $email = $_REQUEST['email'];
    $passwordRaw = $_REQUEST['password'];
    $password = $db->amazonEncode($passwordRaw);
    if (!empty($id)) {
        $data = $db->updateGithub($id, $username, $email, $password, $ownerID);
    } else {
        $data = $db->insertGithub($username, $email, $password, $ownerID);
    }
}
else if ($p=="getGithub")
{
    if (!empty($id)) {
        $data = json_decode($db->getGithubbyID($id, $ownerID));
        foreach($data as $d){
            $password = $d->password;
            $d->password = trim($db->amazonDecode($password));
        }
        $data=json_encode($data);
    } else {
        $data = $db->getGithub($ownerID);
    }
}
else if ($p=="saveProfileCluster"){
    $name = $_REQUEST['name'];
    $public = isset($_REQUEST['public']) ? $_REQUEST['public'] : "";
    settype($public, 'integer');
    $executor = $_REQUEST['executor'];
    $cmd = $_REQUEST['cmd'];
    $next_memory = $_REQUEST['next_memory'];
    $next_queue = $_REQUEST['next_queue'];
    $next_time = $_REQUEST['next_time'];
    $next_cpu = $_REQUEST['next_cpu'];
    $next_clu_opt = $_REQUEST['next_clu_opt'];
    $executor_job = $_REQUEST['executor_job'];
    $job_memory = $_REQUEST['job_memory'];
    $job_queue = $_REQUEST['job_queue'];
    $job_time = $_REQUEST['job_time'];
    $job_cpu = $_REQUEST['job_cpu'];
    $job_clu_opt = $_REQUEST['job_clu_opt'];
    $username = $_REQUEST['username'];
    $hostname = $_REQUEST['hostname'];
    $next_path = $_REQUEST['next_path'];
    $port = $_REQUEST['port'];
    $singu_cache = $_REQUEST['singu_cache'];
    $variable =  addslashes(htmlspecialchars(urldecode($_REQUEST['variable']), ENT_QUOTES));
    $ssh_id = isset($_REQUEST['ssh_id']) ? $_REQUEST['ssh_id'] : "";
    settype($ssh_id, 'integer');
    if (!empty($id)) {
        $data = $db->updateProfileCluster($id, $name, $executor,$next_path, $port, $singu_cache, $username, $hostname, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $next_clu_opt, $job_clu_opt, $ssh_id, $public, $variable, $ownerID);
    } else {
        $data = $db->insertProfileCluster($name, $executor, $next_path, $port, $singu_cache, $username, $hostname, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $next_clu_opt, $job_clu_opt, $ssh_id, $public, $variable, $ownerID);
    }
}
else if ($p=="saveProfileAmazon"){
    $public = isset($_REQUEST['public']) ? $_REQUEST['public'] : "";
    settype($public, 'integer');
    $name = $_REQUEST['name'];
    $executor = $_REQUEST['executor'];
    $cmd = $_REQUEST['cmd'];
    $next_memory = $_REQUEST['next_memory'];
    $next_queue = $_REQUEST['next_queue'];
    $next_time = $_REQUEST['next_time'];
    $next_cpu = $_REQUEST['next_cpu'];
    $next_clu_opt = $_REQUEST['next_clu_opt'];
    $executor_job = $_REQUEST['executor_job'];
    $job_memory = $_REQUEST['job_memory'];
    $job_queue = $_REQUEST['job_queue'];
    $job_time = $_REQUEST['job_time'];
    $job_cpu = $_REQUEST['job_cpu'];
    $job_clu_opt = $_REQUEST['job_clu_opt'];
    $ins_type = $_REQUEST['instance_type'];
    $image_id = $_REQUEST['image_id'];
    $subnet_id = $_REQUEST['subnet_id'];
    $shared_storage_id = $_REQUEST['shared_storage_id'];
    $shared_storage_mnt = $_REQUEST['shared_storage_mnt'];
    $next_path = $_REQUEST['next_path'];
    $port = $_REQUEST['port'];
    $singu_cache = $_REQUEST['singu_cache'];
    $variable =  addslashes(htmlspecialchars(urldecode($_REQUEST['variable']), ENT_QUOTES));
    $ssh_id = isset($_REQUEST['ssh_id']) ? $_REQUEST['ssh_id'] : "";
    settype($ssh_id, 'integer');
    $amazon_cre_id = isset($_REQUEST['amazon_cre_id']) ? $_REQUEST['amazon_cre_id'] : "";
    $security_group = $_REQUEST['security_group'];
    settype($amazon_cre_id, 'integer');
    if (!empty($id)) {
        $data = $db->updateProfileAmazon($id, $name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $subnet_id, $shared_storage_id,$shared_storage_mnt, $ssh_id, $amazon_cre_id, $next_clu_opt, $job_clu_opt, $public, $security_group, $variable, $ownerID);
    } else {
        $data = $db->insertProfileAmazon($name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $subnet_id, $shared_storage_id,$shared_storage_mnt, $ssh_id, $amazon_cre_id, $next_clu_opt, $job_clu_opt, $public, $security_group, $variable, $ownerID);
    }
}
else if ($p=="saveInput"){
    $type = $_REQUEST['type'];
    $name = $_REQUEST['name'];
    if (!empty($id)) {
        $data = $db->updateInput($id, $name, $type, $ownerID);
    } else {
        $data = $db->insertInput($name, $type, $ownerID);
    }
}
else if ($p=="saveCollection"){
    $name = $_REQUEST['name'];
    $colData = $db->getCollectionByName($name, $ownerID);
    $colData = json_decode($colData,true);
    if (isset($colData[0])){
        $colId = $colData[0]["id"];
    } else {
        $colId = "";
    }
    if (!empty($id)) {
    } else {
        if (empty($colId)){
            $data = $db->insertCollection($name, $ownerID);
        } else {
            $data = json_encode(array('id' => $colId));
        }
    }
}
else if ($p=="insertFileCollection"){
    $collection_id = $_REQUEST['collection_id'];
    settype($collection_id, 'integer');
    $file_array = $_REQUEST['file_array'];
    foreach ($file_array as $file_id):
    settype($file_id, 'integer');
    $insertFileCollection = $db->insertFileCollection($file_id, $collection_id, $ownerID);
    $file_col_data = json_decode($insertFileCollection,true);
    $file_col_id = $file_col_data["id"];
    if (empty($file_col_id)) {
        break;
    }
    endforeach;
    $data = $insertFileCollection;
}
else if ($p=="insertFileProject"){
    $collection_id = $_REQUEST['collection_id'];
    $project_id = $_REQUEST['project_id'];
    settype($collection_id, 'integer');
    $file_arr = $db->getCollectionFiles($collection_id,$ownerID);
    $file_array = json_decode($file_arr,true);
    foreach ($file_array as $file_item):
    $file_id = $file_item["id"];
    settype($file_id, 'integer');
    //    check if project input is exist
    $checkPro = $db->checkFileProject($project_id, $file_id);
    $checkProData = json_decode($checkPro,true);
    if (isset($checkProData[0])){
        $projectFileID = $checkProData[0]["id"];
    } else {
        //insert into file project table
        $insertFileProject = $db->insertFileProject($file_id, $project_id, $ownerID);
        $insertProData = json_decode($insertFileProject,true);
        $projectFileID = $insertProData["id"];
    }
    endforeach;
    $data = $projectFileID;
}
else if ($p=="saveFile"){
    $collection_id = $_REQUEST['collection_id'];
    settype($collection_id, 'integer');
    $collection_type = $_REQUEST['collection_type'];
    $archive_dir = isset($_REQUEST['archive_dir']) ? $_REQUEST['archive_dir'] : "";
    $file_dir = isset($_REQUEST['file_dir']) ? $_REQUEST['file_dir'] : "";
    $s3_archive_dir = isset($_REQUEST['s3_archive_dir']) ? $_REQUEST['s3_archive_dir'] : "";
    $file_type = $_REQUEST['file_type'];
    $file_array = $_REQUEST['file_array'];
    $project_id = $_REQUEST['project_id'];
    $run_env = $_REQUEST['run_env'];
    $profileAr = explode("-", $run_env);
    $profileType = $profileAr[0];
    $profileId = $profileAr[1];
    if ($profileType == "amazon"){
        $run_env = "amazon";
    } else if ($profileType == "cluster"){
        if (!empty($profileId)) {
            $proData = $db->getProfileClusterbyID($profileId, $ownerID);
            $proDataAll = json_decode($proData,true);
            $username = $proDataAll[0]["username"];
            $hostname = $proDataAll[0]["hostname"];
            $run_env = $username."@".$hostname;
        }
    } 

    for ($i = 0; $i < count($file_array); $i++) {
        $item = $file_array[$i];
        $item_file_dir = $file_dir[$i];
        $p = explode(" ", $item);
        $name = $p[0];
        unset($p[0]);
        $files_used = join(' ', $p);
        $insert = $db->insertFile($name, $item_file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $run_env, $ownerID);
        $fileData = json_decode($insert,true);
        $file_id = $fileData["id"];
        settype($file_id, 'integer');
        if (empty($file_id)) {
            break;
        } else {
            $insertFileProject = $db->insertFileProject($file_id, $project_id, $ownerID);
            $insertFileCollection = $db->insertFileCollection($file_id, $collection_id, $ownerID);
            $file_col_data = json_decode($insertFileCollection,true);
            $file_col_id = $file_col_data["id"];
            if (empty($file_col_id)) {
                break;
            }
        }
    }
    $data = $insert;
}
else if ($p=="saveProPipeInput"){
    $input_id = $_REQUEST['input_id'];
    $project_id = $_REQUEST['project_id'];
    $pipeline_id = $_REQUEST['pipeline_id'];
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $g_num = $_REQUEST['g_num'];
    settype($g_num, 'integer');
    $given_name = $_REQUEST['given_name'];
    $qualifier = $_REQUEST['qualifier'];
    $collection_id = isset($_REQUEST['collection_id']) ? $_REQUEST['collection_id'] : "";
    settype($collection_id, 'integer');
    $url_id = 0;
    $urlzip_id = 0;
    $checkpath_id = 0;
    if (!empty($id)) {
        $data = $db->updateProPipeInput($id, $project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name,$qualifier, $collection_id, $url_id, $urlzip_id, $checkpath_id, $ownerID);
    } else {
        $data = $db->insertProPipeInput($project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name,$qualifier, $collection_id, $url_id, $urlzip_id, $checkpath_id, $ownerID);
    }
}
else if ($p=="fillInput"){
    $inputID = isset($_REQUEST['inputID']) ? $_REQUEST['inputID'] : "";
    $inputType = $_REQUEST['inputType'];
    $inputName = $_REQUEST['inputName'];
    $project_id = $_REQUEST['project_id'];
    $collection_id = isset($_REQUEST['collection_id']) ? $_REQUEST['collection_id'] : "";
    $pipeline_id = $_REQUEST['pipeline_id'];
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $g_num = $_REQUEST['g_num'];
    settype($g_num, 'integer');
    settype($collection_id, 'integer');
    $given_name = $_REQUEST['given_name'];
    $qualifier = $_REQUEST['qualifier'];
    $proPipeInputID = $_REQUEST['proPipeInputID'];
    $url = isset($_REQUEST['url']) ? $_REQUEST['url'] : "";
    $urlzip = isset($_REQUEST['urlzip']) ? $_REQUEST['urlzip'] : "";
    $checkpath= isset($_REQUEST['checkpath']) ? $_REQUEST['checkpath'] : "";
    $url_id = $db->checkInsertUrlInput($url, "url", $ownerID);
    $urlzip_id = $db->checkInsertUrlInput($urlzip, "url", $ownerID);
    $checkpath_id = $db->checkInsertUrlInput($checkpath, "url", $ownerID);
    settype($url_id, 'integer');
    settype($urlzip_id, 'integer');
    settype($checkpath_id, 'integer');
    if (empty($collection_id)){
        //check if input exist?
        if (empty($inputID)) {
            $checkIn = $db->checkInput($inputName,$inputType);
            $checkInData = json_decode($checkIn,true);
            if (isset($checkInData[0])){
                $input_id = $checkInData[0]["id"];
            } else {
                //insert into input table
                $insertIn = $db->insertInput($inputName, $inputType, $ownerID);
                $insertInData = json_decode($insertIn,true);
                $input_id = $insertInData["id"];
            }
        } else {
            $input_id = $inputID;
            //get inputdata from input table
            $indata = $db -> getInputs($input_id,$ownerID);
            $indata = json_decode($indata,true);
            if (isset($indata[0])){
                $inputName = $indata[0]["name"];
            } 
        }
        $input_id = (string)$input_id;
        //check if project input is exist
        $checkPro = $db->checkProjectInput($project_id, $input_id);
        $checkProData = json_decode($checkPro,true);
        if (isset($checkProData[0])){
            $projectInputID = $checkProData[0]["id"];
        } else {
            //insert into project_input table
            $insertPro = $db->insertProjectInput($project_id, $input_id, $ownerID);
            $insertProData = json_decode($insertPro,true);
            $projectInputID = $insertProData["id"];
        }
        $projectInputID = (string)$projectInputID;
        $data = json_encode($projectInputID);
    } else {
        settype($inputID, 'integer');
        $input_id = $inputID;
    }
    //insert into project_pipeline_input table
    if (!empty($proPipeInputID)){
        $data = $db->updateProPipeInput($proPipeInputID, $project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url_id, $urlzip_id, $checkpath_id, $ownerID);
        $projectPipelineInputID = $proPipeInputID;
    } else {
        $insertProPipe = $db->insertProPipeInput($project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url_id, $urlzip_id, $checkpath_id, $ownerID);
        $insertProPipeData = json_decode($insertProPipe,true);
        $projectPipelineInputID = $insertProPipeData["id"];
    }
    $projectPipelineInputID = (string)$projectPipelineInputID;
    $data = json_encode(array('projectPipelineInputID' => $projectPipelineInputID,'inputName' => $inputName ));
}
else if ($p=="saveProjectInput"){
    $input_id = $_REQUEST['input_id'];
    $project_id = $_REQUEST['project_id'];
    $data = $db->insertProjectInput($project_id, $input_id, $ownerID);
}
else if ($p=="savePipelineGroup"){
    $group_name = $_REQUEST['group_name'];
    $pipeGrData = $db->getPipelineGroupByName($group_name);
    $pipeGrData = json_decode($pipeGrData,true);
    if (isset($pipeGrData[0])){
        $pipeGrId = $pipeGrData[0]["id"];
    } else {
        $pipeGrId = "";
    }
    if (!empty($id)) {
        $data = $db->updatePipelineGroup($id, $group_name, $ownerID);
    } else {
        if (empty($pipeGrId)){
            $data = $db->insertPipelineGroup($group_name, $ownerID);
        } else {
            if ($userRole == "admin"){
                $db->updatePipelineGroup($pipeGrId, $group_name, $ownerID);
            }
            $data = json_encode(array('id' => $pipeGrId));
        }
    }
}
else if ($p=="saveProcessGroup"){
    $group_name = $_REQUEST['group_name'];
    $proGrData = $db->getProcessGroupByName($group_name);
    $proGrData = json_decode($proGrData,true);
    if (isset($proGrData[0])){
        $proGrId = $proGrData[0]["id"];
    } else {
        $proGrId = "";
    }
    if (!empty($id)) {
        $data = $db->updateProcessGroup($id, $group_name, $ownerID);
    } else {
        if (empty($proGrId)){
            $data = $db->insertProcessGroup($group_name, $ownerID);
        } else {
            if ($userRole == "admin"){
                $db->updateProcessGroup($proGrId, $group_name, $ownerID);
            }
            $data = json_encode(array('id' => $proGrId));
        }
    }
}
else if ($p=="saveProcess"){
    $name = $_REQUEST['name'];
    $process_gid = $_REQUEST['process_gid'];
    if (empty($process_gid)) {
        $max_gid = json_decode($db->getMaxProcess_gid(),true)[0]["process_gid"];
        settype($max_gid, "integer");
        if (!empty($max_gid) && $max_gid != 0) {
            $process_gid = $max_gid +1;
        } else {
            $process_gid = 1;
        }
    }
    $process_uuid = isset($_REQUEST['process_uuid']) ? $_REQUEST['process_uuid'] : "";
    $process_rev_uuid = isset($_REQUEST['process_rev_uuid']) ? $_REQUEST['process_rev_uuid'] : "";
    $process_uuid = "$process_uuid";
    $summary = addslashes(htmlspecialchars(urldecode($_REQUEST['summary']), ENT_QUOTES));
    $process_group_id = $_REQUEST['process_group_id'];
    $script = addslashes(htmlspecialchars(urldecode($_REQUEST['script']), ENT_QUOTES));
    $script_header = addslashes(htmlspecialchars(urldecode($_REQUEST['script_header']), ENT_QUOTES));
    $script_footer = addslashes(htmlspecialchars(urldecode($_REQUEST['script_footer']), ENT_QUOTES));
    $script_mode = $_REQUEST['script_mode'];
    $script_mode_header = $_REQUEST['script_mode_header'];
    $rev_id = isset($_REQUEST['rev_id']) ? $_REQUEST['rev_id'] : "";
    $rev_comment = isset($_REQUEST['rev_comment']) ? $_REQUEST['rev_comment'] : "";
    $group_id = $_REQUEST['group']; 
    $perms = $_REQUEST['perms']; 
    $publish = $_REQUEST['publish']; 
    settype($id, 'integer');
    settype($rev_id, 'integer');
    settype($group_id, 'integer');
    settype($process_gid, "integer");
    settype($perms, "integer");
    settype($publish, "integer");
    settype($process_group_id, "integer");
    if (!empty($id)) {
        $db->updateAllProcessGroupByGid($process_gid, $process_group_id,$ownerID);
        $db->updateAllProcessNameByGid($process_gid, $name,$ownerID);
        $data = $db->updateProcess($id, $name, $process_gid, $summary, $process_group_id, $script, $script_header, $script_footer, $group_id, $perms, $publish, $script_mode, $script_mode_header, $ownerID);
    } else {
        $data = $db->insertProcess($name, $process_gid, $summary, $process_group_id, $script, $script_header, $script_footer, $rev_id, $rev_comment, $group_id, $perms, $publish, $script_mode, $script_mode_header, $process_uuid, $process_rev_uuid, $ownerID);
        $idArray = json_decode($data,true);
        $new_pro_id = $idArray["id"];
        if (empty($id) && empty($process_uuid)) {
            $db->getUUIDAPI($data, "process", $new_pro_id);
        } else if (empty($id) && empty($process_rev_uuid)){
            $db->getUUIDAPI($data, "process_rev", $new_pro_id);
        }
    }
}
else if ($p=="callRmarkdown"){
    $uuid = $_REQUEST['uuid'];
    $dir = $_REQUEST['dir'];
    $type = $_REQUEST['type'];
    $filename = $_REQUEST['filename'];
    $text = urldecode($_REQUEST['text']);
    $data = $db->callRmarkdown($type, $uuid, $text, $dir, $filename);
}
else if ($p=="callDebrowser"){
    $uuid = $_REQUEST['uuid'];
    $dir = $_REQUEST['dir'];
    $filename = $_REQUEST['filename'];
    $data = $db->callDebrowser($uuid, $dir, $filename);
}
else if ($p=="moveFile"){
    $from = $_REQUEST['from'];
    $to = $_REQUEST['to'];
    $type = $_REQUEST['type'];
    $data = $db->moveFile($type, $from, $to, $ownerID);
}
else if ($p=="saveProject"){
    $name = addslashes(htmlspecialchars(urldecode($_REQUEST['name']), ENT_QUOTES));
    $summary = isset($_REQUEST['summary']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['summary']), ENT_QUOTES)) : "";
    if (!empty($id)) {
        $data = $db->updateProject($id, $name, $summary, $ownerID);
    } else {
        $data = $db->insertProject($name, $summary, $ownerID);
    }
}
else if ($p=="savePublicInput"){
    $name = $_REQUEST['name'];
    $type = $_REQUEST['type'];
    $host = $_REQUEST['host'];
    if (!empty($id)) {
        $data = $db->updatePublicInput($id, $name, $type, $host, $ownerID);
    } else {
        $data = $db->insertPublicInput($name, $type, $host, $ownerID);
    }
}
else if ($p=="saveGroup"){
    $name = $_REQUEST['name'];
    $data = $db->insertGroup($name, $ownerID);
    $idArray = json_decode($data,true);
    $g_id = $idArray["id"];
    $db->insertUserGroup($g_id, $ownerID, $ownerID);
}
else if ($p=="saveUserGroup"){
    $u_id = $_REQUEST['u_id'];
    $g_id = $_REQUEST['g_id'];
    $data = $db->insertUserGroup($g_id, $u_id, $ownerID);
}
else if ($p=="duplicateProjectPipelineInput"){
    $new_id = $_REQUEST['new_id'];
    $old_id = $_REQUEST['old_id'];
    $data = $db->duplicateProjectPipelineInput($new_id, $old_id, $ownerID);
}
else if ($p=="moveRun"){
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $new_project_id = $_REQUEST['new_project_id'];
    $old_project_id = $_REQUEST['old_project_id'];
    $data = $db->updateProPipe_ProjectID($project_pipeline_id, $new_project_id, $ownerID);
    $db->updateProPipeInput_ProjectID($project_pipeline_id, $new_project_id, $ownerID);
    //get project_pipeline_inputs belong to project_pipeline and add one by one
    $allinputs = json_decode($db->getProjectPipelineInputs($project_pipeline_id, $ownerID));
    foreach ($allinputs as $inputitem):
    $input_id = $inputitem->{'input_id'};
    $collection_id = $inputitem->{'collection_id'};
    $input_id = (string)$input_id;
    //insert into ProjectInput :
    if (!empty($input_id) && $input_id != "0" && $input_id != 0){
        //check if project input is exist
        $checkPro = $db->checkProjectInput($new_project_id, $input_id);
        $checkProData = json_decode($checkPro,true);
        //insert into project_input table
        if (!isset($checkProData[0])){
            $insertPro = $db->insertProjectInput($new_project_id, $input_id, $ownerID);
        } 
    }
    //insert into FileProject :
    if (!empty($collection_id)){
        settype($collection_id, 'integer');
        $file_arr = $db->getCollectionFiles($collection_id,$ownerID);
        $file_array = json_decode($file_arr,true);
        foreach ($file_array as $file_item):
        $file_id = $file_item["id"];
        settype($file_id, 'integer');
        // check if project input is exist
        $checkFilePro = $db->checkFileProject($new_project_id, $file_id);
        $checkFileProData = json_decode($checkFilePro,true);
        //insert into file project table
        if (!isset($checkFileProData[0])){
            $insertFileProject = $db->insertFileProject($file_id, $new_project_id, $ownerID);
        }
        endforeach;
    }
    endforeach;
}
else if ($p=="duplicateProcess"){
    $new_process_gid = $_REQUEST['process_gid'];
    $new_name = $_REQUEST['name'];
    $old_id = $_REQUEST['id'];
    $data = $db->duplicateProcess($new_process_gid, $new_name, $old_id, $ownerID);
    $idArray = json_decode($data,true);
    $new_pro_id = $idArray["id"];
    $db->duplicateProcessParameter($new_pro_id, $old_id, $ownerID);
    $db->getUUIDAPI($data, "process", $new_pro_id);

}
else if ($p=="createProcessRev"){
    $rev_comment = $_REQUEST['rev_comment'];
    $rev_id = $_REQUEST['rev_id'];
    $new_process_gid = $_REQUEST['process_gid'];
    $old_id = $_REQUEST['id'];
    $data = $db->createProcessRev($new_process_gid, $rev_comment, $rev_id, $old_id, $ownerID);
    $idArray = json_decode($data,true);
    $new_pro_id = $idArray["id"];
    $db->duplicateProcessParameter($new_pro_id, $old_id, $ownerID);
    $db->getUUIDAPI($data, "process_rev", $new_pro_id);
}
else if ($p=="saveProjectPipeline"){
    $pipeline_id = $_REQUEST['pipeline_id'];
    $project_id = $_REQUEST['project_id'];
    $name = isset($_REQUEST['name']) ?  addslashes(htmlspecialchars(urldecode($_REQUEST['name']), ENT_QUOTES)) : "";
    $summary = isset($_REQUEST['summary']) ?  addslashes(htmlspecialchars(urldecode($_REQUEST['summary']), ENT_QUOTES)) : "";
    $output_dir = isset($_REQUEST['output_dir']) ? $_REQUEST['output_dir'] : "";
    $publish_dir = isset($_REQUEST['publish_dir']) ? $_REQUEST['publish_dir'] : "";
    $publish_dir_check = isset($_REQUEST['publish_dir_check']) ? $_REQUEST['publish_dir_check'] : "";
    $perms = isset($_REQUEST['perms']) ? $_REQUEST['perms'] : "";
    $profile = isset($_REQUEST['profile']) ? $_REQUEST['profile'] : "";
    $interdel = isset($_REQUEST['interdel']) ? $_REQUEST['interdel'] : "";
    $cmd = isset($_REQUEST['cmd']) ? urldecode($_REQUEST['cmd']) : "";
    $group_id = isset($_REQUEST['group_id']) ? $_REQUEST['group_id'] : "";
    $exec_each = isset($_REQUEST['exec_each']) ? $_REQUEST['exec_each'] : "";
    $exec_all = isset($_REQUEST['exec_all']) ? $_REQUEST['exec_all'] : "";
    $exec_all_settings = isset($_REQUEST['exec_all_settings']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['exec_all_settings']), ENT_QUOTES)) : "";
    $exec_each_settings = isset($_REQUEST['exec_each_settings']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['exec_each_settings']), ENT_QUOTES)) : "";
    $exec_next_settings = isset($_REQUEST['exec_next_settings']) ? $_REQUEST['exec_next_settings'] : "";
    $docker_check = isset($_REQUEST['docker_check']) ? $_REQUEST['docker_check'] : "";
    $docker_img = isset($_REQUEST['docker_img']) ? $_REQUEST['docker_img'] : "";
    $docker_opt = isset($_REQUEST['docker_opt']) ? $_REQUEST['docker_opt'] : "";
    $singu_check = isset($_REQUEST['singu_check']) ? $_REQUEST['singu_check'] : "";
    $singu_save = isset($_REQUEST['singu_save']) ? $_REQUEST['singu_save'] : "";
    $singu_img = isset($_REQUEST['singu_img']) ? $_REQUEST['singu_img'] : "";
    $singu_opt = isset($_REQUEST['singu_opt']) ? $_REQUEST['singu_opt'] : "";
    $amazon_cre_id = isset($_REQUEST['amazon_cre_id']) ? $_REQUEST['amazon_cre_id'] : "";
    $withReport = isset($_REQUEST['withReport']) ? $_REQUEST['withReport'] : "";
    $withTrace = isset($_REQUEST['withTrace']) ? $_REQUEST['withTrace'] : "";
    $withTimeline = isset($_REQUEST['withTimeline']) ? $_REQUEST['withTimeline'] : "";
    $withDag = isset($_REQUEST['withDag']) ? $_REQUEST['withDag'] : "";
    $process_opt = isset($_REQUEST['process_opt']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['process_opt']), ENT_QUOTES)) : "";
    settype($group_id, 'integer');
    settype($amazon_cre_id, 'integer');
    if (!empty($id)) {
        $data = $db->updateProjectPipeline($id, $name, $summary, $output_dir, $perms, $profile, $interdel, $cmd, $group_id, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $ownerID);
        if ($perms !== "3"){
            $db->updateProjectGroupPerm($id, $group_id, $perms, $ownerID);
            $db->updateProjectInputGroupPerm($id, $group_id, $perms, $ownerID);
            $db->updateProjectPipelineInputGroupPerm($id, $group_id, $perms, $ownerID);
            $db->updatePipelineGroupPerm($id, $group_id, $perms, $ownerID);
            $db->updatePipelineProcessGroupPerm($id, $group_id, $perms, $ownerID);
        }
    } else {
        $data = $db->insertProjectPipeline($name, $project_id, $pipeline_id, $summary, $output_dir, $profile, $interdel, $cmd, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $ownerID);
    }
}
else if ($p=="saveProcessParameter"){
    $closure = isset($_REQUEST['closure']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['closure']), ENT_QUOTES)) : "";
    $reg_ex = isset($_REQUEST['reg_ex']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['reg_ex']), ENT_QUOTES)) : "";
    $operator = isset($_REQUEST['operator']) ? $_REQUEST['operator'] : "";
    $optional = isset($_REQUEST['optional']) ? $_REQUEST['optional'] : "";
    $sname = addslashes(htmlspecialchars(urldecode($_REQUEST['sname']), ENT_QUOTES));
    $process_id = $_REQUEST['process_id'];
    $parameter_id = $_REQUEST['parameter_id'];
    $type = $_REQUEST['type'];
    $perms = $_REQUEST['perms'];
    $group_id= $_REQUEST['group'];
    settype($id, 'integer');
    settype($group_id, 'integer');
    settype($perms, 'integer');
    settype($parameter_id, 'integer');
    settype($process_id, 'integer');
    if (!empty($id)) {
        $data = $db->updateProcessParameter($id, $sname, $process_id, $parameter_id, $type, $closure, $operator, $reg_ex, $optional, $perms, $group_id, $ownerID);
    } else {
        $data = $db->insertProcessParameter($sname, $process_id, $parameter_id, $type, $closure, $operator, $reg_ex, $optional, $perms, $group_id, $ownerID);
    }
}
else if ($p=="getProcessData")
{
    if (isset($_REQUEST['process_id'])) {
        $process_id = $_REQUEST['process_id'];
        $data = $db->getProcessDataById($process_id, $ownerID);
    }else {
        $data = $db->getProcessData($ownerID);
    }
}
else if ($p=="getProcessRevision")
{
    $id = $_REQUEST['process_id'];
    $process_gidAr =$db->getProcess_gid($id);
    $checkarray = json_decode($process_gidAr,true); 
    $process_gid = $checkarray[0]["process_gid"];
    $data = $db->getProcessRevision($process_gid,$ownerID);
}
else if ($p=="getPipelineRevision")
{
    $pipeline_id = $_REQUEST['pipeline_id'];
    $pipeline_gid = json_decode($db->getPipeline_gid($pipeline_id))[0]->{'pipeline_gid'};
    $data = $db->getPipelineRevision($pipeline_gid,$ownerID);
}
else if ($p=="getPublicPipelines")
{
    $data = $db->getPublicPipelines($ownerID);
}
else if ($p=="checkPipeline")
{
    $process_id = $_REQUEST['process_id'];
    $data = $db->checkPipeline($process_id, $ownerID);
}
else if ($p=="checkInput")
{
    $name = $_REQUEST['name'];
    $type = $_REQUEST['type'];
    $data = $db->checkInput($name,$type);
}
else if ($p=="checkProjectInput")
{
    $input_id = $_REQUEST['input_id'];
    $project_id = $_REQUEST['project_id'];
    $data = $db->checkProjectInput($project_id, $input_id);
}
else if ($p=="checkProPipeInput")
{
    $input_id = $_REQUEST['input_id'];
    $project_id = $_REQUEST['project_id'];
    $pipeline_id = $_REQUEST['pipeline_id'];
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $data = $db->checkProPipeInput($project_id, $input_id, $pipeline_id, $project_pipeline_id);
}
else if ($p=="checkPipelinePublic")
{
    $process_id = $_REQUEST['process_id'];
    $data = $db->checkPipelinePublic($process_id, $ownerID);
}
else if ($p=="checkProjectPipelinePublic")
{
    $process_id = $_REQUEST['process_id'];
    $data = $db->checkProjectPipelinePublic($process_id, $ownerID);
}
else if ($p=="checkPipelinePerm")
{
    $process_id = $_REQUEST['process_id'];
    $data = $db->checkPipelinePerm($process_id);
}
else if ($p=="checkProjectPipePerm")
{
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->checkProjectPipePerm($pipeline_id);
}
else if ($p=="checkProject")
{
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->checkProject($pipeline_id, $ownerID);
}
else if ($p=="checkProjectPublic")
{
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->checkProjectPublic($pipeline_id, $ownerID);
}
else if ($p=="checkParameter")
{
    $parameter_id = $_REQUEST['parameter_id'];
    $data = $db->checkParameter($parameter_id, $ownerID);
}
else if ($p=="checkMenuGr")
{
    $data = $db->checkMenuGr($id);
}
else if ($p=="checkPipeMenuGr")
{
    $data = $db->checkPipeMenuGr($id);
}
else if ($p=="getMaxProcess_gid")
{
    $data = $db->getMaxProcess_gid();
}
else if ($p=="getMaxPipeline_gid")
{
    $data = $db->getMaxPipeline_gid();
}
else if ($p=="getProcess_gid")
{
    $process_id = $_REQUEST['process_id'];
    $data = $db->getProcess_gid($process_id);
}
else if ($p=="getProcess_uuid")
{
    $process_id = $_REQUEST['process_id'];
    $data = $db->getProcess_uuid($process_id);
}
else if ($p=="check_uuid")
{
    $type = $_REQUEST['type'];
    $uuid = $_REQUEST['uuid'];
    $rev_uuid= $_REQUEST['rev_uuid'];
    $data_uuid = $db->getLastProPipeByUUID($uuid, $type, $ownerID);
    $data_rev_uuid = $db->getProPipeDataByUUID($uuid, $rev_uuid, $type, $ownerID);
    $obj1 = json_decode($data_uuid,true);
    $obj2 = json_decode($data_rev_uuid,true);
    if ($type == "process"){
        $data["process_uuid"] = isset($obj1[0]) ? $obj1[0] : null;
        $data["process_rev_uuid"] = isset($obj2[0]) ? $obj2[0] : null;
        if (isset($obj2[0])){
            $process_id = $obj2[0]["id"];
            $pro_para_in = $db->getInputsPP($process_id);
            $pro_para_out = $db->getOutputsPP($process_id);
            $data["pro_para_inputs_$process_id"]=$pro_para_in;
            $data["pro_para_outputs_$process_id"]=$pro_para_out;
        }
        $data= json_encode($data);
    } else if ($type == "pipeline"){
        $data["pipeline_uuid"] = isset($obj1[0]) ? $obj1[0] : null;
        $data["pipeline_rev_uuid"] = isset($obj2[0]) ? $obj2[0] : null;
        $data= json_encode($data);
    }

}
else if ($p=="getPipeline_gid")
{
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->getPipeline_gid($pipeline_id);
}
else if ($p=="getPipeline_uuid")
{
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->getPipeline_uuid($pipeline_id);
}
else if ($p=="getMaxRev_id")
{
    $process_gid = $_REQUEST['process_gid'];
    $data = $db->getMaxRev_id($process_gid);
}
else if ($p=="getMaxPipRev_id")
{
    $pipeline_gid = $_REQUEST['pipeline_gid'];
    $data = $db->getMaxPipRev_id($pipeline_gid);
}
else if ($p=="getInputsPP")
{
    $process_id = $_REQUEST['process_id'];
    $data = $db->getInputsPP($process_id);
}
else if ($p=="getOutputsPP")
{
    $process_id = $_REQUEST['process_id'];
    $data = $db->getOutputsPP($process_id);
}
else if ($p=="saveAllPipeline")
{
    $dat = $_REQUEST['dat'];
    $data = $db->saveAllPipeline($dat,$ownerID);
    $idArray = json_decode($data,true);
    $new_pipe_id = $idArray["id"];
    if (!empty($new_pipe_id)){
        $obj = json_decode($dat);
        $newObj = new stdClass();
        foreach ($obj as $item):
        foreach($item as $k => $v) $newObj->$k = $v;
        endforeach;
        $pipeline_uuid = isset($newObj->{"pipeline_uuid"}) ? $newObj->{"pipeline_uuid"} : "";
        $pipeline_rev_uuid = isset($newObj->{"pipeline_rev_uuid"}) ? $newObj->{"pipeline_rev_uuid"} : "";
        if (empty($pipeline_uuid)) {
            $db->getUUIDAPI($data,"pipeline", $new_pipe_id);
        } else if (empty($pipeline_rev_uuid)){
            $db->getUUIDAPI($data,"pipeline_rev", $new_pipe_id);
        }
    }
}
else if ($p=="savePipelineDetails")
{
    $summary = addslashes(htmlspecialchars(urldecode($_REQUEST['summary']), ENT_QUOTES));
    $group_id = $_REQUEST['group_id'];
    $nodesRaw = $_REQUEST['nodes'];
    $perms = $_REQUEST['perms'];
    $pin = $_REQUEST['pin'];
    $pin_order = $_REQUEST['pin_order'];
    $publish = $_REQUEST['publish'];
    $pipeline_group_id = $_REQUEST['pipeline_group_id'];
    settype($group_id, 'integer');
    settype($pin_order, "integer");
    $data = $db->savePipelineDetails($id,$summary,$group_id,$perms,$pin,$pin_order, $publish,$pipeline_group_id,$ownerID);
    //update permissions
    if (!empty($nodesRaw)){
        $db->updatePipelinePerms($nodesRaw, $group_id, $perms, $ownerID);
    }
}
else if ($p=="getSavedPipelines") {
    $data = $db->getSavedPipelines($ownerID);
}
else if ($p=="getPipelineSideBar") {
    $data = $db->getPipelineSideBar($ownerID);
}
else if ($p=="exportPipeline"){
    $data = $db->exportPipeline($id,$ownerID, "main", 0);
}
else if ($p=="loadPipeline"){
    $id = $_REQUEST['id'];
    $data = $db->loadPipeline($id,$ownerID);
    //load process parameters 
    $new_obj = json_decode($data,true);
    if (!empty($new_obj[0]["nodes"])){
        $nodes = json_decode($new_obj[0]["nodes"]);
        foreach ($nodes as $item):
        if ($item[2] !== "inPro" && $item[2] !== "outPro"){
            $process_id = $item[2];
            $pro_para_in = $db->getInputsPP($process_id);
            $pro_para_out = $db->getOutputsPP($process_id);
            $new_obj[0]["pro_para_inputs_$process_id"]=$pro_para_in;
            $new_obj[0]["pro_para_outputs_$process_id"]=$pro_para_out;
        }
        endforeach;
        $data= json_encode($new_obj);
    }
}

if (!headers_sent()) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo $data;
    exit;
}else{
    echo $data;
}
