<section class="content" style="max-width: 1500px; ">
    <h2 class="page-header">User Profile</h2>
    <div class="row">

        <div class="col-md-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active"><a href="#runEnvDiv" data-toggle="tab" aria-expanded="true">Run Environments</a></li>
                    <li class=""><a href="#groups" data-toggle="tab" aria-expanded="false">Groups</a></li>
                    <li class=""><a href="#sshKeys" data-toggle="tab" aria-expanded="false">SSH Keys</a></li>
                    <li class=""><a href="#amazonKeys" data-toggle="tab" aria-expanded="false">Amazon Keys</a></li>
                    <li id="adminTabBut" style="display:none;" class=""><a href="#adminTab" data-toggle="tab" aria-expanded="false">Admin</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="runEnvDiv">
                        <form class="form-horizontal">
                            <div class="panel-heading clearfix">
                                <button class="btn btn-primary" type="button" id="addEnv" data-toggle="modal" href="#profilemodal" style="float:right; vertical-align:middle;">Add environment</button>
                                <h6><b></b></h6>
                            </div>
                            <div class="panel panel-default">
                                <div>
                                    </br>
                                    <table id="profilesTable" class="table">
                                        <thead>
                                            <tr>
                                                <th scope="col">Profile Name</th>
                                                <th scope="col">Type</th>
                                                <th scope="col">Details</th>
                                                <th scope="col">Actions</th>
                                            </tr>
                                            <tr id="noProfile">
                                                <td>No Profile Available</td>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
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
                    <div class="tab-pane" id="adminTab">
                        <div class="panel panel-default">
                            <div class="panel-heading clearfix">
                                <div class="pull-left">
                                    <h5><i class="fa fa-group " style="margin-left:0px; margin-right:0px;"></i> Impersonation Page</h5>
                                </div>
                            </div>
                            <div class="panel-body">
                                <button class="btn btn-primary" type="button" id="impersonUser" data-toggle="modal" href="#impersonModal">Select User</button>
                            </div>
                        </div>
                    </div>
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
                            <input type="text" class="form-control" id="mEnvName" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="chooseEnv" class="col-sm-3 control-label">Type</label>
                        <div class="col-sm-9">
                            <select style="width:150px" id="chooseEnv" class="fbtn btn-default form-control" name="runEnv">
                                  <option value="" disabled selected>Select environment </option>
<!--                                  <option value="local">Local</option>-->
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
                            <input type="text" class="form-control" id="mEnvInsType" name="ins_type">
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
                    <div id="mExecDiv" class="form-group" style="display:none">
                        <label for="mExec" class="col-sm-3 control-label">Executor of Nextflow</label>
                        <div class="col-sm-9">
                            <select style=" width:150px" id="mExec" class="fbtn btn-default form-control" name="executor">
<!--                                  <option value="none">None </option>-->
                                  <option class="hideClu" value="local">Local</option>
                                  <option value="sge">SGE</option>
                                  <option value="lsf">LSF</option>
<!--                                  <option value="slurm">SLURM</option>-->
<!--                                  <option value="ignite">IGNITE</option>-->
<!--                                  <option value="pbs">PBS/Torque</option>-->
<!--                                  <option value="nqsii">NQSII</option>-->
<!--                                  <option value="condor">HTCondor</option>-->
<!--                                  <option value="k8s">Kubernetes</option>-->
<!--                                  <option value="awsbatch">AWS Batch</option>-->
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
                                            <th scope="col">Queue</th>
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
                                            <th scope="col">Queue</th>
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
                        <label for="userKeyCheck" class="col-sm-4 control-label"><input type="checkbox" id="userKeyCheck" name="check_userkey"  data-toggle="collapse" data-target="#userKeyDiv"> Use your own keys </input><span><a data-toggle="tooltip" data-placement="bottom" title="Use your own ssh keys and paste them below"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
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
                                <p style="font-size:13px;"><b style="color:blue;">* Important Information:</b> Private key will be used for submiting jobs in the host. Therefore, public key of the private key required to be added into '~/.ssh/authorized_keys' in the host by user </p>
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
                                <p style="font-size:13px;"><b style="color:blue;">* Important Information:</b> Private key will be used for submiting jobs in the host. Therefore, public key of the private key required to be added into '~/.ssh/authorized_keys' in the host by user </p>
                            </div>
                        </div>
                    </div>
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
                            <input type="text" class="form-control" id="mAmzAccKey" name="amz_acc_key">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mAmzSucKey" class="col-sm-3 control-label">Secret Key</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mAmzSucKey" name="amz_suc_key">
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

<!---- impersonModal-->
<div id="impersonModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Impersonation</h4>
            </div>
            <form role="form" method="post">
                <div class="modal-body" style="overflow:scroll">
                    <fieldset>
                        <label>Select user to impersonate</label>
                        <div id="mUserListDiv" class="form-group">
                            <select id="mUserList" class="form-control" size="25"></select></div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="button" id="confirmImpersonBut" class="btn btn-primary" data-dismiss="">Select</button>
                    <button type="button" class="btn btn-default" id="cancelImpersonBut" data-dismiss="modal" onclick="">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!---- impersonModal ends-->
