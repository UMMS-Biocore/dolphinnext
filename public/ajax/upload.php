<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once(__DIR__."/../../config/config.php");
require_once("../ajax/dbfuncs.php");
$db = new dbfuncs();

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
session_write_close();

// Make sure file is not cached (as it happens for example on iOS devices)
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
/* 
// Support CORS
header("Access-Control-Allow-Origin: *");
// other CORS headers if any...
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
	exit; // finish preflight CORS requests here
}
*/
@set_time_limit(1 * 60 * 60); // 1h execution time
// Uncomment this one to fake upload time
// usleep(5000);
$tmp_path = TEMPPATH;
$upload_dir = "$tmp_path/uploads/{$ownerID}";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}
$cleanupTargetDir = true; // Remove old files
$maxFileAge = 1 * 3600; // Temp file age in seconds

// Get a file name
if (isset($_REQUEST["name"])) {
    $fileName = $_REQUEST["name"];
} elseif (!empty($_FILES)) {
    $fileName = $_FILES["file"]["name"];
} else {
    $fileName = uniqid("file_");
}


$filePath = $upload_dir . DIRECTORY_SEPARATOR . $fileName;
$logFile = $upload_dir . DIRECTORY_SEPARATOR . "." . $fileName;
$pidFile = $upload_dir . DIRECTORY_SEPARATOR . "." . $fileName. ".rsyncPid";
// Chunking might be enabled
$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

// Remove old temp files
if ($chunk === 0){
    $tmpFile = $filePath. ".part";
    if (file_exists($tmpFile)) {
        @unlink($tmpFile);
    }
    if (file_exists($filePath)) {
        @unlink($filePath);
    }
    if (file_exists($logFile)) {
        @unlink($logFile);
    }
    if (file_exists($pidFile)) {
        @unlink($pidFile);
    }
}
	
// Open temp file
if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
}
if (!empty($_FILES)) {
    if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
        die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
    }
    // Read binary input stream and append it to temp file
    if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
    }
} else {	
    if (!$in = @fopen("php://input", "rb")) {
        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
    }
}
while ($buff = fread($in, 4096)) {
    fwrite($out, $buff);
}
@fclose($out);
@fclose($in);
// Check if file has been uploaded
if (!$chunks || $chunk == $chunks - 1) {
    // Strip the temp .part suffix off 
    rename("{$filePath}.part", $filePath);
    //start rsync  and send to background
    $target_dir = isset($_REQUEST["target_dir"]) ? $_REQUEST["target_dir"] : "";
    $run_env = isset($_REQUEST["run_env"]) ? $_REQUEST["run_env"] : "";
    if (empty($target_dir)){
        die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to get target directory."}, "id" : "id"}');
    }
    if (empty($run_env)){
        die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to get run environment."}, "id" : "id"}');
    }
    $profileAr = explode("-", $run_env);
    $profileType = $profileAr[0];
    $profileId = $profileAr[1];
    $cmd_log = $db->rsyncTransfer($filePath,$fileName, $target_dir, $upload_dir, $profileId, $profileType, $ownerID, "async");
    die('{"jsonrpc" : "2.0", "result" : null, "id" : "id", "rsync_log": '.json_encode($cmd_log).'}');
    
}
// Return Success JSON-RPC response
die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');




?>    
