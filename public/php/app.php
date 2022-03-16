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

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <div class="pull-right">
            <button type="button" class="btn btn-primary btn-sm" title="Add App" id="addcontainer" data-toggle="modal" data-target="#containermodal">Create App</button>
        </div>
        <div class="pull-left">
            <h5><i class="fa fa-cube " style="margin-left:0px; margin-right:0px;"></i> My Apps</h5>
        </div>
    </div>

    <div class="panel-body">
        <table id="containertable" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>App Name</th>
                    <th>Description</th>
                    <th>Container Name</th>
                    <th>Container Type</th>
                    <th>Status</th>
                    <th>Owner</th>
                    <th>Created on</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading clearfix">
        <div class="pull-left">
            <h5><i class="fa fa-cube " style="margin-left:0px; margin-right:0px;"></i> Shared Apps</h5>
        </div>
    </div>

    <div class="panel-body">
        <table id="sharedcontainertable" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>App Name</th>
                    <th>Description</th>
                    <th>Container Name</th>
                    <th>Container Type</th>
                    <th>Status</th>
                    <th>Owner</th>
                    <th>Created on</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div id="containermodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="containermodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mContainerID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mContainerID" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Label
                            <span><a data-toggle="tooltip" data-placement="bottom" title="App label that will be used in dropdowns."><i class='glyphicon glyphicon-info-sign'></i></a></span>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Description
                        </label>
                        <div class="col-sm-9">
                            <textarea type="text" rows="2" class="form-control" name="summary"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">App Type
                        </label>
                        <div class="col-sm-9">
                            <select class="fbtn btn-default form-control" name="type">
                                <option value="docker">Docker</option>
                                <option value="singularity">Singularity</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Image Name (optional)
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="image_name">
                        </div>
                        <div class="col-sm-3"></div>
                        <div class="col-sm-9">
                            <span class="small">
                                Optional image name if the container is downloadable from Dockerhub or Singularity hub. e.g., <code style="color:#333;">dolphinnext/jupyter:1.0</code> Alternatively, you can enter recipe files in the App Details page.
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-3 control-label">Status
                        </label>
                        <div class="col-sm-9">
                            <select class="fbtn btn-default form-control" name="status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label class="col-sm-6 control-label">Permissions to View</label>
                                <div class="col-sm-6">
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
                                <label class="col-sm-6 control-label">Group Selection</label>
                                <div class="col-sm-6">
                                    <select id="groupSel" class="fbtn btn-default form-control" name="group_id">
                                        <option value="" selected="">Choose group </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savecontainer" data-clickedrow="">Save changes</button>
            </div>
        </div>
    </div>
</div>