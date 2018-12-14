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
  FROM users
  WHERE verification = '$code'
  "));
  if($newuser[0]->verification == $code){
    $insert_user = $query->runSQL("
    UPDATE users
	SET verification = NULL, active = 1
	WHERE verification = '$code'
	");
	mail($newuser[0]->email, "Your DolphinNext account is now active!",
"Your DolphinNext account is now active!
You can start browsing at ".BASE_PATH, "From: ".EMAIL_SENDER);
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