function generateKeys() {
    var genKeys = getValues({ p: "generateKeys" });
    if (genKeys) {
        if (genKeys.$keyPri && genKeys.$keyPub) {
            $('#mOurPriKey').val($.trim(genKeys.$keyPri));
            $('#mOurPubKey').val($.trim(genKeys.$keyPub));
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
    var profileTable = $('#profilesTable').DataTable({
        sScrollX: "100%",
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getProfiles" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "name"
        }, {
            "data": null,
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                if (oData.hostname != undefined) {
                    $(nTd).html("Host");
                } else {
                    $(nTd).html("Amazon");
                }
            }
        }, {
            "data": null,
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                if (oData.hostname != undefined) {
                    $(nTd).html("Nextflow Path:" + oData.next_path + "<br/>Executor:" + oData.executor + "<br/>Connection:" + oData.username + "@" + oData.hostname);
                } else {
                    $(nTd).html("Nextflow Path:" + oData.next_path + "<br/>Executor:" + oData.executor + "<br/>Instance_type:" + oData.instance_type + "<br/>Image_id:" + oData.image_id);
                }
            }
        }, {
            data: null,
            className: "center",
            fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                if (oData.hostname != undefined) {
                    $(nTd).html(getProfileButton('cluster'));
                } else {
                    $(nTd).html(getProfileButton('amazon'));
                }
            }
        }],
        'order': [[2, 'desc']]
    });
    
    function getGithubTableOptions() {
            var button = '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button><ul class="dropdown-menu" role="menu"><li><a href="#githubModal" data-toggle="modal" class="editGithub">Edit</a></li><li><a href="#confirmDelModal" data-toggle="modal" class="deleteGithub">Delete</a></li></ul></div>';
            return button;
        }
    
    var githubTable = $('#githubTable').DataTable({
            "ajax": {
                url: "ajax/ajaxquery.php",
                data: { "p": "getGithub" },
                "dataSrc": ""
            },
            "columns": [{
                "data": "username"
            }, {
                "data": "email"
            },{
                "data": "date_modified"
            }, {
                data: null,
                className: "center",
                fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).html(getGithubTableOptions());
                }
            }]
        });



    function getProfileButton(type) {
        if (type === "amazon") {
            var button = '<div style="display: inline-flex"><button type="button" class="btn btn-primary btn-sm" title="Edit" id="profileedit" data-toggle="modal" data-target="#profilemodal">Edit</button> &nbsp; <button type="button" class="btn btn-primary btn-sm" title="Remove" id="profileremove" data-toggle="modal" data-target="#confirmDelProModal">Remove</button>&nbsp;<button type="button" class="btn btn-primary btn-sm" title="start/stop" id="amzStartStop" data-toggle="modal" data-target="#amzModal">Start/Stop</button> &nbsp;</div>';
        } else if (type === "cluster" || type === "local") {
            var button = '<div style="display: inline-flex"><button type="button" class="btn btn-primary btn-sm" title="Edit" id="profileedit" data-toggle="modal" data-target="#profilemodal">Edit</button> &nbsp; <button type="button" class="btn btn-primary btn-sm" title="Remove" id="profileremove" data-toggle="modal" data-target="#confirmDelProModal">Remove</button>';
        }
        return button;
    }

    function getPublicProfileButton() {
        var button = '<div style="display: inline-flex"><button type="button" class="btn btn-primary btn-sm" title="Edit" id="editPublicProfile" data-toggle="modal" data-target="#profilemodal">Edit</button> &nbsp; <button type="button" class="btn btn-primary btn-sm" title="Remove" id="deletePublicProfile" data-toggle="modal" data-target="#confirmDelProModal">Remove</button>'
        return button;
    }

    function loadOptions(type) {
        if (type === "ssh") {
            var data = getValues({ p: "getSSH", type:"hidden" });
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


    if (usRole === "admin") {
        var publicProfileTable = $('#publicProfileTable').DataTable({
            "ajax": {
                url: "ajax/ajaxquery.php",
                data: { "p": "getProfiles", type: "public" },
                "dataSrc": ""
            },
            "columns": [{
                "data": "name"
            }, {
                "data": null,
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    if (oData.hostname != undefined) {
                        $(nTd).html("Host");
                    } else {
                        $(nTd).html("Amazon");
                    }
                }
            }, {
                "data": null,
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    if (oData.hostname != undefined) {
                        $(nTd).html("Nextflow Path:" + oData.next_path + "<br/>Executor:" + oData.executor + "<br/>Connection:" + oData.username + "@" + oData.hostname);
                    } else {
                        $(nTd).html("Nextflow Path:" + oData.next_path + "<br/>Executor:" + oData.executor + "<br/>Instance_type:" + oData.instance_type + "<br/>Image_id:" + oData.image_id);
                    }
                }
            }, {
                data: null,
                className: "center",
                fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).html(getPublicProfileButton());
                }
            }]
        });
    }

    function selectizeProfileName() {
        var renderMenuGroup = {
            option: function (data, escape) {
                if (data.hostname !== undefined) {
                    return '<div class="option">' +
                        '<span class="title"><i>' + escape(data.name) + ' (Public Profile) </i></span>' +
                        '<span class="url">' + 'Hostname: ' + escape(data.hostname) + '</span>' +
                        '</div>';
                } else if (data.image_id !== undefined) {
                    return '<div class="option">' +
                        '<span class="title"><i>' + escape(data.name) + ' (Public Profile) </i></span>' +
                        '<span class="url">' + 'Image Id: ' + escape(data.image_id) + '</span>' +
                        '</div>';
                } else {
                    return '<div class="option">' +
                        '<span class="title"><i>' + escape(data.name) + '</i></span>' +
                        '</div>';
                }
            },
            item: function (data, escape) {
                return '<div class="item" data-value="' + escape(data.id) + '">' + escape(data.name) + '</div>';
            }
        };


        var allMenuGroup = getValues({ p: "getProfiles", type: "public" });
        for (var i = 0; i < allMenuGroup.length; i++) {
            allMenuGroup[i].variable = decodeHtml(allMenuGroup[i].variable);
        }
        console.log(allMenuGroup)
        $('#mEnvName').selectize({
            valueField: 'id',
            searchField: ['name'],
            createOnBlur: true,
            options: allMenuGroup,
            render: renderMenuGroup,
            create: function (input, callback) {
                callback({ id: input, name: input });
            }
        });


    }

    $(function () {
        $(document).on('change', '#mEnvName', function () {
            var valueID = $('#mEnvName')[0].selectize.getValue();
            var options = $('#mEnvName')[0].selectize.options
            var cpOptions = $.extend(true, {}, options);
            if (cpOptions[valueID]) {
                if (cpOptions[valueID].executor != undefined) {
                    delete cpOptions[valueID]["id"]; //to prevent profile update
                    if (cpOptions[valueID].hostname != undefined) {
                        $('#chooseEnv').val('cluster').trigger('change');
                    } else {
                        $('#chooseEnv').val('amazon').trigger('change');
                    }
                    fillFormByName('#profilemodal', 'input, select, textarea', cpOptions[valueID]);
                    $('#mExec').trigger('change');
                }
            }

        })
    });

    $('#profilemodal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        selectizeProfileName();
        if (button.attr('id') === 'addEnv') {
            $('#mAddEnvTitle').html('Add Environment');
            loadOptions("ssh");
            loadOptions("amz");
        } else if (button.attr('id') === 'addPublicProfile') {
            $('#mAddEnvTitle').html('Add Public Environment');
        } else if (button.attr('id') === 'editPublicProfile' || button.attr('id') === 'profileedit') {
            var clickedRow = button.closest('tr');
            if (button.attr('id') === 'editPublicProfile') {
                $('#mAddEnvTitle').html('Edit Public Environment');
                var rowData = publicProfileTable.row(clickedRow).data();
            } else if (button.attr('id') === 'profileedit') {
                $('#mAddEnvTitle').html('Edit Run Environment');
                var rowData = profileTable.row(clickedRow).data();
                loadOptions("ssh");
                loadOptions("amz");
            }
            $('#saveEnv').data('clickedrow', clickedRow);
            var proType = "";
            if (rowData.hostname != undefined) {
                proType = "cluster";
            } else {
                proType = "amazon";
            }
            var proId = rowData.id;
            if (proType === "cluster") {
                var data = getValues({ p: "getProfileCluster", id: proId });
                console.log(data[0])
                console.log(data[0].variable)
                console.log(decodeHtml(data[0].variable))
                data[0].variable=decodeHtml(data[0].variable)
                $('#chooseEnv').val('cluster').trigger('change');
                fillFormByName('#profilemodal', 'input, select, textarea', data[0]);
                $('#mExec').trigger('change');
            } else if (proType === "amazon") {
                var data = getValues({ p: "getProfileAmazon", id: proId });
                data[0].variable =decodeHtml(data[0].variable)
                $('#chooseEnv').val('amazon').trigger('change');
                fillFormByName('#profilemodal', 'input, select, textarea', data[0]);
                $('#mExec').trigger('change');
            }
            if (!$('#mAddEnvTitle').html().match(/Public/)) {
                $("#mEnvName")[0].selectize.addOption({
                    id: rowData.name,
                    name: rowData.name,
                });
                $("#mEnvName")[0].selectize.setValue(rowData.name, false);
            } else {
                $("#mEnvName")[0].selectize.setValue(rowData.id, false);
            }
            $('#chooseEnv').attr('disabled', "disabled");
        }
    });

    //id='#execJobSetTable' or '#execNextSettTable'
    function showHideColumnProfile(id, colList, type) {
        for (var k = 0; k < colList.length; k++) {
            if (type == "hide") {
                $(id).find('th:nth-child(' + colList[k] + ')').hide();
                $(id).find('td:nth-child(' + colList[k] + ')').hide();
            } else {
                $(id).find('th:nth-child(' + colList[k] + ')').show();
                $(id).find('td:nth-child(' + colList[k] + ')').show();
            }
        }
    }

    $(function () {
        $(document).on('change', '#chooseEnv', function () {
            var selEnvType = $('#chooseEnv option:selected').val();
            var title = $('#mAddEnvTitle').html();
            var noneList = [];
            var blockList = [];
            if (selEnvType === "cluster" && !title.match(/Public/)) {
                var noneList = ["mEnvAmzDefRegDiv", "mEnvAmzAccKeyDiv", "mEnvAmzSucKeyDiv", "mEnvInsTypeDiv", "mEnvImageIdDiv", "mPriKeyAmzDiv", "mPubKeyDiv", "execJobSetDiv", "mSubnetIdDiv", "mSecurityGroupDiv", "mSharedStorageIdDiv", "mSharedStorageMountDiv", "mEnvAmzKeyDiv"];
                var blockList = ["mExecDiv", "mEnvUsernameDiv", "mEnvHostnameDiv", "mEnvPortDiv", "mEnvSinguCacheDiv", "mEnvSSHKeyDiv", "mEnvNextPathDiv", "mEnvCmdDiv", "execNextDiv", "mExecJobDiv", "mEnvVarDiv"];
            } else if (selEnvType === "amazon" && !title.match(/Public/)) {
                var noneList = ["mEnvUsernameDiv", "mEnvHostnameDiv", "mPriKeyCluDiv"];
                var blockList = ["mExecDiv", "mEnvSSHKeyDiv", "mEnvPortDiv", "mEnvSinguCacheDiv", "mEnvAmzKeyDiv", "mEnvInsTypeDiv", "mEnvImageIdDiv", "mEnvNextPathDiv", "mEnvCmdDiv", "mEnvVarDiv", "execNextDiv", "mExecJobDiv", "execJobSetDiv", "mSubnetIdDiv", "mSecurityGroupDiv", "mSharedStorageIdDiv", "mSharedStorageMountDiv"];
            } else if (selEnvType === "cluster" && title.match(/Public/)) {
                var noneList = ["mEnvAmzDefRegDiv", "mEnvAmzAccKeyDiv", "mEnvAmzSucKeyDiv", "mEnvInsTypeDiv", "mEnvImageIdDiv", "mPriKeyAmzDiv", "mPubKeyDiv", "execJobSetDiv", "mSubnetIdDiv", "mSecurityGroupDiv", "mSharedStorageIdDiv", "mSharedStorageMountDiv", "mEnvAmzKeyDiv", "mEnvUsernameDiv", "mEnvSSHKeyDiv"];
                var blockList = ["mExecDiv", , "mEnvHostnameDiv", "mEnvPortDiv", "mEnvSinguCacheDiv", "mEnvNextPathDiv", "mEnvCmdDiv", "execNextDiv", "mExecJobDiv", "mEnvVarDiv"];
            } else if (selEnvType === "amazon" && title.match(/Public/)) {
                var noneList = ["mEnvUsernameDiv", "mEnvHostnameDiv", "mPriKeyCluDiv", "mEnvSSHKeyDiv", "mEnvAmzKeyDiv"];
                var blockList = ["mExecDiv", "mEnvInsTypeDiv", "mEnvImageIdDiv", "mEnvNextPathDiv", "mEnvCmdDiv", "execNextDiv", "mExecJobDiv", "execJobSetDiv", "mSubnetIdDiv", "mSecurityGroupDiv", "mSharedStorageIdDiv", "mSharedStorageMountDiv", "mEnvSinguCacheDiv", "mEnvPortDiv", "mEnvVarDiv"];
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
                showHideColumnProfile('#execNextSettTable', [1, 4, 5], 'hide');
            } else if (mExecType === "sge" || mExecType === "lsf" || mExecType === "slurm") {
                showHideColumnProfile('#execNextSettTable', [1, 4, 5], 'show');
                $('#mExecJob').val(mExecType).trigger('change');
                $('#mExecJob').attr('disabled', "disabled");
                $('#execNextDiv').css('display', 'block');
                $('#mExecJobDiv').css('display', 'block');
            }
            if (mExecType === "slurm"){
                $('#execJobQueue').text('Partition');
                $('#execNextQueue').text('Partition');
            }else {
                $('#execJobQueue').text('Queue');
                $('#execNextQueue').text('Queue');
            }
        })
    });

    $(function () {
        $(document).on('change', '#mExecJob', function () {
            var mExecJobType = $('#mExecJob option:selected').val();
            if (mExecJobType === "ignite") {
                showHideColumnProfile('#execJobSetTable', [1, 4, 5], 'show');
                showHideColumnProfile('#execJobSetTable', [1, 4], 'hide');
            } else if (mExecJobType === "local") {
                showHideColumnProfile('#execJobSetTable', [1, 4, 5], 'hide');
            } else {
                showHideColumnProfile('#execJobSetTable', [1, 4, 5], 'show');
            }
            if (mExecJobType === "slurm"){
                $('#execJobQueue').text('Partition');
            }else {
                $('#execJobQueue').text('Queue');
            }
            $('#execJobSetDiv').css('display', 'block');
        })
    });

    $('#profilemodal').on('hide.bs.modal', function (event) {
        var noneList = ["mEnvUsernameDiv", "mEnvHostnameDiv", "mEnvSSHKeyDiv", "mEnvAmzKeyDiv", "mEnvInsTypeDiv", "mEnvImageIdDiv", "mExecDiv", "mEnvNextPathDiv", "mEnvCmdDiv", "execNextDiv", "mExecJobDiv", "execJobSetDiv", "mSubnetIdDiv", "mSecurityGroupDiv", "mSharedStorageIdDiv", "mSharedStorageMountDiv", "mEnvSinguCacheDiv", "mEnvPortDiv", "mEnvVarDiv"];
        $.each(noneList, function (element) {
            $('#' + noneList[element]).css('display', 'none');
        });
        $('#chooseEnv').removeAttr('disabled');
        $('#mExecJob').removeAttr('disabled');
        $('#mEnvAmzKey').find('option').not(':eq(0)').remove()
        $('#mEnvSSHKey').find('option').not(':eq(0)').remove()
        $('#mEnvName')[0].selectize.destroy();
        cleanHasErrorClass("#profilemodal")
    });

    $('#profilemodal').on('click', '#saveEnv', function (event) {
        event.preventDefault();
        $('#chooseEnv').removeAttr('disabled');
        $('#mExecJob').removeAttr('disabled');
        var formValues = $('#profilemodal').find('input, select, textarea');
        var savetype = $('#mEnvId').val();
        var formObj = {};
        var stop = "";
        var title = $('#mAddEnvTitle').html();
        var clickedRow = $('#saveEnv').data('clickedrow');
        [formObj, stop] = createFormObj(formValues, ["name"]);
        var selEnvType = $('#chooseEnv option:selected').val();
        var nameID = $("#mEnvName")[0].selectize.getValue()
        if (nameID) {
            if ($("#mEnvName")[0].selectize.options[nameID]) {
                formObj.name = $("#mEnvName")[0].selectize.options[nameID].name;
            }
        }
        formObj.variable = encodeURIComponent(formObj.variable);
        if (formObj.executor_job == "ignite") {
            formObj.job_queue = "";
            formObj.job_time = "";
        }
        if (formObj.executor_job == "local") {
            formObj.job_queue = "";
            formObj.job_time = "";
            formObj.job_clu_opt = "";
        }
        if (formObj.executor == "local") {
            formObj.next_queue = "";
            formObj.next_time = "";
            formObj.next_clu_opt = "";
        }

        if (selEnvType.length && stop == false) {
            if (title.match(/Public/)) {
                formObj.public = "1";
            }
            if (selEnvType === "cluster") {
                formObj.p = "saveProfileCluster";
            } else if (selEnvType === "amazon") {
                formObj.p = "saveProfileAmazon";
            }
            console.log(formObj)
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: formObj,
                async: true,
                success: function (s) {
                    if (savetype.length) { //edit
                        var proId = savetype
                        } else {
                            var proId = s.id;
                        }
                    if (selEnvType === "cluster") {
                        var newProfileData = getValues({ p: "getProfileCluster", id: proId });
                    } else if (selEnvType === "amazon") {
                        var newProfileData = getValues({ p: "getProfileAmazon", id: proId });
                    }
                    if (newProfileData[0]) {
                        if (title.match(/Public/)) {
                            publicProfileTable.ajax.reload(null, false);
                        } else {
                            profileTable.ajax.reload(null, false);
                            if (!savetype.length) { //insert
                                if (selEnvType === "amazon") {
                                    checkAmazonTimer(s.id, 40000);
                                }
                            }
                        }
                    }
                    $('#profilemodal').modal('hide');
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });


    // confirm Delete ssh modal 
    $('#confirmDelProModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var clickedRow = button.closest('tr');
        if (button.attr('id') === 'profileremove') {
            $('#mDelProBtn').data('clickedRow', clickedRow);
            $('#mDelProBtn').attr('class', 'btn btn-primary deleteProfile');
            $('#confirmDelProModalText').html('Are you sure you want to delete?');
        } else if (button.attr('id') === 'deletePublicProfile') {
            $('#mDelProBtn').data('clickedRow', clickedRow);
            $('#mDelProBtn').attr('class', 'btn btn-primary deleteProfile');
            $('#confirmDelProModalText').html('Are you sure you want to delete public profile?');
        }

    });

    $('#confirmDelProModal').on('click', '.deleteProfile', function (event) {
        var clickedRow = $('#mDelProBtn').data('clickedRow');
        var title = $('#confirmDelProModalText').html();
        if (title.match(/public/)) {
            var rowData = publicProfileTable.row(clickedRow).data();
        } else {
            var rowData = profileTable.row(clickedRow).data();
        }
        var proType = "";
        if (rowData.hostname != undefined) {
            proType = "cluster";
        } else {
            proType = "amazon";
        }
        var proId = rowData.id;
        var data = {};
        if (proType === "cluster") {
            data = { "id": proId, "p": "removeProCluster" };
        } else if (proType === "amazon") {
            data = { "id": proId, "p": "removeProAmazon" };
        }
        if (proId !== '') {
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
                        if (title.match(/public/)) {
                            publicProfileTable.row(clickedRow).remove().draw();
                        } else {
                            profileTable.row(clickedRow).remove().draw();
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
                        if (param.username){
                            var optionGroup = new Option(param.username, param.id);
                            $("#mGroupList").append(optionGroup);  
                        }
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
        $('#mDelBtn').data('clickedrow', clickedRow);
        if (button.attr('class') === 'deleteSSHKeys') {
            var rowData = sshTable.row(clickedRow).data();
            var remove_id = rowData.id;
            $('#mDelBtn').attr('remove_id', remove_id);
            $('#mDelBtn').attr('class', 'btn btn-primary deleteSSHKeys');
            $('#confirmDelModalText').html('Are you sure you want to delete?');
        } else if (button.attr('class') === 'delUser'){
            var AdmUserTable = $('#AdminUserTable').DataTable();
            var rowData = AdmUserTable.row(clickedRow).data();
            var remove_id = rowData.id;
            $('#mDelBtn').attr('remove_id', remove_id);
            var email = rowData.email;
            var name = rowData.name;
            $('#mDelBtn').attr('class', 'btn btn-primary delUser');
            $('#confirmDelModalText').html('Are you sure you want to delete following user?</br></br>Name: '+name+'</br>E-mail: '+email);
        } else if (button.attr('class') === 'deleteGithub') {
            var rowData = githubTable.row(clickedRow).data();
            var remove_id = rowData.id;
            $('#mDelBtn').attr('remove_id', remove_id);
            $('#mDelBtn').attr('class', 'btn btn-primary deleteGithub');
            $('#confirmDelModalText').html('Are you sure you want to delete?');
        }
    });

    $('#confirmDelModal').on('click', '.deleteGithub', function (event) {
        var remove_id = $('#mDelBtn').attr('remove_id');
        var clickedRow = $('#mDelBtn').data('clickedrow');
        if (remove_id !== '') {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    id: remove_id,
                    p: "removeGithub"
                },
                async: true,
                success: function (s) {
                    githubTable.row(clickedRow).remove().draw();
                    $('#confirmDelModal').modal('hide');
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
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

    $('#confirmDelModal').on('click', '.delUser', function (event) {
        var remove_id = $('#mDelBtn').attr('remove_id');
        var clickedRow = $('#mDelBtn').data('clickedrow');
        if (remove_id) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    id: remove_id,
                    p: "removeUser"
                },
                async: true,
                success: function (s) {
                    var AdmUserTable = $('#AdminUserTable').DataTable();
                    AdmUserTable.row(clickedRow).remove().draw();
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
            $('#confirmDelModal').modal('hide');
        }
    });

    $('#sshKeyModal').on('hide.bs.modal', function (event) {
        $('#ourKeyCheck').prop('disabled', false);
        $('#userKeyCheck').prop('disabled', false);
        $('#createKeysDiv').css('display', 'inline');
        $('#mOurPriKeyDiv').css('display', 'none');
        $('#mOurPubKeyDiv').css('display', 'none');
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
            if (rowData.hide == "true"){
                $('#hideKeys').prop('checked', true);
            } else {
                $('#hideKeys').prop('checked', false);
            }
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
        var hideKeys = $('#hideKeys').is(":checked").toString();
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
            data.push({ name: "hide", value: hideKeys });
            data.push({ name: "p", value: "saveSSHKeys" });
            if (data.length > 4) {
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
                                    var newData = getValues({ p: "getSSH", id: savetype })
                                    if (newData[0]){
                                        sshTable.row(clickedRow).remove().draw();
                                        sshTable.row.add(newData[0]).draw(); 
                                    }
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
                                    var newData = getValues({ p: "getSSH", id: sc[0].id })
                                    if (newData[0]){
                                        sshTable.row.add(newData[0]).draw();
                                    }
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
    function getAdminUserTableOptions(active, role) {
        if (active && active == 1) {
            var activeItem = '<li><a name="deactivate" class="changeActiveUser">Deactivate User</a></li><li><a name="activateSendUser" class="changeActiveUser">Send activation E-mail to User</a></li>';
        } else {
            var activeItem = '<li><a name="activate" class="changeActiveUser">Activate User</a></li><li><a name="activateSendUser" class="changeActiveUser">Activate and send E-mail to User</a></li>';
        }
        if (role && role == "admin") {
            var roleItem = '<li><a name="user" class="changeRoleUser">Assign user role</a></li>';
        } else {
            var roleItem = '<li><a name="admin" class="changeRoleUser">Assign admin role</a></li>';
        }
        var button = '<div class="btn-group"><button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button><ul class="dropdown-menu dropdown-menu-right" role="menu"><li><a class="impersonUser">Impersonate User</a></li><li><a class="editUser" href="#userModal" data-toggle="modal">Edit User</a></li>' + activeItem + roleItem + '<li><a class="delUser" href="#confirmDelModal" data-toggle="modal">Delete User</a></li></ul></div>';
        return button;

    }
    //change password----

    $("#password1, #password2").keyup(function () {
        var vis8char = $("#8charDiv").css("display")
        var visMatch = $("#pwmatchDiv").css("display")
        if (vis8char == "none" && $("#password1").val().length > 0) {
            $("#8charDiv").css("display", "block")
        } else if (!$("#password1").val().length > 0) {
            $("#8charDiv").css("display", "none")
        }
        if (visMatch == "none" && $("#password2").val().length > 0) {
            $("#pwmatchDiv").css("display", "block")
        } else if (!$("#password2").val().length > 0) {
            $("#pwmatchDiv").css("display", "none")
        }

        if ($("#password1").val().length >= 8) {
            $("#8char").removeClass("glyphicon-remove");
            $("#8char").addClass("glyphicon-ok");
            $("#8char").css("color", "#00A41E");
        } else {
            $("#8char").removeClass("glyphicon-ok");
            $("#8char").addClass("glyphicon-remove");
            $("#8char").css("color", "#FF0004");
        }

        if ($("#password1").val() == $("#password2").val() && $("#password1").val().length > 0) {
            $("#pwmatch").removeClass("glyphicon-remove");
            $("#pwmatch").addClass("glyphicon-ok");
            $("#pwmatch").css("color", "#00A41E");
        } else {
            $("#pwmatch").removeClass("glyphicon-ok");
            $("#pwmatch").addClass("glyphicon-remove");
            $("#pwmatch").css("color", "#FF0004");
        }
    });

    $('#passwordForm').on('click', '#changePassBtn', function (event) {
        event.preventDefault();
        var formValues = $('#passwordForm').find('input');
        var requiredFields = ["password0", "password1", "password2"];
        var formObj = {};
        var stop = "";
        [formObj, stop] = createFormObj(formValues, requiredFields)
        if (stop === false) {
            if ($("#password1").val().length >= 8 && $("#password1").val() == $("#password2").val()) {
                formObj.p = "changePassword";
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: formObj,
                    async: true,
                    success: function (s) {
                        if (s.password0 || s.password1) { //error
                            if (s.password0) {
                                toogleErrorUser('#passwordForm', "password0", "insert", s.password0)
                            } else {
                                toogleErrorUser('#passwordForm', "password0", "delete", null)
                            }
                            if (s.password1) {
                                toogleErrorUser('#passwordForm', "password1", "insert", s.password1)
                            } else {
                                toogleErrorUser('#passwordForm', "password1", "delete", null)
                            }
                        } else {
                            toogleErrorUser('#passwordForm', "password0", "delete", null)
                            toogleErrorUser('#passwordForm', "password1", "delete", null)
                            $("#changePassBtn").html("Password has changed!")
                            $("#changePassBtn").attr("class", "col-xs-12 btn btn-success btn-load");
                            $("#changePassBtn").attr("disabled", "disabled");

                            setTimeout(function () {
                                $("#passwordForm").trigger('reset');
                                $("#8charDiv").css("display", "none")
                                $("#pwmatchDiv").css("display", "none")
                                $("#changePassBtn").attr("class", "col-xs-12 btn btn-primary btn-load");
                                $("#changePassBtn").html("Change Password ");
                                $("#changePassBtn").removeAttr("disabled");
                            }, 3000);
                        }
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        }
    });

    function toogleErrorPassword(name, type, error) {
        if (type == "delete") {
            $('#passwordForm').find('input[name=' + name + ']').parent().parent().removeClass("has-error");
            $('#passwordForm').find('font[name=' + name + ']').remove();
        } else if (type == "insert") {
            $('#passwordForm').find('input[name=' + name + ']').parent().parent().addClass("has-error");
            $('#passwordForm').find('font[name=' + name + ']').remove();
            $('#passwordForm').find('input[name=' + name + ']').parent().append('<font name="' + name + '" class="text-center" color="crimson">' + error + '</font>')
        }
    }

    //change password ends----

    if (usRole === "admin") {

        var AdmUserTable = $('#AdminUserTable').DataTable({
            "ajax": {
                url: "ajax/ajaxquery.php",
                data: { "p": "getAllUsers" },
                "dataSrc": ""
            },
            "columns": [{
                "data": "name"
            }, {
                "data": "username"
            }, {
                "data": "email"
            }, {
                "data": "institute"
            }, {
                "data": "role"
            }, {
                "data": "active",
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    if (oData.active && oData.active == 1) {
                        $(nTd).html("true");
                    } else {
                        $(nTd).html("false");
                    }
                }
            }, {
                "data": "memberdate"
            }, {
                data: null,
                className: "center",
                fnCreatedCell: function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).html(getAdminUserTableOptions(oData.active, oData.role));
                }
            }],
            'order': [[6, 'desc']]
        });

        $('#AdminUserTable').on('click', '.impersonUser', function (event) {
            var clickedRow = $(this).closest('tr');
            var rowData = AdmUserTable.row(clickedRow).data();
            var userId = rowData.id
            if (userId !== '') {
                var userData = [];
                userData.push({ name: "user_id", value: userId });
                userData.push({ name: "p", value: 'impersonUser' });
                $.ajax({
                    type: "POST",
                    data: userData,
                    url: "ajax/ajaxquery.php",
                    async: false,
                    success: function (msg) {
                        var logInSuccess = true;
                        window.location.reload('true');
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        });
        $('#AdminUserTable').on('click', '.changeActiveUser, .changeRoleUser', function (event) {
            var type = $(this).attr('name'); //activateSendUser or activate or deactivate
            var p = $(this).attr('class');
            var clickedRow = $(this).closest('tr');
            var rowData = AdmUserTable.row(clickedRow).data();
            var userId = rowData.id
            if (userId !== '') {
                var userData = [];
                userData.push({ name: "user_id", value: userId });
                userData.push({ name: "type", value: type });
                userData.push({ name: "p", value: p });
                $.ajax({
                    type: "POST",
                    data: userData,
                    url: "ajax/ajaxquery.php",
                    async: false,
                    success: function (sc) {
                        var newUserData = getValues({ p: "getUserById", id: userId })
                        if (newUserData[0]) {
                            AdmUserTable.row(clickedRow).remove().draw();
                            AdmUserTable.row.add(newUserData[0]).draw();
                        }
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        });

        //       ---USER MODAL
        $('#userModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            $(this).find('form').trigger('reset');
            if (button.attr('id') === 'addUser') {
                $('#userModalTitle').html('Add User');
            } else {
                $('#userModalTitle').html('Edit User');
                var clickedRow = button.closest('tr');
                var rowData = AdmUserTable.row(clickedRow).data();
                $('#savemUser').data('clickedrow', clickedRow);
                fillFormByName('#userModal', 'input, select', rowData);
            }
        });

        $('#userModal').on('hide.bs.modal', function (event) {
            cleanHasErrorClass("#userModal")
        });

        $('#userModal').on('click', '#savemUser', function (event) {
            event.preventDefault();
            var formValues = $('#userModal').find('input, select');
            var requiredFields = ["name", "username", "email", "institute", "logintype"];
            var clickedRow = $('#savemUser').data('clickedrow')
            var formObj = {};
            var stop = "";
            [formObj, stop] = createFormObj(formValues, requiredFields)
            var savetype = $('#mUserID').val();
            if (stop === false) {
                formObj.p = "saveUserManual"
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: formObj,
                    async: true,
                    success: function (s) {
                        if (s.email || s.username) { //exist in database
                            if (s.email) {
                                toogleErrorUser('#userModal', "email", "insert", s.email)
                            } else {
                                toogleErrorUser('#userModal', "email", "delete", null)
                            }
                            if (s.username) {
                                toogleErrorUser('#userModal', "username", "insert", s.username)
                            } else {
                                toogleErrorUser('#userModal', "username", "delete", null)
                            }
                        } else {
                            toogleErrorUser('#userModal', "username", "delete", null)
                            toogleErrorUser('#userModal', "email", "delete", null)
                            if (savetype.length) { //edit
                                var newUserData = getValues({ p: "getUserById", id: savetype })
                                if (newUserData[0]) {
                                    AdmUserTable.row(clickedRow).remove().draw();
                                    AdmUserTable.row.add(newUserData[0]).draw();
                                }
                            } else { //insert
                                var newUserData = getValues({ p: "getUserById", id: s.id })
                                if (newUserData[0]) {
                                    AdmUserTable.row.add(newUserData[0]).draw();
                                }
                            }
                            $('#userModal').modal('hide');
                        }



                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        });
        //---user modal section ends---

        //---github section starts---
        

        
        $('#githubModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            $(this).find('form').trigger('reset');
            if (button.attr('id') === 'addGithub') {
                $('#githubmodaltitle').html('Add GitHub Account');
            } else {
                $('#githubmodaltitle').html('Edit GitHub Account');
                var clickedRow = button.closest('tr');
                var rowData = githubTable.row(clickedRow).data();
                var data = getValues({ p: "getGithub", id: rowData.id })[0];
                
                $('#saveGithub').data('clickedrow', clickedRow);
                fillFormByName('#githubModal', 'input, select', data);
            }
        });

        $('#githubModal').on('hide.bs.modal', function (event) {
            cleanHasErrorClass("#githubModal")
        });

        $('#githubModal').on('click', '#saveGithub', function (event) {
            event.preventDefault();
            var formValues = $('#githubModal').find('input, select');
            var requiredFields = ["username", "email", "password"];
            var clickedRow = $('#saveGithub').data('clickedrow')
            var formObj = {};
            var stop = "";
            [formObj, stop] = createFormObj(formValues, requiredFields)
            var savetype = $('#mGitID').val();
            if (stop === false) {
                formObj.p = "saveGithub"
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: formObj,
                    async: true,
                    success: function (s) {
                        if (savetype.length) { //edit
                            var newGitData = getValues({ p: "getGithub", id: savetype })
                            if (newGitData[0]) {
                                githubTable.row(clickedRow).remove().draw();
                                githubTable.row.add(newGitData[0]).draw();
                            }
                        } else { //insert
                            var newGitData = getValues({ p: "getGithub", id: s.id })
                            if (newGitData[0]) {
                                githubTable.row.add(newGitData[0]).draw();
                            }
                        }
                        $('#githubModal').modal('hide');
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
                });
            }
        });
        //---github section ends---



    }

});
