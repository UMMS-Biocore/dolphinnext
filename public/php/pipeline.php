<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
if ($ownerID != '') {
    $login = 1;
} else {
    $login = 0;
}
session_write_close();
?>

<div class="box-header" style=" padding-left:1px; padding-right:1px; padding-top:0px; font-size:large; ">
    <div style=" border-bottom:1px solid lightgrey;">

        <div class="row" style="margin-left:0px; margin-right:0px;">
            <div id="pipeGroupFloatLeft" style="float:left;">
                <i class="fa fa-spinner " style="margin-left:0px; margin-right:0px;"></i> Pipeline:
                <input class="box-dynamic width-dynamic" type="text" pipelineid="<?php if ($id != "0") {
                                                                                        echo $id;
                                                                                    } ?>" name="pipelineTitle" autocomplete="off" placeholder="Enter Pipeline Name" style="margin-left:0px; font-size: large; font-style:italic; align-self:center; max-width: 500px;" title="Rename" data-placement="bottom" data-toggle="tooltip" num="" id="pipeline-title"><span class="width-dynamic" style="display:none"></span></input>

                <button type="submit" id="savePipeline" class="btn" data-backdrop="false" onclick="save(&#34;default&#34;)" style="background:none; margin:0px; padding:0px;"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Save Pipeline"><i class="fa fa-save" style="font-size: 17px;"></i></a></button>

                <button type="button" id="newRevPipeline" class="btn" data-backdrop="false" onclick="save(&#34;rev&#34;)" style="background:none; margin:0px; padding:0px;"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Create Revision"><i class="glyphicon glyphicon-open-file" style="font-size: 16px; padding-top:3px;"></i></a></button>
                <?php
                if ($login == 1) {
                    echo '<button type="button" id="dupPipeline" class="btn" name="button" data-toggle="modal" data-backdrop="false" data-target="#confirmModal" style="background:none; margin:0px; padding:0px;"> <a data-toggle="tooltip" data-placement="bottom" data-original-title="Duplicate Pipeline"> <i class="fa fa-copy" style="font-size: 16px;"></i></a></button>
                  <button type="button" id="downPipeline" class="btn" name="button" data-backdrop="false" style="background:none;  margin:0px; padding:0px;">
                    <a data-toggle="tooltip" data-placement="bottom" data-original-title="Download Pipeline">
                        <i class="glyphicon glyphicon-save"></i></a></button>';
                }
                ?>

                <button type="button" id="importPipeline" class="btn" name="button" data-toggle="modal" data-target="#importModal" data-backdrop="false" style="background:none;  display:none; margin:0px; padding:0px;">
                    <a data-toggle="tooltip" data-placement="bottom" data-original-title="Import Pipeline"><i class="glyphicon glyphicon-import"></i></a></button>
                <button type="button" id="exportPipeline" class="btn" name="button" onclick="download(exportPipeline(),&quot;exportPipe&quot;)" data-backdrop="false" style="background:none;  margin:0px; padding:0px; display:none;">
                    <a data-toggle="tooltip" data-placement="bottom" data-original-title="Export Pipeline"><i class="glyphicon glyphicon-export"></i></a></button>
                <?php
                // if ($login == 1 ){
                // echo '<button type="button" id="savePDF" class="btn" name="button" data-backdrop="false" style="background:none;  margin:0px; padding:0px; padding-bottom:2px;">
                //     <a href="#" download data-toggle="tooltip" data-placement="bottom" data-original-title="Download Workflow as PDF" onclick="return downloadPdf()">
                //         <i class="fa fa-file-pdf-o"></i>
                //     </a>
                // </button>';
                // }
                ?>
                <button type="button" id="gitConsoleBtn" class="btn" name="button" data-backdrop="false" style="background:none; margin:0px; padding:0px; padding-bottom:2px;" data-toggle="modal" data-target="#gitConsoleModal">
                    <a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Git Console"><span><i class="fa fa-git"></i></span></a>
                </button>
                <button type="button" id="delPipeline" class="btn" name="button" data-toggle="modal" data-backdrop="false" data-target="#confirmModal" style="background:none; margin:0px; padding:0px;">
                    <a data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Pipeline">
                        <i class="glyphicon glyphicon-trash"></i></a>
                </button>
                <span id="autosaveDiv">
                    <span id="autosave" style="font-color:#C5C5C5; font-size:15px;"></span>
                </span>
                <div id="pipeMenuGroupTop" style="display:inline;">
                    <i id="pipeSepBar" style="color:grey; font-size:25px; padding-top:12px; margin-left:10px; margin-right:10px; ">|</i>
                    <i id="pipeGroupIcon" class="fa fa-th-list " style="margin-left:0px; margin-right:0px; font-size:16px;"></i> <label>Menu Group:</label>
                    <select id="pipeGroupAll" style="width:165px; font-style: italic; font-size:17px;" class="form-control" pipe_group_id=""></select>
                    <button type="button" class="btn btn-default btn-sm" style="background-color:white; font-size:11px; padding:8px; padding-left:9px; padding-right:8px; margin-bottom:3px;" id="pipeGroupAdd" data-toggle="modal" data-target="#pipeGroupModal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Add Pipeline Menu Group"><span><i class="glyphicon glyphicon-plus"></i></span></a></button>
                    <button type="button" class="btn btn-default btn-sm" style="background-color:white; font-size:11px; padding:8px; padding-left:9px; padding-right:8px; margin-bottom:3px;" id="pipeGroupEdit" data-toggle="modal" data-target="#pipeGroupModal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Edit Pipeline Menu Group"><span><i class="fa fa-pencil-square-o"></i></span></a></button>
                    <button type="button" class="btn btn-default btn-sm" style="background-color:white; font-size:11px; padding:8px; padding-left:10px; padding-right:10px; margin-bottom:3px;" id="pipeGroupDel" data-toggle="modal" data-target="#pipeDelGroupModal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Delete Pipeline Menu Group"><span><i class="fa fa-trash-o"></i></span></a></button>
                </div>
            </div>
            <?php
            if ($login == 1) {
                echo '<div id="pipeRunDiv" style="float:right; margin-right:5px;" class="btn-group">
                <button class="btn btn-success" type="button" id="pipeRun" data-toggle="modal" href="#mRun" style="vertical-align:middle;">Run</button>
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li><a href="#mRun" data-toggle="modal">Create New Run</a></li>
                    <li><a href="#mExistRun" data-toggle="modal">Existing Runs</a></li>
                </ul>
            </div>';
            }
            ?>
            <div id="pipeRevSpan" style="margin-right:5px; width:115px; float:right;">
                <select id="pipeRev" class="fbtn btn-default form-control mPipeChange" prev="-1" name="pipeline_rev_id"></select>
            </div>
        </div>
    </div>
</div>

<div class="col-sm-12" style="padding-left:16px; padding-right:16px; padding-bottom:20px; " id="desPipeline">
    <div class="row" id="creatorInfoPip" style="font-size:12px; display:none;"> Created by <span id="ownUserNamePip">admin</span> on <span id="datecreatedPip">Jan. 26, 2016 04:12</span> • Last edited on <span class="lasteditedPip">Feb. 8, 2017 12:15</span>
    </div>
</div>

<div id="pipeTabSection">
    <ul id="pipeTabDiv" class="nav nav-tabs">
        <li class="active"><a class="nav-item" data-toggle="tab" href="#descriptionTab">Description</a></li>
        <li><a class="nav-item" data-toggle="tab" href="#workflowTab">Workflow</a></li>
        <li><a class="nav-item" data-toggle="tab" href="#advancedTab">Advanced</a></li>
    </ul>
    <div class="tab-content">
        <div id="descriptionTab" class="tab-pane fade in active">
            <div class="col-md-12" id="pipGitTitleDiv" style="display:none;">
                <h5><b>GitHub </b> <span id="pipGitTitle"></span></h5>
            </div>
            <div class="col-md-12" id="desTitlePip">
                <h5>
                    <b>Summary</b>
                    <a><i id="editPipeSum" class="fa fa-pencil"></i> </a>
                    <a><i style="display:none;" id="confirmPipeSum" class="fa fa-check"></i> </a>
                </h5>
            </div>

            <div class="col-md-12" style="margin-bottom:20px;">
                <div id="pipelineSum"></div>
                <div id="pipelineSumEditordiv" style="display:none;" class="col-sm-12">
                    <div id="pipelineSumEditor" style="height:400px;"></div>
                    <div id="pipelineSumEditor_dragbar" class="app_editor_dragbar"></div>
                    <div class="row" style="display:none;">
                        <div class="col-sm-3" style="padding-left:0;">
                            <select id="pipelineSumEditor_mode" class="form-control">
                                <option value="markdown">markdown</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div id="workDetails">
                    <h5 style="padding-bottom:5px;"> <b>Pipeline Details</b></h5>
                    <ul id="inOutNav" class="nav nav-tabs nav-justified">
                        <li class="active"><a class="nav-item" data-toggle="tab" href="#processTab">Processes</a></li>
                        <li style="display:none;"><a class="nav-item" data-toggle="tab" href="#inputsTab">Inputs</a></li>
                        <li style="display:none;"><a class="nav-item" data-toggle="tab" href="#outputsTab">Outputs</a></li>
                    </ul>
                    <div class="panel panel-default">
                        <div id="pipeContent" class="tab-content">
                            <div id="processTab" class="tab-pane fade in active">
                                </br>
                                <table id="processTable" class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Process Name</th>
                                            <th scope="col">Revision</th>
                                            <th scope="col">Description</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div id="inputsTab" class="tab-pane fade ">
                                </br>
                                <table id="inputsTable" class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Given Name</th>
                                            <th scope="col">Identifier</th>
                                            <th scope="col">File Type</th>
                                            <th scope="col">Qualifier</th>
                                            <th scope="col">Process Name</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>



                            <div id="outputsTab" class="tab-pane fade">
                                </br>
                                <table id="outputsTable" class="table">
                                    <thead>
                                        <tr>
                                            <th scope="col">Given Name</th>
                                            <th scope="col">Identifier</th>
                                            <th scope="col">File Type</th>
                                            <th scope="col">Qualifier</th>
                                            <th scope="col">Process Name</th>
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
        <!--       end of descriptionTab-->
        <div id="workflowTab" class="tab-pane fade">
            <div class="col-md-12" style="margin-top:5px;">
                <div class="panel panel-default" style="margin-bottom:10px;  padding-bottom:0px;">
                    <div style="height:500px; " id="container" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
                </div>
                <div id="warnSection" class="col-md-12" style=" display:none;">
                    <p style=" float:left;  font-size:17px; padding-left:0px; color:#ff4444;">Warning: </p>
                    <div class="col-md-11">
                        <p style="float:left; font-size:16px;" id="warnArea"></p>
                    </div>
                    </br>
                    </br>
                </div>
                </br>
            </div>
            <div id="subPipelinePanelTitle" class="col-md-12" style="display:none;">
                <h6><b>Pipeline Modules</b></h6>
            </div>
        </div>
        <!--       end of workflowTab-->

        <div id="advancedTab" class="tab-pane fade">
            <div id="advOpt" style="margin-top:15px;">
                <div class="col-md-12" style="float:none; margin-bottom:30px;">
                    <label class="col-sm-12 control-label">Pipeline Files</label>
                </div>
                <div id="pipelineFiles" style="margin-bottom:15px;"></div>
                <div class="col-md-12" style="margin-bottom:15px;">
                    <div class="form-group">
                        <label style="width:150px;" class="col-sm-2 control-label">Pipeline Header Script</label>
                        <div id="editorPipeHeaderdiv" class="col-sm-10">
                            <div id="editorPipeHeader" style="height:200px;"></div>
                            <div id="editorPipeHeader_dragbar" class="app_editor_dragbar"></div>
                            <div class="row">
                                <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                                <div class="col-sm-3" style="padding-left:0;">
                                    <select id="script_mode_pipe_header" name="script_mode_header" class="form-control">
                                        <option value="groovy">groovy</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12" style="padding-top:10px; margin-bottom:25px;">
                    <div class="form-group">
                        <label style="width:150px;" class="col-sm-2 control-label">Pipeline Footer Script</label>
                        <div id="editorPipeFooterdiv" class="col-sm-10">
                            <div id="editorPipeFooter" style="height:200px;"></div>
                            <div id="editorPipeFooter_dragbar" class="app_editor_dragbar"></div>
                            <div class="row">
                                <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                                <div class="col-sm-3" style="padding-left:0;">
                                    <select id="script_mode_pipe_footer" name="script_mode_footer" class="form-control">
                                        <option value="groovy">groovy</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="pipeMenuGroupBottom" class="col-md-12" style="display:none; margin-top:10px; margin-bottom:20px;">
                </div>

                <div id="permsPipeDiv" class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Permission to View</label>
                        <select id="permsPipe" class="fbtn btn-default form-control permscheck" name="perms">
                            <option value="3" selected="">Only me </option>
                            <option value="15">Only my group</option>
                            <option value="63">Everyone </option>
                        </select>
                    </div>
                </div>
                <div id="groupSelPipeDiv" class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Group Permission to View</label>
                        <select id="groupSelPipe" class="fbtn btn-default form-control permscheck" name="group_id">
                            <option value="" selected>Choose group </option>
                        </select>
                    </div>
                </div>
                <div id="writeGroupPipeDiv" class="col-md-4">
                    <div class="form-group">
                        <label class="control-label">Group Permission to Write</label>
                        <div style="display:block;">
                            <select id="writeGroupPipe" class="fbtn btn-default form-control" style="margin-top:10px;" multiple="multiple" name="write_group_id">
                            </select>
                        </div>
                    </div>
                </div>


                <div id="releaseDiv" style="margin-top:5px;" class="col-md-12">
                    <div class="form-group">
                        <label id="releaseLabel" class="control-label">Release Date:</label>
                        <a id="setRelease" href="#"><span date="" id="releaseVal">Set a Date</span></a>
                        <span id="releaseValFinal"></span>
                        <span>
                            <a id="getTokenLink" style="display:none;" token="" data-toggle="tooltip" data-placement="bottom" data-html="true" title="Get Link <br> Only people who have the link can access the pipeline until release date"><i style="font-size: 15px;" class="fa fa-chain"></i></a>
                            <div id="showTokenLink" style="display:none;" class="col-md-6 input-group">
                                <input readonly id="tokenInput" type="text" class="form-control">
                                <div id="copyToken" class="input-group-addon">
                                    <a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Copy Link to Clipboard"><span><i class="glyphicon glyphicon-copy"></i></span></a>
                                </div>
                            </div>
                        </span>
                    </div>
                </div>

                <div id="pinMainPage" style="display:none; margin-bottom:20px;" class="col-md-12">
                    <label class="form-group" style="margin-bottom:5px;">Admin Settings </label>
                    <div class="form-group">
                        <span>Publicly Searchable </span>
                        <input id="publicly_searchable" type="checkbox">
                        <span style="margin-left:10px;">Pin to Main Page </span>
                        <input id="pin" type="checkbox">
                        <span style="margin-left:10px;"> Order </span>
                        <input maxlength="4" size="4" id="pin_order">
                    </div>
                </div>

                <div name="empty_space" style="height:100px; width:100%; clear:both;"></div>
            </div>
        </div>
    </div>
</div>
<!--       end of advancedTab-->