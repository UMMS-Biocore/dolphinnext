<?php
    require_once(__DIR__."/../../config/config.php");
    $SHOW_TEST_PROFILE=SHOW_TEST_PROFILE;
?>

    <style>

    .wizard {
        margin: 20px auto;
        padding: 50px;
        background: #fff;
    }

    .wizard .nav-tabs {
        position: relative;
        margin: 40px auto;
        margin-bottom: 0;
        border-bottom-color: #e0e0e0;
    }

    .wizard > div.wizard-inner {
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

    .wizard .nav-tabs > li.active > a, .wizard .nav-tabs > li.active > a:hover, .wizard .nav-tabs > li.active > a:focus {
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
    span.round-tab i{
        color:#555555;
    }
    .wizard li.active span.round-tab {
        background: #fff;
        border: 2px solid #5bc0de;

    }
    .wizard li.active span.round-tab i{
        color: #5bc0de;
    }

    span.round-tab:hover {
        color: #333;
        border: 2px solid #333;
    }

    .wizard .nav-tabs > li {
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
        left: 46%;
        opacity: 1;
        margin: 0 auto;
        bottom: 0px;
        border: 10px solid transparent;
        border-bottom-color: #5bc0de;
    }

    .wizard .nav-tabs > li a {
        width: 70px;
        height: 70px;
        margin: 20px auto;
        border-radius: 100%;
        padding: 0;
    }

    .wizard .nav-tabs > li a:hover {
        background: transparent;
    }

    .wizard .tab-pane {
        position: relative;
        padding-top: 50px;
    }

    .wizard h3 {
        margin-top: 0;
    }

    @media( max-width : 585px ) {

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

        .wizard .nav-tabs > li a {
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
<div class="modal fade fullscreen profilewizard" id="profilewizardmodal" tabindex="-1" role="dialog" aria-labelledby="profilewizardLabel" aria-hidden="true" style="padding-right:0px;">
    <div class="modal-dialog fullscreen" >
        <div class="modal-content fullscreen">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="profilewizardLabel">Profile Wizard</h4>
            </div>
            <div class="modal-body"> 
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
                                <p>DolphinNext is designed to submit jobs into specified host machines. If you have an access to High Performance Computing (HPC) environments, or personal workstations please choose <b>Host</b>. If you have an Amazon Web Services (AWS) account or planning to create one then please choose <b>Amazon</b>. Finally, if you just want to test our platform in our test environment please choose <b>Test</b>. </p>
                                <div class="form-group" style="margin:20px;">
                                    <div id="pw_profiletype" class="form-group">
                                        <div class="radio">
                                            <label> <input type="radio" name="profiletype" value="host"  checked=""> Host </label>
                                        </div>
                                        <div class="radio">
                                            <label> <input type="radio" name="profiletype"  value="amazon"> Amazon </label>
                                        </div>
                                        <?php
                                        if ($SHOW_TEST_PROFILE && !empty($TEST_PROFILE_GROUP_ID)){
                                            echo '<div class="radio">
                                                    <label> <input type="radio" name="profiletype"  value="test"> Test </label>
                                                </div>';
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
                                    <p >DolphinNext has a built-in automatic submission and run status tracking feature. In order to enable this feature, you need to confirm using ssh keys to connect your host machine.</p>
                                    <div class="col-md-12" style="margin-top:20px;">
                                        <div class="form-group" >
                                            <label class="col-sm-5 control-label">Connection with SSH-Keys</label>
                                            <div class="col-sm-7">
                                                <select  id="pw-usesshkeys" class="btn-default " name="usesshkeys">
                                                    <option value="yes" selected>Yes, use SSH keys.</option>
                                                    <option value="no">No, I will manually submit and track my jobs.</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12" style="margin-top:20px;" id="pw-usesshkeys_checkdiv">
                                        <div class="form-group" >
                                            <div class="col-sm-12">
                                                <input type="checkbox" id="pw-usesshkeys_check" name="usesshkeys_check"> Click here to indicate that you have read and agree to the terms presented in the <a href="php/terms.php" class="text-aqua" target="_blank">Terms & Privacy Policy.</a></input>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-horizontal" id="pw-validatepublickey">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <h3 style="margin-top:40px;">Adding SSH Keys</h3>
                                            <p>Following public key is securely generated for your account and required to be added into '~/.ssh/authorized_keys' in the host by user. After adding public key, please click <b>Validate SSH Keys</b> button at below. For more information, please check <a class="text-aqua" target="_blank" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/profile.html#ssh-keys">adding keys section</a>. </p>
                                            <div class="form-group">
                                                <label class="col-sm-3" style="padding-top:50px; padding-left:30px;">Public Key</label>
                                                <div class="col-sm-9">
                                                    <input style="display:none;" type="text" class="form-control" id="pw-sshkeyid" name="sshkeyid">
                                                    <textarea type="text" rows="5" class="form-control" id="pw-pubkey" name="pubkey"></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-3" style="">Username <span><a data-toggle="tooltip" data-placement="bottom" title="username@hostname (eg. us2r@ghpcc06.umassrc.org)"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" id="pw-username" name="username">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-3" style="">Hostname <span><a data-toggle="tooltip" data-placement="bottom" title="username@hostname (eg. us2r@ghpcc06.umassrc.org)"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                                <div class="col-sm-9">
                                                    <input type="text" class="form-control" id="pw-hostname" name="hostname">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-3" style="padding-top:10px; padding-left:30px;">Validation </label>
                                                <div class="col-sm-3">
                                                    <button style="margin-top:5px;" type="button" class="col-xs-12 btn btn-warning btn-load" data-loading-text="Validating...">Validate SSH Keys </button>
                                                </div>
                                                <div class="col-sm-6" style="padding-top:10px; display:none;">
<!--                                                    <span class="glyphicon glyphicon-remove" style="color:#FF0004;"></span> 8 Characters Long<br>-->
                                                    <span class="glyphicon glyphicon-ok" style="color: rgb(0, 164, 30);"></span>
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
                        <div class="tab-pane" role="tabpanel" id="pw-step-settings" style="min-height:400px;" >
                            <h3>Sofware Dependencies</h3>
                            <div class="form-group" id="pw-step-settings-software">
                                <p>In order to execute our pipelines, you need to install <b>Nextflow</b> into your host platform. Besides, most of our pipelines isolates their dependencies within their <b>Docker</b> or <b>Singularity</b> containers, therefore please install these softwares into your machine by following our <a class="text-aqua" target="_blank" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html#creating-profile">installing guide</a>. After installing them, please click the buttons below to validate each of them.</p>
                                <div class="row" style="margin-top:20px;">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-sm-3" style="padding-top:10px; padding-left:30px;">Nextflow </label>
                                            <div class="col-sm-1">
                                                <button style="margin-top:5px;" type="button" class="btn btn-warning btn-load" data-loading-text="Validating...">Validate</button>
                                            </div>
                                            <div class="col-sm-7" style="padding-top:10px;">
                                                    <span class="glyphicon glyphicon-ok" style="color: rgb(0, 164, 30);"></span>
                                            
<!--                                                <span class="glyphicon glyphicon-remove" style="color:#FF0004;"></span> 8 Characters Long<br>-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-sm-3" style="padding-top:10px; padding-left:30px;">Docker </label>
                                            <div class="col-sm-1">
                                                <button style="margin-top:5px;" type="button" class="btn btn-warning btn-load" data-loading-text="Validating...">Validate</button>
                                            </div>
                                            <div class="col-sm-7" style="padding-top:10px;">
                                                    <span class="glyphicon glyphicon-ok" style="color: rgb(0, 164, 30);"></span>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="col-sm-3" style="padding-top:10px; padding-left:30px;">Singularity </label>
                                            <div class="col-sm-1">
                                                <button style="margin-top:5px;" type="button" class="btn btn-warning btn-load" data-loading-text="Validating...">Validate</button>
                                            </div>
                                            <div class="col-sm-7" style="padding-top:10px;">
                                                    <span class="glyphicon glyphicon-ok" style="color: rgb(0, 164, 30);"></span>
                                                
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <h3 style="margin-top:40px;">Run Settings</h3>
                                        <p>Executor of Nextflow is used to set the method in which nextflow itself is initiated. Currently local, sge, slurm and lsf executors are supported by DolphinNext to initiate nextflow and it will be only used for running nextflow itself. Executor of Nextflow Jobs will be used by nextflow to submit their jobs.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12" style="margin-top:20px;">
                                    <div class="form-group" >
                                        <label class="col-sm-4 control-label">Executor of Nextflow</label>
                                        <div class="col-sm-8">
                                            <select class="btn-default " name="next_exec">
                                                <option value="local" selected>Local</option>
                                                <option value="lsf">LSF</option>
                                                <option value="sge">SGE</option>
                                                <option value="slurm">SLURM</option>
                                                <option value="Ignite">Apache Ignite</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12" style="margin-top:20px;">
                                    <div class="form-group" >
                                        <label class="col-sm-4 control-label">Executor of Nextflow Jobs</label>
                                        <div class="col-sm-8">
                                            <select class="btn-default " name="job_exec">
                                                <option value="local" selected>Local</option>
                                                <option value="lsf">LSF</option>
                                                <option value="sge">SGE</option>
                                                <option value="slurm">SLURM</option>
                                                <option value="Ignite">Apache Ignite</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <ul class="list-inline pull-right">
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
            <button type="button" class="btn btn-primary " data-dismiss="modal">Save and Close</button>
        </div>
    </div>
</div>
</div>