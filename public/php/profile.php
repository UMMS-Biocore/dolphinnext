<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
$name = isset($_SESSION['name']) ? $_SESSION['name'] : "";
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : "";
$role = isset($_SESSION['role']) ? $_SESSION['role'] : "";
if ($ownerID != '') {
    $login = 1;
} else {
    $login = 0;
}
session_write_close();

require_once(__DIR__ . "/../../config/config.php");
$SHOW_GOOGLE_KEYS = SHOW_GOOGLE_KEYS;
$SHOW_AMAZON_KEYS = SHOW_AMAZON_KEYS;
$SHOW_SSH_KEYS = SHOW_SSH_KEYS;
$SHOW_GROUPS = SHOW_GROUPS;
$SHOW_GIT = SHOW_GITHUB;
$GOOGPATH = GOOGPATH;
?>

<style>
    .dropzone {
        /*height: 70px;*/
        /*min-height: 0px !important;*/
        border: 1px solid #ccc;
    }
</style>


<section class="content" style="max-width: 1500px; ">
    <h2 class="page-header">User Profile</h2>
    <div class="row">

        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#runEnvDiv" data-toggle="tab" aria-expanded="true">Run Environments</a></li>
                    <?php
                    if ($login == 1 && ($SHOW_GROUPS != false || !empty($admin_id) || $role == "admin")) {
                        echo '<li class=""><a href="#groups" data-toggle="tab" aria-expanded="false">Groups</a></li>';
                    }
                    if ($login == 1 && ($SHOW_SSH_KEYS != false || !empty($admin_id) || $role == "admin")) {
                        echo '<li class=""><a href="#sshKeys" data-toggle="tab" aria-expanded="false">SSH Keys</a></li>';
                    }
                    if ($login == 1 && ($SHOW_AMAZON_KEYS != false || !empty($admin_id) || $role == "admin")) {
                        echo '<li class=""><a href="#amazonKeys" data-toggle="tab" aria-expanded="false">Amazon Keys</a></li>';
                    }
                    if ($login == 1 && !empty($GOOGPATH) && ($SHOW_GOOGLE_KEYS != false || !empty($admin_id) || $role == "admin")) {
                        echo '<li class=""><a href="#googleKeys" data-toggle="tab" aria-expanded="false">Google Keys</a></li>';
                    }
                    if ($login == 1 && ($SHOW_GIT != false || !empty($admin_id) || $role == "admin")) {
                        echo '<li class=""><a href="#github" data-toggle="tab" aria-expanded="false">GitHub</a></li>';
                    }
                    echo '<li class=""><a href="#changePass" data-toggle="tab" aria-expanded="false">Change Password</a></li>';
                    echo '<li class=""><a href="#changeNotifTab" data-toggle="tab" aria-expanded="false">Notification</a></li>';
                    if ($login == 1 && $role == "admin") {
                        echo '<li id="adminTabBut"  class=""><a href="#adminTab" data-toggle="tab" aria-expanded="false">Admin</a></li>';
                    }
                    ?>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="runEnvDiv">
                        <div class="panel panel-default" id="profilepanel">
                            <div class="panel-heading clearfix">
                                <div class="pull-right">
                                    <button type="button" class="btn btn-primary btn-sm" id="addEnv" data-toggle="modal" data-target="#profilemodal">Add environment</button>
                                </div>
                                <div class="pull-left">
                                    <h5><i class="fa fa-user " style="margin-left:0px; margin-right:0px;"></i> Run Environments</h5>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="profilesTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th scope="col">Profile Name</th>
                                            <th scope="col">Type</th>
                                            <th scope="col">Details</th>
                                            <th scope="col">Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                        <?php
                        if ($login == 1 && $role == "admin") {
                            echo '<div class="panel panel-default" id="publicprofilepanel">
                                <div class="panel-heading clearfix">
                                    <div class="pull-right">
                                        <button type="button" class="btn btn-primary btn-sm" id="addPublicProfile" data-toggle="modal" data-target="#profilemodal">Add Public Profile</button>
                                    </div>
                                    <div class="pull-left">
                                        <h5><i class="fa fa-user " style="margin-left:0px; margin-right:0px;"></i> Public Run Environment Templates</h5>
                                    </div>
                                </div>
                                <div class="panel-body" style="overflow-x:auto;">
                                    <table id="publicProfileTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Profile Host</th>
                                                <th>Type</th>
                                                <th>Details</th>
                                                <th>Options</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>';
                        }
                        ?>

                    </div>
                    <!-- /.tab-pane ends -->

                    <!-- /.tab-pane starts -->
                    <div class="tab-pane" id="groups">
                        <div class="panel panel-default" id="grouptablepanel">
                            <div class="panel-heading clearfix">
                                <div class="pull-right">
                                    <!--                                    <button type="button" class="btn btn-primary btn-sm" id="joingroup" data-toggle="modal" data-target="#joinmodal">Join a Group</button>-->
                                    <button type="button" class="btn btn-primary btn-sm" id="addgroup" data-toggle="modal" data-target="#groupmodal">Create a Group</button>
                                </div>
                                <div class="pull-left">
                                    <h5><i class="fa fa-group " style="margin-left:0px; margin-right:0px;"></i> Groups</h5>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="grouptable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Group Name</th>
                                            <th>Owner</th>
                                            <th>Created on</th>
                                            <th>Options</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane ends -->
                    <!-- /.tab-pane starts -->
                    <div class="tab-pane" id="sshKeys">
                        <div class="panel panel-default" id="sshKeystablepanel">
                            <div class="panel-heading clearfix">
                                <div class="pull-right">
                                    <button type="button" class="btn btn-primary btn-sm" id="addSSHKey" data-toggle="modal" data-target="#sshKeyModal">Add SSH Key</button>
                                </div>
                                <div class="pull-left">
                                    <h5><i class="fa fa-group " style="margin-left:0px; margin-right:0px;"></i> SSH Keys</h5>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="sshKeyTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>SSH Key Name</th>
                                            <th>Modified on</th>
                                            <th>Options</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane ends -->
                    <!-- /.tab-pane starts -->
                    <div class="tab-pane" id="amazonKeys">
                        <div class="panel panel-default" id="amazonKeystablepanel">
                            <div class="panel-heading clearfix">
                                <div class="pull-right">
                                    <button type="button" class="btn btn-primary btn-sm" id="addAmazonKey" data-toggle="modal" data-target="#amzKeyModal">Add Amazon Key</button>
                                </div>
                                <div class="pull-left">
                                    <h5><i class="fa fa-group " style="margin-left:0px; margin-right:0px;"></i> Amazon Keys</h5>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="amzKeyTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Amazon Key Name</th>
                                            <th>Created on</th>
                                            <th>Options</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane ends -->
                    <!-- /.tab-pane starts -->
                    <div class="tab-pane" id="googleKeys">
                        <div class="panel panel-default" id="googleKeystablepanel">
                            <div class="panel-heading clearfix">
                                <div class="pull-right">
                                    <button type="button" class="btn btn-primary btn-sm" id="addGoogleKey" data-toggle="modal" data-target="#googleKeysModal">Add Google Key</button>
                                </div>
                                <div class="pull-left">
                                    <h5><i class="fa fa-group " style="margin-left:0px; margin-right:0px;"></i> Google Keys</h5>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="googleKeyTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>Google Key Name</th>
                                            <th>Created on</th>
                                            <th>Options</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane ends -->
                    <!-- /.tab-pane starts -->
                    <div class="tab-pane" id="github">
                        <div class="panel panel-default">
                            <div class="panel-heading clearfix">
                                <div class="pull-right">
                                    <button type="button" class="btn btn-primary btn-sm" id="addGithub" data-toggle="modal" data-target="#githubModal">Add GitHub Account</button>
                                </div>
                                <div class="pull-left">
                                    <h5><i class="fa fa-group " style="margin-left:0px; margin-right:0px;"></i> GitHub Accounts</h5>
                                </div>
                            </div>
                            <div class="panel-body">
                                <table id="githubTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th>GitHub Username</th>
                                            <th>GitHub E-Mail</th>
                                            <th>Modified on</th>
                                            <th>Options</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane ends -->
                    <!-- /.tab-pane starts -->
                    <div class="tab-pane" id="changePass">
                        <div class="panel panel-default" id="changePassPanel">
                            <div class="panel-heading clearfix">
                                <div class="pull-left">
                                    <h5><i class="fa fa-group " style="margin-left:0px; margin-right:0px;"></i> Change Password</h5>
                                </div>
                            </div>
                            <div class="panel-body">
                                <div class="col-sm-4 ">
                                    <p>Use the form below to change your password.</p>
                                    <form method="post" id="passwordForm">
                                        <div class="form-group">
                                            <div>
                                                <input type="password" class="input form-control" name="password0" id="password0" placeholder="Old Password" autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div>
                                                <input type="password" class="input form-control" name="password1" id="password1" placeholder="New Password" autocomplete="off">
                                                <div class="row" id="8charDiv" style="display:none;">
                                                    <div class="col-sm-6">
                                                        <span id="8char" class="glyphicon glyphicon-remove" style="color:#FF0004;"></span> 8 Characters Long<br>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div>
                                                <input type="password" class="input form-control" name="password2" id="password2" placeholder="Repeat New Password" autocomplete="off">
                                                <div class="row">
                                                    <div class="col-sm-12" id="pwmatchDiv" style="display:none;">
                                                        <span id="pwmatch" class="glyphicon glyphicon-remove" style="color:#FF0004;"></span> Passwords Match
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button id="changePassBtn" type="submit" class="col-xs-12 btn btn-primary btn-load" data-loading-text="Changing Password...">Change Password </button>
                                    </form>
                                </div>
                                <!--/col-sm-6-->
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane ends -->
                    <!-- /.tab-pane starts -->
                    <div class="tab-pane" id="changeNotifTab">
                        <div class="panel panel-default">
                            <div class="panel-heading clearfix">
                                <div class="pull-left">
                                    <h5><i class="fa fa-group " style="margin-left:0px; margin-right:0px;"></i> Notification Settings</h5>
                                </div>
                            </div>
                            <div class="panel-body">
                                <form id="notifSettings">
                                    <div class="col-md-12" style="display: block;">
                                        <div>
                                            <label> <input type="checkbox" id="email_notif" name="email_notif"> Receive an email about completed or failed runs</label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane ends -->
                    <!-- /.tab-pane starts -->
                    <?php
                    if ($login == 1 && $role == "admin") {
                        echo '<div class="tab-pane" id="adminTab">
                                <div class="panel panel-default">
                                    <div class="panel-heading clearfix">
                                        <div class="pull-right">
                                            <button type="button" class="btn btn-primary btn-sm" id="addUser" data-toggle="modal" data-target="#userModal">Add User</button>
                                        </div>
                                        <div class="pull-left">
                                            <h5><i class="fa fa-group " style="margin-left:0px; margin-right:0px;"></i> User Panel</h5>
                                        </div>
                                    </div>
                                    <div class="panel-body" style="overflow-x:auto;">
                                        <table id="AdminUserTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Name</th>
                                                <th>Username</th>
                                                <th>E-mail</th>
                                                <th>Institute</th>
                                                <th>Usage(MB)</th>
                                                <th>Lab</th>
                                                <th>Role</th>
                                                <th>Active</th>
                                                <th>Member Date</th>
                                                <th>Options</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>';
                    }
                    ?>


                </div>
                <!-- /.tab-content -->
            </div>
            <!-- /.nav-tabs-custom -->
        </div>

    </div>
</section>




<!-- profilemodal  Starts-->
<div id="profilemodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mAddEnvTitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mEnvId" class="col-sm-3 control-label">ID</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvId" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mEnvName" class="col-sm-3 control-label">Profile Name</label>
                        <div class="col-sm-9">
                            <select id="mEnvName" class="fbtn btn-default form-control" name="name">
                                <option value="" disabled selected>Choose or Type New Profile Name</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="chooseEnv" class="col-sm-3 control-label">Type</label>
                        <div class="col-sm-9">
                            <select style="width:150px" id="chooseEnv" class="fbtn btn-default form-control" name="runEnv">
                                <option value="" disabled selected>Select environment </option>
                                <option value="cluster">Host</option>
                                <option value="amazon">Amazon Web Services</option>
                                <?php if (!empty($GOOGPATH)) {
                                    echo '<option value="google">Google Cloud</option>';
                                } ?>
                            </select>
                        </div>
                    </div>
                    <div id="mEnvUsernameDiv" class="form-group" style="display:none">
                        <label for="mEnvUsername" class="col-sm-3 control-label">Username
                            <span><a data-toggle="tooltip" data-placement="bottom" title="username@hostname (eg. us2r@ghpcc06.umassrc.org)"><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvUsername" name="username">
                        </div>
                    </div>
                    <div id="mEnvHostnameDiv" class="form-group" style="display:none">
                        <label for="mEnvHostname" class="col-sm-3 control-label">Hostname
                            <span><a data-toggle="tooltip" data-placement="bottom" title="username@hostname (eg. us2r@ghpcc06.umassrc.org)"><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvHostname" name="hostname">
                        </div>
                    </div>
                    <div id="mEnvPortDiv" class="form-group" style="display:none">
                        <label for="mEnvPort" class="col-sm-3 control-label">SSH Port (optional)
                            <span><a data-toggle="tooltip" data-placement="bottom" title="By default TCP port 22 is used for SSH connection. You can change this default by entering port number."><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvPort" name="port">
                        </div>
                    </div>
                    <div id="mEnvSSHKeyDiv" class="form-group" style="display:none">
                        <label for="mEnvSSHKey" class="col-sm-3 control-label">SSH Keys
                            <span><a data-toggle="tooltip" data-placement="bottom" title="Keys that are saved in SSH keys tab and to be used while connecting to host"><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <select id="mEnvSSHKey" class="fbtn btn-default form-control" name="ssh_id">
                                <option value="" disabled selected>Select SSH Keys </option>
                            </select>
                        </div>
                    </div>
                    <div id="mEnvAmzKeyDiv" class="form-group" style="display:none">
                        <label for="mEnvAmzKey" class="col-sm-3 control-label">Amazon Keys<span><a data-toggle="tooltip" data-placement="bottom" title="Keys that are saved in Amazon keys tab and to be used while connecting to Amazon Cloud"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <select id="mEnvAmzKey" class="fbtn btn-default form-control" name="amazon_cre_id">
                                <option value="" disabled selected>Select Amazon Keys </option>
                            </select>
                        </div>
                    </div>
                    <div id="mEnvGoogKeyDiv" class="form-group" style="display:none">
                        <label for="" class="col-sm-3 control-label">Google Keys<span><a data-toggle="tooltip" data-placement="bottom" title="Keys that are saved in Google keys tab and to be used while connecting to Google Cloud"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <select id="mEnvGoogKey" class="fbtn btn-default form-control" name="google_cre_id">
                                <option value="" disabled selected>Select Google Keys </option>
                            </select>
                        </div>
                    </div>
                    <div id="mEnvZoneDiv" class="form-group" style="display:none">
                        <label class="col-sm-3 control-label">Zone</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvZone" name="zone">
                        </div>
                    </div>
                    <div id="mEnvInsTypeDiv" class="form-group" style="display:none">
                        <label for="mEnvInsType" class="col-sm-3 control-label">Instance Type <span><a data-toggle="tooltip" data-placement="bottom" title=" Amazon EC2 or Google Cloud instance type that comprise varying combinations of CPU, memory, storage, and networking capacity (eg. m3.xlarge)."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvInsType" name="instance_type">
                        </div>
                    </div>
                    <div id="mEnvImageIdDiv" class="form-group" style="display:none">
                        <label for="mEnvImageId" class="col-sm-3 control-label">Image Id <span><a data-toggle="tooltip" data-placement="bottom" title=" Virtual machine ID"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvImageId" name="image_id">
                        </div>
                    </div>
                    <div id="mDefWorkDirDiv" class="form-group" style="display:none">
                        <label for="mDefWorkDir" class="col-sm-3 control-label">Default Working Directory <span><a data-toggle="tooltip" data-placement="bottom" title="Default directory where dolphinnext runs will be executed. It is mandatory for google cloud. e.g. /data/dnext."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mDefWorkDir" name="def_workdir">
                        </div>
                    </div>
                    <div id="mDefPublishDirDiv" class="form-group" style="display:none">
                        <label for="mDefPublishDir" class="col-sm-3 control-label">Default Bucket Location for Publishing <span><a data-toggle="tooltip" data-placement="bottom" title="Default bucket location where dolphinnext reports will be published. It is mandatory for google cloud and you can always edit this path in the run page. e.g. s3://bucket/dnext or gs://bucket/dnext"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input style="margin-top:10px;" type="text" class="form-control" id="mDefPublishDir" name="def_publishdir">
                        </div>
                    </div>
                    <div id="mSubnetIdDiv" class="form-group" style="display:none">
                        <label for="mSubnetId" class="col-sm-3 control-label">Subnet Id <span><a data-toggle="tooltip" data-placement="bottom" title="Identifier of the VPC subnet to be applied e.g. subnet-05222a43."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mSubnetId" name="subnet_id">
                        </div>
                    </div>
                    <div id="mSecurityGroupDiv" class="form-group" style="display:none">
                        <label for="mSecurityGroup" class="col-sm-3 control-label">Security Group (opt.)<span><a data-toggle="tooltip" data-placement="bottom" title="Identifier of the security group to be applied e.g. sg-df72b9ba."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>

                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mSecurityGroup" name="security_group">
                        </div>
                    </div>
                    <div id="mSharedStorageIdDiv" class="form-group" style="display:none">
                        <label for="mSharedStorageId" class="col-sm-3 control-label">Shared Storage Id <span><a data-toggle="tooltip" data-placement="bottom" title="Identifier of the shared file system instance e.g. fs-1803efd1."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mSharedStorageId" name="shared_storage_id">
                        </div>
                    </div>
                    <div id="mSharedStorageMountDiv" class="form-group" style="display:none">
                        <label for="mSharedStorageMount" class="col-sm-3 control-label">Shared Storage Mount <span><a data-toggle="tooltip" data-placement="bottom" title="Mount path of the shared file system e.g. /mnt/efs"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mSharedStorageMount" value="/mnt/efs" name="shared_storage_mnt">
                        </div>
                    </div>
                    <div id="mEnvCmdDiv" class="form-group" style="display:none">
                        <label for="mEnvCmd" class="col-sm-3 control-label">Run command
                            <span><a data-toggle="tooltip" data-placement="bottom" title="You may run the command or commands (by seperating each command with && sign) before the nextflow job starts. (eg. source /etc/bashrc && module load java/1.8.0_31)"><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <textarea type="text" rows="2" class="form-control" id="mEnvCmd" name="cmd"></textarea>
                        </div>
                    </div>
                    <div id="mEnvNextPathDiv" class="form-group" style="display:none">
                        <label for="mEnvNextPath" class="col-sm-3 control-label">Nextflow Path
                            <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter the path of the nextflow, if it is not added to $PATH environment. (eg. /project/umw_biocore/bin for ghpcc06.umassrc.org)"><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvNextPath" name="next_path">
                        </div>
                    </div>
                    <div id="mEnvSinguCacheDiv" class="form-group" style="display:none">
                        <label for="mEnvSinguCache" class="col-sm-3 control-label">Singularity Cache Folder
                            <span><a data-toggle="tooltip" data-placement="bottom" title="Directory where remote Singularity images are stored. By default home directory is used. Note: When using a computing cluster it must be a shared folder accessible from all computing nodes."><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvSinguCache" name="singu_cache">
                        </div>
                    </div>
                    <div id="mEnvVarDiv" class="form-group" style="display:none">
                        <label for="mEnvVar" class="col-sm-3 control-label">Profile Variables
                            <span><a data-toggle="tooltip" data-placement="bottom" title="You can set commonly used pipeline variables here, such as params.DOWNDIR. (eg. params.DOWNDIR = '/share/dnext_data'). You can enter multiple variables by separating them with newline."><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <textarea type="text" rows="1" class="form-control" id="mEnvVar" name="variable"></textarea>
                        </div>
                    </div>
                    <div id="mBashVarDiv" class="form-group" style="display:none">
                        <label for="mBashVar" class="col-sm-3 control-label">Environment Variables
                            <span><a data-toggle="tooltip" data-placement="bottom" title="You can set BASH environmental variables here, such as APIKEY. (eg. APIKEY='secretkey'). Please don't use spaces, you can enter multiple variables by separating them with newline."><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <textarea type="text" rows="1" class="form-control" id="mBashVar" name="bash_variable"></textarea>
                        </div>
                    </div>
                    <div id="mExecDiv" class="form-group" style="display:none">
                        <label for="mExec" class="col-sm-3 control-label">Executor of Nextflow</label>
                        <div class="col-sm-9">
                            <select style=" width:150px" id="mExec" class="fbtn btn-default form-control" name="executor">
                                <option class="hideClu" value="local">Local</option>
                                <option value="sge">SGE</option>
                                <option value="lsf">LSF</option>
                                <option value="slurm">SLURM</option>
                                <!--<option value="ignite">IGNITE</option>-->
                                <!--<option value="pbs">PBS/Torque</option>-->
                                <!--<option value="nqsii">NQSII</option>-->
                                <!--<option value="condor">HTCondor</option>-->
                                <!--<option value="k8s">Kubernetes</option>-->
                                <!--<option value="awsbatch">AWS Batch</option>-->
                            </select>
                        </div>
                    </div>
                    <div id="execNextDiv" class="form-group" style="display:none">
                        <label for="execNext" class="col-sm-3 control-label">Executor Settings for Nextflow</label>
                        <div id="execNextSett" class="col-sm-9">
                            <div class="panel panel-default">
                                <table id="execNextSettTable" class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col" id="execNextQueue">Queue</th>
                                            <th scope="col">Memory(GB)</th>
                                            <th scope="col">CPUs</th>
                                            <th scope="col">Time(min.)</th>
                                            <th scope="col">Other Options</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input id="next_queue" name="next_queue" class="form-control" type="text" value="short"></td>
                                            <td><input id="next_memory" class="form-control" type="text" name="next_memory" value="32"></td>
                                            <td><input id="next_cpu" name="next_cpu" class="form-control" type="text" value="1"></td>
                                            <td><input id="next_time" name="next_time" class="form-control" type="text" value="100"></td>
                                            <td><input id="next_clu_opt" name="next_clu_opt" class="form-control" type="text" value=""></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="mExecJobDiv" class="form-group" style="display:none">
                        <label for="mExecJob" class="col-sm-3 control-label">Executor of Nextflow Jobs</label>
                        <div class="col-sm-9">
                            <select style=" width:150px" id="mExecJob" class="fbtn btn-default form-control" name="executor_job">
                                <option value="local">Local</option>
                                <option value="sge">SGE</option>
                                <option value="lsf">LSF</option>
                                <option value="slurm">SLURM</option>
                                <option value="ignite">IGNITE</option>
                                <option value="awsbatch">AWS Batch</option>
                                <!-- <option value="pbs">PBS/Torque</option>-->
                                <!-- <option value="nqsii">NQSII</option>-->
                                <!-- <option value="condor">HTCondor</option>-->
                                <!-- <option value="k8s">Kubernetes</option>-->
                            </select>
                        </div>
                    </div>
                    <div id="execJobSetDiv" class="form-group" style="display:none">
                        <label for="execJobSet" class="col-sm-3 control-label">Executor Settings for Nextflow Jobs</label>
                        <div id="execJobSet" class="col-sm-9">
                            <div class="panel panel-default">
                                <table id="execJobSetTable" class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col" id="execJobQueue">Queue</th>
                                            <th scope="col">Memory(GB)</th>
                                            <th scope="col">CPUs</th>
                                            <th scope="col">Time(min.)</th>
                                            <th scope="col">Other Options</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input id="job_queue" name="job_queue" class="form-control" type="text" value="short"></td>
                                            <td><input id="job_memory" class="form-control" type="text" name="job_memory" value="32"></td>
                                            <td><input id="job_cpu" name="job_cpu" class="form-control" type="text" value="1"></td>
                                            <td><input id="job_time" name="job_time" class="form-control" type="text" value="100"></td>
                                            <td><input id="job_clu_opt" name="job_clu_opt" class="form-control" type="text" value=""></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div id="mEnvAmzKeyDiv2" class="form-group" style="display:none">
                        <label for="mEnvAmzKey2" class="col-sm-3 control-label">Amazon Keys<span><a data-toggle="tooltip" data-placement="bottom" title="Keys that are saved in Amazon keys tab and to be used while creating instance in Amazon Cloud"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <select id="mEnvAmzKey2" class="fbtn btn-default form-control" name="amazon_cre_id2">
                                <option value="" selected>Select Amazon Keys </option>
                            </select>
                        </div>
                    </div>
                    <div id="mDefWorkDirDiv2" class="form-group" style="display:none">
                        <label for="mDefWorkDir2" class="col-sm-3 control-label">Default Working Directory <span><a data-toggle="tooltip" data-placement="bottom" title="Default directory where dolphinnext runs will be executed. e.g. /data/dnext."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mDefWorkDir2" name="def_workdir2">
                        </div>
                    </div>
                    <div id="mDefPublishDirDiv2" class="form-group" style="display:none">
                        <label for="mDefPublishDir2" class="col-sm-3 control-label">Default Bucket Location for Publishing <span><a data-toggle="tooltip" data-placement="bottom" title="Default bucket location where dolphinnext reports will be published. You can always edit this path in the run page. e.g. s3://bucket/dnext or gs://bucket/dnext"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input style="margin-top:10px;" type="text" class="form-control" id="mDefPublishDir2" name="def_publishdir2">
                        </div>
                    </div>
                    <?php
                    if ($login == 1 && (!empty($admin_id) || $role == "admin")) {
                        echo '<div id="shareRunEnvDiv" class="form-group" style="display:none">
                        <label class="col-sm-3 control-label">
                        <input type="checkbox" id="shareRunEnv"> Share with Group </input></label>
                        <div class="col-sm-3">
                            <select id="groupSel" style="width:100%;" class="fbtn btn-default form-control" name="group_id">
                            <option value="" disabled selected>Choose group </option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" id="auto_workdir" name="auto_workdir" placeholder="Please enter generic work directory">
                        </div>
                    </div>';
                    }
                    ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveEnv" data-clickedrow="">Save changes</button>
            </div>
        </div>
    </div>
</div>
<!-- profilemodal Ends-->

<!-- group modal starts-->
<div id="groupmodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="groupmodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mGroupID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mGroupID" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mGroupName" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mGroupName" name="name">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savegroup" data-clickedrow="">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- group modal ends-->

<!---- join Modal-->
<div id="joinmodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="joinmodallabel">Join a group</h4>
            </div>
            <div class="modal-body">

                <form id="joinmodaladd" class="form-horizontal" style="margin-bottom:40px;">
                    <h5 style="margin-bottom:20px;">Add New Member</h5>
                    <div class="form-group">
                        <label class="col-sm-2 control-label" style="padding-left:30px;">E-Mail of the User <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Please enter e-mail address of the user and press add button."><i class="glyphicon glyphicon-info-sign"></i></a></span> </label>
                        <div class="col-sm-8">
                            <input type="email" class="form-control" id="joinmodal_email" name="email" value="">
                        </div>
                        <div class="col-sm-2">
                            <button id="joinmodal_adduser" type="button" class="btn btn-primary">Add User <i class="fa fa-user"></i></button>
                        </div>
                    </div>
                </form>
                <h5 style="margin-bottom:10px;">Group Members</h5>
                <div class="panel-body">
                    <table id="groupmembertable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Username</th>
                                <th>E-mail</th>
                                <th>Options</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="groupmembertabledata" onclick="">OK</button>
            </div>

        </div>
    </div>
</div>


<!---- join Modal ends-->




<!-- ssh keys modal starts-->

<div id="sshKeyModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="sshkeysmodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mSSHKeysID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mSSHKeysID" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mSSHName" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mSSHName" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="userKeyCheck" class="col-sm-4 control-label"><input type="checkbox" id="userKeyCheck" name="check_userkey" data-toggle="collapse" data-target="#userKeyDiv"> Use your own keys </input><span><a data-toggle="tooltip" data-placement="bottom" title="Use your own ssh keys and paste them below"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                    </div>
                    <div id="userKeyDiv" class="collapse">
                        <div id="mUserPriKeyDiv" class="form-group">
                            <label for="mUserPriKey" class="col-sm-4 control-label">Private Key<span><a data-toggle="tooltip" data-placement="bottom" title="Key to be used while connecting to a host"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                            <div class="col-sm-8">
                                <textarea type="text" rows="5" class="form-control" id="mUserPriKey" name="prikey"></textarea>
                            </div>
                        </div>
                        <div id="mUserPubKeyDiv" class="form-group">
                            <label for="mUserPubKey" class="col-sm-4 control-label">Public Key<span><a data-toggle="tooltip" data-placement="bottom" title="Key to to be added into '~/.ssh/authorized_keys' in the host by user"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                            <div class="col-sm-8">
                                <textarea type="text" rows="5" class="form-control" id="mUserPubKey" name="pubkey"></textarea>
                                <p style="font-size:13px;"><b style="color:blue;">* Important Information:</b> Private key will be used for submiting jobs in the host. Therefore, public key of the private key required to be added into '~/.ssh/authorized_keys' in the host by user. Please check <a style="color:blue;" target="_blank" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/profile.html#ssh-keys">adding keys section</a> for more information.</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ourKeyCheck" class="col-sm-4 control-label"><input type="checkbox" id="ourKeyCheck" name="check_ourkey" data-toggle="collapse" data-target="#ourKeyDiv"> Create new keys </input><span><a data-toggle="tooltip" data-placement="bottom" title="Create new ssh keys that are specifically produced for you by clicking generate keys button"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                    </div>
                    <div id="ourKeyDiv" class="collapse">
                        <div id="createKeysDiv" class="form-group">
                            <label for="createKeysButton" class="col-sm-4 control-label">Generate Keys<span><a data-toggle="tooltip" data-placement="bottom" title="Click button to generate new keys for you"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                            <div class="col-sm-8">
                                <button type="button" id="createKeysButton" class="btn btn-primary" onclick="generateKeys()">Generate Keys</button>
                            </div>
                        </div>
                        <div id="mOurPriKeyDiv" class="form-group" style="display:none;">
                            <label for="mOurPriKey" class="col-sm-4 control-label">Private Key<span><a data-toggle="tooltip" data-placement="bottom" title="Key to be used while connecting to a host"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                            <div class="col-sm-8">
                                <textarea type="text" rows="5" class="form-control" id="mOurPriKey" name="prikey"></textarea>
                            </div>
                        </div>
                        <div id="mOurPubKeyDiv" class="form-group" style="display:none;">
                            <label for="mOurPubKey" class="col-sm-4 control-label">Public Key<span><a data-toggle="tooltip" data-placement="bottom" title="Key to to be added into '~/.ssh/authorized_keys' in the host by user"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                            <div class="col-sm-8">
                                <textarea type="text" rows="5" class="form-control" id="mOurPubKey" name="pubkey"></textarea>
                                <p style="font-size:13px;"><b style="color:blue;">* Important Information:</b> Private key will be used for submiting jobs in the host. Therefore, public key of the private key required to be added into '~/.ssh/authorized_keys' in the host by user. Please check <a style="color:blue;" target="_blank" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/profile.html#ssh-keys">adding keys section</a> for more information.</p>
                            </div>
                        </div>
                    </div>
                    <?php
                    if ($login == 1 && (!empty($admin_id) || $role == "admin")) {
                        echo '<div id="hideKeysDiv" class="form-group">
                        <label class="col-sm-4 control-label">
                        <input type="checkbox" id="hideKeys"> Hide Keys from User </input></label>
                    </div>';
                    }
                    ?>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savesshkey" data-clickedrow="">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- ssh modal ends-->

<!-- amz keys modal starts-->
<div id="amzKeyModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="amzkeysmodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mAmzKeysID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mAmzKeysID" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mAmzName" class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mAmzName" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mAmzDefReg" class="col-sm-3 control-label">Default Region</label>
                        <div class="col-sm-9">
                            <select id="mAmzDefReg" class="fbtn btn-default form-control" name="amz_def_reg">
                                <option value="us-east-2">US East (Ohio) (us-east-2) </option>
                                <option value="us-east-1">US East (N. Virginia) (us-east-1)</option>
                                <option value="us-west-1">US West (N. California) (us-west-1)</option>
                                <option value="us-west-2">US West (Oregon) (us-west-2)</option>
                                <option value="ap-northeast-1">Asia Pacific (Tokyo) (ap-northeast-1)</option>
                                <option value="ap-northeast-2">Asia Pacific (Seoul) (ap-northeast-2)</option>
                                <option value="ap-south-1">Asia Pacific (Mumbai) (ap-south-1)</option>
                                <option value="ap-southeast-1">Asia Pacific (Singapore) (ap-southeast-1)</option>
                                <option value="ap-southeast-2">Asia Pacific (Sydney) (ap-southeast-2)</option>
                                <option value="ca-central-1">Canada (Central) (ca-central-1)</option>
                                <option value="cn-north-1">China (Beijing) (cn-north-1)</option>
                                <option value="cn-northwest-1">China (Ningxia) (cn-northwest-1)</option>
                                <option value="eu-central-1">EU (Frankfurt) (eu-central-1)</option>
                                <option value="eu-west-1">EU (Ireland) (eu-west-1)</option>
                                <option value="eu-west-2">EU (London) (eu-west-2)</option>
                                <option value="eu-west-3">EU (Paris) (eu-west-3)</option>
                                <option value="sa-east-1">South America (Sao Paulo) (sa-east-1)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mAmzAccKey" class="col-sm-3 control-label">Access Key</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="mAmzAccKey" name="amz_acc_key">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mAmzSucKey" class="col-sm-3 control-label">Secret Key</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="mAmzSucKey" name="amz_suc_key">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveamzkey" data-clickedrow="">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- amz keys modal ends-->

<!-- github modal starts-->
<div id="githubModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="githubmodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mGitID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mGitID" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mGitName" class="col-sm-3 control-label">GitHub Username</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mGitUsername" name="username">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mGitEmail" class="col-sm-3 control-label">GitHub E-mail</label>
                        <div class="col-sm-9">
                            <input type="email" required class="form-control" id="mGitEmail" name="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Access Token</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" name="token">
                            <p style="font-size:13px;">GitHub access token will be used for creating and updating GitHub repositories. Please follow <a style="color:blue;" target="_blank" href="https://docs.github.com/en/free-pro-team@latest/github/authenticating-to-github/creating-a-personal-access-token"> this guide in GitHub to create a token.</a> While creating token, please enable <b>repo</b> and <b>write:packages</b> in the scope section.</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveGithub">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- amz keys modal ends-->

<!-- google keys modal starts-->
<div id="googleKeysModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="googleKeysModalTitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mGoogID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mGoogID" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mGoogName" class="col-sm-3 control-label">Key Name <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter any name for your key"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mGoogName" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mGoogProjectID" class="col-sm-3 control-label">Project ID <span><a data-toggle="tooltip" data-placement="bottom" title="Please get Project ID by following these steps: Open the Google Cloud Console. On the dashboard, find 'Project Info' box and copy 'Project ID' field. eg.dolphinnext-193616"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mGoogProjectID" name="project_id">
                        </div>
                    </div>
                </form>
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Service Account Key <span><a data-toggle="tooltip" data-placement="bottom" title="Please download the credentials file by following these steps: Open the Google Cloud Console. Go to APIs & Services → Credentials. Click on the Create credentials. Choose Service account key. Click the Create button and download the JSON file e.g. creds.json"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9" id="key_name_div">
                            <p style="padding-top:7px;"><span id="key_name_span"></span><button type="button" id="delGoogKeyIcon" class="btn" name="button" data-toggle="modal" data-backdrop="false" data-target="#confirmDelModal" style="background:none; margin:0px; padding:0px;"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Google Key"><i class="glyphicon glyphicon-trash"></i></a></button></p>
                        </div>
                        <div class="col-sm-9" id="key_import_div">
                            <form id="mgoogkeyform" action="ajax/import.php" class="dropzone">
                                <div class="fallback ">
                                    <input name="file" type="file" />
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveGoogle">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- google keys modal ends-->

<!-- user modal starts-->
<div id="userModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="userModalTitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mUserID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mUserID" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mUserName" class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-9">
                            <input type="text" maxlength="45" required class="form-control" id="mUserName" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mUserUsername" class="col-sm-3 control-label">Username</label>
                        <div class="col-sm-9">
                            <input type="text" maxlength="45" required class="form-control" id="mUserUsername" name="username">
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="mUserEmail" class="col-sm-3 control-label">E-mail</label>
                        <div class="col-sm-9">
                            <input type="email" maxlength="45" required class="form-control" id="mUserEmail" name="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mUserInstitute" class="col-sm-3 control-label">Institute</label>
                        <div class="col-sm-9">
                            <input type="text" maxlength="45" required class="form-control" id="mUserInstitute" name="institute">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mUserLab" class="col-sm-3 control-label">Lab</label>
                        <div class="col-sm-9">
                            <input type="text" maxlength="45" class="form-control" id="mUserLab" name="lab">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Login Type (optional)<span><a data-toggle="tooltip" data-placement="bottom" title="Please leave this field unselected if users sign in through OKTA."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <select id="mUserLoginType" class="form-control" name="logintype">
                                <option value="" disabled selected> Select Login Type </option>
                                <option value="password">Password</option>
                                <option value="google">Google</option>
                                <option value="ldap">LDAP</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savemUser" data-clickedrow="">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- user modal ends-->


<!-- admin Add Group Modal starts-->
<div id="adminAddGroupModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add User into New Group</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="adminAddGroupNewGroup" class="col-sm-3 control-label">User Info:</label>
                        <div class="col-sm-9">
                            <p id="adminAddGroupUserInfo"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="adminAddGroupNewGroup" class="col-sm-3 control-label">Choose Group</label>
                        <div class="col-sm-9">
                            <select id="adminAddGroupNewGroup" class="form-control" name="group_id">
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="adminAddGroupSave">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- admin add group modal ends-->

<div id="warnDelete" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Information</h4>
            </div>
            <div class="modal-body">
                <span id="warnDelText">Text</span>
                </br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!--Confirm Del Modal-->

<div id="confirmDelModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="confirmDelModalTitle">Confirm</h4>
            </div>
            <div class="modal-body" id="confirmDelModalText">Text</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="confirmDelModalDelBtn">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!--Confirm Del Modal Ends-->

<!--Confirm Modal-->

<div id="confirmDelAmzModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="confirmDelAmzModalTitle">Confirm</h4>
            </div>
            <div class="modal-body" id="confirmDelAmzModalText">Text</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="mDelAmzBtn">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!--Confirm Modal Ends-->

<!--Confirm Modal-->

<div id="confirmDelProModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="confirmDelProModalTitle">Confirm</h4>
            </div>
            <div class="modal-body" id="confirmDelProModalText">Text</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="mDelProBtn">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!--Confirm Modal Ends-->