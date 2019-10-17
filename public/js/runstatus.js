function getProjectOptions(projectOwn) {
    if (projectOwn == "1") {
        return getTableButtons("project", EDIT | REMOVE);
    } else {
        return "";
    }
}


$(document).ready(function () {

    function getRunStatusButton(oData) {
        var sendEmail = "";
        var viewRun = '<li><a class="runLink">View Run</a></li>';
        if (oData.own !== "1" && usRole === "admin") {
            sendEmail = '<li><a href="#sendMailModal" data-toggle="modal">Send E-Mail to User</a></li>';
        }
        var button = '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button><ul class="dropdown-menu" role="menu">' + viewRun + sendEmail + '</ul></div>';
        return button;
    }

    $('#sendMailModal').on('show.bs.modal', function (event) {
        $(this).find('form').trigger('reset');
        $("#sendEmailUser").attr("class", "btn btn-primary btn-block");
        $("#sendEmailUser").html("Send!");
        $("#sendEmailUser").removeAttr("disabled");
        var button = $(event.relatedTarget);
        var clickedRow = $(button).closest('tr');
        var rowData = runStatusTable.row(clickedRow).data();
        var useremail = rowData.email;
        var adminemail = $("#userInfo").attr("email");
        var subject = "Regarding to your DophinNext run (" + truncateName(rowData.name, 'process') + ")";
        fillFormByName('#sendMailModal', 'input', { useremail: useremail, adminemail: adminemail, subject: subject });
    });

    $('#sendMailModal').on('hide.bs.modal', function (event) {
        cleanHasErrorClass("#sendMailModal")
    });

    $('#sendMailModal').on('click', '#sendEmailUser', function (event) {
        event.preventDefault();
        var formValues = $('#sendMailModal').find('input, textarea');
        var requiredFields = ["adminemail", "useremail", "message"];
        var formObj = {};
        var stop = "";
        [formObj, stop] = createFormObj(formValues, requiredFields)
        if (stop === false) {
            formObj.p = "sendEmail"
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: formObj,
                async: true,
                success: function (s) {
                    console.log(s)
                    if (s.status == "sent") {
                        $("#sendEmailUser").html("Your mail has sent!")
                        $("#sendEmailUser").attr("class", "btn btn-success btn-block");
                        $("#sendEmailUser").attr("disabled", "disabled");
                        setTimeout(function () {
                            $('#sendMailModal').modal('hide');
                        }, 2000);
                    } else {
                        $("#sendEmailUser").html("Your mail couldn't sent!")
                        $("#sendEmailUser").attr("class", "col-xs-12 btn btn-danger btn-load");
                        $("#sendEmailUser").attr("disabled", "disabled");
                        setTimeout(function () {
                            $("#sendEmailUser").attr("class", "btn btn-primary btn-block");
                            $("#sendEmailUser").html("Send!");
                            $("#sendEmailUser").removeAttr("disabled");
                        }, 2000);
                    }
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });



    $('#runstatustable').on('click', '.runLink', function (event) {
        event.preventDefault();
        var clickedRow = $(this).closest('tr');
        var rowData = runStatusTable.row(clickedRow).data();
        var runId = rowData.project_pipeline_id
        var owner_id = rowData.owner_id
        var own = rowData.own
        if (runId !== '') {
            if ((usRole === "admin" && own == "1") || usRole !== "admin") {
                window.location.replace("index.php?np=3&id=" + runId);
            } else if (usRole === "admin" && own !== "1") {
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
            "data": null,
            "render": function (data, type, row) {
                var href = "";
                if (row.own === "1" || usRole === "admin") {
                    href = 'href=""';
                }
                return '<a ' + href + ' class="runLink">' + row.name + '</a>';
            }
        }, {
            data: null,
            render: function (data, type, row) {
                var pipeline_rev = ""
                if (row.pipeline_rev != null){
                    pipeline_rev =  " (Rev " + row.pipeline_rev + ")"
                }
                return '<a href="index.php?np=1&amp;id=' + row.pipeline_id + '" >' + row.pipeline_name + pipeline_rev + '</a>';
            }
        }, {
            data: "output_dir"
        },  {
            data: null,
            render: function (data, type, row) {
                return truncateName(row.summary, 'newTable');
            }
        }, {
            data: null,
            render: function ( data, type, row ) {
                var st = row.run_status;
                var href = "";
                if (row.own === "1" || usRole === "admin") {
                    href = 'href=""';
                }
                if (st == "NextErr" || st == "Error") {
                    return '<a ' + href + ' class="runLink">Error</a>';
                } else if (st == "Terminated") {
                    return '<a ' + href + ' class="runLink">Terminated</a>';
                } else if (st == "NextSuc") {
                    return '<a ' + href + ' class="runLink">Completed</a>';
                } else if (st == "init" || st == "Waiting") {
                    return '<a ' + href + ' class="runLink">Initializing</a>';
                } else if (st == "NextRun") {
                    return '<a ' + href + ' class="runLink">Running</a>';
                } else if (st == "Aborted") {
                    return '<a ' + href + ' class="runLink">Reconnecting</a>';
                } else if (st == "Manual") {
                    return '<a ' + href + ' class="runLink">Manual</a>';
                } else {
                    return '<a ' + href + ' class="runLink">Not Submitted</a>';
                }
            }
        },  {
            data: null,
            className: "center",
            render: function ( data, type, row ) {
                var date="" 
                if (row.run_date_created){
                    date= row.run_date_created
                } else if (row.pp_date_created) {
                    date= row.pp_date_created
                }
                return '<span>'+date+'</span>';
            }
        },{
            "data": "username"
        }, {
            data: null,
            className: "center",
            render: function (data, type, row) {
                return getRunStatusButton(row);
            }
        }
                   ],
        'order': [[6, 'desc']],
        "createdRow": function (row, data, dataIndex) {
            var st = data.run_status;
            if (st == "NextErr" || st == "Error") {
                $(row).css("background-color", "#F1DEDE");
            } else if (st == "Terminated" || st == "Aborted") {
                $(row).css("background-color", "#e2e2e2");
            } else if (st == "NextSuc") {
                $(row).css("background-color", "#DFEFD8");
            } else if (st == "init" || st == "Waiting" || st == "NextRun") {
                $(row).css("background-color", "#D8EDF6");
            } else if (st == "Manual") {
                $(row).css("background-color", "#dcdbfc");
            } else {
                $(row).css("background-color", "#f4f4f4");
            }
        },
        sScrollX: "100%"
    });


    //reload the table each 30 secs
    setInterval(function () {
        runStatusTable.ajax.reload(null,false);
    }, 30000);



});
