<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once(__DIR__."/../config/config.php");

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
session_write_close();

$tmp_path = TEMPPATH;
$targetDir = "$tmp_path/uploads/{$email}";
if (!file_exists($targetDir)) {
   mkdir($targetDir, 0755, true);
}
if (!empty($_FILES)) {
    $tempFile = $_FILES['file']['tmp_name'];                     
    $targetFile =  "$targetDir/". $_FILES['file']['name'];  
    move_uploaded_file($tempFile,$targetFile);
}

?>    
