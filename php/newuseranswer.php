<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once(__DIR__."/../ajax/dbfuncs.php");
$query=new dbfuncs();

function sendMailAdmin($fullname_space, $firstname_val, $lastname_val, $username_val, $institute_val, $email_val, $lab_val, $verify){
    	mail(EMAIL_ADMIN, "Dolphin User Verification: $fullname_space",
		 "User Information:
		 
First name: ".$firstname_val."
Last name: ".$lastname_val."
Username: ".$username_val."
Institute: ".$institute_val."
Lab: ".$lab_val."
Email: ".$email_val."
		 
Please visit this link in order to activate this dolphin account:\n " . BASE_PATH . "?p=verify&code=$verify", "From: ".EMAIL_SENDER);
}

if (isset($_POST['request'])){
    if($_POST['firstname'] == ""){
        $err_firstname = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
    } else{
	   $firstname_val = str_replace("'", "", $_POST['firstname']);
       $fullname_space = str_replace("'", "", $_POST['firstname'] . " ");
    }
    if($_POST['lastname'] == ""){
        $err_lastname = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
    } else if (!isset($firstname_val)){
        $lastname_val = str_replace("'", "", $_POST['lastname']);
    } else {
        $lastname_val = str_replace("'", "", $_POST['lastname']);
        $fullname_space .= str_replace("'", "", $_POST['lastname']);
    }
  if($_POST['institute'] == ""){
	$err_institute = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
  }else{
	$institute_val = str_replace("'", "", $_POST['institute']);
  }
    
   if($_POST['username'] == ""){
	$err_username = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
  }else{
	   $username_val = str_replace("'", "", $_POST['username']);
	   $username_check = $query->queryAVal("SELECT id FROM users WHERE username = LCASE('" . $_POST['username'] . "')");
	if($username_check != "0"){
	  $err_username = '<font class="text-center" color="crimson">This Username already exists.</font>';
	}
  }
  
  if($_POST['lab'] == ""){
	$err_lab = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
  }else{
	$lab_val = str_replace("'", "", $_POST['lab']);
  }
  
  if($_POST['email'] == ""){
	$err_email = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
  } else{
      $email_val = str_replace("'", "", $_POST['email']);
      $email_check = $query->queryAVal("SELECT id FROM users WHERE email = LCASE('" . $_POST['email'] . "')");
      if($email_check != "0"){
          $err_email = '<font class="text-center" color="crimson">This Email already exists.</font>';
      }
  }
  
  if($_POST['password'] == ""){
	$err_password = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
  }else{
	$password_val = str_replace("'", "", $_POST['password']);
	if(strlen($_POST['password']) < 7){
	  $err_password = '<font class="text-center" color="crimson">Password must be at least 7 characters long.</font>';
	}
  }
  
  if($_POST['verifypassword'] == ""){
	$err_verifypassword = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
  }else if($_POST['password'] != $_POST['verifypassword']){
	$err_password = '<font class="text-center" color="crimson">Passwords must match.</font>';
  }
  
  if(!isset($err_lastname) && !isset($err_firstname) && !isset($err_username) && !isset($err_email) && !isset($err_password) && !isset($err_verifypassword) && !isset($err_lab) && !isset($err_institute)){
	//	Calc pass hash
	$pass_hash=hash('md5', $password_val . SALT) . hash('sha256', $password_val . PEPPER);
	$verify=hash('md5', $email_val . VERIFY);
	//	Add new user to the database
	$insert_user = $query->runSQL("
    INSERT INTO users
	( `username`, `name`, `email`, `institute`, `lab`, `pass_hash`, `verification`, `memberdate`,
    `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user` )
	VALUES
	( '$username_val', '$fullname_space', '".strtolower($email_val)."', '$institute_val',
    '$lab_val', '$pass_hash', '".$verify."', NOW(), 1, 1, 3, NOW(), NOW(), 1 )");
    sendMailAdmin($fullname_space, $firstname_val, $lastname_val, $username_val, $institute_val, $email_val, $lab_val, $verify);  
	session_destroy();
	require_once("newuserverification.php");
	exit;
  } else if (isset($_SESSION['google_login']) && $_SESSION['google_login'] = true && isset($_SESSION['email']) && $_SESSION['email'] != ""  && !isset($err_lastname) && !isset($err_firstname) && !isset($err_username) && !isset($err_lab) && !isset($err_institute)){
    $email = $_SESSION['email'];
    $checkUserData = json_decode($query->getUserByEmail($email));
    $id = isset($checkUserData[0]) ? $checkUserData[0]->{'id'} : "";
	$verify=hash('md5', $email . VERIFY);
    $query->updateUser($id, $fullname_space, $username_val, $institute_val, $lab_val, $verify);
    sendMailAdmin($fullname_space, $firstname_val, $lastname_val, $username_val, $institute_val, $email, $lab_val, $verify);
    session_destroy();
	require_once("newuserverification.php");
	exit;  
  } else {
      session_destroy();
      session_start();
      $_SESSION['google_login'] = false;
      session_write_close();
      require_once("newuserform.php");
      $e="Login Failed.";
      exit;
  }
}
?>