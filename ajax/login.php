<?php
error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
    
require_once("../ajax/dbfuncs.php");
$db = new dbfuncs();
$p = isset($_REQUEST["p"]) ? $_REQUEST["p"] : "";
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
if ($p=="saveUser"){
    $google_id = $_REQUEST['google_id'];
    $name = $_REQUEST['name'];
    $email = $_REQUEST['email'];
    $google_image = $_REQUEST['google_image'];
    $username = $_REQUEST['username'];
    //check if Google ID already exits
    $id = json_decode($db->getUser($google_id))[0]->{'id'};
    $_SESSION['username'] = $username;
    $_SESSION['name'] = $name;
    $_SESSION['google_id'] = $google_id;
    if (!empty($id)) {
	    $_SESSION['ownerID'] = $id;
        $data = $db->updateUser($id, $google_id, $name, $email, $google_image, $username);  
        
    } else {
        $data = $db->insertUser($google_id, $name, $email, $google_image, $username);  
        $ownerIDarr = json_decode($data,true); 
        $id = $ownerIDarr['id'];
	    $_SESSION['ownerID'] = $id;
    }
    session_write_close();
} else if ($p=="impersonUser"){
    $user_id = $_REQUEST['user_id'];
    $admin_id =$_SESSION['ownerID'];
    session_destroy();
    session_start();
    $userData = json_decode($db->getUserById($user_id))[0];
    $google_id = $userData->{'google_id'};
    $username = $userData->{'username'};
    $name = $userData->{'name'};
    $_SESSION['username'] = $username;
    $_SESSION['name'] = $name;
    $_SESSION['google_id'] = $google_id;
    $_SESSION['ownerID'] = $user_id;
    $_SESSION['admin_id'] = $admin_id;
    session_write_close();
    $impersonAr = array('imperson' => 1);
	$data = json_encode($impersonAr);
    
} else if ($p=="logOutUser"){
    if (isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];
        $userData = json_decode($db->getUserById($admin_id))[0];
        $google_id = $userData->{'google_id'};
        $username = $userData->{'username'};
        $name = $userData->{'name'};
        session_destroy();
        session_start();
        $_SESSION['username'] = $username;
        $_SESSION['name'] = $name;
        $_SESSION['google_id'] = $google_id;
        $_SESSION['ownerID'] = $admin_id;
        session_write_close();
        $logOutAr = array('logOut' => 1);
	    $data = json_encode($logOutAr);
    } else {
        session_destroy();
        $logOutAr = array('logOut' => 1);
	    $data = json_encode($logOutAr); 
    }

    
} else {
	$errAr = array('error' => 1);
	$data = json_encode($errAr);
}
//header("Location: ./index.php"); 
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
   