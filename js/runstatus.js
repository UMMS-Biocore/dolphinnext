function getProjectOptions(projectOwn) {
    if (projectOwn == "1") {
        return getTableButtons("project", EDIT | REMOVE);
    } else {
        return "";
    }
}


$(document).ready(function () {

    function getRunStatusButton(oData) {
        var viewRun = "";
        var sendEmail = "";
        console.log(oData)
        if (oData.own === "1" || usRole === "admin") {
            viewRun = '<li><a class="runLink">View Run</a></li>';
        } 
        if (oData.own !== "1" && usRole === "admin") {
            sendEmail = '<li><a href="#sendMailModal" data-toggle="modal">Send E-Mail to User</a></li>';
        }
        var button = '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button><ul class="dropdown-menu" role="menu">' + viewRun + sendEmail +'</ul></div>';
        return button;
    }

    $('#sendMailModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        console.log(button)
//        var clickedRow = $(this).closest('tr');
//        var rowData = runStatusTable.row(clickedRow).data();
//        var owner_id = rowData.owner_id;
//        console.log(owner_id)
        
    });

    $('#runstatustable').on('click', '.runLink', function (event) {
        event.preventDefault();
        var clickedRow = $(this).closest('tr');
        var rowData = runStatusTable.row(clickedRow).data();
        var runId = rowData.project_pipeline_id
        var owner_id = rowData.owner_id
        var own = rowData.own
        if (runId !== '' && own === "1") {
            window.location.replace("index.php?np=3&id=" + runId);
        } else if (usRole === "admin") {
            var userData = [];
            userData.push({ name: "user_id", value: owner_id });
            userData.push({ name: "p", value: 'impersonUser' });
            $.ajax({
                type: "POST",
                data: userData,
                url: "ajax/ajaxquery.php",
                async: false,
                success: function (msg) {
                    var logInSuccess = true;
                    window.location.replace("index.php?np=3&id=" + runId);
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });

    runStatusTable = $('#runstatustable').DataTable({
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getProjectPipelines" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "project_pipeline_id"
            }, {
            "data": "date_modified"
            }, {
            "data": "name",
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                var href = "";
                if (oData.own === "1" || usRole === "admin") {
                    href = 'href=""';
                }
                $(nTd).html('<a ' + href + ' class="runLink">' + oData.name + "</a>");
            }
            }, {
            "data": "output_dir"
            }, {
            "data": "summary"
            }, {
            "data": "run_status",
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                var st = oData.run_status;
                var href = "";
                if (oData.own === "1" || usRole === "admin") {
                    href = 'href=""';
                }
                if (st == "NextErr" || st == "Error") {
                    $(nTd).html('<a ' + href + ' class="runLink">Error</a>');
                } else if (st == "Terminated") {
                    $(nTd).html('<a ' + href + ' class="runLink">Terminated</a>');
                } else if (st == "NextSuc") {
                    $(nTd).html('<a ' + href + ' class="runLink">Completed</a>');
                } else if (st == "init" || st == "Waiting") {
                    $(nTd).html('<a ' + href + ' class="runLink">Initializing</a>');
                } else if (st == "NextRun") {
                    $(nTd).html('<a ' + href + ' class="runLink">Running</a>');
                }
            }
            }, {
            "data": "date_created"
            }, {
            "data": "username"
            }, {
            data: null,
            className: "center",
            fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(getRunStatusButton(oData));
            }
            }],
        'order': [[1, 'desc']],
        "columnDefs": [
            {
                'targets': [1],
                'visible': false,
                'searchable': false
            },
        ],
        "createdRow": function (row, data, dataIndex) {
            var st = data.run_status;
            if (st == "NextErr" || st == "Error") {
                $(row).css("background-color", "#F1DEDE");
            } else if (st == "Terminated") {
                $(row).css("background-color", "#e2e2e2");
            } else if (st == "NextSuc") {
                $(row).css("background-color", "#DFEFD8");
            } else if (st == "init" || st == "Waiting" || st == "NextRun") {
                $(row).css("background-color", "#D8EDF6");
            }
        }
    });

    //reload the table each 30 secs
    setInterval(function () {
        runStatusTable.ajax.reload();
    }, 30000);



});
