<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
require_once(__DIR__ . "/../ajax/dbfuncs.php");
require_once(__DIR__ . "/jwt.php");

$db = new dbfuncs();

if (strpos(getcwd(), "travis/build") == 6) {
    $_SESSION['email'] = 'travis';
}
$SSO_LOGIN = SSO_LOGIN;
$SSO_URL = SSO_URL;
$BASE_PATH = BASE_PATH;
$CLIENT_ID = CLIENT_ID;
$CLIENT_SECRET = CLIENT_SECRET;
$ISSUER = ISSUER;
$SHOW_HOMEPAGE = SHOW_HOMEPAGE;
$OKTA_API_TOKEN = OKTA_API_TOKEN;
$OKTA_METHOD = OKTA_METHOD;
$OKTA_METADATA = OKTA_METADATA;

// for okta login
if (!empty($SSO_LOGIN) && !empty($ISSUER) && $OKTA_METHOD == "OIDC") {
    require_once(__DIR__ . "/../okta/authcode.php");
} else if (!empty($SSO_LOGIN) && !empty($OKTA_METADATA) && $OKTA_METHOD == "SAML") {
    require_once(__DIR__ . "/../../simplesamlphp/lib/_autoload.php");
}


function loadLoginForm($SSO_LOGIN, $SSO_URL, $BASE_PATH, $CLIENT_ID)
{
    // temporary change for sso login
    //    if (!empty($SSO_LOGIN) && !empty($SSO_URL) && !empty($CLIENT_ID)) {
    //        $SSO_LOGIN_URL = "{$SSO_URL}/dialog/authorize?redirect_uri={$BASE_PATH}/php/receivetoken.php?response_type=code&client_id={$CLIENT_ID}&scope=offline_access";
    //        header('Location: '.$SSO_LOGIN_URL);
    //    } else {
    //        require_once("loginform.php");
    //    }
    require_once("loginform.php");
}


if (isset($_GET['p']) && $_GET['p'] == "logout") {
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
        // If the user is logged in and requesting a logout.
        session_destroy();
        setcookie('jwt-dolphinnext', "loggedout", time() - 60 * 60, "/");
        if (!empty($SSO_LOGIN) && !empty($SSO_URL)) {
            $SSO_LOGOUT_URL = "{$SSO_URL}/api/v1/users/logout?redirect_uri={$BASE_PATH}";
            header('Location: ' . $SSO_LOGOUT_URL);
            exit;
        } else if (!empty($OKTA_API_TOKEN) && !empty($SSO_LOGIN) && !empty($ISSUER) && !empty($_SESSION['ownerID']) && $OKTA_METHOD == "OIDC") {
            $ownerID = $_SESSION['ownerID'];
            $userData = json_decode($db->getUserById($ownerID))[0];
            $sso_id = $userData->{'sso_id'};
            $parse = parse_url($ISSUER);
            $oktaDomain = $parse['host'];
            $url = "https://" . $oktaDomain . "/api/v1/users/$sso_id/sessions";
            $headers = [
                'Authorization: SSWS ' . $OKTA_API_TOKEN,
                'Accept: application/json',
                'Content-Type: application/json'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $result = json_decode($result);
            curl_close($ch);
            header('Location: ' . $BASE_PATH);
            exit;
        } else if (!empty($SSO_LOGIN) && $OKTA_METHOD == "SAML" && !empty($OKTA_METADATA)) {
            $sp = "okta-app";
            //unset($_SESSION['saml_session']);
            $as = new SimpleSAML_Auth_Simple($sp);
            $as->logout(["ReturnTo" => $BASE_PATH]);
        } else {
            header('Location: ' . $BASE_PATH);
            exit;
        }
    }
}
if (isset($_GET['p']) && $_GET['p'] == "login") {
    loadLoginForm($SSO_LOGIN, $SSO_URL, $BASE_PATH, $CLIENT_ID);
    exit;
}


if (isset($_GET['p']) && $_GET['p'] == "verify") {
    require_once("adminverify.php");
    exit;
}
if (!isset($_SESSION['username']) || $_SESSION['username'] == "") {
    if (isset($_POST['ok'])) {
        session_destroy();
        loadLoginForm($SSO_LOGIN, $SSO_URL, $BASE_PATH, $CLIENT_ID);
        exit;
    }
    if (!isset($_POST['request']) && isset($_SESSION['google_login']) && $_SESSION['google_login'] != "") {
        require_once("newuserform.php");
        exit;
    }
    if (isset($_POST['signup'])) {
        session_destroy();
        require_once("newuserform.php");
        exit;
    }
    if (isset($_POST['request'])) {
        require_once("newuseranswer.php");
        exit;
    }
    if (isset($_POST['login'])) {
        require_once("login.php");
        exit;
    }
    $token = !empty($_COOKIE['jwt-dolphinnext']) ? $_COOKIE['jwt-dolphinnext'] : "";
    if (isset($_REQUEST["saml_sso"]) && (empty($token) || $token === 'loggedout')) {
        $sp = $_REQUEST["saml_sso"]; //okta-app
        $as = new SimpleSAML_Auth_Simple($sp);
        $as->requireAuth();
        $user = array(
            'sp'         => $sp,
            'authed'     => $as->isAuthenticated(),
            'idp'        => $as->getAuthData('saml:sp:IdP'),
            'nameId'     => $as->getAuthData('saml:sp:NameID')->getValue(),
            'attributes' => $as->getAttributes(),
        );
        error_log(print_r($user, TRUE));
        $_SESSION['saml_session'] = $user;
        $email = $user['nameId'];
        $fullname = "";
        if (isset($user['attributes']['FirstName']) && isset($user['attributes']['FirstName'][0]) && isset($user['attributes']['LastName']) && isset($user['attributes']['LastName'][0])) {
            $fullname = $user['attributes']['FirstName'][0] . " " . $user['attributes']['LastName'][0];
        }

        // if user isAuthenticated then checkuser and insert if necessary
        if ($user['authed'] === true && $email) {
            $checkUserData = json_decode($db->getUserByEmailorUsername($email));
            $id = isset($checkUserData[0]) ? $checkUserData[0]->{'id'} : "";

            $parts = explode("@", $email);
            $username = $parts[0];
            $name = !empty($fullname) ? $fullname : $parts[0];
            $role = isset($checkUserData[0]) ? $checkUserData[0]->{'role'} : "";

            if (empty($id)) {
                // insert user
                // Update db with latest information
                $email_clean = str_replace("'", "''", $email);
                $any_user_check = $db->queryAVal("SELECT id FROM users");
                $any_user_checkAr = json_decode($any_user_check, true);
                $role = "user";
                if (empty($any_user_checkAr)) {
                    $role = "admin";
                }
                $active = 1;
                $sql = "INSERT INTO users(name, email, username, role, active, memberdate, date_created, date_modified, perms, sso_id, scope) VALUES ('$name', '$email_clean', '$username', '$role', $active, now(),now(), now(), '3', '$sso_user_id', '$scope')";
                $inUser = $db->insTable($sql);
                $idArray = json_decode($inUser, true);
                $id = $idArray["id"];
                $db->insertDefaultGroup($id);
                $db->insertDefaultRunEnvironment($id);
            }

            if (!empty($id)) {
                $currentUser = json_decode($db->getUserById($id), true);
                $role = isset($currentUser[0]) ? $currentUser[0]['role'] : "";
                $name = isset($currentUser[0]) ? $currentUser[0]['name'] : "";
                $email = isset($currentUser[0]) ? $currentUser[0]['email'] : "";
                $username = isset($currentUser[0]) ? $currentUser[0]['username'] : "";
                $_SESSION['email'] = $email;
                $_SESSION['username'] = $username;
                $_SESSION['name'] = $name;
                $_SESSION['ownerID'] = $id;
                $_SESSION['role'] = $role;
                // create cookie
                $token = $db->signJWTToken($id);
                if (!empty($token)) {
                    setcookie('jwt-dolphinnext', $token, time() + 60 * 60 * 24 * 365, "/");
                    if (!empty($SSO_LOGIN) && $SHOW_HOMEPAGE == "1") {
                        header('Location: ' . $BASE_PATH . "/php/after-sso.php");
                    } else {
                        header('Location: ' . $BASE_PATH);
                    }
                    exit;
                }
            }
        }
    }

    if (!empty($token) && $token != 'loggedout') {
        $decoded = "";
        $currentUser = array();
        if (empty($ISSUER) || (!empty($SSO_LOGIN) && $OKTA_METHOD == "SAML")) {
            $JWT = new JWT();
            try {
                $decoded = $JWT->decode($token, JWT_SECRET);
            } catch (Exception $e) {
                error_log("token not valid: $token");
            }
            if (!empty($decoded) && !empty($decoded->{"id"})) {
                $currentUser = json_decode($db->getUserById($decoded->{"id"}), true);
            }
        } else if (!empty($SSO_LOGIN) && !empty($ISSUER) && $OKTA_METHOD == "OIDC") {
            // for okta login
            $OKTAAUTH = new AuthCode();
            try {
                $decoded = $OKTAAUTH->getProfile($token);
            } catch (Exception $e) {
                error_log("token not valid: $token");
            }
            if (!empty($decoded) && !empty($decoded["uid"])) {
                $currentUser = json_decode($db->getUserById($decoded["uid"]), true);
            }
        }
        if (!empty($currentUser[0]) && !empty($currentUser[0]['id'])) {
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
    // For DSSO Authorization Server:
    // empty($_SERVER['HTTP_REFERER']) required since php may load page more than once.
    // when reload happens, $_SERVER['HTTP_REFERER'] will be set.

    if (!empty($SSO_LOGIN) && !empty($SSO_URL) && !empty($CLIENT_ID) && empty($_SERVER['HTTP_REFERER'])) {
        //        error_log("ssoLoginCheck: ".$_SESSION["ssoLoginCheck"]);
        //        error_log("HTTP_REFERER: ".$_SERVER["HTTP_REFERER"]);
        if (!empty($token) && $token != 'loggedout') {
            $tokenInfo = json_decode($db->getSSOAccessToken($token), true);
            if (!empty($tokenInfo[0]) &&  $tokenInfo[0]["expirationDate"] && date("Y-m-d H:i:s") < date($tokenInfo[0]["expirationDate"])) {
                if (!empty($tokenInfo[0]["sso_user_id"])) {
                    $currentUser = json_decode($db->getUserBySSOId($tokenInfo[0]["sso_user_id"]), true);
                    if (!empty($currentUser[0]) && !empty($currentUser[0]['id'])) {
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
        } else if (empty($_SESSION["ssoLoginCheck"])) {
            // check if its authenticated on Auth server
            $_SESSION["ssoLoginCheck"] = true;
            //            $originalUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $_SESSION["redirect_original"] = $BASE_PATH;
            $SSO_LOGIN_CHECK = "{$SSO_URL}/api/v1/oauth/check?redirect_original={$BASE_PATH}&redirect_uri={$BASE_PATH}/php/receivetoken.php&response_type=code&client_id={$CLIENT_ID}&scope=offline_access";
            error_log($SSO_LOGIN_CHECK);
            header('Location: ' . $SSO_LOGIN_CHECK);
            exit;
        } else if (!empty($_SESSION["ssoLoginCheck"])) {
            $_SESSION["ssoLoginCheck"] = false;
        }
    }

    // user not signed in - public view:
    if ($SHOW_HOMEPAGE == "1") {
        require_once("main.php");
    } else {
        require_once("loginform.php");
    }
    exit;
} else if (isset($_SESSION['google_login']) && $_SESSION['google_login'] == true) {
    require_once("login.php");
    exit;
} else if (isset($_SESSION['username']) && $_SESSION['username'] != "") {
    require_once("main.php");
}
