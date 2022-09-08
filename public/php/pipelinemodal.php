<style>
    .nodisp {
        display: block
    }
</style>

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
                    <span id="datecreatedPipe">Jan. 26, 2016 04:12</span> • Last edited on
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
<div id="addProcessModal" data-keyboard="false" data-backdrop="static" style="overflow-y:scroll;" class="modal fade fullscreen" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg fullscreen" role=" document">
        <div class="modal-content fullscreen">
            <span id="addHeader"></span>
            <div id="revModalHeader" class="modal-header">
                <button style="padding-top:6px;" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div id="mProActionsDiv" style="float:right; margin-right:15px; display:none;" class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" id="mProActions" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="vertical-align:middle;"><span class="fa fa-ellipsis-h"></span></button>
                    <ul class="dropdown-menu pull-right" role="menu" aria-labelledby="dropdownMenu1">
                        <li><a id="deleteRevision" data-toggle="modal" href="#confirmModal">Delete Revision</a></li>
                        <li><a id="createRevision" class="saveprocess" style="display:none;">Create Revision</a></li>
                        <li><a id="duplicaProRev" onclick="duplicateProcessRev()">Copy Process</a></li>

                    </ul>
                </div>
                <span id="mProRevSpan" style="margin-right:5px; width:130px; float:right; display:none;">
                    <select id="mProRev" class="fbtn btn-default form-control mRevChange" prev="-1" name="process_rev_id"></select>
                </span>
                <h4 class="modal-title" id="processmodaltitle">Title</h4>
                <div class="col-sm-12" id="creatorInfoPro" style="display:none; font-size:12px; padding-left:0px; margin-left:0px;"> Created by
                    <span id="ownUserNamePro"></span> on
                    <span id="datecreatedPro"></span> • Last edited on
                    <span id="lasteditedPro"></span>
                </div>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mIdPro" class="col-sm-1 control-label">ID</label>
                        <div class="col-sm-11">
                            <input type="text" class="form-control" id="mIdPro" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mName" class="col-sm-1 control-label">Name</label>
                        <div class="col-sm-11">
                            <input type="text" class="form-control" id="mName" name="name">
                        </div>
                    </div>
                    <div id="versionGroup" class="form-group" style="display:none">
                        <label for="mVersion" class="col-sm-1 control-label">Version</label>
                        <div class="col-sm-11">
                            <input type="text" class="form-control" id="mVersion" name="version">
                        </div>
                    </div>
                    <div id="describeGroup" class="form-group">
                        <label for="mDescription" class="col-sm-1 control-label">Description</label>
                        <div class="col-sm-11">
                            <textarea rows="3" class="form-control" id="mDescription" name="summary"></textarea>
                        </div>
                    </div>
                    <div id="proGroup" class="form-group">
                        <label for="mProcessGroup" class="col-sm-1 control-label">Menu Group</label>
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
                        <label for="mParamAll" class="col-sm-1 control-label">Parameters</label>
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
                            <p style="padding-left:10px;">Operators <span><a data-toggle="tooltip" data-placement="bottom" title="Optional operator section to transform values emitted by a channel."><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div id="mInClosureT" class="col-sm-2 " style="width:160px; padding-left:5px; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:20px;">Operator Content <span><a data-toggle="tooltip" data-placement="bottom" title="Optional operator content to specify how operators will act. Multiple operators can be added by starting paranthesis. i.e. (size:6).buffer(size:3)"><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div class="col-sm-2 " style="width:100px; padding-left:5px; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:15px;">Optional <span><a data-toggle="tooltip" data-placement="bottom" title="Optional input parameter. Process will be executed in case parameter is empty."><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div id="mInTestValueT" class="col-sm-2" style="width:250px; padding-left:5px; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:20px;">Test Value <span><a data-toggle="tooltip" data-placement="bottom" title="example values for each qualifier: val: 5, file: /export/file, set: [1, 'a'], [2, 'b'], set: /path/*.fastq, each: [5, 10]"><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                    </div>
                    <div id="inputGroup" class="form-group">
                        <label for="mInputs-1" class="col-sm-1 control-label">Inputs</label>
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
                        <div id="mInOptdel" class="col-sm-1" style="width:40px; padding-left:0; padding-right:0; margin-right:10px;">
                            <button type="submit" style="display:none;" class="btn btn-default form-control" id="mInOptdel-0" name="mInOptdel-0"><i class="glyphicon glyphicon-remove"></i></button>
                        </div>
                        <div id="mInOptional" class="col-sm-1" style="width:40px; padding-left:0;padding-right:0;">
                            <label style="display:none;" class="btn btn-default form-control"><input id="mInOptional-0" name="mInOptional-0" type="checkbox" autocomplete="off"> </label>
                        </div>
                        <div id="mInTestValue" class="col-sm-2 " style="width:300px; padding-left:30px; padding-right:0;">
                            <input type="text" style="display:none; " placeholder="Enter value" class="form-control" ppID="" id="mInTestValue-0" name="mInTestValue-0">
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
                        <div class="col-sm-2 " style="width:100px; padding-left:5px; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:15px;">Optional <span><a data-toggle="tooltip" data-placement="bottom" title="Optional output parameter. Process won't fail in case output parameter isn't created."><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                        <div id="mOutTestValueT" class="col-sm-2" style="display:none; width:220px; padding-left:5px; padding-right:0; border-bottom:1px solid lightgrey;">
                            <p style="padding-left:20px;">Test Value <span><a data-toggle="tooltip" data-placement="bottom" title="example values for each qualifier: val: 5, file: /export/file, set: [1, 'a'], [2, 'b'], set: /path/*.fastq, each: [5, 10]"><i class='glyphicon glyphicon-info-sign' style="font-size:13px;"></i></a></span></p>
                        </div>
                    </div>
                    <div id="outputGroup" class="form-group">
                        <label for="mOutput-1" class="col-sm-1 control-label">Outputs</label>
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
                        <div id="mOutOptdel" class="col-sm-1" style="width:40px; padding-left:0;margin-right:10px; padding-right:0;">
                            <button type="submit" style="display:none;" class="btn btn-default form-control" id="mOutOptdel-0" name="mOutOptdel-0"><i class="glyphicon glyphicon-remove"></i></button>
                        </div>
                        <div id="mOutOptional" class="col-sm-1" style="width:40px; padding-left:0;padding-right:0; ">
                            <label style="display:none;" class="btn btn-default form-control"><input id="mOutOptional-0" name="mOutOptional-0" type="checkbox" autocomplete="off"> </label>
                        </div>
                        <div id="mOutTestValue" class="col-sm-2 " style="display:none; width:250px; padding-left:30px; padding-right:0;">
                            <input type="text" style="display:none; " placeholder="Enter value" class="form-control" ppID="" id="mOutTestValue-0" name="mOutTestValue-0">
                        </div>
                    </div>


                    <div class="form-group" style=" padding-left:30px; padding-top:15px; border-top:0.094em solid lightgrey;">
                        <div class="col-sm-12">
                            <ul class="nav nav-tabs">
                                <li class="active"><a class="nav-item" href="#pro_editor_tab" data-toggle="tab" aria-expanded="true">Script</a></li>
                                <li class=""><a class="nav-item" href="#pro_test_tab" data-toggle="tab" aria-expanded="false">Process Test</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="pro_editor_tab">


                                    <div id="editordiv" class="col-sm-12" style="padding-left:0px;">
                                        <div id="editor" style="height: 300px;"></div>
                                        <div id="editor_draghandle" class="ui-icon ui-icon-gripsmall-diagonal-se" style="float:right;"></div>
                                        <div class="row">
                                            <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                                            <div class="col-sm-3" style="padding-left:0;">
                                                <select id="script_mode" name="script_mode" class="form-control">
                                                    <option value="sh">shell</option>
                                                    <option value="groovy">groovy</option>
                                                    <option value="perl">perl</option>
                                                    <option value="python">python</option>
                                                    <option value="r">R</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="pro_test_tab">
                                    <div id="testOptProDiv">
                                        <div id="testOptPro" class="row">
                                            <div class="form-horizontal" style="margin-top:15px;">
                                                <div class="col-sm-9">
                                                    <label>Log</label>
                                                </div>
                                                <div class="col-sm-3" id="pipeRunStatDiv" style="padding-bottom: 5px;">
                                                    <div id="errorProPipe" style="display:none; float: right;" class="btn-group">
                                                        <button class="btn btn-danger" type="button" id="errorProPipeBut">Run Error</button>
                                                        <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                            <li><a class="testscript">ReRun</a></li>
                                                        </ul>
                                                    </div>
                                                    <div id="completeProPipe" style="display:none;  float: right;" class="btn-group">
                                                        <button class="btn btn-success" type="button" id="completeProPipeBut">Completed</button>
                                                        <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                            <li><a class="testscript">ReRun</a></li>
                                                        </ul>
                                                    </div>
                                                    <div id="runningProPipe" style="display:none;  float: right;" class="btn-group">
                                                        <button class="btn btn-info" type="button" id="runningProPipeBut">Running</button>
                                                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                            <li><a href="#" onclick="terminateProjectPipe();return false;">Terminate</a></li>
                                                        </ul>
                                                    </div>
                                                    <div id="waitingProPipe" style="display:none;  float: right;" class="btn-group">
                                                        <button class="btn btn-info" type="button" id="waitingProPipeBut">Initializing..</button>
                                                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                            <li><a href="#" onclick="terminateProjectPipe();return false;">Terminate</a></li>
                                                        </ul>
                                                    </div>
                                                    <div id="connectingProPipe" style="display:none;  float: right;" class="btn-group">
                                                        <button class="btn btn-info" type="button" id="connectingProPipeBut">Connecting..</button>
                                                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                            <li><a href="#" onclick="terminateProjectPipe();return false;">Terminate</a></li>
                                                        </ul>
                                                    </div>
                                                    <div id="terminatedProPipe" style="display:none;  float: right;" class="btn-group">
                                                        <button class="btn btn-danger" type="button" id="terminatedProPipeBut">Terminated</button>
                                                        <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                            <li><a class="testscript">ReRun</a></li>
                                                        </ul>
                                                    </div>
                                                    <div id="abortedProPipe" style="display:none;  float: right;" class="btn-group">
                                                        <button class="btn btn-info" type="button" id="abortedProPipeBut">Reconnecting..</button>
                                                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                                            <li><a href="#" onclick="terminateProjectPipe();return false;">Terminate</a></li>
                                                            <li><a class="testscript">ReRun</a></li>
                                                        </ul>
                                                    </div>
                                                    <div id="runProPipe" style="display:none;  float: right;" class="btn-group">
                                                        <button class="btn btn-success testscript" type="button" id="runProPipeBut"><i class="fa fa-play " style=""></i> Test</button>
                                                    </div>
                                                    <button class="btn btn-warning" type="submit" id="statusProPipe" style="display:none; float: right; vertical-align:middle;" data-original-title="Waiting for input parameters, output directory and selection of active environment" data-placement="bottom" data-toggle="tooltip">Waiting</button>
                                                    <button style="float:right; margin-right: 5px;" id="saveProcessTest" class="btn btn-default saveprocess" type="button" id="saveProcessTest"><i class="fa fa-save "></i></button>

                                                </div>
                                                <div class="col-sm-12">
                                                    <textarea readonly id="testrunLogArea" rows="10" style="overflow-y: scroll; min-width: 100%; max-width: 100%; border-color:lightgrey;"></textarea>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="col-md-6" style="padding-left:0px;" id="runEnvDiv">
                                                        <label>Run Environment </label>
                                                        <select id="test_env" style="width: 100%;" class="fbtn btn-default form-control" name="test_env">
                                                            <option value="" disabled selected>Choose environment </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6" style="padding-left:0px;">
                                                        <label style="float:left; margin-right:4px;"> Work Directory <span><a data-toggle="tooltip" data-placement="bottom" title="Please enter the full path of the work directory in your host. eg. /project/rna-seq/run1"><i style="font-size: 14px;" class='fa fa-info-circle'></i></a></span></label>
                                                        <input type="text" class="form-control" style="width: 100%;" id="test_work_dir" name="test_work_dir" placeholder="Enter work directory">
                                                    </div>
                                                </div>
                                                <div id="containersDiv" class="col-md-12" style="margin-top:15px;">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <input type="checkbox" id="docker_check" name="docker_check" data-toggle="collapse" data-target="#docker_imgDiv"> Use Docker Image
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
                                                            <input type="checkbox" id="singu_check" name="singu_check" data-toggle="collapse" data-target="#singu_imgDiv"> Use Singularity Image
                                                        </div>
                                                        <div id="singu_imgDiv" class="collapse">
                                                            <div class="form-group ">
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
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="editorTestProdiv" class="col-sm-12" style="margin-top:20px; margin-bottom:25px; padding-left:12px;">
                                                <label>Test Parameters </label>
                                                <div id="editorTestPro" style="height:100px;"></div>
                                                <div id="editorTestPro_dragbar" class="app_editor_dragbar"></div>
                                                <div style="display:none;" class="row">
                                                    <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                                                    <div class="col-sm-3" style="padding-left:0;">
                                                        <select id="script_mode_test_pro" name="script_mode_test_pro" class="form-control">
                                                            <option value="groovy">groovy</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div id="advOptProDiv">
                        <div class="form-group">
                            <label for="mAdvIcon" class="col-sm-1 control-label" style="width:fit-content;">Advanced Options</label>
                            <div class="col-sm-10">
                                <i id="mAdvProIcon" data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a id="mAdvProCollap" class="fa fa-plus-square-o collapseIcon" style=" font-size:15px; padding-top:10px; padding-left:5px;" data-toggle="collapse" data-target="#advOptPro"></a></i>
                            </div>
                        </div>
                    </div>
                    <!-- collapsed settings-->
                    <div id="advOptPro" class="row collapse">
                        <div class="form-horizontal">
                            <label style="width:150px;" class="col-sm-2 control-label">Header Script </label>
                            <div id="editorProHeaderdiv" class="col-sm-10" style="margin-top:20px; margin-bottom:25px;">
                                <div id="editorProHeader" style="height:150px;"></div>
                                <div id="editorProHeader_dragbar" class="app_editor_dragbar"></div>
                                <div class="row">
                                    <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                                    <div class="col-sm-3" style="padding-left:0;">
                                        <select id="script_mode_header" name="script_mode_header" class="form-control">
                                            <option value="groovy">groovy</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-horizontal">
                            <label style="width:150px;" class="col-sm-2 control-label">Footer Script </label>
                            <div id="editorProFooterdiv" class="col-sm-10" style="margin-top:20px; margin-bottom:25px;">
                                <div id="editorProFooter" style="height:150px;"></div>
                                <div id="editorProFooter_dragbar" class="app_editor_dragbar"></div>
                                <div class="row">
                                    <p class="col-sm-3" style="padding-top:6px; padding-right:0;">Language Mode:</p>
                                    <div class="col-sm-3" style="padding-left:0;">
                                        <select id="script_mode_footer" name="script_mode_footer" class="form-control">
                                            <option value="groovy">groovy</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-horizontal">
                            <div id="proPermGroPubDiv">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label class="col-sm-5 control-label">Permission to View</label>
                                        <div class="col-sm-7">
                                            <select id="permsPro" class="fbtn btn-default form-control permscheck" name="perms">
                                                <option value="3" selected="">Only me </option>
                                                <option value="15">Only my groups</option>
                                                <option value="63">Everyone </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-4" id="groupSelProDiv">
                                    <label class="col-sm-5 control-label">Group Permission to View</label>
                                    <div class="col-sm-7">
                                        <select id="groupSelPro" class="fbtn btn-default form-control permscheck" name="group_id">
                                            <option value="" selected>Choose group </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group col-sm-4">
                                    <label class="col-sm-5 control-label ">Group Permission to Write</label>
                                    <div class="col-sm-7" style="padding-right:60px;">
                                        <select id="proWriteGroupPipe" class="fbtn btn-default form-control" name="write_group_id" style="margin-top:10px;" multiple="multiple">
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <div>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="button" data-container="body" data-toggle="tooltip" data-placement="top" title="Use the revision of the process in the workflow" class="btn btn-info" style="display:none" id="selectProcess">Select Revision </button>
                    <button type="button" class="btn btn-primary saveprocess" id="saveprocess">Save changes</button>
                    <button type="button" class="btn btn-primary saveprocess" style="display:none" id="createRevisionBut">Create Revision</button>
                </div>
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
                        <label for="mParamList" class="col-sm-4 control-label">Parameters</label>
                        <div id="mParamsDynamic" class="col-sm-1" style=" display:none; width: auto;  ">
                            <button type="button" class="btn btn-default form-control" id="mParamOpen"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Show Parameters"><i class="fa fa-eye"></i></a></button>
                        </div>
                        <div id="mParamList" class="col-sm-8" style=" ">
                            <select id="mParamListIn" class="fbtn btn-default form-control" name="ParamAllIn"></select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="mName" class="col-sm-4 control-label">Identifier <span><a id="mNameTool" data-toggle="tooltip" data-placement="bottom" title="Must begin with a letter ([A-Za-z]) and may be followed by letters, digits or underscores"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="modalName" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mQualifier" class="col-sm-4 control-label">Qualifier</label>
                        <div class="col-sm-8">
                            <select class="form-control" id="modalQualifier" name="qualifier">
                                <option value="file">file</option>
                                <option value="set">set/tuple</option>
                                <option value="val">val</option>
                                <option value="each">each</option>
                                <option value="env">env</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="mFileTypeDiv">
                        <label for="mFileType" id="mFileTypeLabel" class="col-sm-4 control-label">File Type <span><a id="mFileTypeTool" data-toggle="tooltip" data-placement="bottom" title="Must begin with a letter ([A-Za-z]) and may be followed by letters, digits or underscores. If qualifier is set to each, you may enter both file type (if you're planing to connect with file nodes) or identifier(in case of connecting to val nodes.)"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        <div class="col-sm-8">
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



<!-- gitConsoleModal Starts-->
<div id="gitConsoleModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Git Console</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" id="gitConsoleDiv">
                    <div class="form-group">
                        <label for="mGitUsername" class="col-sm-3 control-label">pushGit Account</label>
                        <div class="col-sm-9">
                            <select id="mGitUsername" class="fbtn btn-default form-control" name="username"></select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="github_repo" class="col-sm-3 control-label"> Repository</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="github_repo" name="github_repo">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="github_branch" class="col-sm-3 control-label"> Branch</label>
                        <div class="col-sm-9">
                            <input type="text" value="master" class="form-control" id="github_branch" name="github_branch">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <div class="pull-right">
                                <button type="button" class="btn btn-default" id="pushGit" data-clickedrow="">Push to GitHub</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <textarea rows="14" class="form-control" id="mGitLog" style="display:none;"></textarea>
                            <p id="mGitSuccess"></p>
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
<!-- gitConsoleModal Ends-->


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
                            <label> <input type="checkbox" id="checkDefVal" name="checkDefVal"> Define Default Value <span><a data-toggle="tooltip" data-placement="bottom" title="Please click checkbox to enter default value. Please don't use any quotes."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="defVal" name="defVal" disabled>
                        </div>
                    </div>
                    <div id="dropdownDiv" class="form-group">
                        <div class="col-sm-4 control-label">
                            <label> <input type="checkbox" id="checkDropDown" name="checkDropDown"> Dropdown Options <span><a data-toggle="tooltip" data-placement="bottom" title="Please click checkbox to use drop down menu in the run page. Options need to be entered in comma separated format and without quotes. eg. single, pair"><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="dropDownOpt" name="dropDownOpt" disabled>
                        </div>
                    </div>
                    <div id="showSettDiv" class="form-group">
                        <div class="col-sm-4 control-label">
                            <label> <input type="checkbox" id="checkShowSett" name="checkShowSett"> Show Settings <span><a data-toggle="tooltip" data-placement="bottom" placeholder="Optional process name" title="Please click checkbox to show settings of the connected process as a button in the inputs section of the run page. You may specify alternative processes by entering their process name. eg. map_STAR. Please don't use any quotes."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="showSettOpt" name="showSettOpt" disabled>
                        </div>
                    </div>
                    <div id="inlabelDiv" class="form-group">
                        <div class="col-sm-4 control-label">
                            <label> <input type="checkbox" id="checkInLabel" name="checkInLabel"> Label <span><a data-toggle="tooltip" data-placement="bottom" placeholder="Optional label" title="Please click the checkbox to define the input label seen on the run page."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="inLabelOpt" name="inLabelOpt" disabled></input>
                        </div>
                    </div>
                    <div id="indescDiv" class="form-group">
                        <div class="col-sm-4 control-label">
                            <label> <input type="checkbox" id="checkInDesc" name="checkInDesc"> Description <span><a data-toggle="tooltip" data-placement="bottom" placeholder="Optional description" title="Please click the checkbox to define the input description, which will be seen on the run page. You can use html syntax to create web-links or format the text."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        </div>
                        <div class="col-sm-8">
                            <textarea type="text" class="form-control" id="inDescOpt" name="inDescOpt" disabled></textarea>
                        </div>
                    </div>
                    <div id="pubWebDiv" class="form-group">
                        <div class="col-sm-5 control-label">
                            <label><input type="checkbox" id="checkPubWeb" name="pubWeb" style="margin-right:3px;"> Publish to Web Directory <span><a data-toggle="tooltip" data-placement="bottom" title="Please click checkbox to publish connected output files to web publish directory."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                        </div>
                        <div class="col-sm-7">
                            <select multiple="multiple" id="pubWebOpt" name="pubWebOpt">
                                <option value="apps">Apps</option>
                                <option value="table" plugin="dataTables.js">DataTables</option>
                                <option value="table-percent" plugin="dataTables.js">DataTables Percentage</option>
                                <option value="debrowser">DEBrowser</option>
                                <option value="html">HTML</option>
                                <option value="image">Image</option>
                                <!--<option value="highcharts" plugin="highcharts.js">Highcharts</option>-->
                                <option value="pdf">PDF Reader</option>
                                <option value="rdata">RData</option>
                                <option value="rmarkdown">R Markdown</option>
                                <option value="text">Text</option>
                                <option value="ucsc_genome_browser">USCS Genome Browser</option>
                            </select>
                        </div>

                    </div>
                    <div id="pubWebAppDiv" class="form-group">
                        <div class="col-sm-5 control-label">App Name</div>
                        <div class="col-sm-7">
                            <select class="form-control" id="pubWebApp" name="pubWebApp">
                            </select>
                        </div>
                    </div>
                    <div id="pubDmetaAllDiv">
                        <div id="pubDmetaDiv" class="form-group">
                            <div class="col-sm-5 control-label">
                                <label><input type="checkbox" id="checkPubDmeta" name="pubDmeta" style="margin-right:3px;"> Publish to Dmeta <span><a data-toggle="tooltip" data-placement="bottom" title="Please click checkbox to publish connected output files to Dmeta server."><i class='glyphicon glyphicon-info-sign'></i></a></span></label>
                            </div>
                            <div class="col-sm-7 control-label"></div>
                        </div>
                        <div id="pubDmetaSettings">
                            <div class="form-group">
                                <div class="col-sm-5 control-label">
                                    Location of the Sample Name <span><a data-toggle="tooltip" data-placement="bottom" title="Please choose the location of sample name in the published file."><i class='glyphicon glyphicon-info-sign'></i></a></span>
                                </div>
                                <div class="col-sm-5">
                                    <select class="form-control" id="pubDmetaFilename" name="pubDmetaFilename">
                                        <option value="row">Row Header</option>
                                        <option value="column">Column Header</option>
                                        <option value="filename">Filename</option>
                                    </select>
                                </div>
                            </div>
                            <div id="pubDmetaFeatureDiv" class="form-group">
                                <div class="col-sm-5 control-label">
                                    Location of the Features <span><a data-toggle="tooltip" data-placement="bottom" title="Please choose the location of features in the published file."><i class='glyphicon glyphicon-info-sign'></i></a></span>
                                </div>
                                <div class="col-sm-5">
                                    <select class="form-control" id="pubDmetaFeature" name="pubDmetaFeature">
                                        <option value="row">Row Header</option>
                                        <option value="column">Column Header</option>
                                        <option value="both">Both Headers</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-5 control-label">
                                    Target Collection <span><a data-toggle="tooltip" data-placement="bottom" title="Please choose the target collection in Dmeta server."><i class='glyphicon glyphicon-info-sign'></i></a></span>
                                </div>
                                <div class="col-sm-5">
                                    <select class="form-control" id="pubDmetaTarget" name="pubDmetaTarget">
                                        <option value=""></option>
                                        <option value="sample_summary">sample_summary</option>
                                        <option value="analysis">analysis</option>
                                        <option value="rsem_expected_count_gene">rsem_expected_count_gene</option>
                                        <option value="rsem_expected_count_isoform">rsem_expected_count_isoform</option>
                                        <option value="rsem_tpm_gene">rsem_tpm_gene</option>
                                        <option value="rsem_tpm_isoform">rsem_tpm_isoform</option>
                                    </select>
                                </div>
                            </div>
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


<!--Import Modal-->
<div id="importModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="importModalTitle">Import Tool</h4>
            </div>
            <div class="modal-body">
                <div id="importModalOptions" role="tabpanel">
                    <!-- Nav tabs -->
                    <ul id="importNav" class="nav nav-tabs" role="tablist">
                        <li id="manualImportBut" class="active"><a class="nav-item" data-toggle="tab" href="#manualImportTab">Manually</a></li>
                        <!-- <li id="publicImportBut" class="nav-item"><a class="nav-item" data-toggle="tab" href="#publicImportTab">Public</a></li> -->
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" searchtab="true" id="manualImportTab">
                            <div id="importModalPart1">
                                <form id="importArea" action="ajax/import.php" class="dropzone">
                                    <div class="fallback">
                                        <input name="file" type="file" multiple />
                                    </div>
                                </form>
                            </div>
                            <div id="importModalPart2"></div>
                        </div>
                        <div role="tabpanel" class="tab-pane" searchtab="true" id="publicImportTab">
                            <form class="form-horizontal" id="publicPipeImportForm" style="margin-top:20px;">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> Git Repository URL</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="import_repo">
                                        <span class="indesc">e.g. https://github.com/nf-core/rnaseq or https://github.com/dolphinnext/chipseq</span>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label"> Branch or Tag Name (optional)</label>
                                    <div class="col-sm-9">
                                        <input type="text" value="" class="form-control" name="import_branch">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div class="pull-right">
                                            <button type="button" class="btn btn-default" id="pullPipeline" data-clickedrow="">Pull Pipeline</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <textarea rows="14" class="form-control" id="pullPipeLog" style="display:none;"></textarea>
                                        <p id="mPullPipeSuccess"></p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal" id="cancelButton">Cancel</button>
                <button type="button" class="btn btn-primary" id="nextButton">Next</button>
                <button type="button" class="btn btn-primary" id="importButton">Import</button>
                <button type="button" class="btn btn-success" data-dismiss="modal" id="compButton">Complete</button>
            </div>
        </div>
    </div>
</div>
<!--Import Modal Ends-->

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
                <h4 class="modal-title" id="confirmYesNoTitle">Confirm</h4>
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
                <button type="button" class="btn btn-default cancelRev" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" style="display:none;" id="saveOnExist">Overwrite</button>
                <button type="button" class="btn btn-primary" id="saveRev">Save As New Revision</button>
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

<div id="warnUserImport" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Information</h4>
            </div>
            <div class="modal-body">
                <p style="height:500px; overflow:scroll;" id="warnUserText">Text</p>
                </br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal" id="saveOnExistImport">Overwrite</button>
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
                <h4 class="modal-title" id="mRuntitle"> Select Project to Add Pipeline</h4>
            </div>
            <div class="modal-body">
                <div role="tabpanel">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li id="userProjectLi" class="active"><a class="nav-item" data-toggle="tab" href="#userProjectTab">My Projects</a></li>
                        <li id="sharedProjectLi" class="nav-item"><a class="nav-item" data-toggle="tab" href="#sharedProjectTab">Shared Projects</a></li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" searchtab="true" id="userProjectTab">
                            <form class="form-horizontal" style="margin-top:15px;">
                                <div class="form-group">
                                    <div class="col-sm-12 pull-right">
                                        <button type="button" class="btn btn-primary btn-sm pull-right" title="Add Project" id="addproject" data-toggle="modal" data-target="#projectmodal">Create a Project</button>
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
                <div role="tabpanel">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li id="userRunLi" class="active"><a class="nav-item" data-toggle="tab" href="#userRunTab">My Runs</a></li>
                        <li id="sharedRunLi" class="nav-item"><a class="nav-item" data-toggle="tab" href="#sharedRunTab">Shared Runs</a></li>
                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" searchtab="true" id="userRunTab">
                            <div class="panel panel-default">
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
                        <div role="tabpanel" class="tab-pane" searchtab="true" id="sharedRunTab">
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <table id="sharedRunTable" class="table table-striped table-bordered" cellspacing="0" width="100%">
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