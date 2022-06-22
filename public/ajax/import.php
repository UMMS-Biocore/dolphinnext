<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once(__DIR__."/../../config/config.php");
require_once("../ajax/dbfuncs.php");
$db = new dbfuncs();

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
session_write_close();

function triggerError($msg){
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-type: text/plain');
    exit($msg);
}

if (empty($ownerID)){
    triggerError("Please login and try again.");

}

if (!empty($_FILES)) {
    //pipeline.php
    if (!empty($_FILES["file"])){
        $tmp_path = TEMPPATH;
        $targetDir = "$tmp_path/uploads/{$ownerID}";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $tempFile = $_FILES['file']['tmp_name'];                     
        $targetFile =  "$targetDir/". $_FILES['file']['name'];  
        //profile.php 
    } else if (!empty($_FILES["goog"])){
        $goog_path = GOOGPATH;
        if (empty($goog_path)) die(json_encode('ERROR: Admin should define google path.'));
        $targetDir = "$goog_path/uploads/{$ownerID}";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0700, true);
        }
        $tempFile = $_FILES['goog']['tmp_name'];                     
        $targetFile =  "$targetDir/".$ownerID."_tmpkey";  
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
        //runpipeline.php dynamicRows
    } else if (!empty($ownerID) && !empty($_FILES["pubweb"])){
        $run_path = RUNPATH;
        $uuid = isset($_POST["uuid"]) ? $_POST["uuid"] : "";
        $dir = isset($_POST["dir"]) ? $_POST["dir"] : "";
        $targetDir = "$run_path/$uuid/pubweb/$dir";

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0700, true);
        }
        $tempFile = $_FILES['pubweb']['tmp_name']; 
        $targetFile =  "$targetDir/". $_FILES['pubweb']['name'];  
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
        //runpipeline.php singlefile upload modal
    } else if (!empty($_FILES["single_file"])){
        $tmp_path = TEMPPATH;
        $upload_dir = "$tmp_path/uploads/{$ownerID}";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $tempFile = $_FILES['single_file']['tmp_name'];  
        $fileName = $_FILES['single_file']['name']; 
        $targetFile =  "$upload_dir/". $fileName;   
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
    }

    move_uploaded_file($tempFile,$targetFile);
    if (!empty($_FILES["single_file"])){
        $new_target_dir = isset($_POST["target_dir"]) ? $_POST["target_dir"] : "";
        $run_env = isset($_POST["run_env"]) ? $_POST["run_env"] : "";
        if (empty($new_target_dir)){
            triggerError("Failed to get target directory.");
        }
        if (empty($run_env)){
            triggerError("Failed to get run environment.");
        }
        $profileAr = explode("-", $run_env);
        $profileType = $profileAr[0];
        $profileId = $profileAr[1];

        $cmd_log = $db->rsyncTransfer($targetFile,$fileName, $new_target_dir, $upload_dir, $profileId, $profileType, $ownerID, "sync");
        if (!preg_match("/rsync successfully completed/", $cmd_log)){
            triggerError($cmd_log);
        }
    }

}
