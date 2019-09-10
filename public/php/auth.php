<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
require_once(__DIR__."/../ajax/dbfuncs.php");
$db=new dbfuncs();
if (strpos(getcwd(), "travis/build") == 6){
    $_SESSION['email'] = 'travis';
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
        require_once("loginform.php");
        exit;
    }
}
if(isset($_GET['p']) && $_GET['p'] == "verify" ){
    require_once("adminverify.php");
    exit;
}
if (!isset($_SESSION['username']) || $_SESSION['username'] == ""){
    if(isset($_POST['ok'])){
        session_destroy();
        require_once("loginform.php");
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
    require_once("loginform.php");
} else if(isset($_SESSION['google_login']) && $_SESSION['google_login'] == true){
    require_once("login.php");
    exit;
} else if (isset($_SESSION['username']) && $_SESSION['username'] != ""){
    require_once("main.php");
}
