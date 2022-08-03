var $runscope = {};
$runscope = {
    //-------- Store data:
    checkUserWritePermRun: null,
    getProjectPipelines: null,
    beforeunload: "",
    handsontable: {},

    //-------- Functions:
    //Generic function to save ajax data
    getAjaxData: async function(varName, getValuesObj) {
        if ($runscope[varName] === null) {
            $runscope[varName] = await doAjax(getValuesObj);
            //            Object.defineProperty($runscope, varName, {configureable: false, writable:false});
        }
        return $runscope[varName];
    },
    getAjaxDataSync: function(varName, getValuesObj) {
        if ($runscope[varName] === null) {
            $runscope[varName] = getValues(getValuesObj);
            //            Object.defineProperty($runscope, varName, {configureable: false, writable:false});
        }
        return $runscope[varName];
    },
    //get work OR publish dir OR runcmd
    getPubVal: function(type) {
        var project_pipeline_id = $("#pipeline-title").attr("projectpipelineid");
        var perms = $("#chooseEnv").find(":selected").attr("perms");
        var publish_dir_check = $("#publish_dir_check").is(":checked").toString();
        var dir = "";
        if (type == "work") {
            dir = $.trim($("#rOut_dir").val());
        } else if (type == "publish") {
            dir = $.trim($("#publish_dir").val());
        } else if (type == "runcmd") {
            dir = $.trim($("#runCmd").val());
        } else if (type == "report") {
            if (publish_dir_check === "true" && $.trim($("#publish_dir").val())) {
                dir = $.trim($("#publish_dir").val()) + "/report" + project_pipeline_id;
            } else {
                dir = $.trim($("#rOut_dir").val()) + "/report" + project_pipeline_id;
            }
        }
        if (perms) {
            if (perms == "15") {
                if (type == "work") {
                    var auto_workdir = $("#chooseEnv")
                        .find(":selected")
                        .attr("auto_workdir");
                    if (auto_workdir) {
                        dir = auto_workdir;
                    }
                } else if (type == "runcmd") {
                    dir = "";
                }
            }
        }
        return dir;
    },
    getUploadDir: function(type) {
        //type:new or type:exist
        var perms = $("#chooseEnv").find(":selected").attr("perms");
        var uploadDir = "";
        var workDir = $runscope.getPubVal("work");
        var project_pipeline_id = $("#pipeline-title").attr("projectpipelineid");
        if (type == "new") {
            if (workDir && project_pipeline_id) {
                uploadDir = workDir + "/run" + project_pipeline_id + "/upload";
            }
        } else if (type == "exist") {
            uploadDir = $.trim($("#target_dir").val());
        }
        if (perms) {
            if (perms == "15") {
                uploadDir = workDir + "/run" + project_pipeline_id + "/upload";
            }
        }
        return uploadDir;
    },
    checkUserWritePerm: async function() {
        var project_pipeline_id = $("#pipeline-title").attr("projectpipelineid");
        // if user owns the permission to write, then return 1 else 0;
        var writePerm = await $runscope.getAjaxData("checkUserWritePermRun", {
            p: "checkUserWritePermRun",
            project_pipeline_id: project_pipeline_id,
        });
        return writePerm;
    },
    checkUserWritePermSync: function() {
        var project_pipeline_id = $("#pipeline-title").attr("projectpipelineid");
        // if user owns the permission to write, then return 1 else 0;
        var writePerm = $runscope.getAjaxDataSync("checkUserWritePermRun", {
            p: "checkUserWritePermRun",
            project_pipeline_id: project_pipeline_id,
        });
        return writePerm;
    },
    checkProjectPipelineOwn: async function() {
        var project_pipeline_id = $("#pipeline-title").attr("projectpipelineid");
        // if user owns the permission to write, then return 1 else 0;
        var pipeData = await $runscope.getAjaxData("getProjectPipelines", {
            p: "getProjectPipelines",
            id: project_pipeline_id,
        });
        var projectpipelineOwn = pipeData[0].own;
        $runscope.projectpipelineOwn = projectpipelineOwn;
        return projectpipelineOwn;
    },
};

//prevent functions overwrite
Object.defineProperty($runscope, "getPubVal", {
    configureable: false,
    writable: false,
});
Object.defineProperty($runscope, "getUploadDir", {
    configureable: false,
    writable: false,
});
Object.defineProperty($runscope, "checkUserWritePerm", {
    configureable: false,
    writable: false,
});

// [name] is the name of the event "click", "mouseover", ..
// same as you'd pass it to bind()
// [fn] is the handler function
$.fn.bindFirst = function(name, fn) {
    // bind as you normally would
    // don't want to miss out on any jQuery magic
    this.on(name, fn);

    // Thanks to a comment by @Martin, adding support for
    // namespaced events too.
    this.each(function() {
        var handlers = $._data(this, "events")[name.split(".")[0]];
        // take out the handler we just inserted from the end
        var handler = handlers.pop();
        // move it at the beginning
        handlers.splice(0, 0, handler);
    });
};

/**
 * Extend the Array object
 * @param candid The string to search for
 * @returns Returns the index of the first match or -1 if not found
 */
Array.prototype.searchFor = function(candid) {
    for (var i = 0; i < this.length; i++)
        if (this[i].indexOf(candid) > -1) return true;
    return false;
};

///fixCollapseMenu checkboxes
fixCollapseMenu("#allProcessDiv", "#exec_all");
fixCollapseMenu("#eachProcessDiv", "#exec_each");
fixCollapseMenu("#publishDirDiv", "#publish_dir_check");
//not allow to check both docker and singularity
$("#docker_imgDiv").on("show.bs.collapse", function() {
    if ($("#singu_check").is(":checked") && $("#docker_check").is(":checked")) {
        $("#singu_check").trigger("click");
    }
    $("#docker_check").attr("onclick", "return false;");
});
$("#singu_imgDiv").on("show.bs.collapse", function() {
    if ($("#singu_check").is(":checked") && $("#docker_check").is(":checked")) {
        $("#docker_check").trigger("click");
    }
    $("#singu_check").attr("onclick", "return false;");
});
$("#docker_imgDiv").on("shown.bs.collapse", function() {
    if ($("#singu_check").is(":checked") && $("#docker_check").is(":checked")) {
        $("#singu_check").trigger("click");
    }
    $("#docker_check").removeAttr("onclick");
});
$("#singu_imgDiv").on("shown.bs.collapse", function() {
    if ($("#singu_check").is(":checked") && $("#docker_check").is(":checked")) {
        $("#docker_check").trigger("click");
    }
    $("#singu_check").removeAttr("onclick");
});
$("#singu_imgDiv").on("hide.bs.collapse", function() {
    $("#singu_check").attr("onclick", "return false;");
});
$("#docker_imgDiv").on("hide.bs.collapse", function() {
    $("#docker_check").attr("onclick", "return false;");
});
$("#docker_imgDiv").on("hidden.bs.collapse", function() {
    $("#docker_check").removeAttr("onclick");
});
$("#singu_imgDiv").on("hidden.bs.collapse", function() {
    $("#singu_check").removeAttr("onclick");
});

function createPiGnumList() {
    //get available pipeline Module list
    piGnumList = [];
    $("#subPipelinePanelTitle > div").each(function() {
        if (
            $(this)
            .attr("id")
            .match(/proPanelDiv-(.*)/)
        ) {
            piGnumList.push(
                $(this)
                .attr("id")
                .match(/proPanelDiv-(.*)/)[1]
            );
        }
    });
}

//adjust container size based on window size
window.onresize = function(event) {
    createPiGnumList();
    var Maint = d3.transform(d3.select("#" + "mainG").attr("transform"));
    var Mainx = Maint.translate[0];
    var Mainy = Maint.translate[1];
    var Mainz = Maint.scale[0];
    translateSVG(
        [Mainx, Mainy, Mainz, window.lastMG[3], window.lastMG[4]],
        window
    );
    //for pipeline modules
    for (var j = 0; j < piGnumList.length; j++) {
        var MaintP = d3.transform(
            d3.select("#" + "mainG" + piGnumList[j]).attr("transform")
        );
        var MainxP = MaintP.translate[0];
        var MainyP = MaintP.translate[1];
        var MainzP = MaintP.scale[0];
        translateSVG(
            [
                MainxP,
                MainyP,
                MainzP,
                window["pObj" + piGnumList[j]].lastMG[3],
                window["pObj" + piGnumList[j]].lastMG[4],
            ],
            window["pObj" + piGnumList[j]]
        );
    }
};

function dragStart(event) {
    event.dataTransfer.setData("Text", event.target.id);
}

function dragging(event) {
    event.preventDefault();
}

function allowDrop(event) {
    event.preventDefault();
}

parametersData = getValues({ p: "getAllParameters" });

var sData = "";
var svg = "";
var mainG = "";
var autoFillJSON;
var systemInputs = [];

function createSVG() {
    w = "100%";
    h = "100%";
    r = 70;
    cx = 0;
    cy = 0;
    ior = r / 6;
    rP = r + 24; // r of pipeline circle
    var dat = [{
        x: 0,
        y: 0,
    }, ];
    gNum = 0;
    MainGNum = "";
    selectedgID = "";
    selectedg = "";
    diffx = 0;
    diffy = 0;

    processList = {};
    processListMain = {};
    ccIDList = {}; //pipeline module match id list
    nullIDList = {}; //in case node info is changed after import/save on existing. use getNewNodeId function to get new id
    edges = [];
    candidates = [];
    saveNodes = [];

    dupliPipe = false;
    binding = false;
    renameTextID = "";
    deleteID = "";

    d3.select("#svg").remove();
    //--Pipeline details table clean --
    //    $('#inputsTable').find("tr:gt(0)").remove();
    $("#outputsTable").find("tr:gt(0)").remove();
    $("#processTable").find("tr:gt(0)").remove();

    svg = d3
        .select("#container")
        .append("svg")
        .attr("id", "svg")
        .attr("width", w)
        .attr("height", h)
        .on("mousedown", startzoom);
    mainG = d3
        .select("#container")
        .select("svg")
        .append("g")
        .attr("id", "mainG")
        .attr("transform", "translate(" + 0 + "," + 0 + ")");
}

function startzoom() {
    d3.select("#container").call(zoom);
}

var timeoutId = 0;

function translateSVG(mG, pObj) {
    var MainGNum = "";
    if (pObj != window) {
        // pipeline modules
        var MainGNum = pObj.MainGNum;
    }
    if (!mG[3]) {
        mG[3] = 1378; //default width of container if its not defined before
    }
    //    var widthC = $("#container").width(); //not working for inactive run page div
    var widthC = $("#runTabDiv").width();
    if (!widthC) {
        widthC = 700;
    }
    var coefW = widthC / mG[3];
    var height = widthC / 3;
    if (height < 300) {
        height = 300;
    }
    $("#container" + MainGNum).css("height", height + "px");
    var transX = parseFloat(mG[0]) * coefW;
    var transY = parseFloat(mG[1]) * coefW;
    var transS = parseFloat(mG[2]) * coefW;
    var trans =
        "translate(" + transX + "," + transY + ")" + "scale(" + transS + ")";
    d3.select("#mainG" + MainGNum).attr("transform", trans);

    if (pObj == window) {
        zoom.translate([transX, transY]).scale(transS);
    }
    pObj.lastMG = [transX, transY, transS, widthC, height];
}

async function openSubPipeline(piID, pObj) {
    var sData = pObj.sData[0];
    var MainGNum = pObj.MainGNum;
    var lastGnum = pObj.lastGnum;
    var mergedPipeName = pObj.lastPipeName;
    var prefix = "p" + MainGNum;
    pObj.processList = {};
    pObj.processListMain = {};
    pObj.edges = [];
    var processData = "";
    // insertProPipePanel(
    //     decodeHtml(sData.script_pipe_config) +
    //     "\n" +
    //     decodeHtml(sData.script_pipe_header),
    //     "pipe",
    //     "Pipeline",
    //     window,
    //     processData
    // );
    var hideModule = false;
    if (
        $("#subPipelinePanelTitle").find("div[pipeid*=" + piID + "]").length > 0
    ) {
        hideModule = true;
    }
    var hideModuleText = "";
    if (hideModule) {
        hideModuleText = 'style="display:none;"';
    }
    var pipeName = cleanProcessName(sData.name);
    var dispTitle = $("#subPipelinePanelTitle").css("display");
    if (dispTitle == "none") {
        $("#subPipelinePanelTitle").css("display", "inline");
    }
    var processHeader =
        '<div class="panel-heading collapsible collapseIconDiv" data-toggle="collapse" href="#collapse-' +
        MainGNum +
        '"><h4 class="panel-title">' +
        pipeName +
        '<i data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a style="font-size:15px; padding-left:10px;" class="fa collapseIcon fa-plus-square-o"></a></i></h4></div>';
    var processBodyInt =
        '<div  id="collapse-' +
        MainGNum +
        '" class="panel-collapse collapse"><div style="height:500px; padding:0px;" id="container' +
        MainGNum +
        '" class="panel-body">';
    //create Pipeline Module Panel
    $("#subPipelinePanelTitle").append(
        '<div id="proPanelDiv-' +
        MainGNum +
        '" pipeid="' +
        piID +
        '" ' +
        hideModuleText +
        '><div id="proPanel-' +
        MainGNum +
        '" class="panel panel-default" style="margin-bottom:3px;">' +
        processHeader +
        processBodyInt +
        "</div></div></div></div>"
    );
    pObj.svg = d3
        .select("#container" + MainGNum)
        .append("svg")
        .attr("id", "svg" + MainGNum)
        .attr("width", w)
        .attr("height", h)
        .on("mousedown", startzoom);
    pObj.mainG = d3
        .select("#container" + MainGNum)
        .select("svg")
        .append("g")
        .attr("id", "mainG" + MainGNum)
        .attr("transform", "translate(" + 0 + "," + 0 + ")");
    d3.select("#container" + MainGNum)
        .style("background-image", "url(css/workplace_image.png)")
        .style("background-repeat", "repeat")
        .on("keydown", cancel)
        .on("mousedown", cancel);

    if (sData) {
        pObj.nodes = sData.nodes;
        if (pObj.nodes) {
            if (IsJson5String(pObj.nodes)) {
                pObj.nodes = JSON5.parse(pObj.nodes);
                pObj.mG = sData.mainG;
                pObj.mG = JSON5.parse(pObj.mG)["mainG"];
                translateSVG(pObj.mG, pObj);
                for (var key in pObj.nodes) {
                    pObj.x = pObj.nodes[key][0];
                    pObj.y = pObj.nodes[key][1];
                    pObj.pId = pObj.nodes[key][2];
                    pObj.name = cleanProcessName(pObj.nodes[key][3]);
                    var processModules = pObj.nodes[key][4];
                    pObj.gNum = key.split("-")[1];
                    if (pObj.pId.match(/p(.*)/)) {
                        var newPiID = pObj.pId.match(/p(.*)/)[1];
                        var newMainGnum = "pObj" + MainGNum + "_" + pObj.gNum;
                        var mergedPipeNameFinal = ""
                        window[newMainGnum] = {};
                        window[newMainGnum].piID = newPiID;
                        window[newMainGnum].MainGNum = MainGNum + "_" + pObj.gNum;
                        window[newMainGnum].lastGnum = pObj.gNum;
                        window[newMainGnum].sData = [
                            window.pipeObj["pipeline_module_" + newPiID],
                        ];
                        if (mergedPipeName) {
                            mergedPipeNameFinal = mergedPipeName + "_" + pObj.name
                        } else {
                            mergedPipeNameFinal = pObj.name
                        }
                        window[newMainGnum].lastPipeName = pObj.name;
                        window[newMainGnum].mergedPipeName = mergedPipeNameFinal;
                        // create new SVG workplace inside panel, if not added before
                        await openSubPipeline(newPiID, window[newMainGnum]);
                        // add pipeline circle to main workplace
                        addPipeline(
                            newPiID,
                            pObj.x,
                            pObj.y,
                            pObj.name,
                            pObj,
                            window[newMainGnum]
                        );
                    } else {
                        loadPipeline(
                            pObj.x,
                            pObj.y,
                            pObj.pId,
                            pObj.name,
                            processModules,
                            pObj.gNum,
                            pObj
                        );
                    }
                }
                pObj.ed = sData.edges.slice();
                pObj.ed = JSON5.parse(pObj.ed)["edges"];
                for (var ee = 0; ee < pObj.ed.length; ee++) {
                    pObj.eds = pObj.ed[ee].split("_");
                    createEdges(pObj.eds[0], pObj.eds[1], pObj);
                }
            }
        }
    }
}

async function openPipeline(id) {
    createSVG();
    sData = [window.pipeObj["main_pipeline_" + id]];
    if (sData) {
        if (Object.keys(sData).length > 0) {
            nodes = sData[0].nodes;
            nodes = JSON5.parse(nodes);
            mG = sData[0].mainG;
            mG = JSON5.parse(mG)["mainG"];
            translateSVG(mG, window);
            for (var key in nodes) {
                x = nodes[key][0];
                y = nodes[key][1];
                pId = nodes[key][2];
                name = cleanProcessName(nodes[key][3]);
                var processModules = nodes[key][4];
                gNum = parseInt(key.split("-")[1]);
                //for pipeline circles
                if (pId.match(/p(.*)/)) {
                    var piID = pId.match(/p(.*)/)[1];
                    var newMainGnum = "pObj" + gNum;
                    window[newMainGnum] = {};
                    window[newMainGnum].piID = piID;
                    window[newMainGnum].MainGNum = gNum;
                    window[newMainGnum].lastGnum = gNum;
                    window[newMainGnum].sData = [
                        window.pipeObj["pipeline_module_" + piID],
                    ];
                    window[newMainGnum].lastPipeName = name;
                    window[newMainGnum].mergedPipeName = name;
                    // create new SVG workplace inside panel, if not added before
                    await openSubPipeline(piID, window[newMainGnum]);
                    // add pipeline circle to main workplace
                    addPipeline(piID, x, y, name, window, window[newMainGnum]);
                    //for process circles
                } else {
                    loadPipeline(x, y, pId, name, processModules, gNum, window);
                }
            }
            ed = sData[0].edges;
            ed = JSON5.parse(ed)["edges"];
            for (var ee = 0; ee < ed.length; ee++) {
                eds = ed[ee].split("_");
                createEdges(eds[0], eds[1], window);
            }
        }
    }
}

d3.select("#container")
    .style("background-image", "url(css/workplace_image.png)")
    .style("background-repeat", "repeat")
    .on("keydown", cancel)
    .on("mousedown", cancel);

var zoom = d3.behavior
    .zoom()
    .translate([0, 0])
    .scale(1)
    .scaleExtent([0.15, 2])
    .on("zoom", zoomed);

createSVG();

function zoomed() {
    mainG.attr(
        "transform",
        "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")"
    );
}

//kind=input/output
function drawParam(
    name,
    process_id,
    id,
    kind,
    sDataX,
    sDataY,
    paramid,
    pName,
    classtoparam,
    init,
    pColor,
    defVal,
    dropDown,
    pubWeb,
    showSett,
    inDescOpt,
    inLabelOpt,
    pubDmeta,
    pObj
) {
    var MainGNum = "";
    var prefix = "";
    if (pObj != window) {
        //load workflow of pipeline modules
        MainGNum = pObj.MainGNum;
        prefix = "p" + MainGNum; //prefix for node ids
    }
    //gnum uniqe, id same id (Written in class) in same type process
    pObj.g = d3
        .select("#mainG" + MainGNum)
        .append("g")
        .attr("id", "g" + MainGNum + "-" + pObj.gNum)
        .attr("class", "g" + MainGNum + "-" + id)
        .attr("transform", "translate(" + sDataX + "," + sDataY + ")");
    //        .on("mouseover", mouseOverG)
    //        .on("mouseout", mouseOutG)

    //gnum(written in id): uniqe, id(Written in class): same id in same type process, bc(written in type): same at all bc
    //outermost circle transparent
    pObj.g
        .append("circle")
        .attr("id", "bc" + MainGNum + "-" + pObj.gNum)
        .attr("class", "bc" + MainGNum + "-" + id)
        .attr("type", "bc")
        .attr("cx", cx)
        .attr("cy", cy)
        .attr("r", ipR + ipIor)
        .attr("fill-opacity", 0)
        .attr("fill", "#E0E0E0");

    //second outermost circle visible gray
    pObj.g
        .append("circle")
        .datum([{
            cx: 0,
            cy: 0,
        }, ])
        .attr("id", "sc" + MainGNum + "-" + pObj.gNum)
        .attr("class", "sc" + MainGNum + "-" + id)
        .attr("type", "sc")
        .attr("r", ipR + ipIor)
        .attr("fill", "#E0E0E0")
        .attr("fill-opacity", 1);
    //        .on("mouseover", scMouseOver)
    //        .on("mouseout", scMouseOut)
    //        .call(drag)

    //gnum(written in id): uniqe, id(Written in class): same id in same type process, bc(written in type): same at all bc
    //inner parameter circle

    d3.select("#g" + MainGNum + "-" + pObj.gNum)
        .append("circle")
        .attr(
            "id",
            prefix + init + "-" + id + "-" + 1 + "-" + paramid + "-" + pObj.gNum
        )
        .attr("type", "I/O")
        .attr("kind", kind) //connection candidate=input
        .attr("parentG", "g" + MainGNum + "-" + pObj.gNum)
        .attr("name", name)
        .attr("status", "standard")
        .attr("connect", "single")
        .attr("class", classtoparam)
        .attr("cx", cx)
        .attr("cy", cy)
        .attr("r", ipIor)
        .attr("fill", pColor)
        .attr("fill-opacity", 0.8)
        .on("mouseover", IOmouseOver)
        .on("mousemove", IOmouseMove)
        .on("mouseout", IOmouseOut);
    //        .on("mousedown", IOconnect)

    //gnum(written in id): unique,
    pObj.g
        .append("text")
        .attr("id", "text" + MainGNum + "-" + pObj.gNum)
        .datum([{
            cx: 0,
            cy: 20,
            name: name,
        }, ])
        .attr("font-family", "FontAwesome, sans-serif")
        .attr("font-size", "1em")
        .attr("name", name)
        .attr("class", "inOut")
        .attr("classType", kind)
        .text(truncateName(name, "inOut"))
        .attr("text-anchor", "middle")
        .attr("x", 0)
        .attr("y", 28);
    if (defVal) {
        $("#text" + MainGNum + "-" + pObj.gNum).attr("defVal", defVal);
    }
    if (dropDown) {
        $("#text" + MainGNum + "-" + pObj.gNum).attr("dropDown", dropDown);
    }
    if (showSett != null) {
        $("#text" + MainGNum + "-" + pObj.gNum).attr("showSett", showSett);
    }
    if (inDescOpt != null) $("#text" + MainGNum + "-" + pObj.gNum).data("inDescOpt", inDescOpt);
    if (inLabelOpt != null) $("#text" + MainGNum + "-" + pObj.gNum).data("inLabelOpt", inLabelOpt);

    if (pubDmeta != null) {
        $("#text" + MainGNum + "-" + pObj.gNum).data("pubDmeta", pubDmeta);
    }
    if (pubWeb) {
        $("#text" + MainGNum + "-" + pObj.gNum).attr("pubWeb", pubWeb);
    }
}

//inputText = "example" //* @textbox @description:"One inputbox is invented"
//selectText = "sel1" //* @dropdown @options:"none","sel1","sel2" @description:"One text is invented"
//checkBox = "true" //* @checkbox @description:"One checkbox is created"
//arr = ["name1","name2"] //* @input

function parseVarPart(varPart, type) {
    var splitType = type || "";
    var varName = null;
    var defaultVal = null;
    if (varPart.match(/=/)) {
        if (splitType === "condition") {
            var varSplit = varPart.split("==");
        } else {
            var varSplit = varPart.split("=");
        }
        if (varSplit.length == 2) {
            varName = $.trim(varSplit[0]);
            defaultVal = $.trim(varSplit[1]);
            // if defaultVal starts and ends with single or double quote, remove these. (keep other quotes)
            if (
                (defaultVal.charAt(0) === '"' || defaultVal.charAt(0) === "'") &&
                (defaultVal.charAt(defaultVal.length - 1) === '"' ||
                    defaultVal.charAt(defaultVal.length - 1) === "'")
            ) {
                defaultVal = defaultVal.substr(1, defaultVal.length - 2);
            } else if (
                defaultVal.charAt(0) === "[" ||
                defaultVal.charAt(defaultVal.length - 1) === "]"
            ) {
                var content = defaultVal.substr(1, defaultVal.length - 2);
                content = content.replace(/\"/g, "");
                content = content.replace(/\'/g, "");
                defaultVal = content.split(",");
            }
        }
    } // if /=/ not exist then genericCond is defined
    else {
        if (splitType === "condition") {
            varName = $.trim(varPart);
            defaultVal = null;
        }
    }
    return [varName, defaultVal];
}

//parse {var1, var2, var3}, {var5, var6} into array of [var1, var2, var3], [var5, var6]
function parseBrackets(arr, trim) {
    var finalArr = [];
    arr = arr.split("{");
    if (arr.length) {
        for (var k = 0; k < arr.length; k++) {
            if (trim) {
                arr[k] = $.trim(arr[k]);
            } else {
                if ($.trim(arr[k]) !== "") {
                    arr[k] = $.trim(arr[k]);
                }
            }
            arr[k] = arr[k].replace(/\"/g, "");
            arr[k] = arr[k].replaceAll("^'|'$", "");
            arr[k] = arr[k].replace(/\}/g, "");
            arr[k] = fixParentheses(arr[k]); //turn (a,b,c) into (a|b|c) format for multiple options
            var allcolumn = arr[k]
                .split(",")
                .map(function(item) {
                    var item = item;
                    if (trim) {
                        var item = $.trim(item);
                    } else {
                        if ($.trim(item) !== "") {
                            item = $.trim(item);
                        }
                    }
                    if (item !== "") {
                        return item;
                    }
                })
                .filter(function(item) {
                    return item !== undefined;
                });
            if (!$.isEmptyObject(allcolumn[0])) {
                finalArr.push(allcolumn);
            }
        }
    }
    return finalArr;
}

//parse for autofill: @url, @urlzip, @checkPath
function parseRegPartAutofill(regPart) {
    var url = null;
    var urlzip = null;
    var checkPath = null;
    if (regPart.match(/@/)) {
        var regSplit = regPart.split(" @");
        for (var i = 0; i < regSplit.length; i++) {
            // find url
            var urlCheck = regSplit[i].match(/^url:"(.*)"|^url:'(.*)'/i);
            if (urlCheck) {
                if (urlCheck[1]) {
                    url = urlCheck[1];
                } else if (urlCheck[2]) {
                    url = urlCheck[2];
                }
            }
            // find url
            var urlzipCheck = regSplit[i].match(/^urlzip:"(.*)"|^urlzip:'(.*)'/i);
            if (urlzipCheck) {
                if (urlzipCheck[1]) {
                    urlzip = urlzipCheck[1];
                } else if (urlzipCheck[2]) {
                    urlzip = urlzipCheck[2];
                }
            }
            // find url
            var checkPathCheck = regSplit[i].match(
                /^checkpath:"(.*)"|^checkpath:'(.*)'/i
            );
            if (checkPathCheck) {
                if (checkPathCheck[1]) {
                    checkPath = checkPathCheck[1];
                } else if (checkPathCheck[2]) {
                    checkPath = checkPathCheck[2];
                }
            }
        }
    }
    return [url, urlzip, checkPath];
}

//parse main categories: @checkbox, @textbox, @input, @dropdown, @description, @options @title @autofill @show_settings @optional @file @single_file @hidden  @label
//parse style categories: @multicolumn, @array, @condition, @spreadsheet
function parseRegPart(regPart) {
    var type = null;
    var desc = null;
    var label = null;
    var title = null;
    var tool = null;
    var showsett = null;
    var opt = null;
    var allOptCurly = null;
    var multiCol = null;
    var autoform = null;
    var optional = null;
    var file = null;
    var singleFile = null;
    var hidden = null;
    var arr = null;
    var cond = null;
    var spreadsheet = null;
    if (regPart.match(/@/)) {
        var regSplit = regPart.split(" @");
        for (var i = 0; i < regSplit.length; i++) {
            // find type among following list:checkbox|textbox|input|dropdown
            var typeCheck = regSplit[i].match(/^checkbox|^textbox|^input|^dropdown/i);
            var optionalCheck = regSplit[i].match(/^optional/i);
            var fileCheck = regSplit[i].match(/^file/i);
            var singleFileCheck = regSplit[i].match(/^single_file/i);
            var hiddenCheck = regSplit[i].match(/^hidden/i);
            // check if @autofill tag is defined //* @autofill:{var1="yes", "filling_text"}
            // for multiple options @autofill:{var1=("yes","no"), "filling_text"}
            // for dynamic filling @autofill:{var1=("yes","no"), _build+"filling_text"}
            var autofillCheck = regSplit[i].match(/^autofill:(.*)/i);
            if (autofillCheck) {
                if (autofillCheck[1]) {
                    var autoContent = autofillCheck[1];
                    if (!autoform) {
                        autoform = [];
                    }
                    autoform.push(parseBrackets(autoContent, false));
                }
            }
            // check if @multicolumn tag is defined //* @style @multicolumn:{var1, var2, var3}, {var5, var6}
            var multiColCheck = regSplit[i].match(/^multicolumn:(.*)/i);
            if (multiColCheck) {
                if (multiColCheck[1]) {
                    var multiContent = multiColCheck[1];
                    multiCol = parseBrackets(multiContent, true);
                }
            }
            // check if @array tag is defined //* @style @array:{var1, var2}, {var4}
            var arrayCheck = regSplit[i].match(/^array:(.*)/i);
            if (arrayCheck) {
                if (arrayCheck[1]) {
                    var arrContent = arrayCheck[1];
                    arr = parseBrackets(arrContent, true);
                }
            }
            // check if @spreadsheet tag is defined //* @style @spreadsheet:{var1, var2}, {var4}
            var spreadsheetCheck = regSplit[i].match(/^spreadsheet:(.*)/i);
            if (spreadsheetCheck && spreadsheetCheck[1]) {
                spreadsheet = parseBrackets(spreadsheetCheck[1], true);
            }
            // check if @condition tag is defined //* @style @condition:{var1="yes", var2}, {var1="no", var3, var4}
            var condCheck = regSplit[i].match(/^condition:(.*)/i);
            if (condCheck) {
                if (condCheck[1]) {
                    var condContent = condCheck[1];
                    if (!cond) {
                        cond = [];
                    }
                    cond.push(parseBrackets(condContent, true));
                }
            }
            if (typeCheck) {
                type = typeCheck[0].toLowerCase();
            }
            if (optionalCheck) optional = true;
            if (fileCheck) file = true;
            if (singleFileCheck) singleFile = true;
            if (hiddenCheck) hidden = true;

            // find description
            var descCheck = regSplit[i].match(
                /^description:"(.*)"|^description:'(.*)'/i
            );
            if (descCheck) {
                if (descCheck[1]) {
                    desc = descCheck[1];
                } else if (descCheck[2]) {
                    desc = descCheck[2];
                }
            }
            // find label
            var labelCheck = regSplit[i].match(
                /^label:"(.*)"|^label:'(.*)'/i
            );
            if (labelCheck) {
                if (labelCheck[1]) {
                    label = labelCheck[1];
                } else if (labelCheck[2]) {
                    label = labelCheck[2];
                }
            }
            // find title
            var titleCheck = regSplit[i].match(/^title:"(.*)"|^title:'(.*)'/i);
            if (titleCheck) {
                if (titleCheck[1]) {
                    title = titleCheck[1];
                } else if (titleCheck[2]) {
                    title = titleCheck[2];
                }
            }
            // find tooltip
            var toolCheck = regSplit[i].match(/^tooltip:"(.*)"|^tooltip:'(.*)'/i);
            if (toolCheck) {
                if (toolCheck[1]) {
                    tool = toolCheck[1];
                } else if (toolCheck[2]) {
                    tool = toolCheck[2];
                }
            }
            // find show_settings
            var show_settCheck = regSplit[i].match(
                /^show_settings:"(.*)"|^show_settings:'(.*)'/i
            );
            if (show_settCheck) {
                if (show_settCheck[1]) {
                    showsett = show_settCheck[1];
                } else if (show_settCheck[2]) {
                    showsett = show_settCheck[2];
                }
                //seperate process names by comma
                if (showsett) {
                    showsett = showsett.split(",");
                    if (showsett.length) {
                        for (var k = 0; k < showsett.length; k++) {
                            showsett[k] = $.trim(showsett[k]);
                            showsett[k] = showsett[k].replace(/\"/g, "");
                            showsett[k] = showsett[k].replace(/\'/g, "");
                        }
                    }
                }
            }
            // find options
            var optCheck = regSplit[i].match(
                /^options:\s*"(.*)"|^options:\s*'(.*)'|^options:\s*\{(.*)\}/i
            );
            if (optCheck) {
                if (optCheck[1]) {
                    var allOpt = optCheck[1];
                } else if (optCheck[2]) {
                    var allOpt = optCheck[2];
                } else if (optCheck[3]) {
                    var allOpt = null;
                    var curlyOpt = regSplit[i].match(/^options:\s*(.*)/i);
                    if (curlyOpt[1]) {
                        opt = parseBrackets(curlyOpt[1], true);
                    }
                }
                //seperate options by comma
                if (allOpt) {
                    allOpt = allOpt.split(",");
                    if (allOpt.length) {
                        for (var k = 0; k < allOpt.length; k++) {
                            allOpt[k] = $.trim(allOpt[k]);
                            allOpt[k] = allOpt[k].replace(/\"/g, "");
                            allOpt[k] = allOpt[k].replace(/\'/g, "");
                        }
                    }
                    opt = allOpt;
                }
            }
        }
    }
    return [
        type,
        desc,
        tool,
        opt,
        multiCol,
        arr,
        cond,
        title,
        autoform,
        showsett,
        optional,
        file,
        singleFile,
        hidden,
        spreadsheet,
        label
    ];
}

//remove parent div from process options field
function removeParentDiv(button) {
    $(button).parent().parent().remove();
}
//add array from div to process options field
function appendBeforeDiv(button) {
    var div = $(button).parent().prev();
    div.parent().find('[data-toggle="tooltip"]').tooltip("destroy");
    var divID = div.attr("id"); //var1_var2_var3_var4_ind1
    var groupID = divID.match(/(.*)_ind(.*)$/)[1]; //var1_var2_var3_var4
    var divNum = divID.match(/(.*)_ind(.*)$/)[2]; //ind1
    var origin = div.parent().find("div#" + groupID + "_ind0"); //hidden original div
    divNum = parseInt(divNum) + 1;
    //clone with event handlers
    $(origin).clone(true, true).insertAfter($(div));
    var newDiv = $(button)
        .parent()
        .prev()
        .attr("id", groupID + "_ind" + divNum)
        .css("display", "inline");
    newDiv.find("select").trigger("change");
    newDiv.find("input:checkbox").trigger("change");
    $('[data-toggle="tooltip"]').tooltip();
}

function findDynamicArr(optArr, gNum) {
    var opt = null;
    for (var t = 0; t < optArr.length; t++) {
        if (optArr[t].varNameCond && optArr[t].selOpt && optArr[t].autoVal) {
            var checkVal = checkDynamicVar(
                optArr[t].varNameCond,
                autoFillJSON,
                null,
                gNum
            );
            if (checkVal) {
                optArr[t].varNameCond = checkVal;
                if (optArr[t].varNameCond == optArr[t].selOpt) {
                    opt = optArr[t].autoVal;
                    return opt;
                }
            }
        }
    }
    return opt;
}

// at this position we know that default option will be shown (other options are not valid or conditionally seen options are available -> keep these option in hiddenOpt)
function findDefaultArr(optArr) {
    var defaultOpt = null;
    var hiddenOpt = [];
    var allOpt = [];
    for (var t = 0; t < optArr.length; t++) {
        $.extend(allOpt, optArr[t].autoVal);
        if (!optArr[t].varNameCond && !optArr[t].selOpt && optArr[t].autoVal) {
            var mergedOpt = optArr[t].autoVal.join("");
            if (!mergedOpt.match(/=/)) {
                defaultOpt = optArr[t].autoVal;
            }
        }
    }
    hiddenOpt = $(allOpt).not(defaultOpt).get();
    return [defaultOpt, hiddenOpt, allOpt];
}

//insert form fields into panels of process options
function addProcessPanelRow(gNum, name, varName, defaultVal, type, desc, opt, tool, multicol, array, title, hidden, spreadsheet, labelText) {
    if ($.isArray(defaultVal)) {
        defaultVal = "";
    }
    var checkInsert = $("#addProcessRow-" + gNum)
        .find("[id]")
        .filter(function() {
            return $(this).attr("id") === "var_" + gNum + "-" + varName;
        });
    if (!checkInsert.length) {
        var hiddenOpt = null; // if conditional dropdown options are defined
        var hiddenText = "";
        var allOpt = null; // if conditional dropdown options are defined
        var optArr = []; // for dropdown options
        var arrayCheck = false; //is it belong to array
        var spreadsheetCheck = false; //is it belong to spreadsheet
        var clearFix = ""; //if its the first element of multicol
        var arrayId = "";
        var sheetId = "";
        var columnPercent = 100;
        var descText = "";
        var descTextVar = "";
        if (title) {
            $("#addProcessRow-" + gNum).append(
                '<div style="font-size:16px; font-weight:bold; background-color:#F5F5F5; float:left; padding:5px; margin-bottom:8px; width:100%;">' +
                title +
                '<div  style="border-bottom:1px solid #d5d5d5;" ></div></div>'
            );
        }
        if (desc) {
            descText =
                '<p style=" font-style:italic; color:darkslategray; font-weight: 300; font-size:13px">' +
                desc +
                "</p>";
            descTextVar =
                '<p style=" font-style:italic; color:darkslategray; font-weight: 300; font-size:13px; margin: 0 0 2px;">*' + varName + ": " +
                desc +
                "</p>";
        }
        // if multicol defined then calc columnPercent based on amount of element
        if (multicol) {
            $.each(multicol, function(el) {
                if (multicol[el].indexOf(varName) > -1) {
                    var columnCount = multicol[el].length;
                    columnPercent = Math.floor((columnPercent / columnCount) * 100) / 100;
                }
                if (multicol[el].indexOf(varName) === 0) {
                    clearFix = " clear:both; ";
                }
            });
        }
        // if spreadsheet defined then create spreadsheetdiv 
        if (spreadsheet) {
            $.each(spreadsheet, function(el) {
                if (spreadsheet[el].indexOf(varName) > -1) {
                    spreadsheetCheck = true;
                    sheetId = "spreadsheet_" + gNum + "_" + spreadsheet[el].join("_");
                    if (!$("#addProcessRow-" + gNum).find("#" + sheetId)[0]) {
                        $("#addProcessRow-" + gNum).append(
                            '<div style="float:left;  padding: 5px; width: 100%;" class="form-group"><label style="display:none;">' + sheetId + '</label><div class="" gnum="' + gNum + '" id="' + sheetId + '" ></div><div class="description" ></div></div>'
                        );
                        var container = document.getElementById(sheetId);
                        var hot = new Handsontable(container, {
                            className: 'dnext_spreadsheet',
                            data: [],
                            colHeaders: true,
                            width: '100%',
                            height: 370,
                            rowHeaders: true,
                            stretchH: 'all',
                            contextMenu: true,
                            columnSorting: {
                                indicator: true,
                                headerAction: true
                            },
                            trimWhitespace: false,
                        });
                        $runscope.handsontable[sheetId] = hot
                    }
                    if (descTextVar) {
                        var olddescription = $(`#${sheetId}`).next().html()
                        $(`#${sheetId}`).attr("type", "spreadsheet")
                        $(`#${sheetId}`).next().html(olddescription + descTextVar)
                    }
                }
            })
        }

        // if array defined then create arraydiv and remove/add buttons.
        if (array) {
            $.each(array, function(el) {
                if (array[el].indexOf(varName) > -1) {
                    arrayId = array[el].join("_");
                    arrayCheck = true;
                    if (!$("#addProcessRow-" + gNum).find("#" + arrayId + "_ind0")[0]) {
                        //insert array group div
                        $("#addProcessRow-" + gNum).append(
                            '<div id="' +
                            arrayId +
                            '_ind0" style="display:none; float:left; background-color: #F5F5F5; padding:10px; margin-bottom:8px; width:100%;">  <div id="delDiv" style="width:100%; float:left;" >' +
                            getButtonsDef(gNum + "_", "Remove", arrayId) +
                            "</div></div>"
                        );
                        //append add button
                        $("#addProcessRow-" + gNum).append(
                            '<div id="addDiv" style="float:left; margin-bottom:8px; width:100%; class="form-group">' +
                            getButtonsDef(gNum + "_", "Add", arrayId) +
                            "</div>"
                        );
                        //add onclick remove div feature
                        $(
                            "#addProcessRow-" +
                            gNum +
                            "> #" +
                            arrayId +
                            "_ind0 > #delDiv > button"
                        ).attr("onclick", "javascript:removeParentDiv(this)");
                        //add onclick append div feature
                        $(
                            "#addProcessRow-" +
                            gNum +
                            "> #" +
                            arrayId +
                            "_ind0 + #addDiv > button"
                        ).attr("onclick", "javascript:appendBeforeDiv(this)");
                    }
                }
            });
        }
        if (tool && tool != "") {
            var toolText =
                ' <span><a data-toggle="tooltip" data-placement="bottom" title="' +
                tool +
                '"><i class="glyphicon glyphicon-info-sign"></i></a></span>';
        } else {
            var toolText = "";
        }

        if (hidden) {
            hiddenText = "display:none; "
        }

        var processParamDiv =
            '<div  class="form-group" style="' +
            hiddenText + clearFix +
            "float:left; padding:5px; width:" +
            columnPercent +
            '%;" >';
        var labelTextFinal = varName;
        if (labelText) labelTextFinal = labelText;
        var label =
            '<label style="font-weight:600; word-break:break-all;">' + labelTextFinal + toolText + ' </label><span style="display:none;">' + varName + '</span>';
        if (type === "input") {
            var inputDiv =
                '<input type="text" class="form-control" style="padding:15px;" id="var_' +
                gNum +
                "-" +
                varName +
                '" name="var_' +
                gNum +
                "-" +
                varName +
                '" value="' +
                defaultVal +
                '">';
            processParamDiv += label + inputDiv + descText + "</div>";
        } else if (type === "textbox") {
            var inputDiv =
                '<textarea class="form-control" id="var_' +
                gNum +
                "-" +
                varName +
                '" name="var_' +
                gNum +
                "-" +
                varName +
                '">' +
                defaultVal +
                "</textarea>";
            processParamDiv += label + inputDiv + descText + "</div>";
        } else if (type === "checkbox") {
            if (defaultVal) {
                if (defaultVal === "true") {
                    defaultVal = "checked";
                } else {
                    defaultVal = "";
                }
            }
            var inputDiv =
                '<input type="checkbox" style = "margin-right:5px;" class="form-check-input" id="var_' +
                gNum +
                "-" +
                varName +
                '" name="var_' +
                gNum +
                "-" +
                varName +
                '" ' +
                defaultVal +
                ">";
            processParamDiv += inputDiv + label + descText + "</div>";
        } else if (type === "dropdown") {
            var inputDiv =
                '<select type="dropdown" class="form-control" id="var_' +
                gNum +
                "-" +
                varName +
                '" name="var_' +
                gNum +
                "-" +
                varName +
                '">';
            var optionDiv = "";
            var defaultOpt = null;
            var dynamicOpt = null;
            var optOrg = opt;
            if (opt) {
                if (opt.length) {
                    //check if conditional options are defined.
                    var condOptCheck = $.isArray(opt[0]);
                    if (condOptCheck) {
                        //conditional options
                        optArr = createDynFillArr([opt]);
                        //check if dynamic variables (_var) exist in varNameCond
                        dynamicOpt = findDynamicArr(optArr, gNum);
                        if (dynamicOpt) {
                            opt = dynamicOpt;
                        } else {
                            // check if default option is defined(without =)
                            // check if conditional options are defined and keep them in hiddenOpt
                            [opt, hiddenOpt, allOpt] = findDefaultArr(optArr);
                        }
                    }
                    if (opt) {
                        for (var k = 0; k < opt.length; k++) {
                            if (defaultVal === opt[k]) {
                                optionDiv +=
                                    '<option value="' +
                                    opt[k] +
                                    '" selected>' +
                                    opt[k] +
                                    " </option>";
                            } else {
                                optionDiv +=
                                    '<option value="' + opt[k] + '">' + opt[k] + " </option>";
                            }
                        }
                    }
                    if (hiddenOpt) {
                        for (var k = 0; k < hiddenOpt.length; k++) {
                            optionDiv +=
                                '<option style="display:none;" value="' +
                                hiddenOpt[k] +
                                '">' +
                                hiddenOpt[k] +
                                " </option>";
                        }
                    }
                }
            }
            processParamDiv +=
                label + inputDiv + optionDiv + "</select>" + descText + "</div>";
        }


        if (spreadsheetCheck === true) {
            //skip insertion. -> spreadsheet created
        } else if (arrayCheck === false) {
            $("#addProcessRow-" + gNum).append(processParamDiv);
        } else {
            // if array defined then append each element into that arraydiv.
            $("#addProcessRow-" + gNum + "> #" + arrayId + "_ind0").append(
                processParamDiv
            );
            $(
                "#addProcessRow-" + gNum + "> #" + arrayId + "_ind0 > #delDiv"
            ).insertAfter(
                $("#addProcessRow-" + gNum + "> #" + arrayId + "_ind0 div:last")
            ); //keep remove button at last
        }
        //bind event handler to dynamically show dropdown options
        if (hiddenOpt && optArr && allOpt) {
            $.each(optArr, function(el) {
                if (optArr[el].selOpt) {
                    if (!optArr[el].selOpt.match(/\|/)) {
                        var dataGroup = $.extend(true, {}, optArr[el]);
                        dataGroup.type = type;
                        var varNameCond = dataGroup.varNameCond;
                        //find dropdown based condition changes are created.
                        //in order to grep all array rows which has same id, following jquery pattern is used.
                        var condDiv = $('[id="var_' + gNum + "-" + varNameCond + '"]');
                        //bind change event to dropdown
                        $.each(condDiv, function(eachArrayForm) {
                            $(condDiv[eachArrayForm]).change(dataGroup, function() {
                                var lastdataGroup = $.extend(true, {}, dataGroup);
                                var autoVal = lastdataGroup.autoVal;
                                var varNameCond = lastdataGroup.varNameCond;
                                var selOpt = lastdataGroup.selOpt;
                                var type = lastdataGroup.type;
                                var selectedVal = "";
                                if (type == "dropdown") {
                                    selectedVal = $(this).val();
                                }
                                var parentDiv = $(this).parent().parent();
                                if (selectedVal === selOpt) {
                                    //show only valid options
                                    for (var k = 0; k < autoVal.length; k++) {
                                        parentDiv
                                            .find("#var_" + gNum + "-" + varName)
                                            .children("option[value=" + autoVal[k] + "]")
                                            .css("display", "block");
                                    }
                                    //hide option if they are not valid
                                    var hideOpt = $(allOpt).not(autoVal).get();
                                    for (var k = 0; k < hideOpt.length; k++) {
                                        var oldOpt = parentDiv
                                            .find("#var_" + gNum + "-" + varName)
                                            .children("option[value=" + hideOpt[k] + "]");
                                        oldOpt.css("display", "none");
                                        // if option selected that select first opt of dropdown
                                        if (oldOpt.is(":selected")) {
                                            parentDiv
                                                .find("#var_" + gNum + "-" + varName)
                                                .prop("selectedIndex", 0);
                                        }
                                    }
                                }
                            });
                            $(condDiv[eachArrayForm]).trigger("change");
                        });
                    }
                }
            });
        }
    }
}

//check if dynamic variables (_var) exist in autoVal
function checkDynamicVar(autoVal, autoFillJSON, parentDiv, gNum) {
    if (
        autoFillJSON !== null &&
        autoFillJSON !== undefined &&
        autoVal.match(/^_/)
    ) {
        for (var k = 0; k < autoFillJSON.length; k++) {
            if (autoFillJSON[k].library[autoVal]) {
                //check if condition is met
                var conds = autoFillJSON[k].condition;
                var genConds = autoFillJSON[k].genCondition;
                if (conds && !$.isEmptyObject(conds)) {
                    var statusCond = checkConds(conds);
                    if (statusCond === true) {
                        return autoFillJSON[k].library[autoVal];
                    }
                } else if ($.isEmptyObject(conds) && $.isEmptyObject(genConds)) {
                    return autoFillJSON[k].library[autoVal];
                }
            }
        }
        if (parentDiv) {
            if (parentDiv.find("#var_" + gNum + "-" + autoVal)) {
                return parentDiv.find("#var_" + gNum + "-" + autoVal).val();
            }
        }
        return "";
    } else {
        return autoVal;
    }
}

//convert {var1="yes", "filling_text"} or {var1=("yes","no"), _build+"filling_text"} to array format: [{varNameCond: "var1", selOpt: "yes", autoVal: "filling_text"}]
function createDynFillArr(autoform) {
    var allAutoForm = [];
    //find condition dependent varNameCond, selOpt, and autoVal
    $.each(autoform, function(elem) {
        var condArr = autoform[elem];
        for (var n = 0; n < condArr.length; n++) {
            // it was > 1 before, why?
            if (condArr[n].length > 0) {
                var autoObj = { varNameCond: null, selOpt: null, autoVal: [] };
                for (var k = 0; k < condArr[n].length; k++) {
                    // check if condArr has element like "var1=yes" where varName=var1 or "var1=(yes|no)"
                    if (condArr[n][k].match(/=/)) {
                        autoObj.varNameCond = condArr[n][k].match(/(.*)=(.*)/)[1];
                        autoObj.selOpt = condArr[n][k].match(/(.*)=(.*)/)[2];
                    } else {
                        autoObj.autoVal.push(condArr[n][k]);
                    }
                }
                allAutoForm.push(autoObj);
            }
        }
    });

    //if selOpt contains multiple options: (rRNA|ercc|miRNA|tRNA|piRNA|snRNA|rmsk)
    $.each(allAutoForm, function(el) {
        var selOpt = allAutoForm[el].selOpt;
        if (selOpt) {
            if (selOpt.match(/\|/)) {
                selOpt = selOpt.replace("(", "");
                selOpt = selOpt.replace(")", "");
                var allOpt = selOpt.split("|");
                for (var n = 0; n < allOpt.length; n++) {
                    var newData = $.extend(true, {}, allAutoForm[el]);
                    newData.selOpt = allOpt[n];
                    allAutoForm.push(newData);
                }
            }
        }
    });
    return allAutoForm;
}

// if @autofill exists, then create event binders
//eg.  @autofill:{var1="yes", "filling_text"}
// for multiple options @autofill:{var1=("yes","no"), "filling_text"}
// for dynamic filling @autofill:{var1=("yes","no"), _build+"filling_text"}
function addProcessPanelAutoform(gNum, name, varName, type, autoform) {
    var allAutoForm = [];
    if (autoform) {
        allAutoForm = createDynFillArr(autoform);
        //bind event handlers
        $.each(allAutoForm, function(el) {
            var dataGroup = $.extend(true, {}, allAutoForm[el]);
            dataGroup.type = type;
            var varNameCond = dataGroup.varNameCond;
            //find dropdown/checkbox where condition based changes are created.
            //in order to grep all array rows which has same id, following jquery pattern is used.
            var condDiv = $('[id="var_' + gNum + "-" + varNameCond + '"]');
            //bind change event to dropdown
            $.each(condDiv, function(eachArrayForm) {
                $(condDiv[eachArrayForm]).change(dataGroup, function() {
                    var lastdataGroup = $.extend(true, {}, dataGroup);
                    var autoVal = lastdataGroup.autoVal[0];
                    var varNameCond = lastdataGroup.varNameCond;
                    var selOpt = lastdataGroup.selOpt;
                    var type = lastdataGroup.type;
                    var selectedVal = "";
                    if (type == "checkbox") {
                        selectedVal = $(this).is(":checked").toString();
                    } else {
                        selectedVal = this.value;
                    }
                    var parentDiv = $(this).parent().parent();
                    if (selectedVal === selOpt) {
                        //if autoval contains "+" operator
                        if (autoVal.match(/\+/)) {
                            var autoValAr = autoVal.split("+");
                            for (var n = 0; n < autoValAr.length; n++) {
                                autoValAr[n] = checkDynamicVar(
                                    autoValAr[n],
                                    autoFillJSON,
                                    parentDiv,
                                    gNum
                                );
                            }
                            autoVal = autoValAr.join("");
                        } else {
                            //check if dynamic variables (_var) exist in autoVal
                            autoVal = checkDynamicVar(autoVal, autoFillJSON, parentDiv, gNum);
                        }
                        if (autoVal) {
                            parentDiv.find("#var_" + gNum + "-" + varName).val(autoVal);
                        }
                    }
                });
                $(condDiv[eachArrayForm]).trigger("change");
            });
        });
    }
}

// if @condi is exist, then create event binders
//eg condi = ["var2", "var1=yes"], ["var1=no", "var3", "var4"]
// checkbox and dropdown is supported
function addProcessPanelCondi(gNum, name, varName, type, condi) {
    var allCondForms = []; //all condition dependent forms
    var allUniCondForms = []; //all unique condition dependent forms
    var showFormVarArr = [];
    var dataGroup = {};
    var varN = "";
    var restN = "";
    if (condi) {
        //find all condition dependent forms
        $.each(condi, function(elem) {
            var condArr = condi[elem];
            // check if condArr has element like "var1=yes" where varName=var1
            var filt = condArr.find((a) =>
                new RegExp("^" + varName + "\\s*=").test(a)
            );
            if (filt) {
                //remove the elements where condition is defined
                showFormVarArr = condArr.filter((a) => {
                    return a !== filt;
                });
                allCondForms = allCondForms.concat(showFormVarArr);
            }
        });
        // push all unique form names as array
        allUniCondForms = allCondForms.filter(function(item, pos, self) {
            return self.indexOf(item) == pos;
        });
        //bind event handlers
        $.each(condi, function(el) {
            var newdataGroup = $.extend(true, {}, dataGroup);
            var condArr = condi[el];
            // check if condArr has element like "var1=yes" where varName=var1
            var filt = condArr.find((a) =>
                new RegExp("^" + varName + "\\s*=").test(a)
            );
            if (filt) {
                //trigger when selOpt is selected
                var selOpt = $.trim(filt.split("=")[1]);
                //find condition dependent forms
                showFormVarArr = condArr.filter((a) => {
                    return a !== filt;
                });
                //initially hide all condition dependent forms
                for (var k = 0; k < showFormVarArr.length; k++) {
                    var showHideDiv = $("#addProcessRow-" + gNum).find(
                        "#var_" + gNum + "-" + showFormVarArr[k]
                    )[0];
                    $(showHideDiv).parent().css("display", "none");
                }
                //find dropdown/checkbox where condition based changes are created.
                var condDiv = $("#addProcessRow-" + gNum).find(
                    "#var_" + gNum + "-" + varName
                )[0];
                //bind change event to dropdown
                newdataGroup.showFormVarArr = showFormVarArr;
                newdataGroup.allUniCondForms = allUniCondForms;
                newdataGroup.selOpt = selOpt;
                newdataGroup.type = type;

                $(condDiv).change(newdataGroup, function() {
                    var lastdataGroup = $.extend(true, {}, newdataGroup);
                    var showForms = lastdataGroup.showFormVarArr;
                    var allUniqForms = lastdataGroup.allUniCondForms;
                    var selOpt = lastdataGroup.selOpt;
                    var type = lastdataGroup.type;
                    var selectedVal = "";
                    if (type == "checkbox") {
                        selectedVal = $(this).is(":checked").toString();
                    } else {
                        selectedVal = this.value;
                    }
                    var parentDiv = $(this).parent().parent();
                    var hideForms = $(allUniqForms).not(showForms).get();
                    if (selectedVal === selOpt) {
                        for (var i = 0; i < showForms.length; i++) {
                            var showDiv = parentDiv.find(
                                "#var_" + gNum + "-" + showForms[i]
                            )[0];
                            if (showDiv) {
                                $(showDiv).parent().css("display", "inline");
                            }
                        }
                        for (var i = 0; i < hideForms.length; i++) {
                            var hideDiv = parentDiv.find(
                                "#var_" + gNum + "-" + hideForms[i]
                            )[0];
                            if (hideDiv) {
                                $(hideDiv).parent().css("display", "none");
                            }
                        }
                    }
                });
                $(condDiv).trigger("change");
            }
        });
    }
}

// check if all conditions match
//type="default" will pass for $HOSTNAME == default
function checkConds(conds, type) {
    var checkConditionsFalse = [];
    var checkConditionsTrue = [];
    $.each(conds, function(co) {
        //check if condtion is $HOSTNAME specific
        if (co === "$HOSTNAME") {
            var hostname = conds[co];
            var chooseEnvHost = $("#chooseEnv").find(":selected").attr("host");
            if (hostname && chooseEnvHost && hostname === chooseEnvHost) {
                checkConditionsTrue.push(true);
            } else {
                if (
                    type == "default" &&
                    hostname &&
                    chooseEnvHost &&
                    hostname == "default"
                ) {
                    checkConditionsTrue.push(true);
                } else {
                    checkConditionsFalse.push(false);
                }
            }
        } else {
            var varName = co.match(/params\.(.*)/)[1]; //variable Name
            var defName = conds[co]; // expected Value
            var checkVarName = $("#inputsTab, .ui-dialog").find(
                "td[given_name='" + varName + "']"
            )[0];
            if (checkVarName) {
                var varNameBut = $(checkVarName).find(".firstsec >")[0];
                if (varNameBut) {
                    var varNameVal = $(varNameBut).val();
                }
                if (varNameVal && defName && varNameVal === defName) {
                    checkConditionsTrue.push(true);
                } else {
                    checkConditionsFalse.push(false);
                }
            }
        }
    });
    // if all conditions match, length==0 for checkConditionsFalse
    if (checkConditionsFalse.length === 0 && checkConditionsTrue.length > 0) {
        return true;
    } else {
        return false;
    }
}

function getInputVariables(button) {
    var rowID = button.closest("tr").attr("id"); //"inputTa-5"
    var gNumParam = rowID.split("Ta-")[1];
    var given_name = $("#input-PName-" + gNumParam).attr("name"); //input-PName-3
    var qualifier = $("#" + rowID + " > :nth-child(4)").text();
    var sType = "";
    if (qualifier === "file" || qualifier === "set") {
        sType = "file"; //for simplification
    } else if (qualifier === "val") {
        sType = "val";
    } else if (qualifier === "single_file") {
        sType = "single_file";
    }
    return [rowID, gNumParam, given_name, qualifier, sType];
}

showHideSett = function(rowId) {
    var dropdownID = $("#" + rowId)
        .find(".firstsec > select")
        .attr("id");
    var buttons = $("#" + rowId).find(".firstsec > button[id^=show_sett_]");
    if (buttons.length > 0) {
        for (var i = 0; i < buttons.length; i++) {
            var buttonId = $(buttons[i]).attr("id");
            var yesCheck = $("#" + dropdownID).val();
            if (yesCheck) {
                if (yesCheck.toLowerCase() == "yes") {
                    $("#" + buttonId).css("display", "inline");
                } else {
                    $("#" + buttonId).css("display", "none");
                }
            } else {
                $("#" + buttonId).css("display", "none");
            }
        }
    }
};

function hideHiddenProcessOptions() {
    var showSettingInputsAr = $("#inputsTable > tbody > tr[show_setting]");
    if (showSettingInputsAr.length > 0) {
        for (var i = 0; i < showSettingInputsAr.length; i++) {
            var tooltip = "Settings";
            var insertButton = false;
            var rowId = $(showSettingInputsAr[i]).attr("id");
            var givenName = $(showSettingInputsAr[i])
                .find("td[given_name]")
                .attr("given_name");
            var dropdownID = $(showSettingInputsAr[i])
                .find(".firstsec > select")
                .attr("id");
        }
    }
}

function hideProcessOptionsAsIcons() {
    // allows click events when new modal opens after ui dialog
    $.widget("ui.dialog", $.ui.dialog, {
        _allowInteraction: function(event) {
            return !!true || this._super(event);
        }
    });

    // prepare handsontable's for  ui-dialog
    var allpanels = $('#ProcessPanel > div[processorgname]')
    for (let i = 0; i < allpanels.length; i++) {
        var collapseIconDiv = $(allpanels[i]).find(".collapseIconDiv").next()
        var collapseIconDivID = collapseIconDiv.attr("id")
        let spreadsheets = $(`#${collapseIconDivID}`).find("div.dnext_spreadsheet");
        if (spreadsheets.length) {
            collapseIconDiv.collapse("toggle")
        }
        for (var s = 0; s < spreadsheets.length; s++) {
            let spreadsheetID = $(spreadsheets[s]).attr("id")
            if ($runscope.handsontable[spreadsheetID]) {
                $runscope.handsontable[spreadsheetID].render()
                $(spreadsheets[s]).css("display", "inline-block")
            }
        }
    }




    var showSettingInputsAr = $("#inputsTable > tbody > tr[show_setting]");
    if (showSettingInputsAr.length > 0) {
        for (var i = 0; i < showSettingInputsAr.length; i++) {
            var tooltip = "Settings";
            var insertButton = false;
            var rowId = $(showSettingInputsAr[i]).attr("id");
            var givenName = $(showSettingInputsAr[i])
                .find("td[given_name]")
                .attr("given_name");
            var dropdownID = $(showSettingInputsAr[i])
                .find(".firstsec > select")
                .attr("id");
            var show_setting = $(showSettingInputsAr[i]).attr("show_setting");
            var show_settingArr = show_setting.split(",");
            var wrapperID = "wrapper-" + i;
            var buttonId = "show_sett_" + wrapperID;
            $("#ProcessPanel").append('<div id="' + wrapperID + '"></div>');
            $("#" + wrapperID).append(
                `<ul id="${wrapperID}-ul" class="nav nav-tabs"></ul><div class="tab-content"></div>`
            );

            for (var t = 0; t < show_settingArr.length; t++) {
                show_setting = show_settingArr[t];
                var show_settingProcessPanel = $(
                    '#ProcessPanel > div[processorgname="' +
                    show_setting +
                    '"],div[allname="' +
                    show_setting +
                    '"]'
                );

                var paramsPrefix = "params.";
                if (show_setting.substring(0, paramsPrefix.length) === paramsPrefix) {
                    var varName = show_setting.replace(paramsPrefix, "");
                    var checkVarName = $("#inputsTab, .ui-dialog").find("td[given_name='" + varName + "']").parent();
                    if (checkVarName[0]) {
                        var checkOptional = checkVarName.attr("optional")
                        var labelText = checkVarName.attr("label")
                        var finalText = varName;
                        if (checkOptional) finalText = `${varName} (Optional)`;
                        if (labelText) finalText = labelText;
                        var panelContent = `<table style="margin-bottom:10px;" class="table"><thead>
<tr>
<th style="display:none; width:30%;" scope="col">Given Name</th>
<th style="display:none;" scope="col">Identifier</th>
<th style="display:none;" scope="col">File Type</th>
<th style="display:none;" scope="col">Qualifier</th>
<th style="display:none;" scope="col">Process Name</th>
<th style="width:70%; padding-bottom:4px;" scope="col">${finalText}</th>
</tr>
</thead><tbody id="processInputs-${i}" style="word-break: break-all;"></tbody></table>`;
                    }
                    $(panelContent).prependTo("#" + wrapperID)
                    checkVarName.appendTo(`#processInputs-${i}`)
                    checkVarName.css("display", "table-row")

                    checkVarName.children(":first").css("display", "none")
                    if (!$("#" + buttonId).length) {
                        insertButton = true;
                    }
                }
                for (var k = 0; k < show_settingProcessPanel.length; k++) {
                    var panel = $(show_settingProcessPanel[k]);
                    var panelContent = $(show_settingProcessPanel[k])
                        .children()
                        .children()
                        .eq(1);

                    var label = panel.attr("label");
                    var processname = panel.attr("processname");
                    var processorgname = panel.attr("processorgname");
                    var allname = panel.attr("allname");
                    var modulename = panel.attr("modulename");
                    var activeClass = "";
                    if ($("#" + wrapperID + "-ul").is(":empty")) {
                        activeClass = "active";
                    }
                    if (!$("#" + wrapperID + "-ul").find(
                            `a[href*="#${wrapperID}-${modulename}"]`
                        ).length) {
                        if (modulename) {
                            $("#" + wrapperID + "-ul").append(
                                `<li class="${activeClass}"><a class="nav-item" data-toggle="tab" href="#${wrapperID}-${modulename}">${modulename}</a></li>`
                            );
                        }
                        // else {
                        //   $("#" + wrapperID + "-ul").css("display", "none");
                        // }

                        $("#" + wrapperID + "-ul")
                            .next()
                            .append(
                                `<div id="${wrapperID}-${modulename}" class="tab-pane fade in ${activeClass}"></div>`
                            );
                    }
                    $(`#${wrapperID}-${modulename}`).append(
                        `<div style="font-size:16px; font-weight:bold; float:left; padding:5px; margin-bottom:8px; margin-top:5px; width:100%;">${processname}<div style="border-bottom:1px solid #d5d5d5;"></div></div>`
                    );
                    panelContent
                        .removeClass("collapse")
                        .appendTo(`#${wrapperID}-${modulename}`);


                    tooltip = "Settings: " + processname;
                    if (
                        show_settingArr.length > 1 ||
                        show_settingProcessPanel.length > 1
                    ) {
                        tooltip = "Settings: " + label;
                    }

                    if (processorgname == show_setting || allname == show_setting) {
                        panel.css("display", "none");
                    }

                    if (!$("#" + buttonId).length &&
                        (processorgname == show_setting || allname == show_setting)
                    ) {
                        insertButton = true;
                    }

                }
                if (insertButton) {
                    //insert button
                    var button =
                        '<button style="display:none; margin-left:7px;" show_sett_but="' +
                        wrapperID +
                        '" type="button" class="btn btn-primary btn-sm"  id="' +
                        buttonId +
                        '"><a data-toggle="tooltip" data-placement="bottom" data-original-title="' +
                        tooltip +
                        '"><span><i class="fa fa-wrench"></i></span></a></button>';
                    $(showSettingInputsAr[i]).find(".firstsec").append(button);
                    var doCall = function(
                        panel,
                        givenName,
                        dropdownID,
                        buttonId,
                        rowId,
                        wrapperID
                    ) {
                        if (rowId) {
                            showHideSett(rowId);
                            $(function() {
                                $(document).on("change", "#" + dropdownID, function(event) {
                                    showHideSett(rowId);
                                });
                            });
                        }
                        //ui-dialog
                        $("#" + wrapperID).dialog({
                            title: `Process Settings`,
                            resizable: false,
                            draggable: true,
                            autoOpen: false,
                            closeOnEscape: true,
                            width: "90%",
                            modal: true,
                            minHeight: 0,
                            maxHeight: 650,
                            buttons: {
                                Ok: function() {
                                    $(this).dialog("close");
                                },
                            },
                            open: function(event, ui) {
                                $(event.target).dialog('widget')
                                    .css({ position: 'fixed' })
                                    .position({ my: 'center', at: 'center', of: window });
                                $("body").css({ overflow: "hidden" });
                                $("html").css({ overflow: "hidden" });

                                let spreadsheets = $(event.target).find("div.dnext_spreadsheet");
                                for (var s = 0; s < spreadsheets.length; s++) {
                                    let spreadsheetID = $(spreadsheets[s]).attr("id")
                                    if ($runscope.handsontable[spreadsheetID]) {
                                        // autoresize fix for handsontable
                                        if ($(spreadsheets[s]).css("display") == "inline-block") {
                                            $(spreadsheets[s]).css("display", "block")
                                            $runscope.handsontable[spreadsheetID].render()
                                        }
                                    }
                                }


                            },
                            beforeClose: function(event, ui) {
                                $("html").css({ overflow: "auto" });
                                $("body").css({ overflow: "auto" });
                            },
                        });

                        $(function() {
                            $(document).on("click", "#" + buttonId, function(event) {
                                $("#" + wrapperID).dialog("open");
                                return false;
                            });
                        });
                    };
                    doCall(panel, givenName, dropdownID, buttonId, rowId, wrapperID);
                }
            }
        }
    }
    $('[data-toggle="tooltip"]').tooltip();
}

//fill file/Val buttons
async function autoFillButton(buttonText, value, keepExist, url, urlzip, checkPath) {
    var button = $(buttonText);
    var checkDropDown = false;
    if (button.attr("indropdown")) {
        checkDropDown = true;
    }
    var checkFileExist = button.css("display") == "none";
    //if  checkDropDown == false and checkFileExist == true then edit
    //if  checkDropDown == false and checkFileExist == false then insert
    var rowID = "";
    var gNumParam = "";
    var given_name = "";
    var qualifier = "";
    var sType = "";
    [rowID, gNumParam, given_name, qualifier, sType] = getInputVariables(button);
    var proPipeInputID = $("#" + rowID).attr("propipeinputid");
    var inputID = null;
    var data = [];
    data.push({ name: "id", value: "" });
    data.push({ name: "name", value: value });
    // insert into project pipeline input table
    if (value && value != "") {
        if (checkDropDown == false && checkFileExist == false) {
            await checkInputInsert(
                data,
                gNumParam,
                given_name,
                qualifier,
                rowID,
                sType,
                inputID,
                null,
                url,
                urlzip,
                checkPath
            );
        } else if (
            checkDropDown == false &&
            checkFileExist == true &&
            keepExist == false
        ) {
            await checkInputEdit(
                data,
                gNumParam,
                given_name,
                qualifier,
                rowID,
                sType,
                proPipeInputID,
                inputID,
                null,
                url,
                urlzip,
                checkPath
            );
        } else if (checkDropDown == true && keepExist == false) {
            // if proPipeInputID exist, then first remove proPipeInputID.
            if (proPipeInputID) {
                var removeInput = await doAjax({
                    p: "removeProjectPipelineInput",
                    id: proPipeInputID,
                });
            }
            await checkInputInsert(
                data,
                gNumParam,
                given_name,
                qualifier,
                rowID,
                sType,
                inputID,
                null,
                url,
                urlzip,
                checkPath
            );
        }
    } else {
        // if value is empty:"" then remove from project pipeline input table
        if (keepExist == false) {
            var fillingType = "default";
            var removeInput = await doAjax({
                p: "removeProjectPipelineInput",
                id: proPipeInputID,
            });
            removeSelectFile(rowID, qualifier, fillingType);
        }
    }
}
// fill pipeline or process executor settings
function fillExecSettings(id, defName, type, inputName) {
    if (type === "pipeline") {
        setTimeout(function() {
            updateCheckBox("#exec_all", "true");
            fillFormById("#allProcessSettTable", id, defName);
        }, 1);
    } else if (type === "process") {
        setTimeout(function() {
            var findCheckBox = $(
                "#processTable >tbody> tr[procproid=" + id + "]"
            ).find("input[name=check]");
            if (findCheckBox && findCheckBox[0]) {
                $.each(findCheckBox, function(st) {
                    var checkBoxId = $(findCheckBox[st]).attr("id");
                    updateCheckBox("#" + checkBoxId, "true");
                    updateCheckBox("#exec_each", "true");
                    fillFormById(
                        "#processTable >tbody> tr[procproid=" + id + "]",
                        "input[name=" + inputName + "]",
                        defName
                    );
                });
            }
        }, 1);
    }
}

//execute after page loads to fill missing inputs
//reason-1: new input added into pipeline without changing rev
//reason-2: run copied into new revision
async function autofillEmptyInputs(autoFillJSON) {
    var autoFillJSONKeys = Object.keys(autoFillJSON);
    for (var n = 0; n < autoFillJSONKeys.length; n++) {
        var el = autoFillJSONKeys[n];
        var conds = autoFillJSON[el].condition;
        var states = autoFillJSON[el].statement;
        var url = autoFillJSON[el].url;
        var urlzip = autoFillJSON[el].urlzip;
        var checkPath = autoFillJSON[el].checkPath;
        if (
            conds &&
            states &&
            !$.isEmptyObject(conds) &&
            !$.isEmptyObject(states)
        ) {
            if (conds.$HOSTNAME) {
                var statusCond = checkConds(conds);
                if (statusCond === true) {
                    var statesKeys = Object.keys(states);
                    for (var k = 0; k < statesKeys.length; k++) {
                        var st = statesKeys[k]
                        var defName = states[st]; // expected Value
                        var defUrl = url[st] || null; // expected Value
                        var defUrlzip = urlzip[st] || null; // expected Value
                        var defcheckPath = checkPath[st] || null; // expected Value
                        //if variable start with "params." then check #inputsTab
                        if (st.match(/params\.(.*)/)) {
                            var varName = st.match(/params\.(.*)/)[1]; //variable Name
                            var checkVarName = $("#inputsTab, .ui-dialog").find(
                                "td[given_name='" + varName + "']"
                            )[0];
                            if (checkVarName) {
                                var varNameButAr = $(checkVarName).find(".firstsec >");
                                if (varNameButAr && varNameButAr[0]) {
                                    var keepExist = true;
                                    await autoFillButton(
                                        varNameButAr[0],
                                        defName,
                                        keepExist,
                                        defUrl,
                                        defUrlzip,
                                        defcheckPath
                                    );
                                }
                            }
                            autoCheck("fillstates");
                        }
                    }
                }
            }
        }
    }
}

function getDefaultImage(states) {
    var $DEFAULT_IMAGE = "";
    $.each(states, function(st) {
        var defName = states[st]; // expected Value
        if (st.match(/\$(.*)/)) {
            var varName = st.match(/\$(.*)/)[1]; //variable Name
            if (varName === "DEFAULT_IMAGE") {
                $DEFAULT_IMAGE = defName;
            }
        }
    });
    return $DEFAULT_IMAGE;
}

//change propipeinputs in case all conds are true
function fillStates(states, url, urlzip, checkPath) {
    $("#inputsTab").loading("start");
    var $DEFAULT_IMAGE = getDefaultImage(states);
    $.each(states, async function(st) {
        var defName = states[st]; // expected Value
        var defUrl = url[st] || null; // expected Value
        var defUrlzip = urlzip[st] || null; // expected Value
        var defcheckPath = checkPath[st] || null; // expected Value
        //if variable start with "params." then check #inputsTab
        if (st.match(/params\.(.*)/)) {
            var varName = st.match(/params\.(.*)/)[1]; //variable Name
            var checkVarName = $("#inputsTab, .ui-dialog").find(
                "td[given_name='" + varName + "']"
            )[0];
            if (checkVarName) {
                var varNameButAr = $(checkVarName).find(".firstsec >");
                if (varNameButAr && varNameButAr[0]) {
                    var keepExist = false;
                    await autoFillButton(
                        varNameButAr[0],
                        defName,
                        keepExist,
                        defUrl,
                        defUrlzip,
                        defcheckPath
                    );
                }
            }
            //if variable starts with "$" then run parameters for pipeline are defined. Fill run parameters. $SINGULARITY_IMAGE, $SINGULARITY_OPTIONS, $DOCKER_IMAGE, $DOCKER_OPTIONS, $MEMORY, $TIME, $QUEUE, $CPU, $EXEC_OPTIONS $DEFAULT_IMAGE
        } else if (st.match(/\$(.*)/)) {
            var cloudType = $("#chooseEnv").find(":selected").val();
            var executor_job = $("#chooseEnv").find(":selected").attr("executor_job");

            var patt = /(.*)-(.*)/;
            var proType = cloudType.replace(patt, "$1");
            var varName = st.match(/\$(.*)/)[1]; //variable Name
            // if $DEFAULT_IMAGE is defined then it must be validated before updateCheckBox
            // if google cloud is selected then it must be DOCKER_IMAGE -> $DEFAULT_IMAGE will be overwritten
            if (varName === "SINGULARITY_IMAGE") {
                $("#singu_img").val(defName);
                if (
                    executor_job != "awsbatch" &&
                    proType != "google" &&
                    (!$DEFAULT_IMAGE || $DEFAULT_IMAGE == "singularity")
                ) {
                    updateCheckBox("#singu_check", "true");
                }
            } else if (varName === "DOCKER_IMAGE") {
                $("#docker_img").val(defName);
                if (
                    executor_job == "awsbatch" ||
                    proType == "google" ||
                    !$DEFAULT_IMAGE ||
                    $DEFAULT_IMAGE == "docker"
                ) {
                    updateCheckBox("#docker_check", "true");
                }
            } else if (varName === "SINGULARITY_OPTIONS") {
                $("#singu_opt").val(defName);
            } else if (varName === "DOCKER_OPTIONS") {
                $("#docker_opt").val(defName);
            } else if (varName === "TIME") {
                fillExecSettings("#job_time", defName, "pipeline");
            } else if (varName === "QUEUE") {
                fillExecSettings("#job_queue", defName, "pipeline");
            } else if (varName === "MEMORY") {
                fillExecSettings("#job_memory", defName, "pipeline");
            } else if (varName === "CPU") {
                fillExecSettings("#job_cpu", defName, "pipeline");
            } else if (varName === "EXEC_OPTIONS") {
                fillExecSettings("#job_clu_opt", defName, "pipeline");
                //two conditions covers both process and pipeline run_commands
            } else if (
                varName.match(/RUN_COMMAND@(.*)/) ||
                varName === "RUN_COMMAND"
            ) {
                setTimeout(function() {
                    var initialText = $runscope.getPubVal("runcmd");
                    if (initialText == "") {
                        $("#runCmd").val(defName);
                    } else {
                        $("#runCmd").val(initialText + " && " + defName);
                    }
                }, 1);
            } else if (varName.match(/TIME@(.*)/)) {
                var processId = varName.match(/TIME@(.*)/)[1];
                fillExecSettings(processId, defName, "process", "time");
            } else if (varName.match(/QUEUE@(.*)/)) {
                var processId = varName.match(/QUEUE@(.*)/)[1];
                fillExecSettings(processId, defName, "process", "queue");
            } else if (varName.match(/MEMORY@(.*)/)) {
                var processId = varName.match(/MEMORY@(.*)/)[1];
                fillExecSettings(processId, defName, "process", "memory");
            } else if (varName.match(/CPU@(.*)/)) {
                var processId = varName.match(/CPU@(.*)/)[1];
                fillExecSettings(processId, defName, "process", "cpu");
            } else if (varName.match(/EXEC_OPTIONS@(.*)/)) {
                var processId = varName.match(/EXEC_OPTIONS@(.*)/)[1];
                fillExecSettings(processId, defName, "process", "opt");
            }
        } else {
            //if variable not start with "params." or "$" then check pipeline options:
            var varName = st;
            var checkVarName = $("#var_pipe-" + varName)[0];
            if (checkVarName) {
                $(checkVarName).val(defName);
            }
        }
    });
}

async function getJobData(getType) {
    var chooseEnv = $("#chooseEnv option:selected").val();
    if (chooseEnv) {
        var patt = /(.*)-(.*)/;
        var proType = chooseEnv.replace(patt, "$1");
        var proId = chooseEnv.replace(patt, "$2");
        var profileData = await getProfileData(proType, proId);
        var allProSett = {};
        if (profileData) {
            allProSett.job_queue = profileData[0].job_queue;
            allProSett.job_memory = profileData[0].job_memory;
            allProSett.job_cpu = profileData[0].job_cpu;
            allProSett.job_time = profileData[0].job_time;
            allProSett.job_clu_opt = profileData[0].job_clu_opt;
            if (getType === "job") {
                return profileData;
            } else if (getType === "both") {
                return [allProSett, profileData];
            }
        } else {
            return [allProSett, profileData];
        }
    }
}

function getJobDataSync(getType) {
    var chooseEnv = $("#chooseEnv option:selected").val();
    if (chooseEnv) {
        var patt = /(.*)-(.*)/;
        var proType = chooseEnv.replace(patt, "$1");
        var proId = chooseEnv.replace(patt, "$2");
        var profileData = getProfileDataSync(proType, proId);
        var allProSett = {};
        if (profileData) {
            allProSett.job_queue = profileData[0].job_queue;
            allProSett.job_memory = profileData[0].job_memory;
            allProSett.job_cpu = profileData[0].job_cpu;
            allProSett.job_time = profileData[0].job_time;
            allProSett.job_clu_opt = profileData[0].job_clu_opt;
            if (getType === "job") {
                return profileData;
            } else if (getType === "both") {
                return [allProSett, profileData];
            }
        } else {
            return [allProSett, profileData];
        }
    }
}

// to execute autofill function, binds event handlers to chooseEnv
function bindEveHandlerChooseEnv(autoFillJSON, jsonType) {
    $("#chooseEnv").bindFirst("change", function() {

        var onchangechooseEnvFunc1 = function() {
            if (jsonType == "pipeline") {
                console.log("ww")
                    // autofill def_publishdir and def_workdir
                var def_publishdir = "";
                var def_workdir = "";
                var project_pipeline_id =
                    $("#pipeline-title").attr("projectpipelineid");
                // execute this section ony once for each chooseEnv change
                var [allProSett, profileData] = getJobDataSync("both");
                showhideOnEnv(profileData);
                fillForm("#allProcessSettTable", "input", allProSett);

                if (profileData) {
                    if (profileData[0]) {
                        if (profileData[0].def_publishdir) {
                            def_publishdir = profileData[0].def_publishdir;
                            $("#publish_dir").val(def_publishdir);
                            updateCheckBox("#publish_dir_check", "true");
                        } else {
                            $("#publish_dir").val(def_publishdir);
                            updateCheckBox("#publish_dir_check", "false");
                        }
                        if (profileData[0].def_workdir) {
                            def_workdir = profileData[0].def_workdir;
                            $("#rOut_dir").val(def_workdir);
                        }
                    }
                }

                // fillForm('#allProcessSettTable', 'input', allProSett);
                $("input.execcheckbox").each(function() {
                    $(this).prop("checked", false);
                });
                if (allProSett.job_cpu != null) {
                    $(".form-control.execcpu").each(function() {
                        $(this).val(allProSett.job_cpu);
                    });
                }
                if (allProSett.job_memory != null) {
                    $(".form-control.execmemory").each(function() {
                        $(this).val(allProSett.job_memory);
                    });
                }
                if (allProSett.job_queue != null) {
                    $(".form-control.execqueue").each(function() {
                        $(this).val(allProSett.job_queue);
                    });
                }
                if (allProSett.job_time != null) {
                    $(".form-control.exectime").each(function() {
                        $(this).val(allProSett.job_time);
                    });
                }
                if (allProSett.job_clu_opt != null) {
                    $(".form-control.execopt").each(function() {
                        $(this).val(allProSett.job_clu_opt);
                    });
                }
            }
        };

        var onchangechooseEnvFunc2 = function() {
            if (autoFillJSON !== null && autoFillJSON !== undefined) {
                var fillHostFunc = function(autoFillJSON, type, filledVars) {
                    $.each(autoFillJSON, function(el) {
                        var conds = autoFillJSON[el].condition;
                        var states = autoFillJSON[el].statement;
                        var url = autoFillJSON[el].url;
                        var urlzip = autoFillJSON[el].urlzip;
                        var checkPath = autoFillJSON[el].checkPath;
                        if (
                            conds &&
                            states &&
                            !$.isEmptyObject(conds) &&
                            !$.isEmptyObject(states)
                        ) {
                            //bind eventhandler to #chooseEnv
                            if (conds.$HOSTNAME) {
                                var statusCond = checkConds(conds, type);
                                if (statusCond === true) {
                                    if (type == "default") {
                                        var not_filled_states = $.extend(true, {}, states);
                                        var not_filled_url = $.extend(true, {}, url);
                                        var not_filled_urlzip = $.extend(true, {}, urlzip);
                                        var not_filled_checkPath = $.extend(true, {}, checkPath);
                                        $.each(filledVars, function(filled_el) {
                                            if (filled_el in not_filled_states) {
                                                delete not_filled_states[filled_el];
                                            }
                                            if (filled_el in not_filled_url) {
                                                delete not_filled_url[filled_el];
                                            }
                                            if (filled_el in not_filled_urlzip) {
                                                delete not_filled_urlzip[filled_el];
                                            }
                                            if (filled_el in not_filled_checkPath) {
                                                delete not_filled_checkPath[filled_el];
                                            }
                                            // if one of the following parameter is filled then don't use container info coming from default condition
                                            if (
                                                filled_el == "$SINGULARITY_IMAGE" ||
                                                filled_el == "$DOCKER_IMAGE" ||
                                                filled_el == "$SINGULARITY_OPTIONS" ||
                                                filled_el == "$DOCKER_OPTIONS"
                                            ) {
                                                delete not_filled_states["$SINGULARITY_IMAGE"];
                                                delete not_filled_states["$SINGULARITY_OPTIONS"];
                                                delete not_filled_states["$DOCKER_IMAGE"];
                                                delete not_filled_states["$DOCKER_OPTIONS"];
                                            }
                                        });
                                        fillStates(
                                            not_filled_states,
                                            not_filled_url,
                                            not_filled_urlzip,
                                            not_filled_checkPath
                                        );
                                    } else {
                                        fillStates(states, url, urlzip, checkPath);
                                        $.extend(filledVars, states); // Merge states into filledVars
                                    }
                                    autoCheck("fillstates");
                                }
                            }
                        }
                    });
                    return filledVars;
                };
                //## position where fillwithDefaults() finalized
                var filledVars = fillHostFunc(autoFillJSON, "", {});
                // fill $HOSTNAME ="default" states if not filled before(based on filledVars obj)
                fillHostFunc(autoFillJSON, "default", filledVars);
            }
        };

        var sequentialUpdate = function(callback) {
            onchangechooseEnvFunc1();
            callback();
        };

        var askAutoFill = function() {
            // check if system inputs are filled.
            // if no systemInputs found then don't ask
            if (systemInputs.length < 1) return false;
            // if filled systemInputs found then ask
            var systemInputFilled = false;
            for (var t = 0; t < systemInputs.length; t++) {
                var checkVarName = $("#inputsTab, .ui-dialog").find(
                    "td[given_name='" + systemInputs[t] + "']"
                )[0];
                if (checkVarName) {
                    var varNameBut = $(checkVarName).find(".firstsec >");
                    var checkFileValExist = varNameBut.css("display") == "none";
                    if (checkFileValExist) {
                        systemInputFilled = true;
                    }
                }
            }
            if (systemInputFilled) return true;
            return false;
        };
        if (jsonType == "pipeline") {
            var ask = askAutoFill();
            if (ask) {
                onchangechooseEnvFunc1();
                var text =
                    "Would you like to update System Inputs according to selected run environment?";
                var savedData = "";
                var execFunc = function(savedData) {
                    onchangechooseEnvFunc2();
                };
                var btnText = "Yes";
                showConfirmDeleteModal(text, savedData, execFunc, btnText);
            } else {
                sequentialUpdate(onchangechooseEnvFunc2);
            }
        } else {
            sequentialUpdate(onchangechooseEnvFunc2);
        }
    });
}

// to execute autofill function, binds event handlers to buttons other than chooseEnv
function bindEveHandler(autoFillJSON) {
    //find buttons that should trigger autofill
    var bindButtonArray = [];
    $.each(autoFillJSON, function(el) {
        var conds = autoFillJSON[el].condition;
        var states = autoFillJSON[el].statement;
        var url = autoFillJSON[el].url;
        var urlzip = autoFillJSON[el].urlzip;
        var checkPath = autoFillJSON[el].checkPath;
        if (
            conds &&
            states &&
            !$.isEmptyObject(conds) &&
            !$.isEmptyObject(states)
        ) {
            //if condition exists other than $HOSTNAME then bind eventhandler to #params. button (eg. dropdown or inputValEnter)
            $.each(conds, function(el) {
                if (el !== "$HOSTNAME") {
                    //if variable starts with "params." then check #inputsTab
                    if (el.match(/params\.(.*)/)) {
                        var varName = el.match(/params\.(.*)/)[1]; //variable Name
                        var checkVarName = $("#inputsTab, .ui-dialog").find(
                            "td[given_name='" + varName + "']"
                        )[0];
                        if (checkVarName) {
                            var varNameButAr = $(checkVarName).find(".firstsec >");
                            if (varNameButAr && varNameButAr[0]) {
                                if (bindButtonArray.indexOf(varNameButAr[0]) === -1) {
                                    bindButtonArray.push(varNameButAr[0]);
                                }
                            }
                        }
                    }
                }
            });
        }
    });

    //bind eventhandler to dropdown button
    for (var i = 0; i < bindButtonArray.length; i++) {
        var bindButton = bindButtonArray[i];
        var doCall = function(bindButton) {
            $(bindButton).change(function() {
                var triggeredFillStates = false;
                var fillHostFunc = function(autoFillJSON, type) {
                    var triggeredFillStates = false;
                    $.each(autoFillJSON, function(el) {
                        var conds = autoFillJSON[el].condition;
                        var states = autoFillJSON[el].statement;
                        var url = autoFillJSON[el].url;
                        var urlzip = autoFillJSON[el].urlzip;
                        var checkPath = autoFillJSON[el].checkPath;
                        if (
                            conds &&
                            states &&
                            !$.isEmptyObject(conds) &&
                            !$.isEmptyObject(states)
                        ) {
                            //if condition exists other than $HOSTNAME then bind eventhandler to #params. button (eg. dropdown or inputValEnter)
                            $.each(conds, function(el) {
                                if (el !== "$HOSTNAME") {
                                    //if variable starts with "params." then check #inputsTab
                                    if (el.match(/params\.(.*)/)) {
                                        var varName = el.match(/params\.(.*)/)[1]; //variable Name
                                        var checkVarName = $("#inputsTab, .ui-dialog").find(
                                            "td[given_name='" + varName + "']"
                                        )[0];
                                        if (checkVarName) {
                                            var varNameButAr = $(checkVarName).find(".firstsec >");
                                            if (varNameButAr && varNameButAr[0]) {
                                                if (varNameButAr[0] == bindButton) {
                                                    var statusCond = checkConds(conds, type);
                                                    if (statusCond === true) {
                                                        fillStates(states, url, urlzip, checkPath);
                                                        triggeredFillStates = true;
                                                        autoCheck("fillstates");
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    });
                    return triggeredFillStates;
                };
                triggeredFillStates = fillHostFunc(autoFillJSON);
                // fill $HOSTNAME ="default" states if not triggered before
                if (!triggeredFillStates) {
                    fillHostFunc(autoFillJSON, "default");
                }
            });
        };
        doCall(bindButton);
    }
}

var addProfileLib = function(oldLibObj, profileVariables) {
    var lines = profileVariables.split("\n");
    for (var i = 0; i < lines.length; i++) {
        var varName = null;
        var defaultVal = null;
        [varName, defaultVal] = parseVarPart(lines[i]);
        if (varName && defaultVal != null) {
            oldLibObj[varName] = defaultVal;
        }
    }
};

async function addProfileVar(autoFillJSON) {
    var profileVar = await doAjax({ p: "getProfileVariables" });
    var defaultLib = {};
    if (autoFillJSON) {
        for (var i = 0; i < profileVar.length; i++) {
            var confirmUpdate = false;
            var proHost = profileVar[i].hostname;
            var proVar = decodeHtml(profileVar[i].variable);
            // find conditions that matches $HOSTNAME
            $.each(autoFillJSON, function(el) {
                if (!confirmUpdate) {
                    if (
                        autoFillJSON[el].condition &&
                        autoFillJSON[el].condition != null &&
                        !$.isEmptyObject(autoFillJSON[el].condition)
                    ) {
                        if (autoFillJSON[el].condition.$HOSTNAME) {
                            if (autoFillJSON[el].condition.$HOSTNAME == proHost) {
                                addProfileLib(autoFillJSON[el].library, proVar);
                                confirmUpdate = true;
                            } else if (autoFillJSON[el].condition.$HOSTNAME == "default") {
                                defaultLib = $.extend(true, {}, autoFillJSON[el].library);
                            }
                        }
                    }
                }
            });
            //insert as a new row if not exist in the exising obj
            if (!confirmUpdate) {
                var newCond = {
                    condition: {},
                    genCondition: {},
                    statement: {},
                    library: defaultLib,
                    url: {},
                    urlzip: {},
                    checkPath: {},
                };
                newCond.condition.$HOSTNAME = proHost;
                addProfileLib(newCond.library, proVar);
                autoFillJSON.push(newCond);
            }
        }
    }
    return autoFillJSON;
}

//parses header_script and create autoFill array.
//eg. [condition:{hostname:ghpcc, var:mm10},statement:{indexPath:"/path"}]
//or generic condition eg. [genCondition:{hostname:null, params.genomeTypePipeline:null}, library:{_species:"human"}]
//url:{}, urlzip:{}, checkPath:{}
function parseAutofill(script) {
    if (script) {
        //check if autofill comment is exist: //* autofill
        if (script.match(/\/\/\* autofill/i)) {
            var lines = script.split("\n");
            var blockStart = null; // beginning of autofill block
            var ifBlockStart = null; // beginning of if block
            var conds = {}; //keep all conditions for if block
            var genConds = {}; //keep all generic conditions for if block
            var autoFill = [];
            var states = {}; //keep all statements for if block
            var library = {}; //keep all string filling library for if block
            var url = {};
            var urlzip = {};
            var checkPath = {};
            for (var i = 0; i < lines.length; i++) {
                var varName = null;
                var defaultVal = null;
                var cond = null; // each condition
                //first find the line of autofill
                if (lines[i].match(/^\s*\/\/\* autofill\s*$/i)) {
                    var blockStart = i;
                }
                // parse statements after first line of autofill
                if (blockStart != null && i > blockStart) {
                    // global variables
                    if (!ifBlockStart && !lines[i].match(/.*if *\((.*)\).*/i)) {
                        [varName, defaultVal] = parseVarPart(lines[i]);
                        if (varName && defaultVal) {
                            if (varName.match(/^_.*$/)) {
                                library[varName] = defaultVal;
                            }
                        }
                    }
                    //find if condition
                    if (lines[i].match(/.*if *\((.*)\).*/i)) {
                        if (ifBlockStart) {
                            if (
                                conds &&
                                states &&
                                library &&
                                genConds &&
                                (!$.isEmptyObject(conds) || !$.isEmptyObject(genConds)) &&
                                (!$.isEmptyObject(states) || !$.isEmptyObject(library))
                            ) {
                                autoFill.push({
                                    condition: conds,
                                    genCondition: genConds,
                                    statement: states,
                                    library: library,
                                    url: url,
                                    urlzip: urlzip,
                                    checkPath: checkPath,
                                });
                            }
                            //push global variables
                        } else if (!ifBlockStart) {
                            if (
                                conds &&
                                states &&
                                library &&
                                genConds &&
                                $.isEmptyObject(conds) &&
                                $.isEmptyObject(genConds) &&
                                (!$.isEmptyObject(states) || !$.isEmptyObject(library))
                            ) {
                                autoFill.push({
                                    condition: conds,
                                    genCondition: genConds,
                                    statement: states,
                                    library: library,
                                    url: url,
                                    urlzip: urlzip,
                                    checkPath: checkPath,
                                });
                            }
                        }
                        conds = {};
                        genConds = {};
                        library = {}; //new library object. Will be used for filling strings.
                        states = {}; //new statement object. It will be filled with following statements until next if condition
                        url = {};
                        urlzip = {};
                        checkPath = {};
                        ifBlockStart = i;
                        cond = lines[i].match(/.*if *\((.*)\).*/i)[1];
                        if (cond) {
                            var condsplit = cond.split("&&");
                            $.each(condsplit, function(el) {
                                [varName, defaultVal] = parseVarPart(
                                    condsplit[el],
                                    "condition"
                                );
                                if (varName && defaultVal) {
                                    conds[varName] = defaultVal;
                                } else if (varName && !defaultVal) {
                                    genConds[varName] = defaultVal;
                                }
                            });
                        }
                        //end of the autofill block: //*or
                    } else if (
                        lines[i].match(/^\s*\/\/\*\s*$/i) ||
                        lines[i].match(/^\s*\/\/\* autofill\s*$/i)
                    ) {
                        blockStart = null;
                        ifBlockStart = null;
                        if (
                            conds &&
                            states &&
                            library &&
                            genConds &&
                            (!$.isEmptyObject(conds) || !$.isEmptyObject(genConds)) &&
                            (!$.isEmptyObject(states) || !$.isEmptyObject(library))
                        ) {
                            autoFill.push({
                                condition: conds,
                                genCondition: genConds,
                                statement: states,
                                library: library,
                                url: url,
                                urlzip: urlzip,
                                checkPath: checkPath,
                            });
                        }
                        //end of if condition with curly brackets
                    } else if ($.trim(lines[i]).match(/^\}$/m)) {
                        if (ifBlockStart) {
                            ifBlockStart = null;
                            if (
                                conds &&
                                states &&
                                library &&
                                genConds &&
                                (!$.isEmptyObject(conds) || !$.isEmptyObject(genConds)) &&
                                (!$.isEmptyObject(states) || !$.isEmptyObject(library))
                            ) {
                                autoFill.push({
                                    condition: conds,
                                    genCondition: genConds,
                                    statement: states,
                                    library: library,
                                    url: url,
                                    urlzip: urlzip,
                                    checkPath: checkPath,
                                });
                            }
                        }
                        conds = {};
                        genConds = {};
                        library = {};
                        states = {};
                        url = {};
                        urlzip = {};
                        checkPath = {};
                        //lines of statements
                    } else {
                        if (lines[i].match("//*") && lines[i].split("//*").length > 1) {
                            var varPart = lines[i].split("//*")[0];
                            var regPart = lines[i].split("//*")[1];
                        } else {
                            var varPart = lines[i];
                            var regPart = "";
                        }
                        if (varPart) {
                            [varName, defaultVal] = parseVarPart(varPart);
                        }
                        var urlVal = null;
                        var urlzipVal = null;
                        var checkPathVal = null;
                        if (regPart) {
                            if (regPart.match(/@url|@checkpath|/i)) {
                                [urlVal, urlzipVal, checkPathVal] =
                                parseRegPartAutofill(regPart);
                            }
                        }
                        if (varName && defaultVal) {
                            if (varName.match(/^_.*$/)) {
                                library[varName] = defaultVal;
                            } else {
                                states[varName] = defaultVal;
                                if (urlVal) {
                                    url[varName] = urlVal;
                                }
                                if (urlzipVal) {
                                    urlzip[varName] = urlzipVal;
                                }
                                if (checkPathVal) {
                                    checkPath[varName] = checkPathVal;
                                }
                                //check if params.VARNAME is defined and return all VARNAMES to fill them as system inputs
                                if (varName.match(/params\.(.*)/)) {
                                    var sysInput = varName.match(/params\.(.*)/)[1];
                                    if (sysInput && sysInput != "") {
                                        if (systemInputs.indexOf(sysInput) === -1) {
                                            systemInputs.push(sysInput);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    return autoFill;
}

// get new statements for each combination of conditions
function getNewStatements(
    conditions,
    autoFillJSON,
    genStatement,
    url,
    urlzip,
    checkPath
) {
    var newStateCond = {
        condition: {},
        genCondition: {},
        statement: {},
        library: {},
        url: {},
        urlzip: {},
        checkPath: {},
    };
    if (conditions) {
        var defValLibrary = [];
        var mergedLib = {};
        // get Merged library for given conditions
        $.each(conditions, function(ele) {
            var varName = Object.keys(conditions[ele]);
            var defVal = conditions[ele][varName];
            newStateCond.condition[varName] = defVal;
            //find varName = defVal statement in autoFillJSON which has library
            $.each(autoFillJSON, function(elem) {
                if (
                    autoFillJSON[elem].condition &&
                    autoFillJSON[elem].condition != "" &&
                    !$.isEmptyObject(autoFillJSON[elem].condition) &&
                    autoFillJSON[elem].library &&
                    autoFillJSON[elem].library != "" &&
                    !$.isEmptyObject(autoFillJSON[elem].library)
                ) {
                    var cond = autoFillJSON[elem].condition;
                    if (cond[varName]) {
                        var originDefVal = cond[varName];
                        if (originDefVal === defVal) {
                            var library = autoFillJSON[elem].library;
                            jQuery.extend(mergedLib, library);
                        }
                    }
                    //find default library that not belong to any condition or generic condition.
                } else if (
                    $.isEmptyObject(autoFillJSON[elem].condition) &&
                    $.isEmptyObject(autoFillJSON[elem].genCondition) &&
                    autoFillJSON[elem].library &&
                    !$.isEmptyObject(autoFillJSON[elem].library)
                ) {
                    var deflibrary = autoFillJSON[elem].library;
                    jQuery.extend(mergedLib, deflibrary);
                }
            });
        });

        // use Merged library to fill genStatement,url,urlzip,checkPath
        var fillWithNewValue = function(
            newStateCond,
            section,
            fillName,
            mergedLib
        ) {
            $.each(section, function(key) {
                var stateValue = section[key];
                var newStateValue = fillStateValue(stateValue, mergedLib);
                newStateCond[fillName][key] = newStateValue;
            });
        };
        var sectionAr = [genStatement, url, urlzip, checkPath];
        var fillName = ["statement", "url", "urlzip", "checkPath"];
        for (var i = 0; i < sectionAr.length; i++) {
            fillWithNewValue(newStateCond, sectionAr[i], fillName[i], mergedLib);
        }
    }
    return newStateCond;
}

//replacing stateValue text by using library object
function fillStateValue(stateValue, library) {
    $.each(library, function(key) {
        var replaceKey = "\\$\\{" + key + "\\}";
        var replaceVal = library[key];
        var re = new RegExp(replaceKey, "g");
        stateValue = stateValue.replace(re, replaceVal);
    });
    return stateValue;
}

//Generates combinations from n arrays with m elements
function cartesianProduct(arr) {
    return arr.reduce(
        function(a, b) {
            return a
                .map(function(x) {
                    return b.map(function(y) {
                        return x.concat(y);
                    });
                })
                .reduce(function(a, b) {
                    return a.concat(b);
                }, []);
        }, [
            []
        ]
    );
}

//find each generic condition in other cond&state pairs and get their default values.
function findDefVal(genConditions, autoFillJSON) {
    var genCondDefaultVal = [];
    $.each(genConditions, function(varName) {
        var defValArray = [];
        $.each(autoFillJSON, function(elem) {
            // find conditions and library that satisfy varName
            if (
                autoFillJSON[elem].condition &&
                autoFillJSON[elem].condition != "" &&
                !$.isEmptyObject(autoFillJSON[elem].condition) &&
                autoFillJSON[elem].library &&
                autoFillJSON[elem].library != "" &&
                !$.isEmptyObject(autoFillJSON[elem].library)
            ) {
                var cond = autoFillJSON[elem].condition;
                if (cond[varName]) {
                    var defaultVal = cond[varName];
                    var obj = {};
                    obj[varName] = defaultVal;
                    defValArray.push(obj);
                }
            }
        });
        genCondDefaultVal.push(defValArray);
    });
    return genCondDefaultVal;
}

//reads generic conditions and create condition&statements pairs
//eg. [genCondition:{hostname:null, genomeType:null}, library:{_species:"human"}] to [condition:{hostname:ghpcc, genomeType:human_hg19},statement:{indexPath:"/path"}]
function decodeGenericCond(autoFillJSON) {
    if (autoFillJSON) {
        $.each(autoFillJSON, function(el) {
            // find generic conditions
            if (
                autoFillJSON[el].genCondition &&
                autoFillJSON[el].genCondition != "" &&
                !$.isEmptyObject(autoFillJSON[el].genCondition)
            ) {
                var genConditions = autoFillJSON[el].genCondition;
                var genStatements = autoFillJSON[el].statement;
                var url = autoFillJSON[el].url;
                var urlzip = autoFillJSON[el].urlzip;
                var checkPath = autoFillJSON[el].checkPath;
                var newCondStatements = {};
                //find each generic condition in other cond&state pairs and get their default values.
                var genCondDefaultVal = findDefVal(genConditions, autoFillJSON);
                // get combinations array of each conditions
                var combiConditions = cartesianProduct(genCondDefaultVal);
                // get new statements for each combination of conditions
                $.each(combiConditions, function(cond) {
                    newCondStatements = getNewStatements(
                        combiConditions[cond],
                        autoFillJSON,
                        genStatements,
                        url,
                        urlzip,
                        checkPath
                    );
                    autoFillJSON.push(newCondStatements);
                });
            }
        });
    }
    return autoFillJSON;
}

function prepareInsertInput(
    getProPipeInputs,
    rowID,
    firGnum,
    paraQualifier,
    fillingType
) {
    var filePath = getProPipeInputs[0].name; //value for val type
    var proPipeInputID = getProPipeInputs[0].id;
    var given_name = getProPipeInputs[0].given_name;
    var collection_id = getProPipeInputs[0].collection_id;
    var collection_name = getProPipeInputs[0].collection_name;
    var collection = {
        collection_id: collection_id,
        collection_name: collection_name,
    };
    var url = getProPipeInputs[0].url;
    var urlzip = getProPipeInputs[0].urlzip;
    var checkPath = getProPipeInputs[0].checkpath;
    insertSelectInput(
        rowID,
        firGnum,
        filePath,
        proPipeInputID,
        paraQualifier,
        collection,
        url,
        urlzip,
        checkPath,
        fillingType
    );
}

function fillInputsTable(getProPipeInputs, rowID, firGnum, paraQualifier) {
    if (getProPipeInputs) {
        if (getProPipeInputs.length > 0) {
            var fillingType = "default";
            prepareInsertInput(
                getProPipeInputs,
                rowID,
                firGnum,
                paraQualifier,
                fillingType
            );
        }
        if (getProPipeInputs.length > 1) {
            for (var k = 1; k < getProPipeInputs.length; k++) {
                var removeInput = doAjax({
                    p: "removeProjectPipelineInput",
                    id: getProPipeInputs[k].id,
                });
            }
        }
    }
    //check if run saved before
    if (pipeData[0].date_created == pipeData[0].date_modified) {
        //after filling, if "use default" button is exist, then click default option.
        clickUseDefault(rowID);
    }
}

//*** if input circle is defined in workflow then insertInputOutputRow function is used to insert row into inputs table based on edges of input parameters.
//*** if variable start with "params." then  insertInputRowParams function is used to insert rows into inputs table.
function insertInputRowParams(defaultVal, opt, pipeGnum, varName, type, desc, name, showsett, optional, file, singleFile, labelText) {
    var dropDownQual = false;
    var paraQualifier = "val";
    if (file) paraQualifier = "file";
    if (singleFile) paraQualifier = "single_file";
    var paramGivenName = varName;
    var processName = "-";
    var paraIdentifier = "-";
    var paraFileType = "-";
    var firGnum = pipeGnum;
    var secGnum = "";
    var rowType = "input";
    var show_setting = "";

    // "Use default" button is added if defVal attr is defined.
    if (defaultVal && defaultVal != "") {
        var defValButton = getButtonsDef("defVal", "Use Default", defaultVal);
    } else {
        var defValButton = "";
    }
    // dropdown is added if dropdown attr is defined.
    if (type == "dropdown" && opt && opt != "") {
        var dropDownMenu = getDropdownDef(
            "indropdown" + firGnum,
            "indropdown",
            opt,
            "Choose Value"
        );
        dropDownQual = true;
    } else {
        var dropDownMenu = "";
    }
    //show_setting
    if (showsett) {
        if (Array.isArray(showsett)) {
            show_setting = showsett.join(",");
        }
    }
    var selectFileButton = getSelectFileButton(
        paraQualifier,
        dropDownQual,
        dropDownMenu,
        defValButton
    );
    var inRow = getRowTable(
        rowType,
        firGnum,
        secGnum,
        paramGivenName,
        paraIdentifier,
        paraFileType,
        paraQualifier,
        processName,
        selectFileButton,
        show_setting,
        optional,
        labelText
    );
    // check if parameters added into table before, if not fill table
    insertInRow(inRow, paramGivenName, rowType, "paramsInputs", desc);
    if ($("#userInputs").css("display") === "none") {
        $("#userInputs").css("display", "table-row");
    }
    //check if project_pipeline_inputs exist then fill:
    var getProPipeInputs = projectPipeInputs.filter(function(el) {
        return el.given_name == paramGivenName;
    });
    var rowID = rowType + "Ta-" + firGnum;
    fillInputsTable(getProPipeInputs, rowID, firGnum, paraQualifier);
}

function clickUseDefault(rowID) {
    var checkDefVal = $("#" + rowID)
        .find("#defValUse")
        .css("display");
    var defaultVal = $("#" + rowID)
        .find("#defValUse")
        .attr("defval");
    if (defaultVal && checkDefVal !== "none") {
        $("#" + rowID)
            .find("#defValUse")
            .trigger("click");
    }
}

//parse ProPipePanelScript and create panelObj
//eg. {schema:[{ varName:"varName",
//              defaultVal:"defaultVal",
//              type:"type",
//              desc:"desc",
//              tool:"tool",
//              opt:"opt"}],
//      style:[{ multicol:[[var1, var2, var3], [var5, var6],
//              array:[[var1, var2], [var4]]
//              condi:[{var1:"yes", var2}, {var1:"no", var3, var4}]
//              },
//      pipeline_default:[{ varName:"palindrome_clip_threshold",
//              defaultVal:"defaultVal",
//              process:"Adapter_Trimmer_Quality_Module.Adapter_Removal"}
//     }
function parseProPipePanelScript(script) {
    var panelObj = { schema: [], style: [], pipeline_default: [] };
    var lines = script.split("\n");
    for (var i = 0; i < lines.length; i++) {
        var varName = null;
        var defaultVal = null;
        var type = null;
        var desc = null;
        var label = null;
        var tool = null;
        var opt = null;
        var multiCol = null;
        var autoform = null;
        var showsett = null;
        var optional = null;
        var file = null;
        var singleFile = null;
        var hidden = null;
        var defparams = null;
        var arr = null;
        var cond = null;
        var spreadsheet = null;
        var title = null;
        var varPart = null;
        var regPart = null;
        if (lines[i].includes("//*")) {
            if (lines[i].split("//*").length > 1) {
                varPart = lines[i].split("//*")[0];
                regPart = lines[i].split("//*")[1];
                var trimmedVarPart = $.trim(varPart);
                // line is commented out
                if (trimmedVarPart && trimmedVarPart.indexOf("//") === 0) {
                    varPart = null;
                    regPart = null;
                }
            } else {
                regPart = lines[i].split("//*")[0];
            }
        } else if (lines[i].includes("defparams.")) {
            varPart = lines[i];
            defparams = true;
        }

        if (varPart) {
            [varName, defaultVal] = parseVarPart(varPart);
        }
        if (regPart) {
            [type, desc, tool, opt, multiCol, arr, cond, title, autoform, showsett, optional, file, singleFile, hidden, spreadsheet, label] =
            parseRegPart(regPart);
        }
        if (type && varName) {
            panelObj.schema.push({
                varName: varName,
                defaultVal: defaultVal,
                type: type,
                desc: desc,
                label: label,
                tool: tool,
                opt: opt,
                title: title,
                autoform: autoform,
                showsett: showsett,
                optional: optional,
                file: file,
                singleFile: singleFile,
                hidden: hidden
            });
        }
        if (multiCol || arr || cond || spreadsheet) {
            panelObj.style.push({
                multicol: multiCol,
                array: arr,
                condi: cond,
                spreadsheet: spreadsheet
            });
        }
        if (defparams) {
            // varName: defparams.Adapter_Trimmer_Quality_Module.Adapter_Removal.palindrome_clip_threshold
            // allParam: Adapter_Trimmer_Quality_Module.Adapter_Removal.palindrome_clip_threshold or palindrome_clip_threshold
            var allParam = varName.split("defparams.")[1];
            var splitedParams = allParam.split(".")
            let lastVarName = splitedParams.pop();
            var processName = splitedParams.join("_")
            panelObj.pipeline_default.push({
                varName: lastVarName,
                defaultVal: defaultVal,
                processName: processName
            });
        }
    }
    return panelObj;
    console.log(panelObj)
}

const addEmptyRows = (data, numRows, colHeaders) => {
    const numOfColumns = colHeaders.length;
    if (numOfColumns) {
        let emptyRow = [];
        for (let i = 0; i < numOfColumns; i++) {
            emptyRow.push('');
        }
        for (let i = 0; i < numRows; i++) {
            let copiedList = emptyRow.slice();
            data.push(copiedList);
        }
    }
    return data;
};

function updateSpreadsheet(gNum, insertObj) {
    sheetId = "spreadsheet_" + gNum + "_" + Object.keys(insertObj).join("_");
    var table = $runscope.handsontable[sheetId];
    let colHeaders = Object.keys(insertObj)
    let columns = []
    $.each(insertObj, function(el) {
        if (Array.isArray((insertObj[el].opt))) {
            columns.push({ "type": "dropdown", "source": insertObj[el].opt })
        } else {
            columns.push({})
        }
    });

    let newData = []
    $.each(insertObj, function(el) {
        if (Array.isArray((insertObj[el].defaultVal))) {
            newData.push(insertObj[el].defaultVal)
        } else {
            newData.push([insertObj[el].defaultVal])
        }
    });

    let transpose = (matrix) => {
        return matrix[0].map((col, i) => matrix.map(row => row[i]));
    }
    newData = transpose(newData);
    let finaldata = addEmptyRows(newData, 100, colHeaders)
    table.loadData(finaldata);
    table.updateSettings({ colHeaders: colHeaders, columns: columns })
}


//--Insert Process and Pipeline Panel (where pipelineOpt processOpt defined)
function insertProPipePanel(script, gNum, name, pObj, processData) {
    var MainGNum = "";
    var prefix = "";
    var onlyModuleName = "";
    var onlyProcessName = name;
    var processOrgName = "";
    var separator = "";
    var panelObj = {}
    if (pObj != window) {
        MainGNum = pObj.MainGNum;
        onlyModuleName = pObj.mergedPipeName;
        onlyProcessName = pObj.name;
        separator = ": ";
        prefix = MainGNum + "_";
    }
    if (processData) {
        if (processData[0]) {
            if (processData[0].name) {
                processOrgName = processData[0].name;
            }
        }
    }
    if (script) {
        //check if parameter comment is exist: //*
        if (script.match(/\/\/\*/)) {
            panelObj = parseProPipePanelScript(script);
            // if window.pipeline_panelObj is set then check pipeline defaults to replace process defaults
            if (window.pipeline_panelObj && window.pipeline_panelObj.pipeline_default) {
                var pipeline_default = window.pipeline_panelObj.pipeline_default
                for (var i = 0; i < panelObj.schema.length; i++) {
                    var varName = panelObj.schema[i].varName;
                    for (var p = 0; p < pipeline_default.length; p++) {
                        var processNameForPipelineDefault = onlyProcessName
                        if (onlyModuleName) processNameForPipelineDefault = `${onlyModuleName}_${onlyProcessName}`;
                        if (pipeline_default[p].varName == varName && processNameForPipelineDefault == pipeline_default[p].processName) {
                            panelObj.schema[i].defaultVal = pipeline_default[p].defaultVal
                        }
                    }
                }
            }
            //create processHeader
            var headerLabel =
                createLabel(onlyModuleName) + separator + createLabel(onlyProcessName);
            var processHeader =
                '<div class="panel-heading collapsible collapseIconDiv" data-toggle="collapse" href="#collapse-' +
                prefix +
                gNum +
                '"><h4 class="panel-title">' +
                headerLabel +
                ' Options <i data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a style="font-size:15px; padding-left:10px;" class="fa collapseIcon fa-plus-square-o"></a></i></h4></div>';
            var processBodyInt =
                '<div id="collapse-' +
                prefix +
                gNum +
                '" class="panel-collapse collapse"><div id="addProcessRow-' +
                prefix +
                gNum +
                '" class="panel-body">';
            //create processPanel
            $("#ProcessPanel").append(
                '<div id="proPanelDiv-' +
                prefix +
                gNum +
                '" style="display:none;" processorgname="' +
                processOrgName +
                '" modulename="' +
                onlyModuleName +
                '" processname="' +
                onlyProcessName +
                '" allname="' +
                name +
                '" label="' +
                headerLabel +
                '"><div id="proPanel-' +
                prefix +
                gNum +
                '" class="panel panel-default" style=" margin-bottom:3px;">' +
                processHeader +
                processBodyInt +
                "</div></div></div></div>"
            );
            var multicol = null;
            var array = null;
            var condi = null;
            var spreadsheet = null;
            //only one array for each(multicol, array, condi, spreadsheet) tag is expected
            if (!$.isEmptyObject(panelObj.style[0])) {
                multicol = panelObj.style[0].multicol;
                array = panelObj.style[0].array;
                condi = panelObj.style[0].condi;
                spreadsheet = panelObj.style[0].spreadsheet;
            }
            var displayProDiv = false;
            for (var i = 0; i < panelObj.schema.length; i++) {
                var varName = panelObj.schema[i].varName;
                var defaultVal = panelObj.schema[i].defaultVal;
                var type = panelObj.schema[i].type;
                var desc = panelObj.schema[i].desc;
                var label = panelObj.schema[i].label;
                var tool = panelObj.schema[i].tool;
                var opt = panelObj.schema[i].opt;
                var title = panelObj.schema[i].title;
                var autoform = panelObj.schema[i].autoform;
                var showsett = panelObj.schema[i].showsett;
                var optional = panelObj.schema[i].optional;
                var file = panelObj.schema[i].file;
                var singleFile = panelObj.schema[i].singleFile;
                var hidden = panelObj.schema[i].hidden;
                if (type && varName) {
                    // if variable start with "params." then insert into inputs table
                    if (varName.match(/params\./)) {
                        varName = varName.match(/params\.(.*)/)[1];
                        pipeGnum = pipeGnum - 1; //negative counter for pipeGnum
                        insertInputRowParams(
                            defaultVal,
                            opt,
                            pipeGnum,
                            varName,
                            type,
                            desc,
                            name,
                            showsett,
                            optional,
                            file,
                            singleFile,
                            label
                        );
                    } else {
                        // if any of the form is not hidden then keep the proPanelDiv
                        if (!hidden) displayProDiv = true;
                        addProcessPanelRow(
                            prefix + gNum,
                            name,
                            varName,
                            defaultVal,
                            type,
                            desc,
                            opt,
                            tool,
                            multicol,
                            array,
                            title,
                            hidden,
                            spreadsheet,
                            label
                        );
                    }
                    if (autoform) {
                        // if @autofill exists, then create event binders
                        addProcessPanelAutoform(
                            prefix + gNum,
                            name,
                            varName,
                            type,
                            autoform
                        );
                    }
                }
            }
            if (condi) {
                for (var a = 0; a < condi.length; a++) {
                    //if contains multiple options: (rRNA|ercc|miRNA|tRNA|piRNA|snRNA|rmsk)
                    $.each(condi[a], function(el) {
                        for (var k = 0; k < condi[a][el].length; k++) {
                            var selCond = condi[a][el][k];
                            var varN = "";
                            var restN = "";
                            if (selCond.match(/\|/) && selCond.match(/\=/)) {
                                [varN, restN] = parseVarPart(selCond);
                                restN = restN.replace("(", "");
                                restN = restN.replace(")", "");
                                var allOpt = restN.split("|");
                                for (var n = 0; n < allOpt.length; n++) {
                                    var newData = condi[a][el].slice();
                                    newData[k] = varN + "=" + allOpt[n];
                                    condi[a].push(newData);
                                }
                            }
                        }
                    });
                }
                for (var k = 0; k < condi.length; k++) {
                    for (var i = 0; i < panelObj.schema.length; i++) {
                        varName = panelObj.schema[i].varName;
                        type = panelObj.schema[i].type;
                        var each_condi = condi[k];
                        addProcessPanelCondi(
                            prefix + gNum,
                            name,
                            varName,
                            type,
                            each_condi
                        );
                    }
                }
            }
            if (spreadsheet) {
                for (let s = 0; s < spreadsheet.length; s++) {
                    let insertObj = {};
                    let sheet = spreadsheet[s]
                    for (let ss = 0; ss < sheet.length; ss++) {
                        for (let p = 0; p < panelObj.schema.length; p++) {
                            let varName = panelObj.schema[p].varName;
                            if (sheet[ss] === varName) {
                                insertObj[varName] = panelObj.schema[p];
                            }

                        }
                    }
                    // insert default values of table 
                    updateSpreadsheet(prefix + gNum, insertObj)
                }
            }
            if (array) {
                //if defVal is array than insert array rows and fill them
                for (var a = 0; a < array.length; a++) {
                    for (var i = 0; i < panelObj.schema.length; i++) {
                        var varName = panelObj.schema[i].varName;
                        var defaultVal = panelObj.schema[i].defaultVal;
                        if ($.isArray(defaultVal)) {
                            var insertObj = {};
                            insertObj[prefix + gNum] = {};
                            $.each(array, function(el) {
                                if (array[el].indexOf(varName) > -1) {
                                    var arrayId = array[el].join("_");
                                    for (var k = 0; k < defaultVal.length; k++) {
                                        var ind = k + 1;
                                        //check if div inserted or not
                                        if (!$(
                                                "#addProcessRow-" +
                                                prefix +
                                                gNum +
                                                "> #" +
                                                arrayId +
                                                "_ind" +
                                                ind
                                            ).length) {
                                            insertObj[prefix + gNum][varName + "_ind" + ind] =
                                                defaultVal[k];
                                        }
                                    }
                                    addArrForms(insertObj);
                                }
                            });
                            //fill rows with with default values
                            $.each(array, function(el) {
                                if (array[el].indexOf(varName) > -1) {
                                    var arrayId = array[el].join("_");
                                    for (var k = 0; k < defaultVal.length; k++) {
                                        var ind = k + 1;
                                        if (
                                            $(
                                                "#addProcessRow-" +
                                                prefix +
                                                gNum +
                                                "> #" +
                                                arrayId +
                                                "_ind" +
                                                ind
                                            ).length
                                        ) {
                                            var fillObj = {};
                                            fillObj[varName + "_ind" + ind] = defaultVal[k];
                                            var inputDiv = $(
                                                "#addProcessRow-" +
                                                prefix +
                                                gNum +
                                                "> #" +
                                                arrayId +
                                                "_ind" +
                                                ind
                                            ).find("#var_" + prefix + gNum + "-" + varName);
                                            var inputDivType = $(inputDiv).attr("type");
                                            fillEachProcessOpt(
                                                fillObj,
                                                varName + "_ind" + ind,
                                                inputDiv,
                                                inputDivType
                                            );
                                        }
                                    }
                                }
                            });
                        }
                    }
                }
            }

            if (displayProDiv === true) {
                $('[data-toggle="tooltip"]').tooltip();
                $("#proPanelDiv-" + prefix + gNum).css("display", "inline");
                //                $('#ProcessPanelTitle').css('display', 'inline');
            }
        }
    }
    return panelObj
}

function getRowTable(
    rowType,
    firGnum,
    secGnum,
    paramGivenName,
    paraIdentifier,
    paraFileType,
    paraQualifier,
    processName,
    button,
    show_setting,
    optional,
    labelText
) {
    var trID = 'id="' + rowType + "Ta-" + firGnum + '"';
    var optional_attr = "";
    var optional_txt = "";
    let labelAttr = ""
    if (optional) {
        optional_attr = ' optional="true" ';
        optional_txt = ' (Optional)';
    }
    var show_setting_attr = "";
    if (show_setting) {
        show_setting_attr = ' show_setting="' + show_setting + '" ';
    }
    if (paraQualifier == "val") {
        paraFileType = "-";
    }
    var finalText = paramGivenName + optional_txt;
    if (labelText) {
        labelAttr = ` label="${labelText}" `;
        finalText = labelText;
    }
    return (
        "<tr " +
        trID +
        optional_attr +
        labelAttr +
        show_setting_attr +
        ' ><td name="' + paramGivenName + '" id="' +
        rowType +
        "-PName-" +
        firGnum +
        '" scope="row">' +
        finalText +
        '</td><td style="display:none;">' +
        paraIdentifier +
        '</td><td style="display:none;">' +
        paraFileType +
        '</td><td style="display:none;">' +
        paraQualifier +
        '</td><td style="display:none;"> <span id="proGName-' +
        secGnum +
        '">' +
        processName +
        '</span></td><td given_name="' +
        paramGivenName +
        '"><div class="firstsec">' +
        button +
        '</div><div class="secondsec"><span class = "indesc"></span></div></td></tr>'
    );
}

function insertProRowTable(
    process_id,
    gNum,
    procName,
    procQueDef,
    procMemDef,
    procCpuDef,
    procTimeDef,
    procOptDef
) {
    return (
        '<tr procProId="' +
        process_id +
        '" id="procGnum-' +
        gNum +
        '"><td><input name="check" class="execcheckbox" id="check-' +
        gNum +
        '" type="checkbox" </td><td>' +
        procName +
        '</td><td><input name="queue" class="form-control execSetting execqueue" type="text" value="' +
        procQueDef +
        '"></input></td><td><input class="form-control execSetting execmemory" type="text" name="memory" value="' +
        procMemDef +
        '"></input></td><td><input name="cpu" class="form-control execSetting execcpu" type="text" value="' +
        procCpuDef +
        '"></input></td><td><input name="time" class="form-control execSetting exectime" type="text" value="' +
        procTimeDef +
        '"></input></td><td><input name="opt" class="form-control execSetting execopt" type="text" value="' +
        procOptDef +
        '"></input></td></tr>'
    );
}

//--Pipeline details table --
function addProPipeTab(process_id, gNum, procName, pObj) {
    if (pObj && pObj !== window) {
        var lastProcName = procName;
        var piGnum = pObj.MainGNum + "";
        var piGnums = piGnum.split("_");
        var procName = "";
        for (var k = 0; k < piGnums.length; k++) {
            var selectedGnums = piGnums.slice(0, k + 1);
            var mergedGnum = selectedGnums.join("_");
            if (procName) procName += "_";
            procName += window["pObj" + mergedGnum].lastPipeName;
        }
        if (procName) procName += "_";
        procName += lastProcName;
    }
    var procQueDef = "short";
    var procMemDef = "10";
    var procCpuDef = "1";
    var procTimeDef = "100";
    var procOptDef = "";
    var proRow = insertProRowTable(
        process_id,
        gNum,
        procName,
        procQueDef,
        procMemDef,
        procCpuDef,
        procTimeDef,
        procOptDef
    );
    $("#processTable > tbody:last-child").append(proRow);
}


function addPipeline(piID, x, y, name, pObjOrigin, pObjSub) {
    var id = piID;
    var prefix = "p";
    var MainGNum = "";
    //load workflow of pipeline modules
    MainGNum = pObjOrigin.MainGNum;
    if (pObjOrigin != window) {
        prefix = "p" + MainGNum + "p";
    }

    //gnum uniqe, id same id (Written in class) in same type process
    pObjOrigin.g = d3
        .select("#mainG" + MainGNum)
        .append("g")
        .attr("id", "g" + MainGNum + "-" + pObjOrigin.gNum)
        .attr("class", "g-p" + id) //for pipeline modules
        .attr("transform", "translate(" + x + "," + y + ")")
        .on("mouseover", mouseOverG)
        .on("mouseout", mouseOutG);
    //gnum(written in id): uniqe, id(Written in class): same id in same type process, bc(written in type): same at all bc
    pObjOrigin.g
        .append("circle")
        .attr("id", "bc" + MainGNum + "-" + pObjOrigin.gNum)
        .attr("class", "bc" + MainGNum + "-" + id)
        .attr("type", "bc")
        .attr("cx", cx)
        .attr("cy", cy)
        .attr("r", rP + ior)
        .attr("fill", "red")
        .transition()
        .delay(500)
        .duration(3000)
        .attr("fill", "#cdcff7");
    //gnum(written in id): uniqe, id(Written in class): same id in same type process, sc(written in type): same at all bc
    pObjOrigin.g
        .append("circle")
        .datum([{
            cx: 0,
            cy: 0,
        }, ])
        .attr("id", "sc-" + MainGNum + "-" + pObjOrigin.gNum)
        .attr("class", "sc" + MainGNum + "-" + id)
        .attr("type", "sc")
        .attr("r", rP - ior)
        .attr("fill", "#BEBEBE")
        .attr("fill-opacity", 0.6)
        .on("mouseover", scMouseOver)
        .on("mouseout", scMouseOut)
        .call(drag);
    //gnum(written in id): uniqe,
    pObjOrigin.g
        .append("text")
        .attr("id", "text" + MainGNum + "-" + pObjOrigin.gNum)
        .datum([{
            cx: 0,
            cy: 0,
        }, ])
        .attr("font-family", "FontAwesome, sans-serif")
        .attr("font-size", "1em")
        .attr("name", name)
        .attr("class", "process")
        .text(truncateName(name, "pipelineModule"))
        .style("text-anchor", "middle")
        .on("mouseover", scMouseOver)
        .on("mouseout", scMouseOut)
        .call(drag);

    //get process list of pipeline
    if (pObjSub.sData) {
        if (Object.keys(pObjSub.sData).length > 0) {
            //--Pipeline details table add process--
            pObjSub.nodesOrg = pObjSub.sData[0].nodes;
            if (pObjSub.nodesOrg) {
                if (IsJson5String(pObjSub.nodesOrg)) {
                    pObjSub.nodesOrg = JSON5.parse(pObjSub.nodesOrg);
                    pObjSub.edOrg = pObjSub.sData[0].edges;
                    pObjSub.edOrg = JSON5.parse(pObjSub.edOrg)[
                        "edges"
                    ];
                    pObjSub.inNodes = {}; //input nodes that are connected to "input parameters"
                    pObjSub.outNodes = []; //output nodes that are connected to "output parameters"
                    for (var ee = 0; ee < pObjSub.edOrg.length; ee++) {
                        if (pObjSub.edOrg[ee].indexOf("inPro") > -1) {
                            pObjSub.edsOrg = pObjSub.edOrg[ee].split("_");
                            if (pObjSub.edsOrg[0][0] === "i") {
                                //i-50-0-46-6_o-inPro-1-46-7
                                if (!pObjSub.inNodes[pObjSub.edsOrg[1]]) {
                                    pObjSub.inNodes[pObjSub.edsOrg[1]] = [];
                                }
                                pObjSub.inNodes[pObjSub.edsOrg[1]].push(pObjSub.edsOrg[0]); //keep nodes in the same array if they connected to same "input parameter"
                            } else {
                                //o-inPro-1-46-7_i-50-0-46-6
                                if (!pObjSub.inNodes[pObjSub.edsOrg[0]]) {
                                    pObjSub.inNodes[pObjSub.edsOrg[0]] = [];
                                }
                                pObjSub.inNodes[pObjSub.edsOrg[0]].push(pObjSub.edsOrg[1]);
                            }
                        } else if (pObjSub.edOrg[ee].indexOf("outPro") > -1) {
                            pObjSub.edsOrg = pObjSub.edOrg[ee].split("_");
                            if (pObjSub.edsOrg[0][0] == "o") {
                                pObjSub.outNodes.push(pObjSub.edsOrg[0]);
                            } else {
                                pObjSub.outNodes.push(pObjSub.edsOrg[1]);
                            }
                        }
                    }
                    //I / O id naming: [0] i = input, o = output - [1] process database ID - [2] The number of I / O of the selected process - [3] Parameter database ID - [4] uniqe number
                    var c = 0;
                    $.each(pObjSub.inNodes, function(k) {
                        if (pObjSub.inNodes[k].length === 1) {
                            var proId = pObjSub.inNodes[k][0].split("-")[1];
                            var parId = pObjSub.inNodes[k][0].split("-")[3];
                            var ccNodeId = "p" + pObjSub.MainGNum + pObjSub.inNodes[k][0];
                            var ccNode = $("#" + ccNodeId);
                            ccIDList[
                                prefix +
                                "i-" +
                                proId +
                                "-" +
                                c +
                                "-" +
                                parId +
                                "-" +
                                pObjOrigin.gNum
                            ] = ccNodeId;
                            d3.select("#g" + MainGNum + "-" + pObjOrigin.gNum)
                                .append("circle")
                                .attr(
                                    "id",
                                    prefix +
                                    "i-" +
                                    proId +
                                    "-" +
                                    c +
                                    "-" +
                                    parId +
                                    "-" +
                                    pObjOrigin.gNum
                                )
                                .attr("ccID", ccNodeId) //copyID for pipeline modules
                                .attr("type", "I/O")
                                .attr("kind", "input")
                                .attr("parentG", "g" + MainGNum + "-" + pObjOrigin.gNum)
                                .attr("name", ccNode.attr("name"))
                                .attr("status", "standard")
                                .attr("connect", "single")
                                .attr("class", ccNode.attr("class"))
                                .attr(
                                    "cx",
                                    calculatePos(
                                        Object.keys(pObjSub.inNodes).length,
                                        c,
                                        "cx",
                                        "inputsPipe"
                                    )
                                )
                                .attr(
                                    "cy",
                                    calculatePos(
                                        Object.keys(pObjSub.inNodes).length,
                                        c,
                                        "cy",
                                        "inputsPipe"
                                    )
                                )
                                .attr("r", ior)
                                .attr("fill", "tomato")
                                .attr("fill-opacity", 0.8)
                                .on("mouseover", IOmouseOver)
                                .on("mousemove", IOmouseMove)
                                .on("mouseout", IOmouseOut);
                            //                        .on("mousedown", IOconnect)
                            c++;
                        } else if (pObjSub.inNodes[k].length > 1) {
                            pObjSub.ccIDAr = [];
                            for (var i = 0; i < pObjSub.inNodes[k].length; i++) {
                                pObjSub.ccIDAr[i] =
                                    "p" + pObjSub.MainGNum + pObjSub.inNodes[k][i];
                                var proId = pObjSub.inNodes[k][i].split("-")[1];
                                var parId = pObjSub.inNodes[k][i].split("-")[3];
                                ccIDList[
                                    prefix +
                                    "i-" +
                                    proId +
                                    "-" +
                                    c +
                                    "-" +
                                    parId +
                                    "-" +
                                    pObjOrigin.gNum
                                ] = "p" + pObjSub.MainGNum + pObjSub.inNodes[k][i];
                            }
                            var ccNode = $("#" + pObjSub.ccIDAr[0]);
                            d3.select("#g" + MainGNum + "-" + pObjOrigin.gNum)
                                .append("circle")
                                .attr(
                                    "id",
                                    prefix +
                                    "i-" +
                                    proId +
                                    "-" +
                                    c +
                                    "-" +
                                    parId +
                                    "-" +
                                    pObjOrigin.gNum
                                )
                                .attr("ccID", pObjSub.ccIDAr) //copyID for pipeline modules
                                .attr("type", "I/O")
                                .attr("kind", "input")
                                .attr("parentG", "g" + MainGNum + "-" + pObjOrigin.gNum)
                                .attr("name", ccNode.attr("name"))
                                .attr("status", "standard")
                                .attr("connect", "single")
                                .attr("class", ccNode.attr("class"))
                                .attr(
                                    "cx",
                                    calculatePos(
                                        Object.keys(pObjSub.inNodes).length,
                                        c,
                                        "cx",
                                        "inputsPipe"
                                    )
                                )
                                .attr(
                                    "cy",
                                    calculatePos(
                                        Object.keys(pObjSub.inNodes).length,
                                        c,
                                        "cy",
                                        "inputsPipe"
                                    )
                                )
                                .attr("r", ior)
                                .attr("fill", "tomato")
                                .attr("fill-opacity", 0.8)
                                .on("mouseover", IOmouseOver)
                                .on("mousemove", IOmouseMove)
                                .on("mouseout", IOmouseOut);
                            //                        .on("mousedown", IOconnect)
                            c++;
                        }
                    });
                    for (var k = 0; k < pObjSub.outNodes.length; k++) {
                        var proId = pObjSub.outNodes[k].split("-")[1];
                        var parId = pObjSub.outNodes[k].split("-")[3];
                        var ccNodeID = "p" + pObjSub.MainGNum + pObjSub.outNodes[k];
                        var ccNode = $("#" + ccNodeID);
                        ccIDList[
                            prefix +
                            "o-" +
                            proId +
                            "-" +
                            k +
                            "-" +
                            parId +
                            "-" +
                            pObjOrigin.gNum
                        ] = ccNodeID;
                        d3.select("#g" + MainGNum + "-" + pObjOrigin.gNum)
                            .append("circle")
                            .attr(
                                "id",
                                prefix +
                                "o-" +
                                proId +
                                "-" +
                                k +
                                "-" +
                                parId +
                                "-" +
                                pObjOrigin.gNum
                            )
                            .attr("ccID", ccNodeID) //copyID for pipeline modules
                            .attr("type", "I/O")
                            .attr("kind", "output")
                            .attr("parentG", "g" + MainGNum + "-" + pObjSub.gNum)
                            .attr("name", ccNode.attr("name"))
                            .attr("status", "standard")
                            .attr("connect", "single")
                            .attr("class", ccNode.attr("class"))
                            .attr(
                                "cx",
                                calculatePos(pObjSub.outNodes.length, k, "cx", "outputsPipe")
                            )
                            .attr(
                                "cy",
                                calculatePos(pObjSub.outNodes.length, k, "cy", "outputsPipe")
                            )
                            .attr("r", ior)
                            .attr("fill", "steelblue")
                            .attr("fill-opacity", 0.8)
                            .on("mouseover", IOmouseOver)
                            .on("mousemove", IOmouseMove)
                            .on("mouseout", IOmouseOut);
                        //                    .on("mousedown", IOconnect)
                    }
                }
            }
        }
    }
    pObjOrigin.processList["g" + MainGNum + "-" + pObjOrigin.gNum] = name;
    pObjOrigin.gNum = pObjOrigin.gNum + 1;
}

function findType(id) {
    var parameter = [];
    var parameter = parametersData.filter(function(el) {
        return el.id == id;
    });
    if (parameter && parameter != "") {
        return parameter[0].file_type;
    } else {
        return "";
    }
}

function calculatePos(len, k, poz, type) {
    var degree = (180 / (len + 1)) * (k + 1);
    var inp = ((270 - (180 / (len + 1)) * (k + 1)) * Math.PI) / 180;
    var out = ((270 - (-180 / (len + 1)) * (k + 1)) * Math.PI) / 180;
    if (type == "inputs") {
        var mathVar = inp;
        var calcR = r;
    } else if (type == "inputsPipe") {
        var mathVar = inp;
        var calcR = rP;
    } else if (type == "outputs") {
        var mathVar = out;
        var calcR = r;
    } else if (type == "outputsPipe") {
        var mathVar = out;
        var calcR = rP;
    }
    if (poz == "cx") {
        calc = Math.cos(mathVar);
        result = calc * calcR;
    } else {
        calc = Math.sin(inp);
        result = calc * calcR;
    }
    return result;
}

function mouseOverG() {
    parent = document.getElementById(this.id).parentElement.id;
    //deactive for pipeline modules
    if (parent.match(/mainG(.*)/)[1] === "") {
        d3.select("#container").on("mousedown", null);
        if (!binding) {
            d3.select("#del-" + this.id.split("-")[1]).style("opacity", 1);
            d3.select("#info-" + this.id.split("-")[1]).style("opacity", 1);
        }
    }
}

function mouseOutG() {
    d3.select("#container").on("mousedown", cancel);
    d3.select("#del-" + this.id.split("-")[1]).style("opacity", 0.2);
    d3.select("#info-" + this.id.split("-")[1]).style("opacity", 0.2);
}

var drag = d3.behavior
    .drag()
    .origin(function(d) {
        return d;
    })
    .on("dragstart", dragstarted)
    .on("drag", dragged)
    .on("dragend", dragended);

function dragstarted(d) {
    selectedg = document.getElementById(this.id).parentElement;
    coor = d3.mouse(this);
    diffx = 0 - coor[0];
    diffy = 0 - coor[1];
    d3.event.sourceEvent.stopPropagation();
    d3.select(document.getElementById(this.id).parentElement).classed(
        "dragging",
        true
    );
}

function dragged(d) {
    if (!binding) {
        coor = d3.mouse(this);
        (t = d3.transform(
            d3
            .select("#" + document.getElementById(this.id).parentElement.id)
            .attr("transform")
        )),
        (x = t.translate[0]);
        y = t.translate[1];
        d3.select(selectedg).attr(
            "transform",
            "translate(" + (x + coor[0] + diffx) + "," + (y + coor[1] + diffy) + ")"
        );
        moveLine(selectedg.id, x, y, coor);
    }
}

function dragended(d) {
    d3.select(selectedg).classed("dragging", false);
}

function moveLine(gId, x, y, coor) {
    allLines = d3.selectAll("line")[0];
    for (var line = 0; line < allLines.length; line++) {
        from = allLines[line].getAttribute("g_from");
        to = allLines[line].getAttribute("g_to");

        if (from == gId) {
            lineid = allLines[line].id;
            IOid = lineid.split("_")[0];
            IOx = d3.select("#" + IOid)[0][0].cx.baseVal.value;
            IOy = d3.select("#" + IOid)[0][0].cy.baseVal.value;
            d3.select("#" + lineid)
                .attr("x1", coor[0] + diffx + IOx + x)
                .attr("y1", coor[1] + diffy + IOy + y);
            moveDelCircle(lineid);
        } else if (to == gId) {
            lineid = allLines[line].id;
            IOid = lineid.split("_")[1];
            IOx = d3.select("#" + IOid)[0][0].cx.baseVal.value;
            IOy = d3.select("#" + IOid)[0][0].cy.baseVal.value;
            d3.select("#" + lineid)
                .attr("x2", coor[0] + diffx + IOx + x)
                .attr("y2", coor[1] + diffy + IOy + y);
            moveDelCircle(lineid);
        }
    }
}

function moveDelCircle(lineid) {
    x1 = d3.select("#" + lineid)[0][0].x1.baseVal.value;
    x2 = d3.select("#" + lineid)[0][0].x2.baseVal.value;
    y1 = d3.select("#" + lineid)[0][0].y1.baseVal.value;
    y2 = d3.select("#" + lineid)[0][0].y2.baseVal.value;
    d3.select("#c--" + lineid)
        .attr("cx", (x1 + x2) / 2)
        .attr("cy", (y1 + y2) / 2);
    d3.select("#c--" + lineid).attr(
        "transform",
        "translate(" + (x1 + x2) / 2 + "," + (y1 + y2) / 2 + ")"
    );
}

function scMouseOver() {
    parent = document.getElementById(this.id).parentElement.id;
    //deactive for pipeline modules
    if (parent.match(/g(.*)-.*/)[1] === "") {
        if (this.id.split("-")[0] === "text") {
            //text üzerine gelince
            cid = "sc-" + this.id.split("-")[1];
        } else {
            cid = this.id;
        }
        d3.select("#" + cid).attr("fill", "gray");
        if (!binding) {
            $("#container").find("line").attr("status", "hide");
            d3.selectAll("line[g_from =" + parent + "]").attr("status", "standard");
            d3.selectAll("line[g_to =" + parent + "]").attr("status", "standard");
        }
        showEdges();
    }
}

function scMouseOut() {
    parent = document.getElementById(this.id).parentElement.id;
    //deactive for pipeline modules
    if (parent.match(/g(.*)-.*/)[1] === "") {
        if (this.id.split("-")[0] === "text") {
            cid = "sc-" + this.id.split("-")[1];
        } else {
            cid = this.id;
        }
        d3.select("#" + cid).attr("fill", "#BEBEBE");
        if (!binding) {
            d3.selectAll("line").attr("status", "standard");
        }
        showEdges();
    }
}

var tooltip = d3
    .select("body")
    .append("div")
    .attr("class", "tooltip-svg")
    .style("position", "absolute")
    .style("max-width", "400px")
    .style("max-height", "100px")
    .style("opacity", 0.9)
    .style("z-index", "10")
    .style("visibility", "hidden")
    .text("Something")
    .style("color", "black");

function IOmouseOver() {
    parent = document.getElementById(this.id).parentElement.id;
    //for pipeline modules
    var MainGNum = "";
    if (parent.match(/g(.*)-.*/)[1] !== "") {
        MainGNum = parent.match(/g(.*)-.*/)[1];
    }
    if (binding) {
        if (d3.select("#" + this.id).attr("status") == "candidate") {
            d3.select("#" + this.id).attr("status", "posCandidate");
            showOptions();
        }
    } else {
        className = document.getElementById(this.id).className.baseVal.split(" ");
        cand = searchedType(className[1]);
        candParam = searchedTypeParam(className[1]);
        parentg = d3.select("#" + this.id).attr("parentG");
        givenNamePP = document.getElementById(this.id).getAttribute("name");
        // for pipeline modules:
        var ccID = $("#" + this.id).attr("ccID");
        var processTag = "";
        if (ccID) {
            var parentID = $("#" + ccID)
                .parent()
                .attr("id"); //g73-4
            var textID = parentID.replace("g", "text"); //text73-4
            var processName = $("#" + textID).attr("name");
            processTag = "Process: <em>" + processName + "</em><br/>";
        }

        $("#mainG" + MainGNum)
            .find("circle[type ='I/O']")
            .attr("status", "noncandidate");
        if (className[0] === "connect_to_input") {
            //before first connection of inputparam
            conToInput();
            tooltip.html("Connect to input");
        } else if (className[0] === "connect_to_output") {
            //before first connection of outputparam
            conToOutput();
            tooltip.html("Connect to output");
        } else if (givenNamePP === "inputparam") {
            //after first connection of inputparam
            d3.selectAll("." + className[0])
                .filter("." + cand)
                .attr("status", "candidate");
            var paraID = document.getElementById(this.id).id.split("-")[3];
            var paraData = parametersData.filter(function(el) {
                return el.id == paraID;
            });
            var paraFileType = paraData[0].file_type;
            tooltip.html(
                "Input parameter<br/>File Type: <em>" + paraFileType + "</em>"
            );
        } else if (givenNamePP === "outputparam") {
            //after first connection of outputparam
            //Since outputparam is connected, it is not allowed to connect more parameters
            //              d3.selectAll("." + className[0]).filter("." + cand).attr("status", "candidate")
            var paraID = document.getElementById(this.id).id.split("-")[3];
            var paraData = parametersData.filter(function(el) {
                return el.id == paraID;
            });
            var paraFileType = paraData[0].file_type;
            tooltip.html(
                "Output parameter<br/>File Type: <em>" + paraFileType + "</em>"
            );
        } else {
            //for process nodes:
            $("#mainG" + MainGNum)
                .find("." + className[0])
                .filter("." + cand)
                .attr("status", "candidate");
            $("#mainG" + MainGNum)
                .find("." + candParam)
                .attr("status", "candidate");

            var givenNamePP = document.getElementById(this.id).getAttribute("name");
            var paraID = document.getElementById(this.id).id.split("-")[3];
            var paraData = parametersData.filter(function(el) {
                return el.id == paraID;
            });
            var paraFileType = paraData[0].file_type;
            var paraQualifier = paraData[0].qualifier;
            var paraName = paraData[0].name;
            if (paraQualifier !== "val") {
                tooltip.html(
                    processTag +
                    "Identifier: <em>" +
                    paraName +
                    "</em><br/>Name: <em>" +
                    givenNamePP +
                    "</em><br/>File Type: <em>" +
                    paraFileType +
                    "</em><br/>Qualifier: <em>" +
                    paraQualifier +
                    "</em>"
                );
            } else {
                tooltip.html(
                    processTag +
                    "Identifier: <em>" +
                    paraName +
                    "</em><br/>Name: <em>" +
                    givenNamePP +
                    "</em><br/>Qualifier: <em>" +
                    paraQualifier +
                    "</em>"
                );
            }
        }
        $("#mainG" + MainGNum)
            .find("circle[parentG =" + parentg + "]")
            .attr("status", "noncandidate");
        $("#mainG" + MainGNum)
            .find("#" + this.id)
            .attr("status", "mouseon");
        tooltip.style("visibility", "visible");

        $("#mainG" + MainGNum)
            .find("line")
            .attr("status", "hide");
        $("#mainG" + MainGNum)
            .find("line[IO_from =" + this.id + "]")
            .attr("status", "standard");
        $("#mainG" + MainGNum)
            .find("line[IO_to =" + this.id + "]")
            .attr("status", "standard");

        showOptions();
        showEdges();
    }
}

function IOmouseMove() {
    tooltip
        .style("top", event.pageY - 10 + "px")
        .style("left", event.pageX + 10 + "px");
}

function IOmouseOut() {
    if (binding) {
        if (d3.select("#" + this.id).attr("status") == "posCandidate") {
            d3.select("#" + this.id).attr("status", "candidate");
            showOptions();
        }
    } else {
        d3.selectAll("circle[type ='I/O']").attr("status", "standard");
        d3.selectAll("line").attr("status", "standard");
        showOptions();
        showEdges();
    }
    tooltip.style("visibility", "hidden");
}

function conToInput() {
    d3.selectAll("circle")
        .filter("." + cand)
        .attr("status", "candidate"); //select all available inputs for inputparam circles
}

function conToOutput() {
    d3.selectAll("circle")
        .filter("." + cand)
        .attr("status", "candidate"); //select all available outputs for outputparam circles
}

function startBinding(className, cand, candParam, selectedIO) {
    parentg = d3.select("#" + selectedIO).attr("parentG");
    $("#container").find("circle[type ='I/O']").attr("status", "noncandidate");
    if (className[0] === "connect_to_input") {
        conToInput();
    } else if (className[0] === "connect_to_output") {
        conToOutput();
    } else {
        $("#container")
            .find("." + className[0])
            .filter("." + cand)
            .attr("status", "candidate");
        $("#container")
            .find("." + candParam)
            .attr("status", "candidate");
    }

    $("#container")
        .find("circle[parentG =" + parentg + "]")
        .attr("status", "noncandidate");
    d3.selectAll("#" + selectedIO).attr("status", "selected");
    $("#container").find("line").attr("status", "hide");
    d3.select("#del-" + selectedIO.split("-")[4]).style("opacity", 0.2);

    for (var edge = 0; edge < edges.length; edge++) {
        if (edges[edge].indexOf(selectedIO) > -1) {
            d3.select("#" + findEdges(edges[edge], selectedIO)).attr(
                "status",
                "noncandidate"
            );
        }
    }
    addCandidates2Dict();
    binding = true;
    showOptions();
    showEdges();
}

//second click selectedIO
function stopBinding(className, cand, candParam, selectedIO) {
    firstid = d3.select("circle[status ='selected']")[0][0].id;
    d3.selectAll("line").attr("status", "standard");
    if (selectedIO === firstid) {
        firstid = d3.select("#" + firstid).attr("status", "mouseon");
        d3.selectAll("." + className[0])
            .filter("." + cand)
            .attr("status", "candidate");
        d3.selectAll("." + candParam).attr("status", "candidate");
        d3.select("#del-" + selectedIO.split("-")[4]).style("opacity", 1);
    } else {
        secondid = d3.select("circle[status ='posCandidate']")[0][0].id;
        createEdges(firstid, secondid, window);

        d3.selectAll("circle[type ='I/O']").attr("status", "standard");
        d3.select("#del-" + secondid.split("-")[4]).style("opacity", 1);
    }
    binding = false;
    showOptions();
    showEdges();
}

function showOptions() {
    d3.selectAll("circle[status ='standard']")
        .attr("r", ior)
        .style("stroke", "")
        .style("stroke-width", "")
        .style("stroke-opacity", "");
    d3.selectAll("circle[status ='mouseon']")
        .attr("r", ior * 1.4)
        .style("stroke", "#ff9999")
        .style("stroke-width", 4)
        .style("stroke-opacity", 0.5);
    d3.selectAll("circle[status ='selected']")
        .attr("r", ior * 1.4)
        .style("stroke", "#ff0000")
        .style("stroke-width", 4)
        .style("stroke-opacity", 0.5);
    d3.selectAll("circle[status ='noncandidate']")
        .attr("r", ior * 0.5)
        .style("stroke", "");
    d3.selectAll("circle[status ='candidate']")
        .attr("r", ior * 1.4)
        .style("stroke", "#ccff66")
        .style("stroke-width", 4)
        .style("stroke-opacity", 0.5);
    d3.selectAll("circle[status ='posCandidate']")
        .attr("r", ior * 1.4)
        .style("stroke", "#ff9999")
        .style("stroke-width", 4)
        .style("stroke-opacity", 0.5);
}
var link = d3.svg.diagonal().projection(function(d) {
    return [d.y, d.x];
});

function showEdges() {
    d3.selectAll("line[status = 'standard']")
        .style("stroke", "#B0B0B0")
        .style("stroke-width", 4)
        .attr("opacity", 1);
    d3.selectAll("line[status = 'hide']")
        .style("stroke-width", 2)
        .attr("opacity", 0.3);
}

function searchedType(type) {
    if (type == "input") {
        return "output";
    } else {
        return "input";
    }
}

function searchedTypeParam(type) {
    if (type == "input") {
        return "connect_to_input";
    } else {
        return "connect_to_output";
    }
}

function findEdges(edge, selectedIO) {
    edgeNodes = edge.split("_");
    if (edgeNodes[0] == selectedIO) {
        return edgeNodes[1];
    } else {
        return edgeNodes[0];
    }
}

function addCandidates2Dict() {
    candidates = [];
    candList = d3.selectAll("circle[status ='candidate']")[0];
    sel = d3.selectAll("circle[status ='selected']")[0][0];
    candList.push(sel);

    for (var c = 0; c < candList.length; c++) {
        currid = candList[c].id;
        gid = document.getElementById(currid).parentElement.id;

        (t = d3.transform(d3.select("#" + gid).attr("transform"))),
        (x = t.translate[0]);
        y = t.translate[1];

        circx = candList[c].cx.baseVal.value + x;
        circy = candList[c].cy.baseVal.value + y;

        posList = [circx, circy, gid];

        candidates[currid] = posList;
    }
}

function replaceNextVar(outName, inputName) {
    //search inputName as name attribute of svg elements of
    var connectedNodeId = $("circle.input[name*='" + inputName + "']").attr("id");
    //find the connected node to get gNum
    if (connectedNodeId !== "") {
        for (var e = 0; e < edges.length; e++) {
            if (edges[e].indexOf(connectedNodeId) !== -1) {
                //if not exist: -1
                var nodes = edges[e].split("_");
                var fNode = nodes[0];
                var gNumInputParam = fNode.split("-")[4];
                //get the given name from outputs table
                if (gNumInputParam !== "") {
                    var givenNameInParam = $("#input-PName-" + gNumInputParam).attr("name");
                    var pattern = /(.*)\$\{(.*)\}(.*)/;
                    if (givenNameInParam !== "") {
                        outName = outName.replace(pattern, "$1" + givenNameInParam + "$3");
                    }
                    break;
                }
            }
        }
    }
    return outName;
}

function updateSecClassName(second, inputParamLocF) {
    if (inputParamLocF === 0) {
        var candi = "output";
    } else {
        var candi = "input";
    }
    secClassName =
        document
        .getElementById(second)
        .className.baseVal.split("-")[0]
        .split(" ")[0] +
        " " +
        candi;
    return secClassName;
}

function getSelectFileButton(
    paraQualifier,
    dropDownQual,
    dropDownMenu,
    defValButton
) {
    var buttons = "";
    if (!dropDownQual) {
        if (paraQualifier === "file") {
            var buttons = getButtonsModal("inputFile", "Enter File") + defValButton;
        } else if (paraQualifier === "val") {
            var buttons = getButtonsModal("inputVal", "Enter Value") + defValButton;
        } else if (paraQualifier === "single_file") {
            var buttons = getButtonsModal("inputSingleFile", "Enter File") + defValButton;
        } else {
            var buttons = getButtonsModal("inputFile", "Enter File") + defValButton;
        }
    } else {
        var buttons = dropDownMenu + defValButton;
    }
    return buttons;
}

//insert inRow to inputs table. insertType:{paramsInputs,mainInputs}
function insertInRow(inRow, paramGivenName, rowType, insertType, desc) {
    var checkTable = $("#inputsTable, .ui-dialog")
        .find("td[given_name]")
        .filter(function() {
            return $(this).attr("given_name") === paramGivenName;
        });
    if (!checkTable.length) {
        if (systemInputs.indexOf(paramGivenName) > -1) {
            // fill as system input
            $("#" + rowType + "sTable > tbody:last-child").append(inRow);
            if ($("#systemInputs").css("display") === "none") {
                $("#systemInputs").css("display", "table-row");
            }
        } else {
            // fill as user input
            if (insertType === "paramsInputs") {
                $("#" + rowType + "sTable > tbody > tr[id=systemInputs]").before(inRow); //fill from bottom
            } else {
                $("#" + rowType + "sTable > tbody > tr[id=userInputs]").after(inRow); //fill from top
            }
            if ($("#userInputs").css("display") === "none") {
                $("#userInputs").css("display", "table-row");
            }
        }
    }
    if (desc) {
        $("#inputsTable, .ui-dialog")
            .find("td[given_name=" + paramGivenName + "] > .secondsec > .indesc")
            .html(desc);
    }
}

function showHideColumnRunSett(colList, type) {
    for (var k = 0; k < colList.length; k++) {
        var processTableCol = colList[k] + 2;
        if (type == "hide") {
            $("#allProcessSettTable")
                .find("th:nth-child(" + colList[k] + ")")
                .hide();
            $("#allProcessSettTable")
                .find("td:nth-child(" + colList[k] + ")")
                .hide();
            var doCall = function(processTableCol) {
                setTimeout(function() {
                    $("#processTable")
                        .find("th:nth-child(" + processTableCol + ")")
                        .hide();
                    $("#processTable")
                        .find("tr >td:nth-child(" + processTableCol + ")")
                        .hide();
                }, 100);
            };
            doCall(processTableCol);
        } else {
            $("#allProcessSettTable")
                .find("th:nth-child(" + colList[k] + ")")
                .show();
            $("#allProcessSettTable")
                .find("td:nth-child(" + colList[k] + ")")
                .show();
            var doCall = function(processTableCol) {
                setTimeout(function() {
                    $("#processTable")
                        .find("th:nth-child(" + processTableCol + ")")
                        .show();
                    $("#processTable")
                        .find("tr >td:nth-child(" + processTableCol + ")")
                        .show();
                }, 100);
            };
            doCall(processTableCol);
        }
    }
}

function showhideOnEnv(profileData) {
    if (profileData[0]) {
        var perms = profileData[0].perms;
        var executor_job = profileData[0].executor_job;
        // shared run environments
        if (perms == "15") {
            $("#rOut_dirDiv").css("display", "none");
            $("#runCmdDiv").css("display", "none");
            $("#target_dir_div").css("display", "none");
            $("#containersDiv").css("display", "none");
        } else {
            $("#containersDiv").css("display", "block");
            if (profileData[0].google_cre_id != undefined) {
                $("#rOut_dirDiv").css("display", "none");
            } else {
                $("#rOut_dirDiv").css("display", "block");
            }
            $("#publishDirHide").css("display", "block");
            $("#jobSettingsDiv").css("display", "block");
            $("#runCmdDiv").css("display", "block");
            $("#intermeDelDiv").css("display", "block");
            $("#target_dir_div").css("display", "block");
            $("#archive_dir_geo_div").css("display", "block");
            $("#archive_dir_div").css("display", "block");
        }
        if (executor_job === "ignite") {
            showHideColumnRunSett([1, 4, 5], "show");
            showHideColumnRunSett([1, 4], "hide");
        } else if (executor_job === "awsbatch") {
            showHideColumnRunSett([1, 4, 5], "show");
        } else if (executor_job === "local") {
            showHideColumnRunSett([1, 4, 5], "hide");
        } else {
            showHideColumnRunSett([1, 4, 5], "show");
        }
        if (executor_job === "slurm") {
            $("#eachProcessQueue").text("Partition");
            $("#allProcessQueue").text("Partition");
        } else {
            $("#eachProcessQueue").text("Queue");
            $("#allProcessQueue").text("Queue");
        }
    }
}

//*** if variable start with "params." then  insertInputRowParams function is used to insert rows into inputs table.
//*** if input circle is defined in workflow then insertInputOutputRow function is used to insert row into inputs table based on edges of input parameters.
async function insertInputOutputRow(
    rowType,
    MainGNum,
    firGnum,
    secGnum,
    pObj,
    prefix,
    second,
    optional
) {
    var paramGivenName = document
        .getElementById("text" + MainGNum + "-" + firGnum)
        .getAttribute("name");
    var paraData = parametersData.filter(function(el) {
        return el.id == pObj.secPI;
    });
    var paraFileType = "";
    var paraQualifier = "";
    var paraIdentifier = "";
    var show_setting = "";
    var dropDownQual = false;
    var paramDefVal = $("#text-" + firGnum).attr("defVal");
    var paramDropDown = $("#text-" + firGnum).attr("dropDown");
    var paramShowSett = $("#text-" + firGnum).attr("showSett");
    var desc = $("#text-" + firGnum).data("inDescOpt");
    var label = $("#text-" + firGnum).data("inLabelOpt");
    var processName = $("#text-" + secGnum).attr("name");
    if (desc) desc = decodeHtml(desc);
    if (label) label = decodeHtml(label);

    if (paramShowSett != undefined) {
        if (paramShowSett === "") {
            //check ccID for nested pipelines
            var ccID = $("#" + second).attr("ccID");
            if (ccID) {
                var parentG = $("#" + ccID).attr("parentG");
                var textID = parentG.replace("g", "text"); //text73-4
                var childProcessName = $("#" + textID).attr("name");
                if (childProcessName) {
                    processName = processName + "_" + childProcessName;
                }
            }
            show_setting = processName;
        } else {
            show_setting = paramShowSett;
        }
        show_setting = show_setting.split(",");
        if (show_setting.length) {
            for (var k = 0; k < show_setting.length; k++) {
                show_setting[k] = $.trim(show_setting[k]);
                show_setting[k] = show_setting[k].replace(/\"/g, "");
                show_setting[k] = show_setting[k].replace(/\'/g, "");
            }
        }
        if (Array.isArray(show_setting)) {
            show_setting = show_setting.join(",");
        }
    }
    if (paraData && paraData != "") {
        var paraFileType = paraData[0].file_type;
        var paraQualifier = paraData[0].qualifier;
        var paraIdentifier = paraData[0].name;
    }
    // "Use default" button is added if defVal attr is defined.
    if (paramDefVal) {
        var defValButton = getButtonsDef("defVal", "Use Default", paramDefVal);
    } else {
        var defValButton = "";
    }
    // dropdown is added if dropdown attr is defined.
    if (paramDropDown && paramDropDown != "") {
        var paramDropDownArray = paramDropDown.split(",");
        if (paramDropDownArray) {
            var dropDownMenu = getDropdownDef(
                "indropdown" + firGnum,
                "indropdown",
                paramDropDownArray,
                "Choose Value"
            );
            //select defVal
            dropDownQual = true;
        }
    } else {
        var dropDownMenu = "";
    }
    var rowExist = "";
    rowExist = document.getElementById(rowType + "Ta-" + firGnum);
    if (rowExist) {
        var preProcess = "";
        $("#" + rowType + "Ta-" + firGnum + "> :nth-child(5)").append(
            "<span id=proGcomma-" + secGnum + ">, </span>"
        );
        $("#" + rowType + "Ta-" + firGnum + "> :nth-child(5)").append(
            "<span id=proGName-" + secGnum + ">" + processName + "</span>"
        );
    } else {
        //fill inputsTable
        if (rowType === "input") {
            var selectFileButton = getSelectFileButton(
                paraQualifier,
                dropDownQual,
                dropDownMenu,
                defValButton
            );
            //insert both system and user inputs
            var inRow = getRowTable(
                rowType,
                firGnum,
                secGnum,
                paramGivenName,
                paraIdentifier,
                paraFileType,
                paraQualifier,
                processName,
                selectFileButton,
                show_setting,
                optional,
                label
            );
            insertInRow(inRow, paramGivenName, rowType, "mainInputs", desc);
            //get project_pipeline_inputs:
            var getProPipeInputs = projectPipeInputs.filter(function(el) {
                return el.given_name == paramGivenName;
            });
            var rowID = rowType + "Ta-" + firGnum;
            fillInputsTable(getProPipeInputs, rowID, firGnum, paraQualifier);
            //outputsTable
        } else if (rowType === "output") {
            optional = null;
            var outName = document
                .getElementById(prefix + second)
                .getAttribute("name");
            if (outName) {
                if (outName.match(/file\((.*)\)/)) {
                    outName = outName.match(/file\((.*)\)/i)[1];
                    // if path is divided by slash replace first ${(.*)} with original variable
                    var patt = /\$\{(.*)\}/;
                    if (outName.match(/\//) && outName.match(patt)) {
                        //find input name equavalant and replace
                        var inputName = outName.match(patt)[1];
                        outName = replaceNextVar(outName, inputName);
                    }
                }
                outName = outName.replace(/\"/g, "");
                outName = outName.replace(/\'/g, "");
                outName = outName.replace(/\?/g, "");
                outName = outName.replace(/\${(.*)}/g, "*");
                outName = paramGivenName + "/" + outName;
                var outNameEl = '<span fName="' + outName + '">NA' + "</span>";
                var inRow = getRowTable(
                    rowType,
                    firGnum,
                    secGnum,
                    paramGivenName,
                    paraIdentifier,
                    paraFileType,
                    paraQualifier,
                    processName,
                    outNameEl,
                    show_setting,
                    optional,
                    label
                );
                $("#" + rowType + "sTable > tbody:last-child").append(inRow);
            }
        }
    }
}

function createEdges(first, second, pObj) {
    var MainGNum = "";
    var prefix = "";
    if (pObj != window) {
        //load workflow of pipeline modules
        MainGNum = pObj.MainGNum;
        prefix = "p" + MainGNum;
    }
    if (!document.getElementById(prefix + first) &&
        document.getElementById(prefix + second)
    ) {
        var newID = getNewNodeId(first, pObj);
        if (newID) {
            first = newID.replace(prefix, "");
        }
    } else if (
        document.getElementById(prefix + first) &&
        !document.getElementById(prefix + second)
    ) {
        var newID = getNewNodeId(second, pObj);
        if (newID) {
            second = newID.replace(prefix, "");
        }
    }

    if (
        document.getElementById(prefix + second) &&
        document.getElementById(prefix + first)
    ) {
        addCandidates2DictForLoad(first, pObj);
        d3.selectAll("#" + prefix + first).attr("connect", "mate");
        d3.selectAll("#" + prefix + second).attr("connect", "mate");
        pObj.inputParamLocF = first.indexOf("o-inPro"); //-1: inputparam not exist //0: first click is done on the inputparam
        pObj.inputParamLocS = second.indexOf("o-inPro");
        pObj.outputParamLocF = first.indexOf("i-outPro"); //-1: outputparam not exist //0: first click is done on the inputparam
        pObj.outputParamLocS = second.indexOf("i-outPro");

        if (pObj.inputParamLocS === 0 || pObj.outputParamLocS === 0) {
            //second click is done on the circle of inputparam//outputparam
            //swap elements and treat as fırst click was done on
            pObj.tem = second;
            second = first;
            first = pObj.tem;
            pObj.inputParamLocF = 0;
            pObj.outputParamLocF = 0;
        }
        //first click is done on the circle of inputparam
        if (pObj.inputParamLocF === 0 || pObj.outputParamLocF === 0) {
            //update the class of inputparam based on selected second circle
            pObj.secClassName = updateSecClassName(
                prefix + second,
                pObj.inputParamLocF
            );
            d3.selectAll("#" + prefix + first).attr("class", pObj.secClassName);
            //update the parameter of the inputparam based on selected second circle
            var optional = $("#" + prefix + second).attr("optional") == "true"
            var firGnum = document.getElementById(prefix + first).id.split("-")[4]; //first g-number
            var firPI = document.getElementById(prefix + first).id.split("-")[3]; //first parameter id
            var secGnum = document.getElementById(prefix + second).id.split("-")[4]; //first g-number
            pObj.secPI = document.getElementById(prefix + second).id.split("-")[3]; //second parameter id
            var secProI = document.getElementById(prefix + second).id.split("-")[1]; //second process id
            if (firPI == "inPara" || firPI == "outPara") {
                pObj.patt = /(.*)-(.*)-(.*)-(.*)-(.*)/;
                pObj.secID = first.replace(pObj.patt, "$1-$2-$3-" + pObj.secPI + "-$5");
                d3.selectAll("#" + prefix + first).attr("id", prefix + pObj.secID);
            } else {
                //don't update input/output param id after first connection
                pObj.secID = first;
            }
            pObj.fClickOrigin = first;
            pObj.fClick = pObj.secID;
            pObj.sClick = second;
            var rowType = "";
            //Pipeline details table
            if (pObj.inputParamLocF === 0) {
                rowType = "input";
            } else if (pObj.outputParamLocF === 0) {
                rowType = "output";
            }
            if (pObj == window) {
                insertInputOutputRow(
                    rowType,
                    MainGNum,
                    firGnum,
                    secGnum,
                    pObj,
                    prefix,
                    second,
                    optional
                );
            }
        } else {
            //process to process connection
            pObj.fClickOrigin = first;
            pObj.fClick = first;
            pObj.sClick = second;
        }

        d3.select("#mainG" + MainGNum)
            .append("line")
            .attr("id", prefix + pObj.fClick + "_" + prefix + pObj.sClick)
            .attr("class", "line")
            .attr("type", "standard")
            .style("stroke", "#B0B0B0")
            .style("stroke-width", 4)
            .attr("x1", pObj.candidates[prefix + pObj.fClickOrigin][0])
            .attr("y1", pObj.candidates[prefix + pObj.fClickOrigin][1])
            .attr("x2", pObj.candidates[prefix + pObj.sClick][0])
            .attr("y2", pObj.candidates[prefix + pObj.sClick][1])
            .attr("g_from", pObj.candidates[prefix + pObj.fClickOrigin][2])
            .attr("g_to", pObj.candidates[prefix + pObj.sClick][2])
            .attr("IO_from", prefix + pObj.fClick)
            .attr("IO_to", prefix + pObj.sClick)
            .attr("stroke-width", 2)
            .attr("stroke", "black");

        pObj.edges.push(prefix + pObj.fClick + "_" + prefix + pObj.sClick);
    } else {
        console.log(
            "EDGE FAILED: prefix:",
            prefix + " MainGNum:" + MainGNum + "Edge" + first + "_" + second
        );
    }
}

function delMouseOver() {
    d3.select("#del" + this.id).attr("fill-opacity", 0.8);
    d3.select("#del--" + this.id.split("--")[1]).style("opacity", 0.8);
}

function delMouseOut() {
    d3.select("#del" + this.id).attr("fill-opacity", 0.4);
    d3.select("#del--" + this.id.split("--")[1]).style("opacity", 0.4);
}

function cancel() {
    if (binding) {
        d3.selectAll("circle[type ='I/O']").attr("status", "standard");
        binding = false;
        showOptions();
    }
}

function download(text) {
    var filename = $("#run-title").val() + ".nf";
    downloadText(text, filename);
}

function createProcessPanelAutoFill(id, pObj, name, process_id) {
    if (pObj !== window) {
        name = pObj.lastPipeName + "_" + name;
    }
    var processData = JSON.parse(window.pipeObj["process_" + process_id]);
    if (processData) {
        var allProScript = "";
        if (
            processData[0].script_header !== "" &&
            processData[0].script_header !== null
        ) {
            allProScript = decodeHtml(processData[0].script_header);
        }
        if (processData[0].script !== "" && processData[0].script !== null) {
            var script = decodeHtml(processData[0].script);
            allProScript = allProScript + "\n" + script;
        }
        if (allProScript) {
            insertProPipePanel(allProScript, pObj.gNum, name, pObj, processData);
            //generate json for autofill by using script of process header
            var pro_autoFillJSON = parseAutofill(allProScript);
            // bind event handlers for autofill
            setTimeout(function() {
                if (pro_autoFillJSON !== null && pro_autoFillJSON !== undefined) {
                    $.each(pro_autoFillJSON, function(el) {
                        var stateObj = pro_autoFillJSON[el].statement;
                        $.each(stateObj, function(old_key) {
                            var new_key = old_key + "@" + id;
                            //add process id to each statement after @ sign (eg.$CPU@52) -> will effect only process specific execution parameters.
                            if (old_key !== new_key) {
                                Object.defineProperty(
                                    stateObj,
                                    new_key,
                                    Object.getOwnPropertyDescriptor(stateObj, old_key)
                                );
                                delete stateObj[old_key];
                            }
                        });
                    });
                    bindEveHandlerChooseEnv(pro_autoFillJSON, "process");
                    bindEveHandler(pro_autoFillJSON);
                }
            }, 1000);
        }
    }
}

function loadPipeline(
    sDataX,
    sDataY,
    sDatapId,
    sDataName,
    processModules,
    gN,
    pObj
) {
    var prefix = "";
    var MainGNum = "";
    if (pObj != window) {
        //load workflow of pipeline modules
        MainGNum = pObj.MainGNum;
        prefix = "p" + MainGNum;
    }
    (pObj.t = d3.transform(
        d3.select("#" + "mainG" + MainGNum).attr("transform")
    )),
    (pObj.x = pObj.t.translate[0]);
    pObj.y = pObj.t.translate[1];
    pObj.z = pObj.t.scale[0];

    pObj.gNum = parseInt(gN);
    var name = sDataName;
    var id = sDatapId;
    var process_id = id;
    var defVal = null;
    var dropDown = null;
    var pubWeb = null;
    var showSett = null;
    var inDescOpt = null;
    var inLabelOpt = null;
    var pubDmeta = null;
    if (processModules != null && processModules != {} && processModules != "") {
        if (processModules.defVal) {
            defVal = processModules.defVal;
        }
        if (processModules.dropDown) {
            dropDown = processModules.dropDown;
        }
        if (processModules.showSett != undefined) {
            showSett = processModules.showSett;
        }
        if (processModules.inDescOpt != undefined) inDescOpt = processModules.inDescOpt;
        if (processModules.inLabelOpt != undefined) inLabelOpt = processModules.inLabelOpt;
        if (processModules.pubDmeta != undefined) {
            pubDmeta = processModules.pubDmeta;
        }
        if (processModules.pubWeb) {
            pubWeb = processModules.pubWeb;
        }
    }

    //for input parameters
    if (id === "inPro") {
        ipR = 70 / 2;
        ipIor = ipR / 3;
        var kind = "input";

        //(A)if edges are not formed parameter_id data comes from default: process_parameter table "name" column
        var paramId = "inPara"; //default
        var classtoparam = "connect_to_input output";
        var pName = "inputparam";
        var init = "o";
        var pColor = "orange";
        //(B)if edges are formed parameter_id data comes from biocorepipesave table "edges" column
        pObj.edgeIn = pObj.sData[0].edges;
        pObj.edgeInP = JSON5.parse(pObj.edgeIn)["edges"]; //i-10-0-9-1_o-inPro-1-9-0

        for (var ee = 0; ee < pObj.edgeInP.length; ee++) {
            pObj.patt = /(.*)-(.*)-(.*)-(.*)-(.*)_(.*)-(.*)-(.*)-(.*)-(.*)/;
            pObj.edgeFirstPId = pObj.edgeInP[ee].replace(pObj.patt, "$2");
            pObj.edgeFirstGnum = pObj.edgeInP[ee].replace(pObj.patt, "$5");
            pObj.edgeSecondParID = pObj.edgeInP[ee].replace(pObj.patt, "$9");

            if (
                pObj.edgeFirstGnum === String(pObj.gNum) &&
                pObj.edgeFirstPId === "inPro"
            ) {
                paramId = pObj.edgeSecondParID; //if edge is found
                classtoparam = findType(paramId) + " output";
                pName = parametersData.filter(function(el) {
                    return el.id == paramId;
                })[0].name;
                break;
            }
        }

        drawParam(
            name,
            process_id,
            id,
            kind,
            sDataX,
            sDataY,
            paramId,
            pName,
            classtoparam,
            init,
            pColor,
            defVal,
            dropDown,
            pubWeb,
            showSett,
            inDescOpt,
            inLabelOpt,
            pubDmeta,
            pObj
        );
        pObj.processList["g" + MainGNum + "-" + pObj.gNum] = name;
        pObj.gNum = pObj.gNum + 1;
    } else if (id === "outPro") {
        ipR = 70 / 2;
        ipIor = ipR / 3;
        var kind = "output";

        //(A)if edges are not formed parameter_id data comes from default: process_parameter table "name" column
        var paramId = "outPara"; //default
        var classtoparam = "connect_to_output input";
        var pName = "outputparam";
        var init = "i";
        var pColor = "green";
        //(B)if edges are formed parameter_id data comes from biocorepipesave table "edges" column
        pObj.edgeOut = pObj.sData[0].edges;
        pObj.edgeOutP = JSON5.parse(pObj.edgeOut)["edges"]; //i-10-0-9-1_o-inPro-1-9-0

        for (var ee = 0; ee < pObj.edgeOutP.length; ee++) {
            pObj.patt = /(.*)-(.*)-(.*)-(.*)-(.*)_(.*)-(.*)-(.*)-(.*)-(.*)/;
            pObj.edgeFirstPId = pObj.edgeOutP[ee].replace(pObj.patt, "$2");
            pObj.edgeFirstGnum = pObj.edgeOutP[ee].replace(pObj.patt, "$5");
            pObj.edgeSecondParID = pObj.edgeOutP[ee].replace(pObj.patt, "$9");

            if (
                pObj.edgeFirstGnum === String(pObj.gNum) &&
                pObj.edgeFirstPId === "outPro"
            ) {
                paramId = pObj.edgeSecondParID; //if edge is found
                classtoparam = findType(paramId) + " input";
                pName = parametersData.filter(function(el) {
                    return el.id == paramId;
                })[0].name;
                break;
            }
        }
        drawParam(
            name,
            process_id,
            id,
            kind,
            sDataX,
            sDataY,
            paramId,
            pName,
            classtoparam,
            init,
            pColor,
            defVal,
            dropDown,
            pubWeb,
            showSett,
            inDescOpt,
            inLabelOpt,
            pubDmeta,
            pObj
        );
        pObj.processList["g" + MainGNum + "-" + pObj.gNum] = name;
        pObj.gNum = pObj.gNum + 1;
    } else {
        //--Pipeline details table ---
        addProPipeTab(id, pObj.gNum + prefix, name, pObj);
        //--ProcessPanel (where process options defined)
        createProcessPanelAutoFill(id, pObj, name, process_id);
        //create process circle
        pObj.inputs = JSON.parse(window.pipeObj["pro_para_inputs_" + id]);
        pObj.outputs = JSON.parse(window.pipeObj["pro_para_outputs_" + id]);

        //gnum uniqe, id same id (Written in class) in same type process
        pObj.g = d3
            .select("#mainG" + MainGNum)
            .append("g")
            .attr("id", "g" + MainGNum + "-" + pObj.gNum)
            .attr("class", "g" + MainGNum + "-" + id)
            .attr("transform", "translate(" + sDataX + "," + sDataY + ")");

        //gnum(written in id): uniqe, id(Written in class): same id in same type process, bc(written in type): same at all bc
        pObj.g
            .append("circle")
            .attr("id", "bc" + MainGNum + "-" + pObj.gNum)
            .attr("class", "bc" + MainGNum + "-" + id)
            .attr("type", "bc")
            .attr("cx", cx)
            .attr("cy", cy)
            .attr("r", r + ior)
            .attr("fill-opacity", 0.6)
            .attr("fill", "red")
            .transition()
            .delay(500)
            .duration(3000)
            .attr("fill", "#E0E0E0");
        //gnum(written in id): uniqe, id(Written in class): same id in same type process, sc(written in type): same at all bc
        pObj.g
            .append("circle")
            .datum([{
                cx: 0,
                cy: 0,
            }, ])
            .attr("id", "sc" + MainGNum + "-" + pObj.gNum)
            .attr("class", "sc" + MainGNum + "-" + id)
            .attr("type", "sc")
            .attr("r", r - ior)
            .attr("fill", "#BEBEBE")
            .attr("fill-opacity", 0.6);

        //gnum(written in id): uniqe,
        pObj.g
            .append("text")
            .attr("id", "text" + MainGNum + "-" + pObj.gNum)
            .datum([{
                cx: 0,
                cy: 0,
            }, ])
            .attr("font-family", "FontAwesome, sans-serif")
            .attr("font-size", "1em")
            .attr("name", name)
            .attr("class", "process")
            .text(truncateName(name, "process"))
            .style("text-anchor", "middle");

        // I/O id naming:[0]i = input,o = output -[1]process database ID -[2]The number of I/O of the selected process -[3]Parameter database ID- [4]uniqe number
        for (var k = 0; k < pObj.inputs.length; k++) {
            d3.select("#g" + MainGNum + "-" + pObj.gNum)
                .append("circle")
                .attr(
                    "id",
                    prefix +
                    "i-" +
                    id +
                    "-" +
                    k +
                    "-" +
                    pObj.inputs[k].parameter_id +
                    "-" +
                    pObj.gNum
                )
                .attr("type", "I/O")
                .attr("kind", "input")
                .attr("parentG", "g" + MainGNum + "-" + pObj.gNum)
                .attr("name", pObj.inputs[k].sname)
                .attr("operator", pObj.inputs[k].operator)
                .attr("closure", pObj.inputs[k].closure)
                .attr("optional", pObj.inputs[k].optional)
                .attr("connect", "single")
                .attr("status", "standard")
                .attr("class", findType(pObj.inputs[k].parameter_id) + " input")
                .attr("cx", calculatePos(pObj.inputs.length, k, "cx", "inputs"))
                .attr("cy", calculatePos(pObj.inputs.length, k, "cy", "inputs"))
                .attr("r", ior)
                .attr("fill", "tomato")
                .attr("fill-opacity", 0.8)
                .on("mouseover", IOmouseOver)
                .on("mousemove", IOmouseMove)
                .on("mouseout", IOmouseOut);
            //                .on("mousedown", IOconnect)
        }

        for (var k = 0; k < pObj.outputs.length; k++) {
            d3.select("#g" + MainGNum + "-" + pObj.gNum)
                .append("circle")
                .attr(
                    "id",
                    prefix +
                    "o-" +
                    id +
                    "-" +
                    k +
                    "-" +
                    pObj.outputs[k].parameter_id +
                    "-" +
                    pObj.gNum
                )
                .attr("type", "I/O")
                .attr("kind", "output")
                .attr("parentG", "g" + MainGNum + "-" + pObj.gNum)
                .attr("name", pObj.outputs[k].sname)
                .attr("operator", pObj.outputs[k].operator)
                .attr("closure", pObj.outputs[k].closure)
                .attr("optional", pObj.outputs[k].optional)
                .attr("reg_ex", pObj.outputs[k].reg_ex)
                .attr("status", "standard")
                .attr("connect", "single")
                .attr("class", findType(pObj.outputs[k].parameter_id) + " output")
                .attr("cx", calculatePos(pObj.outputs.length, k, "cx", "outputs"))
                .attr("cy", calculatePos(pObj.outputs.length, k, "cy", "outputs"))
                .attr("r", ior)
                .attr("fill", "steelblue")
                .attr("fill-opacity", 0.8)
                .on("mouseover", IOmouseOver)
                .on("mousemove", IOmouseMove)
                .on("mouseout", IOmouseOut);
            //                .on("mousedown", IOconnect)
        }
        pObj.processList["g" + MainGNum + "-" + pObj.gNum] = name;
        pObj.processListMain["g" + MainGNum + "-" + pObj.gNum] = name;
        pObj.gNum = pObj.gNum + 1;
    }
}

function addCandidates2DictForLoad(fir, pObj) {
    var MainGNum = "";
    var prefix = "";
    if (pObj != window) {
        //load workflow of pipeline modules
        MainGNum = pObj.MainGNum;
        prefix = "p" + MainGNum;
    }
    pObj.candidates = [];
    pObj.candList = $("#mainG" + MainGNum).find("circle[type ='I/O']");
    pObj.sel = d3.select("#" + prefix + fir)[0][0];
    pObj.candList.push(pObj.sel);
    for (var c = 0; c < pObj.candList.length; c++) {
        if (pObj.candList[c]) {
            pObj.currid = pObj.candList[c].id;
            pObj.gid = document.getElementById(pObj.currid).parentElement.id;
            (pObj.t = d3.transform(d3.select("#" + pObj.gid).attr("transform"))),
            (pObj.x = pObj.t.translate[0]);
            pObj.y = pObj.t.translate[1];

            pObj.circx = pObj.candList[c].cx.baseVal.value + pObj.x;
            pObj.circy = pObj.candList[c].cy.baseVal.value + pObj.y;

            pObj.posList = [pObj.circx, pObj.circy, pObj.gid];
            pObj.candidates[pObj.currid] = pObj.posList;
        }
    }
}

function loadPipelineDetails(pipeline_id, pipeData) {

    window.pipeObj = {};
    var getPipelineD = [];
    getPipelineD.push({ name: "id", value: pipeline_id });
    getPipelineD.push({ name: "p", value: "exportPipeline" });

    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: getPipelineD,
        async: true,
        success: async function(s) {

            window.pipeObj = s;
            window.ajaxData.pipelineData = [
                window.pipeObj["main_pipeline_" + pipeline_id],
            ];
            var pData = window.ajaxData.pipelineData;
            var pName = pData[0].name + " (Rev " + pData[0].rev_id + ")";
            $("#pipeline-title").text(pName);
            $("#pipeline-title").attr("href", "index.php?np=1&id=" + pipeline_id);
            $("#pipeline-title2").html(
                '<i class="fa fa-spinner "></i> Go to Pipeline: ' + pName
            );
            $("#pipeline-title2").attr("href", "index.php?np=1&id=" + pipeline_id);
            $("#project-title").attr("href", "index.php?np=2&id=" + project_id);
            $("#pipelineSum").val(decodeHtml(pData[0].summary));
            $("#pipelineSum").attr("disabled", "disabled");
            projectPipeInputs = await doAjax({
                p: "getProjectPipelineInputs",
                project_pipeline_id: project_pipeline_id,
            });
            var script_pipe_header_config = "";
            if (pData[0].script_pipe_header !== null) {
                script_pipe_header_config +=
                    decodeHtml(pData[0].script_pipe_header) + "\n";
            }
            if (pData[0].script_pipe_config !== null) {
                script_pipe_header_config += decodeHtml(pData[0].script_pipe_config);
            }
            if (script_pipe_header_config) {
                pipeGnum = 0;
                //check if params.VARNAME is defined in the autofill section of pipeline header. Then return all VARNAMES to define as system inputs
                //##insertInputRowParams will add inputs rows and fill according to propipeinputs within insertProPipePanel
                // window.pipeline_panelObj parsed and prepared before process settings loaded in open pipeline
                var processData = "";
                window.pipeline_panelObj = insertProPipePanel(
                    script_pipe_header_config,
                    "pipe",
                    "Pipeline",
                    window,
                    processData
                );
                //generate json for autofill by using script of pipeline header
                autoFillJSON = parseAutofill(script_pipe_header_config);
                // get Profile variables -> update library of $HOSTNAME conditions
                autoFillJSON = await addProfileVar(autoFillJSON);
                autoFillJSON = decodeGenericCond(autoFillJSON);
            }

            // first openPipeline() will create tables and forms
            // then loadProjectPipeline will load process options
            var sequentialCmd = async function(pipeline_id, callback) {
                await openPipeline(pipeline_id);
                await callback();
            };
            await sequentialCmd(pipeline_id, async function() {
                //## position where all inputs created and filled
                await loadProjectPipeline(pipeData); // will load process options
            });
        },
        error: function(errorThrown) {
            alert("Error: " + errorThrown);
        },
    });
}

// clean depricated project pipeline inputs(propipeinputs) in case it is not found in the inputs table.
async function cleanDepProPipeInputs() {
    project_pipeline_id = $("#pipeline-title").attr("projectpipelineid");
    var getProPipeInputs = await doAjax({
        p: "getProjectPipelineInputs",
        project_pipeline_id: project_pipeline_id,
    });
    var givenNameObj = {};
    var allInputNames = [];
    $("#inputsTab, .ui-dialog")
        .find("td[given_name]")
        .filter(function() {
            allInputNames.push($(this).attr("given_name"));
        });
    for (var c = 0; c < allInputNames.length; c++) {
        var inGName = allInputNames[c]
        givenNameObj[inGName] = "";
    }

    $.each(getProPipeInputs, async function(el) {
        var givenName = getProPipeInputs[el].given_name;
        //clean inputs whose given_name is not found in numInputRows
        if (givenNameObj[givenName] == undefined) {
            var removeInput = await doAjax({
                p: "removeProjectPipelineInput",
                id: getProPipeInputs[el].id,
            });
        }
    });
}

function updateCheckBox(check_id, status) {
    var targetDiv = $(check_id).attr("data-target");
    if (targetDiv) {
        if (status == "true") {
            $(targetDiv).collapse("show");
            $(check_id).prop("checked", true);
        } else if (status == "false") {
            $(targetDiv).collapse("hide");
            $(check_id).prop("checked", false);
        }
    }
    if (status === "true") {
        $(check_id).prop("checked", true);
    } else if (status === "false") {
        $(check_id).prop("checked", false);
    }
}

async function refreshCreatorData(project_pipeline_id) {
    pipeData = await doAjax({ p: "getProjectPipelines", id: project_pipeline_id });
    if (pipeData && pipeData != "") {
        $("#creatorInfoPip").css("display", "block");
        $("#ownUserNamePip").text(pipeData[0].username);
        $("#datecreatedPip").text(pipeData[0].date_created);
        $(".lasteditedPip").text(pipeData[0].date_modified);
    }
}

async function loadExecSettings(pipeData) {
    var chooseEnv = $("#chooseEnv option:selected").val();
    if (pipeData[0].profile !== "") {
        if (chooseEnv) {
            var [allProSett, profileData] = await getJobData("both");
        } else {
            var prof = pipeData[0].profile;
            var patt = /(.*)-(.*)/;
            var proType = prof.replace(patt, "$1");
            var proId = prof.replace(patt, "$2");
            var profileData = await getProfileData(proType, proId);
        }
        showhideOnEnv(profileData);

        //insert exec_all_settings data into allProcessSettTable table
        if (IsJsonString(decodeHtml(pipeData[0].exec_all_settings))) {
            var exec_all_settings = JSON.parse(
                decodeHtml(pipeData[0].exec_all_settings)
            );
            fillForm("#allProcessSettTable", "input", exec_all_settings);
        }
        //insert exec_each_settings data into #processtable
        if (IsJsonString(decodeHtml(pipeData[0].exec_each_settings))) {
            var exec_each_settings = JSON.parse(
                decodeHtml(pipeData[0].exec_each_settings)
            );
            $.each(exec_each_settings, function(el) {
                var each_settings = exec_each_settings[el];
                //wait for the table to load
                fillForm("#" + el, "input", each_settings);
            });
        }
    }
}

async function loadUserGroups(pipeData) {
    var allUserGrp = await doAjax({ p: "getUserGroups" });
    if (allUserGrp && allUserGrp != "") {
        for (var i = 0; i < allUserGrp.length; i++) {
            var param = allUserGrp[i];
            var optionGroup = new Option(param.name, param.id);
            $("#groupSelRun").append(optionGroup);
        }
    }
    if (pipeData[0].group_id !== "0") {
        $("#groupSelRun").val(pipeData[0].group_id);
    }
}

//fillingType:"default" or "dry"
function fillProPipeInputsRev(projectPipeInputs_rev, fillingType) {
    var numInputRows = $("tbody").find("td[given_name]");
    $.each(numInputRows, function(el) {
        var given_name = $(numInputRows[el]).attr("given_name");
        var in_row = $(numInputRows[el]).closest("tr");
        var rowID = in_row.attr("id"); //"inputTa-5"
        var gnum = rowID.split("Ta-")[1];
        var qualifier = $("#" + rowID + " > :nth-child(4)").text();
        //check if project_pipeline_inputs exist then fill:
        var getProPipeInputs = projectPipeInputs_rev.filter(function(el) {
            return el.given_name == given_name;
        });
        removeSelectFile(rowID, qualifier, fillingType);
        if (getProPipeInputs.length > 0) {
            prepareInsertInput(getProPipeInputs, rowID, gnum, qualifier, fillingType);
        }
        // During dry-fill, update process settings
        showHideSett(rowID);
    });
}

async function loadRunSettings(pipeData) {
    console.log("loadRunSettings")
    $("#rOut_dir").val(pipeData[0].output_dir);
    $("#publish_dir").val(pipeData[0].publish_dir);
    $("#chooseEnv").val(pipeData[0].profile);
    $("#runCmd").val(pipeData[0].cmd);
    $("#runSum").val(decodeHtml(pipeData[0].summary).replaceAll("</br>", "\r\n"));
    disableRunSum();
    $("#docker_img").val(pipeData[0].docker_img);
    $("#docker_opt").val(pipeData[0].docker_opt);
    $("#singu_img").val(pipeData[0].singu_img);
    $("#singu_opt").val(pipeData[0].singu_opt);
    var projectpipelineOwn = await $runscope.checkProjectPipelineOwn();
    toogleReleaseDiv(pipeData[0].perms, projectpipelineOwn);
    updateCheckBox("#publish_dir_check", pipeData[0].publish_dir_check);
    updateCheckBox("#intermeDel", pipeData[0].interdel);
    updateCheckBox("#exec_each", decodeHtml(pipeData[0].exec_each));
    updateCheckBox("#exec_all", decodeHtml(pipeData[0].exec_all));
    updateCheckBox("#docker_check", pipeData[0].docker_check);
    updateCheckBox("#singu_check", pipeData[0].singu_check);
    updateCheckBox("#singu_save", pipeData[0].singu_save);
    updateCheckBox("#withTrace", pipeData[0].withTrace);
    updateCheckBox("#withReport", pipeData[0].withReport);
    updateCheckBox("#withDag", pipeData[0].withDag);
    updateCheckBox("#withTimeline", pipeData[0].withTimeline);
    updateCheckBox("#cron_check", pipeData[0].cron_check);
    updateCheckBox("#notif_check", pipeData[0].notif_check);
    updateCheckBox("#email_notif", pipeData[0].email_notif);
    $("#notif_email_list").val(decodeHtml(pipeData[0].notif_email_list));
    $("#cron_prefix").val(decodeHtml(pipeData[0].cron_prefix));
    $("#cron_min").val(pipeData[0].cron_min);
    $("#cron_hour").val(pipeData[0].cron_hour);
    $("#cron_day").val(pipeData[0].cron_day);
    $("#cron_week").val(pipeData[0].cron_week);
    $("#cron_month").val(pipeData[0].cron_month);
    if (pipeData[0].cron_first) {
        let cron_first_tempVal = pipeData[0].cron_first.split(" ").join("T")
        $("#cron_first").val(cron_first_tempVal);
    }
    $("#cronNextSubDate").text(pipeData[0].cron_target_date)

    //fill process options table
    if (pipeData[0].process_opt) {
        loadProcessOpt(decodeHtml(pipeData[0].process_opt));
    }
    // fill executor settings:
    await loadExecSettings(pipeData);
}

function chooseDefaultRunEnv(pipeData) {
    var selectedOption = $('#chooseEnv').val()
    if ($('#chooseEnv option').length === 2 && !selectedOption && !pipeData[0].onload) {
        console.log("chooseDefaultRunEnv")
        var defEnv = $('#chooseEnv option:eq(1)').val()
        $('#chooseEnv').val(defEnv).change();
    }
}

async function loadProjectPipeline(pipeData) {
    $("#inputsTab").loading("start");
    await loadRunOptions("change");
    $("#creatorInfoPip").css("display", "block");

    disableRunSum();
    $("#permsRun").val(pipeData[0].perms);
    $("#ownUserNamePip").text(pipeData[0].username);
    $("#datecreatedPip").text(pipeData[0].date_created);
    $(".lasteditedPip").text(pipeData[0].date_modified);
    var projectpipelineOwn = await $runscope.checkProjectPipelineOwn();
    // release
    if (pipeData[0].release_date) {
        var parts = pipeData[0].release_date.split("-"); //YYYY-MM-DD
        var releaseDate = parts[1] + "/" + parts[2] + "/" + parts[0]; //MM,DD,YYYY
        $("#releaseVal").attr("date", releaseDate);
        if (projectpipelineOwn == "1") {
            $("#getTokenLink").css("display", "inline");
            $("#releaseVal").text(releaseDate);
        } else {
            $("#releaseVal").text("");
            $("#releaseValFinal").text(releaseDate);
        }
    } else {
        if (projectpipelineOwn !== "1") {
            $("#releaseVal").text("");
            $("#releaseLabel").text("");
        }
    }

    // release ends --

    await loadRunSettings(pipeData);
    checkShub();
    // bind event handlers for autofill
    setTimeout(async function() {
        // execute bindEveHandlerChooseEnv even autoFillJSON is not defined to fill default exec settings.
        bindEveHandlerChooseEnv(autoFillJSON, "pipeline");
        if (autoFillJSON !== null && autoFillJSON !== undefined) {
            bindEveHandler(autoFillJSON);
            // if duplicated run has refreshEnv trigger refreshEnv when page loads
            if (pipeData[0].onload) {
                if (pipeData[0].onload == "refreshEnv") {
                    console.log("onload refreshEnv");
                    await refreshEnv();
                }
            }
        }
    }, 1000);
    checkCloudType(pipeData[0].profile);
    //load cloud keys for possible s3/gs connection
    await loadCloudKeys(pipeData);
    // load group info
    await loadUserGroups(pipeData);
    // activate collapse icon for process options
    refreshCollapseIconDiv();

    await fillRunVerOpt("#runVerLog");
    //hide system inputs
    $("#systemInputs").trigger("click");
    //insert icon for process_options according to show_setting attribute
    hideProcessOptionsAsIcons();
    $("#inputsTab").loading("stop");

    //autofillEmptyInputs
    if (autoFillJSON !== undefined && autoFillJSON !== null) {
        await autofillEmptyInputs(autoFillJSON);
    }
    // clean depricated project pipeline inputs(propipeinputs) in case it is not found in the inputs table.
    await cleanDepProPipeInputs();
    //after loading pipeline disable all the inputs
    if (projectpipelineOwn !== "1") {
        toogleRunInputs("disable");
    }
    updateDiskSpace();
    setTimeout(async function() {
        await checkReadytoRun();
    }, 1000);
}

//click on "system inputs" button
$("#inputsTable").on("click", "#systemInputs", function(e) {
    var indx = $("#systemInputs").index();
    $("#inputsTable> tbody > tr:gt(" + indx + ")").toggle();
});

//click on "system inputs" button
$(document).on("click", ".setCron", async function(e) {
    console.log("setCron")
    var project_pipeline_id = $("#pipeline-title").attr("projectpipelineid");
    var cron_min = $("#cron_min").val();
    var cron_hour = $("#cron_hour").val();
    var cron_day = $("#cron_day").val();
    var cron_week = $("#cron_week").val();
    var cron_month = $("#cron_month").val();
    var cron_prefix = $("#cron_prefix").val();
    var cron_first = $("#cron_first").val();
    var cron_check = $("#cron_check").is(":checked").toString();
    let run = false
    console.log()
    if ((cron_min && cron_min != "0") || (cron_hour && cron_hour != "0") || (cron_day && cron_day != "0") ||
        (cron_week && cron_week != "0") || (cron_month && cron_month != "0") || cron_first) {
        run = true
    } else {
        showInfoModal("#infoModal", "#infoModalText", "Please set frequency or first submission date for automated execution.");
    }
    if (run) {
        var data = await doAjax({
            p: "saveCron",
            project_pipeline_id,
            cron_min,
            cron_hour,
            cron_day,
            cron_week,
            cron_month,
            cron_prefix,
            cron_check,
            cron_first
        });
        console.log(data)
        if (data) {
            toastr.info("Automated Execution is Activated");
            $("#cronNextSubDate").text(data)
        } else {
            toastr.error("Error Occured");

        }
    }


});

async function refreshEnv() {
    console.log("refreshEnv")
    await loadRunOptions("change");
}

//type="change","silent"
async function loadRunOptions(type) {
    var selectedOpt = $("#chooseEnv").find(":selected").val();
    $("#chooseEnv").find("option").not(":disabled").remove();
    //get profiles for user
    var proData = await doAjax({ p: "getProfiles", type: "run" });
    if (proData) {
        for (var c = 0; c < proData.length; c++) {
            var data = proData[c];
            if (data.hostname != undefined) {
                var option = "";
                if (data.perms == "15") {
                    option = new Option(data.name, "cluster-" + data.id);
                } else {
                    option = new Option(
                        data.name +
                        " (Remote machine: " +
                        data.username +
                        "@" +
                        data.hostname +
                        ")",
                        "cluster-" + data.id
                    );
                }
                option.setAttribute("host", data.hostname);
                option.setAttribute("executor_job", data.executor_job);
                option.setAttribute("perms", data.perms);
                if (data.amazon_cre_id) {
                    option.setAttribute("amz_key", data.amazon_cre_id);
                }
                option.setAttribute("auto_workdir", data.auto_workdir);
                $("#chooseEnv").append(option);
            } else if (data.shared_storage_mnt != undefined) {
                var option = "";
                if (data.perms == "15") {
                    option = new Option(data.name, "amazon-" + data.id);
                } else {
                    option = new Option(
                        data.name +
                        " (Amazon: Status:" +
                        data.status +
                        " Image id:" +
                        data.image_id +
                        " Instance type:" +
                        data.instance_type +
                        ")",
                        "amazon-" + data.id
                    );
                }
                // check ajaxquery.php getProfileVariables-> for host definitions
                option.setAttribute("host", data.shared_storage_id);
                option.setAttribute("executor_job", data.executor_job);
                option.setAttribute("perms", data.perms);
                option.setAttribute("status", data.status);
                option.setAttribute("amz_key", data.amazon_cre_id);
                option.setAttribute("auto_workdir", data.auto_workdir);
                $("#chooseEnv").append(option);
            } else if (data.google_cre_id != undefined) {
                var option = "";
                if (data.perms == "15") {
                    option = new Option(data.name, "google-" + data.id);
                } else {
                    option = new Option(
                        data.name +
                        " (Google: Status:" +
                        data.status +
                        " Image id:" +
                        data.image_id +
                        " Instance type:" +
                        data.instance_type +
                        ")",
                        "google-" + data.id
                    );
                }
                // check ajaxquery.php getprofilevariables-> for host definitions
                option.setAttribute("host", data.image_id);
                option.setAttribute("executor_job", data.executor_job);
                option.setAttribute("perms", data.perms);
                option.setAttribute("status", data.status);
                option.setAttribute("goog_key", data.google_cre_id);
                option.setAttribute("auto_workdir", data.auto_workdir);
                $("#chooseEnv").append(option);
            }
        }
    }
    if (selectedOpt) {
        $("#chooseEnv").val(selectedOpt);
        if (type == "silent") {
            await checkReadytoRun();
        } else {
            $("#chooseEnv").trigger("change");
        }
    }
}
//insert selected input to inputs table
function insertSelectInput(
    rowID,
    gNumParam,
    filePath,
    proPipeInputID,
    qualifier,
    collection,
    url,
    urlzip,
    checkPath,
    fillingType
) {
    var checkDropDown = $("#" + rowID).find("select[indropdown]")[0];
    var downloadIcon = "";
    if (checkDropDown) {
        $(checkDropDown).val(filePath);
        $("#" + rowID).attr("propipeinputid", proPipeInputID);
        $("#" + rowID)
            .find("#defValUse")
            .css("display", "none");
        if (fillingType == "dry") {
            $(checkDropDown).prop("disabled", true);
        } else {
            $(checkDropDown).prop("disabled", false);
        }
    } else {
        if (qualifier === "file" || qualifier === "set") {
            var editIcon = getIconButtonModal("inputFile", "Edit", "fa fa-pencil");
            var deleteIcon = getIconButton("inputDel", "Delete", "fa fa-trash-o");
            $("#" + rowID)
                .find("#inputFileEnter")
                .css("display", "none");
            $("#" + rowID)
                .find("#defValUse")
                .css("display", "none");
        } else if (qualifier === "single_file") {
            var editIcon = getIconButtonModal("inputSingleFile", "Edit", "fa fa-pencil");
            var deleteIcon = getIconButton("inputSingleFile", "Delete", "fa fa-trash-o");
            downloadIcon = getIconButton("inputSingleFile", "Download", "fa fa-download", "padding-top: 3px;");
            $("#" + rowID).find("#inputSingleFileEnter").css("display", "none");
            $("#" + rowID).find("#defValUse").css("display", "none");
        } else {
            var editIcon = getIconButtonModal("inputVal", "Edit", "fa fa-pencil");
            var deleteIcon = getIconButton("inputVal", "Delete", "fa fa-trash-o");
            $("#" + rowID)
                .find("#inputValEnter")
                .css("display", "none");
            $("#" + rowID)
                .find("#defValUse")
                .css("display", "none");
        }

        var showUrlIcon = "display:none;";
        var urlData = url || "";
        var urlzipData = urlzip || "";
        var checkPathData = checkPath || "";
        if (url || urlzip) {
            showUrlIcon = "";
        }
        var urlIcon =
            '<button type="button" class="btn"  url="' +
            urlData +
            '" urlzip="' +
            urlzipData +
            '" checkpath="' +
            checkPathData +
            '" style="' +
            showUrlIcon +
            ' background:none; padding:0px; margin-right:2px;" id="urlBut-' +
            rowID +
            '" ><a data-toggle="tooltip" data-placement="bottom" data-original-title="Download Info"><span><i style="font-size: 16px;" class="fa fa-cloud-download"></i></span></a></button>';

        filePath = escapeHtml(filePath);
        var collectionAttr = ' collection_id="" ';
        if (collection) {
            if (collection.collection_id && collection.collection_name) {
                collectionAttr = ' collection_id="' + collection.collection_id + '" ';
                filePath =
                    '<i class="fa fa-database"></i> ' + collection.collection_name;
            }
        }
        if (fillingType == "dry") {
            urlIcon = "";
            editIcon = "";
            deleteIcon = "";
        }
        $("#" + rowID)
            .find(".firstsec")
            .append(
                '<span style="padding-right:7px;" id="filePath-' +
                gNumParam +
                '" ' +
                collectionAttr +
                ">" +
                filePath +
                "</span>" +
                downloadIcon +
                urlIcon +
                editIcon +
                deleteIcon
            );
        $("#" + rowID).attr("propipeinputid", proPipeInputID);
    }
}
//remove for both dropdown and file/val options
function removeSelectFile(rowID, sType, fillingType) {
    var checkDropDown = $("#" + rowID).find("select[indropdown]")[0];
    if (checkDropDown) {
        // During dry-fill, remove selected option
        $("#" + rowID)
            .find("#defValUse")
            .css("display", "inline");
        if (fillingType == "dry") {
            var dropVal = $(checkDropDown).val();
            if (dropVal) {
                $(checkDropDown).val("");
            }
            $("#" + rowID)
                .find("#defValUse")
                .css("display", "none");
        }
        $("#" + rowID).removeAttr("propipeinputid");
    } else {
        if (sType === "file" || sType === "set") {
            $("#" + rowID)
                .find("#inputFileEnter")
                .css("display", "inline");
            if (fillingType == "dry") {
                $("#" + rowID)
                    .find("#inputFileEnter")
                    .prop("disabled", true);
                $("#" + rowID)
                    .find("#defValUse")
                    .css("display", "none");
            } else {
                $("#" + rowID)
                    .find("#inputFileEnter")
                    .prop("disabled", false);
                $("#" + rowID)
                    .find("#defValUse")
                    .css("display", "inline");
            }
        } else if (sType === "single_file") {
            $("#" + rowID).find("#inputSingleFileEnter").css("display", "inline");
            if (fillingType == "dry") {
                $("#" + rowID).find("#inputSingleFileEnter").prop("disabled", true);
                $("#" + rowID).find("#defValUse").css("display", "none");
            } else {
                $("#" + rowID).find("#inputSingleFileEnter").prop("disabled", false);
                $("#" + rowID).find("#defValUse").css("display", "inline");
            }
        } else if (sType === "val") {
            $("#" + rowID)
                .find("#inputValEnter")
                .css("display", "inline");
            $("#" + rowID)
                .find("#defValUse")
                .css("display", "inline");
            if (fillingType == "dry") {
                $("#" + rowID)
                    .find("#inputValEnter")
                    .prop("disabled", true);
                $("#" + rowID)
                    .find("#defValUse")
                    .css("display", "none");
            } else {
                $("#" + rowID)
                    .find("#inputValEnter")
                    .prop("disabled", false);
                $("#" + rowID)
                    .find("#defValUse")
                    .css("display", "inline");
            }
        }

        $("#" + rowID).find(".firstsec > span").remove();
        var buttonList = $("#" + rowID).find(".firstsec > button");
        for (var c = 0; c < buttonList.length; c++) {
            var butid = $(buttonList[c]).attr("id");
            if (
                butid == "inputDelDelete" ||
                butid == "inputValDelete" ||
                butid == "inputValEdit" ||
                butid == "inputSingleFileDelete" ||
                butid == "inputSingleFileDownload" ||
                butid == "inputSingleFileEdit" ||
                butid == "inputFileDelete" ||
                butid == "inputFileEdit" ||
                butid.match(/^urlBut-/)
            ) {
                buttonList[c].remove();
            }
        }
        $("#" + rowID).removeAttr("propipeinputid");
    }
}

async function checkInputInsert(
    data,
    gNumParam,
    given_name,
    qualifier,
    rowID,
    sType,
    inputID,
    collection,
    url,
    urlzip,
    checkPath
) {
    if (inputID === null) {
        inputID = "";
    }
    var nameInput = "";
    if (data) {
        nameInput = data[1].value;
        nameInput = nameInput.replace(/\"/g, "_").replace(/\'/g, "_");
    }
    var collection_id = "";
    var collection_name = "";
    if (collection) {
        if (collection.collection_id) {
            collection_id = collection.collection_id;
            collection_name = collection.collection_name;
        }
    }
    var urlData = url || "";
    var urlzipData = urlzip || "";
    var checkPathData = checkPath || "";
    var fillInput = await doAjax({
        p: "fillInput",
        inputID: inputID,
        collection_id: collection_id,
        inputName: nameInput,
        inputType: sType,
        project_id: project_id,
        pipeline_id: pipeline_id,
        project_pipeline_id: project_pipeline_id,
        g_num: gNumParam,
        given_name: given_name,
        qualifier: qualifier,
        proPipeInputID: "",
        url: urlData,
        urlzip: urlzipData,
        checkpath: checkPathData,
    });
    //insert into #inputsTab
    var fillingType = "default";
    if (fillInput.projectPipelineInputID && collection_name) {
        insertSelectInput(
            rowID,
            gNumParam,
            collection_name,
            fillInput.projectPipelineInputID,
            sType,
            collection,
            url,
            urlzip,
            checkPath,
            fillingType
        );
    } else if (fillInput.projectPipelineInputID && fillInput.inputName) {
        insertSelectInput(
            rowID,
            gNumParam,
            fillInput.inputName,
            fillInput.projectPipelineInputID,
            sType,
            collection,
            url,
            urlzip,
            checkPath,
            fillingType
        );
    }
}

async function checkInputEdit(
    data,
    gNumParam,
    given_name,
    qualifier,
    rowID,
    sType,
    proPipeInputID,
    inputID,
    collection,
    url,
    urlzip,
    checkPath
) {
    if (inputID === null) {
        inputID = "";
    }
    var nameInput = "";
    if (data) {
        nameInput = data[1].value;
        nameInput = nameInput.replace(/\"/g, "").replace(/\'/g, "");
    }
    var collection_id = "";
    var collection_name = "";
    if (collection) {
        if (collection.collection_id && collection.collection_name) {
            collection_id = collection.collection_id;
            collection_name = collection.collection_name;
        }
    }
    var urlData = url || "";
    var urlzipData = urlzip || "";
    var checkPathData = checkPath || "";
    var fillInput = await doAjax({
        p: "fillInput",
        inputID: inputID,
        collection_id: collection_id,
        inputName: nameInput,
        inputType: sType,
        project_id: project_id,
        pipeline_id: pipeline_id,
        project_pipeline_id: project_pipeline_id,
        g_num: gNumParam,
        given_name: given_name,
        qualifier: qualifier,
        proPipeInputID: proPipeInputID,
        url: urlData,
        urlzip: urlzipData,
        checkpath: checkPathData,
    });
    //update #inputsTab
    if (fillInput.projectPipelineInputID && collection_name) {
        $("#filePath-" + gNumParam).html(
            '<i class="fa fa-database"></i> ' + collection_name
        );
    } else if (fillInput.projectPipelineInputID && fillInput.inputName) {
        $("#filePath-" + gNumParam).text(fillInput.inputName);
    }
    $("#filePath-" + gNumParam).attr("collection_id", collection_id);
    //update urlBut settings inside #inputsTab
    var urlData = url || "";
    var urlzipData = urlzip || "";
    var checkPathData = checkPath || "";
    if (url || urlzip) {
        $("#urlBut-" + rowID).css("display", "inline-block");
    } else {
        $("#urlBut-" + rowID).css("display", "none");
    }
    $("#urlBut-" + rowID).attr("url", urlData);
    $("#urlBut-" + rowID).attr("urlzip", urlzipData);
    $("#urlBut-" + rowID).attr("checkpath", checkPathData);
}

async function saveFileSetValModal(data, sType, inputID, collection) {
    if (sType === "file" || sType === "set") {
        sType = "file"; //for simplification
        var rowID = $("#mIdFile").attr("rowID"); //the id of table-row to be updated #inputTa-3
    } else if (sType === "val") {
        var rowID = $("#mIdVal").attr("rowID"); //the id of table-row to be updated #inputTa-3
    } else if (sType === "single_file") {
        var rowID = $("#mIdSingleFile").attr("rowID"); //the id of table-row to be updated #inputTa-3
    }
    var gNumParam = rowID.split("Ta-")[1];
    var given_name = $("#input-PName-" + gNumParam).attr("name"); //input-PName-3
    var qualifier = $("#" + rowID + " > :nth-child(4)").text();
    var url = null,
        urlzip = null,
        checkPath = null;
    //check database if file is exist, if not exist then insert
    await checkInputInsert(
        data,
        gNumParam,
        given_name,
        qualifier,
        rowID,
        sType,
        inputID,
        collection,
        url,
        urlzip,
        checkPath
    );
    await checkReadytoRun();
}

async function editFileSetValModal(data, sType, inputID, collection) {
    if (sType === "file" || sType === "set") {
        sType = "file";
        var rowID = $("#mIdFile").attr("rowID"); //the id of table-row to be updated #inputTa-3
    } else if (sType === "val") {
        var rowID = $("#mIdVal").attr("rowID"); //the id of table-row to be updated #inputTa-3
    } else if (sType === "single_file") {
        var rowID = $("#mIdSingleFile").attr("rowID"); //the id of table-row to be updated #inputTa-3
    }
    var proPipeInputID = $("#" + rowID).attr("propipeinputid");
    var gNumParam = rowID.split("Ta-")[1];
    var given_name = $("#input-PName-" + gNumParam).attr("name"); //input-PName-3
    var qualifier = $("#" + rowID + " > :nth-child(4)").text();
    var url = null,
        urlzip = null,
        checkPath = null;
    //check database if file is exist, if not exist then insert
    await checkInputEdit(
        data,
        gNumParam,
        given_name,
        qualifier,
        rowID,
        sType,
        proPipeInputID,
        inputID,
        collection,
        url,
        urlzip,
        checkPath
    );
    await checkReadytoRun();
}

//keep record of missing variables
function addMissingVar(defName) {
    if (defName) {
        if (defName.match(/\$\{(.*)\}/)) {
            var missingAr = defName.split("${");
            for (var i = 0; i < missingAr.length; i++) {
                if (missingAr[i].match(/}/)) {
                    var missingVar = missingAr[i].substring(0, missingAr[i].indexOf("}"));
                    if (missingVar) {
                        //global object for missing variables
                        if (!window["undefinedVarObj"]) {
                            window["undefinedVarObj"] = {};
                        }
                        if (window["undefinedVarObj"]) {
                            window["undefinedVarObj"][missingVar] = "";
                        }
                    }
                }
            }
        }
    }
}

async function checkMissingVar() {
    window["undefinedVarObj"] = {};
    var projectpipelineOwn = await $runscope.checkProjectPipelineOwn();
    if (projectpipelineOwn === "1") {
        var systemInputAr = $("#inputsTab, .ui-dialog")
            .find("td[given_name]")
            .filter(function() {
                return systemInputs.indexOf($(this).attr("given_name")) > -1;
            });
        //get all system input paths
        for (var i = 0; i < systemInputAr.length; i++) {
            var inputSpan = $(systemInputAr[i]).find("span[id*='filePath']");
            if (inputSpan && inputSpan[0]) {
                var inputPath = $(inputSpan[0]).text();
                addMissingVar(inputPath);
            }
        }

        if (!$.isEmptyObject(window["undefinedVarObj"])) {
            var egText = "";
            var undefinedVarAr = [];
            var warnText =
                'Please choose a directory to save the downloaded files. <button type="button" class="btn btn-danger btn-sm" id="defVarRunEnv" data-toggle="modal" data-target="#profVarRunEnvModal">Enter Directory</button>';
            if (!document.getElementById("undefinedVar")) {
                var warningPanel =
                    '<div id="undefinedVar" class="panel panel-danger" style="border:2px solid #E08D08; background-color:#e08d080f;"><div class="panel-body"><span id="undefText">' +
                    warnText +
                    "</span></div></div>";
                $("#warningSection").append(warningPanel);
            } else {
                $("#undefText").html(warnText);
            }
        } else {
            $("#warningSection> #undefinedVar").remove();
        }
    }
}

checkType = "";
//checkType become "rerun" or "resumerun" when rerun or resume button is clicked.
async function checkReadytoRun(type) {
    console.log("checkReadytoRun");
    if (checkType === "") {
        checkType = type || "";
    }
    await checkMissingVar();
    runStatus = await getRunStatus(project_pipeline_id);
    project_pipeline_id = $("#pipeline-title").attr("projectpipelineid");
    var getProPipeInputs = await doAjax({
        p: "getProjectPipelineInputs",
        project_pipeline_id: project_pipeline_id,
    });
    var filledInputsCheck = false;
    var allInputNames = [];
    $("#inputsTab, .ui-dialog")
        .find("td[given_name]")
        .filter(function() {
            if (!$(this).parent().attr("optional")) {
                allInputNames.push($(this).attr("given_name"));
            }
        });

    var existingInputNames = $.map(getProPipeInputs, function(n, i) {
        return n.given_name;
    });

    var filledInputsCheck = $(allInputNames).not(existingInputNames).get().length < 1;
    var profileNext = $("#chooseEnv").find(":selected").val();
    var cloudStatus = $("#chooseEnv").find(":selected").attr("status");
    var output_dir = $runscope.getPubVal("work");

    var publishReady = false;
    var publish_dir_check = $("#publish_dir_check").is(":checked").toString();
    if (publish_dir_check === "true") {
        var publish_dir = $runscope.getPubVal("publish");
        if (publish_dir !== "") {
            publishReady = true;
        } else {
            publishReady = false;
        }
    } else {
        var publish_dir = "";
        publishReady = true;
    }
    //check if s3: is defined in publish_dir and getProPipeInputs
    //    var s3Status = checkCloudPatt(
    //        "s3:",
    //        "#mRunAmzKeyDiv",
    //        "#mRunAmzKey",
    //        publish_dir,
    //        getProPipeInputs
    //    );
    //    var gsStatus = checkCloudPatt("gs:", "#mRunGoogKeyDiv", '#mRunGoogKey', publish_dir, getProPipeInputs);

    //if ready and not running/waits/error
    if (publishReady && filledInputsCheck && profileNext !== "" && output_dir !== "") {
        console.log("initial runStatus", runStatus);
        if (
            runStatus == "" ||
            checkType === "rerun" ||
            checkType === "newrun" ||
            checkType === "resumerun"
        ) {
            if (cloudStatus) {
                if (cloudStatus === "running") {
                    console.log("checkType", checkType);
                    if (checkType === "rerun" || checkType === "resumerun") {
                        runStatus = "aboutToStart";
                        await runProjectPipe(runProPipeCall, checkType);
                    } else if (checkType === "newrun") {
                        displayButton("runProPipe");
                    } else {
                        displayButton("runProPipe");
                    }
                } else {
                    displayButton("statusProPipe");
                }
            } else {
                console.log("checkType", checkType);
                if (checkType === "rerun" || checkType === "resumerun") {
                    runStatus = "aboutToStart";
                    await runProjectPipe(runProPipeCall, checkType);
                } else if (checkType === "newrun") {
                    displayButton("runProPipe");
                } else {
                    displayButton("runProPipe");
                }
            }
        }
    } else {
        if (
            (runStatus !== "NextRun" &&
                runStatus !== "Waiting" &&
                runStatus !== "init" &&
                (checkType === "rerun" || checkType === "newrun")) ||
            runStatus === ""
        ) {
            displayButton("statusProPipe");
        }
    }
    //reset of checkType will be conducted in runProjectPipe as well
    //if checkType rerun || resumerun come to this point, it means run not executed
    if (checkType === "rerun" || checkType === "resumerun") {
        checkType = "newrun";
    }
}

//check if singu image path contains shub:// pattern
$("#singu_img").keyup(function() {
    autoCheckShub();
});
var timeoutCheckShub = 0;

function autoCheckShub() {
    if (timeoutCheckShub) clearTimeout(timeoutCheck);
    timeoutCheckShub = setTimeout(function() {
        checkShub();
    }, 2000);
}

//check if singu image path contains shub:// pattern then show "save over image" checkbox
function checkShub() {
    var singuPath = $("#singu_img").val();
    var shubpattern = "shub://";
    var pathCheck = false;
    if (singuPath !== "") {
        if (
            singuPath.indexOf(shubpattern) > -1 ||
            singuPath.indexOf("ftp:") > -1 ||
            singuPath.indexOf("http:") > -1 ||
            singuPath.indexOf("https:") > -1
        ) {
            $("#singu_save_div").css("display", "block");
        } else {
            $("#singu_save_div").css("display", "none");
            $("#singu_save").prop("checked", false);
        }
    } else {
        $("#singu_save_div").css("display", "none");
        $("#singu_save").prop("checked", false);
    }
}

function checkCloudType(profileTypeId) {
    if (profileTypeId) {
        var patt = /(.*)-(.*)/;
        var proType = profileTypeId.replace(patt, "$1");
        if (proType) {
            if (proType == "amazon") {
                $("#mArchAmzS3Div_GEO").css("display", "block");
                $("#mArchAmzS3Div").css("display", "block");
                $("#mArchGoogGSDiv").css("display", "none");
                $("#mArchGoogGSDiv_GEO").css("display", "none");
            } else if (proType == "google") {
                $("#mArchAmzS3Div_GEO").css("display", "none");
                $("#mArchAmzS3Div").css("display", "none");
                $("#mArchGoogGSDiv").css("display", "block");
                $("#mArchGoogGSDiv_GEO").css("display", "block");
            } else {
                $("#mArchAmzS3Div_GEO").css("display", "block");
                $("#mArchAmzS3Div").css("display", "block");
                $("#mArchGoogGSDiv").css("display", "block");
                $("#mArchGoogGSDiv_GEO").css("display", "block");
            }
        }
    }
}

function updateDiskSpace() {
    console.log("updateDiskSpace");
    var workDir = $runscope.getPubVal("work");
    var countSlash = (workDir.match(/\//g) || []).length;
    if (countSlash > 1) {
        if (workDir && proTypeWindow && proIdWindow) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    p: "getDiskSpace",
                    dir: workDir,
                    profileType: proTypeWindow,
                    profileId: proIdWindow,
                },
                async: true,
                beforeSend: function() {
                    $("#workdir_diskpercent").html("Checking");
                },
                success: function(s) {
                    if (s) {
                        console.log(s);
                        if (s.percent && s.free) {
                            $("#workdir_diskpercent").css("width", s.percent);
                            $("#workdir_diskpercent").html(s.percent);
                            var workdir_diskspace =
                                '<a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Available Disk Space (updated every 10 minutes)">' +
                                s.free +
                                " free" +
                                "</a>";
                            if (s.workdir_size) {
                                var usedInfo =
                                    '<a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Size of the work directory (updated every 10 minutes)">Size: ' +
                                    s.workdir_size +
                                    '  <i style="font-size: 13px; ma" class="fa fa-info-circle"></i></a> ';
                                workdir_diskspace +=
                                    '<span style="margin-left:3px; margin-right:3px;"> | </span>' +
                                    usedInfo;
                            }
                            $("#workdir_diskspace").html(workdir_diskspace);
                            if (s.workdir_size) {
                                $('[data-toggle="tooltip"]').tooltip();
                            }
                            let now = new Date();
                            $("#workdir_diskpercent").data("lastcheck", now);
                            toogleWorkdirDiskSpace("show");
                        } else {
                            toogleWorkdirDiskSpace("hide");
                        }
                    }
                },
                error: function(errorThrown) {
                    toogleWorkdirDiskSpace("hide");
                },
            });
        } else {
            toogleWorkdirDiskSpace("hide");
        }
    } else {
        toogleWorkdirDiskSpace("hide");
    }
}

function toogleWorkdirDiskSpace(type) {
    if (type == "show") {
        $("#workdir_diskspace").css("display", "block");
        $("#updateDiskSpaceBut").css("display", "inline-block");
        $("#workdir_diskpercent_div").css("display", "block");
        $("#refreshDiskSpaceBut").css("display", "block");
    } else if (type == "hide") {
        $("#workdir_diskspace").css("display", "none");
        $("#updateDiskSpaceBut").css("display", "none");
        $("#workdir_diskpercent_div").css("display", "none");
        $("#refreshDiskSpaceBut").css("display", "none");
    }
}

function checkWorkDir() {
    var showInfoM = false;
    var infoModalText = "Please check your work directory:";
    var output_dir = $runscope.getPubVal("work");
    if (output_dir) {
        // full path should start with slash
        if (output_dir[0] != "/") {
            showInfoM = true;
            infoModalText += "</br> * It should start with slash symbol (/).";
        }
        if (output_dir.indexOf(" ") >= 0) {
            showInfoM = true;
            infoModalText += "</br> * There shouldn't be any space in your path.";
        }
        if (showInfoM) {
            showInfoModal("#infoModal", "#infoModalText", infoModalText);
        } else {
            updateDiskSpace();
        }
    } else {
        updateDiskSpace();
    }
}

//Autocheck the output,publish_dir,publish_dir_check for checkreadytorun
$("#rOut_dir").keyup(function() {
    autoCheck();
    autoCheckWorkDir();
});
$("#publish_dir").keyup(function() {
    autoCheck();
});
$("#publish_dir_check").click(function() {
    autoCheck();
});
//file import modal
$("#file_dir").keyup(function() {
    autoCheckS3("#file_dir", "#mRunAmzKeyS3Div");
    //    autoCheckGS("#file_dir", "#mRunGoogKeyGSDiv");
});
$("#s3_archive_dir_geo").keyup(function() {
    autoCheckS3("#s3_archive_dir_geo", "#mArchAmzKeyS3Div_GEO");
});

$("#s3_archive_dir").keyup(function() {
    autoCheckS3("#s3_archive_dir", "#mArchAmzKeyS3Div");
});

//$("#gs_archive_dir").keyup(function () {
//    autoCheckGS("#gs_archive_dir", "#mArchGoogKeyGSDiv");
//});
//
//$("#gs_archive_dir_geo").keyup(function () {
//    autoCheckGS("#gs_archive_dir_geo", "#mArchGoogKeyGSDiv_GEO");
//});

var timeoutCheck = 0;

function autoCheck(type) {
    var autoCheckType = type || "";
    if (timeoutCheck) clearTimeout(timeoutCheck);
    if (autoCheckType == "fillstates") {
        timeoutCheck = setTimeout(async function() {
            $("#inputsTab").loading("stop");
            await checkReadytoRun();
            if (changeOnchooseEnv != undefined) {
                if (changeOnchooseEnv == true) {
                    //save run after all parameters loaded on change of chooseEnv
                    await saveRun(false, true);
                }
            }
        }, 2000);
    } else {
        timeoutCheck = setTimeout(async function() {
            await checkReadytoRun();
        }, 2000);
    }
}

timeoutCheckGS = 0;

function autoCheckGS(inputID, showDivId) {
    var pattern = "gs:";
    if (timeoutCheckGS) {
        clearTimeout(timeoutCheckGS);
    }
    timeoutCheckGS = setTimeout(function() {
        checkCloudfilePath(pattern, inputID, showDivId);
    }, 2000);
}

timeoutCheckS3 = 0;

function autoCheckS3(inputID, showDivId) {
    var pattern = "s3:";
    if (timeoutCheckS3) {
        clearTimeout(timeoutCheckS3);
    }
    timeoutCheckS3 = setTimeout(function() {
        checkCloudfilePath(pattern, inputID, showDivId);
    }, 2000);
}

timeoutCheckWorkDir = 0;

function autoCheckWorkDir() {
    if (timeoutCheckWorkDir) {
        clearTimeout(timeoutCheckWorkDir);
    }
    timeoutCheckWorkDir = setTimeout(function() {
        checkWorkDir();
    }, 3000);
}

//check if file import path contains s3:// pattern and shows aws menu
function checkCloudfilePath(pattern, inputID, showDivId) {
    var file_path = $(inputID).val();
    var pathCheck = false;
    if (file_path !== "") {
        if (file_path.toLowerCase().indexOf(pattern) > -1) {
            $(showDivId).css("display", "block");
            pathCheck = true;
        }
    }
    if (pathCheck === false) {
        $(showDivId).css("display", "none");
    }
}

//check if path contains s3:// pattern and shows aws menu
function checkCloudPatt(pattern, div, key, path, getProPipeInputs) {
    var pattCheck;
    //path part
    var pathCheck = false;
    if (path !== "") {
        if (path.toLowerCase().indexOf(pattern) > -1) {
            $(div).css("display", "inline");
            pathCheck = true;
        } else {
            pathCheck = false;
        }
    } else {
        pathCheck = false;
    }
    //getProPipeInputs part
    var nameCheck = 0;
    $.each(getProPipeInputs, function(el) {
        var inputName = getProPipeInputs[el].name;
        if (inputName) {
            if (inputName.indexOf(pattern) > -1) {
                $(div).css("display", "inline");
                nameCheck = nameCheck + 1;
            }
        }
    });
    if (nameCheck === 0 && pathCheck === false) {
        $(div).css("display", "none");
        pattCheck = false;
    } else {
        pattCheck = true;
    }

    var keyvalue = $(key).val();
    var status = false;
    if (pattCheck === true && keyvalue !== null) {
        status = true;
    } else if (pattCheck === false) {
        status = true;
    }
    return status;
}

async function loadCloudKeys(pipeData) {
    var data = await doAjax({ p: "getAmz" });
    if (data && data != "") {
        $.each(data, function(i, item) {
            $("#mRunAmzKey").append(
                $("<option>", { value: item.id, text: item.name })
            );
            $("#mRunAmzKeyS3").append(
                $("<option>", { value: item.id, text: item.name })
            );
            $("#mArchAmzKeyS3").append(
                $("<option>", { value: item.id, text: item.name })
            );
            $("#mArchAmzKeyS3_GEO").append(
                $("<option>", { value: item.id, text: item.name })
            );
        });
    }
    var dataG = await doAjax({ p: "getGoogle" });
    if (dataG && dataG != "") {
        $.each(dataG, function(i, item) {
            $("#mRunGoogKey").append(
                $("<option>", { value: item.id, text: item.name })
            );
            $("#mRunGoogKeyGS").append(
                $("<option>", { value: item.id, text: item.name })
            );
            $("#mArchGoogKeyGS").append(
                $("<option>", { value: item.id, text: item.name })
            );
            $("#mArchGoogKeyGS_GEO").append(
                $("<option>", { value: item.id, text: item.name })
            );
        });
    }
    if (pipeData[0].amazon_cre_id !== "0") {
        $("#mRunAmzKey").val(pipeData[0].amazon_cre_id);
    } else {
        selectCloudKey();
    }
    if (pipeData[0].google_cre_id !== "0") {
        $("#mRunGoogKey").val(pipeData[0].google_cre_id);
    } else {
        selectCloudKey();
    }
}

//autoselect selectCloudKey based on selected profile
function selectCloudKey() {
    var amzKeyId = $("#chooseEnv").find(":selected").attr("amz_key");
    if (amzKeyId) {
        $("#mRunAmzKey").val(parseInt(amzKeyId));
        $("#mRunAmzKeyS3").val(parseInt(amzKeyId));
        $("#mArchAmzKeyS3").val(parseInt(amzKeyId));
        $("#mArchAmzKeyS3_GEO").val(parseInt(amzKeyId));
        $("#mRunAmzKey").trigger("change");
        $("#mRunAmzKeyS3").trigger("change");
        $("#mArchAmzKeyS3").trigger("change");
        $("#mArchAmzKeyS3_GEO").trigger("change");
    }
    var googKeyId = $("#chooseEnv").find(":selected").attr("goog_key");
    if (googKeyId) {
        $("#mRunGoogKey").val(parseInt(googKeyId));
        $("#mRunGoogKeyGS").val(parseInt(googKeyId));
        $("#mArchGoogKeyGS").val(parseInt(googKeyId));
        $("#mArchGoogKeyGS_GEO").val(parseInt(googKeyId));
        $("#mRunGoogKey").trigger("change");
        $("#mRunGoogKeyGS").trigger("change");
        $("#mArchGoogKeyGS").trigger("change");
        $("#mArchGoogKeyGS_GEO").trigger("change");
    }
}

//type="" or "Opt"
function displayStatButton(idButton, type) {
    var buttonList = [
        "runStatError",
        "runStatComplete",
        "runStatRunning",
        "runStatWaiting",
        "runStatConnecting",
        "runStatTerminated",
        "runStatAborted",
        "runStatManual",
        "runStatErrorOpt",
        "runStatCompleteOpt",
        "runStatRunningOpt",
        "runStatWaitingOpt",
        "runStatConnectingOpt",
        "runStatTerminatedOpt",
        "runStatAbortedOpt",
        "runStatManualOpt",
        "runStatErrorNoOpt",
        "runStatCompleteNoOpt",
        "runStatRunningNoOpt",
        "runStatWaitingNoOpt",
        "runStatConnectingNoOpt",
        "runStatTerminatedNoOpt",
        "runStatAbortedNoOpt",
        "runStatManualNoOpt",
    ];
    for (var i = 0; i < buttonList.length; i++) {
        if (document.getElementById(buttonList[i])) {
            document.getElementById(buttonList[i]).style.display = "none";
        }
    }
    if (type) {
        idButton = idButton + type;
    }
    if (document.getElementById(idButton)) {
        document.getElementById(idButton).style.display = "inline";
    }
}

function updateRunProPipeOptions() {
    var length = $("#runVerLog > option").length;
    if (length < 3) {
        $("#runProPipeStart").css("display", "list-item");
        $("#runProPipeReRun").css("display", "none");
        $("#runProPipeResume").css("display", "none");
    } else {
        $("#runProPipeStart").css("display", "none");
        $("#runProPipeReRun").css("display", "list-item");
        $("#runProPipeResume").css("display", "list-item");
    }
}

function showManualRunModal() {
    $("#manualRunModal").modal("show");
}

$(document).on("click", "#runProPipeBut", function(e) {
    e.stopPropagation();
    $("#runProPipeDropdown").dropdown("toggle");
    updateRunProPipeOptions();
});

$(document).on("click", "#runProPipeBut2", function(e) {
    updateRunProPipeOptions();
});

function disableRunSum() {
    $("#editRunSum").css("display", "inline-block");
    $("#saveRunSum").css("display", "none");
    $("#runSum").prop("disabled", true);
}

$(document).on("click", "#editRunSum", function(e) {
    $("#editRunSum").css("display", "none");
    $("#saveRunSum").css("display", "inline-block");
    $("#runSum").prop("disabled", false);
});
$(document).on("click", "#saveRunSum", function(e) {
    saveRunSum();
});

function displayButton(idButton) {
    var buttonList = [
        "runProPipe",
        "errorProPipe",
        "completeProPipe",
        "runningProPipe",
        "waitingProPipe",
        "statusProPipe",
        "connectingProPipe",
        "terminatedProPipe",
        "abortedProPipe",
        "manualProPipe",
    ];
    for (var i = 0; i < buttonList.length; i++) {
        if (document.getElementById(buttonList[i])) {
            document.getElementById(buttonList[i]).style.display = "none";
        }
    }
    if (document.getElementById(idButton)) {
        document.getElementById(idButton).style.display = "inline";
    }
}
//xxxxx
async function terminateProjectPipe() {
    if ($runscope.beforeunload) {
        showInfoModal(
            "#infoModal",
            "#infoModalText",
            "Please wait for the submission."
        );
    } else {
        var proType = proTypeWindow;
        var proId = proIdWindow;
        var [allProSett, profileData] = await getJobData("both");
        var executor = profileData[0].executor;
        var terminateRun = await doAjax({
            p: "terminateRun",
            project_pipeline_id: project_pipeline_id,
            profileType: proType,
            profileId: proId,
            executor: executor,
        });
        console.log(terminateRun);

        var setStatus = await doAjax({
            p: "updateRunStatus",
            run_status: "Terminated",
            project_pipeline_id: project_pipeline_id,
        });
        if (setStatus) {
            displayButton("terminatedProPipe");
            window.runStatus = "Terminated";
            //trigger saving newxtflow log file
            setTimeout(function() {
                clearIntNextLog(proType, proId);
            }, 3000);
            readPubWeb(proType, proId, "no_reload");
        }
    }
}

function parseMountPath(path, length) {
    if (path != null && path != "") {
        if (path.match("^/") && !path.match(/:/)) {
            var allDir = path.split("/");
            if (length == 2 && allDir[1] && allDir[2]) {
                return "/" + allDir[1] + "/" + allDir[2];
            } else if (length == 1 && allDir[1]) {
                return "/" + allDir[1];
            }
        }
    }
    return null;
}
//when -E is not defined add paths, If -E defined then replace the content of -E "paths"
function getNewExecOpt(oldExecOpt, newPaths) {
    var newExecAll = "";
    if (!oldExecOpt) {
        oldExecOpt = "";
    }
    if (!oldExecOpt.match(/\-E/)) {
        newExecAll = oldExecOpt + newPaths;
    } else if (oldExecOpt.match(/\-E "(.*)"/)) {
        var patt = /(.*)-E \"(.*)\"(.*)/;
        newExecAll = oldExecOpt.replace(patt, "$1" + newPaths + "$3");
    }
    return newExecAll;
}

function removeCollectionFromInputs(col_id) {
    //get all input paths
    var inputPaths = $("#inputsTab, .ui-dialog").find(
        "span[id*='filePath']"
    );
    if (inputPaths && inputPaths != null) {
        $.each(inputPaths, function(el) {
            var collection_id = $(inputPaths[el]).attr("collection_id");
            if (collection_id) {
                if (collection_id == col_id) {
                    var delButton = $(inputPaths[el])
                        .parent()
                        .find("button[id*='inputDelDelete']");
                    $(delButton).trigger("click");
                    $("#mIdFile").val(""); //reset modal for insert new collection
                }
            }
        });
    }
}

function getPathArray() {
    var pathArray = [];
    var workDir = $runscope.getPubVal("work");
    if (workDir) {
        pathArray.push(workDir);
    }

    if ($("#docker_check").is(":checked") === true) {
        pathArray.push($("#docker_opt").val());
    } else if ($("#singu_check").is(":checked") === true) {
        pathArray.push($("#singu_opt").val());
    }

    //get all input paths
    var inputPaths = $("#inputsTab, .ui-dialog").find(
        "span[id*='filePath']"
    );
    if (inputPaths && inputPaths != null) {
        $.each(inputPaths, async function(el) {
            var collection_id = $(inputPaths[el]).attr("collection_id");
            if (collection_id) {
                var colFiles = await doAjax({
                    id: collection_id,
                    p: "getCollectionFiles",
                });
                for (var i = 0; i < colFiles.length; i++) {
                    if (colFiles[i].file_dir) {
                        if (!colFiles[i].file_dir.match(/s3:/i) ||
                            !colFiles[i].file_dir.match(/gs:/i)
                        ) {
                            var inputPath = colFiles[i].file_dir;
                            if (pathArray.indexOf(inputPath) === -1) {
                                pathArray.push(inputPath);
                            }
                        }
                    }
                }
            } else {
                var inputPath = $(inputPaths[el]).text();
                if (inputPath) {
                    if (pathArray.indexOf(inputPath) === -1) {
                        pathArray.push(inputPath);
                    }
                }
            }
        });
    }
    //get form paths
    var formPaths = $("div[id^='addProcessRow-']").find("input");
    if (formPaths && formPaths != null) {
        $.each(formPaths, function(el) {
            var inputPath = $(formPaths[el]).val();
            if (inputPath) {
                if (pathArray.indexOf(inputPath) === -1) {
                    pathArray.push(inputPath);
                }
            }
        });
    }
    return pathArray;
}

//Autofill runOptions of singularity and docker image
function autofillMountPathImage(pathArrayL1) {
    console.log(pathArrayL1);
    var excludePaths = [
        "/lib",
        "/opt",
        "/bin",
        "/boot",
        "/dev",
        "/lib64",
        "/media",
        "/proc",
        "/root",
        "/sbin",
        "/srv",
        "/sys",
        "/usr",
        "/var",
    ];
    // docker.runOptions = -v /export:/export
    // singularity.runOptions = -B /export:/export

    //default add /home to initial run binding list if google cloud is used. (credential file is required in the image)
    var cloudType = $("#chooseEnv").find(":selected").val();
    var patt = /(.*)-(.*)/;
    var proType = cloudType.replace(patt, "$1");
    if (proType == "google" && pathArrayL1.indexOf("/home") === -1) {
        pathArrayL1.push("/home");
    }

    var newRunOpt = "";
    var oldRunOpt = "";
    var bindParam = "";
    if (pathArrayL1.length > 0) {
        if ($("#docker_check").is(":checked") === true) {
            bindParam = "-v";
            oldRunOpt = $("#docker_opt").val();
        } else if ($("#singu_check").is(":checked") === true) {
            bindParam = "-B";
            oldRunOpt = $("#singu_opt").val();
        }
        //combine items as /path -> /path:/path
        newRunOpt = oldRunOpt;
        for (var k = 0; k < pathArrayL1.length; k++) {
            if (!oldRunOpt.match(pathArrayL1[k]) &&
                $.inArray(pathArrayL1[k], excludePaths) === -1
            ) {
                newRunOpt +=
                    " " + bindParam + " " + pathArrayL1[k] + ":" + pathArrayL1[k] + " ";
            }
        }
        if ($("#docker_check").is(":checked") === true) {
            $("#docker_opt").val(newRunOpt);
        } else if ($("#singu_check").is(":checked") === true) {
            $("#singu_opt").val(newRunOpt);
        }
    }
    return newRunOpt;
}

//autofill for ghpcc06 cluster to mount all directories before run executed.
function autofillMountPath(pathArray) {
    var pathArrayL2 = [];
    for (var i = 0; i < pathArray.length; i++) {
        var length = 2;
        var parsedPath = parseMountPath(pathArray[i], length);
        if (parsedPath != null) {
            if (pathArrayL2.indexOf(parsedPath) === -1) {
                pathArrayL2.push(parsedPath);
            }
        }
    }
    //turn into lsf command (use -E to define scripts which will be executed just before the main job)
    if (pathArrayL2.length > 0) {
        var execOtherOpt = '-E "file ' + pathArrayL2.join(" && file ") + '"';
    } else {
        var execOtherOpt = "";
    }
    //check if exec_all or exec_each checkboxes are clicked.
    if ($("#exec_all").is(":checked") === true) {
        var oldExecAll = $("#job_clu_opt").val();
        var newExecAll = getNewExecOpt(oldExecAll, execOtherOpt);
        $("#job_clu_opt").val(newExecAll);
    }
    if ($("#exec_each").is(":checked") === true) {
        var checkedBox = $("#processTable").find("input:checked");
        var checkedBoxArray = checkedBox.toArray();
        var formDataArr = {};
        $.each(checkedBoxArray, function(el) {
            var boxId = $(checkedBoxArray[el]).attr("id");
            var patt = /(.*)-(.*)/;
            var proGnum = boxId.replace(patt, "$2");
            var oldExecEachDiv = $("#procGnum-" + proGnum).find("input[name=opt]")[0];
            var oldExecEach = $(oldExecEachDiv).val();
            var newExecEach = getNewExecOpt(oldExecEach, execOtherOpt);
            $(oldExecEachDiv).val(newExecEach);
        });
    }
    return execOtherOpt;
}

function getPathArrayL1(pathArray) {
    var pathArrayL1 = [];
    for (var i = 0; i < pathArray.length; i++) {
        var length = 1;
        var parsedPath = parseMountPath(pathArray[i], length);
        if (parsedPath != null) {
            if (pathArrayL1.indexOf(parsedPath) === -1) {
                pathArrayL1.push(parsedPath);
            }
        }
    }
    return pathArrayL1;
}

//callbackfunction to first change the status of button to connecting
async function runProjectPipe(runProPipeCall, checkType) {
    //reset the checktype
    var keepCheckType = checkType;
    var pathArray = [];
    var pathArrayL1 = []; //shortened to 1 directory
    var profileData = [];
    window.checkType = "";
    window.execOtherOpt = "";
    window.sshCheck = false;
    // check ssh key
    profileData = await getJobData("job");
    if (profileData) {
        if (profileData[0]) {
            if (profileData[0].ssh_id) {
                if (profileData[0].ssh_id != "0") {
                    window.sshCheck = true;
                }
            }
        }
    }
    var manualRunCheck = "false";
    if (window["manualRun"]) {
        if (window["manualRun"] == "true") {
            manualRunCheck = "true";
        }
    }
    //sshCheck should be true or manualRunModal should be open to initiate run with runProPipeCall
    if (window.sshCheck || manualRunCheck == "true") {
        if (manualRunCheck != "true") {
            $runscope.beforeunload = "Please wait for the submission.";
            displayButton("connectingProPipe");
            $("#runLogArea").val("");
        }
        //create uuid for run
        $.ajax({
            url: "ajax/ajaxquery.php",
            data: {
                p: "updateRunAttemptLog",
                project_pipeline_id: project_pipeline_id,
                manualRun: manualRunCheck,
            },
            cache: false,
            type: "POST",
            success: async function(uuid) {
                updateNewRunStatus("0");
                var hostname = $("#chooseEnv").find("option:selected").attr("host");
                pathArray = getPathArray();
                //autofill for ghpcc06 cluster to mount all directories before run executed.
                if (hostname === "ghpcc06.umassrc.org") {
                    execOtherOpt = autofillMountPath(pathArray);
                }
                pathArrayL1 = getPathArrayL1(pathArray);
                //Autofill runOptions of singularity and docker image
                window["imageRunOpt"] = autofillMountPathImage(pathArrayL1);
                // Call the callback
                var runAfterSave = async function() {
                    await runProPipeCall(keepCheckType, uuid);
                };
                await saveRun(runAfterSave, true);
            },
            error: function(jqXHR, exception) {
                toastr.error("Error occured.");
            },
        });
    } else {
        $("#manualRunModal").modal("show");
    }
}

//click on run button (callback function)
async function runProPipeCall(checkType, uuid) {
    console.log("runProPipeCall");
    nxf_runmode = true;
    var nextTextRaw = createNextflowFile("run", uuid);
    nxf_runmode = false;
    var nextText = encodeURIComponent(nextTextRaw);
    var proVarObj = encodeURIComponent(JSON.stringify(window["processVarObj"]));
    var imageRunOpt = window["imageRunOpt"]; //creates dependency
    var delIntermediate = "";
    var profileTypeId = $("#chooseEnv").find(":selected").val(); //local-32
    var patt = /(.*)-(.*)/;
    var proType = profileTypeId.replace(patt, "$1");
    var proId = profileTypeId.replace(patt, "$2");
    proTypeWindow = proType;
    proIdWindow = proId;
    var [allProSett, profileData] = await getJobData("both");
    var executor_job = profileData[0].executor_job;
    var executor = profileData[0].executor;
    var manualRunCheck = "false";
    if (window["manualRun"]) {
        if (window["manualRun"] == "true") {
            manualRunCheck = "true";
            window["manualRun"] = "false";
        }
    }
    //save nextflow text as nextflow.nf and start job
    serverLog = "";
    $.ajax({
        url: "ajax/ajaxquery.php",
        data: {
            p: "saveRun",
            nextText: nextText,
            proVarObj: proVarObj,
            project_pipeline_id: project_pipeline_id,
            runType: checkType,
            manualRun: manualRunCheck,
            uuid: uuid,
        },
        cache: false,
        type: "POST",
        success: async function(serverLogGet) {
            $runscope.beforeunload = "";
            updateNewRunStatus("0");
            await fillRunVerOpt("#runVerLog");
            await updateRunVerNavBar();
            $("#refreshVerReport").trigger("click");
            if (manualRunCheck == "true") {
                if (serverLogGet) {
                    if (serverLogGet["manualRunCmd"]) {
                        $("#manualRunCmd").val(serverLogGet["manualRunCmd"]);
                        hideLoadingDiv("manuaRunPanel");
                    }
                }
            }
            $('.nav-tabs a[href="#logTab"]').tab("show");
            readNextflowLogTimer(proType, proId, "default");
        },
        error: function(jqXHR, exception) {
            $runscope.beforeunload = "";
            toastr.error("Error occured.");
        },
    });
}

//#########read nextflow log file for status  ################################################
function readNextflowLogTimer(proType, proId, type) {
    //to trigger fast loading for new page reload
    if (type === "reload") {
        setTimeout(async function() {
            await readNextLog(proType, proId, "no_reload");
        }, 3500);
    }
    interval_readNextlog = setInterval(async function() {
        await readNextLog(proType, proId, "no_reload");
    }, 7000);
    interval_readPubWeb = setInterval(function() {
        readPubWeb(proType, proId, "no_reload");
    }, 60000);
    interval_getWorkDirSpace = setInterval(function() {
        updateDiskSpace();
    }, 600000);
}

autoScrollLog = true;
$(document).on("click", "#runLogArea", function() {
    autoScrollLog = false;
});

function autoScrollLogArea() {
    if (autoScrollLog) {
        if (document.getElementById("runLogArea")) {
            document.getElementById("runLogArea").scrollTop =
                document.getElementById("runLogArea").scrollHeight;
        }
    }
}

$('a[href="#logTab"]').on("shown.bs.tab", function(e) {
    $("#runVerLog").trigger("change");
    autoScrollLogArea();
});

$('a[href="#reportTab"]').on("shown.bs.tab", function(e) {
    $("#runVerLog").trigger("change");
});
$('a[href="#configTab"]').on("shown.bs.tab", function(e) {
    $("#runVerLog").trigger("change");
});
$('a[href="#advancedTab"]').on("shown.bs.tab", function(e) {
    $("#runVerLog").trigger("change");
});

window.saveNextLog = false;

function callAsyncSaveNextLog(data) {
    getValuesAsync(data, function(d) {
        if (d == "logNotFound") {
            window.saveNextLog = "logNotFound";
        } else if (d == "nextflow log saved") {
            window.saveNextLog = true;
        } else if (d == "pubweb is not defined") {
            if (typeof interval_readPubWeb !== "undefined") {
                clearInterval(interval_readPubWeb);
            }
        }
    });
}

function readPubWeb(proType, proId, type) {
    console.log("savePubWeb");
    // save pubWeb files
    callAsyncSaveNextLog({
        p: "savePubWeb",
        project_pipeline_id: project_pipeline_id,
        profileType: proType,
        profileId: proId,
        pipeline_id: pipeline_id,
    });
}

function saveNexLg(proType, proId) {
    callAsyncSaveNextLog({
        p: "saveNextflowLog",
        project_pipeline_id: project_pipeline_id,
        profileType: proType,
        profileId: proId,
    });
    //update log navbar after saving files
    setTimeout(async function() {
        await updateRunVerNavBar();
    }, 2500);
}

function clearIntPubWeb(proType, proId) {
    if (typeof interval_readPubWeb !== "undefined") {
        clearInterval(interval_readPubWeb);
    }
    //last save call after run completed
    setTimeout(function() {
        readPubWeb(proType, proId, "no_reload");
    }, 4000);
}
//

function clearIntNextLog(proType, proId) {
    if (typeof interval_readNextlog !== "undefined") {
        clearInterval(interval_readNextlog);
    }
    //last save call after run completed
    setTimeout(function() {
        saveNexLg(proType, proId);
    }, 5000);
}
// type= reload for reload the page
async function readNextLog(proType, proId, type) {
    var projectpipelineOwn = await $runscope.checkProjectPipelineOwn();
    if (projectpipelineOwn === "1") {
        var updateProPipeStatus = await doAjax({
            p: "updateProPipeStatus",
            project_pipeline_id: project_pipeline_id,
        });
        window.serverLog = "";
        window.nextflowLog = "";
        window.runStatus = "";
        if (updateProPipeStatus) {
            window.serverLog = updateProPipeStatus.serverLog;
            window.nextflowLog = updateProPipeStatus.nextflowLog;
            window.runStatus = updateProPipeStatus.runStatus;
        }
        // Available Run_status States: Terminated,NextSuc,Error,NextErr,NextRun, Waiting,init, Aborted
        // if runStatus equal to  Terminated, NextSuc, Error,NextErr, it means run already stopped. Show the status based on these status.
        if (
            runStatus === "Terminated" ||
            runStatus === "NextSuc" ||
            runStatus === "Error" ||
            runStatus === "NextErr" ||
            runStatus === "Manual"
        ) {
            window["countFailRead"] = 0;
            if (type !== "reload") {
                clearIntNextLog(proType, proId);
                clearIntPubWeb(proType, proId);
            }
            if (runStatus === "NextSuc") {
                displayButton("completeProPipe");
            } else if (runStatus === "Error" || runStatus === "NextErr") {
                displayButton("errorProPipe");
            } else if (runStatus === "Terminated") {
                displayButton("terminatedProPipe");
            } else if (runStatus === "Manual") {
                displayButton("manualProPipe");
                if (window.serverLog) {
                    if (window["serverLog"].match(/RUN COMMAND:/)) {
                        var serverlogRows = window["serverLog"].split("\n");
                        if (serverlogRows[1]) {
                            $("#manualRunCmd").val(serverlogRows[1]);
                        }
                    }
                }
            }
        }
        // when run hasn't finished yet and page reloads then show connecting button
        else if (
            type == "reload" ||
            window.saveNextLog === false ||
            window.saveNextLog === undefined
        ) {
            window["countFailRead"] = 0;
            displayButton("connectingProPipe");
            if (type === "reload") {
                readNextflowLogTimer(proType, proId, type);
            }
        }
        // when run hasn't finished yet and connection is down
        else if (
            window.saveNextLog == "logNotFound" &&
            runStatus !== "Waiting" &&
            runStatus !== "init"
        ) {
            if (window["countFailRead"] > 3) {
                displayButton("abortedProPipe");
                //log file might be deleted or couldn't read the log file
                var setStatus = await doAjax({
                    p: "updateRunStatus",
                    run_status: "Aborted",
                    project_pipeline_id: project_pipeline_id,
                });
                if (nextflowLog !== null && nextflowLog !== undefined) {
                    nextflowLog += "\nConnection is lost.";
                } else {
                    serverLog += "\nConnection is lost.";
                }
            } else {
                window["countFailRead"]++;
            }
        }
        // otherwise parse nextflow file to get status
        else if (
            runStatus === "Waiting" ||
            runStatus === "init" ||
            runStatus === "NextRun"
        ) {
            window["countFailRead"] = 0;
            if (runStatus === "Waiting" || runStatus === "init") {
                displayButton("waitingProPipe");
            } else if (runStatus === "NextRun") {
                displayButton("runningProPipe");
            }
        }

        var lastrun = $("option:selected", "#runVerLog").attr("lastrun");
        if (lastrun) {
            $("#runLogArea").val(serverLog + "\n" + nextflowLog);
            autoScrollLogArea();
        }

        setTimeout(function() {
            saveNexLg(proType, proId);
        }, 8000);
    }
}

function showOutputPath() {
    var outTableRow = $("#outputsTable > tbody > >:last-child").find("span");
    var output_dir = $runscope.getPubVal("work");
    //add slash if outputdir not ends with slash
    if (output_dir && output_dir.substr(-1) !== "/") {
        output_dir = output_dir + "/";
    }
    for (var i = 0; i < outTableRow.length; i++) {
        var fname = $(outTableRow[i]).attr("fname");
        $(outTableRow[i]).text(output_dir + fname);
    }
}

function addOutFileDb() {
    var rowIdAll = $("#outputsTable > tbody").find("tr");
    for (var i = 0; i < rowIdAll.length; i++) {
        var data = [];
        var rowID = $(rowIdAll[i]).attr("id");
        var outTableRow = $("#" + rowID + " >:last-child").find("span");
        var filePath = $(outTableRow[0]).text();
        //	          var gNumParam = rowID.split("Ta-")[1];
        //	          var given_name = $("#input-PName-" + gNumParam).attr("name"); //input-PName-3
        //	          var qualifier = $('#' + rowID + ' > :nth-child(4)').text(); //input-PName-3
        //	          data.push({ name: "id", value: "" });
        //	          data.push({ name: "name", value: filePath });
        //	          data.push({ name: "p", value: "saveInput" });
        //insert into input table
        //	          var inputGet = await doAjax(data);
        //	          if (inputGet) {
        //	              var input_id = inputGet.id;
        //	          }
        //insert into project_input table
        //bug: it adds NA named files after each run
        //	          var proInputGet = await doAjax({ "p": "saveProjectInput", "input_id": input_id, "project_id": project_id });
    }
}


function filterKeys(obj, filter) {
    var key,
        keys = [];
    for (key in obj) {
        if (obj.hasOwnProperty(key) && key.match(filter)) {
            keys.push(key);
        }
    }
    return keys;
}

function formToJson(rawFormData, stringify) {
    var formDataSerial = serializeDisabledArray(rawFormData);
    var formDataArr = {};
    $.each(formDataSerial, function(el) {
        formDataArr[formDataSerial[el].name] = $.trim(formDataSerial[el].value);
    });
    if (stringify && stringify === "stringify") {
        return encodeURIComponent(JSON.stringify(formDataArr));
    } else {
        return formDataArr;
    }
}

function saveSpreadsheetProcessOpt(processOptEach, handsontableID) {
    if (handsontableID && $runscope.handsontable[handsontableID]) {
        const table = $runscope.handsontable[handsontableID]
        let rawdata = table.getData()
            // filter rows that are all empty
        rawdata = rawdata.filter(function(item) {
            let ret = false;
            for (var i = 0; i < rawdata.length; i++) {
                if (item[i]) { ret = true; }
            }
            return ret;
        });
        let transpose = (matrix) => {
            if (!matrix) return [""];
            if (matrix.length === 0) return [""];
            return matrix[0].map((col, i) => matrix.map(row => row[i]));
        }
        rotatedData = transpose(rawdata);
        let colHeaders = table.getColHeader();
        for (var i = 0; i < colHeaders.length; i++) {
            if (rotatedData[i] === undefined || rotatedData[i] === null) {
                processOptEach[colHeaders[i]] = ""
            } else {
                processOptEach[colHeaders[i]] = rotatedData[i]
            }

        }
    }
    console.log(processOptEach)
    return processOptEach
}

function loadSpreadsheetProcessOpt(processOptEach, handsontableID) {
    let loadCheck = $(`#${handsontableID}`).attr("processOptLoaded")
        // load table only once
    if (handsontableID && $runscope.handsontable[handsontableID] && !loadCheck) {
        $(`#${handsontableID}`).attr("processOptLoaded", "true")
        const table = $runscope.handsontable[handsontableID]
        let colHeaders = table.getColHeader();
        let rawdata = []
        for (var i = 0; i < colHeaders.length; i++) {
            if (processOptEach[colHeaders[i]]) {
                rawdata.push(processOptEach[colHeaders[i]])
            }
        }

        let transpose = (matrix) => {
            return matrix[0].map((col, i) => matrix.map(row => row[i]));
        }
        if (rawdata[0]) {
            rawdata = transpose(rawdata);
        }

        let finaldata = addEmptyRows(rawdata, 100, colHeaders)
        table.loadData(finaldata);
    }
}

//prepare JSON to save db
function getProcessOpt() {
    var processOptAll = {};
    var proOptDiv = $("#ProcessPanel").children();
    $.each(proOptDiv, function(el) {
        var boxId = $(proOptDiv[el]).attr("id");
        var patt = /(.*)-(.*)/;
        var proGnum = boxId.replace(patt, "$2");
        var formGroup = $("#addProcessRow-" + proGnum).find(".form-group");
        var formGroupArray = formGroup.toArray();
        var processOptEach = {};
        $.each(formGroupArray, function(el) {
            var outerDivNum = null;
            var outerDiv = $(formGroupArray[el]).parent();
            var outerDivId = outerDiv.attr("id");
            //check if arrayDiv is defined and visible
            if (outerDiv.css("display") === "none") {
                return true;
            }
            //check if arrayDiv is defined whose id ends with ind<x>
            if (outerDivId) {
                if (outerDivId.match(/(.*)_ind(.*)/)) {
                    outerDivNum = outerDivId.match(/(.*)_ind(.*)$/)[2];
                }
            }
            var labelDiv = $(formGroupArray[el]).find("label").next()[0];
            var inputDiv = $(formGroupArray[el]).find("input,textarea,select,div.dnext_spreadsheet")[0];
            var inputDivType = $(inputDiv).attr("type");
            if (labelDiv && inputDiv) {
                // variable name stored at label
                var label = $.trim($(labelDiv).text());
                // if array exist then add _ind + copy Number
                if (outerDivNum) {
                    label = label + "_ind" + outerDivNum;
                }
                if (inputDivType === "spreadsheet") {
                    var handsontableID = $(inputDiv).attr("id")
                    processOptEach = saveSpreadsheetProcessOpt(processOptEach, handsontableID)
                } else {
                    //userInput stored at inputDiv. If type of the input is checkbox different method is use to learn whether it is checked
                    if (inputDivType === "checkbox") {
                        var input = $(inputDiv).is(":checked").toString();
                    } else {
                        var input = $.trim($(inputDiv).val());
                    }
                    processOptEach[label] = input;
                }

            }
        });
        processOptAll[proGnum] = processOptEach;
    });
    return encodeURIComponent(JSON.stringify(processOptAll));
}

function fillEachProcessOpt(eachProcessOpt, label, inputDiv, inputDivType) {
    if (inputDivType === "spreadsheet") {
        let tableID = $(inputDiv).attr("id")
        loadSpreadsheetProcessOpt(eachProcessOpt, tableID)
    } else {
        if (eachProcessOpt[label] != null && eachProcessOpt[label] != undefined) {
            if (inputDivType === "checkbox") {
                updateCheckBox(inputDiv, eachProcessOpt[label]);
                $(inputDiv).trigger("change");
            } else if (inputDivType === "dropdown") {
                $(inputDiv).val(eachProcessOpt[label]);
                $(inputDiv).trigger("change");
            } else {
                $(inputDiv).val(eachProcessOpt[label]);
            }
        }
    }

}

// add array forms before fill the data
function addArrForms(allProcessOpt) {
    $.each(allProcessOpt, function(proGnum) {
        var eachProcessOpt = allProcessOpt[proGnum];
        // find all form-groups for each process by proGnum
        var formGroupArray = $("#addProcessRow-" + proGnum)
            .find(".form-group")
            .toArray();
        $.each(formGroupArray, function(elem) {
            var labelDiv = $(formGroupArray[elem]).find("label").next()[0];
            var inputDiv = $(formGroupArray[elem]).find("input,textarea,select")[0];
            var inputDivType = $(inputDiv).attr("type");
            var outerDiv = $(formGroupArray[elem]).parent();
            var outerDivId = outerDiv.attr("id");
            //check if hidden arrayDiv is exist
            if (outerDivId) {
                if (outerDivId.match(/(.*)_ind(.*)/)) {
                    var outerDivVarname = outerDivId.match(/(.*)_ind(.*)$/)[1];
                    var labelArr = $.trim($(labelDiv).text()); //eg. var1
                    //check if arrayDiv is defined whose id ends with ind<x>
                    var re = new RegExp(labelArr + "_ind" + "(.*)$", "g");
                    var filt_keys = filterKeys(eachProcessOpt, re);
                    var numAddedForm = parseInt(filt_keys.length);
                    //trigger click on add button if numAddedForm>0 and added div is not exist
                    if (numAddedForm && numAddedForm != 0) {
                        if (!$(
                                "#addProcessRow-" +
                                proGnum +
                                " > #" +
                                outerDivVarname +
                                "_ind" +
                                numAddedForm
                            )[0]) {
                            var addButton = $(
                                outerDiv
                                .next()
                                .find(
                                    "button[id*='Add'][defval*='" + outerDivVarname + "']"
                                )[0]
                            );
                            for (var i = 0; i < numAddedForm; i++) {
                                addButton.trigger("click");
                            }
                        }
                    }
                }
            }
        });
    });
}

// add array forms before fill the data
function delArrForms(allProcessOpt) {
    $.each(allProcessOpt, function(proGnum) {
        var eachProcessOpt = allProcessOpt[proGnum];
        // find all form-groups for each process by proGnum
        var formGroupArray = $("#addProcessRow-" + proGnum)
            .find(".form-group")
            .toArray();
        $.each(formGroupArray, function(elem) {
            var outerDiv = $(formGroupArray[elem]).parent();
            var outerDivId = outerDiv.attr("id");
            //check if visible arrayDiv is exist
            if (outerDivId) {
                if (outerDivId.match(/(.*)_ind(.*)/)) {
                    var outerDivIndId = outerDivId.match(/(.*)_ind(.*)$/)[2];
                    if (outerDivIndId > 0) {
                        if ($("#addProcessRow-" + proGnum + " > #" + outerDivId).length) {
                            $("#addProcessRow-" + proGnum + " > #" + outerDivId).remove();
                        }
                    }
                }
            }
        });
    });
}

//get JSON from db and fill the process options
function loadProcessOpt(allProcessOpt) {
    if (allProcessOpt) {
        allProcessOpt = JSON.parse(allProcessOpt);
        // clean array rows before adding new ones (consider added rows by default value["a","b"])
        delArrForms(allProcessOpt);
        // add array forms before fill the data
        addArrForms(allProcessOpt);
        $.each(allProcessOpt, function(el) {
            var proGnum = el;
            var eachProcessOpt = allProcessOpt[el];
            // find all form-groups for each process by proGnum
            var formGroup = $("#addProcessRow-" + proGnum).find(".form-group");
            var formGroupArray = formGroup.toArray();
            $.each(formGroupArray, function(elem) {
                var labelDiv = $(formGroupArray[elem]).find("label").next()[0];
                var inputDiv = $(formGroupArray[elem]).find("input,textarea,select,div.dnext_spreadsheet")[0];
                var inputDivType = $(inputDiv).attr("type");
                var outerDiv = $(formGroupArray[elem]).parent();
                var outerDivId = outerDiv.attr("id");
                //check if added arrayDiv is exist which are visible
                if (outerDivId) {
                    if (outerDivId.match(/(.*)_ind(.*)/)) {
                        if ($(formGroupArray[elem]).parent().css("display") != "none") {
                            var outerDivIndNo = outerDivId.match(/(.*)_ind(.*)$/)[2];
                            var labelArr = $.trim($(labelDiv).text()); //eg. var1
                            //check if labelArr_indx is defined in eachProcessOpt
                            var re = new RegExp(labelArr + "_ind" + "(.*)$", "g");
                            var filt_keys = filterKeys(eachProcessOpt, re);
                            //fill according to sequence in filt_keys array
                            var newlabelArr = filt_keys[parseInt(outerDivIndNo) - 1];
                            if (newlabelArr) {
                                fillEachProcessOpt(
                                    eachProcessOpt,
                                    newlabelArr,
                                    inputDiv,
                                    inputDivType
                                );
                            }
                        }
                        return true;
                    }
                }
                // fill each form if label exist in eachProcessOpt object
                if (labelDiv && inputDiv) {
                    var label = $.trim($(labelDiv).text());
                    fillEachProcessOpt(eachProcessOpt, label, inputDiv, inputDivType);
                }
            });
        });
    }
}

function formToJsonEachPro() {
    var checkedBox = $("#processTable").find("input:checked");
    var checkedBoxArray = checkedBox.toArray();
    var formDataArr = {};
    $.each(checkedBoxArray, function(el) {
        var boxId = $(checkedBoxArray[el]).attr("id");
        var patt = /(.*)-(.*)/;
        var proGnum = boxId.replace(patt, "$2");
        var selectedRow = $("#procGnum-" + proGnum).find("input");
        var selectedRowJson = formToJson(selectedRow, "stringfy");
        formDataArr["procGnum-" + proGnum] = selectedRowJson;
    });
    return encodeURIComponent(JSON.stringify(formDataArr));
}

async function checkNewRunStatus() {
    var pipeData = await $runscope.getAjaxData("getProjectPipelines", {
        p: "getProjectPipelines",
        id: project_pipeline_id,
    });
    var newrunCheck = 0;
    //if new_run is selected then unselect dropdown
    if (pipeData[0]) {
        if (pipeData[0].new_run === "1") {
            newrunCheck = 1;
        }
    }
    return newrunCheck;
}

function updateNewRunStatus(stat) {
    if (pipeData[0]) {
        pipeData[0].new_run = stat;
    }
    if ($runscope.getProjectPipelines[0]) {
        $runscope.getProjectPipelines[0].new_run = stat;
    }
}

function createNewRunFunc(newRunExist) {
    var run_log_uuid = $("#runVerLog").val();
    var lastrun = $("option:selected", "#runVerLog").attr("lastrun") || false;
    //load from projectpipelinedata
    if (!newRunExist && lastrun) {
        //if run_log_uuid is the last run
        $.ajax({
            url: "ajax/ajaxquery.php",
            data: {
                p: "updateProjectPipelineNewRun",
                newrun: 1,
                project_pipeline_id: project_pipeline_id,
            },
            cache: false,
            type: "POST",
            success: async function(data) {
                if (data) {
                    updateNewRunStatus("1");
                    await fillRunVerOpt("#runVerLog");
                    $('.nav-tabs a[href="#configTab"]').tab("show");
                    toastr.info("New run is ready.");
                } else {
                    toastr.error("Error occured.");
                }
            },
            error: function(jqXHR, exception) {
                toastr.error("Error occured.");
            },
        });
    } else {
        $.ajax({
            url: "ajax/ajaxquery.php",
            data: {
                p: "updateProjectPipelineWithOldRun",
                project_pipeline_id: project_pipeline_id,
                run_log_uuid: run_log_uuid,
            },
            cache: false,
            type: "POST",
            success: async function(data) {
                if (data) {
                    //refresh existing pipeData
                    pipeData = data;
                    if ($runscope.getProjectPipelines) {
                        $runscope.getProjectPipelines = data;
                    }
                    await fillRunVerOpt("#runVerLog");
                    $('.nav-tabs a[href="#configTab"]').tab("show");
                    toastr.info("New run is ready.");
                } else {
                    toastr.error("Old run couldn't be loaded.");
                }
            },
            error: function(jqXHR, exception) {
                toastr.error("Error occured.");
            },
        });
    }
}

async function saveRunIcon() {
    //check if lastrun is running, then show warning
    if (runStatus == "NextRun" || runStatus == "Waiting" || runStatus == "init") {
        await saveRun(false, true);
    } else {
        var newRunExist = await checkNewRunStatus();
        var newRun = $("option:selected", "#runVerLog").attr("newrun") || false;
        if (newRunExist && newRun) {
            await saveRun(false, true);
            await checkReadytoRun();
        } else if (!newRunExist) {
            var sucFunc = function() {
                getValuesAsync({ p: "checkNewRunParam", id: project_pipeline_id },
                    function(s) {
                        if (s == "1") {
                            $('.nav-tabs a[href="#configTab"]').tab("show");
                            createNewRunFunc(newRunExist);
                        }
                    }
                );
            };
            await saveRun(sucFunc, true);
            await checkReadytoRun();
        }
    }
}

function saveRunSum() {
    var runSummary = encodeURIComponent($("#runSum").val());
    var uuid = $("#runVerLog").val();
    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: {
            id: project_pipeline_id,
            p: "saveRunSummary",
            uuid: uuid,
            summary: runSummary,
        },
        async: true,
        success: function(s) {
            disableRunSum();
        },
    });
}

async function saveRun(sucFunc, showToastr) {
    var data = [];
    var runSummary = encodeURIComponent($("#runSum").val());
    var run_name = $("#run-title").val();
    run_name = $.trim(run_name);
    var newpipelineID = pipeline_id;
    var onload = ""; //trigger onload function after loading run page
    if (dupliProPipe === false) {
        project_pipeline_id = $("#pipeline-title").attr("projectpipelineid");
    } else if (dupliProPipe === true) {
        var numOfErr;
        var target_group_id;
        var target_perms;
        var new_project_id = getTargetProject();
        [numOfErr, target_group_id, target_perms] =
        await validateMoveCopyRun(new_project_id);
        if (!numOfErr) {
            //if project belong to other user's project than change its value
            $("#groupSelRun").val(target_group_id);
            $("#permsRun").val(target_perms);

            old_project_pipeline_id = project_pipeline_id;
            project_pipeline_id = "";
            project_id = new_project_id;
            run_name = run_name + "-copy";
            if (confirmNewRev) {
                newpipelineID = $("#pipelineRevs").val();
                if (newpipelineID != pipeline_id) {
                    onload = "refreshEnv";
                }
            }
        } else {
            confirmNewRev = false;
            dupliProPipe = false;
            return;
        }
    }
    //check cloud keys
    var amazon_cre_id = "";
    var google_cre_id = "";
    if ($("#mRunAmzKeyDiv").css("display") === "inline") {
        amazon_cre_id = $("#mRunAmzKey").val();
    }
    google_cre_id = $("#mRunGoogKey").val();
    var output_dir = $runscope.getPubVal("work");
    var publish_dir = $runscope.getPubVal("publish");
    var publish_dir_check = $("#publish_dir_check").is(":checked").toString();
    var profile = $("#chooseEnv").val();
    var perms = $("#permsRun").val();
    var interdel = $("#intermeDel").is(":checked").toString();
    var groupSel = $("#groupSelRun").val();
    var cmd = encodeURIComponent($runscope.getPubVal("runcmd"));
    var exec_each = $("#exec_each").is(":checked").toString();
    var exec_all = $("#exec_all").is(":checked").toString();
    var exec_all_settingsRaw = $("#allProcessSettTable").find("input");
    var exec_all_settings = formToJson(exec_all_settingsRaw, "stringify");
    var exec_each_settings = formToJsonEachPro();
    var docker_check = $("#docker_check").is(":checked").toString();
    var docker_img = $("#docker_img").val();
    var docker_opt = $("#docker_opt").val();
    var singu_check = $("#singu_check").is(":checked").toString();
    var singu_save = $("#singu_save").is(":checked").toString();
    var singu_img = $("#singu_img").val();
    var singu_opt = $("#singu_opt").val();
    singu_img = $.trim(singu_img);
    singu_opt = $.trim(singu_opt);
    docker_img = $.trim(docker_img);
    docker_opt = $.trim(docker_opt);
    var withReport = $("#withReport").is(":checked").toString();
    var withTrace = $("#withTrace").is(":checked").toString();
    var withTimeline = $("#withTimeline").is(":checked").toString();
    var withDag = $("#withDag").is(":checked").toString();
    var process_opt = getProcessOpt();
    var release_date = $("#releaseVal").attr("date");
    var cron_check = $("#cron_check").is(":checked").toString();
    var cron_prefix = encodeURIComponent($("#cron_prefix").val());
    var cron_min = $("#cron_min").val();
    var cron_hour = $("#cron_hour").val();
    var cron_day = $("#cron_day").val();
    var cron_week = $("#cron_week").val();
    var cron_month = $("#cron_month").val();
    var cron_first = $("#cron_first").val();
    var notif_check = $("#notif_check").is(":checked").toString();
    var email_notif = $("#email_notif").is(":checked").toString();
    var notif_email_list = encodeURIComponent($("#notif_email_list").val());

    if (run_name && project_id && newpipelineID) {
        data.push({ name: "id", value: project_pipeline_id });
        data.push({ name: "name", value: run_name });
        data.push({ name: "project_id", value: project_id });
        data.push({ name: "pipeline_id", value: newpipelineID });
        data.push({ name: "amazon_cre_id", value: amazon_cre_id });
        data.push({ name: "google_cre_id", value: google_cre_id });
        data.push({ name: "summary", value: runSummary });
        data.push({ name: "output_dir", value: output_dir });
        data.push({ name: "publish_dir", value: publish_dir });
        data.push({ name: "publish_dir_check", value: publish_dir_check });
        data.push({ name: "profile", value: profile });
        data.push({ name: "perms", value: perms });
        data.push({ name: "interdel", value: interdel });
        data.push({ name: "cmd", value: cmd });
        data.push({ name: "group_id", value: groupSel });
        data.push({ name: "exec_each", value: exec_each });
        data.push({ name: "exec_all", value: exec_all });
        data.push({ name: "exec_all_settings", value: exec_all_settings });
        data.push({ name: "exec_each_settings", value: exec_each_settings });
        data.push({ name: "docker_check", value: docker_check });
        data.push({ name: "docker_img", value: docker_img });
        data.push({ name: "docker_opt", value: docker_opt });
        data.push({ name: "singu_check", value: singu_check });
        data.push({ name: "singu_save", value: singu_save });
        data.push({ name: "singu_img", value: singu_img });
        data.push({ name: "singu_opt", value: singu_opt });
        data.push({ name: "withReport", value: withReport });
        data.push({ name: "withTrace", value: withTrace });
        data.push({ name: "withTimeline", value: withTimeline });
        data.push({ name: "withDag", value: withDag });
        data.push({ name: "process_opt", value: process_opt });
        data.push({ name: "onload", value: onload });
        data.push({ name: "release_date", value: release_date });
        data.push({ name: "cron_check", value: cron_check });
        data.push({ name: "cron_prefix", value: cron_prefix });
        data.push({ name: "cron_min", value: cron_min });
        data.push({ name: "cron_hour", value: cron_hour });
        data.push({ name: "cron_day", value: cron_day });
        data.push({ name: "cron_week", value: cron_week });
        data.push({ name: "cron_month", value: cron_month });
        data.push({ name: "cron_first", value: cron_first });
        data.push({ name: "notif_check", value: notif_check });
        data.push({ name: "email_notif", value: email_notif });
        data.push({ name: "notif_email_list", value: notif_email_list });
        data.push({ name: "p", value: "saveProjectPipeline" });
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: data,
            async: true,
            success: async function(s) {
                if (showToastr) {
                    toastr.info("All changes are saved.");
                }
                if (typeof sucFunc === "function") {
                    await sucFunc();
                }
                if (dupliProPipe === false) {
                    if (s) {
                        if ($.isArray(s)) {
                            var infoModalText = s.join("</br>");
                            if (infoModalText) {
                                showInfoModal(
                                    "#infoModal",
                                    "#infoModalText",
                                    "Permission of the pipeline needs to be updated in order to share this run. However, it couldn't be changed because of the following reason:</br></br>" +
                                    infoModalText
                                );
                            }
                        }
                    }
                    await refreshCreatorData(project_pipeline_id);
                    updateSideBarProPipe("", project_pipeline_id, run_name, "edit");
                } else if (dupliProPipe === true) {
                    var duplicateProPipeIn = await doAjax({
                        p: "duplicateProjectPipelineInput",
                        new_id: s.id,
                        old_id: old_project_pipeline_id,
                    });
                    dupliProPipe = false;
                    if (duplicateProPipeIn) {
                        setTimeout(function() {
                            window.location.replace("index.php?np=3&id=" + s.id);
                        }, 0);
                    }
                }
            },
            error: function(errorThrown) {
                toastr.error("Changes couldn't be saved.");
            },
        });
    } else {
        toastr.error("Changes couldn't be saved.");
    }
}

async function getProfileData(proType, proId) {
    if (proType === "cluster") {
        var profileData = await doAjax({
            p: "getProfileCluster",
            id: proId,
            type: "run",
        });
    } else if (proType === "amazon" || proType === "google") {
        var profileData = await doAjax({
            p: "getProfileCloud",
            cloud: proType,
            id: proId,
            type: "run",
        });
    }
    return profileData;
}

function getProfileDataSync(proType, proId) {
    if (proType === "cluster") {
        var profileData = getValues({
            p: "getProfileCluster",
            id: proId,
            type: "run",
        });
    } else if (proType === "amazon" || proType === "google") {
        var profileData = getValues({
            p: "getProfileCloud",
            cloud: proType,
            id: proId,
            type: "run",
        });
    }
    return profileData;
}

function updateSideBarProPipe(
    project_id,
    project_pipeline_id,
    project_pipeline_name,
    type
) {
    if (type === "edit") {
        $("#propipe-" + project_pipeline_id).html(
            '<i class="fa fa-angle-double-right"></i>' +
            truncateName(project_pipeline_name, "sidebarMenu")
        );
    }
}

async function getRunStatus(project_pipeline_id) {
    var runStatusGet = await doAjax({
        p: "getRunStatus",
        project_pipeline_id: project_pipeline_id,
    });
    if (runStatusGet[0]) {
        runStatus = runStatusGet[0].run_status;
    } else {
        runStatus = "";
    }
    return runStatus;
}
dupliProPipe = false;

async function checkNewRevision() {
    //getPipelineRevision will retrive pipeline revisions that user allows to use
    var checkNewRew = await doAjax({
        p: "getPipelineRevision",
        pipeline_id: pipeline_id,
    });
    //fill dropdown
    refreshPipeRevisions("#pipelineRevs", checkNewRew);
    var askNewRev = false;
    var highestRev = pipeData[0].rev_id;
    var highestRevPipeId = pipeline_id;
    if (checkNewRew) {
        $.each(checkNewRew, function(el) {
            if (checkNewRew[el].rev_id > highestRev) {
                askNewRev = true;
                highestRev = checkNewRew[el].rev_id;
                highestRevPipeId = checkNewRew[el].id;
            }
        });
    }
    return askNewRev;
}

//type =="selectproject" or "default"
function refreshProjectDatatable(type) {
    if (!$.fn.DataTable.isDataTable("#projecttable")) {
        var projectTable = $("#projecttable").DataTable({
            ajax: {
                url: "ajax/ajaxquery.php",
                data: { p: "getUserProjects" },
                dataSrc: "",
            },
            columns: [{
                    data: "id",
                    checkboxes: {
                        targets: 0,
                        selectRow: true,
                    },
                },
                {
                    data: "name",
                },
                {
                    data: "username",
                },
                {
                    data: "date_modified",
                },
            ],
            select: {
                style: "single",
            },
            order: [
                [3, "desc"]
            ],
        });
        var sharedProjectTable = $("#sharedProjectTable").DataTable({
            ajax: {
                url: "ajax/ajaxquery.php",
                data: { p: "getSharedProjects" },
                dataSrc: "",
            },
            columns: [{
                    data: "id",
                    checkboxes: {
                        targets: 0,
                        selectRow: true,
                    },
                },
                {
                    data: "name",
                },
                {
                    data: "username",
                },
                {
                    data: "date_modified",
                },
            ],
            select: {
                style: "single",
            },
            order: [
                [3, "desc"]
            ],
        });
    } else {
        var projectTable = $("#projecttable").DataTable();
        var sharedProjectTable = $("#sharedProjectTable").DataTable();
        projectTable.ajax.reload(null, false);
        sharedProjectTable.ajax.reload(null, false);
    }
    projectTable.column(0).checkboxes.deselect();
    sharedProjectTable.column(0).checkboxes.deselect();
    // choose existing project
    if (type == "selectproject") {
        //        projectTable.rows( function ( idx, data, node ){
        //            if (data.id == project_id){
        //                projectTable.row(idx).select();
        //            }
        //        });
        //        sharedProjectTable.rows( function ( idx, data, node ){
        //            if (data.id == project_id){
        //                $('#sharedProjectTable').DataTable().row(idx).select();
        //            }
        //        });
    }
}

function getTargetProject() {
    var new_project_id = "";
    var rows_selected = [];
    var activeTabLi = $("#confirmDuplicate ul.nav-tabs li.active").attr("id");
    if (activeTabLi == "sharedProjectLi") {
        var sharedProjectTable = $("#sharedProjectTable").DataTable();
        rows_selected = sharedProjectTable.column(0).checkboxes.selected();
        new_project_id = rows_selected[0];
    } else if (activeTabLi == "userProjectLi") {
        var projectTable = $("#projecttable").DataTable();
        rows_selected = projectTable.column(0).checkboxes.selected();
        new_project_id = rows_selected[0];
    }
    return new_project_id;
}

function refreshPipeRevisions(id, revData) {
    $(id).empty();
    for (var i = revData.length; i--;) {
        var param = revData[i];
        if (pipeline_id == param.id) {
            var optionGroup = new Option(
                decodeHtml(
                    "Revision: " +
                    param.rev_id +
                    " " +
                    param.rev_comment +
                    " on " +
                    param.date_modified +
                    " (current rev.)"
                ),
                param.id
            );
        } else {
            var optionGroup = new Option(
                decodeHtml(
                    "Revision: " +
                    param.rev_id +
                    " " +
                    param.rev_comment +
                    " on " +
                    param.date_modified
                ),
                param.id
            );
        }
        $(id).append(optionGroup);
    }
    $(id).val(pipeline_id);
}

async function duplicateProPipe(type) {
    $('#confirmDuplicate a[href="#userProjectTab"]').trigger("click");
    dupliProPipe = false;
    confirmNewRev = false;
    if (type == "copy") {
        refreshProjectDatatable("selectproject");
        var askNewRev = await checkNewRevision();
        $("#moveRunBut").css("display", "none");
        $("#copyRunBut").css("display", "inline-block");
        $("#pipelineRevsDiv").css("display", "block");
        $("#chooseTargetProjectLabel").css("display", "block");
        if (askNewRev === true) {
            $("#confirmDuplicateText").html(
                "<b>New revision of this pipeline is available!</b> If you change your pipeline revision, we will transfer your inputs into your new run. However, you might need to re-enter your custom pipeline options."
            );
        } else {
            $("#confirmDuplicateText").text(
                "Please select target project to copy your run. If you change your pipeline revision, we will transfer your input parameters into your new run. However, you might need to re-enter your custom pipeline options."
            );
        }
        $("#confirmDuplicateTitle").text("Copy Run");
        $("#confirmDuplicate").modal("show");
    } else if (type == "move" || type == "changeproject") {
        refreshProjectDatatable("default");
        await saveRun(false, true);
        $("#copyRunBut").css("display", "none");
        $("#moveRunBut").css("display", "inline-block");
        $("#pipelineRevsDiv").css("display", "none");
        $("#chooseTargetProjectLabel").css("display", "none");
        if (type == "move") {
            $("#confirmDuplicateTitle").text("Move Run");
            $("#confirmDuplicateText").text(
                "Please select your target project to move your run."
            );
        } else if (type == "changeproject") {
            $("#confirmDuplicateTitle").text("Change Project");
            $("#confirmDuplicateText").text("Please choose your new project.");
        }
    }
    $("#confirmDuplicate").modal("show");
}

async function fillRunVerOpt(dropDownId) {
    console.log("fillRunVerOpt");
    var runLogs = await doAjax({
        p: "getRunLog",
        project_pipeline_id: project_pipeline_id,
    });
    //allow one outdated log directory
    var newRunLogs = [];
    var once = true;
    $.each(runLogs, function(el) {
        var run_log_uuid = runLogs[el].run_log_uuid;
        var project_pipeline_id = runLogs[el].project_pipeline_id;
        if (run_log_uuid) {
            newRunLogs.push(runLogs[el]);
        } else if (!run_log_uuid && once) {
            once = false;
            newRunLogs.push(runLogs[el]);
        }
    });
    var nRun = $(newRunLogs).size();
    var n = 0;
    var lastDropdownVal = "";
    var lastItem = 'lastRun="yes"';
    var newRun = 'newRun="yes"';
    var tsize = 0;
    $(dropDownId).empty();
    $.each(newRunLogs, function(el) {
        var run_log_uuid = newRunLogs[el].run_log_uuid;
        var date_created = newRunLogs[el].date_created;
        var project_pipeline_id = newRunLogs[el].project_pipeline_id;
        var sizeInKB = newRunLogs[el].size;
        if (sizeInKB) {
            tsize += parseInt(sizeInKB);
        }

        var sizeText = "";
        var runName = "";
        if (run_log_uuid || project_pipeline_id) {
            n++;
            if (sizeInKB && sizeInKB != "0") {
                var size = formatSizeUnits(sizeInKB * 1024);
                if (size) {
                    sizeText = " (" + size + ")";
                }
            }
            if (n == nRun) {
                lastItem = 'lastRun="yes"';
            } else {
                lastItem = "";
            }
            if (newRunLogs[el].name) {
                runName = decodeHtml(newRunLogs[el].name);
            } else {
                runName = "Attempt " + n;
            }

            if (run_log_uuid) {
                lastDropdownVal = run_log_uuid;
                $(dropDownId).prepend(
                    $("<option " + lastItem + "></option>")
                    .attr("ver", n)
                    .val(lastDropdownVal)
                    .html(runName + " created at " + date_created + sizeText)
                );
            } else if (project_pipeline_id) {
                lastDropdownVal = "run" + project_pipeline_id;
                $(dropDownId).prepend(
                    $("<option " + lastItem + "></option>")
                    .attr("ver", n)
                    .val(lastDropdownVal)
                    .html(runName + " created at " + date_created + sizeText)
                );
            }
        }
    });
    if (tsize) {
        var tsize = formatSizeUnits(tsize * 1024);
        if (tsize) {
            var icon =
                '<a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Total size of the runs in the DolphinNext server. It doesn\'t show the actual run size in the run environment.">' +
                tsize +
                "</a>";
            $("#runLogSize").html(icon);
        }
    }
    $(dropDownId).prepend(
        $("<option disabled></option>").val(0).html("-- Run History --")
    );
    var newrunCheck = await checkNewRunStatus();
    if (n === 0) {
        updateNewRunStatus("1");
        newrunCheck = "1";
    }
    //if new_run is selected then select first "Run History" option
    if (newrunCheck) {
        n++;
        $(dropDownId).prepend(
            $("<option " + newRun + "></option>")
            .attr("ver", n)
            .val("newrun")
            .html("New Run " + n)
        );
        $(dropDownId).val($(dropDownId + " option:first").val());
        $(dropDownId).trigger("change");
        if ($("ul#runTabDiv li.active > a")[0]) {
            var attr = $($("ul#runTabDiv li.active > a")[0]).attr("href");
            if (attr == "#reportTab" || attr == "#logTab") {
                $('.nav-tabs a[href="#configTab"]').trigger("click");
            }
        }
        // else choose second option
    } else {
        $(dropDownId).val(
            $(dropDownId + ' option[value="' + lastDropdownVal + '"]').val()
        );
        // don't allow to reload configtab unless another log is selected
        //        $(dropDownId).attr("configTabUID",lastDropdownVal);
        //        $(dropDownId).attr("prevUID",lastDropdownVal);
        $(dropDownId).trigger("change");
    }
}

async function updateRunVerNavBar() {
    var run_log_uuid = $("#runVerLog").val();
    var lastrun = $("option:selected", "#runVerLog").attr("lastrun");
    if (lastrun) {
        lastrun = 'lastrun="yes"';
        var activeTab = $("ul#logNavBar li.active > a");
        var activeID = "";
        if (activeTab[0]) {
            activeID = $(activeTab[0]).attr("href");
        }
        var fileList = await doAjax({
            p: "getFileList",
            uuid: run_log_uuid,
            path: "run",
            type: "onlyfilehidden",
        });
        var fileListAr = getObjectValues(fileList);
        fileListAr.splice($.inArray("serverlog.txt", fileListAr), 1);
        var order = [
            "log.txt",
            "timeline.html",
            "report.html",
            "dag.html",
            "trace.txt",
            ".nextflow.log",
            "initialrun/.nextflow.log",
            "initialrun/trace.txt",
            "nextflow.nf",
            "nextflow.config",
        ];
        var logContentDivAttr = [
            "SHOW_RUN_LOG",
            "SHOW_RUN_TIMELINE",
            "SHOW_RUN_REPORT",
            "SHOW_RUN_DAG",
            "SHOW_RUN_TRACE",
            "SHOW_RUN_NEXTFLOWLOG",
            "SHOW_RUN_NEXTFLOWLOG",
            "SHOW_RUN_TRACE",
            "SHOW_RUN_NEXTFLOWNF",
            "SHOW_RUN_NEXTFLOWCONFIG",
        ];

        if (fileListAr.length > 0) {
            for (var j = 0; j < fileListAr.length; j++) {
                var orderInd = order.indexOf(fileListAr[j]);
                if (orderInd > -1) {
                    if ($("#logContentDiv").attr(logContentDivAttr[orderInd]) == "true") {
                        var tabID = cleanProcessName(fileListAr[j]) + "Tab";
                        $('a[href="#' + tabID + '"]').css("display", "block");
                    }
                }
            }
        }
        if (!activeID) {
            $('.nav-tabs a[href="' + "#log_txtTab" + '"]').trigger("click");
        }
    }
}

//use array of item to fill select element
function fillArray2Select(arr, id, clean) {
    if (clean === true) {
        $(id).empty();
    }
    for (var i = 0; i < arr.length; i++) {
        var param = arr[i];
        var optionGroup = new Option(param, param);
        $(id).append(optionGroup);
    }
}

function resetPatternList() {
    fillArray2Select([], "#singleList", true);
    fillArray2Select([], "#reverseList", true);
    fillArray2Select([], "#forwardList", true);
    fillArray2Select([], "#r3List", true);
    fillArray2Select([], "#r4List", true);
}

async function viewDirButSearch(dir) {
    var amazon_cre_id = "";
    var google_cre_id = "";
    var warnUser = false;
    if (dir) {
        if (dir.match(/:\/\//)) {
            var lastChr1 = dir.slice(-1);
            var lastChr2 = dir.slice(-2);
            if (lastChr1 == "/" && lastChr2 != "//") {
                dir = dir.substring(0, dir.length - 1);
            }
        }
        console.log(dir);
        if (dir.match(/s3:/i)) {
            amazon_cre_id = $("#mRunAmzKeyS3").val();
            //            if (!amazon_cre_id) {
            //                showInfoModal(
            //                    "#infoModal",
            //                    "#infoModalText",
            //                    "Please select Amazon Keys to search files in your S3 storage."
            //                );
            //                warnUser = true;
            //            }
        } else if (dir.match(/gs:/i)) {
            google_cre_id = $("#mRunGoogKeyGS").val();
            if (!google_cre_id) {
                showInfoModal(
                    "#infoModal",
                    "#infoModalText",
                    "Please select Google Keys to search files in your Google storage."
                );
                warnUser = true;
            }
        }
        if (!warnUser) {
            var dirList = await doAjax({
                p: "getLsDir",
                dir: dir,
                profileType: proTypeWindow,
                profileId: proIdWindow,
                amazon_cre_id: amazon_cre_id,
                google_cre_id: google_cre_id,
                project_pipeline_id: project_pipeline_id,
            });
            if (dirList) {
                dirList = $.trim(dirList);
                console.log(dirList);
                var fileArr = [];
                var errorAr = [];
                if (dir.match(/s3:/i) || dir.match(/gs:/i)) {
                    var raw = dirList.split("\n");
                    for (var i = 0; i < raw.length; i++) {
                        var filePathArr = raw[i].split(" ")
                        var filePath = filePathArr.pop();
                        console.log(filePath)
                        console.log(filePathArr)
                        if (filePath) {
                            if (
                                filePath.toLowerCase() === dir.toLowerCase() ||
                                filePath.toLowerCase() === dir.toLowerCase() + "/"
                            ) {
                                console.log("skip", filePath);
                                continue;
                            }
                            if (dirList.match(/Total Objects/i) || filePath.match(/gs:/i)) {
                                // exclude aws s3 --summary info
                                if (raw[i].match(/Total Objects:/) || raw[i].match(/Total Size:/)) {
                                    console.log("skip", filePath);
                                    continue;
                                }
                                var allBlock = filePath.split("/");
                                if (filePath.substr(-1) == "/") {
                                    var lastBlock = allBlock[allBlock.length - 2] + "/";
                                } else {
                                    var lastBlock = allBlock[allBlock.length - 1];
                                }
                                fileArr.push(lastBlock);
                            } else {
                                errorAr.push(raw[i]);
                            }
                        } else {
                            errorAr.push(raw[i]);
                        }
                    }
                } else if (dir.match(/:\/\//)) {
                    fileArr = dirList.split("\n");
                    errorAr = fileArr.filter((line) => line.match(/:/));
                    fileArr = fileArr.filter((line) => !line.match(/:/));
                } else {
                    fileArr = dirList.split("\n");
                    errorAr = fileArr.filter((line) => line.match(/ls:/));
                    fileArr = fileArr.filter((line) => !line.match(/:/));
                }
                // remove space containing lines
                fileArr = fileArr.filter((l) => l.indexOf(' ') < 0);
                console.log(fileArr);
                console.log(errorAr);
                if (fileArr.length > 0) {
                    var copiedList = fileArr.slice();
                    copiedList.unshift("..");
                    fillArray2Select(copiedList, "#viewDir", true);
                    $("#viewDir").data("fileArr", fileArr);
                    $("#viewDir").data("fileDir", dir);
                    var amzKey = "";
                    var googKey = "";
                    if (dir.match(/s3:/i)) {
                        amzKey = $("#mRunAmzKeyS3").val();
                    }
                    if (dir.match(/gs:/i)) {
                        googKey = $("#mRunGoogKeyGS").val();
                    }
                    $("#viewDir").data("amzKey", amzKey);
                    $("#viewDir").data("googKey", googKey);
                    $("#collection_type").trigger("change");
                } else {
                    if (errorAr.length > 0) {
                        var errTxt = errorAr.join(" ");
                        showInfoModal("#infoModal", "#infoModalText", errTxt);
                        resetPatternList();
                    } else {
                        fillArray2Select(["Files Not Found."], "#viewDir", true);
                        resetPatternList();
                    }
                }
            } else {
                fillArray2Select(["Files Not Found."], "#viewDir", true);
                resetPatternList();
            }
        } else {
            fillArray2Select(["Files Not Found."], "#viewDir", true);
            resetPatternList();
        }
        $("#viewDir").css("display", "inline");
        $("#viewDirDiv").css("display", "block");
    } else {
        showInfoModal(
            "#infoModal",
            "#infoModalText",
            "Please enter 'File Location' to search files."
        );
    }
}

function fillFileSearchBox(item, targetDiv) {
    if ($(item)) {
        var val = $(item).text();
        if (val) {
            $("#" + targetDiv).val(val);
            $("#" + targetDiv).keyup();
        }
    }
}

var selectizeCollection = function(idArr, selVal, multipleItems, cb) {
    var maxItems = 1
    if (multipleItems) maxItems = 1000;
    var renderMenu = {
        option: function(data, escape) {
            var files = "file";
            if (data.fileCount > 1) {
                files = "files";
            }
            return (
                '<div class="option">' +
                '<span class="title"><i>' +
                escape(data.name) +
                "<i><small> (" +
                escape(data.fileCount) +
                " " +
                files +
                ")</small></i>" +
                "</i></span>" +
                "</div>"
            );
        },
        item: function(data, escape) {
            return (
                '<div class="item" data-value="' +
                escape(data.id) +
                '">' +
                escape(data.name) +
                "</div>"
            );
        },
    };

    getValuesAsync({ p: "getCollection" }, function(colData) {
        for (var i = 0; i < idArr.length; i++) {
            if ($(idArr[i])[0].selectize) {
                $(idArr[i])[0].selectize.destroy();
            }
            $(idArr[i]).selectize({
                maxItems: maxItems,
                valueField: "id",
                searchField: ["name"],
                createOnBlur: true,
                render: renderMenu,
                options: colData,
                create: function(input, callback) {
                    callback({ id: "_newItm_" + input, name: input });
                },
            });
            $(idArr[i])[0].selectize.clear();
            if (selVal) $(idArr[i])[0].selectize.setValue(selVal)
            if (cb && typeof cb === "function") cb()
        }
    });
};

async function validateMoveCopyRun(new_project_id) {
    var infoText = "";
    var target_group_id = $("#groupSelRun").val();
    var target_perms = $("#permsRun").val();
    var target_project_data = await doAjax({ p: "getProjects", id: new_project_id });
    //if owner of the project is not the user, then change its target_group_id and target_perms
    if (target_project_data[0]) {
        if (!parseInt(target_project_data[0].own) &&
            target_project_data[0].perms &&
            target_project_data[0].group_id
        ) {
            target_group_id = target_project_data[0].group_id;
            target_perms = target_project_data[0].perms;
        }
    }
    var checkPermissionUpdt = await doAjax({
        p: "checkPermUpdtRun",
        pipeline_id: pipeline_id,
        project_id: new_project_id,
        perms: target_perms,
        group_id: target_group_id,
    });
    var warnAr = $.map(checkPermissionUpdt, function(value, index) {
        return [value];
    });
    var numOfErr = warnAr.length;
    if (numOfErr > 0) {
        infoText +=
            "Permission of the project/pipeline needs to be updated in order to move/copy of the run. However, it couldn't be done because of the following reason(s): </br></br>";
        $.each(warnAr, function(element) {
            infoText += warnAr[element] + "</br>";
        });
        showInfoModal("#infoMod", "#infoModText", infoText);
    }
    return [numOfErr, target_group_id, target_perms];
}

function tooglePermsGroupsDiv(mode) {
    if (mode == "show") {
        $("#groupsDiv").css("display", "block");
        $("#permsDiv").css("display", "block");
        $("#releaseDivParent").css("display", "block");
    } else if (mode == "hide") {
        $("#groupsDiv").css("display", "none");
        $("#permsDiv").css("display", "none");
        $("#releaseDivParent").css("display", "none");
    }
}

function toogleMainIcons(mode) {
    if (mode == "show") {
        $("#saveRunIcon").css("display", "inline-block");
        $("#downPipeline").css("display", "inline-block");
        $("#delRun").css("display", "inline-block");
    } else if (mode == "hide") {
        $("#saveRunIcon").css("display", "none");
        $("#downPipeline").css("display", "none");
        $("#delRun").css("display", "none");
    }
}

function toogleStatusMode(mode) {
    if (mode == "default") {
        $("#pipeRunDiv").css("display", "block");
        $("#runStatDiv").css("display", "none");
        $("#runStatDiv").find("div").css("display", "none");
        $("#runStatNoDiv").css("display", "none");
    } else if (mode == "oneOption") {
        $("#pipeRunDiv").css("display", "none");
        $("#runStatDiv").css("display", "block");
        $("#runStatNoDiv").css("display", "none");
    } else if (mode == "noOption") {
        $("#pipeRunDiv").css("display", "none");
        $("#runStatDiv").css("display", "none");
        $("#runStatNoDiv").css("display", "block");
    }
}

// show old run_status of the logs//Available Run_status States: NextErr,NextSuc,NextRun,Error,Waiting,init,Terminated, Aborted
function runLogStatUpdate(runStatus, type) {
    if (runStatus === "NextSuc") {
        displayStatButton("runStatComplete", type);
    } else if (runStatus === "Error" || runStatus === "NextErr") {
        displayStatButton("runStatError", type);
    } else if (runStatus === "Terminated") {
        displayStatButton("runStatTerminated", type);
    } else if (runStatus === "Manual") {
        displayStatButton("runStatManual", type);
    } else if (runStatus === "NextRun") {
        displayStatButton("runStatRunning", type);
    } else if (runStatus === "Waiting" || runStatus === "init") {
        displayStatButton("runStatWaiting", type);
    }
}

function toogleRunInputs(type) {
    var bool;
    if (type == "disable") {
        bool = true;
    } else if (type == "enable") {
        bool = false;
    }
    $("#configTab :input").not(":button#inputSingleFileDownload").not(":button[show_sett_but]").prop("disabled", bool);
    $("#advancedTab :input").prop("disabled", bool);
    $(".ui-dialog :input")
        .not(".ui-dialog-buttonpane :input")
        .not(".ui-dialog-titlebar-close")
        .prop("disabled", bool);
}


$(function() {
    function reloadReportRows() {
        var run_log_uuid = $("#runVerLog").val();
        if (run_log_uuid) {
            var runUID =
                '<span style="font-size:10px; float:right; color:gray;">Run UID: ' +
                run_log_uuid +
                "</span>";
            $("#reportRowsFooter").html(runUID);
        }

        $("#reportRows").empty();
        //add 'className: "center"' to center text in columns array
        $("#reportRows").dynamicRows({
            ajax: {
                url: "ajax/ajaxquery.php",
                data: {
                    p: "getReportData",
                    uuid: run_log_uuid,
                    path: "pubweb",
                    pipeline_id: pipeline_id,
                },
            },
            columnsBody: [{
                    //file list
                    data: null,
                    colPercent: function(oData) {
                        if (oData.pubWeb == "ucsc_genome_browser") {
                            const match = oData.fileList.find(value => /feature_metadata\.tsv/.test(value));
                            if (!match) return 0
                        }
                        return 15
                    },
                    overflow: function(oData) {
                        if (oData.pubWeb == "ucsc_genome_browser") {
                            return ""
                        }
                        return "scroll"
                    },
                    fnCreatedCell: function(nTd, oData) {
                        var getIconByExtension = function(ext) {
                            var icon = "fa fa-file-text-o";
                            if (
                                ext == "tsv" ||
                                ext == "csv" ||
                                ext === "xls" ||
                                ext === "xlsx"
                            ) {
                                icon = "fa fa-table";
                            } else if (ext == "html") {
                                icon = "fa fa-file-code-o";
                            } else if (ext == "pdf") {
                                icon = "fa fa-file-pdf-o";
                            } else if (ext == "rmd") {
                                icon = "fa fa-pie-chart";
                            } else if (ext == "md") {
                                icon = "fa fa-edit";
                            } else if (
                                ext == "jpg" ||
                                ext == "jpeg" ||
                                ext == "png" ||
                                ext == "tif" ||
                                ext == "tiff" ||
                                ext == "bmp" ||
                                ext == "gif"
                            ) {
                                icon = "fa fa-file-image-o";
                            }
                            return icon;
                        };
                        var getVisTypeByExtension = function(ext) {
                            var visType = "text";
                            if (ext == "tsv") {
                                visType = "table";
                            } else if (ext == "html") {
                                visType = "html";
                            } else if (ext == "pdf") {
                                visType = "pdf";
                            } else if (ext == "rmd") {
                                visType = "rmarkdown";
                            } else if (ext == "md") {
                                visType = "markdown";
                            } else if (
                                ext == "jpg" ||
                                ext == "jpeg" ||
                                ext == "png" ||
                                ext == "tif" ||
                                ext == "tiff" ||
                                ext == "bmp" ||
                                ext == "gif"
                            ) {
                                visType = "image";
                                //will download file if not supported
                            } else if (
                                ext == "txt" ||
                                ext === "xls" ||
                                ext === "xlsx" ||
                                ext === "csv"
                            ) {
                                visType = "text";
                            }
                            return visType;
                        };

                        var run_log_uuid = $("#runVerLog").val();
                        var pubWebPath = $("#basepathinfo").attr("pubweb");
                        var visType = oData.pubWeb;
                        var icon = "fa fa-file-text-o";
                        if (visType == "table" || visType === "table-percent") {
                            icon = "fa fa-table";
                        }
                        var fileList = oData.fileList;
                        var liText = "";
                        var active = "";
                        $.each(fileList, function(el) {
                            if (fileList[el]) {
                                if (el == 0) {
                                    active = "active";
                                } else {
                                    active = "";
                                }
                                //if oData.pubWeb= "run_description" -> fill file icons and visType based on their file type
                                if (oData.pubWeb == "run_description") {
                                    var ext = getExtension(fileList[el]);
                                    icon = getIconByExtension(ext);
                                    visType = getVisTypeByExtension(ext);
                                }
                                var filepath = oData.name + "/" + fileList[el];
                                var link =
                                    pubWebPath +
                                    "/" +
                                    run_log_uuid +
                                    "/" +
                                    "pubweb" +
                                    "/" +
                                    filepath;
                                var appID = ""
                                if (oData.pubWebApp && oData.pubWebApp.app) {
                                    appID = `appId="${oData.pubWebApp.app}"`
                                }

                                var filenameCl = cleanProcessName(fileList[el]);
                                var tabID = "reportTab" + oData.id + "_" + filenameCl;
                                var fileID = oData.id + "_" + filenameCl;
                                //remove directory str, only show filename in label
                                var labelText = /[^/]*$/.exec(fileList[el])[0];
                                liText +=
                                    '<li class="' +
                                    active +
                                    '"><a  class="reportFile" data-toggle="tab" fileid="' +
                                    fileID +
                                    '" ' + appID + ' filepath="' + filepath + '" href="#' +
                                    tabID +
                                    '" visType="' +
                                    visType +
                                    '" fillsrc="' +
                                    link +
                                    '" ><i class="' +
                                    icon +
                                    '"></i>' +
                                    labelText +
                                    "</a></li>";
                            }
                        });

                        if (oData.pubWeb == "ucsc_genome_browser") {
                            const match = fileList.find(value => /feature_metadata\.tsv/.test(value));

                            if (match) {
                                var filepath = oData.name + "/" + match;
                                var filepathID = "ucsc_genes_" + filepath;
                                var filepathCl = cleanProcessName(filepathID);
                                var metadata_url = pubWebPath + "/" + run_log_uuid + "/" + "pubweb" + "/" + filepath;

                                var contentDiv = `<div style="margin-top:5px; margin-left:5px;" class="table-responsive"><table style="border:none;  width:100%;" class="table table-striped table-bordered ucsc_gb_genes"  cellspacing="0"  metadata_url="${metadata_url}" id="${filepathCl}"><thead style="white-space: nowrap; table-layout:fixed;"></thead></table></div>`;
                                $(nTd).html(contentDiv);
                                return;
                            } else {
                                // remove left sidebar if feature_metadata.tsv is not there
                                $(nTd).html("");
                                return;
                            }
                        }

                        //xxxxxxx
                        var addEveHandlerIconDiv = function(id) {
                            var addIconID = "addIcon-" + id;
                            var renameIcon = "renameIcon-" + id;
                            var deleteIcon = "deleteIcon-" + id;

                            var getFileName = function(filePath) {
                                var res = { filename: "", rest: "" };
                                var split = filePath.split("/");
                                res.filename = split[split.length - 1];
                                res.rest = split.slice(0, -1).join("/");
                                return res;
                            };

                            //return 1 if newName is found in directory.
                            var checkDuplicateFile = function(newName, fileNameObj) {
                                var ret = 1;
                                if (!fileNameObj.rest) {
                                    return 0;
                                }
                                var targetfilepath = fileNameObj.rest + "/" + newName;
                                var checkDup = $("#" + oData.id + "-ListHeaderIconDiv")
                                    .siblings("li")
                                    .find("a")
                                    .filter(function() {
                                        return $(this).attr("filepath") === targetfilepath;
                                    });
                                if (!checkDup.length) {
                                    ret = 0;
                                }
                                return ret;
                            };

                            var dynrows_savefile = async function(filePath, text) {
                                var obj = getFileName(filePath);
                                var newPath = obj.rest + "/" + obj.filename;
                                text = encodeURIComponent(text);
                                var run_log_uuid = $("#runVerLog").val();
                                var saveData = await doAjax({
                                    p: "saveFileContent",
                                    text: text,
                                    uuid: run_log_uuid,
                                    filename: "pubweb/" + newPath,
                                });
                                return saveData;
                            };

                            var getFileNameObj = function() {
                                var obj = {};
                                var activeLiA = $("#" + oData.id + "-ListHeaderIconDiv")
                                    .siblings("li.active")
                                    .find("a");
                                var filePath = $(activeLiA[0]).attr("filepath");
                                if (filePath) {
                                    obj = getFileName(filePath);
                                }
                                return obj;
                            };

                            var dynrows_movefile = async function(file1, file2) {
                                var run_log_uuid = $("#runVerLog").val();
                                if (file1 && file2) {
                                    var moveFile = await doAjax({
                                        p: "moveFile",
                                        type: "pubweb",
                                        from: run_log_uuid + "/pubweb/" + file1,
                                        to: run_log_uuid + "/pubweb/" + file2,
                                    });
                                    return moveFile;
                                }
                            };

                            var createModal = function() {
                                var fileModal = `
<div id="dynRowsInfo" class="modal fade" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
<h4 class="modal-title" id="dynRowsInfoTitle">Title</h4>
</div>
<div class="modal-body">
<div id="dynRowsEditDiv" class="form-horizontal">
<div class="form-group">
<label class="col-sm-4 control-label">Filename <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Please edit filename with extenstion. Eg. notes.md"><i class="glyphicon glyphicon-info-sign"></i></a></span></label>
<div class="col-sm-8">
<input id="dynRowsEditFileName" type="text" class="form-control">
</div>
</div>
</div>
<div id="dynRowsAddDiv" style="padding-right:10px;" class="form-horizontal">

<div class="form-group">
<label class="col-sm-4 control-label"><input type="checkbox" id="dynRowsEmptyFileCheck" name="check_emptyFile" data-toggle="collapse" data-target="#dynRows_emptyFileDiv" class="collapsed" aria-expanded="false"> Create Empty File </label>
</div>
<div id="dynRows_emptyFileDiv" class="collapse" aria-expanded="false" style="height: 0px;">
<div class="form-group">
<label class="col-sm-4 control-label">Filename <span><a data-toggle="tooltip" data-placement="bottom" title="" data-original-title="Please enter filename with extenstion. Eg. notes.md"><i class="glyphicon glyphicon-info-sign"></i></a></span></label>
<div class="col-sm-8">
<input id="dynRowsFileName" type="text" class="form-control">
</div>
</div>
</div>
<div class="form-group">
<label class="col-sm-4 control-label"><input type="checkbox" id="dynRows_uploadFileCheck" name="check_uploadFile" data-toggle="collapse" data-target="#dynRows_uploadFileDiv" class="collapsed" aria-expanded="false"> Upload File </label>
</div>
<div id="dynRows_uploadFileDiv" class="collapse" aria-expanded="false" style="height: 0px;">
<div class="form-group">
<div class="col-sm-12" id="dynRows_import_div">
<form id="dynRowsUploadForm" action="ajax/import.php" class="dropzone">
<div class="fallback ">
<input name="file" type="file" />
</div>
</form>
</div>
</div>
</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
<button id="dynRowsInfoButton" type="button" class="btn btn-primary" >Save</button>
</div>
</div>
</div>
</div>`;

                                if (document.getElementById("dynRowsInfo") === null) {
                                    $("body").append(fileModal);
                                }
                            };

                            var bindEventHandlerModal = function() {
                                //clean previous binds
                                $("#reportRows").off();
                                $("#dynRowsInfo").off();
                                //not allow to click both option
                                disableDoubleClickCollapse(
                                    "dynRowsEmptyFileCheck",
                                    "dynRows_emptyFileDiv",
                                    "dynRows_uploadFileCheck",
                                    "dynRows_uploadFileDiv",
                                    "dynRowsInfo"
                                );

                                $("#reportRows").on("click", "#" + addIconID, function(event) {
                                    window.dynRows = {};
                                    window.dynRows.filename = "";
                                    var myDropzone = Dropzone.forElement("#dynRowsUploadForm");
                                    myDropzone.removeAllFiles();
                                    if ($("#" + "dynRowsEmptyFileCheck").is(":checked")) {
                                        $("#" + "dynRowsEmptyFileCheck").trigger("click");
                                    }
                                    if ($("#" + "dynRows_uploadFileCheck").is(":checked")) {
                                        $("#" + "dynRows_uploadFileCheck").trigger("click");
                                    }
                                    $("#dynRowsInfoTitle").text("Create New File");
                                    $("#dynRowsFileName").val("NewFile.md");
                                    $("#dynRowsEditDiv").css("display", "none");
                                    $("#dynRowsAddDiv").css("display", "block");
                                    $("#dynRowsInfoButton").attr("class", "btn btn-primary save");
                                    $("#dynRowsInfoButton").text("Save");
                                    $("#dynRowsInfo").modal("show");
                                });

                                var callbackActiveClick = function() {
                                    $("#" + oData.id + "-ListHeaderIconDiv")
                                        .siblings("li.active")
                                        .find("a")
                                        .trigger("click");
                                };

                                $("#dynRowsInfo").on("hide.bs.modal", function(event) {
                                    $("#reportRows").dynamicRows("fnRefresh", {
                                        type: "columnsBody",
                                        callback: callbackActiveClick,
                                    });
                                });

                                $("#dynRowsInfo").on("click", ".save", async function(event) {
                                    event.preventDefault();
                                    var obj = getFileNameObj();
                                    if ($("#" + "dynRowsEmptyFileCheck").is(":checked")) {
                                        var newName = $("#dynRowsFileName").val();
                                        newName = $.trim(newName);
                                        if (newName) {
                                            var checkDuplicate = checkDuplicateFile(newName, obj);
                                            console.log(checkDuplicate);
                                            if (!checkDuplicate) {
                                                var filepath = oData.name + "/" + newName;
                                                var text = "";
                                                await dynrows_savefile(filepath, text);
                                                $("#dynRowsInfo").modal("hide");
                                            } else {
                                                showInfoModal(
                                                    "#infoMod",
                                                    "#infoModText",
                                                    "Same filename found in your directory, please enter another filename."
                                                );
                                            }
                                        } else {
                                            showInfoModal(
                                                "#infoMod",
                                                "#infoModText",
                                                "Please enter valid filename."
                                            );
                                        }
                                    } else {
                                        $("#dynRowsInfo").modal("hide");
                                    }
                                });

                                $("#reportRows").on(
                                    "click",
                                    "#" + deleteIcon,
                                    function(event) {
                                        var obj = getFileNameObj();
                                        var activeLiA = $("#" + oData.id + "-ListHeaderIconDiv")
                                            .siblings("li.active")
                                            .find("a");
                                        var text =
                                            "Are you sure you want to delete " + obj.filename + "?";
                                        var savedData = $(activeLiA[0]);
                                        var execFunc = async function(savedData) {
                                            var filePath = savedData.attr("filepath");
                                            var run_log_uuid = $("#runVerLog").val();
                                            var deleteFile = await doAjax({
                                                p: "deleteFile",
                                                uuid: run_log_uuid,
                                                filename: "pubweb/" + filePath,
                                            });
                                            $("#reportRows").dynamicRows("fnRefresh", {
                                                type: "columnsBody",
                                                callback: callbackActiveClick,
                                            });
                                        };
                                        showConfirmDeleteModal(text, savedData, execFunc);
                                    }
                                );

                                $("#reportRows").on(
                                    "click",
                                    "#" + renameIcon,
                                    function(event) {
                                        var obj = getFileNameObj();
                                        $("#dynRowsInfoTitle").text("Edit Filename");
                                        $("#dynRowsEditFileName").val(obj.filename);
                                        $("#dynRowsEditDiv").css("display", "block");
                                        $("#dynRowsAddDiv").css("display", "none");
                                        $("#dynRowsInfoButton").attr(
                                            "class",
                                            "btn btn-primary rename"
                                        );
                                        $("#dynRowsInfoButton").text("Save");
                                        $("#dynRowsInfo").modal("show");
                                    }
                                );

                                $("#dynRowsInfo").on("click", ".rename", async function(event) {
                                    event.preventDefault();
                                    var obj = getFileNameObj();
                                    var newName = $("#dynRowsEditFileName").val();
                                    newName = $.trim(newName);
                                    if (newName) {
                                        var checkDuplicate = checkDuplicateFile(newName, obj);
                                        if (!checkDuplicate) {
                                            var filepath1 = oData.name + "/" + obj.filename;
                                            var filepath2 = oData.name + "/" + newName;
                                            await dynrows_movefile(filepath1, filepath2);
                                            $("#dynRowsInfo").modal("hide");
                                        } else {
                                            showInfoModal(
                                                "#infoMod",
                                                "#infoModText",
                                                "Same filename found in your directory, please enter another filename."
                                            );
                                        }
                                    } else {
                                        showInfoModal(
                                            "#infoMod",
                                            "#infoModText",
                                            "Please enter valid filename."
                                        );
                                    }
                                });
                            };

                            var createDropzone = function() {
                                console.log("createDropzone");
                                if (Dropzone.options.dynRowsUploadForm) {
                                    $("#dynRowsUploadForm")[0].dropzone.destroy();
                                    $("#dynRowsUploadForm").off();
                                }
                                // Configuriation of dropzone of id:dynRowsUploadForm in mdEditorInfo modal
                                window.dynRows = {};
                                window.dynRows.filename = "";
                                Dropzone.options.dynRowsUploadForm = {
                                    paramName: "pubweb", // The name that will be used to transfer the file
                                    maxFilesize: 30, // MB
                                    maxFiles: 10,
                                    createImageThumbnails: false,
                                    dictDefaultMessage: 'Drop your file here or <button type="button" class="btn btn-default" >Select File </button>',
                                    accept: function(file, done) {
                                        window.dynRows.filename = file.name;
                                        done();
                                        $("#dynRows_upload_name_span").text(file.name);
                                    },
                                    init: function() {
                                        this.on("sending", function(file, xhr, formData) {
                                            formData.append("uuid", run_log_uuid);
                                            formData.append("dir", oData.name);
                                        });
                                    },
                                };
                                $("#dynRowsUploadForm").dropzone();
                            };
                            createModal();
                            createDropzone();
                            bindEventHandlerModal();
                        };

                        var getFileListHeaderIconDiv = function(id) {
                            var border = "border-right: 1px solid lightgray;";
                            var addIcon =
                                `<li role="presentation"><a id="addIcon-` +
                                id +
                                `" data-toggle="tooltip" data-placement="bottom" data-original-title="Add File"><i style="font-size: 18px;" class="fa fa-plus"></i></a></li>`;
                            var renameIcon =
                                `<li role="presentation"><a id="renameIcon-` +
                                id +
                                `" data-toggle="tooltip" data-placement="bottom" data-original-title="Rename File"><i style="font-size: 18px;" class="fa fa-pencil"></i></a></li>`;
                            var deleteIcon =
                                `<li role="presentation"><a  id="deleteIcon-` +
                                id +
                                `" data-toggle="tooltip" data-placement="bottom" data-original-title="Delete File"><i style="font-size: 18px;" class="fa fa-trash"></i></a></li>`;
                            var content =
                                `<ul style="float:right"  class="nav nav-pills panelheader">` +
                                addIcon +
                                renameIcon +
                                deleteIcon +
                                `</ul>`;
                            var wrapDiv =
                                '<div id="' +
                                id +
                                '-ListHeaderIconDiv" style="' +
                                border +
                                'height:35px; width:100%;">' +
                                content +
                                "</div>";
                            return wrapDiv;
                        };
                        var IconDiv = "";
                        var writePerm = $runscope.checkUserWritePermSync();
                        //allows user to edit run page
                        if (writePerm) {
                            if (oData.pubWeb == "run_description") {
                                IconDiv = getFileListHeaderIconDiv(oData.id);
                                addEveHandlerIconDiv(oData.id);
                            }
                        }
                        if (!liText) {
                            liText = '<div style="margin:10px;"> No data available</div>';
                        }
                        var allText = IconDiv + liText;
                        $(nTd).html(
                            '<ul class="nav nav-pills nav-stacked">' + allText + "</ul>"
                        );
                    },
                },
                {
                    //file content
                    data: null,
                    colPercent: function(oData) {
                        if (oData.pubWeb == "ucsc_genome_browser") {
                            const match = oData.fileList.find(value => /feature_metadata\.tsv/.test(value));
                            if (!match) return 100
                        }
                        return 85
                    },
                    fnCreatedCell: function(nTd, oData) {
                        var fileList = oData.fileList;
                        if ($(nTd).is(":empty")) {
                            var navTabDiv = '<div style="height:inherit;" class="tab-content">';
                            if (oData.pubWeb == "ucsc_genome_browser") {
                                const matchHub = fileList.find(value => /hub\.txt/.test(value));
                                const matchGenome = fileList.find(value => /genomes\.txt/.test(value));
                                const matchFeature = fileList.find(value => /feature_metadata\.tsv/.test(value));
                                var dir = oData.name;

                                navTabDiv += '<div ucsc_genome_browser_tabs="" style="height:100%; width:100%;" >';
                                navTabDiv += `<ul class="nav nav-tabs">`
                                if (matchHub && matchGenome) {
                                    navTabDiv += `<li class="active"><a class="nav-item" data-toggle="tab" href="#ucsc_gb_tab_${oData.id}" aria-expanded="false">Genome Browser</a></li>`
                                }
                                if (matchFeature) {
                                    navTabDiv += `<li class=""><a class="nav-item" data-toggle="tab" href="#ucsc_count_tab_${oData.id}" aria-expanded="false">Count Data</a></li>`
                                }
                                navTabDiv += `</ul>`;
                                navTabDiv += `<div class="tab-content">`;
                                if (matchHub && matchGenome) {
                                    var filenameCl = cleanProcessName(matchHub);
                                    var fileid = oData.id + "_" + filenameCl;
                                    navTabDiv += `<div id="ucsc_gb_tab_${oData.id}" dir="${dir}" genome="${matchGenome}" fileid="${fileid}" file="${matchHub}" class="ucsc_gb_tab fullsize tab-pane fade active in" style="width:100%; height:595px;"></div>`;
                                }
                                if (matchFeature) {
                                    var filenameCl = cleanProcessName(matchFeature);
                                    var fileid = oData.id + "_" + filenameCl;
                                    navTabDiv += `<div id="ucsc_count_tab_${oData.id}" dir="${dir}" file="${matchFeature}" fileid="${fileid}" style="width:100%;" class="ucsc_count_tab fullsize tab-pane fade"></div>`
                                }
                                navTabDiv += `</div>`;

                                navTabDiv += "</div>";
                                navTabDiv += "</div>";
                                $(nTd).html(navTabDiv);
                                return;
                            }
                            $.each(fileList, function(el) {
                                var filenameCl = cleanProcessName(fileList[el]);
                                var tabID = "reportTab" + oData.id + "_" + filenameCl;
                                var active = "";
                                if (el == 0) {
                                    active = "in active";
                                }
                                navTabDiv += '<div dynamicrowstabs="" style="height:100%; width:100%;" id = "' + tabID + '" class = "tab-pane fade fullsize ' + active + '" ></div>';
                            });
                            navTabDiv += "</div>";
                            $(nTd).html(navTabDiv);
                        } else {
                            //when dynamicRows.fnrefresh is executed following code will update the tabid section
                            var newTabIdList = [];
                            $.each(fileList, function(el) {
                                var filenameCl = cleanProcessName(fileList[el]);
                                var newTabID = "reportTab" + oData.id + "_" + filenameCl;
                                newTabIdList.push(newTabID);
                            });

                            //get all existing tabs
                            var alltabs = $(nTd).find("div[dynamicrowstabs]");
                            for (var k = 0; k < alltabs.length; k++) {
                                var oldTabID = $(alltabs[k]).attr("id");
                                if ($.inArray(oldTabID, newTabIdList) === -1) {
                                    //remove oldTabID from DOM, since it doesn't found in newTabIdList
                                    $(alltabs[k]).remove();
                                }
                            }
                            //add new tabs
                            $.each(fileList, function(el) {
                                var filenameCl = cleanProcessName(fileList[el]);
                                var newTabID = "reportTab" + oData.id + "_" + filenameCl;
                                if (!$(nTd).find("div#" + newTabID).length) {
                                    $(nTd)
                                        .children()
                                        .append(
                                            '<div dynamicrowstabs="" style="height:100%; width:100%;" id = "' +
                                            newTabID +
                                            '" class = "tab-pane fade fullsize" ></div>'
                                        );
                                }
                                if (el == 0) {
                                    $("#" + newTabID)
                                        .addClass("in active")
                                        .removeClass("fade");
                                }
                            });
                        }
                    },
                },
            ],
            columnsHeader: [{
                    data: null,
                    colPercent: "4",
                    fnCreatedCell: function(nTd, oData) {
                        $(nTd).html(
                            '<span class="info-box-icon" style="height:60px; line-height:60px; width:30px; font-size:18px;  background:rgba(0,0,0,0.2);"><i class="fa fa-folder"></i></span>'
                        );
                    },
                },
                {
                    data: null,
                    fnCreatedCell: function(nTd, oData) {
                        var gNum = oData.id.split("_")[0].split("-")[1];
                        var rowID = "outputTa-" + gNum;
                        var processName = $("#" + rowID + " > :nth-child(5)").text();
                        if (oData.pubWeb == "run_description") {
                            processName = "Run Notes";
                        }
                        var processID = $("#" + rowID + " > :nth-child(5)").text();
                        $(nTd).html(
                            '<span  gnum="' +
                            gNum +
                            '" processid="' +
                            processID +
                            '">' +
                            createLabel(processName) +
                            "</span>"
                        );
                    },
                    colPercent: "37",
                },
                {
                    data: null,
                    colPercent: "39",
                    fnCreatedCell: function(nTd, oData) {
                        $(nTd).html("<span>" + createLabel(oData.name) + "</span>");
                    },
                },
                {
                    data: null,
                    colPercent: "20",
                    fnCreatedCell: function(nTd, oData) {
                        var visType = oData.pubWeb;
                        var icon = "fa fa-file-text-o";
                        var text = visType;
                        if (visType === "table" || visType === "table-percent") {
                            icon = "fa fa-table";
                            text = "Table";
                        } else if (visType === "html") {
                            icon = "fa fa-file-code-o";
                            text = "HTML";
                        } else if (visType === "pdf") {
                            icon = "fa fa-file-pdf-o";
                            text = "PDF";
                        } else if (visType === "rmarkdown") {
                            icon = "fa fa-pie-chart";
                            text = "R-Markdown";
                        } else if (visType === "apps") {
                            icon = "fa fa-cube";
                            text = "App";
                        } else if (visType === "highcharts") {
                            icon = "fa fa-line-chart";
                            text = "Charts";
                        } else if (visType === "debrowser") {
                            icon = "glyphicon glyphicon-stats";
                            text = "DE-Browser";
                        } else if (visType === "image") {
                            icon = "fa fa-file-image-o";
                            text = "Image";
                        } else if (visType === "run_description") {
                            icon = "fa fa-edit";
                            text = "Markdown";
                        } else if (visType === "ucsc_genome_browser") {
                            icon = "fa fa-area-chart";
                            text = "UCSC Genome Browser";
                        }
                        $(nTd).html(
                            '<a data-toggle="tooltip" data-placement="bottom" data-original-title="View"><i class="' +
                            icon +
                            '"></i> ' +
                            text +
                            "</a>"
                        );
                    },
                },
            ],
            columnsTitle: [{
                    data: null,
                    colPercent: "4",
                },
                {
                    data: null,
                    fnCreatedCell: function(nTd, oData) {
                        $(nTd).html("<span>PROCESS</span>");
                    },
                    colPercent: "37",
                },
                {
                    data: "name",
                    colPercent: "39",
                    fnCreatedCell: function(nTd, oData) {
                        $(nTd).html("<span>PUBLISHED DIRECTORY</span>");
                    },
                },
                {
                    data: null,
                    colPercent: "20",
                    fnCreatedCell: function(nTd, oData) {
                        $(nTd).html("<span>VIEW FORMAT</span>");
                    },
                },
            ],
            backgroundcolorenter: "#ced9e3",
            backgroundcolorleave: "#ECF0F4",
            heightHeader: "60px",
        });
    }

    $(document).on("click", "#refreshVerReport", function(event) {
        reloadReportRows();
    });
    $(document).on("click", "#addRunNotes", function(event) {
        var run_log_uuid = $("#runVerLog").val();

        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: { p: "addRunNotes", run_log_uuid: run_log_uuid },
            async: true,
            success: function(pipe) {
                reloadReportRows();
            },
            error: function(errorThrown) {
                toastr.error("Error occured.");
            },
        });
    });

    function updateRunReportTab(run_log_uuid) {
        var reload = true;
        if (run_log_uuid) {
            var newRun = $("option:selected", "#runVerLog").attr("newrun") || false;
            var version = $("option:selected", "#runVerLog").attr("ver");
            if (version && !newRun) {
                var runTitleLog = "<label>Run " + version + " - Report</label>";
                $('a[href="#reportTab"]').css("display", "block");
            } else {
                if ($("ul#runTabDiv li.active > a")[0]) {
                    if (
                        $($("ul#runTabDiv li.active > a")[0]).attr("href") == "#reportTab"
                    ) {
                        $('.nav-tabs a[href="#configTab"]').trigger("click");
                    }
                }

                var runTitleLog = "";
                $('a[href="#reportTab"]').css("display", "none");
            }
            $("#runTitleReport").html(runTitleLog);
            if (reload) {
                reloadReportRows();
            }
        }
    }

    async function updateRunConfigTab(prevUID) {
        var loadRunLogOpt = async function() {
            var run_log_uuid = $("#runVerLog").val();
            var runTitleConfig = "";
            var runTitleAdvanced = "";
            var version = $("option:selected", "#runVerLog").attr("ver");
            if (version) {
                runTitleConfig = "<label>Run " + version + " - Settings</label>";
                runTitleAdvanced = "<label>Run " + version + " - Advanced</label>";
            } else {
                runTitleConfig = "<label>Run Settings</label>";
                runTitleAdvanced = "<label>Advanced</label>";
            }
            $("#runTitleConfig").html(runTitleConfig);
            $("#runTitleAdvanced").html(runTitleAdvanced);
            var lastrun = $("option:selected", "#runVerLog").attr("lastrun") || false;
            var newRun = $("option:selected", "#runVerLog").attr("newrun") || false;
            var newRunExist = await checkNewRunStatus();

            //load from projectpipelinedata
            if (newRun || (!newRunExist && lastrun)) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: { p: "getProjectPipelines", id: project_pipeline_id },
                    async: true,
                    success: async function(pipe) {
                        if (pipe) {
                            await loadRunSettings(pipe);
                            var projectpipelineOwn = await $runscope.checkProjectPipelineOwn();
                            if (projectpipelineOwn == "1") {
                                // chooseDefaultRunEnv(pipe)
                            }
                            await checkReadytoRun();

                        }
                    },
                    error: function(errorThrown) {
                        toastr.error("Error occured.");
                    },
                });
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: {
                        p: "getProjectPipelineInputs",
                        project_pipeline_id: project_pipeline_id,
                    },
                    async: true,
                    success: async function(projectPipeInputs_rev) {
                        if (projectPipeInputs_rev) {
                            var projectpipelineOwn = await $runscope.checkProjectPipelineOwn();
                            if (projectpipelineOwn == "1") {
                                fillProPipeInputsRev(projectPipeInputs_rev, "default");
                            } else {
                                fillProPipeInputsRev(projectPipeInputs_rev, "dry");
                            }
                        }
                    },
                    error: function(errorThrown) {
                        toastr.error("Error occured.");
                    },
                });
                //load from getRunLogOpt
            } else {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: { p: "getRunLogOpt", uuid: run_log_uuid },
                    async: true,
                    success: async function(s) {
                        var pipe = s.run_opt;
                        if (pipe) {
                            console.log(IsJsonString(pipe));
                            if (IsJsonString(pipe)) {
                                var json = JSON.parse(pipe);
                                console.log(json);
                                if (json) {
                                    var pipeData = [];
                                    pipeData[0] = json;
                                    await loadRunSettings(pipeData);
                                    if (json.project_pipeline_input) {
                                        var projectPipeInputs_rev = json.project_pipeline_input;
                                        fillProPipeInputsRev(projectPipeInputs_rev, "dry");
                                    }
                                }
                            }
                        } else {
                            console.log(pipe);
                        }
                    },
                    error: function(errorThrown) {
                        toastr.error("Error occured.");
                    },
                });
            }
        };
        console.log("updateRunConfigTab");
        console.log("prevUID", prevUID);
        var prevlastrun =
            $('#runVerLog option[value="' + prevUID + '"]').attr("lastrun") || false;
        var prevnewRun =
            $('#runVerLog option[value="' + prevUID + '"]').attr("newRun") || false;
        var newRunExist = await checkNewRunStatus();
        var projectpipelineOwn = await $runscope.checkProjectPipelineOwn();
        // if prevUID belong to newrun then saveRun before loadRunLogOpt
        if (
            projectpipelineOwn == "1" &&
            (prevnewRun || (!newRunExist && prevlastrun))
        ) {
            await saveRun(loadRunLogOpt, false);
        } else {
            await loadRunLogOpt();

        }
    }

    async function updateRunLogTab(run_log_uuid) {
        console.log("updateRunLogTab");
        if (run_log_uuid) {
            var runTitleLog = "";
            var version = $("option:selected", "#runVerLog").attr("ver");
            if (version) {
                var runTitleLog = "<label>Run " + version + " - Log</label>";
            }
            var lastrun = $("option:selected", "#runVerLog").attr("lastrun");
            console.log(lastrun);
            if (lastrun) {
                lastrun = 'lastrun="yes"';
            } else {
                lastrun = "";
            }
            var activeTab = $("ul#logNavBar li.active > a");
            var activeID = "";
            if (activeTab[0]) {
                activeID = $(activeTab[0]).attr("href");
            }

            $("#runTitleLog").html(runTitleLog);
            $("#logContentDiv").empty();
            //to support outdated log directory system
            if (run_log_uuid.match(/^run/)) {
                var path = "";
            } else {
                var path = "run";
            }
            var fileList = await doAjax({
                p: "getFileList",
                uuid: run_log_uuid,
                path: path,
                type: "onlyfilehidden",
            });
            var fileListAr = getObjectValues(fileList);
            console.log(fileListAr);
            var order = [
                "log.txt",
                "timeline.html",
                "report.html",
                "dag.html",
                "trace.txt",
                ".nextflow.log",
                "initialrun/.nextflow.log",
                "initialrun/trace.txt",
                "nextflow.nf",
                "nextflow.config",
            ];
            var logContentDivAttr = [
                "SHOW_RUN_LOG",
                "SHOW_RUN_TIMELINE",
                "SHOW_RUN_REPORT",
                "SHOW_RUN_DAG",
                "SHOW_RUN_TRACE",
                "SHOW_RUN_NEXTFLOWLOG",
                "SHOW_RUN_NEXTFLOWLOG",
                "SHOW_RUN_TRACE",
                "SHOW_RUN_NEXTFLOWNF",
                "SHOW_RUN_NEXTFLOWCONFIG",
            ];
            //hide serverlog.txt
            var pubWebPath = $("#basepathinfo").attr("pubweb");
            var navTabDiv = '<ul id="logNavBar" class="nav nav-tabs">';
            var k = 0;
            var tabDiv = [];
            var fileName = [];
            for (var j = 0; j < order.length; j++) {
                if (
                    $("#logContentDiv").attr(logContentDivAttr[j]) == "true" &&
                    (fileListAr.includes(order[j]) || order[j] == "log.txt")
                ) {
                    var exist = 'style="display:block;"';
                } else {
                    var exist = 'style="display:none;"';
                }
                k++;
                var active = "";
                if (k == 1) {
                    active = 'class="active"';
                }
                var tabID = cleanProcessName(order[j]) + "Tab";
                console.log(tabID);
                tabDiv.push(tabID);
                fileName.push(order[j]);
                navTabDiv +=
                    '<li id="' +
                    tabID +
                    '_Div"' +
                    active +
                    '><a class="nav-item sub updateIframe" ' +
                    exist +
                    ' data-toggle="tab"  href="#' +
                    tabID +
                    '">' +
                    order[j] +
                    "</a></li>";
            }
            navTabDiv += "</ul>";
            navTabDiv += '<div id="logNavCont" class="tab-content">';
            for (var n = 0; n < tabDiv.length; n++) {
                var link =
                    pubWebPath + "/" + run_log_uuid + "/" + path + "/" + fileName[n];
                var active = "";
                if (n == 0) {
                    active = "in active";
                }
                navTabDiv +=
                    '<div id = "' +
                    tabDiv[n] +
                    '" class = "tab-pane fade ' +
                    active +
                    '" >';
                if (fileName[n] == "log.txt") {
                    var serverlogText = "";
                    var logText = await doAjax({
                        p: "getFileContent",
                        uuid: run_log_uuid,
                        filename: path + "/log.txt",
                    });
                    if (fileListAr.includes("serverlog.txt")) {
                        serverlogText = await doAjax({
                            p: "getFileContent",
                            uuid: run_log_uuid,
                            filename: path + "/serverlog.txt",
                        });
                        //to support outdated log directory system
                    } else if (fileListAr.includes("nextflow.log")) {
                        logText += await doAjax({
                            p: "getFileContent",
                            uuid: run_log_uuid,
                            filename: path + "/nextflow.log",
                        });
                    }
                    if (fileListAr.includes("err.log")) {
                        serverlogText += await doAjax({
                            p: "getFileContent",
                            uuid: run_log_uuid,
                            filename: path + "/err.log",
                        });
                    }
                    // new version of keeping  initial.log
                    if (fileListAr.includes("initialrun/initial.log")) {
                        serverlogText += await doAjax({
                            p: "getFileContent",
                            uuid: run_log_uuid,
                            filename: path + "/initialrun/initial.log",
                        });
                    }
                    // old version of keeping initial.log:
                    if (fileListAr.includes("initial.log")) {
                        serverlogText += await doAjax({
                            p: "getFileContent",
                            uuid: run_log_uuid,
                            filename: path + "/initial.log",
                        });
                    }
                    navTabDiv +=
                        "<textarea " +
                        lastrun +
                        ' readonly id="runLogArea" rows="20" style="overflow-y: scroll; min-width: 100%; max-width: 100%; border-color:lightgrey;" >' +
                        serverlogText +
                        logText +
                        "</textarea>";
                } else {
                    navTabDiv +=
                        '<iframe frameborder="0"  style="width:100%; height:900px;" fillsrc="' +
                        link +
                        '"></iframe>';
                }
                navTabDiv +=
                    '<a href="' +
                    link +
                    '" class="btn btn-info" role="button" target="_blank">Open Web Link</a>';
                navTabDiv +=
                    '<a style="margin-left:5px;" href="#" class="btn btn-info tooglehelp" href="#" data-toggle="control-sidebar" data-slide="true">Contact Us</a>';
                navTabDiv +=
                    '<span style="font-size:10px; float:right; color:gray;">Run UID: ' +
                    run_log_uuid +
                    "</span>";
                navTabDiv += "</div>";
            }
            navTabDiv += "</div>";
            $("#logContentDiv").append(navTabDiv);
            $('a[href="#log_txtTab"]').on("shown.bs.tab", function(e) {
                autoScrollLogArea();
            });
            if (activeID) {
                if (
                    $('.nav-tabs a[href="' + activeID + '"]').css("display") != "none"
                ) {
                    $('.nav-tabs a[href="' + activeID + '"]').trigger("click");
                }
            }
        }
        $('[data-toggle="tooltip"]').tooltip();
    }

    $(document).on("click", ".createNewRun", async function(event) {
        //check if lastrun is running, then show warning
        if (
            runStatus == "NextRun" ||
            runStatus == "Waiting" ||
            runStatus == "init"
        ) {
            showInfoModal(
                "#infoModal",
                "#infoModalText",
                "Please wait for your last run to finish, before creating new run."
            );
        } else {
            var newRunExist = await checkNewRunStatus();
            if (newRunExist) {
                var text =
                    "New run has already been created. Are you sure you want to overwrite the settings of existing new run page?";
                var savedData = "";
                var execFunc = function(savedData) {
                    createNewRunFunc(newRunExist);
                };
                var btnText = "Overwrite";
                showConfirmDeleteModal(text, savedData, execFunc, btnText);
            } else {
                createNewRunFunc(newRunExist);
            }
            //            $('#runVerLog').val(0);
            //            $('#runVerLog').trigger("change");
        }
    });

    $(document).on("change", "#runVerLog", async function(event) {
        console.log("change:runVerLog");
        //check which tab is active:
        var activeTab = "";
        var activeTab = $("ul#runTabDiv li.active > a");
        var activeID = "";
        if (activeTab[0]) {
            activeID = $(activeTab[0]).attr("href");
        }
        var size = $("#runVerLog > option").length;
        if (size) {
            $("#runHistoryDiv").css("display", "block");
        } else {
            $("#runHistoryDiv").css("display", "none");
        }
        var newRun = $("option:selected", "#runVerLog").attr("newrun") || false;
        var version = $("option:selected", "#runVerLog").attr("ver");
        if (version && !newRun) {
            $('a[href="#logTab"]').css("display", "block");
            $('a[href="#reportTab"]').css("display", "block");
        } else {
            $('a[href="#logTab"]').css("display", "none");
            $('a[href="#reportTab"]').css("display", "none");
        }
        var run_log_uuid = $("#runVerLog").val();
        // save the previous options into their attributes
        var reportTabUID = $("#runVerLog").attr("reportTabUID");
        var logTabUID = $("#runVerLog").attr("logTabUID");
        var configTabUID = $("#runVerLog").attr("configTabUID");
        var prevUID = $("#runVerLog").attr("prevUID");
        // save all change info
        if (prevUID != run_log_uuid) {
            $("#runVerLog").attr("prevUID", run_log_uuid);
        }
        var lastrun = $("option:selected", "#runVerLog").attr("lastrun") || false;
        var newRunExist = await checkNewRunStatus();
        var projectpipelineOwn = await $runscope.checkProjectPipelineOwn();
        console.log("lastrun", lastrun);
        console.log("newRun", newRun);
        console.log("newRunExist", newRunExist);
        //new run mode
        if (newRun && projectpipelineOwn == "1") {
            toogleStatusMode("default");
            tooglePermsGroupsDiv("show");
            toogleMainIcons("show");
            toogleRunInputs("enable");
            checkType = "newrun";
            await checkReadytoRun();
            //last run mode
        } else if (lastrun && !newRunExist && projectpipelineOwn == "1") {
            toogleStatusMode("default");
            tooglePermsGroupsDiv("show");
            toogleMainIcons("show");
            toogleRunInputs("enable");
            checkType = "";

            //history mode
        } else {
            //            if (projectpipelineOwn == "1"){
            //                toogleStatusMode("oneOption");
            //            } else {
            //                toogleStatusMode("noOption");
            //            }
            tooglePermsGroupsDiv("hide");
            toogleMainIcons("hide");
            toogleRunInputs("disable");
            if (prevUID != run_log_uuid) {
                updateRunLogStat(run_log_uuid, projectpipelineOwn);
            }
        }
        console.log("activeID", activeID);
        if (activeID == "#logTab") {
            if (logTabUID != run_log_uuid) {
                $("#runVerLog").attr("logTabUID", run_log_uuid);
                await readNextLog(proTypeWindow, proIdWindow, "no_reload");
                await updateRunLogTab(run_log_uuid);
            }
        } else if (activeID == "#reportTab") {
            if (reportTabUID != run_log_uuid) {
                $("#runVerLog").attr("reportTabUID", run_log_uuid);
                updateRunReportTab(run_log_uuid);
            }
        } else if (activeID == "#configTab" || activeID == "#advancedTab") {
            //don't fill on page load
            if (configTabUID != run_log_uuid) {
                $("#runVerLog").attr("configTabUID", run_log_uuid);
                await updateRunConfigTab(configTabUID);
            }
        }
    });
});

function updateRunLogStat(run_log_uuid, projectpipelineOwn) {
    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: { p: "getRunLogStatus", uuid: run_log_uuid },
        async: true,
        success: function(s) {
            if (s) {
                if (s[0]) {
                    var run_status = s[0].run_status;
                    var run_opt_check = s[0].run_opt_check;
                    console.log(run_status);
                    console.log(run_opt_check);
                    if (run_opt_check == "1" && projectpipelineOwn == "1") {
                        toogleStatusMode("oneOption");
                        runLogStatUpdate(run_status, "Opt");
                    } else {
                        toogleStatusMode("noOption");
                        runLogStatUpdate(run_status, "NoOpt");
                    }
                }
            }
        },
        error: function(errorThrown) {
            toastr.error("Error occured.");
        },
    });
}
Dropzone.autoDiscover = false;
$(document).ready(async function() {
    project_pipeline_id = $("#pipeline-title").attr("projectpipelineid");
    pipeData = await $runscope.getAjaxData("getProjectPipelines", {
        p: "getProjectPipelines",
        id: project_pipeline_id,
    });
    $("#project-title").text(decodeHtml(pipeData[0].project_name));
    $("#run-title").changeVal(decodeHtml(pipeData[0].pp_name));
    $("#runSum").val(decodeHtml(pipeData[0].summary).replaceAll("</br>", "\r\n"));
    var projectpipelineOwn = await $runscope.checkProjectPipelineOwn();
    pipeline_id = pipeData[0].pipeline_id;
    project_id = pipeData[0].project_id;
    countFailRead = 0; //count failed read amount if it reaches 5, show connection lost
    changeOnchooseEnv = false;
    // save info when run saved successfully
    // if user not own it, cannot change or delete run
    if (projectpipelineOwn !== "1") {
        $("#deleteRun").remove();
        $("#editRunSum").remove();
        $("#saveRunSum").remove();
        $("#moveRun").remove();
        $("#delRun").remove();
        $("#saveRunIcon").remove();
        $("#pipeRunDiv").remove();
        $("#runStatDiv").remove();
        $("#runHistoryConsole").remove();
        toogleStatusMode("noOption");
        $("#run-title").prop("disabled", true);
    }
    runStatus = "";
    if (projectpipelineOwn === "1") {
        runStatus = await getRunStatus(project_pipeline_id);
    }
    var profileTypeId = pipeData[0].profile; //local-32
    proTypeWindow = "";
    proIdWindow = "";
    if (profileTypeId) {
        if (profileTypeId.match(/-/)) {
            var patt = /(.*)-(.*)/;
            proTypeWindow = profileTypeId.replace(patt, "$1");
            proIdWindow = profileTypeId.replace(patt, "$2");
        }
    }

    if (runStatus !== "") {
        //Available Run_status States: NextErr,NextSuc,NextRun,Error,Waiting,init
        await readNextLog(proTypeWindow, proIdWindow, "reload");
        readPubWeb(proTypeWindow, proIdWindow, "reload");
    } else {
        $("#statusProPipe").css("display", "inline");
    }

    $("#pipeline-title").attr("pipeline_id", pipeline_id);
    if (project_pipeline_id !== "" && pipeline_id !== "") {
        loadPipelineDetails(pipeline_id, pipeData);
    }


    //##################
    //Single File Modal

    var createDropzoneForSingleFile = function() {
        console.log("createDropzoneForSingleFile");
        if (Dropzone.options.uploadSingleFile) {
            $("#uploadSingleFile")[0].dropzone.destroy();
            $("#uploadSingleFile").off();
        }
        // Configuriation of dropzone of id:dynRowsUploadForm in mdEditorInfo modal
        window.uploadSingleFile = {};
        window.uploadSingleFile.filename = "";
        Dropzone.options.uploadSingleFile = {
            renameFile: function(file) {
                let newName = file.name.replace(/\s/g, '_')
                return newName;
            },
            paramName: "single_file", // The name that will be used to transfer the file
            maxFilesize: 200, // MB
            maxFiles: 1,
            createImageThumbnails: false,
            dictDefaultMessage: 'Drop your file here or <button type="button" class="btn btn-default" >Select File </button>',
            accept: function(file, done) {
                var run_env = $("#chooseEnv").val()
                var target_dir = $("#rOut_dir").val()
                if (!run_env) {
                    done("Please select run environment.");
                    return;
                }
                if (!target_dir) {
                    done("Please enter work directory.");
                    return;
                }
                window.uploadSingleFile.filename = file.name;
                done();
                $("#uploadSingleFile_upload_name_span").text(file.name);
            },
            init: function() {
                this.on("sending", function(file, xhr, formData) {
                    var run_env = $("#chooseEnv").val()
                    var target_dir = $("#rOut_dir").val()
                    if (target_dir) {
                        target_dir = `${target_dir}/run${project_pipeline_id}/upload`
                    }
                    formData.append("target_dir", target_dir);
                    formData.append("run_env", run_env);
                });
                this.on("maxfilesexceeded", function(file) {
                    this.removeAllFiles();
                    this.addFile(file);
                });

            },
            success: function(file, response) {
                var target_dir = $("#rOut_dir").val()
                target_dir = target_dir.replace(/\/$/, '');
                let newName = file.name.replace(/\s/g, '_')
                var target_file = `${target_dir}/run${project_pipeline_id}/upload/${newName}`
                $("#singleFilePath").val(target_file)
                return file.previewElement.classList.add("dz-success");
            }

        };
        $("#uploadSingleFile").dropzone();
    };

    $('#inputSingleFilemodal').on('show.bs.modal', async function(e) {
        createDropzoneForSingleFile()
        var button = $(e.relatedTarget);
        $(this).find("form").trigger("reset");
        var clickedRow = button.closest("tr");
        var rowID = clickedRow[0].id; //#inputTa-3
        var gNumParam = rowID.split("Ta-")[1];
        $("#mIdSingleFile").attr("rowID", rowID);
        if (button.attr("id") === "inputSingleFileEdit") {
            var proPipeInputID = $("#" + rowID).attr("propipeinputid");
            $("#mIdSingleFile").val(proPipeInputID);
            // Get the input id of proPipeInput;
            var proInputGet = await doAjax({
                p: "getProjectPipelineInputs",
                id: proPipeInputID,
            });
            if (proInputGet) {
                var input_id = proInputGet[0].input_id;
                var inputGet = await doAjax({ p: "getInputs", id: input_id });
                inputGet = inputGet[0];
                if (inputGet) {
                    //insert data into form
                    var formValues = $("#inputSingleFilemodal").find("input");
                    var keys = Object.keys(inputGet);
                    for (var i = 0; i < keys.length; i++) {
                        $(formValues[i]).val(inputGet[keys[i]]);
                    }
                }
            }
        }
    })

    $('#inputSingleFilemodal').on('hide.bs.modal', function(e) {
        //reset import area
        var myDropzone = Dropzone.forElement("#uploadSingleFile");
    })

    $("#inputSingleFilemodal").on("click", "#saveSingleFile", async function(e) {
        e.preventDefault();
        $("#inputSingleFilemodal").loading({
            message: "Working...",
        });
        var savetype = $("#mIdSingleFile").val();
        if (!savetype.length) {
            //add item
            var formValues = $("#inputSingleFilemodal").find("input");
            var data = formValues.serializeArray(); // convert form to array
            // check if name is entered
            data[1].value = $.trim(data[1].value);
            if (data[1].value !== "") {
                await saveFileSetValModal(data, "single_file", null, null);
                $("#inputSingleFilemodal").loading("stop");
                $("#inputSingleFilemodal").modal("hide");
            } else {
                $("#inputSingleFilemodal").loading("stop");
                showInfoModal(
                    "#infoModal",
                    "#infoModalText",
                    "Please enter file path or upload file to save."
                );
            }
        } else {
            //edit item
            var formValues = $("#inputSingleFilemodal").find("input");
            var data = formValues.serializeArray(); // convert form to array
            // check if file_path is entered
            data[1].value = $.trim(data[1].value);
            if (data[1].value !== "") {
                await editFileSetValModal(data, "single_file", null, null);
                $("#inputSingleFilemodal").loading("stop");
                $("#inputSingleFilemodal").modal("hide");
            } else {
                $("#inputSingleFilemodal").loading("stop");
                showInfoModal(
                    "#infoModal",
                    "#infoModalText",
                    "Please enter file path or upload file to save."
                );
            }
        }
    });

    //##################
    //Sample Modal
    initCompleteFunction = function(settings, json) {
        var columnsToSearch = { 2: "Collection", 3: "Host", 4: "Project" };
        for (var i in columnsToSearch) {
            var api = new $.fn.dataTable.Api(settings);
            $("#sampleTable_filter").css("display", "inline-block");
            $("#searchBarST").append(
                '<div style="margin-bottom:20px; padding-left:8px; display:inline-block;" id="filter-' +
                columnsToSearch[i] +
                '"></div>'
            );
            var select = $(
                    '<select id="select-' +
                    columnsToSearch[i] +
                    '" name="' +
                    columnsToSearch[i] +
                    '" multiple="multiple"></select>'
                )
                .appendTo($("#filter-" + columnsToSearch[i]).empty())
                .attr("data-col", i)
                .on("change", function() {
                    var vals = $(this).val();
                    var valReg = "";
                    for (var k = 0; k < vals.length; k++) {
                        var val = $.fn.dataTable.util.escapeRegex(vals[k]);
                        if (val) {
                            if (k + 1 !== vals.length) {
                                valReg += val + "|";
                            } else {
                                valReg += val;
                            }
                        }
                    }
                    api
                        .column($(this).attr("data-col"))
                        .search(valReg ? "(^|,)" + valReg + "(,|$)" : "", true, false)
                        .draw();

                    //deselect rows that are selected but not visible
                    var visibleRows = $("#sampleTable")
                        .DataTable()
                        .rows({ search: "applied" })[0];
                    var selectedRows = $("#sampleTable").DataTable().rows(".selected")[0];
                    api.column($(this).attr("data-col")).rows(function(idx, data, node) {
                        if (
                            $.inArray(idx, visibleRows) === -1 &&
                            $.inArray(idx, selectedRows) !== -1
                        ) {
                            $("#sampleTable").DataTable().row(idx).deselect(idx);
                        }
                        return false;
                    });
                });
            var collectionList = [];
            api
                .column(i)
                .data()
                .unique()
                .sort()
                .each(function(d, j) {
                    if (d) {
                        var multiCol = d.split(",");
                        for (var n = 0; n < multiCol.length; n++) {
                            if (collectionList.indexOf(multiCol[n]) == -1) {
                                collectionList.push(multiCol[n]);
                                select.append(
                                    '<option value="' +
                                    multiCol[n] +
                                    '">' +
                                    multiCol[n] +
                                    "</option>"
                                );
                            }
                        }
                    }
                });

            createMultiselect("#select-" + columnsToSearch[i]);
            createMultiselectBinder("#filter-" + columnsToSearch[i]);
            var selCollectionNameArr = $("#sampleTable").data("select");
            var selIdxArr = $("#sampleTable").data("selectIdx");
            // when file updates
            if (selIdxArr && selCollectionNameArr) {
                if (selIdxArr.length) {
                    $("#sampleTable").removeData("selectIdx");
                    $("#sampleTable").DataTable().rows(selIdxArr).select();
                }
                if (selCollectionNameArr.length) {
                    $("#sampleTable").removeData("select");
                    selectMultiselect("#select-Collection", selCollectionNameArr);
                }
                // when new collection created or edit collection button
            } else if (selCollectionNameArr) {
                if (selCollectionNameArr.length) {
                    $("#sampleTable").removeData("select");
                    selectMultiselect("#select-Collection", selCollectionNameArr);
                    $("#sampleTable").DataTable().rows({ search: "applied" }).select();
                }
            }
        }
    };

    $(function() {
        function loadRunHistoryTable(type) {
            if ($.fn.DataTable.isDataTable("#runHistoryTable")) {
                $("#runHistoryTable").dataTable().off();
                $("#runHistoryTable").dataTable().fnDestroy();
            }
            if (type == "default") {
                var histTable = $("#runHistoryTable").DataTable({
                    ajax: {
                        url: "ajax/ajaxquery.php",
                        data: { p: "getRunLog", project_pipeline_id: project_pipeline_id },
                        dataSrc: "",
                    },
                    columns: [{
                            data: "id",
                            visible: false,
                        },
                        {
                            data: null,
                            fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
                                var editicon =
                                    '<a class="runHistoryRecRename" data-toggle="tooltip" data-placement="bottom" data-original-title="Rename" style="margin-left:3px;"><i style="color:grey;" class="fa fa-pencil"></i></a>';
                                var runName;
                                if (oData.name) {
                                    runName = decodeHtml(oData.name);
                                } else {
                                    var runNum = iRow + 1;
                                    runName = "Run " + runNum;
                                }
                                $(nTd).html(
                                    '<span class="runHistName" logid="' +
                                    oData.id +
                                    '">' +
                                    runName +
                                    "</span>" +
                                    editicon
                                );
                            },
                        },
                        {
                            data: null,
                            fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
                                var sizeInMB = 0;
                                if (oData.size) {
                                    sizeInMB = (oData.size / 1024).toFixed(2);
                                    if (sizeInMB == 0.0) {
                                        sizeInMB = 0;
                                    }
                                } else {
                                    sizeInMB = "NA";
                                }
                                $(nTd).html(sizeInMB);
                            },
                        },
                        {
                            data: "date_created",
                        },
                    ],
                    order: [
                        [3, "desc"]
                    ],
                    paging: false,
                    bFilter: false,
                    info: false,
                    scrollY: "200px",
                    sScrollX: true,
                    scrollX: true,
                });
            } else if (type == "delete") {
                var histTable = $("#runHistoryTable").DataTable({
                    ajax: {
                        url: "ajax/ajaxquery.php",
                        data: { p: "getRunLog", project_pipeline_id: project_pipeline_id },
                        dataSrc: "",
                    },
                    columns: [{
                            data: "id",
                            checkboxes: {
                                targets: 0,
                                selectRow: true,
                            },
                        },
                        {
                            data: null,
                            fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
                                if (oData.name) {
                                    $(nTd).html(decodeHtml(oData.name));
                                } else {
                                    var runNum = iRow + 1;
                                    $(nTd).html("Run " + runNum);
                                }
                            },
                        },
                        {
                            data: null,
                            fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
                                var sizeInMB = 0;
                                if (oData.size) {
                                    sizeInMB = (oData.size / 1024).toFixed(2);
                                    if (sizeInMB == 0.0) {
                                        sizeInMB = 0;
                                    }
                                } else {
                                    sizeInMB = "NA";
                                }
                                $(nTd).html(sizeInMB);
                            },
                        },
                        {
                            data: "date_created",
                        },
                    ],
                    order: [
                        [3, "desc"]
                    ],
                    paging: false,
                    bFilter: false,
                    info: false,
                    scrollY: "200px",
                    sScrollX: true,
                    scrollX: true,
                    select: {
                        style: "multiple",
                    },
                });
            } else if (type == "purge" || type == "recover") {
                var histTable = $("#runHistoryTable").DataTable({
                    ajax: {
                        url: "ajax/ajaxquery.php",
                        data: {
                            p: "getRunLogAll",
                            project_pipeline_id: project_pipeline_id,
                        },
                        dataSrc: "",
                    },
                    columns: [{
                            data: "id",
                            checkboxes: {
                                targets: 0,
                                selectRow: true,
                            },
                        },
                        {
                            data: null,
                            fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
                                if (oData.name) {
                                    $(nTd).html(decodeHtml(oData.name));
                                } else {
                                    var runNum = iRow + 1;
                                    $(nTd).html("Run " + runNum);
                                }
                            },
                        },
                        {
                            data: null,
                            fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
                                var sizeInMB = 0;
                                if (oData.size) {
                                    sizeInMB = (oData.size / 1024).toFixed(2);
                                    if (sizeInMB == 0.0) {
                                        sizeInMB = 0;
                                    }
                                } else {
                                    sizeInMB = "NA";
                                }
                                $(nTd).html(sizeInMB);
                            },
                        },
                        {
                            data: "date_created",
                        },
                    ],
                    order: [
                        [3, "desc"]
                    ],
                    paging: false,
                    bFilter: false,
                    info: false,
                    scrollY: "200px",
                    sScrollX: true,
                    scrollX: true,
                    select: {
                        style: "multiple",
                        selector: "tr:not(.no-select)",
                    },
                    rowCallback: function(row, data, index) {
                        if (data.deleted == "0") {
                            $("td:eq(0)", row).html("");
                            $(row).addClass("no-select");
                            $(row).removeClass("strikeline-row");
                        } else {
                            $(row).removeClass("no-select");
                            $(row).addClass("strikeline-row");
                        }
                    },
                });
                histTable.on("select.dt", function(e, dt, type, indexes) {
                    histTable.cells("tr.no-select", 0).checkboxes.deselect();
                });
            }
        }
        $(document).on("click", "#runHistoryConsole", async function() {
            $("#runHistoryModal").modal("show");
            var newRunExist = await checkNewRunStatus();
            if (newRunExist) {
                $("#removeNewRunIcon").css("display", "inline");
            } else {
                $("#removeNewRunIcon").css("display", "none");
            }
        });
        $("#runHistoryModal").on("show.bs.modal", function() {
            $("#runHistoryModalBut").css("display", "none");
            loadRunHistoryTable("default");
        });
        $("#runHistoryModal").on("shown.bs.modal", function() {
            $("#runHistoryTable").DataTable().columns.adjust().draw();
        });
        $("#runHistoryModal").on("click", "#runHistoryDelIcon", function() {
            loadRunHistoryTable("delete");
            $("#runHistoryModalBut").css("display", "block");
            $("#runHistoryModalApply").data("info", "delete");
            $("#runHistoryModalApply").text("Delete");
        });
        $("#runHistoryModal").on("click", ".runHistoryRecRename", function() {
            var runName = $(this).siblings("span").text();
            var logid = $(this).siblings("span").attr("logid");
            $("#editRunName").data("logid", logid);
            $("#editRunName").val(runName);
            $("#editRunModal").modal("show");
            $("#runHistoryModalBut").css("display", "none");
        });
        $("#runHistoryModal").on("click", "#runHistoryPurgeIcon", function() {
            loadRunHistoryTable("purge");
            $("#runHistoryModalBut").css("display", "block");
            $("#runHistoryModalApply").data("info", "purge");
            $("#runHistoryModalApply").text("Purge");
        });
        $("#runHistoryModal").on("click", "#runHistoryRecIcon", function() {
            loadRunHistoryTable("recover");
            $("#runHistoryModalBut").css("display", "block");
            $("#runHistoryModalApply").data("info", "recover");
            $("#runHistoryModalApply").text("Recover");
        });
        $("#runHistoryModal").on("click", "#removeNewRunIcon", function() {
            $.ajax({
                url: "ajax/ajaxquery.php",
                data: {
                    p: "updateProjectPipelineNewRun",
                    newrun: 0,
                    project_pipeline_id: project_pipeline_id,
                },
                cache: false,
                type: "POST",
                success: async function(data) {
                    if (data) {
                        updateNewRunStatus("0");
                        await fillRunVerOpt("#runVerLog");
                        $("#runVerLog").trigger("change");
                        $("#removeNewRunIcon").css("display", "none");
                        showInfoModal(
                            "#infoMod",
                            "#infoModText",
                            "New run removed from dropdown."
                        );
                    } else {
                        toastr.error("Error occured.");
                    }
                },
                error: function(jqXHR, exception) {
                    toastr.error("Error occured.");
                },
            });
        });
        $("#editRunModal").on("click", "#editRunDetails", function() {
            var runName = $("#editRunName").val();
            var logid = $("#editRunName").data("logid");
            if (runName) {
                if (runName.length > 50) {
                    showInfoModal(
                        "#infoMod",
                        "#infoModText",
                        "It is not allowed to enter more than 50 characters."
                    );
                } else {
                    runName = encodeURIComponent(runName);
                    getValuesAsync({ p: "saveRunLogName", name: runName, id: logid },
                        async function(s) {
                            loadRunHistoryTable("default");
                            await fillRunVerOpt("#runVerLog");
                            $("#editRunModal").modal("hide");
                        }
                    );
                }
            } else {
                showInfoModal(
                    "#infoMod",
                    "#infoModText",
                    "Please enter run name to save."
                );
            }
        });

        $("#runHistoryModal").on("click", "#runHistoryModalCancel", function() {
            loadRunHistoryTable("default");
            $("#runHistoryModalApply").removeAttr("info");
            $("#runHistoryModalBut").css("display", "none");
        });

        $("#runHistoryModal").on("click", "#runHistoryModalApply", function() {
            var type = $("#runHistoryModalApply").data("info");
            var selRows = $("#runHistoryTable")
                .DataTable()
                .rows({ selected: true })
                .data();
            var selRowsId = [];
            for (var i = 0; i < selRows.length; i++) {
                selRowsId.push(selRows[i].id);
            }
            var savedData = selRowsId;
            if (type == "delete") {
                if (selRowsId.length > 0) {
                    var text =
                        "Are you sure you want to delete " + selRowsId.length + " run(s)?";
                    var execFunc = function(savedData) {
                        getValuesAsync({
                                p: "removeRunLog",
                                type: "remove",
                                runlogs: selRowsId,
                                project_pipeline_id: project_pipeline_id,
                            },
                            async function(s) {
                                $("#runHistoryModalBut").css("display", "none");
                                loadRunHistoryTable("default");
                                await fillRunVerOpt("#runVerLog");
                            }
                        );
                    };
                    showConfirmDeleteModal(text, savedData, execFunc);
                }
            } else if (type == "purge") {
                if (selRowsId.length > 0) {
                    var text =
                        "Are you sure you want permanently delete selected " +
                        selRowsId.length +
                        " run(s)?";
                    var execFunc = function(savedData) {
                        getValuesAsync({
                                p: "removeRunLog",
                                type: "purge",
                                runlogs: selRowsId,
                                project_pipeline_id: project_pipeline_id,
                            },
                            function(s) {
                                $("#runHistoryModalBut").css("display", "none");
                                loadRunHistoryTable("default");
                            }
                        );
                    };
                    showConfirmDeleteModal(text, savedData, execFunc);
                }
            } else if (type == "recover") {
                if (selRowsId.length > 0) {
                    var text =
                        "Are you sure you want to recover selected " +
                        selRowsId.length +
                        " run(s)?";
                    var execFunc = function(savedData) {
                        getValuesAsync({
                                p: "removeRunLog",
                                type: "recover",
                                runlogs: selRowsId,
                                project_pipeline_id: project_pipeline_id,
                            },
                            async function(s) {
                                $("#runHistoryModalBut").css("display", "none");
                                loadRunHistoryTable("default");
                                await fillRunVerOpt("#runVerLog");
                            }
                        );
                    };
                    showConfirmDeleteModal(text, savedData, execFunc);
                }
            }
        });
    });



    $(function() {
        // Re-Execute initCompleteFunction when table draw is completed
        $(document).on("xhr.dt", "#sampleTable", function(e, settings, json, xhr) {
            new $.fn.dataTable.Api(settings).one("draw", function() {
                initCompleteFunction(settings, json);
            });
        });
        //Prevent BODY from scrolling when a modal is opened
        $("#inputFilemodal")
            .on("show.bs.modal", function() {
                $("body").css("overflow", "hidden");
                $("body").css("position", "fixed");
                $("body").css("width", "100%");
            })
            .on("hidden.bs.modal", function() {
                $("body").css("overflow", "hidden auto");
                $("body").css("position", "static");
            });

        $("#inputFilemodal").on("click", "#addSample", function(event) {
            event.preventDefault();
            if (proTypeWindow && proIdWindow) {
                $("#addFileModal").modal("show");
            } else {
                showInfoModal(
                    "#infoModal",
                    "#infoModalText",
                    "Please first select a run environment in the run page so that you can search files in the specified host."
                );
            }
        });

        $("#addFileModal").on("show.bs.modal", function() {
            $("#addFileModal").find("form").trigger("reset");
            $('.nav-tabs a[href="#hostFiles"]').tab("show");
            $("#viewDir").removeData("fileArr");
            $("#viewDir").removeData("fileDir");
            $("#viewDir").removeData("amzKey");
            $("#viewDir").removeData("googKey");
            fillArray2Select([], "#viewDir", true);
            resetPatternList();
            clearSelection();
            selectedGeoSamplesTable.fnClearTable();
            searchedGeoSamplesTable.fnClearTable();
            selectCloudKey();
            $(".forwardpatternDiv").css("display", "none");
            $(".reversepatternDiv").css("display", "none");
            $(".singlepatternDiv").css("display", "none");
            $(".r3patternDiv").css("display", "none");
            $(".r4patternDiv").css("display", "none");
            $(".patternButs").css("display", "none");
            $(".patternTable").css("display", "none");
            $("#viewDir").css("display", "none");
            $("#viewDirDiv").css("display", "none");
            $("#viewDir > option").attr("style", "pointer-events: auto;");
            $("#seaGeoSamplesDiv").css("display", "none");
            $("#selGeoSamplesDiv").css("display", "none");
            $("#mRunAmzKeyS3Div").css("display", "none");
            $("#mArchAmzKeyS3Div_GEO").css("display", "none");
            $("#mArchAmzKeyS3Div").css("display", "none");
            $("#file_dir_div").css("display", "block");
            $("#viewDirInfo").css("display", "block");
            selectizeCollection(["#collection_id", "#collection_id_geo"], "", false);
            //#uploadFiles tab:
            $("#target_dir").val($runscope.getUploadDir("new"));
            $("#pluploaderReset").trigger("click");

        });

        $("#viewDirBut").click(async function() {
            var dir = $("#file_dir").val();
            dir = $.trim(dir);
            await viewDirButSearch(dir);
        });

        $("#addFileModal").on("dblclick", "#viewDir option", async function() {
            var selectedOpt = $(this).val();
            var olddir = $("#file_dir").val();
            olddir = $.trim(olddir);
            var newdir = "";
            if (selectedOpt == "..") {
                var split = olddir.split("/");
                //if ends with /
                if (olddir.slice(-1) == "/") {
                    var finalPart = split[split.length - 2];
                    newdir = olddir.substring(0, olddir.indexOf(finalPart));
                } else {
                    var finalPart = split[split.length - 1];
                    newdir = olddir.substring(0, olddir.indexOf(finalPart));
                }
            } else {
                if (olddir.slice(-1) == "/") {
                    newdir = olddir + selectedOpt;
                } else {
                    newdir = olddir + "/" + selectedOpt;
                }
            }
            if (newdir) {
                $("#file_dir").val(newdir);
                await viewDirButSearch(newdir);
            }
        });

        removeSRA = function(name, srr_id, collection_type, button) {
            var row = $(button).closest("tr");
            selectedGeoSamplesTable.fnDeleteRow(row);
            selectedGeoSamplesTable.fnDraw();
            //check table data before adding.
            var select_button =
                '<button class="btn btn-primary pull-right" type= "button" id="' +
                srr_id +
                '_select" onclick="selectSRA(\'' +
                name +
                "','" +
                srr_id +
                "', '" +
                collection_type +
                "', this)\">Select</button>";
            //check table data before adding.
            var table_data = searchedGeoSamplesTable.fnGetData();
            var checkTableUniqueData = table_data.filter(function(el) {
                return el[0] == srr_id;
            });
            if (checkTableUniqueData.length == 0) {
                searchedGeoSamplesTable.fnAddData([
                    name,
                    srr_id,
                    collection_type,
                    select_button,
                ]);
            }
        };

        selectSRA = function(name, srr_id, collection_type, button) {
            var row = $(button).closest("tr");
            $("#searchedGeoSamples").DataTable().row(row).remove().draw(false);
            $("#selGeoSamplesDiv").css("display", "block");
            selectedGeoSamplesTable.fnAddData([
                '<input type="text" id="' +
                name +
                '" size="70" class="col-mid-12" onchange="updateNameTable(this)" value="' +
                name +
                '">',
                srr_id,
                collection_type,
                '<button class="btn btn-danger pull-right" id="' +
                srr_id +
                '_remove" onclick="removeSRA(\'' +
                name +
                "','" +
                srr_id +
                "', '" +
                collection_type +
                "', this)\">Remove</button>",
            ]);
        };

        selectAllSRA = function() {
            var table_nodes = searchedGeoSamplesTable.fnGetNodes();
            for (var x = 0; x < table_nodes.length; x++) {
                if (table_nodes[x].children[3].children[0].disabled == false) {
                    table_nodes[x].children[3].children[0].click();
                }
            }
        };

        //--GEO SEARCH STARTS--
        $(function() {
            function showGEOErrorModal(duplicateList, geoFailedList) {
                //true if modal is open
                $("#errGeoModal").off();
                $("#errGeoModal").on("show.bs.modal", function(event) {
                    $(this).find("form").trigger("reset");
                    var dup_text = "";
                    var dup_list = duplicateList.join(" ");
                    var err_text = "";
                    var err_list = geoFailedList.join(" ");
                    if (duplicateList.length > 0) {
                        $("#errGeoModalDupDiv").css("display", "block");
                        $("#errGeoModalDupText").html(
                            "Following geo terms already added into table."
                        );
                        $("#errGeoModalDupList").val(dup_list);
                    } else {
                        $("#errGeoModalDupDiv").css("display", "none");
                    }
                    if (geoFailedList.length > 0) {
                        $("#errGeoModalErrDiv").css("display", "block");
                        $("#errGeoModalErrText").html(
                            "Following " +
                            geoFailedList.length +
                            " geo terms could not be loaded. If you want to skip these items, please click 'ok' button, otherwise click 'retry' button to search those items again."
                        );
                        $("#errGeoModalErrList").removeData();
                        $("#errGeoModalErrList").val(err_list);
                        $("#errGeoModalErrList").data("geoFailedList", geoFailedList);
                        $("#retry_geo_search").css("display", "inline");
                    } else {
                        $("#errGeoModalErrDiv").css("display", "none");
                        $("#retry_geo_search").css("display", "none");
                    }
                });

                $("#errGeoModal").on("click", "#retry_geo_search", function(event) {
                    var geoFailList = $("#errGeoModalErrList").data("geoFailedList");
                    $("#errGeoModalErrList").removeData();
                    console.log(geoFailList);
                    var geoList = [];
                    var geoFailedList = [];
                    $("#viewGeoBut").data("searchList", geoFailList);
                    $("#viewGeoBut").data("searchIndex", 0);
                    $("#viewGeoBut").data("searchType", "retry");
                    $("#viewGeoBut").data("retryFailed", 2); //retry amount in case some queries fails
                    showLoadingDivText("viewGeoButDiv", "");
                    initGEOsearch(geoList, geoFailedList);
                });
                $("#errGeoModal").modal("show");
            }

            //check waitingList obj to find "waiting" calls
            var checkWaitingList = function(geoList, geoFailedList, count) {
                count++;
                var waitingList = $("#viewGeoBut").data("waitingList");
                var check = true;
                $.each(waitingList, function(el) {
                    if (waitingList[el] == "waiting") {
                        check = false;
                    }
                });
                if (check || count > 20) {
                    onCompleteCall(geoList, geoFailedList);
                } else {
                    setTimeout(function() {
                        console.log("wait for remaining queries");
                        checkWaitingList(geoList, geoFailedList, count);
                    }, 1000);
                }
            };

            var onCompleteCall = function(geoList, geoFailedListRaw) {
                var geoFailedList = geoFailedListRaw.filter(function(
                    elem,
                    index,
                    self
                ) {
                    return index === self.indexOf(elem);
                });
                console.log(geoList);
                console.log(geoFailedList);
                var searchList = $("#viewGeoBut").data("searchList");
                var searchIndex = $("#viewGeoBut").data("searchIndex");
                var searchType = $("#viewGeoBut").data("searchType");
                var retryFailed = $("#viewGeoBut").data("retryFailed");
                if (!retryFailed) {
                    retryFailed = 0;
                }

                searchIndex++;
                $("#viewGeoBut").data("searchIndex", searchIndex);
                if (typeof searchList === "undefined") {
                    return;
                }
                if (typeof searchIndex === "undefined") {
                    return;
                }
                if (searchList[searchIndex]) {
                    if (searchType == "retry") {
                        var percent = Math.floor((100 * searchIndex) / searchList.length);
                        if (percent < 100) {
                            showLoadingDivText("viewGeoButDiv", percent + "%");
                        }
                    }
                    initGEOsearch(geoList, geoFailedList);
                } else {
                    if (geoFailedList.length > 0 && retryFailed > 0) {
                        retryFailed--;
                        console.log("retryFailed", retryFailed);
                        $("#viewGeoBut").data("retryFailed", retryFailed);
                        $("#viewGeoBut").data("searchList", geoFailedList);
                        $("#viewGeoBut").data("searchIndex", 0);
                        $("#viewGeoBut").data("searchType", "retry");
                        showLoadingDivText("viewGeoButDiv", "");
                        var geoFailedList = [];
                        initGEOsearch(geoList, geoFailedList);
                    } else {
                        //oncomplete:
                        $("#viewGeoBut").removeData("searchList");
                        $("#viewGeoBut").removeData("searchIndex");
                        $("#seaGeoSamplesDiv").css("display", "block");
                        hideLoadingDiv("viewGeoButDiv");
                        //fill the table based on geoList
                        var duplicateList = [];
                        if (geoList.length) {
                            for (var i = 0; i < geoList.length; i++) {
                                if (geoList[i].srr_id && geoList[i].collection_type) {
                                    var name = geoList[i].srr_id;
                                    if (geoList[i].name) {
                                        name = geoList[i].name;
                                    }
                                    var srr_id = geoList[i].srr_id;
                                    var collection_type = geoList[i].collection_type;
                                    if (collection_type == "single") {
                                        collection_type = "Single";
                                    } else if (collection_type == "pair") {
                                        collection_type = "Paired";
                                    } else if (collection_type == "triple") {
                                        collection_type = "Triple";
                                    } else if (collection_type == "quadruple") {
                                        collection_type = "Quadruple";
                                    }
                                    var select_button =
                                        '<button class="btn btn-primary pull-right" type= "button" id="' +
                                        srr_id +
                                        '_select" onclick="selectSRA(\'' +
                                        name +
                                        "','" +
                                        srr_id +
                                        "', '" +
                                        collection_type +
                                        "', this)\">Select</button>";
                                    //check table data before adding.
                                    var selected_data = selectedGeoSamplesTable.fnGetData();
                                    var checkSelectedUniqueData = selected_data.filter(function(
                                        el
                                    ) {
                                        return el[1] == srr_id;
                                    });
                                    var table_data = searchedGeoSamplesTable.fnGetData();
                                    var checkTableUniqueData = table_data.filter(function(el) {
                                        return el[1] == srr_id;
                                    });
                                    if (
                                        checkTableUniqueData.length == 0 &&
                                        checkSelectedUniqueData.length == 0
                                    ) {
                                        searchedGeoSamplesTable.fnAddData([
                                            name,
                                            srr_id,
                                            collection_type,
                                            select_button,
                                        ]);
                                    } else {
                                        duplicateList.push(srr_id);
                                    }
                                }
                            }
                        }

                        if (geoList.length == 0 && geoFailedList.length == 0) {
                            showInfoModal(
                                "#infoModal",
                                "#infoModalText",
                                "There was an error in your GEO query. Search term cannot be found"
                            );
                        } else if (duplicateList.length > 0 || geoFailedList.length > 0) {
                            showGEOErrorModal(duplicateList, geoFailedList);
                        }
                    }
                }
            };
            var sraQuery = function(
                sraQueryList,
                sraQueInd,
                retstart,
                retmax,
                geoList,
                geoFailedList,
                queryDB,
                callback
            ) {
                var startTime = new Date();
                //execute nextQuery only once
                var nextQuery = (function() {
                    var executed = false;
                    return function() {
                        if (!executed) {
                            executed = true;
                            if (sraQueryList.length - 1 > sraQueInd) {
                                var newSraQueInd = sraQueInd + 1;
                                console.log(
                                    "sraQuery " + (newSraQueInd + 1) + "/" + sraQueryList.length,
                                    sraQueryList[newSraQueInd]
                                );
                                var percent = Math.floor(
                                    (100 * newSraQueInd) / sraQueryList.length
                                );
                                showLoadingDivText("viewGeoButDiv", percent + "%");
                                var endTime = new Date();
                                var timeDiff = endTime - startTime; //in ms
                                //3 query in 1000ms allowed by ncbi
                                var wait = 1000 - timeDiff;
                                if (wait < 1) {
                                    wait = 0;
                                }
                                setTimeout(function() {
                                    sraQuery(
                                        sraQueryList,
                                        newSraQueInd,
                                        retstart,
                                        retmax,
                                        geoList,
                                        geoFailedList,
                                        queryDB,
                                        callback
                                    );
                                }, wait);
                            }
                        }
                    };
                })();

                //execute endFunc only once
                var endFunc = (function() {
                    var executed = false;
                    return function() {
                        if (!executed) {
                            executed = true;
                            if (typeof callback === "function") {
                                callback(geoList);
                                //gds query
                            } else {
                                if (sraQueryList.length - 1 == sraQueInd) {
                                    checkWaitingList(geoList, geoFailedList, 0);
                                } else if (sraQueryList.length - 1 > sraQueInd) {
                                    nextQuery();
                                }
                            }
                        }
                    };
                })();

                var geo_id = sraQueryList[sraQueInd];
                var searchURL =
                    "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=sra&usehistory=y&retmode=json&term=" +
                    geo_id;
                var succCheck1 = false;
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: { p: "getRemoteData", url: searchURL },
                    async: true,
                    error: function(jqXHR, exception) {
                        reportAjaxError(jqXHR, exception, searchURL);
                    },
                    success: function(jsonText) {
                        console.log(geo_id);
                        var deferreds = [];
                        var deferredsRes = [];
                        var deferredsData = [];
                        if (IsJsonString(jsonText)) {
                            var res = JSON.parse(jsonText);
                            console.log(res);
                            if (res) {
                                if (res.esearchresult) {
                                    if (res.esearchresult.webenv && res.esearchresult.querykey) {
                                        var webenv = res.esearchresult.webenv;
                                        var querykey = res.esearchresult.querykey;
                                        var resultsURL =
                                            "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=sra&retmode=json&query_key=" +
                                            querykey +
                                            "&WebEnv=" +
                                            webenv +
                                            "&retstart=" +
                                            retstart +
                                            "&retmax=" +
                                            retmax;
                                        console.log(resultsURL);
                                        succCheck1 = true;
                                        var succCheck2 = false;
                                        $.ajax({
                                            type: "POST",
                                            url: "ajax/ajaxquery.php",
                                            data: { p: "getRemoteData", url: resultsURL },
                                            async: true,
                                            error: function(jqXHR, exception) {
                                                reportAjaxError(jqXHR, exception, resultsURL);
                                            },
                                            success: function(jsonText2) {
                                                nextQuery();
                                                if (IsJsonString(jsonText2)) {
                                                    var res2 = JSON.parse(jsonText2);
                                                    console.log(res2);
                                                    if (res2) {
                                                        if (res2.result) {
                                                            var res2_res = res2.result;
                                                            var k = 0;
                                                            var ajaxcount = 0;
                                                            $.each(res2_res, function(el) {
                                                                if (res2_res[el]["expxml"]) {
                                                                    var expJSON = xmlStringToJson(
                                                                        res2_res[el]["expxml"]
                                                                    );
                                                                    var runsJSON = xmlStringToJson(
                                                                        res2_res[el]["runs"]
                                                                    );
                                                                    if (expJSON.Summary && runsJSON.Run) {
                                                                        // If only one SRR, make it an array
                                                                        if (!$.isArray(runsJSON.Run)) {
                                                                            runsJSON.Run = [runsJSON.Run];
                                                                        }
                                                                        for (
                                                                            var i = 0; i < runsJSON.Run.length; i++
                                                                        ) {
                                                                            var collectionType = "";
                                                                            if (
                                                                                expJSON.Library_descriptor &&
                                                                                expJSON.Library_descriptor
                                                                                .LIBRARY_LAYOUT
                                                                            ) {
                                                                                console.log(
                                                                                    expJSON.Library_descriptor
                                                                                    .LIBRARY_LAYOUT
                                                                                );
                                                                                if (
                                                                                    expJSON.Library_descriptor
                                                                                    .LIBRARY_LAYOUT.PAIRED
                                                                                ) {
                                                                                    collectionType = "pair";
                                                                                } else {
                                                                                    collectionType = "single";
                                                                                }
                                                                            }
                                                                            console.log(expJSON.Summary.Title);
                                                                            console.log(runsJSON.Run[i].attributes);
                                                                            if (
                                                                                expJSON.Summary.Title &&
                                                                                runsJSON.Run[i].attributes
                                                                            ) {
                                                                                if (
                                                                                    expJSON.Summary.Title.text &&
                                                                                    runsJSON.Run[i].attributes.acc
                                                                                ) {
                                                                                    var srr_id =
                                                                                        runsJSON.Run[i].attributes.acc;
                                                                                    var sra_clean =
                                                                                        expJSON.Summary.Title.text
                                                                                        .replace(/[^a-z0-9\._\-]/gi, "_")
                                                                                        .replace(/_+/g, "_");
                                                                                    console.log(srr_id);
                                                                                    if (srr_id.match(/SRR/i)) {
                                                                                        k++;
                                                                                        var searchENAUrl =
                                                                                            "https://www.ebi.ac.uk/ena/portal/api/filereport?result=read_run&fields=fastq_ftp&format=JSON&accession=" +
                                                                                            srr_id;
                                                                                        deferredsData.push({
                                                                                            srr_id: srr_id,
                                                                                            name: sra_clean,
                                                                                            collectionType: collectionType,
                                                                                        });
                                                                                        var waitingList =
                                                                                            $("#viewGeoBut").data(
                                                                                                "waitingList"
                                                                                            );
                                                                                        if (!waitingList) {
                                                                                            waitingList = {};
                                                                                        }
                                                                                        waitingList[srr_id] = "waiting";
                                                                                        $("#viewGeoBut").data(
                                                                                            "waitingList",
                                                                                            waitingList
                                                                                        );

                                                                                        succCheck2 = true;
                                                                                        deferreds.push(
                                                                                            $.ajax({
                                                                                                url: searchENAUrl,
                                                                                                async: true,
                                                                                                complete: function() {
                                                                                                    ajaxcount++;
                                                                                                    if (queryDB == "sra") {
                                                                                                        var percent = Math.floor(
                                                                                                            (100 * ajaxcount) / k
                                                                                                        );
                                                                                                        if (percent < 100) {
                                                                                                            showLoadingDivText(
                                                                                                                "viewGeoButDiv",
                                                                                                                percent + "%"
                                                                                                            );
                                                                                                        }
                                                                                                    }
                                                                                                },
                                                                                                type: "GET",
                                                                                                success: function(res) {
                                                                                                    console.log("**", res);
                                                                                                    deferredsRes.push(res);
                                                                                                },
                                                                                                error: function(
                                                                                                    jqXHR,
                                                                                                    exception
                                                                                                ) {
                                                                                                    if (
                                                                                                        typeof callback !==
                                                                                                        "function"
                                                                                                    ) {
                                                                                                        console.log(
                                                                                                            "FAILED:" + geo_id
                                                                                                        );
                                                                                                        geoFailedList.push(geo_id);
                                                                                                    }
                                                                                                    reportAjaxError(
                                                                                                        jqXHR,
                                                                                                        exception,
                                                                                                        searchENAUrl
                                                                                                    );
                                                                                                },
                                                                                            })
                                                                                        );
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            });
                                                        }
                                                    }
                                                }
                                                //wait for all async calls to finish
                                                $.when.apply($, deferreds).always(function() {
                                                    for (var i = 0; i < deferredsRes.length; i++) {
                                                        var succCheck3 = false;
                                                        var collectionType = "";
                                                        if (deferredsRes[i] && deferredsRes[i][0]) {
                                                            var fastq_ftp = deferredsRes[i][0].fastq_ftp;
                                                            if (fastq_ftp) {
                                                                if (fastq_ftp.match(/;/)) {
                                                                    collectionType = "pair";
                                                                } else {
                                                                    collectionType = "single";
                                                                }
                                                                if (collectionType) {
                                                                    succCheck3 = true;
                                                                    var waitingList =
                                                                        $("#viewGeoBut").data("waitingList");
                                                                    if (waitingList) {
                                                                        waitingList[deferredsData[i].srr_id] =
                                                                            "done";
                                                                    }
                                                                    $("#viewGeoBut").data(
                                                                        "waitingList",
                                                                        waitingList
                                                                    );
                                                                    geoList.push({
                                                                        srr_id: deferredsData[i].srr_id,
                                                                        collection_type: collectionType,
                                                                        name: deferredsData[i].name,
                                                                    });
                                                                }
                                                            }
                                                        }
                                                        if (!succCheck3) {
                                                            if (deferredsData[i].collectionType) {
                                                                console.log(
                                                                    "ncbi layout will be used.",
                                                                    deferredsData[i].collectionType
                                                                );
                                                                var waitingList =
                                                                    $("#viewGeoBut").data("waitingList");
                                                                if (waitingList) {
                                                                    waitingList[deferredsData[i].srr_id] = "done";
                                                                }
                                                                $("#viewGeoBut").data(
                                                                    "waitingList",
                                                                    waitingList
                                                                );
                                                                geoList.push({
                                                                    srr_id: deferredsData[i].srr_id,
                                                                    collection_type: deferredsData[i].collectionType,
                                                                    name: deferredsData[i].name,
                                                                });
                                                            }
                                                            if (typeof callback !== "function") {
                                                                geoFailedList.push(deferredsData[i].srr_id);
                                                            }
                                                        }
                                                    }
                                                    if (deferredsRes.length < 1) {
                                                        if (typeof callback !== "function") {
                                                            if (!geo_id.match("GPL") &&
                                                                !geo_id.match("GSE")
                                                            ) {
                                                                geoFailedList.push(geo_id);
                                                            }
                                                        }
                                                    }
                                                    console.log("endcheck1:" + geo_id);
                                                    console.log("geoList:" + geoList);
                                                    endFunc(callback, geoList, geoFailedList);
                                                });
                                                if (succCheck1 && !succCheck2) {
                                                    if (typeof callback !== "function") {
                                                        if (!geo_id.match("GPL") && !geo_id.match("GSE")) {
                                                            geoFailedList.push(geo_id);
                                                        }
                                                    }
                                                    console.log("endcheck2:" + geo_id);
                                                    endFunc(callback, geoList, geoFailedList);
                                                }
                                            },
                                        });
                                    }
                                }
                            }
                        }
                        if (!succCheck1) {
                            if (typeof callback !== "function") {
                                if (!geo_id.match("GPL") && !geo_id.match("GSE")) {
                                    geoFailedList.push(geo_id);
                                }
                            }
                            console.log("endcheck3:" + geo_id);
                            endFunc(callback, geoList, geoFailedList);
                        }
                    },
                });
            };

            var gdsQuery = function(
                geo_id,
                retstart,
                retmax,
                geoList,
                geoFailedList,
                queryDB
            ) {
                console.log("gdsQuery");
                var searchURL =
                    "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=gds&usehistory=y&retmode=json&term=" +
                    geo_id;
                var res = apiCallUrl(searchURL);
                var reachToLastLevel = false;
                console.log("Geo id: ", geo_id);
                console.log(searchURL);
                console.log(res);
                if (res) {
                    if (res.esearchresult) {
                        if (res.esearchresult.webenv && res.esearchresult.querykey) {
                            var webenv = res.esearchresult.webenv;
                            var querykey = res.esearchresult.querykey;
                            var resultsURL =
                                "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?db=sra&retmode=json&query_key=" +
                                querykey +
                                "&WebEnv=" +
                                webenv +
                                "&retstart=" +
                                retstart +
                                "&retmax=" +
                                retmax;
                            var res2 = apiCallUrl(resultsURL);
                            console.log(res2);
                            if (res2) {
                                if (res2.result) {
                                    var res2_res = res2.result;
                                    var sraQueryList = [];
                                    $.each(res2_res, function(el) {
                                        if (res2_res[el]["accession"] && res2_res[el]["title"]) {
                                            sraQueryList.push(res2_res[el]["accession"]);
                                            reachToLastLevel = true;
                                        }
                                    });
                                    var sraQueInd = 0;
                                    sraQuery(
                                        sraQueryList,
                                        sraQueInd,
                                        retstart,
                                        retmax,
                                        geoList,
                                        geoFailedList,
                                        queryDB,
                                        ""
                                    );
                                }
                            } else {
                                console.log("FAILED:" + geo_id);
                                if (!geo_id.match("GPL") && !geo_id.match("GSE")) {
                                    geoFailedList.push(geo_id);
                                }
                            }
                        }
                    }
                } else {
                    console.log("FAILED:" + geo_id);
                    if (!geo_id.match("GPL") && !geo_id.match("GSE")) {
                        geoFailedList.push(geo_id);
                    }
                }
                if (!reachToLastLevel) {
                    console.log(geoList);
                    checkWaitingList(geoList, geoFailedList, 0);
                }
            };

            function initGEOsearch(geoList, geoFailedList) {
                var searchList = $("#viewGeoBut").data("searchList");
                var searchIndex = $("#viewGeoBut").data("searchIndex");
                var searchType = $("#viewGeoBut").data("searchType");
                if (typeof searchList === "undefined") {
                    return;
                }
                if (typeof searchIndex === "undefined") {
                    return;
                }
                var geo_id = searchList[searchIndex];
                if (geo_id) {
                    //onstart:
                    if (searchType != "retry") {
                        showLoadingDivText("viewGeoButDiv", "");
                    }
                    var retmax = 2000;
                    var retstart = 0;
                    //show the precent complete based on queryDB
                    // if queryDB equals to sra show it inside sraQuery function
                    // if queryDB equals to gds show it inside gdsQuery function
                    var callback = function(geoList) {
                        if (geoList.length == 0) {
                            var queryDB = "gds";
                            setTimeout(function() {
                                gdsQuery(
                                    geo_id,
                                    retstart,
                                    retmax,
                                    geoList,
                                    geoFailedList,
                                    queryDB
                                );
                            }, 1000);
                        } else {
                            setTimeout(function() {
                                checkWaitingList(geoList, geoFailedList, 0);
                            }, 1000);
                        }
                    };

                    var queryDB = "sra";
                    var sraQueryList = [geo_id];
                    var sraQueryIndex = 0;
                    sraQuery(
                        sraQueryList,
                        sraQueryIndex,
                        retstart,
                        retmax,
                        geoList,
                        geoFailedList,
                        queryDB,
                        callback
                    );
                }
            }

            //ex. GSM1331276
            //GSE30567(205 sample, sra)
            //GSE65774(208sample, gds)
            //GSE78274(96sample, gds)
            //GSE55190(24sample gds)
            //ERP009109 PRJEB8073
            //SRR10095965
            //
            $("#viewGeoBut").click(function() {
                var geo_id = $("#geo_id").val();
                if (geo_id) {
                    var geo_id = geo_id.replace(/,/g, " ");
                    var rawSearchList = geo_id.split(" ");
                    var searchList = rawSearchList.filter(Boolean);
                    $("#viewGeoBut").data("searchList", searchList);
                    $("#viewGeoBut").data("searchIndex", 0);
                    $("#viewGeoBut").data("searchType", "default");
                    $("#viewGeoBut").data("retryFailed", 2);
                    var geoList = [];
                    var geoFailedList = [];
                    initGEOsearch(geoList, geoFailedList);
                }
            });
        });
        //--GEO SEARCH ENDS--

        $("#addFileModal").on("click", "#mSaveFiles", async function(event) {
            event.preventDefault();
            var checkTab = $("#addFileModal")
                .find(".active.tab-pane")[0]
                .getAttribute("id");
            var warnUser = false;
            if (checkTab === "hostFiles") {
                var formValues = $("#hostFiles").find("input, select");
                var requiredFields = ["file_dir", "collection_type", "collection_id"];
                var ret = {};
                var infoModalText = "";
                ret = getTableSamples("selectedSamplesTable");
                var rowData = selectedSamplesTable.fnGetData();
                var fileDirArr = [];
                for (var i = 0; i < rowData.length; i++) {
                    var file_dir = rowData[i][2];
                    var amzKey = rowData[i][4];
                    var googKey = rowData[i][5];
                    if (file_dir.match(/s3:/i)) {
                        file_dir = file_dir + "\t" + amzKey;
                    }
                    if (file_dir.match(/gs:/i)) {
                        file_dir = file_dir + "\t" + googKey;
                    }
                    fileDirArr.push(file_dir);
                }

                if (ret.warnUser) {
                    infoModalText += ret.warnUser;
                }
                if (!ret.file_array.length) {
                    infoModalText +=
                        " * Please fill table by clicking 'Add All Files' or 'Add Selected Files' buttons.\n";
                }
                var s3_archive_dir = $.trim($("#s3_archive_dir").val());
                var amzArchKey = $("#mArchAmzKeyS3").val();
                if (!warnUser && s3_archive_dir.match(/s3:/i)) {
                    if (!amzArchKey) {
                        infoModalText +=
                            " * Please select Amazon Archive Keys to save files into your S3 storage.\n";
                        warnUser = true;
                    }
                }
                var gs_archive_dir = $.trim($("#gs_archive_dir").val());
                var googArchKey = $("#mArchGoogKeyGS").val();
                if (!warnUser && gs_archive_dir.match(/gs:/i)) {
                    if (!googArchKey) {
                        infoModalText +=
                            " * Please select Google Archive Keys to save files into your Google storage.\n";
                        warnUser = true;
                    }
                }

                if (infoModalText) {
                    showInfoModal("#infoModal", "#infoModalText", infoModalText);
                }

                var formObj = {};
                var stop = "";
                [formObj, stop] = createFormObj(formValues, requiredFields);
                if (
                    stop === false &&
                    !ret.warnUser &&
                    ret.file_array.length &&
                    !warnUser
                ) {
                    //new items come with prefix: _newItm_
                    var collection_name = $("#collection_id")[0].selectize.getItem(
                        formObj.collection_id
                    )[0].innerHTML;
                    collection_name = cleanSpecChar(collection_name);
                    if (formObj.collection_id.match(/^_newItm_(.*)/)) {
                        var collection_data = await doAjax({
                            p: "saveCollection",
                            name: collection_name,
                        });
                        if (collection_data.id) {
                            formObj.collection_id = collection_data.id;
                        }
                    }
                    formObj.file_dir = JSON.stringify(fileDirArr);
                    if (s3_archive_dir.match(/s3:/i)) {
                        s3_archive_dir = $.trim(s3_archive_dir);
                        formObj.s3_archive_dir = s3_archive_dir + "\t" + amzArchKey;
                    }
                    if (gs_archive_dir.match(/gs:/i)) {
                        gs_archive_dir = $.trim(gs_archive_dir);
                        formObj.gs_archive_dir = gs_archive_dir + "\t" + googArchKey;
                    }
                    formObj.archive_dir = $.trim(formObj.archive_dir)
                    formObj.file_array = JSON.stringify(ret.file_array);
                    formObj.run_env = $("#chooseEnv").find(":selected").val();
                    formObj.project_id = project_id;
                    formObj.p = "saveFile";
                    console.log(formObj);
                    $.ajax({
                        type: "POST",
                        url: "ajax/ajaxquery.php",
                        data: formObj,
                        async: true,
                        success: function(s) {
                            if (s.length) {
                                $("#sampleTable").data("select", [collection_name]);
                                $("#sampleTable").DataTable().ajax.reload(null, false);
                                $("#addFileModal").modal("hide");
                            }
                        },
                        error: function(errorThrown) {
                            alert("Error: " + errorThrown);
                        },
                    });
                }
            } else if (checkTab === "geoFiles") {
                var formValues = $("#geoFiles").find("input, select");
                var requiredFields = ["collection_id"];
                var ret = {};
                var infoModalText = "";
                ret = getTableSamples("selectedGeoSamplesTable");
                if (ret.warnUser) {
                    infoModalText += ret.warnUser;
                }
                if (!ret.file_array.length) {
                    infoModalText +=
                        " * Please fill 'Selected GEO Files' table by clicking 'Select' buttons in the 'Searched GEO Files' table.\n";
                }
                var s3_archive_dir_geo = $.trim($("#s3_archive_dir_geo").val());
                var amzArchKey = $("#mArchAmzKeyS3_GEO").val();
                //                if (s3_archive_dir_geo.match(/s3:/i)) {
                //                    if (!amzArchKey) {
                //                        infoModalText +=
                //                            " * Please select Amazon Keys to save files into your S3 storage.\n";
                //                        warnUser = true;
                //                    }
                //                }
                var gs_archive_dir_geo = $.trim($("#gs_archive_dir_geo").val());
                var googArchKey = $("#mArchGoogKeyGS_GEO").val();
                if (gs_archive_dir_geo.match(/gs:/i)) {
                    if (!googArchKey) {
                        infoModalText +=
                            " * Please select Google Keys to save files into your Google storage.\n";
                        warnUser = true;
                    }
                }
                if (infoModalText) {
                    showInfoModal("#infoModal", "#infoModalText", infoModalText);
                }

                var formObj = {};
                var stop = "";
                [formObj, stop] = createFormObj(formValues, requiredFields);
                if (
                    stop === false &&
                    !ret.warnUser &&
                    ret.file_array.length &&
                    !warnUser
                ) {
                    //new items come with prefix: _newItm_
                    var collection_name = $("#collection_id_geo")[0].selectize.getItem(
                        formObj.collection_id
                    )[0].innerHTML;
                    if (formObj.collection_id.match(/^_newItm_(.*)/)) {
                        var collection_data = await doAjax({
                            p: "saveCollection",
                            name: collection_name,
                        });
                        if (collection_data.id) {
                            formObj.collection_id = collection_data.id;
                        }
                    }
                    formObj.file_type = "fastq";
                    var collection_type = selectedGeoSamplesTable.fnGetData();
                    if (collection_type[0][2] == "Paired") {
                        formObj.collection_type = "pair";
                    } else if (collection_type[0][2] == "Single") {
                        formObj.collection_type = "single";
                    }
                    if (s3_archive_dir_geo.match(/s3:/i)) {
                        formObj.s3_archive_dir = s3_archive_dir_geo + "\t" + amzArchKey;
                    }
                    if (gs_archive_dir_geo.match(/gs:/i)) {
                        formObj.gs_archive_dir = gs_archive_dir_geo + "\t" + googArchKey;
                    }
                    formObj.archive_dir = $.trim(formObj.archive_dir);
                    formObj.file_array = JSON.stringify(ret.file_array);
                    formObj.run_env = $("#chooseEnv").find(":selected").val();
                    formObj.project_id = project_id;
                    formObj.p = "saveFile";
                    console.log(formObj);
                    $.ajax({
                        type: "POST",
                        url: "ajax/ajaxquery.php",
                        data: formObj,
                        async: true,
                        success: function(s) {
                            if (s.length) {
                                $("#sampleTable").data("select", [collection_name]);
                                $("#sampleTable").DataTable().ajax.reload(null, false);
                                $("#addFileModal").modal("hide");
                            }
                        },
                        error: function(errorThrown) {
                            alert("Error: " + errorThrown);
                        },
                    });
                }
            }
        });
    });

    createMultiselect = function(id, columnToSearch, apiColumn) {
        $(id).multiselect({
            maxHeight: 500,
            enableFiltering: true,
            enableCaseInsensitiveFiltering: true,
            includeResetOption: true,
            resetText: "Clear filters",
            includeResetDivider: true,
            buttonText: function(options, select) {
                if (options.length == 0) {
                    return select.attr("name") + ": All";
                } else if (options.length > 2) {
                    return select.attr("name") + ": " + options.length + " selected";
                } else {
                    var labels = [];
                    options.each(function() {
                        labels.push($(this).text());
                    });
                    return select.attr("name") + ": " + labels.join(", ") + "";
                }
            },
        });
    };

    createMultiselectBinder = function(id) {
        var resetBut = $(id).find("a.btn-block");
        resetBut.click(function() {
            $($(id).find("input")[0]).trigger("change");
        });
    };

    var buildSampleTable = async function(cb) {
        if (!$.fn.DataTable.isDataTable("#sampleTable")) {
            $("#sampleTable").DataTable({
                dom: '<"#searchBarST.pull-left"f>rt<"pull-left"i><"bottom"p><"clear">',
                destroy: true,
                ajax: {
                    url: "ajax/ajaxquery.php",
                    data: { p: "getFile" },
                    dataSrc: "",
                },
                hover: true,
                columns: [{
                        data: "id",
                        checkboxes: {
                            targets: 0,
                            selectRow: true,
                        },
                    },
                    {
                        data: "name",
                    },
                    {
                        data: "collection_name",
                    },
                    {
                        data: "run_env",
                    },
                    {
                        data: "project_name",
                    },
                    {
                        data: "date_created",
                    },
                    {
                        data: null,
                        fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
                            $(nTd).html(
                                '<button type="button" class="btn btn-default btn-sm singleEditSample"> Edit</button><button style="margin-left:2px;" type="button" class="btn btn-default btn-sm showDetailSample"> Details</button>'
                            );
                        },
                    },
                ],
                select: {
                    style: "multi",
                    selector: "td:not(.no_select_row)",
                },
                order: [
                    [3, "desc"]
                ],
                columnDefs: [{
                        targets: [3, 4],
                        className: "disp_none",
                    },
                    {
                        targets: [6],
                        className: "no_select_row",
                    },
                ],
            });
            $("#sampleTable").on("init.dt", async function() {
                await cb();
            });
        } else {
            await cb();
        }
    };

    selectedSamplesTable = $("#selectedSamples").dataTable({
        sScrollX: "100%",
        columnDefs: [{
            targets: [4, 5],
            visible: false,
        }, ],
    });
    selectedGeoSamplesTable = $("#selectedGeoSamples").dataTable({
        sScrollX: "100%",
    });
    searchedGeoSamplesTable = $("#searchedGeoSamples").dataTable({
        sScrollX: "100%",
    });

    // show file details if one file is selected
    $("#sampleTable").on(
        "select.dt deselect.dt",
        function(e, dt, type, indexes) {
            var selectedRows = $("#sampleTable")
                .DataTable()
                .rows({ selected: true })
                .data();
            if (selectedRows.length > 1) {
                $("#editSample").css("display", "inline-block");
            } else {
                $("#editSample").css("display", "none");
            }
            if (selectedRows.length > 0) {
                $("#deleteSample").css("display", "inline-block");
            } else {
                $("#deleteSample").css("display", "none");
            }
        }
    );

    $(function() {
        $(document).on("change", "#collection_type", function() {
            var collection_type = $(this).val();
            if (collection_type == "pair") {
                $(".r3patternDiv").css("display", "none");
                $(".r4patternDiv").css("display", "none");
                $(".forwardpatternDiv").css("display", "inline");
                $(".reversepatternDiv").css("display", "inline");
                $(".singlepatternDiv").css("display", "none");
                $(".patternButs").css("display", "inline");
                $(".patternTable").css("display", "inline");
                $("#forward_pattern").trigger("keyup");
                $("#reverse_pattern").trigger("keyup");
            } else if (collection_type == "single") {
                $(".r3patternDiv").css("display", "none");
                $(".r4patternDiv").css("display", "none");
                $(".patternButs").css("display", "inline");
                $(".patternTable").css("display", "inline");
                $(".singlepatternDiv").css("display", "inline");
                $(".forwardpatternDiv").css("display", "none");
                $(".reversepatternDiv").css("display", "none");
                $("#single_pattern").trigger("keyup");
            } else if (collection_type == "triple") {
                $(".r3patternDiv").css("display", "inline");
                $(".r4patternDiv").css("display", "none");
                $(".forwardpatternDiv").css("display", "inline");
                $(".reversepatternDiv").css("display", "inline");
                $(".singlepatternDiv").css("display", "none");
                $(".patternButs").css("display", "inline");
                $(".patternTable").css("display", "inline");
                $("#forward_pattern").trigger("keyup");
                $("#reverse_pattern").trigger("keyup");
                $("#r3_pattern").trigger("keyup");
            } else if (collection_type == "quadruple") {
                $(".r3patternDiv").css("display", "inline");
                $(".r4patternDiv").css("display", "inline");
                $(".forwardpatternDiv").css("display", "inline");
                $(".reversepatternDiv").css("display", "inline");
                $(".singlepatternDiv").css("display", "none");
                $(".patternButs").css("display", "inline");
                $(".patternTable").css("display", "inline");
                $("#forward_pattern").trigger("keyup");
                $("#reverse_pattern").trigger("keyup");
                $("#r3_pattern").trigger("keyup");
                $("#r4_pattern").trigger("keyup");
            }
        });

        var syncSelectedOpts = function(allItems, selItems, targetID) {
            var otherOpt = $(targetID + " > option");
            otherOpt.prop("selected", false);
            for (var i = 0; i < selItems.length; i++) {
                var order = allItems.indexOf(selItems[i]);
                if (otherOpt[order]) {
                    $(otherOpt[order]).prop("selected", true);
                }
            }
        };
        $(document).on("click", "#forwardList", function() {
            var allItems = [];
            $("#forwardList > option").each(function() {
                allItems.push(this.value);
            });
            var selItems = $("#forwardList").val();
            syncSelectedOpts(allItems, selItems, "#reverseList");
            syncSelectedOpts(allItems, selItems, "#r3List");
            syncSelectedOpts(allItems, selItems, "#r4List");
        });
        $(document).on("click", "#reverseList", function() {
            var allItems = [];
            $("#reverseList > option").each(function() {
                allItems.push(this.value);
            });
            var selItems = $("#reverseList").val();
            syncSelectedOpts(allItems, selItems, "#forwardList");
            syncSelectedOpts(allItems, selItems, "#r3List");
            syncSelectedOpts(allItems, selItems, "#r4List");
        });
        $(document).on("click", "#r3List", function() {
            var allItems = [];
            $("#r3List > option").each(function() {
                allItems.push(this.value);
            });
            var selItems = $("#r3List").val();
            syncSelectedOpts(allItems, selItems, "#forwardList");
            syncSelectedOpts(allItems, selItems, "#reverseList");
            syncSelectedOpts(allItems, selItems, "#r4List");
        });
        $(document).on("click", "#r4List", function() {
            var allItems = [];
            $("#r4List > option").each(function() {
                allItems.push(this.value);
            });
            var selItems = $("#r4List").val();
            syncSelectedOpts(allItems, selItems, "#forwardList");
            syncSelectedOpts(allItems, selItems, "#reverseList");
            syncSelectedOpts(allItems, selItems, "#r3List");
        });
    });

    var syncRegexOptions = function(collection_type, optsObjs) {
        var data = [];
        $.each(optsObjs, function(el) {
            if (optsObjs[el]) {
                optsObjs[el] = optsObjs[el].sort();
            }
        });
        var regexObj = $.extend(true, {}, optsObjs);
        // clean regex pattern from array options
        $.each(regexObj, function(el) {
            if (regexObj[el]) {
                var pattern = "";
                if (el == "#forwardList") {
                    pattern = $("#forward_pattern").val();
                } else if (el == "#reverseList") {
                    pattern = $("#reverse_pattern").val();
                } else if (el == "#r3List") {
                    pattern = $("#r3_pattern").val();
                } else if (el == "#r4List") {
                    pattern = $("#r4_pattern").val();
                }
                for (var i = 0; i < regexObj[el].length; i++) {
                    regexObj[el][i] = regexObj[el][i].split(pattern).join("");
                }
            }
        });
        if (collection_type == "pair") {
            data = [regexObj["#forwardList"], regexObj["#reverseList"]];
        } else if (collection_type == "triple") {
            data = [
                regexObj["#forwardList"],
                regexObj["#reverseList"],
                regexObj["#r3List"],
            ];
        } else if (collection_type == "quadruple") {
            data = [
                regexObj["#forwardList"],
                regexObj["#reverseList"],
                regexObj["#r3List"],
                regexObj["#r4List"],
            ];
        }

        var intersection = data.reduce((a, b) => a.filter((c) => b.includes(c)));
        var reorderObj = {};
        var reorderCheck = false; // if one item is reordered than enable this
        $.each(regexObj, function(el) {
            if (!reorderObj[el]) reorderObj[el] = [];
            var pushLast = [];
            for (var i = 0; i < regexObj[el].length; i++) {
                var index = intersection.indexOf(regexObj[el][i]);
                var item = optsObjs[el][i];
                if (index < 0) {
                    pushLast.push(item);
                    reorderCheck = true;
                } else {
                    reorderObj[el].push(item);
                }
            }
            if (pushLast.length) reorderObj[el] = reorderObj[el].concat(pushLast);
        });
        console.log(reorderObj);
        if (reorderCheck) {
            $.each(reorderObj, function(el) {
                fillArray2Select(reorderObj[el], el, true);
            });
        }
        return reorderCheck;
    };

    function updateFileArea(selectId, pattern) {
        var fileOrj = $("#viewDir").data("fileArr");
        if (fileOrj) {
            var fileAr = fileOrj.slice(); //clone list
            var delArr = $(selectId).data("samples");
            // files that are selected are kept in delArr and removed before loading fillArray2Select
            if (delArr && delArr.length) {
                for (var i = 0; i < delArr.length; i++) {
                    var index = fileAr.indexOf(delArr[i]);
                    if (index > -1) {
                        fileAr.splice(index, 1);
                    }
                }
            }
            console.log("fileAr", fileAr);
            if (fileAr) {
                // keeps $ and ^ for regex
                var cleanRegEx = function(pat) {
                    return pat.replace(/[-\/\\*+?.()|[\]{}]/g, "\\$&");
                };
                var patternReg = cleanRegEx(pattern);
                var reg = new RegExp(patternReg);
                var filteredAr = fileAr.filter((line) => line.match(reg));
                if (filteredAr.length > 0) {
                    //xxxxxxxxxxxx
                    var reorderCheck = false;
                    var collection_type = $("#collection_type").val();
                    if (collection_type != "single") {
                        var syncOtherFieldOptions = function(
                            collection_type,
                            selectId,
                            filteredAr
                        ) {
                            var fieldsArray = [];
                            var optsObjs = {};
                            optsObjs[selectId] = filteredAr;
                            if (collection_type == "pair") {
                                fieldsArray = ["#forwardList", "#reverseList"];
                            } else if (collection_type == "triple") {
                                fieldsArray = ["#forwardList", "#reverseList", "#r3List"];
                            } else if (collection_type == "quadruple") {
                                fieldsArray = [
                                    "#forwardList",
                                    "#reverseList",
                                    "#r3List",
                                    "#r4List",
                                ];
                            }
                            var index = fieldsArray.indexOf(selectId);
                            fieldsArray.splice(index, 1);
                            for (var i = 0; i < fieldsArray.length; i++) {
                                var allopts = [];
                                $(fieldsArray[i] + " > option").each(function() {
                                    if (!this.value.match(/no file/i)) allopts.push(this.value);
                                });
                                optsObjs[fieldsArray[i]] = allopts;
                            }
                            // return the latest options after update (includes latest filteredAr)
                            // {#forwardList: ["test.R1.fastq"], #r3List:["test.R3.fastq"]}
                            console.log(optsObjs);
                            var forwardList = optsObjs["#forwardList"];
                            var reverseList = optsObjs["#reverseList"];
                            var r3List = optsObjs["#r3List"];
                            var r4List = optsObjs["#r4List"];
                            if (
                                (collection_type == "pair" &&
                                    forwardList.length &&
                                    reverseList.length) ||
                                (collection_type == "triple" &&
                                    forwardList.length &&
                                    reverseList.length &&
                                    r3List.length) ||
                                (collection_type == "quadruple" &&
                                    forwardList.length &&
                                    reverseList.length &&
                                    r3List.length &&
                                    r4List.length)
                            ) {
                                reorderCheck = syncRegexOptions(collection_type, optsObjs);
                            }
                        };
                        syncOtherFieldOptions(collection_type, selectId, filteredAr);
                    }
                    if (!reorderCheck) fillArray2Select(filteredAr, selectId, true);
                } else {
                    fillArray2Select(["No files matching the pattern."], selectId, true);
                }
            } else {
                fillArray2Select(["No files matching the pattern."], selectId, true);
            }
        }
    }
    window.timeoutID = {};
    window.timeoutID["#forward_pattern"] = 0;
    window.timeoutID["#reverse_pattern"] = 0;
    window.timeoutID["#r3_pattern"] = 0;
    window.timeoutID["#r4_pattern"] = 0;
    window.timeoutID["#single_pattern"] = 0;

    function updateFileList(selectId, pattern) {
        if (window.timeoutID[selectId]) clearTimeout(window.timeoutID[selectId]);
        window.timeoutID[selectId] = setTimeout(function() {
            updateFileArea(selectId, pattern);
        }, 500);
    }
    $(function() {
        $(document).on("keyup", "#forward_pattern", function() {
            updateFileList("#forwardList", $("#forward_pattern").val());
        });
        $(document).on("keyup", "#reverse_pattern", function() {
            updateFileList("#reverseList", $("#reverse_pattern").val());
        });
        $(document).on("keyup", "#r3_pattern", function() {
            updateFileList("#r3List", $("#r3_pattern").val());
        });
        $(document).on("keyup", "#r4_pattern", function() {
            updateFileList("#r4List", $("#r4_pattern").val());
        });
        $(document).on("keyup", "#single_pattern", function() {
            var pattern = $(this).val();
            updateFileList("#singleList", pattern);
        });
    });

    clearSelection = function() {
        selectedSamplesTable.fnClearTable();
        $("#forwardList").html("");
        $("#reverseList").html("");
        $("#r3List").html("");
        $("#r4List").html("");
        $("#singleList").html("");
        recordDelList("#forwardList", null, "reset");
        recordDelList("#reverseList", null, "reset");
        recordDelList("#r3List", null, "reset");
        recordDelList("#r4List", null, "reset");
        recordDelList("#singleList", null, "reset");
        $("#collection_type").trigger("change");
    };

    removeRowSelTable = function(button, collection_type) {
        var row = $(button).closest("tr");
        var files_used = row.children()[1].innerHTML.split(" | ");
        for (var x = 0; x < files_used.length; x++) {
            if (files_used[x].match(/,/)) {
                var splitedFiles = files_used[x].split(",");
                var forwardFile = splitedFiles[0];
                var reverseFile = splitedFiles[1];
                var r3File = splitedFiles[2];
                var r4File = splitedFiles[3];
                $("#forwardList > option").each(function() {
                    if (this.value.match(/no file/i)) {
                        $(this).remove();
                    }
                });
                $("#reverseList > option").each(function() {
                    if (this.value.match(/no file/i)) {
                        $(this).remove();
                    }
                });
                $("#r3List > option").each(function() {
                    if (this.value.match(/no file/i)) {
                        $(this).remove();
                    }
                });
                $("#r4List > option").each(function() {
                    if (this.value.match(/no file/i)) {
                        $(this).remove();
                    }
                });

                document.getElementById("forwardList").innerHTML +=
                    '<option value="' + forwardFile + '">' + forwardFile + "</option>";
                document.getElementById("reverseList").innerHTML +=
                    '<option value="' + reverseFile + '">' + reverseFile + "</option>";
                recordDelList("#forwardList", forwardFile, "add");
                recordDelList("#reverseList", reverseFile, "add");
                if (r3File) {
                    document.getElementById("r3List").innerHTML +=
                        '<option value="' + r3File + '">' + r3File + "</option>";
                    recordDelList("#r3List", r3File, "add");
                }
                if (r4File) {
                    document.getElementById("r4List").innerHTML +=
                        '<option value="' + r4File + '">' + r4File + "</option>";
                    recordDelList("#r4List", r4File, "add");
                }
            } else {
                $("#singleList > option").each(function() {
                    if (this.value.match(/no file/i)) {
                        $(this).remove();
                    }
                });
                document.getElementById("singleList").innerHTML +=
                    '<option value="' +
                    files_used[x] +
                    '">' +
                    files_used[x] +
                    "</option>";
                recordDelList("#singleList", files_used[x], "add");
            }
        }
        $("#selectedSamples").DataTable().row(row).remove().draw(false);
    };
    updateNameTable = function(input) {
        input.id = input.value;
    };
    replaceCharacters = function(string) {
        string = string.replace(/\./g, "_");
        string = string.replace(/-/g, "_");
        return string;
    };

    //keep record of the deleted items from singleList, forwardList, reverseList
    //in case of new search don't show these items
    recordDelList = function(listDiv, value, type) {
        if (type == "reset") {
            $(listDiv).removeData("samples");
        } else {
            var delArr = $(listDiv).data("samples");
            if (delArr) {
                if (delArr.length) {
                    if (type !== "add") {
                        delArr.push(value);
                    } else {
                        var index = delArr.indexOf(value);
                        if (index > -1) {
                            delArr.splice(index, 1);
                        }
                    }
                    $(listDiv).data("samples", delArr);
                }
            } else {
                if (type !== "add") {
                    $(listDiv).data("samples", [value]);
                }
            }
        }
    };



    getMultipleSelected = function(options) {
        var result = [];
        var opt;
        for (var i = 0, iLen = options.length; i < iLen; i++) {
            opt = options[i];
            if (opt.selected) {
                result.push(opt);
            }
        }
        return result;
    }

    // mode->all, selected
    smartSelection = function(mode) {

        var collection_type = $("#collection_type").val();
        var auto_merge_pattern = $.trim($("#auto_merge_pattern").val())
        var auto_merge_pattern_re = glob(auto_merge_pattern);

        if (collection_type == "single") {
            var files_select1 = document.getElementById("singleList").options;
            if (mode == "only") files_select1 = getMultipleSelected(files_select1)
            var regex1 = $("#single_pattern").val();
            //	use regex to find the values before the pivot
            if (regex1 === "") {
                regex1 = ".";
            }
            if (auto_merge_pattern !== "") {
                regex1 = auto_merge_pattern_re;
            }
        } else {
            var files_select1 = document.getElementById("forwardList").options;
            var files_select2 = document.getElementById("reverseList").options;
            var files_select3 = document.getElementById("r3List").options;
            var files_select4 = document.getElementById("r4List").options;
            if (mode == "only") files_select1 = getMultipleSelected(files_select1)
            if (mode == "only") files_select2 = getMultipleSelected(files_select2)
            if (mode == "only") files_select3 = getMultipleSelected(files_select3)
            if (mode == "only") files_select4 = getMultipleSelected(files_select4)
            var regex1 = $("#forward_pattern").val();
            var regex2 = $("#reverse_pattern").val();
            var regex3 = $("#r3_pattern").val();
            var regex4 = $("#r4_pattern").val();
            if (auto_merge_pattern !== "") {
                regex1 = auto_merge_pattern_re;
                regex2 = auto_merge_pattern_re;
                regex3 = auto_merge_pattern_re;
                regex4 = auto_merge_pattern_re;
            }
        }
        while (
            (collection_type == "single" && files_select1.length != 0) ||
            (collection_type == "pair" &&
                files_select1.length != 0 &&
                files_select2.length != 0) ||
            (collection_type == "triple" &&
                files_select1.length != 0 &&
                files_select2.length != 0 &&
                files_select3.length != 0) ||
            (collection_type == "quadruple" &&
                files_select1.length != 0 &&
                files_select2.length != 0 &&
                files_select3.length != 0 &&
                files_select4.length != 0)
        ) {

            var file_string = "";
            //  var file_regex = new RegExp(regex_string);
            if (collection_type == "single") {

                var regex_string = files_select1[0].value.split(regex1)[0];
                for (var x = 0; x < files_select1.length; x++) {
                    var prefix = files_select1[x].value.split(regex1)[0];

                    if (regex_string === prefix) {
                        file_string += files_select1[x].value + " | ";
                        recordDelList("#singleList", files_select1[x].value, "del");
                        $('#singleList option[value="' + files_select1[x].value + '"]')[0].remove();
                        if (mode == "only") files_select1.splice(x, 1);
                        x--;
                    }
                }
            } else if (collection_type == "pair") {
                var regex_string1 = files_select1[0].value.split(regex1)[0];
                var regex_string2 = files_select2[0].value.split(regex2)[0];
                for (var x = 0; x < files_select1.length; x++) {
                    var prefix1 = "";
                    var prefix2 = "";
                    if (files_select1[x])
                        prefix1 = files_select1[x].value.split(regex1)[0];
                    if (files_select2[x])
                        prefix2 = files_select2[x].value.split(regex2)[0];
                    if (regex_string1 === prefix1 && regex_string2 === prefix2) {
                        file_string +=
                            files_select1[x].value + "," + files_select2[x].value + " | ";
                        recordDelList("#forwardList", files_select1[x].value, "del");
                        recordDelList("#reverseList", files_select2[x].value, "del");
                        $(
                            '#forwardList option[value="' + files_select1[x].value + '"]'
                        )[0].remove();
                        $(
                            '#reverseList option[value="' + files_select2[x].value + '"]'
                        )[0].remove();
                        if (mode == "only") files_select1.splice(x, 1);
                        if (mode == "only") files_select2.splice(x, 1);
                        x--;
                    }
                }
            } else if (collection_type == "triple") {
                var regex_string1 = files_select1[0].value.split(regex1)[0];
                var regex_string2 = files_select2[0].value.split(regex2)[0];
                var regex_string3 = files_select3[0].value.split(regex3)[0];
                for (var x = 0; x < files_select1.length; x++) {
                    var prefix1 = "";
                    var prefix2 = "";
                    var prefix3 = "";
                    if (files_select1[x])
                        prefix1 = files_select1[x].value.split(regex1)[0];
                    if (files_select2[x])
                        prefix2 = files_select2[x].value.split(regex2)[0];
                    if (files_select3[x])
                        prefix3 = files_select3[x].value.split(regex3)[0];
                    if (
                        regex_string1 === prefix1 &&
                        regex_string2 === prefix2 &&
                        regex_string3 === prefix3
                    ) {
                        file_string +=
                            files_select1[x].value +
                            "," +
                            files_select2[x].value +
                            "," +
                            files_select3[x].value +
                            " | ";
                        recordDelList("#forwardList", files_select1[x].value, "del");
                        recordDelList("#reverseList", files_select2[x].value, "del");
                        recordDelList("#r3List", files_select3[x].value, "del");
                        $(
                            '#forwardList option[value="' + files_select1[x].value + '"]'
                        )[0].remove();
                        $(
                            '#reverseList option[value="' + files_select2[x].value + '"]'
                        )[0].remove();
                        $(
                            '#r3List option[value="' + files_select3[x].value + '"]'
                        )[0].remove();
                        if (mode == "only") files_select1.splice(x, 1);
                        if (mode == "only") files_select2.splice(x, 1);
                        if (mode == "only") files_select3.splice(x, 1);
                        x--;
                    }
                }
            } else if (collection_type == "quadruple") {
                var regex_string1 = files_select1[0].value.split(regex1)[0];
                var regex_string2 = files_select2[0].value.split(regex2)[0];
                var regex_string3 = files_select3[0].value.split(regex3)[0];
                var regex_string4 = files_select4[0].value.split(regex4)[0];
                for (var x = 0; x < files_select1.length; x++) {
                    var prefix1 = "";
                    var prefix2 = "";
                    var prefix3 = "";
                    var prefix4 = "";
                    if (files_select1[x])
                        prefix1 = files_select1[x].value.split(regex1)[0];
                    if (files_select2[x])
                        prefix2 = files_select2[x].value.split(regex2)[0];
                    if (files_select3[x])
                        prefix3 = files_select3[x].value.split(regex3)[0];
                    if (files_select4[x])
                        prefix4 = files_select4[x].value.split(regex4)[0];
                    if (
                        regex_string1 === prefix1 &&
                        regex_string2 === prefix2 &&
                        regex_string3 === prefix3 &&
                        regex_string4 === prefix4
                    ) {
                        file_string +=
                            files_select1[x].value +
                            "," +
                            files_select2[x].value +
                            "," +
                            files_select3[x].value +
                            "," +
                            files_select4[x].value +
                            " | ";
                        recordDelList("#forwardList", files_select1[x].value, "del");
                        recordDelList("#reverseList", files_select2[x].value, "del");
                        recordDelList("#r3List", files_select3[x].value, "del");
                        recordDelList("#r4List", files_select4[x].value, "del");
                        $(
                            '#forwardList option[value="' + files_select1[x].value + '"]'
                        )[0].remove();
                        $(
                            '#reverseList option[value="' + files_select2[x].value + '"]'
                        )[0].remove();
                        $(
                            '#r3List option[value="' + files_select3[x].value + '"]'
                        )[0].remove();
                        $(
                            '#r4List option[value="' + files_select4[x].value + '"]'
                        )[0].remove();
                        if (mode == "only") files_select1.splice(x, 1);
                        if (mode == "only") files_select2.splice(x, 1);
                        if (mode == "only") files_select3.splice(x, 1);
                        if (mode == "only") files_select4.splice(x, 1);
                        x--;
                    }
                }
            }
            file_string = file_string.substring(0, file_string.length - 3);
            if (regex1 === "") {
                var name = file_string;
            } else {
                var name = file_string.split(regex1)[0];
            }
            var name = name.split(" | ")[0].split(".")[0];
            var input = createElement(
                "input", ["id", "type", "class", "value", "onChange"], [name, "text", "", name, "updateNameTable(this)"]
            );
            var button_div = createElement("div", ["class"], ["text-center"]);
            var remove_button = createElement(
                "button", ["class", "type", "onclick"], [
                    "btn-sm btn-danger text-center",
                    "button",
                    "removeRowSelTable(this,'" + collection_type + "')",
                ]
            );
            var icon = createElement("i", ["class"], ["fa fa-times"]);
            remove_button.appendChild(icon);
            button_div.appendChild(remove_button);
            var fileDir = $("#viewDir").data("fileDir");
            var mRunAmzKeyS3 = "";
            if (fileDir.match(/s3:/i)) {
                mRunAmzKeyS3 = $("#viewDir").data("amzKey");
            }

            var mRunGoogKeyGS = "";
            if (fileDir.match(/gs:/i)) {
                mRunGoogKeyGS = $("#viewDir").data("googKey");
            }

            selectedSamplesTable.fnAddData([
                input.outerHTML,
                file_string,
                fileDir,
                button_div.outerHTML,
                mRunAmzKeyS3,
                mRunGoogKeyGS,
            ]);
        }
    };

    addSelection = function() {
        var collection_type = $("#collection_type").val();
        if (collection_type == "single") {
            var current_selection = document.getElementById("singleList").options;
            var regex = $("#single_pattern").val();
            var file_string = "";
            for (var x = 0; x < current_selection.length; x++) {
                if (current_selection[x].selected) {
                    file_string += current_selection[x].value + " | ";
                    recordDelList("#singleList", current_selection[x].value, "del");
                    $(
                        '#singleList option[value="' + current_selection[x].value + '"]'
                    )[0].remove();
                    x--;
                }
            }
        } else if (collection_type == "pair") {
            var current_selectionF = document.getElementById("forwardList").options;
            var current_selectionR = document.getElementById("reverseList").options;
            var regex = $("#forward_pattern").val();
            var file_string = "";
            for (var x = 0; x < current_selectionF.length; x++) {
                if (current_selectionF[x].selected && current_selectionR[x].selected) {
                    file_string +=
                        current_selectionF[x].value +
                        "," +
                        current_selectionR[x].value +
                        " | ";
                    recordDelList("#forwardList", current_selectionF[x].value, "del");
                    recordDelList("#reverseList", current_selectionR[x].value, "del");
                    $(
                        '#forwardList option[value="' + current_selectionF[x].value + '"]'
                    )[0].remove();
                    $(
                        '#reverseList option[value="' + current_selectionR[x].value + '"]'
                    )[0].remove();
                    x--;
                }
            }
        } else if (collection_type == "triple") {
            var current_selectionF = document.getElementById("forwardList").options;
            var current_selectionR = document.getElementById("reverseList").options;
            var current_selectionR3 = document.getElementById("r3List").options;
            var regex = $("#forward_pattern").val();
            var file_string = "";
            for (var x = 0; x < current_selectionF.length; x++) {
                if (
                    current_selectionF[x].selected &&
                    current_selectionR[x].selected &&
                    current_selectionR3[x].selected
                ) {
                    file_string +=
                        current_selectionF[x].value +
                        "," +
                        current_selectionR[x].value +
                        "," +
                        current_selectionR3[x].value +
                        " | ";
                    recordDelList("#forwardList", current_selectionF[x].value, "del");
                    recordDelList("#reverseList", current_selectionR[x].value, "del");
                    recordDelList("#r3List", current_selectionR3[x].value, "del");
                    $(
                        '#forwardList option[value="' + current_selectionF[x].value + '"]'
                    )[0].remove();
                    $(
                        '#reverseList option[value="' + current_selectionR[x].value + '"]'
                    )[0].remove();
                    $(
                        '#r3List option[value="' + current_selectionR3[x].value + '"]'
                    )[0].remove();
                    x--;
                }
            }
        } else if (collection_type == "quadruple") {
            var current_selectionF = document.getElementById("forwardList").options;
            var current_selectionR = document.getElementById("reverseList").options;
            var current_selectionR3 = document.getElementById("r3List").options;
            var current_selectionR4 = document.getElementById("r4List").options;
            var regex = $("#forward_pattern").val();
            var file_string = "";
            for (var x = 0; x < current_selectionF.length; x++) {
                if (
                    current_selectionF[x].selected &&
                    current_selectionR[x].selected &&
                    current_selectionR3[x].selected &&
                    current_selectionR4[x].selected
                ) {
                    file_string +=
                        current_selectionF[x].value +
                        "," +
                        current_selectionR[x].value +
                        "," +
                        current_selectionR3[x].value +
                        "," +
                        current_selectionR4[x].value +
                        " | ";
                    recordDelList("#forwardList", current_selectionF[x].value, "del");
                    recordDelList("#reverseList", current_selectionR[x].value, "del");
                    recordDelList("#r3List", current_selectionR3[x].value, "del");
                    recordDelList("#r4List", current_selectionR4[x].value, "del");
                    $(
                        '#forwardList option[value="' + current_selectionF[x].value + '"]'
                    )[0].remove();
                    $(
                        '#reverseList option[value="' + current_selectionR[x].value + '"]'
                    )[0].remove();
                    $(
                        '#r3List option[value="' + current_selectionR3[x].value + '"]'
                    )[0].remove();
                    $(
                        '#r4List option[value="' + current_selectionR4[x].value + '"]'
                    )[0].remove();
                    x--;
                }
            }
        }
        if (file_string) {
            file_string = file_string.substring(0, file_string.length - 3);
            if (file_string != "") {
                if (regex == "") {
                    var name = file_string;
                } else {
                    var name = file_string.split(regex)[0];
                }
                var name = name.split(" | ")[0].split(".")[0];
                var input = createElement(
                    "input", ["id", "type", "class", "value", "onChange"], [name, "text", "", name, "updateNameTable(this)"]
                );
                var button_div = createElement("div", ["class"], ["text-center"]);
                var remove_button = createElement(
                    "button", ["class", "type", "onclick"], [
                        "btn-sm btn-danger text-center",
                        "button",
                        "removeRowSelTable(this,'" + collection_type + "')",
                    ]
                );
                var icon = createElement("i", ["class"], ["fa fa-times"]);
                remove_button.appendChild(icon);
                button_div.appendChild(remove_button);
                var fileDir = $("#viewDir").data("fileDir");
                var mRunAmzKeyS3 = "";
                if (fileDir.match(/s3:/i)) {
                    mRunAmzKeyS3 = $("#viewDir").data("amzKey");
                }
                var mRunGoogKeyGS = "";
                if (fileDir.match(/gs:/i)) {
                    mRunGoogKeyGS = $("#viewDir").data("googKey");
                }

                selectedSamplesTable.fnAddData([
                    input.outerHTML,
                    file_string,
                    fileDir,
                    button_div.outerHTML,
                    mRunAmzKeyS3,
                    mRunGoogKeyGS,
                ]);
            }
        }
    };

    //Sample Modal ENDs
    //##################

    //click on "use default" button
    $(document).on("click", "#defValUse", async function(e) {
        var button = $(this);
        var rowID = "";
        var gNumParam = "";
        var given_name = "";
        var qualifier = "";
        var sType = "";
        [rowID, gNumParam, given_name, qualifier, sType] =
        getInputVariables(button);
        var value = $(button).attr("defVal");
        var data = [];
        data.push({ name: "id", value: "" });
        data.push({ name: "name", value: value });
        var inputID = null;
        var url = null,
            urlzip = null,
            checkPath = null;
        //check database if file is exist, if not exist then insert
        await checkInputInsert(
            data,
            gNumParam,
            given_name,
            qualifier,
            rowID,
            sType,
            inputID,
            null,
            url,
            urlzip,
            checkPath
        );
        button.css("display", "none");
        showHideSett(rowID);
        autoCheck();
    });
    //change on exec settings
    $(function() {
        $(document).on("keyup", ".form-control.execSetting", function() {
            var rowDiv = $(this).parent().parent();
            if (rowDiv) {
                var rowId = rowDiv.attr("id");
                if (rowId.match(/procGnum-(.*)/)) {
                    var checkId = rowId.match(/procGnum-(.*)/)[1];
                    var checkBoxId = "check-" + checkId;
                    updateCheckBox("#" + checkBoxId, "true");
                    updateCheckBox("#exec_each", "true");
                }
            }
        });
    });
    //change on dropDown button
    $(function() {
        $(document).on("change", "select[indropdown]", async function() {
            var button = $(this);
            var value = $(this).val();
            var rowID = "";
            var gNumParam = "";
            var given_name = "";
            var qualifier = "";
            var sType = "";
            [rowID, gNumParam, given_name, qualifier, sType] =
            getInputVariables(button);
            var proPipeInputID = $("#" + rowID).attr("propipeinputid");
            // if proPipeInputID exist, then first remove proPipeInputID.
            if (proPipeInputID) {
                var removeInput = await doAjax({
                    p: "removeProjectPipelineInput",
                    id: proPipeInputID,
                });
            }
            // insert into project pipeline input table
            if (value && value != "") {
                var data = [];
                data.push({ name: "id", value: "" });
                data.push({ name: "name", value: value });
                var inputID = null;
                var url = null,
                    urlzip = null,
                    checkPath = null;
                await checkInputInsert(
                    data,
                    gNumParam,
                    given_name,
                    qualifier,
                    rowID,
                    sType,
                    inputID,
                    null,
                    url,
                    urlzip,
                    checkPath
                );
            } else {
                // remove from project pipeline input table
                var removeInput = await doAjax({
                    p: "removeProjectPipelineInput",
                    id: proPipeInputID,
                });
                var fillingType = "default";
                removeSelectFile(rowID, qualifier, fillingType);
            }
            await checkReadytoRun();
        });
    });

    $(function() {
        $(document).on("change", "#mRunAmzKey", async function() {
            await checkReadytoRun();
        });
        $(document).on("change", "#mRunGoogKey", async function() {
            await checkReadytoRun();
        });
    });

    $(function() {
        $(document).on("change", "#chooseEnv", async function() {
            //reset before autofill feature actived for #runCmd
            changeOnchooseEnv = true;
            $("#runCmd").val("");
            // this section commented out and moved to bindEveHandlerChooseEnv function
            // var [allProSett, profileData] = await getJobData("both");
            // showhideOnEnv(profileData);
            // fillForm("#allProcessSettTable", "input", allProSett);
            var profileTypeId = $("#chooseEnv").find(":selected").val();
            var patt = /(.*)-(.*)/;
            var proType = profileTypeId.replace(patt, "$1");
            var proId = profileTypeId.replace(patt, "$2");
            proTypeWindow = proType;
            proIdWindow = proId;
            selectCloudKey();
            checkShub();
            checkCloudType(profileTypeId);
            await checkReadytoRun();
            updateDiskSpace();
            //save run after change
            await saveRun(false, true);
        });
    });

    $.getScript("js/runpipeline/fileModal.js");

    $("#inputFilemodal").on("show.bs.modal", async function(e) {
        var cleanDmetaTable = function() {
            var dmetaurl = $("#dmetaFileTab").attr("dmetaurl");
            var dmetaid = $("#dmetaFileTab").attr("dmetaid");
            if (dmetaurl && dmetaid) {
                var dURLs = dmetaurl.split(",");
                var dIDs = dmetaid.split(",");
                for (var i = 0; i < dURLs.length; i++) {
                    var TableID = "#" + dIDs[i] + "Table";
                    if ($.fn.DataTable.isDataTable(TableID)) {
                        var dmetaTable = $(TableID).DataTable();
                        dmetaTable.column(0).checkboxes.deselect();
                    }
                }
            }
        };
        var sampleTableCallback = async function() {
            console.log(e)
            $("#projectFileTable").DataTable().rows().deselect();
            $('.nav-tabs a[href="#importedFiles"]').trigger("click");
            $("#detailsOffileDiv").css("display", "none");
            $("#detailsOfmetaDiv").css("display", "none");
            $("#sampleTable").DataTable().rows().deselect();
            selectMultiselect("#select-Collection", []);
            selectMultiselect("#select-Host", []);
            selectMultiselect("#select-Project", []);
            var button = $(e.relatedTarget);
            var clickedRow = button.closest("tr");
            var rowID = clickedRow[0].id; //#inputTa-3
            var gNumParam = rowID.split("Ta-")[1];
            if (button.attr("id") === "inputFileEnter") {
                $("#filemodaltitle").html("Select/Add Input File");
                $("#mIdFile").attr("rowID", rowID);
            } else if (button.attr("id") === "inputFileEdit") {
                $("#filemodaltitle").html("Change Input File");
                $("#mIdFile").attr("rowID", rowID);
                var proPipeInputID = $("#" + rowID).attr("propipeinputid");
                $("#mIdFile").val(proPipeInputID);
                // Get the input id of proPipeInput;
                var proInputGet = await doAjax({
                    p: "getProjectPipelineInputs",
                    id: proPipeInputID,
                });
                console.log(proInputGet)
                if (proInputGet) {
                    if (proInputGet[0]) {
                        var input_id = proInputGet[0].input_id;
                        var collection_id = proInputGet[0].collection_id;
                        var collection_name = proInputGet[0].collection_name;
                        if (collection_id && collection_id != "0" && collection_name) {
                            selectMultiselect("#select-Collection", [collection_name]);
                            $("#sampleTable")
                                .DataTable()
                                .rows({ search: "applied" })
                                .select();
                            $('.nav-tabs a[href="#importedFilesTab"]').tab("show");
                        } else if (input_id) {
                            $('.nav-tabs a[href="#manualTab"]').tab("show");
                            var inputGet = await doAjax({ p: "getInputs", id: input_id });
                            console.log(inputGet)
                            inputGet = inputGet[0]
                            if (inputGet) {
                                //insert data (input_id) into form
                                var formValues = $("#manualTab").find("form").find("input");
                                var keys = Object.keys(inputGet);
                                console.log(keys)
                                console.log(inputGet)
                                for (var i = 0; i < keys.length; i++) {
                                    $(formValues[i]).val(inputGet[keys[i]]);
                                }
                            }
                        }
                    }
                }
            }
        };
        $(this).find("form").trigger("reset");
        await buildSampleTable(sampleTableCallback);
        cleanDmetaTable();
    });

    $("#inputFilemodal").on("shown.bs.modal", function(e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    getTableSamples = function(tableId) {
        var ret = {};
        var file_array = [];
        var warnUser = "";
        var table_data = window[tableId].fnGetData();
        var table_nodes = window[tableId].fnGetNodes();
        for (var y = 0; y < table_data.length; y++) {
            var name = $.trim(table_nodes[y].children[0].children[0].id);
            name = name
                .replace(/:/g, "_")
                .replace(/,/g, "_")
                .replace(/\$/g, "_")
                .replace(/\!/g, "_")
                .replace(/\</g, "_")
                .replace(/\>/g, "_")
                .replace(/\?/g, "_")
                .replace(/\(/g, "-")
                .replace(/\)/g, "-")
                .replace(/\"/g, "_")
                .replace(/\'/g, "_")
                .replace(/\//g, "_")
                .replace(/\\/g, "_")
                .replace(/ /g, "_");
            if (!name) {
                warnUser = "Please fill all the filenames in the table.";
            }
            var files_used = table_data[y][1];
            file_array.push(name + " " + files_used);
        }
        ret.file_array = file_array;
        ret.warnUser = warnUser;
        return ret;
    };

    checkOneCollection = async function(selectedRows) {
        //get collection_id of first item. it can be space separated multiple ids
        var pushFeatureIntoArray = function(ar, column) {
            var vals = [];
            for (var i = 0; i < ar.length; i++) {
                vals.push(ar[i][column]);
            }
            return vals;
        };
        var collectionIdAr = selectedRows[0].collection_id.split(",");
        var selRowsfileIdAr = [];
        selRowsfileIdAr = pushFeatureIntoArray(selectedRows, "id");
        for (var n = 0; n < collectionIdAr.length; n++) {
            var selColData = await doAjax({
                id: collectionIdAr[n],
                p: "getCollectionFiles",
            });

            var selColfileIdAr = pushFeatureIntoArray(selColData, "id");
            var checkEq = checkArraysEqual(
                selColfileIdAr.sort(),
                selRowsfileIdAr.sort()
            );
            if (checkEq === true) {
                return [collectionIdAr[n], selRowsfileIdAr];
                break;
            }
        }
        return [false, selRowsfileIdAr];
    };

    $("#inputFilemodal").on("click", "#savefile", async function(e) {
        $("#inputFilemodal").loading({
            message: "Working...",
        });
        e.preventDefault();
        var fillCollection = async function(savetype, collection) {
            if (!savetype.length) {
                //add item
                await saveFileSetValModal(null, "file", null, collection);
            } else {
                await editFileSetValModal(null, "file", null, collection);
            }
            var insertFileProject = await doAjax({
                p: "insertFileProject",
                collection_id: collection.collection_id,
                project_id: project_id,
            });
            $("#sampleTable").DataTable().ajax.reload(null, false);
        };
        var checkValidCollection = function(selectedRows) {
            var collTypes = {};
            var fileTypes = {};
            var nameAr = [];
            var invalidNameAr = [];
            var ret = "";
            for (var i = 0; i < selectedRows.length; i++) {
                var collection_type = selectedRows[i].collection_type;
                var file_type = selectedRows[i].file_type;
                var name = selectedRows[i].name;
                if (!collTypes[collection_type]) {
                    collTypes[collection_type] = [name];
                } else {
                    collTypes[collection_type].push(name);
                }
                if (!fileTypes[file_type]) {
                    fileTypes[file_type] = [name];
                } else {
                    fileTypes[file_type].push(name);
                }
                if (!nameAr.includes(name)) {
                    nameAr.push(name)
                } else {
                    invalidNameAr.push(name)
                }
            }
            if (invalidNameAr.length > 0) {
                ret += "Duplicate file names are not allowed in a collection:</br>";
                for (var n = 0; n < invalidNameAr.length; n++) {
                    ret += "  <b>* " + invalidNameAr[n] + "</b></br>";
                }
            }
            if (
                Object.keys(fileTypes).length > 1 ||
                Object.keys(collTypes).length > 1
            ) {
                if (Object.keys(fileTypes).length > 1) {
                    ret += "Multiple file types are not allowed in a collection:</br>";
                    $.each(fileTypes, function(el) {
                        ret +=
                            "  <b>* " +
                            el +
                            ":</b> " +
                            fileTypes[el]
                            .map(function(s) {
                                return s.substring(0, 15);
                            })
                            .join(", ") +
                            "</br>";
                    });
                }
                if (Object.keys(collTypes).length > 1) {
                    ret +=
                        "Multiple collection types are not allowed in a collection:</br>";
                    $.each(collTypes, function(el) {
                        ret +=
                            " <b>* " +
                            el +
                            ":</b> " +
                            collTypes[el]
                            .map(function(s) {
                                return s.substring(0, 15);
                            })
                            .join(", ") +
                            "</br>";
                    });
                }
            }
            return ret;
        };

        var savetype = $("#mIdFile").val();
        var checkdata = $("#inputFilemodal")
            .find("[searchTab='true'].active.tab-pane")[0]
            .getAttribute("id");
        if (checkdata === "importedFilesTab") {
            $("#inputFilemodal").loading("stop");
            var selectedRows = $("#sampleTable")
                .DataTable()
                .rows({ selected: true })
                .data();
            if (selectedRows.length === 0) {
                showInfoModal(
                    "#infoModal",
                    "#infoModalText",
                    "None of the file is selected in the table. Please use checkboxes to select files."
                );
            } else if (selectedRows.length > 0) {
                //check if selected items belong to only one collection
                var collection_id = "";
                var selRowsfileIdAr = [];
                var checkOneCol = "";
                [checkOneCol, selRowsfileIdAr] = await checkOneCollection(selectedRows);
                var warningText = checkValidCollection(selectedRows);
                if (warningText) {
                    showInfoModal("#infoModal", "#infoModalText", warningText);
                    return;
                }
                //if new collection required, ask for name
                if (!checkOneCol) {
                    $("#newCollectionModal").off();
                    $("#newCollectionModal").on("show.bs.modal", function(event) {
                        $(this).find("form").trigger("reset");
                        $("#newCollectionModalText").html(
                            "Selected files are not match with the one collection. Please enter <b> a new collection name </b> or <b>choose collection from dropdown</b> to add your files into existing collection."
                        );
                        selectizeCollection(["#newCollectionName"], "", false);
                    });
                    $("#newCollectionModal").on("click", "#saveNewCollect", async function(e) {
                        e.preventDefault();
                        var newCollName = $("#newCollectionName")[0].selectize.getItem(
                            $("#newCollectionName").val()
                        )[0].innerHTML;
                        if (newCollName != "") {
                            newCollName = cleanSpecChar(newCollName);
                            var collection_data = await doAjax({
                                p: "saveCollection",
                                name: newCollName,
                            });
                            if (collection_data.id) {
                                collection_id = collection_data.id;
                                var savecollection = await doAjax({
                                    p: "insertFileCollection",
                                    file_array: selRowsfileIdAr,
                                    collection_id: collection_id,
                                });
                                if (savecollection.id) {
                                    var collection = {
                                        collection_id: collection_id,
                                        collection_name: newCollName,
                                    };
                                    await fillCollection(savetype, collection);
                                    $("#sampleTable").DataTable().ajax.reload(null, false);
                                    $("#newCollectionModal").modal("hide");
                                    $("#inputFilemodal").modal("hide");
                                }
                            }
                        }
                    });
                    $("#newCollectionModal").modal("show");
                } else {
                    collection_id = checkOneCol;
                    var getcollection = await doAjax({
                        p: "getCollection",
                        id: collection_id,
                    });
                    if (getcollection.length) {
                        if (getcollection[0].name) {
                            var collection = {
                                collection_id: collection_id,
                                collection_name: getcollection[0].name,
                            };
                            await fillCollection(savetype, collection);
                            $("#inputFilemodal").modal("hide");
                        }
                    }
                }
            }
        } else if (checkdata === "dmetaFileTab") {
            $("#inputFilemodal").loading("stop");
            // find selected dmeta tab
            var activeDmetaID = $("#inputFilemodal")
                .find("[searchmetatab='true'].active.tab-pane")[0]
                .getAttribute("id"); //"Dmeta-cancerTab"
            var selTableID = "#" + activeDmetaID + "le";
            var selectedRows = $(selTableID)
                .DataTable()
                .rows({ selected: true })
                .data();
            if (selectedRows.length === 0) {
                showInfoModal(
                    "#infoModal",
                    "#infoModalText",
                    "None of the file is selected in the table. Please use checkboxes to select files."
                );
            } else if (selectedRows.length > 0) {
                var insertFiles = function(
                    selectedRows,
                    collection_id,
                    collection_name
                ) {
                    var fileObj = {};
                    var fileDirArr = [];
                    var fileArr = [];
                    // convert dmeta format (Array) to dnext format
                    for (var i = 0; i < selectedRows.length; i++) {
                        var cpData = $.extend(true, {}, selectedRows[i]);
                        var file_dir = cpData.file_dir;
                        var file_name = cpData.name;
                        var files_used = cpData.files_used;
                        if (file_dir.constructor === Array) {
                            file_dir = file_dir.join("\t");
                        }
                        if (files_used.constructor === Array) {
                            for (var k = 0; k < files_used.length; k++) {
                                files_used[k] = files_used[k].join(",");
                            }
                            files_used = files_used.join(" | ");
                        }
                        fileDirArr.push(file_dir);
                        fileArr.push(file_name + " " + files_used);
                    }
                    fileObj.run_env = $("#chooseEnv").find(":selected").val();
                    fileObj.project_id = project_id;
                    fileObj.collection_id = collection_id;
                    fileObj.collection_type = selectedRows[0].collection_type;
                    fileObj.file_type = selectedRows[0].file_type;
                    //fileObj.s3_archive_dir = selectedRows[0].s3_archive_dir;
                    //fileObj.gs_archive_dir = selectedRows[0].gs_archive_dir;
                    //fileObj.archive_dir = selectedRows[0].archive_dir;
                    fileObj.file_array = fileArr;
                    fileObj.file_dir = fileDirArr;
                    fileObj.p = "saveFile";
                    console.log(fileObj);
                    getValuesAsync(fileObj, async function(fileIdAr) {
                        if (fileIdAr) {
                            var savecollection = await doAjax({
                                p: "insertFileCollection",
                                file_array: fileIdAr,
                                collection_id: collection_id,
                            });
                            var collection = {
                                collection_id: collection_id,
                                collection_name: collection_name,
                            };
                            await fillCollection(savetype, collection);
                            $("#sampleTable").DataTable().ajax.reload(null, false);
                            $("#newCollectionModal").modal("hide");
                            $("#inputFilemodal").modal("hide");
                        }
                    });
                };
                var showModal = function(selectedRows) {
                    //new collection is required, ask for name
                    $("#newCollectionModal").off();
                    $("#newCollectionModal").on("show.bs.modal", function(event) {
                        $(this).find("form").trigger("reset");
                        $("#newCollectionModalText").html(
                            "Please enter <b> a new collection name </b> or <b>choose collections from dropdown</b> to add  your files into existing collections."
                        );
                        selectizeCollection(["#newCollectionName"], "", false);
                    });
                    $("#newCollectionModal").on("click", "#saveNewCollect", async function(e) {
                        e.preventDefault();
                        var collection_name = $("#newCollectionName")[0].selectize.getItem(
                            $("#newCollectionName").val()
                        )[0].innerHTML;
                        if (collection_name != "") {
                            collection_name = cleanSpecChar(collection_name);
                            var collection_data = await doAjax({
                                p: "saveCollection",
                                name: collection_name,
                            });
                            if (collection_data.id) {
                                collection_id = collection_data.id;
                                insertFiles(selectedRows, collection_id, collection_name);
                            }
                        }
                    });
                    $("#newCollectionModal").modal("show");
                };
                var warningText = checkValidCollection(selectedRows);
                if (warningText) {
                    showInfoModal("#infoModal", "#infoModalText", warningText);
                    return;
                }
                // 1. get collection name of first item.
                var collection_name = selectedRows[0].collection_name;
                collection_name = cleanSpecChar(collection_name);
                var collection_data = await doAjax({
                    p: "saveCollection",
                    name: collection_name,
                });
                if (collection_data.id) {
                    collection_id = collection_data.id;
                    // 2. get number of files of collection
                    var colFiles = await doAjax({
                        id: collection_id,
                        p: "getCollectionFiles",
                    });
                    // 3a. if num > 0, then ask for collection name with modal
                    if (colFiles.length > 0) {
                        showModal(selectedRows);
                    }
                    // 3b. else insert files and file collection
                    else {
                        insertFiles(selectedRows, collection_id, collection_name);
                    }
                }
            }
        } else {
            if (!savetype.length) {
                //add item
                if (checkdata === "manualTab") {
                    var formValues = $("#manualTab").find("input");
                    var data = formValues.serializeArray(); // convert form to array
                    // check if name is entered
                    data[1].value = $.trim(data[1].value);
                    if (data[1].value !== "") {
                        await saveFileSetValModal(data, "file", null, null);
                        $("#inputFilemodal").loading("stop");
                        $("#inputFilemodal").modal("hide");
                    } else {
                        $("#inputFilemodal").loading("stop");
                        showInfoModal(
                            "#infoModal",
                            "#infoModalText",
                            "Please enter or select files from table to fill 'File Path' box."
                        );
                    }
                } else if (checkdata === "publicFileTab") {
                    var rows_selected = publicFileTable.column(0).checkboxes.selected();
                    if (rows_selected.length === 1) {
                        var input_id = rows_selected[0];
                        await saveFileSetValModal(null, "file", input_id, null);
                    }
                    $("#inputFilemodal").loading("stop");
                    $("#inputFilemodal").modal("hide");
                }
            } else {
                //edit item
                if (checkdata === "manualTab") {
                    var formValues = $("#inputFilemodal").find("input");
                    var data = formValues.serializeArray(); // convert form to array
                    // check if file_path is entered
                    data[1].value = $.trim(data[1].value);
                    if (data[1].value !== "") {
                        await editFileSetValModal(data, "file", null, null);
                        $("#inputFilemodal").loading("stop");
                        $("#inputFilemodal").modal("hide");
                    } else {
                        $("#inputFilemodal").loading("stop");
                        showInfoModal(
                            "#infoModal",
                            "#infoModalText",
                            "Please enter or select files from table to fill 'File Path' box."
                        );
                    }
                } else if (checkdata === "publicFileTab") {
                    var rows_selected = publicFileTable.column(0).checkboxes.selected();
                    if (rows_selected.length === 1) {
                        var input_id = rows_selected[0];
                        await editFileSetValModal(null, "file", input_id, null);
                        $("#inputFilemodal").loading("stop");
                        $("#inputFilemodal").modal("hide");
                    }
                }
            }
        }
        $("#inputFilemodal").loading("stop");
    });

    //clicking on top tabs of select files table
    $('a[data-toggle="tab"]').on("shown.bs.tab click", function(e) {
        // header fix of datatabes in add to files/values tab
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        var activatedTab = $(e.target).attr("href");
        if (activatedTab === "#manualTab") {
            var projectRows = $("#projectListTable > tbody >");
            // if project is exist click on the first one to show files
            console.log(projectRows);
            if (projectRows && projectRows.length > 0) {
                $("#projectListTable > tbody > tr > td ")
                    .find('[projectid="' + project_id + '"]')
                    .trigger("click");
            }
        } else if (activatedTab === "#manualTabV") {
            var projectRows = $("#projectListTableVal > tbody >");
            console.log(projectRows);
            // if project is exist click on the first one to show files
            if (projectRows && projectRows.length > 0) {
                $("#projectListTableVal > tbody > tr > td ")
                    .find('[projectid="' + project_id + '"]')
                    .trigger("click");
            }
        } else if (activatedTab === "#publicFileTab") {
            var host = $("#chooseEnv").find(":selected").attr("host");
            if (host != undefined) {
                if (host != "") {
                    $("#publicFileTabWarn").html("");
                    $("#publicFileTable").show();
                    var table_id = "publicFileTable";
                    var ajax = { host: host, p: "getPublicFiles" };
                    $("#" + table_id)
                        .dataTable()
                        .fnDestroy();
                    createFileTable(table_id, ajax);
                }
            } else {
                $("#publicFileTabWarn").html(
                    "</br> Please select run environments to see public files."
                );
                $("#publicFileTable").hide();
            }
        } else if (activatedTab === "#publicValTab") {
            var host = $("#chooseEnv").find(":selected").attr("host");
            if (host != undefined) {
                if (host != "") {
                    $("#publicValTabWarn").html("");
                    $("#publicValTable").show();

                    var table_id = "publicValTable";
                    var ajax = { host: host, p: "getPublicValues" };
                    $("#" + table_id)
                        .dataTable()
                        .fnDestroy();
                    createFileTable(table_id, ajax);
                }
            } else {
                $("#publicValTabWarn").html(
                    "</br> Please select run environments to see public files."
                );
                $("#publicValTable").hide();
            }
        }
    });

    function createFileTable(table_id, ajax) {
        window[table_id] = $("#" + table_id).DataTable({
            //            scrollY: '42vh',
            dom: '<"top"i>rt<"pull-left"f><"bottom"p><"clear">',
            bInfo: false,
            ajax: {
                url: "ajax/ajaxquery.php",
                data: ajax,
                dataSrc: "",
            },
            columns: [{
                    width: "25px",
                    data: "input_id",
                    checkboxes: {
                        targets: 0,
                        selectRow: true,
                    },
                },
                {
                    data: "name",
                },
                {
                    data: "date_modified",
                    width: "130px",
                },
            ],
            select: {
                style: "single",
            },
            order: [
                [2, "desc"]
            ],
        });
    }

    function createProjectListTable(table_id) {
        table_id = $("#" + table_id).DataTable({
            scrollY: "42vh",
            pagingType: "simple",
            dom: '<"top"i>rt<"pull-left"f><"bottom"p><"clear">',
            bInfo: false,
            searching: false,
            ajax: {
                url: "ajax/ajaxquery.php",
                data: {
                    p: "getProjects",
                },
                dataSrc: "",
            },
            columns: [{
                data: "name",
                fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
                    $(nTd).html(
                        '<a class="clickproject" projectid="' +
                        oData.id +
                        '">' +
                        oData.name +
                        "</a>"
                    );
                },
            }, ],
            select: {
                style: "single",
            },
        });
    }

    //clicking on rows of projectFileTable
    $("#projectFileTable").on("click", "tr", function(event) {
        var a = $("#projectFileTable").dataTable().fnGetData(this);
        if (a) {
            if (a.name) {
                var name = a.name;
                $("#mFilePath").val(name);
            }
        }
    });
    //clicking on rows of projectValTable
    $("#projectValTable").on("click", "tr", function(event) {
        var a = $("#projectValTable").dataTable().fnGetData(this);
        if (a) {
            if (a.name) {
                var name = a.name;
                $("#mValName").val(name);
            }
        }
    });

    //left side project list table on add File/value modals
    createProjectListTable("projectListTable");
    createProjectListTable("projectListTableVal");

    //add file modal projectListTable click on project name
    $("#projectListTable").on("click", "td", function(e) {
        var sel_project_id = $(this).children().attr("projectid");
        var table_id = "projectFileTable";
        var ajax = { project_id: sel_project_id, p: "getProjectFiles" };
        $("#" + table_id)
            .dataTable()
            .fnDestroy();
        createFileTable(table_id, ajax);
    });

    //add val modal projectListTableVal click on project name
    $("#projectListTableVal").on("click", "td", function(e) {
        var sel_project_id = $(this).children().attr("projectid");
        var table_id = "projectValTable";
        var ajax = { project_id: sel_project_id, p: "getProjectValues" };
        $("#" + table_id)
            .dataTable()
            .fnDestroy();
        createFileTable(table_id, ajax);
    });

    $(document).on("click", "#inputSingleFileDownload", async function(e) {
        var clickedRow = $(this).closest("tr");
        var rowID = clickedRow[0].id; //#inputTa-3
        var gNumParam = rowID.split("Ta-")[1];
        var proPipeInputID = $("#" + rowID).attr("propipeinputid");
        var path = $(`#filePath-${gNumParam}`).text()
        if (!proPipeInputID) {
            toastr.error("File Not Found.");
            return;
        }
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: {
                pro_pipe_input_id: proPipeInputID,
                path: path,
                p: "getHostFile"
            },
            async: true,
            success: function(s) {
                if (s && s.location && s.name) {
                    var link = document.createElement("a");
                    link.setAttribute('download', s.name);
                    link.href = s.location
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                }
            },
            error: function(errorThrown) {
                toastr.error("File Not Found.");
            },
        });
    });
    $(document).on("click", "#inputDelDelete, #inputValDelete, #inputSingleFileDelete", async function(e) {
        var clickedRow = $(this).closest("tr");
        var rowID = clickedRow[0].id; //#inputTa-3
        var gNumParam = rowID.split("Ta-")[1];
        var proPipeInputID = $("#" + rowID).attr("propipeinputid");
        var removeInput = await doAjax({
            p: "removeProjectPipelineInput",
            id: proPipeInputID,
        });
        var qualifier = $("#" + rowID + " > :nth-child(4)").text();
        var fillingType = "default;";
        removeSelectFile(rowID, qualifier, fillingType);
        await checkReadytoRun();
    });

    $("#inputValmodal").on("show.bs.modal", async function(e) {
        var button = $(e.relatedTarget);
        $(this).find("form").trigger("reset");
        $("#projectValTable").DataTable().rows().deselect();
        $('.nav-tabs a[href="#manualTabV"]').trigger("click");
        var clickedRow = button.closest("tr");
        var rowID = clickedRow[0].id; //#inputTa-3
        var gNumParam = rowID.split("Ta-")[1];
        if (button.attr("id") === "inputValEnter") {
            $("#valmodaltitle").html("Add Value");
            $("#mIdVal").attr("rowID", rowID);
        } else if (button.attr("id") === "inputValEdit") {
            $("#valmodaltitle").html("Edit Value");
            $("#mIdVal").attr("rowID", rowID);
            var proPipeInputID = $("#" + rowID).attr("propipeinputid");
            $("#mIdVal").val(proPipeInputID);
            // Get the input id of proPipeInput;
            var proInputGet = await doAjax({
                p: "getProjectPipelineInputs",
                id: proPipeInputID,
            });
            if (proInputGet) {
                var input_id = proInputGet[0].input_id;
                var inputGet = await doAjax({ p: "getInputs", id: input_id });
                inputGet = inputGet[0];
                if (inputGet) {
                    //insert data into form
                    var formValues = $("#inputValmodal").find("input");
                    var keys = Object.keys(inputGet);
                    for (var i = 0; i < keys.length; i++) {
                        $(formValues[i]).val(inputGet[keys[i]]);
                    }
                }
            }
        }
    });
    $("#inputValmodal").on("shown.bs.modal", function(e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    $("#inputValmodal").on("click", "#saveValue", async function(e) {
        e.preventDefault();
        $("#inputValmodal").loading({
            message: "Working...",
        });
        var savetype = $("#mIdVal").val();
        var checkdata = $("#inputValmodal")
            .find(".active.tab-pane")[0]
            .getAttribute("id");
        if (!savetype.length) {
            //add item
            if (checkdata === "manualTabV") {
                var formValues = $("#inputValmodal").find("input");
                var data = formValues.serializeArray(); // convert form to array
                // check if name is entered
                data[1].value = $.trim(data[1].value);
                if (data[1].value !== "") {
                    await saveFileSetValModal(data, "val", null, null);
                    $("#inputValmodal").loading("stop");
                    $("#inputValmodal").modal("hide");
                } else {
                    $("#inputValmodal").loading("stop");
                    showInfoModal(
                        "#infoModal",
                        "#infoModalText",
                        "Please enter or select values from table to fill 'Value' box."
                    );
                }
            } else if (checkdata === "publicValTab") {
                var rows_selected = publicValTable.column(0).checkboxes.selected();
                if (rows_selected.length === 1) {
                    var input_id = rows_selected[0];
                    await saveFileSetValModal(null, "val", input_id, null);
                }
                $("#inputValmodal").loading("stop");
                $("#inputValmodal").modal("hide");
            }
        } else {
            //edit item
            if (checkdata === "manualTabV") {
                var formValues = $("#inputValmodal").find("input");
                var data = formValues.serializeArray(); // convert form to array
                // check if file_path is entered
                data[1].value = $.trim(data[1].value);
                if (data[1].value !== "") {
                    await editFileSetValModal(data, "val", null, null);
                    $("#inputValmodal").loading("stop");
                    $("#inputValmodal").modal("hide");
                } else {
                    $("#inputValmodal").loading("stop");
                    showInfoModal(
                        "#infoModal",
                        "#infoModalText",
                        "Please enter or select values from table to fill 'Value' box."
                    );
                }
            } else if (checkdata === "publicValTab") {
                var rows_selected = publicValTable.column(0).checkboxes.selected();
                if (rows_selected.length === 1) {
                    var input_id = rows_selected[0];
                    await editFileSetValModal(null, "val", input_id, null);
                    $("#inputValmodal").loading("stop");
                    $("#inputValmodal").modal("hide");
                }
            }
        }
    });

    $("#confirmModal").on("show.bs.modal", function(e) {
        var button = $(e.relatedTarget);
        $("#confirmModal").data("buttonID", button.attr("id"));
        if (button.attr("id") === "deleteRun" || button.attr("id") === "delRun") {
            $("#confirmModalText").html("Are you sure you want to delete this run?");
        } else if (button.attr("id") === "deleteSample") {
            var selRows = $("#sampleTable")
                .DataTable()
                .rows({ selected: true })
                .data();
            var selRowsName = [];
            for (var i = 0; i < selRows.length; i++) {
                selRowsName.push(selRows[i].name);
            }
            var selRowsTxt = selRowsName.join("<br/>");
            $("#confirmModalText").html(
                "Are you sure you want to delete selected " +
                selRows.length +
                " file(s)?<br/><br/>File List:<br/>" +
                selRowsTxt
            );
        }
    });

    $("#confirmModal").on("click", "#deleteBtn", function(e) {
        e.preventDefault();
        var buttonID = $("#confirmModal").data("buttonID");
        if (buttonID === "deleteRun" || buttonID === "delRun") {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    id: project_pipeline_id,
                    p: "removeProjectPipeline",
                },
                async: true,
                success: function(s) {
                    window.location.replace("index.php?np=2&id=" + project_id);
                },
                error: function(errorThrown) {
                    alert("Error: " + errorThrown);
                },
            });
        } else if (buttonID === "deleteSample") {
            var selRows = $("#sampleTable")
                .DataTable()
                .rows({ selected: true })
                .data();
            var selRowsId = [];
            for (var i = 0; i < selRows.length; i++) {
                selRowsId.push(selRows[i].id);
            }
            if (selRowsId.length > 0) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: {
                        file_array: selRowsId,
                        p: "removeFile",
                    },
                    async: true,
                    success: function(s) {
                        if (s.length > 0) {
                            var removedCollection = s;
                            for (var i = 0; i < removedCollection.length; i++) {
                                removeCollectionFromInputs(removedCollection[i]);
                            }
                        }
                        $("#sampleTable").DataTable().ajax.reload(null, false);
                        $("#detailsOffileDiv").css("display", "none");
                        $("#detailsOfmetaDiv").css("display", "none");
                    },
                    error: function(errorThrown) {
                        alert("Error: " + errorThrown);
                    },
                });
            }
        }
    });

    //### pluplouder start

    $(function() {
        function log() {
            var str = "";
            plupload.each(arguments, function(arg) {
                var row = "";
                if (typeof arg != "string") {
                    plupload.each(arg, function(value, key) {
                        // Convert items in File objects to human readable form
                        if (arg instanceof plupload.File) {
                            // Convert status to human readable
                            switch (value) {
                                case plupload.QUEUED:
                                    value = "QUEUED";
                                    break;
                                case plupload.UPLOADING:
                                    value = "UPLOADING";
                                    break;
                                case plupload.FAILED:
                                    value = "FAILED";
                                    break;
                                case plupload.DONE:
                                    value = "DONE";
                                    break;
                            }
                        }

                        if (typeof value != "function") {
                            row += (row ? ", " : "") + key + "=" + value;
                        }
                    });

                    str += row + " ";
                } else {
                    str += arg + " ";
                }
            });
            var log = $("#pluploaderLog");
            log.append(str + "\n");
            log.scrollTop(log[0].scrollHeight);
        }

        function getTransferedFiles() {
            var done = 0;
            if (window["plupload_transfer_obj"]) {
                var obj = window["plupload_transfer_obj"];
                if (!jQuery.isEmptyObject(obj)) {
                    $.each(obj, function(el) {
                        if (window["plupload_transfer_obj"][el]["status"]) {
                            if (window["plupload_transfer_obj"][el]["status"] == "done") {
                                done++;
                            }
                        }
                    });
                }
            }
            return done;
        }

        function updateTransferedFiles() {
            var uploader = $("#pluploader").pluploadQueue();
            var totalFile = 0;
            var transferedFile = 0;
            var totalFile = uploader.files.length;
            var transferedFile = getTransferedFiles();
            if (transferedFile && totalFile) {
                if (totalFile === 1 && transferedFile == totalFile) {
                    $("#uploadSucSingleDiv").css("display", "inline");
                    $("#uploadSucDiv").css("display", "none");
                } else if (totalFile > 1 && transferedFile == totalFile) {
                    $("#uploadSucDiv").css("display", "inline");
                    $("#uploadSucSingleDiv").css("display", "none");
                }
            }
            if (transferedFile && totalFile && transferedFile == totalFile) {
                $("#plupload_tiny_spinner").css("display", "none");
            }

            if (totalFile) {
                if (!$(".plupload_filelist_footer").find("#plupload_tiny_spinner")[0]) {
                    $("span.plupload_transfer_status").after("<div id='plupload_tiny_spinner' class='tiny-spinner' style='display:none;'></div>")
                }

            }
            $("span.plupload_transfer_status").html("  Transfered " + transferedFile + "/" + totalFile + " files ");
        }
        //interval will decide the check period
        function checkRsyncTimer(up, fileName, fileId, interval) {
            window["interval_rsyncStatus_" + fileId] = setInterval(function() {
                runRsyncCheck(up, fileName, fileId);
            }, interval);
        }

        function upd_plupload_transfer_obj(fileId, status, transfer, rsyncPid) {
            if (typeof window["plupload_transfer_obj"] == "undefined") {
                window["plupload_transfer_obj"] = {};
            }
            if (typeof window["plupload_transfer_obj"][fileId] == "undefined") {
                window["plupload_transfer_obj"][fileId] = {};
            }
            if (status) {
                window["plupload_transfer_obj"][fileId]["status"] = status;
                if (status == "error") {
                    $("#" + fileId)
                        .attr("class", "plupload_failed")
                        .find("a")
                        .css("display", "block");
                } else if (status == "uploading") {
                    $("#" + fileId)
                        .attr("class", "plupload_rsync")
                        .find("a")
                        .css("display", "block");
                } else if (status == "done") {
                    $("#" + fileId)
                        .attr("class", "plupload_done")
                        .find("a")
                        .css("display", "block");
                }
            }
            if (transfer) {
                window["plupload_transfer_obj"][fileId]["transfer"] = transfer;
                $("#" + fileId + " > .plupload_file_transfer").text(transfer);
            }
            if (rsyncPid) {
                window["plupload_transfer_obj"][fileId]["rsync"] = rsyncPid;
            }
        }

        function runRsyncCheck(up, fileName, fileId) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    filename: fileName,
                    p: "getRsyncStatus",
                },
                async: true,
                success: function(s) {
                    if (s) {
                        console.log(s);
                        log("[TransferFile]", s);
                        if (s.match(/cannot create directory/)) {
                            upd_plupload_transfer_obj(fileId, "error", "Error", "");
                            clearInterval(window["interval_rsyncStatus_" + fileId]);
                        } else {
                            if (s.match(/\d+\%/)) {
                                var list = s.match(/\d+\%/g);
                                if (list.length > 0) {
                                    var percent = list[list.length - 1];
                                    upd_plupload_transfer_obj(fileId, "uploading", percent, "");
                                    if (percent.match(/100%/)) {
                                        upd_plupload_transfer_obj(fileId, "done", percent, "");
                                        clearInterval(window["interval_rsyncStatus_" + fileId]);
                                        log("[TransferFile]", "Done");
                                    }
                                } else {
                                    var percent = "0";
                                    upd_plupload_transfer_obj(fileId, "uploading", percent, "");
                                }
                            }
                        }
                        updateTransferedFiles();
                    }
                },
                error: function(errorThrown) {
                    console.log("Error: " + errorThrown);
                },
            });
        }

        var initPlupload = function() {
            $("#pluploader").pluploadQueue({
                runtimes: "html5,html4", //flash,silverlight
                url: "ajax/upload.php",
                chunk_size: "2mb",
                // to enable chunk_size larger than 2mb: "ajax/.htaccess file should have "php_value post_max_size 12M", "php_value upload_max_filesize 12M"
                // test for 320mb file :
                // chunk_size=10mb :take 130sec
                // chunk_size=3-4-5mb :take 80sec
                // chunk_size=1-2mb :take 110sec
                max_retries: 3,
                unique_names: true,
                multiple_queues: true,
                rename: true,
                dragdrop: true,
                multipart: true,
                //multipart_params : {'target_dir': "old"},
                filters: {
                    // Maximum file size
                    max_file_size: "2gb",
                },
                // PreInit events, bound before any internal events
                preinit: {
                    Init: function(up, info) {
                        log("[Init]", "Info:", info, "Features:", up.features);
                    },
                    UploadFile: function(up, file) {
                        log("[UploadFile]", file);
                        //                        up.stop();
                        // You can override settings before the file is uploaded
                        // up.setOption('url', 'upload.php?id=' + file.id);
                    },
                },
                // Post init events, bound after the internal events
                init: {
                    PostInit: function(up) {
                        // Called after initialization is finished and internal event handlers bound
                        log("[PostInit]");
                    },
                    Browse: function(up) {
                        // Called when file picker is clicked
                        log("[Browse]");
                    },
                    Refresh: function(up) {
                        // Called when the position or dimensions of the picker change
                        log("[Refresh]");
                        updateTransferedFiles();
                    },
                    StateChanged: function(up) {
                        // Called when the state of the queue is changed
                        log(
                            "[StateChanged]",
                            up.state == plupload.STARTED ? "STARTED" : "STOPPED"
                        );
                    },
                    QueueChanged: function(up) {
                        // Called when queue is changed by adding or removing files
                        log("[QueueChanged]");
                    },
                    OptionChanged: function(up, name, value, oldValue) {
                        // Called when one of the configuration options is changed
                        log(
                            "[OptionChanged]",
                            "Option Name: ",
                            name,
                            "Value: ",
                            value,
                            "Old Value: ",
                            oldValue
                        );
                    },
                    BeforeUpload: function(up, file) {
                        //Called right before the upload for a given file starts, can be used to cancel it if required
                        log("[BeforeUpload]", "File: ", file);
                        updateTransferedFiles();
                        var target_dir = $runscope.getUploadDir("exist");
                        var run_env = $("#chooseEnv").find(":selected").val();
                        if (target_dir && run_env) {
                            up.settings.multipart_params.target_dir = target_dir;
                            up.settings.multipart_params.run_env = run_env;
                        }
                        $("#plupload_tiny_spinner").css("display", "inline-block");
                    },
                    UploadProgress: function(up, file) {
                        // Called while file is being uploaded
                        log("[UploadProgress]", "File:", file, "Total:", up.total);
                    },
                    FileFiltered: function(up, file) {
                        // Called when file successfully files all the filters
                        log("[FileFiltered]", "File:", file);
                    },
                    FilesAdded: function(up, files) {
                        // Called when files are added to queue
                        log("[FilesAdded]");
                        //get files in the target directory
                        var target_dir = $runscope.getUploadDir("exist");
                        var amazon_cre_id = "";
                        var google_cre_id = "";
                        var dirList = getValues({
                            p: "getLsDir",
                            dir: target_dir,
                            profileType: proTypeWindow,
                            profileId: proIdWindow,
                            amazon_cre_id: amazon_cre_id,
                            google_cre_id: google_cre_id,
                            project_pipeline_id: project_pipeline_id,
                        });
                        console.log(dirList);
                        var fileArr = [];
                        var errorAr = [];
                        if (dirList) {
                            dirList = $.trim(dirList);
                            fileArr = dirList.split("\n");
                            errorAr = fileArr.filter((line) => line.match(/ls:/));
                            fileArr = fileArr.filter((line) => !line.match(/:/));
                        }
                        var removedFiles = [];
                        var dupFiles = [];
                        var emptyFiles = [];
                        console.log(fileArr);
                        //check if file is found in the targetdir -> remove file and give warning
                        plupload.each(files, function(file) {
                            //remove files that has no size
                            if (file.size == 0) {
                                emptyFiles.push(file.name);
                                up.removeFile(file);
                            }
                            //remove duplicate file
                            var upfile = $.grep(up.files, function(v) {
                                return v.name === file.name;
                            });
                            if (upfile.length > 0) {
                                if (upfile[0] != file) {
                                    dupFiles.push(file.name);
                                    up.removeFile(file);
                                }
                            }
                            var containsSpecialChars = function(str) {
                                    //with space as special character
                                    const specialChars = /[ `!#$%^&*()+\=\[\]{};':"\\|,<>\/?~]/;
                                    return specialChars.test(str);
                                }
                                //remove file if it has paranthesis or space in the name 
                            if (containsSpecialChars(file.name)) {
                                removedFiles.push(file.name);
                                up.removeFile(file);
                            }
                            log("  File:", file);
                        });

                        var delRowsTxt = "";
                        var dupFilesTxt = "";
                        var emptyFilesTxt = "";
                        if (removedFiles.length > 0) {
                            var delRowsTxt = "Please don't use special character or space in your file name.<br/>Following file(s) removed from download queue.<br/><br/>File List:<br/>" +
                                removedFiles.join("<br/>") +
                                "<br/><br/>";
                        }
                        if (dupFiles.length > 0) {
                            var dupFilesTxt =
                                "Following file(s) already found in the queue list. <br/><br/>File List:<br/>" +
                                dupFiles.join("<br/>") +
                                "<br/><br/>";
                        }
                        if (emptyFiles.length > 0) {
                            var emptyFilesTxt =
                                "Following file(s) are empty and removed from the download queue. <br/><br/>File List:<br/>" +
                                emptyFiles.join("<br/>") +
                                "<br/><br/>";
                        }
                        if (
                            removedFiles.length > 0 ||
                            dupFiles.length > 0 ||
                            emptyFiles.length > 0
                        ) {
                            showInfoModal(
                                "#infoModal",
                                "#infoModalText",
                                delRowsTxt + dupFilesTxt + emptyFilesTxt
                            );
                        }
                    },
                    FilesRemoved: function(up, files) {
                        // Called when files are removed from queue
                        log("[FilesRemoved]");
                        plupload.each(files, function(file) {
                            log("  File:", file);
                        });
                    },
                    FileUploaded: function(up, file, info) {
                        // Called when file has finished uploading
                        log("[FileUploaded] File:", file, "Info:", info);
                        console.log(file);
                        console.log(info);
                        console.log(up);
                        var fileName = file.name;
                        var fileId = file.id;
                        var fileState = file.state; //5==done
                        if (fileState == 5 && fileName) {
                            if (info) {
                                console.log(info);
                                if (info.response) {
                                    console.log(info.response);
                                    if (IsJsonString(info.response)) {
                                        var json = JSON.parse(info.response);
                                        console.log(json);
                                        if (json) {
                                            if (json.rsync_log) {
                                                var pid = $.trim(json.rsync_log);
                                                upd_plupload_transfer_obj(fileId, "", "", pid);
                                            }
                                            if (!json.error) {
                                                //start reading log from rsync each 10 sec.
                                                checkRsyncTimer(up, fileName, fileId, 10000);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    },
                    ChunkUploaded: function(up, file, info) {
                        // Called when file chunk has finished uploading
                        log("[ChunkUploaded] File:", file, "Info:", info);
                    },
                    UploadComplete: function(up, files) {
                        // Called when all files are either uploaded or failed
                        log("[UploadComplete]");

                    },
                    Destroy: function(up) {
                        // Called when uploader is destroyed
                        log("[Destroy] ");
                    },
                    Error: function(up, args) {
                        // Called when error occurs
                        log("[Error] ", args);
                    },
                },
            });
        };
        initPlupload();

        $("#addFileModal").on("click", ".plupload_start_dummy", async function(e) {
            var target_dir = $runscope.getUploadDir("exist");
            var run_env = $("#chooseEnv").find(":selected").val();
            var warning = "";
            if (target_dir && run_env) {
                var chkRmDirWritable = await doAjax({
                    p: "chkRmDirWritable",
                    dir: target_dir,
                    run_env: run_env,
                });
                console.log(chkRmDirWritable);
                if (chkRmDirWritable.match(/writeable/)) {
                    $(".plupload_start").trigger("click");
                } else {
                    warning += "Write permission denied for your target directory.";
                    showInfoModal("#infoModal", "#infoModalText", warning);
                }
            } else {
                if (!target_dir && !run_env) {
                    warning +=
                        "Please choose your run environment and enter target directory and try again.";
                } else if (!target_dir) {
                    warning += "Please enter target directory and try again.";
                } else if (!run_env) {
                    warning += "Please choose your run environment and try again.";
                }
                showInfoModal("#infoModal", "#infoModalText", warning);
            }
        });

        //xxxx
        $("#addFileModal").on(
            "click",
            ".plupload_rsync > .plupload_file_action > a",
            async function(e) {
                var clickedFileUID = $(e.target).closest("li").attr("id");
                var uploader = $("#pluploader").pluploadQueue();
                var files = uploader.files;
                var fileName = "";
                for (var i = 0; i < files.length; i++) {
                    var fileID = files[i].uid;
                    if (fileID == clickedFileUID) {
                        fileName = files[i].name;
                        break;
                    }
                }
                var target_dir = $runscope.getUploadDir("exist");
                var run_env = $("#chooseEnv").find(":selected").val();
                if (fileName && target_dir && run_env) {
                    var retryRsync = await doAjax({
                        p: "retryRsync",
                        dir: target_dir,
                        run_env: run_env,
                        filename: fileName,
                    });
                    console.log(retryRsync);
                }
            }
        );

        $("#addFileModal").on("click", "#pluploaderReset", async function(e) {
            var uploader = $("#pluploader").pluploadQueue();
            var files = uploader.files;
            for (var i = 0; i < files.length; i++) {
                var fileID = files[i].uid;
                var fileName = files[i].name;
                if (window["plupload_transfer_obj"]) {
                    if (window["plupload_transfer_obj"][fileID]) {
                        if (window["interval_rsyncStatus_" + fileID]) {
                            clearInterval(window["interval_rsyncStatus_" + fileID]);
                        }
                        if (
                            window["plupload_transfer_obj"][fileID]["status"] &&
                            window["plupload_transfer_obj"][fileID]["rsync"]
                        ) {
                            if (window["plupload_transfer_obj"][fileID]["status"] != "done") {
                                var killRsync = await doAjax({
                                    p: "resetUpload",
                                    filename: fileName,
                                });
                            }
                        }
                    }
                }
            }
            uploader.splice(0);
            uploader.destroy();
            window["plupload_transfer_obj"] = {};
            initPlupload();
            $("#uploadSucDiv").css("display", "none");
            $("#uploadSucSingleDiv").css("display", "none");
        });

        //        $('#addFileModal').on('click', '#pluploaderStop', function (e) {
        ////            var uploader = $("#pluploader").pluploadQueue();
        ////            uploader.stop()
        ////            var uploader = $("#pluploader").pluploadQueue();
        ////            var copiedObj = $.extend(true, {}, uploader);
        ////            console.log(copiedObj)
        ////            var fileList = []
        ////            var files = copiedObj.files;
        ////            for (var i = 0; i < files.length; i++) {
        ////                var fileObj  = files[i].getSource()
        ////                fileList.push(fileObj)
        ////            }
        ////            uploader.splice(0);
        ////            uploader.destroy();
        ////            initPlupload();
        //            //** objectler icin:
        ////            var copiedObj = $.extend(true, {}, orObj);
        //
        ////            var uploaderNew = $("#pluploader").pluploadQueue();
        ////            for (var i = 0; i < fileList.length; i++) {
        ////                uploaderNew.addFile(fileList[i])
        ////            }
        ////            $(".plupload_buttons,.plupload_upload_status,.plupload_transfer_status").css("display", "inline");
        //        });
    });




    $("#inputFilemodal").on("click", "#uploadManualFile", async function(e) {
        $("#addFileModal").modal("show");
        $('.nav-tabs a[href="#uploadFiles"]').tab("show");
    });

    $("#addFileModal").on("click", "#selectAsManually", async function(e) {
        var uploader = $("#pluploader").pluploadQueue();
        var totalFile = 0;
        var transferedFile = 0;
        var totalFile = uploader.files
        if (totalFile.length < 1) {
            showInfoModal("#infoMod", "#infoModText", "Please first upload the file.");
        } else if (totalFile.length > 0) {
            var finalFile = totalFile[totalFile.length - 1].name
            if (finalFile) {
                var targetDir = $("#target_dir").val()
                finalFile = targetDir + "/" + finalFile
                var data = [];
                data.push({ name: "id", value: "" });
                data.push({ name: "name", value: finalFile });
                var savetype = $("#mIdFile").val();
                if (!savetype.length) {
                    //add item
                    await saveFileSetValModal(data, "file", null, null);
                } else {
                    //edit item
                    await editFileSetValModal(data, "file", null, null);
                }
                $("#addFileModal").modal("hide");
                $("#inputFilemodal").modal("hide");
            }
        }
    })

    $("#addFileModal").on("click", "#showHostFiles", async function(e) {
        var perms = $("#chooseEnv").find(":selected").attr("perms");
        var sharedProfile = false;
        if (perms) {
            if (perms == "15") {
                sharedProfile = true;
            }
        }
        var target_dir = $runscope.getUploadDir("exist");
        $("#file_dir").val(target_dir);
        if (sharedProfile) {
            await viewDirButSearch(target_dir);
            $("#file_dir_div").css("display", "none");
            $("#viewDirInfo").css("display", "none");
            $("#viewDir > option").attr("style", "pointer-events: none;");
            $("#addFileModal").find('.nav-tabs a[href="#hostFiles"]').tab("show");
        } else {
            $("#viewDir > option").attr("style", "pointer-events: auto;");
            $("#viewDirBut").trigger("click");
            $("#addFileModal").find('.nav-tabs a[href="#hostFiles"]').tab("show");
        }
    });

    //### pluplouder ends

    //######### copy/move runs
    $("#confirmDuplicate").on("click", "#moveRunBut", async function(e) {
        var new_project_id = getTargetProject();
        if (!new_project_id) {
            showInfoModal(
                "#infoMod",
                "#infoModText",
                "Please choose one of the projects."
            );
        } else {
            // check if moving operation is valid.
            var numOfErr;
            var target_group_id;
            var target_perms;
            [numOfErr, target_group_id, target_perms] =
            await validateMoveCopyRun(new_project_id);
            if (!numOfErr) {
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: {
                        old_project_id: project_id,
                        new_project_id: new_project_id,
                        project_pipeline_id: project_pipeline_id,
                        perms: target_perms,
                        group_id: target_group_id,
                        p: "moveRun",
                    },
                    async: true,
                    success: function(s) {
                        setTimeout(function() {
                            window.location.replace(
                                "index.php?np=3&id=" + project_pipeline_id
                            );
                        }, 0);
                    },
                    error: function(errorThrown) {
                        alert("Error: " + errorThrown);
                    },
                });
            }
        }
    });
    $("#confirmDuplicate").on("click", "#copyRunBut", async function(e) {
        var new_project_id = getTargetProject();
        if (!new_project_id) {
            showInfoModal(
                "#infoMod",
                "#infoModText",
                "Please choose one of the projects."
            );
        } else {
            confirmNewRev = true;
            dupliProPipe = true;
            await saveRun(false, true);
        }
    });

    $("#projectmodal").on("show.bs.modal", function(event) {
        $(this).find("form").trigger("reset");
    });

    $("#projectmodal").on("click", "#saveproject", function(event) {
        event.preventDefault();
        var projectName = $("#mProjectName").val();
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: { name: projectName, summary: "", p: "saveProject" },
            async: true,
            success: function(s) {
                refreshProjectDatatable("default");
                $("#projectmodal").modal("hide");
            },
            error: function(errorThrown) {
                alert("Error: " + errorThrown);
            },
        });
    });

    //######### copy/move runs end

    $(function() {
        $(document).on("click", ".updateIframe", function(event) {
            var href = $(this).attr("href");
            var iframe = $(href).find("iframe");
            //update iframe in case its a txt file
            if (iframe && iframe.attr("src") && !href.match(/_html/)) {
                iframe.attr("src", "");
                setTimeout(function() {
                    iframe.attr("src", iframe.attr("fillsrc"));
                }, 100);
            }
            //load iframe when first time it is clicked
            if (iframe && iframe.attr("fillsrc") && !iframe.attr("src")) {
                iframe.attr("src", iframe.attr("fillsrc"));
            }
        });
    });
    var bindEveHandlerIcon = function(fileid, visType, pubWebPath, uuid) {
        $('[data-toggle="tooltip"]').tooltip();
        $("#fullscr-" + fileid).on("click", function(event) {
            var iconClass = $(this).children().attr("class");
            var toogleType;
            if (iconClass == "fa fa-expand") {
                $(this).children().attr("class", "fa fa-compress");
                toogleType = "expand";
            } else {
                $(this).children().attr("class", "fa fa-expand");
                toogleType = "compress";
            }
            toogleFullSize(this, toogleType);
            if (visType == "text") {
                toogleEditorSize(toogleType);
            }
        });
        $(document).on("click", "#downUrl-" + fileid, function(event) {
            event.preventDefault();
            var fileid = $(this).attr("fileid");
            var filename = $("#" + fileid).attr("filename");
            var filepath = $("#" + fileid).attr("filepath");
            var a = document.createElement("A");
            var url = pubWebPath + "/" + uuid + "/pubweb/" + filepath;
            download_file(url, filename);
        });
        $("#blankUrl-" + fileid).on("click", function(event) {
            var fileid = $(this).attr("fileid");
            var filepath = $("#" + fileid).attr("filepath");
            var url = pubWebPath + "/" + uuid + "/pubweb/" + filepath;
            if (!filepath) {
                var ucsc_gb_tab = $(this).closest("div.ucsc_gb_tab")
                if (ucsc_gb_tab[0]) {
                    url = ucsc_gb_tab.find("iframe").attr("src")
                }
            }
            var w = window.open();
            w.location = url;
        });
    };
    var getHeaderIconDiv = function(fileid, visType) {
        var blankUrlIcon = "";
        var downloadIcon = "";
        // "ucsc_genome_browser"
        if (visType !== "table-percent" && visType !== "table" && visType !== "debrowser" && visType !== "ucsc_genome_browser_metadata") {
            blankUrlIcon = `<li role="presentation"><a fileid="` + fileid + `" id="blankUrl-` +
                fileid + `" data-toggle="tooltip" data-placement="bottom" data-original-title="Open in a New Window"><i style="font-size: 18px;" class="fa fa-external-link"></i></a></li>`;
        }
        if (visType !== "ucsc_genome_browser") {
            downloadIcon = `<li role="presentation"><a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i style="font-size: 18px;" class="fa fa-download"></i> <span class="caret"></span></a>
            <ul class="dropdown-menu dropdown-menu-right">
            <li><a fileid="` + fileid + `" id="downUrl-` + fileid + `" href="#">Download</a></li> </ul> </li>`;
        }

        var content = `<ul style="float:inherit"  class="nav nav-pills panelheader">` + blankUrlIcon +
            ` <li role="presentation"><a fileid="` +
            fileid + `" id="fullscr-` + fileid + `" data-toggle="tooltip" data-placement="bottom" data-original-title="Toogle Full Screen"><i style="font-size: 18px;" class="fa fa-expand"></i></a></li>` + downloadIcon + `</ul>`;
        var wrapDiv = '<div id="' + fileid + '-HeaderIconDiv" style="float:right; height:35px; width:100%;">' +
            content + "</div>";
        return wrapDiv;
    };

    const addUcscDatatableLinks = (obj) => {
        if (obj && obj.columns && obj.columns[0]) {
            obj.columns[0] = {
                "title": "Genes",
                "fnCreatedCell": function(nTd, sData, oData, iRow, iCol) {
                    $(nTd).html(`<a class="ucsc_gene_link" href="#" gene="${oData[0]}">${oData[0]}</a>`);
                }
            }
        }
        return obj
    }

    // visType == "html","pdf","image","rdata" 
    const loadIframeView = (visType, filePath, pubWebPath, uuid, fileid, dir, filename, href) => {
        var ext = getExtension(filePath);
        var link = pubWebPath + "/" + uuid + "/" + "pubweb" + "/" + filePath;
        var checkIfValidIframeExt = function(ext) {
            var ret = false;
            var validList = [
                "pdf",
                "jpeg",
                "html",
                "jpg",
                "png",
                "gif",
                "tif",
                "tiff",
                "svg",
                "txt",
            ];
            if (validList.includes(ext)) {
                ret = true;
            }
            return ret;
        };
        var content = "";
        if (visType == "pdf" && ext && ext.toLowerCase() == "pdf") {
            content =
                '<object style="width:100%; height:100%;"  data="' +
                link +
                '" type="application/pdf"><embed src="' +
                link +
                '" type="application/pdf" /></object>';
        } else {
            var validExt = checkIfValidIframeExt(ext);
            if (validExt) {
                content =
                    '<iframe frameborder="0"  style="width:100%; height:100%;" src="' +
                    link +
                    '"></iframe>';
            } else {
                content =
                    '<div style="text-align:center; vertical-align:middle; line-height: 300px;">File preview is not available, click to <a class="link-underline" fileid="' +
                    fileid +
                    '" id="downUrl-' +
                    fileid +
                    '" href="#">download</a> the file.</div>';
            }
        }
        var contentDiv =
            getHeaderIconDiv(fileid, visType) +
            '<div style="width:100%; height:calc(100% - 35px);" dir="' + dir + '" filename="' + filename + '" filepath="' + filePath + '" id="' + fileid + '">' + content + "</div>";
        $(href).append(contentDiv);
        bindEveHandlerIcon(fileid, visType, pubWebPath, uuid);
    }

    const loadTableView = (visType, filePath, fileid, dir, filename, href, uuid, pubWebPath) => {
        var ext = getExtension(filePath);
        var validList = ["csv", "tsv", "txt"];
        var iframeList = [
            "pdf",
            "jpeg",
            "html",
            "jpg",
            "png",
            "gif",
            "tif",
            "tiff",
            "svg",
            "txt",
        ];
        console.log(ext)
        if (validList.includes(ext)) {
            var headerStyle = "";
            var tableStyle = "";
            if (visType == "table-percent") {
                headerStyle = "white-space: nowrap;";
            } else {
                tableStyle = "white-space: nowrap; table-layout:fixed;";
            }
            var contentDiv =
                getHeaderIconDiv(fileid, visType) +
                '<div style="margin-left:15px; margin-right:15px; margin-bottom:15px; overflow-x:auto; width:calc(100% - 35px);" class="table-responsive"><table style="' +
                headerStyle +
                ' border:none;  width:100%;" class="table table-striped table-bordered" cellspacing="0"  dir="' +
                dir +
                '" filename="' +
                filename +
                '" filepath="' +
                filePath +
                '" id="' +
                fileid +
                '"><thead style="' +
                tableStyle +
                '" "></thead></table></div>';
            $.ajax({
                url: "ajax/ajaxquery.php",
                data: {
                    p: "getFileContent",
                    uuid: uuid,
                    filename: "pubweb/" + filePath,
                },
                async: true,
                beforeSend: function() {
                    showLoadingDiv(href.substr(1));
                },
                cache: false,
                type: "POST",
                success: function(data) {
                    $(href).append(contentDiv);
                    var fixHeader = true;
                    var dataTableObj;
                    if (ext && ext.toLowerCase() == "csv") {
                        dataTableObj = tsvCsvDatatablePrep(data, fixHeader, ",");
                    } else {
                        if (visType == "table-percent") {
                            //by default based on second column data, calculate percentages for each row
                            data = tsvPercent(data);
                        }
                        dataTableObj = tsvCsvDatatablePrep(data, fixHeader, "\t");
                    }
                    if (visType == "ucsc_genome_browser_metadata") {
                        dataTableObj = addUcscDatatableLinks(dataTableObj)
                    }
                    //speed up the table loading
                    dataTableObj.deferRender = true;
                    dataTableObj.scroller = true;
                    dataTableObj.scrollCollapse = true;
                    dataTableObj.scrollY = 395;
                    dataTableObj.scrollX = true;
                    dataTableObj.sScrollX = true;
                    //hides undefined error
                    dataTableObj.columnDefs = [
                        { defaultContent: "-", targets: "_all" },
                    ];
                    $("#" + fileid).DataTable(dataTableObj);
                    hideLoadingDiv(href.substr(1));
                    bindEveHandlerIcon(fileid, visType, pubWebPath, uuid);
                },
                error: function(errorThrown) {
                    hideLoadingDiv(href.substr(1));
                    console.log("AJAX Error occured.");
                    var content =
                        '<div style="text-align:center; vertical-align:middle; line-height: 300px;">File preview is not available, click to <a class="link-underline" fileid="' +
                        fileid +
                        '" id="downUrl-' +
                        fileid +
                        '" href="#">download</a> the file.</div>';
                    var contentDiv =
                        getHeaderIconDiv(fileid, visType) +
                        '<div style="width:100%; height:calc(100% - 35px);" dir="' +
                        dir +
                        '" filename="' +
                        filename +
                        '" filepath="' +
                        filePath +
                        '" id="' +
                        fileid +
                        '">' +
                        content +
                        "</div>";
                    $(href).append(contentDiv);
                    bindEveHandlerIcon(fileid, visType, pubWebPath, uuid);
                },
            });
        } else if (iframeList.includes(ext)) {
            loadIframeView(visType, filePath, pubWebPath, uuid, fileid, dir, filename, href)
        } else {
            var content =
                '<div style="text-align:center; vertical-align:middle; line-height: 300px;">File preview is not available, click to <a class="link-underline" fileid="' +
                fileid +
                '" id="downUrl-' +
                fileid +
                '" href="#">download</a> the file.</div>';
            var contentDiv =
                getHeaderIconDiv(fileid, visType) +
                '<div style="width:100%; height:calc(100% - 35px);" dir="' +
                dir +
                '" filename="' +
                filename +
                '" filepath="' +
                filePath +
                '" id="' +
                fileid +
                '">' +
                content +
                "</div>";
            $(href).append(contentDiv);
            bindEveHandlerIcon(fileid, visType, pubWebPath, uuid);
        }

    }

    //$(document) allows to trigger when a.reportFile added later into the DOM
    $(function() {




        $(document).on("shown.bs.tab click", "a.reportFile", async function(event) {
            var href = $(this).attr("href");
            // change height of the div to reload its content for pdf/iframe
            var currHeight = $(href).closest("div.fullsize").css("height")
            if (currHeight == "600px") {
                $(href).closest("div.fullsize").css("height", "601px")
            } else {
                $(href).closest("div.fullsize").css("height", "600px")
            }

            $(href).removeClass("fade").addClass("active in");
            $(href).siblings().removeClass("active in").addClass("fade");
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            //check if div is empty
            if (!$.trim($(href).html()).length) {
                var writePerm = await $runscope.checkUserWritePerm();
                var uuid = $("#runVerLog").val();
                var visType = $(this).attr("visType");
                var filePath = $(this).attr("filepath");
                var appID = $(this).attr("appID");
                var split = filePath.split("/");
                var filename = "";
                var dir = "";
                if (split.length > 1) {
                    filename = split[split.length - 1];
                    dir = filePath.substring(0, filePath.indexOf(filename));
                }
                var fileid = $(this).attr("fileid");
                var pubWebPath = $("#basepathinfo").attr("pubweb");
                var debrowserUrl = $("#basepathinfo").attr("debrowser");

                var toogleEditorSize = function(toogleType) {
                    var editorID = "textEditorID_" + fileid;
                    if (toogleType == "expand") {
                        $("#" + editorID).css("height", $(window).height() - 35 - 45);
                    } else {
                        $("#" + editorID).css("height", "525px");
                    }
                    setTimeout(function() {
                        window[editorID].resize();
                        window[editorID].setOption("wrap", false);
                        window[editorID].setOption("wrapBehavioursEnabled", false);
                        window[editorID].setOption("wrap", true);
                    }, 100);
                };



                if (visType == "table" || visType == "table-percent") {
                    loadTableView(visType, filePath, fileid, dir, filename, href, uuid, pubWebPath)
                } else if (visType == "rmarkdown") {
                    var contentDiv =
                        '<div style="width:100%;" dir="' +
                        dir +
                        '" filename="' +
                        filename +
                        '" filepath="' +
                        filePath +
                        '" id="' +
                        fileid +
                        '"></div>';
                    $(href).append(contentDiv);
                    var data = await doAjax({
                        p: "getFileContent",
                        uuid: uuid,
                        filename: "pubweb/" + filePath,
                    });
                    $("#" + fileid).rMarkEditor({
                        ajax: {
                            url: "ajax/ajaxquery.php",
                            text: data,
                            uuid: uuid,
                            dir: dir,
                            filename: filename,
                            pubWebPath: pubWebPath,
                            editable: writePerm,
                        },
                        editorWidth: "60%",
                        reportWidth: "40%",
                        height: "565px",
                        theme: "monokai", //tomorrow
                    });
                } else if (visType == "apps") {
                    var pubWebApp = {}
                    if (appID) {
                        pubWebApp.app = appID
                    }
                    var contentDiv =
                        '<div style="width:100%;" dir="' +
                        dir +
                        '" filename="' +
                        filename +
                        '" filepath="' +
                        filePath +
                        '" id="' +
                        fileid +
                        '"></div>';
                    $(href).append(contentDiv);
                    var data = await doAjax({
                        p: "getFileContent",
                        uuid: uuid,
                        filename: "pubweb/" + filePath,
                    });
                    $("#" + fileid).appEditor({
                        ajax: {
                            url: "ajax/ajaxquery.php",
                            text: data,
                            uuid: uuid,
                            dir: dir,
                            filename: filename,
                            pubWebPath: pubWebPath,
                            editable: writePerm,
                        },
                        editorWidth: "60%",
                        reportWidth: "40%",
                        height: "565px",
                        theme: "monokai", //tomorrow
                        pubWebApp: pubWebApp
                    });
                } else if (visType == "markdown") {
                    var contentDiv =
                        '<div style="width:100%;" dir="' +
                        dir +
                        '" filename="' +
                        filename +
                        '" filepath="' +
                        filePath +
                        '" id="' +
                        fileid +
                        '"></div>';
                    $(href).append(contentDiv);
                    var data = await doAjax({
                        p: "getFileContent",
                        uuid: uuid,
                        filename: "pubweb/" + filePath,
                    });

                    $("#" + fileid).markdownEditor({
                        ajax: {
                            data: {
                                text: data,
                                uuid: uuid,
                                pubWebPath: pubWebPath,
                                filePath: filePath,
                                editable: writePerm,
                            },
                        },
                        backgroundcolorenter: "#ced9e3",
                        backgroundcolorleave: "#ECF0F4",
                        height: "565px",
                    });
                } else if (
                    visType == "html" ||
                    visType == "pdf" ||
                    visType == "image" ||
                    visType == "rdata"
                ) {
                    loadIframeView(visType, filePath, pubWebPath, uuid, fileid, dir, filename, href)
                } else if (visType == "text") {
                    var link = pubWebPath + "/" + uuid + "/" + "pubweb" + "/" + filePath;
                    var editorID = "textEditorID_" + fileid;
                    var scriptModeDivID = "textScriptModeID_" + fileid;
                    var scriptMode = "textScriptMode_" + fileid;
                    var aceEditorDiv =
                        `<div id="` +
                        editorID +
                        `" style="height:525px; width: 100%;"></div>
<div style="display:none;" id="` +
                        scriptModeDivID +
                        `" class="row">
<p class="col-sm-4" style="padding-top:4px; padding-right:0; padding-left:60px;">Language Mode:</p>
<div class="col-sm-3">
<select id="` +
                        scriptMode +
                        `" class="form-control">
<option value="markdown">markdown</option>
</select>
</div>
</div>`;
                    var contentDiv =
                        getHeaderIconDiv(fileid, visType) +
                        '<div style="width:100%; height:calc(100% - 35px);" dir="' +
                        dir +
                        '" filename="' +
                        filename +
                        '" filepath="' +
                        filePath +
                        '" id="' +
                        fileid +
                        '">' +
                        aceEditorDiv +
                        "</div>";
                    $(href).append(contentDiv);
                    bindEveHandlerIcon(fileid, visType, pubWebPath, uuid);
                    $.ajax({
                        url: "ajax/ajaxquery.php",
                        data: {
                            p: "getFileContent",
                            uuid: uuid,
                            filename: "pubweb/" + filePath,
                        },
                        async: true,
                        beforeSend: function() {
                            showLoadingDiv(href.substr(1));
                        },
                        cache: false,
                        type: "POST",
                        success: function(data) {
                            // create ace editor
                            window[editorID] = ace.edit(editorID);
                            window[editorID].setValue(data);
                            window[editorID].clearSelection();
                            window[editorID].setOption("wrap", true);
                            window[editorID].setOption("indentedSoftWrap", false);
                            window[editorID].setTheme("ace/theme/tomorrow");
                            window[editorID].$blockScrolling = Infinity;
                            window[editorID].setReadOnly(true);
                            window[editorID].setShowPrintMargin(false);
                            hideLoadingDiv(href.substr(1));
                        },
                        error: function(errorThrown) {
                            hideLoadingDiv(href.substr(1));
                            console.log("AJAX Error occured.", data);
                            toastr.error("Error occured.");
                        },
                    });
                } else if (visType == "debrowser") {
                    if (filename.match("debrowser_metadata")) {
                        loadTableView(visType, filePath, fileid, dir, filename, href, uuid, pubWebPath)
                    } else {
                        $.ajax({
                            url: "ajax/ajaxquery.php",
                            data: {
                                p: "callDebrowser",
                                dir: dir,
                                uuid: uuid,
                                filename: filename,
                            },
                            async: true,
                            cache: false,
                            beforeSend: function() {
                                showLoadingDiv(href.substr(1));
                            },
                            type: "POST",
                            success: function(jsonData) {
                                console.log(jsonData)
                                var filePathJson = jsonData.count_file
                                var metadataFile = ""
                                if (jsonData.metadata_file) {
                                    metadataFile = "&meta=" + encodeURIComponent(pubWebPath + "/" + uuid + "/" + "pubweb" + "/" + jsonData.metadata_file);
                                }
                                var link = encodeURIComponent(pubWebPath + "/" + uuid + "/" + "pubweb" + "/" + filePathJson) + metadataFile;

                                var debrowserlink =
                                    debrowserUrl + link;
                                console.log(debrowserlink)
                                var iframe =
                                    '<iframe id="deb-' +
                                    fileid +
                                    '" frameborder="0"  style="width:100%; height:100%;" src="' +
                                    debrowserlink +
                                    '"></iframe>';
                                var contentDiv =
                                    getHeaderIconDiv(fileid, visType) +
                                    '<div style="width:100%; height:calc(100% - 35px);" dir="' +
                                    dir +
                                    '" filename="' +
                                    filename +
                                    '" filepath="' +
                                    filePath +
                                    '" id="' +
                                    fileid +
                                    '">' +
                                    iframe +
                                    "</div>";
                                $(href).append(contentDiv);
                                $("#deb-" + fileid).load(function() {
                                    hideLoadingDiv(href.substr(1));
                                });
                                bindEveHandlerIcon(fileid, visType, pubWebPath, uuid);
                            },
                            error: function(errorThrown) {
                                console.log("AJAX Error occured.", data);
                                hideLoadingDiv(href.substr(1));
                                toastr.error("Error occured.");
                            },
                        });
                    }


                }
            }
        });
    });

    //left tab-pane collapse
    //fix dataTable column width in case, width of the page is changed while panel closed
    $(function() {
        $(document).on("shown.bs.collapse", ".tab-pane", function(event) {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        });
    });
    //main row panel collapse
    $(function() {
        $(document).on("shown.bs.collapse", ".collapseRowBody", function(event) {
            $(this).find("li.active > a").trigger("click");
        });
    });

    $.getScript("js/plugins/markdownEditor.js", function() {
        //        console.log("markdownEditor.js loaded");
    });

    $.getScript("js/plugins/rMarkEditor.js", function() {
        //        console.log("rMarkEditor.js loaded");
    });
    $.getScript("js/plugins/appEditor.js", function() {
        //        console.log("appEditor.js loaded");
    });

    //################################
    // --dynamicRows jquery plugin --
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
                        heightTitle: "50px",
                        lineHeightTitle: "50px",
                    },
                    options
                );
                var elems = $(this);
                var elemsID = $(this).attr("id");
                elems.data("settings", settings);
                var data = getData(settings);
                // hide add run notes button
                if (data) {
                    $("#addRunNotes").css("display", "none")
                    if ($runscope.projectpipelineOwn === "1") {
                        $("#addRunNotes").css("display", "inline-block")
                        let runNoteCheck = data.filter(i => i.name == "_Description")
                        if (runNoteCheck && runNoteCheck[0]) {
                            $("#addRunNotes").css("display", "none")
                        }
                    }

                }

                if (data === undefined || data == null || data == "") {
                    elems.append(
                        '<div  style="font-weight:900; line-height:' +
                        settings.lineHeightTitle +
                        "height:" +
                        settings.heightTitle +
                        ';">No data available to report</div>'
                    );
                } else {
                    // append panel
                    var title = getTitle(data[0], settings);
                    if (title) {
                        elems.append(title);
                    }
                    $(data).each(function(i) {
                        elems.append(getPanel(data[i], settings, elemsID));
                    });
                    // after appending panel
                    afterAppendPanel(data, settings, elemsID, elems);
                    refreshHandler(settings);
                }
                return this;
            },
            fnRefresh: function(content) {
                var elems = $(this);
                var elemsID = $(this).attr("id");
                var settings = elems.data("settings");
                var data = getData(settings);
                if (content.type == "columnsBody") {
                    $(data).each(function(i) {
                        var dataObj = data[i];
                        var id = dataObj.id;
                        var existWrapBody = $("#" + elemsID + "-" + id);
                        if (existWrapBody) {
                            var existBodyDiv = existWrapBody.children().children();
                            var col = settings.columnsBody;
                            $.each(existBodyDiv, function(el) {
                                if (existBodyDiv[el]) {
                                    if (content.id && content.id == dataObj.id) {
                                        getColumnContent(dataObj, col[el], existBodyDiv[el]);
                                    } else {
                                        getColumnContent(dataObj, col[el], existBodyDiv[el]);
                                    }
                                }
                            });
                        }
                    });
                    if (content.callback) {
                        content.callback();
                    }
                }
                return this;
            },
        };

        $.fn.dynamicRows = function(methodOrOptions) {
            if (methods[methodOrOptions]) {
                return methods[methodOrOptions].apply(
                    this,
                    Array.prototype.slice.call(arguments, 1)
                );
            } else if (typeof methodOrOptions === "object" || !methodOrOptions) {
                // Default to "init"
                return methods.init.apply(this, arguments);
            } else {
                $.error(
                    "Method " + methodOrOptions + " does not exist on jQuery.tooltip"
                );
            }
        };

        var afterAppendPanel = function(dataObj, settings, elemsID, elems) {
            //            createModal()
        };

        var refreshHandler = function(settings) {
            $(function() {
                $('[data-toggle="tooltip"]').tooltip();
            });
            $(".collapseRowDiv").on({
                mouseenter: function() {
                    $(this).css("background-color", settings.backgroundcolorenter);
                },
                mouseleave: function() {
                    $(this).css("background-color", settings.backgroundcolorleave);
                },
            });

            var refreshUcscIframe = (link, navTabDiv, fileid) => {
                // link example:
                // https://genome.ucsc.edu/cgi-bin/hgTracks?hgsid=1385266329_hzFy7AyNX1CiNfXlY7g4s5VHK3ZB
                var content = '<iframe frameborder="0"  style="width:100%; height:100%;" src="' + link + '"></iframe>';
                var visType = "ucsc_genome_browser";
                var contentDiv = getHeaderIconDiv(fileid, visType) + '<div style="width:100%; height:calc(100% - 70px);">' + content + "</div>";
                var run_log_uuid = $("#runVerLog").val();
                var pubWebPath = $("#basepathinfo").attr("pubweb");
                $(navTabDiv).empty().append(contentDiv);
                bindEveHandlerIcon(fileid, visType, pubWebPath, run_log_uuid);

            }
            const ucscQueryUrl = "https://genome.ucsc.edu/cgi-bin/hgTracks?hgsid="

            $(document).on("click", "a.ucsc_gene_link", function(e) {
                e.preventDefault();
                var ucsc_session = $(this).closest(".collapseRowBody").parent().find(".collapseRowDiv").attr("ucsc_session");
                var gene = $(this).attr("gene");
                if (ucsc_session) {
                    var navTabDiv = $(this).closest(".collapseRowBody").find(".ucsc_gb_tab");
                    var hubFileID = navTabDiv.attr("fileid")
                    var link = `${ucscQueryUrl}${ucsc_session}&position=${gene}`
                    refreshUcscIframe(link, navTabDiv, hubFileID)
                    var tabHrefId = $(this).closest(".collapseRowBody").find(".ucsc_gb_tab").attr("id")
                    $(`a[href="#${tabHrefId}"]`)[0].click();
                }
            });

            $(".collapseRowDiv").on("click", function(e) {
                var run_log_uuid = $("#runVerLog").val();
                var href = $(this).attr("href");
                var ucsc_session = $(this).attr("ucsc_session");
                var collapseRowDiv = $(this)
                var navTabDiv = collapseRowDiv.closest(".collapseRowDiv").parent().find(".ucsc_gb_tab");
                var pubWebPath = $("#basepathinfo").attr("pubweb");

                //for pubWeb == "ucsc_genome_browser"
                if (!ucsc_session && navTabDiv[0]) {
                    // check session for files -> if nor found then create session. 
                    var hubFileLoc = $(href).find("div.ucsc_gb_tab").attr("file")
                    var dir = $(href).find("div.ucsc_gb_tab").attr("dir")
                    var hubFileUrl = `${pubWebPath}/${run_log_uuid}/pubweb/${dir}/${hubFileLoc}`;
                    var hubFileID = $(href).find("div.ucsc_gb_tab").attr("fileid")
                    var genomeFileLoc = $(href).find("div.ucsc_gb_tab").attr("genome")
                    getValuesAsync({ p: "getUcscSessionID", hubFileLoc, genomeFileLoc, run_log_uuid, dir },
                        function(sessionID) {
                            console.log(sessionID)
                            if (sessionID) collapseRowDiv.attr("ucsc_session", sessionID)
                            var navTabDiv = collapseRowDiv.closest(".collapseRowDiv").parent().find(".ucsc_gb_tab");
                            var link = `${ucscQueryUrl}${sessionID}&hubUrl=${hubFileUrl}`
                            console.log(link)
                            refreshUcscIframe(link, navTabDiv, hubFileID)
                        }
                    );
                }


                //check if metadata table is loaded
                if (!($.trim($(href).find("div.ucsc_count_tab").html())).length) {
                    var table = $(href).find("div.ucsc_count_tab")
                    var table_id = table.attr("id");
                    var metadataFileID = $(href).find("div.ucsc_count_tab").attr("fileid")
                    var metadataFileLoc = $(href).find("div.ucsc_count_tab").attr("file")
                    var metadataFileDir = $(href).find("div.ucsc_count_tab").attr("dir")
                    var insertHref = `#${$(href).find("div.ucsc_count_tab").attr("id")}`

                    // fileid="g-124_table_overall_summary_tsv"
                    //  filepath="summary/overall_summary.tsv"
                    //   href="#reportTabg-124_table_overall_summary_tsv" 

                    var filePath = metadataFileDir + "/" + metadataFileLoc;
                    var split = filePath.split("/");
                    var filename = "";
                    var dir = "";
                    if (split.length > 1) {
                        filename = split[split.length - 1];
                        dir = filePath.substring(0, filePath.indexOf(filename));
                    }


                    var visType = "ucsc_genome_browser_metadata";
                    loadTableView(visType, filePath, metadataFileID, metadataFileDir, filename, insertHref, run_log_uuid, pubWebPath)

                }

                //check if left sidebar features table is loaded
                if ($.trim($(href).find("table.ucsc_gb_genes")[0])) {
                    var table = $(href).find("table.ucsc_gb_genes")
                    var metadata_url = table.attr("metadata_url");
                    var table_id = table.attr("id");
                    if (!$.fn.DataTable.isDataTable(`#${table_id}`)) {
                        $.ajax({
                            url: metadata_url,
                            async: true,
                            type: "GET",
                            success: function(metadata_tsv) {
                                var geneList = ""
                                if (metadata_tsv) {
                                    let metadata_arr = metadata_tsv.split('\n')
                                    metadata_arr.splice(0, 1);
                                    metadata_arr = metadata_arr.filter(n => n)
                                    geneList = metadata_arr.map((elt) => {
                                        const gene = elt.split("\t")[0].replace('\r', '');
                                        return [gene];
                                    });
                                    var dataTableObj = {};
                                    dataTableObj.columns = [{
                                        "title": "Genes",
                                        "fnCreatedCell": function(nTd, sData, oData, iRow, iCol) {
                                            $(nTd).html(`<a class="ucsc_gene_link" href="#" gene="${oData[0]}">${oData[0]}</a>`);
                                        }
                                    }]

                                    dataTableObj.data = geneList;
                                    dataTableObj.dom = 'ft';
                                    //speed up the table loading;
                                    dataTableObj.deferRender = true;
                                    dataTableObj.scroller = true;
                                    dataTableObj.scrollCollapse = true;
                                    dataTableObj.scrollY = 505;
                                    dataTableObj.scrollX = true;
                                    dataTableObj.sScrollX = true;
                                    //hides undefined error
                                    dataTableObj.columnDefs = [
                                        { defaultContent: "-", targets: "_all" },
                                    ];
                                    $("#" + table_id).DataTable(dataTableObj);
                                }
                            },
                            error: function(e) {
                                console.log(e)
                            }
                        });

                    }
                }
            });
            $(".collapseIconItem").on("click", function(e) {
                var itemClass = $(this).attr("class");
                if (itemClass.match(/fa-plus-square-o/)) {
                    $(this).removeClass("fa-plus-square-o");
                    $(this).addClass("fa-minus-square-o");
                } else if (itemClass.match(/fa-minus-square-o/)) {
                    $(this).removeClass("fa-minus-square-o");
                    $(this).addClass("fa-plus-square-o");
                }
            });
        };

        var getColumnContent = function(dataObj, colObj, nTd) {
            var col = "";
            if (colObj.fnCreatedCell && !nTd) {
                var nTd = $("<span></span>");
                colObj.fnCreatedCell(nTd, dataObj);
                col = nTd.clone().wrap("<p>").html();
                // fnRefresh will execute if nTd is available
            } else if (colObj.fnCreatedCell && nTd) {
                colObj.fnCreatedCell(nTd, dataObj);
            } else if (colObj.data) {
                col = dataObj[colObj.data];
            }
            return col;
        };

        var getColumnData = function(dataObj, settings, cols, height, lineHeight) {
            var columnPercent = 100;
            var clearFix = ""; //if its the first element of multicolumn
            var center = ""; //align="center" to div
            var columnCount = $(cols).size();
            var processParamDiv = "";
            var heightT = "";
            var lineHeightT = "";
            $.each(cols, function(el) {
                var overflowT = "";
                if (cols[el].overflow) {
                    var oTemp = cols[el].overflow
                    if (cols[el].overflow && typeof cols[el].overflow === 'function') {
                        oTemp = cols[el].overflow(dataObj)
                    }
                    overflowT = "overflow:" + oTemp + "; ";
                }
                if (cols[el].colPercent) {
                    columnPercent = cols[el].colPercent;
                    if (cols[el].colPercent && typeof cols[el].colPercent === 'function') {
                        columnPercent = cols[el].colPercent(dataObj)
                    }
                } else {
                    columnPercent = Math.floor((columnPercent / columnCount) * 100) / 100;
                }
                if (el === 0) {
                    clearFix = " clear:both; ";
                } else {
                    clearFix = "";
                }
                if (cols[el].className == "center") {
                    center = ' align="center"; ';
                } else {
                    center = "";
                }
                if (height) {
                    heightT = "height:" + height + "; ";
                }
                if (lineHeight) {
                    lineHeightT = "line-height:" + lineHeight + "; ";
                }

                processParamDiv +=
                    "<div " +
                    center +
                    ' style="' +
                    heightT +
                    lineHeightT +
                    clearFix +
                    overflowT +
                    "float:left;  width:" +
                    columnPercent +
                    '%; ">';
                processParamDiv += getColumnContent(dataObj, cols[el], null);
                processParamDiv += "</div>";
            });
            return processParamDiv;
        };

        var getPanel = function(dataObj, settings, elemsID) {
            if (dataObj) {
                var id = dataObj.id;
                var headerDiv = getColumnData(
                    dataObj,
                    settings,
                    settings.columnsHeader,
                    settings.heightHeader,
                    settings.lineHeightHeader
                );
                var bodyDiv = getColumnData(
                    dataObj,
                    settings,
                    settings.columnsBody,
                    settings.heightBody,
                    settings.lineHeightBody
                );
                var wrapHeader =
                    '<div class="collapsible collapseRowDiv" data-toggle="collapse" style="height:' +
                    settings.heightHeader +
                    ';" href="#' +
                    elemsID +
                    "-" +
                    id +
                    '"><h3 class="panel-title">' +
                    headerDiv +
                    "</h3></div>";
                var wrapBody =
                    '<div  id="' +
                    elemsID +
                    "-" +
                    id +
                    '" class="panel-collapse collapse collapseRowBody" style="word-break: break-all;"><div class="panel-body" style="background-color:white; height:' +
                    settings.heightBody +
                    '; padding:0px;">' +
                    bodyDiv +
                    "</div>";
                return (
                    '<div id="' +
                    elemsID +
                    "PanelDiv-" +
                    id +
                    '" ><div class="panel" style="background-color:' +
                    settings.backgroundcolorleave +
                    '; margin-bottom:15px;">' +
                    wrapHeader +
                    wrapBody +
                    "</div></div>"
                );
            } else return "";
        };

        var getTitle = function(dataObj, settings) {
            if (settings.columnsTitle) {
                var titleDiv = getColumnData({},
                    settings,
                    settings.columnsTitle,
                    settings.heightTitle,
                    settings.lineHeightTitle
                );
                return (
                    '<div  style="font-weight:900; height:' +
                    settings.heightTitle +
                    ';">' +
                    titleDiv +
                    "</div>"
                );
            } else return "";
        };

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
                        res = results;
                    },
                    error: function(errorThrown) {
                        console.log("##Error: ");
                        console.log(errorThrown);
                    },
                });
                return res;
            } else if (settings.ajax.data) {
                if (
                    settings.ajax.data === undefined ||
                    settings.ajax.data.length == 0
                ) {
                    res = null;
                } else {
                    res = settings.ajax.data;
                }
            }
            return res;
        };
    })(jQuery);

    $("#manualRunModal").on("show.bs.modal", async function() {
        window.sshCheck = false;
        // check ssh key
        var profileData = await getJobData("job");
        if (profileData) {
            if (profileData[0]) {
                if (profileData[0].ssh_id) {
                    if (profileData[0].ssh_id != "0") {
                        window.sshCheck = true;
                    }
                }
            }
        }
        if (window.sshCheck) {
            $("#manualRunText").html(
                '<b style="color:blue;">Warning:</b> This is an optional feature for users who want to execute their run manually in the terminal. If you want DolphinNext to execute your run, please close this window and click <b>Start</b>, <b>Resume</b> or <b>Rerun</b> buttons. </br>Otherwise, please choose your <b>Run Type</b> at below and click <b>Get Run Command</b> button. Run command will be created in the box below which is ready to be executed in your machine.'
            );
            $("#manualRunHelp").html(
                '<b style="color:blue;">* Warning:</b> This command expires after 10 minutes for security purposes. After timeout, you can click <b>Get Run Command</b> button to create new one.</br><b style="color:blue;">* Monitoring:</b> You can check the progress of your run by checking following files: initialrun/initial.log and log.txt.'
            );
        } else {
            $("#manualRunText").html(
                "You haven't defined SSH-Keys in you run environment. However, you can still execute your run by using terminal. </br>Please choose your <b>Run Type</b> and click <b>Get Run Command</b> button. Run command will be created in the box below which is ready to be executed in your machine."
            );
            $("#manualRunHelp").html(
                '<b style="color:blue;">* Warning:</b> This command expires after 10 minutes for security purposes. After timeout, you can click <b>Get Run Command</b> button to create new one.</br><b style="color:blue;">* Monitoring:</b> You can check the progress of your run by checking following files: initialrun/initial.log and log.txt.</br><b style="color:blue;">* Note:</b> If you want DolphinNext to execute your run, please follow <b><a target="_blank" href="https://dolphinnext.readthedocs.io/en/latest/dolphinNext/profile.html#ssh-keys">this tutorial</a></b> and add SSH-Keys into your profile.'
            );
        }
        var checkExistingManualRun = false;
        if (window.runStatus) {
            if (window.runStatus == "Manual") {
                checkExistingManualRun = true;
            }
        }
        if (!checkExistingManualRun) {
            $("#manualRunCmd").val("");
        }
    });

    $("#getManualRunCmd").on("click", async function(e) {
        var checkType = $("#manuaRunType").val();
        if (checkType) {
            window["manualRun"] = "true";
            showLoadingDiv("manuaRunPanel");
            $("#manualRunCmd").val("");
            await runProjectPipe(runProPipeCall, checkType);
        }
    });

    $("#cpTooltipManRun").on("click", function(e) {
        var copyText = document.getElementById("manualRunCmd");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        copyText.setSelectionRange(0, 0);
    });

    $("#profVarRunEnvModal").on("show.bs.modal", function() {
        if (window["undefinedVarObj"]) {
            var downdirCheck = false;
            var htmlBlock = "";
            $.each(window["undefinedVarObj"], function(el) {
                if (el == "params.DOWNDIR" && $(window.undefinedVarObj).length == 1) {
                    downdirCheck = true;
                    htmlBlock =
                        '<div class="form-group"><label class="col-sm-3 control-label">Download Path</label><div class="col-sm-9"><input type="text" class="form-control" placeholder="/share/dolphinnext/data" name="' +
                        el +
                        '"></div></div>';
                } else {
                    htmlBlock +=
                        '<div class="form-group"><label class="col-sm-3 control-label">' +
                        el +
                        '</label><div class="col-sm-9"><input type="text" placeholder="/share/dolphinnext/data"  class="form-control" name="' +
                        el +
                        '"></div></div>';
                }
            });
            $("#profVarRunEnvBlock").empty();
            $("#profVarRunEnvBlock").append(htmlBlock);
            if (downdirCheck) {
                $("#profVarRunEnvModalText").html(
                    "Please define a download path to keep reusable pipeline files such as genome indexes. "
                );
            } else {
                $("#profVarRunEnvModalText").html(
                    'Please define download paths to keep reusable pipeline files such as genome indexes. </br></br> <span style="padding-left:20px;">e.g. </span><code>  /share/dolphinnext/data</code>'
                );
            }
            $("#profVarRunEnvModalText2").html(`

<a style="color:#1479cc; cursor:pointer; text-decoration:underline;" data-toggle="collapse" data-target="#profVarRunEnvModalText3"><i class="glyphicon glyphicon-info-sign"></i> Need Help?</a>
<div id="profVarRunEnvModalText3" class="collapse">
<p></br>

<b>Info-1:</b> You can always edit this parameter in <b>Profile Variables</b> section: </br>

<span class="badge" style="margin-top:10px; margin-left:25px;"> Profile -> Run Environments -> Profile Variables</span></br></br>

<b>Info-2:</b> If you choose not to enter download path, your <b>Work Directory</b> will be used as default. However, this approach is not recommended and might cause unnecessary download for each run. We suggest to define a path which will allow to reuse the downloaded files. </br></br>

<b>Use Case-1:</b> If you want to use DolphinNext by yourself and don't have any shared directory system, you can simply set any path that you have permission to write. This way, all of the pipelines will use same set of files and unnecessary downloads will be prevented. </br></br>

<b>Use Case-2:</b> If you're using shared directory system and DolphinNext already been used in your platform, then your admin should have defined such path. Please contact with your admin to learn that location and enter that path.</br></br>

<b>Use Case-3:</b> If you're the admin of your platform, then please set a path that you have permission to write and other members have the permission to read.</p>
</div>

`);
        }
    });

    $("#profVarRunEnvModal").on("click", "#profVarRunEnvSave", function() {
        var formValues = $("#profVarRunEnvModal").find("input");
        var requiredFields = [];
        var formObj = {};
        var stop = "";
        var profVar = "";
        [formObj, stop] = createFormObj(formValues, requiredFields);
        $.each(formObj, function(el) {
            if (formObj[el]) {
                profVar += el + " = " + '"' + formObj[el] + '"\n';
            }
        });
        profVar = $.trim(profVar);
        if (profVar) {
            profVar = encodeURIComponent(profVar);
            $.ajax({
                url: "ajax/ajaxquery.php",
                data: {
                    p: "appendProfileVariables",
                    variable: profVar,
                    proType: proTypeWindow,
                    id: proIdWindow,
                    project_pipeline_id: project_pipeline_id,
                },
                cache: false,
                type: "POST",
                success: function(data) {
                    if (!data) {
                        toastr.error("Error occured.");
                    } else {
                        $("#profVarRunEnvModal").modal("hide");
                        setTimeout(function() {
                            window.location.reload(false);
                        }, 10);
                    }
                },
                error: function(jqXHR, exception) {
                    toastr.error("Error occured.");
                },
            });
        } else {
            showInfoModal(
                "#infoModal",
                "#infoModalText",
                "Please enter a download path."
            );
        }
    });

    // release date section:
    $("#relDateDiv").datepicker({
        format: "mm/dd/yyyy",
        startDate: "0",
        autoclose: true,
    });

    $("#setRelease").on("click", function(e) {
        e.preventDefault();
        $("#releaseModalText").html(
            "If you want to limit the access of the run until certain date, you can set a release date below. We will create temporary link for your run and only people who have the link could access the run."
        );
        $("#relDateDiv").data("datepicker").setDate(null);
        $("#releaseModal").modal("show");
    });

    $(".cancelReleaseDateBut").on("click", async function(e) {
        var currRelDate = $("#releaseVal").attr("date");
        var today = getCurrDate();
        $("#releaseVal").attr("date", today);
        var sucFunc = function() {
            $("#releaseVal").text(today);
            $("#getTokenLink").css("display", "inline");
            $("#releaseModal").modal("hide");
        };
        await saveRun(sucFunc, false);
    });

    $("#setReleaseDateBut").on("click", async function(e) {
        var date = $("#relDateInput").val();
        if (!date) {
            showInfoModal(
                "#infoMod",
                "#infoModText",
                "Please enter the release date."
            );
        } else {
            $("#releaseVal").attr("date", date);
            var sucFunc = function() {
                $("#releaseVal").text(date);
                $("#getTokenLink").css("display", "inline");
                $("#releaseModal").modal("hide");
            };
            await saveRun(sucFunc, false);
        }
    });

    $("#copyToken").on("click", function(e) {
        var copyText = document.getElementById("tokenInput");
        copyText.select();
        copyText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        copyText.setSelectionRange(0, 0);
        toastr.info("Link copied to the clipboard");
    });

    $("#getTokenLink").on("click", function(e) {
        var currToken = $(this).attr("token");
        var showToken = function(token) {
            $("#showTokenLink").css("display", "table");
            var basepath = $("#basepathinfo").attr("basepath");
            $("#tokenInput").val(basepath + "/index.php?t=" + token);
            $("#copyToken").trigger("click");
        };
        if (currToken) {
            showToken(currToken);
        } else {
            //insert token
            getValuesAsync({ p: "saveToken", type: "project_pipeline", id: project_pipeline_id },
                function(s) {
                    if (s.token) {
                        $(this).attr("token", s.token);
                        showToken(s.token);
                    }
                }
            );
        }
    });
    //release date section ends

    // prevent closing window before run submission
    $(window).bind("beforeunload", function(e) {
        if ($runscope.beforeunload) return true;
        else e = null;
    });
})