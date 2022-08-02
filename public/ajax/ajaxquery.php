<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors', 'on');

require_once("../ajax/dbfuncs.php");
$db = new dbfuncs();

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
$accessToken = isset($_SESSION['accessToken']) ? $_SESSION['accessToken'] : "";
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
$email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : "";
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : "";
if (!empty($username)) {
    $usernameCl = str_replace(".", "__", $username);
}
session_write_close();
$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
$p = isset($_REQUEST["p"]) ? $_REQUEST["p"] : "";

if (isset($_REQUEST['id'])) {
    $id = $_REQUEST['id'];
}

if ($p == "saveRun") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $nextText = urldecode($_REQUEST['nextText']);
    $proVarObj = urldecode($_REQUEST['proVarObj']);

    $runType = $_REQUEST['runType']; //"resumerun" or "newrun"
    $manualRun = isset($_REQUEST['manualRun']) ? $_REQUEST['manualRun'] : ""; //"true" or "false"
    $uuid = $_REQUEST['uuid'];
    $data = $db->saveRun($project_pipeline_id, $nextText, $runType, $manualRun, $uuid, $proVarObj, $ownerID);
} else if ($p == "updateRunAttemptLog") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $manualRun = isset($_REQUEST['manualRun']) ? $_REQUEST['manualRun'] : "";
    //add run into run table and increase the run attempt. $status = "init";
    $uuid = $db->updateRunAttemptLog($manualRun, $project_pipeline_id, $ownerID);
    $data = json_encode($uuid);
} else if ($p == "updateProPipeStatus") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $loadtype = "fast";
    $process_id = "";
    $data = $db->updateProPipeStatus($project_pipeline_id, $process_id, $loadtype, $ownerID);
} else if ($p == "updateProcessStatus") {
    $process_id = $_REQUEST['process_id'];
    $loadtype = "fast";
    $project_pipeline_id = "";
    $data = $db->updateProPipeStatus($project_pipeline_id, $process_id, $loadtype, $ownerID);
} else if ($p == "saveRunLogSize") {
    $uuid = isset($_REQUEST['uuid']) ? $_REQUEST['uuid'] : "";
    $project_pipeline_id = isset($_REQUEST['project_pipeline_id']) ? $_REQUEST['project_pipeline_id'] : "";
    $data = $db->saveRunLogSize($uuid, $project_pipeline_id, $ownerID);
} else if ($p == "saveRunLogName") {
    $name =  addslashes(htmlspecialchars(urldecode($_REQUEST['name']), ENT_QUOTES));
    $data = $db->updateRunLogName($id, $name, $ownerID);
} else if ($p == "saveRunLogSizeAllUsers") {
    $userRole = $db->getUserRoleVal($ownerID);
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
    $data = json_encode("");
    if ($userRole == "admin") {
        $sql = "SELECT id, IF(disk_usage IS NULL,0,1) as disk_usage_check FROM $db->db.users WHERE deleted=0";
        $usersRaw = $db->queryTable($sql);
        $users = json_decode($usersRaw);
        foreach ($users as $user) :
            $userID = $user->{'id'};
            $disk_usage_check = $user->{'disk_usage_check'};
            if ($type == "all") {
                $db->saveRunLogSizeUser($userID, $ownerID);
            } else if (empty($disk_usage_check)) {
                $db->saveRunLogSizeUser($userID, $ownerID);
            }
        endforeach;
    }
} else if ($p == "saveRunLogSizeUser") {
    $userRole = $db->getUserRoleVal($ownerID);
    $data = json_encode("");
    if ($userRole == "admin") {
        $userID = $_REQUEST['userid'];
        $db->saveRunLogSizeUser($userID, $ownerID);
    }
} else if ($p == "getFileContent") {
    $filename = $_REQUEST['filename'];
    if (isset($_REQUEST['project_pipeline_id'])) {
        $project_pipeline_id = $_REQUEST['project_pipeline_id'];
        $uuid = $db->getProPipeLastRunUUID($project_pipeline_id);
        //fix for old runs 
        if (empty($uuid)) {
            $uuid = "run" . $project_pipeline_id;
            $filename = preg_replace('/^run/', '', $filename);
        }
    } else if (isset($_REQUEST['uuid'])) {
        $uuid = $_REQUEST['uuid'];
    }
    $data = $db->getFileContent($uuid, $filename, $ownerID);
} else if ($p == "saveFileContent") {
    $textRaw = $_REQUEST['text'];
    $text = urldecode($textRaw);
    $filename = $_REQUEST['filename'];
    $uuid = $_REQUEST['uuid'];
    $data = $db->saveFileContent($text, $uuid, $filename, $ownerID);
} else if ($p == "deleteFile") {
    $filename = $_REQUEST['filename'];
    $uuid = $_REQUEST['uuid'];
    $data = $db->deleteFile($uuid, $filename, $ownerID);
} else if ($p == "getFileList") {
    $uuid  = $_REQUEST['uuid'];
    $path = $_REQUEST['path'];
    $type = $_REQUEST['type'];
    $data = $db->getFileList($uuid, $path, $type);
} else if ($p == "getRsyncStatus") {
    $filename  = $_REQUEST['filename'];
    $data = $db->getRsyncStatus($filename, $ownerID);
} else if ($p == "resetUpload") {
    $filename  = $_REQUEST['filename'];
    $data = $db->resetUpload($filename, $ownerID);
} else if ($p == "retryRsync") {
    $fileName  = $_REQUEST['filename'];
    $target_dir = $_REQUEST['dir'];
    $run_env = $_REQUEST['run_env'];
    $data = $db->retryRsync($fileName, $target_dir, $run_env, $email, $ownerID);
} else if ($p == "getRemoteData") {
    $url  = $_REQUEST['url'];
    $data = array();
    if (!empty($ownerID)) {
        $data = $db->getRemoteData($url);
    }
    $data = json_encode($data);
} else if ($p == "getHostFile") {
    $data = array();

    if (!empty($ownerID)) {
        $pro_pipe_input_id  = $_REQUEST['pro_pipe_input_id'];
        $path  = $_REQUEST['path'];
        if (!empty($pro_pipe_input_id)) {
            $userRole = $db->getUserRoleVal($ownerID);
            $where = " WHERE p.id=$pro_pipe_input_id AND (p.owner_id='$ownerID' OR p.perms = 63 OR (ug.u_id ='$ownerID' and p.perms = 15)) ";
            if ($userRole == "admin") {
                $where = "WHERE  p.id=$pro_pipe_input_id";
            }

            $sql = "SELECT p.owner_id, p.project_pipeline_id, p.uid, pp.profile
                FROM $db->db.project_pipeline_input p
                LEFT JOIN $db->db.project_pipeline pp ON  p.project_pipeline_id=pp.id
                LEFT JOIN $db->db.user_group ug ON  pp.group_id=ug.g_id
                $where ";
            $inputData = json_decode($db->queryTable($sql), true);
            if (!empty($inputData) && !empty($inputData[0]) && !empty($inputData[0]["owner_id"])) {
                $profile  = $inputData[0]["profile"];
                $uid  = $inputData[0]["uid"];
                error_log($pro_pipe_input_id);
                error_log($uid);

                if (empty($uid)) {
                    $res = $db->getUUIDLocal("run_log");
                    $uid = $res->rev_uuid;
                    $db->updateUIDproPipeInput($pro_pipe_input_id, $uid, $ownerID);
                }

                if (!empty($uid)) {
                    $data = $db->getHostFile($path, $uid, $profile, $ownerID);
                }
            }
        }
    }
    $data = json_encode($data);
} else if ($p == "getUcscSessionID") {
    $hubFileLoc  = $_REQUEST['hubFileLoc'];
    $genomeFileLoc  = $_REQUEST['genomeFileLoc'];
    $run_log_uuid = $_REQUEST['run_log_uuid'];
    $dir = $_REQUEST['dir'];
    $data = $db->getUcscSessionID($hubFileLoc, $genomeFileLoc, $run_log_uuid, $dir, $ownerID);
} else if ($p == "getReportData") {
    $uuid  = $_REQUEST['uuid'];
    $path = $_REQUEST['path']; //pubweb, run
    $pipeline_id = $_REQUEST['pipeline_id'];
    $pipe = $db->loadPipeline($pipeline_id, $ownerID);
    $pipeData = json_decode($pipe, true);
    $data = array();
    if (!empty($pipeData[0]["nodes"])) {
        $nodes = json_decode($pipeData[0]["nodes"]);
        foreach ($nodes as $gNum => $item) :
            $out = array();
            if ($item[2] == "outPro") {
                $push = false;
                $name = $item[3];
                $processOpt = $item[4];
                $out["id"] = $gNum;
                $out["name"] = $name; //directory name which has the report files
                foreach ($processOpt as $key => $feature) :
                    if ($key == "pubWeb") {
                        $push = true;
                        $pubWebAr = explode(",", $feature);
                    } else if ($key == "pubWebApp") {
                        $out[$key] = $feature;
                    }
                    $out[$key] = $feature;
                endforeach;
                if ($push == true) {
                    $fileList = array_values((array)json_decode($db->getFileList($uuid, "$path/$name", "onlyfile")));
                    //If no callback is supplied to array_filter, all empty entries of array will be removed
                    $fileList = array_filter($fileList);

                    if (!empty($fileList)) {
                        $out["fileList"] = $fileList;
                        //split each view method into new array
                        $savedID = $out["id"];

                        foreach ($pubWebAr as $eachPubWeb) :
                            $out["pubWeb"] = $eachPubWeb;
                            if ($eachPubWeb == "debrowser" && preg_grep("/^debrowser_metadata/i", $fileList)) {
                                // show metadata file at last
                                $debrowser_metadata_indexes = array_keys(preg_grep("/^debrowser_metadata/i", $fileList));
                                foreach ($debrowser_metadata_indexes as $debrowser_metadata_index) :
                                    $moveMeta = $out["fileList"][$debrowser_metadata_index];
                                    array_splice($out["fileList"], $debrowser_metadata_index, 1);
                                    $out["fileList"][] =  $moveMeta;
                                endforeach;
                            }
                            $out["id"] = $savedID . "_" . $eachPubWeb;
                            if (strtolower($name) == "summary"  || strtolower($name) == "multiqc" || strtolower($name) == "fastqc" || strtolower($name) == "report") {
                                array_unshift($data, $out); //push to the top of the array
                            } else {
                                $data[] = $out; //push $out object into array
                            }
                        endforeach;
                    }
                }
            }
        endforeach;
        // sort the reports by given $order
        $order = array("multiqc", "fastqc", "summary");
        $ordered = array();
        foreach ($order as $key) {
            foreach ($data as $k => $content) {
                if (strtolower($content["name"]) == $key) {
                    $ordered[] = $content;
                    unset($data[$k]);
                    break;
                }
            }
        }
        // add the rest of the content
        foreach ($data as $k => $content) {
            $ordered[] = $content;
        }
        $data = $db->checkDescriptionBox($ordered, $uuid, $path);
    }
    $data = json_encode($data);
} else if ($p == "savePubWeb") {
    $data = "";
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $profileType = $_REQUEST['profileType'];
    $profileId = $_REQUEST['profileId'];
    $pipeline_id = $_REQUEST['pipeline_id'];
    if (!empty($ownerID)) {
        $data = $db->savePubWeb($project_pipeline_id, $profileType, $profileId, $pipeline_id, $ownerID, $accessToken);
    }
} else if ($p == "saveNextflowLog") {
    $data = json_encode("");
    $profileType = $_REQUEST['profileType'];
    $profileId = isset($_REQUEST['profileId']) ? $_REQUEST['profileId'] : "";
    $process_id = isset($_REQUEST['process_id']) ? $_REQUEST['process_id'] : "";
    $project_pipeline_id = isset($_REQUEST['project_pipeline_id']) ? $_REQUEST['project_pipeline_id'] : "";
    if (!empty($project_pipeline_id)) {
        $uuid = $db->getProPipeLastRunUUID($project_pipeline_id);
        if (!empty($uuid) && !empty($ownerID)) {
            // get outputdir
            $proPipeAll = json_decode($db->getProjectPipelines($project_pipeline_id, "", $ownerID, ""));
            list($dolphin_path_real, $dolphin_publish_real) = $db->getDolphinPathReal($proPipeAll);
        }
    } else if (!empty($process_id)) {
        $process_data = json_decode($db->getProcessDataById($process_id, $ownerID), true);
        if (!empty($process_data[0])) {
            $uuid = $process_data[0]["run_uuid"];
            $output_dir = $process_data[0]["test_work_dir"];
            $dolphin_path_real = "$output_dir/run{$project_pipeline_id}";
        }
    }


    if (!empty($uuid) && !empty($ownerID) && !empty($dolphin_path_real)) {
        $down_file_list = array("log.txt", ".nextflow.log", "report.html", "timeline.html", "trace.txt", "dag.html", "err.log", "initialrun/initial.log", "initialrun/.nextflow.log", "initialrun/trace.txt");
        foreach ($down_file_list as &$value) {
            $value = $dolphin_path_real . "/" . $value;
        }
        unset($value);
        $data = $db->saveNextflowLog($down_file_list, $uuid, "run", $profileType, $profileId, $project_pipeline_id, $dolphin_path_real, $ownerID);
    }
} else if ($p == "getDiskSpace") {
    $dir = $_REQUEST['dir'];
    $profileType = $_REQUEST['profileType'];
    $profileId = $_REQUEST['profileId'];
    $data = $db->getDiskSpace($dir, $profileType, $profileId, $ownerID);
} else if ($p == "getLsDir") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $dir = $_REQUEST['dir'];
    $profileType = $_REQUEST['profileType'];
    $profileId = $_REQUEST['profileId'];
    $amazon_cre_id = isset($_REQUEST['amazon_cre_id']) ? $_REQUEST['amazon_cre_id'] : "";
    $google_cre_id = isset($_REQUEST['google_cre_id']) ? $_REQUEST['google_cre_id'] : "";
    $data = $db->getLsDir($dir, $profileType, $profileId, $amazon_cre_id, $google_cre_id, $project_pipeline_id, $ownerID);
} else if ($p == "chkRmDirWritable") {
    $dir = $_REQUEST['dir'];
    $run_env = $_REQUEST['run_env'];
    $profileAr = explode("-", $run_env);
    $profileType = $profileAr[0];
    $profileId = $profileAr[1];
    $data = $db->chkRmDirWritable($dir, $profileType, $profileId, $ownerID);
} else if ($p == "getGeoData") {
    $geo_id = $_REQUEST['geo_id'];
    $data = $db->getGeoData($geo_id, $ownerID);
} else if ($p == "getRun") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $data = $db->getRun($project_pipeline_id, $ownerID);
} else if ($p == "terminateRun") {
    $commandType = "terminateRun";
    $process_id = isset($_REQUEST['process_id']) ? $_REQUEST['process_id'] : "";
    $project_pipeline_id = isset($_REQUEST['project_pipeline_id']) ? $_REQUEST['project_pipeline_id'] : "";
    $profileType = $_REQUEST['profileType'];
    $profileId = $_REQUEST['profileId'];
    $executor = $_REQUEST['executor'];

    if ($executor != 'local') {
        if (!empty($project_pipeline_id)) {
            $pid = json_decode($db->getRunPid($project_pipeline_id))[0]->{'pid'};
        } else if (!empty($process_id)) {
            $pid = json_decode($db->getProcessRunPid($process_id))[0]->{'run_pid'};
        }
        if (!empty($pid)) {
            $data = $db->sshExeCommand($commandType, $pid, $profileType, $profileId, $project_pipeline_id, $process_id, $ownerID);
        } else {
            $data = json_encode("pidNotExist");
        }
    } else if ($executor == 'local') {
        $data = $db->sshExeCommand($commandType, "", $profileType, $profileId, $project_pipeline_id, $process_id, $ownerID);
    }
} else if ($p == "updateProcessRunStatus") {
    $process_id = $_REQUEST['process_id'];
    $run_status = $_REQUEST['run_status'];
    if (!empty($ownerID)) {
        $data = $db->updateProcessRunStatus($process_id, $run_status, $ownerID);
    }
} else if ($p == "updateRunStatus") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $run_status = $_REQUEST['run_status'];
    $duration = isset($_REQUEST['duration']) ? $_REQUEST['duration'] : "";
    if (!empty($ownerID)) {
        $db->updateRunLog($project_pipeline_id, $run_status, $duration, $ownerID);
        $data = $db->updateRunStatus($project_pipeline_id, $run_status, $ownerID);
        // cloud check triggerShutdown
        $runDataJS = $db->getLastRunData($project_pipeline_id);
        if (!empty(json_decode($runDataJS, true))) {
            $runData = json_decode($runDataJS, true)[0];
            $profile = $runData["profile"];
            if (!empty($profile)) {
                $profileAr = explode("-", $profile);
                $profileType = $profileAr[0];
                $profileId = $profileAr[1];
                if (($profileType == "amazon" || $profileType == "google") && ($run_status == "Terminated")) {
                    error_log("triggerShutdown fast2");
                    $db->triggerShutdown($profileId, $profileType, $ownerID, "fast");
                }
            }
        }
    }
} else if ($p == "getRunStatus") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $data = $db->getRunStatus($project_pipeline_id, $ownerID);
} else if ($p == "startProCloud") {
    $nodes = $_REQUEST['nodes'];
    $cloud = $_REQUEST['cloud'];
    $autoscale_check = $_REQUEST['autoscale_check'];
    $autoscale_maxIns = isset($_REQUEST['autoscale_maxIns']) ? $_REQUEST['autoscale_maxIns'] : "";
    $autoscale_minIns = isset($_REQUEST['autoscale_minIns']) ? $_REQUEST['autoscale_minIns'] : "";
    $autoshutdown_check = $_REQUEST['autoshutdown_check'];
    //reset on startup
    $autoshutdown_active = "";
    $autoshutdown_date = NULL;
    $db->updateProfileCloudOnStart($id, $nodes, $autoscale_check, $autoscale_maxIns, $autoscale_minIns, $autoshutdown_date, $autoshutdown_active, $autoshutdown_check, $cloud, $ownerID);
    $data = $db->startProCloud($id, $cloud, $ownerID, $usernameCl);
} else if ($p == "stopProCloud") {
    $cloud = $_REQUEST['cloud'];
    $data = $db->stopProCloud($id, $ownerID, $usernameCl, $cloud);
} else if ($p == "checkCloudStatus") {
    $profileId = $_REQUEST['profileId'];
    $cloud = $_REQUEST['cloud'];
    $data = $db->checkCloudStatus($profileId, $ownerID, $usernameCl, $cloud);
} else if ($p == "runCloudCheck") {
    $profileId = $_REQUEST['profileId'];
    $cloud = $_REQUEST['cloud'];
    $data = $db->runCloudCheck($profileId, $cloud, $ownerID, $usernameCl);
} else if ($p == "getAllParameters") {
    $data = $db->getAllParameters($ownerID);
} else if ($p == "getEditDelParameters") {
    $data = $db->getEditDelParameters($ownerID);
} else if ($p == "savefeedback") {
    $email = "";
    $message = $_REQUEST['message'];
    $url = $_REQUEST['url'];
    $userData = json_decode($db->getUserById($ownerID));
    if (!empty($userData)) {
        if (!empty($userData[0])) {
            $email = $userData[0]->{'email'};
            $name = $userData[0]->{'name'};
            $username = $userData[0]->{'username'};
            $institute = $userData[0]->{'institute'};
            $lab = $userData[0]->{'lab'};
            $from = EMAIL_SENDER;
            $from_name = "DolphinNext Team";
            $to =  EMAIL_ADMIN;
            $subject = "User Message";
            $send = "New message has been received by the user.<br><br><b>User Information:</b>";
            $send .= "<br>Name: " . $name;
            $send .= "<br>Username: " . $username;
            $send .= "<br>Institute: " . $institute;
            $send .= "<br>Lab: " . $lab;
            $send .= "<br>Email: " . $email;
            $send .= "<br>Page: " . $url;
            $send .= "<br><br>Message:<br> " . $message;
            $stat = $db->sendEmail($from, $from_name, $to, $subject, $send);
        }
    }
    $data = $db->savefeedback($email, $message, $url, $ownerID);
} else if ($p == "getUpload") {
    $name = $_REQUEST['name'];
    $data = $db->getUpload($name, $ownerID);
} else if ($p == "removeUpload") {
    $name = $_REQUEST['name'];
    $data = $db->removeUpload($name, $ownerID);
} else if ($p == "addRunNotes") {
    $run_log_uuid = $_REQUEST['run_log_uuid'];
    $data = $db->addRunNotes($run_log_uuid, $ownerID);
} else if ($p == "getAllGroups") {
    $data = $db->getAllGroups($ownerID);
} else if ($p == "getAllAvailableGroups") {
    $user_id = $_REQUEST['user_id'];
    $data = $db->getAllAvailableGroups($user_id, $ownerID);
} else if ($p == "viewGroupMembers") {
    $g_id = $_REQUEST['g_id'];
    $data = $db->viewGroupMembers($g_id);
} else if ($p == "saveGroupMemberByEmail") {
    $email = $_REQUEST['email'];
    $g_id = $_REQUEST['g_id'];
    $data = $db->saveGroupMemberByEmail($email, $g_id, $ownerID);
} else if ($p == "getProjects") {
    $type = "default";
    $data = $db->getProjects($id, $type, $ownerID);
} else if ($p == "getUserProjects") {
    $type = "user";
    $data = $db->getProjects($id, $type, $ownerID);
} else if ($p == "getSharedProjects") {
    $type = "shared";
    $data = $db->getProjects($id, $type, $ownerID);
} else if ($p == "getContainers") {
    $type = "default";
    $data = $db->getContainers($id, $type, $ownerID);
} else if ($p == "getUserContainers") {
    $type = "user";
    $data = $db->getContainers($id, $type, $ownerID);
} else if ($p == "getSharedContainers") {
    $type = "shared";
    $data = $db->getContainers($id, $type, $ownerID);
} else if ($p == "getGroups") {
    $data = $db->getGroups($id, $ownerID);
} else if ($p == "getAllUsers") {
    $data = $db->getAllUsers($ownerID);
} else if ($p == "getCurrentUser") {
    $data = $db->getUserById($ownerID);
} else if ($p == "getUserById") {
    $data = $db->getUserById($id);
} else if ($p == "changeActiveUser") {
    $user_id = $_REQUEST['user_id'];
    $type = $_REQUEST['type'];
    $data = $db->changeActiveUser($user_id, $type);
    if ($type == "activateSendUser") {
        $userData = json_decode($db->getUserById($user_id))[0];
        if (!empty($userData)) {
            $email = $userData->{'email'};
            $name = $userData->{'name'};
            $logintype = $userData->{'logintype'};
            if ($email != "") {
                $subject = "DolphinNext Account Activation";
                $loginText = "You can start browsing at " . BASE_PATH;
                if ($logintype == "google") {
                    $loginText = "Please use <b>Sign-In with Google</b> button to enter your account at " . BASE_PATH;
                } else if ($logintype == "ldap") {
                    $loginText = "Please use your e-mail address($email) and e-mail password to enter your account at " . BASE_PATH;
                } else if ($logintype == "password") {
                    $password_val = $db->randomPassword();
                    $pass_hash = hash('md5', $password_val . SALT) . hash('sha256', $password_val . PEPPER);
                    $db->updateUserPassword($user_id, $pass_hash, $ownerID);
                    $loginText = "Please use following e-mail address and password to enter your account at " . BASE_PATH;
                    $loginText .= "<br><br>E-mail: $email<br>";
                    $loginText .= "Password: $password_val";
                }
                $from = EMAIL_SENDER;
                $from_name = "DolphinNext Team";
                $to  = $email;
                $message = "Dear $name,<br><br>Your DolphinNext account is now active!<br>$loginText<br><br>Best Regards,<br><br>" . COMPANY_NAME . " DolphinNext Team";
                $db->sendEmail($from, $from_name, $to, $subject, $message);
            }
        }
    }
} else if ($p == "changeRoleUser") {
    $user_id = $_REQUEST['user_id'];
    $type = $_REQUEST['type'];
    $data = $db->changeRoleUser($user_id, $type, $ownerID);
} else if ($p == "changePassword") {
    $error = array();
    $password0 = $_REQUEST['password0'];
    $password1 = $_REQUEST['password1'];
    $password2 = $_REQUEST['password2'];
    $pass_hash0 = hash('md5', $password0 . SALT) . hash('sha256', $password0 . PEPPER);
    $pass_hash1 = "";
    if ($password1 == $password2 && !empty($password1)) {
        $pass_hash1 = hash('md5', $password1 . SALT) . hash('sha256', $password1 . PEPPER);
    } else {
        $error['password1'] = "New password is not match";
    }
    $pass_hash0DB = $db->queryAVal("SELECT pass_hash FROM $db->db.users WHERE id = '$ownerID'");
    if ($pass_hash0DB !=  $pass_hash0 && !empty($pass_hash0DB)) {
        $error['password0'] = "Old password is not correct.";
    } else if (!empty($pass_hash1)) {
        $data =  $db->updateUserPassword($ownerID, $pass_hash1, $ownerID);
    }
    if (!empty($error)) {
        $data  = json_encode($error);
    }
} else if ($p == "saveUserManual") {
    $name = str_replace("'", "", $_REQUEST['name']);
    $email = $_REQUEST['email'];
    $username = str_replace("'", "", $_REQUEST['username']);
    $institute = str_replace("'", "", $_REQUEST['institute']);
    $lab = str_replace("'", "", $_REQUEST['lab']);
    $logintype = $_REQUEST['logintype'];
    $error = $db->checkExistUser($id, $username, $email);
    if (!empty($error)) {
        $data = json_encode($error);
    } else {
        if (!empty($id)) {
            $data = $db->updateUserManual($id, $name, $email, $username, $institute, $lab, $logintype, $ownerID);
        } else {
            $any_user_check = $db->queryAVal("SELECT id FROM $db->db.users");
            $any_user_checkAr = json_decode($any_user_check, true);
            if (empty($any_user_checkAr)) {
                $role = "admin";
            } else {
                $role = "user";
            }
            $active = 1;
            $pass_hash = NULL;
            $verify = NULL;
            $google_id = NULL;
            $data = $db->insertUserManual($name, $email, $username, $institute, $lab, $logintype, $role, $active, $pass_hash, $verify, $google_id);
            $ownerIDarr = json_decode($data, true);
            $user_id = $ownerIDarr["id"];
            $db->insertDefaultGroup($user_id);
            $db->insertDefaultRunEnvironment($user_id);
        }
    }
} else if ($p == "saveGoogleUser") {
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
    if ($username != "") {
        $_SESSION['username'] = $username;
    }
    $_SESSION['google_login'] = true;
    $_SESSION['google_id'] = $google_id;
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;
    $_SESSION['google_image'] = $google_image;
    if (!empty($id)) {
        $_SESSION['ownerID'] = $id;
        $_SESSION['role'] = $role;
        // send cookie 
        $token = $db->signJWTToken($id);
        if (!empty($token)) {
            setcookie('jwt-dolphinnext', $token, time() + 60 * 60 * 24 * 365, "/");
        }
    }
    $data = json_encode("done");
    session_write_close();
} else if ($p == "impersonUser") {
    $user_id = $_REQUEST['user_id'];
    if (!empty($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];
    } else {
        $admin_id = $_SESSION['ownerID'];
    }
    if (!empty($admin_id)) {
        session_destroy();
        session_start();
        $_SESSION = []; //manually clear $_SESSION
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
} else if ($p == "getUserGroups") {
    $data = $db->getUserGroups($ownerID);
} else if ($p == "getUserRole") {
    $data = $db->getUserRole($ownerID);
} else if ($p == "getExistProjectPipelines") {
    $type = "default";
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->getExistProjectPipelines($pipeline_id, $type, $ownerID);
} else if ($p == "getExistUserProjectPipelines") {
    $type = "user";
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->getExistProjectPipelines($pipeline_id, $type, $ownerID);
} else if ($p == "getExistSharedProjectPipelines") {
    $type = "shared";
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->getExistProjectPipelines($pipeline_id, $type, $ownerID);
} else if ($p == "getProjectPipelinesCron") {
    $data = $db->getProjectPipelinesCron($ownerID, $userRole);
} else if ($p == "getProjectPipelines") {
    $project_id = isset($_REQUEST['project_id']) ? $_REQUEST['project_id'] : "";
    $data = $db->getProjectPipelines($id, $project_id, $ownerID, $userRole);
} else if ($p == "checkNewRunParam") {
    $uuid = $db->queryAVal("SELECT last_run_uuid FROM $db->db.project_pipeline WHERE id='$id'");
    //return 1 if parameters have changed.
    $data = json_encode(0);
    $pipeData = json_decode($db->getProjectPipelines($id, "", $ownerID, $userRole), true)[0];
    $inputData = json_decode($db->getProjectPipelineInputs($id, $ownerID), true);
    if (!empty($pipeData)) {
        //last run info in run_logs
        $raw_data = json_decode($db->getRunLogOpt($uuid), true);
        if (!empty($raw_data[0])) {
            $raw_data[0]['run_opt'] = str_replace('\\', '\\\\', $raw_data[0]['run_opt']);
            $run_opt = json_decode($raw_data[0]['run_opt'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $run_input = $run_opt["project_pipeline_input"];
                //remove collection-id and project_pipeline_input keys
                unset($run_opt['project_pipeline_input']);
                foreach ($run_opt as $key => $value) {
                    if (strpos($key, 'collection-') === 0) {
                        unset($run_opt[$key]);
                    }
                }
                //don't check these keys
                //            $run_include = array("process_opt");
                //            foreach( $run_opt as $key => $value ) {
                //                if (in_array($key, $run_exclude)) {
                //                    if( $value != $pipeData[ $key ] ) {
                //                        $data = json_encode(1);
                //                        break;
                //                    }
                //                }
                //            }
                // check run inputs
                $run_input_given_name_arr = array();
                $run_input_id_arr = array();
                for ($i = 0; $i < count($run_input); $i++) {
                    foreach ($run_input[$i] as $k => $v) {
                        if ($k == "given_name") {
                            $run_input_given_name_arr[] = $v;
                        }
                        if ($k == "id") {
                            $run_input_id_arr[] = $v;
                        }
                    }
                }
                $run_input_dict = array_combine($run_input_given_name_arr, $run_input_id_arr);

                for ($i = 0; $i < count($inputData); $i++) {
                    $inItemId = $inputData[$i]["id"];
                    $inItemName = $inputData[$i]["given_name"];
                    if ($run_input_dict[$inItemName] != $inItemId) {
                        $data = json_encode(1);
                        break;
                    }
                }
            }
        }
    }
} else if ($p == "updateProjectPipelineNewRun") {
    $project_pipeline_id = isset($_REQUEST['project_pipeline_id']) ? $_REQUEST['project_pipeline_id'] : "";
    $new_run = $_REQUEST['newrun'];
    //only use when newrun is not exist
    $data = $db->updateProjectPipelineNewRun($project_pipeline_id, $new_run, $ownerID);
} else if ($p == "updateProjectPipelineWithOldRun") {
    $project_pipeline_id = isset($_REQUEST['project_pipeline_id']) ? $_REQUEST['project_pipeline_id'] : "";
    $run_log_uuid = $_REQUEST['run_log_uuid'];
    //only use when newrun is exist
    $data = $db->updateProjectPipelineWithOldRun($project_pipeline_id, $run_log_uuid, $ownerID);
}
// else if ($p=="updateReleaseDate"){
//     $type = $_REQUEST['type'];
//     $permCheck = 0;
//     //don't allow to update if user not own the pipeline.
//     if ($type == "pipeline" && !empty($id)){
//         $curr_ownerID= $db->queryAVal("SELECT owner_id FROM $db->db.biocorepipe_save WHERE id='$id'");
//         $permCheck = $db->checkUserOwnPerm($curr_ownerID, $ownerID);
//     }
//     if (!empty($permCheck)){
//         $data = $db -> updateReleaseDate($id,$type,$ownerID);
//     }
// }
else if ($p == "saveToken") {
    $type = $_REQUEST['type'];
    if ($type == "pipeline") {
        $np = 1;
        $curr_token = $db->queryAVal("SELECT token FROM $db->db.token WHERE np='$np' AND id='$id'");
        $curr_ownerID = $db->queryAVal("SELECT owner_id FROM $db->db.biocorepipe_save WHERE id='$id'");
    } else if ($type == "project_pipeline") {
        $np = 3;
        $curr_token = $db->queryAVal("SELECT token FROM $db->db.token WHERE np='$np' AND id='$id'");
        $curr_ownerID = $db->queryAVal("SELECT owner_id FROM $db->db.project_pipeline WHERE id='$id'");
    }
    $ownCheck = $db->checkUserOwnPerm($curr_ownerID, $ownerID);
    if (empty($curr_token)) {
        if (!empty($ownCheck)) {
            $data = $db->insertToken($id, $np, $ownerID);
        }
    } else {
        $ret = array();
        $ret["token"] = $curr_token;
        $data = json_encode($ret);
    }
} else if ($p == "getToken") {
    $type = $_REQUEST['type'];
    $ret = array();
    if ($type == "pipeline") {
        $np = 1;
        $curr_token = $db->queryAVal("SELECT token FROM $db->db.token WHERE np='$np' AND id='$id' AND owner_id='$ownerID'");
    }
    if (empty($curr_token)) {
        $data = json_encode($ret);
    } else {
        $ret["token"] = $curr_token;
        $data = json_encode($ret);
    }
} else if ($p == "getSSOAccessTokenByUserID") {
    $data = $db->getSSOAccessTokenByUserID($ownerID);
} else if ($p == "getRunLog") {
    $project_pipeline_id = isset($_REQUEST['project_pipeline_id']) ? $_REQUEST['project_pipeline_id'] : "";
    $type = "default";
    $data = $db->getRunLog($project_pipeline_id, $type);
} else if ($p == "getRunLogAll") {
    $project_pipeline_id = isset($_REQUEST['project_pipeline_id']) ? $_REQUEST['project_pipeline_id'] : "";
    $type = "all";
    $data = $db->getRunLog($project_pipeline_id, $type);
} else if ($p == "getRunLogStatus") {
    $uuid = $_REQUEST['uuid'];
    $data = $db->getRunLogStatus($uuid);
} else if ($p == "getRunLogOpt") {
    $data = json_encode("");
    $uuid = $_REQUEST['uuid'];
    $raw_data = json_decode($db->getRunLogOpt($uuid));
    if (!empty($raw_data[0])) {
        $raw_data[0]->{'run_opt'} = str_replace('\\', '\\\\', $raw_data[0]->{'run_opt'});
        $data = json_encode($raw_data[0]);
    }
} else if ($p == "sendEmail") {
    $adminemail = $_REQUEST['adminemail'];
    $useremail = $_REQUEST['useremail'];
    $message = $_REQUEST['message'];
    $subject = $_REQUEST['subject'];
    $checkAdminData = json_decode($db->getUserByEmail($adminemail));
    $adminName = isset($checkAdminData[0]) ? $checkAdminData[0]->{'name'} : "";
    $data = $db->sendEmail($adminemail, $adminName, $useremail, $subject, $message);
} else if ($p == "getProjectInputs") {
    $project_id = $_REQUEST['project_id'];
    $data = $db->getProjectInputs($project_id, $ownerID);
} else if ($p == "getProjectFiles") {
    $project_id = $_REQUEST['project_id'];
    $data = $db->getProjectFiles($project_id, $ownerID);
} else if ($p == "getPublicInputs") {
    $data = $db->getPublicInputs($id);
} else if ($p == "getPublicFiles") {
    $host = $_REQUEST['host'];
    $data = $db->getPublicFiles($host);
} else if ($p == "getPublicValues") {
    $host = $_REQUEST['host'];
    $data = $db->getPublicValues($host);
} else if ($p == "getProjectValues") {
    $project_id = $_REQUEST['project_id'];
    $data = $db->getProjectValues($project_id, $ownerID);
} else if ($p == "getProjectInput") {
    $data = $db->getProjectInput($id, $ownerID);
} else if ($p == "getProjectPipelineInputs") {
    $project_pipeline_id = isset($_REQUEST['project_pipeline_id']) ? $_REQUEST['project_pipeline_id'] : "";
    if (!empty($id)) {
        $data = $db->getProjectPipelineInputsById($id, $ownerID);
    } else {
        $data = $db->getProjectPipelineInputs($project_pipeline_id, $ownerID);
    }
} else if ($p == "getInputs") {
    $data = $db->getInputs($id, $ownerID);
} else if ($p == "getCollection") {
    if (!empty($id)) {
        $data = $db->getCollectionById($id, $userRole, $ownerID);
    } else {
        $data = $db->getCollections($ownerID);
    }
} else if ($p == "getCollectionFiles") {
    $data = $db->getCollectionFiles($id, $ownerID);
} else if ($p == "getFile") {
    if (!empty($id)) {
        //        $data = $db->getFileById($id,$ownerID);
    } else {
        $data = $db->getFiles($ownerID);
    }
} else if ($p == "getPipelineGroup") {
    $data = $db->getPipelineGroup($ownerID);
} else if ($p == "getAllProcessGroups") {
    $data = $db->getAllProcessGroups($ownerID);
} else if ($p == "getEditDelProcessGroups") {
    $data = $db->getEditDelProcessGroups($ownerID);
} else if ($p == "getEditDelPipelineGroups") {
    $data = $db->getEditDelPipelineGroups($ownerID);
} else if ($p == "removeParameter") {
    $db->removeProcessParameterByParameterID($id);
    $data = $db->removeParameter($id);
} else if ($p == "removeProcessGroup") {
    $db->removeProcessParameterByProcessGroupID($id);
    $db->removeProcessByProcessGroupID($id);
    $data = $db->removeProcessGroup($id);
} else if ($p == "removePipelineGithub") {
    $permCheck = 0;
    $userRole = $db->getUserRoleVal($ownerID);
    //don't allow to update if user not own the pipeline.
    if ($userRole != "admin" && !empty($id)) {
        $curr_ownerID = $db->queryAVal("SELECT owner_id FROM $db->db.biocorepipe_save WHERE id='$id'");
        $permCheck = $db->checkUserOwnPerm($curr_ownerID, $ownerID);
    } else if ($userRole == "admin") {
        $permCheck = 1;
    }
    if (!empty($permCheck)) {
        $data = $db->removePipelineGithub($id, $ownerID);
    }
} else if ($p == "removePipelineGroup") {
    $data = $db->removePipelineGroup($id);
} else if ($p == "removePipelineById") {
    $data = $db->removePipelineById($id);
} else if ($p == "removeProcess") {
    $db->removeProcessParameterByProcessID($id);
    $data = $db->removeProcess($id);
} else if ($p == "removeProject") {
    $name = $_REQUEST['name'];
    $data = $db->removeProject($id, $name, $ownerID);
} else if ($p == "removeContainer") {
    $data = $db->removeContainer($id, $ownerID);
} else if ($p == "removeGroup") {
    $data = $db->removeGroup($id, $ownerID);
} else if ($p == "removeUserFromGroup") {
    $u_id = $_REQUEST['u_id'];
    $g_id = $_REQUEST['g_id'];
    $data = $db->removeUserFromGroup($u_id, $g_id, $ownerID);
} else if ($p == "removeProjectPipeline") {
    $db->removeRun($id);
    $db->removeRunLogByPipe($id);
    $db->removeProjectPipelineInputByPipe($id);
    $data = $db->removeProjectPipeline($id);
} else if ($p == "removeRunLog") {
    $data = json_encode("");
    $runlogs = $_REQUEST['runlogs'];
    $type = $_REQUEST['type'];
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    foreach ($runlogs as $runlog_id) :
        if ($type == "remove") {
            $data = $db->removeRunLog($runlog_id, $ownerID);
        } else if ($type == "purge") {
            $data = $db->purgeRunLog($runlog_id, $ownerID);
        } else if ($type == "recover") {
            $data = $db->recoverRunLog($runlog_id, $ownerID);
        }
    endforeach;
    if ($type == "purge") {
        $db->saveUserDiskUsage($ownerID, $ownerID);
    }
    $runDataJS = $db->getLastRunData($project_pipeline_id);
    $last_run_uuid = "";
    $run_status = "";
    if (!empty(json_decode($runDataJS, true))) {
        $runData = json_decode($runDataJS, true)[0];
        if (!empty($runData)) {
            $last_run_uuid = $runData["last_run_uuid"];
            $run_status = $runData["run_status"];
        }
    }
    $db->updateProPipeLastRunUUID($project_pipeline_id, $last_run_uuid);
    $db->updateRunStatus($project_pipeline_id, $run_status, $ownerID);
} else if ($p == "removeProjectInput") {
    $data = $db->removeProjectInput($id);
} else if ($p == "removeInput") {
    $data = $db->removeInput($id);
} else if ($p == "removeFile") {
    $file_array = $_REQUEST['file_array'];
    $collection_arr = array();
    foreach ($file_array as $file_id) :
        //   Get all collections into array
        $colsOfFile = json_decode($db->getCollectionsOfFile($file_id, $ownerID));
        for ($i = 0; $i < count($colsOfFile); $i++) {
            $c_id = $colsOfFile[$i]->{'c_id'};
            if (!in_array($c_id, $collection_arr)) {
                $collection_arr[] = $c_id;
            }
        }
        $removeFileCollection = $db->removeFileCollection($file_id, $ownerID);
        $removeFileProject = $db->removeFileProject($file_id, $ownerID);
        $db->removeFile($file_id, $ownerID);
    endforeach;
    //check if these collections have any files, if not delete collection
    $removedCollection = array();
    for ($i = 0; $i < count($collection_arr); $i++) {
        $allfiles = json_decode($db->getCollectionFiles($collection_arr[$i], $ownerID));
        if (empty($allfiles[0])) {
            $db->removeCollection($collection_arr[$i], $ownerID);
            $db->removeProjectPipelineInputByCollection($collection_arr[$i]);
            $removedCollection[] = $collection_arr[$i];
        }
    }
    $data = json_encode($removedCollection);
} else if ($p == "removeProLocal") {
    $data = $db->removeProLocal($id);
} else if ($p == "removeProCluster") {
    $data = $db->removeProCluster($id);
} else if ($p == "removeProAmazon") {
    $data = $db->removeProAmazon($id);
} else if ($p == "removeProGoogle") {
    $data = $db->removeProGoogle($id);
} else if ($p == "removeProjectPipelineInput") {
    $data = $db->removeProjectPipelineInput($id);
} else if ($p == "removeProcessParameter") {
    $data = $db->removeProcessParameter($id);
} else if ($p == "saveParameter") {
    $name = $_REQUEST['name'];
    $qualifier = $_REQUEST['qualifier'];
    $file_type = $_REQUEST['file_type'];
    $parData = $db->getParameterByName($name, $qualifier, $file_type);
    $parData = json_decode($parData, true);
    if (isset($parData[0])) {
        $parId = $parData[0]["id"];
    } else {
        $parId = "";
    }
    settype($id, 'integer');
    if (!empty($id)) {
        $data = $db->updateParameter($id, $name, $qualifier, $file_type, $ownerID);
    } else {
        if (empty($parId)) {
            $data = $db->insertParameter($name, $qualifier, $file_type, $ownerID);
        } else {
            if ($userRole == "admin") {
                $db->updateParameter($parId, $name, $qualifier, $file_type, $ownerID);
            }
            $data = json_encode(array('id' => $parId));
        }
    }
} else if ($p == "getAmz") {
    if (!empty($id)) {
        $data = json_decode($db->getAmzbyID($id, $ownerID));
        foreach ($data as $d) {
            $access = $d->amz_acc_key;
            $d->amz_acc_key = trim($db->amazonDecode($access));
            $secret = $d->amz_suc_key;
            $d->amz_suc_key = trim($db->amazonDecode($secret));
        }
        $data = json_encode($data);
    } else {
        $data = $db->getAmz($ownerID);
    }
} else if ($p == "saveGoogle") {
    $name = $_REQUEST['name'];
    $project_id = $_REQUEST['project_id'];
    $key_name = $_REQUEST['key_name'];
    if (!empty($id)) {
        $data = $db->updateGoogle($id, $name, $project_id, $key_name, $ownerID);
    } else {
        $data = $db->insertGoogle($name, $project_id, $key_name, $ownerID);
        $newData = json_decode($data, true);
        $id = $newData["id"];
    }
    $ret = $db->insertGoogKey($id, $key_name, $ownerID);
} else if ($p == "getGoogle") {
    if (!empty($id)) {
        $data = $db->getGooglebyID($id, $ownerID);
    } else {
        $data = $db->getGoogle($ownerID);
    }
} else if ($p == "getSSH") {
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
    if (!empty($id)) {
        $data = json_decode($db->getSSHbyID($id, $userRole, $admin_id, $ownerID));
        foreach ($data as $d) {
            $d->prikey = $db->readKey($id, 'ssh_pri', $ownerID);
            $d->pubkey = $db->readKey($id, 'ssh_pub', $ownerID);
        }
        $data = json_encode($data);
    } else {
        $data = $db->getSSH($userRole, $admin_id, $type, $ownerID);
    }
} else if ($p == "removeSSH") {
    $db->delKey($id, "ssh_pri", $ownerID);
    $db->delKey($id, "ssh_pub", $ownerID);
    $data = $db->removeSSH($id);
} else if ($p == "removeAmz") {
    $data = $db->removeAmz($id);
} else if ($p == "removeUser") {
    $data = $db->removeUser($id, $ownerID);
} else if ($p == "removeGithub") {
    $data = $db->removeGithub($id, $ownerID);
} else if ($p == "removeGoogle") {
    $data = $db->removeGoogle($id, $ownerID);
} else if ($p == "generateKeys") {
    $data = $db->generateKeys($ownerID);
} else if ($p == "getProfileVariables") {
    $proType = isset($_REQUEST['proType']) ? $_REQUEST['proType'] : "";
    if (!empty($id) && !empty($proType)) {
        if ($proType == "cluster") {
            $data = $db->getProfileClusterbyID($id, $ownerID);
        } else if ($proType == "amazon" || $proType == "google") {
            $data = $db->getProfileCloudbyID($id, $proType, $ownerID);
        }
    } else {
        $proClu = $db->getRunProfileCluster($ownerID);
        $proAmz = $db->getRunProfileAmazon($ownerID);
        $proGoog = $db->getRunProfileGoogle($ownerID);
        $clu_obj = json_decode($proClu, true);
        $amz_obj = json_decode($proAmz, true);
        $goog_obj = json_decode($proGoog, true);
        $merged = array_merge($clu_obj, $amz_obj);
        $merged_obj = array_merge($goog_obj, $merged);
        $new_obj = array();
        if (isset($merged_obj)) {
            if (!empty($merged_obj[0])) {
                for ($i = 0; $i < count($merged_obj); $i++) {
                    $variable = isset($merged_obj[$i]["variable"]) ? $merged_obj[$i]["variable"] : "";
                    $hostname = "";
                    if (isset($merged_obj[$i]["hostname"])) {
                        $hostname = $merged_obj[$i]["hostname"];
                    } else if (isset($merged_obj[$i]["shared_storage_id"])) {
                        $hostname = $merged_obj[$i]["shared_storage_id"];
                    } else if (isset($merged_obj[$i]["image_id"])) {
                        $hostname = $merged_obj[$i]["image_id"];
                    }
                    if (!empty($hostname)) {
                        $tmpObj = array();
                        $tmpObj["variable"] = $variable;
                        $tmpObj["hostname"] = $hostname;
                        $new_obj[] = $tmpObj; //push $out object into array
                    }
                }
            }
        }
        $data = json_encode($new_obj);
    }
} else if ($p == "appendProfileVariables") {
    $data = json_encode("");
    $new_variable =  addslashes(htmlspecialchars(urldecode($_REQUEST['variable']), ENT_QUOTES));
    $proType = $_REQUEST['proType'];
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    if (!empty($id) && !empty($proType)) {
        if ($proType == "cluster") {
            $profdata = $db->getProfileClusterbyID($id, $ownerID);
        } else if ($proType == "amazon" || $proType == "google") {
            $profdata = $db->getProfileCloudbyID($id, $proType, $ownerID);
        }
    }
    $checkProData = json_decode($profdata, true);
    if (isset($checkProData[0])) {
        $old_variable = $checkProData[0]["variable"];
        if (empty($old_variable)) {
            $final_variable = $new_variable;
        } else {
            $final_variable = $old_variable . "\n" . $new_variable;
        }
        $db->updateProfileVariable($id, $proType, $final_variable, $ownerID);
        $onload = "refreshEnv";
        $data = $db->updateProjectPipelineOnload($project_pipeline_id, $onload, $ownerID);
    }
} else if ($p == "getProfiles") {
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
    $data = json_decode($db->getProfiles($type, $ownerID));
    foreach ($data as $d) {
        $bash_variable = $d->bash_variable;
        $d->bash_variable = trim($db->amazonDecode($bash_variable));
    }
    $data = json_encode($data);
} else if ($p == "getProfileCluster") {
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
    if (!empty($id)) {
        $data = json_decode($db->getProfileClusterbyID($id, $ownerID));
        foreach ($data as $d) {
            $bash_variable = $d->bash_variable;
            $d->bash_variable = trim($db->amazonDecode($bash_variable));
        }
        $data = json_encode($data);
    } else {
        if (empty($type)) {
            $data = $db->getProfileCluster($ownerID);
        } else if ($type == "public") {
            $data = $db->getPublicProfileCluster($ownerID);
        } else if ($type == "run") {
            $data = $db->getRunProfileCluster($ownerID);
        }
    }
} else if ($p == "getProfileCloud") {
    $cloud = $_REQUEST['cloud'];
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
    if ($cloud == "amazon") {
        if (!empty($id)) {
            $data = json_decode($db->getProfileCloudbyID($id, $cloud, $ownerID));
            foreach ($data as $d) {
                $bash_variable = $d->bash_variable;
                $d->bash_variable = trim($db->amazonDecode($bash_variable));
            }
            $data = json_encode($data);
        } else {
            if (empty($type)) {
                $data = $db->getProfileAmazon($ownerID);
            } else if ($type == "public") {
                $data = $db->getPublicProfileAmazon($ownerID);
            } else if ($type == "run") {
                $data = $db->getRunProfileAmazon($ownerID);
            }
        }
    } else if ($cloud == "google") {
        if (!empty($id)) {
            $data = json_decode($db->getProfileCloudbyID($id, $cloud, $ownerID));
            foreach ($data as $d) {
                $bash_variable = $d->bash_variable;
                $d->bash_variable = trim($db->amazonDecode($bash_variable));
            }
            $data = json_encode($data);
        } else {
            if (empty($type)) {
                $data = $db->getProfileGoogle($ownerID);
            } else if ($type == "public") {
                $data = $db->getPublicProfileGoogle($ownerID);
            } else if ($type == "run") {
                $data = $db->getRunProfileGoogle($ownerID);
            }
        }
    }
    // convert autoshutdown_date time to seconds
    $new_obj = json_decode($data, true);
    if (!empty($new_obj)) {
        for ($i = 0; $i < count($new_obj); $i++) {
            $autoshutdown_date = isset($new_obj[$i]["autoshutdown_date"]) ? $new_obj[$i]["autoshutdown_date"] : "";
            if (!empty($autoshutdown_date)) {
                $expected_date = strtotime($autoshutdown_date);
                $remaining = $expected_date - time();
                $new_obj[$i]["autoshutdown_date"] = $remaining;
            }
        }
        $data = json_encode($new_obj);
    }
} else if ($p == "updateCloudProStatus") {
    $status = $_REQUEST['status'];
    $cloud = $_REQUEST['cloud'];
    $data = $db->updateCloudProStatus($id, $status, $cloud, $ownerID);
} else if ($p == "updateCloudShutdownCheck") {
    $autoshutdown_check = $_REQUEST['autoshutdown_check'];
    $cloud = $_REQUEST['cloud'];
    if ($autoshutdown_check == "false") {
        $db->updateCloudShutdownDate($id, NULL, $cloud, $ownerID);
    }
    $data = $db->updateCloudShutdownCheck($id, $autoshutdown_check, $cloud, $ownerID);
    if ($autoshutdown_check == "true") {
        //to set timer
        error_log("triggerShutdown fast3 updateCloudShutdownCheck");
        $db->triggerShutdown($id, $cloud, $ownerID, "fast");
    }
} else if ($p == "validateSSH") {
    $connect = $_REQUEST['connect'];
    $ssh_port = $_REQUEST['ssh_port'];
    $ssh_id = $_REQUEST['ssh_id'];
    $type = $_REQUEST['type'];
    $cmd = $_REQUEST['cmd'];
    $path = $_REQUEST['path'];
    $data = $db->validateSSH($connect, $ssh_id, $ssh_port, $type, $cmd, $path, $ownerID);
} else if ($p == "saveSSHKeys") {
    $name = $_REQUEST['name'];
    $hide = $_REQUEST['hide'];
    $check_userkey = isset($_REQUEST['check_userkey']) ? $_REQUEST['check_userkey'] : "";
    $check_ourkey = isset($_REQUEST['check_ourkey']) ? $_REQUEST['check_ourkey'] : "";
    $prikeyRaw = $_REQUEST['prikey'];
    $pubkeyRaw = $_REQUEST['pubkey'];
    $prikey = urldecode($prikeyRaw);
    $pubkey = urldecode($pubkeyRaw);

    if (!empty($id)) {
        $data = $db->updateSSH($id, $name, $hide, $check_userkey, $check_ourkey, $ownerID);
        $db->insertKey($id, $prikey, "ssh_pri", $ownerID);
        $db->insertKey($id, $pubkey, "ssh_pub", $ownerID);
    } else {
        $data = $db->insertSSH($name, $hide, $check_userkey, $check_ourkey, $ownerID);
        $idArray = json_decode($data, true);
        $id = $idArray["id"];
        $db->insertKey($id, $prikey, "ssh_pri", $ownerID);
        $db->insertKey($id, $pubkey, "ssh_pub", $ownerID);
    }
} else if ($p == "getAutoPublicKey") {
    $data = "";
    if (!empty($ownerID)) {
        $name = "Auto-Generated Keys";
        $checkSSH = $db->getSSHbyName($name, $userRole, $admin_id, $ownerID);
        $checkSSHData = json_decode($checkSSH, true);
        if (isset($checkSSHData[0])) {
            $id = $checkSSHData[0]["id"];
            $ret['id'] = $id;
            $ret['$keyPub'] = $db->readKey($id, 'ssh_pub', $ownerID);
            $data = json_encode($ret);
        } else {
            $hide = "false";
            $check_userkey = "";
            $check_ourkey = "on";
            $sshkeys = $db->generateKeys($ownerID);
            $sshkeysAr = json_decode($sshkeys, true);
            $pubkey = $sshkeysAr['$keyPub'];
            $prikey = $sshkeysAr['$keyPri'];
            $insertSSH = $db->insertSSH($name, $hide, $check_userkey, $check_ourkey, $ownerID);
            $idArray = json_decode($insertSSH, true);
            $id = $idArray["id"];
            $db->insertKey($id, $prikey, "ssh_pri", $ownerID);
            $db->insertKey($id, $pubkey, "ssh_pub", $ownerID);
            $idArray['$keyPub'] = $pubkey;
            $data = json_encode($idArray);
        }
    }
} else if ($p == "saveAmzKeys") {
    $name = $_REQUEST['name'];
    $amz_def_reg = $_REQUEST['amz_def_reg'];
    $amz_acc_keyRaw = $_REQUEST['amz_acc_key'];
    $amz_suc_keyRaw = $_REQUEST['amz_suc_key'];
    $amz_acc_key = $db->amazonEncode($amz_acc_keyRaw);
    $amz_suc_key = $db->amazonEncode($amz_suc_keyRaw);
    if (!empty($id)) {
        $data = $db->updateAmz($id, $name, $amz_def_reg, $amz_acc_key, $amz_suc_key, $ownerID);
    } else {
        $data = $db->insertAmz($name, $amz_def_reg, $amz_acc_key, $amz_suc_key, $ownerID);
    }
}
if ($p == "publishGithub") {
    $data = json_encode("");
    $username_id = isset($_REQUEST['username']) ? $_REQUEST['username'] : "";
    $github_repo = isset($_REQUEST['github_repo']) ? $_REQUEST['github_repo'] : "";
    $github_branch = isset($_REQUEST['github_branch']) ? $_REQUEST['github_branch'] : "";
    $proVarObj = isset($_REQUEST['proVarObj']) ? json_decode(urldecode($_REQUEST['proVarObj'])) : "";
    $type = $_REQUEST['type']; //downPack, pushGithub
    $pipeline_id = $_REQUEST['pipeline_id'];
    $pipeData = $db->loadPipeline($pipeline_id, $ownerID);
    $pipe_obj = json_decode($pipeData, true);
    if (!empty($pipe_obj[0])) {
        if ($pipe_obj[0]["own"] == "1" || $type == "downPack") {
            $pipeline_name = $db->cleanName($pipe_obj[0]["name"], 30);
            $script_pipe_config = isset($pipe_obj[0]["script_pipe_config"]) ? $pipe_obj[0]["script_pipe_config"] : "";
            $description = htmlspecialchars_decode($pipe_obj[0]["summary"], ENT_QUOTES);
            $configText = "";

            $configText = $db->getProcessParams($proVarObj, $configText);

            if (!empty($script_pipe_config)) {
                $configText .= "\n// Pipeline Config:\n";
                $configText .= "\$HOSTNAME='default'\n";
                $configText .= htmlspecialchars_decode($script_pipe_config, ENT_QUOTES);
            }
            $nfData = urldecode($_REQUEST['nfData']);
            $dnData = urldecode($_REQUEST['dnData']);
            $initGitRepo = $db->initGitRepo($description, $pipeline_id, $pipeline_name, $username_id, $github_repo, $github_branch, $configText, $nfData, $dnData, $type, $ownerID);
            $data = json_encode($initGitRepo);
        }
    }
} else if ($p == "saveGithub") {
    $username = $_REQUEST['username'];
    $email = $_REQUEST['email'];
    $tokenRaw = $_REQUEST['token'];
    $token = $db->amazonEncode($tokenRaw);
    if (!empty($id)) {
        $data = $db->updateGithub($id, $username, $email, $token, $ownerID);
    } else {
        $data = $db->insertGithub($username, $email, $token, $ownerID);
    }
} else if ($p == "checkNewRelease") {
    $version = $_REQUEST['version'];
    $checkNewRelease = $db->checkNewRelease($version, $ownerID);
    $data = json_encode($checkNewRelease);
} else if ($p == "saveWizard") {
    $name = $_REQUEST['name'];
    $status = $_REQUEST['status'];
    $data =  addslashes(htmlspecialchars(urldecode($_REQUEST['data']), ENT_QUOTES));
    if (!empty($id)) {
        $data = $db->updateWizard($id, $name, $data, $status, $ownerID);
    } else {
        $data = $db->insertWizard($name, $data, $status, $ownerID);
    }
} else if ($p == "removeWizard") {
    $data = $db->removeWizard($id, $ownerID);
} else if ($p == "getWizard") {
    $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
    if (!empty($id)) {
        $data = $db->getWizardByID($id, $ownerID);
    } else {
        if ($type == "active") {
            $data = $db->checkActiveWizard($ownerID);
        } else if ($type == "all") {
            $data = $db->getWizardAll($ownerID);
        }
    }
} else if ($p == "getChangeLog") {
    $file = __DIR__ . "/../../NEWS";
    $content = "";
    if (file_exists($file)) {
        $content = $db->file_get_contents_utf8($file);
    }
    $data = json_encode($content);
} else if ($p == "getGithub") {
    if (!empty($id)) {
        $data = json_decode($db->getGithubbyID($id, $ownerID));
        foreach ($data as $d) {
            $token = $d->token;
            $d->token = trim($db->amazonDecode($token));
        }
        $data = json_encode($data);
    } else {
        $data = $db->getGithub($ownerID);
    }
} else if ($p == "saveProfileCluster") {
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
    $bash_variable =  addslashes(htmlspecialchars(urldecode($_REQUEST['bash_variable']), ENT_QUOTES));
    $bash_variable = $db->amazonEncode($bash_variable);
    $ssh_id = isset($_REQUEST['ssh_id']) ? $_REQUEST['ssh_id'] : "";
    settype($ssh_id, 'integer');
    $group_id = isset($_REQUEST['group_id']) ? $_REQUEST['group_id'] : "";
    settype($group_id, 'integer');
    $auto_workdir = isset($_REQUEST['auto_workdir']) ? $_REQUEST['auto_workdir'] : "";
    $perms = isset($_REQUEST['perms']) ? $_REQUEST['perms'] : 3;
    settype($perms, 'integer');
    $amazon_cre_id = isset($_REQUEST['amazon_cre_id']) ? $_REQUEST['amazon_cre_id'] : "";
    settype($amazon_cre_id, 'integer');
    $def_publishdir = isset($_REQUEST['def_publishdir']) ? $_REQUEST['def_publishdir'] : "";
    $def_workdir = isset($_REQUEST['def_workdir']) ? $_REQUEST['def_workdir'] : "";
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }
    if (!empty($id)) {
        $data = $db->updateProfileCluster($id, $name, $executor, $next_path, $port, $singu_cache, $username, $hostname, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $next_clu_opt, $job_clu_opt, $ssh_id, $public, $variable, $bash_variable, $group_id, $auto_workdir, $perms, $amazon_cre_id, $def_publishdir, $def_workdir, $ownerID);
    } else {
        $data = $db->insertProfileCluster($name, $executor, $next_path, $port, $singu_cache, $username, $hostname, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $next_clu_opt, $job_clu_opt, $ssh_id, $public, $variable, $bash_variable, $group_id, $auto_workdir, $perms, $amazon_cre_id, $def_publishdir, $def_workdir, $ownerID);
    }
} else if ($p == "saveProfileUser") {
    $emailNotif = $_REQUEST['emailNotif'];
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }
    if (!empty($ownerID)) {
        $data = $db->updateProfileUser($emailNotif, $ownerID);
    }
} else if ($p == "saveProfileAmazon") {
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
    $bash_variable =  addslashes(htmlspecialchars(urldecode($_REQUEST['bash_variable']), ENT_QUOTES));
    $bash_variable = $db->amazonEncode($bash_variable);
    $ssh_id = isset($_REQUEST['ssh_id']) ? $_REQUEST['ssh_id'] : "";
    settype($ssh_id, 'integer');
    $amazon_cre_id = isset($_REQUEST['amazon_cre_id']) ? $_REQUEST['amazon_cre_id'] : "";
    $security_group = $_REQUEST['security_group'];
    settype($amazon_cre_id, 'integer');
    $group_id = isset($_REQUEST['group_id']) ? $_REQUEST['group_id'] : "";
    settype($group_id, 'integer');
    $auto_workdir = isset($_REQUEST['auto_workdir']) ? $_REQUEST['auto_workdir'] : "";
    $def_publishdir = isset($_REQUEST['def_publishdir']) ? $_REQUEST['def_publishdir'] : "";
    $def_workdir = isset($_REQUEST['def_workdir']) ? $_REQUEST['def_workdir'] : "";
    $perms = isset($_REQUEST['perms']) ? $_REQUEST['perms'] : 3;
    settype($perms, 'integer');
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }
    if (!empty($id)) {
        $data = $db->updateProfileAmazon($id, $name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $subnet_id, $shared_storage_id, $shared_storage_mnt, $ssh_id, $amazon_cre_id, $next_clu_opt, $job_clu_opt, $public, $security_group, $variable, $bash_variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID);
    } else {
        $data = $db->insertProfileAmazon($name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $subnet_id, $shared_storage_id, $shared_storage_mnt, $ssh_id, $amazon_cre_id, $next_clu_opt, $job_clu_opt, $public, $security_group, $variable, $bash_variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID);
    }
} else if ($p == "saveProfileGoogle") {
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
    $next_path = $_REQUEST['next_path'];
    $port = $_REQUEST['port'];
    $singu_cache = $_REQUEST['singu_cache'];
    $variable =  addslashes(htmlspecialchars(urldecode($_REQUEST['variable']), ENT_QUOTES));
    $bash_variable =  addslashes(htmlspecialchars(urldecode($_REQUEST['bash_variable']), ENT_QUOTES));
    $bash_variable = $db->amazonEncode($bash_variable);
    $ssh_id = isset($_REQUEST['ssh_id']) ? $_REQUEST['ssh_id'] : "";
    settype($ssh_id, 'integer');
    $google_cre_id = isset($_REQUEST['google_cre_id']) ? $_REQUEST['google_cre_id'] : "";
    $zone = isset($_REQUEST['zone']) ? $_REQUEST['zone'] : "";
    settype($google_cre_id, 'integer');
    $group_id = isset($_REQUEST['group_id']) ? $_REQUEST['group_id'] : "";
    settype($group_id, 'integer');
    $auto_workdir = isset($_REQUEST['auto_workdir']) ? $_REQUEST['auto_workdir'] : "";
    $def_publishdir = isset($_REQUEST['def_publishdir']) ? $_REQUEST['def_publishdir'] : "";
    $def_workdir = isset($_REQUEST['def_workdir']) ? $_REQUEST['def_workdir'] : "";
    $perms = isset($_REQUEST['perms']) ? $_REQUEST['perms'] : 3;
    settype($perms, 'integer');
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }
    if (!empty($id)) {
        $data = $db->updateProfileGoogle($id, $name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ssh_id, $google_cre_id, $next_clu_opt, $job_clu_opt, $public, $zone, $variable, $bash_variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID);
    } else {
        $data = $db->insertProfileGoogle($name, $executor, $next_path, $port, $singu_cache, $ins_type, $image_id, $cmd, $next_memory, $next_queue, $next_time, $next_cpu, $executor_job, $job_memory, $job_queue, $job_time, $job_cpu, $ssh_id, $google_cre_id, $next_clu_opt, $job_clu_opt, $public, $zone, $variable, $bash_variable, $group_id, $auto_workdir, $def_publishdir, $def_workdir, $perms, $ownerID);
    }
} else if ($p == "saveInput") {
    $type = $_REQUEST['type'];
    $name = $_REQUEST['name'];
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }
    if (!empty($id)) {
        $data = $db->updateInput($id, $name, $type, $ownerID);
    } else {
        $data = $db->insertInput($name, $type, $ownerID);
    }
} else if ($p == "saveCollection") {
    $name = $_REQUEST['name'];
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }
    $data = $db->saveCollection($name, $ownerID);
} else if ($p == "insertFileCollection") {
    $collection_id = $_REQUEST['collection_id'];
    settype($collection_id, 'integer');
    $file_array = $_REQUEST['file_array'];
    foreach ($file_array as $file_id) :
        settype($file_id, 'integer');
        $insertFileCollection = $db->insertFileCollection($file_id, $collection_id, $ownerID);
        $file_col_data = json_decode($insertFileCollection, true);
        $file_col_id = $file_col_data["id"];
        if (empty($file_col_id)) {
            break;
        }
    endforeach;
    $data = $insertFileCollection;
} else if ($p == "insertFileProject") {
    $collection_id = $_REQUEST['collection_id'];
    $project_id = $_REQUEST['project_id'];
    settype($collection_id, 'integer');
    $file_arr = $db->getCollectionFiles($collection_id, $ownerID);
    $file_array = json_decode($file_arr, true);
    foreach ($file_array as $file_item) :
        $file_id = $file_item["id"];
        settype($file_id, 'integer');
        //    check if project input is exist
        $checkPro = $db->checkFileProject($project_id, $file_id);
        $checkProData = json_decode($checkPro, true);
        if (isset($checkProData[0])) {
            $projectFileID = $checkProData[0]["id"];
        } else {
            //insert into file project table
            $insertFileProject = $db->insertFileProject($file_id, $project_id, $ownerID);
            $insertProData = json_decode($insertFileProject, true);
            $projectFileID = $insertProData["id"];
        }
    endforeach;
    $data = $projectFileID;
} else if ($p == "updateFile") {
    $name = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
    $file_dir = isset($_REQUEST['file_dir']) ? $_REQUEST['file_dir'] : null;
    $file_type = isset($_REQUEST['file_type']) ? $_REQUEST['file_type'] : null;
    $files_used = isset($_REQUEST['files_used']) ? $_REQUEST['files_used'] : null;
    $collection_type = isset($_REQUEST['collection_type']) ? $_REQUEST['collection_type'] : null;
    $archive_dir = isset($_REQUEST['archive_dir']) ? $_REQUEST['archive_dir'] : null;
    $s3_archive_dir = isset($_REQUEST['s3_archive_dir']) ? $_REQUEST['s3_archive_dir'] : null;
    $gs_archive_dir = isset($_REQUEST['gs_archive_dir']) ? $_REQUEST['gs_archive_dir'] : null;
    $collection_ids = isset($_REQUEST['collection_id']) ? $_REQUEST['collection_id'] : array();
    $file_ids = $_REQUEST['file_id'];
    $removedCollections = isset($_REQUEST['removedCollections']) ? $_REQUEST['removedCollections'] : array();
    $updateArr = array();

    for ($i = 0; $i < count($file_ids); $i++) {
        $file_id = $file_ids[$i];
        for ($c = 0; $c < count($removedCollections); $c++) {
            $c_id = $removedCollections[$c];
            settype($c_id, 'integer');
            $db->removeSingleFileCollection($file_id, $c_id, $ownerID);
        }
        $update = $db->updateFile($file_id, $name, $file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $ownerID);
        $updateArr[] = $file_id;
        for ($d = 0; $d < count($collection_ids); $d++) {
            $collection_id = $collection_ids[$d];
            settype($collection_id, 'integer');
            $db->insertFileCollection($file_id, $collection_id, $ownerID);
        }
    }
    $data = json_encode($updateArr);
} else if ($p == "saveFile") {
    $collection_id = $_REQUEST['collection_id'];
    settype($collection_id, 'integer');
    $collection_type = $_REQUEST['collection_type'];
    $archive_dir = isset($_REQUEST['archive_dir']) ? $_REQUEST['archive_dir'] : "";
    $file_dir = isset($_REQUEST['file_dir']) ? json_decode($_REQUEST['file_dir']) : "";
    $file_array = json_decode($_REQUEST['file_array']);
    $s3_archive_dir = isset($_REQUEST['s3_archive_dir']) ? $_REQUEST['s3_archive_dir'] : "";
    $gs_archive_dir = isset($_REQUEST['gs_archive_dir']) ? $_REQUEST['gs_archive_dir'] : "";
    $file_type = $_REQUEST['file_type'];
    $project_id = $_REQUEST['project_id'];
    $run_env = $_REQUEST['run_env'];
    $run_env = $db->getRunEnv($run_env, $ownerID);
    $insertArr = array();
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }
    for ($i = 0; $i < count($file_array); $i++) {
        $item = $file_array[$i];
        $item_file_dir = isset($file_dir[$i]) ? $file_dir[$i] : "";
        $p = explode(" ", $item);
        $name = $p[0];
        unset($p[0]);
        $files_used = join(' ', $p);
        $insert = $db->insertFile($name, $item_file_dir, $file_type, $files_used, $collection_type, $archive_dir, $s3_archive_dir, $gs_archive_dir, $run_env, $ownerID);
        $fileData = json_decode($insert, true);
        $file_id = $fileData["id"];
        settype($file_id, 'integer');
        if (empty($file_id)) {
            break;
        } else {
            $insertArr[] = $file_id;
            $insertFileProject = $db->insertFileProject($file_id, $project_id, $ownerID);
            $insertFileCollection = $db->insertFileCollection($file_id, $collection_id, $ownerID);
            $file_col_data = json_decode($insertFileCollection, true);
            $file_col_id = $file_col_data["id"];
            if (empty($file_col_id)) {
                break;
            }
        }
    }
    $data = json_encode($insertArr);
} else if ($p == "saveProPipeInput") {
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
        $data = $db->updateProPipeInput($id, $project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url_id, $urlzip_id, $checkpath_id, $ownerID);
    } else {
        $data = $db->insertProPipeInput($project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url_id, $urlzip_id, $checkpath_id, $ownerID);
    }
} else if ($p == "fillInput") {
    $inputID = isset($_REQUEST['inputID']) ? $_REQUEST['inputID'] : "";
    $inputType = $_REQUEST['inputType'];
    $qualifier = $_REQUEST['qualifier'];
    if ($inputType == "single_file") $inputType = "file";
    if ($qualifier == "single_file") $qualifier = "file";
    $inputName = $_REQUEST['inputName'];
    $project_id = $_REQUEST['project_id'];
    $collection_id = isset($_REQUEST['collection_id']) ? $_REQUEST['collection_id'] : "";
    $pipeline_id = $_REQUEST['pipeline_id'];
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $g_num = $_REQUEST['g_num'];
    settype($g_num, 'integer');
    settype($collection_id, 'integer');
    $given_name = $_REQUEST['given_name'];
    $proPipeInputID = $_REQUEST['proPipeInputID'];
    $url = isset($_REQUEST['url']) ? $_REQUEST['url'] : "";
    $urlzip = isset($_REQUEST['urlzip']) ? $_REQUEST['urlzip'] : "";
    $checkpath = isset($_REQUEST['checkpath']) ? $_REQUEST['checkpath'] : "";
    $url_id = $db->checkAndInsertInput($url, "url", $ownerID);
    $urlzip_id = $db->checkAndInsertInput($urlzip, "url", $ownerID);
    $checkpath_id = $db->checkAndInsertInput($checkpath, "url", $ownerID);
    settype($url_id, 'integer');
    settype($urlzip_id, 'integer');
    settype($checkpath_id, 'integer');
    if (empty($collection_id)) {
        if (empty($inputID)) {
            $input_id = $db->checkAndInsertInput($inputName, $inputType, $ownerID);
        } else {
            $input_id = $inputID;
            //get inputdata from input table
            $indata = $db->getInputs($input_id, $ownerID);
            $indata = json_decode($indata, true);
            if (isset($indata[0])) {
                $inputName = $indata[0]["name"];
            }
        }
        $input_id = (string)$input_id;
        $projectInputID = $db->checkAndInsertProjectInput($project_id, $input_id, $ownerID);
        $projectInputID = (string)$projectInputID;
        $data = json_encode($projectInputID);
    } else {
        settype($inputID, 'integer');
        $input_id = $inputID;
    }
    //    $db->removeProjectPipelineInputByPipeAndName($project_pipeline_id, $given_name);
    //insert into project_pipeline_input table
    if (!empty($proPipeInputID)) {
        $data = $db->updateProPipeInput($proPipeInputID, $project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url_id, $urlzip_id, $checkpath_id, $ownerID);
        $projectPipelineInputID = $proPipeInputID;
    } else {
        $insertProPipe = $db->insertProPipeInput($project_pipeline_id, $input_id, $project_id, $pipeline_id, $g_num, $given_name, $qualifier, $collection_id, $url_id, $urlzip_id, $checkpath_id, $ownerID);
        $insertProPipeData = json_decode($insertProPipe, true);
        $projectPipelineInputID = $insertProPipeData["id"];
    }
    $projectPipelineInputID = (string)$projectPipelineInputID;
    $data = json_encode(array('projectPipelineInputID' => $projectPipelineInputID, 'inputName' => $inputName));
} else if ($p == "saveProjectInput") {
    $input_id = $_REQUEST['input_id'];
    $project_id = $_REQUEST['project_id'];
    $data = $db->insertProjectInput($project_id, $input_id, $ownerID);
} else if ($p == "savePipelineGroup") {
    $group_name = $_REQUEST['group_name'];
    $pipeGrData = $db->getPipelineGroupByName($group_name);
    $pipeGrData = json_decode($pipeGrData, true);
    $pipeGrId = "";
    if (isset($pipeGrData[0])) {
        $pipeGrId = $pipeGrData[0]["id"];
    }
    if (!empty($id)) {
        if (empty($pipeGrId)) {
            $data = $db->updatePipelineGroup($id, $group_name, $ownerID);
        } else {
            if ($userRole == "admin" && $pipeGrId == $id) {
                $data = $db->updatePipelineGroup($id, $group_name, $ownerID);
            } else {
                $data = json_encode(array('id' => $pipeGrId, 'message' => "Defined name already found in the menu groups."));
            }
        }
    } else {
        if (empty($pipeGrId)) {
            $data = $db->insertPipelineGroup($group_name, $ownerID);
        } else {
            if ($userRole == "admin") {
                $db->updatePipelineGroup($pipeGrId, $group_name, $ownerID);
            }
            $data = json_encode(array('id' => $pipeGrId));
        }
    }
} else if ($p == "saveProcessGroup") {
    $group_name = $_REQUEST['group_name'];
    $proGrData = $db->getProcessGroupByName($group_name);
    $proGrData = json_decode($proGrData, true);
    $proGrId = "";
    if (isset($proGrData[0])) {
        $proGrId = $proGrData[0]["id"];
    }
    if (!empty($id)) {
        //first check if $proGrId is found -> then return with warning -> this group already found in menu group
        if (empty($proGrId)) {
            $data = $db->updateProcessGroup($id, $group_name, $ownerID);
        } else {
            //prevents duplicate names
            if ($userRole == "admin" && $proGrId == $id) {
                $data = $db->updateProcessGroup($id, $group_name, $ownerID);
            } else {
                $data = json_encode(array('id' => $proGrId, 'message' => "Defined name already found in the menu groups."));
            }
        }
    } else {
        if (empty($proGrId)) {
            $data = $db->insertProcessGroup($group_name, $ownerID);
        } else {
            if ($userRole == "admin") {
                $db->updateProcessGroup($proGrId, $group_name, $ownerID);
            }
            $data = json_encode(array('id' => $proGrId));
        }
    }
} else if ($p == "testScript") {
    $outputs = isset($_REQUEST['outputs']) ? $_REQUEST['outputs'] : array();
    $inputs = isset($_REQUEST['inputs']) ? $_REQUEST['inputs'] : array();
    $code = $_REQUEST['code'];
    $env = $_REQUEST['env'];
    $data = $db->saveTestRun($inputs, $outputs, $code, $env, $ownerID);
} else if ($p == "saveProcess") {
    $name = $_REQUEST['name'];
    $process_gid = isset($_REQUEST['process_gid']) ? $_REQUEST['process_gid'] : "";
    if ($process_gid == "") {
        $max_gid = json_decode($db->getMaxProcess_gid(), true)[0]["process_gid"];
        settype($max_gid, "integer");
        if (!empty($max_gid)) {
            $process_gid = $max_gid + 1;
        } else {
            $process_gid = 1;
        }
    }
    $process_uuid = isset($_REQUEST['process_uuid']) ? $_REQUEST['process_uuid'] : "";
    $process_rev_uuid = isset($_REQUEST['process_rev_uuid']) ? $_REQUEST['process_rev_uuid'] : "";
    $process_uuid = "$process_uuid";
    $summary = isset($_REQUEST['summary']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['summary']), ENT_QUOTES)) : "";
    $process_group_id = $_REQUEST['process_group_id'];
    $script = isset($_REQUEST['script']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['script']), ENT_QUOTES)) : "";
    $script_header = isset($_REQUEST['script_header']) ?  addslashes(htmlspecialchars(urldecode($_REQUEST['script_header']), ENT_QUOTES)) : "";
    $script_footer = isset($_REQUEST['script_footer']) ?  addslashes(htmlspecialchars(urldecode($_REQUEST['script_footer']), ENT_QUOTES)) : "";

    $test_env = isset($_REQUEST['test_env']) ? $_REQUEST['test_env'] : "";
    $test_work_dir = isset($_REQUEST['test_work_dir']) ? $_REQUEST['test_work_dir'] : "";
    $docker_check = !empty($_REQUEST['docker_check']) ? 1 : 0;
    $docker_img = isset($_REQUEST['docker_img']) ? $_REQUEST['docker_img'] : "";
    $docker_opt = isset($_REQUEST['docker_opt']) ? $_REQUEST['docker_opt'] : "";
    $singu_check = !empty($_REQUEST['singu_check']) ? 1 : 0;
    $singu_img = isset($_REQUEST['singu_img']) ? $_REQUEST['singu_img'] : "";
    $singu_opt = isset($_REQUEST['singu_opt']) ? $_REQUEST['singu_opt'] : "";
    $script_test = isset($_REQUEST['script_test']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['script_test']), ENT_QUOTES)) : "";
    $script_test_mode = isset($_REQUEST['script_test_mode']) ? $_REQUEST['script_test_mode'] : "";
    $script_mode = isset($_REQUEST['script_mode']) ? $_REQUEST['script_mode'] : "";
    $script_mode_header = isset($_REQUEST['script_mode_header']) ? $_REQUEST['script_mode_header'] : "";
    $rev_id = isset($_REQUEST['rev_id']) ? $_REQUEST['rev_id'] : "";
    $rev_comment = isset($_REQUEST['rev_comment']) ? $_REQUEST['rev_comment'] : "";
    $group_id = isset($_REQUEST['group']) ? $_REQUEST['group'] : "";
    $write_group_id = isset($_REQUEST['write_group_id']) ? $_REQUEST['write_group_id'] : "";
    $perms = isset($_REQUEST['perms']) ? $_REQUEST['perms'] : 3;
    settype($id, 'integer');
    settype($rev_id, 'integer');
    settype($group_id, 'integer');
    settype($process_gid, "integer");
    settype($perms, "integer");
    settype($process_group_id, "integer");
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }




    if (!empty($id)) {
        $permCheck = 1;
        $$userRole = $db->getUserRoleVal($ownerID);
        $getUserGroupsIDs = json_decode($db->getUserGroupsIDs($ownerID), true);
        list($permCheck, $warnName) = $db->getWritePerm($ownerID, $id, $userRole, "process", $getUserGroupsIDs);

        if (!empty($permCheck)) {
            $db->updateAllProcessGroupByGid($process_gid, $process_group_id, $ownerID);
            $db->updateAllProcessNameByGid($process_gid, $name, $ownerID);
            $data = $db->updateProcess($id, $name, $process_gid, $summary, $process_group_id, $script, $script_header, $script_footer, $group_id, $perms, $script_mode, $script_mode_header, $test_env, $test_work_dir, $docker_check, $docker_img, $docker_opt, $singu_check, $singu_img, $singu_opt, $script_test, $script_test_mode, $write_group_id, $ownerID);
        }
    } else {
        $data = $db->insertProcess($name, $process_gid, $summary, $process_group_id, $script, $script_header, $script_footer, $rev_id, $rev_comment, $group_id, $perms, $script_mode, $script_mode_header, $process_uuid, $process_rev_uuid, $test_env, $test_work_dir, $docker_check, $docker_img, $docker_opt, $singu_check, $singu_img, $singu_opt, $script_test, $script_test_mode, $write_group_id, $ownerID);
        $idArray = json_decode($data, true);
        $new_pro_id = $idArray["id"];
        if (empty($id) && empty($process_uuid)) {
            $db->getUUIDAPI($data, "process", $new_pro_id);
        } else if (empty($id) && empty($process_rev_uuid)) {
            $db->getUUIDAPI($data, "process_rev", $new_pro_id);
        }
    }
} else if ($p == "callApp") {
    $ret = array();
    $uuid = $_REQUEST['uuid'];
    $location = $_REQUEST['location'];
    $dir = $_REQUEST['dir'];
    $type = $_REQUEST['type'];
    $filename = $_REQUEST['filename'];
    $container_id = $_REQUEST['container_id'];
    $memory = $_REQUEST['memory'];
    $cpu = $_REQUEST['cpu'];
    $time = $_REQUEST['time'];
    $text = urldecode($_REQUEST['text']);
    $pUUID = uniqid();
    //available app status: initiated, terminated, running
    $status = "initiated";
    // 1. check if app exists
    $checkApp = json_decode($db->checkApp($type, $uuid, $location, $ownerID), true);
    //insert into file_project table
    if (!isset($checkApp[0])) {
        $insertApp = $db->insertApp($status, $type, $uuid, $location, $dir, $filename, $container_id, $memory, $cpu, $time, $pUUID, $ownerID);
        $app_id = json_decode($insertApp, true)["id"];
        $ret = $db->callApp($app_id, $uuid, $text, $dir, $filename, $container_id, $pUUID, $time, $ownerID);
    } else {
        $app_id = $checkApp[0]["id"];
        $old_status = $checkApp[0]["status"];
        if ($old_status == "terminated" || $old_status == "error" || empty($old_status)) {
            $updateApp = $db->updateApp($app_id, $status, $type, $uuid, $location, $dir, $filename, $container_id, $memory, $cpu, $time, $pUUID, $ownerID);
            $ret = $db->callApp($app_id, $uuid, $text, $dir, $filename, $container_id, $pUUID, $time, $ownerID);
        } else {
            $ret["message"] = "The app is already running. Please terminate your app and try again.";
        }
    }
    $data = json_encode($ret);
} else if ($p == "callRmarkdown") {
    $uuid = $_REQUEST['uuid'];
    $dir = $_REQUEST['dir'];
    $type = $_REQUEST['type'];
    $filename = $_REQUEST['filename'];
    $text = urldecode($_REQUEST['text']);
    $data = $db->callRmarkdown($type, $uuid, $text, $dir, $filename);
} else if ($p == "callDebrowser") {
    $uuid = $_REQUEST['uuid'];
    $dir = $_REQUEST['dir'];
    $filename = $_REQUEST['filename'];
    $data = $db->callDebrowser($uuid, $dir, $filename);
} else if ($p == "checkFileExist") {
    $location = $_REQUEST['location'];
    $uuid = $_REQUEST['uuid'];
    $data = $db->checkFileExist($location, $uuid, $ownerID);
} else if ($p == "moveFile") {
    $from = $_REQUEST['from'];
    $to = $_REQUEST['to'];
    $type = $_REQUEST['type'];
    $data = $db->moveFile($type, $from, $to, $ownerID);
} else if ($p == "saveProject") {
    $name = addslashes(htmlspecialchars(urldecode($_REQUEST['name']), ENT_QUOTES));
    $summary = isset($_REQUEST['summary']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['summary']), ENT_QUOTES)) : "";
    if (!empty($id)) {
        $data = $db->updateProject($id, $name, $summary, $ownerID);
    } else {
        $data = $db->insertProject($name, $summary, $ownerID);
    }
} else if ($p == "saveContainer") {
    $name = addslashes(htmlspecialchars(urldecode($_REQUEST['name']), ENT_QUOTES));
    $summary = isset($_REQUEST['summary']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['summary']), ENT_QUOTES)) : "";
    $files = isset($_REQUEST['files']) ? $_REQUEST['files'] : "";
    $type = $_REQUEST['type'];
    $image_name = $_REQUEST['image_name'];
    $status = $_REQUEST['status'];
    $perms = $_REQUEST['perms'];
    $group_id = $_REQUEST['group_id'];
    settype($group_id, 'integer');
    if (!empty($files) && !empty($id)) {
        $savedFiles = $db->saveAppFiles($files, $id, $ownerID);
        if (empty($savedFiles)) die(json_encode("Files couldn't be saved!"));
    }

    if (!empty($id)) {
        $data = $db->updateContainer($id, $name, $summary, $type, $image_name, $status, $group_id, $perms, $ownerID);
    } else {
        $data = $db->insertContainer($name, $summary, $type, $image_name, $status, $group_id, $perms, $ownerID);
    }
} else if ($p == "savePublicInput") {
    $name = $_REQUEST['name'];
    $type = $_REQUEST['type'];
    $host = $_REQUEST['host'];
    if (!empty($id)) {
        $data = $db->updatePublicInput($id, $name, $type, $host, $ownerID);
    } else {
        $data = $db->insertPublicInput($name, $type, $host, $ownerID);
    }
} else if ($p == "saveGroup") {
    $name = $_REQUEST['name'];
    $name = str_replace("'", "", $name);
    if (!empty($id)) {
        $data = $db->updateGroup($id, $name, $ownerID);
    } else {
        $data = $db->insertGroup($name, $ownerID);
        $idArray = json_decode($data, true);
        $g_id = $idArray["id"];
        $db->insertUserGroup($g_id, $ownerID, $ownerID);
    }
} else if ($p == "saveTestGroup") {
    $data = $db->saveTestGroup($ownerID);
} else if ($p == "checkUpdateAppStatus") {
    $data = json_encode(array());
    $uuid = $_REQUEST['uuid'];
    $location = $_REQUEST['location'];
    $type = $_REQUEST['type'];
    $checkApp = json_decode($db->checkApp($type, $uuid, $location, $ownerID), true);
    if (!empty($checkApp[0])) {
        $app_id = $checkApp[0]["id"];
        $data = $db->checkUpdateAppStatus($app_id, $ownerID);
    }
} else if ($p == "terminateApp") {
    $data = json_encode(array());
    $uuid = $_REQUEST['uuid'];
    $location = $_REQUEST['location'];
    $type = $_REQUEST['type'];
    $checkApp = json_decode($db->checkApp($type, $uuid, $location, $ownerID), true);
    if (!empty($checkApp[0])) {
        $app_id = $checkApp[0]["id"];
        $data = $db->terminateApp($app_id, $ownerID);
    }
} else if ($p == "saveUserGroup") {
    $u_id = $_REQUEST['u_id'];
    $g_id = $_REQUEST['g_id'];
    $data = $db->insertUserGroup($g_id, $u_id, $ownerID);
} else if ($p == "duplicateProjectPipelineInput") {
    $new_id = $_REQUEST['new_id'];
    $old_id = $_REQUEST['old_id'];
    $data = $db->duplicateProjectPipelineInput($new_id, $old_id, $ownerID);
    // dupicate collection files if user is admin and files/collection not shared
    $userRole = $db->getUserRoleVal($ownerID);
    if ($userRole == "admin") {
        // check if collection exists
        $allinputs = json_decode($db->getProjectPipelineInputs($new_id, $ownerID));
        if (!empty($allinputs)) {
            foreach ($allinputs as $inputitem) :
                $collection_id = $inputitem->{'collection_id'};
                $proPipeInputId = $inputitem->{'id'};
                if (!empty($collection_id)) {
                    error_log("proPipeInputId: $proPipeInputId");
                    // get collection and check if it is not shared
                    // $checkerRole set to user to learn if it is shared to admin/owned by admin or not
                    $checkerRole = "user";
                    $colData = $db->getCollectionById($collection_id, $checkerRole, $ownerID);
                    $colData = json_decode($colData, true);
                    error_log(print_r($colData, TRUE));
                    if (!isset($colData[0])) {
                        error_log("## collection not shared -> insert collection");
                        $checkerRole = "admin";
                        $orgColData = $db->getCollectionById($collection_id, $checkerRole, $ownerID);
                        $orgColData = json_decode($orgColData, true);
                        error_log(print_r($orgColData, TRUE));

                        if (isset($orgColData[0])) {
                            $collection_name = $orgColData[0]["name"];
                            error_log($collection_name);
                            // collection not shared or not owned
                            $allfiles = json_decode($db->getCollectionFiles($collection_id, $ownerID), true);
                            error_log(print_r($allfiles, TRUE));
                            $new_collection_id = $db->checkAndInsertCollectionForDup($allfiles,  $collection_name, $ownerID);
                            error_log("update updateValProPipeInput $proPipeInputId: $new_collection_id");
                            $input_id = 0;
                            $db->updateProPipeInputCollInput($proPipeInputId, $input_id, $new_collection_id, $ownerID);
                        }
                    }
                }
            endforeach;
        }
    }
} else if ($p == "moveRun") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $new_project_id = $_REQUEST['new_project_id'];
    $old_project_id = $_REQUEST['old_project_id'];
    $group_id = $_REQUEST['group_id'];
    $perms = $_REQUEST['perms'];
    $old_project_id = $_REQUEST['old_project_id'];
    $data = $db->updateProPipe_ProjectID($project_pipeline_id, $new_project_id, $group_id, $perms, $ownerID);
    $db->updateProPipeInput_ProjectID($project_pipeline_id, $new_project_id, $group_id, $perms, $ownerID);
    //get project_pipeline_inputs belong to project_pipeline and add into file_project and project_input tables
    $allinputs = json_decode($db->getProjectPipelineInputs($project_pipeline_id, $ownerID));
    foreach ($allinputs as $inputitem) :
        $input_id = $inputitem->{'input_id'};
        $collection_id = $inputitem->{'collection_id'};
        $input_id = (string)$input_id;
        //insert into ProjectInput :
        if (!empty($input_id) && $input_id != "0" && $input_id != 0) {
            //check if project input is exist
            $checkPro = $db->checkProjectInput($new_project_id, $input_id);
            $checkProData = json_decode($checkPro, true);
            //insert into project_input table
            if (!isset($checkProData[0])) {
                $insertPro = $db->insertProjectInput($new_project_id, $input_id, $ownerID);
            }
        }
        //insert into FileProject :
        if (!empty($collection_id)) {
            settype($collection_id, 'integer');
            $file_arr = $db->getCollectionFiles($collection_id, $ownerID);
            $file_array = json_decode($file_arr, true);
            foreach ($file_array as $file_item) :
                $file_id = $file_item["id"];
                settype($file_id, 'integer');
                // check if project input is exist
                $checkFilePro = $db->checkFileProject($new_project_id, $file_id);
                $checkFileProData = json_decode($checkFilePro, true);
                //insert into file_project table
                if (!isset($checkFileProData[0])) {
                    $insertFileProject = $db->insertFileProject($file_id, $new_project_id, $ownerID);
                }
            endforeach;
        }
    endforeach;
} else if ($p == "duplicateProcess") {
    $new_process_gid = $_REQUEST['process_gid'];
    $new_name = $_REQUEST['name'];
    $old_id = $_REQUEST['id'];
    $data = $db->duplicateProcess($new_process_gid, $new_name, $old_id, $ownerID);
    $idArray = json_decode($data, true);
    $new_pro_id = $idArray["id"];
    $db->duplicateProcessParameter($new_pro_id, $old_id, $ownerID);
    $db->getUUIDAPI($data, "process", $new_pro_id);
} else if ($p == "saveRunSummary") {
    $data = json_encode("");
    $uuid = $_REQUEST['uuid'];
    $summary = isset($_REQUEST['summary']) ?  addslashes(htmlspecialchars(urldecode($_REQUEST['summary']), ENT_QUOTES)) : "";
    if (!empty($id)) {
        $curr_ownerID = $db->queryAVal("SELECT owner_id FROM $db->db.project_pipeline WHERE id='$id'");
        $permCheck = $db->checkUserOwnPerm($curr_ownerID, $ownerID);
        if (!empty($permCheck)) {
            $data = $db->updateProjectPipelineSummary($id, $uuid, $summary, $ownerID);
        }
    }
} else if ($p == "saveCron") {
    $data = json_encode("");
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $cron_min = !empty($_REQUEST['cron_min']) ? $_REQUEST['cron_min'] : 0;
    $cron_hour = !empty($_REQUEST['cron_hour']) ? $_REQUEST['cron_hour'] : 0;
    $cron_day = !empty($_REQUEST['cron_day']) ? $_REQUEST['cron_day'] : 0;
    $cron_week = !empty($_REQUEST['cron_week']) ? $_REQUEST['cron_week'] : 0;
    $cron_month = !empty($_REQUEST['cron_month']) ? $_REQUEST['cron_month'] : 0;
    $cron_prefix = isset($_REQUEST['cron_prefix']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['cron_prefix']), ENT_QUOTES)) : "";
    $cron_first = isset($_REQUEST['cron_first']) ? $_REQUEST['cron_first'] : "";
    error_log($cron_first);
    if (!empty($cron_first)) {
        $cron_first = date('Y-m-d H:i:s', strtotime("$cron_first"));
        error_log($cron_first);
    }
    if (!empty($project_pipeline_id)) {
        //don't allow to update if user doesn't own the project_pipeline.
        $curr_ownerID = $db->queryAVal("SELECT owner_id FROM $db->db.project_pipeline WHERE id='$project_pipeline_id'");
        $permCheck = $db->checkUserOwnPerm($curr_ownerID, $ownerID);
        if (!empty($permCheck)) {
            $data = $db->updateProjectPipelineCron($project_pipeline_id, $cron_min, $cron_hour, $cron_day, $cron_week, $cron_month, $cron_prefix, $cron_first, $ownerID);
        }
    }
} else if ($p == "saveProjectPipeline") {
    $pipeline_id = $_REQUEST['pipeline_id'];
    $project_id = $_REQUEST['project_id'];
    $name = isset($_REQUEST['name']) ?  addslashes(htmlspecialchars(urldecode($_REQUEST['name']), ENT_QUOTES)) : "";
    $summary = isset($_REQUEST['summary']) ?  addslashes(htmlspecialchars(urldecode($_REQUEST['summary']), ENT_QUOTES)) : "";
    $output_dir = isset($_REQUEST['output_dir']) ? $_REQUEST['output_dir'] : "";
    $publish_dir = isset($_REQUEST['publish_dir']) ? $_REQUEST['publish_dir'] : "";
    $publish_dir_check = isset($_REQUEST['publish_dir_check']) ? $_REQUEST['publish_dir_check'] : "";
    $perms = isset($_REQUEST['perms']) ? $_REQUEST['perms'] : 3;
    $group_id = isset($_REQUEST['group_id']) ? $_REQUEST['group_id'] : 0;
    $profile = isset($_REQUEST['profile']) ? $_REQUEST['profile'] : "";
    $interdel = isset($_REQUEST['interdel']) ? $_REQUEST['interdel'] : "";
    $cmd = isset($_REQUEST['cmd']) ? urldecode($_REQUEST['cmd']) : "";
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
    $google_cre_id = isset($_REQUEST['google_cre_id']) ? $_REQUEST['google_cre_id'] : "";
    $withReport = isset($_REQUEST['withReport']) ? $_REQUEST['withReport'] : "";
    $withTrace = isset($_REQUEST['withTrace']) ? $_REQUEST['withTrace'] : "";
    $withTimeline = isset($_REQUEST['withTimeline']) ? $_REQUEST['withTimeline'] : "";
    $withDag = isset($_REQUEST['withDag']) ? $_REQUEST['withDag'] : "";
    $process_opt = isset($_REQUEST['process_opt']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['process_opt']), ENT_QUOTES)) : "";
    $onload = isset($_REQUEST['onload']) ? $_REQUEST['onload'] : "";
    $release_date = isset($_REQUEST['release_date']) ? $_REQUEST['release_date'] : "";
    if (!empty($release_date)) {
        $release_date = date('Y-m-d', strtotime($release_date));
    }


    $cron_check = isset($_REQUEST['cron_check']) ? $_REQUEST['cron_check'] : "";
    $cron_prefix = isset($_REQUEST['cron_prefix']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['cron_prefix']), ENT_QUOTES)) : "";
    $cron_min = isset($_REQUEST['cron_min']) ? $_REQUEST['cron_min'] : "";
    $cron_hour = isset($_REQUEST['cron_hour']) ? $_REQUEST['cron_hour'] : "";
    $cron_day = isset($_REQUEST['cron_day']) ? $_REQUEST['cron_day'] : "";
    $cron_week = isset($_REQUEST['cron_week']) ? $_REQUEST['cron_week'] : "";
    $cron_month = isset($_REQUEST['cron_month']) ? $_REQUEST['cron_month'] : "";
    $cron_first = isset($_REQUEST['cron_first']) ? $_REQUEST['cron_first'] : "";
    $notif_check = isset($_REQUEST['notif_check']) ? $_REQUEST['notif_check'] : "";
    $email_notif = isset($_REQUEST['email_notif']) ? $_REQUEST['email_notif'] : "";
    $notif_email_list = isset($_REQUEST['notif_email_list']) ?  addslashes(htmlspecialchars(urldecode($_REQUEST['notif_email_list']), ENT_QUOTES)) : "";

    settype($perms, 'integer');
    settype($group_id, 'integer');
    settype($amazon_cre_id, 'integer');
    settype($google_cre_id, 'integer');
    settype($cron_min, 'integer');
    settype($cron_hour, 'integer');
    settype($cron_day, 'integer');
    settype($cron_week, 'integer');
    settype($cron_month, 'integer');
    if (!empty($id)) {
        //don't allow to update if user doesn't own the project_pipeline.
        $curr_ownerID = $db->queryAVal("SELECT owner_id FROM $db->db.project_pipeline WHERE id='$id'");
        $permCheck = $db->checkUserOwnPerm($curr_ownerID, $ownerID);
        if (!empty($permCheck)) {
            if ($cron_check == "false") {
                $targetTime = NULL;
                $db->updateProjectPipelineCronTargetDate($id, $targetTime);
            }
            $db->updateProjectPipeline($id, $name, $summary, $output_dir, $perms, $profile, $interdel, $cmd, $group_id, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $google_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $onload, $release_date, $cron_check, $cron_prefix, $cron_min, $cron_hour, $cron_day, $cron_week, $cron_month, $notif_check, $email_notif, $cron_first, $notif_email_list, $ownerID);
            $db->updateProjectPipelineInputGroupPerm($id, $group_id, $perms, $ownerID);
            $listPermsDenied = array();
            $listPermsDenied = $db->recursivePermUpdtPipeline("greaterOrEqual", $listPermsDenied, $pipeline_id, $group_id, $perms, $ownerID, null, null, null);
            $listPermsDenied = $db->checkPermUpdtProject("greaterOrEqual", $listPermsDenied, $project_id, $group_id, $perms, $ownerID);
            $data = json_encode($listPermsDenied);
        }
    } else {
        $data = $db->insertProjectPipeline($name, $project_id, $pipeline_id, $summary, $output_dir, $profile, $interdel, $cmd, $exec_each, $exec_all, $exec_all_settings, $exec_each_settings, $docker_check, $docker_img, $singu_check, $singu_save, $singu_img, $exec_next_settings, $docker_opt, $singu_opt, $amazon_cre_id, $google_cre_id, $publish_dir, $publish_dir_check, $withReport, $withTrace, $withTimeline, $withDag, $process_opt, $onload, $perms, $group_id, $cron_check, $cron_prefix, $cron_min, $cron_hour, $cron_day, $cron_week, $cron_month, $notif_email_list, $ownerID);
    }
} else if ($p == "saveProcessParameter") {
    $closure = isset($_REQUEST['closure']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['closure']), ENT_QUOTES)) : "";
    $test = isset($_REQUEST['test']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['test']), ENT_QUOTES)) : "";
    $reg_ex = isset($_REQUEST['reg_ex']) ? addslashes(htmlspecialchars(urldecode($_REQUEST['reg_ex']), ENT_QUOTES)) : "";
    $operator = isset($_REQUEST['operator']) ? $_REQUEST['operator'] : "";
    $optional = isset($_REQUEST['optional']) ? $_REQUEST['optional'] : "";
    $sname = addslashes(htmlspecialchars(urldecode($_REQUEST['sname']), ENT_QUOTES));
    $process_id = $_REQUEST['process_id'];
    $parameter_id = $_REQUEST['parameter_id'];
    $type = $_REQUEST['type'];
    $perms = $_REQUEST['perms'];
    $group_id = $_REQUEST['group'];
    settype($id, 'integer');
    settype($group_id, 'integer');
    settype($perms, 'integer');
    settype($parameter_id, 'integer');
    settype($process_id, 'integer');
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }
    if (!empty($id)) {
        $data = $db->updateProcessParameter($id, $sname, $process_id, $parameter_id, $type, $closure, $operator, $reg_ex, $test, $optional, $perms, $group_id, $ownerID);
    } else {
        $data = $db->insertProcessParameter($sname, $process_id, $parameter_id, $type, $closure, $operator, $reg_ex, $test, $optional, $perms, $group_id, $ownerID);
    }
} else if ($p == "getProcessData") {
    if (isset($_REQUEST['process_id'])) {
        $process_id = $_REQUEST['process_id'];
        $data = $db->getProcessDataById($process_id, $ownerID);
    } else {
        $data = $db->getProcessData($ownerID);
    }
} else if ($p == "getProcessRevision") {
    $id = $_REQUEST['process_id'];
    $process_gidAr = $db->getProcess_gid($id);
    $checkarray = json_decode($process_gidAr, true);
    $process_gid = $checkarray[0]["process_gid"];
    $data = $db->getProcessRevision($process_gid, $ownerID);
} else if ($p == "getPipelineRevision") {
    $pipeline_id = $_REQUEST['pipeline_id'];
    $pipeline_gid = json_decode($db->getPipeline_gid($pipeline_id))[0]->{'pipeline_gid'};
    $data = $db->getPipelineRevision($pipeline_gid, $ownerID);
} else if ($p == "getPublicPipelines") {
    $data = $db->getPublicPipelines($ownerID);
} else if ($p == "getRunStatsByPipeline") {
    $type = $_REQUEST['type'];
    $userRole = $db->getUserRoleVal($ownerID);
    if ($userRole == "admin") {
        $data = $db->getRunStatsByPipeline($type, $ownerID);
    }
} else if ($p == "checkPipeline") {
    $process_id = $_REQUEST['process_id'];
    $data = $db->checkPipeline($process_id, $ownerID);
} else if ($p == "checkInput") {
    $name = $_REQUEST['name'];
    $type = $_REQUEST['type'];
    $data = $db->checkInput($name, $type);
} else if ($p == "checkProjectInput") {
    $input_id = $_REQUEST['input_id'];
    $project_id = $_REQUEST['project_id'];
    $data = $db->checkProjectInput($project_id, $input_id);
} else if ($p == "checkProPipeInput") {
    $input_id = $_REQUEST['input_id'];
    $project_id = $_REQUEST['project_id'];
    $pipeline_id = $_REQUEST['pipeline_id'];
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $data = $db->checkProPipeInput($project_id, $input_id, $pipeline_id, $project_pipeline_id);
} else if ($p == "checkPipelinePublic") {
    $process_id = $_REQUEST['process_id'];
    $data = $db->checkPipelinePublic($process_id, $ownerID);
} else if ($p == "checkProjectPipelinePublic") {
    $process_id = $_REQUEST['process_id'];
    $data = $db->checkProjectPipelinePublic($process_id, $ownerID);
} else if ($p == "checkPermUpdtProcess") {
    $listPermsDenied = array();
    $process_id = $_REQUEST['process_id'];
    $group_id = isset($_REQUEST['group_id']) ? $_REQUEST['group_id'] : "";
    $perms = $_REQUEST['perms'];
    $process_data = json_decode($db->getProcessDataById($process_id, $ownerID), true);
    if (!empty($process_data[0])) {
        $pro_group_id = $process_data[0]["group_id"];
        $pro_perms = $process_data[0]["perms"];
        $pro_owner_id = $process_data[0]["owner_id"];
        settype($group_id, 'integer');
        settype($perms, 'integer');
        $getUserGroupsIDs = json_decode($db->getUserGroupsIDs($ownerID), true);
        $listPermsDenied = $db->permUpdtModule($listPermsDenied, "dry-run-strict", "process", $process_id, $pro_group_id, $pro_perms, $group_id, $perms, $pro_owner_id, $ownerID, null, null, $getUserGroupsIDs);
    }
    $data = json_encode($listPermsDenied);
} else if ($p == "checkPermUpdtPipeline") {
    $pipeline_id = $_REQUEST['pipeline_id'];
    $group_id = isset($_REQUEST['group_id']) ? $_REQUEST['group_id'] : "";
    $perms = $_REQUEST['perms'];
    settype($group_id, 'integer');
    settype($perms, 'integer');
    $listPermsDenied = array();
    // strict removed because couldn't make the pipeline public since one public module used 
    $listPermsDenied = $db->recursivePermUpdtPipeline("dry-run", $listPermsDenied, $pipeline_id, $group_id, $perms, $ownerID, null, null, null);
    $data = json_encode($listPermsDenied);
} else if ($p == "checkPermUpdtRun") {
    $project_id = $_REQUEST['project_id'];
    $pipeline_id = $_REQUEST['pipeline_id'];
    $group_id = isset($_REQUEST['group_id']) ? $_REQUEST['group_id'] : "";
    $perms = $_REQUEST['perms'];
    settype($group_id, 'integer');
    settype($perms, 'integer');
    $listPermsDenied = array();
    $listPermsDenied = $db->recursivePermUpdtPipeline("dry-run", $listPermsDenied, $pipeline_id, $group_id, $perms, $ownerID, null, null, null);
    $listPermsDenied = $db->checkPermUpdtProject("dry-run", $listPermsDenied, $project_id, $group_id, $perms, $ownerID);
    $data = json_encode($listPermsDenied);
} else if ($p == "checkUserWritePermRun") {
    $project_pipeline_id = $_REQUEST['project_pipeline_id'];
    $checkUserWritePermRun = $db->checkUserWritePermRun($project_pipeline_id, $ownerID);
    if (empty(json_decode($checkUserWritePermRun))) {
        $data = json_encode(0);
    } else {
        $data = json_encode(1);
    }
} else if ($p == "checkProject") {
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->checkProject($pipeline_id, $ownerID);
} else if ($p == "checkProjectPublic") {
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->checkProjectPublic($pipeline_id, $ownerID);
} else if ($p == "checkParameter") {
    $parameter_id = $_REQUEST['parameter_id'];
    $data = $db->checkParameter($parameter_id, $ownerID);
} else if ($p == "checkMenuGr") {
    $data = $db->checkMenuGr($id);
} else if ($p == "checkPipeMenuGr") {
    $data = $db->checkPipeMenuGr($id);
} else if ($p == "getMaxProcess_gid") {
    $data = $db->getMaxProcess_gid();
} else if ($p == "getMaxOptionalInputNum") {
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->getMaxOptionalInputNum($pipeline_id, $ownerID);
} else if ($p == "getMaxPipeline_gid") {
    $data = $db->getMaxPipeline_gid();
} else if ($p == "getProcess_gid") {
    $process_id = $_REQUEST['process_id'];
    $data = $db->getProcess_gid($process_id);
} else if ($p == "getProcess_uuid") {
    $process_id = $_REQUEST['process_id'];
    $data = $db->getProcess_uuid($process_id);
} else if ($p == "check_uuid") {
    $type = $_REQUEST['type'];
    $uuid = $_REQUEST['uuid'];
    $rev_uuid = $_REQUEST['rev_uuid'];
    $data_uuid = $db->getLastProPipeByUUID($uuid, $type, $ownerID);
    $data_rev_uuid = $db->getProPipeDataByUUID($uuid, $rev_uuid, $type, $ownerID);
    $obj1 = json_decode($data_uuid, true);
    $obj2 = json_decode($data_rev_uuid, true);
    if ($type == "process") {
        $data["process_uuid"] = isset($obj1[0]) ? $obj1[0] : null;
        $data["process_rev_uuid"] = isset($obj2[0]) ? $obj2[0] : null;
        if (isset($obj2[0])) {
            $process_id = $obj2[0]["id"];
            $pro_para_in = $db->getInputsPP($process_id);
            $pro_para_out = $db->getOutputsPP($process_id);
            $data["pro_para_inputs_$process_id"] = $pro_para_in;
            $data["pro_para_outputs_$process_id"] = $pro_para_out;
        }
        $data = json_encode($data);
    } else if ($type == "pipeline") {
        $data["pipeline_uuid"] = isset($obj1[0]) ? $obj1[0] : null;
        $data["pipeline_rev_uuid"] = isset($obj2[0]) ? $obj2[0] : null;
        $data = json_encode($data);
    }
} else if ($p == "getPipeline_gid") {
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->getPipeline_gid($pipeline_id);
} else if ($p == "getPipeline_uuid") {
    $pipeline_id = $_REQUEST['pipeline_id'];
    $data = $db->getPipeline_uuid($pipeline_id);
} else if ($p == "getMaxRev_id") {
    $process_gid = $_REQUEST['process_gid'];
    $data = $db->getMaxRev_id($process_gid);
} else if ($p == "getMaxPipRev_id") {
    $data = json_encode("");
    $pipeline_gid = $_REQUEST['pipeline_gid'];
    $pipeline_id = $_REQUEST['pipeline_id'];
    $userRole = $db->getUserRoleVal($ownerID);
    $getUserGroupsIDs = json_decode($db->getUserGroupsIDs($ownerID), true);
    list($permCheck, $warnName) = $db->getWritePerm($ownerID, $pipeline_id, $userRole, "biocorepipe_save", $getUserGroupsIDs);
    if (!empty($permCheck)) {
        $data = $db->getMaxPipRev_id($pipeline_gid);
    }
} else if ($p == "getInputsPP") {
    $process_id = $_REQUEST['process_id'];
    $data = $db->getInputsPP($process_id);
} else if ($p == "getOutputsPP") {
    $process_id = $_REQUEST['process_id'];
    $data = $db->getOutputsPP($process_id);
} else if ($p == "saveAllPipeline") {
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }
    $dat = $_REQUEST['dat'];
    $obj = json_decode($dat);
    $newObj = new stdClass();
    foreach ($obj as $item) :
        foreach ($item as $k => $v) $newObj->$k = $v;
    endforeach;
    $id = $newObj->{"id"};
    $group_id = $newObj->{"group_id"};
    $write_group_id = $newObj->{"write_group_id"};
    settype($group_id, 'integer');
    $perms = $newObj->{"perms"};
    $publicly_searchable = isset($newObj->{"publicly_searchable"}) ? $newObj->{"publicly_searchable"} : "false"; // only effective if user is admin
    $release_date = !empty($newObj->{"release_date"}) ? $newObj->{"release_date"} : NULL;
    if (!empty($release_date)) {
        $release_date = date('Y-m-d', strtotime($release_date));
    }
    $userRole = $db->getUserRoleVal($ownerID);
    $getUserGroupsIDs = json_decode($db->getUserGroupsIDs($ownerID), true);
    list($permCheck, $warnName) = $db->getWritePerm($ownerID, $id, $userRole, "biocorepipe_save", $getUserGroupsIDs);
    error_log($permCheck);
    if (!empty($permCheck)) {
        $data = $db->saveAllPipeline($newObj, $userRole, $ownerID);
    }
    //update
    if (!empty($id)) {
        if (!empty($permCheck)) {
            $listPermsDenied = array();
            $listPermsDenied = $db->recursivePermUpdtPipeline("default", $listPermsDenied, $id, $group_id, $perms, $ownerID, $publicly_searchable, $release_date, $write_group_id);
            $data = json_encode($listPermsDenied);
        }
        //insert
    } else {
        $idArray = json_decode($data, true);
        $new_pipe_id = $idArray["id"];
        if (!empty($new_pipe_id)) {
            $pipeline_uuid = isset($newObj->{"pipeline_uuid"}) ? $newObj->{"pipeline_uuid"} : "";
            $pipeline_rev_uuid = isset($newObj->{"pipeline_rev_uuid"}) ? $newObj->{"pipeline_rev_uuid"} : "";
            if (empty($pipeline_uuid)) {
                $db->getUUIDAPI($data, "pipeline", $new_pipe_id);
            } else if (empty($pipeline_rev_uuid)) {
                $db->getUUIDAPI($data, "pipeline_rev", $new_pipe_id);
            }
        }
    }
} else if ($p == "savePipelineDetails") {
    $data = json_encode("");
    $summary = addslashes(htmlspecialchars(urldecode($_REQUEST['summary']), ENT_QUOTES));
    $write_group_id = $_REQUEST['write_group_id'];
    $group_id = $_REQUEST['group_id'];
    $nodesRaw = isset($_REQUEST['nodes']) ? $_REQUEST['nodes'] : "";
    $perms = $_REQUEST['perms'];
    $pin = $_REQUEST['pin'];
    $pin_order = $_REQUEST['pin_order'];
    $publicly_searchable = isset($_REQUEST['publicly_searchable']) ? $_REQUEST['publicly_searchable'] : "false";
    $pipeline_group_id = $_REQUEST['pipeline_group_id'];
    $release_date = $_REQUEST['release_date'];
    if (empty($ownerID)) {
        header("HTTP/1.0 400 User session ended");
        exit;
    }
    if (!empty($release_date)) {
        $release_date = date('Y-m-d', strtotime($release_date));
    }
    settype($group_id, 'integer');
    settype($pin_order, "integer");
    $userRole = $db->getUserRoleVal($ownerID);
    $getUserGroupsIDs = json_decode($db->getUserGroupsIDs($ownerID), true);
    list($permCheck, $warnName) = $db->getWritePerm($ownerID, $id, $userRole, "biocorepipe_save", $getUserGroupsIDs);

    if (!empty($permCheck)) {
        $data = $db->savePipelineDetails($id, $summary, $group_id, $perms, $pin, $pin_order, $publicly_searchable, $pipeline_group_id, $userRole, $release_date, $ownerID);
        //update permissions
        if (!empty($nodesRaw)) {
            $listPermsDenied = array();
            $listPermsDenied = $db->recursivePermUpdtPipeline("default", $listPermsDenied, $id, $group_id, $perms, $ownerID, $publicly_searchable, $release_date, $write_group_id);
        }
    }
} else if ($p == "getSavedPipelines") {
    $data = $db->getSavedPipelines($ownerID);
} else if ($p == "getPipelineSideBar") {
    $data = $db->getPipelineSideBar($ownerID);
} else if ($p == "exportPipeline") {
    $data = $db->exportPipeline($id, $ownerID, "main", 0);
} else if ($p == "loadPipeline") {
    $id = $_REQUEST['id'];
    $data = $db->loadPipeline($id, $ownerID);
    //load process parameters 
    $new_obj = json_decode($data, true);
    if (!empty($new_obj[0]["nodes"])) {
        $nodes = json_decode($new_obj[0]["nodes"]);
        foreach ($nodes as $item) :
            if ($item[2] !== "inPro" && $item[2] !== "outPro") {
                $process_id = $item[2];
                $pro_para_in = $db->getInputsPP($process_id);
                $pro_para_out = $db->getOutputsPP($process_id);
                $new_obj[0]["pro_para_inputs_$process_id"] = $pro_para_in;
                $new_obj[0]["pro_para_outputs_$process_id"] = $pro_para_out;
            }
        endforeach;
        $data = json_encode($new_obj);
    }
}

if (!headers_sent()) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo $data;
    exit;
} else {
    echo $data;
}
