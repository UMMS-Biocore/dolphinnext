<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
require_once(__DIR__."/../ajax/dbfuncs.php");
require_once(__DIR__."/jwt.php");
$db=new dbfuncs();

if (strpos(getcwd(), "travis/build") == 6){
    $_SESSION['email'] = 'travis';
}
$SSO_LOGIN=SSO_LOGIN;
$SSO_URL=SSO_URL;
$BASE_PATH=BASE_PATH;
$CLIENT_ID=CLIENT_ID;
$SHOW_HOMEPAGE=SHOW_HOMEPAGE;

function loadLoginForm($SSO_LOGIN, $SSO_URL, $BASE_PATH, $CLIENT_ID){
    // temporary change for sso login
    //    if (!empty($SSO_LOGIN) && !empty($SSO_URL) && !empty($CLIENT_ID)) {
    //        $SSO_LOGIN_URL = "{$SSO_URL}/dialog/authorize?redirect_uri={$BASE_PATH}/php/receivetoken.php?response_type=code&client_id={$CLIENT_ID}&scope=offline_access";
    //        header('Location: '.$SSO_LOGIN_URL);
    //    } else {
    //        require_once("loginform.php");
    //    }
    require_once("loginform.php");
}

if (isset($_GET['p']) && $_GET['p'] == "logout" ){
    if (isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];
        $userData = json_decode($db->getUserById($admin_id))[0];
        $username = $userData->{'username'};
        $email = $userData->{'email'};
        $name = $userData->{'name'};
        $role = $userData->{'role'};
        session_destroy();
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['ownerID'] = $admin_id;
        $_SESSION['role'] = $role;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    } else {
        session_destroy();
        setcookie('jwt-dolphinnext', "loggedout", time()-60*60, "/");
        if (!empty($SSO_LOGIN) && !empty($SSO_URL)){
            $SSO_LOGOUT_URL = "{$SSO_URL}/api/v1/users/logout?redirect_uri={$BASE_PATH}";
            header('Location: ' . $SSO_LOGOUT_URL);
            exit;
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;  
        }
    }
}
if (isset($_GET['p']) && $_GET['p'] == "login" ){
    loadLoginForm($SSO_LOGIN, $SSO_URL, $BASE_PATH, $CLIENT_ID);
    exit;
}


if(isset($_GET['p']) && $_GET['p'] == "verify" ){
    require_once("adminverify.php");
    exit;
}
if (!isset($_SESSION['username']) || $_SESSION['username'] == ""){
    if(isset($_POST['ok'])){
        session_destroy();
        loadLoginForm($SSO_LOGIN, $SSO_URL, $BASE_PATH, $CLIENT_ID);
        exit;
    }
    if (!isset($_POST['request']) && isset($_SESSION['google_login']) && $_SESSION['google_login'] != ""){
        require_once("newuserform.php");
        exit;
    }
    if(isset($_POST['signup'])){
        session_destroy();
        require_once("newuserform.php");
        exit;
    }    
    if(isset($_POST['request'])){
        require_once("newuseranswer.php");
        exit;
    }
    if(isset($_POST['login'])){
        require_once("login.php");
        exit;
    }

    $token = $_COOKIE['jwt-dolphinnext'];
    if (!empty($token) && $token != 'loggedout'){
        $JWT=new JWT();
        $decoded = "";
        try {
            $decoded = $JWT->decode($token, JWT_SECRET);
        } catch (Exception $e) {
            error_log ("token not valid: $token");
        }
        if (!empty($decoded) && !empty($decoded->{"id"})){
            $currentUser = json_decode($db->getUserById($decoded->{"id"}),true);
            if (!empty($currentUser[0]) && !empty($currentUser[0]['id'])){
                $id = $currentUser[0]['id'];
                $role = isset($currentUser[0]) ? $currentUser[0]['role'] : "";
                $name = isset($currentUser[0]) ? $currentUser[0]['name'] : "";
                $email = isset($currentUser[0]) ? $currentUser[0]['email'] : "";
                $username = isset($currentUser[0]) ? $currentUser[0]['username'] : "";
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $username;
                $_SESSION['name'] = $name;
                $_SESSION['ownerID'] = $id;
                $_SESSION['role'] = $role;
                require_once("main.php");
                exit;
            }
        }
    }
    // empty($_SERVER['HTTP_REFERER']) required since php may load page more than once.
    // when reload happens, $_SERVER['HTTP_REFERER'] will be set.
    if (!empty($SSO_LOGIN) && !empty($SSO_URL) && !empty($CLIENT_ID) && empty($_SERVER['HTTP_REFERER'])){
        error_log("ssoLoginCheck: ".$_SESSION["ssoLoginCheck"]);
        error_log("HTTP_REFERER: ".$_SERVER["HTTP_REFERER"]);
        if (!empty($token) && $token != 'loggedout') {
            $tokenInfo = json_decode($db->getSSOAccessToken($token),true);
            if (!empty($tokenInfo[0]) &&  $tokenInfo[0]["expirationDate"] && date("Y-m-d H:i:s") < date($tokenInfo[0]["expirationDate"])){
                if (!empty($tokenInfo[0]["sso_user_id"])){
                    $currentUser = json_decode($db->getUserBySSOId($tokenInfo[0]["sso_user_id"]),true);
                    if (!empty($currentUser[0]) && !empty($currentUser[0]['id'])){
                        $id = $currentUser[0]['id'];
                        $role = isset($currentUser[0]) ? $currentUser[0]['role'] : "";
                        $name = isset($currentUser[0]) ? $currentUser[0]['name'] : "";
                        $email = isset($currentUser[0]) ? $currentUser[0]['email'] : "";
                        $username = isset($currentUser[0]) ? $currentUser[0]['username'] : "";
                        $_SESSION['email'] = $email;
                        $_SESSION['username'] = $username;
                        $_SESSION['name'] = $name;
                        $_SESSION['ownerID'] = $id;
                        $_SESSION['role'] = $role;
                    }
                }
            }
        } else if (empty($_SESSION["ssoLoginCheck"])){
            // check if its authenticated on Auth server
            $_SESSION["ssoLoginCheck"] = true;
            $originalUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            error_log($originalUrl);
            $_SESSION["redirect_original"] = $originalUrl;
            $SSO_LOGIN_CHECK = "{$SSO_URL}/api/v1/oauth/check?redirect_original={$originalUrl}&redirect_uri={$BASE_PATH}/php/receivetoken.php&response_type=code&client_id={$CLIENT_ID}&scope=offline_access";
            error_log($SSO_LOGIN_CHECK);
            header('Location: '.$SSO_LOGIN_CHECK);
            exit;
        } 
        else if (!empty($_SESSION["ssoLoginCheck"])){
            $_SESSION["ssoLoginCheck"] = false;
        }
    }
    // user not signed in - public view:
    if ($SHOW_HOMEPAGE == "1"){
        require_once("main.php");
    } else {
        require_once("loginform.php");
    }
    exit;
} else if(isset($_SESSION['google_login']) && $_SESSION['google_login'] == true){
    require_once("login.php");
    exit;
} else if (isset($_SESSION['username']) && $_SESSION['username'] != ""){
    require_once("main.php");
}
