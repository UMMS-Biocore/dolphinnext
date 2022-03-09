<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$ownerID = isset($_SESSION['ownerID']) ? $_SESSION['ownerID'] : "";
$admin_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : "";
$role = isset($_SESSION['role']) ? $_SESSION['role'] : "";
if ($ownerID != '') {
    $login = 1;
} else {
    $login = 0;
}
session_write_close();

?>



<div id="projectHeader" class="box-header" style=" padding-top:0px; font-size:large; ">
    <div style=" border-bottom:1px solid lightgrey;">
        <i class="fa fa-cube " style="margin-left:0px; margin-right:0px;"></i> App:
        <input class="box-dynamic width-dynamic" type="text" appid="<?php echo $id; ?>" name="projectTitle" autocomplete="off" placeholder="Enter App Name" style="margin-left:0px; font-size: large; font-style:italic; align-self:center; max-width: 500px;" title="Rename" data-placement="bottom" data-toggle="tooltip" num="" id="app-title"><span class="width-dynamic" style="display:none"></span></input>
        <button type="submit" class="btn" name="button" data-backdrop="false" onclick="saveAppIcon()" style=" margin:0px; padding:0px; background: none;">
            <a data-toggle="tooltip" data-placement="bottom" data-original-title="Save App">
                <i class="fa fa-save" style="font-size: 17px;"></i></a></button>
        <button type="button" class="btn deleteApp" name="button" data-backdrop="false" style=" margin:0px; padding:0px; background: none;">
            <a data-toggle="tooltip" data-placement="bottom" data-original-title="Delete App">
                <i class="glyphicon glyphicon-trash"></i></a></button>
        <div id="projectActDiv" style="float:right; margin-right:5px;" class="dropdown">
            <button class="btn btn-default dropdown-toggle" type="button" id="projectAct" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true" style="vertical-align:middle;">
                <div class="fa fa-ellipsis-h"></div>
            </button>
            <ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="dropdownMenu4">
                <li><a class="deleteApp">Delete App</a></li>
            </ul>
        </div>
    </div>
</div>

<div style="padding-left:16px; padding-right:16px; padding-bottom:20px;" id="desProject">
    <div class="row" id="creatorProject" style="font-size:12px;"> Created by <span id="ownUserName"></span> on <span id="datecreatedPj"></span> • Last edited on <span id="lasteditedPj"></span></div>
    </br>
    <div class="pull-left">
        <h6 class="row" id="desTitleProject"><b>Description</b> </h6>
    </div>
    </br>
    <div class="row"><textarea id="projectSum" placeholder="Enter app description here.." rows="4" style="min-width: 100%; max-width: 100%; border-color:lightgrey;"></textarea></div>
</div>

<form class="form-horizontal" id="appForm">
    <div class="form-group">
        <label class="col-sm-2 control-label">Image Name (optional)
        </label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="image_name"></input>
            <span class="small">
                Optional image name if the container is downloadable from Dockerhub or Singularity hub. e.g., <code style="color:#333;">dolphinnext/jupyter:1.0</code> Alternatively, you can enter recipe files in the App Files section.
            </span>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">Container Type
        </label>
        <div class="col-sm-4">
            <select class="fbtn btn-default form-control" name="type">
                <option value="docker">Docker</option>
                <option value="singularity">Singularity</option>
            </select>
        </div>
        <label class="col-sm-2 control-label">Status
        </label>
        <div class="col-sm-4">
            <select class="fbtn btn-default form-control" name="status">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-6">
            <div class="form-group">
                <label class="col-sm-4 control-label">Permissions to View</label>
                <div class="col-sm-8">
                    <select id="perms" class="fbtn btn-default form-control" name="perms">
                        <option value="3" selected="">Only me </option>
                        <option value="15">Only my groups</option>
                        <?php
                        if ($login == 1 && ($role == "admin")) {
                            echo '<option value="63">Everyone </option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label class="col-sm-4 control-label">Group Selection</label>
                <div class="col-sm-8">
                    <select id="groupSel" class="fbtn btn-default form-control" name="group_id">
                        <option value="" selected="">Choose group </option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="panel panel-default" id="runtablepanel">
    <div class="panel-heading clearfix">
        <div class="pull-left">
            <h5><i class="fa fa-file-text"></i> App Files</h5>

        </div>
    </div>
    <div id="containerfiles_wrapper" class="panel-body">
        <div id="containerfiles"></div>
    </div>
</div>
</br>



<div id="confirmModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Information</h4>
            </div>
            <div class="modal-body">
                <span id="confirmModalText">Text</span>
                </br>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="deleteBtn">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal" id="cancelButton">Cancel</button>
            </div>
        </div>
    </div>
</div>