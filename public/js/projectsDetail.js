$projDetScope = {
    //-------- Store data:
    getProjects : null,

    //-------- Functions:
    //Generic function to save ajax data
    getAjaxData : function(varName, getValuesObj){
        if ($projDetScope[varName] === null){
            $projDetScope[varName] = getValues(getValuesObj);
        }
        return $projDetScope[varName];
    }
}



function saveProjectIcon() {
    var data = [];
    var projectSummary = encodeURIComponent($('#projectSum').val());
    var project_id = $('#project-title').attr('projectid');
    var project_name = $('#project-title').val();
    data.push({ name: "name", value: project_name });
    data.push({ name: "id", value: project_id });
    data.push({ name: "summary", value: projectSummary });
    data.push({ name: "p", value: "saveProject" });

    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: data,
        async: true,
        success: function (s) {
            loadProjectDetails(project_id);
        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
}

function loadProjectDetails(project_id) {
    var getProjectD = [];
    getProjectD.push({ name: "id", value: project_id });
    getProjectD.push({ name: "p", value: 'getProjects' });
    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: getProjectD,
        async: true,
        success: function (s) {
            $projDetScope.getProjects = s;
            $('#project-title').val(decodeHtml(s[0].name));
            $('#ownUserName').text(s[0].username);
            $('#projectSum').val(decodeHtml(s[0].summary));

            $('#datecreatedPj').text(s[0].date_created);
            $('#lasteditedPj').text(s[0].date_modified);
            resizeForText.call($inputText, decodeHtml(s[0].name));
        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
};





function updateSideBarProPipe(project_id, project_pipeline_id, project_pipeline_name, type) {
    if (type === 'add') {
        $('#side-' + project_id).append('<li><a href="index.php?np=3&id=' + project_pipeline_id + '" class="projectItems" draggable="false" id="propipe-' + project_pipeline_id + '"><i class="fa fa-angle-double-right"></i>' + project_pipeline_name + '</a></li>');
    } else if (type === "remove") {
        $('#propipe-' + project_pipeline_id).parent().remove();
    }
}

function getRunTableOptions(proPipeOwn) {
    if (proPipeOwn == "1") {
        return getButtonsDef('selectRun', 'Run') + getTableButtons("projectrun", REMOVE);
    } else {
        return getButtonsDef('selectRun', 'Run');
    }
}

function getFilesTableOptions(proInputOwn) {
    if (proInputOwn == "1") {
        return getTableButtons("projectfile", REMOVE);
    } else {
        return "";
    }
}

$(document).ready(function () {
    var project_id = $('#project-title').attr('projectid');
    loadProjectDetails(project_id);

    var runsTable = $('#runtable').DataTable({
        "scrollY": "500px",
        "scrollCollapse": true,
        "scrollX": true,
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: {
                "project_id": project_id,
                "p": "getProjectPipelines"
            },
            "dataSrc": ""
        },
        "columns": [{
            "data": "pp_name",
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html("<a href='index.php?np=3&id=" + oData.id + "'>" + oData.pp_name + "</a>");
            }
        }, {
            "data": "name",
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html("<a href='index.php?np=1&id=" + oData.pip_id + "'>" + oData.name + "</a>");
            }
        }, {
            "data": "rev_id"
        }, {
            "data": "summary",
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                $(nTd).text(truncateName(decodeHtml(oData.summary), 'newTable'));
            }
        }, {
            "data": "username"
        }, {
            "data": "date_modified"
        }, {
            data: null,
            className: "center",
            fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(getRunTableOptions(oData.own));
            }
        }],
        'order': [[5, 'desc']]
    });

    allAvailablePipelines = getValues({ p: "getSavedPipelines" });

    function getPipeRevDropDown(pipeRowData){
        var dropdown = '<select class="btn-default pipeRevChange" prev="-1" >';
        var pipeline_gid = pipeRowData.pipeline_gid;
        var rev_id = pipeRowData.rev_id;
        var found_revs = $.grep(allAvailablePipelines, function(e) { 
            return e.pipeline_gid === pipeline_gid ; 
        });
        var highestRev = 0;

        for (var k = 0; k < found_revs.length; k++) {
            var selected = "";
            if (found_revs[k].rev_id == rev_id){
                selected = "selected";
            }
            dropdown += '<option '+selected+' value="'+found_revs[k].id+'">'+found_revs[k].rev_id+'</option>';

        }
        dropdown += '</select>';
        return dropdown
    }
    $(function () {
        $(document).on('change', ".pipeRevChange", function (e) {
            var newPipeRevId = $(this).val();
            var row = $(this).closest('tr');
            var pipeData = $.grep(allAvailablePipelines, function(e) { 
                return e.id === newPipeRevId ; 
            });
            console.log(pipeData)
            console.log(row)
            //            $('#allpipelinestable').DataTable().row( row ).data( pipeData[0] ).draw();
        });
    });

    var allpipelinestable = $('#allpipelinestable').DataTable({
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getPipelineSideBar" },
            "dataSrc": ""
        },
        "columns": [
            {
                "data": "id",
                "checkboxes": {
                    'targets': 0,
                    'selectRow': true
                }
            },
            {
                "data": "name",
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).html("<a href='index.php?np=1&id=" + oData.id + "'>" + oData.name + "</a>");
                }
            }, {
                "data": null,
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).html(getPipeRevDropDown(oData));
                }
            }, {
                "data": "summary",
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).text(truncateName(decodeHtml(oData.summary), 'newTable'));
                }
            }, {
                "data": "username"
            }, {
                "data": "date_modified"
            }],
        'select': {
            style:    'os',
            selector: 'td:first-child'
        },
        'order': [[5, 'desc']]
    });


    $('#runmodal').on('show.bs.modal', function (event) {
        allpipelinestable.column(0).checkboxes.deselect();
        $('#runmodaltitle').html('Select Pipeline to Run');

    });

    $('#runmodal').on('click', '#selectPipe', function (event) {
        event.preventDefault();
        var rows_selected = allpipelinestable.column(0).checkboxes.selected();
        if (rows_selected.length === 1) {
            $('#runNameModal').modal('show');
        }
    });


    //enter run name modal
    $('#runNameModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        if (button.attr('id') === 'selectPipe') {
            $('#runNameModaltitle').html('Enter Run Name');
        }
    });
    //save run on database
    $('#runNameModal').on('click', '#saveRun', function (event) {
        event.preventDefault();
        var allRowDom = $('#allpipelinestable').dataTable().fnGetNodes();
        var rows_selected = $('#allpipelinestable').DataTable().rows({selected:  true})
        var run_name = $('#runName').val();
        if (rows_selected.length === 1 && run_name !== '') {
            var data = [];
            for (var x = 0; x < allRowDom.length; x++) {
                var rowClass = $(allRowDom[x]).attr('class');
                if (rowClass !== undefined) {
                    if (rowClass.match(/selected/)){
                        var dropdown= $($(allRowDom[x]).children()[2]).children();
                        var $pipeline_id = dropdown.val()
                        if ($pipeline_id){
                            var projData = $projDetScope.getAjaxData("getProjects", {p:"getProjects", "id":project_id});
                            if (projData[0]){
                                if (projData[0].perms && projData[0].group_id){
                                    data.push({ name: "perms", value: projData[0].perms });
                                    data.push({ name: "group_id", value: projData[0].group_id });
                                }
                            }
                            data.push({ name: "name", value: run_name });
                            data.push({ name: "project_id", value: project_id });
                            data.push({ name: "pipeline_id", value: $pipeline_id });
                            console.log(data)
                            data.push({ name: "p", value: "saveProjectPipeline" });
                            var proPipeGet = getValues(data);
                            //add new row into the table
                            var project_pipeline_id = proPipeGet.id;
                            var getProPipeData = [];
                            getProPipeData.push({ name: "id", value: project_pipeline_id });
                            getProPipeData.push({ name: "project_id", value: project_id });
                            getProPipeData.push({ name: "p", value: "getProjectPipelines" });
                            $.ajax({
                                type: "POST",
                                url: "ajax/ajaxquery.php",
                                data: getProPipeData,
                                async: true,
                                success: function (s) {
                                    var pipelineDat = s;
                                    var rowData = {};
                                    var keys = runsTable.settings().init().columns;
                                    for (var i = 0; i < keys.length; i++) {
                                        var key = keys[i].data;
                                        rowData[key] = pipelineDat[0][key];
                                    }
                                    rowData.own = pipelineDat[0].own;
                                    rowData.pip_id = pipelineDat[0].pip_id;
                                    rowData.id = project_pipeline_id;
                                    runsTable.row.add(rowData).draw();
                                    updateSideBarProPipe(project_id, project_pipeline_id, run_name, 'add');
                                },
                                error: function (errorThrown) {
                                    alert("Error: " + errorThrown);
                                }
                            });
                            $('#runNameModal').modal('hide');
                            $('#runmodal').modal('hide');
                        }
                    }
                }
            }



        }
    });

    $("#runtable").on('click', '#projectrunremove', function (event) {
        var clickedRow = $(this).closest('tr');
        var rowData = runsTable.row(clickedRow).data();
        console.log(rowData)
        var text = 'Are you sure you want to delete this run: "'+rowData.pp_name+'" ?';
        var savedData = clickedRow;
        var execFunc = function(clickedRow){
            var runsTable = $('#runtable').DataTable();
            var rowData = runsTable.row(clickedRow).data();
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    id: rowData.id,
                    p: "removeProjectPipeline"
                },
                async: true,
                success: function (s) {
                    runsTable.ajax.reload(null, false);
                    updateSideBarProPipe("", rowData.id, "", 'remove');
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
        showConfirmDeleteModal(text, savedData, execFunc)
    });

    $(document).on('click', '.deleteProject', function (event) {
        var project_id = $('#project-title').attr('projectid');
        var project_name = $('#project-title').val();
        var text = 'Are you sure you want to delete this project: "'+project_name+'" ?';
        var proPipeGet = getValues({
            "project_id": project_id,
            "p": "getProjectPipelines"
        });
        if (proPipeGet.length){
            text += "</br></br>Following runs will be deleted as well:</br>";
            for (var i = 0; i < proPipeGet.length; i++) {
                text += "- "+ proPipeGet[i].pp_name + "</br>";
            }
        }
        var savedData = project_id;
        var execFunc = function(project_id){
            var project_name = $('#project-title').val();
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    p: "removeProject",
                    'id': project_id,
                    'name': project_name
                },
                async: true,
                success: function (s) {
                    if (s){
                        if ($.isEmptyObject(s)){
                            window.location.replace("index.php?np=2");
                        } else {
                            showInfoModal("#infoMod", "#infoModText", s)
                        }
                    }

                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
        showConfirmDeleteModal(text, savedData, execFunc)
    });



    $('#runtable').on('click', '#selectRunRun', function (e) {
        e.preventDefault();
        var clickedRow = $(this).closest('tr');
        var rowData = runsTable.row(clickedRow).data();
        var project_pipeline_id = (rowData.id);
        window.location.replace("index.php?np=3&id=" + project_pipeline_id);
    });

    var filesTable = $('#filetable').DataTable({
        "scrollY": "500px",
        "scrollCollapse": true,
        "scrollX": true,
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: {
                "project_id": project_id,
                "p": "getProjectInputs"
            },
            "dataSrc": ""
        },
        "columns": [{
            "data": "name"
        }, {
            data: null,
            className: "center",
            fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(getFilesTableOptions(oData.own));
            }
        }]
    });


    $('#filetable').on('click', '#projectfileremove', function (e) {
        e.preventDefault();
        var clickedRow = $(this).closest('tr');
        var rowData = filesTable.row(clickedRow).data();
        //get input_id from project input table
        var proInputGet = getValues({ "p": "getProjectInput", id: rowData.id });
        var input_id = proInputGet[0].input_id;
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: {
                id: rowData.id,
                input_id: input_id,
                p: "removeProjectInput"
            },
            async: true,
            success: function (s) {
                filesTable.row(clickedRow).remove().draw();
            },
            error: function (errorThrown) {
                alert("Error: " + errorThrown);
            }
        });
    });








});
