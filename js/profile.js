function generateKeys() {
    var genKeys = getValues({ p: "generateKeys" });
    if (genKeys) {
        if (genKeys.create_key_status === "active") {
            setTimeout(function () { readGenerateKeys() }, 500);
        } else {
            $('#mOurPriKey').val("");
            $('#mOurPubKey').val("");
            $('#mOurPriKeyDiv').css('display', 'none');
            $('#mOurPubKeyDiv').css('display', 'none');
        }
    }

}

function readGenerateKeys() {
    var genKeysLog = getValues({ p: "readGenerateKeys" });
    if (genKeysLog) {
        if (genKeysLog.$keyPri !== "" && genKeysLog.$keyPub !== "" && genKeysLog.$keyPri !== false && genKeysLog.$keyPub !== false) {
            $('#mOurPriKey').val($.trim(genKeysLog.$keyPri));
            $('#mOurPubKey').val($.trim(genKeysLog.$keyPub));
            $('#mOurPriKeyDiv').css('display', 'inline');
            $('#mOurPubKeyDiv').css('display', 'inline');
        } else {
            $('#mOurPriKey').val("");
            $('#mOurPubKey').val("");
            $('#mOurPriKeyDiv').css('display', 'none');
            $('#mOurPubKeyDiv').css('display', 'none');
        }
    }

}


$(document).ready(function () {
    //get profiles for user
    var proCluData = getValues({ p: "getProfileCluster" });
    var proAmzData = getValues({ p: "getProfileAmazon" });
    if (proCluData.length + proAmzData.length !== 0) {
        $('#noProfile').css('display', 'none');
        $.each(proCluData, function (el) {
            addClusterRow(proCluData[el].id, proCluData[el].name, proCluData[el].next_path, proCluData[el].executor, proCluData[el].username, proCluData[el].hostname);
        });
        $.each(proAmzData, function (el) {
            addAmazonRow(proAmzData[el].id, proAmzData[el].name, proAmzData[el].next_path, proAmzData[el].executor, proAmzData[el].instance_type, proAmzData[el].image_id);
        });
    }
    //adminTab
    if (usRole === "admin") {
        $('#adminTabBut').css('display', 'inline');
    }

    function getProfileButton(type) {
        if (type === "amazon") {
            var button = '<div style="display: inline-flex"><button type="button" class="btn btn-primary btn-sm" title="Edit" id="profileedit" data-toggle="modal" data-target="#profilemodal">Edit</button> &nbsp; <button type="button" class="btn btn-primary btn-sm" title="Remove" id="profileremove" data-toggle="modal" data-target="#confirmDelProModal">Remove</button>&nbsp;<button type="button" class="btn btn-primary btn-sm" title="start/stop" id="amzStartStop" data-toggle="modal" data-target="#amzModal">Start/Stop</button> &nbsp;</div>';
        } else if (type === "cluster" || type === "local") {
            var button = '<div style="display: inline-flex"><button type="button" class="btn btn-primary btn-sm" title="Edit" id="profileedit" data-toggle="modal" data-target="#profilemodal">Edit</button> &nbsp; <button type="button" class="btn btn-primary btn-sm" title="Remove" id="profileremove" data-toggle="modal" data-target="#confirmDelProModal">Remove</button>';
        }
        return button;
    }

    function addLocalRow(id, name, next_path, executor) {
        $('#profilesTable > thead').append('<tr id="local-' + id + '"> <td>' + name + '</td> <td>Local</td><td>Nextflow Path: ' + next_path + '<br> Executor: ' + executor + '</td><td>' + getProfileButton('local') + '</td></tr>');
    }

    function addClusterRow(id, name, next_path, executor, username, hostname) {
        $('#profilesTable > thead').append('<tr id="cluster-' + id + '"> <td>' + name + '</td> <td>Host</td><td>Nextflow Path: ' + next_path + '<br> Executor: ' + executor + '<br>  Connection: ' + username + '@' + hostname + '</td><td>' + getProfileButton('cluster') + '</td></tr>');
    }

    function addAmazonRow(id, name, next_path, executor, instance_type, image_id) {
        $('#profilesTable > thead').append('<tr id="amazon-' + id + '"> <td>' + name + '</td> <td>Amazon</td><td>Nextflow Path: ' + next_path + '<br> Executor: ' + executor + '<br>  Instance_type: ' + instance_type + '<br>  Image_id: ' + image_id + '</td><td>' + getProfileButton('amazon') + '</td></tr>');
    }

    function updateLocalRow(id, name, next_path, executor) {
        $('#profilesTable > thead > #local-' + id).html('<td>' + name + '</td> <td>Local</td><td>Nextflow Path: ' + next_path + '<br> Executor: ' + executor + '</td><td>' + getProfileButton('local') + '</td>');
    }

    function updateClusterRow(id, name, next_path, executor, username, hostname) {
        $('#profilesTable > thead > #cluster-' + id).html('<td>' + name + '</td> <td>Host</td><td>Nextflow Path: ' + next_path + '<br> Executor: ' + executor + '<br>  Connection: ' + username + '@' + hostname + '</td><td>' + getProfileButton('cluster') + '</td>');
    }

    function updateAmazonRow(id, name, next_path, executor, instance_type, image_id) {
        $('#profilesTable > thead > #amazon-' + id).html('<td>' + name + '</td> <td>Amazon</td><td>Nextflow Path: ' + next_path + '<br> Executor: ' + executor + '<br>  Instance_type: ' + instance_type + '<br>  Image_id: ' + image_id + '</td><td>' + getProfileButton('amazon') + '</td>');
    }

    function loadOptions(type) {
        if (type === "ssh") {
            var data = getValues({ p: "getSSH" });
            for (var i = 0; i < data.length; i++) {
                var param = data[i];
                var optionGroup = new Option(param.name, param.id);
                $("#mEnvSSHKey").append(optionGroup);
            }
        } else if (type === "amz") {
            var data = getValues({ p: "getAmz" });
            for (var i = 0; i < data.length; i++) {
                var param = data[i];
                var optionGroup = new Option(param.name, param.id);
                $("#mEnvAmzKey").append(optionGroup);
            }
        }
    }

    $('#profilemodal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        if (button.attr('id') === 'addEnv') {
            $('#mAddEnvTitle').html('Add Environment');
            loadOptions("ssh");
            loadOptions("amz");
        } else if (button.attr('id') === 'profileedit') {
            $('#mAddEnvTitle').html('Edit Environment');
            loadOptions("ssh");
            loadOptions("amz");
            var clickedRowId = button.closest('tr').attr('id'); //local-20
            var patt = /(.*)-(.*)/;
            var proType = clickedRowId.replace(patt, '$1');
            var proId = clickedRowId.replace(patt, '$2');
            var formValues = $('#profilemodal').find('input, select, textarea');

            function fillFixedCol(formValues, data) {
                $(formValues[0]).val(data[0].id);
                $(formValues[1]).val(data[0].name);
                $(formValues[12]).val(data[0].cmd);
                $(formValues[13]).val(data[0].next_path);
                $(formValues[14]).val(data[0].executor);
                $(formValues[15]).val(data[0].next_queue);
                $(formValues[16]).val(data[0].next_memory);
                $(formValues[17]).val(data[0].next_cpu);
                $(formValues[18]).val(data[0].next_time);
                $(formValues[19]).val(data[0].next_clu_opt);
                $(formValues[20]).val(data[0].executor_job);
                $(formValues[21]).val(data[0].job_queue);
                $(formValues[22]).val(data[0].job_memory);
                $(formValues[23]).val(data[0].job_cpu);
                $(formValues[24]).val(data[0].job_time);
                $(formValues[25]).val(data[0].job_clu_opt);
            };
            if (proType === "cluster") {
                var data = getValues({ p: "getProfileCluster", id: proId });
                $('#chooseEnv').val('cluster').trigger('change');
                fillFixedCol(formValues, data);
                $(formValues[3]).val(data[0].username);
                $(formValues[4]).val(data[0].hostname);
                $(formValues[5]).val(data[0].ssh_id);
                $('#mExec').trigger('change');
            } else if (proType === "amazon") {
                var data = getValues({ p: "getProfileAmazon", id: proId });
                $('#chooseEnv').val('amazon').trigger('change');
                fillFixedCol(formValues, data);
                $(formValues[5]).val(data[0].ssh_id);
                $(formValues[6]).val(data[0].amazon_cre_id);
                $(formValues[7]).val(data[0].instance_type);
                $(formValues[8]).val(data[0].image_id);
                $(formValues[9]).val(data[0].subnet_id);
                $(formValues[10]).val(data[0].shared_storage_id);
                $(formValues[11]).val(data[0].shared_storage_mnt);
                $('#mExec').trigger('change');
            }
            $('#chooseEnv').attr('disabled', "disabled");
        }
    });

//id='#execJobSetTable' or '#execNextSettTable'
function showHideColumnProfile(id, colList, type) {
    for (var k = 0; k < colList.length; k++) {
        if (type == "hide"){
            $(id).find('th:nth-child('+colList[k]+')').hide();
            $(id).find('td:nth-child('+colList[k]+')').hide();
        } else {
            $(id).find('th:nth-child('+colList[k]+')').show();
            $(id).find('td:nth-child('+colList[k]+')').show();
        }
    }
}

    $(function () {
        $(document).on('change', '#chooseEnv', function () {
            var selEnvType = $('#chooseEnv option:selected').val();
            var noneList = [];
            var blockList = [];
            if (selEnvType === "local") {
                var noneList = ["mEnvUsernameDiv", "mEnvHostnameDiv", "mEnvSSHKeyDiv", "mEnvAmzKeyDiv", "mEnvInsTypeDiv", "mEnvImageIdDiv", "execJobSetDiv", "mSubnetIdDiv", "mSharedStorageIdDiv", "mSharedStorageMountDiv"];
                var blockList = ["mExecDiv", "mEnvNextPathDiv", "mEnvCmdDiv", "execNextDiv", "mExecJobDiv"];
            } else if (selEnvType === "cluster") {
                var noneList = ["mEnvAmzDefRegDiv", "mEnvAmzAccKeyDiv", "mEnvAmzSucKeyDiv", "mEnvInsTypeDiv", "mEnvImageIdDiv", "mPriKeyAmzDiv", "mPubKeyDiv", "execJobSetDiv", "mSubnetIdDiv", "mSharedStorageIdDiv", "mSharedStorageMountDiv", "mEnvAmzKeyDiv"];
                var blockList = ["mExecDiv", "mEnvUsernameDiv", "mEnvHostnameDiv", "mEnvSSHKeyDiv", "mEnvNextPathDiv", "mEnvCmdDiv", "execNextDiv", "mExecJobDiv"];
            } else if (selEnvType === "amazon") {
                var noneList = ["mEnvUsernameDiv", "mEnvHostnameDiv", "mPriKeyCluDiv"];
                var blockList = ["mExecDiv", "mEnvSSHKeyDiv", "mEnvAmzKeyDiv", "mEnvInsTypeDiv", "mEnvImageIdDiv", "mEnvNextPathDiv", "mEnvCmdDiv", "execNextDiv", "mExecJobDiv", "execJobSetDiv", "mSubnetIdDiv", "mSharedStorageIdDiv", "mSharedStorageMountDiv"];
            }
            $.each(noneList, function (element) {
                $('#' + noneList[element]).css('display', 'none');
            });
            $.each(blockList, function (element) {
                $('#' + blockList[element]).css('display', 'block');
            });
            $('#mExec').trigger('change');

        })
    });
    $(function () {
        $(document).on('change', '#mExec', function () {
            var mExecType = $('#mExec option:selected').val();
            $('#mExecJob').removeAttr('disabled');
            if (mExecType === "local") {
                $('#mExecJob').trigger('change');
                $('#execNextDiv').css('display', 'block');
                $('#mExecJobDiv').css('display', 'block');
                showHideColumnProfile('#execNextSettTable',[1,4,5],'hide');
            } else if (mExecType === "sge" || mExecType === "lsf" || mExecType === "slurm") {
                showHideColumnProfile('#execNextSettTable',[1,4,5],'show');
                $('#mExecJob').val(mExecType).trigger('change');
                $('#mExecJob').attr('disabled', "disabled");
                $('#execNextDiv').css('display', 'block');
                $('#mExecJobDiv').css('display', 'block');
            }
        })
    });

    $(function () {
        $(document).on('change', '#mExecJob', function () {
            var mExecJobType = $('#mExecJob option:selected').val();
                if (mExecJobType === "ignite"){
                    showHideColumnProfile('#execJobSetTable',[1,4,5],'show');
                    showHideColumnProfile('#execJobSetTable',[1,4],'hide');
                } else if (mExecJobType === "local"){
                    showHideColumnProfile('#execJobSetTable',[1,4,5],'hide');
                } else {
                    showHideColumnProfile('#execJobSetTable',[1,4,5],'show');
                }
                $('#execJobSetDiv').css('display', 'block');
        })
    });

    // Dismiss parameters modal 
    $('#profilemodal').on('hide.bs.modal', function (event) {
        var noneList = ["mEnvUsernameDiv", "mEnvHostnameDiv", "mEnvSSHKeyDiv", "mEnvAmzKeyDiv", "mEnvInsTypeDiv", "mEnvImageIdDiv", "mExecDiv", "mEnvNextPathDiv", "mEnvCmdDiv", "execNextDiv", "mExecJobDiv", "execJobSetDiv", "mSubnetIdDiv", "mSharedStorageIdDiv", "mSharedStorageMountDiv"];
        $.each(noneList, function (element) {
            $('#' + noneList[element]).css('display', 'none');
        });
        $('#chooseEnv').removeAttr('disabled');
        $('#mExecJob').removeAttr('disabled');
        $('#mEnvAmzKey').find('option').not(':eq(0)').remove()
        $('#mEnvSSHKey').find('option').not(':eq(0)').remove()
    });

    $('#profilemodal').on('click', '#saveEnv', function (event) {
        event.preventDefault();
        $('#chooseEnv').removeAttr('disabled');
        $('#mExecJob').removeAttr('disabled');
        var formValues = $('#profilemodal').find('input, select, textarea');
        var savetype = $('#mEnvId').val();
        var profileName = $('#mEnvName').val();
        var data = formValues.serializeArray(); // convert form to array
        var selEnvType = $('#chooseEnv option:selected').val();
        if (selEnvType === "cluster" && (data[19].value =="ignite" || data[19].value =="local")){
            data[20].value =""; //queue
            data[23].value =""; //time
        }
        if (selEnvType === "amazon" && (data[20].value =="ignite" || data[20].value =="local")){
            data[21].value = "";//queue
            data[24].value = "";//time
        }
        if (selEnvType === "cluster" && data[19].value =="local"){
            data[24].value =""; //"job_clu_opt"
        }
        if (selEnvType === "amazon" && data[20].value =="local"){
            data[25].value = "";//"job_clu_opt"
        }
        if (selEnvType === "cluster" && data[13].value =="local"){
            data[14].value = "";//queue
            data[17].value = "";//time
            data[18].value = "";//next_clu_opt
        }
        if (selEnvType === "amazon" && data[14].value =="local"){
            data[15].value = "";//queue
            data[18].value = "";//time
            data[19].value = "";//next_clu_opt
        }
            
        if (selEnvType.length && profileName !== '') {
            if (selEnvType === "cluster") {
                var sshID = $('#mEnvSSHKey').val();
                if (sshID) {
                    data.push({ name: "p", value: "saveProfileCluster" });
                } else {
                    data = [];
                }
            } else if (selEnvType === "amazon") {
                var sshID = $('#mEnvSSHKey').val();
                var amzID = $('#mEnvAmzKey').val();
                if (sshID && amzID) {
                    data.push({ name: "p", value: "saveProfileAmazon" });
                } else {
                    data = [];
                }
            }
            if (data != '') {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: data,
                    async: true,
                    success: function (s) {
                        if (savetype.length) { //edit
                            var clickedRowId = selEnvType + '-' + savetype;
                            if (selEnvType === "local") {
                                updateLocalRow(data[0].value, data[1].value, data[12].value, data[13].value)
                            } else if (selEnvType === "cluster") {
                                updateClusterRow(data[0].value, data[1].value, data[12].value, data[13].value, data[3].value, data[4].value)
                            } else if (selEnvType === "amazon") {
                                updateAmazonRow(data[0].value, data[1].value, data[12].value, data[13].value, data[6].value, data[7].value);
                            }
                        } else { //insert
                            if (selEnvType === "local") {
                                addLocalRow(s.id, data[1].value, data[12].value, data[13].value);
                            } else if (selEnvType === "cluster") {
                                addClusterRow(s.id, data[1].value, data[12].value, data[13].value, data[3].value, data[4].value);
                            } else if (selEnvType === "amazon") {
                                addAmazonRow(s.id, data[1].value, data[12].value, data[13].value, data[6].value, data[7].value);
                                $('#manageAmz').css('display', 'inline');
                                checkAmazonTimer(s.id, 40000);
                            }
                            var numRows = $('#profilesTable > > tr').length;
                            if (numRows > 2) {
                                $('#noProfile').css('display', 'none');
                            }
                        }
                        $('#profilemodal').modal('hide');
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        }
    });


    // confirm Delete ssh modal 
    $('#confirmDelProModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var clickedRowId = button.closest('tr').attr('id'); //local-20
        if (button.attr('id') === 'profileremove') {
            $('#mDelProBtn').attr('clickedRowId', clickedRowId);
            $('#mDelProBtn').attr('class', 'btn btn-primary deleteProfile');
            $('#confirmDelProModalText').html('Are you sure you want to delete?');
        }
    });

    $('#confirmDelProModal').on('click', '.deleteProfile', function (event) {
        var clickedRowId = $('#mDelProBtn').attr('clickedRowId');
        var patt = /(.*)-(.*)/;
        var proType = clickedRowId.replace(patt, '$1');
        var proId = clickedRowId.replace(patt, '$2');
        var data = {};
        if (proType === "local") {
            data = { "id": proId, "p": "removeProLocal" };
        } else if (proType === "cluster") {
            data = { "id": proId, "p": "removeProCluster" };
        } else if (proType === "amazon") {
            data = { "id": proId, "p": "removeProAmazon" };
        }
        if (clickedRowId !== '') {
            var warnUser = false;
            var warnText = '';
            //[warnUser, warnText] = checkDeletionProfile(remove_id);

            //A. If it is allowed to delete    
            if (warnUser === false) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: data,
                    async: true,
                    success: function (s) {
                        $('#profilesTable > > #' + clickedRowId).remove();
                        var numRows = $('#profilesTable > > tr').length;
                        if (numRows === 2) {
                            $('#noProfile').css('display', 'block');
                        }
                        // check the amazon profiles
                        if (proType === "amazon") {
                            clearInterval(window['interval_amzStatus_' + proId]);
                            var proAmzData = getValues({ p: "getProfileAmazon" });
                            if (proAmzData.length < 1) {
                                $('#manageAmz').css('display', 'none');
                            }
                        }
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
            //B. If it is not allowed to delete
            else if (warnUser === true) {
                $('#warnDelete').off();
                $('#warnDelete').on('show.bs.modal', function (event) {
                    $('#warnDelText').html(warnText);
                });
                $('#warnDelete').modal('show');
            }
            $('#confirmDelProModal').modal('hide');
        }
    });








    //------------   groups section-------------
    function getGroupTableOptions(owner_id, u_id) {
        if (owner_id === u_id) {
            //if user is the owner of the group
            var button = '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button><ul class="dropdown-menu" role="menu"><li><a href="#joinmodal" data-toggle="modal" class="viewGroupMembers">View Group Members</a></li><li class="divider"></li><li><a href="#joinmodal" data-toggle="modal" class="addUsers">Add Users</a></li><li class="divider"></li><li><a href="#" class="deleteGroup">Delete Group</a></li></ul></div>';
        } else {
            var button = '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button><ul class="dropdown-menu" role="menu"><li><a href="#joinmodal" data-toggle="modal" class="viewGroupMembers">View Group Members</a></li></ul></div>';
        }
        return button;
    }

    var groupTable = $('#grouptable').DataTable({
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getUserGroups" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "name"
            }, {
            "data": "username"
            }, {
            "data": "date_created"
            }, {
            data: null,
            className: "center",
            fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(getGroupTableOptions(oData.owner_id, oData.u_id));
            }
            }]
    });




    $('#groupmodal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        if (button.attr('id') === 'addgroup') {
            $('#groupmodaltitle').html('Create a New Group');
        } else {
            $('#groupmodaltitle').html('Edit Group Name');
            var clickedRow = button.closest('tr');
            var rowData = groupTable.row(clickedRow).data();
            $('#savegroup').data('clickedrow', clickedRow);
            var formValues = $('#groupmodal').find('input');
            $(formValues[0]).val(rowData.id);
            $(formValues[1]).val(rowData.name);

        }
    });

    $('#groupmodal').on('click', '#savegroup', function (event) {
        event.preventDefault();
        var formValues = $('#groupmodal').find('input');
        if ($('#mProjectName').val() !== '') {
            var savetype = $('#mGroupID').val();
            var data = formValues.serializeArray(); // convert form to array
            data.push({ name: "p", value: "saveGroup" });
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: data,
                async: true,
                success: function (s) {
                    if (savetype.length) { //edit
                        //                        var clickedRow = $('#savegroup').data('clickedrow');
                        //                        var getGroupData = [];
                        //                        getGroupData.push({ name: "id", value: savetype });
                        //                        getGroupData.push({ name: "p", value: 'getGroups' });
                        //                        $.ajax({
                        //                            type: "POST",
                        //                            url: "ajax/ajaxquery.php",
                        //                            data: getGroupData,
                        //                            async: true,
                        //                            success: function (sc) {
                        //                                var groupDat = sc;
                        //                                var rowData = {};
                        //                                var keys = groupTable.settings().init().columns;
                        //                                for (var i = 0; i < keys.length; i++) {
                        //                                    var key = keys[i].data;
                        //                                    rowData[key] = groupDat[0][key];
                        //                                }
                        //                                rowData.id = groupDat[0].id;
                        //                                groupTable.row(clickedRow).remove().draw();
                        //                                groupTable.row.add(rowData).draw();
                        //
                        //                            },
                        //                            error: function (errorThrown) {
                        //                                alert("Error: " + errorThrown);
                        //                            }
                        //                        });

                    } else { //insert
                        var getGroupData = [];
                        getGroupData.push({ name: "id", value: s.id });
                        getGroupData.push({ name: "p", value: 'getGroups' });
                        $.ajax({
                            type: "POST",
                            url: "ajax/ajaxquery.php",
                            data: getGroupData,
                            async: true,
                            success: function (sc) {
                                var groupDat = sc;
                                var addData = {};
                                var keys = groupTable.settings().init().columns;
                                for (var i = 0; i < keys.length; i++) {
                                    var key = keys[i].data;
                                    addData[key] = groupDat[0][key];
                                }
                                addData.id = groupDat[0].id;
                                groupTable.row.add(addData).draw();

                            },
                            error: function (errorThrown) {
                                alert("Error: " + errorThrown);
                            }
                        });
                    }

                    $('#groupmodal').modal('hide');

                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });



    $('#joinmodal').on('show.bs.modal', function (event) {
        $('#confirmGroupButton').css('display', 'inline');
        var button = $(event.relatedTarget);
        $(this).find('option').remove();
        if (button.attr('class') === 'viewGroupMembers') {
            $('#joinmodallabel').html('View Group Members');
            $('#groupLabel').html('Group Members');
            $('#confirmGroupButton').css('display', 'none');
            $('#cancelGroupButton').html('OK');
            var clickedRow = button.closest('tr');
            var rowData = groupTable.row(clickedRow).data();
            $.ajax({
                type: "GET",
                url: "ajax/ajaxquery.php",
                data: {
                    g_id: rowData.id,
                    p: "viewGroupMembers"
                },
                async: false,
                success: function (s) {
                    for (var i = 0; i < s.length; i++) {
                        var param = s[i];
                        var optionGroup = new Option(param.username, param.id);
                        $("#mGroupList").append(optionGroup);
                    }
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });

        } else if (button.attr('class') === 'addUsers') {
            $('#joinmodallabel').html('List of All Users');
            $('#groupLabel').html('Select a user to add to this group');
            $('#confirmGroupButton').html('Add to group');
            $('#cancelGroupButton').html('Cancel');
            var clickedRow = button.closest('tr');
            var rowData = groupTable.row(clickedRow).data();
            $('#joinmodallabel').attr('clickedrow', rowData.id);

            $.ajax({
                type: "GET",
                url: "ajax/ajaxquery.php",
                data: {
                    g_id: rowData.id,
                    p: "getMemberAdd"
                },
                async: false,
                success: function (s) {
                    for (var i = 0; i < s.length; i++) {
                        var param = s[i];
                        var optionGroup = new Option(param.username, param.id);
                        $("#mGroupList").append(optionGroup);
                    }
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });

        }
    });

    $('#joinmodal').on('click', '#confirmGroupButton', function (event) {
        event.preventDefault();
        var label = $('#joinmodallabel').html();
        if (label === 'Join a Group') {
            var selGroup = $('#mGroupList').val();
            if (selGroup !== '') {
                var joinGro = getValues({ p: "saveUserGroup", g_id: selGroup });
                if (joinGro) {
                    var getGroupData = [];
                    getGroupData.push({ name: "id", value: selGroup });
                    getGroupData.push({ name: "p", value: 'getGroups' });
                    $.ajax({
                        type: "POST",
                        url: "ajax/ajaxquery.php",
                        data: getGroupData,
                        async: true,
                        success: function (sc) {
                            var groupDat = sc;
                            var addData = {};
                            var keys = groupTable.settings().init().columns;
                            for (var i = 0; i < keys.length; i++) {
                                var key = keys[i].data;
                                addData[key] = groupDat[0][key];
                            }
                            addData.id = groupDat[0].id;
                            groupTable.row.add(addData).draw();
                            $('#joinmodal').modal('hide');


                        },
                        error: function (errorThrown) {
                            alert("Error: " + errorThrown);
                        }
                    });

                }
            }
        } else if (label === 'List of All Users') {
            var clickedrow = $('#joinmodallabel').attr('clickedrow');
            var selGroup = $('#mGroupList').val();
            if (selGroup !== '') {
                var joinGro = getValues({ p: "saveUserGroup", u_id: selGroup, g_id: clickedrow });
                if (joinGro) {
                    $('#joinmodal').modal('hide');
                }
            }
        }

    });

    $('#grouptable').on('click', '.deleteGroup', function (e) {
        e.preventDefault();
        var clickedRow = $(this).closest('tr');
        var rowData = groupTable.row(clickedRow).data();
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: {
                id: rowData.id,
                p: "removeGroup"
            },
            async: true,
            success: function (s) {
                groupTable.row(clickedRow).remove().draw();
            },
            error: function (errorThrown) {
                alert("Error: " + errorThrown);
            }
        });
    });
    //--------------- groups section ends------------------

    //------------   ssh keys section-------------
    //not allow to click both option
    $('#userKeyDiv').on('show.bs.collapse', function () {
        if ($('#ourKeyCheck').is(":checked") && $('#userKeyCheck').is(":checked")) {
            $('#ourKeyCheck').trigger("click");
        }
        $('#userKeyCheck').attr('onclick', "return false;");
    });
    $('#ourKeyDiv').on('show.bs.collapse', function () {
        if ($('#ourKeyCheck').is(":checked") && $('#userKeyCheck').is(":checked")) {
            $('#userKeyCheck').trigger("click");
        }
        $('#ourKeyCheck').attr('onclick', "return false;");
    });
    $('#userKeyDiv').on('shown.bs.collapse', function () {
        if ($('#ourKeyCheck').is(":checked") && $('#userKeyCheck').is(":checked")) {
            $('#ourKeyCheck').trigger("click");
        }
        $('#userKeyCheck').removeAttr('onclick');
    });
    $('#ourKeyDiv').on('shown.bs.collapse', function () {
        if ($('#ourKeyCheck').is(":checked") && $('#userKeyCheck').is(":checked")) {
            $('#userKeyCheck').trigger("click");
        }
        $('#ourKeyCheck').removeAttr('onclick');
    });
    $('#ourKeyDiv').on('hide.bs.collapse', function () {
        $('#ourKeyCheck').attr('onclick', "return false;");
    });
    $('#userKeyDiv').on('hide.bs.collapse', function () {
        $('#userKeyCheck').attr('onclick', "return false;");
    });
    $('#userKeyDiv').on('hidden.bs.collapse', function () {
        $('#userKeyCheck').removeAttr('onclick');
    });
    $('#ourKeyDiv').on('hidden.bs.collapse', function () {
        $('#ourKeyCheck').removeAttr('onclick');
    });

    function getSSHTableOptions() {
        var button = '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button><ul class="dropdown-menu" role="menu"><li><a href="#sshKeyModal" data-toggle="modal" class="editSSHKeys">Edit</a></li><li><a href="#confirmDelModal" data-toggle="modal" class="deleteSSHKeys">Delete</a></li></ul></div>';
        return button;
    }

    var sshTable = $('#sshKeyTable').DataTable({
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getSSH" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "name"
            }, {
            "data": "date_modified"
            }, {
            data: null,
            className: "center",
            fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(getSSHTableOptions());
            }
            }]
    });

    // confirm Delete ssh modal 
    $('#confirmDelModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var clickedRow = button.closest('tr');
        var rowData = sshTable.row(clickedRow).data();
        var remove_id = rowData.id;
        if (button.attr('class') === 'deleteSSHKeys') {
            $('#mDelBtn').data('clickedrow', clickedRow);
            $('#mDelBtn').attr('remove_id', remove_id);
            $('#mDelBtn').attr('class', 'btn btn-primary deleteSSHKeys');
            $('#confirmDelModalText').html('Are you sure you want to delete?');
        }
    });

    $('#confirmDelModal').on('click', '.deleteSSHKeys', function (event) {
        var remove_id = $('#mDelBtn').attr('remove_id');
        var clickedRow = $('#mDelBtn').data('clickedrow');

        if (remove_id !== '') {
            var warnUser = false;
            var warnText = '';
            //[warnUser, warnText] = checkDeletionSSH(remove_id);

            //A. If it is allowed to delete    
            if (warnUser === false) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: {
                        id: remove_id,
                        p: "removeSSH"
                    },
                    async: true,
                    success: function (s) {
                        sshTable.row(clickedRow).remove().draw();
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
            //B. If it is not allowed to delete
            else if (warnUser === true) {
                $('#warnDelete').off();
                $('#warnDelete').on('show.bs.modal', function (event) {
                    $('#warnDelText').html(warnText);
                });
                $('#warnDelete').modal('show');
            }
            $('#confirmDelModal').modal('hide');
        }
    });

    $('#sshKeyModal').on('hide.bs.modal', function (event) {
        $('#ourKeyCheck').prop('disabled', false);
        $('#userKeyCheck').prop('disabled', false);
        if ($('#userKeyCheck').is(":checked")) {
            $('#userKeyCheck').trigger("click");
        }
        if ($('#ourKeyCheck').is(":checked")) {
            $('#ourKeyCheck').trigger("click");
        }
    });



    $('#sshKeyModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        if (button.attr('id') === 'addSSHKey') {
            $('#sshkeysmodaltitle').html('Add SSH Keys');
        } else {
            $('#sshkeysmodaltitle').html('Edit SSH Keys');
            var clickedRow = button.closest('tr');
            var rowData = sshTable.row(clickedRow).data();
            $('#savesshkey').data('clickedrow', clickedRow);
            var formValues = $('#sshKeyModal').find('input');
            var data = getValues({ p: "getSSH", id: rowData.id })[0];
            $(formValues[0]).val(rowData.id);
            $(formValues[1]).val(rowData.name);
            if (data.check_userkey === 'on') {
                $('#userKeyCheck').trigger('click');
                $('#mUserPriKey').val(data.prikey)
                $('#mUserPubKey').val(data.pubkey)
                $('#ourKeyCheck').prop('disabled', true);
                $('#userKeyCheck').prop('disabled', true);

            } else if (data.check_ourkey === 'on') {
                $('#ourKeyCheck').trigger('click');
                $('#mOurPriKeyDiv').css('display', 'inline');
                $('#mOurPubKeyDiv').css('display', 'inline');
                $('#createKeysDiv').css('display', 'none');
                $('#mOurPriKey').val(data.prikey)
                $('#mOurPubKey').val(data.pubkey)
                $('#userKeyCheck').prop('disabled', true);
                $('#ourKeyCheck').prop('disabled', true);
            }

        }
    });

    $('#sshKeyModal').on('click', '#savesshkey', function (event) {
        event.preventDefault();
        var data = [];
        var sshName = $('#mSSHName').val();
        var savetype = $('#mSSHKeysID').val();
        if (sshName !== '' && ($('#userKeyCheck').is(":checked") || $('#ourKeyCheck').is(":checked"))) {
            if ($('#userKeyCheck').is(":checked")) {
                if ($('#mUserPriKey').val() !== "" && $('#mUserPubKey').val() !== "") {
                    data.push({ name: "check_userkey", value: "on" });
                    data.push({ name: "prikey", value: encodeURIComponent($('#mUserPriKey').val()) });
                    data.push({ name: "pubkey", value: encodeURIComponent($('#mUserPubKey').val()) });
                }
            } else if ($('#ourKeyCheck').is(":checked")) {
                if ($('#mOurPriKey').val() !== "" && $('#mOurPubKey').val() !== "") {
                    data.push({ name: "check_ourkey", value: "on" });
                    data.push({ name: "prikey", value: encodeURIComponent($('#mOurPriKey').val()) });
                    data.push({ name: "pubkey", value: encodeURIComponent($('#mOurPubKey').val()) });
                }
            }
            data.push({ name: "id", value: savetype });
            data.push({ name: "name", value: sshName });
            data.push({ name: "p", value: "saveSSHKeys" });
            if (data.length > 3) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: data,
                    async: true,
                    success: function (s) {
                        if (savetype.length) { //edit
                            var clickedRow = $('#savesshkey').data('clickedrow');
                            var getsshData = [];
                            getsshData.push({ name: "id", value: savetype });
                            getsshData.push({ name: "p", value: 'getSSH' });
                            $.ajax({
                                type: "POST",
                                url: "ajax/ajaxquery.php",
                                data: getsshData,
                                async: true,
                                success: function (sc) {
                                    var rowData = {};
                                    var keys = sshTable.settings().init().columns;
                                    for (var i = 0; i < keys.length; i++) {
                                        var key = keys[i].data;
                                        rowData[key] = sc[0][key];
                                    }
                                    rowData.id = sc[0].id;
                                    sshTable.row(clickedRow).remove().draw();
                                    sshTable.row.add(rowData).draw();

                                },
                                error: function (errorThrown) {
                                    alert("Error: " + errorThrown);
                                }
                            });

                        } else { //insert
                            var getSSHData = [];
                            getSSHData.push({ name: "id", value: s.id });
                            getSSHData.push({ name: "p", value: 'getSSH' });
                            $.ajax({
                                type: "POST",
                                url: "ajax/ajaxquery.php",
                                data: getSSHData,
                                async: true,
                                success: function (sc) {
                                    var addData = {};
                                    var keys = sshTable.settings().init().columns;
                                    for (var i = 0; i < keys.length; i++) {
                                        var key = keys[i].data;
                                        addData[key] = sc[0][key];
                                    }
                                    addData.id = sc[0].id;
                                    sshTable.row.add(addData).draw();
                                },
                                error: function (errorThrown) {
                                    alert("Error: " + errorThrown);
                                }
                            });
                        }
                        $('#sshKeyModal').modal('hide');

                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        }
    });

    //------------   ssh keys section ends -------------

    //------------ amazon keys section-------------
    function getAmzTableOptions() {
        var button = '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button><ul class="dropdown-menu" role="menu"><li><a href="#amzKeyModal" data-toggle="modal" class="editAmzKeys">Edit</a></li><li><a href="#confirmDelAmzModal" data-toggle="modal" class="deleteAmzKeys">Delete</a></li></ul></div>';
        return button;
    }
    var amzTable = $('#amzKeyTable').DataTable({
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getAmz" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "name"
            }, {
            "data": "date_modified"
            }, {
            data: null,
            className: "center",
            fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html(getAmzTableOptions());
            }
            }]
    });
    //
    // confirm Delete amz modal 
    $('#confirmDelAmzModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var clickedRow = button.closest('tr');
        var rowData = amzTable.row(clickedRow).data();
        var remove_id = rowData.id;
        if (button.attr('class') === 'deleteAmzKeys') {
            $('#mDelAmzBtn').data('clickedrow', clickedRow);
            $('#mDelAmzBtn').attr('remove_id', remove_id);
            $('#mDelAmzBtn').attr('class', 'btn btn-primary deleteAmzKeys');
            $('#confirmDelAmzModalText').html('Are you sure you want to delete?');
        }
    });


    $('#confirmDelAmzModal').on('click', '.deleteAmzKeys', function (event) {
        var remove_id = $('#mDelAmzBtn').attr('remove_id');
        var clickedRow = $('#mDelAmzBtn').data('clickedrow');
        if (remove_id !== '') {
            var warnUser = false;
            var warnText = '';
            //[warnUser, warnText] = checkDeletionAmz(remove_id);

            //A. If it is allowed to delete    
            if (warnUser === false) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: {
                        id: remove_id,
                        p: "removeAmz"
                    },
                    async: true,
                    success: function (s) {
                        amzTable.row(clickedRow).remove().draw();
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
            //B. If it is not allowed to delete
            else if (warnUser === true) {
                $('#warnDelete').off();
                $('#warnDelete').on('show.bs.modal', function (event) {
                    $('#warnDelText').html(warnText);
                });
                $('#warnDelete').modal('show');
            }
            $('#confirmDelAmzModal').modal('hide');
        }
    });

    $('#amzKeyModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        if (button.attr('id') === 'addAmazonKey') {
            $('#amzkeysmodaltitle').html('Add Amazon Credentials');
        } else {
            $('#amzkeysmodaltitle').html('Edit Amazon Credentials');
            var clickedRow = button.closest('tr');
            var rowData = amzTable.row(clickedRow).data();
            $('#saveamzkey').data('clickedrow', clickedRow);
            var formValues = $('#amzKeyModal').find('input');
            var data = getValues({ p: "getAmz", id: rowData.id })[0];
            $(formValues[0]).val(rowData.id);
            $(formValues[1]).val(rowData.name);
            $('#mAmzDefReg').val(data.amz_def_reg);
            $('#mAmzAccKey').val(data.amz_acc_key);
            $('#mAmzSucKey').val(data.amz_suc_key);
        }
    });

    $('#amzKeyModal').on('click', '#saveamzkey', function (event) {
        event.preventDefault();
        var data = [];
        var amzName = $('#mAmzName').val();
        var savetype = $('#mAmzKeysID').val();
        var amz_def_reg = $('#mAmzDefReg').val();
        var amz_acc_key = $('#mAmzAccKey').val();
        var amz_suc_key = $('#mAmzSucKey').val();
        if (amzName !== '' && amz_def_reg !== "" && amz_acc_key !== "" && amz_suc_key !== "") {
            data.push({ name: "id", value: savetype });
            data.push({ name: "name", value: amzName });
            data.push({ name: "amz_def_reg", value: amz_def_reg });
            data.push({ name: "amz_acc_key", value: amz_acc_key });
            data.push({ name: "amz_suc_key", value: amz_suc_key });
            data.push({ name: "p", value: "saveAmzKeys" });
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: data,
                async: true,
                success: function (s) {
                    if (savetype.length) { //edit
                        var clickedRow = $('#saveamzkey').data('clickedrow');
                        var getAmzData = [];
                        getAmzData.push({ name: "id", value: savetype });
                        getAmzData.push({ name: "p", value: 'getAmz' });
                        $.ajax({
                            type: "POST",
                            url: "ajax/ajaxquery.php",
                            data: getAmzData,
                            async: true,
                            success: function (sc) {
                                var rowData = {};
                                var keys = amzTable.settings().init().columns;
                                for (var i = 0; i < keys.length; i++) {
                                    var key = keys[i].data;
                                    rowData[key] = sc[0][key];
                                }
                                rowData.id = sc[0].id;
                                amzTable.row(clickedRow).remove().draw();
                                amzTable.row.add(rowData).draw();

                            },
                            error: function (errorThrown) {
                                alert("Error: " + errorThrown);
                            }
                        });

                    } else { //insert
                        var getAmzData = [];
                        getAmzData.push({ name: "id", value: s.id });
                        getAmzData.push({ name: "p", value: 'getAmz' });
                        $.ajax({
                            type: "POST",
                            url: "ajax/ajaxquery.php",
                            data: getAmzData,
                            async: true,
                            success: function (sc) {
                                var addData = {};
                                var keys = amzTable.settings().init().columns;
                                for (var i = 0; i < keys.length; i++) {
                                    var key = keys[i].data;
                                    addData[key] = sc[0][key];
                                }
                                addData.id = sc[0].id;
                                amzTable.row.add(addData).draw();
                            },
                            error: function (errorThrown) {
                                alert("Error: " + errorThrown);
                            }
                        });
                    }
                    $('#amzKeyModal').modal('hide');

                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });

    //------------   amazon keys section ends -------------
    //------------   adminTab starts -------------

    $('#impersonModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('option').remove();
        if (button.attr('id') === 'impersonUser') {
            $.ajax({
                type: "GET",
                url: "ajax/ajaxquery.php",
                data: {
                    p: "getAllUsers"
                },
                async: false,
                success: function (s) {
                    for (var i = 0; i < s.length; i++) {
                        var param = s[i];
                        var optionGroup = new Option(param.username, param.id);
                        $("#mUserList").append(optionGroup);
                    }
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });

        }
    });


    $('#impersonModal').on('click', '#confirmImpersonBut', function (event) {
        event.preventDefault();
        var userId = $('#mUserList').val();
        if (userId !== '') {
            var userData = [];
            userData.push({ name: "user_id", value: userId });
            userData.push({ name: "p", value: 'impersonUser' });
            $.ajax({
                type: "POST",
                data: userData,
                url: "ajax/login.php",
                async: false,
                success: function (msg) {
                    if (msg.error == 1) {
                        alert('Something Went Wrong!');
                    } else {
                        var logInSuccess = true;
                        window.location.reload('true');
                    }
                }
            });
            $('#impersonModal').modal('hide');
        }
    });




});
