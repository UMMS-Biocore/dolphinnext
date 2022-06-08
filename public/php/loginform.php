<!DOCTYPE html>
<html>

<head class="bg-gray">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo COMPANY_NAME ?> DolphinNext</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!--   app’s client ID prodcued in the Google Developers Console-->
    <meta name="google-signin-client_id" content="1051324819082-6mjdouf9dhmhv9ov5vvdkdknqrb8tont.apps.googleusercontent.com">
    <!--    google icon-->
    <link rel="icon" type="image/png" href="https://www.w3.org/2000/svg">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">

    <!-- Ionicons -->
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <link href="css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <!-- AdminLTE Skins. Choose a skin from the css/skins
folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <!--  w3 fonts-->
    <link rel="stylesheet" type="text/css" href="css/w3.css">
    <!-- Google Font -->
    <link rel="stylesheet" type="text/css" href="css/googleApiSourceSans.css" />
    <style>
        /* Input border fix */

        .form-control {
            border: 1px solid #B7BFC6 !important
        }
    </style>
</head>

<!-- Theme style -->

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->

<body style="background:linear-gradient(to top right, #7474BF 10%, #5c7cc2 55%, #348AC7 100%);">
    <span id="basepathinfo" basepath="<?php echo BASE_PATH ?>" pubweb="<?php echo PUBWEB_URL ?>" debrowser="<?php echo DEBROWSER_URL ?>" ocpupubweb="<?php echo OCPU_PUBWEB_URL ?>" sso_login="<?php echo SSO_LOGIN ?>" sso_url="<?php echo SSO_URL ?>" client_id="<?php echo CLIENT_ID ?>" google_client_id="<?php echo GOOGLE_CLIENT_ID ?>"></span>
    <div name="empty_space" style="height:10%; width:100%; clear:both;"></div>
    <div class="form-box" id="login-box" style="width:470px;">
        <h1 class="text-center" style="padding-bottom:20px;"><span class="logo-lg" style="color:white; font-size:30px;"><b><?php echo COMPANY_NAME ?></b> DolphinNext</span></h1>
        <div class="body bg-white" style=" border-radius:5px; padding:30px;">
            <form action="<?php echo BASE_PATH ?>/" method="post">
                <div style="margin:auto; height:80px; padding-top:20px;">
                    <h2 class="text-center">Log In</h2>
                </div>
                <?php
                if (ALLOW_SIGNUPGOOGLE != false || !empty(SSO_LOGIN)) {
                    echo '<div style="margin:auto; width:50%;  height:120px; padding-top:20px;">';
                    if (ALLOW_SIGNUPGOOGLE != false) {
                        echo '<div id="googleSignIn"></div> ';
                    }
                    if (!empty(SSO_LOGIN)) {
                        $SSO_LOGIN_URL = "";
                        if (!empty(SSO_LOGIN) && !empty(ISSUER) && !empty(CLIENT_ID) && !empty(CLIENT_SECRET) && !empty(OKTA_METHOD) && OKTA_METHOD == "OIDC") {
                            $state = 'applicationState';
                            $query = http_build_query([
                                'client_id' => $CLIENT_ID,
                                'response_type' => 'code',
                                'response_mode' => 'query',
                                'scope' => 'openid profile',
                                'redirect_uri' => BASE_PATH . '/okta/authorization-code-callback.php',
                                'state' => $state
                            ]);
                            $SSO_LOGIN_URL = ISSUER . '/v1/authorize?' . $query;
                        } else if (!empty(SSO_LOGIN) && !empty(OKTA_METHOD) && OKTA_METHOD == "SAML") {
                            $SSO_LOGIN_URL = BASE_PATH . '?saml_sso=okta-app';
                        } else if (!empty(SSO_LOGIN) && !empty(SSO_URL) && !empty(CLIENT_ID) && !empty(CLIENT_SECRET)) {
                            $SSO_LOGIN_URL = SSO_URL . "/dialog/authorize?redirect_uri=" . BASE_PATH . "/php/receivetoken.php&response_type=code&client_id=" . CLIENT_ID . "&scope=offline_access";
                        }
                        echo '<a style="width:199px; margin-top:10px;" id="ssosigninbtn" href="' . $SSO_LOGIN_URL . '" class="btn btn-default"><i style="padding-right:25px;" class="fa fa-lock" aria-hidden="true"></i> Sign In with SSO </a>';
                    }
                    echo '</div>';
                }
                if ((ALLOW_SIGNUPGOOGLE != false || !empty(SSO_LOGIN)) && !empty(PASSWORD_LOGIN)) {
                    echo '<div style="width: 100%; height: 16px; border-bottom: 1px solid #E0E6E8; text-align: center"><span style="font-size: 18px; background-color: white; padding: 0 7px; color:#B7BFC6;"> or </span></div>';
                }
                if (PASSWORD_LOGIN != false) {
                    echo '<div class="form-group" style="margin-top:20px;">
                        <input name="emailusername" class="form-control" placeholder="E-mail/Username" />
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" />
                    </div>';
                }
                if (isset($loginfail)) echo  $loginfail;
                if (PASSWORD_LOGIN != false) {
                    echo '<div class="footer">
                        <button type="submit" name="login" class="btn btn-primary" style="float:right;">Login</button>
                    </div>';
                }
                ?>

            </form>


            <form action="<?php echo BASE_PATH ?>/" method="post">
                <?php
                if (ALLOW_SIGNUP != false) {
                    echo '<div class="text-center" style="margin-top:30%;">Don\'t have an account <button type="submit" name="signup" class="btn btn-default" style="margin-left:10px;">Sign Up</button></div>';
                } else {
                    echo '<div class="text-center" style="margin-top:15%;"></div>';
                }
                ?>
            </form>
        </div>




    </div>
    <!--Google client library for Google Sign-In-->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="bower_components/jwt-decode/jwt-decode.js" type="text/javascript"></script>
    <script src="js/googlesign.js" type="text/javascript"></script>

</body>

</html>