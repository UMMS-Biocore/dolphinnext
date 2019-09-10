<?php
    if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
    $ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
    $name = isset($_SESSION['name']) ? $_SESSION['name'] : "";
    $admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : "";
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : "";
    if ($ownerID != ''){$login = 1;} 
    else { $login = 0;}
    session_write_close();

    require_once(__DIR__."/../../config/config.php");
    $SHOW_AMAZON_KEYS= SHOW_AMAZON_KEYS;
    $SHOW_SSH_KEYS=SHOW_SSH_KEYS;
    $SHOW_GROUPS=SHOW_GROUPS;
    $SHOW_GIT=SHOW_GITHUB;
?>

<section class="content" style="max-width: 1500px; ">
    <h2 class="page-header">User Profile</h2>
    <div class="row">

        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#runEnvDiv" data-toggle="tab" aria-expanded="true">Run Environments</a></li>
                    <?php
                        if ($login == 1 && ($SHOW_GROUPS != false || !empty($admin_id) || $role == "admin")){
                            echo '<li class=""><a href="#groups" data-toggle="tab" aria-expanded="false">Groups</a></li>';
                        }
                        if ($login == 1 && ($SHOW_SSH_KEYS != false || !empty($admin_id) || $role == "admin")){
                            echo '<li class=""><a href="#sshKeys" data-toggle="tab" aria-expanded="false">SSH Keys</a></li>';
                        }
                        if ($login == 1 && ($SHOW_AMAZON_KEYS != false || !empty($admin_id) || $role == "admin")){
                            echo '<li class=""><a href="#amazonKeys" data-toggle="tab" aria-expanded="false">Amazon Keys</a></li>';
                        }
                        if ($login == 1 && ($SHOW_GIT != false || !empty($admin_id) || $role == "admin")){
                            echo '<li class=""><a href="#github" data-toggle="tab" aria-expanded="false">GitHub</a></li>';
                        }
                        echo '<li class=""><a href="#changePass" data-toggle="tab" aria-expanded="false">Change Password</a></li>';
                        if ($login == 1 && $role == "admin"){
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
                            if ($login == 1 && $role == "admin"){
                        echo '<div class="panel panel-default" id="publicprofilepanel">
                                <div class="panel-heading clearfix">
                                    <div class="pull-right">
                                        <button type="button" class="btn btn-primary btn-sm" id="addPublicProfile" data-toggle="modal" data-target="#profilemodal">Add Public Profile</button>
                                    </div>
                                    <div class="pull-left">
                                        <h5><i class="fa fa-user " style="margin-left:0px; margin-right:0px;"></i> Public Run Environments</h5>
                                    </div>
                                </div>
                                <div class="panel-body">
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
                    <div class="tab-pane" id="github">
                        <div class="panel panel-default" >
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
                    <?php 
                        if ($login == 1 && $role == "admin"){
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
                                <div class="panel-body">
                                    <table id="AdminUserTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Username</th>
                                                <th>E-mail</th>
                                                <th>Institute</th>
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
                                <option value="amazon">Amazon</option>
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
                    <div id="mEnvInsTypeDiv" class="form-group" style="display:none">
                        <label for="mEnvInsType" class="col-sm-3 control-label">Instance Type</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvInsType" name="instance_type">
                        </div>
                    </div>
                    <div id="mEnvImageIdDiv" class="form-group" style="display:none">
                        <label for="mEnvImageId" class="col-sm-3 control-label">Image Id</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mEnvImageId" name="image_id">
                        </div>
                    </div>
                    <div id="mSubnetIdDiv" class="form-group" style="display:none">
                        <label for="mSubnetId" class="col-sm-3 control-label">Subnet Id</label>
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
                        <label for="mSharedStorageId" class="col-sm-3 control-label">Shared Storage Id</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mSharedStorageId" name="shared_storage_id">
                        </div>
                    </div>
                    <div id="mSharedStorageMountDiv" class="form-group" style="display:none">
                        <label for="mSharedStorageMount" class="col-sm-3 control-label">Shared Storage Mount</label>
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
                            <span><a data-toggle="tooltip" data-placement="bottom" title="You can set commonly used pipeline variables here, such as _DOWNDIR. (eg. _DOWNDIR='/share/data'). You can enter multiple variables by separating them with newline."><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <textarea type="text" rows="1" class="form-control" id="mEnvVar" name="variable"></textarea>
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
                                <!--                                  <option value="none">None </option>-->
                                <option value="local">Local</option>
                                <option value="sge">SGE</option>
                                <option value="lsf">LSF</option>
                                <option value="slurm">SLURM</option>
                                <option value="ignite">IGNITE</option>
                                <!--                                  <option value="pbs">PBS/Torque</option>-->
                                <!--                                  <option value="nqsii">NQSII</option>-->
                                <!--                                  <option value="condor">HTCondor</option>-->
                                <!--                                  <option value="k8s">Kubernetes</option>-->
                                <!--                                  <option value="awsbatch">AWS Batch</option>-->
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="joinmodallabel">Join a group</h4>
            </div>
            <form role="form" method="post">
                <div class="modal-body" style="overflow:scroll">
                    <fieldset>
                        <label id="groupLabel">Select a group to join</label>
                        <div id="groupListDiv" class="form-group">
                            <select id="mGroupList" class="form-control" size="25"></select></div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmGroupButton" class="btn btn-primary" data-dismiss="">Join</button>
                    <button type="button" class="btn btn-default" id="cancelGroupButton" data-dismiss="modal" onclick="">Cancel</button>
                </div>
            </form>
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
                        if ($login == 1 && (!empty($admin_id) || $role == "admin")){
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
                        <label for="mGitPassword" class="col-sm-3 control-label">GitHub Password</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="mGitPassword" name="password">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveGithub" >Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- amz keys modal ends-->


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
                            <input type="text" required class="form-control" id="mUserName" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mUserUsername" class="col-sm-3 control-label">Username</label>
                        <div class="col-sm-9">
                            <input type="text" required class="form-control" id="mUserUsername" name="username">
                        </div>
                    </div>
                    <div class="form-group has-feedback">
                        <label for="mUserEmail" class="col-sm-3 control-label">E-mail</label>
                        <div class="col-sm-9">
                            <input type="email" required class="form-control" id="mUserEmail" name="email">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mUserInstitute" class="col-sm-3 control-label">Institute</label>
                        <div class="col-sm-9">
                            <input type="text" required class="form-control" id="mUserInstitute" name="institute">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mUserLab" class="col-sm-3 control-label">Lab</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mUserLab" name="lab">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mUserLoginType" class="col-sm-3 control-label">Login Type</label>
                        <div class="col-sm-9">
                            <select id="mUserLoginType" class="form-control" name="logintype">
                                <option value="" disabled selected>Select Login Type </option>
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

<!--Confirm Modal-->

<div id="confirmDelModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="confirmDelModalTitle">Confirm</h4>
            </div>
            <div class="modal-body" id="confirmDelModalText">Text</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="mDelBtn">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!--Confirm Modal Ends-->

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
