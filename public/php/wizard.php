<?php
require_once(__DIR__."/../../config/config.php");
$SHOW_TEST_PROFILE=SHOW_TEST_PROFILE;
?>

<style>
    .wizard {
        margin: 20px auto;
        padding: 50px;
        padding-top: 0px;
        background: #fff;
    }

    .wizard .nav-tabs {
        position: relative;
        margin: 40px auto;
        margin-bottom: 0;
        border-bottom-color: #e0e0e0;
    }

    .wizard>div.wizard-inner {
        position: relative;
    }

    .connecting-line {
        height: 2px;
        background: #e0e0e0;
        position: absolute;
        width: 80%;
        margin: 0 auto;
        left: 0;
        right: 0;
        top: 50%;
        z-index: 1;
    }

    .wizard .nav-tabs>li.active>a,
    .wizard .nav-tabs>li.active>a:hover,
    .wizard .nav-tabs>li.active>a:focus {
        color: #555555;
        cursor: default;
        border: 0;
        border-bottom-color: transparent;
    }

    span.round-tab {
        width: 70px;
        height: 70px;
        line-height: 70px;
        display: inline-block;
        border-radius: 100px;
        background: #fff;
        border: 2px solid #e0e0e0;
        z-index: 2;
        position: absolute;
        left: 0;
        text-align: center;
        font-size: 25px;
    }

    span.round-tab i {
        color: #555555;
    }

    .wizard li.active span.round-tab {
        background: #fff;
        border: 2px solid #5bc0de;

    }

    .wizard li.active span.round-tab i {
        color: #5bc0de;
    }

    span.round-tab:hover {
        color: #333;
        border: 2px solid #333;
    }

    .wizard .nav-tabs>li {
        width: 25%;
    }

    .wizard li:after {
        content: " ";
        position: absolute;
        left: 46%;
        opacity: 0;
        margin: 0 auto;
        bottom: 0px;
        border: 5px solid transparent;
        border-bottom-color: #5bc0de;
        transition: 0.1s ease-in-out;
    }

    .wizard li.active:after {
        content: " ";
        position: absolute;
        left: 48%;
        opacity: 1;
        margin: 0 auto;
        bottom: 0px;
        border: 10px solid transparent;
        border-bottom-color: #5bc0de;
    }

    .wizard .nav-tabs>li a {
        width: 70px;
        height: 70px;
        margin: 20px auto;
        border-radius: 100%;
        padding: 0;
    }

    .wizard .nav-tabs>li a:hover {
        background: transparent;
    }

    .wizard .tab-pane {
        position: relative;
        padding-top: 50px;
    }

    .wizard h3 {
        margin-top: 0;
    }

    @media(max-width : 585px) {

        .wizard {
            width: 90%;
            height: auto !important;
        }

        span.round-tab {
            font-size: 16px;
            width: 50px;
            height: 50px;
            line-height: 50px;
        }

        .wizard .nav-tabs>li a {
            width: 50px;
            height: 50px;
            line-height: 50px;
        }

        .wizard li.active:after {
            content: " ";
            position: absolute;
            left: 35%;
        }
    }

</style>


<!-- Modal -->
<div class="modal fade fullscreen profilewizard" data-keyboard="false" id="profilewizardmodal" tabindex="-1" role="dialog" aria-labelledby="profilewizardLabel" aria-hidden="true" style="padding-right:0px;">
    <div class="modal-dialog fullscreen" style="overflow-y: initial">
        <div class="modal-content fullscreen">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="profilewizardLabel">Profile Wizard</h4>
            </div>
            <div class="modal-body" style="height: calc(100vh - 150px); overflow-y: auto;">
                <div class="wizard">
                    <div class="wizard-inner">
                        <div class="connecting-line"></div>
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active">
                                <a href="#pw-step-profiletype" data-toggle="tab" aria-controls="pw-step-profiletype" role="tab" title="Profile Type">
                                    <span class="round-tab">
                                        <i class="glyphicon glyphicon-hdd"></i>
                                    </span>
                                </a>
                            </li>
                            <li role="presentation" class="disabled">
                                <a href="#pw-step-connectiontype" data-toggle="tab" aria-controls="pw-step-profiletype" role="tab" title="Connection Type">
                                    <span class="round-tab">
                                        <i class="glyphicon glyphicon-signal"></i>
                                    </span>
                                </a>
                            </li>
                            <li role="presentation" class="disabled">
                                <a href="#pw-step-settings" data-toggle="tab" aria-controls="pw-step-settings" role="tab" title="Configuration">
                                    <span class="round-tab">
                                        <i class="glyphicon glyphicon-cog"></i>
                                    </span>
                                </a>
                            </li>
                            <li role="presentation" class="disabled">
                                <a href="#pw-step-complete" data-toggle="tab" aria-controls="pw-step-complete" role="tab" title="Complete">
                                    <span class="round-tab">
                                        <i class="glyphicon glyphicon-ok"></i>
                                    </span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <form role="form">
                        <div class="tab-content">
                            <div class="tab-pane active" role="tabpanel" id="pw-step-profiletype" style="min-height:400px;">
                                <h3>Profile Type</h3>
                                <p>Profile wizard will guide you to create your run environment. If you have an access to High Performance Computing (HPC) environments, or personal workstations please choose <b>Host</b>. If you have an Amazon Web Services (AWS) or Google Cloud account or planning to create one, then please choose <b>Amazon</b> or <b>Google</b>. <?php
                                        if ($SHOW_TEST_PROFILE && !empty($TEST_PROFILE_GROUP_ID)){
                                            echo 'Finally, if you want to use our MGHPCC cluster please choose <b>MGHPCC cluster</b> option.';
                                        }
                                    ?> </p>
                                <div class="form-group" style="margin:20px;">
                                    <div id="pw_profiletype" class="form-group">
                                        <div class="radio">
                                            <label> <input type="radio" name="profiletype" value="host" checked=""> Host </label>
                                            <input id="pw-saveprofilecluster_id" style="display:none;" type="text" name="saveprofilecluster_id">
                                        </div>
                                        <div class="radio">
                                            <label> <input type="radio" name="profiletype" value="amazon"> Amazon </label>
                                            <input id="pw-saveprofileamazon_id" style="display:none;" type="text" name="saveprofileamazon_id">
                                        </div>
                                        <div class="radio">
                                            <label> <input type="radio" name="profiletype" value="google"> Google </label>
                                            <input id="pw-saveprofilegoogle_id" style="display:none;" type="text" name="saveprofilegoogle_id">
                                        </div>
                                        <?php
                                        if ($SHOW_TEST_PROFILE && !empty($TEST_PROFILE_GROUP_ID)){
                                            echo '<div class="radio">
                                                    <label> <input type="radio" name="profiletype"  value="test"> MGHPCC cluster </label>
                                                </div>
                                                <div id="pw-step-info-alert" class="alert alert-info">
                                                    <strong>Note:</strong> You can easily upload your files to our MGHPCC cluster to process your data and download your results from report section. However, you will not have direct access to our cluster.
                                                </div>
                                                ';
                                        }
                                        ?>
                                    </div>
                                </div>
                                <ul class="list-inline pull-right">
                                    <li><button type="button" class="btn btn-warning next-step ">Next</button></li>
                                </ul>
                            </div>
                            <div class="tab-pane" role="tabpanel" id="pw-step-connectiontype" style="min-height:400px;">
                                <h3>Connection Type</h3>
                                <div class="form-group" id="pw-step-connectiontype-host">
                                    <p>DolphinNext has a built-in automatic submission and run status tracking feature. In order to enable this feature, you need to confirm using ssh keys to connect your host machine.</p>
                                    <div class="col-md-12" style="margin-top:20px;">
                                        <div class="form-group">
                                            <label class="col-sm-5 control-label">Connection with SSH-Keys</label>
                                            <div class="col-sm-7">
                                                <select id="pw-usesshkeys" class="btn-default " name="usesshkeys">
                                                    <option value="yes" selected>Yes, use SSH keys.</option>
                                                    <option value="no">No, I will manually submit and track my jobs.</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="margin-top:20px;" id="pw-usesshkeys_checkdiv">
                                        <div class="form-group">
                                            <div class="col-sm-12">
                                                <input type="checkbox" id="pw-usesshkeys_check" name="usesshkeys_check"> Click here to indicate that you have read and agree to the terms presented in the <a href="php/terms.php" class="text-aqua" target="_blank">Terms and Conditions</a> & <a href="php/privacy.php" class="text-aqua" target="_blank">Privacy Policy.</a></input>
                                            </div>
                                        </div>
                                        <div class="form-horizontal col-md-12" id="pw-validatepublickey">
                                            <div class="col-md-12">
                                                <div class="form-group col-md-12">
                                                    <h3 style="margin-top:40px;">Adding SSH Keys</h3>
                                                    <p>Following public key is securely generated for your account and required to be added into '~/.ssh/authorized_keys' in the host by user. After adding public key, please click <b>Validate SSH Keys</b> button at below. For more information, please check <a class="text-aqua" target="_blank" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/public_ssh_key.html">adding keys section</a>. </p>
                                                    <div class="form-group col-md-12">
                                                        <label class="col-sm-3" style="padding-top:50px;">Public Key</label>
                                                        <div class="col-sm-9">
                                                            <input style="display:none;" type="text" class="form-control" id="pw-sshkeyid" name="sshkeyid">
                                                            <textarea type="text" rows="5" class="form-control" readonly="readonly" id="pw-pubkey" name="pubkey"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label class="col-sm-3" style="">Username <span><a data-toggle="tooltip" data-placement="bottom" title="Your username to connect your host machine(eg. for us2r@ghpcc06.umassrc.org it is us2r)"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" id="pw-username" name="username">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label class="col-sm-3" style="">Hostname <span><a data-toggle="tooltip" data-placement="bottom" title="Your hostname to connect your host machine(eg. for us2r@ghpcc06.umassrc.org it is ghpcc06.umassrc.org"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" id="pw-hostname" name="hostname" placeholder="Type your hostname or choose from the list">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label class="col-sm-3">SSH port (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="By default TCP port 22 is used for SSH connection. You can change this default by entering port number."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                                        <div class="col-sm-9">
                                                            <input type="text" class="form-control" id="pw-sshport" name="sshport">
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label class="col-sm-3" style="padding-top:8px;"> </label>
                                                        <div class="col-sm-9">
                                                            <button id="pw-validate-ssh-key" style="margin-right:10px;" type="button" class="col-sm-2 btn btn-warning btn-load validate-pw-button" data-loading-text="Validating...">Validate</button>
                                                            <input style="display:none;" type="checkbox" name="sshkey_validate" class="validate-by-checkbox"></input>
                                                            <input style="display:none;" type="checkbox" name="sshkey_checked"></input>
                                                            <div style="display:none; padding-left:10px; padding-top:10px;"><span class="glyphicon glyphicon-ok" style="color: rgb(0, 164, 30);"> Successfully validated.</span></div>
                                                            <div style="display:none; padding-left:10px; padding-top:10px;"><span class="glyphicon glyphicon-remove" style="color:#FF0004;"></span> Validation failed.</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <ul class="list-inline pull-right">
                                    <li><button type="button" class="btn btn-default prev-step ">Previous</button></li>
                                    <li><button type="button" class="btn btn-warning next-step ">Next</button></li>
                                </ul>
                            </div>
                            <div class="tab-pane" role="tabpanel" id="pw-step-settings" style="min-height:400px;">
                                <div class="form-group" id="pw-step-settings-software">
                                    <h3>Sofware Dependencies</h3>
                                    <p>In order to execute our pipelines, you need to install <b>Nextflow</b> into your host platform. Besides, most of our pipelines isolates their dependencies within their <b>Docker</b> or <b>Singularity</b> containers, therefore please install these softwares into your machine by following our <a class="text-aqua" target="_blank" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html#creating-profile">installing guide</a>. After installing them, please click the buttons below to validate each of them.</p>
                                    <div class="form-horizontal" style="margin-top:20px;">
                                        <div class="form-group">
                                            <label class="col-sm-12">Java </label>
                                        </div>
                                        <div class="form-group">
                                            <p class="col-sm-3 control-label">Command (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="If JAVA is not added to $PATH environment, you can run command (eg. module load java/8.0) to manipulate your $PATH environment and gain access to JAVA."><i class='glyphicon glyphicon-info-sign'></i></a></span></p>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="pw-javacmd" name="java_cmd">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <p class="col-sm-3"> </p>
                                            <div class="col-sm-9">
                                                <button id="pw-validate-java" style="margin-right:10px;" type="button" class="col-sm-2 btn btn-warning btn-load validate-pw-button" data-loading-text="Validating...">Validate</button>
                                                <input style="display:none;" type="checkbox" name="java_validate" class="validate-by-checkbox"></input>
                                                <input style="display:none;" type="checkbox" name="java_checked"></input>
                                                <div style="display:none; padding-left:10px; padding-top:10px;"><span class="glyphicon glyphicon-ok" style="color: rgb(0, 164, 30);"> Successfully validated.</span></div>
                                                <div style="display:none; padding-left:10px; padding-top:10px;"><span class="glyphicon glyphicon-remove" style="color:#FF0004;"></span> Validation failed.</div>
                                                <input style="display:none;" type="text" name="java_version"></input>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-12">Nextflow </label>
                                        </div>
                                        <div class="form-group">
                                            <p class="col-sm-3 control-label">Nextflow Path or Command (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="If nextflow is not added to $PATH environment, you can either enter the path of the nextflow (eg. /project/bin), or run command (eg. module load nextflow/1.0.0) to manipulate your $PATH environment and gain access to new softwares."><i class='glyphicon glyphicon-info-sign'></i></a></span></p>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="pw-nextflowpath" name="next_path">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <p class="col-sm-3"> </p>
                                            <div class="col-sm-9">
                                                <button id="pw-validate-nextflow" style="margin-right:10px;" type="button" class="col-sm-2 btn btn-warning btn-load validate-pw-button" data-loading-text="Validating...">Validate</button>
                                                <input style="display:none;" type="checkbox" name="nextflow_validate" class="validate-by-checkbox"></input>
                                                <input style="display:none;" type="checkbox" name="nextflow_checked"></input>
                                                <div style="display:none; padding-left:10px; padding-top:10px;"><span class="glyphicon glyphicon-ok" style="color: rgb(0, 164, 30);"> Successfully validated.</span></div>
                                                <div style="display:none; padding-left:10px; padding-top:10px;"><span class="glyphicon glyphicon-remove" style="color:#FF0004;"></span> Validation failed.</div>
                                                <input style="display:none;" type="text" name="nextflow_version"></input>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-12">Docker </label>
                                        </div>
                                        <div class="form-group">
                                            <p class="col-sm-3 control-label">Command (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="You can run command to manipulate your $PATH environment in order to gain access to new softwares. For example, you might run: module load docker/1.0.0"><i class='glyphicon glyphicon-info-sign'></i></a></span></p>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="pw-dockercmd" name="docker_cmd">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <p class="col-sm-3 control-label"> </p>
                                            <div class="col-sm-9">
                                                <button id="pw-validate-docker" style="margin-right:10px;" type="button" class="col-sm-2 btn btn-warning btn-load validate-pw-button" data-loading-text="Validating...">Validate</button>
                                                <input style="display:none;" type="checkbox" name="docker_validate" class="validate-by-checkbox"></input>
                                                <input style="display:none;" type="checkbox" name="docker_checked"></input>
                                                <div style="display:none; padding-left:10px; padding-top:10px;"><span class="glyphicon glyphicon-ok" style="color: rgb(0, 164, 30);"> Successfully validated.</span></div>
                                                <div style="display:none; padding-left:10px; padding-top:10px;"><span class="glyphicon glyphicon-remove" style="color:#FF0004;"></span> Validation failed.</div>
                                                <input style="display:none;" type="text" name="docker_version"></input>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-sm-12">Singularity </label>
                                        </div>
                                        <div class="form-group">
                                            <p class="col-sm-3 control-label">Command (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="You can run command to manipulate your $PATH environment in order to gain access to new softwares. For example, you might run: module load singularity/3.0.0"><i class='glyphicon glyphicon-info-sign'></i></a></span></p>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" id="pw-singularitycmd" name="singularity_cmd">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <p class="col-sm-3 control-label"> </p>
                                            <div class="col-sm-9">
                                                <button id="pw-validate-singularity" style="margin-right:10px;" type="button" class="col-sm-2 btn btn-warning btn-load validate-pw-button" data-loading-text="Validating...">Validate</button>
                                                <input style="display:none;" type="checkbox" name="singularity_validate" class="validate-by-checkbox"></input>
                                                <input style="display:none;" type="checkbox" name="singularity_checked"></input>
                                                <div style="display:none; padding-left:10px; padding-top:10px;"><span class="glyphicon glyphicon-ok" style="color: rgb(0, 164, 30);"> Successfully validated.</span></div>
                                                <div style="display:none; padding-left:10px; padding-top:10px;"><span class="glyphicon glyphicon-remove" style="color:#FF0004;"></span> Validation failed.</div>
                                                <input style="display:none;" type="text" name="singularity_version"></input>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <h3 style="margin-top:40px;">Run Settings</h3>
                                            <ul>
                                                <li><b>Executor of Nextflow:</b> Nextflow itself is initiated with this method and it will be only used for running nextflow itself.</li>
                                                <li><b>Executor of Nextflow Jobs:</b> This setting will be used as default setting for submitted jobs by Nextflow.</li>
                                                <li><b>Download Directory:</b> Used to download shared pipeline files such as genome indexes. If your platform has already such path, please enter that location. Otherwise you can set any path that you have permission to write.</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12" style="margin-top:20px;">
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label">Executor of Nextflow</label>
                                            <div class="col-sm-8">
                                                <select style="width:250px;" id="pw_next_exec" class="btn-default " name="next_exec">
                                                    <option value="local" selected>Local</option>
                                                    <option value="lsf">LSF</option>
                                                    <option value="sge">SGE</option>
                                                    <option value="slurm">SLURM</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12" style="margin-top:20px;">
                                        <div class="form-group">
                                            <label style="margin-top:3px;" class="col-sm-4 control-label">Executor of Nextflow Jobs</label>
                                            <div class="col-sm-8">
                                                <select style="width:250px;" class="btn-default " id="pw_job_exec" name="job_exec">
                                                    <option value="local" selected>Local</option>
                                                    <option value="lsf">LSF</option>
                                                    <option value="sge">SGE</option>
                                                    <option value="slurm">SLURM</option>
                                                    <option value="ignite">Apache Ignite</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12" style="margin-top:15px;">
                                        <div class="form-group">
                                            <label style="margin-top:7px;" class="col-sm-4 control-label">Download Directory </label>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="pw-downdir" name="pw-downdir">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <ul style="margin-top:20px;" class="list-inline pull-right">
                                    <li><button type="button" class="btn btn-default prev-step ">Previous</button></li>
                                    <li><button type="button" class="btn btn-warning next-step ">Next</button></li>
                                </ul>
                            </div>
                            <div class="tab-pane" role="tabpanel" id="pw-step-complete" style="min-height:400px;">
                                <h3 id="pw-step-complete-header">Complete</h3>
                                <p id="pw-step-complete-text">We have successfully created your run environment. You can always edit your parameters in the <a href="index.php?np=4" class="text-aqua" target="_blank">Profile</a> section.</p>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-12" style="margin-top:15px;">
                        <div class="form-group">
                            <div class="col-sm-11 footer-copyright text-center small">*If you have any issues/questions please contact us on: support@dolphinnext.com
                        </div>
                            <div class="col-sm-1">
                                <button type="button" class="btn btn-primary " data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
