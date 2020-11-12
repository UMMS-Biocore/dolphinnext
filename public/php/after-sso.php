<?php
require_once(__DIR__."/../../config/config.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>
            <?php echo COMPANY_NAME?> DolphinNext Pipeline Builder</title>
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <!--   app’s client ID prodcued in the Google Developers Console-->
        <meta name="google-signin-client_id" content="1051324819082-6mjdouf9dhmhv9ov5vvdkdknqrb8tont.apps.googleusercontent.com">
        <link rel="icon" type="image/png" href="images/favicon.ico" />
    </head>
    <body class="hold-transition skin-blue fixed">
        <span id="basepathinfo" basepath="<?php echo BASE_PATH ?>"></span>
        <div id="wrapper">
            <section id="after-sso-close">
                <p></p>
            </section>
        </div>
        <script type="text/javascript" src="./../js/after-sso.js"></script>
    </body>
</html>