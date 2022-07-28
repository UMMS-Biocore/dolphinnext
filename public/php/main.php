<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
$email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
$name = isset($_SESSION['name']) ? $_SESSION['name'] : "";
$google_image = isset($_SESSION['google_image']) ? $_SESSION['google_image'] : "";
$role = isset($_SESSION['role']) ? $_SESSION['role'] : "";
if ($email != '') {
    $login = 1;
} else {
    $login = 0;
}


require_once(__DIR__ . "/../../config/config.php");
$SHOW_WIZARD = SHOW_WIZARD;
$SHOW_APPS = SHOW_APPS;
$CUSTOM_HELP_MESSAGE = CUSTOM_HELP_MESSAGE;

require_once("php/funcs.php");
$np = isset($_REQUEST["np"]) ? $_REQUEST["np"] : "";
$id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
$t = isset($_REQUEST["t"]) ? $_REQUEST["t"] : "";
if (!empty($t) && empty($id) && empty($np)) {
    $db = new dbfuncs();
    $sql = "SELECT id, np FROM `token` WHERE token = '$t'";
    $lData = $db->queryTable($sql);
    $ln = json_decode($lData, true);
    if (isset($ln[0])) {
        $id = $ln[0]["id"];
        $np = $ln[0]["np"];
        if (isset($_SESSION['token'])) {
            if (!in_array($t, $_SESSION['token'], true)) {
                array_push($_SESSION['token'], $t);
            }
        } else {
            $_SESSION['token'] = array($t);
        }
    }
}

session_write_close();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>
        <?php echo COMPANY_NAME ?> DolphinNext Pipeline Builder</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!--   app’s client ID prodcued in the Google Developers Console-->
    <meta name="google-signin-client_id" content="1051324819082-6mjdouf9dhmhv9ov5vvdkdknqrb8tont.apps.googleusercontent.com">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Ionicons -->
    <link rel="stylesheet" href="bower_components/Ionicons/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
    <!-- selectize style -->
    <link rel="stylesheet" href="css/selectize.bootstrap3.css">
    <!--  w3 fonts-->
    <link rel="stylesheet" type="text/css" href="css/w3.css">
    <!-- Google Font -->
    <link rel="stylesheet" type="text/css" href="css/googleApiSourceSans.css" />
    <!-- Datatables-->
    <link rel="stylesheet" type="text/css" href="bower_components/DataTables/datatables.min.css" />
    <!--    dataTables.checkboxes-->
    <link type="text/css" href="css/dataTables.checkboxes.css" rel="stylesheet" />
    <!--    pagination-->
    <link type="text/css" href="css/pagination.css" rel="stylesheet" />
    <!--    jquery loading-->
    <link href="dist/jquery_loading/jquery.loading.min.css" rel="stylesheet" />
    <!--    jquery-ui-bootstrap-->
    <link href="bower_components/jquery-ui-1.13.0.custom/jquery-ui.min.css" rel="stylesheet" />
    <!--    bootstrap-multiselect-->
    <link href="bower_components/bootstrap-multiselect/dist/css/bootstrap-multiselect.css" rel="stylesheet" />
    <!-- dropzone -->
    <link type="text/css" rel="stylesheet" href="bower_components/dropzone/dropzone.min.css" />
    <!-- plupload -->
    <link rel="stylesheet" href="bower_components/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />
    <link rel="stylesheet" href="bower_components/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" />
    <!-- toastr -->
    <link rel="stylesheet" href="bower_components/toastr/build/toastr.min.css" type="text/css" />
    <!-- toastr -->
    <link rel="stylesheet" href="bower_components/toastr/build/toastr.min.css" type="text/css" />
    <link rel="stylesheet" href="bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css" type="text/css" />
    <!-- handsontable 6.2.2 ->public dist -> don't upgrade version -->
    <link rel="stylesheet" href="bower_components/handsontable-6.2.2/dist/handsontable.full.min.css" type="text/css" />


    <!-- to fix favicon.ico not found error-->
    <!--        <link rel="shortcut icon" href="#">-->
    <link rel="icon" type="image/png" href="images/favicon.ico" />
    <style>
        /* Prevents page navigation on scroll*/
        html,
        body {
            overscroll-behavior-x: none;
        }

        /* strikethrough css deleted rows in Datatables */
        tr.strikeline-row {
            color: gray;
            text-decoration: line-through;
        }

        .link-underline {
            color: #1479cc;
            cursor: pointer;
            text-decoration: underline;
        }

        /* Ace Editor scroll problem fix */
        .ace_text-input {
            position: absolute !important
        }

        /*glyphicon-stack    */

        .glyphicon-stack {
            position: relative;
        }

        .glyphicon-stack-2x {
            position: absolute;
            left: 14px;
            top: -5px;
            font-size: 10px;
            text-align: center;
        }

        .glyphicon-stack-3x {
            position: absolute;
            left: 8px;
            top: -1px;
            font-size: 15px;
            text-align: center;
        }

        /*Pipeline Name Dynamic Input Box */

        .width-dynamic {
            padding: 5px;
            font-size: 20px;
            font-family: Sans-serif;
            white-space: pre;
        }

        .box-dynamic:hover {
            border: 1px solid lightgrey;
        }

        .box-dynamic {
            border: 1px solid transparent;
        }

        /*Combobox Menu*/

        .selectize-control .option .title {
            display: block;
        }

        .selectize-control .option .url {
            font-size: 12px;
            display: block;
            color: #a0a0a0;
        }

        .selectize-dropdown {
            width: 350px !important;
        }

        /*    D3 tooltip*/

        div.tooltip-svg {
            position: absolute;
            text-align: left;
            padding: 2px;
            font: 14px sans-serif;
            background: lightsteelblue;
            border: 0px;
            border-radius: 8px;
            pointer-events: none;
            font-color: black;
        }

        /*    NavBar process details*/


        .nav-tabs>li>a {
            border: medium none;
        }

        .nav-tabs>li>a:hover {
            border: medium none;
            border-radius: 0;
            color: #0570c1;
        }

        .nav-item {
            color: #428bca !important;
            font-weight: 600;
        }

        .nav-item.sub {
            color: #428bca !important;
            font-weight: 500;
        }

        .nav-pills>li.active>a,
        .nav-pills>li.active>a:hover,
        .nav-pills>li.active>a:focus {
            background-color: transparent;
            color: black;
        }

        /*        table links should appear blue*/

        .table.table-striped.table-bordered a {
            color: #0570c1;
        }

        .table.table-striped.table-bordered a:hover {
            color: #428bca !important;
            text-decoration: underline;
        }

        /*        public pipelines page*/

        .boxheader {
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            padding: 1% 0;
            border-bottom: 2px solid #eee;
            height: 60px !important;
        }

        .widget-user-header {
            height: 100px !important;
        }

        .box-body {
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
            padding: 20px;
            padding-top: 5px;
        }

        .movebox {
            min-width: 100%;
            min-height: 100%;
            margin-bottom: 10px;
            border: 2px solid #dee2e8;
            position: relative;
            display: inline-block;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0px 0px 2px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease-in-out;
        }

        .movebox::after {
            position: absolute;
            z-index: -1;
            opacity: 0;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: opacity 0.3s ease-in-out;
        }

        /* Scale up the box */

        .movebox:hover {
            box-shadow: 0 0 20px rgba(33, 33, 33, .2);
        }

        /* Fade in the pseudo-element with the bigger shadow */

        .movebox:hover::after {
            opacity: 1;
        }

        /* In order to fix textarea width*/

        textarea {
            resize: vertical;
        }

        /*        Make center the pagination numbers*/

        .paginationjs {
            display: flex;
            justify-content: center;
        }

        /*        Hover property for bootstrap panel headers */

        .collapsible:hover {
            background-color: lightgray;
        }

        .collapsibleRow:hover {
            background-color: lightgray;
        }

        .collapsibleRow {
            background-color: #F5F5F5;
        }

        /*        Red warning for empty form sections */

        .borderClass {
            border: 1px solid red;
        }

        /*        Datatable link hover */
        .txtlink:hover {
            text-decoration: underline;
            color: #397FA7;
        }

        .txtlink {
            color: #397FA7;
        }

        /*        multi-Modal overflow fix */
        .modal {
            overflow: auto;
        }

        body.modal-open {
            overflow: hidden;
            position: fixed;
            width: 100%;
        }

        .form-horizontal .control-label.text-left {
            text-align: left;
        }

        .nav.rmarkeditor>li>a {
            padding: 5px;
        }

        .nav.appeditor>li>a {
            padding: 5px;
        }

        .nav.panelheader>li>a {
            padding: 5px;
        }

        .modal-dialog.fullscreen {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .modal-content.fullscreen {
            height: auto;
            min-height: 100%;
            border-radius: 0;
        }

        .modal.fade.in.fullscreen {
            padding: 0 !important;
        }


        /* slider*/

        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        /*   Loader div      */

        .loader-spin-parent {
            position: relative;
        }

        .loader-spin-icon {
            display: block;
            margin: auto;
        }

        .loader-spin-iconDiv {
            display: block;
            z-index: 1000;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, .8);
        }

        /* tiny spinner*/
        .tiny-spinner {
            border: 5px solid #f3f3f3;
            border-radius: 50%;
            border-top: 5px solid #3498db;
            width: 5px;
            height: 5px;
            -webkit-animation: spin 2s linear infinite;
            /* Safari */
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }

            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .ui-dialog {
            z-index: 1000 !important;
        }

        .ui-widget {
            font-size: 95% !important;
            font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .disp_none {
            display: none !important;
        }

        /*
            .tooltip-inner {
            max-width: none;
            white-space: nowrap;
            }
            */

        /* modal show form values on multiple values */
        div.multi-value {
            border: 1px dotted #666;
            border-radius: 3px;
            padding: 5px;
            background-color: #fafafa;
            cursor: pointer;
        }

        div.multi-restore {
            margin-top: 0.5em;
            font-size: 0.8em;
            line-height: 1.25em;
            color: #3879d9;
        }

        div.multi-restore:hover {
            text-decoration: underline;
            cursor: pointer;
        }

        .dz-error-mark svg g g {
            fill: #c00;
        }

        .multiselect.dropdown-toggle {
            text-align: left;
        }


        /*      draggable ace editor

            .app_editor_dragbar {
            background-color: #f6f6f6;
            width: 100%;
            height: 5px;
            cursor: row-resize;
            opacity: 1;
            }

            .app_editor_wrapper {
            background-color: #cccccc;
            border-bottom: 1px solid #222222;
            }
            */
    </style>

</head>

<body class="hold-transition skin-blue fixed">
    <div class="wrapper" style="position:static">
        <span id="basepathinfo" basepath="<?php echo BASE_PATH ?>" pubweb="<?php echo PUBWEB_URL ?>" debrowser="<?php echo DEBROWSER_URL ?>" ocpupubweb="<?php echo OCPU_PUBWEB_URL ?>" sso_login="<?php echo SSO_LOGIN ?>" sso_url="<?php echo SSO_URL ?>" client_id="<?php echo CLIENT_ID ?>"></span>
        <header class="main-header">
            <!-- Logo -->
            <a href="index.php" class="logo">
                <!-- mini logo for sidebar mini 50x50 pixels -->
                <span class="logo-mini"><b>U</b>Bio</span>
                <!-- logo for regular state and mobile devices -->
                <span class="logo-lg" style="font-size:17px;"><b>
                        <?php echo COMPANY_NAME ?></b> DolphinNext<b></b></span>
            </a>

            <!-- Header Navbar: style can be found in header.less -->
            <nav class="navbar navbar-static-top">
                <div class="navbar-custom-menu pull-left">
                    <ul class="nav navbar-nav">
                        <li><a href="index.php?np=1">Pipelines </a></li>
                        <?php
                        if ($login == 1) {
                            echo '<li><a href="index.php?np=2">Projects </a></li>
                                  <li><a href="index.php?np=5">Run Status </a></li>';
                            if ($login == 1 && $SHOW_APPS) {
                                echo '<li class="dropdown" >
        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">Data <span class="caret"></span></a>
        <ul class="dropdown-menu" role="menu" style="left: 0;">
            <li><a href="index.php?np=7" >Apps</a></li>
        </ul>
        </li>';
                            }
                            if ($login == 1 && $role == "admin") {
                                echo '<li><a href="index.php?np=8">Dashboard</a></li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
                <div class="navbar-custom-menu pull-right">
                    <ul class="nav navbar-nav">
                        <li id="manageAmz" style="display:none">
                            <a href="#amazonModal" data-toggle="modal"><i style="padding:4px;" data-toggle="tooltip" data-placement="bottom" title="Amazon Web Services" class="fa fa-amazon"></i>
                                <small id="amzAmount" style="display:none" class="label pull-right bg-green"></small>
                            </a>
                        </li>
                        <li id="manageGoog" style="display:none">
                            <a href="#googleModal" data-toggle="modal"><i style="padding:4px;" data-toggle="tooltip" data-placement="bottom" title="Google Cloud" class="fa fa-google"></i>
                                <small id="googAmount" style="display:none" class="label pull-right bg-green"></small>
                            </a>
                        </li>
                        <?php
                        if ($login == 1 && $SHOW_WIZARD) {
                            echo '<li id="manageProfileWizard" class="dropdown notifications-menu" >
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true" >
                                    <i data-toggle="tooltip" data-placement="bottom" title="Wizards" style="padding:3px; padding-left:0px; padding-right:5px;" class="fa fa-magic"></i>
                                    <small id="wizAmount" style="display:none" class="label pull-right label-warning"></small>
                                </a>
                                <ul class="dropdown-menu" style="width:400px;">
                                    <li class="header"><label>Wizards</label></li>
                                    <li>
                                        <ul class="menu">
                                            <li><a id="addProfileWizard" wid="" mode="add" type="runenv" href="#profilewizardmodal" data-toggle="modal"><i class="fa fa-plus-circle text-aqua"></i> Create New Run Environment</a></li>

                                        </ul>
                                    </li>
                                    <li class="header" id="savedWizardHeader"> Saved Wizards</li>
                                    <li>
                                        <ul class="menu" id="ongoingwizard">
                                        </ul>
                                    </li>
                                </ul>
                            </li>';
                        }

                        if ($login == 1) {
                            echo '<li><a href="index.php?np=4" data-toggle="tooltip" data-placement="bottom" title="Profiles"><i class="glyphicon glyphicon-user"></i> </a></li>';
                        }
                        ?>

                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-mortar-board"></i> <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="http://dolphinnext.readthedocs.io/" target="_blank">Reference Documentation</a></li>
                                <li><a href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html" target="_blank">Quick Start Guide</a></li>
                                <li><a href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/dev_quick.html" target="_blank">Developer Tutorial</a></li>
                                <li><a href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/faq.html" target="_blank">Frequently Asked Questions</a></li>
                                <!--                                    <li class="divider"></li>-->
                                <!--                                    <li><a href="#">One more separated link</a></li>-->
                            </ul>
                        </li>
                        <li>
                            <a id="tooglehelp" class="tooglehelp" href="#" data-toggle="control-sidebar" data-slide="true"><i class="glyphicon glyphicon-question-sign"></i></a>
                        </li>

                        <li><a id="dnVersionBut" href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span id="dn-version" ver="<?php echo DN_VERSION ?>">v<?php echo DN_VERSION ?> </span></a>
                            <div class="dropdown-menu" style="width:650px; padding:0px;">
                                <div class="panel panel-default" style="margin:0px;">
                                    <div class="panel-heading clearfix">
                                        <div class="pull-left">
                                            <h5>&nbsp; Version Notes</h5>
                                        </div>
                                        <div class="pull-right" style="padding-top:6px;">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"> <span aria-hidden="true">&times;</span></a>
                                        </div>
                                    </div>
                                    <div class="panel-body" style="padding:0px;">
                                        <textarea rows="30" class="form-control" style="resize:none; " id="versionNotes"></textarea>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <?php
                        if ($login == 1 && $role == "admin") {
                            echo '<li class="dropdown tasks-menu" id="softUpdBut" style="display:none;">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                        <i class="fa fa-cloud-download"></i>
                                        <span class="label label-danger">1</span>
                                        </a>
                                        <div class="dropdown-menu" style="width:550px;">
                                            <div class="panel panel-default" style="margin-bottom:0px;">
                                                <div class="panel-heading clearfix">
                                                    <div class="pull-left">
                                                        <h5>&nbsp; A new version of DolphinNext is avaliable!</h5>
                                                    </div>
                                                    <div class="pull-right" style="padding-top:6px;">
                                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true" > <span aria-hidden="true">&times;</span></a>
                                                    </div>
                                                </div>
                                                <div class="panel-body">
                                                    <form>
                                                        <div class="form-group col-sm-12">
                                                            <p id="softUptDesc"></p> 
                                                            <textarea readonly rows="2" class="form-control" style="resize:none;" id="softUptCmd"></textarea>
                                                        </div>
                                                        <div class="form-group col-sm-12">
                                                            <p>Release Notes:</p> 
                                                            <textarea readonly rows="7" class="form-control" style="resize:none;" id="softUptReleaseNotes"></textarea>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </li>';
                        }
                        if ($login != 1) {
                            echo '<li><a id="signinbtn" href="index.php?p=login">Sign In</i> </a></li>';
                        }

                        ?>

                    </ul>
                </div>
            </nav>
        </header>



        <!-- Left side column. contains the logo and sidebar -->
        <aside class="main-sidebar">
            <!-- sidebar: style can be found in sidebar.less -->
            <section class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel" <?php
                                        if ($login != 1) {
                                            echo 'style="display:none;"';
                                        }
                                        ?>style="padding-bottom:5px;">
                    <div id="userAvatar" style="display:inline" class="pull-left image">
                        <img id="userAvatarImg" src="
                                                         <?php
                                                            if ($google_image != "") {
                                                                $file_headers = @get_headers($google_image);
                                                                if ($file_headers && !strpos($file_headers[0], '404')) {
                                                                    echo $google_image;
                                                                } else {
                                                                    echo 'dist/img/user-orange.png';
                                                                }
                                                            } else {
                                                                echo 'dist/img/user-orange.png';
                                                            }
                                                            ?>
                                                         " class="img-circle" alt="User Image">
                    </div>
                    <div id="userInfo" class="info" email="<?php echo $email; ?>">
                        <p id="userName">
                            <?php
                            if ($login == 1) {
                                if (strlen($name) > 17) {
                                    $name = substr($name, 0, 16);
                                }
                                echo $name;
                            }
                            ?>
                        </p>
                        <span style="font-size:11px;"><i class="fa fa-circle text-success"></i> Online <a style="color:#fff; margin-left:35px; font-size:11px; " href="<?php echo BASE_PATH ?>/index.php?p=logout">Sign out</a></span>
                    </div>
                </div>

                <!-- search form -->
                <form action="#" method="get" class="sidebar-form" autocomplete="off">
                    <div class="input-group">
                        <input type="text" id="tags" name="q" class="form-control" placeholder="Search..." />
                        <span class="input-group-btn">
                            <button type='button' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i></button>
                        </span>
                    </div>
                </form>
                <!-- /.search form -->
                <!-- sidebar menu: : style can be found in sidebar.less -->
                <?php
                print getSidebarMenu($np, $login);
                ?>
        </aside>
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <h1>
                    <?php echo COMPANY_NAME . " ";
                    print getTitle($np); ?>

                </h1>
                <ol class="breadcrumb">
                    <li><a href=""><i class="fa fa-dashboard"></i> Home</a></li>
                    <li><a href=""></a>
                        <?php echo COMPANY_NAME ?>
                    </li>
                    <li class="active">
                        <?php print getTitle($np); ?>
                    </li>
                </ol>
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="row">
                    <div class="box">

                        <!--/.box-header -->
                        <div id="main-content-box-header" class="box-body table-responsive" style="min-height:90vh; overflow-y:scroll;">

                            <?php print getPage($np, $login, $id, $ownerID);

                            ?>
                        </div>
                        <!-- /.box-body -->
                    </div>
                    <!-- /.box -->
                </div>
                <!-- /.row -->
            </section>
            <!-- /.content -->
            </aside>
            <!-- /.control-sidebar -->
            <!-- Add the sidebar's background. This div must be placed
immediately after the control sidebar -->
            <div class="control-sidebar-bg"></div>
        </div>
        <!-- ./wrapper -->
        <footer class="main-footer" style="display:none; background:#E9EDF2; border-top:0px;">
            <div class="pull-right hidden-xs">
                <a href="php/terms.php" target="_blank">Terms & Privacy Policy </a>
            </div>
            <p> </p>
        </footer>

        <aside id="controlSidebarHelp" class="control-sidebar control-sidebar-light">
            <div>
                <div style="padding-top:15px; padding-right:15px;">
                    <a href="#" class="pull-right" data-toggle="control-sidebar"> <span aria-hidden="true" style="font-size:larger;">×</span></a>
                </div>
                <div style="margin-top:30px; text-align:center;"><img src="data:image/png;base64,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" /> </div>
                <form class="form panel-body" role="form">
                    <div style="margin-top:30px; margin-bottom:15px;"><b>Need Help?</b></div>
                    <div class="form-group">
                        <p><?php echo $CUSTOM_HELP_MESSAGE ?></p>
                        <p>Learn from the documentation below.</p>
                    </div>
                    <div class="form-group">
                        <ul>
                            <li><a class="link-underline" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html#running-pipelines" target="_blank">Running Pipelines</a></li>
                            <li><a class="link-underline" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html#adding-files" target="_blank">Adding Files</a></li>
                            <li><a class="link-underline" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/faq.html" target="_blank">Frequently Asked Questions</a></li>
                            <li><a class="link-underline" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/known_issues.html" target="_blank">Known Issues</a></li>
                        </ul>
                        Not finding what you need? Visit our <a class="link-underline" href="https://dolphinnext.readthedocs.io" target="_blank">Reference Documentation</a></li>
                    </div>
                    <div style="margin-top:30px; margin-bottom:15px;"><b>Contact our support</b></div>
                    <div class="form-group">
                        <textarea class="form-control" id="feedmessage" name="message" required placeholder="Describe your issue or share your ideas here..." rows="10"></textarea>
                    </div>
                    <button style="margin-top:15px;" class="btn btn-primary pull-right" type="button" id="sendfeedback">Send</button>
                    <button style="margin-top:15px; margin-right:5px; " class="btn btn-default pull-right" type="button" href="#" data-toggle="control-sidebar" data-slide="true">Cancel</button>
                </form>
            </div>
        </aside>
        <!-- The sidebar's background -->
        <!-- This div must placed right after the sidebar for it to work-->
        <div class="control-sidebar-bg"></div>
        <!--            Contact Us footer-->
        <a style="position:fixed; right: 15px; bottom: 0px; border-bottom-left-radius:0px; border-bottom-right-radius:0px;" href="#" class="btn btn-default tooglehelp" href="#" data-toggle="control-sidebar" data-slide="true"> <i class="fa fa-envelope"></i> Support</a>

        <?php print include("php/wizard.php"); ?>


        <!-- Add google Modal Starts-->
        <div id="googleModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Google Cloud Console</h4>
                    </div>
                    <div class="modal-body">
                        <form class="form-horizontal">
                            <div class="panel panel-default">
                                <div>
                                    </br>
                                    <table id="googleTable" class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Profile Name</th>
                                                <th scope="col">Details</th>
                                                <th scope="col">Auto Shutdown <span><a data-toggle="tooltip" data-placement="bottom" title="Google instance will be automaticaly shutdown when machine is idle for 10 minutes. This feature will be activated after you initiate your first run."><i class='glyphicon glyphicon-info-sign'></i></a></span></th>
                                                <th style="width:300px;" scope="col">Status</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Google Modal Ends-->

        <!-- Add Amazon Modal Starts-->
        <div id="amazonModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Amazon Web Services Console</h4>
                    </div>
                    <div class="modal-body">
                        <form class="form-horizontal">
                            <div class="panel panel-default">
                                <div>
                                    </br>
                                    <table id="amazonTable" class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Profile Name</th>
                                                <th scope="col">Details</th>
                                                <th scope="col">Auto Shutdown <span><a data-toggle="tooltip" data-placement="bottom" title="Amazon instance will be automaticaly shutdown when machine is idle for 10 minutes. This feature will be activated after you initiate your first run."><i class='glyphicon glyphicon-info-sign'></i></a></span></th>
                                                <th style="width:300px;" scope="col">Status</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Amazon Modal Ends-->

        <!-- Add Amazon Node Modal Starts-->
        <div id="addamazonNodeModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Configuration</h4>
                    </div>
                    <div class="modal-body">
                        <form class="form-horizontal">
                            <div class="form-group" style="display:none">
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="id">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Nodes</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="amazonnumNodes" required name="nodes" placeholder="Enter the number of nodes">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Use Autoscale</label>
                                <div class="col-sm-9">
                                    <input type="checkbox" id="amazonautoscale_check" name="autoscale_check" data-toggle="collapse" data-target="#amazonautoscaleDiv">
                                </div>
                            </div>
                            <div id="amazonautoscaleDiv" class="collapse">
                                <div class="form-group row">
                                    <label class="col-sm-3 control-label">Maximum instances <span><a data-toggle="tooltip" data-placement="bottom" title="Maximum number of instances on the cluster"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="autoscale_maxIns" value="4" name="autoscale_maxIns" placeholder="Enter the number of maximum instances">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Auto Shutdown <span><a data-toggle="tooltip" data-placement="bottom" title="Amazon instance will be automaticaly shutdown when machine is idle for 10 minutes. This feature will be activated after you initiate your first run."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                <div class="col-sm-9">
                                    <input id="amazonautoshut_check" type="checkbox" name="autoshutdown_check">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="amazonActivate">Activate Cluster</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Amazon Node Modal Ends-->
        <!-- Add Google Node Modal Starts-->
        <div id="addgoogleNodeModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Configuration</h4>
                    </div>
                    <div class="modal-body">
                        <form class="form-horizontal">
                            <div class="form-group" style="display:none">
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="id">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Nodes</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="googlenumNodes" required name="nodes" placeholder="Enter the number of nodes">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Use Autoscale</label>
                                <div class="col-sm-9">
                                    <input type="checkbox" id="googleautoscale_check" name="autoscale_check" data-toggle="collapse" data-target="#googleautoscaleDiv">
                                </div>
                            </div>
                            <div id="googleautoscaleDiv" class="collapse">
                                <div class="form-group row">
                                    <label for="autoscale_maxIns" class="col-sm-3 control-label">Maximum instances <span><a data-toggle="tooltip" data-placement="bottom" title="Maximum number of instances on the cluster"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="googleautoscale_maxIns" value="4" name="autoscale_maxIns" placeholder="Enter the number of maximum instances">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Auto Shutdown <span><a data-toggle="tooltip" data-placement="bottom" title="Amazon instance will be automaticaly shutdown when machine is idle for 10 minutes. This feature will be activated after you initiate your first run."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                <div class="col-sm-9">
                                    <input id="googleautoshut_check" type="checkbox" name="autoshutdown_check">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="googleActivate">Activate Cluster</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Google Node Modal Ends-->

        <!--Info Modal Starts-->
        <div id="infoMod" style="z-index:1055;" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Information</h4>
                    </div>
                    <div class="modal-body">
                        <span id="infoModText">Text</span>
                        </br>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>
        <!--Info Modal ENDs-->

        <!--Confirm Delete Modal-->
        <div id="confirmDeleteModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="confirmDeleteModalTitle">Confirm</h4>
                    </div>
                    <div class="modal-body" id="confirmDeleteModalText">Text</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal" id="confirmDeleteModalDelBtn">Delete</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <!--Confirm Del Modal Ends-->


        <!--Confirm Delete Wizard Modal-->
        <div id="confirmDelWizardModal" class="modal fade" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Confirm</h4>
                    </div>
                    <div class="modal-body" id="confirmDelWizardModalText">Text</div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary delete" data-dismiss="modal">Delete</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        <!--Confirm Delete Wizard Modal Ends-->


        <!--Google client library for Google Sign-In-->
        <script src="https://accounts.google.com/gsi/client" async defer></script>
        <!-- jQuery 3 -->
        <script src="bower_components/jquery/dist/jquery.min-3.6.0.js"></script>
        <!-- Bootstrap 3.3.7 -->
        <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
        <!-- jquery-ui-1.13.0.custom.min-->
        <script src="bower_components/jquery-ui-1.13.0.custom/jquery-ui.js"></script>
        <!-- jquery-migrate-3.0.0-->
        <script src="bower_components/jquery-ui-bootstrap/js/jquery-migrate-3.0.0.js"></script>
        <!-- bootstrap-multiselect-->
        <script src="bower_components/bootstrap-multiselect/dist/js/bootstrap-multiselect.js"></script>
        <!-- json5 2.2.1-->
        <script src="bower_components/json5/json5_2.2.1.min.js"></script>
        <!-- Selectize 0.12.4.  -->
        <script src="dist/selectize/selectize.js"></script>
        <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
        <script>
            $.widget.bridge('uibutton', $.ui.button);
        </script>
        <!-- AdminLTE App -->
        <script src="dist/js/adminlte.min.js"></script>
        <!--    <script src="dist/js/adminlte.js"></script>-->
        <!-- AdminLTE for demo purposes -->
        <script src="dist/js/demo.js"></script>
        <!-- pagination 2.1.2 -->
        <script src="dist/js/pagination.min.js"></script>
        <!--   Datatables-->
        <script type="text/javascript" src="bower_components/DataTables/datatables.min.js"></script>
        <script type="text/javascript" src="bower_components/DataTables/dataTables.scroller.min.js"></script>
        <!-- jquery loading -->
        <script src="dist/jquery_loading/jquery.loading.min.js"></script>
        <!-- SlimScroll -->
        <script src="bower_components/jQuery-slimScroll-1.3.8/jquery.slimscroll.min.js"></script>
        <!-- d3 pdf export -->
        <script src="dist/css_to_pdf/xepOnline.jqPlugin.js"></script>
        <script src="bower_components/ace/ace.js"></script>
        <!-- crypto-js -->
        <script src="bower_components/crypto-js/aes.js"></script>
        <!-- dropzone -->
        <script src="bower_components/dropzone/dropzone.js"></script>
        <!-- showdownjs -->
        <script src="bower_components/showdownjs/dist/showdown.min.js"></script>
        <!-- plupload -->
        <script src="bower_components/plupload/js/plupload.full.min.js"></script>
        <script src="bower_components/plupload/js/jquery.ui.plupload/jquery.ui.plupload.min.js"></script>
        <script src="bower_components/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>
        <script type="text/javascript" src="dist/js/dataTables.checkboxes.js"></script>
        <script type="text/javascript" src="bower_components/toastr/build/toastr.min.js"></script>
        <script type="text/javascript" src="bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>
        <?php print getJS($np, $login, $id); ?>

</body>

</html>