<div class="panel panel-default" id="runstatuspanel">
    <div class="panel-heading clearfix">
        <div class="pull-left">
            <h5><i class="fa fa-calendar-o " style="margin-left:0px; margin-right:0px;"></i> Run Status</h5>
        </div>
    </div>

    <div class="panel-body" style="overflow-x:auto;  min-height:600px;">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs" id="statTabs">
                        <li class="active"><a href="#runStatusTab" data-toggle="tab" aria-expanded="true">All</a></li>
                        <li><a href="#autoStatusTab" data-toggle="tab" aria-expanded="true">Automated</a></li>
                    </ul>
                    <div class="tab-content" style="display:inline;">
                        <div class="tab-pane active" id="runStatusTab">
                            <table id="runstatustable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th style="width:5%;">ID</th>
                                        <th style="width:15%;">Run Name</th>
                                        <th style="width:15%;">Pipeline</th>
                                        <th style="width:15%;">Work Directory</th>
                                        <th style="width:15%;">Description</th>
                                        <th style="width:10%;">Type</th>
                                        <th style="width:8%;">Status</th>
                                        <th style="width:12%;">Date Created</th>
                                        <th style="width:10%;">Owner</th>
                                        <th style="width:5%;">Options</th>
                                    </tr>
                                </thead>
                                <tbody style="word-break: break-all;"></tbody>
                            </table>
                        </div>
                        <!-- /.tab-pane ends -->
                        <div class="tab-pane" id="autoStatusTab">
                            <table id="autostatustable" class="table table-striped table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th style="width:3%;">ID</th>
                                        <th style="width:15%;">Run Name</th>
                                        <th style="width:14%;">Pipeline</th>
                                        <th style="width:14%;">Work Directory</th>
                                        <th style="width:14%;">Description</th>
                                        <th style="width:8%;">Type</th>
                                        <th style="width:5%;">Template</th>
                                        <th style="width:8%;">Status</th>
                                        <th style="width:10%;">Date Created</th>
                                        <th style="width:8%;">Owner</th>
                                        <th style="width:1%;">Options</th>
                                    </tr>
                                </thead>
                                <tbody style="word-break: break-all;"></tbody>
                            </table>
                        </div>
                        <!-- /.tab-pane ends -->
                    </div>
                    <!-- /.tab-content -->
                </div>
                <!-- /.nav-tabs-custom -->
            </div>
        </div>
    </div>
</div>






<div id="sendMailModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h4 class="modal-title">Send E-mail</h4>
            </div>
            <div class="modal-body">
                <form role="form" method="post" id="reused_form">
                    <p>
                        Send user a message by using the form below.
                    </p>

                    <div class="form-group">
                        <label for="adminemail">
                            Your E-mail:</label>
                        <div>
                            <input type="email" class="form-control" name="adminemail" required="" maxlength="50">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="useremail">
                            User's E-mail:</label>
                        <div>
                            <input type="email" class="form-control" name="useremail" required="" maxlength="50">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="subject">
                            Subject:</label>
                        <div>
                            <input type="text" class="form-control" name="subject" required="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">
                            Message:</label>
                        <div>
                            <textarea class="form-control" type="textarea" name="message" placeholder="Your Message Here" rows="7"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block" id="sendEmailUser">Send!</button>
                </form>
                <div id="success_message" style="width:100%; height:100%; display:none; ">
                    <h3>Sent your message successfully!</h3>
                </div>
                <div id="error_message" style="width:100%; height:100%; display:none; ">
                    <h3>Error</h3>
                    Sorry there was an error sending your form.
                </div>
            </div>

        </div>

    </div>
</div>


<!--Confirm Modal-->

<div id="confirmDelProModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="confirmDelProModalTitle">Confirm</h4>
            </div>
            <div class="modal-body" id="confirmDelProModalText">Text</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" id="mDelProBtn">Delete</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!--Confirm Modal Ends-->