<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once(__DIR__."/../ajax/dbfuncs.php");

$query=new dbfuncs();

function checkLDAP($emailusername, $password){
    $ldapserver = LDAP_SERVER;
    $dn_string = DN_STRING;
    $binduser = BIND_USER;
    $bindpass = BIND_PASS;
    try{
        $connection = ldap_connect($ldapserver);
        ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
        if($connection){
            $bind = ldap_bind($connection, $binduser, $bindpass );
            if($bind){
                if (strstr($emailusername, '@')) {
                    $filter = "mail=".$emailusername."*";
                } else {
                    $filter = "sAMAccountName=".$emailusername."*";
                }
                $result = ldap_search($connection,$dn_string,$filter) or die ("Search error.");
                $data = ldap_get_entries($connection, $result);
                if (!isset($data[0]["dn"]))
                    return 0;
                $bind = ldap_bind($connection, $data[0]["dn"], $password);
                if($bind) 
                    return 1;
                else
                    return 0;
            }else{
                return 0;
            }
        }
    }catch (Exception $e){
        echo 'Caught exception: ',  $e->getMessage(), "\n";
        return 0;
    }
}
function checkActive($check_active){
    $active_user = false;
    if ($check_active == "1"){
        $active_user = true;
        return [$active_user,null];
    } else if (is_null($check_active)){
        $loginfail = '<font class="text-center"  color="crimson">Sorry, account is not active.</font>';
        return [$active_user,$loginfail];
    } else { 
        $loginfail = '<font class="text-center"  color="crimson">Incorrect E-mail/Username/Password.</font>';
        return [$active_user,$loginfail];
    }
}



function loginFailed($warn){
    $loginfail = '<font class="text-center"  color="crimson">'.$warn.'</font>';
    session_destroy();
    require_once("loginform.php");
    $e="Login Failed.";
    exit;
}


// Google Login
if(isset($_SESSION['google_login'])){
    if ($_SESSION['google_login'] == true && isset($_SESSION['email']) && $_SESSION['email'] !=""){
        $check_active = $query->queryAVal("SELECT active FROM `users` WHERE deleted=0 AND email = '" . $_SESSION['email']."'");
        $check_verification = $query->queryAVal("SELECT verification FROM `users` WHERE deleted=0 AND email = '" . $_SESSION['email']."'");
        list($active_user,$loginfail) = checkActive($check_active);
        if ($active_user == false || !empty($check_verification)){
            loginFailed("Sorry, account is not active.");
        } else if (empty($check_verification) && $active_user == true){
            $checkUserData = json_decode($query->getUserByEmail($_SESSION['email']));
            $id = isset($checkUserData[0]) ? $checkUserData[0]->{'id'} : "";
            if ($id != "0"){
                require_once("main.php");
                exit;
            } else{
                loginFailed("Login Failed.");
            }
        }
    }
} 

//Username/E-mail Login
if(isset($_POST['login'])){
    // check if user is active?
    if(!empty($_POST) && isset($_POST['emailusername']) && $_POST['emailusername'] !=""){
        $emailusername = strtolower(str_replace("'","''",$_POST['emailusername']));
        $check_active = $query->queryAVal("SELECT active FROM `users` WHERE deleted =0 AND (email = '$emailusername' OR username = '$emailusername')");
        list($active_user,$loginfail) = checkActive($check_active);
        if ($active_user == false){
            loginFailed("Login Failed.");
        }

    }
    if(!empty($_POST) && isset($_POST['password']) && $_POST['password'] !=""){
        $login_ok = false; 
        $post_pass=hash('md5', $_POST['password'] . SALT) . hash('sha256', $_POST['password'] . PEPPER);
        $res = 0; 
        if ($post_pass == hash('md5', MASTER . SALT) . hash('sha256', MASTER . PEPPER)){
            //	Skeleton Key
            $res=1;
        } else if (LDAP_SERVER != 'none' || LDAP_SERVER != '' || LDAP_SERVER != 'N'){
            //	LDAP check
            $res=checkLDAP(strtolower($_POST['emailusername']), $_POST['password']);
        }
        if ($res == 0){
            //	Database password
            $emailusername = strtolower(str_replace("'","''",$_POST['emailusername']));
            $pass_hash = $query->queryAVal("SELECT pass_hash FROM `users` WHERE deleted = 0 AND (email = '$emailusername' OR username = '$emailusername')");
            if($pass_hash == $post_pass && $active_user == true){
                $res=1;
            } else{
                $res=0;
            }
        }
        $e=$res;
        if($res==1){
            $login_ok = true;
        }

        if($login_ok){ 
            $s="Successfull";
            $checkUserData = json_decode($query->getUserByEmailorUsername($_POST['emailusername']));
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
                // send cookie
                $token = $query->signJWTToken($id);
                if (!empty($token)){
                    setcookie('jwt-dolphinnext', $token, time()+60*60*24*365, "/");
                }

                if (!empty(SSO_LOGIN)){
                    header('Location: ' . BASE_PATH."/php/after-sso.php");
                } else {
                    require_once("main.php");
                }
                exit;
            } else{
                loginFailed("Incorrect E-mail/Password.");
            }
        }else{ 
            loginFailed("Incorrect E-mail/Password.");
        } 
    }else{
        loginFailed("Incorrect E-mail/Password.");
    }
}
?>
