<?php
$google_singup = false;
$first_name = "";
$last_name = "";
$name = isset($_SESSION['name']) ? $_SESSION['name'] : "";
$google_login = isset($_SESSION['google_login']) ? $_SESSION['google_login'] : "";
if (!isset($_SESSION['username']) && $google_login != "") {
    $google_singup = true;
    if (strpos($name, " ") !== false) {
        $name = trim($name);
        $parts = explode(" ", $name);
        $last_name = array_pop($parts);
        $first_name = implode(" ", $parts);
    }
}
?>


<!DOCTYPE html>
<html>

<head class="bg-gray">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo COMPANY_NAME ?> DolphinNext Pipeline Builder</title>
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

        .footer {
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
    <div name="empty_space" style="height:10%; width:100%; clear:both;"></div>
    <div class="form-box" id="login-box" style="width:470px;">
        <h1 class="text-center" style="padding-bottom:20px;"><span class="logo-lg" style="color:white; font-size:30px;"><b><?php echo COMPANY_NAME ?></b> DolphinNext</span></h1>
        <form action="<?php echo BASE_PATH ?>/" method="post">
            <div class="body bg-white" style=" border-radius:5px; padding:30px;">
                <div style="margin:auto; height:80px; padding-top:20px;">
                    <h2 class="text-center">
                        <?php
                        if ($google_singup == true) {
                            echo "Sign Up with Google";
                        } else {
                            echo "Sign Up";
                        }
                        ?></h2>
                </div>
                <div class="text-center form-group">
                    <input type="text" name="firstname" class="form-control" placeholder="First name" maxlength="25" value="<?php
                                                                                                                            if (isset($firstname_val)) {
                                                                                                                                echo $firstname_val;
                                                                                                                            } else if ($first_name != "") {
                                                                                                                                echo $first_name;
                                                                                                                            }
                                                                                                                            ?>" />
                    <?php if (isset($err_firstname)) echo $err_firstname; ?>
                </div>
                <div class="text-center form-group">
                    <input type="text" name="lastname" class="form-control" placeholder="Last name" maxlength="20" value="<?php
                                                                                                                            if (isset($lastname_val)) {
                                                                                                                                echo $lastname_val;
                                                                                                                            } else if ($last_name != "") {
                                                                                                                                echo $last_name;
                                                                                                                            }
                                                                                                                            ?>" />
                    <?php if (isset($err_lastname)) echo $err_lastname;  ?>
                </div>
                <div class="text-center form-group">
                    <input type="text" name="username" class="form-control" placeholder="Username" maxlength="45" value="<?php if (isset($username_val)) echo $username_val ?>" />
                    <?php if (isset($err_username)) echo $err_username;  ?>
                </div>
                <?php
                if ($google_singup == false) {
                    echo '<div class="text-center form-group"><input type="text" name="email" class="form-control" maxlength="45" placeholder="Email" value="';
                    if (isset($email_val)) echo $email_val;
                    echo '" />';
                    if (isset($err_email)) echo $err_email;
                    echo '</div>';
                }
                ?>
                <div class="text-center form-group">
                    <input type="text" maxlength="45" name="institute" class="form-control" placeholder="Institute" value="<?php if (isset($institute_val)) echo $institute_val ?>" />
                    <?php if (isset($err_institute)) echo $err_institute;  ?>
                </div>
                <div class="text-center form-group">
                    <input type="text" name="lab" class="form-control" placeholder="Lab/Department" maxlength="45" value="<?php if (isset($lab_val)) echo $lab_val ?>" />
                    <?php if (isset($err_lab)) echo $err_lab ?>
                </div>
                <?php
                if ($google_singup == false) {
                    echo '<div class="text-center form-group"><input type="password" name="password" class="form-control password" placeholder="Password" value="';
                    if (isset($password_val)) echo $password_val;
                    echo '" />';
                    if (isset($err_password)) echo $err_password;
                    echo '</div>';
                    echo '<div class="text-center form-group"><input type="password" name="verifypassword" class="form-control password" placeholder="Verify Password" value=""/>';
                    if (isset($err_verifypassword)) echo $err_verifypassword;
                    echo '</div>';
                }
                ?>
                <div class="text-center form-group" style="margin-top:10%;">
                    <button type="submit" name="request" class="btn btn-primary btn-block">Submit Request</button>
                    <button type="submit" name="ok" class="btn btn-default btn-block">Back</button>
                </div>
            </div>

        </form>


    </div>
    <!--Google client library for Google Sign-In-->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>

</body>

</html>