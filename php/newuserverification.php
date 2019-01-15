<!DOCTYPE html>
<html>

<head class="bg-gray">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo COMPANY_NAME?> DolphinNext Pipeline Builder</title>
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

<body  style="background:linear-gradient(to top right, #7474BF 10%, #5c7cc2 55%, #348AC7 100%);">
    <div name="empty_space" style="height:10%; width:100%; clear:both;"></div>
    <div class="form-box" id="login-box" style="width:470px;">
        <h1 class="text-center" style="padding-bottom:20px;"><span class="logo-lg" style="color:white; font-size:30px;"><b><?php echo COMPANY_NAME?></b> DolphinNext</span></h1>
        <form action="<?php echo BASE_PATH?>/" method="post">
            <div class="body bg-white" style=" border-radius:5px; padding:30px;">
                <div class="text-center form-group">
                        <label>
							An email has been sent to our admins for approval of your DolphinNext user account.  We will e-mail you upon verification.
						</label>
                    </div>
                <div class="text-center form-group" style="margin-top:10%;">
                    <button type="submit" name="ok" class="btn btn-primary btn-block">OK</button>
                </div>
            </div>

        </form>


    </div>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js" type="text/javascript"></script>
</body>

</html>
