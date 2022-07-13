function getProjectOptions(projectOwn) {
    if (projectOwn == "1") {
        return getTableButtons("project", EDIT | REMOVE);
    } else {
        return "";
    }
}


$(document).ready(function() {

    initCompleteFunction = function(settings, json, tableID, columnsToSearch) {
        console.log("initCompleteFunction")
        for (var i in columnsToSearch) {
            var api = new $.fn.dataTable.Api(settings);
            $(`#${tableID}_filter`).css("display", "inline-block");
            $(`#${tableID}_searchBarST`).append(
                `<div style="margin-bottom:20px; padding-left:8px; display:inline-block;" id="${tableID}_filter-` +
                columnsToSearch[i] +
                '"></div>'
            );
            var select = $(
                    `<select id="${tableID}_select-` +
                    columnsToSearch[i] +
                    '" name="' +
                    columnsToSearch[i] +
                    '" multiple="multiple"></select>'
                )
                .appendTo($(`#${tableID}_filter-` + columnsToSearch[i]).empty())
                .attr("data-col", i)
                .on("change", function() {
                    var vals = $(this).val();
                    var valReg = "";
                    for (var k = 0; k < vals.length; k++) {
                        var val = $.fn.dataTable.util.escapeRegex(vals[k]);
                        if (val) {
                            if (k + 1 !== vals.length) {
                                valReg += val + "|";
                            } else {
                                valReg += val;
                            }
                        }
                    }
                    api
                        .column($(this).attr("data-col"))
                        .search(valReg ? "(^|,)" + valReg + "(,|$)" : "", true, false)
                        .draw();

                    //deselect rows that are selected but not visible
                    var visibleRows = $(`#${tableID}`)
                        .DataTable()
                        .rows({ search: "applied" })[0];
                    var selectedRows = $(`#${tableID}`).DataTable().rows(".selected")[0];
                    api.column($(this).attr("data-col")).rows(function(idx, data, node) {
                        if (
                            $.inArray(idx, visibleRows) === -1 &&
                            $.inArray(idx, selectedRows) !== -1
                        ) {
                            $(`#${tableID}`).DataTable().row(idx).deselect(idx);
                        }
                        return false;
                    });
                });
            var collectionList = [];
            api
                .column(i)
                .data()
                .unique()
                .sort(function(a, b) {
                    return a.toLowerCase().localeCompare(b.toLowerCase());
                })
                .each(function(d, j) {
                    if (d) {
                        var multiCol = d.split(",");
                        for (var n = 0; n < multiCol.length; n++) {
                            if (collectionList.indexOf(multiCol[n]) == -1) {
                                collectionList.push(multiCol[n]);
                                select.append(
                                    '<option value="' +
                                    multiCol[n] +
                                    '">' +
                                    multiCol[n] +
                                    "</option>"
                                );
                            }
                        }
                    }
                });

            createMultiselect = function(id, columnToSearch, apiColumn) {
                $(id).multiselect({
                    enableFiltering: true,
                    maxHeight: 400,
                    includeResetOption: true,
                    resetText: "Clear filters",
                    includeResetDivider: true,
                    enableCaseInsensitiveFiltering: true,
                    buttonText: function(options, select) {
                        if (options.length == 0) {
                            return select.attr("name") + ": All";
                        } else if (options.length > 2) {
                            return select.attr("name") + ": " + options.length + " selected";
                        } else {
                            var labels = [];
                            options.each(function() {
                                labels.push($(this).text());
                            });
                            return select.attr("name") + ": " + labels.join(", ") + "";
                        }
                    },
                });
            };

            createMultiselectBinder = function(id) {
                var resetBut = $(id).find("a.btn-block");
                resetBut.click(function() {
                    $($(id).find("select")[0]).trigger("change");
                });
            };

            createMultiselect(`#${tableID}_select-` + columnsToSearch[i]);
            createMultiselectBinder(`#${tableID}_filter-` + columnsToSearch[i]);

        }
    };

    function getRunStatusButton(oData) {
        var sendEmail = "";
        var deleteRun = "";
        var viewRun = '<li><a class="runLink">View Run</a></li>';
        if (oData.own !== "1" && usRole === "admin") {
            sendEmail = '<li><a href="#sendMailModal" data-toggle="modal">Send E-Mail to User</a></li>';
        }
        if (oData.own === "1") {
            deleteRun = '<li><a href="#confirmDelProModal" data-toggle="modal">Delete Run</a></li>';
        }
        var button = '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true"><i class="fa fa-gear"></i> <span class="fa fa-caret-down"></span></button><ul class="dropdown-menu" role="menu">' + viewRun + deleteRun + sendEmail + '</ul></div>';
        return button;
    }

    getActiveDataTable = function() {
        let tab = $("ul#statTabs li.active >a ").attr("href")
        if (tab == '#runStatusTab') return runStatusTable
        if (tab == '#autoStatusTab') return autoStatusTable
    }

    $('#sendMailModal').on('show.bs.modal', function(event) {
        $(this).find('form').trigger('reset');
        $("#sendEmailUser").attr("class", "btn btn-primary btn-block");
        $("#sendEmailUser").html("Send!");
        $("#sendEmailUser").removeAttr("disabled");
        var button = $(event.relatedTarget);
        var clickedRow = $(button).closest('tr');
        var activeTable = getActiveDataTable()
        var rowData = activeTable.row(clickedRow).data();
        var useremail = rowData.email;
        var adminemail = $("#userInfo").attr("email");
        var subject = "Regarding to your DophinNext run (" + truncateName(rowData.name, 'process') + ")";
        fillFormByName('#sendMailModal', 'input', { useremail: useremail, adminemail: adminemail, subject: subject });
    });

    $('#sendMailModal').on('hide.bs.modal', function(event) {
        cleanHasErrorClass("#sendMailModal")
    });

    $('#sendMailModal').on('click', '#sendEmailUser', function(event) {
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
                success: function(s) {
                    console.log(s)
                    if (s.status == "sent") {
                        $("#sendEmailUser").html("Your mail has sent!")
                        $("#sendEmailUser").attr("class", "btn btn-success btn-block");
                        $("#sendEmailUser").attr("disabled", "disabled");
                        setTimeout(function() {
                            $('#sendMailModal').modal('hide');
                        }, 2000);
                    } else {
                        $("#sendEmailUser").html("Your mail couldn't sent!")
                        $("#sendEmailUser").attr("class", "col-xs-12 btn btn-danger btn-load");
                        $("#sendEmailUser").attr("disabled", "disabled");
                        setTimeout(function() {
                            $("#sendEmailUser").attr("class", "btn btn-primary btn-block");
                            $("#sendEmailUser").html("Send!");
                            $("#sendEmailUser").removeAttr("disabled");
                        }, 2000);
                    }
                },
                error: function(errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });



    $(document).on('click', '.runLinkImpersonate', function(event) {
        event.preventDefault();
        var clickedRow = $(this).closest('tr');
        var activeTable = getActiveDataTable()
        var rowData = activeTable.row(clickedRow).data();
        var runId = rowData.project_pipeline_id
        var owner_id = rowData.owner_id
        var own = rowData.own
        if (runId) {
            var userData = [];
            userData.push({ name: "user_id", value: owner_id });
            userData.push({ name: "p", value: 'impersonUser' });
            $.ajax({
                type: "POST",
                data: userData,
                url: "ajax/ajaxquery.php",
                async: false,
                success: function(msg) {
                    var logInSuccess = true;
                    window.location.replace("index.php?np=3&id=" + runId);
                },
                error: function(errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });

    $(document).on('click', '.runLink', function(event) {
        event.preventDefault();
        var clickedRow = $(this).closest('tr');
        var activeTable = getActiveDataTable()
        var rowData = activeTable.row(clickedRow).data();
        var runId = rowData.project_pipeline_id
        var owner_id = rowData.owner_id
        var own = rowData.own
        if (runId) {
            window.location.replace("index.php?np=3&id=" + runId);
        }
    });

    runStatusTable = $('#runstatustable').DataTable({
        "initComplete": function(settings, json) {
            initCompleteFunction(settings, json, "runstatustable", { 2: "Pipeline", 5: "Type", 6: "Status", 8: "Owner" })
        },
        dom: `<"#runstatustable_searchBarST.pull-left"><"pull-right"f>rt<"pull-left"li><"bottom"p><"clear">`,
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getProjectPipelines" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "project_pipeline_id"
        }, {
            "data": null,
            "render": function(data, type, row) {
                var imperBut = "";
                if (row.own !== "1" && usRole === "admin") {
                    imperBut = '<button type="button" class="btn runLinkImpersonate" data-backdrop="false"  style="background: none; margin: 0px; margin-left:5px; padding-left: 0px; padding-top: 1px; display: inline;"><a style=""  data-toggle="tooltip" data-placement="bottom" data-original-title="Impersonate User"><i style="font-size:12px;" class="glyphicon  glyphicon-log-out"></i></a></button>';
                }
                return '<a href="index.php?np=3&amp;id=' + row.project_pipeline_id + '" >' + row.name + '</a>   ' + imperBut;
            }
        }, {
            data: function(data) {
                var pipeline_rev = ""
                if (data.pipeline_rev != null) {
                    pipeline_rev = " (Rev " + data.pipeline_rev + ")"
                }
                return data.pipeline_name + pipeline_rev
            },
            render: function(data, type, row) {
                var pipeline_rev = ""
                if (row.pipeline_rev != null) {
                    pipeline_rev = " (Rev " + row.pipeline_rev + ")"
                }
                return '<a href="index.php?np=1&amp;id=' + row.pipeline_id + '" >' + row.pipeline_name + pipeline_rev + '</a>';
            }
        }, {
            data: "output_dir"
        }, {
            data: null,
            render: function(data, type, row) {
                return truncateName(row.summary, 'newTable');
            }
        }, {
            data: function(data) {
                var type = "Standard"
                if (data.type && data.type == "auto") {
                    type = "Scheduled"
                } else if (data.type && data.type == "cron") {
                    if (data.cron_target_date) {
                        type = "Automated - Active"
                    } else {
                        type = "Automated - Deactived"
                    }
                }
                return type
            },
            render: function(data, type, row) {
                var runType = "Standard"
                if (row.type && row.type == "auto") {
                    runType = "Scheduled"
                } else if (row.type && row.type == "cron") {
                    if (row.cron_target_date) {
                        runType = "Automated - Active"
                    } else {
                        runType = "Automated - Deactived"
                    }
                }
                return '<span>' + runType + '</span>';
            }
        }, {
            data: function(data) {
                var st = data.run_status;
                if (st == "NextErr" || st == "Error") {
                    return 'Error';
                } else if (st == "Terminated") {
                    return 'Terminated';
                } else if (st == "NextSuc") {
                    return 'Completed';
                } else if (st == "init" || st == "Waiting") {
                    return 'Initializing';
                } else if (st == "NextRun") {
                    return 'Running';
                } else if (st == "Aborted") {
                    return 'Reconnecting';
                } else if (st == "Manual") {
                    return 'Manual';
                } else {
                    return 'Not Submitted';
                }
            },
            render: function(data, type, row) {
                var st = row.run_status;
                var href = 'href="index.php?np=3&amp;id=' + row.project_pipeline_id + '"';
                if (st == "NextErr" || st == "Error") {
                    return '<a ' + href + ' >Error</a>';
                } else if (st == "Terminated") {
                    return '<a ' + href + ' >Terminated</a>';
                } else if (st == "NextSuc") {
                    return '<a ' + href + ' >Completed</a>';
                } else if (st == "init" || st == "Waiting") {
                    return '<a ' + href + ' >Initializing</a>';
                } else if (st == "NextRun") {
                    return '<a ' + href + ' >Running</a>';
                } else if (st == "Aborted") {
                    return '<a ' + href + ' >Reconnecting</a>';
                } else if (st == "Manual") {
                    return '<a ' + href + ' >Manual</a>';
                } else {
                    return '<a ' + href + ' >Not Submitted</a>';
                }
            }
        }, {
            data: null,
            className: "center",
            render: function(data, type, row) {
                var date = ""
                if (row.run_date_created) {
                    date = row.run_date_created
                } else if (row.pp_date_created) {
                    date = row.pp_date_created
                }
                return '<span>' + date + '</span>';
            }
        }, {
            "data": "username"
        }, {
            data: null,
            className: "center",
            render: function(data, type, row) {
                return getRunStatusButton(row);
            }
        }],
        'order': [
            [7, 'desc']
        ],
        "createdRow": function(row, data, dataIndex) {
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
        "autoWidth": false,
        deferRender: true
    });


    autoStatusTable = $('#autostatustable').DataTable({
        "initComplete": function(settings, json) {
            initCompleteFunction(settings, json, "autostatustable", { 2: "Pipeline", 5: "Type", 6: "Template", 7: "Status", 9: "Owner" })
        },
        dom: `<"#autostatustable_searchBarST.pull-left"><"pull-right"f>rt<"pull-left"li><"bottom"p><"clear">`,
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getProjectPipelinesCron" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "project_pipeline_id"
        }, {
            "data": null,
            "render": function(data, type, row) {
                var imperBut = "";
                if (row.own !== "1" && usRole === "admin") {
                    imperBut = '<button type="button" class="btn runLinkImpersonate" data-backdrop="false"  style="background: none; margin: 0px; margin-left:5px; padding-left: 0px; padding-top: 1px; display: inline;"><a style=""  data-toggle="tooltip" data-placement="bottom" data-original-title="Impersonate User"><i style="font-size:12px;" class="glyphicon  glyphicon-log-out"></i></a></button>';
                }
                return '<a href="index.php?np=3&amp;id=' + row.project_pipeline_id + '" >' + row.name + '</a>   ' + imperBut;
            }
        }, {
            data: function(data) {
                var pipeline_rev = ""
                if (data.pipeline_rev != null) {
                    pipeline_rev = " (Rev " + data.pipeline_rev + ")"
                }
                return data.pipeline_name + pipeline_rev
            },
            render: function(data, type, row) {
                var pipeline_rev = ""
                if (row.pipeline_rev != null) {
                    pipeline_rev = " (Rev " + row.pipeline_rev + ")"
                }
                return '<a href="index.php?np=1&amp;id=' + row.pipeline_id + '" >' + row.pipeline_name + pipeline_rev + '</a>';
            }
        }, {
            data: "output_dir"
        }, {
            data: null,
            render: function(data, type, row) {
                return truncateName(row.summary, 'newTable');
            }
        }, {
            data: function(data) {
                var type = "Standard"
                if (data.type && data.type == "auto") {
                    type = "Scheduled"
                } else if (data.type && data.type == "cron") {
                    if (data.cron_target_date) {
                        type = "Automated - Active"
                    } else {
                        type = "Automated - Deactived"
                    }
                }
                return type
            },
            render: function(data, type, row) {
                var runType = "Standard"
                if (row.type && row.type == "auto") {
                    runType = "Scheduled"
                } else if (row.type && row.type == "cron") {
                    if (row.cron_target_date) {
                        runType = "Automated - Active"
                    } else {
                        runType = "Automated - Deactived"
                    }
                }
                return '<span>' + runType + '</span>';
            }
        }, {
            data: function(data) {
                var templateID = ""
                if (data.template_id) {
                    templateID = data.template_id
                    var allData = $('#autostatustable').DataTable().rows().data();
                    for (var i = 0; i < allData.length; i++) {
                        if (allData[i].project_pipeline_id == templateID) {
                            templateID = `${templateID} - ${allData[i].name}`
                            break
                        }
                    }
                }
                return templateID;
            },
            render: function(data, type, row) {
                var templateID = ""
                if (row.template_id) {
                    templateID = row.template_id
                    var allData = $('#autostatustable').DataTable().rows().data();
                    for (var i = 0; i < allData.length; i++) {
                        if (allData[i].project_pipeline_id == templateID) {
                            templateID = `${templateID} - ${allData[i].name}`
                            break
                        }
                    }
                }
                return '<span>' + templateID + '</span>';
            }
        }, {
            data: function(data) {
                var st = data.run_status;
                if (st == "NextErr" || st == "Error") {
                    return 'Error';
                } else if (st == "Terminated") {
                    return 'Terminated';
                } else if (st == "NextSuc") {
                    return 'Completed';
                } else if (st == "init" || st == "Waiting") {
                    return 'Initializing';
                } else if (st == "NextRun") {
                    return 'Running';
                } else if (st == "Aborted") {
                    return 'Reconnecting';
                } else if (st == "Manual") {
                    return 'Manual';
                } else {
                    return 'Not Submitted';
                }
            },
            render: function(data, type, row) {
                var st = row.run_status;
                var href = 'href="index.php?np=3&amp;id=' + row.project_pipeline_id + '"';
                if (st == "NextErr" || st == "Error") {
                    return '<a ' + href + ' >Error</a>';
                } else if (st == "Terminated") {
                    return '<a ' + href + ' >Terminated</a>';
                } else if (st == "NextSuc") {
                    return '<a ' + href + ' >Completed</a>';
                } else if (st == "init" || st == "Waiting") {
                    return '<a ' + href + ' >Initializing</a>';
                } else if (st == "NextRun") {
                    return '<a ' + href + ' >Running</a>';
                } else if (st == "Aborted") {
                    return '<a ' + href + ' >Reconnecting</a>';
                } else if (st == "Manual") {
                    return '<a ' + href + ' >Manual</a>';
                } else {
                    return '<a ' + href + ' >Not Submitted</a>';
                }
            }
        }, {
            data: null,
            className: "center",
            render: function(data, type, row) {
                var date = ""
                if (row.run_date_created) {
                    date = row.run_date_created
                } else if (row.pp_date_created) {
                    date = row.pp_date_created
                }
                return '<span>' + date + '</span>';
            }
        }, {
            "data": "username"
        }, {
            data: null,
            className: "center",
            render: function(data, type, row) {
                return getRunStatusButton(row);
            }
        }],
        'order': [
            [8, 'desc']
        ],
        "createdRow": function(row, data, dataIndex) {
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
        "autoWidth": false,
        deferRender: true
    });


    // Re-Execute initCompleteFunction when table draw is completed
    // $(document).on("xhr.dt", "#runstatustable", function(e, settings, json, xhr) {
    //     new $.fn.dataTable.Api(settings).one("draw", function() {
    //         initCompleteFunction(settings, json, "runstatustable", { 2: "Pipeline", 5: "Type", 6: "Status", 8: "Owner" });
    //     });
    // });

    //reload the table each 30 secs
    setInterval(function() {
        runStatusTable.ajax.reload(null, false);
        autoStatusTable.ajax.reload(null, false);
    }, 30000);

    // confirm Delete ssh modal 
    $('#confirmDelProModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var clickedRow = button.closest('tr');
        var activeTable = getActiveDataTable()
        var rowData = activeTable.row(clickedRow).data();
        $('#mDelProBtn').data('clickedRow', rowData);
        $('#mDelProBtn').attr('class', 'btn btn-primary deleteRun');
        $('#confirmDelProModalText').html(`Are you sure you want to delete "${rowData.name}"?`);

    });

    $('#confirmDelProModal').on('click', '.deleteRun', function(event) {
        var rowData = $('#mDelProBtn').data('clickedRow');
        var title = $('#confirmDelProModalText').html();
        var proId = rowData.project_pipeline_id;
        var data = { "id": proId, "p": "removeProjectPipeline" };
        if (proId) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: data,
                async: true,
                success: function(s) {
                    if (s.error) {
                        showInfoModal("#infoMod", "#infoModText", s.error)
                    } else {
                        runStatusTable.ajax.reload(null, false);
                        autoStatusTable.ajax.reload(null, false);
                        $('#confirmDelProModal').modal('hide');
                    }
                },
                error: function(errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });


});