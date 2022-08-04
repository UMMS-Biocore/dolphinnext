<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : "";
$role = isset($_SESSION['role']) ? $_SESSION['role'] : "";
session_write_close();

require_once(__DIR__ . "/../../config/config.php");
$SHOW_RUN_LOG = SHOW_RUN_LOG;
$SHOW_RUN_TIMELINE = SHOW_RUN_TIMELINE;
$SHOW_RUN_REPORT = SHOW_RUN_REPORT;
$SHOW_RUN_DAG = SHOW_RUN_DAG;
$SHOW_RUN_TRACE = SHOW_RUN_TRACE;
$SHOW_RUN_NEXTFLOWLOG = SHOW_RUN_NEXTFLOWLOG;
$SHOW_RUN_NEXTFLOWNF = SHOW_RUN_NEXTFLOWNF;
$SHOW_RUN_NEXTFLOWCONFIG = SHOW_RUN_NEXTFLOWCONFIG;
$SHOW_DMETA = SHOW_DMETA;
$DMETA_URL = DMETA_URL;
$DMETA_LABEL = DMETA_LABEL;
$CUSTOM_FILE_LOCATION_MESSAGE = CUSTOM_FILE_LOCATION_MESSAGE;
?>
<style type="text/css">
    #fileContent .multiselect-item.multiselect-filter {
        text-align: center;
        width: 100%;
    }

    #fileContent .multiselect-item.multiselect-filter>.input-group {
        width: 93%;
        margin: 5px;
    }

    #fileContent .multiselect-item.multiselect-filter>.input-group>.input-group-btn {
        width: 40px;
    }

    .indesc {
        font-style: italic;
        color: darkslategray;
        font-weight: 300;
        font-size: 13px;
        display: block;
        padding-top: 5px;
    }
</style>


<div class="box-header" style=" padding-top:0px;  font-size:large; ">
    <div style="padding-bottom:6px;  border-bottom:1px solid lightgrey;">
        <i class="fa fa-rocket " style="padding-top:12px; margin-left:0px; margin-right:0px;"></i> Run:
        <input class="box-dynamic width-dynamic" type="text" projectid="<?php echo $id; ?>" name="projectTitle" autocomplete="off" placeholder="Enter Run Name" style="margin-left:0px; font-size: large; font-style:italic; align-self:center; max-width: 300px;" title="Rename" data-placement="bottom" data-toggle="tooltip" num="" id="run-title"><span class="width-dynamic" style="display:none"></span></input>

        <i style="color:grey; font-size:25px; padding-top:12px; margin-left:10px; margin-right:10px;">|</i>
        <i class="fa fa-calendar-o " style="padding-top:12px; margin-left:0px; margin-right:0px;"></i> <a data-toggle="tooltip" data-placement="bottom" data-original-title="Change Project" href="#" onclick="duplicateProPipe(&#34;changeproject&#34;);return false;">Project:</a>
        <a href="" style="font-size: large; font-style:italic;  max-width: 500px;" data-toggle="tooltip" data-placement="bottom" data-original-title="Go to Project" id="project-title"></a>

        <i style="color:grey; font-size:25px; padding-top:12px; margin-left:10px; margin-right:10px;">|</i>

        <i class="fa fa-spinner " style="margin-left:0px; margin-right:0px;"></i> Pipeline:
        <a href="" projectpipelineid="<?php echo $id; ?>" style="margin-left:0px; font-size: large; font-style:italic; align-self:center; max-width: 500px;" id="pipeline-title" data-toggle="tooltip" data-placement="bottom" data-original-title="Go to Pipeline"></a>
        <i style="color:grey; font-size:25px; padding-top:12px; margin-left:10px; margin-right:10px;">|</i>

        <!--        Save and delete icons-->
        <button type="submit" id="saveRunIcon" class="btn" name="button" data-backdrop="false" onclick="saveRunIcon()" style="background:none;  margin:0px; padding:0px;  ">
            <a data-toggle="tooltip" data-placement="bottom" data-original-title="Save Run">
                <i class="fa fa-save" style="font-size: 17px;"></i>
            </a>
        </button>
        <button type="button" id="downPipeline" class="btn" name="button" onclick="download(createNextflowFile(&quot;run&quot;))" data-backdrop="false" style=" background:none; margin:0px; padding:0px;">
            <a data-toggle="tooltip" data-placement="bottom" data-original-title="Download Pipeline"> <i class="glyphicon glyphicon-save"></i>
            </a>
        </button>
        <button type="button" id="delRun" class="btn" name="button" data-backdrop="false" data-toggle="modal" href="#confirmModal" style=" margin:0px; background:none; padding:0px;"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Run">
                <i class="glyphicon glyphicon-trash"></i>
            </a>
        </button>
        <!--  Save and delete icons ends-->

        <div id="pipeActionsDiv" style="float:right;  margin-right:5px;" class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" id="pipeActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="vertical-align:middle;">
                <div class="fa fa-ellipsis-h"></div>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="dropdownMenu2">
                <li><a id="deleteRun" data-toggle="modal" href="#confirmModal">Delete Run</a>
                <li><a id="duplicaRun" onclick="duplicateProPipe(&#34;copy&#34;);return false;">Duplicate Run</a></li>
                <li><a id="moveRun" onclick="duplicateProPipe(&#34;move&#34;);return false;">Move Run</a></li>
                </li>
            </ul>
        </div>
        <div id="pipeRunDiv" style="float:right; margin-right:5px;">
            <div id="errorProPipe" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-danger" type="button" id="errorProPipeBut">Run Error</button>
                <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="checkReadytoRun(&#34;rerun&#34;);return false;">ReRun</a></li>
                    <li><a href="#" onclick="checkReadytoRun(&#34;resumerun&#34;);return false;">Resume</a></li>
                    <li><a data-toggle="modal" href="#manualRunModal">(Optional) Manual Run</a></li>
                </ul>
            </div>
            <div id="completeProPipe" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-success" type="button" id="completeProPipeBut">Completed</button>
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="checkReadytoRun(&#34;rerun&#34;);return false;">ReRun</a></li>
                    <li><a href="#" onclick="checkReadytoRun(&#34;resumerun&#34;);return false;">Resume</a></li>
                    <li><a data-toggle="modal" href="#manualRunModal">(Optional) Manual Run</a></li>
                </ul>
            </div>
            <div id="runningProPipe" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button" id="runningProPipeBut">Running</button>
                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="terminateProjectPipe();return false;">Terminate</a></li>
                </ul>
            </div>
            <div id="waitingProPipe" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button" id="waitingProPipeBut">Initializing..</button>
                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="terminateProjectPipe();return false;">Terminate</a></li>
                </ul>
            </div>
            <div id="connectingProPipe" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button" id="connectingProPipeBut">Connecting..</button>
                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="terminateProjectPipe();return false;">Terminate</a></li>
                </ul>
            </div>
            <div id="terminatedProPipe" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-danger" type="button" id="terminatedProPipeBut">Terminated</button>
                <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="checkReadytoRun(&#34;rerun&#34;);return false;">ReRun</a></li>
                    <li><a href="#" onclick="checkReadytoRun(&#34;resumerun&#34;);return false;">Resume</a></li>
                    <li><a data-toggle="modal" href="#manualRunModal">(Optional) Manual Run</a></li>
                </ul>
            </div>
            <div id="abortedProPipe" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button" id="abortedProPipeBut">Reconnecting..</button>
                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="terminateProjectPipe();return false;">Terminate</a></li>
                    <li><a href="#" onclick="checkReadytoRun(&#34;rerun&#34;);return false;">ReRun</a></li>
                    <li><a href="#" onclick="checkReadytoRun(&#34;resumerun&#34;);return false;">Resume</a></li>
                    <li><a data-toggle="modal" href="#manualRunModal">(Optional) Manual Run</a></li>
                </ul>
            </div>
            <div id="manualProPipe" style="display:none; float:right; " class="btn-group">
                <button class="btn bg-purple-active color-palette" type="button" id="manualProPipeBut" data-toggle="modal" href="#manualRunModal">Manual Run</button>
                <button type="button" class="btn bg-purple-active color-palette dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="checkReadytoRun(&#34;rerun&#34;);return false;">ReRun</a></li>
                    <li><a href="#" onclick="checkReadytoRun(&#34;resumerun&#34;);return false;">Resume</a></li>
                    <li><a data-toggle="modal" href="#manualRunModal">Manual Run</a></li>
                </ul>
            </div>
            <div id="runProPipe" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-success" type="button" id="runProPipeBut"><i class="fa fa-play" style=""></i> Run</button>
                <button id="runProPipeBut2" type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                </button>
                <ul id="runProPipeDropdown" class="dropdown-menu dropdown-menu-right" role="menu">
                    <li id="runProPipeStart"><a href="#" onclick="runProjectPipe(runProPipeCall,&#34;newrun&#34;);return false;">Start</a></li>
                    <li id="runProPipeReRun"><a href="#" onclick="runProjectPipe(runProPipeCall,&#34;rerun&#34;);return false;">ReRun</a></li>
                    <li id="runProPipeResume"><a href="#" onclick="runProjectPipe(runProPipeCall,&#34;resumerun&#34;);return false;">Resume</a></li>
                    <li><a href="#" onclick="showManualRunModal();return false;">(Optional) Manual Run</a></li>
                </ul>
            </div>
            <button class="btn btn-warning" type="submit" id="statusProPipe" style="display:none; vertical-align:middle;" data-original-title="Waiting for input parameters, output directory and selection of active environment" data-placement="bottom" data-toggle="tooltip">Waiting</button>
        </div>

        <div id="runStatDiv" style="display:none; float:right; margin-right:5px;">
            <div id="runStatErrorOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-danger" type="button">Run Error</button>
                <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="return false;" class="createNewRun">Use these Params to Create New Run</a></li>
                </ul>
            </div>
            <div id="runStatCompleteOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-success" type="button">Completed</button>
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="return false;" class="createNewRun">Use these Params to Create New Run</a></li>
                </ul>
            </div>
            <div id="runStatRunningOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button">Running</button>
            </div>
            <div id="runStatWaitingOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button">Initializing..</button>
            </div>
            <div id="runStatConnectingOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button">Connecting..</button>
            </div>
            <div id="runStatTerminatedOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-danger" type="button">Terminated</button>
                <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="return false;" class="createNewRun">Use these Params to Create New Run</a></li>
                </ul>
            </div>
            <div id="runStatAbortedOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button">Reconnecting..</button>
                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="return false;" class="createNewRun">Use these Params to Create New Run</a></li>
                </ul>
            </div>
            <div id="runStatManualOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn bg-purple-active color-palette" type="button">Manual Run</button>
                <button type="button" class="btn bg-purple-active color-palette dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><span class="caret"></span></button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#" onclick="return false;" class="createNewRun">Use these Params to Create New Run</a></li>
                </ul>
            </div>
        </div>
        <div id="runStatNoDiv" style="display:none; float:right; margin-right:5px;">
            <div id="runStatErrorNoOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-danger" type="button">Run Error</button>
            </div>
            <div id="runStatCompleteNoOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-success" type="button">Completed</button>
            </div>
            <div id="runStatRunningNoOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button">Running</button>
            </div>
            <div id="runStatWaitingNoOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button">Initializing..</button>
            </div>
            <div id="runStatConnectingNoOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button">Connecting..</button>
            </div>
            <div id="runStatTerminatedNoOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-danger" type="button">Terminated</button>
            </div>
            <div id="runStatAbortedNoOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn btn-info" type="button">Reconnecting..</button>
            </div>
            <div id="runStatManualNoOpt" style="display:none; float:right; " class="btn-group">
                <button class="btn bg-purple-active color-palette" type="button">Manual Run</button>
            </div>
        </div>
    </div>
</div>

<div style="padding-left:16px; padding-right:16px; padding-bottom:20px; " id="desPipeline">
    <div class="row" id="creatorInfoPip" style="font-size:12px; display:none;"> Created by <span id="ownUserNamePip">admin</span> on <span id="datecreatedPip">Jan. 26, 2016 04:12</span> • Last edited on <span class="lasteditedPip">Feb. 8, 2017 12:15</span>
    </div>
</div>

<div id="warningSection">
</div>

<div id="infoSection" style="background-color:#FDF2C1; border-left:0px; padding:2px 2px 2px 5px; margin:0 0 10px 0; display:none;">
    <h5 style="color:#937D68;"></h5>
</div>

<div id="runTabSection">
    <div id="runHistoryDiv" style="display:none; float:right;">
        <div style="float:right;  margin-left:5px; padding-top:8px;">
            <a id="runHistoryConsole" data-toggle="tooltip" data-placement="bottom" data-original-title="Run Management"><i class="fa fa-gear" style="font-size: 18px;"></i></a>
        </div>
        <div style="width:140px; float:right;">
            <select id="runVerLog" class="fbtn btn-default form-control"></select>
        </div>
        <div style="float:right; padding-top:6px; padding-right:10px;">
            <span id="runLogSize"></span>
        </div>
    </div>

    <ul id="runTabDiv" class="nav nav-tabs">
        <li class="active"><a class="nav-item" data-toggle="tab" href="#configTab">Run Settings</a></li>
        <li><a class="nav-item" data-toggle="tab" href="#advancedTab">Advanced</a></li>
        <li><a class="nav-item" data-toggle="tab" href="#logTab" style="display:none;">Log</a></li>
        <li><a class="nav-item" data-toggle="tab" href="#workflowTab">Workflow</a></li>
        <li><a class="nav-item" data-toggle="tab" href="#reportTab" style="display:none;">Report</a></li>
    </ul>
    <div class="tab-content">
        <div id="configTab" class="tab-pane fade in active">
            <div class="row" style="margin-top:10px; margin-bottom:2px;">
                <div class="col-md-12">
                    <div class="form-group">
                        <h5 id="runTitleConfig"></h5>
                    </div>
                </div>
            </div>
            <div id="desTitlePip">
                <div class="row">
                    <div class="col-md-12">
                        <label>Run Description</label>
                        <a><i id="editRunSum" class="fa fa-pencil" style="display: inline-block;"></i></a>
                        <a><i id="saveRunSum" class="fa fa-check" style="display: none;"></i> </a>
                    </div>
                </div>
            </div>
            <div><textarea id="runSum" rows="3" placeholder="Enter run description here.." style="min-width: 100%; max-width: 100%; border-color:lightgrey;"></textarea></div>
            <div id="runSettings" style="padding-top:7px;">
                <div>

                    <div class="row">
                        <div class="col-md-12">
                            <div id="mRunAmzKeyDiv" class="form-group" style="display:none;">
                                <label>Select Amazon Keys <span><a data-toggle="tooltip" data-placement="bottom" title="Amazon Keys to connect and access your S3 storage"><i class='glyphicon glyphicon-info-sign' style="color:#ffbb33;"></i></a></span></label>
                                <div style="padding-bottom:15px;">
                                    <select id="mRunAmzKey" class="fbtn btn-default form-control" name="amazon_cre_id">
                                        <option value="" selected>Select Amazon Keys </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div id="mRunGoogKeyDiv" class="form-group" style="display:none;">
                                <label>Select Google Keys <span><a data-toggle="tooltip" data-placement="bottom" title="Google Keys to connect and access your Google storage"><i class='glyphicon glyphicon-info-sign' style="color:#ffbb33;"></i></a></span></label>
                                <div style="padding-bottom:15px;">
                                    <select id="mRunGoogKey" class="fbtn btn-default form-control" name="google_cre_id">
                                        <option value="" disabled selected>Select Google Keys </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Run Environment </label>
                                <button type="button" class="btn" data-backdrop="false" onclick="refreshEnv()" style="background:none; padding:0px;"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Refresh Environments"><i class="fa fa-refresh" style="font-size: 14px;"></i></a></button>
                                <select id="chooseEnv" style="width: 100%;" class="fbtn btn-default form-control" name="runEnv">
                                    <option value="" disabled selected>Choose environment </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="rOut_dirDiv">
                        <div class="col-md-12">
                            <label style="float:left; margin-right:4px;"> Work Directory <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter the full path of the work directory in your host. eg. /project/rna-seq/run1"><i style="font-size: 14px;" class='fa fa-info-circle'></i></a></span></label>
                            <span title="Check Disk Space" style="cursor:pointer;" onclick="updateDiskSpace()">
                                <div id="workdir_diskpercent_div" class="col-md-1 progress" style=" display:none; margin-bottom:0px; padding-left:0px; padding-right:0px; margin-right:5px; margin-left:5px;">
                                    <div id="workdir_diskpercent" class="progress-bar progress-bar-info" role="progressbar" style="padding-left:3px; color:black; width:60%"></div>
                                </div>
                            </span>
                            <p id="workdir_diskspace" style="float:left; display:none;" class="small"></p>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="text" class="form-control" style="width: 100%;" id="rOut_dir" name="output_dir" placeholder="Enter output directory">
                            </div>
                        </div>
                    </div>
                    <div class="row" id="containersDiv">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div>
                                    <input type="checkbox" id="docker_check" name="docker_check" data-toggle="collapse" data-target="#docker_imgDiv"> Use Docker Image</input>
                                </div>
                            </div>
                            <div id="docker_imgDiv" class="collapse">
                                <div class="form-group row">
                                    <label for="docker_img" class="col-sm-2 control-label">Image </label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="docker_img" name="docker_img" placeholder="Enter docker image">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="docker_opt" class="col-sm-2 control-label"> RunOptions</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="docker_opt" name="docker_opt" placeholder="Enter docker runOptions">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="checkbox" id="singu_check" name="singu_check" data-toggle="collapse" data-target="#singu_imgDiv"> Use Singularity Image</input>
                            </div>
                            <div id="singu_imgDiv" class="collapse">
                                <div class="form-group row">
                                    <label for="singu_img" class="col-sm-3 control-label">Image Path <span><a data-toggle="tooltip" data-placement="bottom" title="(eg. project/umw_biocore/singularity/ UMMS-Biocore-singularity-master.simg)"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="singu_img" name="singu_img" placeholder="Enter singularity image path">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="singu_opt" class="col-sm-3 control-label"> RunOptions <span><a data-toggle="tooltip" data-placement="bottom" title="You can mount the directories by usig --bind command (eg. --bind /project:/project --bind /nl:/nl --bind /share:/share). It requires you to create the directories in the image beforehand. "><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" id="singu_opt" name="singu_opt" placeholder="Enter singularity runOptions">
                                    </div>
                                </div>
                                <div class="form-group row" id="singu_save_div" style="display:none">
                                    <label for="singu_save" class="col-sm-3 control-label"> Save over Image <span><a data-toggle="tooltip" data-placement="bottom" title="If you want to download and save over an existing image, you can check this box."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                    <div class="col-sm-9">
                                        <input type="checkbox" id="singu_save" name="singu_save"></input>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--            End of runsettings div-->
            <div id="workDetails">
                <div id="pipefiles">
                    <div>
                        <h6><b>Inputs</b></h6>
                    </div>
                    <div class="panel panel-default">
                        <div id="inputsTab">
                            </br>
                            <table id="inputsTable" class="table">
                                <thead>
                                    <tr>
                                        <th style="width:30%;" scope="col">Given Name</th>
                                        <th style="display:none;" scope="col">Identifier</th>
                                        <th style="display:none;" scope="col">File Type</th>
                                        <th style="display:none;" scope="col">Qualifier</th>
                                        <th style="display:none;" scope="col">Process Name</th>
                                        <th style="color:#D59035;  width:70%;" scope="col">Select Items</th>
                                    </tr>
                                </thead>
                                <tbody style="word-break: break-all;">
                                    <tr id="userInputs" style="display:none; background-color:#F5F5F5; font-weight:bold; font-style: italic; height:40px;">
                                        <td colspan="6">~ User Inputs ~</td>
                                    </tr>
                                    <tr id="systemInputs" class="collapsibleRow collapseIconDiv" style="display:none; font-weight:bold; font-style: italic; height:40px;">
                                        <td colspan="6">~ System Inputs ~<i data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a class="fa fa-minus-square-o collapseIcon collapseIconItem" style="font-size:15px; padding-left:10px; vertical-align:0px;"></a></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="ProcessPanelTitle" style="display:none;">
                        <h6><b>Process Options </b></h6>
                    </div>
                    <div id="ProcessPanel">
                    </div>
                    <div style="display:none;">
                        <h6><b>Outputs</b></h6>
                    </div>

                    <div class="panel panel-default" style="display:none;">
                        <div id="outputsTab">
                            </br>
                            <table id="outputsTable" class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width:30%;">Given Name</th>
                                        <th style="display:none;" scope="col">Identifier</th>
                                        <th style="display:none;" scope="col">File Type</th>
                                        <th style="display:none;" scope="col">Qualifier</th>
                                        <th style="display:none;" scope="col">Process Name</th>
                                        <th scope="col" style="width:70%;">Output Files</th>
                                    </tr>
                                </thead>
                                <tbody style="word-break: break-all;"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!--       end of work details div-->
        </div>

        <div id="advancedTab" class="tab-pane fade">
            <div class="row" style="margin-top:10px; margin-bottom:2px;">
                <div class="col-md-12">
                    <div class="form-group">
                        <h5 id="runTitleAdvanced"><label></label></h5>
                    </div>
                </div>
            </div>
            <div id="advOpt" class="row" style="margin-bottom:2px;">
                <div class="col-md-6" id="permsDiv">
                    <div class="form-group" style="margin-bottom: 5px;">
                        <label>Permissions to View</label>
                        <select id="permsRun" style="width:100%;" class="fbtn btn-default form-control permscheck" name="perms">
                            <option value="3" selected>Only me </option>
                            <option value="15">Only my group</option>
                            <option value="63">Everyone</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6" id="groupsDiv">
                    <div class="form-group" style="margin-bottom: 5px;">
                        <label>Group Selection </label>
                        <select id="groupSelRun" style="width:100%;" class="fbtn btn-default form-control permscheck" name="group_id">
                            <option value="" selected>Choose group </option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12">
                    <span class="small">You can share your run with a specific group (created in profile-> groups tab) by choosing "Only my group" and selecting the group name. The group members will see the run on their run status page. Alternatively, you can set the Permissions to View to "Everyone". This way, only users that know the run link will see it. The selected run will not show up on their run status page. Please click the save icon at the top after changing your permissions.
                    </span>
                </div>
                <div id="releaseDivParent">
                    <div id="releaseDiv" style="margin-top:5px;" class="col-md-12">
                        <div class="form-group">
                            <label id="releaseLabel" class="control-label">Release Date:</label>
                            <a id="setRelease" href="#"><span date="" id="releaseVal">Set a Date</span></a>
                            <span id="releaseValFinal"></span>
                            <span>
                                <a id="getTokenLink" style="display:none;" token="" data-toggle="tooltip" data-placement="bottom" data-html="true" title="Get Link <br> Only people who have the link can access the run until release date"><i style="font-size: 15px;" class="fa fa-chain"></i></a>
                                <div id="showTokenLink" style="display:none;" class="col-md-6 input-group">
                                    <input readonly id="tokenInput" type="text" class="form-control">
                                    <div id="copyToken" class="input-group-addon">
                                        <a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Copy Link to Clipboard"><span><i class="glyphicon glyphicon-copy"></i></span></a>
                                    </div>
                                </div>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" id="runCmdDiv" style="margin-top:10px;">
                    <div class="form-group">
                        <label>Run Command <span><a data-toggle="tooltip" data-placement="bottom" title="You may run the command or commands (by seperating each command with && sign) before the nextflow job starts. (eg. source /etc/bashrc && module load java/1.8.0_31 && module load singularity/singularity-2.4)"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <textarea id="runCmd" rows="1" name="runCmd" placeholder="Enter commands here.." style="min-width: 100%; max-width: 100%; border-color:lightgrey;"></textarea>
                    </div>
                </div>
                <div class="col-md-12" id="publishDirHide">
                    <div>
                        <label><input type="checkbox" id="publish_dir_check" name="publish_dir_check" data-toggle="collapse" data-target="#publishDirDiv"> Publish Directory</input> <span><a data-toggle="tooltip" data-placement="bottom" title="You may enter new publish directory (default: work directory) by clicking this item and entering path below.(eg. s3://yourbucket/test)"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                    </div>
                    <div id="publishDirDiv" class=" collapse">
                        <div class="form-group">
                            <input type="text" class="form-control" id="publish_dir" name="publish_dir" placeholder="Enter new publish directory">
                        </div>
                    </div>
                </div>

                <div id="jobSettingsDiv" class="col-md-12">
                    <div>
                        <label><input type="checkbox" id="exec_all" name="exec_all" data-toggle="collapse" data-target="#allProcessDiv"> Executor Settings for All Processes</input> <span><a data-toggle="tooltip" data-placement="bottom" title="You may adjust the nextflow executor settings by clicking this item."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                    </div>
                    <div id="allProcessDiv" class="panel panel-default collapse">
                        <div id="allProcessSett">
                            <table id="allProcessSettTable" class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" id="allProcessQueue">Queue</th>
                                        <th scope="col">Memory(GB)</th>
                                        <th scope="col">CPUs</th>
                                        <th scope="col">Time(min.)</th>
                                        <th scope="col">Other Options</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input id="job_queue" name="job_queue" class="form-control execqueue" type="text" value="short"></td>
                                        <td><input id="job_memory" class="form-control execmemory" type="text" name="job_memory" value="32"></td>
                                        <td><input id="job_cpu" name="job_cpu" class="form-control execcpu" type="text" value="1"></td>
                                        <td><input id="job_time" name="job_time" class="form-control exectime" type="text" value="100"></td>
                                        <td><input id="job_clu_opt" name="job_clu_opt" class="form-control execopt" type="text" value=""></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div>
                        <label><input type="checkbox" id="exec_each" name="exec_each" data-toggle="collapse" data-target="#eachProcessDiv"> Executor Settings for Each Process </input><span><a data-toggle="tooltip" data-placement="bottom" title="You may change executor settings for each process and override to 'executor settings for all processes' by clicking this item and clicking the checkbox of process that you want to change. This will only affect the settings of clicked process and keep the original settings for the rest."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                    </div>
                    <div id="eachProcessDiv" class="panel panel-default collapse">
                        <div id="processTab">
                            <table id="processTable" class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">Select</th>
                                        <th scope="col">Process Name</th>
                                        <th scope="col" id="eachProcessQueue">Queue</th>
                                        <th scope="col">Memory(GB)</th>
                                        <th scope="col">CPUs</th>
                                        <th scope="col">Time(min.)</th>
                                        <th scope="col">Other Options</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-12" id="intermeDelDiv">
                    <div>
                        <label> <input type="checkbox" id="intermeDel" name="interDelete" value="interDel" checked> Delete intermediate files after run</input></label>
                    </div>
                </div>
                <div class="col-md-12" id="notifDivHide">
                    <div>
                        <label><input type="checkbox" id="notif_check" name="notif_check" data-toggle="collapse" data-target="#notifDiv"> Notification Settings </input> <span><a data-toggle="tooltip" data-placement="bottom" title="You may overwrite the default notification settings of the profile page by checking this checkbox"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                    </div>
                    <div id="notifDiv" class="collapse">
                        <div class="col-md-12" style="margin-bottom:10px;">
                            <input type="checkbox" id="email_notif" name="email_notif" checked> Receive an email when the run is completed successfully or failed
                        </div>
                        <div class="col-md-12 form-group" style="margin-bottom:10px;">
                            <span>Email Recipients (optional): <span><a data-toggle="tooltip" data-placement="bottom" title="You may overwrite the default email recipients by entering comma-separated emails."><i class='glyphicon glyphicon-info-sign'></i></a></span></span> <input type="text" style="width:100%;" id="notif_email_list" name="notif_email_list">
                        </div>
                    </div>
                </div>

                <div class="col-md-12" id="cronDivHide">
                    <div>
                        <label><input type="checkbox" id="cron_check" name="cron_check" data-toggle="collapse" data-target="#cronDiv"> Automated Execution </input> <span><a data-toggle="tooltip" data-placement="bottom" title="You may activate the automated execution feature for periodically submission of this run"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                    </div>
                    <div id="cronDiv" class="collapse">
                        <div class="col-md-12" style="margin-top:10px;">
                            <label class="col-md-2">Run Prefix</label>
                            <div class="form-group col-md-5">
                                <input type="text" class="form-control" id="cron_prefix" name="cron_prefix">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="col-md-2" style="margin-top:20px;">Frequency</label>
                            <div class="form-group col-md-1">
                                <div class="text-center">Minute(s)</div>
                                <input value=0 type="number" min="0" class="form-control" id="cron_min" name="cron_min">
                            </div>
                            <div class="form-group col-md-1">
                                <div class="text-center">Hour(s)</div>
                                <input value=0 type="number" min="0" class="form-control" id="cron_hour" name="cron_hour">
                            </div>
                            <div class="form-group col-md-1">
                                <div class="text-center">Day(s)</div>
                                <input value=0 type="number" min="0" class="form-control" id="cron_day" name="cron_day">
                            </div>
                            <div class="form-group col-md-1">
                                <div class="text-center">Week(s)</div>
                                <input value=0 type="number" min="0" class="form-control" id="cron_week" name="cron_week">
                            </div>
                            <div class="form-group col-md-1">
                                <div class="text-center">Month(s)</div>
                                <input value=0 type="number" min="0" class="form-control" id="cron_month" name="cron_month">
                            </div>
                            <div class="form-group col-md-1">
                                <button style="margin-top:22px;" type="button" class="btn btn-primary setCron">Set</button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="col-md-2" style="margin-top:20px;">First Submission Date (Optional)</label>
                            <div class="form-group col-md-5">
                                <div class="text-center">Time</div>
                                <input type="datetime-local" class="form-control" id="cron_first" name="cron_first">
                            </div>
                            <div class="form-group col-md-1">
                                <button style="margin-top:22px;" type="button" class="btn btn-primary setCron">Set</button>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label class="col-md-2" style="margin-top:20px;">Next Submission</label>
                            <div class="form-group col-md-5">
                                <div class="form-control" style="margin-top:12px;" id="cronNextSubDate"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group" style="margin-top:20px; margin-bottom:8px;">
                        <label>Report Options</label>
                        <i data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a class="fa fa-plus-square-o collapseIcon" style=" font-size:15px; padding-left:5px; " data-toggle="collapse" data-target="#dispOpt"></a></i>
                    </div>
                </div>
                <!-- collapsed settings-->
                <div id="dispOpt" class="col-md-12 collapse">
                    <div class="form-group col-md-1">
                        <input type="checkbox" id="withReport" checked> Report</input>
                    </div>
                    <div class="form-group col-md-1">
                        <input type="checkbox" id="withTrace" checked> Trace</input>
                    </div>
                    <div class="form-group col-md-1">
                        <input type="checkbox" id="withDag"> DAG</input>
                    </div>
                    <div class="form-group col-md-2">
                        <input type="checkbox" id="withTimeline" checked> Timeline</input>
                    </div>
                </div>
            </div>
            <!-- collapsed settings ended-->
        </div>

        <div id="logTab" class="tab-pane fade">
            <div class="row" style="margin-top:10px; margin-bottom:2px;">
                <div class="col-md-12">
                    <div class="form-group">
                        <h5 id="runTitleLog"></h5>
                    </div>
                </div>
            </div>
            <div id="logContentDiv" <?php
                                    if ($SHOW_RUN_LOG != false || !empty($admin_id) || $role == "admin") {
                                        echo 'SHOW_RUN_LOG="true" ';
                                    }
                                    if ($SHOW_RUN_TIMELINE != false || !empty($admin_id) || $role == "admin") {
                                        echo 'SHOW_RUN_TIMELINE="true" ';
                                    }
                                    if ($SHOW_RUN_REPORT != false || !empty($admin_id) || $role == "admin") {
                                        echo 'SHOW_RUN_REPORT="true" ';
                                    }
                                    if ($SHOW_RUN_DAG != false || !empty($admin_id) || $role == "admin") {
                                        echo 'SHOW_RUN_DAG="true" ';
                                    }
                                    if ($SHOW_RUN_TRACE != false || !empty($admin_id) || $role == "admin") {
                                        echo 'SHOW_RUN_TRACE="true" ';
                                    }
                                    if ($SHOW_RUN_NEXTFLOWLOG != false || !empty($admin_id) || $role == "admin") {
                                        echo 'SHOW_RUN_NEXTFLOWLOG="true" ';
                                    }
                                    if ($SHOW_RUN_NEXTFLOWNF != false || !empty($admin_id) || $role == "admin") {
                                        echo 'SHOW_RUN_NEXTFLOWNF="true" ';
                                    }
                                    if ($SHOW_RUN_NEXTFLOWCONFIG != false || !empty($admin_id) || $role == "admin") {
                                        echo 'SHOW_RUN_NEXTFLOWCONFIG="true" ';
                                    }
                                    ?>></div>

        </div>
        <!-- logTab ended-->
        <div id="workflowTab" class="tab-pane fade">
            <div>
                <div style="padding-bottom:7px;">
                    <h4>Workflow<i data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a class="fa fa-minus-square-o collapseIcon" style="font-size:15px; padding-left:10px; vertical-align:2px;" data-toggle="collapse" data-target="#workFlowDiv"></a></i></h4>
                </div>
                <div id="workFlowDiv" class="collapse in">
                    <a href="" style="margin-left:0px;  align-self:center; max-width: 500px;" id="pipeline-title2"><i class="fa fa-spinner "></i></a>
                    <h6><b>Description</b></h6>
                    <div style="padding-left:16px; padding-right:16px; padding-bottom:20px; " id="desTitlePip">
                        <div class="row"><textarea id="pipelineSum" rows="3" style="min-width: 100%; max-width: 100%; border-color:lightgrey;"></textarea></div>
                    </div>

                    <div class="panel panel-default">
                        <div style="height:500px;" id="container" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
                    </div>
                </div>
            </div>

            <div id="subPipelinePanelTitle" style="display:none;">
                <h6><b>Pipeline Modules</b></h6>
            </div>
        </div>
        <!-- workflowTab ended-->
        <div id="reportTab" class="tab-pane fade">
            <div class="row" style="margin-top:10px; margin-bottom:2px;">
                <div class="col-md-12">
                    <div class="form-group">
                        <h5><span id="runTitleReport">Run Report</span>
                            <button id="refreshVerReport" type="button" class="btn" data-backdrop="false" style=" background:none; margin-left:5px; padding:0px;"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Reload Report Section"><i class="fa fa-refresh" style="font-size: 15px;"></i></a></button>
                            <button id="addRunNotes" type="button" class="btn" data-backdrop="false" style=" background:none; margin-left:5px; padding:0px;"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Add Run Notes"><i class="fa fa-edit" style="font-size: 15px;"></i></a></button>
                        </h5>
                    </div>
                    <div id="reportRows" style="margin-top:25px;"></div>
                    <div id="reportRowsFooter"></div>
                </div>
            </div>
        </div>
        <!-- reportTab ended-->

    </div>
</div>



<!--Confirm d3 Modal-->
<div id="confirmD3Modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="confirmD3ModalTitle">Confirm</h4>
            </div>
            <div class="modal-body" id="confirmD3ModalText">Text</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary delprocess" data-dismiss="modal" id="deleteD3Btn">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!--confirmD3Modal Modal Ends-->





<!--confirmDuplicate Modal-->
<div id="confirmDuplicate" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="confirmDuplicateTitle">Confirm</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" style="padding-left:15px;">
                    <div class="text-left" style="padding-top:10px;  padding-bottom:20px;">
                        <p id="confirmDuplicateText"></p>
                    </div>
                    <div class="form-group" id="pipelineRevsDiv">
                        <label class="col-sm-3 control-label" style="text-align: left; ">1. Choose Pipeline Revision: </label>
                        <div class="col-md-8" style="padding-right:0px;">
                            <select id="pipelineRevs" class="fbtn btn-default form-control">
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="chooseTargetProjectLabel">
                        <label class="col-sm-3 control-label" style="text-align: left; ">2. Choose Target Project: </label>
                    </div>
                    <div role="tabpanel">
                        <!-- Nav tabs -->
                        <ul class="nav nav-tabs" role="tablist">
                            <li id="userProjectLi" class="active"><a class="nav-item" data-toggle="tab" href="#userProjectTab">My Projects</a></li>
                            <li id="sharedProjectLi" class="nav-item"><a class="nav-item" data-toggle="tab" href="#sharedProjectTab">Shared Projects</a></li>
                        </ul>
                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane active" searchtab="true" id="userProjectTab">
                                <form class="form-horizontal">
                                    <div class="form-group">
                                        <div class="col-sm-12 pull-right">
                                            <button style="margin-top:15px;" type="button" class="btn btn-primary btn-sm pull-right" title="Add Project" id="addproject" data-toggle="modal" data-target="#projectmodal">Create a Project</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <table id="projecttable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>Check</th>
                                                    <th>Project Name</th>
                                                    <th>Owner</th>
                                                    <th>Modified on</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div role="tabpanel" class="tab-pane" searchtab="true" id="sharedProjectTab">
                                <div class="panel panel-default">
                                    <div class="panel-body">
                                        <table id="sharedProjectTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>Check</th>
                                                    <th>Project Name</th>
                                                    <th>Owner</th>
                                                    <th>Modified on</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="copyRunBut">Copy</button>
                <button type="button" class="btn btn-primary" id="moveRunBut">Move</button>
            </div>
        </div>
    </div>
</div>
<!--confirmDuplicate Modal Ends-->


<!--runHistoryModal-->
<div id="runHistoryModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Run Management</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" style="padding-left:15px;">
                    <div class="text-left" style="padding-top:10px;  padding-bottom:20px;">
                        <a id="removeNewRunIcon" data-toggle="tooltip" data-placement="bottom" data-original-title="Remove New Run"><i class="glyphicon glyphicon-remove" style="font-size: 15px; margin-right:5px;"></i></a>
                        <a id="runHistoryRecIcon" data-toggle="tooltip" data-placement="bottom" data-original-title="Choose Run to Recover"><i class="glyphicon glyphicon-repeat" style="font-size: 15px; margin-right:5px;"></i></a>
                        <a id="runHistoryDelIcon" data-toggle="tooltip" data-placement="bottom" data-original-title="Choose Run to Delete"><i class="fa fa-trash-o" style="font-size: 18px; margin-right:5px;"></i></a>
                        <a id="runHistoryPurgeIcon" data-toggle="tooltip" data-placement="bottom" data-original-title="Choose Run to Purge (Permanently Delete)"><i class="fa fa-trash" style="font-size: 18px; margin-right:5px;"></i></a>
                    </div>
                    <div style="margin-right:10px; ">
                        <table id="runHistoryTable" class="table table-striped" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>Check</th>
                                    <th>Run Name</th>
                                    <th>Disk Space(MB)</th>
                                    <th>Created on</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div id="runHistoryModalBut" class="text-right" style="margin-right:0px; margin-top:10px; display:none;">
                        <button type="button" class="btn btn-default" id="runHistoryModalCancel">Cancel</button>
                        <button type="button" class="btn btn-warning" id="runHistoryModalApply">Apply</button>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!--runHistoryModal Ends-->


<!--projectmodal Modal-->
<div id="projectmodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="projectmodaltitle">Add New Project</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="mProjectName" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mProjectName" name="name">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveproject" data-clickedrow="">Save changes</button>
            </div>
        </div>
    </div>
</div>
<!--projectmodal ends-->



<!--Select File modal-->
<div id="inputFilemodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" style="width:1250px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="filemodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <div id="fileAddOptions" role="tabpanel">
                    <!-- Nav tabs -->
                    <ul id="fileNav" class="nav nav-tabs" role="tablist">
                        <li id="importedFiles" class="active"><a class="nav-item" data-toggle="tab" href="#importedFilesTab">Files</a></li>
                        <li id="manualTabFile" class="nav-item"><a class="nav-item" data-toggle="tab" href="#manualTab">Manually</a></li>
                        <li id="publicFileTabFile"><a class="nav-item" data-toggle="tab" href="#publicFileTab">Public Files</a></li>
                        <?php if ($SHOW_DMETA != false) {
                            echo '<li id="dmetaFiles"><a class="nav-item" data-toggle="tab" href="#dmetaFileTab">Dmeta</a></li>';
                        } ?>
                    </ul>
                    <!-- Tab panes -->
                    <div id="fileContent" class="tab-content">
                        <div role="tabpanel" class="tab-pane active" searchTab="true" id="importedFilesTab">
                            <div class="panel panel-default">
                                </br>
                                <div class="panel-body">
                                    <div class="pull-right">
                                        <button type="button" class="btn btn-success btn-sm" id="addSample" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-plus"></i> Add File</button>
                                        <button type="button" class="btn btn-primary btn-sm" style="display:none;" id="editSample" data-toggle="modal" href="#editSampleModal" data-backdrop="static" data-keyboard="false"><i class="fa fa-pencil"></i> Edit Multiple Files</button>
                                        <button type="button" class="btn btn-primary btn-sm" style="display:none;" id="deleteSample" data-toggle="modal" href="#confirmModal" data-backdrop="static" data-keyboard="false"><i class="fa fa-trash"></i> Remove File</button>
                                    </div>
                                    <table id="sampleTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th style="width:40px;">Check</th>
                                                <th>Name</th>
                                                <th>Collection</th>
                                                <th>Run Environment</th>
                                                <th>Project</th>
                                                <th>Added on</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="panel panel-default" id="detailsOffileDiv" style="display:none;">
                                <div class="panel-body">
                                    <div class="pull-left">
                                        <h4>Details</h4>
                                    </div>
                                    <div class="box-body tab-pane active">
                                        <table class="table table-hover table-striped table-condensed" id="details_of_file_table">
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="manualTab" searchTab="true">
                            <div class="panel panel-default">
                                <div class="pull-right" style="margin:5px;">
                                    <button type="button" class="btn btn-success btn-sm" id="uploadManualFile" data-toggle="modal" data-backdrop="static" data-keyboard="false"><i class="fa fa-upload"></i> Upload File</button>
                                </div>
                                <form style="padding-top:50px; padding-right:10px; padding-bottom:40px; border-bottom:1px solid lightgrey; " class="form-horizontal">
                                    <div class="form-group" style="display:none">
                                        <label for="mIdFile" class="col-sm-2 control-label">ID</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="mIdFile" name="id">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="mFilePath" class="col-sm-2 control-label">File Path</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="mFilePath" name="name">
                                        </div>
                                    </div>
                                </form>
                                </br>
                                <div class="row">
                                    <div class="col-sm-3" style="border-right:1px solid lightgrey; padding-top:6px;">
                                        <table id="projectListTable" class="table  table-striped display" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Project Name</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <div class="col-sm-9" style="overflow:auto">
                                        <table id="projectFileTable" class="table table-striped  display" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Check</th>
                                                    <th scope="col">File/Values</th>
                                                    <th scope="col">Modified On</th>
                                                </tr>
                                            </thead>
                                            <tbody style="word-break: break-all; "></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" id="publicFileTab" class="tab-pane" searchTab="true">
                            <div class="row">
                                <div class="col-sm-12" style="padding-top:6px;">
                                    <p id="publicFileTabWarn"></p>
                                    <table id="publicFileTable" class="table table-striped table-bordered display" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th scope="col">Check</th>
                                                <th scope="col">File/Values</th>
                                                <th scope="col">Modified On</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php if ($SHOW_DMETA != false) {
                            $dURLS = array_map('trim', explode(',', $DMETA_URL));
                            $dLabels = array_map('trim', explode(',', $DMETA_LABEL));
                            $ids = array();
                            $tabList = "";
                            $tabContent = "";
                            for ($i = 0; $i < count($dURLS); $i++) {
                                if (count($dURLS) == count($dLabels)) {
                                    $pre = $dLabels[$i];
                                } else {
                                    $pre = str_replace("https:", "", $dURLS[$i]);
                                    $pre = str_replace("http:", "", $pre);
                                    $pre = str_replace("/", "", $pre);
                                    $pre = str_replace(":", "", $pre);
                                }
                                $ids[] = $pre;
                                $tabDivId = $pre . "Tab";
                                $tableDivId = $pre . "Table";
                                $active = "";
                                if ($i === 0) {
                                    $active = "active";
                                }
                                $tabList .= '<li class="' . $active . '"><a class="nav-item" data-toggle="tab" href="#' . $tabDivId . '">' . $pre . '</a></li>';
                                $tabContent .= '
            <div id="' . $tabDivId . '" role="tabpanel" class="tab-pane ' . $active . '" searchmetatab="true">
                <div class="row">
                    <div class="col-sm-12" style="padding-top:6px;">
                        <table id="' . $tableDivId . '" class="table table-striped table-bordered display" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th style="width:40px;">Check</th>
                                    <th>Name</th>
                                    <th>Collection</th>
                                    <th>Run Environment</th>
                                    <th>Project</th>
                                    <th>Added on</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="panel panel-default" id="detailsOfmetaDiv" style="display:none; margin-top:10px;">
                            <div class="panel-body">
                                <div class="pull-left">
                                    <h4>Details</h4>
                                </div>
                                <div class="box-body tab-pane active">
                                    <table class="table table-hover table-striped table-condensed" id="details_of_meta_table">
                                    </table>
                                </div>
                            </div>
                        </div>
            </div>';
                            }


                            $dmetaDiv = '
            <div role="tabpanel" id="dmetaFileTab" dmetaurl="' . $DMETA_URL . '" dmetaid="' . join(",", $ids) . '" class="tab-pane" searchTab="true">
               <div role="tabpanel">
                    <!-- Nav tabs -->
                    <ul id="test" style="margin-top:10px;" class="nav nav-tabs" role="tablist">
                    ' . $tabList . '
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        ' . $tabContent . '
                    </div>
                </div>
            </div>
            ';
                            echo $dmetaDiv;
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savefile" data-clickedrow="">Save</button>
            </div>
        </div>
    </div>
</div>




<!--input SingleFile modal-->
<div id="inputSingleFilemodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Enter File</h4>
            </div>
            <div class="modal-body">
                <form style="padding-top:30px; padding-right:10px; padding-bottom:50px; border-bottom:1px solid lightgrey; " class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mIdSingleFile" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-2 control-label">File Location</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="singleFilePath" name="name">
                        </div>
                        <div>
                            <div class="col-sm-2"></div>
                            <div class="col-sm-10">
                                <span class="small">
                                    You can enter the full path of a file, Amazon (S3) or Google (GS) location
                                </span>
                            </div>
                        </div>
                    </div>
                    <div style="width: 100%; height: 16px; border-bottom: 1px solid #E0E6E8; text-align: center"><span style="font-size: 18px; background-color: white; padding: 0 7px; color:#B7BFC6;"> or </span></div>
                </form>
                <div id="importModalPart1">
                    <form id="uploadSingleFile" action="ajax/import.php" class="dropzone">
                        <div class="fallback">
                            <input name="file" type="file" multiple />
                        </div>
                    </form>
                </div>
                <div id="importModalPart2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveSingleFile" data-clickedrow="">Save</button>
            </div>
        </div>
    </div>
</div>

<!--Save Value modal-->
<div id="inputValmodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" style="width:1200px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="valmodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <div id="valAddOptions" role="tabpanel">
                    <!-- Nav tabs -->
                    <ul id="ValNav" class="nav nav-tabs" role="tablist">
                        <li id="manualTabVal" class="active"><a class="nav-item" data-toggle="tab" href="#manualTabV">Manually</a></li>
                        <li id="publicValTabVal"><a class="nav-item" data-toggle="tab" href="#publicValTab">Public Values</a></li>
                        </li>
                    </ul>
                    <!-- Tab panes -->
                    <div id="fileContent" class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="manualTabV">
                            <div class="panel panel-default">
                                </br>
                                <form style="padding-top:30px; padding-right:10px; padding-bottom:50px; border-bottom:1px solid lightgrey; " class="form-horizontal">
                                    <div class="form-group" style="display:none">
                                        <label for="mIdVal" class="col-sm-2 control-label">ID</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="mIdVal" name="id">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="mValName" class="col-sm-1 control-label">Value</label>
                                        <div class="col-sm-11">
                                            <input type="text" class="form-control" id="mValName" name="name">
                                        </div>
                                    </div>
                                </form>
                                <div class="row">
                                    <div class="col-sm-3" style="border-right:1px solid lightgrey; padding-top:6px;">
                                        <table id="projectListTableVal" class="table  table-striped display" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Project Name</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <div class="col-sm-9" style="overflow:auto">
                                        <table id="projectValTable" class="table  table-striped  display" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Check</th>
                                                    <th scope="col">File/Values</th>
                                                    <th scope="col">Modified On</th>
                                                </tr>
                                            </thead>
                                            <tbody style="word-break: break-all; "></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="publicValTab" class="tab-pane ">
                            <div class="row">
                                <div class="col-sm-12" style="padding-top:6px;">
                                    <p id="publicValTabWarn"></p>
                                    <table id="publicValTable" class="table table-striped table-bordered display" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th scope="col">Check</th>
                                                <th scope="col">File/Values</th>
                                                <th scope="col">Modified On</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveValue" data-clickedrow="">Save Value</button>
            </div>
        </div>
    </div>
</div>





<!--Add Sample Modal-->
<div id="addFileModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" style="width:1250px;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Add File</h4>
            </div>
            <div class="modal-body">
                <div role="tabpanel">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li id="hostFileTab" class="active"><a class="nav-item" data-toggle="tab" href="#hostFiles">Remote Files</a></li>
                        <li id="geoFileTab"><a class="nav-item" data-toggle="tab" href="#geoFiles">GEO/NCBI Files</a></li>
                        </li>
                        <li id="uploadFileTab"><a class="nav-item" data-toggle="tab" href="#uploadFiles">Upload Files</a></li>
                        </li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="hostFiles">
                            </br>
                            <form class="form-horizontal">
                                <div class="form-group" id="mRunAmzKeyS3Div" style="display:none; ">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">Optional: Amazon Keys for S3 <span><a data-toggle="tooltip" data-placement="bottom" title="Amazon Keys to access your S3 storage. If you need Amazon Keys to access S3 storage, you can enter your keys in profile->Amazon Keys section."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                                    <div class="col-sm-8">
                                        <select id="mRunAmzKeyS3" class="fbtn btn-default form-control" name="amazon_cre_id">
                                            <option value="" selected>None</option>
                                        </select>
                                    </div>
                                    <div>
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-8">
                                            <span class="small">If you can't access the s3 bucket after clicking the search button, please select Amazon keys to reach the s3 bucket. </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group" id="mRunGoogKeyGSDiv" style="display:none; ">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:45px; color:#a7a218;">Select Google Keys (for GS) <span><a data-toggle="tooltip" data-placement="bottom" title="Google Keys to access your GS storage"><i class='glyphicon glyphicon-info-sign' style="color:#ffbb33;"></i></a></span></label>
                                    <div class="col-sm-8">
                                        <select id="mRunGoogKeyGS" class="fbtn btn-default form-control" name="google_cre_id">
                                            <option value="" disabled selected>Select Google Keys </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" style="margin-bottom:10px;" id="file_dir_div">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">1. File Location <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter the location of your files and click search button."><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search" id="file_dir" name="file_dir" value="">
                                            <div class="input-group-btn">
                                                <button class="btn btn-primary" id="viewDirBut" type="button">
                                                    <span class="glyphicon glyphicon-search"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9">
                                            <span class="small"><?php echo $CUSTOM_FILE_LOCATION_MESSAGE ?></span>
                                            <a class="small" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html#adding-files" target="_blank">Need help?</a> <span class="small">
                                                Full path of a directory: eg.<code style="color:#333; cursor:pointer;" onclick="fillFileSearchBox(this, 'file_dir')">/share/data/umw_biocore/genome_data/mousetest/mm10/gz</code>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9">
                                            <span class="small">
                                                Web link: eg.<code style="color:#333; cursor:pointer;" onclick="fillFileSearchBox(this, 'file_dir')">https://galaxyweb.umassmed.edu/pub/dnext_data/test/reads</code>
                                            </span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9">
                                            <span style="font-size:80%;">
                                                Amazon (S3) or Google (GS) Bucket: eg.<code style="color:#333; cursor:pointer;" onclick="fillFileSearchBox(this, 'file_dir') ">s3://biocore/fastq</code> or <code style="color:#333; cursor:pointer;" onclick="fillFileSearchBox(this, 'file_dir')">gs://biocore/fastq</code>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div id="viewDirDiv" class="form-group" style="margin-top:10px;">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-8">
                                        <select id="viewDir" class="form-control" size="5" style="display:none;"></select>
                                        <span id="viewDirInfo" style="font-size:80%;">
                                            <a class="small" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/faq.html#run-questions" target="_blank">Couldn't find your files?</a> You can navigate through folders by double-clicking above.
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">2. File Type <span><a data-toggle="tooltip" data-placement="bottom" title="Please choose your file type"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <select id="file_type" class="fbtn btn-default form-control" name="file_type">
                                            <option value="fastq" selected>FASTQ</option>
                                            <option value="bam">BAM</option>
                                            <option value="bai">BAI</option>
                                            <option value="bed">BED</option>
                                            <option value="csv">CSV</option>
                                            <option value="tab">TAB</option>
                                            <option value="tsv">TSV</option>
                                            <option value="txt">TXT</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="collection_type" class="col-sm-3 control-label text-left" style="padding-left:30px;">3. Collection Type <span><a data-toggle="tooltip" data-placement="bottom" title="For paired-end reads please choose 'paired list' and for single-end reads or any list of files choose 'single/list' option."><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <select id="collection_type" class="fbtn btn-default form-control" name="collection_type">
                                            <option value="" disabled selected>Choose Collection Type</option>
                                            <option value="single">Single/List</option>
                                            <option value="pair">Paired List</option>
                                            <option value="triple">Triple List</option>
                                            <option value="quadruple">Quadruple List</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">4. File Pattern <span><a data-toggle="tooltip" data-placement="bottom" title="For paired-end reads please enter forward and reverse read pattern to match file pairs."><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div class="col-sm-6 singlepatternDiv" style="display:none;">
                                            <p class="col-sm-4 control-label">Filename Extension <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter end of the file name to filter files (eg. fastq or fq.gz). This pattern will be removed from the file names to fill 'Names' field in the table below."><i class='glyphicon glyphicon-info-sign'></i></a></span> </p>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="single_pattern" name="single_pattern" value="">
                                            </div>
                                            <div class="col-sm-12" style="margin-top:8px;">
                                                <select id="singleList" type="select-multiple" multiple class="form-control" size="9"></select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 forwardpatternDiv" style="display:none;">
                                            <p class="col-sm-4 control-label">R1 Pattern <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter pattern for forward reads eg. _R1"><i class='glyphicon glyphicon-info-sign'></i></a></span> </p>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="forward_pattern" name="forward_pattern" value="_R1">
                                            </div>
                                            <div class="col-sm-12" style="margin-top:8px; margin-bottom:12px;">
                                                <select id="forwardList" type="select-multiple" multiple class="form-control" size="9"></select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 reversepatternDiv" style="display:none;">
                                            <p class="col-sm-4 control-label">R2 Pattern <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter pattern for reverse reads eg. _R2"><i class='glyphicon glyphicon-info-sign'></i></a></span> </p>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="reverse_pattern" name="reverse_pattern" value="_R2">
                                            </div>
                                            <div class="col-sm-12" style="margin-top:8px; margin-bottom:12px;">
                                                <select id="reverseList" type="select-multiple" multiple class="form-control" size="9"></select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 r3patternDiv" style="display:none;">
                                            <p class="col-sm-4 control-label">R3 Pattern <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter pattern for reads eg. _R3"><i class='glyphicon glyphicon-info-sign'></i></a></span> </p>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="r3_pattern" name="r3_pattern" value="_R3">
                                            </div>
                                            <div class="col-sm-12" style="margin-top:8px;">
                                                <select id="r3List" type="select-multiple" multiple class="form-control" size="9"></select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6 r4patternDiv" style="display:none;">
                                            <p class="col-sm-4 control-label">R4 Pattern <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter pattern for reads eg. _R4"><i class='glyphicon glyphicon-info-sign'></i></a></span> </p>
                                            <div class="col-sm-8">
                                                <input type="text" class="form-control" id="r4_pattern" name="r4_pattern" value="_R4">
                                            </div>
                                            <div class="col-sm-12" style="margin-top:8px;">
                                                <select id="r4List" type="select-multiple" multiple class="form-control" size="9"></select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group patternButs" style="display:none;">
                                    <div class="col-sm-12">
                                        <div class="col-sm-6">
                                            <p class="col-sm-6 control-label">Auto-merging Pattern (Optional) <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="A glob pattern to merge files automatically. e.g., for merging reads coming from different lanes L001, L002, L003, etc., you can enter _L00?"><i class="glyphicon glyphicon-info-sign"></i></a></span> </p>
                                            <div class="col-sm-6">
                                                <input type="text" class="form-control" id="auto_merge_pattern" name="auto_merge_pattern" value="">
                                            </div>

                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="col-sm-4"></div>
                                        <div class="col-sm-8">
                                            <span class="pull-right" style="padding-top:7px; padding-left:5px;"><a data-toggle="tooltip" data-placement="bottom" title="In order to merge multiple files, first select the files and then click 'Add Selected Files' button. If you don't need to merge files, you can simply click 'Add All Files' button."><i class='glyphicon glyphicon-info-sign'></i></a></span>
                                            <button id="smart_add_file" type="button" class="btn btn-primary pull-right" style="margin-right:3px;" onclick="smartSelection('all')">Add All Files</button>
                                            <button type="button" class="btn btn-primary pull-right" onclick="smartSelection('only')" style="margin-right:3px;">Add Selected Files</button>
                                            <button id="add_selection_file" type="button" class="btn btn-primary pull-right" onclick="addSelection()" style="margin-right:3px;">Merge Selected Files</button>
                                            <button id="clear_selection" type="button" class="btn btn-warning pull-right" style="margin-right:3px;" onclick="clearSelection()">Reset</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group patternTable" style="display:none;">
                                    <div class="col-sm-12" style="padding:30px;">
                                        <table id="selectedSamples" class="table table-striped compact table-bordered display" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">Files Used</th>
                                                    <th scope="col">Directory</th>
                                                    <th scope="col" style="width:20px;">Remove</th>
                                                    <th scope="col">Amz_key</th>
                                                    <th scope="col">Goog_key</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">5. Collection Name <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter name of collection to recall all of the entered files later"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <select id="collection_id" class="fbtn btn-default form-control" name="collection_id">
                                            <option value="" disabled selected>Type New Collection Name or Choose to Add into Existing Collection</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" id="archive_dir_div">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">Local Archive Directory (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter full path of the directory where all of the entered files will be published after merging/renaming operation eg. /home/test/archive"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="archive_dir" name="archive_dir">
                                    </div>
                                </div>
                                <div class="form-group" id="mArchAmzS3Div">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">Amazon S3 Backup (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="Please specify your Amazon bucket where all of the entered files will be published after merging/renaming operation eg. s3://yourbucket/archive"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="s3_archive_dir" name="s3_archive_dir">
                                    </div>
                                </div>
                                <div class="form-group" id="mArchGoogGSDiv">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">Google Storage Backup (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="Please specify your Google Storage bucket where all of the entered files will be published after merging/renaming operation eg. gs://yourbucket/archive"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="gs_archive_dir" name="gs_archive_dir">
                                    </div>
                                </div>
                                <div class="form-group" id="mArchAmzKeyS3Div" style="display:none; ">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:45px; color:#a7a218;">Select Amazon Keys(S3 Archive) <span><a data-toggle="tooltip" data-placement="bottom" title="Amazon Keys to access your S3 archive directory"><i class='glyphicon glyphicon-info-sign' style="color:#ffbb33;"></i></a></span></label>
                                    <div class="col-sm-8">
                                        <select id="mArchAmzKeyS3" class="fbtn btn-default form-control" name="amazon_cre_id">
                                            <option value="" disabled selected>Select Amazon Keys </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" id="mArchGoogKeyGSDiv" style="display:none; ">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:45px; color:#a7a218;">Select Google Keys(GS Archive) <span><a data-toggle="tooltip" data-placement="bottom" title="Google Keys to access your GS archive directory"><i class='glyphicon glyphicon-info-sign' style="color:#ffbb33;"></i></a></span></label>
                                    <div class="col-sm-8">
                                        <select id="mArchGoogKeyGS" class="fbtn btn-default form-control" name="google_cre_id">
                                            <option value="" disabled selected>Select Google Keys </option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- geoFiles tab-pane-->
                        <div role="tabpanel" class="tab-pane" id="geoFiles">
                            </br>
                            <form class="form-horizontal">
                                <div class="form-group" id="viewGeoButDiv">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">1. GSE/GSM/SRR ID <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter GSE, GSM or SRR id "><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search GEO/NCBI Data" id="geo_id" name="geo_id" value="">
                                            <div class="input-group-btn">
                                                <button class="btn btn-primary" id="viewGeoBut" type="button">
                                                    <span class="glyphicon glyphicon-search"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="col-sm-3"></div>
                                        <div class="col-sm-9">
                                            <a class="small" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html#adding-files" target="_blank">Need help?</a> <span class="small">
                                                eg.<code style="color:#333; cursor:pointer;" onclick="fillFileSearchBox(this,'geo_id')">GSM1331276</code>, <code style="color:#333; cursor:pointer;" onclick="fillFileSearchBox(this,'geo_id')">GSE55190</code>, <code style="color:#333; cursor:pointer;" onclick="fillFileSearchBox(this,'geo_id')">SRR10095965</code>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">2. Searched GEO Files <span><a data-toggle="tooltip" data-placement="bottom" title="Click 'select' or 'select all' button to add files to a collection"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                </div>
                                <div class="form-group" id="seaGeoSamplesDiv">
                                    <div class="col-sm-12" style="padding-left:30px; margin-bottom:25px;">
                                        <table id="searchedGeoSamples" class="table table-striped compact table-bordered display" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">GEO Accession</th>
                                                    <th scope="col">Single/Paired</th>
                                                    <th scope="col" style="width:20px;">Select</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="col-sm-8"></div>
                                        <div class="col-sm-4">
                                            <button id="selectAllSraBut" type="button" class="btn btn-primary pull-right" onclick="selectAllSRA()">Select All</button>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">3. Selected GEO Files <span><a data-toggle="tooltip" data-placement="bottom" title="These files will be added into collection" class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                </div>
                                <div class="form-group" id="selGeoSamplesDiv">
                                    <div class="col-sm-12" style="padding-left:30px;">
                                        <table id="selectedGeoSamples" class="table table-striped compact table-bordered display" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th scope="col">Name</th>
                                                    <th scope="col">GEO Accession</th>
                                                    <th scope="col">Single/Paired</th>
                                                    <th scope="col" style="width:20px;">Remove</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">4. Collection Name <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter name of collection to recall all of the entered files later"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <select id="collection_id_geo" class="fbtn btn-default form-control" name="collection_id">
                                            <option value="" disabled selected>Type New Collection Name or Choose to Add into Existing Collection</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" id="archive_dir_geo_div">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">Local Archive Directory (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter full path of the directory where all of the entered files will be published after merging/renaming operation eg. /home/test/archive"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="archive_dir_geo" name="archive_dir">
                                    </div>
                                </div>
                                <div class="form-group" id="mArchAmzS3Div_GEO">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">Amazon S3 Backup (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="Please specify your Amazon bucket where all of the entered files will be published after merging/renaming operation eg. s3://yourbucket/archive"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="s3_archive_dir_geo" name="s3_archive_dir">
                                    </div>
                                </div>
                                <div class="form-group" id="mArchGoogGSDiv_GEO">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px;">Google Storage Backup (optional) <span><a data-toggle="tooltip" data-placement="bottom" title="Please specify your Google Cloud bucket where all of the entered files will be published after merging/renaming operation eg. gs://yourbucket/archive"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="gs_archive_dir_geo" name="gs_archive_dir">
                                    </div>
                                </div>
                                <div class="form-group" id="mArchAmzKeyS3Div_GEO" style="display:none; ">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px; color:#a7a218;">Select Amazon Keys(S3 Archive) <span><a data-toggle="tooltip" data-placement="bottom" title="Amazon Keys to access your S3 archive directory"><i class='glyphicon glyphicon-info-sign' style="color:#ffbb33;"></i></a></span></label>
                                    <div class="col-sm-8">
                                        <select id="mArchAmzKeyS3_GEO" class="fbtn btn-default form-control" name="amazon_cre_id">
                                            <option value="" disabled selected>Select Amazon Keys </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" id="mArchGoogKeyGSDiv_GEO" style="display:none; ">
                                    <label class="col-sm-3 control-label text-left" style="padding-left:30px; color:#a7a218;">Select Google Keys(GS Archive) <span><a data-toggle="tooltip" data-placement="bottom" title="Google Keys to access your GS archive directory"><i class='glyphicon glyphicon-info-sign' style="color:#ffbb33;"></i></a></span></label>
                                    <div class="col-sm-8">
                                        <select id="mArchGoogKeyGS_GEO" class="fbtn btn-default form-control" name="google_cre_id">
                                            <option value="" disabled selected>Select Google Keys </option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- uploadFiles tab-pane-->
                        <div role="tabpanel" class="tab-pane" id="uploadFiles">
                            </br>
                            <form class="form-horizontal">
                                <div class="form-group" id="target_dir_div">
                                    <label class="col-sm-4 control-label text-left" style="padding-left:30px;">Target Directory in the Host (Full Path) <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter the full path of the directory in your host where all uploaded files will be transfered. eg. /share/data/umw_biocore/ genome_data/mousetest/mm10/gz"><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="target_dir" name="target_dir" value="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-12 control-label text-left" style="padding-left:30px;">Add files to upload and click the 'Start Upload' button. First, all files will be uploaded and then transfered to target directory in your run environment.</label>
                                </div>
                            </form>
                            <div id="pluploader">
                                <p>Your browser doesn't have Flash, Silverlight or HTML5 support.</p>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-default pull-right" style="margin-right:8px;" data-toggle="collapse" data-target="#pluploaderLogDiv">View Log</button>
                                <button type="button" class="btn btn-default pull-right" style="margin-right:8px;" id="pluploaderReset">Reset</button>
                                <!--                                <button type="button" class="btn btn-default pull-right" style="margin-right:8px;" id="pluploaderStop" >Stop</button>-->
                            </div>
                            <div class="form-group">
                                <div id="pluploaderLogDiv" class="col-sm-12 collapse">
                                    <pre id="pluploaderLog" style="height:250px;"></pre>
                                </div>
                            </div>
                            </br>
                            <form class="form-horizontal" id="uploadSucDiv" style="display:none;">
                                <div class="form-group">
                                    <label class="col-sm-12 control-label text-left" style="padding-left:30px;">3. All files uploaded and transfered to your run environment. Now, you can add these files into the system by clicking 'Select Files' button.</label>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-6" style="padding-left:30px;">
                                        <button type="button" class="btn btn-success" id="showHostFiles">Select Files</button>
                                    </div>
                                </div>
                            </form>
                            <form class="form-horizontal" id="uploadSucSingleDiv" style="display:none;">
                                <div class="form-group">
                                    <label class="col-sm-12 control-label text-left" style="padding-left:30px;">3. Your file successfully uploaded. </label>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-6" style="padding-left:30px;">
                                        <button type="button" class="btn btn-success" id="selectAsManually">Use Uploaded File </button>
                                    </div>
                                </div>
                            </form>
                        </div> <!-- uploadFiles tab-pane-->
                    </div> <!--  Navtab-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="mSaveFiles">Save Files</button>
                </div>
            </div>
            <!--            Modal Body ends-->
        </div>
    </div>
</div>
<!--addFileModal ends-->

<!--profVarRunEnvModal Modal Starts-->
<div id="profVarRunEnvModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Profile Variables</h4>
            </div>
            <div class="modal-body" id="profVarRunEnvModalBody">
                <form class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <p id="profVarRunEnvModalText">Test</p>
                        </div>
                    </div>
                </form>
                <form class="form-horizontal" id="profVarRunEnvBlock">

                </form>
                <form class="form-horizontal" style="margin-top:30px;">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <p id="profVarRunEnvModalText2">Test</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="profVarRunEnvSave">Save</button>
            </div>
        </div>
    </div>
</div>
<!--profVarRunEnvModal Modal ENDs-->




<!--Info Modal Starts-->
<div id="infoModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Information</h4>
            </div>
            <div class="modal-body">
                <span id="infoModalText">Text</span>
                </br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<!--Info Modal ENDs-->

<!--errGeoModal Starts-->
<div id="errGeoModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Information</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div id="errGeoModalDupDiv">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div id="errGeoModalDupText">Dup Text</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <textarea rows="3" class="form-control" style="resize:none;" readonly id="errGeoModalDupList"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="errGeoModalErrDiv">
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div id="errGeoModalErrText">Err Text</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="col-sm-12">
                                    <textarea rows="3" class="form-control" style="resize:none;" readonly id="errGeoModalErrList"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" id="retry_geo_search" data-dismiss="modal">Retry</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>
<!--errGeoModal ENDs-->

<!--New Collection Modal Starts-->
<div id="newCollectionModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">New Collection</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <div class="col-sm-12">
                            <p id="newCollectionModalText">Selected files are not match with the existing collections. Please enter a new collection name in the field below.</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <label for="mUserLab" class="col-sm-2 control-label">Collection</label>
                            <div class="col-sm-10">
                                <select id="newCollectionName" class="fbtn btn-default form-control">
                                    <option value="" disabled selected>Type New Collection Name or Choose to Add into Existing Collection</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveNewCollect">Save</button>
            </div>
        </div>
    </div>
</div>
<!--New Collection Modal ENDs-->

<div id="releaseModal" class="modal fade" tabindex="-1" role="dialog" data-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Release Date</h4>
            </div>
            <div class="modal-body">
                <p id="releaseModalText">Text</p>
                <label class="col-md-3" style="padding-top:5px;">Release Date:</label>
                <div id="relDateDiv" class="col-md-6 input-group date">
                    <input id="relDateInput" type="text" class="form-control">
                    <div class="input-group-addon">
                        <span class="fa fa-calendar"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary cancelReleaseDateBut" data-dismiss="modal">Release Immediately</button>
                <button type="button" class="btn btn-primary" id="setReleaseDateBut">Set Release Date</button>
            </div>
        </div>
    </div>
</div>

<!--Edit Sample Modal-->
<div id="editSampleModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Edit File</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">GEO ID</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="geo_used">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Input File(s) <span><a data-toggle="tooltip" data-placement="bottom" title="To merge files, please enter each file set into a new line. If you're entering paired/triple/quadruple file set, please separate each file with a comma."><i class='glyphicon glyphicon-info-sign'></i></a></span> </label>

                        <div class="col-sm-9">
                            <textarea spellcheck="false" rows="3" class="form-control" name="files_used"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Input File(s) Location</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="file_dir">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Collection Type</label>
                        <div class="col-sm-9">
                            <select class="fbtn btn-default form-control" name="collection_type">
                                <option value="single">Single/List</option>
                                <option value="pair">Paired List</option>
                                <option value="triple">Triple List</option>
                                <option value="quadruple">Quadruple List</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">File Type</label>
                        <div class="col-sm-9">
                            <select id="file_type" class="fbtn btn-default form-control" name="file_type">
                                <option value="fastq" selected>FASTQ</option>
                                <option value="bam">BAM</option>
                                <option value="bai">BAI</option>
                                <option value="bed">BED</option>
                                <option value="csv">CSV</option>
                                <option value="tab">TAB</option>
                                <option value="tsv">TSV</option>
                                <option value="txt">TXT</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Local Archive Directory</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="archive_dir">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Amazon S3 Backup</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="s3_archive_dir">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Google Storage Backup</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="gs_archive_dir">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Collection</label>
                        <div class="col-sm-9">
                            <select name="collection_id" class="fbtn btn-default form-control" id="collection_id_edit">
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveEditSampleModal">Save</button>
            </div>
        </div>
    </div>
</div>

<!--Confirm Modal-->
<div id="confirmModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="confirmModalTitle">Confirm</h4>
            </div>
            <div class="modal-body" id="confirmModalText">Text</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary delprocess" data-dismiss="modal" id="deleteBtn">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal" id="cancelButton">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!--Confirm Modal Ends-->

<!--manualRunModal-->
<div id="manualRunModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Manual Run Console</h4>
            </div>
            <div class="modal-body" style="padding:20px;">
                <div class="row">
                    <div class="form-group col-sm-12">
                        <p id="manualRunText"></p>
                    </div>
                </div>

                <div class="panel panel-default">
                    <div class="panel-body" id="manuaRunPanel">
                        <div class="form-group col-sm-12" style="margin-bottom:20px;">
                            <label class="col-sm-3 control-label text-left" style="padding-left:70px; padding-top:5px;">Run Type: </label>
                            <div class="col-sm-4">
                                <select id="manuaRunType" class="form-control">
                                    <option value="rerun">Fresh Start</option>
                                    <option value="resumerun">Resume</option>
                                </select>
                            </div>
                            <div class="col-sm-1" style="padding-top:5px;">
                                <span><i class='fa fa-long-arrow-right'></i></span>

                            </div>
                            <div class="col-sm-4">
                                <button id="getManualRunCmd" type="button" class="btn btn-primary">Get Run Command</button>
                            </div>
                        </div>
                        <div class="form-group col-sm-12">
                            <p class="col-sm-12 control-label text-left" style="padding:0px; margin:0px;">Run Command: </p>
                            <div class="col-sm-11" style="padding:0px; margin:0px;">
                                <textarea readonly rows="4" class="form-control" style="resize:none; " id="manualRunCmd"></textarea>
                            </div>
                            <div class="col-sm-1" style="margin-top:20px; padding-right:10px;">
                                <button id="cpTooltipManRun" type="button" class="btn btn-default form-control" data-backdrop="false">
                                    <a data-toggle="tooltip" data-placement="bottom" data-original-title="Copy to Clipboard">
                                        <span><i class="glyphicon glyphicon-copy"></i></span></a>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-sm-12">
                        <p id="manualRunHelp" style="font-size:13px;"></p>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!--manualRunModal Ends-->


<!--rename Modal-->
<div id="editRunModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Edit</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="editRunName" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="editRunName">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="editRunDetails" data-clickedrow="">Save</button>
            </div>
        </div>
    </div>
</div>
<!--rename Ends-->