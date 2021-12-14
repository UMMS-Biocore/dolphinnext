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
        $username_check = $query->queryAVal("SELECT id FROM `users` WHERE deleted = 0 AND username = LCASE('" . $_POST['username'] . "')");
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
            $email_check = $query->queryAVal("SELECT id FROM `users` WHERE deleted = 0 AND email = LCASE('" . $_POST['email'] . "')");
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

    if(!isset($_SESSION['google_login']) && !isset($err_lastname) && !isset($err_firstname) && !isset($err_username) && !isset($err_email) && !isset($err_password) && !isset($err_verifypassword) && !isset($err_lab) && !isset($err_institute)){
        //	Calc pass hash
        $pass_hash=hash('md5', $password_val . SALT) . hash('sha256', $password_val . PEPPER);
        $verify=hash('md5', $email_val . VERIFY);
        //check if user table is empty: then insert as admin
        $google_id = NULL;
        $role = "user";
        $active = 0;
        $logintype = NULL;
        $admin_check = $query->queryTable("SELECT id FROM `users`");
        $admin_checkAr = json_decode($admin_check,true); 
        if (empty($admin_checkAr)){
            $role = "admin";
            $active = 1;
            $insert_user = $query->insertUserManual($fullname_space, strtolower($email_val), $username_val, $institute_val, $lab_val, $logintype, $role, $active, $pass_hash, $verify, $google_id); 
            $ownerIDarr = json_decode($insert_user,true); 
            $user_id = $ownerIDarr["id"];
            $query->insertDefaultGroup($user_id); 
            $query->insertDefaultRunEnvironment($user_id); 
            $queryEmail = strtolower(str_replace("'","''",$email_val));
            $checkUserData = json_decode($query->getUserByEmail($queryEmail));
            //check if user exits
            $id = isset($checkUserData[0]) ? $checkUserData[0]->{'id'} : "";
            if (!empty($id)){
                $role = isset($checkUserData[0]) ? $checkUserData[0]->{'role'} : "";
                $name = isset($checkUserData[0]) ? $checkUserData[0]->{'name'} : "";
                $email = isset($checkUserData[0]) ? $checkUserData[0]->{'email'} : "";
                $username = isset($checkUserData[0]) ? $checkUserData[0]->{'username'} : "";
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $username;
                $_SESSION['name'] = $name;
                $_SESSION['ownerID'] = $id;
                $_SESSION['role'] = $role;
                require_once("main.php");
            }
        } else {
            //	Add new user to the database
            $insert_user = $query->insertUserManual($fullname_space, strtolower($email_val), $username_val, $institute_val, $lab_val, $logintype, $role, $active, $pass_hash, $verify, $google_id); 
            $ownerIDarr = json_decode($insert_user,true); 
            $user_id = $ownerIDarr["id"];
            $query->insertDefaultGroup($user_id); 
            $query->insertDefaultRunEnvironment($user_id); 
            sendMailAdmin($fullname_space, $firstname_val, $lastname_val, $username_val, $institute_val, $email_val, $lab_val, $verify, $query);  
            session_destroy();
            require_once("newuserverification.php"); 
        }
        exit;
    } else if (isset($_SESSION['google_login']) && $_SESSION['google_login'] == true && isset($_SESSION['email']) && $_SESSION['email'] != ""  && !isset($err_lastname) && !isset($err_firstname) && !isset($err_username) && !isset($err_lab) && !isset($err_institute)){
        $email = $_SESSION['email'];
        $google_id = $_SESSION['google_id'];
        $admin_check = $query->queryTable("SELECT id FROM `users`");
        $admin_checkAr = json_decode($admin_check,true); 
        //check if user table is empty: then insert as admin  
        if (empty($admin_checkAr)){
            $role = "admin";
            $active = 1;
            $logintype = NULL;
            $verify = NULL;
            $pass_hash = NULL;
            $insert_user = $query->insertUserManual($fullname_space, strtolower($email), $username_val, $institute_val, $lab_val, $logintype, $role, $active, $pass_hash, $verify, $google_id); 
            $ownerIDarr = json_decode($insert_user,true); 
            $user_id = $ownerIDarr["id"];
            $query->insertDefaultGroup($user_id); 
            $query->insertDefaultRunEnvironment($user_id); 
            $queryEmail = strtolower(str_replace("'","''",$email));
            $checkUserData = json_decode($query->getUserByEmail($queryEmail));
            //check if user exits
            $id = isset($checkUserData[0]) ? $checkUserData[0]->{'id'} : "";
            if (!empty($id)){
                $role = isset($checkUserData[0]) ? $checkUserData[0]->{'role'} : "";
                $name = isset($checkUserData[0]) ? $checkUserData[0]->{'name'} : "";
                $email = isset($checkUserData[0]) ? $checkUserData[0]->{'email'} : "";
                $username = isset($checkUserData[0]) ? $checkUserData[0]->{'username'} : "";
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $username;
                $_SESSION['name'] = $name;
                $_SESSION['ownerID'] = $id;
                $_SESSION['role'] = $role;
                require_once("main.php");
            }
        } else {
            $role = "user";
            $active = 0;
            $logintype = NULL;
            $verify=hash('md5', $email . VERIFY);
            $pass_hash = NULL;
            $insert_user = $query->insertUserManual($fullname_space, strtolower($email), $username_val, $institute_val, $lab_val, $logintype, $role, $active, $pass_hash, $verify, $google_id); 
            $ownerIDarr = json_decode($insert_user,true); 
            $user_id = $ownerIDarr["id"];
            $query->insertDefaultGroup($user_id); 
            $query->insertDefaultRunEnvironment($user_id); 
            sendMailAdmin($fullname_space, $firstname_val, $lastname_val, $username_val, $institute_val, $email, $lab_val, $verify,$query);
            session_destroy();
            require_once("newuserverification.php");
        }
        exit;  
    } else {
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
