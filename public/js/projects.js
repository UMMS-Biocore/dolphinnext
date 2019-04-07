function updateSideBarProject(project_id, project_name, type) {
    if (type === 'add') {
        $('#autocompletes1').append('<li class="treeview"><a href="" draggable="false"><i  class="fa fa-circle-o"></i> <span>' + truncateName(project_name, 'sidebarMenu') + '</span><i class="fa fa-angle-left pull-right"></i></a><ul id="side-' + project_id + '" class="treeview-menu"></ul></li>');
    } else if (type === "edit") {
        $('#side-' + project_id).parent().find('span').html(truncateName(project_name, 'sidebarMenu'));
    } else if (type === "remove") {
        $('#side-' + project_id).parent().remove();
    }

}

function getProjectOptions(projectOwn) {
    if (projectOwn == "1") {
        return getTableButtons("project", EDIT | REMOVE);
    } else {
        return "";
    }
}


$(document).ready(function () {
    if (usRole === "admin") {
        $('#publicfileValpanel').css('display', 'block');
        var publicfiletable = $('#publicfiletable').DataTable({
            "scrollY": "500px",
            "scrollCollapse": true,
            "scrollX": true,
            "ajax": {
                url: "ajax/ajaxquery.php",
                data: { "p": "getPublicInputs" },
                "dataSrc": ""
            },
            "columns": [{
                "data": "name"
            }, {
                "data": "type"
            }, {
                "data": "host"
            }, {
                "data": "username"
            }, {
                "data": "date_created"
            }, {
                data: null,
                className: "center",
                fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).html(getTableButtons("public", EDIT | REMOVE));
                }
            }]
        });
    } else {
        $('#publicfileValpanel').css('display', 'none');
    }
    var projectTable = $('#projecttable').DataTable({
        "scrollY": "500px",
        "scrollCollapse": true,
        "scrollX": true,
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getProjects" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "name",
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html("<a href='index.php?np=2&id=" + oData.id + "'>" + oData.name + "</a>");
            }
            }, {
            "data": "username"
            }, {
            "data": "date_created"
            }, {
            data: null,
            className: "center",
            fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(getProjectOptions(oData.own));
            }
            }]
    });


    $('#projectmodal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        if (button.attr('id') === 'addproject') {
            $('#projectmodaltitle').html('Add New Project');
        } else {
            $('#projectmodaltitle').html('Edit Project');
            var clickedRow = button.closest('tr');
            var rowData = projectTable.row(clickedRow).data();
            $('#saveproject').data('clickedrow', clickedRow);
            var formValues = $('#projectmodal').find('input');
            $(formValues[0]).val(rowData.id);
            $(formValues[1]).val(rowData.name);

        }
    });

    $('#projectmodal').on('click', '#saveproject', function (event) {
        event.preventDefault();
        var formValues = $('#projectmodal').find('input');
        if ($('#mProjectName').val() !== '') {
            var savetype = $('#mProjectID').val();
            var data = formValues.serializeArray(); // convert form to array
            data.push({ name: "p", value: "saveProject" });
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: data,
                async: true,
                success: function (s) {
                    if (savetype.length) { //edit
                        var clickedRow = $('#saveproject').data('clickedrow');
                        var getProjectData = [];
                        getProjectData.push({ name: "id", value: savetype });
                        getProjectData.push({ name: "p", value: 'getProjects' });
                        $.ajax({
                            type: "POST",
                            url: "ajax/ajaxquery.php",
                            data: getProjectData,
                            async: true,
                            success: function (sc) {
                                var projectDat = sc;
                                var rowData = {};
                                var keys = projectTable.settings().init().columns;
                                for (var i = 0; i < keys.length; i++) {
                                    var key = keys[i].data;
                                    rowData[key] = projectDat[0][key];
                                }
                                rowData.id = projectDat[0].id;
                                rowData.own = projectDat[0].own;
                                projectTable.row(clickedRow).remove().draw();
                                projectTable.row.add(rowData).draw();
                                updateSideBarProject(projectDat[0].id, projectDat[0].name, 'edit');

                            },
                            error: function (errorThrown) {
                                alert("Error: " + errorThrown);
                            }
                        });

                    } else { //insert
                        var getProjectData = [];
                        getProjectData.push({ name: "id", value: s.id });
                        getProjectData.push({ name: "p", value: 'getProjects' });
                        $.ajax({
                            type: "POST",
                            url: "ajax/ajaxquery.php",
                            data: getProjectData,
                            async: true,
                            success: function (sc) {
                                var projectDat = sc;
                                var addData = {};
                                var keys = projectTable.settings().init().columns;
                                for (var i = 0; i < keys.length; i++) {
                                    var key = keys[i].data;
                                    addData[key] = projectDat[0][key];
                                }
                                addData.id = projectDat[0].id;
                                addData.own = projectDat[0].own;
                                projectTable.row.add(addData).draw();
                                updateSideBarProject(projectDat[0].id, projectDat[0].name, 'add');

                            },
                            error: function (errorThrown) {
                                alert("Error: " + errorThrown);
                            }
                        });
                    }

                    $('#projectmodal').modal('hide');

                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });

    $('#projecttable').on('click', '#projectremove', function (e) {
        e.preventDefault();

        var clickedRow = $(this).closest('tr');
        var rowData = projectTable.row(clickedRow).data();
        console.log(rowData);

        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: {
                id: rowData.id,
                p: "removeProject"
            },
            async: true,
            success: function (s) {
                projectTable.row(clickedRow).remove().draw();
                updateSideBarProject(rowData.id, rowData.name, 'remove');
            },
            error: function (errorThrown) {
                alert("Error: " + errorThrown);
            }
        });
    });


    //------------------------ public modal  -----------------------------
    function loadHostnames() {
        $('#mInputHost').find('option').not(':disabled').remove();
        var option = new Option("amazon", "amazon")
        $("#mInputHost").append(option);
        //get profiles for user
        var proCluData = getValues({ p: "getProfileCluster" });
        if (proCluData) {
            if (proCluData.length !== 0) {
                $.each(proCluData, function (el) {
                    var option = new Option(proCluData[el].hostname, proCluData[el].hostname)
                    $("#mInputHost").append(option);
                });

            }
        }
        var proAmzData = getValues({ p: "getProfileAmazon" });
        if (proAmzData) {
            if (proAmzData.length !== 0) {
                $.each(proAmzData, function (el) {
                    var option = new Option(proAmzData[el].shared_storage_id, proAmzData[el].shared_storage_id)
                    $("#mInputHost").append(option);
                });

            }
        }
        
    }

    $('#publicmodal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        loadHostnames();
        $(this).find('form').trigger('reset');
        if (button.attr('id') === 'addpublicFileVal') {
            $('#publicmodaltitle').html('Add Public File/Value');
        } else {
            $('#publicmodaltitle').html('Edit Public File/Value');
            var clickedRow = button.closest('tr');
            var rowData = publicfiletable.row(clickedRow).data();
            $('#savepublic').data('clickedrow', clickedRow);
            var formValues = $('#publicmodal').find('input, select');
            $(formValues[0]).val(rowData.id);
            $(formValues[1]).val(rowData.name);
            $(formValues[2]).val(rowData.type);
            $(formValues[3]).val(rowData.host);
        }
    });

    $('#publicmodal').on('click', '#savepublic', function (event) {
        event.preventDefault();
        var formValues = $('#publicmodal').find('input, select');
        if ($('#mInputName').val() !== '' && $('#mInputType').val() !== null && $('#mInputHost').val() !== null) {
            var savetype = $('#mInputID').val();
            var data = formValues.serializeArray(); // convert form to array
            data.push({ name: "p", value: "savePublicInput" });
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: data,
                async: true,
                success: function (s) {
                    if (savetype.length) { //edit
                        var clickedRow = $('#savepublic').data('clickedrow');
                        var getPublicData = [];
                        getPublicData.push({ name: "id", value: savetype });
                        getPublicData.push({ name: "p", value: 'getPublicInputs' });
                        $.ajax({
                            type: "POST",
                            url: "ajax/ajaxquery.php",
                            data: getPublicData,
                            async: true,
                            success: function (sc) {
                                var projectDat = sc;
                                var rowData = {};
                                var keys = publicfiletable.settings().init().columns;
                                for (var i = 0; i < keys.length; i++) {
                                    var key = keys[i].data;
                                    rowData[key] = projectDat[0][key];
                                }
                                rowData.id = projectDat[0].id;
                                rowData.own = projectDat[0].own;
                                publicfiletable.row(clickedRow).remove().draw();
                                publicfiletable.row.add(rowData).draw();

                            },
                            error: function (errorThrown) {
                                alert("Error: " + errorThrown);
                            }
                        });

                    } else { //insert
                        var getPublicData = [];
                        getPublicData.push({ name: "id", value: s.id });
                        getPublicData.push({ name: "p", value: 'getPublicInputs' });
                        $.ajax({
                            type: "POST",
                            url: "ajax/ajaxquery.php",
                            data: getPublicData,
                            async: true,
                            success: function (sc) {
                                var projectDat = sc;
                                var addData = {};
                                var keys = publicfiletable.settings().init().columns;
                                for (var i = 0; i < keys.length; i++) {
                                    var key = keys[i].data;
                                    addData[key] = projectDat[0][key];
                                }
                                addData.id = projectDat[0].id;
                                addData.own = projectDat[0].own;
                                publicfiletable.row.add(addData).draw();

                            },
                            error: function (errorThrown) {
                                alert("Error: " + errorThrown);
                            }
                        });
                    }
                    $('#publicmodal').modal('hide');
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });

    $('#publicfiletable').on('click', '#publicremove', function (e) {
        e.preventDefault();
        var clickedRow = $(this).closest('tr');
        var rowData = publicfiletable.row(clickedRow).data();
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: {
                id: rowData.id,
                p: "removeInput"
            },
            async: true,
            success: function (s) {
                publicfiletable.row(clickedRow).remove().draw();
            },
            error: function (errorThrown) {
                alert("Error: " + errorThrown);
            }
        });
    });









});
