<div class="panel panel-default" id="projecttablepanel">
    <div class="panel-heading clearfix">
        <div class="pull-right">
            <button type="button" class="btn btn-primary btn-sm" title="Add Project" id="addproject" data-toggle="modal" data-target="#projectmodal">Create a Project</button>
        </div>
        <div class="pull-left">
            <h5><i class="fa fa-calendar-o " style="margin-left:0px; margin-right:0px;"></i> Projects</h5>
        </div>
    </div>

    <div class="panel-body">
        <table id="projecttable" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Project Name</th>
                    <th>Owner</th>
                    <th>Created on</th>
                    <th>Edit/Remove</th>
                </tr>
            </thead>
        </table>
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


<div class="panel panel-default" id="publicfileValpanel" style="display:none;">
    <div class="panel-heading clearfix">
        <div class="pull-right">
            <button type="button" class="btn btn-primary btn-sm" title="Add Public Files/Values" id="addpublicFileVal" data-toggle="modal" data-target="#publicmodal">Add Public Files/Values</button>
        </div>
        <div class="pull-left">
            <h5><i class="fa fa-folder-open-o"></i> Public Files/Values</h5>
        </div>
    </div>
        <div class="panel-body">
            <table id="publicfiletable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                <thead>
                    <tr>
                        <th>File/Value Name</th>
                        <th>Type</th>
                        <th>Host/Shared Storage ID</th>
                        <th>Owner</th>
                        <th>Created On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
</div>


<div id="publicmodal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="publicmodaltitle">Modal title</h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal">
                    <div class="form-group" style="display:none">
                        <label for="mInputID" class="col-sm-2 control-label">ID</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" id="mInputID" name="id">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mInputName" class="col-sm-3 control-label">Name</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="mInputName" name="name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mInputType" class="col-sm-3 control-label">Type</label>
                        <div class="col-sm-9">
                            <select id="mInputType" class="fbtn btn-default form-control" name="type">
                                    <option value="" disabled selected>Select Type </option>
                                    <option value="file">file</option>
                                    <option value="val" >val</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="mInputHost" class="col-sm-3 control-label">Hostname/ Shared Storage Id</label>
                        <div class="col-sm-9">
                            <select id="mInputHost" class="fbtn btn-default form-control" name="host">
                              <option value="" disabled selected>Choose Hostname </option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="savepublic" data-clickedrow="">Save changes</button>
            </div>
        </div>
    </div>
</div>