function nextTab(elem) {
    $(elem).next().find('a[data-toggle="tab"]').click();
}
function lastTab(elem) {
    $(elem).last().find('a[data-toggle="tab"]').click();
}
function prevTab(elem) {
    $(elem).prev().find('a[data-toggle="tab"]').click();
}

function saveWizardData(modalId){
    var sendObj = {};
    var formObj = {};
    var stop = "";
    var name = $(modalId).data('name');
    var wid = $(modalId).data('wid');
    var status = $(modalId).data('status');
    var formValues = $(modalId).find('input, select, textarea');
    [formObj, stop] = createFormObj(formValues, ["name"]);
    //don't save ssh key to database
    formObj.pubkey = "";
    var disabled_tab_id = [];
    $(modalId).find(".nav-tabs li.disabled > a").each(function() {
        disabled_tab_id.push($(this).attr("href"));
    });
    formObj.disabled_tab_id = disabled_tab_id;
    var last_tab_id = $(modalId).find(".tab-pane.active").attr("id");
    formObj.last_tab_id = last_tab_id;
    sendObj.data = encodeURIComponent(JSON.stringify(formObj));
    sendObj.p = "saveWizard";
    sendObj.name = name;
    sendObj.status = status;
    sendObj.id = wid;
    console.log(formObj)
    console.log(sendObj)
    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: sendObj,
        async: true,
        success: function (s) {
            console.log(s)
            if (s.id){
                $(modalId).data('wid', s.id);
            }
        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
}

function showWizardProfileAlert(){
    var profiletype = $(".profilewizard input[name='profiletype']:checked").val()
    if (profiletype == "test"){
        $("#pw-step-info-alert").css("display", "block");
    } else {
        $("#pw-step-info-alert").css("display", "none");
    } 
}



function validatePW(type){
    var username = $("#pw-username").val();
    var hostname =  $("#pw-hostname").val();
    var connect = username + "@" + hostname;
    var ssh_id =  $("#pw-sshkeyid").val();
    var ssh_port =  $("#pw-sshport").val();
    var path = "";
    if (type == "pw-validate-ssh-key"){
        var ret = getValues({ p: "validateSSH", connect: connect,  ssh_port: ssh_port, ssh_id:ssh_id, type:"ssh"});
    } else if (type == "pw-validate-java"){
        var cmd =  $.trim($("#pw-javacmd").val());
        var ret = getValues({ p: "validateSSH", connect: connect,  ssh_port: ssh_port, ssh_id:ssh_id, type:"java", cmd:cmd, path:path });
    } else if (type == "pw-validate-nextflow"){
        var java_cmd =  $.trim($("#pw-javacmd").val());
        var cmd =  $.trim($("#pw-nextflowpath").val());
        if (cmd){
            if (cmd.substring(0, 1) == "/"){
                path = cmd;
                cmd = java_cmd;
            } else if (java_cmd){
                cmd = cmd + " && " + java_cmd;
            }  
        }
        var ret = getValues({ p: "validateSSH", connect: connect,  ssh_port: ssh_port, ssh_id:ssh_id, type:"nextflow", cmd:cmd, path:path });
    } else if (type == "pw-validate-docker"){
        var cmd =  $.trim($("#pw-dockercmd").val());
        var ret = getValues({ p: "validateSSH", connect: connect,  ssh_port: ssh_port, ssh_id:ssh_id, type:"docker", cmd:cmd, path:path });
    } else if (type == "pw-validate-singularity"){
        var cmd =  $.trim($("#pw-singularitycmd").val());
        var ret = getValues({ p: "validateSSH", connect: connect,  ssh_port: ssh_port, ssh_id:ssh_id, type:"singularity", cmd:cmd, path:path });
    }
    return ret;
}

function checkPublicProfileSelected(){
    //check if public profile settings are available in the #pw-hostname
    var hostname = $('#pw-hostname').val()
    var publicProfile = $('#pw-hostname')[0].selectize.options[hostname]
    var ret = false;
    if (publicProfile){
        if (publicProfile["date_created"]){
            //autofill other options, and go to last section
            return publicProfile;
        }
    }
    return ret;
}

function selectizePubProfileHostname(dropdown_id) {
    var renderMenuGroup = {
        option: function (data, escape) {
            if (data.hostname !== undefined) {
                return '<div class="option">' +
                    '<span class="title"><i>' + escape(data.hostname) + '</i></span>' +
                    '</div>';
            } 
        },
        item: function (data, escape) {
            return '<div class="item" data-value="' + escape(data.hostname) + '">' + escape(data.hostname) + '</div>';
        }
    };

    var hostMenuGroup = []
    var allMenuGroup = getValues({ p: "getProfiles", type: "public" });
    for (var i = 0; i < allMenuGroup.length; i++) {
        if (allMenuGroup[i].hostname !== undefined) {
            allMenuGroup[i].variable = decodeHtml(allMenuGroup[i].variable);
            hostMenuGroup.push(allMenuGroup[i])
        }
    }
    console.log(hostMenuGroup)
    $(dropdown_id).selectize({
        valueField: 'hostname',
        searchField: ['hostname'],
        maxItems: 1,
        createOnBlur: true,
        options: hostMenuGroup,
        render: renderMenuGroup,
        create: function (input, callback) {
            //first check if its exist, otherwise add as new option 
            var opts = this.options;
            var checkExist = false;
            $.each(opts, function (el) {
                if (opts[el].hostname == input){
                    checkExist = true;
                    return false;
                }
            });
            if (!checkExist){
                callback({ id: input, hostname: input });
            } else {
                this.setValue(input, false);
                return false;
            }
        }
    });
}


$(document).ready(function () {
    $('#profilewizardmodal').on('show.bs.modal', function (event) {
        //Prevent BODY from scrolling when a modal is opened
        $('body').css('overflow', 'hidden');
        $('body').css('position', 'fixed');
        $('body').css('width', '100%');
        var modalId = "#profilewizardmodal";
        var button = $(event.relatedTarget);
        var type = button.attr('type')
        var mode = button.attr('mode')
        console.log(type)
        var wid = button.attr('wid');
        console.log(wid)
        $(modalId).data('wid', wid);
        $('.wizard .nav-tabs li').removeClass("disabled active")        
        $('.wizard .nav-tabs a').first().click()
        $("#pw-step-info-alert").css("display", "none");
        $(this).find('form').trigger('reset');
        $("#pw_job_exec").removeAttr('disabled');
        $(this).find('input[type=checkbox]').attr('checked',false); 
        $(this).find('input[type=radio]').attr('checked',false); 
        selectizePubProfileHostname('#pw-hostname');
        if (mode === 'add') {
            $('.wizard .nav-tabs li').addClass("disabled")
            $('.wizard .nav-tabs li').first().removeClass("disabled").addClass("active");
            $(".profilewizard input[name='profiletype'][value='host']").attr('checked',true); 
            var currentdate = new Date(); 
            var datetime = " on " + currentdate.getDate() + "/" + (currentdate.getMonth()+1)  + "/"  + currentdate.getFullYear();
            var name = "Run Environment" + datetime
            $(modalId).data('name', name);
            $(modalId).data('status', "active");
            saveWizardData('#profilewizardmodal');
        } else if (mode === 'edit') {
            var getWizard = getValues({ p: "getWizard", id: wid });
            if (getWizard){
                if (getWizard[0]){
                    $(modalId).data('name', getWizard[0].name);
                    $(modalId).data('status', getWizard[0].status);
                    var data=decodeHtml(getWizard[0].data);
                    if (IsJsonString(data)) {
                        var json = JSON.parse(data)
                        console.log(json)
                        if (json) {
                            fillFormByName('#profilewizardmodal', 'input, select, textarea', json);
                            if (json.hostname){
                                // check if its filled, otherwise add as new option 
                                if (!$('#pw-hostname')[0].selectize.getValue()) {
                                    $("#pw-hostname")[0].selectize.addOption({
                                        id: json.hostname,
                                        hostname: json.hostname,
                                    });
                                    $("#pw-hostname")[0].selectize.setValue(json.hostname, false);
                                } 
                            }
                            if (json.last_tab_id){
                                $("#profilewizardmodal").find('a[data-toggle="tab"][href="#'+json.last_tab_id+'"]').click();
                            }
                            if (json.disabled_tab_id){
                                for (var i = 0; i < json.disabled_tab_id.length; i++) {
                                    var tabid = json.disabled_tab_id[i];
                                    if (tabid){
                                        $("#profilewizardmodal").find('a[data-toggle="tab"][href="'+tabid+'"]').parent().addClass('disabled');
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } 
        //trigger change or click events on load
        $('.wizard #pw-usesshkeys').change()
        $('.wizard #pw-usesshkeys_check').change()
        $('.wizard .validate-by-checkbox').change()
        //show Profile Alert based on selected value
        showWizardProfileAlert()

    }).on('hidden.bs.modal', function () {
        $('body').css('overflow', 'hidden auto');
        $('body').css('position', 'static');
        loadOngoingWizard()
    })

    $('#profilewizardmodal').on('hide.bs.modal', function (event) {
        saveWizardData('#profilewizardmodal');
        $('#pw-hostname')[0].selectize.destroy();
    });

    $(function () {
        $(document).on('click', '.delete_wizard', function (e) {
            var $target = $(e.target);
            var wid = $target.closest("a").prev().attr("wid")
            if (wid){
                $('#confirmDelWizardModal').off()
                $('#confirmDelWizardModal').data("wid", wid)
                $('#confirmDelWizardModalText').html('Are you sure you want to delete?');
                $('#confirmDelWizardModal').on('click', '.delete', function (event) {
                    var wid = $('#confirmDelWizardModal').data("wid")
                    if (wid) {
                        $.ajax({
                            type: "POST",
                            url: "ajax/ajaxquery.php",
                            data: {
                                id: wid,
                                p: "removeWizard"
                            },
                            async: true,
                            success: function (s) {
                                loadOngoingWizard()
                                $('#confirmDelWizardModal').modal('hide');
                            },
                            error: function (errorThrown) {
                                alert("Error: " + errorThrown);
                            }
                        });
                    }
                });
                $('#confirmDelWizardModal').modal("show");
            }
        });
    });


    $('.wizard #pw-usesshkeys').on('change', function (e) {
        if ($(this).val() == "yes"){
            $("#pw-usesshkeys_checkdiv").css("display","inline")
        } else {
            $("#pw-usesshkeys_checkdiv").css("display","none")
        }
    });
    $('.wizard #pw-usesshkeys_check').on('change', function (e) {
        if ($(this).is(':checked')){
            $("#pw-validatepublickey").css("display","inline")
        } else {
            $("#pw-validatepublickey").css("display","none")
        }
    });

    $('.wizard #pw_next_exec').on('change', function (e) {
        var next_exec= $(this).val()
        $("#pw_job_exec").removeAttr('disabled');
        if (next_exec == "lsf" || next_exec == "sge" || next_exec == "slurm"){
            $("#pw_job_exec").val(next_exec);
            $("#pw_job_exec").attr('disabled', "disabled");
        } 
    });

    $(".profilewizard input[name='profiletype']").on('change', function (e) {
        showWizardProfileAlert()
    });

    $(".profilewizard .validate-by-checkbox").on('change', function (e) {
        console.log($(this).next())
        console.log($(this).next().is(":checked"))
        if ($(this).next().is(":checked")){
            if ($(this).is(":checked")){
                $(this).next().next().css("display", "block")
                $(this).next().next().next().css("display", "none")
            } else {
                $(this).next().next().css("display", "none")
                $(this).next().next().next().css("display", "block")
            } 
        } else {
            $(this).next().next().css("display", "none")
            $(this).next().next().next().css("display", "none")
        }

    });



    $(".profilewizard .validate-pw-button").on('click', function (e) {
        var butID = $(this).attr("id");
        var validateCheckbox = $(this).next();
        var queryCheckbox = $(this).next().next();
        //pw-validate-ssh-key
        var validateSSH = validatePW(butID)
        console.log(validateSSH)
        //first set next() checkbox to save query info as completed
        queryCheckbox.attr('checked', true);
        validateCheckbox.attr('checked', false);
        if (validateSSH){
            if (validateSSH["validation"]){
                if (validateSSH["validation"] == "success"){
                    validateCheckbox.attr('checked', true);
                }
            }
        } 
        validateCheckbox.change();
        saveWizardData('#profilewizardmodal');
    });

    //Initialize tooltips
    $('.nav-tabs > li a[title]').tooltip();
    //Wizard
    $('.wizard a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        var $target = $(e.target);
        var href = $target.attr("href")
        if ($target.parent().hasClass('disabled')) {
            return false;
        }
        //content of #pw-step-connectiontype tab
        if (href == "#pw-step-connectiontype"){
            var profiletype = $(".profilewizard input[name='profiletype']:checked").val()
            if (profiletype == "host"){
                var sshID = $('#pw-sshkeyid').val();
                var pubKeyCheck = $('#pw-pubkey').val();
                if (!sshID){
                    // create new key or use "Auto-Generated Keys" if exist 
                    var genKeys = getValues({ p: "getAutoPublicKey" });
                    if (genKeys) {
                        if (genKeys.$keyPub && genKeys.id ) {
                            $('#pw-pubkey').val($.trim(genKeys.$keyPub));
                            $('#pw-sshkeyid').val($.trim(genKeys.id));
                        } 
                    } 
                } else {
                    // load pubkey if sshkeyid is known
                    if (!pubKeyCheck){
                        var pubKeyData = getValues({ p: "getSSH", id: sshID })[0];
                        if (pubKeyData) {
                            if (pubKeyData.pubkey ) {
                                $('#pw-pubkey').val($.trim(pubKeyData.pubkey));
                            } 
                        } 
                    }
                }
            } else if (profiletype == "amazon" || profiletype == "google" ){
                $("pw-step-connectiontype-mainp").html()
            }
        } else if (href == "#pw-step-settings"){
            var profiletype = $(".profilewizard input[name='profiletype']:checked").val()
            var useSSH = $("#pw-usesshkeys").val()
            if (profiletype == "host"){
                if (useSSH == "yes"){
                    $("#pw-step-settings-software").css("display", "block")
                } else {
                    $("#pw-step-settings-software").css("display", "none")
                }
            }
        } else if (href == "#pw-step-complete"){
            var profiletype = $(".profilewizard input[name='profiletype']:checked").val()
            if (profiletype == "host"){
                $('#pw-step-complete-header').html("Complete");
                $('#pw-step-complete-text').html('Your run environment successfully added into your account and you can start using publicly available pipelines. Please check our tutorial: <a href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html#running-pipelines" class="text-aqua" target="_blank">Running Pipelines</a> for details on how to start a new run. </br></br> Note: You can always access your run environments in the <a href="index.php?np=4" class="text-aqua" target="_blank">Profiles </a> section by clicking on its icon <a href="index.php?np=4" data-toggle="tooltip" data-placement="bottom" target="_blank" title="Profiles"><i class="glyphicon glyphicon-user"></i></a> located on the upper right corner of the website.');
                $('[data-toggle="tooltip"]').tooltip();
                var username = $.trim($("#pw-username").val());
                var hostname = $.trim($("#pw-hostname").val());
                if (hostname){
                    var name = "Run Environment for "+hostname;
                } else {
                    var currentdate = new Date(); 
                    var datetime = " on " + currentdate.getDate() + "/" + (currentdate.getMonth()+1)  + "/"  + currentdate.getFullYear();
                    var name = "Run Environment" + datetime
                    }
                var saveprofilecluster_id = $("#pw-saveprofilecluster_id").val();
                var executor = $("#pw_next_exec").val();
                var executor_job = $("#pw_job_exec").val();
                //combine commands
                var java_cmd =  $.trim($("#pw-javacmd").val());
                var next_cmd =  $.trim($("#pw-nextflowpath").val());
                var next_path = "";
                if (next_cmd){
                    if (next_cmd.substring(0, 1) == "/"){
                        next_path = next_cmd;
                        next_cmd = "";
                    } 
                }
                var docker_cmd =  $.trim($("#pw-dockercmd").val());
                var singularity_cmd =  $.trim($("#pw-singularitycmd").val());
                var cmdAr = ["source /etc/profile", java_cmd, next_cmd, docker_cmd, singularity_cmd]
                var cmd = combineLinuxCmd(cmdAr);

                var port = $.trim($("#pw-sshport").val());
                var ssh_id =  $("#pw-sshkeyid").val();
                var variable = $.trim($("#pw-downdir").val());
                if (variable){
                    variable = 'params.DOWNDIR = "'+variable+'"';
                }
                var profileClusterObj = { 
                    p: "saveProfileCluster", 
                    id: saveprofilecluster_id,
                    name: name,
                    executor: executor,
                    cmd: cmd,
                    next_path: next_path, 
                    next_memory: "10", 
                    next_queue: "", 
                    next_time: "", 
                    next_cpu: "1", 
                    next_clu_opt: "", 
                    executor_job: executor_job, 
                    job_memory: "20", 
                    job_queue: "", 
                    job_time: "", 
                    job_cpu: "1", 
                    job_clu_opt: "", 
                    username: username, 
                    hostname: hostname, 
                    port: port, 
                    singu_cache: "", 
                    variable: variable, 
                    ssh_id: ssh_id, 
                    group_id: "", 
                    auto_workdir: "", 
                    perms: "3"
                };
                var publicProfile = checkPublicProfileSelected();
                if (publicProfile){
                    profileClusterObj.executor = publicProfile.executor;
                    profileClusterObj.cmd = publicProfile.cmd;
                    profileClusterObj.next_path = publicProfile.next_path;
                    profileClusterObj.next_memory = publicProfile.next_memory;
                    profileClusterObj.next_queue = publicProfile.next_queue;
                    profileClusterObj.next_time = publicProfile.next_time;
                    profileClusterObj.next_cpu = publicProfile.next_cpu;
                    profileClusterObj.next_clu_opt = publicProfile.next_clu_opt;
                    profileClusterObj.executor_job = publicProfile.executor_job;
                    profileClusterObj.job_memory = publicProfile.job_memory;
                    profileClusterObj.job_queue = publicProfile.job_queue;
                    profileClusterObj.job_time = publicProfile.job_time;
                    profileClusterObj.job_cpu = publicProfile.job_cpu;
                    profileClusterObj.job_clu_opt = publicProfile.job_clu_opt;
                    profileClusterObj.singu_cache = publicProfile.singu_cache;
                    profileClusterObj.variable = publicProfile.variable;
                    profileClusterObj.auto_workdir = publicProfile.auto_workdir;
                }
                console.log(profileClusterObj)
                var profileCluster = getValues(profileClusterObj);
                console.log(profileCluster)
                if (profileCluster){
                    if (profileCluster.id){
                        $("#pw-saveprofilecluster_id").val(profileCluster.id);
                        saveWizardData('#profilewizardmodal');
                    }
                }
                $('#profilewizardmodal').data('status', "success");
            } else if (profiletype == "amazon"){
                $('#profilewizardmodal').data('status', "success");
                $('#pw-step-complete-header').html("Quick Start Guide");
                $('#pw-step-complete-text').html('Please click and follow our <a href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/profile.html#b-defining-amazon-profile" class="text-aqua" target="_blank">Amazon Profile Guide</a> to create your run enironment. If you have any issues/questions about creating profiles please contact us on: support@dolphinnext.com');
            } else if (profiletype == "google"){
                $('#profilewizardmodal').data('status', "success");
                $('#pw-step-complete-header').html("Quick Start Guide");
                $('#pw-step-complete-text').html('Please click and follow our <a href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/profile.html#c-defining-google-profile" class="text-aqua" target="_blank">Google Profile Guide</a> to create your run enironment. If you have any issues/questions about creating profiles please contact us on: support@dolphinnext.com');
            } else if (profiletype == "test"){
                $('#profilewizardmodal').data('status', "success");
                $('#pw-step-complete-header').html("Complete");
                //add into demo group
                var saveTestGroup = getValues({ "p": "saveTestGroup" });
                var start = "";
                var rest = 'Now you can start a new run and choose <b>MGHPCC cluster</b> in the run page. </br></br> You can check our <a href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html#running-pipelines" class="text-aqua" target="_blank">Run Guide</a> for tutorial videos.';
                var log = "";
                if (saveTestGroup){
                    if (saveTestGroup["id"]){
                        //inserted
                        start = 'We have successfully defined <b>MGHPCC cluster</b> into your run environments. ';
                    } else if (saveTestGroup[0]){
                        //already inserted
                        start = "<b>MGHPCC cluster</b> already defined into your run environments. ";
                    } else {
                        //failed
                        start = "An error occurred, please try again later.";
                        rest = "";
                    }
                    log = start+rest;

                }
                $('#pw-step-complete-text').html(log);
            }

        }
    });
    $(".next-step").click(function (e) {
        var $active = $('.wizard .nav-tabs li.active');
        var $activeTabId = $('.wizard .tab-pane.active').attr("id");
        if ($activeTabId == "pw-step-profiletype"){
            var profiletype = $(".profilewizard input[name='profiletype']:checked").val()
            if (!profiletype){
                showInfoModal("#infoMod","#infoModText", "Please choose one of the profile types to continue.");
                return;
            }
            if (profiletype == "test" || profiletype == "amazon" || profiletype == "google"){
                var $tabs = $('.wizard .nav-tabs li');
                $tabs.last().removeClass('disabled');
                lastTab($tabs);
                return;
            }
        } else if ($activeTabId == "pw-step-connectiontype"){
            var profiletype = $(".profilewizard input[name='profiletype']:checked").val()
            var useSSH = $("#pw-usesshkeys").val()
            var useSSHcheckbox = $("#pw-usesshkeys_check").is(":checked")
            var useSSHvalidate = $("#pw-validate-ssh-key").next().is(":checked")
            if (profiletype == "host" && useSSH == "yes"){
                var infoText = "";
                if (!useSSHcheckbox){
                    infoText = "Please confirm our Terms & Privacy Policy by clicking checkbox."
                }
                if (!useSSHvalidate){
                    infoText = "Please validate your SSH Keys by clicking 'Validate SSH Keys' button."
                }
                if (infoText){
                    showInfoModal("#infoMod","#infoModText", infoText);
                    return;
                } 
                //check if public profile settings are available in the #pw-hostname
                var publicProfile = checkPublicProfileSelected();
                if (publicProfile){
                    //autofill other options, and go to last section
                    var $tabs = $('.wizard .nav-tabs li');
                    $tabs.last().removeClass('disabled');
                    lastTab($tabs);
                    return;
                }
            }

        } else if ($activeTabId == "pw-step-settings"){
            var profiletype = $(".profilewizard input[name='profiletype']:checked").val()
            var useSSH = $("#pw-usesshkeys").val()
            var validateJava = $("#pw-validate-java").next().is(":checked")
            var validateNextflow = $("#pw-validate-nextflow").next().is(":checked")
            var validateDocker = $("#pw-validate-docker").next().is(":checked")
            var validateSingularity = $("#pw-validate-singularity").next().is(":checked")
            if (profiletype == "host" && useSSH == "yes"){
                var infoText = "";
                if (!validateJava || !validateNextflow){
                    var infoText = "Please validate ";
                    if (!validateJava && !validateNextflow){
                        infoText += "Java and Nextflow " ;
                    } else if (!validateNextflow){
                        infoText += "Nextflow ";
                    } else if (!validateJava){
                        infoText += "Java ";
                    }
                    infoText += "in your machine to finalize your run environment.";
                }
                if (infoText){
                    showInfoModal("#infoMod","#infoModText", infoText);
                    return;
                }

            }
        } 
        $active.next().removeClass('disabled');
        nextTab($active); 
    });

    $(".prev-step").click(function (e) {
        var $active = $('.wizard .nav-tabs li.active');
        prevTab($active);
    });
});

