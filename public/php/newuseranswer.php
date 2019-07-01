<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once(__DIR__."/../ajax/dbfuncs.php");
$query=new dbfuncs();

function sendMailAdmin($fullname_space, $firstname_val, $lastname_val, $username_val, $institute_val, $email_val, $lab_val, $verify, $query){
    $message="New user has registered. User Information:<br><br>";
    $message.="First name: ".$firstname_val;
    $message.="<br>Last name: ".$lastname_val;
    $message.="<br>Username: ".$username_val;
    $message.="<br>Institute: ".$institute_val;
    $message.="<br>Lab: ".$lab_val;
    $message.="<br>Email: ".$email_val;
    $message.="<br><br>Please visit this link in order to activate this dolphin account:<br> " . BASE_PATH . "?p=verify&code=$verify";
    $from = EMAIL_SENDER;
    $from_name = "DolphinNext Team";
    $to =  EMAIL_ADMIN;
    $subject = "DolphinNext User Verification: $fullname_space";
    $ret = $query->sendEmail($from, $from_name, $to, $subject, $message);
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
	   $username_check = $query->queryAVal("SELECT id FROM users WHERE deleted = 0 AND username = LCASE('" . $_POST['username'] . "')");
	if($username_check != "0"){
	  $err_username = '<font class="text-center" color="crimson">This Username already exists.</font>';
	}
  }
  
  if($_POST['lab'] == ""){
	$err_lab = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
  }else{
	$lab_val = str_replace("'", "", $_POST['lab']);
  }
  
    if (isset($_POST['email'])){
        if($_POST['email'] == ""){
            $err_email = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
        } else{
            $email_val = str_replace("'", "", $_POST['email']);
            $email_check = $query->queryAVal("SELECT id FROM users WHERE deleted = 0 AND email = LCASE('" . $_POST['email'] . "')");
            if($email_check != "0"){
                $err_email = '<font class="text-center" color="crimson">This Email already exists.</font>';
            }
        }
    }
  
  if (isset($_POST['password'])){
    if($_POST['password'] == ""){
	   $err_password = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
    }else{
	   $password_val = str_replace("'", "", $_POST['password']);
	   if(strlen($_POST['password']) < 7){
	   $err_password = '<font class="text-center" color="crimson">Password must be at least 7 characters long.</font>';
	   }
    }
  }
    
  if (isset($_POST['verifypassword'])){
    if($_POST['verifypassword'] == ""){
	   $err_verifypassword = '<font class="text-center" color="crimson">Cannot submit with this field empty.</font>';
    }else if($_POST['password'] != $_POST['verifypassword']){
	   $err_password = '<font class="text-center" color="crimson">Passwords must match.</font>';
    }
  }
  
    error_log(!isset($_SESSION['google_login']));
    error_log(!isset($err_lastname));
    error_log(!isset($err_firstname));
    error_log(!isset($err_username));
    error_log(!isset($err_email));
    error_log(!isset($err_password));
    error_log(!isset($err_verifypassword));
    error_log(!isset($err_lab));
    error_log(!isset($err_institute));
    
  if(!isset($_SESSION['google_login']) && !isset($err_lastname) && !isset($err_firstname) && !isset($err_username) && !isset($err_email) && !isset($err_password) && !isset($err_verifypassword) && !isset($err_lab) && !isset($err_institute)){
      error_log("kkkoooo1");
	//	Calc pass hash
	$pass_hash=hash('md5', $password_val . SALT) . hash('sha256', $password_val . PEPPER);
	$verify=hash('md5', $email_val . VERIFY);
	//	Add new user to the database
	$insert_user = $query->runSQL("
    INSERT INTO users
	( `username`, `name`, `email`, `institute`, `lab`, `pass_hash`, `verification`, `memberdate`,
    `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user`,  `role`)
	VALUES
	( '$username_val', '$fullname_space', '".strtolower($email_val)."', '$institute_val',
    '$lab_val', '$pass_hash', '".$verify."', NOW(), 1, 1, 3, NOW(), NOW(), 1, 'user' )");
    sendMailAdmin($fullname_space, $firstname_val, $lastname_val, $username_val, $institute_val, $email_val, $lab_val, $verify, $query);  
	session_destroy();
	require_once("newuserverification.php");
	exit;
  } else if (isset($_SESSION['google_login']) && $_SESSION['google_login'] == true && isset($_SESSION['email']) && $_SESSION['email'] != ""  && !isset($err_lastname) && !isset($err_firstname) && !isset($err_username) && !isset($err_lab) && !isset($err_institute)){
      error_log("kkkoooo2");
      
    $email = $_SESSION['email'];
    $checkUserData = json_decode($query->getUserByEmail($email));
    $id = isset($checkUserData[0]) ? $checkUserData[0]->{'id'} : "";
	$verify=hash('md5', $email . VERIFY);
    $query->updateUser($id, $fullname_space, $username_val, $institute_val, $lab_val, $verify);
    sendMailAdmin($fullname_space, $firstname_val, $lastname_val, $username_val, $institute_val, $email, $lab_val, $verify,$query);
    session_destroy();
	require_once("newuserverification.php");
	exit;  
  } else {
      error_log("kkkoooo3");
      
      $google_login = isset($_SESSION['google_login']) ? $_SESSION['google_login'] : "";
      $email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
      session_destroy();
      session_start();
      if ($google_login == true && $email != ""){
          $_SESSION['google_login'] = true;
          $_SESSION['email'] = $email;
      } 
      session_write_close();
      require_once("newuserform.php");
      $e="Login Failed.";
      exit;
  }
}
?>