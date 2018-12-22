<div class="panel panel-default" id="runstatuspanel">
    <div class="panel-heading clearfix">
        <div class="pull-left">
            <h5><i class="fa fa-calendar-o " style="margin-left:0px; margin-right:0px;"></i> Run Status</h5>
        </div>
    </div>

    <div class="panel-body">
        <table id="runstatustable" class="table table-striped table-bordered" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th style="display:none;">Date</th>
                    <th>Run Name</th>
                    <th>Work Directory</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date Created</th>
                    <th>Owner</th>
                    <th>Options</th>
                </tr>
            </thead>
        </table>
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
                        <input type="email" class="form-control" name="adminemail" required="" maxlength="50">

                    </div>
                    <div class="form-group">
                        <label for="useremail">
                            Users Email:</label>
                        <input type="email" class="form-control"  name="useremail" required="" maxlength="50">
                    </div>
                    <div class="form-group">
                        <label for="name">
                            Message:</label>
                        <textarea class="form-control" type="textarea" name="message" placeholder="Your Message Here" rows="7"></textarea>
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


