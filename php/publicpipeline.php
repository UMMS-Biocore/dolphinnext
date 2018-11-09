<style>
    .nodisp {
        display: block
    }
</style>

<div class="box-header" style=" padding-top:0px;  font-size:large; ">
    <div style="padding-bottom:6px;  border-bottom:1px solid lightgrey;">
        <i class="fa fa-spinner " style="padding-top:12px; margin-left:0px; margin-right:0px;"></i> Pipeline:
        <a href="" pipelineid="<?php echo $id;?>" style="margin-left:0px; font-size: large; font-style:italic; align-self:center; max-width: 500px;" id="pipeline-title"></a>
    </div>
</div>




<div style="padding-left:16px; padding-right:16px; padding-bottom:20px; " id="desPipeline">
    <div class="row" id="creatorInfoPip" style="font-size:12px; display:none;"> Created by <span id="ownUserNamePip">admin</span> on <span id="datecreatedPip">Jan. 26, 2016     04:12</span> • Last edited on <span class="lasteditedPip">Feb. 8, 2017 12:15</span>
    </div>
    </br>
    <div class="row" id="desTitlePip">
        <h6><b>Description</b></h6>
    </div>
    <div class="row"><textarea id="pipelineSum" placeholder="Enter pipeline description here.." rows="7" style="min-width: 100%; max-width: 100%; border-color:lightgrey;"></textarea></div>

</div>
<div>
    <h4>Workflow</h4>
    <div class="panel panel-default">
        <div style="height:500px;" id="container" ondrop="drop(event)" ondragover="allowDrop(event)"></div>
    </div>
</div>
</br>
<div id="workDetails">
    <div>
        <h4>Pipeline Details</h4>
        </br>
    </div>
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
</br>


