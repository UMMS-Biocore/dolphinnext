<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once(__DIR__."/../../config/config.php");

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
session_write_close();

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
    //runpipeline.php
    } else if (!empty($ownerID) && !empty($_FILES["pubweb"])){
        $run_path = RUNPATH;
        $uuid = $_POST['uuid'];
        $dir = $_POST['dir'];
        $targetDir = "$run_path/$uuid/pubweb/$dir";
        
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0700, true);
        }
        $tempFile = $_FILES['pubweb']['tmp_name']; 
        $targetFile =  "$targetDir/". $_FILES['pubweb']['name'];  
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
    }
    
    move_uploaded_file($tempFile,$targetFile);
}

?>    
