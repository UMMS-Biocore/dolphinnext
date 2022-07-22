<div class="row">
    <div class="col-md-6" bis_skin_checked="1">
        <div class="box box-primary" bis_skin_checked="1" style="border-top-width:7px;">
            <div class="box-header with-border" bis_skin_checked="1">
                <h3 class="box-title" id="runStatsByPipelineTitle"></h3>
            </div>
            <div class="box-body" bis_skin_checked="1">
                <div class="row" bis_skin_checked="1">
                    <div class="col-md-10" bis_skin_checked="1">
                        <canvas id="runStatsByPipelineChart" width="400" height="250"></canvas>
                    </div>
                    <div class="col-md-2" bis_skin_checked="1">
                        <select id="runsByPipelineUser" style="display:none;" name="User" multiple="multiple"></select>
                        <div style="margin-top:5px;">
                            <select id="runsByPipelineGroup" style="display:none; margin-top:10px;" name="Lab" multiple="multiple"></select>
                        </div>
                        <div style="margin-top:5px;">
                            <select id="runsByPipelineData" class="btn-default" style="width:96px; font-size: 14px; padding: 5px 8px;">
                                <option value="run">Data: Run</option>
                                <option value="run_attempt">Data: Run Attempt</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6" bis_skin_checked="1">
        <div class="box box-primary" bis_skin_checked="1" style="border-top-width:7px;">
            <div class="box-header with-border" bis_skin_checked="1">
                <h3 class="box-title" id="runStatsByUserTitle"></h3>
            </div>
            <div class="box-body" bis_skin_checked="1">
                <div class="row" bis_skin_checked="1">
                    <div class="col-md-9" bis_skin_checked="1">
                        <canvas id="runStatsByUserChart" width="400" height="250"></canvas>
                    </div>
                    <div class="col-md-3" bis_skin_checked="1">
                        <select class="btn-default" style="width:100%; font-size: 14px; padding: 5px 8px;" id="runsByUser">
                            <option value="oname">Group By: User</option>
                            <option value="olab">Group By: Lab</option>
                        </select>
                        <div style="margin-top:5px;">
                            <select id="runStatsByUserData" class="btn-default" style="width:100%; font-size: 14px; padding: 5px 8px;">
                                <option value="run">Data: Run</option>
                                <option value="run_attempt">Data: Run Attempt</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6" bis_skin_checked="1">
        <div class="box box-primary" style="border-top-width:7px;">
            <div class="box-header with-border" bis_skin_checked="1">
                <h3 class="box-title">Processed Samples by Pipeline</h3>
            </div>
            <div class="box-body" bis_skin_checked="1">
                <div class="row" bis_skin_checked="1">
                    <div class="col-md-12" bis_skin_checked="1">
                        <canvas id="fileStatsByPipeline" width="400" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6" bis_skin_checked="1">
        <div class="box box-success" bis_skin_checked="1" style="border-top-width:7px;">
            <div class="box-header with-border" bis_skin_checked="1">
                <h3 class="box-title">Average Successful Run Time</h3>
            </div>
            <div class="box-body" bis_skin_checked="1">
                <div class="row" bis_skin_checked="1">
                    <div class="col-md-12" bis_skin_checked="1">
                        <canvas id="runStatsAvgRun" width="400" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6" bis_skin_checked="1">
        <div class="box box-danger" bis_skin_checked="1" style="border-top-width:7px;">
            <div class="box-header with-border" bis_skin_checked="1">
                <h3 class="box-title">Total Users</h3>
            </div>
            <div class="box-body" bis_skin_checked="1">
                <div class="row" bis_skin_checked="1">
                    <div class="col-md-12" bis_skin_checked="1">
                        <canvas id="runStatsTotalUsers" width="400" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>