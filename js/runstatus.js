function getProjectOptions(projectOwn) {
    if (projectOwn == "1") {
        return getTableButtons("project", EDIT | REMOVE);
    } else {
        return "";
    }
}


$(document).ready(function () {

    function getPublicProfileButton() {
        var button = '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button><ul class="dropdown-menu" role="menu"><li><a class="viewrun">View Run</a></li></ul></div>';
        return button;
    }


    $('#runstatustable').on('click', '.viewrun', function (event) {
        var clickedRow = $(this).closest('tr');
        var rowData = runStatusTable.row(clickedRow).data();
        var runId = rowData.id
        if (runId !== '') {
            window.location.replace("index.php?np=3&id=" + runId);
        }
    });

    var runStatusTable = $('#runstatustable').DataTable({
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getProjectPipelines" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "id"
            }, {
            "data": "date_modified"
            }, {
            "data": "name",
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html("<a href='index.php?np=3&id=" + oData.id + "'>" + oData.name + "</a>");
            }
            }, {
            "data": "output_dir"
            }, {
            "data": "summary"
            }, {
            "data": "run_status",
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                var st = oData.run_status;
                if (st == "NextErr" || st == "Error") {
                    $(nTd).html("<a href='index.php?np=3&id=" + oData.id + "'>Error</a>");
                } else if (st == "Terminated") {
                    $(nTd).html("<a href='index.php?np=3&id=" + oData.id + "'>Terminated</a>");
                } else if (st == "NextSuc") {
                    $(nTd).html("<a href='index.php?np=3&id=" + oData.id + "'>Completed</a>");
                } else if (st == "init" || st == "Waiting") {
                    $(nTd).html("<a href='index.php?np=3&id=" + oData.id + "'>Initializing</a>");
                } else if (st == "NextRun") {
                    $(nTd).html("<a href='index.php?np=3&id=" + oData.id + "'>Running</a>");
                }
            }
            }, {
            "data": "username"
            }, {
            data: null,
            className: "center",
            fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(getPublicProfileButton());
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
