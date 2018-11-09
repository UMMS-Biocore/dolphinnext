//template text for ace editor
templategroovy = '//groovy example: \n\n println "Hello, World!"';
templateperl = '#perl example: \n\n#!/usr/bin/perl \n print \'Hi there!\' . \'\\n\';';
templatepython = '#python example: \n\n#!/usr/bin/python \nx = \'Hello\'  \ny = \'world!\' \nprint "%s - %s" % (x,y)';
templatesh = '#shell example: \n\n#!/bin/sh \nmy_variable="Hello World" \necho $my_variable';

createAceEditors("editor", "#script_mode"); //ace process main editor
createAceEditors("editorProHeader", "#script_mode_header") //ace process header editor
createAceEditors("editorProFooter", "#script_mode_footer") //ace process header editor
createAceEditors("editorPipeHeader", "#script_mode_pipe_header") //ace pipeline header editor
createAceEditors("editorPipeFooter", "#script_mode_pipe_footer") //ace pipeline footer editor

function createAceEditors(editorId, script_modeId) {
    //ace process editor
    window[editorId] = ace.edit(editorId);
    window[editorId].setTheme("ace/theme/tomorrow");
    window[editorId].getSession().setMode("ace/mode/sh");
    window[editorId].$blockScrolling = Infinity;
    //If mode is exist, then apply it
    var mode = $(script_modeId).val();
    if (mode && mode != "") {
        window[editorId].session.setMode("ace/mode/" + mode);
    }
    // If template text is not changed or it is blank : set the template text on change
    $(function () {
        $(document).on('change', script_modeId, function () {
            var newMode = $(script_modeId).val();
            window[editorId].session.setMode("ace/mode/" + newMode);
            var editorText = window[editorId].getValue();
            if (editorText === templategroovy || editorText === templateperl || editorText === templatepython || editorText === templatesh || editorText === '') {
                var newTempText = 'template' + newMode;
                window[editorId].setValue(window[newTempText]);
            }
        })
    });
}
// To refresh the content of ace editors. Otherwise it doesn't show the text
$('#advOptPro').on('show.bs.collapse', function () {
    var scriptProHeader = editorProHeader.getValue();
    editorProHeader.setValue(scriptProHeader);
    editorProHeader.clearSelection();
    var scriptProFooter = editorProFooter.getValue();
    editorProFooter.setValue(scriptProFooter);
    editorProFooter.clearSelection();
});
$('#advOpt').on('show.bs.collapse', function () {
    var scriptPipeHeader = editorPipeHeader.getValue();
    editorPipeHeader.setValue(scriptPipeHeader);
    editorPipeHeader.clearSelection();
    var scriptPipeFooter = editorPipeFooter.getValue();
    editorPipeFooter.setValue(scriptPipeFooter);
    editorPipeFooter.clearSelection();
});

function cleanProcessName(proName) {
    proName = proName.replace(/ /g, "_");
    proName = proName.replace(/-/g, "_");
    proName = proName.replace(/:/g, "_");
    proName = proName.replace(/,/g, "_");
    proName = proName.replace(/\$/g, "_");
    proName = proName.replace(/\!/g, "_");
    proName = proName.replace(/\</g, "_");
    proName = proName.replace(/\>/g, "_");
    proName = proName.replace(/\?/g, "_");
    proName = proName.replace(/\(/g, "_");
    proName = proName.replace(/\"/g, "_");
    proName = proName.replace(/\'/g, "_");
    proName = proName.replace(/\./g, "_");
    return proName;
}



// cleanProcessModal when modal is closed     
function cleanProcessModal() {
    $('#mParameters').remove();
    $('#inputGroup').remove();
    $('#inputTitle').remove();
    $('#outputGroup').remove();
    $('#outputTitle').remove();
    $('#proGroup').remove();
    $('#revModalHeader').remove();
    var menuRevBackup = stateModule.getState("menuRevBackup");
    var menuGrBackup = stateModule.getState("menuGrBackup");
    var allBackup = stateModule.getState("allBackup");
    var inBackup = stateModule.getState("inBackup");
    var inTitleBackup = stateModule.getState("inTitleBackup");
    var outBackup = stateModule.getState("outBackup");
    var outTitleBackup = stateModule.getState("outTitleBackup");

    $('#addHeader').after(menuRevBackup);
    $('#describeGroup').after(menuGrBackup);
    $('#proGroup').after(allBackup);
    $('#mParameters').after(inTitleBackup);
    $('#inputTitle').after(inBackup);
    $('#inputGroup').after(outTitleBackup);
    $('#outputTitle').after(outBackup);
    editor.setValue("");
    editorProHeader.setValue("");
    editorProFooter.setValue("");
    $('#mProActionsDiv').css('display', "none");
    $('#mProRevSpan').css('display', "none");
    $('#mName').removeAttr('disabled');
    $('#permsPro').removeAttr('disabled');
    $('#publishPro').removeAttr('disabled');
    var advOptProClass = $('#advOptPro').attr('class');
    if (advOptProClass !== "row collapse") {
        $('#mAdvProCollap').trigger("click");
    }
    $('#createRevisionBut').css('display', "none");
    $('#saveprocess').css('display', "inline");

}

function cleanInfoModal() {
    $('#mProRev').removeAttr('info');
    $('#mName').removeAttr('disabled');
    $('#mVersion').removeAttr('disabled');
    $('#mDescription').removeAttr('disabled');
    $('#selectProcess').removeAttr("gNum");
    $('#selectProcess').removeAttr("fProID");
    $('#selectProcess').removeAttr("lastProID");
    $('#selectProcess').removeAttr("pName");
    $('#selectProcess').removeAttr("xCoor");
    $('#selectProcess').removeAttr("yCoor");
    editor.setReadOnly(false);
    editorProHeader.setReadOnly(false);
    editorProFooter.setReadOnly(false);
    $('#saveprocess').css('display', "inline");
    $('#selectProcess').css('display', "none");
    $('#createRevisionBut').css('display', "none");
}

function refreshProcessModal(selProId) {
    $(this).find('form').trigger('reset');
    cleanProcessModal();
    $('#mProRev').attr("prev", "-1");
    editor.setValue(templatesh);
    loadModalProGro();
    loadModalParam();
    $('#processmodaltitle').html('Edit/Delete Process');
    $('#mProActionsDiv').css('display', "inline");
    $('#mProRevSpan').css('display', "inline");
    $('#proPermGroPubDiv').css('display', "inline");
    loadModalRevision(selProId);
    loadSelectedProcess(selProId);
}

function loadModalProGro() {
    $.ajax({
        type: "GET",
        url: "ajax/ajaxquery.php",
        data: {
            p: "getAllProcessGroups"
        },
        async: false,
        success: function (s) {
            $("#mProcessGroup").empty();
            var firstOptionGroup = new Option("Select Menu Process Group...", '');
            $("#mProcessGroup").append(firstOptionGroup);
            for (var i = 0; i < s.length; i++) {
                var param = s[i];
                var optionGroup = new Option(param.group_name, param.id);
                $("#mProcessGroup").append(optionGroup);
            }
            $('#mProcessGroup').selectize({});
        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
};

// newPipe=true when loading new pipeline
function loadPipeMenuGroup(newPipe) {
    //ajax for Pipeline Menu Group 
    $.ajax({
        type: "GET",
        url: "ajax/ajaxquery.php",
        data: {
            p: "getPipelineGroup"
        },
        async: false,
        success: function (s) {
            $("#pipeGroupAll").empty();
            if (newPipe === true) {
                var firstOptionGroup = new Option("Select Menu Group...", '');
                $("#pipeGroupAll").append(firstOptionGroup);
            }
            for (var i = 0; i < s.length; i++) {
                var param = s[i];
                var optionGroup = new Option(param.group_name, param.id);
                $("#pipeGroupAll").append(optionGroup);
            }
        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
};

function loadModalParam() {
    //ajax for parameters
    $.ajax({
        type: "GET",
        url: "ajax/ajaxquery.php",
        data: {
            p: "getAllParameters"
        },
        async: false,
        success: function (s) {
            $("#mInputs-1").empty();
            $("#mOutputs-1").empty();
            numInputs = 1;
            numOutputs = 1;
            $('#mInputs-1').selectize({
                valueField: 'id',
                searchField: 'name',
                placeholder: "Add input...",
                options: s,
                render: renderParam
            });
            $('#mOutputs-1').selectize({
                valueField: 'id',
                searchField: 'name',
                placeholder: "Add output...",
                options: s,
                render: renderParam
            });
            $('#mParamAllIn').parent().hide();
        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
};


function loadPipeModalRevision(pipeline_id) {
    var revisions = getValues({ p: "getPipelineRevision", "pipeline_id": pipeline_id });
    $('#mPipeRev').selectize({
        valueField: 'id',
        searchField: ['rev_id', 'rev_comment'],
        options: revisions,
        render: renderRev
    });
    $('#mPipeRev')[0].selectize.setValue(pipeline_id, false);
}


function loadModalRevision(selProcessId) {
    var revisions = getValues({ p: "getProcessRevision", "process_id": selProcessId });
    if (revisions.length > 1) {
        $('#mName').attr('disabled', "disabled");
    }
    $('#mProRev').selectize({
        valueField: 'id',
        searchField: ['rev_id', 'rev_comment'],
        options: revisions,
        render: renderRev
    });
    $('#mProRev')[0].selectize.setValue(selProcessId, false);
}

function loadSelectedProcess(selProcessId) {
    $('#mIdPro').val(selProcessId);
    //Ajax for selected process
    var showProcess = getValues({
        p: "getProcessData",
        "process_id": selProcessId
    })[0];
    sMenuProGroupIdFirst = showProcess.process_group_id;
    var processOwn = showProcess.own;
    //insert data into form
    var formValues = $('#addProcessModal').find('input, select, textarea');
    $(formValues[2]).val(showProcess.id);
    $(formValues[3]).val(showProcess.name);
    $(formValues[5]).val(decodeHtml(showProcess.summary));
    $('#permsPro').val(showProcess.perms);
    if (showProcess.username !== "" && showProcess.username !== null) {
        $('#creatorInfoPro').css("display", "inline");
        $('#ownUserNamePro').text(showProcess.username);
    }
    if (showProcess.date_created !== "" && showProcess.date_created !== null) {
        $('#datecreatedPro').text(showProcess.date_created);
    }
    if (showProcess.date_modified !== "" && showProcess.date_modified !== null) {
        $('#lasteditedPro').text(showProcess.date_modified);
    }
    if (showProcess.group_id !== "" && showProcess.group_id !== null) {
        $('#groupSelPro').val(showProcess.group_id);
    }
    if (showProcess.publish !== "" && showProcess.publish !== null) {
        $('#publishPro').val(showProcess.publish);
    }
    if (showProcess.script_mode !== "" && showProcess.script_mode !== null) {
        $('#script_mode').val(showProcess.script_mode);
    }
    if (showProcess.script_mode_header !== "" && showProcess.script_mode_header !== null) {
        $('#script_mode_header').val(showProcess.script_mode_header);
    }
    if (showProcess.script !== null) {
        editorScript = removeDoubleQuote(decodeHtml(showProcess.script));
        editor.setValue(editorScript);
        editor.clearSelection();
    }
    if (showProcess.script_header !== null) {
        editorProHeaderScript = removeDoubleQuote(decodeHtml(showProcess.script_header));
        editorProHeader.setValue(editorProHeaderScript);
        editorProHeader.clearSelection();
    }
    if (showProcess.script_footer !== null) {
        editorProFooterScript = removeDoubleQuote(decodeHtml(showProcess.script_footer));
        editorProFooter.setValue(editorProFooterScript);
        editorProFooter.clearSelection();
    }
    if (showProcess.process_group_id !== "" && showProcess.process_group_id !== null) {
        $('#mProcessGroup')[0].selectize.setValue(showProcess.process_group_id, false);
    }
    //Ajax for selected process input/outputs
    var inputs = getValues({
        p: "getInputsPP",
        "process_id": selProcessId
    });
    var outputs = getValues({
        p: "getOutputsPP",
        "process_id": selProcessId
    });
    for (var i = 0; i < inputs.length; i++) {
        var numForm = i + 1;
        $('#mInputs-' + numForm)[0].selectize.setValue(inputs[i].parameter_id, false);
        $('#mInName-' + numForm).val(inputs[i].sname);
        $('#mInName-' + numForm).attr('ppID', inputs[i].id);
        var closureText = "";
        if (inputs[i].closure !== '' && inputs[i].closure !== null) {
            closureText = decodeHtml(inputs[i].closure);
        }
        $('#mInClosure-' + numForm).val(closureText);
        if (inputs[i].operator !== '' && inputs[i].operator !== null) {
            $('#mInOpt-' + numForm).val(inputs[i].operator);
            $('#mInOptBut-' + numForm).trigger('click');
        }

    }
    for (var i = 0; i < outputs.length; i++) {
        var numForm = i + 1;
        $('#mOutputs-' + numForm)[0].selectize.setValue(outputs[i].parameter_id, false);
        $('#mOutName-' + numForm).val(outputs[i].sname);
        $('#mOutName-' + numForm).attr('ppID', outputs[i].id);
        var closureText = "";
        if (outputs[i].closure !== '' && outputs[i].closure !== null) {
            closureText = decodeHtml(outputs[i].closure);
            $('#mOutClosure-' + numForm).val(closureText);
        }
        if (outputs[i].operator !== '' && outputs[i].operator !== null) {
            $('#mOutOpt-' + numForm).val(outputs[i].operator);
            $('#mOutOptBut-' + numForm).trigger('click');
        }
        if (outputs[i].reg_ex !== '' && outputs[i].reg_ex !== null) {
            var reg_exText = decodeHtml(outputs[i].reg_ex);
            $('#mOutReg-' + numForm).val(reg_exText);
            $('#mOutRegBut-' + numForm).trigger('click');
        }
    }
    if (showProcess.perms === "63" && usRole !== "admin") {
        $('#permsPro').attr('disabled', "disabled");
        $('#publishPro').attr('disabled', "disabled");
        disableProModalPublic(selProcessId);
    }
    return [showProcess.perms, processOwn];
};

function removeDoubleQuote(script) {
    var lastLetter = script.length - 1;
    if (script[0] === '"' && script[lastLetter] === '"' && script[1] !== '"') {
        script = script.substring(1, script.length - 1); //remove first and last duble quote
    }
    return script
}

function sortByKey(array, key) {
    return array.sort(function (a, b) {
        var x = a[key];
        var y = b[key];
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    });
}

function arraysEqual(a, b) {
    if (a === b) return true;
    if (a == null || b == null) return false;
    if (a.length != b.length) return false;
    a = sortByKey(a, 'parameter_id')
    b = sortByKey(b, 'parameter_id')
    for (var i = 0; i < a.length; i++) {
        if (JSON.stringify(a[i]) !== JSON.stringify(b[i])) return false;
    }
    return true;
}

//Check if process is ever used in pipelines 
function checkPipeline(proid) {
    var checkPipe = getValues({ p: "checkPipeline", "process_id": proid });
    return checkPipe
}
//Check if process is ever used in pipelines that user not owner 
function checkPipelinePublic(proid) {
    var checkPipe = getValues({ p: "checkPipelinePublic", "process_id": proid });
    return checkPipe
}
//Check if process is ever used in project_pipeline that user not owner 
function checkProjectPipelinePublic(proid) {
    var checkProPipePub = getValues({ p: "checkProjectPipelinePublic", "process_id": proid });
    return checkProPipePub
}

//Check if pipeline is ever used in projects 
function checkProject(pipeline_id) {
    var checkProj = getValues({ p: "checkProject", "pipeline_id": pipeline_id });
    console.log(checkProj)
    var checkProjPipeModule = getValues({ p: "checkProjectPipeModule", "pipeline_id": pipeline_id });

    return checkProj
}
//Check if pipeline is ever used in projects that are group or public
function checkProjectPublic(pipeline_id) {
    var checkProj = getValues({ p: "checkProjectPublic", "pipeline_id": pipeline_id });
    return checkProj
}
//Check if parameter is ever used in processes 
function checkParameter(parameter_id) {
    var checkPara = getValues({ p: "checkParameter", "parameter_id": parameter_id });
    return checkPara
}
//Check if menu group contains any processes 
function checkMenuGr(menu_id) {
    var checkMeGr = getValues({ p: "checkMenuGr", "id": menu_id });
    return checkMeGr
}
//Check if pipeline menu group contains any pipelines 
function checkPipeMenuGr(menu_id) {
    var checkMeGr = getValues({ p: "checkPipeMenuGr", "id": menu_id });
    return checkMeGr
}

//Check if process parameters are the same
//True equal
function checkProParameters(inputProParams, outputProParams, proID) {

    var pro1inputs = inputProParams;
    var pro1outputs = outputProParams;
    var pro2inputs = getValues({ p: "getInputsPP", "process_id": proID });
    var pro2outputs = getValues({ p: "getOutputsPP", "process_id": proID });
    $.each(pro2inputs, function (element) {
        delete pro2inputs[element].id;
    });
    $.each(pro2outputs, function (element) {
        delete pro2outputs[element].id;
    });
    checkEqIn = arraysEqual(pro1inputs, pro2inputs);
    checkEqOut = arraysEqual(pro1outputs, pro2outputs);
    return checkEqIn && checkEqOut //both should be true to be equal
}

//-----Add input output parameters to process_parameters
// startpoint: first object in data array where inputparameters starts.
function addProParatoDB(data, startPoint, process_id) {
    var ppIDinputList = [];
    var ppIDoutputList = [];
    for (var i = startPoint; i < data.length; i++) {
        var dataToProcessParam = []; //dataToProcessPram to save in process_parameters table
        var PattPar = /(.*)-(.*)/;
        var matchFPart = '';
        var matchSPart = '';
        var matchVal = '';
        var matchFPart = data[i].name.replace(PattPar, '$1')
        var matchSPart = data[i].name.replace(PattPar, '$2')
        var matchVal = data[i].value
        var perms = $('#permsPro').val();
        var group = $('#groupSelPro').val();


        if (matchFPart === 'mInputs' && matchVal !== '') {
            //first check if closures are visible
            if ($("#mInClosure-" + matchSPart).css('visibility') === 'visible') {
                for (var n = startPoint; n < data.length; n++) {
                    if (data[n].name === 'mInOpt-' + matchSPart) {
                        dataToProcessParam.push({ name: "operator", value: data[n].value });
                    } else if (data[n].name === 'mInClosure-' + matchSPart) {
                        dataToProcessParam.push({ name: "closure", value: encodeURIComponent(data[n].value) });
                    }
                }
            }
            //for process parameters
            for (var k = startPoint; k < data.length; k++) {
                if (data[k].name === 'mInName-' + matchSPart && data[k].value === '') {
                    dataToProcessParam = [];
                    break;
                } else if (data[k].name === 'mInName-' + matchSPart && data[k].value !== '') {
                    var ppID = $('#' + data[k].name).attr("ppID");
                    ppIDinputList.push(ppID);
                    dataToProcessParam.push({ name: "perms", value: perms });
                    dataToProcessParam.push({ name: "group", value: group });
                    dataToProcessParam.push({ name: "parameter_id", value: matchVal });
                    dataToProcessParam.push({ name: "type", value: 'input' });
                    dataToProcessParam.push({ name: "sname", value: encodeURIComponent(data[k].value) });
                    dataToProcessParam.push({ name: "process_id", value: process_id });
                    dataToProcessParam.push({ name: "id", value: ppID });
                    dataToProcessParam.push({ name: "p", value: "saveProcessParameter" });
                }
            }
        } else if (matchFPart === 'mOutputs' && matchVal !== '') {
            //first check if regEx are visible
            if ($("#mOutReg-" + matchSPart).css('visibility') === 'visible') {
                for (var n = startPoint; n < data.length; n++) {
                    if (data[n].name === 'mOutReg-' + matchSPart) {
                        dataToProcessParam.push({ name: "reg_ex", value: encodeURIComponent(data[n].value) });
                    }
                }
            }
            //first check if closures are visible
            if ($("#mOutClosure-" + matchSPart).css('visibility') === 'visible') {
                for (var n = startPoint; n < data.length; n++) {
                    if (data[n].name === 'mOutOpt-' + matchSPart) {
                        dataToProcessParam.push({ name: "operator", value: data[n].value });
                    } else if (data[n].name === 'mOutClosure-' + matchSPart) {
                        dataToProcessParam.push({ name: "closure", value: encodeURIComponent(data[n].value) });
                    }
                }
            }
            //for process parameters 
            for (var k = startPoint; k < data.length; k++) {
                if (data[k].name === 'mOutName-' + matchSPart && data[k].value === '') {
                    dataToProcessParam = [];
                    break;
                } else if (data[k].name === 'mOutName-' + matchSPart && data[k].value !== '') {
                    var ppID = $('#' + data[k].name).attr("ppID");
                    ppIDoutputList.push(ppID);
                    dataToProcessParam.push({ name: "perms", value: perms });
                    dataToProcessParam.push({ name: "group", value: group });
                    dataToProcessParam.push({ name: "parameter_id", value: matchVal });
                    dataToProcessParam.push({ name: "type", value: 'output' });
                    dataToProcessParam.push({ name: "sname", value: encodeURIComponent(data[k].value) });
                    dataToProcessParam.push({ name: "process_id", value: process_id });
                    dataToProcessParam.push({ name: "id", value: ppID });
                    dataToProcessParam.push({ name: "p", value: "saveProcessParameter" });
                }
            }
        }
        if (dataToProcessParam.length > 0) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: dataToProcessParam,
                async: true,
                success: function (s) {},
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    }
    return [ppIDinputList, ppIDoutputList];
};

//-----Add input output parameters to process_parameters at revision
// startpoint: first object in data array where inputparameters starts.
function addProParatoDBbyRev(data, startPoint, process_id) {
    for (var i = startPoint; i < data.length; i++) {
        var dataToProcessParam = []; //dataToProcessPram to save in process_parameters table
        var PattPar = /(.*)-(.*)/;
        var matchFPart = '';
        var matchSPart = '';
        var matchVal = '';
        var matchFPart = data[i].name.replace(PattPar, '$1');
        var matchSPart = data[i].name.replace(PattPar, '$2');
        var matchVal = data[i].value;
        var perms = $('#permsPro').val();
        var group = $('#groupSelPro').val();
        if (matchFPart === 'mInputs' && matchVal !== '') {
            //first check if closures are visible
            if ($("#mInClosure-" + matchSPart).css('visibility') === 'visible') {
                for (var n = startPoint; n < data.length; n++) {
                    if (data[n].name === 'mInOpt-' + matchSPart) {
                        dataToProcessParam.push({ name: "operator", value: data[n].value });
                    } else if (data[n].name === 'mInClosure-' + matchSPart) {
                        dataToProcessParam.push({ name: "closure", value: encodeURIComponent(data[n].value) });
                    }
                }
            }
            //for process parameters 
            for (var k = startPoint; k < data.length; k++) {
                if (data[k].name === 'mInName-' + matchSPart && data[k].value === '') {
                    dataToProcessParam = [];
                    break;
                } else if (data[k].name === 'mInName-' + matchSPart && data[k].value !== '') {
                    dataToProcessParam.push({ name: "perms", value: "3" });
                    dataToProcessParam.push({ name: "group", value: group });
                    dataToProcessParam.push({ name: "parameter_id", value: matchVal });
                    dataToProcessParam.push({ name: "type", value: 'input' });
                    dataToProcessParam.push({ name: "sname", value: encodeURIComponent(data[k].value) });
                    dataToProcessParam.push({ name: "process_id", value: process_id });
                    dataToProcessParam.push({ name: "p", value: "saveProcessParameter" });
                }
            }
        } else if (matchFPart === 'mOutputs' && matchVal !== '') {
            //first check if regEx are visible
            if ($("#mOutReg-" + matchSPart).css('visibility') === 'visible') {
                for (var n = startPoint; n < data.length; n++) {
                    if (data[n].name === 'mOutReg-' + matchSPart) {
                        dataToProcessParam.push({ name: "reg_ex", value: encodeURIComponent(data[n].value) });
                    }
                }
            }
            //first check if closures are visible
            if ($("#mOutClosure-" + matchSPart).css('visibility') === 'visible') {
                for (var n = startPoint; n < data.length; n++) {
                    if (data[n].name === 'mOutOpt-' + matchSPart) {
                        dataToProcessParam.push({ name: "operator", value: data[n].value });
                    } else if (data[n].name === 'mOutClosure-' + matchSPart) {
                        dataToProcessParam.push({ name: "closure", value: encodeURIComponent(data[n].value) });
                    }
                }
            }
            //for process parameters 
            for (var k = startPoint; k < data.length; k++) {
                if (data[k].name === 'mOutName-' + matchSPart && data[k].value === '') {
                    dataToProcessParam = [];
                    break;
                } else if (data[k].name === 'mOutName-' + matchSPart && data[k].value !== '') {
                    dataToProcessParam.push({ name: "perms", value: "3" });
                    dataToProcessParam.push({ name: "group", value: group });
                    dataToProcessParam.push({ name: "parameter_id", value: matchVal });
                    dataToProcessParam.push({ name: "type", value: 'output' });
                    dataToProcessParam.push({ name: "sname", value: encodeURIComponent(data[k].value) });
                    dataToProcessParam.push({ name: "process_id", value: process_id });
                    dataToProcessParam.push({ name: "p", value: "saveProcessParameter" });
                }
            }
        }
        if (dataToProcessParam.length > 0) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: dataToProcessParam,
                async: true,
                success: function (s) {},
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    }
};

function updateProPara(inputsBefore, outputsBefore, ppIDinputList, ppIDoutputList, proID) {

    //Find deleted input/outputs
    for (var i = 0; i < inputsBefore.length; i++) {
        if (ppIDinputList.indexOf(inputsBefore[i].id) < 0) {
            //removeProcessParameter
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    id: inputsBefore[i].id,
                    p: "removeProcessParameter"
                },
                async: true,
                success: function () {},
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    }
    for (var i = 0; i < outputsBefore.length; i++) {
        if (ppIDoutputList.indexOf(outputsBefore[i].id) < 0) {
            //removeProcessParameter
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    id: outputsBefore[i].id,
                    p: "removeProcessParameter"
                },
                async: true,
                success: function () {},
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    }
};

function checkDeletion(proID) {
    var warnUser = false;
    var infoText = '';
    var startPoint = 6;
    //has selected process ever used in other pipelines?
    var checkPipe = checkPipeline(proID);
    if (checkPipe.length > 0) {
        warnUser = true;
        infoText = infoText + 'It is not allowed to remove this revision, since this revision of process exists in the following pipelines: '
        $.each(checkPipe, function (element) {
            if (element !== 0) {
                infoText = infoText + ', ';
            }
            infoText = infoText + '"' + checkPipe[element].name + '"';
        });
        infoText = infoText + '</br></br>In order to delete this revision, you may remove the process from above pipeline/pipelines.'

    }

    return [warnUser, infoText];
}

function checkParamDeletion(delparId) {
    var warnUser = false;
    var infoText = '';
    //has selected parameter ever used in other processes?
    var checkPara = checkParameter(delparId);
    if (checkPara.length > 0) {
        warnUser = true;
        infoText = infoText + 'It is not allowed to edit/remove this parameter, since it is exists in the following processes: '
        $.each(checkPara, function (element) {
            if (element !== 0) {
                infoText = infoText + ', ';
            }
            infoText = infoText + '"' + checkPara[element].name + '"';
        });
        infoText = infoText + '</br></br>In order to edit/delete this parameter, you may remove the parameter from above process/processes.'
    }
    return [warnUser, infoText];
}

function checkMenuGrDeletion(menu_id) {
    var warnUser = false;
    var infoText = '';
    //has selected parameter ever used in other processes?
    var checkMenu = checkMenuGr(menu_id);
    if (checkMenu.length > 0) {
        warnUser = true;
        infoText = infoText + 'It is not allowed to remove this menu group, since it contains following processes: '
        $.each(checkMenu, function (element) {
            if (element !== 0) {
                infoText = infoText + ', ';
            }
            infoText = infoText + '"' + checkMenu[element].name + '"';
        });
        infoText = infoText + '</br></br>In order to delete this menu group, you may remove above process/processes.'
    }
    return [warnUser, infoText];
}

function checkPipeMenuGrDeletion(menu_id) {
    var warnUser = false;
    var infoText = '';
    //has selected menu group ever used in other pipelines?
    var checkMenu = checkPipeMenuGr(menu_id);
    if (checkMenu.length > 0) {
        warnUser = true;
        infoText = infoText + 'It is not allowed to remove this menu group, since it contains following pipelines: '
        $.each(checkMenu, function (element) {
            if (element !== 0) {
                infoText = infoText + ', ';
            }
            infoText = infoText + '"' + checkMenu[element].name + '"';
        });
        infoText = infoText + '</br></br>In order to delete this menu group, you may remove above pipeline(s).'
    }
    return [warnUser, infoText];
}


function checkDeletionPipe(pipeline_id) {
    var warnUserPipe = false;
    var warnPipeText = '';
    //has selected pipeline ever used in projects?
    var checkProj = checkProject(pipeline_id);
    //has selected pipeline ever used in projects that user not owns?
    var checkProjPublic = checkProjectPublic(pipeline_id);
    var numOfProject = checkProj.length;
    var numOfProjectPublic = checkProjPublic.length;
    if (numOfProject > 0 && numOfProjectPublic === 0) {
        warnUserPipe = true;
        warnPipeText = warnPipeText + 'It is not allowed to remove this revision, since this revision of pipeline exists in the following projects: ';
        $.each(checkProj, function (element) {
            if (element !== 0) {
                warnPipeText = warnPipeText + ', ';
            }
            warnPipeText = warnPipeText + '"' + checkProj[element].name + '"';
        });
        warnPipeText = warnPipeText + '</br></br>In order to delete this revision, you may remove the pipeline from above project/projects.'
    } else if (numOfProjectPublic > 0) {
        warnUserPipe = true;
        warnPipeText = warnPipeText + 'This revision of pipeline already used in group/public projects, therefore it is not allowed to delete.';
    }
    return [warnUserPipe, warnPipeText];
}

//xxxxxxxx1
function checkRevisionPipe(pipeline_id) {
    var warnUserPipe = false;
    var warnPipeText = '';
    //has selected pipeline ever used in projects?
    var checkProj = checkProject(pipeline_id);
    //has selected pipeline ever used in projects that user not owns?
    var checkProjPublic = checkProjectPublic(pipeline_id);
    var numOfProject = checkProj.length;
    var numOfProjectPublic = checkProjPublic.length;
    if (numOfProject > 0 && numOfProjectPublic === 0) {
        warnUserPipe = true;
        warnPipeText = warnPipeText + 'This revision of pipeline already used in following project/projects: ';
        $.each(checkProj, function (element) {
            if (element !== 0) {
                warnPipeText = warnPipeText + ', ';
            }
            warnPipeText = warnPipeText + '"' + checkProj[element].name + '"';
        });
        warnPipeText = warnPipeText + '</br></br>Your changes may effect the current run/runs. If you still want to save on existing revision, please click on "save on existing" button. </br></br>Otherwise you can save as a new revision by entering revision comment at below and clicking the save button.'
    } else if (numOfProjectPublic > 0) {
        warnUserPipe = true;
        warnPipeText = warnPipeText + 'This revision of pipeline already used in following group/public projects: ';
        $.each(checkProjPublic, function (element) {
            if (element !== 0) {
                warnPipeText = warnPipeText + ', ';
            }
            warnPipeText = warnPipeText + '"' + checkProjPublic[element].name + '"';
        });
        warnPipeText = warnPipeText + '</br></br>You can save as a new revision by entering revision comment at below and clicking the save button.'
    }

    return [warnUserPipe, warnPipeText, numOfProject, numOfProjectPublic];
}

function checkRevisionProc(data, proID) {
    var warnUser = false;
    var infoText = '';
    var startPoint = 6;
    var inputProParams = prepareProParam(data, startPoint, 'inputs');
    var outputProParams = prepareProParam(data, startPoint, 'outputs');
    //Check if process name is changed 
    //xx
    //Check if process parameters is changed 
    var checkResult = checkProParameters(inputProParams, outputProParams, proID);
    if (checkResult === false) {
        //has edited process ever used in other pipelines?
        var checkPipe = checkPipeline(proID);
        var checkPipePublic = checkPipelinePublic(proID);
        var checkProPipePublic = checkProjectPipelinePublic(proID);
        var numOfProcess = checkPipe.length;
        var numOfProcessPublic = checkPipePublic.length;
        var numOfProPipePublic = checkProPipePublic.length;
        if (numOfProcess > 0 && numOfProcessPublic === 0) {
            warnUser = true;
            infoText = infoText + 'This revision of process already used in following pipeline/pipelines: ';
            $.each(checkPipe, function (element) {
                if (element !== 0) {
                    infoText = infoText + ', ';
                }
                infoText = infoText + '"' + checkPipe[element].name + '"';
            });
            infoText = infoText + '</br></br>Your changes may effect the current pipeline. If you still want to save on existing revision, please click on "save on existing" button. </br></br>Otherwise you can save as a new revision by entering revision comment at below and clicking the save button.'
        } else if (numOfProcessPublic > 0) {
            warnUser = true;
            infoText = infoText + 'This revision of process already used in group/public pipelines.';
            infoText = infoText + '</br></br>You can save as a new revision by entering revision comment at below and clicking the save button.'
        } else if (numOfProPipePublic > 0) {
            warnUser = true;
            infoText = infoText + 'This revision of process already used in group/public runs.';
            infoText = infoText + '</br></br>You can save as a new revision by entering revision comment at below and clicking the save button.'
        }
    }
    return [warnUser, infoText, numOfProcess, numOfProcessPublic, numOfProPipePublic];
}

function checkPermissionProc(proID) {
    var warnUser = false;
    var infoText = '';
    //has process ever used in other pipelines which are group or public?
    var checkPipe = getValues({ p: "checkPipelinePerm", "process_id": proID });
    var numOfPipelines = checkPipe.length;
    if (numOfPipelines > 0) {
        warnUser = true;
        infoText = infoText + 'It is not allowed to change permission of current revision since this revision of process exists in following group/public pipelines: '
        $.each(checkPipe, function (element) {
            if (element !== 0) {
                infoText = infoText + ', ';
            }
            infoText = infoText + '"' + checkPipe[element].name + '"';
        });
    }

    return [warnUser, infoText, numOfPipelines];
}

function checkPermissionPipe(pipeline_id) {
    var warnUser = false;
    var infoText = '';
    //has pipeline ever used in other project_pipeline which are group or public?
    var checkProjectPipeline = getValues({ p: "checkProjectPipePerm", "pipeline_id": pipeline_id });
    var numOfProjects = checkProjectPipeline.length;
    if (numOfProjects > 0) {
        warnUser = true;
        infoText = infoText + 'It is not allowed to change permission of current revision since this revision of pipeline exists in following group/public runs: '
        $.each(checkProjectPipeline, function (element) {
            if (element !== 0) {
                infoText = infoText + ', ';
            }
            infoText = infoText + '"' + checkProjectPipeline[element].name + '"';
        });
    }

    return [warnUser, infoText, numOfProjects];
}


function prepareProParam(data, startPoint, typeInOut) {
    if (typeInOut === 'inputs') {
        var searchFpart = 'mInputs';
        var searchName = 'mInName-';
    } else if (typeInOut === 'outputs') {
        var searchFpart = 'mOutputs';
        var searchName = 'mOutName-';
    }
    var proParams = [];
    for (var i = startPoint; i < data.length; i++) {
        var PattPar = /(.*)-(.*)/;
        var matchFPart = '';
        var matchSPart = '';
        var matchVal = '';
        var matchFPart = data[i].name.replace(PattPar, '$1')
        var matchSPart = data[i].name.replace(PattPar, '$2')
        var matchVal = data[i].value
        if (matchFPart === searchFpart && matchVal !== '') {
            for (var k = startPoint; k < data.length; k++) {
                if (data[k].name === searchName + matchSPart && data[k].value === '') {
                    break;
                } else if (data[k].name === searchName + matchSPart && data[k].value !== '') {
                    proParams.push({
                        "parameter_id": matchVal,
                        "name": data[k].value
                    });
                    break;
                }
            }
        }
    }
    return proParams;
}

function updateSideBar(sMenuProIdFirst, sMenuProIdFinal, sMenuProGroupIdFirst, sMenuProGroupIdFinal) {

    document.getElementById(sMenuProIdFirst).setAttribute('id', sMenuProIdFinal);
    var PattMenu = /(.*)@(.*)/; //Map_Tophat2@11
    var nMenuProName = sMenuProIdFinal.replace(PattMenu, '$1');
    document.getElementById(sMenuProIdFinal).innerHTML = '<i class="fa fa-angle-double-right"></i>' + truncateName(nMenuProName, 'sidebarMenu');
    if (sMenuProGroupIdFirst !== sMenuProGroupIdFinal) {
        document.getElementById(sMenuProIdFinal).remove();
        $('#side-' + sMenuProGroupIdFinal).append('<li> <a data-toggle="modal" data-target="#addProcessModal" data-backdrop="false" href="" ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="' + sMenuProIdFinal + '"> <i class="fa fa-angle-double-right"></i>' + truncateName(nMenuProName, 'sidebarMenu') + '</a></li>');
    }
}

function disableProModal(selProcessId) {
    var formValues = $('#addProcessModal').find('input, select, textarea');
    $('#mName').attr('disabled', "disabled");
    $('#mDescription').attr('disabled', "disabled");
    $('#mProcessGroup')[0].selectize.disable();
    editor.setReadOnly(true);
    editorProHeader.setReadOnly(true);
    editorProFooter.setReadOnly(true);
    //Ajax for selected process input/outputs
    var inputs = getValues({
        p: "getInputsPP",
        "process_id": selProcessId
    });
    var outputs = getValues({
        p: "getOutputsPP",
        "process_id": selProcessId
    });
    for (var i = 0; i < inputs.length; i++) {
        var numFormIn = i + 1;
        $('#mInputs-' + numFormIn)[0].selectize.disable();
        $('#mInName-' + numFormIn).attr('disabled', "disabled");
        $('#mInNamedel-' + numFormIn).remove();
        $('#mInClosure-' + numFormIn).attr('disabled', "disabled");
        $('#mInOpt-' + numFormIn).attr('disabled', "disabled");
        $('#mInOptBut-' + numFormIn).css("pointer-events", "none");
        $('#mInOptdel-' + numFormIn).remove();

    }
    //
    var delNumIn = numFormIn + 1;
    $('#mInputs-' + delNumIn + '-selectized').parent().parent().remove();
    for (var i = 0; i < outputs.length; i++) {
        var numFormOut = i + 1;
        $('#mOutputs-' + numFormOut)[0].selectize.disable();
        $('#mOutName-' + numFormOut).attr('disabled', "disabled");
        $('#mOutNamedel-' + numFormOut).remove();
        $('#mOutClosure-' + numFormOut).attr('disabled', "disabled");
        $('#mOutOpt-' + numFormOut).attr('disabled', "disabled");
        $('#mOutOptBut-' + numFormOut).css("pointer-events", "none");
        $('#mOutOptdel-' + numFormOut).remove();
        $('#mOutReg-' + numFormOut).attr('disabled', "disabled");
        $('#mOutRegBut-' + numFormOut).css("pointer-events", "none");
        $('#mOutRegdel-' + numFormOut).remove();
    }
    var delNumOut = numFormOut + 1;
    $('#mOutputs-' + delNumOut + '-selectized').parent().parent().remove();
    $('#mParameters').remove();
    $('#mProcessGroupAdd').remove();
    $('#mProcessGroupEdit').remove();
    $('#mProcessGroupDel').remove();
    $('#saveprocess').css('display', "none");
    $('#proPermGroPubDiv').css('display', "none");
    $('#mProActionsDiv').css('display', "inline");
    $('#createRevision').css('display', "none");
    $('#createRevisionBut').css('display', "none");
    $('#deleteRevision').css('display', "none");
    if (gNumInfo.match(/-/)) { //for pipeline module windows
        $('#selectProcess').css("display", "none")
    } else {
        $('#selectProcess').css('display', "inline");
    }

};
//disable when it is selected as everyone
function disableProModalPublic(selProcessId) {
    var formValues = $('#addProcessModal').find('input, select, textarea');
    $('#mName').attr('disabled', "disabled");
    $('#mDescription').attr('disabled', "disabled");
    //    $('#modeAce').attr('disabled', "disabled");
    $('#mProcessGroup')[0].selectize.disable();
    editor.setReadOnly(true);
    editorProHeader.setReadOnly(true);
    editorProFooter.setReadOnly(true);
    //Ajax for selected process input/outputs
    var inputs = getValues({
        p: "getInputsPP",
        "process_id": selProcessId
    });
    var outputs = getValues({
        p: "getOutputsPP",
        "process_id": selProcessId
    });
    for (var i = 0; i < inputs.length; i++) {
        var numFormIn = i + 1;
        $('#mInputs-' + numFormIn)[0].selectize.disable();
        $('#mInName-' + numFormIn).attr('disabled', "disabled");
        $('#mInNamedel-' + numFormIn).remove();
        $('#mInClosure-' + numFormIn).attr('disabled', "disabled");
        $('#mInOpt-' + numFormIn).attr('disabled', "disabled");
        $('#mInOptBut-' + numFormIn).css("pointer-events", "none");
        $('#mInOptdel-' + numFormIn).remove();

    }
    //
    var delNumIn = numFormIn + 1;
    $('#mInputs-' + delNumIn + '-selectized').parent().parent().remove();
    for (var i = 0; i < outputs.length; i++) {
        var numFormOut = i + 1;
        $('#mOutputs-' + numFormOut)[0].selectize.disable();
        $('#mOutName-' + numFormOut).attr('disabled', "disabled");
        $('#mOutNamedel-' + numFormOut).remove();
        $('#mOutClosure-' + numFormOut).attr('disabled', "disabled");
        $('#mOutOpt-' + numFormOut).attr('disabled', "disabled");
        $('#mOutOptBut-' + numFormOut).css("pointer-events", "none");
        $('#mOutOptdel-' + numFormOut).remove();
        $('#mOutReg-' + numFormOut).attr('disabled', "disabled");
        $('#mOutRegBut-' + numFormOut).css("pointer-events", "none");
        $('#mOutRegdel-' + numFormOut).remove();
    }
    var delNumOut = numFormOut + 1;
    $('#mOutputs-' + delNumOut + '-selectized').parent().parent().remove();
    $('#mParameters').remove();
    $('#mProcessGroupAdd').remove();
    $('#mProcessGroupEdit').remove();
    $('#mProcessGroupDel').remove();
    $('#saveprocess').css('display', "none");
    $('#deleteRevision').css('display', "none");
    $('#createRevision').css('display', "inline");
    $('#createRevisionBut').css('display', "inline");
};

function createRevPipeline(savedList, id, sName) {
    createPipeRev = "true";
    [savedList, id, sName] = save();

    var infoText = "Since this pipeline is public, it is not allowed for modification. You can save the pipeline as a new revision by entering revision comment and clicking the save button."
    $('#confirmRevision').off();
    $('#confirmRevision').on('show.bs.modal', function (event) {
        $(this).find('form').trigger('reset');
        $('#confirmYesNoText').html(infoText);
    });
    $('#confirmRevision').on('click', '#saveRev', function (event) {
        var confirmformValues = $('#confirmRevision').find('input');
        var revCommentData = confirmformValues.serializeArray();
        var revComment = revCommentData[0].value;
        if (revComment === '') { //xxx warn user to enter comment
        } else if (revComment !== '') {
            var pipeline_gid = getValues({ p: "getPipeline_gid", "pipeline_id": id })[0].pipeline_gid;
            var maxPipRev_id = getValues({ p: "getMaxPipRev_id", "pipeline_gid": pipeline_gid })[0].rev_id;
            var newPipRev_id = parseInt(maxPipRev_id) + 1;
            savedList[1].id = '';
            savedList[7].perms = '3';
            savedList[7].pin = 'false';
            savedList[10].publish = '0';
            savedList.push({ "pipeline_gid": pipeline_gid });
            savedList.push({ "rev_comment": revComment });
            savedList.push({ "rev_id": newPipRev_id });
            sl = JSON.stringify(savedList);
            var ret = getValues({ p: "saveAllPipeline", dat: sl });
            $('#confirmRevision').modal('hide');
            $('#autosave').text('Changes saved on new revision');
            setTimeout(function () { window.location.replace("index.php?np=1&id=" + ret.id); }, 700);
        }
    });
    $('#confirmRevision').modal('show');

}


function createRevision() {
    var infoText = "You can save the process as a new revision by entering revision comment at below and clicking the save button."
    $('#confirmRevision').off();
    $('#confirmRevision').on('show.bs.modal', function (event) {
        $(this).find('form').trigger('reset');
        $('#confirmYesNoText').html(infoText);
    });
    $('#confirmRevision').on('click', '#saveRev', function (event) {
        var proID = $('#mIdPro').val();
        $('#mName').removeAttr('disabled');
        var proName = $('#mName').val();
        var confirmformValues = $('#confirmRevision').find('input');
        var revCommentData = confirmformValues.serializeArray();
        var revComment = revCommentData[0].value;
        if (revComment === '') { //warn user to enter comment

        } else if (revComment !== '') {
            var proGroId = $('#mProcessGroup').val();
            var sMenuProIdFinal = proName + '@' + proID;
            var sMenuProGroupIdFinal = proGroId;
            var dataToProcess = []; //dataToProcess to save in process table
            var process_gid = getValues({ p: "getProcess_gid", "process_id": proID })[0].process_gid;
            var maxRev_id = getValues({ p: "getMaxRev_id", "process_gid": process_gid })[0].rev_id;
            var newRev_id = parseInt(maxRev_id) + 1;
            dataToProcess.push({ name: "id", value: proID });
            dataToProcess.push({ name: "rev_comment", value: revComment });
            dataToProcess.push({ name: "rev_id", value: newRev_id });
            dataToProcess.push({ name: "process_gid", value: process_gid });
            dataToProcess.push({ name: "p", value: "createProcessRev" });

            if (dataToProcess.length > 0) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: dataToProcess,
                    async: true,
                    success: function (s) {
                        var newProcess_id = s.id;
                        //update process link into sidebar menu
                        sMenuProIdFinal = proName + '@' + newProcess_id;
                        updateSideBar(sMenuProIdFirst, sMenuProIdFinal, sMenuProGroupIdFirst, sMenuProGroupIdFinal);
                        refreshDataset();
                        $('#addProcessModal').modal('hide');
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
            $('#confirmRevision').modal('hide');
        }
    });
    $('#confirmRevision').modal('show');

}

function prepareInfoModal() {
    $('#processmodaltitle').html('Select Process Revision');
    $('#mProActionsDiv').css('display', "none");
    $('#proPermGroPubDiv').css('display', "none");
    $('#mProRevSpan').css('display', "inline");
    $('#mProRev').attr('info', "info");
    $('#createRevisionBut').css('display', "none");
}

//xxxx
function loadPipelineDetails(pipeline_id, usRole) {
    var getPipelineD = [];
    getPipelineD.push({ name: "id", value: pipeline_id });
    getPipelineD.push({ name: "p", value: 'loadPipeline' });
    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: getPipelineD,
        async: true,
        success: function (s) {
            if (s[0]) {
                loadPipeMenuGroup(false);
                $('#creatorInfoPip').css('display', "block");
                $('#pipeline-title').changeVal(s[0].name);
                $('#ownUserNamePip').text(s[0].username);
                $('#pipelineSum').val(decodeHtml(s[0].summary));
                pipelineOwn = s[0].own;
                pipelinePerm = s[0].perms;
                // if user not own it, cannot change or delete pipeline
                if (pipelineOwn === "0") {
                    $('#deletePipeRevision').remove();
                    $('#delPipeline').remove();
                    $('#savePipeline').css('display', 'none');
                    $('#pipelineSum').attr('disabled', 'disabled');
                    editorPipeHeader.setReadOnly(true);
                    editorPipeFooter.setReadOnly(true);
                    $('#permsPipeDiv').css('display', 'none');
                    $('#groupSelPipeDiv').css('display', 'none');
                    $('#publishPipeDiv').css('display', 'none');
                    $('#pipeMenuGroupBottom').css('display', 'none');
                }
                if (usRole === "admin") {
                    $('#advOptDiv').css('display', 'inline');
                    $('#pinMainPage').css("display", "inline");
                    $('#savePipeline').css('display', 'inline');
                    $('#permsPipeDiv').css('display', 'inline');
                    $('#groupSelPipeDiv').css('display', 'inline');
                    $('#publishPipeDiv').css('display', 'inline');
                    $('#pipeMenuGroupBottom').css('display', 'inline');
                    $("#permsPro option[value='63']").attr("disabled", false);
                    $("#permsPipe option[value='63']").attr("disabled", false);
                }
                // fill Script_modes
                if (s[0].script_mode_header) {
                    $('#script_mode_pipe_header').val(s[0].script_mode_header);
                }
                if (s[0].script_mode_footer) {
                    $('#script_mode_pipe_footer').val(s[0].script_mode_footer);
                }
                //load header and foother script
                if (s[0].script_pipe_header !== "" && s[0].script_pipe_header !== null) {
                    var editorScriptPipeHeader = removeDoubleQuote(decodeHtml(s[0].script_pipe_header));
                    editorPipeHeader.setValue(editorScriptPipeHeader);
                    editorPipeHeader.clearSelection();
                }
                if (s[0].script_pipe_footer !== "" && s[0].script_pipe_footer !== null) {
                    var editorScriptPipeFooter = removeDoubleQuote(decodeHtml(s[0].script_pipe_footer));
                    editorPipeFooter.setValue(editorScriptPipeFooter);
                    editorPipeFooter.clearSelection();
                }
                //load user groups
                var allUserGrp = getValues({ p: "getUserGroups" });
                for (var i = 0; i < allUserGrp.length; i++) {
                    var param = allUserGrp[i];
                    var optionGroup = new Option(param.name, param.id);
                    $("#groupSelPipe").append(optionGroup);
                }
                if (s[0].group_id !== "0") {
                    $('#groupSelPipe').val(s[0].group_id);
                }
                $('#publishPipe').val(s[0].publish);
                // permissions
                $('#permsPipe').val(s[0].perms);
                if (s[0].perms === "63" && usRole !== "admin") {
                    $("#permsPipe").attr('disabled', "disabled");
                    $("#publishPipe").attr('disabled', "disabled");
                    $("#pipeGroupAll").attr('disabled', "disabled");
                    $('#deletePipeRevision').remove();
                    $('#delPipeline').remove();
                    $('#savePipeline').css('display', 'none');
                }
                if (s[0].pin === 'true') {
                    $('#pin').attr('checked', true);
                } else if (s[0].pin === "false") {
                    $('#pin').removeAttr('checked');
                }
                if (s[0].pin_order !== "0") {
                    $('#pin_order').val(s[0].pin_order);
                }
                $('#datecreatedPip').text(s[0].date_created);
                $('.lasteditedPip').text(s[0].date_modified);
                if (s[0].pipeline_group_id && s[0].pipeline_group_id != '') {
                    if ($("#pipeGroupAll option[value='" + s[0].pipeline_group_id + "']").length > 0) {
                        $('#pipeGroupAll').val(s[0].pipeline_group_id);
                        $('#pipeGroupAll').attr("pipe_group_id", s[0].pipeline_group_id);
                    }
                }

                // fill the footer script
                openPipeline(pipeline_id);
                checkNameUnique(processListNoOutput);
            }

        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });

    var getRevD = [];
    getRevD.push({ name: "pipeline_id", value: pipeline_id });
    getRevD.push({ name: "p", value: 'getPipelineRevision' });
    $.ajax({
        type: "GET",
        url: "ajax/ajaxquery.php",
        data: getRevD,
        async: false,
        success: function (s) {
            if (s.length > 1) {
                $("#pipeline-title").attr('disabled', "disabled");
            } else {
                $("#pipeline-title").removeAttr('disabled');
            }
            $("#pipeRev").empty();
            for (var i = 0; i < s.length; i++) {
                var param = s[i];
                if (param.id === pipeline_id) { //selected option
                    var optionAll = new Option('Revision: ' + param.rev_id + ' ' + param.rev_comment + ' on ' + param.date_modified, param.id, false, true);
                } else {
                    var optionAll = new Option('Revision: ' + param.rev_id + ' ' + param.rev_comment + ' on ' + param.date_modified, param.id);
                }
                $("#pipeRev").append(optionAll);
            }
        }
    });

};


function duplicateProcessRev() {
    var processID = $('#mIdPro').val();
    if (processID !== '') {
        var proName = $('#mName').val() + "-copy";
        var proGroId = $('#mProcessGroup').val();
        var maxProcess_gid = getValues({ p: "getMaxProcess_gid" })[0].process_gid;
        var newProcess_gid = parseInt(maxProcess_gid) + 1;
        var s = getValues({
            p: "duplicateProcess",
            'process_gid': newProcess_gid,
            'name': proName,
            'id': processID
        });
        if (s) {
            var process_id = s.id;
            //add process link into sidebar menu
            $('#side-' + proGroId).append('<li> <a data-toggle="modal" data-target="#addProcessModal" data-backdrop="false" href="" ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="' + proName + '@' + process_id + '"> <i class="fa fa-angle-double-right"></i>' + truncateName(proName, 'sidebarMenu') + '</a></li>');
            refreshDataset();
            $('#addProcessModal').modal('hide');
        }
    }
}

function getScriptEditor(editorId) {
    var scripteditor = window[editorId].getValue();
    scripteditor = encodeURIComponent(scripteditor);
    return scripteditor
}


setTimeout(function () { AddNamespace() }, 1000);

// to export d3 to pdf this is required
function AddNamespace() {
    var svg = jQuery('#container svg');
    svg.attr("xmlns", "http://www.w3.org/2000/svg");
}
// to export d3 to pdf
function downloadPdf() {
    var svg = jQuery('#container svg');
    var svgWidth = parseInt(svg.width() * 0.264583333) + 30;
    var svgHeight = parseInt(svg.height() * 0.264583333);
    if (svgWidth < 160) {
        svgWidth = 160;
    }
    if (svgHeight < 160) {
        svgHeight = 160;
    }
    svgWidth = svgWidth.toString() + "mm";
    svgHeight = svgHeight.toString() + "mm";
    var filename = $('#pipeline-title').val()
    return xepOnline.Formatter.Format('container', { filename: filename, pageWidth: svgWidth, pageHeight: svgHeight });
}


//xxxxxxx
function loadSelectedPipeline(pipeline_id) {
    var pData = getValues({ p: "loadPipeline", id: pipeline_id })
    var pDataTable = [];
    if (pData) {
        if (Object.keys(pData).length > 0) {
            console.log(pData)
            $('#selectPipeline').attr("pName", pData[0].name);
            var nodes = pData[0].nodes
            nodes = JSON.parse(nodes.replace(/'/gi, "\""))
            $.each(nodes, function (el) {
                if (nodes[el][2].indexOf("inPro") === -1 && nodes[el][2].indexOf("outPro") === -1) {
                    pDataTable.push({ process_name: nodes[el][3], process_id: nodes[el][2] })
                }
            });
            console.log(pDataTable)
            if (pDataTable.length > 0) {
                for (var i = 0; i < pDataTable.length; i++) {
                    if (pDataTable[i].process_id.match(/p/)) {
                        var pipeModId = pDataTable[i].process_id.match(/p(.*)/)[1]
                        var proData = getValues({ p: "loadPipeline", id: pipeModId })
                    } else {
                        var proData = getValues({ p: "getProcessData", "process_id": pDataTable[i].process_id });
                    }
                    if (proData) {
                        pDataTable[i]["rev_id"] = proData[0].rev_id;
                        pDataTable[i]["rev_comment"] = proData[0].rev_comment;
                        pDataTable[i]["date_modified"] = proData[0].date_modified;
                    }
                }
            }
            $('#selectPipeTable').DataTable({
                destroy: true,
                "data": pDataTable,
                "hover":true,
                "columns": [{
                "data": "process_name",
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    if (oData.process_id.match("p")){
                        $(nTd).text(oData.process_name);
                    } else {
                        $(nTd).html("<a data-toggle='modal' data-target='#addProcessModal' data-backdrop='false' href='' pipeMode='true' id='"+oData.process_name+ "@" + oData.process_id + "'>" +'<span class="txtlink">'+oData.process_name+"</span>"  + "</a>");
                    }
                }
            }, {
                    "data": "rev_id"
            }, {
                    "data": "rev_comment"
            }, {
                    "data": "date_modified"
            }]
            });
        }
    }
}

$('#selectPipelineModal').on('hidden.bs.modal', function (ev) {
    $('#selectPipeTable').dataTable().fnDestroy();
    $('#mPipeRev')[0].selectize.destroy();
    $('#selectPipeline').css("display","inline")
});

$('#selectPipelineModal').on('show.bs.modal', function (ev) {
    var selPipelineId = infoID;
    $('#selectPipeline').attr("fPipeID", selPipelineId);
    $('#selectPipeline').attr("gNum", gNumInfo);
    if (gNumInfo.match(/-/)) { //for pipeline module windows
        var coorProRaw = d3.select("#g" + gNumInfo)[0][0].attributes.transform.value;
        $('#selectPipeline').css("display","none")
    } else {
        var coorProRaw = d3.select("#g-" + gNumInfo)[0][0].attributes.transform.value;
    }
    var PattCoor = /translate\((.*),(.*)\)/; //417.6,299.6
    var xProCoor = coorProRaw.replace(PattCoor, '$1');
    var yProCoor = coorProRaw.replace(PattCoor, '$2');
    $('#selectPipeline').attr("xCoor", xProCoor);
    $('#selectPipeline').attr("yCoor", yProCoor);
    loadPipeModalRevision(selPipelineId);
    loadSelectedPipeline(selPipelineId);
});

//xxxxxxxxx
$("#selectPipelineModal").on('click', '#selectPipeline', function (event) {
    event.preventDefault();
    var gNumInfo = $('#selectPipeline').attr("gNum");
    var firstPipeID = $('#selectPipeline').attr("fPipeID");
    var lastPipeID = $('#selectPipeline').attr("lastPipeID");
    var pName = $('#selectPipeline').attr("pName");
    if (lastPipeID && lastPipeID !== firstPipeID) {
        remove('del-' + gNumInfo);
        var d3main = d3.transform(d3.select('#' + "mainG").attr("transform"));
        var scale = d3main.scale[0];
        var translateX = d3main.translate[0];
        var translateY = d3main.translate[1];
        var xPos = $('#selectPipeline').attr("xCoor")
        var yPos = $('#selectPipeline').attr("yCoor")
        piID = lastPipeID
        var newMainGnum = "pObj" + gNum;
        window[newMainGnum] = {};
        window[newMainGnum].piID = piID;
        window[newMainGnum].MainGNum = gNum;
        window[newMainGnum].lastGnum = gNum;
        window[newMainGnum].sData = getValues({ p: "loadPipeline", id: piID })
        window[newMainGnum].lastPipeName = pName;
        //            var proName = window[newMainGnum].sData[0].name;
        // create new SVG workplace inside panel, if not added before
        openSubPipeline(piID, window[newMainGnum]);
        // add pipeline circle to main workplace
        addPipeline(piID, xPos, yPos, pName, window, window[newMainGnum]);
    }
    $('#selectPipelineModal').modal('hide');
});


$(document).ready(function () {
    var usRole = callusRole();
    pipeline_id = $('#pipeline-title').attr('pipelineid');
    //fill pipeline groups
    if (pipeline_id !== '') {
        $('#pipeMenuGroupTop').css('display', 'none');
        $('#pipeMenuGroupBottom').css('display', 'inline');
        $('#pipeSepBar').remove()
        $('#pipeGroupIcon').remove()
        $("#pipeMenuGroupTop").appendTo($("#pipeMenuGroupBottom"));
        $('#pipeMenuGroupTop').css('display', 'inline');
        loadPipelineDetails(pipeline_id, usRole);

    } else { // fresh page
        $('#pipeMenuGroupTop').css('display', 'inline')

        //load user groups
        var allUserGrp = getValues({ p: "getUserGroups" });
        for (var i = 0; i < allUserGrp.length; i++) {
            var param = allUserGrp[i];
            var optionGroup = new Option(param.name, param.id);
            $("#groupSelPipe").append(optionGroup);
            $("#groupSelPro").append(optionGroup);
        }
        loadPipeMenuGroup(true);
    }

    //Make modal draggable    
    $('.modal-dialog').draggable({ cancel: 'input, textarea, select, #editordiv, #editorHeaderdiv, #editorFooterdiv, button, span, a, #amzTable' });



    stateModule = (function () {
        var state = {}; // Private Variable
        var pub = {}; // public object - returned at end of module
        pub.changeState = function (newstate, backNode) {
            state[newstate] = backNode;
            //selectize gives error when using copied node clone. Therefore HTML part is kept separate and replaced at getState
            state[newstate + 'HTML'] = backNode.html();
        };
        pub.getState = function (getName) {
            state[getName].html(state[getName + 'HTML']);
            return state[getName];
        }
        return pub; // expose externally
    }());


    //xxxx
    $("#addProcessModal").on('click', '#selectProcess', function (event) {
        event.preventDefault();
        var gNumInfo = $('#selectProcess').attr("gNum");
        var firstProID = $('#selectProcess').attr("fProID");
        var lastProID = $('#selectProcess').attr("lastProID");
        var pName = $('#selectProcess').attr("pName");
        if (lastProID && lastProID !== firstProID) {
            var processDat = pName + '@' + lastProID;
            remove('del-' + gNumInfo);
            var d3main = d3.transform(d3.select('#' + "mainG").attr("transform"));
            var scale = d3main.scale[0];
            var translateX = d3main.translate[0];
            var translateY = d3main.translate[1];
            var xCor = $('#selectProcess').attr("xCoor") * scale + 30 - r - ior + translateX;
            var yCor = $('#selectProcess').attr("yCoor") * scale + 10 - r - ior + translateY;
            addProcess(processDat, xCor, yCor);
        }
        autosave();
        $('#addProcessModal').modal('hide');
    });



    renderParam = {
        option: function (data, escape) {
            if (data.qualifier === 'val') {
                return '<div class="option">' +
                    '<span class="title">' + escape(data.name) + '</span>' +
                    '<span class="url">' + 'Qualifier: ' + escape(data.qualifier) + '</span>' +
                    '</div>';
            } else {
                return '<div class="option">' +
                    '<span class="title">' + escape(data.name) + '</span>' +
                    '<span class="url">' + 'File Type: ' + escape(data.file_type) + '</span>' +
                    '<span class="url">' + 'Qualifier: ' + escape(data.qualifier) + '</span>' +
                    '</div>';
            }

        },
        item: function (data, escape) {
            if (data.qualifier === 'val') {
                return '<div class="item" data-value="' + escape(data.id) + '">' + escape(data.name) + '  <i><small>' + '  (' + escape(data.qualifier) + ')</small></i>' + '</div>';
            } else {
                return '<div class="item" data-value="' + escape(data.id) + '">' + escape(data.name) + '  <i><small>' + '  (' + escape(data.file_type) + ', ' + escape(data.qualifier) + ')</small></i>' + '</div>';
            }
        }
    };

    renderRev = {
        option: function (data, escape) {
            return '<div class="option">' +
                '<span class="title">' + escape(data.rev_id) + '<i> ' + escape(data.rev_comment) + '' + ' on ' + escape(data.date_created) + '</i></span>' +
                '</div>';
        },
        item: function (data, escape) {
            return '<div class="item" data-value="' + escape(data.id) + '">Revision: ' + escape(data.rev_id) + '</div>';
        }
    };

    $("#permsPro").click(function () {
        lastSel = $("#permsPro option:selected");
    });
    $("#permsPipe").click(function () {
        lastSelPipe = $("#permsPipe option:selected");
    });
    $(function () {
        $(document).on('change', '#permsPipe', function (event) {
            var selPerm = $(this).val();
            var pipeline_id = $('#pipeline-title').attr('pipelineid');
            if (pipeline_id !== "" && selPerm == "3") {
                var warnUser = false;
                var infoText = '';
                var numOfRuns = '';
                //check if pipeline ever used in projects_pipelines that and have permission higher than 3
                //then not allowed to change
                [warnUser, infoText, numOfRuns] = checkPermissionPipe(pipeline_id);
                if (warnUser === true) {
                    lastSelPipe.prop("selected", true);
                    // warnDelete process modal 
                    $('#warnDelete').off();
                    $('#warnDelete').on('show.bs.modal', function (event) {
                        $(this).find('form').trigger('reset');
                        $('#warnDelText').html(infoText);
                    });
                    $('#warnDelete').modal('show');
                } else {
                    autosaveDetails();
                }
            } else {
                autosaveDetails();
            }
        })
    });

    $(function () {
        $(document).on('change', '#permsPro', function (event) {
            var selPerm = $(this).val();
            var proID = $('#mIdPro').val();
            if (proID !== "" && selPerm == "3") {
                var warnUser = false;
                var infoText = '';
                var numOfPipelines = '';
                //check if process ever used in pipelines that and have permission higher than 3
                //then not allowed to change
                [warnUser, infoText, numOfPipelines] = checkPermissionProc(proID);
                if (warnUser === true) {
                    lastSel.prop("selected", true);
                    // warnDelete process modal 
                    $('#warnDelete').off();
                    $('#warnDelete').on('show.bs.modal', function (event) {
                        $(this).find('form').trigger('reset');
                        $('#warnDelText').html(infoText);
                    });
                    $('#warnDelete').modal('show');
                }
            }
        })
    });

    $(function () {
        $(document).on('change', '.mRevChange', function (event) {
            var infoAttr = $(this).attr("info");
            var id = $(this).attr("id");
            var prevParId = $("#" + id).attr("prev");
            var selProId = $("#" + id + " option:selected").val();
            if (prevParId !== '-1' && infoAttr === 'info') {
                refreshProcessModal(selProId);
                prepareInfoModal();
                disableProModal(selProId);
                $('#selectProcess').attr("lastProID", selProId);
                $('#mProRev').attr('info', "info");
            } else if (prevParId !== '-1') {
                refreshProcessModal(selProId);
            }
            $("#" + id).attr("prev", selProId);
        })
    });

    $(function () {
        $(document).on('change', '#pipeRev', function (event) {
            var selPipeRev = $('#pipeRev option:selected').val();
            window.location.replace("index.php?np=1&id=" + selPipeRev);
        })
    });





    $(function () {
        $(document).on('change', '.mPipeRevChange', function (event) {
            var id = $(this).attr("id");
            var prevParId = $("#" + id).attr("prev");
            var selPipeId = $("#" + id + " option:selected").val();
            if (prevParId !== '-1') {
                loadSelectedPipeline(selPipeId);
                $('#selectPipeline').attr("lastPipeID", selPipeId);
            }
            $("#" + id).attr("prev", selPipeId);
        })
    });




    infoID = '';
    //Add Process Modal
    $('#addProcessModal').on('show.bs.modal', function (event) {
        $(this).find('form').trigger('reset');
        var backUpObj = {};
        backUpObj.menuRevBackup = $('#revModalHeader').clone();
        backUpObj.menuGrBackup = $('#proGroup').clone();
        backUpObj.inTitleBackup = $('#inputTitle').clone();
        backUpObj.inBackup = $('#inputGroup').clone();
        backUpObj.outTitleBackup = $('#outputTitle').clone();
        backUpObj.outBackup = $('#outputGroup').clone();
        backUpObj.allBackup = $('#mParameters').clone();
        stateModule.changeState("menuRevBackup", backUpObj.menuRevBackup);
        stateModule.changeState("menuGrBackup", backUpObj.menuGrBackup);
        stateModule.changeState("inBackup", backUpObj.inBackup);
        stateModule.changeState("inTitleBackup", backUpObj.inTitleBackup);
        stateModule.changeState("outBackup", backUpObj.outBackup);
        stateModule.changeState("outTitleBackup", backUpObj.outTitleBackup);
        stateModule.changeState("allBackup", backUpObj.allBackup);
        stateModule.changeState("menuRevBackup", backUpObj.menuRevBackup);

        editor.setValue(templatesh);
        loadModalProGro();
        loadModalParam();

        var button = $(event.relatedTarget);
        if (button.attr('id') === 'addprocess') {
            $('#processmodaltitle').html('Add New Process');
            $('#proPermGroPubDiv').css('display', "inline");

        } else if (button.is('a') === true) { //Edit/Delete Process
            $('#processmodaltitle').html('Edit/Delete Process');
            $('#mProActionsDiv').css('display', "inline");
            $('#mProRevSpan').css('display', "inline");
            $('#proPermGroPubDiv').css('display', "inline");

            delProMenuID = button.attr('id');
            sMenuProIdFirst = button.attr('id');
            var pipeMode = button.attr('pipeMode');
            var PattPro = /(.*)@(.*)/; //Map_Tophat2@11
            var selProcessId = button.attr('id').replace(PattPro, '$2');
            loadModalRevision(selProcessId);
            var processOwn = "";
            var proPerms = "";
            [proPerms, processOwn] = loadSelectedProcess(selProcessId);
            console.log(pipeMode)
            if (pipeMode){
                $('#permsPro').attr('disabled', "disabled");
                $('#publishPro').attr('disabled', "disabled");
                disableProModalPublic(selProcessId);
                $('#createRevision').css('display', "none");
                $('#createRevisionBut').css('display', "none");
                $('#mProActionsDiv').css('display', "none");
            } else if (usRole === "admin") {
                $("#permsPro option[value='63']").attr("disabled", false);
            } else if (processOwn === "1" && proPerms === "63" && usRole !== "admin") {
                $('#permsPro').attr('disabled', "disabled");
                $('#publishPro').attr('disabled', "disabled");
                disableProModalPublic(selProcessId);
            }
            // if user is the owner of the process (processOwn=1) allowed for edit and delete.
            else if (processOwn === "0" && usRole !== "admin") {
                setTimeout(function () { prepareInfoModal(); }, 0);
                var pName = $('#mName').val();
                $('#selectProcess').attr("pName", pName);
                setTimeout(function () { disableProModal(selProcessId); }, 1);
            }
        } else { //Info Modal 
            prepareInfoModal();
            var selProcessId = infoID;
            $('#selectProcess').attr("fProID", selProcessId);
            $('#selectProcess').attr("gNum", gNumInfo);
            if (gNumInfo.match(/-/)) { //for pipeline module windows
                var coorProRaw = d3.select("#g" + gNumInfo)[0][0].attributes.transform.value;
            } else {
                var coorProRaw = d3.select("#g-" + gNumInfo)[0][0].attributes.transform.value;
            }
            var PattCoor = /translate\((.*),(.*)\)/; //417.6,299.6
            var xProCoor = coorProRaw.replace(PattCoor, '$1');
            var yProCoor = coorProRaw.replace(PattCoor, '$2');
            $('#selectProcess').attr("xCoor", xProCoor);
            $('#selectProcess').attr("yCoor", yProCoor);

            loadModalRevision(selProcessId);
            loadSelectedProcess(selProcessId);
            var pName = $('#mName').val();
            $('#selectProcess').attr("pName", pName);
            setTimeout(function () { disableProModal(selProcessId); }, 1);
        }
    });

    // Dismiss process modal 
    $('#addProcessModal').on('hide.bs.modal', function (event) {
        cleanProcessModal();
        cleanInfoModal();
    });

    // Delete process pipeline modal 
    $('#confirmModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        if (button.attr('id') === 'deleteRevision') {
            $('#deleteBtn').attr('class', 'btn btn-primary delprocess');
            $('#confirmModalText').html('Are you sure you want to delete this process revision?');
        } else if (button.attr('id') === 'deletePipeRevision' || button.attr('id') === 'delPipeline') {
            $('#deleteBtn').attr('class', 'btn btn-primary delpipeline');
            $('#confirmModalText').html('Are you sure you want to delete this pipeline?');
        }
    });

    $('#confirmModal').on('click', '.delpipeline', function (event) {
        var pipeline_id = $('#pipeline-title').attr('pipelineid');
        if (pipeline_id !== '') {
            var warnUserPipe = false;
            var warnPipeText = '';
            [warnUserPipe, warnPipeText] = checkDeletionPipe(pipeline_id);
            //A. If it is allowed to delete    
            if (warnUserPipe === false) {
                delPipeline();
            }
            //B. If it is not allowed to delete
            else if (warnUserPipe === true) {
                $('#warnDelete').off();
                $('#warnDelete').on('show.bs.modal', function (event) {
                    $('#warnDelText').html(warnPipeText);
                });
                $('#warnDelete').modal('show');
            }
            $('#confirmModal').modal('hide');
        }
    })

    $('#confirmModal').on('click', '.delprocess', function (event) {
        var processIdDel = $('#mIdPro').val();
        var disableCheck = $('#mName').attr('disabled');
        if (disableCheck === 'disabled') {
            $('#mName').removeAttr('disabled'); //temporary remove disabled attribute for serializeArray().
            var formValues = $('#addProcessModal').find('input, select, textarea');
            var data = formValues.serializeArray();
            $('#mName').attr('disabled', "disabled");
        } else {
            var formValues = $('#addProcessModal').find('input, select, textarea');
            var data = formValues.serializeArray();
        }
        var proID = data[1].value;
        var proName = data[2].value;
        var warnUser = false;
        var infoText = '';
        [warnUser, infoText] = checkDeletion(proID);
        if (warnUser === true) {
            $('#warnDelete').off();
            $('#warnDelete').on('show.bs.modal', function (event) {
                $('#warnDelText').html(infoText);
            });
            $('#warnDelete').modal('show');
        } else if (warnUser === false) {
            var revisions = getValues({ p: "getProcessRevision", "process_id": processIdDel });
            var removedRev = [];
            if (revisions.length === 1) {
                var delProce = getValues({ p: "removeProcess", "id": processIdDel });
                var delSideMenuNode = document.getElementById(delProMenuID).parentNode;
                delSideMenuNode.parentNode.removeChild(delSideMenuNode);
                delProMenuID = '';
            } else if (revisions.length > 1) {
                //remove the selected revision from list
                for (var k = 0; k < revisions.length; k++) {
                    if (revisions[k].id !== processIdDel) {
                        removedRev.push(revisions[k]);
                    }
                }
                //find  the maximum rev_id in the list
                let max = removedRev[0].rev_id;
                for (let i = 1, len = removedRev.length; i < len; i++) {
                    let v = removedRev[i].rev_id;
                    max = (v > max) ? v : max;
                }
                //find the id of the process which has the maximum rev_id
                for (var k = 0; k < removedRev.length; k++) {
                    if (removedRev[k].rev_id === max) {
                        var revMaxId = removedRev[k].id
                    }
                }
                var PattPro = /(.*)@(.*)/; //Map_Tophat2@11
                var delProName = delProMenuID.replace(PattPro, '$1');
                var newMenuID = delProName + '@' + revMaxId;
                var delProce = getValues({ p: "removeProcess", "id": processIdDel });
                document.getElementById(delProMenuID).id = newMenuID;
            }
            $('#addProcessModal').modal('hide');
        }
    });
    //Add new parameter modal
    $('#parametermodal').on('click', '#mParamOpen', function (event) {
        $('#mParamsDynamic').css('display', "none");
        $('#mParamList').css('display', "inline");
    });


    // Add process modal to database
    $('#addProcessModal').on('click', '#saveprocess', function (event) {
        event.preventDefault();
        var savetype = $('#mIdPro').val();
        $('#permsPro').removeAttr('disabled');
        $('#publishPro').removeAttr('disabled');
        $("#permsPro option[value='63']").attr("disabled", false);
        var perms = $('#permsPro').val();
        var publish = $('#publishPro').val();
        $("#permsPro option[value='63']").attr("disabled", true);
        $('#permsPro').attr('disabled', "disabled");
        $('#publishPro').attr('disabled', "disabled");
        var group = $('#groupSelPro').val();
        if (!group) {
            group = "";
        }

        // A) Add New Process Starts
        if (!savetype.length) {
            var formValues = $('#addProcessModal').find('input, select, textarea');
            var data = formValues.serializeArray();
            data[1].value = cleanProcessName(data[1].value);
            var dataToProcess = []; //dataToProcess to save in process table
            var proName = data[1].value;
            var proGroId = data[4].value;
            for (var i = 0; i < 5; i++) {
                dataToProcess.push(data[i]);
            }
            var scripteditor = getScriptEditor('editor');
            var scripteditorProHeader = getScriptEditor('editorProHeader');
            var scripteditorProFooter = getScriptEditor('editorProFooter');
            var maxProcess_gid = getValues({ p: "getMaxProcess_gid" })[0].process_gid;
            var newProcess_gid = parseInt(maxProcess_gid) + 1;
            var script_mode = $('#script_mode').val();
            var script_mode_header = $('#script_mode_header').val();
            dataToProcess.push({ name: "perms", value: perms });
            dataToProcess.push({ name: "group", value: group });
            dataToProcess.push({ name: "publish", value: publish });
            dataToProcess.push({ name: "process_gid", value: newProcess_gid });
            dataToProcess.push({ name: "script", value: scripteditor });
            dataToProcess.push({ name: "script_mode", value: script_mode });
            dataToProcess.push({ name: "script_mode_header", value: script_mode_header });
            dataToProcess.push({ name: "script_header", value: scripteditorProHeader });
            dataToProcess.push({ name: "script_footer", value: scripteditorProFooter });
            dataToProcess.push({ name: "p", value: "saveProcess" });
            if (proName === '' || proGroId === '') {
                dataToProcess = [];
            }
            if (dataToProcess.length > 0) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: dataToProcess,
                    async: true,
                    success: function (s) {
                        var process_id = s.id;
                        //add process link into sidebar menu
                        $('#side-' + proGroId).append('<li> <a data-toggle="modal" data-target="#addProcessModal" data-backdrop="false" href="" ondragstart="dragStart(event)" ondrag="dragging(event)" draggable="true" id="' + proName + '@' + process_id + '"> <i class="fa fa-angle-double-right"></i>' + truncateName(proName, 'sidebarMenu') + '</a></li>');
                        var startPoint = 5; //first object in data array where inputparameters starts.
                        addProParatoDB(data, startPoint, process_id);
                        refreshDataset();
                        $('#addProcessModal').modal('hide');
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        }
        // A) Add New Process Ends----

        // B) Edit Process Starts
        if (savetype.length) {
            $('#mName').removeAttr('disabled'); //temporary remove disabled attribute for serializeArray().
            var formValues = $('#addProcessModal').find('input, select, textarea');
            var data = formValues.serializeArray();
            data[2].value = cleanProcessName(data[2].value);
            $('#mName').attr('disabled', "disabled");

            var proID = data[1].value;
            var proName = data[2].value;
            var warnUser = false;
            var infoText = '';
            var numOfProcess = '';
            var numOfProcessPublic = '';
            var numOfProPipePublic = '';
            [warnUser, infoText, numOfProcess, numOfProcessPublic, numOfProPipePublic] = checkRevisionProc(data, proID);
            //B.1)Save on current process
            if (warnUser === false) {
                var proGroId = data[5].value;
                var sMenuProIdFinal = proName + '@' + proID;
                var sMenuProGroupIdFinal = proGroId;
                var dataToProcess = []; //dataToProcess to save in process table
                for (var i = 1; i < 6; i++) {
                    dataToProcess.push(data[i]);
                }
                var scripteditor = getScriptEditor('editor');
                var scripteditorProHeader = getScriptEditor('editorProHeader');
                var scripteditorProFooter = getScriptEditor('editorProFooter');
                var process_gid = getValues({ p: "getProcess_gid", "process_id": proID })[0].process_gid;
                var script_mode = $('#script_mode').val();
                var script_mode_header = $('#script_mode_header').val();
                dataToProcess.push({ name: "script_mode", value: script_mode });
                dataToProcess.push({ name: "script_mode_header", value: script_mode_header });
                dataToProcess.push({ name: "perms", value: perms });
                dataToProcess.push({ name: "group", value: group });
                dataToProcess.push({ name: "publish", value: publish });
                dataToProcess.push({ name: "process_gid", value: process_gid });
                dataToProcess.push({ name: "script_header", value: scripteditorProHeader });
                dataToProcess.push({ name: "script_footer", value: scripteditorProFooter });
                dataToProcess.push({ name: "script", value: scripteditor });
                dataToProcess.push({ name: "p", value: "saveProcess" });
                if (proName === '' || proGroId === '') {
                    dataToProcess = [];
                }
                if (dataToProcess.length > 0) {
                    $.ajax({
                        type: "POST",
                        url: "ajax/ajaxquery.php",
                        data: dataToProcess,
                        async: true,
                        success: function (s) {
                            //update process link into sidebar menu
                            updateSideBar(sMenuProIdFirst, sMenuProIdFinal, sMenuProGroupIdFirst, sMenuProGroupIdFinal);
                            var startPoint = 6; //first object in data array where inputparameters starts.
                            var ppIDinputList;
                            var ppIDoutputList;
                            var inputsBefore = getValues({ p: "getInputsPP", "process_id": proID });
                            var outputsBefore = getValues({ p: "getOutputsPP", "process_id": proID });
                            [ppIDinputList, ppIDoutputList] = addProParatoDB(data, startPoint, proID);
                            updateProPara(inputsBefore, outputsBefore, ppIDinputList, ppIDoutputList, proID);
                            refreshDataset();
                            $('#addProcessModal').modal('hide');
                        },
                        error: function (errorThrown) {
                            alert("Error: " + errorThrown);
                        }
                    });
                }
                //B.2) Save on new revision
            } else if (warnUser === true) {
                // ConfirmYesNo process modal 
                $('#confirmRevision').off();
                $('#confirmRevision').on('show.bs.modal', function (event) {
                    $(this).find('form').trigger('reset');
                    $('#confirmYesNoText').html(infoText);
                    if ((numOfProcessPublic === 0 && numOfProPipePublic === 0) || usRole == "admin") {
                        $('#saveOnExist').css('display', 'inline');
                        if (usRole == "admin" && !(numOfProcessPublic === 0 && numOfProPipePublic === 0)) {
                            $('#saveOnExist').attr('class', 'btn btn-danger');
                        }
                    }
                });
                $('#confirmRevision').on('hide.bs.modal', function (event) {
                    $('#saveOnExist').css('display', 'none');
                    $('#saveOnExist').attr('class', 'btn btn-warning');
                });
                $('#confirmRevision').on('click', '#saveOnExist', function (event) {
                    var proGroId = data[5].value;
                    var sMenuProIdFinal = proName + '@' + proID;
                    var sMenuProGroupIdFinal = proGroId;
                    var dataToProcess = []; //dataToProcess to save in process table
                    for (var i = 1; i < 6; i++) {
                        dataToProcess.push(data[i]);
                    }
                    var scripteditor = getScriptEditor('editor');
                    var scripteditorProHeader = getScriptEditor('editorProHeader');
                    var scripteditorProFooter = getScriptEditor('editorProFooter');
                    var process_gid = getValues({ p: "getProcess_gid", "process_id": proID })[0].process_gid;
                    var script_mode = $('#script_mode').val();
                    var script_mode_header = $('#script_mode_header').val();
                    dataToProcess.push({ name: "script_mode", value: script_mode });
                    dataToProcess.push({ name: "script_mode_header", value: script_mode_header });
                    dataToProcess.push({ name: "perms", value: perms });
                    dataToProcess.push({ name: "group", value: group });
                    dataToProcess.push({ name: "publish", value: publish });
                    dataToProcess.push({ name: "process_gid", value: process_gid });
                    dataToProcess.push({ name: "script_header", value: scripteditorProHeader });
                    dataToProcess.push({ name: "script_footer", value: scripteditorProFooter });
                    dataToProcess.push({ name: "script", value: scripteditor });
                    dataToProcess.push({ name: "p", value: "saveProcess" });
                    if (proName === '' || proGroId === '') {
                        dataToProcess = [];
                    }
                    if (dataToProcess.length > 0) {
                        $.ajax({
                            type: "POST",
                            url: "ajax/ajaxquery.php",
                            data: dataToProcess,
                            async: true,
                            success: function (s) {
                                //update process link into sidebar menu
                                updateSideBar(sMenuProIdFirst, sMenuProIdFinal, sMenuProGroupIdFirst, sMenuProGroupIdFinal);
                                var startPoint = 6; //first object in data array where inputparameters starts.
                                var ppIDinputList;
                                var ppIDoutputList;
                                var inputsBefore = getValues({ p: "getInputsPP", "process_id": proID });
                                var outputsBefore = getValues({ p: "getOutputsPP", "process_id": proID });
                            [ppIDinputList, ppIDoutputList] = addProParatoDB(data, startPoint, proID);
                                updateProPara(inputsBefore, outputsBefore, ppIDinputList, ppIDoutputList, proID);
                                refreshDataset();
                                $('#confirmRevision').modal('hide');
                                $('#addProcessModal').modal('hide');
                            },
                            error: function (errorThrown) {
                                alert("Error: " + errorThrown);
                            }
                        });
                    }
                });

                $('#confirmRevision').on('click', '#saveRev', function (event) {
                    var confirmformValues = $('#confirmRevision').find('input');
                    var revCommentData = confirmformValues.serializeArray();
                    var revComment = revCommentData[0].value;
                    if (revComment === '') { //warn user to enter comment
                    } else if (revComment !== '') {
                        var proGroId = data[5].value;
                        var sMenuProIdFinal = proName + '@' + proID;
                        var sMenuProGroupIdFinal = proGroId;
                        var dataToProcess = []; //dataToProcess to save in process table
                        for (var i = 2; i < 6; i++) { //not included by process id i=1
                            dataToProcess.push(data[i]);
                        }
                        var scripteditor = getScriptEditor('editor');
                        var scripteditorProHeader = getScriptEditor('editorProHeader');
                        var scripteditorProFooter = getScriptEditor('editorProFooter');
                        var process_gid = getValues({ p: "getProcess_gid", "process_id": proID })[0].process_gid;
                        var maxRev_id = getValues({ p: "getMaxRev_id", "process_gid": process_gid })[0].rev_id;
                        var newRev_id = parseInt(maxRev_id) + 1;
                        var script_mode = $('#script_mode').val();
                        var script_mode_header = $('#script_mode_header').val();
                        dataToProcess.push({ name: "script_mode", value: script_mode });
                        dataToProcess.push({ name: "script_mode_header", value: script_mode_header });
                        dataToProcess.push({ name: "perms", value: "3" });
                        dataToProcess.push({ name: "group", value: group });
                        dataToProcess.push({ name: "publish", value: "0" });
                        dataToProcess.push({ name: "rev_comment", value: revComment });
                        dataToProcess.push({ name: "rev_id", value: newRev_id });
                        dataToProcess.push({ name: "process_gid", value: process_gid });
                        dataToProcess.push({ name: "script_header", value: scripteditorProHeader });
                        dataToProcess.push({ name: "script_footer", value: scripteditorProFooter });
                        dataToProcess.push({ name: "script", value: scripteditor });
                        dataToProcess.push({ name: "p", value: "saveProcess" });

                        if (proName === '' || proGroId === '') {
                            dataToProcess = [];
                        }
                        if (dataToProcess.length > 0) {
                            $.ajax({
                                type: "POST",
                                url: "ajax/ajaxquery.php",
                                data: dataToProcess,
                                async: true,
                                success: function (s) {
                                    var newProcess_id = s.id;
                                    //update process link into sidebar menu
                                    sMenuProIdFinal = proName + '@' + newProcess_id;
                                    updateSideBar(sMenuProIdFirst, sMenuProIdFinal, sMenuProGroupIdFirst, sMenuProGroupIdFinal);
                                    var startPoint = 6; //first object in data array where inputparameters starts.
                                    addProParatoDBbyRev(data, startPoint, newProcess_id);
                                    refreshDataset();
                                    $('#addProcessModal').modal('hide');
                                },
                                error: function (errorThrown) {
                                    alert("Error: " + errorThrown);
                                }
                            });
                        }
                        $('#confirmRevision').modal('hide');
                    }
                });
                $('#confirmRevision').modal('show');
            }
        }
        // B) Edit Process Ends----
    });
    //insert dropdown, textbox and 'remove button' for each parameters
    $(function () {
        $(document).on('change', '.mParChange', function () {

            var id = $(this).attr("id");
            var Patt = /m(.*)puts-(.*)/;
            var type = id.replace(Patt, '$1'); //In or Out
            var col1init = "m" + type + "puts"; //column1 initials
            var col2init = "m" + type + "Name";
            var col3init = "m" + type + "Namedel";
            var col4init = "m" + type + "OptBut";
            var col5init = "m" + type + "Opt";
            var col6init = "m" + type + "Closure";
            var col7init = "m" + type + "Optdel";
            var col8init = "m" + type + "RegBut";
            var col9init = "m" + type + "Reg";
            var col10init = "m" + type + "Regdel";

            var num = id.replace(Patt, '$2');
            var prevParId = $("#" + id).attr("prev");
            var selParId = $("#" + id + " option:selected").val();

            if (prevParId === '-1' && selParId !== '-1') {

                if (type === 'In') {
                    numInputs++
                    var idRows = numInputs; // numInputs or numOutputs
                } else if (type === 'Out') {
                    numOutputs++
                    var idRows = numOutputs; // numInputs or numOutputs
                }
                $("#" + col1init).append('<select id="' + col1init + '-' + idRows + '" num="' + idRows + '" class="fbtn btn-default form-control mParChange" style ="margin-bottom: 5px;" prev ="-1"  name="' + col1init + '-' + idRows + '"></select>');
                $("#" + col2init).append('<input type="text" ppID="" placeholder="Enter name" class="form-control " style ="margin-bottom: 6px;" id="' + col2init + '-' + String(idRows - 1) + '" name="' + col2init + '-' + String(idRows - 1) + '">');
                $("#" + col3init).append('<button  type="button" class="btn btn-default form-control delRow" style ="margin-bottom: 6px;" id="' + col3init + '-' + String(idRows - 1) + '" name="' + col3init + '-' + String(idRows - 1) + '"><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Delete Row"><span><i class="glyphicon glyphicon-remove"></i></span></a></button>');
                $("#" + col4init).append('<button  type="button" class="btn btn-default form-control addOpt" style ="margin-bottom: 6px;" id="' + col4init + '-' + String(idRows - 1) + '" name="' + col4init + '-' + String(idRows - 1) + '"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Add/Remove operator"><span><i class="fa fa-wrench"></i></span></a></button>');
                $("#" + col5init).append('<select class="form-control" style ="visibility:hidden; margin-bottom: 6px;" id="' + col5init + '-' + String(idRows - 1) + '" name="' + col5init + '-' + String(idRows - 1) + '"></button>');
                $("#" + col6init).append('<input type="text" ppID="" placeholder="Operator content" class="form-control " style ="visibility:hidden; margin-bottom: 6px;" id="' + col6init + '-' + String(idRows - 1) + '" name="' + col6init + '-' + String(idRows - 1) + '">');
                $("#" + col7init).append('<button type="submit" class="btn btn-default form-control delOpt" style ="visibility:hidden; margin-bottom: 6px;" id="' + col7init + '-' + String(idRows - 1) + '" name="' + col7init + '-' + String(idRows - 1) + '"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Remove operator"><span><i class="glyphicon glyphicon-remove"></i></span></a></button>');
                $("#" + col8init).append('<button  type="button" class="btn btn-default form-control addRegEx" style ="margin-bottom: 6px;" id="' + col8init + '-' + String(idRows - 1) + '" name="' + col8init + '-' + String(idRows - 1) + '"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Add/Remove Output RegEx"><span><i class="fa fa-code"></i></span></a></button>');
                $("#" + col9init).append('<input type="text" ppID="" placeholder="Enter RegEx" class="form-control " style ="visibility:hidden; margin-bottom: 6px;" id="' + col9init + '-' + String(idRows - 1) + '" name="' + col9init + '-' + String(idRows - 1) + '">');
                $("#" + col10init).append('<button type="submit" class="btn btn-default form-control delRegEx" style ="visibility:hidden; margin-bottom: 6px;" id="' + col10init + '-' + String(idRows - 1) + '" name="' + col10init + '-' + String(idRows - 1) + '"><a data-toggle="tooltip" data-placement="bottom" data-original-title="Remove Output RegEx"><span><i class="glyphicon glyphicon-remove"></i></span></a></button>');
                //refresh tooltips
                $('[data-toggle="tooltip"]').tooltip();
                //load closure options
                var closureOpt = $('#mOutOpt-0 option').each(function () {
                    var val = $(this).val()
                    var optionClo = new Option(val, val);
                    $('#' + col5init + '-' + String(idRows - 1)).append(optionClo);
                });
                $('#' + col5init + '-' + String(idRows - 1) + ' option:first').attr('disabled', "disabled");
                var opt = $('#mInputs > :first-child')[0].selectize.options;
                var newOpt = [];
                $.each(opt, function (element) {
                    delete opt[element].$order;
                    newOpt.push(opt[element]);
                });
                $("#" + id).attr("prev", selParId)
                $("#" + col1init + "-" + idRows).selectize({
                    valueField: 'id',
                    searchField: 'name',
                    placeholder: "Add input...",
                    options: newOpt,
                    render: renderParam
                });
            }
        })

    });

    //toggle operators section in addprocessmodal when click on wrench
    $(document).on("click", ".addOpt", function (event) {
        event.preventDefault();
        var id = $(this).attr("id");
        var Patt = /m(.*)OptBut-(.*)/;
        var type = id.replace(Patt, '$1'); //In or Out
        var num = id.replace(Patt, '$2');
        var col4init = "m" + type + "OptBut";
        var col5init = "m" + type + "Opt";
        var col6init = "m" + type + "Closure";
        var col7init = "m" + type + "Optdel";
        if ($("#" + col5init + "-" + String(num)).css('visibility') === 'visible') {
            $("#" + col4init + "-" + String(num)).css('background', '#E7E7E7');
            $("#" + col5init + "-" + String(num)).css('visibility', 'hidden');
            $("#" + col6init + "-" + String(num)).css('visibility', 'hidden');
            $("#" + col7init + "-" + String(num)).css('visibility', 'hidden');

        } else if ($("#" + col5init + "-" + String(num)).css('visibility') === 'hidden') {
            $("#" + col4init + "-" + String(num)).css('background', '#FFF');
            $("#" + col5init + "-" + String(num)).css('visibility', 'visible');
            $("#" + col6init + "-" + String(num)).css('visibility', 'visible');
            $("#" + col7init + "-" + String(num)).css('visibility', 'visible');
        }
    });
    //remove operators section in addprocessmodal when click on wrench
    $(document).on("click", ".delOpt", function (event) {
        event.preventDefault();
        var id = $(this).attr("id");
        var Patt = /m(.*)Optdel-(.*)/;
        var type = id.replace(Patt, '$1'); //In or Out
        var num = id.replace(Patt, '$2');
        var col4init = "m" + type + "OptBut";
        var col5init = "m" + type + "Opt";
        var col6init = "m" + type + "Closure";
        var col7init = "m" + type + "Optdel";
        $("#" + col4init + "-" + String(num)).css('background', '#E7E7E7');
        $("#" + col5init + "-" + String(num)).css('visibility', 'hidden');
        $("#" + col6init + "-" + String(num)).css('visibility', 'hidden');
        $("#" + col7init + "-" + String(num)).css('visibility', 'hidden');
    });
    //toggle regEx section in addprocessmodal when click on Regex
    $(document).on("click", ".addRegEx", function (event) {
        event.preventDefault();
        var id = $(this).attr("id");
        var Patt = /m(.*)RegBut-(.*)/;
        var type = id.replace(Patt, '$1'); //In or Out
        var num = id.replace(Patt, '$2');
        var col4init = "m" + type + "RegBut";
        var col5init = "m" + type + "Reg";
        var col7init = "m" + type + "Regdel";
        if ($("#" + col5init + "-" + String(num)).css('visibility') === 'visible') {
            $("#" + col4init + "-" + String(num)).css('background', '#E7E7E7');
            $("#" + col5init + "-" + String(num)).css('visibility', 'hidden');
            $("#" + col7init + "-" + String(num)).css('visibility', 'hidden');

        } else if ($("#" + col5init + "-" + String(num)).css('visibility') === 'hidden') {
            $("#" + col4init + "-" + String(num)).css('background', '#FFF');
            $("#" + col5init + "-" + String(num)).css('visibility', 'visible');
            $("#" + col7init + "-" + String(num)).css('visibility', 'visible');
        }
    });
    //remove operators section in addprocessmodal when click on wrench
    $(document).on("click", ".delRegEx", function (event) {
        event.preventDefault();
        var id = $(this).attr("id");
        var Patt = /m(.*)Regdel-(.*)/;
        var type = id.replace(Patt, '$1'); //In or Out
        var num = id.replace(Patt, '$2');
        var col4init = "m" + type + "RegBut";
        var col5init = "m" + type + "Reg";
        var col7init = "m" + type + "Regdel";
        $("#" + col4init + "-" + String(num)).css('background', '#E7E7E7');
        $("#" + col5init + "-" + String(num)).css('visibility', 'hidden');
        $("#" + col7init + "-" + String(num)).css('visibility', 'hidden');
    });



    //remove  dropdown list of parameters
    $(document).on("click", ".delRow", function (event) {
        event.preventDefault();
        var id = $(this).attr("id");
        var Patt = /m(.*)Namedel-(.*)/;
        var type = id.replace(Patt, '$1'); //In or Out
        var num = id.replace(Patt, '$2');
        var col1init = "m" + type + "puts"; //column1 initials
        var col2init = "m" + type + "Name";
        var col3init = "m" + type + "Namedel";
        var col4init = "m" + type + "OptBut";
        var col5init = "m" + type + "Opt";
        var col6init = "m" + type + "Closure";
        var col7init = "m" + type + "Optdel";
        var col8init = "m" + type + "RegBut";
        var col9init = "m" + type + "Reg";
        var col10init = "m" + type + "Regdel";
        $("#" + col1init + "-" + String(num)).next().remove();
        $("#" + col1init + "-" + String(num)).remove();
        $("#" + col2init + "-" + String(num)).remove();
        $("#" + col3init + "-" + String(num)).remove();
        $("#" + col4init + "-" + String(num)).remove();
        $("#" + col5init + "-" + String(num)).remove();
        $("#" + col6init + "-" + String(num)).remove();
        $("#" + col7init + "-" + String(num)).remove();
        $("#" + col8init + "-" + String(num)).remove();
        $("#" + col9init + "-" + String(num)).remove();
        $("#" + col10init + "-" + String(num)).remove();
    });

    //parameter modal file type change:(save file type as identifier for val)
    $('#modalQualifier').change(function () {
        if ($('#modalQualifier').val() === 'val') {
            $('#mFileTypeDiv').css('display', 'none');
        } else {
            $('#mFileTypeDiv').css('display', 'block');
        }
    });

    //parameter modal 
    $('#parametermodal').on('show.bs.modal', function (event) {
        $('#mFileTypeDiv').css('display', 'block');
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');

        if (button.attr('id') === 'mParamAdd') {
            $('#parametermodaltitle').html('Add New Parameter');
            $('#mParamsDynamic').css('display', "inline");
            $('#mParamList').css('display', "none");
            //ajax for parameters
            $.ajax({
                type: "GET",
                url: "ajax/ajaxquery.php",
                data: {
                    p: "getAllParameters"
                },
                async: false,
                success: function (s) {
                    $("#mParamListIn").empty();
                    var firstOptionSelect = new Option("Available Parameters...", '');
                    $("#mParamListIn").append(firstOptionSelect);
                    for (var i = 0; i < s.length; i++) {
                        var param = s[i];
                        var optionAll = new Option(param.name, param.id);
                        $("#mParamListIn").append(optionAll);
                    }
                    $('#mParamListIn').selectize({});
                }
            });

        } else if (button.attr('id') === 'mParamEdit') {
            $('#parametermodaltitle').html('Edit Parameter');
            $('#mParamsDynamic').css('display', "none");
            $('#mParamList').css('display', "inline");
            //ajax for parameters
            $.ajax({
                type: "GET",
                url: "ajax/ajaxquery.php",
                data: {
                    p: "getEditDelParameters"
                },
                async: false,
                success: function (s) {
                    $("#mParamListIn").empty();
                    var firstOptionSelect = new Option("Editable Parameters...", '');
                    $("#mParamListIn").append(firstOptionSelect);
                    for (var i = 0; i < s.length; i++) {
                        var param = s[i];
                        var optionAll = new Option(param.name, param.id);
                        $("#mParamListIn").append(optionAll);
                    }
                    $('#mParamListIn').selectize({});
                }
            });
        }
    });
    // Dismiss parameters modal 
    $('#parametermodal').on('hide.bs.modal', function (event) {
        $('#mParamListIn')[0].selectize.destroy();
        $('#mParamsDynamic').css('display', "inline");
        $('#mParamList').css('display', "none");
    });

    //Delparametermodal to delete parameters
    $('#delparametermodal').on('show.bs.modal', function (event) {
        $.ajax({
            type: "GET",
            url: "ajax/ajaxquery.php",
            data: {
                p: "getEditDelParameters"
            },
            async: false,
            success: function (s) {
                $("#mParamListDel").empty();
                var firstOptionSelect = new Option("Select Parameter to Delete...", '');
                $("#mParamListDel").append(firstOptionSelect);
                for (var i = 0; i < s.length; i++) {
                    var param = s[i];
                    var optionAll = new Option(param.name, param.id);
                    $("#mParamListDel").append(optionAll);
                }
                $('#mParamListDel').selectize({});
            }
        });
    });

    //parameter delete button in Delparametermodal
    $('#delparametermodal').on('click', '#delparameter', function (e) {
        var formValues = $('#delparametermodal').find('#mParamListDel');
        var data = formValues.serializeArray();
        var delparId = data[0].value;
        var warnUser = false;
        var infoText = '';
        [warnUser, infoText] = checkParamDeletion(delparId);
        if (warnUser === true) {
            $('#warnDelete').off();
            $('#warnDelete').on('show.bs.modal', function (event) {
                $('#warnDelText').html(infoText);
            });
            $('#warnDelete').modal('show');

        } else if (warnUser === false) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    id: delparId,
                    p: "removeParameter"
                },
                async: false,
                success: function (s) {
                    var allBox = $('#addProcessModal').find('select').filter(function () { return this.id.match(/mInputs(.*)|mOutputs(.*)/); });
                    for (var i = 0; i < allBox.length; i++) {
                        var parBoxId = allBox[i].getAttribute('id');
                        $('#' + parBoxId)[0].selectize.removeOption(delparId);
                    }
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
            $('#delparametermodal').modal('hide');
            refreshDataset()
        }
    });

    // Dismiss parameters delete modal 
    $('#delparametermodal').on('hide.bs.modal', function (event) {
        $('#mParamListDel')[0].selectize.destroy();
    });

    //edit parameter modal dropdown change for each parameters
    $(function () {
        $(document).on('change', '#mParamListIn', function () {
            var id = $(this).attr("id");
            var formValues = $('#parametermodal').find('select');
            var data = formValues.serializeArray(); // convert form to array
            var selectParamId = data[0].value
            $.ajax({
                type: "GET",
                url: "ajax/ajaxquery.php",
                data: {
                    p: "getAllParameters"
                },
                async: false,
                success: function (s) {
                    var showParam = {};
                    s.forEach(function (element) {
                        if (element.id === selectParamId) {
                            showParam = element;
                        }
                    });
                    //insert data into form
                    $('#mIdPar').val(showParam.id);
                    $('#mFileType').val(showParam.file_type);
                    $('#modalName').val(showParam.name);
                    $('#modalQualifier').val(showParam.qualifier);
                    $('#modalQualifier').trigger("change");
                }
            });
            var modaltit = $('#parametermodaltitle').html();
            if (modaltit === 'Add New Parameter') {
                $('#mIdPar').val('');
                var savetype = $('#mIdPar').val();
            }

        })
    });

    //parameter modal save button
    $('#parametermodal').on('click', '#saveparameter', function (event) {
        event.preventDefault();
        var selParName = '';
        var formValues = $('#parametermodal').find('input, select');
        var savetype = $('#mIdPar').val();
        var data = formValues.serializeArray(); // convert form to array
        data.splice(1, 1); //Remove "ParamAllIn"
        var selParID = data[0].value;
        var selParName = $.trim(data[1].value);
        var selParQual = data[2].value;
        var selParType = $.trim(data[3].value);
        if (selParQual === 'val') {
            data[3].value = selParName;
            selParType = selParName;
        }
        var warnUser = false;
        var infoText = '';
        [warnUser, infoText] = checkParamDeletion(selParID);
        if (warnUser === true) {
            $('#warnDelete').off();
            $('#warnDelete').on('show.bs.modal', function (event) {
                $('#warnDelText').html(infoText);
            });
            $('#warnDelete').modal('show');

        } else if (warnUser === false) {
            if (selParName === '' || selParQual === '' || selParType === '') {

            }
            //check if it starts with letter
            else if (!selParName[0].match(/[a-z]/i)) {
                $('#mNameTool').tooltip("show");
                setTimeout(function () { $('#mNameTool').tooltip("hide"); }, 5000);
            } else if (!selParType[0].match(/[a-z]/i)) {
                $('#mFileTypeTool').tooltip("show");
                setTimeout(function () { $('#mFileTypeTool').tooltip("hide"); }, 5000);
            } else {
                data.push({
                    name: "p",
                    value: "saveParameter"
                });
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: data,
                    async: false,
                    success: function (s) {
                        if (savetype.length) { //Edit Parameter
                            var allBox = $('#addProcessModal').find('select').filter(function () { return this.id.match(/mInputs(.*)|mOutputs(.*)/); });
                            for (var i = 0; i < allBox.length; i++) {
                                var parBoxId = allBox[i].getAttribute('id');
                                $('#' + parBoxId)[0].selectize.updateOption(selParID, {
                                    id: selParID,
                                    name: selParName,
                                    qualifier: selParQual,
                                    file_type: selParType
                                });
                            }

                        } else { //Add Parameter
                            var allBox = $('#addProcessModal').find('select').filter(function () { return this.id.match(/mInputs(.*)|mOutputs(.*)/); });
                            for (var i = 0; i < allBox.length; i++) {
                                var parBoxId = allBox[i].getAttribute('id');
                                $('#' + parBoxId)[0].selectize.addOption({
                                    id: s.id,
                                    name: selParName,
                                    qualifier: selParQual,
                                    file_type: selParType
                                });
                            }
                        }
                        $('#parametermodal').modal('hide');
                        refreshDataset()
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        }
    });
    // "change name" modal for input parameters: remove attr:disabled by click
    toggleCheckBox('#checkDropDown', '#dropDownOpt');
    toggleCheckBox('#checkDefVal', '#defVal');

    function toggleCheckBox(checkboxId, inputId) {
        $(function () {
            $(document).on('change', checkboxId, function (event) {
                var checkdropDownOpt = $(checkboxId).is(":checked").toString();
                if (checkdropDownOpt === "true") {
                    $(inputId).removeAttr('disabled')
                } else if (checkdropDownOpt === "false") {
                    $(inputId).attr('disabled', 'disabled')
                }
            })
        });
    }
    // "change name" modal for input parameters
    function fillRenameModal(renameTextDefVal, checkID, inputID) {
        if (renameTextDefVal) {
            if (renameTextDefVal !== "") {
                $(inputID).removeAttr('disabled')
                $(inputID).val(renameTextDefVal)
                $(checkID).attr('checked', true);
            } else {
                $(checkID).removeAttr('checked');
                $(inputID).attr('disabled', 'disabled')
            }
        } else {
            $(checkID).removeAttr('checked');
            $(inputID).attr('disabled', 'disabled')
        }
    }
    // "change name" modal for input parameters
    function saveValue(checkId, valueId, attr) {
        var value = $(valueId).val();
        var checkValue = $(checkId).is(":checked").toString();
        if (checkValue === "true" && value !== "") {
            $("#" + renameTextID).attr(attr, value)
        } else {
            $("#" + renameTextID).removeAttr(attr);
        }
    }

    $('#renameModal').on('show.bs.modal', function (event) {
        $(this).find('form').trigger('reset');
        if (renameTextClassType === "output" || renameTextClassType === null) {
            $('#defValDiv').css("display", "none")
            $('#dropdownDiv').css("display", "none")
        } else if (renameTextClassType === "input") {
            $('#defValDiv').css("display", "block")
            $('#dropdownDiv').css("display", "block")
        }
        fillRenameModal(renameTextDefVal, "#checkDefVal", '#defVal');
        fillRenameModal(renameTextDropDown, '#checkDropDown', '#dropDownOpt');

        $('#renameModaltitle').html('Change Name');
        $('#mRenName').val(renameText);
    });
    $('#renameModal').on('click', '#renameProPara', function (event) {
        saveValue('#checkDefVal', '#defVal', "defVal");
        saveValue('#checkDropDown', '#dropDownOpt', "dropDown");
        changeName();
        autosave();
        $('#renameModal').modal("hide");
    });

    // Delete process d3 modal 
    $('#confirmD3Modal').on('show.bs.modal', function (event) {
        $('#confirmD3ModalText').html('Are you sure you want to delete?');

    });
    $('#confirmD3Modal').on('click', '#deleteD3Btn', function (event) {
        if (deleteID.split("_").length == 2) {
            removeEdge();
        } else if (deleteID.split("_").length == 1) {
            remove();
        }
        autosave();
        $('#confirmD3Modal').modal("hide");
    });


    // process group modal 
    $('#processGroupModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        $(this).find('option').remove();

        if (button.attr('id') === 'groupAdd') {
            $('#processGroupmodaltitle').html('Add Menu Group');
        } else if (button.attr('id') === 'groupEdit') {
            $('#processGroupmodaltitle').html('Edit Menu Group');
            $('#mGroupListForm').css('display', "block");
            var formValues = $('#proGroup').find('select');
            var selGroupId = "";
            if (formValues.serializeArray()[0]) {
                var selGroupId = formValues.serializeArray()[0].value; // convert form to array
                $.ajax({
                    type: "GET",
                    url: "ajax/ajaxquery.php",
                    data: {
                        p: "getEditDelProcessGroups"
                    },
                    async: false,
                    success: function (s) {
                        $("#mMenuGroupList").append('<option  value="">Select menu groups to edit..</option>');
                        for (var i = 0; i < s.length; i++) {
                            var param = s[i];
                            var optionGroup = new Option(param.group_name, param.id);
                            $("#mMenuGroupList").append(optionGroup);
                        }
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            } else {
                event.preventDefault();
                $('#mGroupListForm').css('display', "none");
            }
        }
    });

    //process group modal save button
    $('#processGroupModal').on('click', '#saveProcessGroup', function (event) {
        event.preventDefault();
        var formValues = $('#processGroupModal').find('input');
        var savetype = 'add';
        var data = formValues.serializeArray(); // convert form to array
        var selProGroupID = $("#mMenuGroupList").val();
        if ($('#processGroupmodaltitle').html() === 'Edit Menu Group') {
            data[0].value = selProGroupID;
            savetype = "edit";
        }
        var selProGroupName = data[1].value;
        data.push({ name: "p", value: "saveProcessGroup" });


        if ((savetype === "edit" && selProGroupID !== '' && selProGroupName !== '') || (savetype === "add" && selProGroupName !== '')) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: data,
                async: false,
                success: function (s) {
                    if (savetype === 'edit') { //Edit Process Group
                        var allProBox = $('#proGroup').find('select');
                        var proGroBoxId = allProBox[0].getAttribute('id');
                        $('#' + proGroBoxId)[0].selectize.updateOption(selProGroupID, {
                            value: selProGroupID,
                            text: selProGroupName
                        });
                        $('#side-' + selProGroupID).parent().find('span').html(selProGroupName);
                    } else { //Add process group
                        var allProBox = $('#proGroup').find('select');
                        var proGroBoxId = allProBox[0].getAttribute('id');
                        selProGroupID = s.id;
                        $('#' + proGroBoxId)[0].selectize.addOption({
                            value: selProGroupID,
                            text: selProGroupName
                        });
                        $('#autocompletes1').append('<li class="treeview"><a href="" draggable="false"><i  class="fa fa-circle-o"></i> <span>' + selProGroupName + '</span><i class="fa fa-angle-left pull-right"></i></a><ul id="side-' + selProGroupID + '" class="treeview-menu"></ul></li>');
                    }
                    $('#mProcessGroup')[0].selectize.setValue(selProGroupID, false);
                    $('#processGroupModal').modal('hide');

                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });

    //close process group modal 
    $('#processGroupModal').on('hide.bs.modal', function (event) {
        $('#mGroupListForm').css('display', "none");
    });

    //process group remove button
    $('#delprocessGrmodal').on('show.bs.modal', function (event) {
        $.ajax({
            type: "GET",
            url: "ajax/ajaxquery.php",
            data: {
                p: "getEditDelProcessGroups"
            },
            async: false,
            success: function (s) {
                $("#mMenuGroupListDel").empty();
                var firstOptionSelect = new Option("Select Menu Group to Delete...", '');
                $("#mMenuGroupListDel").append(firstOptionSelect);
                for (var i = 0; i < s.length; i++) {
                    var param = s[i];
                    var optionAll = new Option(param.group_name, param.id);
                    $("#mMenuGroupListDel").append(optionAll);
                }
            }
        });
    });



    //process group remove button
    $('#delprocessGrmodal').on('click', '#delproGroup', function (e) {
        e.preventDefault();
        var selectProGro = $("#mMenuGroupListDel").val();
        var warnUser = false;
        var infoText = '';
        if (selectProGro !== '') {
        [warnUser, infoText] = checkMenuGrDeletion(selectProGro);
            if (warnUser === true) {
                $('#warnDelete').off();
                $('#warnDelete').on('show.bs.modal', function (event) {
                    $('#warnDelText').html(infoText);
                });
                $('#warnDelete').modal('show');

            } else if (warnUser === false) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: {
                        id: selectProGro,
                        p: "removeProcessGroup"
                    },
                    async: false,
                    success: function (s) {
                        var allProBox = $('#proGroup').find('select');
                        var proGroBoxId = allProBox[0].getAttribute('id');
                        $('#' + proGroBoxId)[0].selectize.removeOption(selectProGro);
                        $('#side-' + selectProGro).parent().remove();
                        $('#delprocessGrmodal').modal('hide');
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        }
    });

    //   ---- Run modal starts ---
    var projectTable = $('#projecttable').DataTable({
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getProjects" },
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
                "data": "name"
            }, {
                "data": "username"
            }, {
                "data": "date_modified"
            }],
        'select': {
            'style': 'single'
        },
        'order': [[3, 'desc']]
    });

    $('#mRun').on('show.bs.modal', function (event) {
        projectTable.column(0).checkboxes.deselect();
        var pipeline_id = $('#pipeline-title').attr('pipelineid');
        if (pipeline_id === '') {
            event.preventDefault();
        }
    });

    $('#mRun').on('click', '#selectProject', function (event) {
        event.preventDefault();
        var pipeline_id = $('#pipeline-title').attr('pipelineid');
        var rows_selected = projectTable.column(0).checkboxes.selected();
        if (rows_selected.length === 1 && pipeline_id !== '') {
            $('#runNameModal').modal('show');
        }
    });
    //enter run name modal
    $('#runNameModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        if (button.attr('id') === 'selectProject') {
            $('#runNameModaltitle').html('Enter Run Name');
        } else {}
    });
    //save run on database
    $('#runNameModal').on('click', '#saveRun', function (event) {
        event.preventDefault();
        var pipeline_id = $('#pipeline-title').attr('pipelineid');
        var rows_selected = projectTable.column(0).checkboxes.selected();
        var run_name = $('#runName').val();

        if (rows_selected.length === 1 && pipeline_id !== '' && run_name !== '') {
            var data = [];
            var project_id = rows_selected[0];
            data.push({ name: "name", value: run_name });
            data.push({ name: "project_id", value: project_id });
            data.push({ name: "pipeline_id", value: pipeline_id });
            data.push({ name: "p", value: "saveProjectPipeline" });
            var proPipeGet = getValues(data);
            var projectPipelineID = proPipeGet.id;
            $('#runNameModal').modal('hide');
            $('#mRun').modal('hide');
            setTimeout(function () { window.location.replace("index.php?np=3&id=" + projectPipelineID); }, 700);
        }
    });

    $('#projectmodal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        if (button.attr('id') === 'addproject') {
            $('#projectmodaltitle').html('Add New Project');
        }
    });

    $('#projectmodal').on('click', '#saveproject', function (event) {
        event.preventDefault();
        var formValues = $('#projectmodal').find('input');
        var savetype = $('#mProjectID').val();
        var data = formValues.serializeArray(); // convert form to array
        data.push({ name: "summary", value: "" });
        data.push({ name: "p", value: "saveProject" });
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: data,
            async: true,
            success: function (s) {
                if (savetype.length) { //edit

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
                            projectTable.row.add(addData).draw();
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
    });
    //   ---- Run modal ends ---

    //   ---- Run Exist modal starts ---
    var existProjectTable = $('#existRunTable').DataTable({
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getExistProjectPipelines", pipeline_id: pipeline_id },
            "dataSrc": ""
        },
        "columns": [
            {
                "data": "id",
                "checkboxes": {
                    'targets': 0,
                    'selectRow': true
                }
            }, {
                "data": "pp_name"
            }, {
                "data": "project_name"
            }, {
                "data": "username"
            }, {
                "data": "date_modified"
            }],
        'select': {
            'style': 'single'
        },
        'order': [[4, 'desc']]
    });

    $('#mExistRun').on('show.bs.modal', function (event) {
        existProjectTable.column(0).checkboxes.deselect();
        if (pipeline_id === '') {
            event.preventDefault();
        }
    });


    $('#mExistRun').on('click', '#selectExistRun', function (event) {
        event.preventDefault();
        var rows_selected = existProjectTable.column(0).checkboxes.selected();
        if (rows_selected.length === 1 && pipeline_id !== '') {
            var project_pipeline_id = rows_selected[0];
            $('#mExistRun').modal('hide');
            setTimeout(function () { window.location.replace("index.php?np=3&id=" + project_pipeline_id); }, 700);
        }
    });
    //   ---- Run Exist modal ends ---

    //  ---- Pipiline Group Modals starts ---
    $('#pipeGroupModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        $(this).find('option').remove();
        if (button.attr('id') === 'pipeGroupAdd') {
            $('#pipelineGroupmodaltitle').html('Add Pipeline Menu Group');
        } else if (button.attr('id') === 'pipeGroupEdit') {
            $('#pipelineGroupmodaltitle').html('Edit Pipeline Menu Group');
            $('#mGroupPipeList').css('display', "block");
            $.ajax({
                type: "GET",
                url: "ajax/ajaxquery.php",
                data: {
                    p: "getEditDelPipelineGroups"
                },
                async: false,
                success: function (s) {
                    $("#mGroupPipe").append('<option  value="">Select menu groups to edit..</option>');
                    for (var i = 0; i < s.length; i++) {
                        var param = s[i];
                        var optionGroup = new Option(param.group_name, param.id);
                        $("#mGroupPipe").append(optionGroup);
                    }
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });

    //process group modal save button
    $('#pipeGroupModal').on('click', '#savePipeGroup', function (event) {
        event.preventDefault();
        var formValues = $('#pipeGroupModal').find('input');
        var savetype = 'add';
        var data = formValues.serializeArray(); // convert form to array
        var selPipeGroupID = $("#mGroupPipe").val();
        if ($('#pipelineGroupmodaltitle').html() === 'Edit Pipeline Menu Group') {
            data[0].value = selPipeGroupID;
            savetype = "edit";
        }
        var selPipeGroupName = data[1].value;
        data.push({ name: "p", value: "savePipelineGroup" });
        if ((savetype === "edit" && selPipeGroupID !== '' && selPipeGroupName !== '') || (savetype === "add" && selPipeGroupName !== '')) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: data,
                async: false,
                success: function (s) {
                    if (savetype === 'edit') { //Edit Group
                        $("#pipeGroupAll option[value='" + selPipeGroupID + "']").remove();
                        $('#pipeGroupAll').append($('<option>', { value: selPipeGroupID, text: selPipeGroupName }));
                        $("#pipeGroupAll").val(selPipeGroupID);
                        //sidebar menu update
                        $('#pipeGr-' + selPipeGroupID).parent().find('span').html(selPipeGroupName);
                    } else { //Add group
                        var pipeGroupID = s.id;
                        $('#pipeGroupAll').append($('<option>', { value: pipeGroupID, text: selPipeGroupName }));
                        $("#pipeGroupAll").val(pipeGroupID);
                        //sidebar menu add
                        $('#processSideHeader').before('<li class="treeview"><a href="" draggable="false"><i  class="fa fa-spinner"></i> <span>' + truncateName(selPipeGroupName, 'sidebarMenu') + '</span><i class="fa fa-angle-left pull-right"></i></a><ul id="pipeGr-' + pipeGroupID + '" class="treeview-menu"></ul></li>');
                    }
                    $('#pipeGroupModal').modal('hide');

                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });
    //close process group modal 
    $('#pipeGroupModal').on('hide.bs.modal', function (event) {
        $('#mGroupPipeList').css('display', "none");
    });
    //pipeline group remove button
    $('#pipeDelGroupModal').on('show.bs.modal', function (event) {
        $.ajax({
            type: "GET",
            url: "ajax/ajaxquery.php",
            data: {
                p: "getEditDelPipelineGroups"
            },
            async: false,
            success: function (s) {
                $("#mPipeMenuGroupDel").empty();
                var firstOptionSelect = new Option("Select Menu Group to Delete...", '');
                $("#mPipeMenuGroupDel").append(firstOptionSelect);
                for (var i = 0; i < s.length; i++) {
                    var param = s[i];
                    var optionAll = new Option(param.group_name, param.id);
                    $("#mPipeMenuGroupDel").append(optionAll);
                }
            }
        });
    });
    //pipeline group remove button
    $('#pipeDelGroupModal').on('click', '#delpipeGroup', function (e) {
        e.preventDefault();
        var selectPipeGro = $("#mPipeMenuGroupDel").val();
        var warnUser = false;
        var infoText = '';
        if (selectPipeGro !== '') {
        [warnUser, infoText] = checkPipeMenuGrDeletion(selectPipeGro);
            if (warnUser === true) {
                $('#warnDelete').off();
                $('#warnDelete').on('show.bs.modal', function (event) {
                    $('#warnDelText').html(infoText);
                });
                $('#warnDelete').modal('show');

            } else if (warnUser === false) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: {
                        id: selectPipeGro,
                        p: "removePipelineGroup"
                    },
                    async: false,
                    success: function (s) {
                        $("#pipeGroupAll option[value='" + selectPipeGro + "']").remove();
                        $('#pipeGr-' + selectPipeGro).parent().remove();
                        $('#pipeDelGroupModal').modal('hide');
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        }
    });

    //  ---- Pipiline Group Modals ends ---








});
