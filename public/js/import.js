// Configuriation of dropzone of id:importArea in importModal
window.importObj = {};
window.importObj.filename = [];
Dropzone.options.importArea = {
    paramName: "file", // The name that will be used to transfer the file
    maxFilesize: 2, // MB
    acceptedFiles: ".dn",
    dictDefaultMessage: 'Drop files here or <button type="button" class="btn btn-default" >Select File </button>',
    accept: function(file, done) {
        window.importObj.filename.push(file.name)
        done();
        $('#nextButton').prop("disabled", false);
    }
};

renderMenuGroup = {
    option: function(data, escape) {
        return '<div class="option">' +
            '<span class="title"><i>' + escape(data.group_name) + '</i></span>' +
            '</div>';
    },
    item: function(data, escape) {
        return '<div class="item" data-value="' + escape(data.id) + '">' + escape(data.group_name) + '</div>';
    }
};

function createSelectize(rowClass, dropdownID) {
    if (rowClass == "Process") {
        var allMenuGroup = window.ajaxData.processMenuGroup;
    } else if (rowClass == "Pipeline") {
        var allMenuGroup = window.ajaxData.pipelineMenuGroup;
    }
    $('#' + dropdownID).selectize({
        valueField: 'id',
        searchField: ['group_name'],
        options: allMenuGroup,
        render: renderMenuGroup,
        create: function(input, callback) {
            $.ajax({
                url: "ajax/ajaxquery.php",
                data: { p: "save" + rowClass + "Group", group_name: input },
                type: 'POST',
                success: function(result) {
                    if (result) {
                        callback({ id: result.id, group_name: input });
                        refreshSelectize(rowClass, null)
                    }
                }
            });
        }
    });

}


function refreshSelectize(rowClass, newId) {
    var dropdownlist = $("#importModalPart2").find("select[name='" + rowClass + "_menu_group']")
    if (rowClass == "Process") {
        window.ajaxData.processMenuGroup = getValues({ p: "getAllProcessGroups" });
    } else if (rowClass == "Pipeline") {
        window.ajaxData.pipelineMenuGroup = getValues({ p: "getPipelineGroup" });
    }
    for (var i = 0; i < dropdownlist.length; i++) {
        var dropId = dropdownlist[i].getAttribute('id');
        var selectizecheck = $('#' + dropId)[0].selectize;
        if (selectizecheck) {
            var valueID = $('#' + dropId)[0].selectize.getValue();
            if (!valueID && newId) {
                valueID = newId
            }
            $('#' + dropId).selectize()[0].selectize.destroy();
            createSelectize(rowClass, dropId)
            $('#' + dropId)[0].selectize.setValue(valueID, false);
        }
    }
}

//rowClass:Process/Pipeline
function activateSelectizeMenuGroup(rowClass, dropdownID, menu_group_name, fileId) {
    refreshSelectize(rowClass, null)
    createSelectize(rowClass, dropdownID);
    var valueList = $('#' + dropdownID)[0].selectize.search(menu_group_name).items;
    //select menu group if exist
    if (valueList.length > 0) {
        if (valueList.length == 1) {
            var valueID = valueList[0].id;
            $('#' + dropdownID)[0].selectize.setValue(valueID, false);
        } else {
            for (var i = 0; i < valueList.length; i++) {
                var eachValue = $('#' + dropdownID)[0].selectize.options[valueList[i].id];
                if (eachValue) {
                    var eachValueName = eachValue.group_name
                    if (eachValueName.match('^' + menu_group_name + '$')) {
                        $('#' + dropdownID)[0].selectize.setValue(valueList[i].id, false);
                        break
                    }
                }
            }
        }
    } else {
        $.ajax({
            url: "ajax/ajaxquery.php",
            data: { p: "save" + rowClass + "Group", group_name: menu_group_name },
            type: 'POST',
            success: function(result) {
                if (result) {
                    refreshSelectize(rowClass, result.id)
                }
            }
        });
    }
}

//rowClass:Process/Pipeline rowType:process/pipeModule/pipeline
function getRowImportTable(rowType, rowClass, fileId, blockID, givenName, menu_group_name, pipeline_uuid) {
    var rowID = rowType + fileId + '_' + blockID;
    var dropdownID = "menuGroup_" + rowID;
    var pipeRevDrop = '<select id="' + dropdownID + '" class="fbtn btn-default form-control" defVal="' + menu_group_name + '" name="' + rowClass + '_menu_group"></select>';
    setTimeout(function() { activateSelectizeMenuGroup(rowClass, dropdownID, menu_group_name, fileId); }, 100);
    return '<tr id="' + rowID + '" type="' + rowType + '" fileID="' + fileId + '"><td scope="row">' + givenName + '</td><td>' + pipeRevDrop + '</td><td id="stat_' + rowID + '"><i class="fa fa-spinner fa-spin"></i>  Processing..</td></tr>'
}

function keyChecker(keyList, obj1, obj2) {
    var checkObj = {};
    for (var i = 0; i < keyList.length; i++) {
        var keyName = keyList[i];
        if (obj1[keyName] && obj2[keyName]) {
            if (obj1[keyName] === obj2[keyName]) {
                checkObj[keyName] = true;
            } else {
                checkObj[keyName] = "<<<<<<" + keyName + " not matched:\n" + obj1[keyName] + "\n======\n" + obj2[keyName] + "\n>>>>>>\n";
            }
        } else if (obj1[keyName]) {
            checkObj[keyName] = "<<<<<<" + keyName + " not matched:\n" + obj1[keyName] + "\n======\n" + ">>>>>>\n";
        } else if (obj2[keyName]) {
            checkObj[keyName] = "<<<<<<" + keyName + " not matched:\n" + "\n======\n" + obj2[keyName] + "\n>>>>>>\n";
        } else {
            checkObj[keyName] = true;
        }
    }
    return checkObj
}

function checkIfEqual(type, importJSON, dbJSON, fileID) {
    console.log(type)
    var checkObj = {};
    if (type == "process") {
        checkObj = keyChecker(["script", "script_footer", "script_header"], importJSON, dbJSON)
    } else if (type == "process_parameter_in" || type == "process_parameter_out") {
        //prep of importJSON and dbJSON
        for (var i = 0; i < importJSON.length; i++) {
            importJSON[i] = decodeElement("process_parameter", importJSON[i]) || {};
            if (importJSON[i].id) {
                window.importObj[fileID].dict.propara[importJSON[i].id] = "insert"; //default value is insert/ Will be replaced by dbJSONfilt[i].id; If not replaced that means it will stay as "insert"
            }
        }
        for (var i = 0; i < dbJSON.length; i++) {
            dbJSON[i] = decodeElement("process_parameter", dbJSON[i]) || {}
        }
        //check. order of propara should match
        for (var i = 0; i < importJSON.length; i++) {
            var dbParamId = window.importObj[fileID].dict.parameter[importJSON[i].parameter_id];
            if (dbParamId) {
                if (dbJSON[i] && dbJSON[i].parameter_id == dbParamId && dbJSON[i].sname == importJSON[i].sname) {
                    //pro para match found = update this item
                    if (dbJSON[i].id) {
                        window.importObj[fileID].dict.propara[importJSON[i].id] = dbJSON[i].id; //default value ("insert") now being replaced 
                    }
                    checkObj[type + i] = keyChecker(["sname", "operator", "closure", "reg_ex", "optional", "file_type", "qualifier"], importJSON[i], dbJSON[i])
                }

            }
        }
        //find redundant propara
        for (var k = 0; k < dbJSON.length; k++) {
            i++;
            if (dbJSON[k].id) {
                var checkExistObj = filterObjVal(window.importObj[fileID].dict.propara, dbJSON[k].id)
                if ($.isEmptyObject(checkExistObj)) {
                    window.importObj[fileID].redundant_propara[dbJSON[k].id] = { p: "removeProcessParameter", id: dbJSON[k].id };
                    checkObj[type + i] = {};
                    checkObj[type + i]["redundant_process_parameter"] = "<<<<<< Redundant process parameter(" + dbJSON[k].name + ") will be removed. >>>>>>\n";
                }
            }
        }

    } else if (type == "pipeline") {
        var checkObj2 = {};
        // remove process/parameter specific regions
        var importJSONcp = $.extend(true, {}, importJSON);
        var dbJSONcp = $.extend(true, {}, dbJSON);
        importJSONcp = removeSpecific(importJSONcp)
        dbJSONcp = removeSpecific(dbJSONcp)
        checkObj2 = keyChecker(["nodes", "edges", "mainG"], importJSONcp, dbJSONcp)
        checkObj = keyChecker(["summary", "script_pipe_footer", "script_pipe_header", "script_pipe_config", "name"], importJSON, dbJSON)
        jQuery.extend(checkObj, checkObj2);
    } else if (type == "pipeline_strict") {
        var checkObj2 = {};
        // remove process/parameter specific regions
        var importJSONcp = $.extend(true, {}, importJSON);
        var dbJSONcp = $.extend(true, {}, dbJSON);
        importJSONcp = prepareCompare(importJSONcp, fileID, "importJSON")
        dbJSONcp = prepareCompare(dbJSONcp, fileID, "dbJSON")
        checkObj2 = keyChecker(["nodes", "edges", "mainG"], importJSONcp, dbJSONcp)
        checkObj = keyChecker(["summary", "script_pipe_footer", "script_pipe_header", "script_pipe_config", "name"], importJSON, dbJSON)
        jQuery.extend(checkObj, checkObj2);
    }
    return checkObj;
}


function prepareCompare(obj, fileID, mode) {
    if (mode == "importJSON") {
        //nodes
        obj.nodes = encodeNodes(obj.nodes, fileID)
        obj.nodes = JSON.stringify(obj.nodes)
            //edges
        obj.edges = encodeEdges(obj.edges, fileID)
        obj.edges = JSON.stringify(obj.edges)
    } else if (mode == "dbJSON") {
        obj.nodes = JSON5.parse(obj.nodes)
        obj.nodes = JSON.stringify(obj.nodes)
        obj.edges = JSON5.parse(obj.edges)["edges"]
        obj.edges = JSON.stringify(obj.edges)
    }

    return obj
}

// first check checkIfEqual will be less sensitive to process/module id's
//second check will be done after import.
function removeSpecific(obj) {
    //nodes
    obj.nodes = JSON5.parse(obj.nodes)
    $.each(obj.nodes, function(el) {
        if (obj.nodes[el]) {
            var proPipeID = obj.nodes[el][2];
            if (proPipeID != "outPro" && proPipeID != "inPro") {
                if (proPipeID.match(/p/)) {
                    obj.nodes[el][2] = "p"
                } else {
                    obj.nodes[el][2] = ""
                }
            }
        }
    });
    obj.nodes = JSON.stringify(obj.nodes)
        //edges
    var final = []
    obj.edges = JSON5.parse(obj.edges)["edges"]
    for (var i = 0; i < obj.edges.length; i++) {
        if (obj.edges[i]) {
            var eds = obj.edges[i].split("_")
            if (eds) {
                if (eds.length == 2) {
                    var eds0 = eds[0].split("-")
                    var eds1 = eds[1].split("-")
                    if (eds0[1] != "outPro" && eds0[1] != "inPro") {
                        eds0[1] = ""
                    }
                    if (eds1[1] != "outPro" && eds1[1] != "inPro") {
                        eds1[1] = ""
                    }
                    eds0[3] = ""
                    eds1[3] = ""
                    final.push(eds0.join("-") + "_" + eds1.join("-"))
                }
            }
        }
    }
    obj.edges = final.join(", ")
    return obj
}

function sumObj(obj) {
    var status = true;
    var txt = "";
    $.each(obj, function(el) {
        $.each(obj[el], function(e) {
            if (obj[el][e] !== true) {
                status = "warnUser";
                txt += obj[el][e];
            }
        });
    });
    return [status, txt]
}

function createImportReport(obj) {
    var status;
    var txt;
    [status, txt] = sumObj(obj)
    return [status, txt]
}



$('#importButton').on('click', function(e) {
    e.preventDefault();
    $('#importButton').css("display", "none");
    $('#compButton').css("display", "inline");
    var processRowList = $("#importModalPart2").find("tr[type='process']")
    var pipeModList = $("#importModalPart2").find("tr[type='pipeModule']")
    var pipeList = $("#importModalPart2").find("tr[type='pipeline']")
    var pipeRowList = $.merge(pipeModList, pipeList)
        //first insert missing_parameters
    var fileList = window.importObj.filename;
    for (var fileID = 0; fileID < fileList.length; fileID++) {
        var missing_parameters = window.importObj[fileID].missing_parameters
        if (missing_parameters) {
            $.each(missing_parameters, function(el) {
                var commandLog = getValues(missing_parameters[el]);
                if ((commandLog && commandLog.id)) {
                    var newParameterID = commandLog.id;
                    window.importObj[fileID].dict.parameter[el] = newParameterID;
                } else {
                    window.importObj["importStatus"] = "stopped";
                    showInfoModal("#infoMod", "#infoModText", "parameter insert (id:" + el + ") was not successful:" + JSON.stringify(missing_parameters[el]))
                    return;
                }
            });
        }
    }

    function removeRedundantProPara() {
        for (var fileid = 0; fileid < fileList.length; fileid++) {
            var redundant_propara = window.importObj[fileid].redundant_propara
            if (redundant_propara) {
                $.each(redundant_propara, function(el) {
                    $.ajax({
                        type: "POST",
                        url: "ajax/ajaxquery.php",
                        data: redundant_propara[el],
                        async: false,
                        success: function(s) {
                            console.log("Removal of process parameter is successfully completed")
                        },
                        error: function(errorThrown) {
                            window.importObj["importStatus"] = "stopped";
                            showInfoModal("#infoMod", "#infoModText", "Error: " + errorThrown + "Removal of process parameter (id:" + el + ") was not successful:" + JSON.stringify(redundant_propara[el]))
                        }
                    });
                });
            }
        }
    }

    var indexCache;
    //loop through each row for process/pipeline module/pipeline
    function loop(type, list) {
        if (window.importObj["importStatus"]) {
            if (window.importObj["importStatus"] == "stopped") {
                return;
            }
        }
        var index = indexCache || 0;
        for (var i = index; i < list.length; i++) {
            var parBoxId = list[i].getAttribute('id');
            var fileID = list[i].getAttribute('fileID');
            var command = window.importObj[parBoxId].command;
            if (type == "process") {
                var oldID = window.importObj[parBoxId]["oldProcessId"];
            } else if (type == "pipeline" || type == "pipeline_strict") {
                var oldID = window.importObj[parBoxId]["oldPipelineId"];
            }
            var warnUser = window.importObj[parBoxId]["warnUser"];
            window.importObj[parBoxId]["pass"] = true;
            if (warnUser && !window.importObj[parBoxId]["skipWarnUserCheck"]) {
                window.importObj[parBoxId]["pass"] = false;
                indexCache = i;
                $('#warnUserImport').on('show.bs.modal', function(event) {
                    $(this).find('form').trigger('reset');
                    $("#warnUserText").multiline(warnUser);

                });
                $('#warnUserImport').on('hide.bs.modal', function(event) {
                    var tmpid = $(document.activeElement).attr('id');
                    if (tmpid === "saveOnExistImport") {
                        window.importObj[parBoxId]["skipWarnUserCheck"] = true;
                    } else {
                        window.importObj["importStatus"] = "stopped"
                        $('#compButton').css("display", "none");
                    }
                });
                $('#warnUserImport').modal('show').one('hidden.bs.modal', function() {
                    loop(type, list)
                })
                return;
            }
            if (command && window.importObj[parBoxId]["pass"]) {
                var commandID = command.id
                command[type + "_group_id"] = $("#menuGroup_" + parBoxId)[0].selectize.getValue()
                command = encodeElement(type, command, fileID)
                if (window.importObj["importStatus"]) {
                    if (window.importObj["importStatus"] == "stopped") {
                        rowUpdateStatus(parBoxId, "Error Occured ", "error");
                        throw new Error("Something went wrong!");
                    }
                }
                var commandLog = null
                    //insert process/pipeline
                var commandLog = getValues(command);
                if ((commandLog && commandLog.id) || commandID) {
                    var newID = commandLog.id;
                    if (oldID && newID) {
                        window.importObj[fileID].dict[type][oldID] = newID
                    } else if (window.importObj[fileID].dict[type][oldID]) {
                        newID = window.importObj[fileID].dict[type][oldID];
                    }
                    if (type == "process") {
                        //insert process parameters
                        var commandProPara = window.importObj[parBoxId].commandProPara;
                        if (commandProPara) {
                            for (var k = 0; k < commandProPara.length; k++) {
                                var commandProParaID = commandProPara[k].id
                                commandProPara[k].process_id = newID;
                                commandProPara[k].perms = command.perms; //taken from process perms
                                commandProPara[k].parameter_id = window.importObj[fileID].dict.parameter[commandProPara[k].parameter_id]
                                var commandProParaLog = null
                                var commandProParaLog = getValues(commandProPara[k]);
                                if ((commandProParaLog && commandProParaLog.id) || commandProParaID) {
                                    var newProParaID = commandProParaLog.id;
                                } else {
                                    rowUpdateStatus(parBoxId, "Error Occured ", "error");
                                    window.importObj["importStatus"] == "stopped"
                                    throw new Error("Something went wrong!");
                                }
                            }
                        }
                    }
                    rowUpdateStatus(parBoxId, "Imported ", "ok");
                } else {
                    rowUpdateStatus(parBoxId, "Error Occured ", "error");
                    window.importObj["importStatus"] == "stopped"
                    throw new Error("Something went wrong!");
                }
            } else {
                if (window.importObj[fileID].dict[type][oldID]) {
                    rowUpdateStatus(parBoxId, "Done ", "ok");
                } else {
                    rowUpdateStatus(parBoxId, "Error Occured ", "error");
                    window.importObj["importStatus"] == "stopped"
                    throw new Error("Something went wrong!");
                }
            }
            if (type == "process" && i == list.length - 1) {
                removeRedundantProPara();
                indexCache = 0;
                loop("pipeline", pipeRowList);
            } else if (type == "pipeline" && i == list.length - 1) {
                indexCache = 0;
                //strict validation for pipelines
                validatePipeImportBlock(fileID);
                loop("pipeline_strict", pipeRowList);
            } else if (type == "pipeline_strict" && i == list.length - 1) {
                var parBoxId = list[list.length - 1].getAttribute('id');
                var oldID = window.importObj[parBoxId]["oldPipelineId"];
                var newID = window.importObj[fileID].dict[type][oldID]
                window.importObj[fileID].dict["lastpipeline"] = newID
                window.importObj["importStatus"] = "finalized";
            }
        }
    }
    loop("process", processRowList);
});

function prepareSendJSON(type, sendJSON, importJSON, allParameters, fileID, rowID, dbJSON) {
    if (type == "process") {
        if (dbJSON) {
            importJSON.summary = switchHostSpecific(importJSON.summary, dbJSON.summary)
            importJSON.script = switchHostSpecific(importJSON.script, dbJSON.script)
            importJSON.script_footer = switchHostSpecific(importJSON.script_footer, dbJSON.script_footer)
            importJSON.script_header = switchHostSpecific(importJSON.script_header, dbJSON.script_header)
        } else {
            importJSON.summary = removePlatform(importJSON.summary)
            importJSON.script = removePlatform(importJSON.script)
            importJSON.script_footer = removePlatform(importJSON.script_footer)
            importJSON.script_header = removePlatform(importJSON.script_header)
        }
        sendJSON.p = "saveProcess"
        sendJSON.process_uuid = importJSON.process_uuid;
        sendJSON.process_rev_uuid = importJSON.process_rev_uuid;
        sendJSON.process_group_id = "" //modify later
        sendJSON.name = importJSON.name
        sendJSON.script = importJSON.script
        sendJSON.script_footer = importJSON.script_footer
        sendJSON.script_header = importJSON.script_header
        sendJSON.summary = importJSON.summary
        sendJSON.script_mode = importJSON.script_mode
        sendJSON.script_mode_header = importJSON.script_mode_header
        sendJSON.rev_comment = "imported";
        sendJSON.group = ""
        sendJSON.publicly_searchable = "false";
        if (importJSON.perms == 15) {
            sendJSON.perms = 3;
        } else {
            sendJSON.perms = importJSON.perms;
        }
    } else if (type == "process_parameter_input" || type == "process_parameter_output") {
        for (var i = 0; i < importJSON.length; i++) {
            var oldParameterId = importJSON[i].parameter_id;
            var parameterCheck = allParameters.filter(function(el) {
                return (el.file_type == importJSON[i].file_type && el.qualifier == importJSON[i].qualifier && el.name == importJSON[i].name)
            });
            var newParameterId = "";
            if (parameterCheck && parameterCheck.length > 0) {
                newParameterId = parameterCheck[0].id;
                window.importObj[fileID].dict.parameter[oldParameterId] = newParameterId;
            } else {
                window.importObj[fileID].missing_parameters[oldParameterId] = { p: "saveParameter", name: importJSON[i].name, file_type: importJSON[i].file_type, qualifier: importJSON[i].qualifier }
                window.importObj[rowID].missing_parameters.push(importJSON[i].name);

            }
            sendJSON[i] = {};
            sendJSON[i].p = "saveProcessParameter"
            if (type == "process_parameter_input") {
                sendJSON[i].type = "input";
            } else if (type == "process_parameter_output") {
                sendJSON[i].type = "output";
            }
            sendJSON[i].id = importJSON[i].id; //modify later
            sendJSON[i].sname = encodeURIComponent(importJSON[i].sname);
            sendJSON[i].closure = encodeURIComponent(importJSON[i].closure);
            sendJSON[i].reg_ex = encodeURIComponent(importJSON[i].reg_ex);
            if (importJSON[i].optional) {
                sendJSON[i].optional = importJSON[i].optional;
            }
            sendJSON[i].operator = importJSON[i].operator;
            sendJSON[i].process_id = "" //modify later
            sendJSON[i].parameter_id = importJSON[i].parameter_id //it is the old_parameter_id. modify it later
            sendJSON[i].group = ""
            sendJSON[i].perms = "" //modify later: take from process perms
        }
    } else if (type == "pipeline" || type == "pipeline_strict") {
        if (dbJSON) {
            importJSON.summary = switchHostSpecific(importJSON.summary, dbJSON.summary)
            importJSON.script_pipe_footer = switchHostSpecific(importJSON.script_pipe_footer, dbJSON.script_pipe_footer)
            importJSON.script_pipe_header = switchHostSpecific(importJSON.script_pipe_header, dbJSON.script_pipe_header)
            importJSON.script_pipe_config = switchHostSpecific(importJSON.script_pipe_config, dbJSON.script_pipe_config)
        } else {
            importJSON.summary = removePlatform(importJSON.summary)
            importJSON.script_pipe_footer = removePlatform(importJSON.script_pipe_footer)
            importJSON.script_pipe_header = removePlatform(importJSON.script_pipe_header)
            importJSON.script_pipe_config = removePlatform(importJSON.script_pipe_config)
        }
        sendJSON.id = ""; //modify later
        sendJSON.nodes = importJSON.nodes;
        sendJSON.mainG = importJSON.mainG;
        sendJSON.edges = importJSON.edges;
        sendJSON.pipeline_uuid = importJSON.pipeline_uuid;
        sendJSON.pipeline_rev_uuid = importJSON.pipeline_rev_uuid;
        sendJSON.pipeline_group_id = "" //modify later
        sendJSON.process_list = importJSON.process_list //modify later
        sendJSON.pipeline_list = importJSON.pipeline_list //modify later
        sendJSON.publish_web_dir = importJSON.publish_web_dir
        sendJSON.publish_dmeta_dir = importJSON.publish_dmeta_dir
        sendJSON.name = importJSON.name
        sendJSON.script_pipe_footer = importJSON.script_pipe_footer
        sendJSON.script_pipe_header = importJSON.script_pipe_header
        sendJSON.script_pipe_config = importJSON.script_pipe_config
        sendJSON.summary = importJSON.summary
        sendJSON.script_mode_footer = importJSON.script_mode_footer
        sendJSON.script_mode_header = importJSON.script_mode_header
        sendJSON.rev_comment = importJSON.rev_comment;
        sendJSON.group_id = ""
        if (importJSON.publicly_searchable) {
            sendJSON.publicly_searchable = importJSON.publicly_searchable;
        } else {
            sendJSON.publicly_searchable = "false";
        }
        sendJSON.pin = importJSON.pin
        sendJSON.pin_order = importJSON.pin_order
        if (importJSON.perms == 15) {
            sendJSON.perms = 3;
        } else {
            sendJSON.perms = importJSON.perms;
        }

    }
    return sendJSON
}

function removePlatform(importItem) {
    var importList = [];
    if (importItem) {
        var importList = importItem.split("//* platform")
        if (importList.length > 2) {
            for (var i = 0; i < importList.length; i++) {
                if (Math.abs(i % 2) == 1) {
                    importList[i] = "\n";
                }
            }
            importItem = importList.join("//* platform")
        }
    }
    return importItem
}

function switchHostSpecific(importItem, dbItem) {
    var importList = [];
    var dbList = [];
    if (importItem && dbItem) {
        var importList = importItem.split("//* platform")
        var dbList = dbItem.split("//* platform")
        if (importList.length > 2 && dbList.length > 2 && dbList.length == importList.length) {
            for (var i = 0; i < dbList.length; i++) {
                if (Math.abs(i % 2) == 1) {
                    importList[i] = dbList[i]
                }
            }
            importItem = importList.join("//* platform")
                //in case imported file have no platform tag, add to last part of the text
        } else if (importList.length === 1 && dbList.length > 2) {
            for (var i = 0; i < dbList.length; i++) {
                if (Math.abs(i % 2) == 1) {
                    importList.push(dbList[i] + "//* platform")
                }
            }
            importItem = importList.join("//* platform")
                // in case imported file have platform tag, but db hasn't.
        } else if (importList.length > 2 && dbList.length === 1) {
            for (var i = 0; i < importList.length; i++) {
                if (Math.abs(i % 2) == 1) {
                    importList[i] = "\n";
                }
            }
            importItem = importList.join("//* platform")
        }
    }
    return importItem
}

function checkImport(optObj) {
    //extract optional parameters
    var type = optObj.type
    var rowType = optObj.rowType
    var fileId = optObj.fileId
    var blockID = optObj.blockID
    var importJSON = optObj.importJSON
    var proInJSON = optObj.proInJSON
    var proOutJSON = optObj.proOutJSON
    var allParameters = optObj.allParameters
        //--optional parameters ends--
    var sendJSONprocess = {};
    var sendJSONpipeline = {};
    var sendJSONproPara = [];
    var sendJSONproParaIn = [];
    var sendJSONproParaOut = [];
    var mergedReport = {};
    var mergedReportPipe = {};
    var status = null;
    var report = null;
    var rowID = rowType + fileId + '_' + blockID;
    //command: to save/update db. 
    window.importObj[rowID] = { "command": null, "missing_parameters": [] }
    if (rowType == "process") {
        //check database for uuid
        var process_uuid = importJSON.process_uuid;
        var process_rev_uuid = importJSON.process_rev_uuid;
        var checkUUID = getValues({
            p: "check_uuid",
            type: "process",
            uuid: process_uuid,
            rev_uuid: process_rev_uuid
        });
        console.log("checkUUID", checkUUID)
            //prepare command to save/update db
        sendJSONprocess = prepareSendJSON("process", sendJSONprocess, importJSON, allParameters, fileId, rowID, decodeElement("process", checkUUID.process_rev_uuid))
        sendJSONproParaIn = prepareSendJSON("process_parameter_input", sendJSONproParaIn, proInJSON, allParameters, fileId, rowID, decodeElement("process", checkUUID.process_rev_uuid))
        sendJSONproParaOut = prepareSendJSON("process_parameter_output", sendJSONproParaOut, proOutJSON, allParameters, fileId, rowID, decodeElement("process", checkUUID.process_rev_uuid))
        sendJSONproPara = sendJSONproParaIn.concat(sendJSONproParaOut);
        if (checkUUID) {
            //if process_rev_uuid and process_uuid is found then check if process and pro_para are the same
            if (checkUUID.process_rev_uuid && checkUUID.process_uuid) {
                checkUUID.process_rev_uuid = decodeElement("process", checkUUID.process_rev_uuid)
                var checkUUID_process_id = checkUUID.process_rev_uuid.id;
                var checkObjPro = {};
                var checkObjProParaIn = {};
                var checkObjProParaOut = {};
                var checkUUIDpro_para_in = [];
                var checkUUIDpro_para_out = [];
                if (checkUUID["pro_para_inputs_" + checkUUID_process_id]) {
                    checkUUIDpro_para_in = JSON.parse(checkUUID["pro_para_inputs_" + checkUUID_process_id]);
                }
                if (checkUUID["pro_para_outputs_" + checkUUID_process_id]) {
                    checkUUIDpro_para_out = JSON.parse(checkUUID["pro_para_outputs_" + checkUUID_process_id]);
                }
                checkObjPro = checkIfEqual("process", importJSON, checkUUID.process_rev_uuid, fileId)
                    //check if process parameters are the same
                if (checkUUIDpro_para_in && proInJSON) {
                    checkObjProParaIn = checkIfEqual("process_parameter_in", proInJSON, checkUUIDpro_para_in, fileId)
                }
                if (checkUUIDpro_para_out && proOutJSON) {
                    checkObjProParaOut = checkIfEqual("process_parameter_out", proOutJSON, checkUUIDpro_para_out, fileId)
                }
                mergedReport.process = checkObjPro
                $.extend(mergedReport, checkObjProParaIn);
                $.extend(mergedReport, checkObjProParaOut);
                [status, report] = createImportReport(mergedReport)
                if (status === true) {
                    $("#stat_" + rowID).html('Process is exist <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Process will not be imported, instead existing revision will be used"><i class="glyphicon glyphicon-info-sign" style="font-size:13px;"></i></a></span>')
                        //process
                    window.importObj[rowID]["command"] = null;
                    window.importObj[rowID]["oldProcessId"] = importJSON.id;
                    window.importObj[fileId].dict.process[importJSON.id] = checkUUID_process_id;
                    //process parameter
                    window.importObj[rowID]["commandProPara"] = null;

                } else if (status === "warnUser") {
                    $("#stat_" + rowID).html('Conflict is detected <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Conflict has found between the existing and imported processes. Please proceed to view conflict and overwrite existing version"><i class="glyphicon glyphicon-info-sign" style="font-size:13px;"></i></a></span>')
                        //process
                    sendJSONprocess.rev_id = checkUUID.process_rev_uuid.rev_id;
                    sendJSONprocess.process_gid = checkUUID.process_rev_uuid.process_gid;
                    sendJSONprocess.id = checkUUID.process_rev_uuid.id;
                    window.importObj[rowID]["command"] = sendJSONprocess;
                    window.importObj[rowID]["oldProcessId"] = importJSON.id;
                    window.importObj[rowID]["warnUser"] = report;
                    window.importObj[fileId].dict.process[importJSON.id] = checkUUID.process_rev_uuid.id;
                    //process parameter
                    sendJSONproPara = insertIDColumn(sendJSONproPara, checkUUIDpro_para_in, checkUUIDpro_para_out, fileId)
                    window.importObj[rowID]["commandProPara"] = sendJSONproPara;
                }
                //if !process_rev_uuid but process_uuid is found then add as new revision into existing process_gid
            } else if (checkUUID.process_uuid) {
                checkUUID.process_uuid = decodeElement("process", checkUUID.process_uuid)
                $("#stat_" + rowID).html('Process group has found <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Process will be added as a new revision of the process: ' + checkUUID.process_uuid.name + '"><i class="glyphicon glyphicon-info-sign" style="font-size:13px;"></i></a></span>')
                    //process
                sendJSONprocess.rev_id = parseInt(checkUUID.process_uuid.rev_id) + 1;
                sendJSONprocess.process_gid = checkUUID.process_uuid.process_gid;
                window.importObj[rowID]["command"] = sendJSONprocess;
                window.importObj[rowID]["oldProcessId"] = importJSON.id;
                //process parameter
                window.importObj[rowID]["commandProPara"] = cleanIDColumn(sendJSONproPara);
                //if process_rev_uuid and process_uuid not found then add as new revision and process
            } else {
                $("#stat_" + rowID).html('Save as new process <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="It will be added as a new process"><i class="glyphicon glyphicon-info-sign" style="font-size:13px;"></i></a></span>')
                    //process
                sendJSONprocess.process_gid = ""
                window.importObj[rowID]["command"] = sendJSONprocess;
                window.importObj[rowID]["oldProcessId"] = importJSON.id;
                //process parameter
                window.importObj[rowID]["commandProPara"] = cleanIDColumn(sendJSONproPara);
            }
            // add log of missing parameters 
            if (window.importObj[rowID]["missing_parameters"].length > 0) {
                var existingHTML = $("#stat_" + rowID).html()
                $("#stat_" + rowID).html(existingHTML + ' New Parameters <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="New parameters (' + window.importObj[rowID]["missing_parameters"].join(", ") + ') will be added"><i class="glyphicon glyphicon-info-sign" style="font-size:13px;"></i></a></span>')
            }
        }
    } else if (rowType == "pipeline" || rowType == "pipeModule") {
        //check database for uuid
        var pipeline_uuid = importJSON.pipeline_uuid;
        var pipeline_rev_uuid = importJSON.pipeline_rev_uuid;
        var pipeUUID = getValues({
            p: "check_uuid",
            type: 'pipeline',
            uuid: pipeline_uuid,
            rev_uuid: pipeline_rev_uuid
        });
        console.log("pipeline_rev_uuid", pipeline_rev_uuid)
        console.log("pipeline_uuid", pipeline_uuid)
        console.log("check_uuid", pipeUUID)
        sendJSONpipeline = prepareSendJSON(type, sendJSONpipeline, importJSON, null, fileId, rowID, decodeElement("pipeline", pipeUUID.pipeline_rev_uuid))
        if (pipeUUID) {
            //if pipeline_rev_uuid is found then check if contents of pipeline are the same
            if (pipeUUID.pipeline_rev_uuid) {
                pipeUUID.pipeline_rev_uuid = decodeElement("pipeline", pipeUUID.pipeline_rev_uuid)
                var checkObjPipe = {};
                checkObjPipe = checkIfEqual(type, importJSON, pipeUUID.pipeline_rev_uuid, fileId)
                mergedReportPipe.pipeline = checkObjPipe
                mergedReportPipe = $.extend(true, {}, mergedReportPipe);
                [status, report] = createImportReport(mergedReportPipe)

                if (status === true) {
                    $("#stat_" + rowID).html('Pipeline is exist <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Pipeline will not be imported, instead existing revision will be used"><i class="glyphicon glyphicon-info-sign" style="font-size:13px;"></i></a></span>')
                    window.importObj[rowID]["command"] = null;
                    window.importObj[rowID]["oldPipelineId"] = importJSON.id;
                    window.importObj[fileId].dict[type][importJSON.id] = pipeUUID.pipeline_rev_uuid.id;

                } else if (status === "warnUser") {
                    $("#stat_" + rowID).html('Conflict is detected <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Conflict has found between the existing and imported pipelines. Please proceed to view conflict and overwrite existing version."><i class="glyphicon glyphicon-info-sign" style="font-size:13px;"></i></a></span>')
                    sendJSONpipeline.rev_id = pipeUUID.pipeline_rev_uuid.rev_id;
                    sendJSONpipeline.pipeline_gid = pipeUUID.pipeline_rev_uuid.pipeline_gid;
                    sendJSONpipeline.id = pipeUUID.pipeline_rev_uuid.id;
                    window.importObj[rowID]["command"] = sendJSONpipeline;
                    window.importObj[rowID]["oldPipelineId"] = importJSON.id;
                    window.importObj[rowID]["warnUser"] = report;
                    window.importObj[fileId].dict[type][importJSON.id] = pipeUUID.pipeline_rev_uuid.id;
                }
                //if !pipeline_rev_uuid but pipeline_uuid is found then add as new revision into existing pipeline_gid
            } else if (pipeUUID.pipeline_uuid) {
                pipeUUID.pipeline_uuid = decodeElement("pipeline", pipeUUID.pipeline_uuid)
                $("#stat_" + rowID).html('Pipeline group has found <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Pipeline will be added as a new revision of the pipeline: ' + pipeUUID.pipeline_uuid.name + '"><i class="glyphicon glyphicon-info-sign" style="font-size:13px;"></i></a></span>')
                sendJSONpipeline.rev_id = parseInt(pipeUUID.pipeline_uuid.rev_id) + 1;
                sendJSONpipeline.pipeline_gid = pipeUUID.pipeline_uuid.pipeline_gid;
                window.importObj[rowID]["command"] = sendJSONpipeline;
                window.importObj[rowID]["oldPipelineId"] = importJSON.id;
                //if pipeline_rev_uuid and pipeline_uuid not found then add as new revision and process
            } else {
                $("#stat_" + rowID).html('Save as new pipeline <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="It will be added as a new pipeline"><i class="glyphicon glyphicon-info-sign" style="font-size:13px;"></i></a></span>')
                sendJSONpipeline.pipeline_gid = ""
                window.importObj[rowID]["command"] = sendJSONpipeline;
                window.importObj[rowID]["oldPipelineId"] = importJSON.id;
            }
        }
    }

}


function insertIDColumn(sendJSONproPara, checkUUIDpro_para_in, checkUUIDpro_para_out, fileID) {
    var checkUUIDpro_para = [];
    checkUUIDpro_para = checkUUIDpro_para_in.concat(checkUUIDpro_para_out);
    for (var i = 0; i < sendJSONproPara.length; i++) {
        var proparaID = window.importObj[fileID].dict.propara[sendJSONproPara[i].id]
        if (proparaID && proparaID != "insert") {
            sendJSONproPara[i].id = proparaID;
        } else if (proparaID == "insert") {
            sendJSONproPara[i].id = "";
        }
    }
    return sendJSONproPara
}

function cleanIDColumn(sendJSONproPara) {
    if (sendJSONproPara) {
        for (var i = 0; i < sendJSONproPara.length; i++) {
            sendJSONproPara[i].id = "";
        }
    }
    return sendJSONproPara
}


function validatePipeImportBlock(fileId) {
    var importJSON = window.importObj[fileId].importJSON;
    var checkMainPipe = filterObjKeys(importJSON, /main_pipeline.*/, "obj");
    var checkPipeModule = filterObjKeys(importJSON, /pipeline_module.*/, "obj");
    checkPipeModule = sortByKey(checkPipeModule, 'layer')
    var optObj = {};

    if (checkMainPipe.length > 0) {
        for (var i = 0; i < checkMainPipe.length; i++) {
            checkMainPipe[i] = decodeElement("pipeline", checkMainPipe[i])
            optObj = { type: "pipeline_strict", rowType: "pipeline", fileId: fileId, blockID: i, importJSON: checkMainPipe[i] };
            checkImport(optObj)
        }
    }
    if (checkPipeModule.length > 0) {
        for (var i = checkPipeModule.length - 1; i >= 0; i--) {
            checkPipeModule[i] = decodeElement("pipeline", checkPipeModule[i])
            optObj = { type: "pipeline_strict", rowType: "pipeModule", fileId: fileId, blockID: i, importJSON: checkPipeModule[i] };
            checkImport(optObj)
        }
    }
}

function getFileBlock(fileId, fileName, importJSON, allParameters) {
    var showPipeBlock = "none";
    var showPipeModuleBlock = "none";
    var showProcessBlock = "none";
    var pipeBlock = "";
    var pipeModuleBlock = "";
    var processBlock = "";
    var checkMainPipe = filterObjKeys(importJSON, /main_pipeline.*/, "obj");
    var checkPipeModule = filterObjKeys(importJSON, /pipeline_module.*/, "obj");
    checkPipeModule = sortByKey(checkPipeModule, 'layer')
    var checkProcess = filterObjKeys(importJSON, /process.*/);
    var optObj = {};

    if (checkMainPipe.length > 0) {
        showPipeBlock = "table-row";
        for (var i = 0; i < checkMainPipe.length; i++) {
            pipeBlock += getRowImportTable("pipeline", "Pipeline", fileId, i, checkMainPipe[i].name, checkMainPipe[i].pipeline_group_name, checkMainPipe[i].pipeline_uuid);
            checkMainPipe[i] = decodeElement("pipeline", checkMainPipe[i])
            optObj = { type: "pipeline", rowType: "pipeline", fileId: fileId, blockID: i, importJSON: checkMainPipe[i] };
            var doCall = function(optObj) { setTimeout(function() { checkImport(optObj); }, 10); }
            doCall(optObj);
        }
    }
    if (checkPipeModule.length > 0) {
        showPipeModuleBlock = "table-row";
        for (var i = checkPipeModule.length - 1; i >= 0; i--) {
            pipeModuleBlock += getRowImportTable("pipeModule", "Pipeline", fileId, i, checkPipeModule[i].name, checkPipeModule[i].pipeline_group_name, checkPipeModule[i].pipeline_uuid);
            checkPipeModule[i] = decodeElement("pipeline", checkPipeModule[i])
            optObj = { type: "pipeline", rowType: "pipeModule", fileId: fileId, blockID: i, importJSON: checkPipeModule[i] };
            var doCall = function(optObj) { setTimeout(function() { checkImport(optObj); }, 10); }
            doCall(optObj);
        }
    }
    if (checkProcess.length > 0) {
        showProcessBlock = "table-row";
        for (var i = 0; i < checkProcess.length; i++) {
            var proJSON = {};
            var proInJSON = {};
            var proOutJSON = {};
            proJSON = JSON.parse(importJSON[checkProcess[i]])[0];
            proJSON = decodeElement("process", proJSON)
            var process_id = proJSON.id
            proInJSON = JSON.parse(importJSON["pro_para_inputs_" + process_id]);
            proOutJSON = JSON.parse(importJSON["pro_para_outputs_" + process_id]);
            importJSON[checkProcess[i]] = proJSON;
            processBlock += getRowImportTable("process", "Process", fileId, i, proJSON.name, proJSON.process_group_name, proJSON.process_uuid);
            optObj = { type: "process", rowType: "process", fileId: fileId, blockID: i, importJSON: proJSON, proInJSON: proInJSON, proOutJSON: proOutJSON, allParameters: allParameters };
            var doCall = function(optObj) { setTimeout(function() { checkImport(optObj); }, 10); }
            doCall(optObj);

        }
    }
    var panelDiv = '<div><h6>File Name: ' + fileName + '</h6></div><div class="panel panel-default"><div id="fileTab' + fileId + '"> </br><table style="width:100%; table-layout: fixed; word-break:break-all;" id="Table' + fileId + ' class="table"><thead><tr><th style="width:40%;" scope="col">Name</th><th style="width:20%;" scope="col">Menu Group</th><th style="width:40%;" scope="col">Status</th></tr></thead><tbody style="word-break: break-all;"><tr id="imPipeline" style="display:' + showPipeBlock + '; background-color:#F5F5F5; font-weight:bold; font-style: italic; height:40px;"><td colspan="6">~ Pipeline ~</td></tr>' + pipeBlock + '<tr id="imPipeModule" style="display:' + showPipeModuleBlock + '; background-color:#F5F5F5; font-weight:bold; font-style: italic; height:40px;"><td colspan="6">~ Pipeline Module ~</td></tr>' + pipeModuleBlock + '<tr id="imProcess" style="display:' + showProcessBlock + '; background-color:#F5F5F5; font-weight:bold; font-style: italic; height:40px;"><td colspan="6">~ Process ~</td></tr>' + processBlock + '</tbody></table></div></div>';
    return panelDiv;
}

function decodeElement(type, importJSON) {
    if (!importJSON) {
        return importJSON
    }
    if (type == "process") {
        importJSON.script = removeDoubleQuote(decodeHtml(importJSON.script))
        importJSON.script_footer = removeDoubleQuote(decodeHtml(importJSON.script_footer))
        importJSON.script_header = removeDoubleQuote(decodeHtml(importJSON.script_header))
        importJSON.summary = decodeHtml(importJSON.summary)
    } else if (type == "process_parameter") {
        importJSON.closure = decodeHtml(importJSON.closure)
        importJSON.reg_ex = decodeHtml(importJSON.reg_ex)
    } else if (type == "pipeline" || type == "pipeline_strict") {
        importJSON.script_pipe_footer = decodeHtml(importJSON.script_pipe_footer);
        importJSON.script_pipe_header = decodeHtml(importJSON.script_pipe_header);
        importJSON.script_pipe_config = decodeHtml(importJSON.script_pipe_config);
        importJSON.summary = decodeHtml(importJSON.summary)
    }
    return importJSON
}

// I/O id naming:[0]i = input,o = output -[1]process database ID -[2]The number of I/O of the selected process -[3]Parameter database ID- [4]uniqe number
function convertEdgeIm(id, fileID) {
    var idlist = id.split("-")
    if (idlist[1] != "outPro" && idlist[1] != "inPro") {
        if (window.importObj[fileID].dict.process[idlist[1]]) {
            idlist[1] = String(window.importObj[fileID].dict.process[idlist[1]])
        } else {
            window.importObj["importStatus"] = "stopped";
            showInfoModal("#infoMod", "#infoModText", "process (id:" + idlist[1] + ") not found in pipeline dictionary and pipeline edge conversion was not successful.")
        }
    }
    if (window.importObj[fileID].dict.parameter[idlist[3]]) {
        idlist[3] = String(window.importObj[fileID].dict.parameter[idlist[3]])
    } else {
        window.importObj["importStatus"] = "stopped";
        showInfoModal("#infoMod", "#infoModText", "parameter (id:" + idlist[1] + ") not found in pipeline dictionary and pipeline edge conversion was not successful.")
    }
    return idlist.join("-")
}

function encodeEdges(edges, fileID) {
    var final = [];
    edges = JSON5.parse(edges)["edges"]
    for (var i = 0; i < edges.length; i++) {
        if (edges[i]) {
            var eds = edges[i].split("_")
            if (eds) {
                if (eds.length == 2) {
                    var eds0 = convertEdgeIm(eds[0], fileID);
                    var eds1 = convertEdgeIm(eds[1], fileID);
                    final.push(eds0 + "_" + eds1)
                }
            }
        }
    }
    return final
}

function encodeNodes(nodes, fileID) {
    nodes = JSON5.parse(nodes)
    $.each(nodes, function(el) {
        if (nodes[el]) {
            var proPipeID = nodes[el][2];
            if (proPipeID != "outPro" && proPipeID != "inPro") {
                if (proPipeID.match(/p/)) {
                    var piID = proPipeID.match(/p(.*)/)[1];
                    var replace = window.importObj[fileID].dict.pipeline[piID];
                    if (replace) {
                        nodes[el][2] = "p" + String(replace)
                    } else {
                        window.importObj["importStatus"] = "stopped";
                        showInfoModal("#infoMod", "#infoModText", "pipeline module (id:" + piID + ") not found in pipeline dictionary and pipeline node conversion was not successful.")
                    }
                } else {
                    var replace = window.importObj[fileID].dict.process[proPipeID];
                    if (proPipeID && replace) {
                        nodes[el][2] = String(replace)
                    } else {
                        window.importObj["importStatus"] = "stopped";
                        showInfoModal("#infoMod", "#infoModText", "process (id:" + proPipeID + ") not found in pipeline dictionary and pipeline node conversion was not successful.")
                    }
                }
            }
        }
    });
    return nodes
}

function encodeProPipeList(list, fileID, type) {
    var items = [];
    if (!list || list == "") {
        return list
    }
    var items = list.split(",")
    for (var i = 0; i < items.length; i++) {
        if (items[i]) {
            var replace = window.importObj[fileID].dict[type][items[i]];
            if (replace) {
                items[i] = String(replace);
            }
        }
    }
    return items.join(",")
}

function encodeElement(type, importJSON, fileID) {
    if (!importJSON) {
        return importJSON
    }
    if (type == "process") {
        importJSON.script = encodeURIComponent(importJSON.script)
        importJSON.script_footer = encodeURIComponent(importJSON.script_footer)
        importJSON.script_header = encodeURIComponent(importJSON.script_header)
    } else if (type == "pipeline" || type == "pipeline_strict") {
        importJSON.summary = encodeURIComponent(importJSON.summary)
        importJSON.script_pipe_footer = encodeURIComponent(importJSON.script_pipe_footer)
        importJSON.script_pipe_header = encodeURIComponent(importJSON.script_pipe_header)
        importJSON.script_pipe_config = encodeURIComponent(importJSON.script_pipe_config)
        importJSON.edges = encodeEdges(importJSON.edges, fileID)
        importJSON.nodes = encodeNodes(importJSON.nodes, fileID)
        importJSON.mainG = JSON5.parse(importJSON.mainG)["mainG"];
        importJSON.pipeline_list = encodeProPipeList(importJSON.pipeline_list, fileID, "pipeline")
        importJSON.process_list = encodeProPipeList(importJSON.process_list, fileID, "process")
        var savedList = [];
        var pipelineColums = ["name", "id", "nodes", "mainG", "edges", "summary", "group_id", "perms", "pin", "pin_order", "publicly_searchable", "script_pipe_header", "script_pipe_config", "script_pipe_footer", "script_mode_header", "script_mode_footer", "pipeline_group_id", "process_list", "pipeline_list", "publish_web_dir", "publish_dmeta_dir", "pipeline_gid", "rev_comment", "rev_id", "pipeline_uuid", "pipeline_rev_uuid"];
        for (var i = 0; i < pipelineColums.length; i++) {
            var key = pipelineColums[i];
            var tObj = {};
            if (importJSON[key] !== undefined) {
                tObj[key] = importJSON[key]
            } else {
                tObj[key] = ""
            }
            savedList.push(tObj)
        }
        var sl = JSON.stringify(savedList);
        importJSON = { p: "saveAllPipeline", dat: sl };
    }
    return importJSON
}

$('#nextButton').on('click', function(e) {
    $('#importModalPart1').css("display", "none");
    $('#importModalPart2').css("display", "inline");
    $('#importButton').css("display", "inline");
    $('#nextButton').css("display", "none");
    window.ajaxData.pipelineMenuGroup = getValues({ p: "getPipelineGroup" });
    window.ajaxData.processMenuGroup = getValues({ p: "getAllProcessGroups" });
    var allParameters = getValues({ p: "getAllParameters" });
    var fileList = window.importObj.filename;
    for (var i = 0; i < fileList.length; i++) {
        window.importObj[i] = { dict: {}, missing_parameters: {}, redundant_propara: {}, missing_process_menu: {}, missing_pipeline_menu: {} };
        //dict:keep log of created process, parameter and process_paramater id's.
        window.importObj[i].dict = { process: {}, pipeline_strict: {}, pipeline: {}, parameter: {}, propara: {}, lastpipeline: "" };
        var text = getValues({ p: "getUpload", "name": fileList[i] });
        if (text) {
            var decrypted = CryptoJS.AES.decrypt(text, "");
            if (decrypted) {
                var decryptedText = decrypted.toString(CryptoJS.enc.Utf8)
                if (IsJsonString(decryptedText)) {
                    var importJSON = JSON.parse(decryptedText)
                    console.log(importJSON)
                    window.importObj[i].importJSON = importJSON;
                    var panelDiv = getFileBlock(i, fileList[i], importJSON, allParameters);
                    $("#importModalPart2").append(panelDiv);
                }
            }
        }
    }
    setTimeout(function() { $('[data-toggle="tooltip"]').tooltip(); }, 10);

});




function rowUpdateStatus(parBoxId, text, status) {
    if (status == "ok") {
        var icon = '<i class="glyphicon glyphicon-ok" style="font-size:13px;"></i>'
        var color = "palegreen";
    } else if (status == "error") {
        var icon = '<i class="glyphicon glyphicon-remove" style="font-size:13px;"></i>'
        var color = "indianred";
    }
    $("#stat_" + parBoxId).html(text + icon).css("background-color", color);
}

$('#importModal').on('show.bs.modal', function(e) {
    $('#importModalPart1').css("display", "inline");
    $('#importModalPart2').css("display", "none");
    $('#nextButton').prop("disabled", true);
    $('#nextButton').css("display", "inline");
    $('#importButton').css("display", "none");
    $('#compButton').css("display", "none");
    $('#importModalPart2').css("display", "none");
    $('#importModalPart2').empty();
    window.importObj = {};
    window.importObj.filename = [];
});


$('#importModal').on('hide.bs.modal', function(e) {
    //reset pipeline Menu group
    //    loadPipeMenuGroup(true);
    //reset import area
    var myDropzone = Dropzone.forElement("#importArea");
    myDropzone.removeAllFiles();
    var fileList = window.importObj.filename;
    for (var i = 0; i < fileList.length; i++) {
        var text = getValues({ p: "removeUpload", "name": fileList[i] });
    }
    if (window.importObj["importStatus"] == "finalized") {
        var obj = window.importObj[0].dict.lastpipeline
        if (obj) {
            var lastPipeId = obj;
            if (lastPipeId) {
                setTimeout(function() { window.location.replace("index.php?np=1&id=" + lastPipeId); }, 700);
            }
        }

    }

});