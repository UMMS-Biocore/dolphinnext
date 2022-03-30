//################################
// --rMarkEditor jquery plugin --
//################################

(function($) {
    $.fn.rMarkEditor = function(options) {
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
            var rmarkeditorrun = "";
            var rmarkeditorsaveas = "";
            var rmarkeditorsave = "";
            var rmarkeditorsett = "";
            if (settings.ajax.editable) {
                rmarkeditorrun = `<li role="presentation"><a class="rmarkeditorrun" data-toggle="tooltip" data-placement="bottom" data-original-title="Run Script"><i style="font-size: 18px;" class="fa fa-play"></i></a></li>`;
                rmarkeditorsaveas = `<li role="presentation"><a class="rmarkeditorsaveas" data-toggle="tooltip" data-placement="bottom" data-original-title="Save As"><span class="glyphicon-stack"><i class="fa fa-pencil glyphicon-stack-3x"></i><i style="font-size: 18px;" class="fa fa-save glyphicon-stack-1x"></i></span></a></li>`;
                rmarkeditorsave = `<li role="presentation"><a class="rmarkeditorsave" data-toggle="tooltip" data-placement="bottom" data-original-title="Save"><i style="font-size: 18px;" class="fa fa-save"></i></a></li>`;
                rmarkeditorsett = `<li role="presentation"><a class="rmarkeditorsett" data-toggle="tooltip" data-placement="bottom" data-original-title="Settings"><i style="font-size: 18px;" class="fa fa-gear"></i></a></li>`;
            }
            return `<ul style="float:inherit" class="nav nav-pills rmarkeditor">` + rmarkeditorrun + rmarkeditorsaveas + rmarkeditorsave + rmarkeditorsett + `</ul>`
        }
        var getReportIconDiv = function() {
            return `<ul style="float:inherit"  class="nav nav-pills rmarkeditor">
<li role="presentation"><a class="rmarkeditorlink" data-toggle="tooltip" data-placement="bottom" data-original-title="Open Report in a New Window"><i style="font-size: 18px;" class="fa fa-external-link"></i></a></li>
<li role="presentation"><a class="rmarkeditorfull" data-toggle="tooltip" data-placement="bottom" data-original-title="Toogle Full Screen"><i style="font-size: 18px;" class="fa fa-expand"></i></a></li>
<li role="presentation"><a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
<i style="font-size: 18px;" class="fa fa-download"></i> <span class="caret"></span></a>
<ul class="dropdown-menu dropdown-menu-right">
<li><a class="rmarkreportdownpdf" href="#">Download PDF</a></li>
<li><a class="rmarkeditordownrmd" href="#">Download RMD</a></li>
</ul>
</li>
</ul>`

        }
        var renameModal = `
<div id="rMarkRename" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title">Save</h4>
</div>
<div class="modal-body">
<p id="rMarkRenameText"></p>
<form style="padding-right:10px;" class="form-horizontal">
<div class="form-group">
<label class="col-sm-3 control-label">File Name</label>
<div class="col-sm-9">
<input type="text" class="form-control rmarkfilename">
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
<div id="rMarkInfo" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title">Info</h4>
</div>
<div class="modal-body">
<p id="rMarkInfoText"></p>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>
</div>
</div>
</div>
</div>`;
        var settingsModal = ` 
<div id="rMarkSett" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title" >Settings</h4>
</div>
<div class="modal-body">
<form style="padding-right:10px;" class="form-horizontal">
<div class="form-group">
<label class="col-sm-5 control-label">Auto updating output <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="If enabled, the preview panel updates automatically as you code. If disabled, use the 'Run Script' button to update."><i class="glyphicon glyphicon-info-sign"></i></a></span></label>
<div class="col-sm-7">
<label class="switch">
<input class="aUpdateOut" type="checkbox">
<span class="slider round"></span>
</label>
</div>
</div>
<div class="form-group">
<label class="col-sm-5 control-label">Autosave <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="If active, DolphinNext will autosave the file content every 30 seconds."><i class="glyphicon glyphicon-info-sign"></i></a></span></label>
<div class="col-sm-7">
<label class="switch">
<input class="aSave" type="checkbox">
<span class="slider round"></span>
</label>
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-primary" data-dismiss="modal" >close</button>
</div>
</div>
</div>
</div>`;


        var getDiv = function(settings, outputHtml) {
            var id = "rMarkEditor"
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
            if (document.getElementById("rMarkSett") === null) {
                $('body').append(settingsModal);
            }
            if (document.getElementById("rMarkRename") === null) {
                $('body').append(renameModal);
            }
            if (document.getElementById("rMarkInfo") === null) {
                $('body').append(infoModal);
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

        var saveRmd = async function(editorId, type) {
            var obj = getFileName();
            var run_log_uuid = $("#runVerLog").val();
            var unlockedFile = "pubweb/" + obj.rest + "/." + obj.filename + ".UNLOCKED";
            // allow save on existing when checkUnlockedFile === 1
            var checkUnlockedFile = await doAjax({
                p: "checkFileExist",
                location: unlockedFile,
                uuid: run_log_uuid
            });

            //check if readonly
            if (type == "saveas") {
                //ask new name  
                $("#rMarkRenameText").text("");
                $("#rMarkRename").attr("filename", obj.filename)
                $("#rMarkRename").modal("show");
            } else if (type == "save") {
                if (checkUnlockedFile === 1) {
                    var saveData = saveCommand(editorId, obj.filename)
                    if (saveData) {
                        updateLogText("All changes saved.", "clean")
                    }
                } else {
                    //ask new name  for saveas
                    $("#rMarkRenameText").text("Modification on the original file is not allowed. Please enter a new name to save your changes into a new file.")
                    $("#rMarkRename").attr("filename", obj.filename)
                    $("#rMarkRename").modal("show");
                }

            }
        }

        var openBlankPage = function(editorId) {
            var obj = getFileName();
            var newPath = obj.rest + "/" + obj.filename
            var url = settings.ajax.pubWebPath + "/" + settings.ajax.uuid + "/pubweb/" + settings.ajax.dir + "/.tmp/" + settings.ajax.filename + ".html"
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

        var callback = function(settings, tmpPath, orgPath, pid, type) {
            if (tmpPath && orgPath) {
                //move tmp path to original path
                var format = ""
                if (type == "rmdtext") {
                    format = ".html"
                } else if (type == "rmdpdf") {
                    format = ".pdf"
                }
                var data = {
                    "p": "moveFile",
                    "type": "pubweb",
                    "from": settings.ajax.uuid + "/pubweb/" + settings.ajax.dir + "/.tmp/" + settings.ajax.filename + format + pid,
                    "to": settings.ajax.uuid + "/pubweb/" + settings.ajax.dir + "/.tmp/" + settings.ajax.filename + format
                };
                var moveFile = ajaxRq(settings, data); //returns true or false
            }
            if ((tmpPath && orgPath && moveFile) || (!tmpPath && orgPath && !moveFile)) {
                updateLogText("Done.", "clean")
                progress(100)
                if (type == "rmdtext") {
                    var reportId = elemsID + "-report";
                    var iframe = $("#" + reportId + "> iframe")
                    if (iframe && iframe.attr("src")) {
                        iframe[0].contentWindow.location.reload(true)
                    } else {
                        iframe.attr("src", orgPath)
                    }
                } else if (type == "rmdpdf") {
                    var a = document.createElement('A');
                    var filename = elems.attr("filename")
                    filename = filename.substr(0, filename.lastIndexOf('.')) + ".pdf";
                    download_file(orgPath, filename);
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

        var downpdf = function(editorId) {
            var text = window[editorId].getValue();
            callData(text, settings, "rmdpdf", callback);
        }

        var downRmd = function(editorId) {
            var text = window[editorId].getValue();
            var filename = elems.attr("filename")
            downloadText(text, filename)
        }

        var update = function(editorId) {
            var text = window[editorId].getValue();
            callData(text, settings, "rmdtext", callback);
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
                window['interval_aSave_' + editorId] = setInterval(async function() {
                    await saveRmd(editorId, "save");
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
                $('a.rmarkeditorrun').on('click', function(event) {
                    if ($(this).parents("#" + elemsID).length) {
                        update(editorId);
                    }
                });
                $('a.rmarkreportdownpdf').on('click', function(event) {
                    if ($(this).parents("#" + elemsID).length) {
                        event.preventDefault();
                        downpdf(editorId);
                    }
                });
                $('a.rmarkeditordownrmd').on('click', function(event) {
                    if ($(this).parents("#" + elemsID).length) {
                        event.preventDefault();
                        downRmd(editorId);
                    }
                });
            });
            $(function() {
                //check current status on first creation
                checkAutoUpdateOut(editorId)
                $(document).on('change', 'input.aUpdateOut', function(event) {
                    checkAutoUpdateOut(editorId)
                });
                checkAutoSave(editorId)
                $(document).on('change', 'input.aSave', function(event) {
                    checkAutoSave(editorId)
                });
            });
            $(function() {
                $('a.rmarkeditorsave').on('click', async function(event) {
                    if ($(this).parents("#" + elemsID).length) {
                        await saveRmd(editorId, "save")
                    }
                });
                $('a.rmarkeditorsaveas').on('click', async function(event) {
                    if ($(this).parents("#" + elemsID).length) {
                        await saveRmd(editorId, "saveas")
                    }
                });
                $('a.rmarkeditorsett').on('click', function(event) {
                    $("#rMarkSett").modal("show");
                });
                $('a.rmarkeditorfull').on('click', function(event) {
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
                $('a.rmarkeditorlink').on('click', function(event) {
                    if ($(this).parents("#" + elemsID).length) {
                        openBlankPage(editorId)
                    }
                });


            });
            $(function() {
                $('#rMarkRename').on('show.bs.modal', function(event) {
                    var divOldName = elems.attr("filename")
                    var modalOldName = $("#rMarkRename").attr("filename")
                    if (divOldName === modalOldName) {
                        if ($('#rMarkRename').find("input.rmarkfilename")) {
                            $($('#rMarkRename').find("input.rmarkfilename")[0]).val(divOldName)
                        }
                    }
                });
                $("#rMarkRename").on('click', '.save', async function(event) {
                    var divOldName = elems.attr("filename")
                    var divOldDir = elems.attr("dir")
                    var modalOldName = $("#rMarkRename").attr("filename");
                    var run_log_uuid = $("#runVerLog").val();

                    if (divOldName === modalOldName) {
                        if ($('#rMarkRename').find("input.rmarkfilename")) {
                            var obj = getFileName();
                            var newName = $.trim($($('#rMarkRename').find("input.rmarkfilename")[0]).val());
                            var targetFile = "pubweb/" + obj.rest + "/" + newName;
                            // 1. check if file already exist -> give another warning.
                            var checkFile = await doAjax({
                                p: "checkFileExist",
                                location: targetFile,
                                uuid: run_log_uuid
                            });
                            if (checkFile === 1) {
                                $("#rMarkInfoText").text(`The file ${newName} already exists. Please enter a new filename.`)
                                $("#rMarkInfo").modal("show");
                                return;
                            }
                            //2. allow saving file 
                            // first create unlocked file
                            var unlockedFile = "pubweb/" + obj.rest + "/." + newName + ".UNLOCKED";
                            var saveData = getValues({ p: "saveFileContent", text: "", uuid: run_log_uuid, filename: unlockedFile });
                            var saveData = saveCommand(editorId, newName);
                            // remove "-editor" + divOldName.length
                            var dynamicRowID = editorId.substring(0, editorId.length - (1 + "-editor".length + divOldName.length));
                            // editor id: g-161_rmarkdown_1_rmd-editor
                            // g-161_rmarkdown
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
                                $("#rMarkRename").modal("hide");
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
            if (type == "rmdtext") {
                format = ".html"
            } else if (type == "rmdpdf") {
                format = ".pdf"
            }
            var orgPath = settings.ajax.pubWebPath + "/" + settings.ajax.uuid + "/pubweb/" + settings.ajax.dir + "/.tmp/" + settings.ajax.filename + format
            var tmpPath = orgPath + pid
            window[elemsID + type] = setInterval(function() {
                var checkExistUrl = checkUrl(tmpPath)
                if (!checkExistUrl) {
                    var checkExistError = checkUrl(orgPath + ".err" + pid)
                    if (checkExistError) {
                        getUrlContent(orgPath + ".err" + pid).success(function(data) {
                            if (data) {
                                if (!$('#myModal').hasClass('in')) {
                                    $("#rMarkInfoText").text(data)
                                    $("#rMarkInfo").modal("show");
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
                    if (window[elemsID + type]) {
                        clearInterval(window[elemsID + type]);
                        window[elemsID + type] = null;
                        return callback(settings, tmpPath, orgPath, pid, type)
                    }
                }
            }, 2000);

        }
        var initialUrlCheck = function(settings, type) {
            var format = ""
            if (type == "rmdtext") {
                format = ".html"
            } else if (type == "rmdpdf") {
                format = ".pdf"
            }
            var orgPath = settings.ajax.pubWebPath + "/" + settings.ajax.uuid + "/pubweb/" + settings.ajax.dir + "/.tmp/" + settings.ajax.filename + format
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
            var ret = null
            $.ajax({
                type: "POST",
                url: settings.ajax.url,
                data: {
                    "p": "callRmarkdown",
                    "text": editTextSend,
                    "type": type,
                    "uuid": settings.ajax.uuid,
                    "dir": settings.ajax.dir,
                    "filename": settings.ajax.filename
                },
                async: false,
                cache: false,
                success: function(results) {
                    ret = results;
                    if (ret) {
                        getUrl(settings, type, callback, ret)
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
        var initialFileCheck = initialUrlCheck(settings, "rmdtext")
        if (!initialFileCheck) {
            callData(settings.ajax.text, settings, "rmdtext", callback);
        }
        createEditor(settings)
        createModal()
        eventHandler(settings);
        return this;
    };
}(jQuery));