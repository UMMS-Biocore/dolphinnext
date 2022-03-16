//################################
// --markdownEditor jquery plugin --
//################################

(function($) {
    var methods = {
        init: function(options) {
            var settings = $.extend({
                // default values.
                color: "#556b2f",
                backgroundColor: "white",
                heightHeader: "60px",
                lineHeightHeader: "60px",
                heightBody: "600px",
                heightEditor: "525px",
                heightFileList: "565px",
                heightTitle: "50px",
                lineHeightTitle: "50px",
                heightIconBar: "35px"
            }, options);
            var elems = $(this);
            elems.css("width", "100%")
            elems.css("height", "100%")
            var elemsID = $(this).attr("id");
            elems.data('settings', settings);
            elems.data("tooglescreen", "expand")
            var data = getData(settings);
            if (data === undefined || data == null || data == "") {
                elems.append('<div  style="font-weight:900; line-height:' + settings.lineHeightTitle + 'height:' + settings.heightTitle + ';">No data available to show</div>')
            } else {
                // append panel
                elems.append(getPanel(data, settings, elemsID));
                // after appending panel
                afterAppendPanel(data, settings, elemsID, elems)
            }
            return this;
        }
    };

    $.fn.markdownEditor = function(methodOrOptions) {
        if (methods[methodOrOptions]) {
            return methods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof methodOrOptions === 'object' || !methodOrOptions) {
            // Default to "init"
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + methodOrOptions + ' does not exist on jQuery');
        }
    };

    var cleanFileName = function(name, type) {
        if (type == "jquery") {
            name = name.replace(/\./g, "_");
            name = name.replace(/\//g, "_");
        }
        name = name.replace(/\$/g, "_");
        name = name.replace(/\!/g, "_");
        name = name.replace(/\</g, "_");
        name = name.replace(/\>/g, "_");
        name = name.replace(/\?/g, "_");
        name = name.replace(/\(/g, "_");
        name = name.replace(/\"/g, "_");
        name = name.replace(/\'/g, "_");
        name = name.replace(/\\/g, "_");
        name = name.replace(/@/g, "_");
        return name;
    }



    var getTab = function(elemsID, settings) {
        var fileid = elemsID;
        var tabID = "fileTabs_" + fileid;
        var active = 'in active';
        var editorID = "editorID_" + fileid;
        var htmlID = "htmlID_" + fileid;
        var scriptModeDivID = "scriptModeID_" + fileid;
        var scriptMode = "scriptMode_" + fileid;
        var htmlDiv = `<div id="` + htmlID + `" style="padding-left:15px; overflow:scroll; height:` + settings.heightFileList + `; width: 100%;"></div>`;
        var aceEditorDiv = `<div id="` + editorID + `" style="display:none; height:` + settings.heightEditor + `; width: 100%;"></div>
<div style="display:none;" id="` + scriptModeDivID + `" class="row">
<p class="col-sm-4" style="padding-top:4px; padding-right:0; padding-left:60px;">Language Mode:</p>
<div class="col-sm-3">
<select id="` + scriptMode + `" class="form-control">
<option value="markdown">markdown</option>
</select>
</div>
</div>`;
        var contentDiv = getFileContentHeaderIconDiv(fileid, settings) + '<div style="width:100%; height:' + settings.heightIconBar + ';" editorid= "' + editorID + '" id="' + fileid + '">' + htmlDiv + aceEditorDiv + '</div>';
        return '<div style="height:100%; width:100%;" id = "' + tabID + '" class = "tab-pane fade fullsize ' + active + '" >' + contentDiv + '</div>';
    }

    var getFileContentHeaderIconDiv = function(fileid, settings) {
        var editConfirmIcon = "";
        var downloadIcon = `<li role="presentation"><a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"> <i style="font-size: 18px;" class="fa fa-download"></i> <span class="caret"></span></a> <ul class="dropdown-menu dropdown-menu-right"> <li><a fileid="` + fileid + `" id="downUrl-` + fileid + `" href="#">Download</a></li> </ul> </li>`;
        if (settings.ajax.data.editable) {
            editConfirmIcon = `<li role="presentation"><a fileid="` + fileid + `" id="confirmmd-` + fileid + `" style="display:none;" data-toggle="tooltip" data-placement="bottom" data-original-title="Save Changes"><i style="font-size: 18px;" class="fa fa-save"></i></a><a fileid="` + fileid + `"  id="editmd-` + fileid + `" data-toggle="tooltip" data-placement="bottom" data-original-title="Edit Markdown"><i style="font-size: 18px;" class="fa fa-pencil-square-o"></i></a></li>`;

        }
        var fullScreenIcon = `<li role="presentation"><a fileid="` + fileid + `" id="fullscr-` + fileid + `" data-toggle="tooltip" data-placement="bottom" data-original-title="Toogle Full Screen"><i style="font-size: 18px;" class="fa fa-expand"></i></a></li>`;

        var content = `<ul style="float:inherit"  class="nav nav-pills panelheader">` + editConfirmIcon + fullScreenIcon + downloadIcon + `</ul>`;
        var wrapDiv = '<div id="' + fileid + '-ContentHeaderIconDiv" style="float:right; height:35px; width:100%;">' + content + '</div>';
        return wrapDiv;
    }

    var getFileContentCol = function(elemsID, dataObj, height, lineHeight, settings) {
        var colPercent = "100";
        var heightT = ""
        var lineHeightT = ""
        var navTabDiv = '<div style="height:inherit;" class="tab-content">';
        navTabDiv += getTab(elemsID, settings);
        navTabDiv += '</div>';
        if (height) {
            heightT = 'height:' + height + '; ';
        }
        if (lineHeight) {
            lineHeightT = 'line-height:' + lineHeight + '; ';
        }
        return '<div style="' + heightT + lineHeightT + 'float:left;  width:' + colPercent + '%; ">' + navTabDiv + "</div>";
    }

    var getColumnContent = function(dataObj, colObj, nTd) {
        var col = "";
        if (colObj.fnCreatedCell && !nTd) {
            var nTd = $("<span></span>");
            colObj.fnCreatedCell(nTd, dataObj)
            col = nTd.clone().wrap('<p>').html();
        } else if (colObj.fnCreatedCell && nTd) {
            colObj.fnCreatedCell(nTd, dataObj)
        } else if (colObj.data) {
            col = dataObj[colObj.data]
        }
        return col
    };

    var toogleFullSize = function(tooglescreen, elems, settings) {
        if (tooglescreen == "expand") {
            var featList = ["z-index", "height", "position", "top", "left", "background"]
            var newValue = ["1049", "100%", "fixed", "0", "0", "white"]
            var oldCSS = {};
            var newCSS = {};
            for (var i = 0; i < featList.length; i++) {
                oldCSS[featList[i]] = elems.css(featList[i])
                newCSS[featList[i]] = newValue[i]
            }
            elems.data("oldCSS", oldCSS);
            elems.data("tooglescreen", "compress");

        } else {
            var newCSS = elems.data("oldCSS");
            elems.data("tooglescreen", "expand");

        }
        //apply css obj
        $.each(newCSS, function(el) {
            elems.css(el, newCSS[el])
        });
    }

    var toogleHtmlSize = function(htmlID, elemsID, tooglescreen, elems, settings) {
        if (tooglescreen == "expand") {
            $("#" + htmlID).css("height", $(window).height() - 45)
        } else {
            $("#" + htmlID).css("height", settings.heightFileList)
        }
    }


    var aceEditorResize = function(editorId) {
        setTimeout(function() {
            window[editorId].resize();
            window[editorId].setOption("wrap", false);
            window[editorId].setOption("wrapBehavioursEnabled", false);
            window[editorId].setOption("wrap", true);
        }, 100);
    }

    var toogleEditorSize = function(editorId, elemsID, tooglescreen, elems, settings) {
        var each_file_id = elemsID;
        var icon = $('#fullscr-' + each_file_id).children()
        if (tooglescreen == "expand") {
            icon.attr("class", "fa fa-compress")
        } else {
            icon.attr("class", "fa fa-expand")
        }
        if (tooglescreen == "expand") {
            $("#" + editorId).css("height", $(window).height() - settings.heightIconBar.substring(0, settings.heightIconBar.length - 2) - 45)
        } else {
            $("#" + editorId).css("height", settings.heightEditor)
        }
        aceEditorResize(editorId);
    }

    var getFileName = function(settings) {
        var res = { filename: "", rest: "" };
        var filePath = settings.ajax.data.filePath
        var split = filePath.split("/")
        res.filename = split[split.length - 1]
        res.rest = split.slice(0, -1).join('/');
        return res
    }

    var savemd = function(settings, editorId) {
        var obj = getFileName(settings);
        var newPath = obj.rest + "/" + obj.filename
        var text = window[editorId].getValue();
        text = encodeURIComponent(text);
        var run_log_uuid = $("#runVerLog").val();
        var saveData = getValues({ p: "saveFileContent", text: text, uuid: run_log_uuid, filename: "pubweb/" + newPath });
        return saveData
    }


    var bindEveHandlerIcon = function(fileid, elems, elemsID, settings) {
        $('[data-toggle="tooltip"]').tooltip();
        $('#fullscr-' + fileid).on('click', function(event) {
            var tooglescreen = elems.data("tooglescreen");
            var editorID = "editorID_" + fileid;
            var htmlID = "htmlID_" + fileid;
            toogleHtmlSize(htmlID, elemsID, tooglescreen, elems, settings);
            toogleEditorSize(editorID, elemsID, tooglescreen, elems, settings);
            toogleFullSize(tooglescreen, elems, settings);
        });
        $('#downUrl-' + fileid).on('click', function(event) {
            var fileid = $(this).attr("fileid")
            var filename = $("#" + fileid).attr("filename")
            var filepath = $("#" + fileid).attr("filepath")
            var a = document.createElement('A');
            var url = settings.ajax.data.pubWebPath + "/" + settings.ajax.data.uuid + "/pubweb/" + filepath
            download_file(url, filename);
        });
        $('#editmd-' + fileid).on('click', function(event) {
            var fileid = $(this).attr("fileid");
            var editorID = "editorID_" + fileid;
            var htmlID = "htmlID_" + fileid;
            var scriptModeDivID = "scriptModeID_" + fileid;
            aceEditorResize(editorID)
            $('#confirmmd-' + fileid).css("display", "inline-block");
            $('#editmd-' + fileid).css("display", "none");
            $('#' + editorID).css("display", "inline-block");
            $('#' + scriptModeDivID).css("display", "block");
            $('#' + htmlID).css("display", "none");
        });
        $('#confirmmd-' + fileid).on('click', function(event) {
            var fileid = $(this).attr("fileid")
            var editorID = "editorID_" + fileid;
            var htmlID = "htmlID_" + fileid;
            var scriptModeDivID = "scriptModeID_" + fileid;
            aceEditorResize(editorID);
            $('#confirmmd-' + fileid).css("display", "none");
            $('#editmd-' + fileid).css("display", "inline-block");
            savemd(settings, editorID);
            var textEditor = getValueAceEditor(editorID)
            updateMarkdown(textEditor, htmlID)
            $('#' + editorID).css("display", "none");
            $('#' + scriptModeDivID).css("display", "none");
            $('#' + htmlID).css("display", "inline-block");
        });

    }




    var getColumnData = function(elemsID, dataObj, settings, height, lineHeight) {
        var processParamDiv = ""
        processParamDiv += getFileContentCol(elemsID, dataObj, height, lineHeight, settings)
        return processParamDiv
    }

    var createAceEditor = function(editorId, script_modeId) {
        //ace process editor
        window[editorId] = ace.edit(editorId);
        window[editorId].setOption("wrap", true);
        window[editorId].setOption("indentedSoftWrap", false);
        window[editorId].setTheme("ace/theme/tomorrow");
        window[editorId].getSession().setMode("ace/mode/sh");
        window[editorId].$blockScrolling = Infinity;
        //If mode is exist, then apply it
        var mode = $("#" + script_modeId).val();
        if (mode && mode != "") {
            window[editorId].session.setMode("ace/mode/" + mode);
        }
        $(function() {
            $(document).on('change', script_modeId, function() {
                var newMode = $(script_modeId).val();
                window[editorId].session.setMode("ace/mode/" + newMode);
            })
        });
    }

    var setValueAceEditor = function(editorId, text) {
        window[editorId].setValue(text);
        window[editorId].clearSelection();
    }

    var getValueAceEditor = function(editorId) {
        var val = window[editorId].getValue();
        return val;
    }




    var afterAppendEachEl = function(text, elemsID, elems, settings) {
        var fileid = elemsID;
        var tabID = "fileTabs_" + fileid;
        var editorID = "editorID_" + fileid;
        var scriptMode = "scriptMode_" + fileid;
        var htmlID = "htmlID_" + fileid;
        bindEveHandlerIcon(fileid, elems, elemsID, settings);
        createAceEditor(editorID, scriptMode);
        setValueAceEditor(editorID, text);
        var textEditor = getValueAceEditor(editorID)
        updateMarkdown(textEditor, htmlID)

    }
    var afterAppendPanel = function(dataObj, settings, elemsID, elems) {
        var text = dataObj.text
        afterAppendEachEl(text, elemsID, elems, settings)
    }

    var getPanel = function(dataObj, settings, elemsID) {
        if (dataObj) {
            var id = "0"
            var bodyDiv = getColumnData(elemsID, dataObj, settings, settings.heightBody, settings.lineHeightBody);
            var wrapBody = '<div  id="' + elemsID + '-' + id + '" style="word-break: break-all;"><div class="panel-body" style="background-color:white; height:' + settings.heightBody + '; padding:0px;">' + bodyDiv + '</div>';
            return '<div id="' + elemsID + 'PanelDiv-' + id + '" ><div class="panel" style="background-color:' + settings.backgroundcolorleave + '; margin-bottom:15px;">' + wrapBody + '</div></div>'
        } else
            return ""
    }


    var getData = function(settings) {
        var res = null;
        if (settings.ajax.url) {
            $.ajax({
                type: "POST",
                url: settings.ajax.url,
                data: settings.ajax.data,
                datatype: "json",
                async: false,
                cache: false,
                success: function(results) {
                    res = results
                },
                error: function(errorThrown) {
                    console.log("##Error: ");
                    console.log(errorThrown)
                }
            });
            return res
        } else if (settings.ajax.data) {
            if (settings.ajax.data === undefined || settings.ajax.data.length == 0) {
                res = null;
            } else {
                res = settings.ajax.data;
            }
        }
        return res;
    };
}(jQuery));