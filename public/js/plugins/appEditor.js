//################################
// --appEditor jquery plugin --
//################################

(function($) {
    $.fn.appEditor = function(options) {
        var settings = $.extend({
            // default values.
            height: "500px",
            heightIconBar: "35px"
        }, options);
        var elems = $(this);
        elems.css("width", "100%")
        elems.css("height", "100%")
        var elemsID = $(this).attr("id");
        var getEditorIconDiv = function() {
            var appeditorsaveas = "";
            var appeditorsave = "";
            var appeditorsett = "";
            var appeditorlog = "";
            if (settings.ajax.editable) {
                appeditorsett = `<li role="presentation"><a class="appeditorsett-${elemsID}" data-toggle="tooltip" data-placement="bottom" data-original-title="Launch App"><i style="font-size: 18px;" class="fa fa-play"></i></a></li>`;
                appeditorsaveas = `<li role="presentation"><a class="appeditorsaveas-${elemsID}" data-toggle="tooltip" data-placement="bottom" data-original-title="Save As"><span class="glyphicon-stack"><i class="fa fa-pencil glyphicon-stack-3x"></i><i style="font-size: 18px;" class="fa fa-save glyphicon-stack-1x"></i></span></a></li>`;
                appeditorsave = `<li role="presentation"><a class="appeditorsave-${elemsID}" data-toggle="tooltip" data-placement="bottom" data-original-title="Save"><i style="font-size: 18px;" class="fa fa-save"></i></a></li>`;
                appeditorlog = `<li role="presentation"><a class="appeditorlog-${elemsID}" data-toggle="tooltip" data-placement="bottom" data-original-title="App Logs"><i style="font-size: 18px;" class="fa fa-file-text-o"></i></a></li>`;
            }
            return `<ul style="float:inherit" class="nav nav-pills appeditor">` + appeditorsaveas + appeditorsave + appeditorlog + appeditorsett + `</ul>`
        }
        var getReportIconDiv = function() {
            return `<ul style="float:inherit"  class="nav nav-pills appeditor">
<li role="presentation"><a class="appeditorlink-${elemsID}" data-toggle="tooltip" data-placement="bottom" data-original-title="Open Report in a New Window"><i style="font-size: 18px;" class="fa fa-external-link"></i></a></li>
<li role="presentation"><a class="appeditorfull-${elemsID}" data-toggle="tooltip" data-placement="bottom" data-original-title="Toogle Full Screen"><i style="font-size: 18px;" class="fa fa-expand"></i></a></li>
</ul>`

        }
        var renameModal = `
<div id="appRename-${elemsID}" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title">Save</h4>
</div>
<div class="modal-body">
<form style="padding-right:10px;" class="form-horizontal">
<div class="form-group">
<label class="col-sm-3 control-label">File Name</label>
<div class="col-sm-9">
<input type="text" class="form-control appfilename">
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
<button type="button" class="btn btn-primary save" data-dismiss="modal">Save</button>
</div>
</div>
</div>
</div>`;

        var infoModal = `
<div id="appInfo-${elemsID}" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog modal-xl" role="document">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title">Info</h4>
</div>
<div class="modal-body">
    <textarea readonly rows="10" style="overflow-y: scroll; min-width: 100%; max-width: 100%; border-color:lightgrey;" id="appInfoText-${elemsID}"></textarea>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>
</div>
</div>
</div>
</div>`;
        var settingsModal = ` 
        <div id="appSett-${elemsID}" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Settings</h4>
                </div>
                <div class="modal-body">
                    <form style="padding-right:10px;" class="form-horizontal">
                        <div class="form-group">
                            <div class="col-sm-5 control-label"><label>App Name</label></div>
                            <div class="col-sm-7">
                                <select class="form-control" id="pubWebApp-${elemsID}" name="pubWebApp">
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-5 control-label"><label>Memory</label></div>
                            <div class="col-sm-7">
                                <select class="form-control" name="memory">
                                    <option value="10">10GB</option>
                                    <option value="30">30GB</option>
                                    <option value="100">100GB</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-5 control-label"><label>CPU</label></div>
                            <div class="col-sm-7">
                                <input class="form-control" type="number" name="cpu" min="1" max="10" value="1"></input>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">close</button>
                    <button type="button" class="btn btn-success appeditorlaunch-${elemsID}" data-dismiss="modal">Launch App</button>
                </div>
            </div>
        </div>
    </div>`;


        var getDiv = function(settings, outputHtml) {
            if (!outputHtml || outputHtml == null) {
                outputHtml = ""
            } else {
                outputHtml = 'src="' + outputHtml + '"';
            }
            var log = '<div id="' + elemsID + '-log" style="position: absolute; padding-left:10px; padding-top:5px; height:' + settings.heightIconBar + '; width:20%;"></div>';
            var progressBar = '<div id="' + elemsID + '-reportProgress" style="position: absolute; background-color:lightgrey; height:' + settings.heightIconBar + '; width:0;"></div>';
            var editoriconBar = '<div id="' + elemsID + '-editoricons" style="float:right; height:' + settings.heightIconBar + '; width:' + settings.editorWidth + ';">' + getEditorIconDiv() + '</div>';
            var reporticonBar = '<div id="' + elemsID + '-reporticons" style="float:right; height:' + settings.heightIconBar + '; width:' + settings.reportWidth + ';">' + progressBar + log + getReportIconDiv() + '</div>';
            var editorDiv = '<div id="' + elemsID + '-editor" style="clear:both; float:left; height:' + settings.height + '; width:' + settings.editorWidth + ';"></div>';
            var reportDiv = '<div id="' + elemsID + '-report" style="float:left; height:' + settings.height + '; width:' + settings.reportWidth + ';"><iframe style="width:100%; height:100%"' + outputHtml + '></iframe></div>';
            return reporticonBar + editoriconBar + editorDiv + reportDiv
        }
        var createEditor = function(settings) {
            var editorId = elemsID + "-editor";
            window[editorId] = ace.edit(editorId);
            window[editorId].setTheme("ace/theme/" + settings.theme);
            window[editorId].getSession().setMode("ace/mode/r");
            window[editorId].setFontSize("14px");
            window[editorId].$blockScrolling = Infinity;
            window[editorId].setValue(settings.ajax.text);
        }
        var createModal = function() {
            if (document.getElementById(`appSett-${elemsID}`) === null) {
                $('body').append(settingsModal);
            }
            if (document.getElementById(`appRename-${elemsID}`) === null) {
                $('body').append(renameModal);
            }
            if (document.getElementById(`appInfo-${elemsID}`) === null) {
                $('body').append(infoModal);
            }
            var allApps = getValues({ p: "getContainers" });
            $(`#pubWebApp-${elemsID}`).selectize({
                valueField: 'id',
                searchField: 'name',
                placeholder: "Choose App...",
                options: allApps,
                render: {
                    option: function(data, escape) {
                        return '<div class="option">' +
                            '<span class="title"><b>' + escape(data.name) + '</b></span>' +
                            '<i> ' + escape(data.image_name) + '</i>' +
                            '<span class="url">' + escape(data.summary) + '</span>' +
                            '</div>';
                    },
                    item: function(data, escape) {
                        return '<div class="item" data-value="' + escape(data.id) + '">' + escape(data.name) + '</div>';
                    }
                }
            })
            if (options.pubWebApp && options.pubWebApp.app) {
                $(`#pubWebApp-${elemsID}`)[0].selectize.setValue(options.pubWebApp.app, false);
            }
        }

        var progress = function(value) {
            var width; //percent
            var rate = 5;
            var n = 0;
            var bar = $("#" + elemsID + '-reportProgress');
            var maxWidthPx = bar.parent().width();
            if (value) {
                width = value;
                if (width == 100) {
                    setTimeout(function() { bar.width(0) }, 300);
                }
                frame()
            } else {
                width = Math.ceil(bar.width() / maxWidthPx * 100); //current percent
            }
            if (!window[elemsID + '_progress']) {
                window[elemsID + '_progress'] = setInterval(frame, 50);
            }

            function frame() {
                if (width >= 100) {
                    clearInterval(window[elemsID + '_progress']);
                    window[elemsID + '_progress'] = null;
                    bar.width(bar.parent().width() + "px")
                } else {
                    if (width < 70) {
                        n += rate;
                    } else if (width >= 70 && width < 90) {
                        n += rate / 6;
                    } else {
                        n += rate / 100;
                    }
                    width = Math.sqrt(n) / Math.sqrt(100) * 20; //logaritmic percent
                    var widthPx = Math.ceil(width * bar.parent().width() / 100);
                    bar.width(widthPx + "px")
                }
            }
        }



        var getFileName = function() {
            var res = { filename: "", rest: "" };
            var filePath = elems.attr("filePath")
            var split = filePath.split("/")
            res.filename = split[split.length - 1]
            res.rest = split.slice(0, -1).join('/');
            return res
        }
        var saveCommand = function(editorId, filename) {
            var obj = getFileName();
            var newPath = obj.rest + "/" + filename
            var text = window[editorId].getValue();
            text = encodeURIComponent(text);
            var run_log_uuid = $("#runVerLog").val();
            var saveData = getValues({ p: "saveFileContent", text: text, uuid: run_log_uuid, filename: "pubweb/" + newPath });
            return saveData
        }

        var saveRmd = function(editorId, type) {
            var obj = getFileName();
            var newPath = obj.rest + "/" + obj.filename
                //check if readonly
            if (elems.attr("read_only") || type == "saveas") {
                //ask new name  
                $(`#appRename-${elemsID}`).attr("filename", obj.filename)
                $(`#appRename-${elemsID}`).modal("show");
            } else {
                var saveData = saveCommand(editorId, obj.filename)
                if (saveData) {
                    updateLogText("All changes saved.", "clean")
                }
            }
        }

        var openBlankPage = function(elemsID) {
            var reportId = elemsID + "-report";
            var iframe = $("#" + reportId + "> iframe")
            var url = iframe.attr("src")
            var w = window.open();
            w.location = url;
        }

        var toogleFullSize = function(editorId, type) {
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
                $("#" + elemsID + '-editor').css("height", $(window).height() - settings.heightIconBar.substring(0, settings.heightIconBar.length - 2))
                $("#" + elemsID + '-report').css("height", $(window).height() - settings.heightIconBar.substring(0, settings.heightIconBar.length - 2))
                window[elemsID + '-editor'].resize();
            } else {
                var newCSS = elems.data("oldCSS");
                $("#" + elemsID + '-editor').css("height", settings.height)
                $("#" + elemsID + '-report').css("height", settings.height)
                window[elemsID + '-editor'].resize();
            }
            //apply css obj
            $.each(newCSS, function(el) {
                elems.css(el, newCSS[el])
            });
        }

        var ajaxRq = function(settings, data) {
            var ret = null;
            $.ajax({
                type: "POST",
                url: settings.ajax.url,
                data: data,
                async: false,
                cache: false,
                success: function(results) {
                    ret = results;
                },
                error: function(jqXHR, exception) {
                    console.log("#Error:")
                    console.log(jqXHR.status)
                    console.log(exception)
                    updateLogText("Error occurred.")
                    progress(100)
                }
            });
            return ret
        }

        var callback = function(orgPath) {
            if (orgPath) {
                updateLogText("Done.", "clean")
                progress(100)
                var reportId = elemsID + "-report";
                var iframe = $("#" + reportId + "> iframe")

                if (orgPath.includes("localhost") || orgPath.includes("127.0.0.1")) {
                    // iframe.attr("src", orgPath)
                    window.open(orgPath, "myWindow", 'width=1000,height=800')
                        // var w = window.open();
                        // w.location = orgPath;
                } else {
                    if (iframe && iframe.attr("src")) {
                        iframe[0].contentWindow.location.reload(true)
                    } else {
                        iframe.attr("src", orgPath)
                    }
                }
            } else {
                updateLogText("Error Occured.")
                progress(100)
            }

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

        var downloadText = function(text, filename) {
            var element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
            element.setAttribute('download', filename);
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        }

        var update = function(editorId) {
            var text = window[editorId].getValue();
            callData(text, settings, "report", callback);
        }
        var timeoutId = 0;
        var autoUpdate = function(editorId) {
            if (timeoutId) clearTimeout(timeoutId);
            timeoutId = setTimeout(function() { update(editorId) }, 2000);
        }

        var checkAutoUpdateOut = function(editorId) {
            if ($('input.aUpdateOut').is(":checked")) {
                $('#' + editorId).keyup(function() {
                    autoUpdate(editorId);
                });
            } else {
                $('#' + editorId).off("keyup");
            }
        }
        var checkAutoSave = function(editorId) {
            if ($('input.aSave').is(":checked")) {
                window['interval_aSave_' + editorId] = setInterval(function() {
                    saveRmd(editorId, "autosave");
                }, 30000);
            } else {
                if (window['interval_aSave_' + editorId]) {
                    clearInterval(window['interval_aSave_' + editorId])
                }
            }
        }

        var eventHandler = function(settings) {
            var editorId = elemsID + "-editor";

            $(function() {
                $('[data-toggle="tooltip"]').tooltip();
            });
            $(function() {

                $(document).on("show.bs.modal", `#appSett-${elemsID}`, function(e) {
                    // 1. check if app is exist in db. and fill the modal
                    console.log(elemsID);
                })

                $(`#appSett-${elemsID}`).on('click', `.appeditorlaunch-${elemsID}`, async function(event) {
                    update(editorId);
                });
            });
            $(function() {
                $(`a.appeditorsave-${elemsID}`).on('click', function(event) {
                    if ($(this).parents("#" + elemsID).length) {
                        saveRmd(editorId, "save")
                    }
                });
                $(`a.appeditorsaveas-${elemsID}`).on('click', function(event) {
                    if ($(this).parents("#" + elemsID).length) {
                        saveRmd(editorId, "saveas")
                    }
                });
                $(`a.appeditorsett-${elemsID}`).on('click', function(event) {
                    $(`.appeditorlaunch-${elemsID}`).data("elemsID", elemsID)
                    $(`#appSett-${elemsID}`).modal("show");
                });
                $(`a.appeditorlog-${elemsID}`).on('click', function(event) {
                    $(`#appInfo-${elemsID}`).modal("show");
                });


                $(`a.appeditorfull-${elemsID}`).on('click', function(event) {
                    if ($(this).parents("#" + elemsID).length) {
                        var iconClass = $(this).children().attr("class");
                        if (iconClass == "fa fa-expand") {
                            $(this).children().attr("class", "fa fa-compress")
                            toogleFullSize(editorId, "expand");
                        } else {
                            $(this).children().attr("class", "fa fa-expand")
                            toogleFullSize(editorId, "compress");
                        }
                    }
                });
                $(`a.appeditorlink-${elemsID}`).on('click', function(event) {
                    if ($(this).parents("#" + elemsID).length) {
                        openBlankPage(elemsID)
                    }
                });


            });
            $(function() {
                $(`#appRename-${elemsID}`).on('show.bs.modal', function(event) {
                    var divOldName = elems.attr("filename")
                    var modalOldName = $(`#appRename-${elemsID}`).attr("filename")
                    if (divOldName === modalOldName) {
                        if ($(`#appRename-${elemsID}`).find("input.appfilename")) {
                            $($(`#appRename-${elemsID}`).find("input.appfilename")[0]).val(divOldName)
                        }
                    }
                });
                $(`#appRename-${elemsID}`).on('click', '.save', function(event) {
                    var divOldName = elems.attr("filename")
                    var divOldDir = elems.attr("dir")
                    var modalOldName = $(`#appRename-${elemsID}`).attr("filename")
                    if (divOldName === modalOldName) {
                        if ($(`#appRename-${elemsID}`).find("input.appfilename")) {
                            var newName = $($(`#appRename-${elemsID}`).find("input.appfilename")[0]).val();
                            var saveData = saveCommand(editorId, newName)
                                // remove "-editor" + divOldName.length
                            var dynamicRowID = editorId.substring(0, editorId.length - (1 + "-editor".length + divOldName.length));
                            // editor id: g-161_appdown_1_rmd-editor
                            // g-161_appdown
                            $("#reportRows").dynamicRows("fnRefresh", { type: "columnsBody", id: dynamicRowID })
                            var newFilepath = divOldDir + newName;
                            var allfiles = elems.closest("div.panel-body").find("a[filepath]")
                            for (var i = 0; i < allfiles.length; i++) {
                                var menuFile2 = $(allfiles[i]).attr("filepath")
                                if (menuFile2 == newFilepath) {
                                    $(allfiles[i]).trigger("click");
                                }
                            }
                            if (saveData) {
                                $(`#appRename-${elemsID}`).modal("hide");
                            }
                        }
                    }
                });
            });
        }


        var checkUrl = function(url) {
            var ret = null;
            $.ajax({
                url: url,
                type: 'GET',
                async: false,
                cache: false,
                error: function() {
                    ret = false;
                },
                success: function() {
                    ret = true;
                }
            });
            return ret;
        }

        var updateLogText = function(text, type) {
            $("#" + elemsID + '-log').text(text);
            if (type == "clean") {
                setTimeout(function() {
                    if ($("#" + elemsID + '-log').text() == text) {
                        $("#" + elemsID + '-log').text("");
                    }
                }, 2000);
            }
        }

        var getUrlContent = function(url) {
            return $.get(url);
        }

        var getUrl = function(settings, type, callback, pid) {
            if (window[elemsID + type]) {
                return; // Don't allow click if already running.
            }
            var format = ""
            var serverLogPath = settings.ajax.pubWebPath + "/" + settings.ajax.uuid + "/pubweb/" + `.${pid}_jupyter_server.log`
            var containerLogPath = settings.ajax.pubWebPath + "/" + settings.ajax.uuid + "/pubweb/" + settings.ajax.dir + "/.app/" + settings.ajax.filename + ".log" + pid
            var containerErrPath = settings.ajax.pubWebPath + "/" + settings.ajax.uuid + "/pubweb/" + settings.ajax.dir + "/.app/" + settings.ajax.filename + ".err" + pid
            window[elemsID + type] = setInterval(function() {
                var checkExistUrl = checkUrl(containerLogPath)
                console.log(`checkExistUrl: ${containerLogPath} ${checkExistUrl}`)
                if (!checkExistUrl) {
                    var checkExistError = checkUrl(containerErrPath)
                    if (checkExistError) {
                        getUrlContent(containerErrPath).success(function(data) {
                            if (data) {
                                if (!$(`#appInfo-${elemsID}`).hasClass('in')) {
                                    $(`#appInfoText-${elemsID}`).text(data)
                                    $(`#appInfo-${elemsID}`).modal("show");
                                }
                            }

                        });
                        updateLogText("Error occurred.")
                        progress(100)
                        if (window[elemsID + type]) {
                            clearInterval(window[elemsID + type]);
                            window[elemsID + type] = null;
                        }
                    }
                    return ""
                } else {
                    var checkServerURL = checkUrl(serverLogPath)
                    if (checkServerURL) {
                        getUrlContent(serverLogPath).success(function(data) {
                            if (data) {
                                window.wdata = data
                                console.log(window.wdata)
                                var urlRegex = /(http?:\/\/127\.0\.0\.1[^ ]*)[\n]/;
                                if (data.match(urlRegex) && data.match(urlRegex)[1]) {
                                    var serverURL = data.match(urlRegex)[1];
                                    console.log(serverURL)
                                    if (window[elemsID + type]) {
                                        clearInterval(window[elemsID + type]);
                                        window[elemsID + type] = null;
                                        return callback(serverURL)
                                    }
                                }



                            }

                        })

                    }


                }
            }, 2000);

        }
        var initialUrlCheck = function(settings, type) {
            var orgPath = settings.ajax.pubWebPath + "/" + settings.ajax.uuid + "/pubweb/" + settings.ajax.dir + "/.app/" + settings.ajax.filename
            var checkExistUrl = checkUrl(orgPath)
            if (checkExistUrl) {
                var tmpPath = ""
                var pid = ""
                callback(settings, tmpPath, orgPath, pid, type)
            }
            return checkExistUrl
        }

        var callData = function(editText, settings, type, callback) {
            if (window[elemsID + type]) {
                return; // Don't allow click if already running.
            }
            progress()
            updateLogText("Preparing..")
            var editTextSend = encodeURIComponent(JSON.stringify(editText));
            var container_id = $(`#pubWebApp-${elemsID}`).val()

            var formObj = {};
            var stop = "";
            [formObj, stop] = createFormObj($(`#appSett-${elemsID}`).find("input, select"), []);
            formObj.p = "callApp";
            formObj.location = elemsID;
            formObj.type = type;
            formObj.text = editTextSend;
            formObj.uuid = settings.ajax.uuid;
            formObj.dir = settings.ajax.dir;
            formObj.filename = settings.ajax.filename;
            formObj.container_id = container_id;
            console.log(formObj)

            var ret = null
            $.ajax({
                type: "POST",
                url: settings.ajax.url,
                data: formObj,
                async: false,
                cache: false,
                success: function(results) {
                    console.log(results)
                    var pid = results;
                    if (pid) {
                        getUrl(settings, type, callback, pid)
                    }
                },
                error: function(jqXHR, exception) {
                    console.log("#Error:")
                    console.log(jqXHR.status)
                    console.log(exception)
                    updateLogText("Error occurred.")
                    progress(100)
                }
            });
            if (!ret) ret = "";
            return ret

        }
        elems.append(getDiv(settings, ""));
        // var initialFileCheck = initialUrlCheck(settings, "rmdtext")
        // if (!initialFileCheck) {
        //     callData(settings.ajax.text, settings, "report", callback);
        // }
        createEditor(settings)
        createModal()
        eventHandler(settings);
        return this;
    };
}(jQuery));