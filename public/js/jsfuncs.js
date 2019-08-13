//global data libraries
window.ajaxData = {};

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

function showLoadingDiv(parentId) {
    $("#" + parentId).addClass("loader-spin-parent")
    $("#" + parentId).append('<div class="loader-spin-iconDiv" id="loading-image-' + parentId + '"><img class="loader-spin-icon"  src="css/loader.gif" alt="Loading..." /></div>');
}

function hideLoadingDiv(parentId) {
    $("#" + parentId).removeClass("loader-spin-parent")
    $('#loading-image-' + parentId).remove();
}

//eg showInfoModal('#warnDelete','#warnDeleteText', text)
function showInfoModal(modalId, textID, text) {
    //true if modal is open
    if (($("#infoModal").data('bs.modal') || {}).isShown ){
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

// check the amazon profiles activity each 40 sec.
checkAmzProfiles("timer");

//to start timer, enter "timer" as input
function checkAmzProfiles(timer) {
    var proAmzData = getValues({ p: "getProfileAmazon" });
    if (proAmzData) {
        if (proAmzData.length > 0) {
            $('#manageAmz').css('display', 'inline');
            var countActive = 0;
            for (var k = 0; k < proAmzData.length; k++) {
                if (proAmzData[k].status === "running" || proAmzData[k].status === "waiting" || proAmzData[k].status === "initiated" || proAmzData[k].status === "retry") {
                    countActive++;
                }
                if (timer === "timer") {
                    checkAmazonTimer(proAmzData[k].id, 60000);
                }
                window.modalRec = {};
                window.modalRec['last_status_log_' + proAmzData[k].id] = "";
                window.modalRec['last_status_' + proAmzData[k].id] = proAmzData[k].status;
            }
            if (countActive > 0) {
                $('#amzAmount').css('display', 'inline');
                $('#amzAmount').text(countActive);
            } else {
                $('#amzAmount').text(countActive);
                $('#amzAmount').css('display', 'none');
            }
        }
    }
}

//interval will decide the check period: default: 20 sec. for termination 5 sec
function checkAmazonTimer(proId, interval) {
    window['interval_amzStatus_' + proId] = setInterval(function () {
        var runAmzCloudCheck = runAmazonCloudCheck(proId);
        setTimeout(function () { checkAmazonStatus(proId); }, 5000);
    }, interval);
}

//run CloudCheck command which creates log file in apprx. 3 seconds. 
function runAmazonCloudCheck(proId) {
    var runAmzCloudCheck = getValues({ p: "runAmazonCloudCheck", profileId: proId });
    return runAmzCloudCheck;
}
stopAmz = false;
retryTimer = 5000;
//read CloudCheck log file
function checkAmazonStatus(proId) {
    var checkAmazonStatusLog = getValues({ p: "checkAmazonStatus", profileId: proId });
    console.log(checkAmazonStatusLog)
    var logAmzStart = "";
    var logAmzCloudList = "";
    if (checkAmazonStatusLog.logAmzStart){
        logAmzStart = '<button type="button" class="btn btn-sm" style=" margin:2px; background-color: rgb(59, 140, 188);" data="amzLogStart" id="amzLogStart-'+proId+'" ><a data-toggle="tooltip" data-placement="bottom" data-original-title="Log"><span><i class="fa fa-file-text-o"></i></span></a></button>'
    }
    if (checkAmazonStatusLog.logAmzCloudList){
        logAmzCloudList = '<button type="button" class="btn btn-sm" style=" margin:2px; background-color: rgb(59, 140, 188);" data="AmzCloudListLog" id="AmzCloudListLog-'+proId+'" ><a data-toggle="tooltip" data-placement="bottom" data-original-title="Cloud List"><span><i class="fa fa-file-text-o"></i></span></a></button>'
    }


    if (stopAmz && checkAmazonStatusLog.status !== "terminated") {
        window.modalRec['last_status_log_' + proId] = "Waiting for termination..";
        $('#status-' + proId).html('<i class="fa fa-hourglass-1"></i> Waiting for termination..' + "<p>" + logAmzStart + logAmzCloudList + "</p>");
        clearInterval(window['interval_amzStatus_' + proId]);
        checkAmazonTimer(proId, 5500);
    } else if (checkAmazonStatusLog.status === "waiting") {
        window.modalRec['last_status_log_' + proId] = "Waiting for reply..";
        $('#status-' + proId).html('<i class="fa fa-hourglass-1"></i> Waiting for reply..' + "<p>" + logAmzStart + logAmzCloudList + "</p>");
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStart').css('display', 'none');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').css('display', 'inline');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').attr('disabled', 'disabled');
        clearInterval(window['interval_amzStatus_' + proId]);
        checkAmazonTimer(proId, 20000);
    } else if (checkAmazonStatusLog.status === "initiated") {
        window.modalRec['last_status_log_' + proId] = "Initializing..";
        window.modalRec['last_status_' + proId] = checkAmazonStatusLog.status;
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStart').css('display', 'none');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').css('display', 'inline');
        $('#status-' + proId).html('<i class="fa fa-hourglass-half"></i> Initializing..' + "<p>" + logAmzStart + logAmzCloudList + "</p>");
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').removeAttr('disabled');
        clearInterval(window['interval_amzStatus_' + proId]);
        checkAmazonTimer(proId, 20000);
    } else if (checkAmazonStatusLog.status === "retry") { //could not read the log file
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStart').css('display', 'none');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').css('display', 'none');
        var tempLog = window.modalRec['last_status_log_' + proId]
        if (tempLog) {
            $('#status-' + proId).html('<i class="fa fa-hourglass-half"></i> ' + tempLog + "<p>" + logAmzStart + logAmzCloudList + "</p>");
        } else {
            $('#status-' + proId).html('<i class="fa fa-hourglass-half"></i> ' + "<p>" + logAmzStart + logAmzCloudList + "</p>");
        }
        clearInterval(window['interval_amzStatus_' + proId]);
        checkAmazonTimer(proId, retryTimer);
        if (retryTimer <= 19000) {
            retryTimer += 1000;
        }
        var lastStat = window.modalRec['last_status_' + proId];
        if (lastStat === "running" || lastStat === "initiated") {
            $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').css('display', 'inline');
            $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').removeAttr('disabled');
        }


    } else if (checkAmazonStatusLog.status === "running") {
        window.modalRec['last_status_' + proId] = checkAmazonStatusLog.status;
        //check if run env. in run page is amazon and status is not running (then activate loadRunOptions()
        var chooseEnv = $('#chooseEnv').find(":selected").val();
        if (chooseEnv) {
            var status = $('#chooseEnv').find(":selected").attr("status");
            if (status) {
                if (chooseEnv === "amazon-" + proId && status !== "running") {
                    loadRunOptions("silent"); //used from runpipeline.js
                }
            }

        }
        if (checkAmazonStatusLog.sshText) {
            var sshText = "(" + checkAmazonStatusLog.sshText + ")";
        } else {
            var sshText = "";
        }
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStart').css('display', 'none');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').css('display', 'inline');
        $('#status-' + proId).html('Running <br/>' + sshText + "<p>" + logAmzStart + logAmzCloudList + "</p>");
        window.modalRec['last_status_log_' + proId] = 'Running <br/>' + sshText;
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').removeAttr('disabled');

    } else if (checkAmazonStatusLog.status === "inactive") {
        clearInterval(window['interval_amzStatus_' + proId]);
        $('#status-' + proId).text('Inactive');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').attr('disabled', 'disabled');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStart').css('display', 'inline');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').css('display', 'inline');
    } else if (checkAmazonStatusLog.status === "terminated") {
        stopAmz = false;
        clearInterval(window['interval_amzStatus_' + proId]);
        window.modalRec['last_status_log_' + proId] = "";
        window.modalRec['last_status_' + proId] = checkAmazonStatusLog.status;
        if (checkAmazonStatusLog.logAmzCloudList) {
            var logText = checkAmazonStatusLog.logAmzCloudList;
            if (logText.match(/INSTANCE ID ADDRESS STATUS ROLE(.*)/)) {
                var errorText = logText.match(/INSTANCE ID ADDRESS STATUS ROLE(.*)/)[1];
                if (errorText !== "") {
                    errorText = "(" + errorText + ")";
                }
            } else {
                errorText = "(" + logText + ")";
            }
        } else {
            var errorText = "";
        }
        if (errorText === "" && checkAmazonStatusLog.logAmzStart) {
            var logTextStart = checkAmazonStatusLog.logAmzStart;
            //WARN: One or more errors have been detected parsing EC2 prices
            if ((logTextStart.match(/error/i) && !logTextStart.match(/WARN: One or more errors/i)) || logTextStart.match(/denied/i) || logTextStart.match(/missing/i) || logTextStart.match(/couldn't/i) || logTextStart.match(/help/i) || logTextStart.match(/wrong/i))
                errorText = "(" + logTextStart + ")";
        }
        $('#status-' + proId).html('Terminated <br/>' + errorText );
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStart').css('display', 'inline');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').css('display', 'inline');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').attr('disabled', 'disabled');
    } else {
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').css('display', 'inline');
        $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').removeAttr('disabled');
    }
    $('[data-toggle="tooltip"]').tooltip();
    if (checkAmazonStatusLog.logAmzStart){
        $('#amzLogStart-'+proId).data("logData", checkAmazonStatusLog.logAmzStart)
    }
    if (checkAmazonStatusLog.logAmzCloudList){
        $('#AmzCloudListLog-'+proId).data("logData", checkAmazonStatusLog.logAmzCloudList)
    }

    // set autoshutdown counter
    var proAmzData = getValues({ p: "getProfileAmazon", id:proId });
    var autoshutdown_check = proAmzData[0].autoshutdown_check;
    var autoshutdown_active = proAmzData[0].autoshutdown_active;
    var autoshutdown_date = proAmzData[0].autoshutdown_date;
    var pro_status = proAmzData[0].status;

    if (autoshutdown_check == "true" && autoshutdown_active == "true" && autoshutdown_date && (pro_status == "running" || pro_status == "waiting" || pro_status == "initiated" || pro_status == "retry")){
        if (!window['countdown_' + proId]){
            console.log("countdown_"+proId+"setInterval")
            window['elapsed_' + proId]  = 0;
            window['countdown_' + proId] = setInterval(function () {
                window['elapsed_' + proId] ++;
                var remaining = autoshutdown_date-window['elapsed_' + proId];
                var autoshutdown_check = $('#autoshutdown-' + proId).is(":checked").toString();
                if (autoshutdown_check == "true"){
                    if (remaining < 1) {
                        clearInterval(window['countdown_' + proId]);
                        window['countdown_' + proId] = null;
                        window['elapsed_' + proId] = 0;
                        stopAmzByAjax(proId)
                        $('#shutdownTimer-' + proId).text("Terminated")
                    } else {
                        $('#shutdownTimer-' + proId).text(remaining+" sec.")
                    }
                } else {
                    $('#shutdownTimer-' + proId).text("");
                    clearInterval(window['countdown_' + proId]);
                    window['countdown_' + proId] = null;
                    window['elapsed_' + proId] = 0;
                }
            }, 1000); 
        }
    }

}




function stopAmzByAjax(proId){
    var data = { "id": proId, "p": "stopProAmazon" };
    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: data,
        async: true,
        success: function (s) {
            if (s.stop_cloud) {
                stopAmz = true;
                $('#status-' + proId).html('<i class="fa fa-hourglass-1"></i> Waiting for termination..');
                //clear previous interval and set new one(with faster check interval).
                clearInterval(window['interval_amzStatus_' + proId]);
                setTimeout(function () { checkAmazonTimer(proId, 5500); }, 1000);
            }
        }
    });
}

function updAmzActive(proId, proAmzData) {
    if (!proAmzData){
        proAmzData = getValues({ p: "getProfileAmazon", id:proId });
        if (proAmzData){
            proAmzData = proAmzData[0];
        }
    }
    var autoshutdown_check = proAmzData.autoshutdown_check;
    var autoshutdown_active = proAmzData.autoshutdown_active;
    var autoshutdown_date = proAmzData.autoshutdown_date;
    var pro_status = proAmzData.status;
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
    $('#shutdownLog-'+proId).text(activeTxt);
}


$(document).ready(function () {

    $(function () {
        $(document).on('change', '.autoShutCheck', function (event) {
            var autoShutCheck = $(this).is(":checked").toString();
            var amzProfileId = $(this).attr("id").split("-")[1];
            var proAmzData = getValuesErr({ p: "updateAmzShutdownCheck", autoshutdown_check:autoShutCheck, id: amzProfileId });
            if (autoShutCheck == "false"){
                $('#shutdownLog-'+amzProfileId).empty();
                $('#shutdownTimer-'+amzProfileId).empty();
            } else {
                updAmzActive(amzProfileId, "")
                checkAmazonStatus(amzProfileId);
            }
        });
        $(document).on('click', 'button[data=amzLogStart]', function (e) {
            e.preventDefault();
            var amzProfileId = $(this).attr("id").split("-")[1];
            var logData = $('#amzLogStart-'+amzProfileId).data("logData")
            if (logData.match(/Downloading nextflow dependencies(.*)Fetching EC2 prices/)){
                logData = logData.replace(/Downloading nextflow dependencies(.*)Fetching EC2 prices/,'')
            }
            logData = logData.replace(/\n/g, '<br/>');
            showInfoModal("#infoMod","#infoModText", logData)
        });
        $(document).on('click', 'button[data=AmzCloudListLog]', function (e) {
            e.preventDefault();
            var amzProfileId = $(this).attr("id").split("-")[1];
            var logData = $('#AmzCloudListLog-'+amzProfileId).data("logData")
            logData = logData.replace(/\n/g, '<br/>');
            showInfoModal("#infoMod","#infoModText", logData)
        });


    });
    function addAmzRow(id, name, executor, instance_type, image_id, subnet_id, autoshutdown_check, autoshutdown_active, autoshutdown_date, status, proAmzData) {
        if (autoshutdown_check == null){ autoshutdown_check = ""; }
        var checked = "";
        if (autoshutdown_check == "true"){
            checked = "checked";
        }
        var checkBox = '<input id="autoshutdown-' + id + '" class="autoShutCheck" type="checkbox"  name="autoshutdown_check" '+checked+'><p id="shutdownLog-' + id + '"></p><p id="shutdownTimer-' + id + '"></p>';

        $('#amzTable > tbody').append('<tr id="amazon-' + id + '"> <td>' + name + '</td><td>Instance_type: ' + instance_type + '<br>  Image id: ' + image_id + '<br>  Subnet Id: ' + subnet_id + '<br> Executor: ' + executor + '<br>  </td><td>'+checkBox+'</td><td id="status-' + id + '"><i class="fa fa-hourglass-half"></i></td><td>' + getButtonsDef('amz', 'Start') + getButtonsDef('amz', 'Stop') + '</td></tr>');
        updAmzActive(id, proAmzData)

    }



    $('#amzModal').on('show.bs.modal', function (event) {
        $(this).find('form').trigger('reset');
        var proAmzData = getValues({ p: "getProfileAmazon" });
        $('#amzTable > tbody').empty();
        $.each(proAmzData, function (el) {
            addAmzRow(proAmzData[el].id, proAmzData[el].name, proAmzData[el].executor, proAmzData[el].instance_type, proAmzData[el].image_id, proAmzData[el].subnet_id, proAmzData[el].autoshutdown_check, proAmzData[el].autoshutdown_active, proAmzData[el].autoshutdown_date, proAmzData[el].status, proAmzData[el]);
            checkAmazonStatus(proAmzData[el].id);
        });
    });

    //close amzModal
    $('#amzModal').on('hide.bs.modal', function (event) {
        $('#amzTable td ').remove();
        checkAmzProfiles("notimer");
    });

    $('#amzModal').on('click', '#amzStart', function (e) {
        e.preventDefault();
        var clickedRowId = $(this).closest('tr').attr('id'); //local-20
        var patt = /(.*)-(.*)/;
        var proId = clickedRowId.replace(patt, '$2');
        if (window['countdown_' + proId]){
            window['countdown_' + proId] = null;
        }
        //enter amazon details modal
        $('#addAmzNodeModal').off();
        $('#addAmzNodeModal').on('show.bs.modal', function (event) {
            $(this).find('form').trigger('reset');
            if ($('#autoshutdown-'+proId).is(":checked").toString() == "true"){
                $('#autoshut_check').prop('checked', true)
            }
        });
        //close addAmzNodeModal
        $('#addAmzNodeModal').on('hide.bs.modal', function (event) {
            $('#autoscaleDiv').attr('class', 'collapse');
        });
        $('#addAmzNodeModal').on('click', '#activateAmz', function (event) {
            event.preventDefault();
            var data = {};
            var numNodes = $('#numNodes').val();
            var autoshutdown_check = $('#autoshut_check').is(":checked").toString();


            $('#shutdownLog-'+proId).empty();
            $('#shutdownTimer-'+proId).empty();
            if (autoshutdown_check == "true"){
                $('#autoshutdown-'+proId).prop('checked', true);
                updAmzActive(proId, "")
            } else {
                $('#autoshutdown-'+proId).prop('checked', false);
            }
            var autoscale_check = $('#autoscale_check').is(":checked").toString();
            var autoscale_maxIns = $('#autoscale_maxIns').val();
            if (numNodes !== '') {
                data = {
                    "id": proId,
                    "nodes": numNodes,
                    "autoshutdown_check": autoshutdown_check,
                    "autoscale_check": autoscale_check,
                    "autoscale_maxIns": autoscale_maxIns,
                    "p": "startProAmazon"
                };
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: data,
                    async: true,
                    success: function (s) {
                        if (s.start_cloud) {
                            // check the amazon profiles activity each minute.
                            $('#status-' + proId).html('<i class="fa fa-hourglass-1"></i> Waiting for reply..');
                            $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStart').css('display', 'none');
                            $('#amzTable > tbody > #amazon-' + proId + ' > > #amzStop').attr('disabled', 'disabled');
                            $('#addAmzNodeModal').modal('hide');
                            clearInterval(window['interval_amzStatus_' + proId]);
                            checkAmazonTimer(proId, 20000);
                        }
                    }
                });
            }
        });
        $('#addAmzNodeModal').modal('show');
    });

    $('#amzModal').on('click', '#amzStop', function (e) {
        e.preventDefault();
        var clickedRowId = $(this).closest('tr').attr('id'); //local-20
        var patt = /(.*)-(.*)/;
        var proId = clickedRowId.replace(patt, '$2');
        var data = { "id": proId, "p": "stopProAmazon" };
        stopAmzByAjax(proId);
        if (window['countdown_' + proId]){
            clearInterval(window['countdown_' + proId]);
        }
        $('#shutdownTimer-' + proId).text("");
        //        window['countdown_' + proId] = null;
        window['elapsed_' + proId] = 0;
        $('#shutdownLog-'+proId).empty();
        $('#shutdownTimer-'+proId).empty();
    });
});

//load filter sidebar menu options
if (usRole === "admin") {
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
            if (options[i] === '3' || options[i] === '63' || options[i] === '630') {
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
                if ($(tagElems2).eq(j).attr('pin')) {
                    if ($(tagElems2).eq(j).attr('pin') === 'true') {
                        var checkPinText = '63';
                    } else {
                        var checkPinText = '630';
                    }
                }
                if ($(tagElems2).eq(j).attr('pub')) {
                    if ($(tagElems2).eq(j).attr('pub') === '1') {
                        var checkPubText = '630';
                    } else {
                        var checkPubText = '0';
                    }
                }
                var checkPublish = $.inArray(checkPubText, selOptArr) >= 0;
                var checkPin = $.inArray(checkPinText, selOptArr) >= 0;
                var checkPerm = $.inArray($(tagElems2).eq(j).attr('p'), selOptArr) >= 0;
                var checkGroup = $.inArray($(tagElems2).eq(j).attr('g'), group_idArr) >= 0;

                if (($(tagElems2).eq(j).attr('p') === "15" && checkPerm && checkGroup) || ($(tagElems2).eq(j).attr('p') === "3" && checkPerm) || ($(tagElems2).eq(j).attr('p') === "63" && checkPin) || ($(tagElems2).eq(j).attr('p') !== "63" && checkPublish)) {
                    $(tagElems).eq(i).show()
                    if (selOpt !== "") {} else {
                        $(tagElems).show()
                    }
                    $(tagElems2).eq(j).show()
                }
            }
        }
    } else {
        //if nothing is selected show everything
        $(tagElems).show()
        for (var i = 0; i < tagElems.length; i++) {
            var tagElems2 = $(tagElems).eq(i).children().eq(1).children()
            $(tagElems2).show()
        }
    }
    $('#inputs').show();
    $('#outputs').show();
    $('.header').show();
    $('#Pipelines').show();
}

//SideBar menu Search Function
//$('#tags').on('keyup',function(e){
$('.main-sidebar').on('keyup', '#tags', function (e) {
    $('.filterM a >').prop('checked', false);
    var tagElems = $('#autocompletes1').children()
    $(tagElems).hide()
    for (var i = 0; i < tagElems.length; i++) {
        var tagElems2 = $(tagElems).eq(i).children().eq(1).children()

        $(tagElems2).hide()
        $(tagElems).eq(i).closest('li').children('ul.treeview-menu').hide()
        for (var j = 0; j < tagElems2.length; j++) {
            if (($(tagElems2).eq(j).text().toLowerCase()).indexOf($(this).val().toLowerCase()) > -1) {
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
    var button = '<button type="submit" style="padding:0px;" class="btn" title="' + buttons + '" id="' + name + buttonId + '" data-toggle="modal" data-target="#' + name + 'modal"><a data-toggle="tooltip" data-placement="bottom" data-original-title="' + name + '"><i class="' + icon + '" style="font-size: 17px;"></i></a></button>';
    return button;
}

function getIconButton(name, buttons, icon) {
    var buttonId = buttons.split(' ')[0];
    var button = '<button type="submit" style="padding:0px;" class="btn" title="' + buttons + '" id="' + name + buttonId + '"><a data-toggle="tooltip" data-placement="bottom" data-original-title="' + name + '"><i class="' + icon + '" style="font-size: 17px;"></i></a></button>';
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
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
            console.log("#Ajax Error: ");
            console.log(msg);
        }
    });
    return result;
}

function getValuesErr(data, async) {
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
            alert("#Ajax Error: "+msg);
        }
    });
    return result;
}


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
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
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
        var val = $(formValues[i]).val();
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
        var keys = Object.keys(data);
        if (data[nameAttr]) {
            if (data[nameAttr] === "on") {
                $(formValues[k]).attr('checked', true);
            } else {
                $(formValues[k]).val(data[nameAttr]);
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
        return null
    } else if (str === undefined) {
        return ""
    }
    return str.replace(/&amp;|&lt;|&gt;|&quot;|&#039;/g, function (m) { return map[m]; });
}
