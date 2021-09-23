<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once(__DIR__."/../ajax/dbfuncs.php");
$query=new dbfuncs();


if(isset($_GET['p']) && $_GET['p'] == "verify"){
  if (isset($_GET['code'])){$code = $_GET['code'];}
  $newuser = json_decode($query->queryTable("
  SELECT *
  FROM `users`
  WHERE verification = '$code'
  "));
  if($newuser[0]->verification == $code){
    $insert_user = $query->runSQL("
    UPDATE `users`
	SET verification = NULL, active = 1
	WHERE verification = '$code'
	");
    $message ="Your DolphinNext account is now active!<br>You can start browsing at ".BASE_PATH;
    $from = EMAIL_SENDER;
    $from_name = "DolphinNext Team";
    $to =  $newuser[0]->email;
    $subject = "Your DolphinNext account is now active!";
    $ret = $query->sendEmail($from, $from_name, $to, $subject, $message);
    require_once("newuserverified.php");
    session_destroy();
    exit;
  }else{
    require_once("loginform.php");
    session_destroy();
    exit;
  }
}
?>
