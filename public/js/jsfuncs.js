//global data libraries
window.ajaxData = {};

function initCloudData(cloud){
    window[cloud+"Data"] = {};
    window[cloud+"Data"].stop = false;
}

initCloudData("amazon");
initCloudData("google");

//user role
function callusRole() {
    var userRole = getValues({ p: "getUserRole" });
    if (userRole && userRole != '') {
        if (userRole[0].role !== null) {
            if (userRole[0].role === "admin") {
                var usRole = "admin";
            } else {
                var usRole = "";
            }
        } else {
            var usRole = "";
        }
    } else {
        var usRole = "";
    }
    return usRole;
}
usRole = callusRole();



//initialize all tooltips on a page (eg.$('#mFileTypeTool').tooltip("show"))
//to activate dynamically added tooltips, run the command below
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

//jquery clean css notation and add # sign to beginning
function jqcss(myid) {
    return "#" + myid.replace(/(:|\.|\[|\]|,|=|@)/g, "\\$1");
}

function IsJsonString(str) {
    try {
        JSON.parse(str);
    } catch (e) {
        return false;
    }
    return true;
}

// example:
//var filteredNames = filterObjKeys(names, /Peter/); // second parameter is a javascript regex object, so for exemple for case insensitive you would do /Peter/i  
//third parameter is optional: return(array if keys/array of obj)=(keys/obj)
function filterObjKeys(obj, filter, type) {
    var type = type || "keys"
    var key, keys = [];
    for (key in obj) {
        if (obj.hasOwnProperty(key) && filter.test(key)) {
            if (type == "keys") {
                keys.push(key);
            } else if (type == "obj") {
                keys.push(obj[key]);
            }
        }
    }
    return keys;
}


//var filteredObj = filterObjVal(obj, "34")
function filterObjVal(obj, filter) {
    var result = {};
    for (var k in obj) {
        if (obj[k] == filter) {
            result[k] = obj[k];
        }
    }
    return result
}


//sort array of object by key
function sortByKey(array, key) {
    return array.sort(function (a, b) {
        var x = a[key];
        var y = b[key];
        return ((x < y) ? -1 : ((x > y) ? 1 : 0));
    });
}

function checkArraysEqual(a, b) {
    if (a === b) return true;
    if (a == null || b == null) return false;
    if (a.length != b.length) return false;

    for (var i = 0; i < a.length; ++i) {
        if (a[i] !== b[i]) return false;
    }
    return true;
}

//use array of item to fill select element
//clean=true will empty dropdown first
function fillDropdownArrObj(arr, val, name, dropdownId, clean, first) {
    first = first || false; //default false
    if (clean === true) {
        $(dropdownId).empty();
    }
    if (first){
        $(dropdownId).append(first);
    }
    for (var i = 0; i < arr.length; i++) {
        var param = arr[i];
        var optionGroup = new Option(param[name], param[val]);
        $(dropdownId).append(optionGroup);
    }
}

function combineLinuxCmd(cmdAr){
    var ret = ""
    for (var i = 0; i < cmdAr.length; i++) {
        var cmd = cmdAr[i];
        if (cmd && ret){
            ret += " && ";
        }
        ret += cmd;
    }
    return ret;
}

function showLoadingDiv(parentId) {
    $("#" + parentId).addClass("loader-spin-parent")
    $("#" + parentId).append('<div class="loader-spin-iconDiv" id="loading-image-' + parentId + '"><img class="loader-spin-icon"  src="css/loader.gif" alt="Loading..." /></div>');
}
function showLoadingDivText(parentId, text) {
    if ($("#loading-badge-" + parentId).length){
        $("#loading-badge-" + parentId).text(text)
    } else {
        $("#" + parentId).addClass("loader-spin-parent")
        $("#" + parentId).append('<div class="loader-spin-iconDiv" id="loading-image-' + parentId + '"><img class="loader-spin-icon"  src="css/loader.gif" alt="Loading..." /><p class="text-center"><span style:"margin-left:9px;" id="loading-badge-'+parentId+'" class="badge align-middle">'+text+'</span></p></div>');
    }
    return $("#" + parentId)
}

function hideLoadingDiv(parentId) {
    $("#" + parentId).removeClass("loader-spin-parent")
    $('#loading-image-' + parentId).remove();
}

//eg showInfoModal('#warnDelete','#warnDeleteText', text)
function showInfoModal(modalId, textID, text) {
    //true if modal is open
    if (($(modalId).data('bs.modal') || {}).isShown ){
        var oldText = $(textID).html();
        var newText = oldText + "<br/><br/>" +text;
        $(textID).html(newText);
    } else {
        $(modalId).off();
        $(modalId).on('show.bs.modal', function (event) {
            $(this).find('form').trigger('reset');
            $(textID).html(text);
        });
        $(modalId).modal('show'); 
    }
}

//text: show in browser, 
//savedData: save data to delete button
//execFunc: execute execFunc(savedData) when clicking on delete button
function showConfirmDeleteModal(text, savedData, execFunc) {
    var modalId = "#confirmDeleteModal";
    var textID = "#confirmDeleteModalText";
    var clickid = "#confirmDeleteModalDelBtn";
    //true if modal is open
    if (($(modalId).data('bs.modal') || {}).isShown ){
        $(textID).html(text);
        $(clickid).removeData("data");
        $(clickid).data("data",savedData);
        $(modalId).on('click', clickid, function (event) {
            var savedData = $(clickid).data("data")
            execFunc(savedData)
        });
    } else {
        $(modalId).off();
        $(clickid).removeData("data")
        $(modalId).on('show.bs.modal', function (event) {
            $(this).find('form').trigger('reset');
            $(textID).html(text);
            $(clickid).data("data",savedData)
        });
        $(modalId).on('click', clickid, function (event) {
            var savedData = $(clickid).data("data")
            execFunc(savedData)
            $(modalId).modal('hide'); 
        });
        $(modalId).modal('show'); 
    }
}


function selectMultiselect(id, nameArr) {
    $(id).multiselect('deselectAll', false)
    $(id).multiselect('select', nameArr)
    $(id).trigger("change")
}

//after importing script text, remove extra quote if exist
function removeDoubleQuote(script) {
    if (script === null) {
        return null
    }
    var lastLetter = script.length - 1;
    if (script[0] === '"' && script[lastLetter] === '"' && script[1] !== '"') {
        script = script.substring(1, script.length - 1); //remove first and last duble quote
    }
    return script
}


//refreshSession every 10 minutes
window.setInterval( function() {
    $.ajax({
        cache: false,
        type: "GET",
        url: "ajax/refreshSession.php",
        success: function(data) {
        }
    });
}, 600000 );



// check the cloud profiles activity each 40 sec.
checkCloudProfiles("timer", "amazon");
checkCloudProfiles("timer", "google");

//to start timer, enter "timer" as input
function checkCloudProfiles(timer, cloud) {
    if (cloud == "amazon"){
        var icon = '#manageAmz';
        var amountIcon = '#amzAmount';
    } else if (cloud == "google"){
        var icon = '#manageGoog';
        var amountIcon = '#googAmount';
    }
    var proData = getValues({ p: "getProfileCloud", cloud:cloud });
    if (proData) {
        if (proData.length > 0) {
            $(icon).css('display', 'inline');
            var countActive = 0;
            for (var k = 0; k < proData.length; k++) {
                if (proData[k].status === "running" || proData[k].status === "waiting" || proData[k].status === "initiated" || proData[k].status === "retry") {
                    countActive++;
                }
                if (timer === "timer") {
                    checkCloudTimer(proData[k].id, 60000, cloud);
                }
                window.modalRec = {};
                window.modalRec[cloud+'last_status_log_' + proData[k].id] = "";
                window.modalRec[cloud+'last_status_' + proData[k].id] = proData[k].status;
            }
            if (countActive > 0) {
                $(amountIcon).css('display', 'inline');
                $(amountIcon).text(countActive);
            } else {
                $(amountIcon).text(countActive);
                $(amountIcon).css('display', 'none');
            }
        }
    }
}

//interval will decide the check period: default: 20 sec. for termination 5 sec
function checkCloudTimer(proId, interval, cloud) {
    var intervalName = 'interval_'+cloud+'Status_' + proId;
    window[intervalName] = setInterval(function () {
        //run CloudCheck command which creates log file in apprx. 3 seconds. 
        getValues({ p: "runCloudCheck", cloud: cloud, profileId: proId });
        setTimeout(function () { checkCloudStatus(proId, cloud); }, 5000);
    }, interval);
}



retryTimer = 5000;
//read CloudCheck log file
function checkCloudStatus(proId, cloud) {
    var intervalName = 'interval_'+cloud+'Status_' + proId;
    var checkCloudStatusLog = getValues({ p: "checkCloudStatus", cloud:cloud , profileId: proId });
    console.log(checkCloudStatusLog)
    var logStart = "";
    var logCloudList = "";
    if (checkCloudStatusLog.logStart){
        logStart = '<button type="button" class="btn btn-sm" style=" margin:2px; background-color: rgb(59, 140, 188);" cloud='+cloud+' data="cloudLogStart" id="'+cloud+'LogStart-'+proId+'" ><a data-toggle="tooltip" data-placement="bottom" data-original-title="Log"><span><i class="fa fa-file-text-o"></i></span></a></button>'
    }
    if (checkCloudStatusLog.logCloudList){
        logCloudList = '<button type="button" class="btn btn-sm" style=" margin:2px; background-color: rgb(59, 140, 188);" cloud='+cloud+' data="cloudListLog" id="'+cloud+'CloudListLog-'+proId+'" ><a data-toggle="tooltip" data-placement="bottom" data-original-title="Cloud List"><span><i class="fa fa-file-text-o"></i></span></a></button>'
    }


    if (window[cloud+"Data"].stop && checkCloudStatusLog.status !== "terminated") {
        window.modalRec[cloud+'last_status_log_' + proId] = "Waiting for termination..";
        $('#status-' + cloud + proId).html('<i class="fa fa-hourglass-1"></i> Waiting for termination..' + "<p>" + logStart + logCloudList + "</p>");
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Start').css('display', 'none');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').css('display', 'inline');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').removeAttr('disabled');
        clearInterval(window[intervalName]);
        checkCloudTimer(proId, 5500, cloud);
    } else if (checkCloudStatusLog.status === "waiting") {
        window.modalRec[cloud+'last_status_log_' + proId] = "Waiting for reply..";
        $('#status-' + cloud + proId).html('<i class="fa fa-hourglass-1"></i> Waiting for reply..' + "<p>" + logStart + logCloudList + "</p>");
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Start').css('display', 'none');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').css('display', 'inline');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').attr('disabled', 'disabled');
        clearInterval(window[intervalName]);
        checkCloudTimer(proId, 20000, cloud);
    } else if (checkCloudStatusLog.status === "initiated") {
        window.modalRec[cloud+'last_status_log_' + proId] = "Initializing..";
        window.modalRec[cloud+'last_status_' + proId] = checkCloudStatusLog.status;
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Start').css('display', 'none');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').css('display', 'inline');
        $('#status-' + cloud + proId).html('<i class="fa fa-hourglass-half"></i> Initializing..' + "<p>" + logStart + logCloudList + "</p>");
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').removeAttr('disabled');
        clearInterval(window[intervalName]);
        checkCloudTimer(proId, 20000, cloud);
    } else if (checkCloudStatusLog.status === "retry") { //could not read the log file
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Start').css('display', 'none');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').css('display', 'none');
        var tempLog = window.modalRec[cloud+'last_status_log_' + proId]
        if (tempLog) {
            $('#status-' + cloud + proId).html('<i class="fa fa-hourglass-half"></i> ' + tempLog + "<p>" + logStart + logCloudList + "</p>");
        } else {
            $('#status-'+ cloud + proId).html('<i class="fa fa-hourglass-half"></i> ' + "<p>" + logStart + logCloudList + "</p>");
        }
        clearInterval(window[intervalName]);
        checkCloudTimer(proId, retryTimer, cloud);
        if (retryTimer <= 19000) {
            retryTimer += 1000;
        }
        var lastStat = window.modalRec[cloud+'last_status_' + proId];
        if (lastStat === "running" || lastStat === "initiated") {
            $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').css('display', 'inline');
            $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').removeAttr('disabled');
        }


    } else if (checkCloudStatusLog.status === "running") {
        window.modalRec[cloud+'last_status_' + proId] = checkCloudStatusLog.status;
        //check if run env. in run page is amazon and status is not running (then activate loadRunOptions()
        var chooseEnv = $('#chooseEnv').find(":selected").val();
        if (chooseEnv) {
            var status = $('#chooseEnv').find(":selected").attr("status");
            if (status) {
                if (chooseEnv === cloud+"-" + proId && status !== "running") {
                    loadRunOptions("silent"); //used from runpipeline.js
                }
            }
        }
        if (checkCloudStatusLog.sshText) {
            var sshText = "(" + checkCloudStatusLog.sshText + ")";
        } else {
            var sshText = "";
        }
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Start').css('display', 'none');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').css('display', 'inline');
        $('#status-' + cloud + proId).html('Running <br/>' + sshText + "<p>" + logStart + logCloudList + "</p>");
        window.modalRec[cloud+'last_status_log_' + proId] = 'Running <br/>' + sshText;
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').removeAttr('disabled');

    } else if (checkCloudStatusLog.status === "inactive") {
        clearInterval(window[intervalName]);
        $('#status-' + cloud + proId).text('Inactive');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').attr('disabled', 'disabled');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Start').css('display', 'inline');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').css('display', 'inline');
    } else if (checkCloudStatusLog.status === "terminated") {
        var chooseEnv = $('#chooseEnv').find(":selected").val();
        if (chooseEnv) {
            var status = $('#chooseEnv').find(":selected").attr("status");
            if (status) {
                if (chooseEnv === cloud+"-" + proId && status !== "terminated") {
                    loadRunOptions("silent"); //used from runpipeline.js
                }
            }
        }

        window[cloud+"Data"].stop = false;
        clearInterval(window[intervalName]);
        window.modalRec[cloud+'last_status_log_' + proId] = "";
        window.modalRec[cloud+'last_status_' + proId] = checkCloudStatusLog.status;
        var errorText = "";
        if (checkCloudStatusLog.logCloudList) {
            var logText = checkCloudStatusLog.logCloudList;
            if (logText.match(/INSTANCE ID ADDRESS STATUS ROLE(.*)/)) {
                var errorT = logText.match(/INSTANCE ID ADDRESS STATUS ROLE(.*)/)[1];
                if (errorT !== "" && !errorT.match(/stopping/i)) {
                    errorText = "(" + errorT + ")";
                }
            } else {
                if (!logText.match(/stopping/i)) {
                    errorText = "(" + logText + ")";
                }
            }
        } 
        if (errorText === "" && checkCloudStatusLog.logStart) {
            var logTextStart = checkCloudStatusLog.logStart;
            //WARN: One or more errors have been detected parsing EC2 prices
            if ((logTextStart.match(/ERROR/) && !logTextStart.match(/WARN: One or more errors/i)) || logTextStart.match(/denied/i) || logTextStart.match(/invalid/i)|| logTextStart.match(/missing/i) || logTextStart.match(/couldn't/i) || logTextStart.match(/help/i) || logTextStart.match(/wrong/i))
                errorText = "(" + logTextStart + ")";
        }
        $('#status-' + cloud + proId).html('Terminated <br/>' + errorText );
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Start').css('display', 'inline');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').css('display', 'inline');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').attr('disabled', 'disabled');
    } else {
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').css('display', 'inline');
        $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').removeAttr('disabled');
    }
    $('[data-toggle="tooltip"]').tooltip();
    if (checkCloudStatusLog.logStart){
        $('#'+cloud+'LogStart-'+proId).data("logData", checkCloudStatusLog.logStart)
    }
    if (checkCloudStatusLog.logCloudList){
        $('#'+cloud+'CloudListLog-'+proId).data("logData", checkCloudStatusLog.logCloudList)
    }

    // set autoshutdown counter
    var proData = getValues({ p: "getProfileCloud", cloud:cloud, id:proId });
    var autoshutdown_check = proData[0].autoshutdown_check;
    var autoshutdown_active = proData[0].autoshutdown_active;
    var autoshutdown_date = proData[0].autoshutdown_date;
    var pro_status = proData[0].status;

    if (autoshutdown_check == "true" && autoshutdown_active == "true" && autoshutdown_date && (pro_status == "running" || pro_status == "waiting" || pro_status == "initiated" || pro_status == "retry")){
        if (!window[cloud+'countdown_' + proId]){
            window[cloud+'elapsed_' + proId]  = 0;
            window[cloud+'countdown_' + proId] = setInterval(function () {
                window[cloud+'elapsed_' + proId] ++;
                var remaining = autoshutdown_date-window[cloud+'elapsed_' + proId];
                var autoshutdown_check = $('#'+cloud+'autoshutdown-' + proId).is(":checked").toString();
                if (autoshutdown_check == "true"){
                    if (remaining < 1) {
                        clearInterval(window[cloud+'countdown_' + proId]);
                        window[cloud+'countdown_' + proId] = null;
                        window[cloud+'elapsed_' + proId] = 0;
                        stopCloudByAjax(proId, cloud)
                        $('#'+cloud+'shutdownLog-'+proId).text("");
                        $('#'+cloud+'shutdownTimer-' + proId).text("Shutdown Triggered")
                    } else {
                        $('#'+cloud+'shutdownTimer-' + proId).text(remaining+" sec.")
                    }
                } else {
                    $('#'+cloud+'shutdownTimer-' + proId).text("");
                    clearInterval(window[cloud+'countdown_' + proId]);
                    window[cloud+'countdown_' + proId] = null;
                    window[cloud+'elapsed_' + proId] = 0;
                }
            }, 1000); 
        }
    }

}

function stopCloudByAjax(proId, cloud){
    var intervalName = 'interval_'+cloud+'Status_' + proId;
    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: { "id": proId, cloud: cloud, "p": "stopProCloud" },
        async: true,
        success: function (s) {
            if (s.stop_cloud) {
                window[cloud+"Data"].stop = true;
                $('#status-'+ cloud + proId).html('<i class="fa fa-hourglass-1"></i> Waiting for termination..');
                //clear previous interval and set new one(with faster check interval).
                clearInterval(window[intervalName]);
                setTimeout(function () { checkCloudTimer(proId, 5500, cloud); }, 1000);
            }
        }
    });
}

function updCloudActive(proId, proData, cloud) {
    if (!proData){
        proData = getValues({ p: "getProfileCloud", cloud: cloud, id:proId });
        if (proData){
            proData = proData[0];
        }
    }
    var autoshutdown_check = proData.autoshutdown_check;
    var autoshutdown_active = proData.autoshutdown_active;
    var autoshutdown_date = proData.autoshutdown_date;
    var pro_status = proData.status;
    if (autoshutdown_check == null){ autoshutdown_check = ""; }
    if (autoshutdown_active == null){ autoshutdown_active = ""; }
    if (autoshutdown_date == null){ autoshutdown_date = ""; }
    var activeTxt = "";

    if (autoshutdown_check == "true" && (pro_status == "running" || pro_status == "waiting" || pro_status == "initiated" || pro_status == "retry")){
        if (autoshutdown_active == "true"){
            if (autoshutdown_date !== ""){
                activeTxt = "Countdown for shutdown: ";
            } else {
                activeTxt = "Activated - Waiting for the termination of active run";
            }
        } else {
            activeTxt = "Idle - Waiting for the initial run";
        }
    }
    $('#'+cloud+'shutdownLog-'+proId).text(activeTxt);
}

//mode: edit/add , type: runenv
function getWizardLi(name, mode, type, href, wid){
    return '<li><a class="col-sm-11" wid="'+wid+'" mode="'+mode+'"  type="'+type+'"  data-toggle="modal" href="'+href+'"><i class="fa fa-user text-yellow"></i> '+name+'</a><a class="col-sm-1 delete_wizard" style="position:static" data-toggle="tooltip" data-placement="bottom" title="Delete"><i style="padding:5px;" class="fa fa-trash pull-right"></i></a></li>';
}

function loadOngoingWizard (type){
    type = type || ""; //default ""
    //check if #manageProfileWizard is exist
    if ($('#manageProfileWizard').length > 0) {
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: {p: "getWizard", type:"all"},
            async: true,
            cache: false,
            success: function (data) {
                var countActive = 0;
                $("#ongoingwizard").empty();
                if(data){
                    for (var k = 0; k < data.length; k++) {
                        if(data[k]){
                            if(data[k].id && data[k].name && data[k].status == "active" && data[k].deleted === "0"){
                                countActive++
                                $("#ongoingwizard").append(getWizardLi(data[k].name, "edit", "runenv", "#profilewizardmodal", data[k].id));
                            }
                        }
                    }
                }
                if (countActive > 0) {
                    $('#savedWizardHeader').css('display', 'inline');
                    $('#wizAmount').css('display', 'inline');
                    $('#wizAmount').text(countActive);
                    $('[data-toggle="tooltip"]').tooltip();
                } else {
                    $('#savedWizardHeader').css('display', 'none');
                    $('#wizAmount').text(countActive);
                    $('#wizAmount').css('display', 'none');
                    if (type == "onload"){
                        // check if user has seen any wizard before
                        if (data.length < 1) {
                            //check if user has any run environment. if not-> show wizard
                            var proData = getValues({ p: "getProfiles" });
                            if (proData) {
                                if (proData.length < 1) {
                                    $("#addProfileWizard").trigger("click");
                                }
                            }
                        }
                    }
                }
            },
            error: function (jqXHR, exception) {
                console.log("#Error:")
                console.log(jqXHR.status)
                console.log(exception)
            }
        });
    }
}   



function addCloudRow(cloud, id, name, executor, instance_type, image_id, subnet_id, autoshutdown_check, autoshutdown_active, autoshutdown_date, status, proData) {
    if (autoshutdown_check == null){ autoshutdown_check = ""; }
    var checked = "";
    if (autoshutdown_check == "true"){
        checked = "checked";
    }

    var checkBox = '<input id="'+cloud+'autoshutdown-' + id + '" cloud="'+cloud+'" class="autoShutCheck" type="checkbox"  name="autoshutdown_check" '+checked+'><p id="'+cloud+'shutdownLog-' + id + '"></p><p id="'+cloud+'shutdownTimer-' + id + '"></p>';

    $('#'+cloud+'Table > tbody').append('<tr id="'+cloud+'-' + id + '"> <td>' + name + '</td><td>Instance_type: ' + instance_type + '<br>  Image id: ' + image_id + '<br>  Executor: ' + executor + '<br>  </td><td>'+checkBox+'</td><td id="status-'+ cloud + id + '"><i class="fa fa-hourglass-half"></i></td><td>' + getButtonsDef(cloud, 'Start') + getButtonsDef(cloud, 'Stop') + '</td></tr>');
    updCloudActive(id, proData, cloud)

}

function initCloudConsole(cloud){
    $('#'+cloud+'Modal').on('show.bs.modal', function (event) {
        $(this).find('form').trigger('reset');
        var proData = getValues({ p: "getProfileCloud", cloud:cloud});
        $('#'+cloud+'Table > tbody').empty();
        $.each(proData, function (el) {
            addCloudRow(cloud, proData[el].id, proData[el].name, proData[el].executor, proData[el].instance_type, proData[el].image_id, proData[el].subnet_id, proData[el].autoshutdown_check, proData[el].autoshutdown_active, proData[el].autoshutdown_date, proData[el].status, proData[el]);
            checkCloudStatus(proData[el].id, cloud);
        });
    });

    $('#'+cloud+'Modal').on('hide.bs.modal', function (event) {
        $('#'+cloud+'Table td ').remove();
        checkCloudProfiles("notimer", cloud);
    });

    $('#'+cloud+'Modal').on('click', '#'+cloud+'Start', function (e) {
        e.preventDefault();
        var clickedRowId = $(this).closest('tr').attr('id'); //local-20
        var patt = /(.*)-(.*)/;
        var proId = clickedRowId.replace(patt, '$2');
        if (window[cloud+'countdown_' + proId]){
            window[cloud+'countdown_' + proId] = null;
        }
        //enter cloud details modal
        $('#add'+cloud+'NodeModal').off();
        $('#add'+cloud+'NodeModal').on('show.bs.modal', function (event) {
            $(this).find('form').trigger('reset');
            if ($('#'+cloud+'autoshutdown-'+proId).is(":checked").toString() == "true"){
                $('#'+cloud+'autoshut_check').prop('checked', true)
            }
        });
        $('#add'+cloud+'NodeModal').on('hide.bs.modal', function (event) {
            $('#'+cloud+'autoscaleDiv').attr('class', 'collapse');
        });
        $('#add'+cloud+'NodeModal').on('click', '#'+cloud+'Activate', function (event) {
            event.preventDefault();
            var data = {};
            var numNodes = $('#'+cloud+'numNodes').val();
            var autoshutdown_check = $('#'+cloud+'autoshut_check').is(":checked").toString();

            $('#'+cloud+'shutdownLog-'+proId).empty();
            $('#'+cloud+'shutdownTimer-'+proId).empty();
            if (autoshutdown_check == "true"){
                $('#'+cloud+'autoshutdown-'+proId).prop('checked', true);
                updCloudActive(proId, "", cloud)
            } else {
                $('#'+cloud+'autoshutdown-'+proId).prop('checked', false);
            }
            var autoscale_check = $('#'+cloud+'autoscale_check').is(":checked").toString();
            var autoscale_maxIns = $('#'+cloud+'autoscale_maxIns').val();
            if (numNodes !== '') {
                data = {
                    "id": proId,
                    "nodes": numNodes,
                    "cloud": cloud,
                    "autoshutdown_check": autoshutdown_check,
                    "autoscale_check": autoscale_check,
                    "autoscale_maxIns": autoscale_maxIns,
                    "p": "startProCloud"
                };
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: data,
                    async: true,
                    success: function (s) {
                        if (s.start_cloud) {
                            // check the amazon profiles activity each minute.
                            $('#status-' + cloud + proId).html('<i class="fa fa-hourglass-1"></i> Waiting for reply..');
                            $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Start').css('display', 'none');
                            $('#'+cloud+'Table > tbody > #'+cloud+'-' + proId + ' > > #'+cloud+'Stop').attr('disabled', 'disabled');
                            $('#add'+cloud+'NodeModal').modal('hide');
                            var intervalName = 'interval_'+cloud+'Status_' + proId;
                            clearInterval(window[intervalName]);
                            checkCloudTimer(proId, 20000, cloud);
                        }
                    }
                });
            }
        });
        $('#add'+cloud+'NodeModal').modal('show');
    });

    $('#'+cloud+'Modal').on('click', '#'+cloud+'Stop', function (e) {
        e.preventDefault();
        var clickedRowId = $(this).closest('tr').attr('id'); //local-20
        var patt = /(.*)-(.*)/;
        var proId = clickedRowId.replace(patt, '$2');
        stopCloudByAjax(proId,cloud);
        if (window[cloud+'countdown_' + proId]){
            clearInterval(window[cloud+'countdown_' + proId]);
        }
        $('#'+cloud+'shutdownTimer-' + proId).text("");
        window[cloud+'elapsed_' + proId] = 0;
        $('#'+cloud+'shutdownLog-'+proId).empty();
        $('#'+cloud+'shutdownTimer-'+proId).empty();
    });

}


var disableDoubleClickCollapse = function(id1check, id1div, id2check,id2div, baseid){
    //not allow to click both option
    $('#'+baseid).on('show.bs.collapse', '#'+id1div, function () {
        if ($('#'+id2check).is(":checked") && $('#'+id1check).is(":checked")) {
            $('#'+id2check).trigger("click");
        }
        $('#'+id1check).attr('onclick', "return false;");
    });
    $('#'+baseid).on('show.bs.collapse', '#'+id2div, function () {
        if ($('#'+id2check).is(":checked") && $('#'+id1check).is(":checked")) {
            $('#'+id1check).trigger("click");
        }
        $('#'+id2check).attr('onclick', "return false;");
    });
    $('#'+baseid).on('shown.bs.collapse', '#'+id1div, function () {
        if ($('#'+id2check).is(":checked") && $('#'+id1check).is(":checked")) {
            $('#'+id2check).trigger("click");
        }
        $('#'+id1check).removeAttr('onclick');
    });
    $('#'+baseid).on('shown.bs.collapse', '#'+id2div, function () {
        if ($('#'+id2check).is(":checked") && $('#'+id1check).is(":checked")) {
            $('#'+id1check).trigger("click");
        }
        $('#'+id2check).removeAttr('onclick');
    });

    $('#'+baseid).on('hide.bs.collapse', '#'+id2div, function () {
        $('#'+id2check).attr('onclick', "return false;");
    });
    $('#'+baseid).on('hide.bs.collapse', '#'+id1div, function () {
        $('#'+id1check).attr('onclick', "return false;");
    });
    $('#'+baseid).on('hidden.bs.collapse', '#'+id1div, function () {
        $('#'+id1check).removeAttr('onclick');
    });
    $('#'+baseid).on('hidden.bs.collapse', '#'+id2div, function () {
        $('#'+id2check).removeAttr('onclick');
    });
}


$(document).ready(function () {
    initCloudConsole("amazon");
    initCloudConsole("google");

    $(function () {
        $(document).on('change', '.autoShutCheck', function (event) {
            var cloud = $(this).attr("cloud");
            var autoShutCheck = $(this).is(":checked").toString();
            var profileId = $(this).attr("id").split("-")[1];
            var proData = getValuesErr({ p: "updateCloudShutdownCheck", cloud:cloud, autoshutdown_check:autoShutCheck, id: profileId });
            if (autoShutCheck == "false"){
                $('#'+cloud+'shutdownLog-'+profileId).empty();
                $('#'+cloud+'shutdownTimer-'+profileId).empty();
            } else {
                updCloudActive(profileId, "", cloud)
                checkCloudStatus(profileId, cloud);
            }
        });
        $(document).on('click', 'button[data=cloudLogStart]', function (e) {
            e.preventDefault();
            var cloud = $(this).attr("cloud");
            var profileId = $(this).attr("id").split("-")[1];
            var logData = $('#'+cloud+'LogStart-'+profileId).data("logData")
            if (logData.match(/Downloading nextflow dependencies(.*)Fetching EC2 prices/)){
                logData = logData.replace(/Downloading nextflow dependencies(.*)Fetching EC2 prices/,'')
            }
            logData = logData.replace(/\n/g, '<br/>');
            showInfoModal("#infoMod","#infoModText", logData)
        });
        $(document).on('click', 'button[data=cloudListLog]', function (e) {
            e.preventDefault();
            var profileId = $(this).attr("id").split("-")[1];
            var cloud = $(this).attr("cloud");
            var logData = $('#'+cloud+'CloudListLog-'+profileId).data("logData")
            logData = logData.replace(/\n/g, '<br/>');
            showInfoModal("#infoMod","#infoModText", logData)
        });
    });


    $(function () {
        //check latest release and warn admin
        if (usRole == "admin"){
            var currentVersion = $("#dn-version").attr("ver")
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {p: "checkNewRelease", version:currentVersion},
                async: true,
                cache: true,
                success: function (releaseData) {
                    if (IsJsonString(releaseData)) {
                        var json = JSON.parse(releaseData)
                        if (json) {
                            if (json.release_cmd_log){
                                var latestVersion = json.release_cmd_log.tag_name;
                                var releaseNotes = json.release_cmd_log.body;
                                if (latestVersion && releaseNotes){
                                    var scriptsPath = "scripts";
                                    if (json.scripts_path){
                                        scriptsPath = json.scripts_path
                                    }
                                    $("#softUpdBut").css("display", "inline")
                                    $("#softUptDesc").html("DolphinNext "+latestVersion+ " is now available. You can update your mirror by running following command:");
                                    $("#softUptCmd").val("cd "+scriptsPath+" && python updateDN.py --version "+latestVersion)
                                    $("#softUptReleaseNotes").val(releaseNotes)
                                }
                            }
                        }
                    }
                },
                error: function (jqXHR, exception) {
                    console.log("#Error:")
                    console.log(jqXHR.status)
                    console.log(exception)
                }
            });


        }
        //load news on click to version button
        $(document).on('click', '#dnVersionBut', function (event) {
            var checkLoad = $("#versionNotes").attr("readonly")
            // For some browsers, `attr` is undefined; for others, `attr` is false. Check for both.
            if (typeof checkLoad === typeof undefined || checkLoad === false) {
                var changeLogData = getValues({ p: "getChangeLog" });
                if (changeLogData){
                    $("#versionNotes").val(changeLogData)
                    $("#versionNotes").attr('readonly','readonly');

                } 
            }
        });
    });
    //check active wizard and fill the dropdown and show warning
    loadOngoingWizard("onload")


    //--------- permission control for process/pipeline/run starts-------
    $(function () {
        var previousOpt;
        var checkPermissionUpdt = function (id,perms,group_id,format) {
            var warnUser = false;
            var infoText = '';
            if (format == "pipeline" || format == "run" ){
                var checkPermissionUpdt = getValues({ p: "checkPermUpdtPipeline", "pipeline_id": id, perms:perms,  group_id:group_id}); 
            } else if (format == "process"){
                var checkPermissionUpdt = getValues({ p: "checkPermUpdtProcess", "process_id": id, perms:perms,  group_id:group_id}); 
            }
            console.log(checkPermissionUpdt);
            var warnAr = $.map(checkPermissionUpdt, function(value, index) {
                return [value];
            });
            var numOfErr = warnAr.length;
            if (numOfErr > 0) {
                warnUser = true;
                if (format == "pipeline" || format == "process" ){
                    infoText += 'It is not allowed to change permission/group of current revision because of the following reason(s): </br></br>'
                } else if (format == "run"){
                    infoText += "Permission of the pipeline needs to be updated in order to share this run. However, it couldn't be changed because of the following reason(s): </br></br>"
                } 
                $.each(warnAr, function (element) {
                    infoText += warnAr[element]+"</br>";
                });
            }
            return [warnUser, infoText];
        }

        $(document).on('focus', '.permscheck', function () {
            previousOpt = $(this).children("option:selected");
        }).on('change', '.permscheck', function (event) {
            var dropdownID = this.id;
            if (dropdownID == "permsPipe" || dropdownID == "groupSelPipe"){
                var selGroup = $("#groupSelPipe").val();
                var selPerm = $("#permsPipe").val();
                var pipeline_id = $('#pipeline-title').attr('pipelineid');
                if (pipeline_id) {
                    var warnUser = false;
                    var infoText = '';
                    //check if pipeline permission is allowed to change
                    [warnUser, infoText] = checkPermissionUpdt(pipeline_id,selPerm,selGroup, "pipeline");
                    if (warnUser === true) {
                        previousOpt.prop("selected", true);
                        showInfoModal("#infoMod","#infoModText", infoText);
                    } else {
                        autosaveDetails();
                    }
                } else {
                    autosaveDetails();
                }
            } else if (dropdownID == "permsRun" || dropdownID == "groupSelRun"){
                var selGroup = $("#groupSelRun").val();
                var selPerm = $("#permsRun").val();
                var pipeline_id = $('#pipeline-title').attr('pipeline_id');
                if (pipeline_id){
                    var warnUser = false;
                    var infoText = '';
                    //check if pipeline permission is allowed to change
                    [warnUser, infoText] = checkPermissionUpdt(pipeline_id,selPerm,selGroup, "run");
                    if (warnUser === true) {
                        previousOpt.prop("selected", true);
                        showInfoModal("#infoMod","#infoModText", infoText);
                    } 
                }
            } else if (dropdownID == "permsPro" || dropdownID == "groupSelPro"){
                var selGroup = $("#groupSelPro").val();
                var selPerm = $("#permsPro").val();
                var process_id = $('#mIdPro').val()
                if (process_id){
                    var warnUser = false;
                    var infoText = '';
                    var numOfRuns = '';
                    //check if process permission is allowed to change
                    [warnUser, infoText] = checkPermissionUpdt(process_id,selPerm,selGroup, "process");
                    if (warnUser === true) {
                        previousOpt.prop("selected", true);
                        showInfoModal("#infoMod","#infoModText", infoText);
                    } 
                }
            }
            //reasign previousOpt value after change
            previousOpt = $(this).children("option:selected");
        });
    });
    //---- permission control for process/pipeline/run ends

});

//load filter sidebar menu options
if (usRole === "admin") {
    $("#filterMenu").append('<li><a href="#" data-value="admin" tabIndex="-1"><input type="checkbox"/>&nbsp;Admin</a></li>');
    $("#filterMenu").append('<li><a href="#" data-value="630" tabIndex="-1"><input type="checkbox"/>&nbsp;Waiting Approval</a></li>');
}
$("#filterMenu").append('<li><a href="#" data-value="3" tabIndex="-1"><input type="checkbox"/>&nbsp;Private</a></li>');
$("#filterMenu").append('<li><a href="#" data-value="63" tabIndex="-1"><input type="checkbox"/>&nbsp;Public</a></li>');
allUserGrp = getValues({ p: "getUserGroups" });
if (allUserGrp && allUserGrp != '') {
    for (var i = 0; i < allUserGrp.length; i++) {
        var param = allUserGrp[i];
        $("#filterMenu").append('<li><a href="#" data-value="group-' + param.id + '" tabIndex="-1"><input type="checkbox"/>&nbsp;' + param.name + '</a></li>');
    }
}

//filter sidebar menu (multiple selection feature)
var optionsFilter = [];
$('.filterM a').on('click', function (event) {
    $('#tags').val("");
    var $target = $(event.currentTarget),
        val = $target.attr('data-value'),
        $inp = $target.find('input'),
        idx;
    if ((idx = optionsFilter.indexOf(val)) > -1) {
        optionsFilter.splice(idx, 1);
        setTimeout(function () { $inp.prop('checked', false) }, 0);
    } else {
        optionsFilter.push(val);
        setTimeout(function () { $inp.prop('checked', true) }, 0);
    }
    $(event.target).blur();
    //filter based on options array
    filterSideBar(optionsFilter)
    return false;
});


function filterSideBar(options) {
    var tagElems = $('#autocompletes1').children()
    if (options.length) {
        $(tagElems).hide();
        var selOptArr = [];
        var group_idArr = [];
        for (var i = 0; i < options.length; i++) {
            if (options[i] === '3' || options[i] === '63' || options[i] === '630' || options[i] === 'admin') {
                var selOpt = options[i];
                selOptArr.push(selOpt);
            } else if (options[i].match(/group-(.*)/)) {
                var group_id = options[i].match(/group-(.*)/)[1];
                var selOpt = "15";
                selOptArr.push(selOpt);
                group_idArr.push(group_id);
            }
        }
        for (var i = 0; i < tagElems.length; i++) {
            var tagElems2 = $(tagElems).eq(i).children().eq(1).children()
            $(tagElems2).hide()
            for (var j = 0; j < tagElems2.length; j++) {
                if ($(tagElems2).eq(j).attr('pub')) {
                    if ($(tagElems2).eq(j).attr('pub') === '1') {
                        var checkPubText = '630';
                    } else {
                        var checkPubText = '0';
                    }
                }
                var checkAdmin = false;
                if ($(tagElems2).eq(j).attr('admin')) {
                    if ($(tagElems2).eq(j).attr('admin') === '1') {
                        checkAdmin = $.inArray("admin", selOptArr) >= 0;
                    } 
                }
                var checkPublish = $.inArray(checkPubText, selOptArr) >= 0;
                var checkPerm = $.inArray($(tagElems2).eq(j).attr('p'), selOptArr) >= 0;
                var checkGroup = $.inArray($(tagElems2).eq(j).attr('g'), group_idArr) >= 0;
                if (($(tagElems2).eq(j).attr('p') === "15" && checkPerm && checkGroup ) || ($(tagElems2).eq(j).attr('p') === "3" && checkPerm) || ($(tagElems2).eq(j).attr('p') === "63" && checkPerm) || ($(tagElems2).eq(j).attr('p') !== "63" && checkPublish) || checkAdmin) {
                    $(tagElems).eq(i).show()
                    $(tagElems2).eq(j).show()
                }
            }
        }
    } else {
        //if nothing is selected show everything
        //but hide if attribute admin=1 is found
        $(tagElems).hide();
        for (var i = 0; i < tagElems.length; i++) {
            var tagElems2 = $(tagElems).eq(i).children().eq(1).children();
            $(tagElems2).hide()
            for (var j = 0; j < tagElems2.length; j++) {
                if ($(tagElems2).eq(j).attr('admin') === '1') {
                    $(tagElems2).eq(j).hide()
                } else {
                    $(tagElems2).eq(j).show()
                    $(tagElems).eq(i).show()
                }
            }
        }
    }
    $('#inputs').show();
    $('#outputs').show();
    $('.header').show();
    $('#Pipelines').show();
}

//SideBar menu Search Function
$('.main-sidebar').on('keyup', '#tags', function (e) {
    $('.filterM a >').prop('checked', false);
    var tagElems = $('#autocompletes1').children()
    $(tagElems).hide()
    for (var i = 0; i < tagElems.length; i++) {
        var tagElems2 = $(tagElems).eq(i).children().eq(1).children()
        $(tagElems2).hide()
        $(tagElems).eq(i).closest('li').children('ul.treeview-menu').hide()
        for (var j = 0; j < tagElems2.length; j++) {
            if (($(tagElems2).eq(j).children().attr("origin"))){
                if (($(tagElems2).eq(j).children().attr("origin").toLowerCase()).indexOf($(this).val().toLowerCase()) > -1) {
                    $(tagElems).eq(i).show()
                    if ($(this).val().toLowerCase() !== "") {
                        $(tagElems).eq(i).closest('li').addClass('menu-open')
                        $(tagElems).eq(i).closest('li').children('ul.treeview-menu').show()
                    } else {
                        $(tagElems).eq(i).closest('li').removeClass('menu-open')
                        $(tagElems).show()
                    }
                    $(tagElems2).eq(j).show()
                }
            }
        }
    }
    $('#inputs').show();
    $('#outputs').show();
    $('.header').show();
    $('#Pipelines').show();
});

//turn (a,b,c)  into (a|b|c) format
function fixParentheses(outputName) {
    if (outputName.match(/(.*)\((.*?),(.*?)\)(.*)/)) {
        var patt = /(.*)\((.*?),(.*?)\)(.*)/;
        var insideBrackets = outputName.replace(patt, '$2' + "," + '$3');
        insideBrackets = insideBrackets.replace(/\,/g, '|')
        var outputNameFix = outputName.replace(patt, '$1' + "(" + insideBrackets + ")" + '$4');
        if (outputNameFix.match(/(.*)\((.*?),(.*?)\)(.*)/)) {
            return fixParentheses(outputNameFix);
        } else {
            return outputNameFix;
        }
    } else {
        return outputName;
    }
}

//table buttons:
var SELECT = 4; // 1
var EDIT = 2; // 10
var REMOVE = 1; // 100
function getTableButtons(name, buttons) {
    var selectButton = '';
    var editButton = '';
    var removeButton = '';
    if (buttons.toString(2) & SELECT) {
        selectButton = '<div style="display: inline-flex"><button type="button" class="btn btn-primary btn-sm" title="Select" id="' + name + 'select">Select</button> &nbsp; '
    }
    if (buttons.toString(2) & EDIT) {
        editButton = '<div style="display: inline-flex"><button type="button" class="btn btn-primary btn-sm" title="Edit" id="' + name + 'edit" data-toggle="modal" data-target="#' + name + 'modal">Edit</button> &nbsp;'
    }
    if (buttons.toString(2) & REMOVE) {
        removeButton = '<button type="button" class="btn btn-primary btn-sm" title="Remove" id="' + name + 'remove">Remove</button></div>'
    }
    return selectButton + editButton + removeButton
}

// eg. name:run buttons:select
function getButtonsModal(name, buttons) {
    var buttonId = buttons.split(' ')[0];
    var button = '<button type="button" style= "margin-right:5px;" class="btn btn-primary btn-sm" title="' + buttons + '" id="' + name + buttonId + '" data-toggle="modal" data-target="#' + name + 'modal">' + buttons + '</button>';
    return button;
}
//Default type of buttons
function getButtonsDef(name, buttons, defVal) {
    if (defVal) {
        var defValText = 'defval="' + defVal + '" ';
    } else {
        var defValText = "";
    }
    var buttonId = buttons.split(' ')[0];
    var button = '<button type="submit" style= "margin-right:5px;" class="btn btn-primary btn-sm" ' + defValText + ' title="' + buttons + '" id="' + name + buttonId + '">' + buttons + '</button>';
    return button;
}
//Default type of dropdown. 
function getDropdownDef(id, attr, optionArr, defText) {
    if (defText && defText != "") {
        var firstOpt = '<option value="" selected="">Choose Value </option>';
    } else {
        var firstOpt = "";
    }
    var optText = "";
    $.each(optionArr, function (el) {
        var opt = $.trim(optionArr[el]);
        optText += '<option value="' + opt + '">' + opt + '</option>';
    });
    var dropdownMenu = '<select style="width:92px; height:30px; margin-right:5px;" class="btn  btn-primary btn-sm"  '+attr+'="'+attr+'" id="' + id + '">' + firstOpt + optText + '</select>';;
    return dropdownMenu;
}

function getIconButtonModal(name, buttons, icon) {
    var buttonId = buttons.split(' ')[0];
    var button = '<button type="submit" style="background:none; padding:0px;" class="btn" title="' + buttons + '" id="' + name + buttonId + '" data-toggle="modal" data-target="#' + name + 'modal"><a data-toggle="tooltip" data-placement="bottom" data-original-title="' + name + '"><i class="' + icon + '" style="font-size: 17px;"></i></a></button>';
    return button;
}

function getIconButton(name, buttons, icon) {
    var buttonId = buttons.split(' ')[0];
    var button = '<button type="submit" style="background:none; padding:0px;" class="btn" title="' + buttons + '" id="' + name + buttonId + '"><a data-toggle="tooltip" data-placement="bottom" data-original-title="' + name + '"><i class="' + icon + '" style="font-size: 17px;"></i></a></button>';
    return button;
}




//use changeVal to trigger change event after using val()
$.fn.changeVal = function (v) {
    return $(this).val(v).trigger("change");
}

//Adjustable textwidth
var $inputText = $('input.width-dynamic');
// Resize based on text if text.length > 0
// Otherwise resize based on the placeholder

$("input.width-dynamic").on("change", function () {
    var namePip = $('input.width-dynamic').val();
    resizeForText.call($inputText, namePip);
});

function resizeForText(text) {
    var $this = $(this);
    if (!text.trim()) {
        text = $this.attr('placeholder').trim();
    }
    var $span = $this.parent().find('span.width-dynamic');
    $span.text(text);
    var $inputSize = $span.width() + 10;
    if ($inputSize < 50) {
        $inputSize = 50;
    }
    $this.css("width", $inputSize);
}
$inputText.keypress(function (e) {
    if (e.which && e.charCode) {
        var c = String.fromCharCode(e.keyCode | e.charCode);
        var $this = $(this);
        resizeForText.call($this, $this.val() + c);
    }
});
// Backspace event only fires for keyup
$inputText.keyup(function (e) {
    if (e.keyCode === 8 || e.keyCode === 46) {
        resizeForText.call($(this), $(this).val());
    }
});
$inputText.each(function () {
    var $this = $(this);
    resizeForText.call($this, $this.val())
});

function numberWithCommas(x) {
    if (typeof x !== 'undefined'){
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    } else {
        return ""
    }
}


function tsvPercent(tsv) {
    var tsvPercent = "";
    var tsv = $.trim(tsv);
    var lines = tsv.split("\n");
    tsvPercent += lines[0] + "\n";
    var headers = lines[0].split("\t");
    for (var i = 1; i < lines.length; i++) {
        var currentline = lines[i].split("\t");
        var divider = currentline[1];
        tsvPercent += currentline[0] + "\t" + numberWithCommas(currentline[1]);
        for (var j = 2; j < currentline.length; j++) {
            if ($.isNumeric(divider) && $.isNumeric(currentline[j] / divider)){
                tsvPercent += "\t" + numberWithCommas(currentline[j]) + " (" + parseFloat(Math.round(currentline[j] / divider * 100 * 100) / 100).toFixed(2) + "%)";
            } else {
                tsvPercent += "\t" + numberWithCommas(currentline[j]);
            }
            if (currentline.length - 1 == j) {
                tsvPercent += "\n"
            }
        }
    }

    return tsvPercent
}
//var tsv is the TSV file with headers
//columns: [{title: "Id", data: "Id"} 1: {title: "Name", data: "Name"}]
//data: [{Id: "123", Name: "John Doe Fresno"},{Id: "124", Name: "Alice Alicia"}]
function tsvConvert(tsv, format, fixHeader) {
    var tsv = $.trim(tsv);
    var lines = tsv.split("\n");
    if (fixHeader){
        lines[0] = lines[0].replace(/\./g, "_");
    }
    var headers = lines[0].split("\t");
    var data = [];
    for (var i = 1; i < lines.length; i++) {
        var obj = {};
        var currentline = lines[i].split("\t");
        for (var j = 0; j < headers.length; j++) {
            obj[headers[j]] = currentline[j];
        }
        data.push(obj);
    }
    if (format == "json") {
        return data;
    }
    if (format == "json2") {
        var result = { columns: [], data: data };
        for (var j = 0; j < headers.length; j++) {
            var obj = {};
            obj.title = headers[j]
            obj.data = headers[j]
            result.columns.push(obj);
        }
        return result;
    }
}

function reportAjaxError(jqXHR, exception, query){
    var msg = '';
    if (jqXHR.status === 0) {
        msg = 'Not connect.\n Verify Network.';
    } else if (jqXHR.status == 404) {
        msg = 'Requested page not found. [404]';
    } else if (jqXHR.status == 500) {
        msg = 'Internal Server Error [500].';
    } else if (exception === 'parsererror') {
        msg = 'Requested JSON parse failed.';
    } else if (exception === 'timeout') {
        msg = 'Time out error.';
    } else if (exception === 'abort') {
        msg = 'Ajax request aborted.';
    } else {
        if (jqXHR.responseText) {
            msg = 'Uncaught Error.\n' + jqXHR.responseText;
        }
    }
    console.log("#Query: ");
    console.log(query);
    console.log("#Ajax Error: "+msg);
}

function getValues(data, async) {
    async = async ||false; //default false
    var result = null;
    $.ajax({
        url: "ajax/ajaxquery.php",
        data: data,
        async: async,
        cache: false,
        type: "POST",
        success: function (data) {
            result = data;
        },
        error: function (jqXHR, exception) {
            reportAjaxError(jqXHR, exception, data)
        }
    });
    return result;
}

function getValuesErr(data, async) {
    var result = null;
    $.ajax({
        url: "ajax/ajaxquery.php",
        data: data,
        async: async,
        cache: false,
        type: "POST",
        success: function (data) {
            result = data;
        },
        error: function (jqXHR, exception) {
            reportAjaxError(jqXHR, exception, data)
        }
    });
    return result;
}

//function crossOriginCall(url) { 
//    var result = null;
//    $.ajax({
//        crossOrigin: true,
//        url: url,
//        //dataType: "json", //no need. if you use crossOrigin, the dataType will be override with "json"
//        //charset: 'ISO-8859-1', //use it to define the charset of the target url
//        context: {},
//        success: function(data) {
//            result = data;
//        }
//    })
//    console.log(result)
//    return result;
//}


function apiCallUrl(url) { 
    var result = null;
    $.ajax({
        url: url,
        async: false,
        type: "GET",
        success: function (data) {
            result = data;
        },
        error: function (jqXHR, exception) {
            reportAjaxError(jqXHR, exception, url)
        }
    });
    return result;
}




function xmlStringToJson(xmlString){
    var expXMLraw = '<document>'+$('<div/>').html(xmlString).text().trim()+'</document>';
    var parser = new DOMParser();
    var xml = parser.parseFromString(expXMLraw,"text/xml");
    var obj = xmlToJson(xml)
    if (obj.document){
        obj = obj.document
    }
    return obj;
}

// Changes XML to JSON
function xmlToJson(xml) {
    // Create the return object
    var obj = {};
    if (xml.nodeType == 1) { // element
        // do attributes
        if (xml.attributes.length > 0) {
            obj["attributes"] = {};
            for (var j = 0; j < xml.attributes.length; j++) {
                var attribute = xml.attributes.item(j);
                obj["attributes"][attribute.nodeName] = attribute.value;
            }
        }
    } else if (xml.nodeType == 3) { // text
        obj = xml.nodeValue;
    }
    // do children
    if (xml.hasChildNodes()) {
        for(var i = 0; i < xml.childNodes.length; i++) {
            var item = xml.childNodes.item(i);
            var nodeName = item.nodeName.replace('#','');
            if (typeof(obj[nodeName]) == "undefined") {
                obj[nodeName] = xmlToJson(item);
            } else {
                if (typeof(obj[nodeName].push) == "undefined") {
                    var old = obj[nodeName];
                    obj[nodeName] = [];
                    obj[nodeName].push(old);
                }
                obj[nodeName].push(xmlToJson(item));
            }
        }
    }
    return obj;
};

function callMarkDownApp(text) {
    text = JSON.stringify(text)
    var result = null;
    var localbasepath = $("#basepathinfo").attr("localbasepath")
    $.ajax({
        url: localbasepath + "/ocpu/library/markdownapp/R/rmdtext",
        data: { 'text': text },
        async: false,
        cache: false,
        type: "POST",
        success: function (data) {
            result = data;
        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
    if (result) {
        var lines = result.split("\n");
        for (var i = 0; i < lines.length; i++) {
            if (lines[i].match(/output.html/)) {
                result = localbasepath + lines[i];
                return result
            }
        }
    }
    return result
}




function getValuesAsync(data, callback) {
    var result = null;
    $.ajax({
        url: "ajax/ajaxquery.php",
        data: data,
        async: true,
        cache: false,
        type: "POST",
        success: function (data) {
            result = data;
            callback(result);
        }
    });
}






function truncateName(name, type) {
    if (type === 'inOut') {
        var letterLimit = 8;
    } else if (type === 'process') {
        var letterLimit = 13;
    } else if (type === 'newTable') {
        var letterLimit = 120;
    } else if (type === 'processTable') {
        var letterLimit = 300;
    } else if (type === 'pipelineModule') {
        var letterLimit = 23;
    } else if (type === 'sidebarMenu') {
        return name.substring(0, 20);
    }
    if (name.length > letterLimit)
        return name.substring(0, letterLimit - 1) + '..';
    else
        return name;
}


function cleanRegEx(pat) {
    return pat.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
}

function createElement(type, fields, options) {
    var element = document.createElement(type);
    for (var x = 0; x < fields.length; x++) {
        if (fields[x] == 'OPTION') {
            var opt = document.createElement('option');
            opt.value = options[x];
            opt.innerHTML = options[x];
            element.appendChild(opt);
        } else if (fields[x] == 'OPTION_DIS_SEL') {
            var opt = document.createElement('option');
            opt.value = options[x];
            opt.innerHTML = options[x];
            opt.disabled = true;
            opt.selected = true;
            element.appendChild(opt);
        } else if (fields[x] == 'TEXTNODE') {
            element.appendChild(document.createTextNode(options[x]));
        } else if (fields[x] == 'type' && options[x] == 'button') {
            element.setAttribute(fields[x], options[x]);
            element.innerHTML = element.value;
        } else if (fields[x] == 'INNERHTML') {
            element.innerHTML = options[x]
        } else {
            element.setAttribute(fields[x], options[x]);
        }
    }
    return element;
}

function cleanProcessName(proName) {
    if (proName){
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
        proName = proName.replace(/\//g, "_");
        proName = proName.replace(/\\/g, "_");
        proName = proName.replace(/@/g, "_"); 
    }
    return proName;
}

function createLabel(proName) {
    proName = proName.replace(/_/g, " ");
    proName = proName.replace(/\w\S*/g, function (txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });

    return proName;
}

//use fullsize class to find parent element which is going to be fullscreen
//assign parent divs width as 100% and don't change within featList at below
var toogleFullSize = function (iconElem, type) {
    var elems = $(iconElem).closest("div.fullsize")
    if (type == "expand") {
        var featList = ["z-index", "height", "position", "top", "left", "background"]
        var newValue = ["1049", "100%", "fixed", "0", "0", "white"]
        var oldCSS = {};
        var newCSS = {};
        for (var i = 0; i < featList.length; i++) {
            oldCSS[featList[i]] = elems.css(featList[i])
            newCSS[featList[i]] = newValue[i]
        }
        elems.data("oldCSS", oldCSS);
        elems.css("height", $(window).height())
    } else {
        var newCSS = elems.data("oldCSS");
    }
    //apply css obj
    $.each(newCSS, function (el) {
        elems.css(el, newCSS[el])
    });
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
}

function download_file(fileURL, fileName) {
    // for non-IE
    if (!window.ActiveXObject) {
        var save = document.createElement('a');
        save.href = fileURL;
        save.target = '_blank';
        var filename = fileURL.substring(fileURL.lastIndexOf('/') + 1);
        save.download = fileName || filename;
        if (navigator.userAgent.toLowerCase().match(/(ipad|iphone|safari)/) && navigator.userAgent.search("Chrome") < 0) {
            document.location = save.href;
            // window event not working here
        } else {
            var evt = new MouseEvent('click', {
                'view': window,
                'bubbles': true,
                'cancelable': false
            });
            save.dispatchEvent(evt);
            (window.URL || window.webkitURL).revokeObjectURL(save.href);
        }
    }
    // for IE < 11
    else if (!!window.ActiveXObject && document.execCommand) {
        var _window = window.open(fileURL, '_blank');
        _window.document.close();
        _window.document.execCommand('SaveAs', true, fileName || fileURL)
        _window.close();
    }
}

function downloadText(text, filename) {
    var element = document.createElement('a');
    element.setAttribute('href', 'data:application/octet-stream;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);
    element.style.display = 'none';
    document.body.appendChild(element);
    element.click();
    document.body.removeChild(element);
}




$('.collapseIcon').on('click', function (e) {
    var textClass = $(this).attr('class');
    if (textClass.includes('fa-plus-square-o')) {
        $(this).removeClass('fa-plus-square-o');
        $(this).addClass('fa-minus-square-o');
    } else if (textClass.includes('fa-minus-square-o')) {
        $(this).removeClass('fa-minus-square-o');
        $(this).addClass('fa-plus-square-o');
    }
});

function refreshCollapseIconDiv() {
    $('.collapseIconDiv').on('click', function (e) {
        var textClassPlus = $(this).find('.fa-plus-square-o')[0];
        var textClassMinus = $(this).find('.fa-minus-square-o')[0];
        if (textClassPlus) {
            $(this).css("background-color", "lightgrey")
            $(textClassPlus).removeClass('fa-plus-square-o');
            $(textClassPlus).addClass('fa-minus-square-o');
        } else if (textClassMinus) {
            $(this).css("background-color", "")
            $(textClassMinus).removeClass('fa-minus-square-o');
            $(textClassMinus).addClass('fa-plus-square-o');
        }
    });
    $('.collapseIconItem').on('click', function (e) {
        var itemClass = $(this).attr("class")
        if (itemClass.match(/fa-plus-square-o/)) {
            $(this).removeClass('fa-plus-square-o');
            $(this).addClass('fa-minus-square-o');
        } else if (itemClass.match(/fa-minus-square-o/)) {
            $(this).removeClass('fa-minus-square-o');
            $(this).addClass('fa-plus-square-o');
        }
    });


}

// return array of (.*) in the following regex = "/Job <(.*)> is submitted/g";
function getMultipleRegex(txt, regex) {
    var matches = [];
    var match = regex.exec(txt);
    while (match != null) {
        matches.push(match[1]);
        match = regex.exec(txt);
    }
    return matches;
}

// get object values
function getObjectValues(obj) {
    var vals = Object.keys(obj).map(function(key) {
        return obj[key];
    });
    return vals;
}



//creates ajax object and change color of requiredFields
function createFormObj(formValues, requiredFields) {
    var formObj = {}
    var stop = false;
    for (var i = 0; i < formValues.length; i++) {
        var name = $(formValues[i]).attr("name");
        var type = $(formValues[i]).attr("type");
        var val = "";
        if (type == "radio"){
            for (var k = 0; k < formValues.length; k++) {
                if ($(formValues[k]).attr("name")){
                    if ($(formValues[k]).attr("name") == name && $(formValues[k]).is(':checked')){
                        val = $(formValues[k]).val();
                        break;
                    }  
                }
            }
        } else if (type == "checkbox"){
            if ($(formValues[i]).is(':checked')){
                val = "on";
            }
        } else {
            val = $(formValues[i]).val();
        }
        if (requiredFields.includes(name)) {
            if (val != "") {
                $(formValues[i]).parent().parent().removeClass("has-error")
            } else {
                $(formValues[i]).parent().parent().addClass("has-error")
                stop = true;
            }
        }
        formObj[name] = val
    }
    return [formObj, stop];
}

function cleanHasErrorClass(modalID) {
    var formValues = $(modalID).find('.has-error');
    for (var i = 0; i < formValues.length; i++) {
        $(formValues[i]).removeClass("has-error")
    }
}
//toogleErrorUser('#userModal', "username", "insert", s.username)
//toogleErrorUser('#userModal', "username", "delete", null)
function toogleErrorUser(formID, name, type, error) {
    if (type == "delete") {
        $(formID).find('input[name=' + name + ']').parent().parent().removeClass("has-error");
        $(formID).find('font[name=' + name + ']').remove();
    } else if (type == "insert") {
        $(formID).find('input[name=' + name + ']').parent().parent().addClass("has-error");
        $(formID).find('font[name=' + name + ']').remove();
        $(formID).find('input[name=' + name + ']').parent().append('<font name="' + name + '" class="text-center" color="crimson">' + error + '</font>')
    }
}

// fills the from with the object data. find is comma separated string for form types such as: 'input, p'
//eg.  fillForm('#execNextSettTable','input', exec_next_settings);
function fillForm(formId, find, data) {
    var formValues = $(formId).find(find);
    var keys = Object.keys(data);
    for (var i = 0; i < keys.length; i++) {
        if (data[keys[i]] === "on") {
            $(formValues[i]).attr('checked', true);
        } else {
            $(formValues[i]).val(data[keys[i]]);
        }
    }
}

//use name attr to fill form
function fillFormByName(formId, find, data) {
    var formValues = $(formId).find(find)
    for (var k = 0; k < formValues.length; k++) {
        var nameAttr = $(formValues[k]).attr("name");
        var radioCheck = $(formValues[k]).is(':radio');
        var checkboxCheck = $(formValues[k]).is(':checkbox');
        var keys = Object.keys(data);
        if (data[nameAttr]) {
            if (radioCheck){
                if (data[nameAttr] == $(formValues[k]).val()) {
                    $(formValues[k]).attr("checked", true);
                } 
            } else {
                if (data[nameAttr] === "on") {
                    $(formValues[k]).attr('checked', true);
                } else {
                    $(formValues[k]).val(data[nameAttr]);
                }
            }
        }
    }
}

function fillFormById(formId, find, data) {
    var formValues = $(formId).find(find);
    if (formValues[0]) {
        $.each(formValues, function (st) {
            $(formValues[st]).val(data);
        });
    }
}

function fixCollapseMenu(collapseDiv, checkboxId) {
    $(function () {
        $(collapseDiv).on('show.bs.collapse', function () {
            $(checkboxId).attr('onclick', "return false;");
        });
        $(collapseDiv).on('shown.bs.collapse', function () {
            $(checkboxId).removeAttr('onclick');
        });
        $(collapseDiv).on('hide.bs.collapse', function () {
            $(checkboxId).attr('onclick', "return false;");
        });
        $(collapseDiv).on('hidden.bs.collapse', function () {
            $(checkboxId).removeAttr('onclick');
        });
    });
}

$(function () {
    $("#feedback-tab").click(function () {
        $("#feedback-form").toggle("slide right");
    });

    $("#feedback-form form").on('submit', function (event) {
        var $form = $(this);
        var data = $form.serializeArray();
        var url = window.location.href;
        data.push({ name: "url", value: url })
        data.push({ name: "p", value: "savefeedback" })
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: data,
            success: function () {
                $("#feedback-form").toggle("slide right").find("textarea").val('');
            }
        });
        event.preventDefault();
    });
});

//$("#example").multiline('this\n has\n newlines');
$.fn.multiline = function (text) {
    this.text(text);
    this.html(this.html().replace(/\n/g, '<br/>'));
    return this;
}


function escapeHtml(str) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    if (str === null) {
        return null
    }
    return str.replace(/[&<>"']/g, function (m) { return map[m]; });
}

function decodeHtml(str) {
    var map = {
        '&amp;': '&',
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#039;': "'"
    };
    if (str === null) {
        return ""
    } else if (str === undefined) {
        return ""
    }
    return str.replace(/&amp;|&lt;|&gt;|&quot;|&#039;/g, function (m) { return map[m]; });
}
