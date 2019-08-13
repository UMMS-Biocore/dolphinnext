<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
$email = isset($_SESSION['email']) ? $_SESSION['email'] : "";
$name = isset($_SESSION['name']) ? $_SESSION['name'] : "";
$google_image = isset($_SESSION['google_image']) ? $_SESSION['google_image'] : "";
if ($email != ''){$login = 1;} 
else { $login = 0;}
session_write_close();
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
        <!-- feedback modal style -->
        <link rel="stylesheet" href="css/feedback.css">
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
        <link href="bower_components/jquery-ui-bootstrap/css/custom-theme/jquery-ui-1.10.0.custom.css" rel="stylesheet" />
        <!--    bootstrap-multiselect-->
        <link href="bower_components/bootstrap-multiselect/dist/css/bootstrap-multiselect.css" rel="stylesheet" />
        <!-- dropzone -->
        <link type="text/css" rel="stylesheet" href="bower_components/dropzone/dropzone.min.css" />
        <!-- plupload -->
        <link rel="stylesheet" href="bower_components/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />
        <link rel="stylesheet" href="bower_components/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" />
        <!-- to fix favicon.ico not found error-->
        <link rel="shortcut icon" href="#">
        <style>
            /* Ace Editor scroll problem fix */
            .ace_text-input {
                position: absolute!important
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

            .nav-pills > li.active > a, 
            .nav-pills > li.active > a:hover, 
            .nav-pills > li.active > a:focus{
                background-color:transparent;
                color:black;
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

            /*        Hover property for boostrap panel headers */

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

            .form-horizontal .control-label.text-left{
                text-align: left;
            }

            .nav.rmarkeditor>li>a {
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

            input:checked + .slider {
                background-color: #2196F3;
            }

            input:focus + .slider {
                box-shadow: 0 0 1px #2196F3;
            }

            input:checked + .slider:before {
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

            .loader-spin-parent{
                position:relative;
            }

            .loader-spin-icon{
                display:block; 
                margin:auto;
            }
            .loader-spin-iconDiv{
                display:block; 
                z-index: 1000; 
                position: absolute; 
                top: 0; 
                left: 0; 
                width: 100%; 
                height: 100%; 
                background: rgba( 255, 255, 255, .8 );
            }

            .disp_none{
                display: none !important;
             }

        </style>

    </head>

    <body class="hold-transition skin-blue sidebar-mini">
        <div class="wrapper">
            <span id="basepathinfo" basepath="<?php echo BASE_PATH?>" pubweb="<?php echo PUBWEB_URL?>" debrowser="<?php echo DEBROWSER_URL?>" ocpupubweb="<?php echo OCPU_PUBWEB_URL?>"></span>
            <header class="main-header">
                <!-- Logo -->
                <a href="index.php" class="logo">
                    <!-- mini logo for sidebar mini 50x50 pixels -->
                    <span class="logo-mini"><b>U</b>Bio</span>
                    <!-- logo for regular state and mobile devices -->
                    <span class="logo-lg" style="font-size:17px;"><b>
                        <?php echo COMPANY_NAME?></b> DolphinNext<b></b></span>
                </a>

                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top">
                    <div class="navbar-custom-menu pull-left">
                        <ul class="nav navbar-nav">
                            <li><a href="index.php?np=1">Pipelines </a></li>
                            <li><a href="index.php?np=2">Projects </a></li>
                            <li><a href="index.php?np=5">Run Status </a></li>
                            <!-- <li><a href="#"><i class="fa fa-bell-o"></i></a></li>-->
                            <?php
    include("php/funcs.php");
            $np = isset($_REQUEST["np"]) ? $_REQUEST["np"] : "";
            $id = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
                            ?>
                        </ul>
                    </div>
                    <div class="navbar-custom-menu pull-right">
                        <ul class="nav navbar-nav">
                            <li id="manageAmz" style="display:none">
                                <a href="#amzModal" data-toggle="modal">Amazon
                                    <small id="amzAmount" style="display:none" class="label pull-right bg-green"></small>
                                </a>
                            </li>
                            <?php
                            if ($login == 1){
                                echo '<li><a href="index.php?np=4">Profiles </a></li>';
                            }
                            ?>
                            <li><a href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html" target="_blank">Tutorial</a></li>
                            <li><a href="http://dolphinnext.readthedocs.io/" target="_blank"><i class="fa fa-mortar-board"></i></a></li>
                            <li> <a><b style="color:#7c1842;"> VERSION <?php echo DN_VERSION?> </b> </a></li>
                        </ul>
                    </div>
                </nav>
            </header>



            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <div class="user-panel" style="padding-bottom:5px;">
                        <div id="userAvatar" style="display:inline" class="pull-left image">
                            <img id="userAvatarImg" src="
                                                         <?php 
                                                         if ($google_image != ""){
                                                             $file_headers = @get_headers($google_image);
                                                             if($file_headers && !strpos($file_headers[0], '404')) {
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
                        <div id="userInfo" style="display:inline" class="info" email="<?php
                                                                                      echo $email;  
                                                                                      ?>">
                            <p id="userName">
                                <?php
                                if ($login == 1){
                                    if (strlen($name) >17){
                                        $name = substr($name,0,16); 
                                    }
                                    echo $name;
                                }
                                ?>
                            </p>
                            <span style="font-size:11px;"><i class="fa fa-circle text-success"></i> Online</span>
                            <a style="padding-left:5px; font-size:11px; float:right;" href="<?php echo BASE_PATH?>/index.php?p=logout">Sign out</a>
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
                            <?php echo COMPANY_NAME." ";
                            print getTitle($np); ?>

                        </h1>
                        <ol class="breadcrumb">
                            <li><a href=""><i class="fa fa-dashboard"></i> Home</a></li>
                            <li><a href=""></a>
                                <?php echo COMPANY_NAME?>
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
                                <div class="box-body table-responsive" style="min-height:90vh; overflow-y:scroll;">

                                    <?php print getPage($np, $login, $id); 

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
            <!--        feedback modal-->
            <div id="feedback">
                <div id="feedback-form" style='display:none;' class="col-xs-4 col-md-4 panel panel-default">
                    <form method="POST" action="/feedback" class="form panel-body" role="form">
                        <div class="form-group">
                            <input class="form-control" name="email" autofocus placeholder="Your e-mail" type="email" />
                        </div>
                        <div class="form-group">
                            <textarea class="form-control" name="message" required placeholder="Please write your feedback here..." rows="5"></textarea>
                        </div>
                        <button class="btn btn-primary pull-right" type="submit">Send</button>
                    </form>
                </div>
                <div id="feedback-tab">Feedback</div>
            </div>
            <!--        feedback modal ends-->

            <!-- Add Amazon Modal Starts-->
            <div id="amzModal" class="modal fade" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Amazon Management Console</h4>
                        </div>
                        <div class="modal-body">
                            <form class="form-horizontal">
                                <div class="panel panel-default">
                                    <div>
                                        </br>
                                    <table id="amzTable" class="table">
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
        <div id="addAmzNodeModal" class="modal fade" tabindex="-1" role="dialog">
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
                                    <input type="text" class="form-control" id="profileID" name="id">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="numNodes" class="col-sm-3 control-label">Nodes</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" id="numNodes" required name="nodes" placeholder="Enter the number of nodes">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-3 control-label">Use Autoscale</label>
                                <div class="col-sm-9">
                                    <input type="checkbox" id="autoscale_check" name="autoscale_check" data-toggle="collapse" data-target="#autoscaleDiv">
                                </div>
                            </div>
                            <div id="autoscaleDiv" class="collapse">
                                <div class="form-group row">
                                    <label for="autoscale_maxIns" class="col-sm-3 control-label">Maximum instances <span><a data-toggle="tooltip" data-placement="bottom" title="Maximum number of instances on the cluster"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="autoscale_maxIns" value="4" name="autoscale_maxIns" placeholder="Enter the number of maximum instances">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="numNodes" class="col-sm-3 control-label">Auto Shutdown <span><a data-toggle="tooltip" data-placement="bottom" title="Amazon instance will be automaticaly shutdown when machine is idle for 10 minutes. This feature will be activated after you initiate your first run."><i class='glyphicon glyphicon-info-sign'></i></a></span></label> 
                                <div class="col-sm-9">
                                    <input id="autoshut_check"type="checkbox"  name="autoshutdown_check" >
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="activateAmz">Activate Cluster</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Add Amazon Node Modal Ends-->
        <!--Info Modal Starts-->
        <div id="infoMod" class="modal fade" tabindex="-1" role="dialog">
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


    <!--Google Platform Library on your web pages that integrate Google Sign-In-->
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <!-- jQuery 3 -->
    <script src="bower_components/jquery/dist/jquery.min.js"></script>
    <!-- jquery-ui-1.9.2.custom.min-->
    <script src="bower_components/jquery-ui-bootstrap/assets/js/jquery-ui-1.10.0.custom.min.js"></script>
    <!-- Bootstrap 3.3.7 -->
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
    <!-- jquery-migrate-3.0.0-->
    <script src="bower_components/jquery-ui-bootstrap/js/jquery-migrate-3.0.0.js"></script>
    <!-- bootstrap-multiselect-->
    <script src="bower_components/bootstrap-multiselect/dist/js/bootstrap-multiselect.js"></script>
    <!-- Selectize 0.12.4.  -->
    <script src="dist/selectize/selectize.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button);

    </script>
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="dist/js/demo.js"></script>
    <!-- pagination 2.1.2 -->
    <script src="dist/js/pagination.min.js"></script>
    <!--   Datatables-->
    <script type="text/javascript" src="bower_components/DataTables/datatables.min.js"></script>
    <!-- jquery loading -->
    <script src="dist/jquery_loading/jquery.loading.min.js"></script>
    <!-- d3 pdf export -->
    <script src="dist/css_to_pdf/xepOnline.jqPlugin.js"></script>
    <script src="dist/ace/ace.js"></script>
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
    <?php print getJS($np, $login, $id); ?>

    </body>

</html>
