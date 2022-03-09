function loadAppDetails(app_id) {
    var data = { id: app_id, "p": 'getContainers' };
    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: data,
        async: true,
        success: function(s) {
            console.log(s)
            let fileData = [{ "filename": "Dockerfile", "text": "" }]
            if (s[0] && s[0].files) {
                let files = s[0].files
                fileData = []
                Object.keys(files).forEach((k, i) => {
                    fileData.push({ "filename": k, "text": files[k] })
                });
            }
            $("#containerfiles").textEditor({
                ajax: {
                    data: fileData
                },
                backgroundcolorenter: "#ced9e3",
                backgroundcolorleave: "#ECF0F4",
                height: "600px",
                language: ["markdown", "dockerfile"]
            });

            $('#app-title').val(decodeHtml(s[0].name));
            $('#ownUserName').text(s[0].username);
            $('#projectSum').val(decodeHtml(s[0].summary));

            $('#datecreatedPj').text(s[0].date_created);
            $('#lasteditedPj').text(s[0].date_modified);
            fillFormByName('#appForm', 'input, select, textarea', s[0]);

            resizeForText.call($inputText, decodeHtml(s[0].name));
        },
        error: function(errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
};

function saveAppIcon() {
    var projectSummary = encodeURIComponent($('#projectSum').val());
    var app_id = $('#app-title').attr('appid');
    var app_name = $('#app-title').val();
    var formValues = $("#appForm").find('input,select, textarea');
    var formObj = {};
    var stop = "";
    [formObj, stop] = createFormObj(formValues, [])
    formObj.name = app_name
    formObj.id = app_id
    formObj.summary = projectSummary
    formObj.p = "saveContainer"
    formObj.files = combineTextEditor('containerfiles')
    console.log(formObj.files)

    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: formObj,
        async: true,
        success: function(s) {
            toastr.success("Changes saved.");
        },
        error: function(errorThrown) {
            toastr.error("Error occured.");
        }
    });
}

function combineTextEditor(divID) {
    var ret = {};
    var liAr = $("#fileListDiv_" + divID).find("li");
    for (var i = 0; i < liAr.length; i++) {
        var filename = $(liAr[i]).attr("id");
        var editorID = $(liAr[i]).attr("editorID");
        if (editorID && filename) {
            filename = filename.trim()
            var script = window[editorID].getValue();
            ret[filename] = script
        }
    }
    return ret
}

$(document).ready(async function() {

    var app_id = $('#app-title').attr('appid');
    loadAppDetails(app_id);
    var allUserGrp = getValues({ p: "getUserGroups" });
    fillDropdownArrObj(allUserGrp, "id", "name", "#groupSel", true, '<option value="0"  selected>Choose group </option>')

    $(document).on('click', '.deleteApp', function(e) {
        e.preventDefault();
        var container_id = $('#app-title').attr('appid');
        var container_name = $('#app-title').val();
        var text = 'Are you sure you want to delete this app: "' + container_name + '" ?';
        var savedData = ""
        var execFunc = function(savedData) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    p: "removeContainer",
                    'id': container_id,
                    'name': container_name
                },
                async: true,
                success: function(s) {
                    if (s) {
                        if ($.isEmptyObject(s)) {
                            window.location.replace("index.php?np=7");
                        } else {
                            toastr.error("Error occured.");
                        }
                    }

                },
                error: function(errorThrown) {
                    toastr.error("Error occured.");
                }
            });
        }
        showConfirmDeleteModal(text, savedData, execFunc)
    })



});