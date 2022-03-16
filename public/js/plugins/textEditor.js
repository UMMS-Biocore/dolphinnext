//################################
// --textEditor jquery plugin --
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
                heightEditor: "530px",
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
        },
        fnAddFile: function(filename) {
            var elems = $(this);
            var elemsID = $(this).attr("id");
            var settings = elems.data('settings');
            var liAr = elems.find("li[tabid]");
            var newLiID = ""
            if (liAr.length > 0) {
                newLiID = parseInt($(liAr[liAr.length - 1]).attr("el")) + 1
            } else {
                newLiID = "0"
            }
            var liDiv = elems.find(".li-content");
            var tabDivAr = elems.find(".tab-content");
            var newLiElement = getLi(filename, elemsID, newLiID);
            var text = ""
            $(liDiv[0]).append(newLiElement);
            $(tabDivAr[0]).append(getTab(elemsID, newLiID, settings));
            afterAppendEachEl(text, newLiID, elemsID, elems, settings)
            var tooglescreen = elems.data("tooglescreen") //expand or compress
            if (tooglescreen == "compress") {
                tooglescreen = "expand"
                var editorID = $(newLiElement).attr("editorID");
                toogleContentSize(editorID, elemsID, newLiID, tooglescreen, elems, settings);
            }
            var a = elems.find('li[el="' + newLiID + '"] > a');
            if (a.length) {
                a.click()
            }
            return this;
        }
    };

    $.fn.textEditor = function(methodOrOptions) {
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

    var getFileListHeaderIconDiv = function(elemsID) {
        var border = "border-right: 1px solid lightgray;"
        var addIcon = `<li role="presentation"><a id="addIcon-` + elemsID + `" data-toggle="tooltip" data-placement="bottom" data-original-title="Add File"><i style="font-size: 18px;" class="fa fa-plus"></i></a></li>`;
        var renameIcon = `<li role="presentation"><a id="renameIcon-` + elemsID + `" data-toggle="tooltip" data-placement="bottom" data-original-title="Rename File"><i style="font-size: 18px;" class="fa fa-pencil"></i></a></li>`;
        var deleteIcon = `<li role="presentation"><a  id="deleteIcon-` + elemsID + `" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete File"><i style="font-size: 18px;" class="fa fa-trash"></i></a></li>`;
        var content = `<ul style="float:right"  class="nav nav-pills panelheader">` + addIcon + renameIcon + deleteIcon + `</ul>`;
        var wrapDiv = '<div id="' + elemsID + '-ListHeaderIconDiv" style="' + border + 'height:35px; width:100%;">' + content + '</div>';
        return wrapDiv;
    }

    var getLi = function(filename, elemsID, el) {
        var active = "";
        if (el == 0) {
            active = "active"
        } else {
            active = "";
        }
        var icon = "fa-file-text-o";
        var filenameClwithDot = cleanFileName(filename, "default")
        var editorID = "editorID_" + elemsID + "_" + el;
        var fileid = "fileID_" + elemsID + "_" + el;
        var tabID = "fileTabs_" + elemsID + "_" + el;
        //remove directory str, only show filename in label
        return '<li el="' + el + '" tabID="' + tabID + '" editorID="' + editorID + '" id="' + filenameClwithDot + '" class="' + active + '"><a  class="reportFile" data-toggle="tab" href="#' + tabID + '" ><i class="fa ' + icon + '"></i><span>' + filenameClwithDot + '</span></a></li>';
    }

    var getTab = function(elemsID, el, settings) {
        var tabID = "fileTabs_" + elemsID + "_" + el;
        var fileid = "fileID_" + elemsID + "_" + el;
        var active = "";
        if (el == 0) {
            active = 'in active';
        }
        var editorID = "editorID_" + elemsID + "_" + el;
        var scriptMode = "scriptMode_" + elemsID + "_" + el;
        var languageOptions = `<option value="groovy">groovy</option><option value="markdown">markdown</option>`
        if (settings.language) {
            languageOptions = "";
            for (var i = 0; i < settings.language.length; i++) {
                languageOptions += `<option value="${settings.language[i]}">${settings.language[i]}</option>`
            }
        }
        var aceEditorDiv = `<div id="` + editorID + `" style="height:` + settings.heightEditor + `; width: 100%;"></div>
<div class="row">
<p class="col-sm-4" style="padding-top:4px; padding-right:0; padding-left:60px;">Language Mode:</p>
<div class="col-sm-3">
<select id="` + scriptMode + `" class="form-control">
${languageOptions}
</select>
</div>
</div>`;
        var contentDiv = getFileContentHeaderIconDiv(fileid) + '<div style="width:100%; height:' + settings.heightIconBar + ';" id="' + fileid + '">' + aceEditorDiv + '</div>';
        return '<div style="height:100%; width:100%;" id = "' + tabID + '" class = "tab-pane fade fullsize ' + active + '" >' + contentDiv + '</div>';
    }

    var getFileListCol = function(elemsID, dataObj, height, lineHeight, settings) {
        var fileListColID = "fileListDiv_" + elemsID;
        var colPercent = "15";
        var overflowT = 'overflow: scroll; ';
        var liText = "";
        $.each(dataObj, function(el) {
            if (dataObj[el]) {
                liText += getLi(dataObj[el].filename, elemsID, el);
            }
        });
        if (!liText) {
            liText = '<div style="margin:10px;"> <ul class="nav nav-pills nav-stacked li-content">No data available</ul></div>';
        } else {
            liText = '<ul class="nav nav-pills nav-stacked li-content">' + liText + '</ul>';
            liText = '<div style="' + overflowT + 'width:100%; height:calc(100% - ' + settings.heightIconBar + ');" >' + liText + '</div>';
        }
        var columnPercent = '15';
        var clearFix = " clear:both; "; //if its the first element of multicolumn
        var center = ""
        var heightT = ""
        var lineHeightT = ""
        if (height) {
            heightT = 'height:' + height + '; ';
        }
        if (lineHeight) {
            lineHeightT = 'line-height:' + lineHeight + '; ';
        }
        var IconDiv = getFileListHeaderIconDiv(elemsID);
        var listDiv = '<div id="' + fileListColID + '" style="' + heightT + lineHeightT + clearFix + ' float:left; ' + ' width:' + columnPercent + '%;" id = "' + fileListColID + '" >' + IconDiv + liText + '</div>';
        return listDiv
    }

    var getFileContentHeaderIconDiv = function(fileid) {
        var content = `<ul style="float:inherit"  class="nav nav-pills panelheader">` +
            `<li role="presentation"><a fileid="` + fileid + `" id="fullscr-` + fileid + `" data-toggle="tooltip" data-placement="bottom" data-original-title="Toogle Full Screen"><i style="font-size: 18px;" class="fa fa-expand"></i></a></li>` + `</ul>`
        var wrapDiv = '<div id="' + fileid + '-ContentHeaderIconDiv" style="float:right; height:35px; width:100%;">' + content + '</div>';
        return wrapDiv;
    }

    var getFileContentCol = function(elemsID, dataObj, height, lineHeight, settings) {
        var colPercent = "85";
        var heightT = ""
        var lineHeightT = ""
        var navTabDiv = '<div style="height:inherit;" class="tab-content">';
        $.each(dataObj, function(el) {
            navTabDiv += getTab(elemsID, el, settings);
        });
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

    var toogleContentSize = function(editorId, elemsID, el, tooglescreen, elems, settings) {
        var fileListColID = "fileListDiv_" + elemsID;
        var each_file_id = "fileID_" + elemsID + "_" + el;
        var icon = $('#fullscr-' + each_file_id).children()
        if (tooglescreen == "expand") {
            icon.attr("class", "fa fa-compress")
        } else {
            icon.attr("class", "fa fa-expand")
        }
        if (tooglescreen == "expand") {
            $("#" + editorId).css("height", $(window).height() - settings.heightIconBar.substring(0, settings.heightIconBar.length - 2) - 45)
            $("#" + fileListColID).css("height", $(window).height() - settings.heightIconBar.substring(0, settings.heightIconBar.length - 2) - 10)
        } else {
            $("#" + editorId).css("height", settings.heightEditor)
            $("#" + fileListColID).css("height", settings.heightFileList)
        }
        window[editorId].resize();
    }


    var bindEveHandlerIcon = function(fileid, elems, elemsID, settings) {
        $('[data-toggle="tooltip"]').tooltip();
        $('#fullscr-' + fileid).on('click', function(event) {
            var tooglescreen = elems.data("tooglescreen");
            var liAr = elems.find("li[tabid]");
            for (var el = 0; el < liAr.length; el++) {
                var editorID = $(liAr[el]).attr("editorID");
                toogleContentSize(editorID, elemsID, el, tooglescreen, elems, settings);
            }
            toogleFullSize(tooglescreen, elems, settings);
        });
    }

    var getColumnData = function(elemsID, dataObj, settings, height, lineHeight) {
        var processParamDiv = ""
        processParamDiv += getFileListCol(elemsID, dataObj, height, lineHeight, settings)
        processParamDiv += getFileContentCol(elemsID, dataObj, height, lineHeight, settings)
        return processParamDiv
    }

    var createAceEditor = function(editorId, script_modeId) {
        //ace process editor
        window[editorId] = ace.edit(editorId);
        window[editorId].setTheme("ace/theme/tomorrow");
        window[editorId].getSession().setMode("ace/mode/sh");
        window[editorId].$blockScrolling = Infinity;
        //If mode is exist, then apply it
        var mode = $("#" + script_modeId).val();
        if (mode && mode != "") {
            window[editorId].session.setMode("ace/mode/" + mode);
        }
        $(function() {
            $(document).on('change', "#" + script_modeId, function() {
                var newMode = $("#" + script_modeId).val();
                console.log(newMode)
                window[editorId].session.setMode("ace/mode/" + newMode);
            })
        });
    }

    var setValueAceEditor = function(editorId, text) {
        window[editorId].setValue(text);
        window[editorId].clearSelection();
    }

    var createModal = function() {
        var infoModal = `
<div id="tEditorInfo" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title" id="tEditorInfoTitle">Title</h4>
</div>
<div class="modal-body">
<p id="tEditorInfoText"></p>
<form style="padding-right:10px;" class="form-horizontal">
<div id="tEditorInfoNameDiv" class="form-group">
<label class="col-sm-3 control-label">File Name</label>
<div class="col-sm-9">
<input id="tEditorInfoName" type="text" class="form-control">
</div>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
<button id="tEditorInfoButton" type="button" class="btn btn-primary" data-dismiss="modal">Save</button>
</div>
</div>
</div>
</div>`;
        if (document.getElementById("tEditorInfo") === null) {
            $('body').append(infoModal);
        }
    }

    var bindEventHandlerModal = function(elemsID) {
        $(function() {
            $('#addIcon-' + elemsID).on('click', function(e) {
                $("#tEditorInfoTitle").text("Create New File")
                $("#tEditorInfoName").val("New File")
                $("#tEditorInfoText").css("display", "none");
                $("#tEditorInfoNameDiv").css("display", "inline");
                $("#tEditorInfoButton").attr("class", "btn btn-primary save")
                $("#tEditorInfoButton").text("Save")
                $("#tEditorInfo").modal("show");
            });
            $('#renameIcon-' + elemsID).on('click', function(e) {
                var activeLi = $("#fileListDiv_" + elemsID).find("li.active");
                var filename = $(activeLi[0]).attr("id");
                $("#tEditorInfo").removeData("li_element")
                $("#tEditorInfo").data("li_element", activeLi)
                $("#tEditorInfoTitle").text("Rename File")
                $("#tEditorInfoName").val(filename)
                $("#tEditorInfoText").css("display", "none");
                $("#tEditorInfoNameDiv").css("display", "inline");
                $("#tEditorInfoButton").attr("class", "btn btn-primary rename")
                $("#tEditorInfoButton").text("Rename")
                $("#tEditorInfo").modal("show");
            });
            $('#deleteIcon-' + elemsID).on('click', function(e) {
                var activeLi = $("#fileListDiv_" + elemsID).find("li.active");
                var filename = $(activeLi[0]).attr("id");
                $("#tEditorInfo").removeData("li_element")
                $("#tEditorInfo").data("li_element", activeLi)
                $("#tEditorInfoTitle").text("Delete File")
                $("#tEditorInfoNameDiv").css("display", "none");
                $("#tEditorInfoText").css("display", "inline");
                $("#tEditorInfoText").text('Are you sure you want to delete ' + filename + '?')
                $("#tEditorInfoButton").attr("class", "btn btn-primary delete")
                $("#tEditorInfoButton").text("Delete")
                $("#tEditorInfo").modal("show");
            });
            $("#tEditorInfo").on('click', '.rename', function(event) {
                var newName = $("#tEditorInfoName").val();
                var activeLi = $("#tEditorInfo").data("li_element")
                if (activeLi[0] && newName) {
                    newName = newName.trim()
                    var activeSpan = $(activeLi[0]).find("span")
                    if (activeSpan[0]) {
                        $(activeLi[0]).attr("id", newName)
                        $(activeSpan[0]).text(newName)
                        $("#tEditorInfo").modal("hide");
                    }
                }
            });
            $("#tEditorInfo").on('click', '.delete', function(event) {
                var activeLi = $("#tEditorInfo").data("li_element")
                if (activeLi[0]) {
                    var a = $(activeLi[0]).find("[href]")
                    if (a[0]) {
                        var href = $(a[0]).attr("href")
                        var hrefDiv = $("#" + elemsID).find(href)
                        if (hrefDiv[0]) {
                            $(activeLi[0]).remove()
                            $(hrefDiv[0]).remove()
                            var liAr = $("#fileListDiv_" + elemsID).find('li[tabid]');
                            if (liAr.length > 0) {
                                var a = $(liAr[0]).find("a");
                                if (a.length) {
                                    a.click()
                                }
                            }
                            $("#tEditorInfo").modal("hide");
                        }
                    }
                }

            });
            $("#tEditorInfo").on('click', '.save', function(event) {
                var newName = $("#tEditorInfoName").val();
                $("#" + elemsID).textEditor("fnAddFile", newName)
            });
        });
    }


    var afterAppendEachEl = function(text, el, elemsID, elems, settings) {
        var tabID = "fileTabs_" + elemsID + "_" + el;
        var fileid = "fileID_" + elemsID + "_" + el;
        var editorID = "editorID_" + elemsID + "_" + el;
        var scriptMode = "scriptMode_" + elemsID + "_" + el;
        bindEveHandlerIcon(fileid, elems, elemsID, settings);
        createAceEditor(editorID, scriptMode);
        setValueAceEditor(editorID, text);
    }
    var afterAppendPanel = function(dataObj, settings, elemsID, elems) {
        $.each(dataObj, function(el) {
            var text = dataObj[el].text
            afterAppendEachEl(text, el, elemsID, elems, settings)
        });
        createModal()
        bindEventHandlerModal(elemsID)
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
    }

}(jQuery));

//--end of textEditor jquery plugin --
//#####################################