<style>
    .nodisp {
        display: block
    }

</style>

<div class="box-header" style=" padding-left:1px; padding-right:1px; padding-top:0px; font-size:large; ">
    <div style=" border-bottom:1px solid lightgrey;">

        <div class="row" style="margin-left:0px; margin-right:0px;">
            <div id="pipeGroupFloatLeft" style="float:left;">
                <i class="fa fa-spinner " style="margin-left:0px; margin-right:0px;"></i> Pipeline:
                <input class="box-dynamic width-dynamic" type="text" pipelineid="<?php echo $id;?>" name="pipelineTitle" autocomplete="off" placeholder="Enter Pipeline Name" style="margin-left:0px; font-size: large; font-style:italic; align-self:center; max-width: 500px;" title="Rename" data-placement="bottom" data-toggle="tooltip" num="" id="pipeline-title"><span class="width-dynamic" style="display:none"></span></input>
                <button type="submit" id="savePipeline" class="btn" name="button" data-backdrop="false" onclick="save()" style=" margin:0px; padding:0px;">
                    <a data-toggle="tooltip" data-placement="bottom" data-original-title="Save Pipeline">
                        <i class="fa fa-save" style="font-size: 17px;"></i></a></button>
                <button type="submit" id="dupPipeline" class="btn" name="button" data-backdrop="false" onclick="duplicatePipeline()" style=" margin:0px; padding:0px;">
                    <a data-toggle="tooltip" data-placement="bottom" data-original-title="Copy Pipeline">
                        <i class="fa fa-copy" style="font-size: 16px;"></i></a></button>
                <button type="submit" id="createRevPipeIcon" class="btn" name="button" data-backdrop="false" onclick="createRevPipeline()" style=" margin:0px; padding:0px; display:none;">
                    <a data-toggle="tooltip" data-placement="bottom" data-original-title="Create Revision">
                        <i class="fa fa-chain" style="font-size: 16px;"></i></a></button>

                <button type="button" id="downPipeline" class="btn" name="button" onclick="download(createNextflowFile(&quot;pipeline&quot;))" data-backdrop="false" style=" margin:0px; padding:0px;">
                    <a data-toggle="tooltip" data-placement="bottom"  data-original-title="Download Pipeline">
                        <i class="glyphicon glyphicon-save"></i></a></button>
                <button type="button" id="savePDF" class="btn" name="button" data-backdrop="false" style=" margin:0px; padding:0px; padding-bottom:2px;">
                    <a href="#" download data-toggle="tooltip" data-placement="bottom" data-original-title="Download Workflow as PDF" onclick="return downloadPdf()">
                        <i class="fa fa-file-pdf-o"></i></a></button>
                <button type="button" id="delPipeline" class="btn" name="button" data-toggle="modal" data-backdrop="false" data-target="#confirmModal" style=" margin:0px; padding:0px;">
                    <a data-toggle="tooltip" data-placement="bottom" data-original-title="Delete Pipeline">
                        <i class="glyphicon glyphicon-trash"></i></a>
        		</button>
                <span id="autosaveDiv">
            		<span id="autosave" style="font-color:#C5C5C5; font-size:15px;"></span>
                </span>
                <div id="pipeMenuGroupTop" style="display:inline;">
                    <i id="pipeSepBar" style="color:grey; font-size:25px; padding-top:12px; margin-left:10px; margin-right:10px; ">|</i>
                    <i id="pipeGroupIcon" class="fa fa-th-list " style="margin-left:0px; margin-right:0px; font-size:16px;"></i> Menu Group:
                    <select id="pipeGroupAll" style="width:165px; font-style: italic; font-size:17px; margin-top:0px; padding-top:0px;" class="fbtn btn-default" name="process_group_id"></select>
                    <button type="button" class="btn btn-default btn-sm" style="font-size:11px; padding:4px; padding-left:7px; padding-right:7px; margin-bottom:3px;" id="pipeGroupAdd" data-toggle="modal" data-target="#pipeGroupModal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Add Pipeline Menu Group"><span><i class="glyphicon glyphicon-plus"></i></span></a></button>
                    <button type="button" class="btn btn-default btn-sm" style="font-size:11px; padding:4px; padding-left:7px; padding-right:7px; margin-bottom:3px;" id="pipeGroupEdit" data-toggle="modal" data-target="#pipeGroupModal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Edit Pipeline Menu Group"><span><i class="fa fa-pencil-square-o"></i></span></a></button>
                    <button type="button" class="btn btn-default btn-sm" style="font-size:11px; padding:4px; padding-left:8px; padding-right:8px; margin-bottom:3px;" id="pipeGroupDel" data-toggle="modal" data-target="#pipeDelGroupModal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Delete Pipeline Menu Group"><span><i class="fa fa-trash-o"></i></span></a></button>
                </div>
            </div>


            <div id="pipeActionsDiv" style="float:right; margin-right:5px;" class="dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" id="pipeActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="vertical-align:middle;"><div class="fa fa-ellipsis-h"></div></button>
                <ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="dropdownMenu2">
                    <li><a id="deletePipeRevision" data-toggle="modal" href="#confirmModal">Delete Revision</a></li>
                    <li><a id="duplicaPipeline" onclick="duplicatePipeline()">Copy Pipeline</a></li>
                    <li><a id="createRevPipe" style="display:none;" onclick="createRevPipeline()">Create Revision</a></li>
                </ul>
            </div>
            <div id="pipeRunDiv" style="float:right; margin-right:5px;" class="btn-group">
                <button class="btn btn-success" type="button" id="pipeRun" data-toggle="modal" href="#mRun" style="vertical-align:middle;">Run</button>
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                <ul class="dropdown-menu" role="menu">
                    <li><a href="#mExistRun" data-toggle="modal">Existing Runs</a></li>
                </ul>
            </div>
            <div id="pipeRevSpan" style="margin-right:5px; width:110px; float:right;">
                <select id="pipeRev" class="fbtn btn-default form-control mPipeChange" prev="-1" name="pipeline_rev_id"></select>
            </div>
        </div>
    </div>
</div>

<div style="padding-left:16px; padding-right:16px; padding-bottom:20px; " id="desPipeline">
    <div class="row" id="creatorInfoPip" style="font-size:12px; display:none;"> Created by <span id="ownUserNamePip">admin</span> on <span id="datecreatedPip">Jan. 26, 2016     04:12</span> • Last edited on <span class="lasteditedPip">Feb. 8, 2017 12:15</span>
    </div>
    </br>
    <div class="row" id="desTitlePip">
        <h6><b>Description</b></h6>
    </div>
    <div class="row"><textarea id="pipelineSum" placeholder="Enter pipeline description here.." rows="3" style="min-width: 100%; max-width: 100%; border-color:lightgrey;"></textarea></div>


</div>



<div class="panel panel-default" style="margin-bottom:10px; padding-bottom:0px;">
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


<div id="workDetails">
    <h4>Pipeline Details</h4>
    </br>
    <ul id="inOutNav" class="nav nav-tabs nav-justified">
        <li class="active"><a class="nav-item" data-toggle="tab" href="#processTab">Processes</a></li>
        <li><a class="nav-item" data-toggle="tab" href="#inputsTab">Inputs</a></li>
        <li><a class="nav-item" data-toggle="tab" href="#outputsTab">Outputs</a></li>
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

<div id="subPipelinePanelTitle" style="display:none;">
    <h6><b>Pipeline Modules</b></h6>
</div>



<div id="advOptDiv" class="row">
    <div class="col-md-12">
        <div class="form-group" style="margin-bottom:5px;">
            <label>Advanced Options</label>
            <i data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a class="fa fa-plus-square-o collapseIcon" style=" font-size:15px; padding-left:5px;" data-toggle="collapse" data-target="#advOpt"></a></i>
        </div>
    </div>
</div>

<!-- collapsed settings-->
<div id="advOpt" class="collapse">
    <div class="col-md-12" style="margin-bottom:15px;">
        <div class="form-group">
            <label style="width:150px;" class="col-sm-2 control-label">Pipeline Header Script</label>
            <div id="editorPipeHeaderdiv" class="col-sm-10">
                <div id="editorPipeHeader" style="height:200px;"></div>
                <div class="row">
                    <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                    <div class="col-sm-3" style="padding-left:0;">
                        <select id="script_mode_pipe_header" name="script_mode_header" class="form-control">
                        <option value="groovy" >groovy</option>
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
                <div class="row">
                    <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                    <div class="col-sm-3" style="padding-left:0;">
                        <select id="script_mode_pipe_footer" name="script_mode_footer" class="form-control">
                        <option value="groovy" >groovy</option>
                    </select>
                    </div>
                </div>
            </div>
        </div>
    </div>




    <div id="permsPipeDiv" class="col-md-4">
        <div class="form-group">
            <label class="col-sm-12 control-label">Permissions to View</label>
            <select id="permsPipe" class="fbtn btn-default form-control" name="perms">
                              <option value="3" selected="">Only me </option>
                              <option value="15">Only my group</option>
                              <option disabled value="63">Everyone </option>
                        </select>
        </div>
    </div>
    <div id="groupSelPipeDiv" class="col-md-4">
        <div class="form-group">
            <label class="col-sm-12 control-label">Group Selection</label>
            <select id="groupSelPipe" class="fbtn btn-default form-control" name="group_id">
                          <option value="" selected>Choose group </option>
                        </select>
        </div>
    </div>
    <div id="publishPipeDiv" class="col-sm-4">
        <div class="form-group">
            <label class="col-sm-12 control-label">Publish</label>
            <select id="publishPipe" class="fbtn btn-default form-control" name="publish">
                                      <option value="0">No</option>
                                      <option value="1">Yes</option>
                                    </select>
        </div>
    </div>
    <div id="pipeMenuGroupBottom" class="col-md-12" style="display:none; margin-top:10px; margin-bottom:20px;">
    </div>

    <div id="pinMainPage" style="display:none;" class="col-md-6">
        <div class="form-group">
            <label>Pin to Main Page </label>
            <input id="pin" type="checkbox">
            <label> Order </label>
            <input id="pin_order">
        </div>
    </div>
</div>


<!-- selectPipelineModal -->
<div id="selectPipelineModal" style="overflow-y:scroll;" class="modal fade " tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <span></span>
            <div id="revModalHeaderPipe" class="modal-header">
                <button style="padding-top:6px;" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <span id="mPipeRevSpan" style="margin-right:5px; width:130px; float:right;">
                <select id="mPipeRev" class="fbtn btn-default form-control mPipeRevChange" prev="-1" name="pipeline_rev_id"></select>
                    </span>
                <h4 class="modal-title">Select Pipeline</h4>
                <div class="col-sm-12" id="creatorInfoPipe" style="display:none; font-size:12px; padding-left:0px; margin-left:0px;"> Created by
                    <span id="ownUserNamePipe">admin</span> on
                    <span id="datecreatedPipe">Jan. 26, 2016     04:12</span> • Last edited on
                    <span id="lasteditedPipe">Feb. 8, 2017 12:15</span>
                </div>
            </div>
            <div class="modal-body">
                <div class="panel-body">
                    <table id="selectPipeTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>Process Name</th>
                                <th>Rev Id</th>
                                <th>Rev Comment</th>
                                <th>Modified on</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="selectPipeline">Select Revision</button>
            </div>
        </div>
    </div>
</div>
<!-- selectPipelineModal Ends-->


<!-- Add Process Modal -->
<div id="addProcessModal" style="overflow-y:scroll;" class="modal fade " tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" style="width:1300px;" role="document">
        <div class="modal-content">
            <span id="addHeader"></span>
            <div id="revModalHeader" class="modal-header">
                <button style="padding-top:6px;" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div id="mProActionsDiv" style="float:right; margin-right:15px; display:none;" class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" id="mProActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="vertical-align:middle;"><span class="fa fa-ellipsis-h"></span></button>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
                        <li><a id="deleteRevision" data-toggle="modal" href="#confirmModal">Delete Revision</a></li>
                        <li><a id="createRevision" style="display:none;" onclick="createRevision()">Create Revision</a></li>
                        <li><a id="duplicaProRev" onclick="duplicateProcessRev()">Copy Process</a></li>

                    </ul>
                </div>
                <span id="mProRevSpan" style="margin-right:5px; width:130px; float:right; display:none;">
                <select id="mProRev" class="fbtn btn-default form-control mRevChange" prev="-1" name="process_rev_id"></select>
                    </span>
                <h4 class="modal-title" id="processmodaltitle">Title</h4>
                <div class="col-sm-12" id="creatorInfoPro" style="display:none; font-size:12px; padding-left:0px; margin-left:0px;"> Created by
                    <span id="ownUserNamePro">admin</span> on
                    <span id="datecreatedPro">Jan. 26, 2016     04:12</span> • Last edited on
                    <span id="lasteditedPro">Feb. 8, 2017 12:15</span>
                </div>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mIdPro" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mIdPro" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mName" style="width:150px;" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mName" name="name">
                        </div>
                    </div>
                    <div id="versionGroup" class="form-group" style="display:none">
                        <label for="mVersion" style="width:150px;" class="col-sm-2 control-label">Version</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mVersion" name="version">
                        </div>
                    </div>
                    <div id="describeGroup" class="form-group">
                        <label for="mDescription" style="width:150px;" class="col-sm-2 control-label">Description</label>
                        <div class="col-sm-10">
                            <textarea rows="3" class="form-control" id="mDescription" name="summary"></textarea>
                        </div>
                    </div>
                    <div id="proGroup" class="form-group">
                        <label for="mProcessGroup" style="width:150px;" class="col-sm-2 control-label">Menu Group</label>
                        <div style="width:270px;" class="col-sm-3">
                            <select id="mProcessGroup" class="fbtn btn-default form-control" name="process_group_id"></select>
                        </div>
                        <div id="mProcessGroupAdd" class="col-sm-1" style=" width: auto; padding-left: 0; padding-right: 0;">
                            <button type="button" class="btn btn-default form-control" id="groupAdd" data-toggle="modal" data-target="#processGroupModal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Add Menu Group"><span><i class="glyphicon glyphicon-plus"></i></span></a></button>
                        </div>
                        <div id="mProcessGroupEdit" class="col-sm-1" style=" width: auto; padding-left: 0; padding-right: 0;">
                            <button type="button" class="btn btn-default form-control" id="groupEdit" data-toggle="modal" data-target="#processGroupModal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Edit Menu Group"><span><i class="fa fa-pencil-square-o"></i></span></a></button>
                        </div>
                        <div id="mProcessGroupDel" class="col-sm-1" style=" width: auto; padding-left: 0; padding-right: 0;">
                            <button type="button" class="btn btn-default form-control" id="groupDel" data-toggle="modal" data-target="#delprocessGrmodal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Delete Menu Group"><span><i class="fa fa-trash-o"></i></span></a></button>
                        </div>
                    </div>
                    <div id="mParameters" class="form-group" style=" padding-top:15px; border-top:0.094em solid lightgrey;">
                        <label for="mParamAll" style="width:150px;" class="col-sm-2 control-label">Parameters</label>
                        <div id="mParamAll" class="col-sm-5">
                            <select id="mParamAllIn" class="fbtn btn-default form-control mParChange" name="ParamAll" style="display:none;"></select>
                        </div>
                        <div id="mParamsAdd" class="col-sm-1" style=" width:auto; padding-right:0;">
                            <button type="button" class="btn btn-default form-control" id="mParamAdd" data-toggle="modal" data-target="#parametermodal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Add Parameter"><span><i class="glyphicon glyphicon-plus"></i></span></a></button>
                        </div>
                        <div id="mParamsEdit" class="col-sm-1" style=" width: auto; padding-left: 0; padding-right: 0;">
                            <button type="button" class="btn btn-default form-control" id="mParamEdit" data-toggle="modal" data-target="#parametermodal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Edit Parameter"><span><i class="fa fa-pencil-square-o"></i></span></a></button>
                        </div>
                        <div id="mParamsDel" class="col-sm-1" style=" width: auto; padding-left: 0; padding-right: 0;">
                            <button type="button" class="btn btn-default form-control" id="mParDel" data-toggle="modal" data-target="#delparametermodal" data-backdrop="false"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Delete Parameter"><span><i class="fa fa-trash-o"></i></span></a></button>
                        </div>
                    </div>
                    <div id="inputTitle" class="form-group" style="   margin-bottom:15px; padding-top:15px;">
                        <p style="width:150px;" class="col-sm-2 control-label"></p>
                        <div id="mInputsT" class="col-sm-3" style="width:270px; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:50px;">Input Parameters <span><a data-toggle="tooltip" data-placement="bottom" title="Input parameters that are defined in parameters section"><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div id="mInNameT" class="col-sm-2" style="width:200px; padding-left:0; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:50px;">Input Name <span><a data-toggle="tooltip" data-placement="bottom" title="Name to define input groups according to their qualifier"><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div id="mInNamedelT" class="col-sm-1" style="width:40px; padding-left:0;padding-right:0; padding-bottom:22px; border-bottom:1px solid lightgrey;">
                            <p> </p>
                        </div>
                        <div id="mInOptButT" class="col-sm-1" style="width:45px; padding-left:5px; padding-right:0; padding-bottom:22px; border-bottom:1px solid lightgrey;">
                            <p> </p>
                        </div>
                        <div id="mInOptT" class="col-sm-3" style="width:100px; padding-left:5px; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:10px;">Operators <span><a data-toggle="tooltip" data-placement="bottom"  title="Optional operator section to transform values emitted by a channel."><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div id="mInClosureT" class="col-sm-2 " style="width:160px; padding-left:5px; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:20px;">Operator Content <span><a data-toggle="tooltip" data-placement="bottom" title="Optional operator content to specify how operators will act. Multiple operators can be added by starting paranthesis. i.e. (size:6).buffer(size:3)"><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                    </div>
                    <div id="inputGroup" class="form-group">
                        <label for="mInputs-1" style="width:150px;" class="col-sm-2 control-label">Inputs</label>
                        <div id="mInputs" class="col-sm-3" style="width:270px;">
                            <select id="mInputs-1" num="1" class="fbtn btn-default form-control mParChange" prev="-1" name="mInputs-1"></select>
                        </div>
                        <div id="mInName" class="col-sm-2 " style="width:200px; padding-left:0; padding-right:0;">
                            <input type="text" style="display:none; " placeholder="Enter name" class="form-control" ppID="" id="mInName-0" name="mInName-0">
                        </div>
                        <div id="mInNamedel" class="col-sm-1" style="width:40px; padding-left:0;padding-right:0;">
                            <button type="submit" style="display:none;" class="btn btn-default form-control" id="mInNamedel-0" name="mInNamedel-0"><i class="glyphicon glyphicon-remove"></i></button>
                        </div>
                        <div id="mInOptBut" class="col-sm-1" style="width:45px; padding-left:5px; padding-right:0;">
                            <button type="submit" style="display:none;" class="btn btn-default form-control" id="mInOptBut-0" name="mInOptBut-0"><i class="fa fa-wrench"></i></button>
                        </div>
                        <div id="mInOpt" class="col-sm-2" style="width:100px; padding-left:5px; padding-right:0;">
                            <select id="mInOpt-0" name="mInOpt-0" style=" display:none;" class="form-control">
                                   <option value="Operators" disabled>Operators</option>
                                    <option value="buffer">buffer</option>
                                    <option value="choice">choice</option>
                                    <option value="close">close</option>
                                    <option value="collate">collate</option>
                                    <option value="collect">collect</option>
                                    <option value="collectFile">collectFile</option>
                                    <option value="combine">combine</option>
                                    <option value="concat">concat</option>
                                    <option value="count">count</option>
                                    <option value="countBy">countBy</option>
                                    <option value="cross">cross</option>
                                    <option value="distinct">distinct</option>
                                    <option value="dump">dump</option>
				                    <option value="filter">filter</option>
                                    <option value="first">first</option>
                                    <option value="flatMap">flatMap</option>
                                    <option value="flatten">flatten</option>
                                    <option value="groupBy">groupBy</option>
                                    <option value="groupTuple">groupTuple</option>
                                    <option value="ifEmpty">ifEmpty</option>
                                    <option value="into">into</option>
                                    <option value="join">join</option>
                                    <option value="last">last</option>
                                    <option value="map">map</option>
                                    <option value="max">max</option>
                                    <option value="merge">merge</option>
                                    <option value="min">min</option>
                                    <option value="mix">mix</option>
                                    <option value="mode flatten">mode flatten</option>
                                    <option value="phase">phase</option>
                                    <option value="print">print</option>
                                    <option value="println">println</option>
                                    <option value="randomSample">randomSample</option>
                                    <option value="reduce">reduce</option>
                                    <option value="route">route</option>
                                    <option value="separate">separate</option>
                                    <option value="set">set</option>
                                    <option value="splitCsv">splitCsv</option>
                                    <option value="splitFasta">splitFasta</option>
                                    <option value="splitFastq">splitFastq</option>
                                    <option value="splitText">splitText</option>
                                    <option value="spread">spread</option>
                                    <option value="sum">sum</option>
                                    <option value="take">take</option>
                                    <option value="tap">tap</option>
                                    <option value="toInteger">toInteger</option>
                                    <option value="toList">toList</option>
                                    <option value="toSortedList">toSortedList</option>
                                    <option value="transpose">transpose</option>
                                    <option value="unique">unique</option>
                                    <option value="view">view</option>
                                    </select>
                        </div>
                        <div id="mInClosure" class="col-sm-2 " style="width:140px; padding-left:5px; padding-right:0;">
                            <input type="text" style="display:none; " placeholder="Operator content" class="form-control" ppID="" id="mInClosure-0" name="mInClosure-0">
                        </div>
                        <div id="mInOptdel" class="col-sm-1" style="width:40px; padding-left:0;padding-right:0;">
                            <button type="submit" style="display:none;" class="btn btn-default form-control" id="mInOptdel-0" name="mInOptdel-0"><i class="glyphicon glyphicon-remove"></i></button>
                        </div>
                    </div>
                    <div id="outputTitle" class="form-group" style="  margin-bottom:15px; padding-top:15px;">
                        <p style="width:150px;" class="col-sm-2 control-label"></p>
                        <div id="mOutputsT" class="col-sm-3" style="width:270px; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:50px;">Output Parameters <span><a data-toggle="tooltip" data-placement="bottom" title="Output parameters that are defined in parameters section"><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div id="mOutNameT" class="col-sm-2" style="width:200px; padding-left:0; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:50px;">Output Name <span><a data-toggle="tooltip" data-placement="bottom" title="Name to define output groups according to their qualifier"><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div id="mOutNamedelT" class="col-sm-1" style="width:40px; padding-left:0;padding-right:0; padding-bottom:22px; border-bottom:1px solid lightgrey;">
                            <p> </p>
                        </div>
                        <div id="mOutOptButT" class="col-sm-1" style="width:45px; padding-left:5px; padding-right:0; padding-bottom:22px; border-bottom:1px solid lightgrey;">
                            <p> </p>
                        </div>
                        <div id="mOutOptT" class="col-sm-3" style="width:100px; padding-left:5px; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:10px;">Operators <span><a data-toggle="tooltip" data-placement="bottom" title="Optional operator section to transform values emitted by a channel."><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div id="mOutClosureT" class="col-sm-2 " style="width:160px; padding-left:5px; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:20px;">Operator Content <span><a data-toggle="tooltip" data-placement="bottom" title="Optional operator content to specify how operators will work. Multiple operators can be added by starting paranthesis. i.e. (size:6).buffer(size:3)"><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div id="mOutOptdelT" class="col-sm-1" style="width:45px; padding-left:0;padding-right:5px; padding-bottom:22px; border-bottom:1px solid lightgrey;">
                            <p> </p>
                        </div>
                        <div id="mOutRegT" class="col-sm-2 " style="width:180px; padding-left:0px; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:30px;">Regular Expression <span><a data-toggle="tooltip" data-placement="bottom" title="Optional regular expresion to filter output files, which are going to be transferred to output directory. (Default: output name pattern is used)"><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                    </div>
                    <div id="outputGroup" class="form-group">
                        <label for="mOutput-1" style="width:150px;" class="col-sm-2 control-label">Outputs</label>
                        <div id="mOutputs" class="col-sm-3" style="width:270px;">
                            <select id="mOutputs-1" num="1" class="fbtn btn-default form-control mParChange" prev="-1" name="mOutputs-1"></select>
                        </div>
                        <div id="mOutName" class="col-sm-2" style="width:200px; padding-left:0; padding-right:0;">
                            <input type="text" style="display:none;" placeholder="Enter name" class="form-control" id="mOutName-0" name="mOutName-0">
                        </div>
                        <div id="mOutNamedel" class="col-sm-1" style="width:40px; padding-left:0;padding-right:0;">
                            <button type="submit" style="display:none;" class="btn btn-default form-control" id="mOutNamedel-0" name="mOutNamedel-0"><i class="glyphicon glyphicon-remove"></i></button>
                        </div>
                        <div id="mOutOptBut" class="col-sm-1" style="width:45px; padding-left:5px; padding-right:0;">
                            <button type="submit" style="display:none;" class="btn btn-default form-control" id="mOutOptBut-0" name="mOutOptBut-0"><i class="fa fa-wrench"></i></button>
                        </div>
                        <div id="mOutOpt" class="col-sm-3" style="width:100px; padding-left:5px; padding-right:0;">
                            <select id="mOutOpt-0" name="mOutOpt-0" style="display:none;" class="form-control">
                                   <option value="Operators" disabled>Operators</option>
                                    <option value="buffer">buffer</option>
                                    <option value="choice">choice</option>
                                    <option value="close">close</option>
                                    <option value="collate">collate</option>
                                    <option value="collect">collect</option>
                                    <option value="collectFile">collectFile</option>
                                    <option value="combine">combine</option>
                                    <option value="concat">concat</option>
                                    <option value="count">count</option>
                                    <option value="countBy">countBy</option>
                                    <option value="cross">cross</option>
                                    <option value="distinct">distinct</option>
                                    <option value="dump">dump</option>
				                    <option value="filter">filter</option>
                                    <option value="first">first</option>
                                    <option value="flatMap">flatMap</option>
                                    <option value="flatten">flatten</option>
                                    <option value="groupBy">groupBy</option>
                                    <option value="groupTuple">groupTuple</option>
                                    <option value="ifEmpty">ifEmpty</option>
                                    <option value="into">into</option>
                                    <option value="join">join</option>
                                    <option value="last">last</option>
                                    <option value="map">map</option>
                                    <option value="max">max</option>
                                    <option value="merge">merge</option>
                                    <option value="min">min</option>
                                    <option value="mix">mix</option>
                                    <option value="mode flatten">mode flatten</option>
                                    <option value="phase">phase</option>
                                    <option value="print">print</option>
                                    <option value="println">println</option>
                                    <option value="randomSample">randomSample</option>
                                    <option value="reduce">reduce</option>
                                    <option value="route">route</option>
                                    <option value="separate">separate</option>
                                    <option value="set">set</option>
                                    <option value="splitCsv">splitCsv</option>
                                    <option value="splitFasta">splitFasta</option>
                                    <option value="splitFastq">splitFastq</option>
                                    <option value="splitText">splitText</option>
                                    <option value="spread">spread</option>
                                    <option value="sum">sum</option>
                                    <option value="take">take</option>
                                    <option value="tap">tap</option>
                                    <option value="toInteger">toInteger</option>
                                    <option value="toList">toList</option>
                                    <option value="toSortedList">toSortedList</option>
                                    <option value="transpose">transpose</option>
                                    <option value="unique">unique</option>
                                    <option value="view">view</option>
                                    </select>
                        </div>
                        <div id="mOutClosure" class="col-sm-2 " style="width:140px; padding-left:5px; padding-right:0;">
                            <input type="text" style="display:none; " placeholder="Operator content" class="form-control" ppID="" id="mOutClosure-0" name="mOutClosure-0">
                        </div>
                        <div id="mOutOptdel" class="col-sm-1" style="width:45px; padding-left:0;padding-right:5px;">
                            <button type="submit" style="display:none;" class="btn btn-default form-control" id="mOutOptdel-0" name="mOutOptdel-0"><i class="glyphicon glyphicon-remove"></i></button>
                        </div>
                        <div id="mOutRegBut" class="col-sm-1" style="width:45px; padding-left:0px; padding-right:0;">
                            <button type="submit" style="display:none;" class="btn btn-default form-control" id="mOutRegBut-0" name="mOutRegBut-0"><i class="fa fa-code"></i></button>
                        </div>
                        <div id="mOutReg" class="col-sm-2 " style="width:140px; padding-left:0px; padding-right:0;">
                            <input type="text" style="display:none; " placeholder="Operator content" class="form-control" ppID="" id="mOutReg-0" name="mOutReg-0">
                        </div>
                        <div id="mOutRegdel" class="col-sm-1" style="width:40px; padding-left:0; padding-right:0;">
                            <button type="submit" style="display:none;" class="btn btn-default form-control" id="mOutRegdel-0" name="mOutRegdel-0"><i class="glyphicon glyphicon-remove"></i></button>
                        </div>
                    </div>
                    <div class="form-group" style=" padding-top:15px; border-top:0.094em solid lightgrey;">
                        <label for="mScript" style="width:150px;" class="col-sm-2 control-label">Script</label>
                        <div id="editordiv" class="col-sm-10">
                            <div id="editor" style="height: 300px;"></div>
                            <div class="row">
                                <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                                <div class="col-sm-3" style="padding-left:0;">
                                    <select id="script_mode" name="script_mode" class="form-control">
                                    <option value="sh" >shell</option>
                                    <option value="groovy" >groovy</option>
                                    <option value="perl" >perl</option>
                                    <option value="python" >python</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="advOptProDiv">
                        <div class="form-group">
                            <label for="mAdvIcon" class="col-sm-2 control-label">Advanced Options</label>
                            <div class="col-sm-10">
                                <i id="mAdvProIcon" data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a id="mAdvProCollap" class="fa fa-plus-square-o collapseIcon" style=" font-size:15px; padding-top:10px; padding-left:5px;" data-toggle="collapse" data-target="#advOptPro"></a></i>
                            </div>
                        </div>
                    </div>
                    <!-- collapsed settings-->
                    <div id="advOptPro" class="row collapse">
                        <div class="form-horizontal">
                            <label style="width:150px;" class="col-sm-2 control-label">Header Script </label>
                            <div id="editorHeaderdiv" class="col-sm-10" style="margin-top:20px; margin-bottom:25px;">
                                <div id="editorProHeader" style="height:150px;"></div>
                                <div class="row">
                                    <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                                    <div class="col-sm-3" style="padding-left:0;">
                                        <select id="script_mode_header" name="script_mode_header" class="form-control">
                                    <option value="groovy" >groovy</option>
                                    </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-horizontal">
                            <label style="width:150px;" class="col-sm-2 control-label">Footer Script </label>
                            <div id="editorFooterdiv" class="col-sm-10" style="margin-top:20px; margin-bottom:25px;">
                                <div id="editorProFooter" style="height:150px;"></div>
                                <div class="row">
                                    <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                                    <div class="col-sm-3" style="padding-left:0;">
                                        <select id="script_mode_footer" name="script_mode_footer" class="form-control">
                                    <option value="groovy" >groovy</option>
                                    </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-horizontal">
                            <div id="proPermGroPubDiv">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Permissions to View</label>
                                        <div class="col-sm-6">
                                            <select id="permsPro" class="fbtn btn-default form-control" name="perms">
                              <option value="3" selected="">Only me </option>
                              <option value="15">Only my groups</option>
                              <option disabled value="63">Everyone </option>
                        </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="col-sm-6 control-label">Group Selection</label>
                                        <div class="col-sm-6">
                                            <select id="groupSelPro" class="fbtn btn-default form-control" name="group_id">
                          <option value="" selected>Choose group </option>
                        </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">Publish</label>
                                        <div class="col-sm-3">
                                            <select id="publishPro" class="fbtn btn-default form-control" name="publish">
                                      <option value="0">No</option>
                                      <option value="1">Yes</option>
                                    </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveprocess">Save changes</button>
                <button type="button" class="btn btn-primary" style="display:none" id="selectProcess">Select Revision</button>
                <button type="button" class="btn btn-primary" style="display:none" id="createRevisionBut" onclick="createRevision()">Create Revision</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Process Modal Ends-->

<!-- Add Parameter Modal Starts-->
<div id="parametermodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close dismissparameter" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="parametermodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mIdPar" class="col-sm-3 control-label">ID</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mIdPar" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mParamList" class="col-sm-3 control-label">Parameters</label>
                        <div id="mParamsDynamic" class="col-sm-1" style=" display:none; width: auto;  ">
                            <button type="button" class="btn btn-default form-control" id="mParamOpen"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Show Parameters"><i class="fa fa-eye" ></i></a></button>
                        </div>
                        <div id="mParamList" class="col-sm-9" style=" ">
                            <select id="mParamListIn" class="fbtn btn-default form-control" name="ParamAllIn"></select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mName" class="col-sm-3 control-label">Identifier <span><a id="mNameTool" data-toggle="tooltip" data-placement="bottom" title="Must begin with a letter ([A-Za-z]) and may be followed by letters, digits or underscores (&quot;_&quot;)"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="modalName" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mQualifier" class="col-sm-3 control-label">Qualifier</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="modalQualifier" name="qualifier">
                                            <option value="file">file</option>
                                            <option value="set">set</option>
                                            <option value="val">val</option>
                                        </select>
                        </div>
                    </div>
                    <div class="form-group" id="mFileTypeDiv">
                        <label for="mFileType" class="col-sm-3 control-label">File Type <span><a id="mFileTypeTool" data-toggle="tooltip" data-placement="bottom" title="Must begin with a letter ([A-Za-z]) and may be followed by letters, digits or underscores (&quot;_&quot;)"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mFileType" name="file_type">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default dismissparameter" id="dismissparameter" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveparameter" data-clickedrow="">Save changes</button>
            </div>
        </div>
    </div>
</div>
<!-- Add Parameter Modal Ends-->

<!-- Delete Parameter Modal Starts-->
<div id="delparametermodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close dismissparameterdel" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Delete Parameter</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="mParamList" class="col-sm-2 control-label">Parameters</label>
                        <div id="mParamListDelDiv" class="col-sm-10">
                            <select id="mParamListDel" class="fbtn btn-default form-control" name="ParamAllIn"></select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default dismissparameterdel" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="delparameter">Delete Parameter</button>
            </div>
        </div>
    </div>
</div>
<!-- Delete Parameter Modal Ends-->

<!-- Process Group Modal Starts-->
<div id="processGroupModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="processGroupmodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mIdProGroup" class="col-sm-3 control-label">ID</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mIdProGroup" name="id">
                        </div>
                    </div>
                    <div id="mGroupListForm" class="form-group" style="display:none">
                        <label for="mGroupListDiv" class="col-sm-3 control-label">Menu Group</label>
                        <div id="mGroupListDiv" class="col-sm-9">
                            <select id="mMenuGroupList" class="fbtn btn-default form-control" name="group_name"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mProGroupName" class="col-sm-3 control-label"> New Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mGroName" name="group_name">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveProcessGroup" data-clickedrow="">Save changes</button>
            </div>
        </div>
    </div>
</div>
<!-- Process Group Modal Ends-->

<!-- Delete Process Group Modal Starts-->
<div id="delprocessGrmodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Delete Process Group</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="mGroupListDelDiv" class="col-sm-3 control-label">Menu Group</label>
                        <div class="col-sm-9">
                            <select id="mMenuGroupListDel" class="fbtn btn-default form-control" name="group_name"></select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="delproGroup">Delete Process Group</button>
            </div>
        </div>
    </div>
</div>
<!-- Delete Process Group Modal Ends-->

<!-- Pipeline Group Modal Starts-->
<div id="pipeGroupModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="pipelineGroupmodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mIdPipeGroup" class="col-sm-3 control-label">ID</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mIdPipeGroup" name="id">
                        </div>
                    </div>
                    <div id="mGroupPipeList" class="form-group" style="display:none">
                        <label for="mGroupListDiv" class="col-sm-3 control-label">Menu Group</label>
                        <div id="mGroupPipeDiv" class="col-sm-9">
                            <select id="mGroupPipe" class="fbtn btn-default form-control" name="group_name"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mPipeGroupName" class="col-sm-3 control-label">New Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mPipeGroupName" name="group_name">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savePipeGroup" data-clickedrow="">Save changes</button>
            </div>
        </div>
    </div>
</div>
<!-- Pipeline Group Modal Ends-->

<!-- Delete Pipeline Group Modal Starts-->
<div id="pipeDelGroupModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Delete Pipeline Menu Group</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Pipeline Menu Group</label>
                        <div class="col-sm-8">
                            <select id="mPipeMenuGroupDel" class="fbtn btn-default form-control" name="group_name"></select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="delpipeGroup">Delete Pipeline Group</button>
            </div>
        </div>
    </div>
</div>
<!-- Delete Pipeline Group Modal Ends-->

<!-- Rename Modal Starts-->
<div id="renameModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="renameModaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mRenameID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mRenameID" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="md3Name" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mRenName" name="d3_name">
                        </div>
                    </div>
                    <div id="defValDiv" class="form-group">
                        <div class="col-sm-4 control-label">
                            <label> <input type="checkbox" id="checkDefVal" name="checkDefVal" > Define Default Value</label>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="defVal" name="defVal" disabled>
                        </div>
                    </div>
                    <div id="dropdownDiv" class="form-group">
                        <div class="col-sm-4 control-label">
                            <label> <input type="checkbox" id="checkDropDown" name="checkDropDown" > Dropdown Options <span><a data-toggle="tooltip" data-placement="bottom" title="Please click checkbox to use drop down menu in the run pipeline page. Options need to be entered in comma separated format. eg. single, pair"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="dropDownOpt" name="dropDownOpt" disabled>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="renameProPara" data-clickedrow="">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- Rename Modal Ends-->



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
<!--Confirm Modal Ends-->

<div id="confirmRevision" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close cancelRev" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="confirmYesNoTitle">Confirm revision</h4>
            </div>
            <div class="modal-body">
                <span id="confirmYesNoText">Text</span>
                </br>
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="mRevComment" class="col-sm-2 control-label">Comment</label>
                        <div class="col-sm-4">
                            <input type="text" maxlength="20" class="form-control" id="mRevComment" name="rev_comment">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" style="display:none;" id="saveOnExist">Save on Existing</button>
                <button type="button" class="btn btn-default cancelRev" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveRev">Save</button>
            </div>
        </div>
    </div>
</div>


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

<!--Run Modal-->

<div id="mRun" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="mRuntitle"> Add Pipeline to Project</h4>
            </div>
            <div class="modal-body">
                <div class="panel panel-default" id="projecttablepanel">
                    <div class="panel-heading clearfix">
                        <div class="pull-right">
                            <button type="button" class="btn btn-primary btn-sm" title="Add Project" id="addproject" data-toggle="modal" data-target="#projectmodal">Create a Project</button>
                        </div>
                        <div class="pull-left">
                            <h5><i class="fa fa-calendar-o " style="margin-left:0px; margin-right:0px;"></i> Select Project</h5>
                        </div>
                    </div>

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
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="selectProject" data-clickedrow="">Select Project</button>
            </div>
        </div>
    </div>
</div>

<!--ExistRuns Modal-->

<div id="mExistRun" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"> Go to Existing Runs</h4>
            </div>
            <div class="modal-body">
                <div class="panel panel-default">
                    <div class="panel-heading clearfix">
                        <div class="pull-left">
                            <h5><i class="fa fa-calendar-o " style="margin-left:0px; margin-right:0px;"></i> Select Run</h5>
                        </div>
                    </div>

                    <div class="panel-body">
                        <table id="existRunTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                            <thead>
                                <tr>
                                    <th>Check</th>
                                    <th>Run Name</th>
                                    <th>Project Name</th>
                                    <th>Owner</th>
                                    <th>Modified on</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="selectExistRun" data-clickedrow="">Go to Run</button>
            </div>
        </div>
    </div>
</div>

<div id="runNameModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="runNameModaltitle">Enter Run Name</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="runID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="runID" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="runName" class="col-sm-2 control-label">Name</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="runName" name="name">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveRun" data-clickedrow="">Save run</button>
            </div>
        </div>
    </div>
</div>

<div id="projectmodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="projectmodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mProjectID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mProjectID" name="id">
                        </div>
                    </div>
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

<!--Run Modal ends-->



<div id="id01" class="w3-modal">
    <div class="w3-modal-content w3-card-4 w3-animate-zoom">
        <header class="w3-container w3-blue">
            <span onclick="document.getElementById('id01').style.display='none'" class="w3-button w3-green w3-xlarge w3-display-topright">&times;</span>
            <h2>Process</h2>
        </header>

        <div class="w3-bar w3-border-bottom">
            <button class="tablink w3-bar-item w3-button" onclick="openPage(event, 'process')">Process</button>
            <button class="tablink w3-bar-item w3-button" onclick="openPage(event, 'inputs')">Inputs</button>
            <button class="tablink w3-bar-item w3-button" onclick="openPage(event, 'outputs')">Outputs</button>
        </div>

        <div id="process" class="w3-container nodisp">
            <h1 id="process_name"></h1>
            <div id="process_summary"></div>
            <div id="process_script"></div>
        </div>

        <div id="inputs" class="w3-container nodisp">
            <div class="panel panel-default" id="pinputpanel">
                <div class="panel-body">
                    <h4>Input List</h4>
                    <table id="pinputtable" class="table table-striped" cellspacing="0" width="100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Process Name</th>
                                <th>Version</th>
                                <th>Type</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Process Name</th>
                                <th>Version</th>
                                <th>Type</th>
                                <th>Delete</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div id="outputs" class="w3-container nodisp">
            <div class="panel-body">
                <h4>Output List</h4>
                <table id="poutputtable" class="table table-striped" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Process Name</th>
                            <th>Version</th>
                            <th>Type</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Process Name</th>
                            <th>Version</th>
                            <th>Type</th>
                            <th>Delete</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="w3-container w3-light-grey w3-padding">
            <button class="w3-btn w3-right w3-white w3-border" onclick="document.getElementById('id01').style.display='none'">Close</button>
        </div>
    </div>
</div>



