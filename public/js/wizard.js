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
    console.log(wid)
    var formValues = $(modalId).find('input, select, textarea');
    [formObj, stop] = createFormObj(formValues, ["name"]);
    sendObj.data = encodeURIComponent(JSON.stringify(formObj));
    sendObj.p = "saveWizard";
    sendObj.name = name;
    sendObj.status = status;
    sendObj.id = wid;
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
        $('.wizard .nav-tabs a').first().click()
        $(this).find('form').trigger('reset');
        $(this).find('input[type=checkbox]').attr('checked',false); 
        if (mode === 'add') {
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
                        }
                    }
                }
            }
        } 
        //trigger change or click events on load
        $('.wizard #pw-usesshkeys').change()
        $('.wizard #pw-usesshkeys_check').change()

    }).on('hidden.bs.modal', function () {
        $('body').css('overflow', 'hidden auto');
        $('body').css('position', 'static');
        loadOngoingWizard()
    })

    //    $('#profilewizardmodal').on('click', '.save_wizard', function (e) {
    //        saveWizardData('#profilewizardmodal', "active");
    //    });
    $('#profilewizardmodal').on('hide.bs.modal', function (event) {
        saveWizardData('#profilewizardmodal');
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
            $("#pw-validatepublickey").css("display","none")
        }
    });
    $('.wizard #pw-usesshkeys_check').on('change', function (e) {
        if ($(this).is(':checked')){
            $("#pw-validatepublickey").css("display","inline")
        } else {
            $("#pw-validatepublickey").css("display","none")
        }
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
                //                if (!$('#pw-sshkeyid').val()){
                //                    var genKeys = getValues({ p: "getAutoPublicKey" });
                //                    if (genKeys) {
                //                        if (genKeys.$keyPub && genKeys.id ) {
                //                            $('#pw-pubkey').val($.trim(genKeys.$keyPub));
                //                            $('#pw-sshkeyid').val($.trim(genKeys.id));
                //                        } 
                //                    } 
                //                }

            } else if (profiletype == "amazon"){
                $("pw-step-connectiontype-mainp").html()
            }
        } else if (href == "#pw-step-complete"){
            var profiletype = $(".profilewizard input[name='profiletype']:checked").val()
            if (profiletype == "host" || profiletype == "amazon"){
                $('#profilewizardmodal').data('status', "success");
                $('#pw-step-complete-header').html("Quick Start Guide");
                $('#pw-step-complete-text').html('Please click and follow our <a href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html#creating-profile" class="text-aqua" target="_blank">Profile Guide</a> to create your run enironment. If you have any issues/questions about creating profiles please contact us on: support@dolphinnext.com');
            } else if (profiletype == "test"){
                $('#profilewizardmodal').data('status', "success");
                $('#pw-step-complete-header').html("Complete");
                //add into demo group
                var saveTestGroup = getValues({ "p": "saveTestGroup" });
                var start = "";
                var rest = 'Now you can start a new run and choose <b>MASS cluster</b> in the run page. </br></br> You can check our <a href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/quick.html#running-pipelines" class="text-aqua" target="_blank">Run Guide</a> for tutorial videos.';
                var log = "";
                if (saveTestGroup){
                    if (saveTestGroup["id"]){
                        //inserted
                        start = 'We have successfully defined <b>MASS cluster</b> into your run environments. ';
                    } else if (saveTestGroup[0]){
                        //already inserted
                        start = "<b>MASS cluster</b> already defined into your run environments. ";
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
            if (profiletype == "test" || profiletype == "amazon" || profiletype == "host"){
                var $tabs = $('.wizard .nav-tabs li');
                $tabs.last().removeClass('disabled');
                lastTab($tabs);
                return;
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

