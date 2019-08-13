/**
 * Extend the Array object
 * @param candid The string to search for
 * @returns Returns the index of the first match or -1 if not found
 */
Array.prototype.searchFor = function (candid) {
    for (var i = 0; i < this.length; i++)
        if (this[i].indexOf(candid) > -1)
            return true;
    return false;
};

function createPiGnumList() {
    //get available pipeline Module list
    piGnumList = [];
    $("#subPipelinePanelTitle > div").each(function () {
        if ($(this).attr('id').match(/proPanelDiv-(.*)/)) {
            piGnumList.push($(this).attr('id').match(/proPanelDiv-(.*)/)[1]);
        }
    });
}

//adjust container size based on window size
window.onresize = function (event) {
    createPiGnumList();
    var Maint = d3.transform(d3.select('#' + "mainG").attr("transform"));
    var Mainx = Maint.translate[0]
    var Mainy = Maint.translate[1]
    var Mainz = Maint.scale[0]
    translateSVG([Mainx, Mainy, Mainz, window.lastMG[3], window.lastMG[4]], window)
    //for pipeline modules
    for (var j = 0; j < piGnumList.length; j++) {
        var MaintP = d3.transform(d3.select('#' + "mainG" + piGnumList[j]).attr("transform"));
        var MainxP = MaintP.translate[0]
        var MainyP = MaintP.translate[1]
        var MainzP = MaintP.scale[0]
        translateSVG([MainxP, MainyP, MainzP, window["pObj" + piGnumList[j]].lastMG[3], window["pObj" + piGnumList[j]].lastMG[4]], window["pObj" + piGnumList[j]]);
    };
}


function dragStart(event) {
    event.dataTransfer.setData("Text", event.target.id);
}

function dragging(event) {
    event.preventDefault();
}

function allowDrop(event) {
    event.preventDefault();
}

parametersData = getValues({ p: "getAllParameters" })

var sData = "";
var svg = "";
var mainG = "";
var autoFillJSON;
var systemInputs = [];

function createSVG() {
    w = '100%'
    h = '100%'
    r = 70
    cx = 0
    cy = 0
    ior = r / 6
    rP = r + 24; // r of pipeline circle 
    var dat = [{
        x: 0,
        y: 0
    }]
    gNum = 0
    MainGNum = "";
    selectedgID = ""
    selectedg = ""
    diffx = 0
    diffy = 0

    processList = {}
    processListMain = {}
    ccIDList = {} //pipeline module match id list
    nullIDList = {} //in case node info is changed after import/save on existing. use getNewNodeId function to get new id
    edges = []
    candidates = []
    saveNodes = []

    createPipeRev = "";
    dupliPipe = false
    binding = false
    renameTextID = ""
    deleteID = ""

    d3.select("#svg").remove();
    //--Pipeline details table clean --
    //    $('#inputsTable').find("tr:gt(0)").remove();
    $('#outputsTable').find("tr:gt(0)").remove();
    $('#processTable').find("tr:gt(0)").remove();

    svg = d3.select("#container").append("svg")
        .attr("id", "svg")
        .attr("width", w)
        .attr("height", h)
        .on("mousedown", startzoom)
    mainG = d3.select("#container").select("svg").append("g")
        .attr("id", "mainG")
        .attr("transform", "translate(" + 0 + "," + 0 + ")")
}

function startzoom() {
    d3.select("#container").call(zoom)
}

var timeoutId = 0;



function resetSingleParam(paramId) {
    if ($('#' + paramId).attr("connect") === "single") {
        if ($('#' + paramId).parent().attr("class") === "g-inPro") {
            resetOriginal("inPro", paramId)
        } else if ($('#' + paramId).parent().attr("class") === "g-outPro") {
            resetOriginal("outPro", paramId)
            return true
        }
    }
    return false
}

//resets input/output parameters to original state
//paramType:outPro or inPro
function resetOriginal(paramType, firstParamId) {
    var patt = /(.*)-(.*)-(.*)-(.*)-(.*)/;
    if (paramType === 'outPro') {
        var originalID = firstParamId.replace(patt, '$1-$2-$3-' + "outPara" + '-$5')
        d3.selectAll("#" + firstParamId).attr("id", originalID);
        d3.selectAll("#" + originalID).attr("class", "connect_to_output input");
    } else if (paramType === 'inPro') {
        var originalID = firstParamId.replace(patt, '$1-$2-$3-' + "inPara" + '-$5')
        d3.selectAll("#" + firstParamId).attr("id", originalID);
        d3.selectAll("#" + originalID).attr("class", "connect_to_input output");
    }
}

//edges-> all edge list, nullId-> process input/output id that not exist in the d3 diagrams 
function getNewNodeId(edges, nullId, MainGNum) {
    //nullId: i-24-14-20-1
    var nullProcessInOut = nullId.split("-")[0];
    var nullProcessId = nullId.split("-")[1];
    var nullProcessParId = nullId.split("-")[3];
    var nullProcessGnum = nullId.split("-")[4];
    //check is parameter is unique:
    if (nullProcessInOut === "i") {
        var nodes = JSON.parse(window.pipeObj["pro_para_inputs_" + nullProcessId]);
    } else if (nullProcessInOut === "o") {
        var nodes = JSON.parse(window.pipeObj["pro_para_outputs_" + nullProcessId]);
    }
    if (nodes) {
        var paraData = nodes.filter(function (el) { return el.parameter_id == nullProcessParId });
        //get newNodeID  
        if (paraData.length === 1 && nullProcessId !== "inPro" && nullProcessId !== "outPro") {
            var patt = /(.*)-(.*)-(.*)-(.*)-(.*)/;
            var nullIdRegEx = new RegExp(nullId.replace(patt, '$1-$2-' + '(.*)' + '-$4-$5'), 'g')
            var newNode = $('#g' + MainGNum + "-" + nullProcessGnum).find("circle").filter(function () {
                return this.id.match(nullIdRegEx);
            })
            if (newNode.length === 1) {
                var newNodeId = newNode.attr("id");
                nullIDList["p"+MainGNum+nullId]=newNodeId
                return newNodeId;
            }
        }
    }
}

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
    $("#container" + MainGNum).css("height", height + "px")
    var transX = parseFloat(mG[0]) * coefW;
    var transY = parseFloat(mG[1]) * coefW;
    var transS = parseFloat(mG[2]) * coefW;
    var trans = 'translate(' + transX + ',' + transY + ')' + "scale(" + transS + ')';
    d3.select("#mainG" + MainGNum).attr("transform", trans)

    if (pObj == window) {
        zoom.translate([transX, transY]).scale(transS);
    }
    pObj.lastMG = [transX, transY, transS, widthC, height]
}

function openSubPipeline(piID, pObj) {
    var sData = pObj.sData[0];
    var MainGNum = pObj.MainGNum;
    var lastGnum = pObj.lastGnum;
    var prefix = "p" + MainGNum;
    pObj.processList = {};
    pObj.processListMain = {};
    pObj.edges = [];
    //check if params.VARNAME is defined in the autofill section of pipeline header or pipeline config. Then return all VARNAMES to define as system inputs
    var processData = ""
    insertProPipePanel(decodeHtml(sData.script_pipe_config) + "\n" + decodeHtml(sData.script_pipe_header), "pipe", "Pipeline", window, processData);
    var hideModule = false;
    if ($("#subPipelinePanelTitle").find('div[pipeid*=' + piID + ']').length > 0) {
        hideModule = true;
    }
    var hideModuleText = '';
    if (hideModule) {
        hideModuleText = 'style="display:none;"';
    }
    var pipeName = cleanProcessName(sData.name);
    var dispTitle = $('#subPipelinePanelTitle').css("display");
    if (dispTitle == "none") {
        $('#subPipelinePanelTitle').css("display", "inline");
    }
    var processHeader = '<div class="panel-heading collapsible collapseIconDiv" data-toggle="collapse" href="#collapse-' + MainGNum + '"><h4 class="panel-title">' + pipeName + '<i data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a style="font-size:15px; padding-left:10px;" class="fa collapseIcon fa-plus-square-o"></a></i></h4></div>';
    var processBodyInt = '<div  id="collapse-' + MainGNum + '" class="panel-collapse collapse"><div style="height:500px; padding:0px;" id="container' + MainGNum + '" class="panel-body">'
    //create Pipeline Module Panel
    $('#subPipelinePanelTitle').append('<div id="proPanelDiv-' + MainGNum + '" pipeid="' + piID + '" ' + hideModuleText + '><div id="proPanel-' + MainGNum + '" class="panel panel-default" style="margin-bottom:3px;">' + processHeader + processBodyInt + '</div></div></div></div>')
    pObj.svg = d3.select("#container" + MainGNum).append("svg")
        .attr("id", "svg" + MainGNum)
        .attr("width", w)
        .attr("height", h)
        .on("mousedown", startzoom)
    pObj.mainG = d3.select("#container" + MainGNum).select("svg").append("g")
        .attr("id", "mainG" + MainGNum)
        .attr("transform", "translate(" + 0 + "," + 0 + ")")
    d3.select("#container" + MainGNum).style("background-image", "url(css/workplace_image.png)").style("background-repeat", "repeat").on("keydown", cancel).on("mousedown", cancel)

    if (sData) {
        pObj.nodes = sData.nodes
        pObj.nodes = JSON.parse(pObj.nodes.replace(/'/gi, "\""))
        pObj.mG = sData.mainG
        pObj.mG = JSON.parse(pObj.mG.replace(/'/gi, "\""))["mainG"]
        translateSVG(pObj.mG, pObj)
        for (var key in pObj.nodes) {
            pObj.x = pObj.nodes[key][0]
            pObj.y = pObj.nodes[key][1]
            pObj.pId = pObj.nodes[key][2]
            pObj.name = cleanProcessName(pObj.nodes[key][3]);
            var processModules = pObj.nodes[key][4];
            pObj.gNum = key.split("-")[1]
            if (pObj.pId.match(/p(.*)/)) {
                var newPiID = pObj.pId.match(/p(.*)/)[1];
                var newMainGnum = "pObj" + MainGNum + "_" + pObj.gNum;
                window[newMainGnum] = {};
                window[newMainGnum].piID = newPiID;
                window[newMainGnum].MainGNum = MainGNum + "_" + pObj.gNum;
                window[newMainGnum].lastGnum = pObj.gNum;
                window[newMainGnum].sData = [window.pipeObj["pipeline_module_" + newPiID]]
                window[newMainGnum].lastPipeName = pObj.name;
                // create new SVG workplace inside panel, if not added before
                openSubPipeline(newPiID, window[newMainGnum]);
                // add pipeline circle to main workplace
                addPipeline(newPiID, pObj.x, pObj.y, pObj.name, pObj, window[newMainGnum]);
            } else {
                loadPipeline(pObj.x, pObj.y, pObj.pId, pObj.name, processModules, pObj.gNum, pObj)
            }
        }
        pObj.ed = sData.edges.slice();
        pObj.ed = JSON.parse(pObj.ed.replace(/'/gi, "\""))["edges"]
        for (var ee = 0; ee < pObj.ed.length; ee++) {
            pObj.eds = pObj.ed[ee].split("_")
            //specific to module panel
            //if process is updated through process modal, reconnect the uneffected one based on their parameter_id.
            if (!document.getElementById(prefix + pObj.eds[0]) && document.getElementById(prefix + pObj.eds[1])) {
                var newID = getNewNodeId(pObj.ed, pObj.eds[0], MainGNum)
                if (newID) {
                    newID = newID.replace(prefix, "")
                    pObj.eds[0] = newID;
                    createEdges(pObj.eds[0], pObj.eds[1], pObj)
                }
                //if process is updated through process modal, reset the edge of input/output parameter and reset the single circles.
            } else if (!document.getElementById(prefix + pObj.eds[1]) && document.getElementById(prefix + pObj.eds[0])) {
                var newID = getNewNodeId(pObj.ed, pObj.eds[1], MainGNum);
                if (newID) {
                    newID = newID.replace(prefix, "")
                    pObj.eds[1] = newID;
                    createEdges(pObj.eds[0], pObj.eds[1], pObj)
                }
            } else if (document.getElementById(prefix + pObj.eds[1]) && document.getElementById(prefix + pObj.eds[0])) {
                addCandidates2DictForLoad(pObj.eds[0], pObj)
                createEdges(pObj.eds[0], pObj.eds[1], pObj)
            }
        }
    }
}

function openPipeline(id) {
    createSVG()
    sData = [window.pipeObj["main_pipeline_" + id]]
    if (sData) {
        if (Object.keys(sData).length > 0) {
            nodes = sData[0].nodes
            nodes = JSON.parse(nodes.replace(/'/gi, "\""))
            mG = sData[0].mainG
            mG = JSON.parse(mG.replace(/'/gi, "\""))["mainG"]
            translateSVG(mG, window)
            for (var key in nodes) {
                x = nodes[key][0]
                y = nodes[key][1]
                pId = nodes[key][2]
                name = cleanProcessName(nodes[key][3])
                var processModules = nodes[key][4];
                gNum = parseInt(key.split("-")[1])
                //for pipeline circles
                if (pId.match(/p(.*)/)) {
                    var piID = pId.match(/p(.*)/)[1];
                    var newMainGnum = "pObj" + gNum;
                    window[newMainGnum] = {};
                    window[newMainGnum].piID = piID;
                    window[newMainGnum].MainGNum = gNum;
                    window[newMainGnum].lastGnum = gNum;
                    window[newMainGnum].sData = [window.pipeObj["pipeline_module_" + piID]]
                    window[newMainGnum].lastPipeName = name;
                    // create new SVG workplace inside panel, if not added before
                    openSubPipeline(piID, window[newMainGnum]);
                    // add pipeline circle to main workplace
                    addPipeline(piID, x, y, name, window, window[newMainGnum]);
                    //for process circles
                } else {
                    loadPipeline(x, y, pId, name, processModules, gNum, window)
                }
            }
            ed = sData[0].edges
            ed = JSON.parse(ed.replace(/'/gi, "\""))["edges"]
            for (var ee = 0; ee < ed.length; ee++) {
                eds = ed[ee].split("_")
                if (!document.getElementById(eds[0]) && document.getElementById(eds[1])) {
                    //if process is updated through process modal, reconnect the uneffected one based on their parameter_id.
                    var newID = getNewNodeId(ed, eds[0], "")
                    if (newID) {
                        eds[0] = newID;
                        addCandidates2DictForLoad(eds[0], window)
                        createEdges(eds[0], eds[1], window)
                    }
                    //if process is updated through process modal, reset the edge of input/output parameter and reset the single circles.
                    resetSingleParam(eds[1]);

                } else if (!document.getElementById(eds[1]) && document.getElementById(eds[0])) {
                    var newID = getNewNodeId(ed, eds[1], "");
                    if (newID) {
                        eds[1] = newID;
                        addCandidates2DictForLoad(eds[0], window)
                        createEdges(eds[0], eds[1], window)
                    }
                    resetSingleParam(eds[0]);

                } else if (document.getElementById(eds[1]) && document.getElementById(eds[0])) {
                    addCandidates2DictForLoad(eds[0], window)
                    createEdges(eds[0], eds[1], window)
                }
            }
        }
    }
}

d3.select("#container").style("background-image", "url(css/workplace_image.png)").style("background-repeat", "repeat").on("keydown", cancel).on("mousedown", cancel)

var zoom = d3.behavior.zoom()
.translate([0, 0])
.scale(1)
.scaleExtent([0.15, 2])
.on("zoom", zoomed);

createSVG()

function zoomed() {
    mainG.attr("transform", "translate(" + d3.event.translate + ")scale(" + d3.event.scale + ")");
}

//kind=input/output
function drawParam(name, process_id, id, kind, sDataX, sDataY, paramid, pName, classtoparam, init, pColor, defVal, dropDown, pubWeb, showSett, pObj) {
    var MainGNum = "";
    var prefix = "";
    if (pObj != window) {
        //load workflow of pipeline modules 
        MainGNum = pObj.MainGNum;
        prefix = "p" + MainGNum; //prefix for node ids
    }
    //gnum uniqe, id same id (Written in class) in same type process
    pObj.g = d3.select("#mainG" + MainGNum).append("g")
        .attr("id", "g" + MainGNum + "-" + pObj.gNum)
        .attr("class", "g" + MainGNum + "-" + id)
        .attr("transform", "translate(" + sDataX + "," + sDataY + ")")
    //        .on("mouseover", mouseOverG)
    //        .on("mouseout", mouseOutG)

    //gnum(written in id): uniqe, id(Written in class): same id in same type process, bc(written in type): same at all bc
    //outermost circle transparent
    pObj.g.append("circle").attr("id", "bc" + MainGNum + "-" + pObj.gNum)
        .attr("class", "bc" + MainGNum + "-" + id)
        .attr("type", "bc")
        .attr("cx", cx)
        .attr("cy", cy)
        .attr("r", ipR + ipIor)
        .attr('fill-opacity', 0)
        .attr("fill", "#E0E0E0")

    //second outermost circle visible gray
    pObj.g.append("circle")
        .datum([{
            cx: 0,
            cy: 0
        }])
        .attr("id", "sc" + MainGNum + "-" + pObj.gNum)
        .attr("class", "sc" + MainGNum + "-" + id)
        .attr("type", "sc")
        .attr("r", ipR + ipIor)
        .attr("fill", "#E0E0E0")
        .attr('fill-opacity', 1)
    //        .on("mouseover", scMouseOver)
    //        .on("mouseout", scMouseOut)
    //        .call(drag)

    //gnum(written in id): uniqe, id(Written in class): same id in same type process, bc(written in type): same at all bc
    //inner parameter circle


    d3.select("#g" + MainGNum + "-" + pObj.gNum).append("circle")
        .attr("id", prefix + init + "-" + id + "-" + 1 + "-" + paramid + "-" + pObj.gNum)
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
        .attr('fill-opacity', 0.8)
        .on("mouseover", IOmouseOver)
        .on("mousemove", IOmouseMove)
        .on("mouseout", IOmouseOut)
    //        .on("mousedown", IOconnect)

    //gnum(written in id): unique,
    pObj.g.append("text").attr("id", "text" + MainGNum + "-" + pObj.gNum)
        .datum([{
            cx: 0,
            cy: 20,
            "name": name
        }])
        .attr('font-family', "FontAwesome, sans-serif")
        .attr('font-size', '1em')
        .attr('name', name)
        .attr('class', 'inOut')
        .attr('classType', kind)
        .text(truncateName(name, 'inOut'))
        .attr("text-anchor", "middle")
        .attr("x", 0)
        .attr("y", 28)
    if (defVal) {
        $("#text" + MainGNum + "-" + pObj.gNum).attr('defVal', defVal)
    }
    if (dropDown) {
        $("#text" + MainGNum + "-" + pObj.gNum).attr('dropDown', dropDown)
    }
    if (showSett != null) {
        $("#text" + MainGNum + "-" + pObj.gNum).attr('showSett', showSett)
    }
    if (pubWeb) {
        $("#text" + MainGNum + "-" + pObj.gNum).attr('pubWeb', pubWeb)
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
            var varSplit = varPart.split('==');
        } else {
            var varSplit = varPart.split('=');
        }
        if (varSplit.length == 2) {
            varName = $.trim(varSplit[0]);
            defaultVal = $.trim(varSplit[1]);
            // if defaultVal starts and ends with single or double quote, remove these. (keep other quotes)
            if ((defaultVal.charAt(0) === '"' || defaultVal.charAt(0) === "'") && (defaultVal.charAt(defaultVal.length - 1) === '"' || defaultVal.charAt(defaultVal.length - 1) === "'")) {
                defaultVal = defaultVal.substr(1, defaultVal.length - 2);
            } else if (defaultVal.charAt(0) === '[' || defaultVal.charAt(defaultVal.length - 1) === "]"){
                var content = defaultVal.substr(1, defaultVal.length - 2);
                content = content.replace(/\"/g, '');
                content = content.replace(/\'/g, '');
                defaultVal = content.split(",")
            }
        }
    } // if /=/ not exist then genericCond is defined   
    else {
        if (splitType === "condition") {
            varName = $.trim(varPart);
            defaultVal = null;
        }
    }
    return [varName, defaultVal]
}

//parse {var1, var2, var3}, {var5, var6} into array of [var1, var2, var3], [var5, var6]
function parseBrackets(arr, trim) {
    var finalArr = [];
    arr = arr.split('{');
    if (arr.length) {
        for (var k = 0; k < arr.length; k++) {
            if (trim) {
                arr[k] = $.trim(arr[k]);
            } else {
                if ($.trim(arr[k]) !== "") {
                    arr[k] = $.trim(arr[k]);
                }
            }
            arr[k] = arr[k].replace(/\"/g, '');
            arr[k] = arr[k].replace(/\'/g, '');
            arr[k] = arr[k].replace(/\}/g, '');
            arr[k] = fixParentheses(arr[k]); //turn (a,b,c) into (a|b|c) format for multiple options
            var allcolumn = arr[k].split(",").map(function (item) {
                var item = item;
                if (trim) { var item = $.trim(item) } else {
                    if ($.trim(item) !== "") {
                        item = $.trim(item)
                    }
                }
                if (item !== "") { return item; }
            }).filter(function (item) { return item !== undefined; });
            if (!$.isEmptyObject(allcolumn[0])) {
                finalArr.push(allcolumn)
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
        var regSplit = regPart.split('@');
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
            var checkPathCheck = regSplit[i].match(/^checkpath:"(.*)"|^checkpath:'(.*)'/i);
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

//parse main categories: @checkbox, @textbox, @input, @dropdown, @description, @options @title @autofill @show_settings
//parse style categories: @multicolumn, @array, @condition
function parseRegPart(regPart) {
    var type = null;
    var desc = null;
    var title = null;
    var tool = null;
    var showsett = null;
    var opt = null;
    var allOptCurly = null;
    var multiCol = null;
    var autoform = null;
    var arr = null;
    var cond = null;
    if (regPart.match(/@/)) {
        var regSplit = regPart.split('@');
        for (var i = 0; i < regSplit.length; i++) {
            // find type among following list:checkbox|textbox|input|dropdown
            var typeCheck = regSplit[i].match(/^checkbox|^textbox|^input|^dropdown/i);
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
            // find description
            var descCheck = regSplit[i].match(/^description:"(.*)"|^description:'(.*)'/i);
            if (descCheck) {
                if (descCheck[1]) {
                    desc = descCheck[1];
                } else if (descCheck[2]) {
                    desc = descCheck[2];
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
            var show_settCheck = regSplit[i].match(/^show_settings:"(.*)"|^show_settings:'(.*)'/i);
            if (show_settCheck) {
                if (show_settCheck[1]) {
                    showsett = show_settCheck[1];
                } else if (show_settCheck[2]) {
                    showsett = show_settCheck[2];
                }
                //seperate process names by comma
                if (showsett) {
                    showsett = showsett.split(',');
                    if (showsett.length) {
                        for (var k = 0; k < showsett.length; k++) {
                            showsett[k] = $.trim(showsett[k]);
                            showsett[k] = showsett[k].replace(/\"/g, '');
                            showsett[k] = showsett[k].replace(/\'/g, '');
                        }
                    }
                }
            }
            // find options
            var optCheck = regSplit[i].match(/^options:\s*"(.*)"|^options:\s*'(.*)'|^options:\s*\{(.*)\}/i);
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
                    allOpt = allOpt.split(',');
                    if (allOpt.length) {
                        for (var k = 0; k < allOpt.length; k++) {
                            allOpt[k] = $.trim(allOpt[k]);
                            allOpt[k] = allOpt[k].replace(/\"/g, '');
                            allOpt[k] = allOpt[k].replace(/\'/g, '');
                        }
                    }
                    opt = allOpt;
                }
            }
        }
    }
    return [type, desc, tool, opt, multiCol, arr, cond, title, autoform, showsett]
}

//remove parent div from process options field
function removeParentDiv(button) {
    $(button).parent().parent().remove();
}
//add array from div to process options field
function appendBeforeDiv(button) {
    var div = $(button).parent().prev();
    div.parent().find('[data-toggle="tooltip"]').tooltip('destroy');
    var divID = div.attr("id"); //var1_var2_var3_var4_ind1
    var groupID = divID.match(/(.*)_ind(.*)$/)[1]; //var1_var2_var3_var4
    var divNum = divID.match(/(.*)_ind(.*)$/)[2]; //ind1
    var origin = div.parent().find("div#" + groupID + "_ind0"); //hidden original div
    divNum = parseInt(divNum) + 1;
    //clone with event handlers
    $(origin).clone(true, true).insertAfter($(div));
    var newDiv = $(button).parent().prev().attr("id", groupID + "_ind" + divNum).css("display", "inline");
    newDiv.find("select").trigger("change");
    newDiv.find("input:checkbox").trigger("change");
    $('[data-toggle="tooltip"]').tooltip();
}

function findDynamicArr(optArr, gNum) {
    var opt = null;
    for (var t = 0; t < optArr.length; t++) {
        if (optArr[t].varNameCond && optArr[t].selOpt && optArr[t].autoVal) {
            var checkVal = checkDynamicVar(optArr[t].varNameCond, autoFillJSON, null, gNum)
            if (checkVal){
                optArr[t].varNameCond = checkVal
                if (optArr[t].varNameCond == optArr[t].selOpt) {
                    opt = optArr[t].autoVal;
                    return opt
                }
            }
        }
    }
    return opt
}

// at this position we know that default option will be shown (other options are not valid or conditionally seen options are available -> keep these option in hiddenOpt)
function findDefaultArr(optArr) {
    var defaultOpt = null;
    var hiddenOpt = [];
    var allOpt = [];
    for (var t = 0; t < optArr.length; t++) {
        $.extend(allOpt,optArr[t].autoVal)
        if (!optArr[t].varNameCond && !optArr[t].selOpt && optArr[t].autoVal) {
            var mergedOpt = optArr[t].autoVal.join("")
            if (!mergedOpt.match(/=/)) {
                defaultOpt = optArr[t].autoVal;
            }
        } 
    }
    hiddenOpt = $(allOpt).not(defaultOpt).get();
    return [defaultOpt,hiddenOpt,allOpt]
}




//insert form fields into panels of process options 
function addProcessPanelRow(gNum, name, varName, defaultVal, type, desc, opt, tool, multicol, array, title) {
    if ($.isArray(defaultVal)){
        defaultVal = "";
    }
    var checkInsert = $('#addProcessRow-' + gNum).find('[id]').filter(function () {
        return $(this).attr('id') === 'var_' + gNum + '-' + varName
    });
    if (!checkInsert.length) {
        var hiddenOpt = null; // if conditional dropdown options are defined
        var allOpt = null; // if conditional dropdown options are defined
        var optArr = []; // for dropdown options
        var arrayCheck = false; //is it belong to array
        var clearFix = ""; //if its the first element of multicol
        var arrayId = "";
        var columnPercent = 100;
        if (title) {
            $('#addProcessRow-' + gNum).append('<div style="font-size:16px; font-weight:bold; background-color:#F5F5F5; float:left; padding:5px; margin-bottom:8px; width:100%;">' + title + '<div  style="border-bottom:1px solid #d5d5d5;" ></div></div>');
        }
        // if multicol defined then calc columnPercent based on amount of element
        if (multicol) {
            $.each(multicol, function (el) {
                if (multicol[el].indexOf(varName) > -1) {
                    var columnCount = multicol[el].length;
                    columnPercent = Math.floor(columnPercent / columnCount * 100) / 100;
                }
                if ((multicol[el].indexOf(varName) === 0)) {
                    clearFix = " clear:both; "
                }
            });
        }
        // if array defined then create arraydiv and remove/add buttons.
        if (array) {
            $.each(array, function (el) {
                if (array[el].indexOf(varName) > -1) {
                    arrayId = array[el].join('_');
                    arrayCheck = true;
                    if (!$('#addProcessRow-' + gNum).find("#" + arrayId + '_ind0')[0]) {
                        //insert array group div
                        $('#addProcessRow-' + gNum).append('<div id="' + arrayId + '_ind0" style="display:none; float:left; background-color: #F5F5F5; padding:10px; margin-bottom:8px; width:100%;">  <div id="delDiv" style="width:100%; float:left;" >' + getButtonsDef(gNum + "_", 'Remove', arrayId) + '</div></div>');
                        //append add button
                        $('#addProcessRow-' + gNum).append('<div id="addDiv" style="float:left; margin-bottom:8px; width:100%; class="form-group">' + getButtonsDef(gNum + "_", 'Add', arrayId) + '</div>');
                        //add onclick remove div feature 
                        $('#addProcessRow-' + gNum + "> #" + arrayId + '_ind0 > #delDiv > button').attr("onclick", "javascript:removeParentDiv(this)")
                        //add onclick append div feature 
                        $('#addProcessRow-' + gNum + "> #" + arrayId + '_ind0 + #addDiv > button').attr("onclick", "javascript:appendBeforeDiv(this)")
                    }
                }
                //xxxxxxxxx
            });
        }
        if (tool && tool != "") {
            var toolText = ' <span><a data-toggle="tooltip" data-placement="bottom" title="' + tool + '"><i class="glyphicon glyphicon-info-sign"></i></a></span>';
        } else {
            var toolText = "";
        }
        if (!desc) {
            var descText = "";
        } else {
            var descText = '<p style=" font-style:italic; color:darkslategray; font-weight: 300; font-size:13px">' + desc + '</p>';
        }
        var processParamDiv = '<div  class="form-group" style="' + clearFix + 'float:left; padding:5px; width:' + columnPercent + '%;" >';
        var label = '<label style="font-weight:600;">' + varName + toolText + ' </label>';
        if (type === "input") {
            var inputDiv = '<input type="text" class="form-control" style="padding:15px;" id="var_' + gNum + '-' + varName + '" name="var_' + gNum + '-' + varName + '" value="' + defaultVal + '">';
            processParamDiv += label + inputDiv + descText + '</div>';
        } else if (type === "textbox") {
            var inputDiv = '<textarea class="form-control" id="var_' + gNum + '-' + varName + '" name="var_' + gNum + '-' + varName + '">' + defaultVal + '</textarea>';
            processParamDiv += label + inputDiv + descText + '</div>';
        } else if (type === "checkbox") {
            if (defaultVal) {
                if (defaultVal === "true") {
                    defaultVal = "checked"
                } else {
                    defaultVal = ""
                }
            }
            var inputDiv = '<input type="checkbox" style = "margin-right:5px;" class="form-check-input" id="var_' + gNum + '-' + varName + '" name="var_' + gNum + '-' + varName + '" ' + defaultVal + '>';
            processParamDiv += inputDiv + label + descText + '</div>';
        } else if (type === "dropdown") {
            var inputDiv = '<select type="dropdown" class="form-control" id="var_' + gNum + '-' + varName + '" name="var_' + gNum + '-' + varName + '">';
            var optionDiv = "";
            var defaultOpt = null;
            var dynamicOpt = null;
            var optOrg = opt;
            if (opt) {
                if (opt.length) {
                    //check if conditional options are defined.
                    var condOptCheck = $.isArray(opt[0])
                    if (condOptCheck) {
                        //conditional options
                        optArr = createDynFillArr([opt])
                        //check if dynamic variables (_var) exist in varNameCond
                        dynamicOpt = findDynamicArr(optArr, gNum)
                        if (dynamicOpt) {
                            opt = dynamicOpt;
                        } else {
                            // check if default option is defined(without =)
                            // check if conditional options are defined and keep them in hiddenOpt 
                            [opt,hiddenOpt,allOpt] = findDefaultArr(optArr)
                        }
                    }
                    if (opt) {
                        for (var k = 0; k < opt.length; k++) {
                            if (defaultVal === opt[k]) {
                                optionDiv += '<option value="'+opt[k]+'" selected>' + opt[k] + ' </option>';
                            } else {
                                optionDiv += '<option value="'+opt[k]+'">' + opt[k] + ' </option>';
                            }
                        }
                    }
                    if (hiddenOpt) {
                        for (var k = 0; k < hiddenOpt.length; k++) {
                            optionDiv += '<option style="display:none;" value="'+hiddenOpt[k]+'">' + hiddenOpt[k] + ' </option>';
                        }
                    }
                }
            }
            processParamDiv += label + inputDiv + optionDiv + '</select>' + descText + '</div>';
        }

        if (arrayCheck === false) {
            $('#addProcessRow-' + gNum).append(processParamDiv)
        } else {
            // if array defined then append each element into that arraydiv.
            $('#addProcessRow-' + gNum + "> #" + arrayId + '_ind0').append(processParamDiv);
            $('#addProcessRow-' + gNum + "> #" + arrayId + '_ind0 > #delDiv').insertAfter($('#addProcessRow-' + gNum + "> #" + arrayId + '_ind0 div:last')); //keep remove button at last
        }
        //bind event handler to dynamically show dropdown options  
        if (hiddenOpt && optArr && allOpt) {
            $.each(optArr, function (el) {
                if (optArr[el].selOpt){
                    if (!optArr[el].selOpt.match(/\|/)){
                        var dataGroup = $.extend(true, {}, optArr[el]);
                        dataGroup.type = type;
                        var varNameCond = dataGroup.varNameCond;
                        //find dropdown based condition changes are created.
                        //in order to grep all array rows which has same id, following jquery pattern is used.
                        var condDiv = $('[id="var_' + gNum + "-" + varNameCond + '"]');
                        //bind change event to dropdown
                        $.each(condDiv, function (eachArrayForm) {
                            $(condDiv[eachArrayForm]).change(dataGroup, function () {
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
                                        parentDiv.find("#var_" + gNum + "-" + varName).children("option[value=" + autoVal[k] + "]").css("display","block")
                                    }
                                    //hide option if they are not valid 
                                    var hideOpt = $(allOpt).not(autoVal).get();
                                    for (var k = 0; k < hideOpt.length; k++) {
                                        var oldOpt= parentDiv.find("#var_" + gNum + "-" + varName).children("option[value=" + hideOpt[k] + "]")
                                        oldOpt.css("display","none");
                                        // if option selected that select first opt of dropdown
                                        if (oldOpt.is(':selected')){
                                            parentDiv.find("#var_" + gNum + "-" + varName).prop("selectedIndex", 0);;
                                        }
                                    }
                                } 
                            });
                            $(condDiv[eachArrayForm]).trigger("change")
                        });
                    }
                }
            });
        }
    }
}

//check if dynamic variables (_var) exist in autoVal
function checkDynamicVar(autoVal, autoFillJSON, parentDiv, gNum) {
    if (autoFillJSON !== null && autoFillJSON !== undefined && autoVal.match(/^_/)) {
        for (var k = 0; k < autoFillJSON.length; k++) {
            if (autoFillJSON[k].library[autoVal]) {
                //check if condition is met
                var conds = autoFillJSON[k].condition
                var genConds = autoFillJSON[k].genCondition
                if (conds && !$.isEmptyObject(conds)) {
                    var statusCond = checkConds(conds);
                    if (statusCond === true) {
                        return autoFillJSON[k].library[autoVal]
                    }
                } else if ($.isEmptyObject(conds) && $.isEmptyObject(genConds)) {
                    return autoFillJSON[k].library[autoVal]
                }
            }
        };
        if (parentDiv) {
            if (parentDiv.find("#var_" + gNum + "-" + autoVal)) {
                return parentDiv.find("#var_" + gNum + "-" + autoVal).val()
            }
        }
        return ""
    } else {
        return autoVal
    }
}

//convert {var1="yes", "filling_text"} or {var1=("yes","no"), _build+"filling_text"} to array format: [{varNameCond: "var1", selOpt: "yes", autoVal: "filling_text"}]
function createDynFillArr(autoform) {
    var allAutoForm = [];
    //find condition dependent varNameCond, selOpt, and autoVal
    $.each(autoform, function (elem) {
        var condArr = autoform[elem];
        for (var n = 0; n < condArr.length; n++) {
            // it was > 1 before, why?
            if (condArr[n].length > 0) {
                var autoObj = { varNameCond: null, selOpt: null, autoVal: [] };
                for (var k = 0; k < condArr[n].length; k++) {
                    // check if condArr has element like "var1=yes" where varName=var1 or "var1=(yes|no)"
                    if (condArr[n][k].match(/=/)) {
                        autoObj.varNameCond = condArr[n][k].match(/(.*)=(.*)/)[1]
                        autoObj.selOpt = condArr[n][k].match(/(.*)=(.*)/)[2]
                    } else {
                        autoObj.autoVal.push(condArr[n][k]);
                    }
                }
                allAutoForm.push(autoObj)
            }
        }
    });

    //if selOpt contains multiple options: (rRNA|ercc|miRNA|tRNA|piRNA|snRNA|rmsk)
    $.each(allAutoForm, function (el) {
        var selOpt = allAutoForm[el].selOpt;
        if (selOpt) {
            if (selOpt.match(/\|/)) {
                selOpt = selOpt.replace("(", "")
                selOpt = selOpt.replace(")", "")
                var allOpt = selOpt.split("|")
                for (var n = 0; n < allOpt.length; n++) {
                    var newData = $.extend(true, {}, allAutoForm[el]);
                    newData.selOpt = allOpt[n];
                    allAutoForm.push(newData)
                }
            }
        }
    });
    return allAutoForm
}


// if @autofill exists, then create event binders
//eg.  @autofill:{var1="yes", "filling_text"}  
// for multiple options @autofill:{var1=("yes","no"), "filling_text"}
// for dynamic filling @autofill:{var1=("yes","no"), _build+"filling_text"}
function addProcessPanelAutoform(gNum, name, varName, type, autoform) {
    var allAutoForm = [];
    if (autoform) {
        allAutoForm = createDynFillArr(autoform)
        //bind event handlers
        $.each(allAutoForm, function (el) {
            var dataGroup = $.extend(true, {}, allAutoForm[el]);
            dataGroup.type = type;
            var varNameCond = dataGroup.varNameCond;
            //find dropdown/checkbox where condition based changes are created.
            //in order to grep all array rows which has same id, following jquery pattern is used.
            var condDiv = $('[id="var_' + gNum + "-" + varNameCond + '"]');
            //bind change event to dropdown
            $.each(condDiv, function (eachArrayForm) {
                $(condDiv[eachArrayForm]).change(dataGroup, function () {
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
                                autoValAr[n] = checkDynamicVar(autoValAr[n], autoFillJSON, parentDiv, gNum)
                            }
                            autoVal = autoValAr.join("");
                        } else {
                            //check if dynamic variables (_var) exist in autoVal
                            autoVal = checkDynamicVar(autoVal, autoFillJSON, parentDiv, gNum)
                        }
                        if (autoVal) {
                            parentDiv.find("#var_" + gNum + "-" + varName).val(autoVal)
                        }
                    }
                });
                $(condDiv[eachArrayForm]).trigger("change")
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
        $.each(condi, function (elem) {
            var condArr = condi[elem];
            // check if condArr has element like "var1=yes" where varName=var1
            var filt = condArr.find(a => (new RegExp("^" + varName + "\\s*=")).test(a));
            if (filt) {
                //remove the elements where condition is defined
                showFormVarArr = condArr.filter(a => { return a !== filt });
                allCondForms = allCondForms.concat(showFormVarArr);
            }
        });
        // push all unique form names as array
        allUniCondForms = allCondForms.filter(function (item, pos, self) { return self.indexOf(item) == pos; });
        //bind event handlers
        $.each(condi, function (el) {
            var newdataGroup = $.extend(true, {}, dataGroup);
            var condArr = condi[el];
            // check if condArr has element like "var1=yes" where varName=var1
            var filt = condArr.find(a => (new RegExp("^" + varName + "\\s*=")).test(a));
            if (filt) {
                //trigger when selOpt is selected
                var selOpt = $.trim(filt.split("=")[1]);
                //find condition dependent forms
                showFormVarArr = condArr.filter(a => { return a !== filt });
                //initially hide all condition dependent forms
                for (var k = 0; k < showFormVarArr.length; k++) {
                    var showHideDiv = $("#addProcessRow-" + gNum).find("#var_" + gNum + "-" + showFormVarArr[k])[0];
                    $(showHideDiv).parent().css("display", "none");
                }
                //find dropdown/checkbox where condition based changes are created.
                var condDiv = $("#addProcessRow-" + gNum).find("#var_" + gNum + "-" + varName)[0];
                //bind change event to dropdown
                newdataGroup.showFormVarArr = showFormVarArr;
                newdataGroup.allUniCondForms = allUniCondForms;
                newdataGroup.selOpt = selOpt;
                newdataGroup.type = type;

                $(condDiv).change(newdataGroup, function () {
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
                            var showDiv = parentDiv.find("#var_" + gNum + "-" + showForms[i])[0];
                            if (showDiv) {
                                $(showDiv).parent().css("display", "inline");
                            }
                        }
                        for (var i = 0; i < hideForms.length; i++) {
                            var hideDiv = parentDiv.find("#var_" + gNum + "-" + hideForms[i])[0];
                            if (hideDiv) {
                                $(hideDiv).parent().css("display", "none");
                            }
                        }
                    }
                });
                $(condDiv).trigger("change")
            }
        });
    }
}



// check if all conditions match	
//type="default" will pass for $HOSTNAME == default
function checkConds(conds, type) {
    var checkConditionsFalse = [];
    var checkConditionsTrue = [];
    $.each(conds, function (co) {
        //check if condtion is $HOSTNAME specific
        if (co === "$HOSTNAME") {
            var hostname = conds[co];
            var chooseEnvHost = $('#chooseEnv').find(":selected").attr("host");
            if (hostname && chooseEnvHost && hostname === chooseEnvHost) {
                checkConditionsTrue.push(true);
            } else {
                if (type=="default" && hostname && chooseEnvHost && hostname == "default"){
                    checkConditionsTrue.push(true);
                } else {
                    checkConditionsFalse.push(false);
                }
            }
        } else {
            var varName = co.match(/params\.(.*)/)[1]; //variable Name
            var defName = conds[co]; // expected Value
            var checkVarName = $("#inputsTab").find("td[given_name='" + varName + "']")[0];
            if (checkVarName) {
                var varNameBut = $(checkVarName).children()[0];
                if (varNameBut) {
                    var varNameVal = $(varNameBut).val();
                }
                if (varNameVal && defName && varNameVal === defName) {
                    checkConditionsTrue.push(true)
                } else {
                    checkConditionsFalse.push(false)
                }
            }
        }
    });
    // if all conditions match, length==0 for checkConditionsFalse
    if (checkConditionsFalse.length === 0 && checkConditionsTrue.length > 0) {
        return true
    } else {
        return false
    }
}

function getInputVariables(button) {
    var rowID = button.parent().parent().attr("id"); //"inputTa-5"
    var gNumParam = rowID.split("Ta-")[1];
    var given_name = $("#input-PName-" + gNumParam).text(); //input-PName-3
    var qualifier = $('#' + rowID + ' > :nth-child(4)').text();
    var sType = "";
    if (qualifier === 'file' || qualifier === 'set') {
        sType = 'file'; //for simplification 
    } else if (qualifier === 'val') {
        sType = qualifier
    }
    return [rowID, gNumParam, given_name, qualifier, sType]
}

showHideSett = function (rowId){
    var dropdownID = $("#"+rowId).find("td[given_name] > select").attr("id");
    var buttons = $("#"+rowId).find("td[given_name] > button[id^=show_sett_]")
    if (buttons.length > 0){
        for (var i = 0; i < buttons.length; i++) {
            var buttonId = $(buttons[i]).attr("id");
            var yesCheck  = $("#"+dropdownID).val()
            if (yesCheck){
                if (yesCheck.toLowerCase() == "yes"){
                    $("#"+buttonId).css("display","inline")
                } else {
                    $("#"+buttonId).css("display","none")
                }
            } else {
                $("#"+buttonId).css("display","none")
            }
        }
    }
}

function hideProcessOptionsAsIcons (){
    var showSettingInputsAr = $('#inputsTable > tbody > tr[show_setting]')
    if (showSettingInputsAr.length >0){
        for (var i = 0; i < showSettingInputsAr.length; i++) {
            var rowId = $(showSettingInputsAr[i]).attr("id");
            var dropdownID = $(showSettingInputsAr[i]).find("td[given_name] > select").attr("id");
            var show_setting = $(showSettingInputsAr[i]).attr("show_setting")
            var show_settingArr = show_setting.split(',');
            for (var t = 0; t < show_settingArr.length; t++) {
                show_setting = show_settingArr[t];
                var show_settingProcessPanel = $('#ProcessPanel > div[processorgname="'+show_setting+'"],div[allname="'+show_setting+'"]')
                for (var k = 0; k < show_settingProcessPanel.length; k++) {
                    var panel = $(show_settingProcessPanel[k]);
                    var panelContent = $(show_settingProcessPanel[k]).children().children().eq(1);
                    var processorgname = panel.attr("processorgname")
                    var processname = panel.attr("processname")
                    var modulename = panel.attr("modulename")
                    var allname = panel.attr("allname")
                    var label = panel.attr("label")
                    var tooltip = "Edit: " + processname
                    if (show_settingArr.length > 1 || show_settingProcessPanel.length >1){
                        tooltip = "Edit: " + label
                    }

                    if (processorgname == show_setting || allname == show_setting){
                        //insert button
                        var buttonId = 'show_sett_'+allname;
                        var button = '<button style="display:none; margin-left:7px;" show_sett_but="'+allname+'" type="button" class="btn btn-primary btn-sm"  id="'+buttonId+'"><a data-toggle="tooltip" data-placement="bottom" data-original-title="'+tooltip+'"><span><i class="fa fa-wrench"></i></span></a></button>';
                        $(showSettingInputsAr[i]).children().eq(5).append(button);
                        var doCall = function (panel, panelContent, label, dropdownID, buttonId, rowId) {
                            panel.css("display","none");
                            if (rowId){
                                showHideSett(rowId)
                                $(function () {
                                    $(document).on('change', "#"+dropdownID, function (event) {
                                        showHideSett(rowId)
                                    });
                                });
                            }
                            //ui-dialog
                            $(panelContent).dialog({
                                title: label,
                                resizable: false,
                                draggable: true,
                                autoOpen: false,
                                position:['middle',100],
                                width: '90%',
                                modal: true,
                                minHeight: 0,
                                maxHeight: 650,
                                buttons: {
                                    "Ok": function () {
                                        $(this).dialog("close");
                                    }
                                },
                                open: function(event, ui) {
                                    $("body").css({ overflow: 'hidden'  })
                                    $("html").css({ overflow: 'hidden' })
                                },
                                beforeClose: function(event, ui) {
                                    $("html").css({ overflow: 'auto' })
                                    $("body").css({ overflow: 'auto' })
                                }
                            });


                            $(function () {
                                $(document).on('click', '#'+buttonId, function (event) {
                                    $(panelContent).dialog('open'); return false; 
                                });
                            });
                        }
                        doCall(panel, panelContent, label, dropdownID, buttonId, rowId);
                    }
                }
            }
        }


    }
    $('[data-toggle="tooltip"]').tooltip();
}



//fill file/Val buttons
function autoFillButton(buttonText, value, keepExist, url, urlzip, checkPath) {
    var button = $(buttonText);
    var checkDropDown = button.attr("id") == "dropDown";
    var checkFileExist = button.css("display") == "none";
    //if  checkDropDown == false and checkFileExist == true then edit
    //if  checkDropDown == false and checkFileExist == false then insert
    var rowID = "";
    var gNumParam = "";
    var given_name = "";
    var qualifier = "";
    var sType = "";
    [rowID, gNumParam, given_name, qualifier, sType] = getInputVariables(button);
    var proPipeInputID = $('#' + rowID).attr('propipeinputid');
    var inputID = null;
    var data = [];
    data.push({ name: "id", value: "" });
    data.push({ name: "name", value: value });
    // insert into project pipeline input table
    if (value && value != "") {
        if (checkDropDown == false && checkFileExist == false) {
            checkInputInsert(data, gNumParam, given_name, qualifier, rowID, sType, inputID, null, url, urlzip, checkPath);
        } else if (checkDropDown == false && checkFileExist == true && keepExist == false) {
            checkInputEdit(data, gNumParam, given_name, qualifier, rowID, sType, proPipeInputID, inputID, null, url, urlzip, checkPath);
        } else if (checkDropDown == true && keepExist == false) {
            // if proPipeInputID exist, then first remove proPipeInputID.
            if (proPipeInputID) {
                var removeInput = getValues({ "p": "removeProjectPipelineInput", id: proPipeInputID });
            }
            checkInputInsert(data, gNumParam, given_name, qualifier, rowID, sType, inputID, null, url, urlzip, checkPath);
        }
    } else { // if value is empty:"" then remove from project pipeline input table
        if (keepExist == false) {
            var removeInput = getValues({ "p": "removeProjectPipelineInput", id: proPipeInputID });
            removeSelectFile(rowID, qualifier);
        }
    }
}
// fill pipeline or process executor settings
function fillExecSettings(id, defName, type, inputName) {
    if (type === "pipeline") {
        setTimeout(function () {
            updateCheckBox('#exec_all', "true");
            fillFormById('#allProcessSettTable', id, defName);
        }, 1);
    } else if (type === "process") {
        setTimeout(function () {
            var findCheckBox = $('#processTable >tbody> tr[procproid=' + id + ']').find("input[name=check]")
            if (findCheckBox && findCheckBox[0]) {
                $.each(findCheckBox, function (st) {
                    var checkBoxId = $(findCheckBox[st]).attr("id")
                    updateCheckBox("#" + checkBoxId, "true");
                    updateCheckBox('#exec_each', "true");
                    fillFormById('#processTable >tbody> tr[procproid=' + id + ']', "input[name=" + inputName + "]", defName)
                });
            }
        }, 1);
    }
}

//run after page loads to fill if missing inputs
//reason-1: new input added into pipeline without changing rev
//reason-2: run copied into new revision 
function autofillEmptyInputs(autoFillJSON) {
    $.each(autoFillJSON, function (el) {
        var conds = autoFillJSON[el].condition;
        var states = autoFillJSON[el].statement;
        var url = autoFillJSON[el].url;
        var urlzip = autoFillJSON[el].urlzip;
        var checkPath = autoFillJSON[el].checkPath;
        if (conds && states && !$.isEmptyObject(conds) && !$.isEmptyObject(states)) {
            if (conds.$HOSTNAME) {
                var statusCond = checkConds(conds);
                if (statusCond === true) {
                    $.each(states, function (st) {
                        var defName = states[st]; // expected Value
                        var defUrl = url[st] || null;; // expected Value
                        var defUrlzip = urlzip[st] || null; // expected Value
                        var defcheckPath = checkPath[st] || null; // expected Value
                        //if variable start with "params." then check #inputsTab
                        if (st.match(/params\.(.*)/)) {
                            var varName = st.match(/params\.(.*)/)[1]; //variable Name
                            var checkVarName = $("#inputsTab").find("td[given_name='" + varName + "']")[0];
                            if (checkVarName) {
                                var varNameButAr = $(checkVarName).children();
                                if (varNameButAr && varNameButAr[0]) {
                                    var keepExist = true;
                                    autoFillButton(varNameButAr[0], defName, keepExist, defUrl, defUrlzip, defcheckPath);
                                }
                            }
                            autoCheck("fillstates")
                        }
                    });
                }
            }
        }
    })
}

//change propipeinputs in case all conds are true
function fillStates(states, url, urlzip, checkPath) {
    $("#inputsTab").loading('start');
    $.each(states, function (st) {
        var defName = states[st] ; // expected Value
        var defUrl = url[st] || null;; // expected Value
        var defUrlzip = urlzip[st] || null; // expected Value
        var defcheckPath = checkPath[st] || null; // expected Value
        //if variable start with "params." then check #inputsTab
        if (st.match(/params\.(.*)/)) {
            var varName = st.match(/params\.(.*)/)[1]; //variable Name
            var checkVarName = $("#inputsTab").find("td[given_name='" + varName + "']")[0];
            if (checkVarName) {
                var varNameButAr = $(checkVarName).children();
                if (varNameButAr && varNameButAr[0]) {
                    var keepExist = false;
                    autoFillButton(varNameButAr[0], defName, keepExist, defUrl, defUrlzip, defcheckPath);
                }
            }
            //if variable starts with "$" then run parameters for pipeline are defined. Fill run parameters. $SINGULARITY_IMAGE, $SINGULARITY_OPTIONS, $DOCKER_IMAGE, $DOCKER_OPTIONS, $MEMORY, $TIME, $QUEUE, $CPU, $EXEC_OPTIONS 
        } else if (st.match(/\$(.*)/)) {
            var varName = st.match(/\$(.*)/)[1]; //variable Name
            if (varName === "SINGULARITY_IMAGE") {
                $('#singu_img').val(defName);
                updateCheckBox('#singu_check', "true");
            } else if (varName === "DOCKER_IMAGE") {
                $('#docker_img').val(defName);
                updateCheckBox('#docker_check', "true");
            } else if (varName === "SINGULARITY_OPTIONS") {
                $('#singu_opt').val(defName);
                updateCheckBox('#singu_check', "true");
            } else if (varName === "DOCKER_OPTIONS") {
                $('#docker_opt').val(defName);
                updateCheckBox('#docker_check', "true");
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
            } else if (varName.match(/RUN_COMMAND@(.*)/) || varName === "RUN_COMMAND") {
                setTimeout(function () {
                    var initialText = $('#runCmd').val();
                    if (initialText == "") {
                        $('#runCmd').val(defName);
                    } else {
                        $('#runCmd').val(initialText + " && " + defName);
                    }
                }, 1);
            } else if (varName.match(/TIME@(.*)/)) {
                var processId = varName.match(/TIME@(.*)/)[1];
                fillExecSettings(processId, defName, "process", "time");
            } else if (varName.match(/QUEUE@(.*)/)) {
                var processId = varName.match(/QUEUE@(.*)/)[1]
                fillExecSettings(processId, defName, "process", "queue");
            } else if (varName.match(/MEMORY@(.*)/)) {
                var processId = varName.match(/MEMORY@(.*)/)[1]
                fillExecSettings(processId, defName, "process", "memory");
            } else if (varName.match(/CPU@(.*)/)) {
                var processId = varName.match(/CPU@(.*)/)[1]
                fillExecSettings(processId, defName, "process", "cpu");
            } else if (varName.match(/EXEC_OPTIONS@(.*)/)) {
                var processId = varName.match(/EXEC_OPTIONS@(.*)/)[1]
                fillExecSettings(processId, defName, "process", "opt");
            }

        } else { //if variable not start with "params." or "$" then check pipeline options:
            var varName = st;
            var checkVarName = $("#var_pipe-" + varName)[0];
            if (checkVarName) {
                $(checkVarName).val(defName);
            }
        }
    });
}
// to execute autofill function, binds event handlers
function bindEveHandler(autoFillJSON) {
    $("#chooseEnv").change(autoFillJSON, function () {
        var triggeredFillStates = false;
        var fillHostFunc = function(autoFillJSON, type) {
            var triggeredFillStates = false;
            $.each(autoFillJSON, function (el) {
                var conds = autoFillJSON[el].condition;
                var states = autoFillJSON[el].statement;
                var url = autoFillJSON[el].url;
                var urlzip = autoFillJSON[el].urlzip;
                var checkPath = autoFillJSON[el].checkPath;
                if (conds && states && !$.isEmptyObject(conds) && !$.isEmptyObject(states)) {
                    //bind eventhandler to #chooseEnv
                    if (conds.$HOSTNAME) {   
                        var statusCond = checkConds(conds, type);
                        if (statusCond === true) {
                            fillStates(states, url, urlzip, checkPath)
                            triggeredFillStates = true;
                            autoCheck("fillstates")
                        }
                    }
                };
            }); 
            return triggeredFillStates
        }
        triggeredFillStates = fillHostFunc(autoFillJSON)
        // fill $HOSTNAME ="default" states if not triggered before
        if (!triggeredFillStates){
            fillHostFunc(autoFillJSON, "default")
        }
    });


    $.each(autoFillJSON, function (el) {
        var conds = autoFillJSON[el].condition;
        var states = autoFillJSON[el].statement;
        var url = autoFillJSON[el].url;
        var urlzip = autoFillJSON[el].urlzip;
        var checkPath = autoFillJSON[el].checkPath;
        if (conds && states && !$.isEmptyObject(conds) && !$.isEmptyObject(states)) {
            //if condition exist other than $HOSTNAME then bind eventhandler to #params. button (eg. dropdown or inputValEnter)
            $.each(conds, function (el) {
                if (el !== "$HOSTNAME") {
                    //if variable start with "params." then check #inputsTab
                    if (el.match(/params\.(.*)/)) {
                        var varName = el.match(/params\.(.*)/)[1]; //variable Name
                        var checkVarName = $("#inputsTab").find("td[given_name='" + varName + "']")[0];
                        if (checkVarName) {
                            var varNameButAr = $(checkVarName).children();
                            if (varNameButAr && varNameButAr[0]) {
                                //bind eventhandler to indropdown button
                                $(varNameButAr[0]).change(function () {
                                    var statusCond = checkConds(conds);
                                    var statusCondDefault = checkConds(conds, "default");
                                    if (statusCond === true) {
                                        fillStates(states, url, urlzip, checkPath);
                                        autoCheck("fillstates")
                                    } else if (statusCondDefault === true){
                                        fillStates(states, url, urlzip, checkPath);
                                        autoCheck("fillstates")
                                    }
                                });
                            }
                        }
                    }
                }
            });
        }
    });
}

var addProfileLib = function (oldLibObj, profileVariables){
    var lines = profileVariables.split('\n');
    for (var i = 0; i < lines.length; i++) {
        var varName = null;
        var defaultVal = null;
        [varName, defaultVal] = parseVarPart(lines[i]);
        if (varName && defaultVal != null){
            oldLibObj[varName] = defaultVal
        }
    }
}

function addProfileVar(autoFillJSON) {
    var profileVar = getValues({ "p": "getProfileVariables" });
    if (autoFillJSON) {
        for (var i = 0; i < profileVar.length; i++) {
            var confirmUpdate = false;
            var proHost = profileVar[i].hostname
            var proVar = decodeHtml(profileVar[i].variable);
            // find conditions that matches $HOSTNAME
            $.each(autoFillJSON, function (el) {
                if (!confirmUpdate){
                    if (autoFillJSON[el].condition && autoFillJSON[el].condition != null && !$.isEmptyObject(autoFillJSON[el].condition)) {
                        if (autoFillJSON[el].condition.$HOSTNAME){
                            if (autoFillJSON[el].condition.$HOSTNAME == proHost){
                                addProfileLib (autoFillJSON[el].library,proVar);
                                confirmUpdate = true;
                            }
                        }
                    }
                }
            });
            //insert as a new row if not exist in the exising obj
            if (!confirmUpdate){
                var newCond = { condition: {}, genCondition: {}, statement: {}, library: {}, url:{}, urlzip:{}, checkPath:{} };
                newCond.condition.$HOSTNAME = proHost;
                addProfileLib(newCond.library,proVar);
                autoFillJSON.push(newCond);
            }
        }
    }
    return autoFillJSON
}

//parses header_script and create autoFill array. 
//eg. [condition:{hostname:ghpcc, var:mm10},statement:{indexPath:"/path"}] 
//or generic condition eg. [genCondition:{hostname:null, params.genomeTypePipeline:null}, library:{_species:"human"}] 
//url:{}, urlzip:{}, checkPath:{}
function parseAutofill(script) {
    if (script) {
        //check if autofill comment is exist: //* autofill
        if (script.match(/\/\/\* autofill/i)) {
            var lines = script.split('\n');
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
                                library[varName] = defaultVal
                            }
                        }
                    }
                    //find if condition
                    if (lines[i].match(/.*if *\((.*)\).*/i)) {
                        if (ifBlockStart) {
                            if (conds && states && library && genConds && (!$.isEmptyObject(conds) || !$.isEmptyObject(genConds)) && (!$.isEmptyObject(states) || !$.isEmptyObject(library))) {
                                autoFill.push({ condition: conds, genCondition: genConds, statement: states, library: library, url: url, urlzip:urlzip, checkPath:checkPath })
                            }
                            //push global variables    
                        } else if (!ifBlockStart) {
                            if (conds && states && library && genConds && ($.isEmptyObject(conds) && $.isEmptyObject(genConds) && (!$.isEmptyObject(states) || !$.isEmptyObject(library)))) {
                                autoFill.push({ condition: conds, genCondition: genConds, statement: states, library: library, url: url, urlzip:urlzip, checkPath:checkPath })
                            }
                        }
                        conds = {};
                        genConds = {};
                        library = {}; //new library object. Will be used for filling strings. 
                        states = {}; //new statement object. It will be filled with following statements until next if condition
                        url ={};
                        urlzip = {}; 
                        checkPath = {};
                        ifBlockStart = i;
                        cond = lines[i].match(/.*if *\((.*)\).*/i)[1]
                        if (cond) {
                            var condsplit = cond.split("&&");
                            $.each(condsplit, function (el) {
                                [varName, defaultVal] = parseVarPart(condsplit[el], "condition");
                                if (varName && defaultVal) {
                                    conds[varName] = defaultVal;
                                } else if (varName && !defaultVal) {
                                    genConds[varName] = defaultVal;
                                }
                            });
                        }
                        //end of the autofill block: //*or 
                    } else if (lines[i].match(/^\s*\/\/\*\s*$/i) || lines[i].match(/^\s*\/\/\* autofill\s*$/i)) {
                        blockStart = null;
                        ifBlockStart = null;
                        if (conds && states && library && genConds && (!$.isEmptyObject(conds) || !$.isEmptyObject(genConds)) && (!$.isEmptyObject(states) || !$.isEmptyObject(library))) {
                            autoFill.push({ condition: conds, genCondition: genConds, statement: states, library: library, url: url, urlzip:urlzip, checkPath:checkPath })
                        }
                        //end of if condition with curly brackets 
                    } else if ($.trim(lines[i]).match(/^\}$/m)) {
                        if (ifBlockStart) {
                            ifBlockStart = null;
                            if (conds && states && library && genConds && (!$.isEmptyObject(conds) || !$.isEmptyObject(genConds)) && (!$.isEmptyObject(states) || !$.isEmptyObject(library))) {
                                autoFill.push({ condition: conds, genCondition: genConds, statement: states, library: library, url: url, urlzip:urlzip, checkPath:checkPath })
                            }
                        }
                        conds = {};
                        genConds = {};
                        library = {};
                        states = {};
                        url ={};
                        urlzip = {}; 
                        checkPath = {};
                        //lines of statements 
                    } else {
                        if (lines[i].match('\/\/\*') && lines[i].split('\/\/\*').length>1 ) {
                            var varPart = lines[i].split('\/\/\*')[0];
                            var regPart = lines[i].split('\/\/\*')[1];
                        } else {
                            var varPart = lines[i];
                            var regPart = "";
                        }
                        if (varPart){
                            [varName, defaultVal] = parseVarPart(varPart);
                        }
                        var urlVal = null;
                        var urlzipVal = null;
                        var checkPathVal = null;
                        if (regPart){
                            if (regPart.match(/@url|@checkpath|/i)){
                                [urlVal, urlzipVal, checkPathVal] = parseRegPartAutofill(regPart)
                            }
                        }
                        if (varName && defaultVal) {
                            if (varName.match(/^_.*$/)) {
                                library[varName] = defaultVal;
                            } else {
                                states[varName] = defaultVal;
                                if (urlVal){
                                    url[varName] = urlVal;
                                }
                                if (urlzipVal){
                                    urlzip[varName] = urlzipVal;
                                }
                                if (checkPathVal){
                                    checkPath[varName] = checkPathVal;
                                }
                                //check if params.VARNAME is defined and return all VARNAMES to fill them as system inputs
                                if (varName.match(/params\.(.*)/)) {
                                    var sysInput = varName.match(/params\.(.*)/)[1];
                                    if (sysInput && sysInput != "") {
                                        if (systemInputs.indexOf(sysInput) === -1) {
                                            systemInputs.push(sysInput)
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
    return autoFill
}

// get new statements for each combination of conditions
function getNewStatements(conditions, autoFillJSON, genStatement, url, urlzip, checkPath) {
    var newStateCond = { condition: {}, genCondition: {}, statement: {}, library: {}, url:{}, urlzip:{}, checkPath:{} };
    if (conditions) {
        var defValLibrary = [];
        var mergedLib = {}
        // get Merged library for given conditions
        $.each(conditions, function (ele) {
            var varName = Object.keys(conditions[ele]);
            var defVal = conditions[ele][varName];
            newStateCond.condition[varName] = defVal;
            //find varName = defVal statement in autoFillJSON which has library
            $.each(autoFillJSON, function (elem) {
                if (autoFillJSON[elem].condition && autoFillJSON[elem].condition != "" && !$.isEmptyObject(autoFillJSON[elem].condition) && autoFillJSON[elem].library && autoFillJSON[elem].library != "" && !$.isEmptyObject(autoFillJSON[elem].library)) {
                    var cond = autoFillJSON[elem].condition;
                    if (cond[varName]) {
                        var originDefVal = cond[varName];
                        if (originDefVal === defVal) {
                            var library = autoFillJSON[elem].library;
                            jQuery.extend(mergedLib, library);
                        }
                    }
                    //find default library that not belong to any condition or generic condition.
                } else if ($.isEmptyObject(autoFillJSON[elem].condition) && $.isEmptyObject(autoFillJSON[elem].genCondition) && autoFillJSON[elem].library && !$.isEmptyObject(autoFillJSON[elem].library)){
                    var deflibrary = autoFillJSON[elem].library;
                    jQuery.extend(mergedLib, deflibrary);    
                }
            });

        });

        // use Merged library to fill genStatement,url,urlzip,checkPath
        var fillWithNewValue = function(newStateCond, section,fillName, mergedLib){
            $.each(section, function (key) {
                var stateValue = section[key];
                var newStateValue = fillStateValue(stateValue, mergedLib);
                newStateCond[fillName][key] = newStateValue;
            });
        }
        var sectionAr = [genStatement,url,urlzip,checkPath];
        var fillName = ["statement","url","urlzip","checkPath"];
        for (var i = 0; i < sectionAr.length; i++) {
            fillWithNewValue(newStateCond, sectionAr[i], fillName[i], mergedLib);
        }

    }
    return newStateCond
}

//replacing stateValue text by using library object
function fillStateValue(stateValue, library) {
    $.each(library, function (key) {
        var replaceKey = '\\$\\{' + key + "\\}";
        var replaceVal = library[key];
        var re = new RegExp(replaceKey, "g");
        stateValue = stateValue.replace(re, replaceVal);
    });
    return stateValue;
}

//Generates combinations from n arrays with m elements
function cartesianProduct(arr) {
    return arr.reduce(function (a, b) {
        return a.map(function (x) {
            return b.map(function (y) {
                return x.concat(y);
            })
        }).reduce(function (a, b) { return a.concat(b) }, [])
    }, [[]])
}


//find each generic condition in other cond&state pairs and get their default values.
function findDefVal(genConditions, autoFillJSON) {
    var genCondDefaultVal = [];
    $.each(genConditions, function (varName) {
        var defValArray = [];
        $.each(autoFillJSON, function (elem) {
            // find conditions and library that satisfy varName
            if (autoFillJSON[elem].condition && autoFillJSON[elem].condition != "" && !$.isEmptyObject(autoFillJSON[elem].condition) && autoFillJSON[elem].library && autoFillJSON[elem].library != "" && !$.isEmptyObject(autoFillJSON[elem].library)) {
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
    return genCondDefaultVal
}

//reads generic conditions and create condition&statements pairs 
//eg. [genCondition:{hostname:null, genomeType:null}, library:{_species:"human"}] to [condition:{hostname:ghpcc, genomeType:human_hg19},statement:{indexPath:"/path"}] 
function decodeGenericCond(autoFillJSON) {
    if (autoFillJSON) {
        $.each(autoFillJSON, function (el) {
            // find generic conditions
            if (autoFillJSON[el].genCondition && autoFillJSON[el].genCondition != "" && !$.isEmptyObject(autoFillJSON[el].genCondition)) {
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
                $.each(combiConditions, function (cond) {
                    newCondStatements = getNewStatements(combiConditions[cond], autoFillJSON, genStatements, url, urlzip, checkPath);
                    autoFillJSON.push(newCondStatements)
                });
            }
        });
    }
    return autoFillJSON
}

//*** if input circle is defined in workflow then insertInputOutputRow function is used to insert row into inputs table based on edges of input parameters.
//*** if variable start with "params." then  insertInputRowParams function is used to insert rows into inputs table.
function insertInputRowParams(defaultVal, opt, pipeGnum, varName, type, name, showsett) {
    var dropDownQual = false;
    var paraQualifier = "val"
    var paramGivenName = varName;
    var processName = "-";
    var paraIdentifier = "-"
    var paraFileType = "-"
    var firGnum = pipeGnum;
    var secGnum = "";
    var rowType = "input";
    var show_setting = "";

    // "Use default" button is added if defVal attr is defined.
    if (defaultVal && defaultVal != "") {
        var defValButton = getButtonsDef('defVal', 'Use Default', defaultVal);
    } else {
        var defValButton = "";
    }
    // dropdown is added if dropdown attr is defined.
    if (type == "dropdown" && opt && opt != "") {
        var dropDownMenu = getDropdownDef('indropdown'+firGnum, "indropdown", opt, "Choose Value");
        dropDownQual = true;
    } else {
        var dropDownMenu = "";
    }
    //show_setting
    if (showsett){
        if (Array.isArray(showsett)){
            show_setting = showsett.join(",");
        }
    }
    var selectFileButton = getSelectFileButton(paraQualifier, dropDownQual, dropDownMenu, defValButton)
    var inRow = getRowTable(rowType, firGnum, secGnum, paramGivenName, paraIdentifier, paraFileType, paraQualifier, processName, selectFileButton, show_setting);
    // check if parameters added into table before, if not fill table
    insertInRow(inRow, paramGivenName, rowType, "paramsInputs")
    if ($("#userInputs").css("display") === "none") {
        $("#userInputs").css("display", "table-row")
    }
    //check if project_pipeline_inputs exist then fill:
    var getProPipeInputs = projectPipeInputs.filter(function (el) { return el.given_name == paramGivenName })
    var rowID = rowType + 'Ta-' + firGnum;
    if (getProPipeInputs && getProPipeInputs != "") {
        if (getProPipeInputs.length > 0) {
            var filePath = getProPipeInputs[0].name; //value for val type
            var proPipeInputID = getProPipeInputs[0].id;
            var given_name = getProPipeInputs[0].given_name;
            var collection_id = getProPipeInputs[0].collection_id;
            var collection_name = getProPipeInputs[0].collection_name;
            var collection = { collection_id: collection_id, collection_name: collection_name }
            var url = getProPipeInputs[0].url;
            var urlzip = getProPipeInputs[0].urlzip;
            var checkPath = getProPipeInputs[0].checkpath;
            insertSelectInput(rowID, firGnum, filePath, proPipeInputID, paraQualifier, collection, url, urlzip, checkPath);
        }
        if (getProPipeInputs.length > 1) {
            for (var k = 1; k < getProPipeInputs.length; k++) {
                var removeInput = getValues({ "p": "removeProjectPipelineInput", id: getProPipeInputs[k].id });
            }
        }
    }
    //check if run saved before
    if (pipeData[0].date_created == pipeData[0].date_modified) {
        //after filling, if "use default" button is exist, then click default option.
        clickUseDefault(rowID, defaultVal);
    }
}


function clickUseDefault(rowID, defaultVal) {
    //    var checkDropDown = $('#' + rowID).find('select[indropdown]')[0]
    //    var checkinputValEnter = $('#' + rowID).find('#inputValEnter')[0]
    var checkDefVal = $('#' + rowID).find('#defValUse').css('display')
    if (defaultVal && defaultVal != "" && checkDefVal !== "none") {
        $('#' + rowID).find('#defValUse').trigger("click")
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
//              }]
//     }
function parseProPipePanelScript(script) {
    var panelObj = { schema: [], style: [] };
    var lines = script.split('\n');
    for (var i = 0; i < lines.length; i++) {
        var varName = null;
        var defaultVal = null;
        var type = null;
        var desc = null;
        var tool = null;
        var opt = null;
        var multiCol = null;
        var autoform = null;
        var showsett = null;
        var arr = null;
        var cond = null;
        var title = null;
        if (lines[i].split('\/\/\*').length > 1) {
            var varPart = lines[i].split('\/\/\*')[0];
            var regPart = lines[i].split('\/\/\*')[1];
        } else {
            var regPart = lines[i].split('\/\/\*')[0];
        }
        if (varPart) {
            [varName, defaultVal] = parseVarPart(varPart);
        }
        if (regPart) {
            [type, desc, tool, opt, multiCol, arr, cond, title, autoform, showsett] = parseRegPart(regPart);
        }
        if (type && varName) {
            panelObj.schema.push({
                varName: varName,
                defaultVal: defaultVal,
                type: type,
                desc: desc,
                tool: tool,
                opt: opt,
                title: title,
                autoform: autoform,
                showsett: showsett
            })
        }
        if (multiCol || arr || cond) {
            panelObj.style.push({
                multicol: multiCol,
                array: arr,
                condi: cond
            })
        }
    }
    return panelObj;
}

//--Insert Process and Pipeline Panel (where pipelineOpt processOpt defined)
function insertProPipePanel(script, gNum, name, pObj, processData) {
    var MainGNum = "";
    var prefix = "";
    var onlyModuleName = "";
    var onlyProcessName = name;
    var processOrgName = "";
    var separator = ""
    if (pObj != window) {
        MainGNum = pObj.MainGNum;
        onlyModuleName = pObj.lastPipeName
        onlyProcessName = pObj.name
        separator = ": "
        prefix = MainGNum + "_";
    }
    if (processData){
        if (processData[0]){
            if (processData[0].name){
                processOrgName = processData[0].name;
            }
        }
    }
    if (script) {
        //check if parameter comment is exist: //*
        if (script.match(/\/\/\*/)) {
            var panelObj = parseProPipePanelScript(script);
            //create processHeader
            var headerLabel = createLabel(onlyModuleName) + separator +createLabel(onlyProcessName)
            var processHeader = '<div class="panel-heading collapsible collapseIconDiv" data-toggle="collapse" href="#collapse-' + prefix + gNum + '"><h4 class="panel-title">' + headerLabel + ' Options <i data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a style="font-size:15px; padding-left:10px;" class="fa collapseIcon fa-plus-square-o"></a></i></h4></div>';
            var processBodyInt = '<div id="collapse-' + prefix + gNum + '" class="panel-collapse collapse"><div id="addProcessRow-' + prefix + gNum + '" class="panel-body">'
            //create processPanel
            $('#ProcessPanel').append('<div id="proPanelDiv-' + prefix + gNum + '" style="display:none;" processorgname="'+processOrgName+'" modulename="'+onlyModuleName+'" processname="'+onlyProcessName+'" allname="'+name+'" label="'+headerLabel+'"><div id="proPanel-' + prefix + gNum + '" class="panel panel-default" style=" margin-bottom:3px;">' + processHeader + processBodyInt + '</div></div></div></div>')
            var multicol = null;
            var array = null;
            var condi = null;
            //only one array for each(multicol, array, condi) tag is expected
            if (!$.isEmptyObject(panelObj.style[0])) {
                multicol = panelObj.style[0].multicol;
                array = panelObj.style[0].array;
                condi = panelObj.style[0].condi;
            }
            var displayProDiv = false;
            for (var i = 0; i < panelObj.schema.length; i++) {
                var varName = panelObj.schema[i].varName;
                var defaultVal = panelObj.schema[i].defaultVal;
                var type = panelObj.schema[i].type;
                var desc = panelObj.schema[i].desc;
                var tool = panelObj.schema[i].tool;
                var opt = panelObj.schema[i].opt;
                var title = panelObj.schema[i].title;
                var autoform = panelObj.schema[i].autoform;
                var showsett = panelObj.schema[i].showsett;
                if (type && varName) {
                    // if variable start with "params." then insert into inputs table
                    if (varName.match(/params\./)) {
                        varName = varName.match(/params\.(.*)/)[1];
                        pipeGnum = pipeGnum - 1; //negative counter for pipeGnum
                        insertInputRowParams(defaultVal, opt, pipeGnum, varName, type, name, showsett);
                    } else {
                        displayProDiv = true;
                        addProcessPanelRow(prefix + gNum, name, varName, defaultVal, type, desc, opt, tool, multicol, array, title);
                    }
                    if (autoform) {
                        // if @autofill exists, then create event binders
                        addProcessPanelAutoform(prefix + gNum, name, varName, type, autoform);
                    }
                }
            }
            if (array){
                //if defVal is array than insert array rows and fill them
                for (var a = 0; a < array.length; a++) {
                    for (var i = 0; i < panelObj.schema.length; i++) {
                        var varName = panelObj.schema[i].varName;
                        var defaultVal = panelObj.schema[i].defaultVal;
                        if ($.isArray(defaultVal)){
                            var insertObj = {}
                            insertObj[prefix + gNum] = {}
                            $.each(array, function (el) {
                                if (array[el].indexOf(varName) > -1) {
                                    var arrayId = array[el].join('_');
                                    for (var k = 0; k < defaultVal.length; k++) {
                                        var ind = k+1
                                        //check if div inserted or not
                                        if (!$('#addProcessRow-' + prefix + gNum + "> #" + arrayId + '_ind'+ind).length){
                                            insertObj[prefix + gNum][varName+"_ind"+ind]= defaultVal[k]
                                        }
                                    }
                                    addArrForms(insertObj)
                                }
                            });
                            //fill rows with with default values
                            $.each(array, function (el) {
                                if (array[el].indexOf(varName) > -1) {
                                    var arrayId = array[el].join('_');
                                    for (var k = 0; k < defaultVal.length; k++) {
                                        var ind = k+1
                                        if ($('#addProcessRow-' + prefix + gNum + "> #" + arrayId + '_ind'+ind).length){
                                            var fillObj = {}
                                            fillObj[varName+"_ind"+ind]= defaultVal[k]
                                            var inputDiv = $('#addProcessRow-' + prefix + gNum + "> #" + arrayId + '_ind'+ind).find("#var_"+prefix + gNum+"-"+varName);
                                            var inputDivType = $(inputDiv).attr("type");
                                            fillEachProcessOpt(fillObj, varName+"_ind"+ind, inputDiv, inputDivType);
                                        }
                                    }
                                }
                            });
                        }
                    }
                }
            }
            if (condi) {
                for (var a = 0; a < condi.length; a++) {
                    //if contains multiple options: (rRNA|ercc|miRNA|tRNA|piRNA|snRNA|rmsk)
                    $.each(condi[a], function (el) {
                        for (var k = 0; k < condi[a][el].length; k++) {
                            var selCond = condi[a][el][k];
                            var varN = "";
                            var restN = "";
                            if (selCond.match(/\|/) && selCond.match(/\=/)) {
                                [varN, restN] = parseVarPart(selCond)
                                restN = restN.replace("(", "")
                                restN = restN.replace(")", "")
                                var allOpt = restN.split("|")
                                for (var n = 0; n < allOpt.length; n++) {
                                    var newData = condi[a][el].slice();
                                    newData[k] = varN + "=" + allOpt[n];
                                    condi[a].push(newData)
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
                        addProcessPanelCondi(prefix + gNum, name, varName, type, each_condi);
                    }
                }
            }
            if (displayProDiv === true) {
                $('[data-toggle="tooltip"]').tooltip();
                $('#proPanelDiv-' + prefix + gNum).css('display', 'inline');
                //                $('#ProcessPanelTitle').css('display', 'inline');

            }
        }
    }
}


function getRowTable(rowType, firGnum, secGnum, paramGivenName, paraIdentifier, paraFileType, paraQualifier, processName, button, show_setting) {
    var trID = 'id="' + rowType + 'Ta-' + firGnum + '"';
    var show_setting_attr = "";
    if (show_setting){
        show_setting_attr = ' show_setting="'+show_setting+'" ';
    } 
    if (paraQualifier == "val") {
        paraFileType = "-";
    }
    return '<tr ' + trID + show_setting_attr +' ><td id="' + rowType + '-PName-' + firGnum + '" scope="row">' + paramGivenName + '</td><td style="display:none;">' + paraIdentifier + '</td><td style="display:none;">' + paraFileType + '</td><td style="display:none;">' + paraQualifier + '</td><td style="display:none;"> <span id="proGName-' + secGnum + '">' + processName + '</span></td><td given_name="' + paramGivenName + '">' + button + '</td></tr>'
}

function insertProRowTable(process_id, gNum, procName, procQueDef, procMemDef, procCpuDef, procTimeDef, procOptDef) {
    return '<tr procProId="' + process_id + '" id="procGnum-' + gNum + '"><td><input name="check" id="check-' + gNum + '" type="checkbox" </td><td>' + procName + '</td><td><input name="queue" class="form-control execSetting" type="text" value="' + procQueDef + '"></input></td><td><input class="form-control execSetting" type="text" name="memory" value="' + procMemDef + '"></input></td><td><input name="cpu" class="form-control execSetting" type="text" value="' + procCpuDef + '"></input></td><td><input name="time" class="form-control execSetting" type="text" value="' + procTimeDef + '"></input></td><td><input name="opt" class="form-control execSetting" type="text" value="' + procOptDef + '"></input></td></tr>'
}



//--Pipeline details table --
function addProPipeTab(process_id, gNum, procName, pObj) {
    if (pObj && pObj !== window) {
        procName = pObj.lastPipeName + "_" + procName;
    }
    var procQueDef = 'short';
    var procMemDef = '10'
    var procCpuDef = '1';
    var procTimeDef = '100';
    var procOptDef = '';
    var proRow = insertProRowTable(process_id, gNum, procName, procQueDef, procMemDef, procCpuDef, procTimeDef, procOptDef);
    $('#processTable > tbody:last-child').append(proRow);
}

function addPipeline(piID, x, y, name, pObjOrigin, pObjSub) {
    var id = piID
    var prefix = "p";
    var MainGNum = "";
    //load workflow of pipeline modules 
    MainGNum = pObjOrigin.MainGNum;
    if (pObjOrigin != window) {
        prefix = "p" + MainGNum + "p";
    }

    //gnum uniqe, id same id (Written in class) in same type process
    pObjOrigin.g = d3.select("#mainG" + MainGNum).append("g")
        .attr("id", "g" + MainGNum + "-" + pObjOrigin.gNum)
        .attr("class", "g-p" + id) //for pipeline modules
        .attr("transform", "translate(" + x + "," + y + ")")
        .on("mouseover", mouseOverG)
        .on("mouseout", mouseOutG)
    //gnum(written in id): uniqe, id(Written in class): same id in same type process, bc(written in type): same at all bc
    pObjOrigin.g.append("circle").attr("id", "bc" + MainGNum + "-" + pObjOrigin.gNum)
        .attr("class", "bc" + MainGNum + "-" + id)
        .attr("type", "bc")
        .attr("cx", cx)
        .attr("cy", cy)
        .attr("r", rP + ior)
        .attr("fill", "red")
        .transition()
        .delay(500)
        .duration(3000)
        .attr("fill", "#cdcff7")
    //gnum(written in id): uniqe, id(Written in class): same id in same type process, sc(written in type): same at all bc
    pObjOrigin.g.append("circle")
        .datum([{
            cx: 0,
            cy: 0
        }])
        .attr("id", "sc-" + MainGNum + "-" + pObjOrigin.gNum)
        .attr("class", "sc" + MainGNum + "-" + id)
        .attr("type", "sc")
        .attr("r", rP - ior)
        .attr("fill", "#BEBEBE")
        .attr('fill-opacity', 0.6)
        .on("mouseover", scMouseOver)
        .on("mouseout", scMouseOut)
        .call(drag)
    //gnum(written in id): uniqe,
    pObjOrigin.g.append("text").attr("id", "text" + MainGNum + "-" + pObjOrigin.gNum)
        .datum([{
            cx: 0,
            cy: 0
        }])
        .attr('font-family', "FontAwesome, sans-serif")
        .attr('font-size', '1em')
        .attr('name', name)
        .attr('class', 'process')
        .text(truncateName(name, 'pipelineModule'))
        .style("text-anchor", "middle")
        .on("mouseover", scMouseOver)
        .on("mouseout", scMouseOut)
        .call(drag)

    //get process list of pipeline
    if (pObjSub.sData) {
        if (Object.keys(pObjSub.sData).length > 0) {
            //--Pipeline details table add process--
            pObjSub.nodesOrg = pObjSub.sData[0].nodes
            pObjSub.nodesOrg = JSON.parse(pObjSub.nodesOrg.replace(/'/gi, "\""));
            pObjSub.edOrg = pObjSub.sData[0].edges;
            pObjSub.edOrg = JSON.parse(pObjSub.edOrg.replace(/'/gi, "\""))["edges"]
            pObjSub.inNodes = {}; //input nodes that are connected to "input parameters"
            pObjSub.outNodes = []; //output nodes that are connected to "output parameters"
            for (var ee = 0; ee < pObjSub.edOrg.length; ee++) {
                if (pObjSub.edOrg[ee].indexOf("inPro") > -1) {
                    pObjSub.edsOrg = pObjSub.edOrg[ee].split("_")
                    if (pObjSub.edsOrg[0][0] === "i") { //i-50-0-46-6_o-inPro-1-46-7
                        if (!pObjSub.inNodes[pObjSub.edsOrg[1]]) {
                            pObjSub.inNodes[pObjSub.edsOrg[1]] = [];
                        }
                        pObjSub.inNodes[pObjSub.edsOrg[1]].push(pObjSub.edsOrg[0]); //keep nodes in the same array if they connected to same "input parameter"
                    } else { //o-inPro-1-46-7_i-50-0-46-6
                        if (!pObjSub.inNodes[pObjSub.edsOrg[0]]) {
                            pObjSub.inNodes[pObjSub.edsOrg[0]] = []
                        }
                        pObjSub.inNodes[pObjSub.edsOrg[0]].push(pObjSub.edsOrg[1]);
                    }
                } else if (pObjSub.edOrg[ee].indexOf("outPro") > -1) {
                    pObjSub.edsOrg = pObjSub.edOrg[ee].split("_")
                    if (pObjSub.edsOrg[0][0] == "o") {
                        pObjSub.outNodes.push(pObjSub.edsOrg[0]);
                    } else {
                        pObjSub.outNodes.push(pObjSub.edsOrg[1]);
                    }
                }
            }
            //I / O id naming: [0] i = input, o = output - [1] process database ID - [2] The number of I / O of the selected process - [3] Parameter database ID - [4] uniqe number
            var c = 0;
            $.each(pObjSub.inNodes, function (k) {
                if (pObjSub.inNodes[k].length === 1) {
                    var proId = pObjSub.inNodes[k][0].split("-")[1];
                    var parId = pObjSub.inNodes[k][0].split("-")[3];
                    var ccNodeId = "p" + pObjSub.MainGNum + pObjSub.inNodes[k][0];
                    var ccNode = $("#" + ccNodeId);
                    ccIDList[prefix + "i-" + proId + "-" + c + "-" + parId + "-" + pObjOrigin.gNum] = ccNodeId;
                    d3.select("#g" + MainGNum + "-" + pObjOrigin.gNum).append("circle")
                        .attr("id", prefix + "i-" + proId + "-" + c + "-" + parId + "-" + pObjOrigin.gNum)
                        .attr("ccID", ccNodeId) //copyID for pipeline modules
                        .attr("type", "I/O")
                        .attr("kind", "input")
                        .attr("parentG", "g" + MainGNum + "-" + pObjOrigin.gNum)
                        .attr("name", ccNode.attr('name'))
                        .attr("status", "standard")
                        .attr("connect", "single")
                        .attr("class", ccNode.attr('class'))
                        .attr("cx", calculatePos(Object.keys(pObjSub.inNodes).length, c, "cx", "inputsPipe"))
                        .attr("cy", calculatePos(Object.keys(pObjSub.inNodes).length, c, "cy", "inputsPipe"))
                        .attr("r", ior)
                        .attr("fill", "tomato")
                        .attr('fill-opacity', 0.8)
                        .on("mouseover", IOmouseOver)
                        .on("mousemove", IOmouseMove)
                        .on("mouseout", IOmouseOut)
                    //                        .on("mousedown", IOconnect)
                    c++;
                } else if (pObjSub.inNodes[k].length > 1) {
                    pObjSub.ccIDAr = [];
                    for (var i = 0; i < pObjSub.inNodes[k].length; i++) {
                        pObjSub.ccIDAr[i] = "p" + pObjSub.MainGNum + pObjSub.inNodes[k][i];
                        var proId = pObjSub.inNodes[k][i].split("-")[1];
                        var parId = pObjSub.inNodes[k][i].split("-")[3];
                        ccIDList[prefix + "i-" + proId + "-" + c + "-" + parId + "-" + pObjOrigin.gNum] = "p" + pObjSub.MainGNum + pObjSub.inNodes[k][i];
                    }
                    var ccNode = $("#" + pObjSub.ccIDAr[0])
                    d3.select("#g" + MainGNum + "-" + pObjOrigin.gNum).append("circle")
                        .attr("id", prefix + "i-" + proId + "-" + c + "-" + parId + "-" + pObjOrigin.gNum)
                        .attr("ccID", pObjSub.ccIDAr) //copyID for pipeline modules
                        .attr("type", "I/O")
                        .attr("kind", "input")
                        .attr("parentG", "g" + MainGNum + "-" + pObjOrigin.gNum)
                        .attr("name", ccNode.attr('name'))
                        .attr("status", "standard")
                        .attr("connect", "single")
                        .attr("class", ccNode.attr('class'))
                        .attr("cx", calculatePos(Object.keys(pObjSub.inNodes).length, c, "cx", "inputsPipe"))
                        .attr("cy", calculatePos(Object.keys(pObjSub.inNodes).length, c, "cy", "inputsPipe"))
                        .attr("r", ior)
                        .attr("fill", "tomato")
                        .attr('fill-opacity', 0.8)
                        .on("mouseover", IOmouseOver)
                        .on("mousemove", IOmouseMove)
                        .on("mouseout", IOmouseOut)
                    //                        .on("mousedown", IOconnect)
                    c++;
                }
            })
            for (var k = 0; k < pObjSub.outNodes.length; k++) {
                var proId = pObjSub.outNodes[k].split("-")[1];
                var parId = pObjSub.outNodes[k].split("-")[3];
                var ccNodeID = "p" + pObjSub.MainGNum + pObjSub.outNodes[k];
                var ccNode = $("#" + ccNodeID)
                ccIDList[prefix + "o-" + proId + "-" + k + "-" + parId + "-" + pObjOrigin.gNum] = ccNodeID;
                d3.select("#g" + MainGNum + "-" + pObjOrigin.gNum).append("circle")
                    .attr("id", prefix + "o-" + proId + "-" + k + "-" + parId + "-" + pObjOrigin.gNum)
                    .attr("ccID", ccNodeID) //copyID for pipeline modules
                    .attr("type", "I/O")
                    .attr("kind", "output")
                    .attr("parentG", "g" + MainGNum + "-" + pObjSub.gNum)
                    .attr("name", ccNode.attr('name'))
                    .attr("status", "standard")
                    .attr("connect", "single")
                    .attr("class", ccNode.attr('class'))
                    .attr("cx", calculatePos(pObjSub.outNodes.length, k, "cx", "outputsPipe"))
                    .attr("cy", calculatePos(pObjSub.outNodes.length, k, "cy", "outputsPipe"))
                    .attr("r", ior).attr("fill", "steelblue")
                    .attr('fill-opacity', 0.8)
                    .on("mouseover", IOmouseOver)
                    .on("mousemove", IOmouseMove)
                    .on("mouseout", IOmouseOut)
                //                    .on("mousedown", IOconnect)
            }
        }
    }
    pObjOrigin.processList[("g" + MainGNum + "-" + pObjOrigin.gNum)] = name
    pObjOrigin.gNum = pObjOrigin.gNum + 1

}

function findType(id) {
    var parameter = [];
    var parameter = parametersData.filter(function (el) { return el.id == id });
    if (parameter && parameter != '') {
        return parameter[0].file_type
    } else {
        return '';
    }
}

function calculatePos(len, k, poz, type) {
    var degree = (180 / (len + 1)) * (k + 1)
    var inp = (270 - (180 / (len + 1)) * (k + 1)) * Math.PI / 180
    var out = (270 - (-180 / (len + 1)) * (k + 1)) * Math.PI / 180
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
        calc = Math.cos(mathVar)
        result = (calc * calcR)
    } else {
        calc = Math.sin(inp)
        result = (calc * calcR)
    }
    return result;
}

function mouseOverG() {
    parent = document.getElementById(this.id).parentElement.id;
    //deactive for pipeline modules
    if (parent.match(/mainG(.*)/)[1] === "") {
        d3.select("#container").on("mousedown", null)
        if (!binding) {
            d3.select("#del-" + this.id.split("-")[1]).style("opacity", 1)
            d3.select("#info-" + this.id.split("-")[1]).style("opacity", 1)
        }
    }
}

function mouseOutG() {
    d3.select("#container").on("mousedown", cancel)
    d3.select("#del-" + this.id.split("-")[1]).style("opacity", 0.2)
    d3.select("#info-" + this.id.split("-")[1]).style("opacity", 0.2)

}

var drag = d3.behavior.drag()
.origin(function (d) {
    return d;
})
.on("dragstart", dragstarted)
.on("drag", dragged)
.on("dragend", dragended);

function dragstarted(d) {

    selectedg = document.getElementById(this.id).parentElement
    coor = d3.mouse(this)
    diffx = 0 - coor[0]
    diffy = 0 - coor[1]
    d3.event.sourceEvent.stopPropagation();
    d3.select(document.getElementById(this.id).parentElement).classed("dragging", true);
}

function dragged(d) {
    if (!binding) {
        coor = d3.mouse(this)
        t = d3.transform(d3.select('#' + document.getElementById(this.id).parentElement.id).attr("transform")),
            x = t.translate[0]
        y = t.translate[1]
        d3.select(selectedg).attr("transform", "translate(" + (x + coor[0] + diffx) + "," + (y + coor[1] + diffy) + ")")
        moveLine(selectedg.id, x, y, coor)
    }
}

function dragended(d) {
    d3.select(selectedg).classed("dragging", false);
}

function moveLine(gId, x, y, coor) {
    allLines = d3.selectAll("line")[0]
    for (var line = 0; line < allLines.length; line++) {
        from = allLines[line].getAttribute("g_from")
        to = allLines[line].getAttribute("g_to")

        if (from == gId) {
            lineid = allLines[line].id
            IOid = lineid.split("_")[0]
            IOx = d3.select("#" + IOid)[0][0].cx.baseVal.value
            IOy = d3.select("#" + IOid)[0][0].cy.baseVal.value
            d3.select("#" + lineid).attr("x1", coor[0] + diffx + IOx + x).attr("y1", coor[1] + diffy + IOy + y)
            moveDelCircle(lineid)
        } else if (to == gId) {
            lineid = allLines[line].id
            IOid = lineid.split("_")[1]
            IOx = d3.select("#" + IOid)[0][0].cx.baseVal.value
            IOy = d3.select("#" + IOid)[0][0].cy.baseVal.value
            d3.select("#" + lineid).attr("x2", coor[0] + diffx + IOx + x).attr("y2", coor[1] + diffy + IOy + y)
            moveDelCircle(lineid)
        }
    }
}

function moveDelCircle(lineid) {
    x1 = d3.select("#" + lineid)[0][0].x1.baseVal.value
    x2 = d3.select("#" + lineid)[0][0].x2.baseVal.value
    y1 = d3.select("#" + lineid)[0][0].y1.baseVal.value
    y2 = d3.select("#" + lineid)[0][0].y2.baseVal.value
    d3.select("#c--" + lineid).attr("cx", (x1 + x2) / 2).attr("cy", (y1 + y2) / 2)
    d3.select("#c--" + lineid).attr("transform", "translate(" + ((x1 + x2) / 2) + "," + ((y1 + y2) / 2) + ")")
}

function scMouseOver() {
    parent = document.getElementById(this.id).parentElement.id;
    //deactive for pipeline modules
    if (parent.match(/g(.*)-.*/)[1] === "") {
        if (this.id.split("-")[0] === "text") { //text üzerine gelince
            cid = "sc-" + this.id.split("-")[1]
        } else {
            cid = this.id
        }
        d3.select("#" + cid).attr("fill", "gray")
        if (!binding) {
            $("#container").find("line").attr("status", "hide")
            d3.selectAll("line[g_from =" + parent + "]").attr("status", "standard")
            d3.selectAll("line[g_to =" + parent + "]").attr("status", "standard")
        }
        showEdges()
    }
}

function scMouseOut() {
    parent = document.getElementById(this.id).parentElement.id;
    //deactive for pipeline modules
    if (parent.match(/g(.*)-.*/)[1] === "") {
        if (this.id.split("-")[0] === "text") {
            cid = "sc-" + this.id.split("-")[1]
        } else {
            cid = this.id
        }
        d3.select("#" + cid).attr("fill", "#BEBEBE")
        if (!binding) {
            d3.selectAll("line").attr("status", "standard")
        }
        showEdges()
    }
}


function removeDelCircle(lineid) {
    d3.select("#c--" + lineid).remove()
}
var tooltip = d3.select("body")
.append("div").attr("class", "tooltip-svg")
.style("position", "absolute")
.style("max-width", "400px")
.style("max-height", "100px")
.style("opacity", .9)
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
            d3.select("#" + this.id).attr("status", "posCandidate")
            showOptions()
        }
    } else {
        className = document.getElementById(this.id).className.baseVal.split(" ")
        cand = searchedType(className[1])
        candParam = searchedTypeParam(className[1]);
        parentg = d3.select("#" + this.id).attr("parentG")
        givenNamePP = document.getElementById(this.id).getAttribute("name")
        // for pipeline modules:
        var ccID = $("#" + this.id).attr("ccID");
        var processTag = "";
        if (ccID) {
            var parentID = $("#" + ccID).parent().attr("id"); //g73-4
            var textID = parentID.replace("g", "text"); //text73-4
            var processName = $("#" + textID).attr("name");
            processTag = 'Process: <em>' + processName + '</em><br/>';
        }

        $('#mainG' + MainGNum).find("circle[type ='I/O']").attr("status", "noncandidate")
        if (className[0] === "connect_to_input") {
            //before first connection of inputparam
            conToInput()
            tooltip.html('Connect to input')
        } else if (className[0] === "connect_to_output") {
            //before first connection of outputparam
            conToOutput()
            tooltip.html('Connect to output')
        } else if (givenNamePP === 'inputparam') {
            //after first connection of inputparam
            d3.selectAll("." + className[0]).filter("." + cand).attr("status", "candidate")
            var paraID = document.getElementById(this.id).id.split("-")[3]
            var paraData = parametersData.filter(function (el) {
                return el.id == paraID
            })
            var paraFileType = paraData[0].file_type
            tooltip.html('Input parameter<br/>File Type: <em>' + paraFileType + '</em>')
        } else if (givenNamePP === 'outputparam') {
            //after first connection of outputparam
            //Since outputparam is connected, it is not allowed to connect more parameters
            //              d3.selectAll("." + className[0]).filter("." + cand).attr("status", "candidate")
            var paraID = document.getElementById(this.id).id.split("-")[3]
            var paraData = parametersData.filter(function (el) {
                return el.id == paraID
            })
            var paraFileType = paraData[0].file_type
            tooltip.html('Output parameter<br/>File Type: <em>' + paraFileType + '</em>')
        } else {
            //for process nodes:
            $('#mainG' + MainGNum).find("." + className[0]).filter("." + cand).attr("status", "candidate")
            $('#mainG' + MainGNum).find("." + candParam).attr("status", "candidate")

            var givenNamePP = document.getElementById(this.id).getAttribute("name")
            var paraID = document.getElementById(this.id).id.split("-")[3]
            var paraData = parametersData.filter(function (el) {
                return el.id == paraID
            })
            var paraFileType = paraData[0].file_type
            var paraQualifier = paraData[0].qualifier
            var paraName = paraData[0].name
            if (paraQualifier !== 'val') {
                tooltip.html(processTag + 'Identifier: <em>' + paraName + '</em><br/>Name: <em>' + givenNamePP + '</em><br/>File Type: <em>' + paraFileType + '</em><br/>Qualifier: <em>' + paraQualifier + '</em>')
            } else {
                tooltip.html(processTag + 'Identifier: <em>' + paraName + '</em><br/>Name: <em>' + givenNamePP + '</em><br/>Qualifier: <em>' + paraQualifier + '</em>')
            }
        }
        $('#mainG' + MainGNum).find("circle[parentG =" + parentg + "]").attr("status", "noncandidate")
        $('#mainG' + MainGNum).find("#" + this.id).attr("status", "mouseon")
        tooltip.style("visibility", "visible");

        $('#mainG' + MainGNum).find("line").attr("status", "hide");
        $('#mainG' + MainGNum).find("line[IO_from =" + this.id + "]").attr("status", "standard")
        $('#mainG' + MainGNum).find("line[IO_to =" + this.id + "]").attr("status", "standard")

        showOptions()
        showEdges()
    }
}

function IOmouseMove() {
    tooltip.style("top", (event.pageY - 10) + "px").style("left", (event.pageX + 10) + "px");
}

function IOmouseOut() {
    if (binding) {
        if (d3.select("#" + this.id).attr("status") == "posCandidate") {
            d3.select("#" + this.id).attr("status", "candidate")
            showOptions()
        }

    } else {
        d3.selectAll("circle[type ='I/O']").attr("status", "standard")
        d3.selectAll("line").attr("status", "standard")
        showOptions()
        showEdges()
    }
    tooltip.style("visibility", "hidden");

}


function conToInput() {
    d3.selectAll("circle").filter("." + cand).attr("status", "candidate") //select all available inputs for inputparam circles
}

function conToOutput() {
    d3.selectAll("circle").filter("." + cand).attr("status", "candidate") //select all available outputs for outputparam circles
}

function startBinding(className, cand, candParam, selectedIO) {
    parentg = d3.select("#" + selectedIO).attr("parentG")
    $("#container").find("circle[type ='I/O']").attr("status", "noncandidate")
    if (className[0] === "connect_to_input") {
        conToInput()
    } else if (className[0] === "connect_to_output") {
        conToOutput()
    } else {
        $("#container").find("." + className[0]).filter("." + cand).attr("status", "candidate")
        $("#container").find("." + candParam).attr("status", "candidate")
    }

    $("#container").find("circle[parentG =" + parentg + "]").attr("status", "noncandidate")
    d3.selectAll("#" + selectedIO).attr("status", "selected")
    $("#container").find("line").attr("status", "hide")
    d3.select("#del-" + selectedIO.split("-")[4]).style("opacity", 0.2)

    for (var edge = 0; edge < edges.length; edge++) {
        if (edges[edge].indexOf(selectedIO) > -1) {
            d3.select("#" + findEdges(edges[edge], selectedIO)).attr("status", "noncandidate")
        }
    }
    addCandidates2Dict()
    binding = true
    showOptions()
    showEdges()
}

//second click selectedIO
function stopBinding(className, cand, candParam, selectedIO) {
    firstid = d3.select("circle[status ='selected']")[0][0].id
    d3.selectAll("line").attr("status", "standard")
    if (selectedIO === firstid) {
        firstid = d3.select("#" + firstid).attr("status", "mouseon")
        d3.selectAll("." + className[0]).filter("." + cand).attr("status", "candidate")
        d3.selectAll("." + candParam).attr("status", "candidate")
        d3.select("#del-" + selectedIO.split("-")[4]).style("opacity", 1)
    } else {
        secondid = d3.select("circle[status ='posCandidate']")[0][0].id
        createEdges(firstid, secondid, window)

        d3.selectAll("circle[type ='I/O']").attr("status", "standard")
        d3.select("#del-" + secondid.split("-")[4]).style("opacity", 1)
    }
    binding = false
    showOptions()
    showEdges()
}

function showOptions() {
    d3.selectAll("circle[status ='standard']").attr("r", ior).style("stroke", "").style("stroke-width", "").style("stroke-opacity", "")
    d3.selectAll("circle[status ='mouseon']").attr("r", ior * 1.4).style("stroke", "#ff9999").style("stroke-width", 4).style("stroke-opacity", .5)
    d3.selectAll("circle[status ='selected']").attr("r", ior * 1.4).style("stroke", "#ff0000").style("stroke-width", 4).style("stroke-opacity", .5)
    d3.selectAll("circle[status ='noncandidate']").attr("r", ior * 0.5).style("stroke", "")
    d3.selectAll("circle[status ='candidate']").attr("r", ior * 1.4).style("stroke", "#ccff66").style("stroke-width", 4).style("stroke-opacity", .5)
    d3.selectAll("circle[status ='posCandidate']").attr("r", ior * 1.4).style("stroke", "#ff9999").style("stroke-width", 4).style("stroke-opacity", .5)
}
var link = d3.svg.diagonal()
.projection(function (d) {
    return [d.y, d.x];
});

function showEdges() {
    d3.selectAll("line[status = 'standard']").style("stroke", "#B0B0B0").style("stroke-width", 4).attr("opacity", 1);
    d3.selectAll("line[status = 'hide']").style("stroke-width", 2).attr("opacity", 0.3)
}

function searchedType(type) {
    if (type == "input") {
        return "output"
    } else {
        return "input"
    }
}

function searchedTypeParam(type) {
    if (type == "input") {
        return "connect_to_input"
    } else {
        return "connect_to_output"
    }
}

function findEdges(edge, selectedIO) {
    edgeNodes = edge.split("_")
    if (edgeNodes[0] == selectedIO) {
        return edgeNodes[1]
    } else {
        return edgeNodes[0]
    }
}

function addCandidates2Dict() {
    candidates = []
    candList = d3.selectAll(("circle[status ='candidate']"))[0]
    sel = d3.selectAll(("circle[status ='selected']"))[0][0]
    candList.push(sel)

    for (var c = 0; c < candList.length; c++) {
        currid = candList[c].id
        gid = document.getElementById(currid).parentElement.id;

        t = d3.transform(d3.select('#' + gid).attr("transform")),
            x = t.translate[0]
        y = t.translate[1]

        circx = candList[c].cx.baseVal.value + x
        circy = candList[c].cy.baseVal.value + y

        posList = [circx, circy, gid]

        candidates[currid] = posList
    }
}

function replaceNextVar(outName, inputName) {
    //search inputName as name attribute of svg elements of
    var connectedNodeId = $("circle.input[name*='" + inputName + "']").attr('id');
    //find the connected node to get gNum
    if (connectedNodeId !== "") {
        for (var e = 0; e < edges.length; e++) {
            if (edges[e].indexOf(connectedNodeId) !== -1) { //if not exist: -1
                var nodes = edges[e].split("_")
                var fNode = nodes[0]
                var gNumInputParam = fNode.split("-")[4]
                //get the given name from outputs table
                if (gNumInputParam !== '') {
                    var givenNameInParam = $('#input-PName-' + gNumInputParam).text();
                    var pattern = /(.*)\$\{(.*)\}(.*)/;
                    if (givenNameInParam !== '') {
                        outName = outName.replace(pattern, '$1' + givenNameInParam + '$3');
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
        var candi = "output"
        } else {
            var candi = "input"
            }
    secClassName = document.getElementById(second).className.baseVal.split("-")[0].split(" ")[0] + " " + candi
    return secClassName
}

function getSelectFileButton(paraQualifier, dropDownQual, dropDownMenu, defValButton) {
    var buttons = ""
    if (!dropDownQual) {
        if (paraQualifier === 'file') {
            var buttons = getButtonsModal('inputFile', 'Enter File') + defValButton;
        } else if (paraQualifier === 'val') {
            var buttons = getButtonsModal('inputVal', 'Enter Value') + defValButton;
        } else {
            var buttons = getButtonsModal('inputFile', 'Enter File') + defValButton;
        }
    } else {
        var buttons = dropDownMenu + defValButton;
    }
    return buttons
}


//insert inRow to inputs table. insertType:{paramsInputs,mainInputs} 
function insertInRow(inRow, paramGivenName, rowType, insertType) {
    var checkTable = $('#inputsTable > tbody').find('td[given_name]').filter(function () {
        return $(this).attr('given_name') === paramGivenName
    });
    if (!checkTable.length) {
        if (systemInputs.indexOf(paramGivenName) > -1) { // fill as system input
            $('#' + rowType + 'sTable > tbody:last-child').append(inRow);
            if ($("#systemInputs").css("display") === "none") {
                $("#systemInputs").css("display", "table-row")
            }
        } else { // fill as user input
            if (insertType === "paramsInputs") {
                $('#' + rowType + 'sTable > tbody > tr[id=systemInputs]').before(inRow); //fill from bottom 
            } else {
                $('#' + rowType + 'sTable > tbody > tr[id=userInputs]').after(inRow); //fill from top
            }
            if ($("#userInputs").css("display") === "none") {
                $("#userInputs").css("display", "table-row")
            }
        }
    }
}

//*** if variable start with "params." then  insertInputRowParams function is used to insert rows into inputs table.
//*** if input circle is defined in workflow then insertInputOutputRow function is used to insert row into inputs table based on edges of input parameters.
function insertInputOutputRow(rowType, MainGNum, firGnum, secGnum, pObj, prefix, second) {
    var paramGivenName = document.getElementById('text' + MainGNum + "-" + firGnum).getAttribute("name");
    var paraData = parametersData.filter(function (el) { return el.id == pObj.secPI });
    var paraFileType = "";
    var paraQualifier = "";
    var paraIdentifier = "";
    var show_setting = "";
    var dropDownQual = false;
    var paramDefVal = $('#text-' + firGnum).attr("defVal");
    var paramDropDown = $('#text-' + firGnum).attr("dropDown");
    var paramShowSett = $('#text-' + firGnum).attr("showSett");
    var processName = $('#text-' + secGnum).attr('name');
    if (paramShowSett != undefined){
        if (paramShowSett === ""){
            //check ccID for nested pipelines
            var ccID = $("#"+second).attr("ccID")
            if (ccID){
                var parentG = $("#"+ccID).attr("parentG")
                var textID = parentG.replace("g", "text"); //text73-4
                var childProcessName = $("#" + textID).attr("name");
                if (childProcessName){
                    processName = processName + "_" + childProcessName;
                }
            }
            show_setting = processName;
        } else {
            show_setting = paramShowSett;
        }
        show_setting = show_setting.split(',');
        if (show_setting.length) {
            for (var k = 0; k < show_setting.length; k++) {
                show_setting[k] = $.trim(show_setting[k]);
                show_setting[k] = show_setting[k].replace(/\"/g, '');
                show_setting[k] = show_setting[k].replace(/\'/g, '');
            }
        }
        if (Array.isArray(show_setting)){
            show_setting = show_setting.join(",");
        }
    }
    if (paraData && paraData != '') {
        var paraFileType = paraData[0].file_type;
        var paraQualifier = paraData[0].qualifier;
        var paraIdentifier = paraData[0].name;
    }
    // "Use default" button is added if defVal attr is defined.
    if (paramDefVal) {
        var defValButton = getButtonsDef('defVal', 'Use Default', paramDefVal);
    } else {
        var defValButton = "";
    }
    // dropdown is added if dropdown attr is defined.
    if (paramDropDown && paramDropDown != "") {
        var paramDropDownArray = paramDropDown.split(",");
        if (paramDropDownArray) {
            var dropDownMenu = getDropdownDef('indropdown'+firGnum, 'indropdown', paramDropDownArray, "Choose Value");
            //select defVal
            dropDownQual = true;
        }
    } else {
        var dropDownMenu = "";
    }
    var rowExist = ''
    rowExist = document.getElementById(rowType + 'Ta-' + firGnum);
    if (rowExist) {
        var preProcess = '';
        $('#' + rowType + 'Ta-' + firGnum + '> :nth-child(5)').append('<span id=proGcomma-' + secGnum + '>, </span>');
        $('#' + rowType + 'Ta-' + firGnum + '> :nth-child(5)').append('<span id=proGName-' + secGnum + '>' + processName + '</span>');
    } else {
        //fill inputsTable
        if (rowType === 'input') {
            var selectFileButton = getSelectFileButton(paraQualifier, dropDownQual, dropDownMenu, defValButton)
            //insert both system and user inputs
            var inRow = getRowTable(rowType, firGnum, secGnum, paramGivenName, paraIdentifier, paraFileType, paraQualifier, processName, selectFileButton, show_setting);
            insertInRow(inRow, paramGivenName, rowType, "mainInputs")
            //get project_pipeline_inputs:
            var getProPipeInputs = projectPipeInputs.filter(function (el) { return el.given_name == paramGivenName })
            var rowID = rowType + 'Ta-' + firGnum;
            if (getProPipeInputs && getProPipeInputs != "") {
                if (getProPipeInputs.length > 0) {
                    var filePath = getProPipeInputs[0].name; //value for val type
                    var proPipeInputID = getProPipeInputs[0].id;
                    var given_name = getProPipeInputs[0].given_name;
                    var collection_id = getProPipeInputs[0].collection_id;
                    var collection_name = getProPipeInputs[0].collection_name;
                    var collection = { collection_id: collection_id, collection_name: collection_name }
                    var url = getProPipeInputs[0].url;
                    var urlzip = getProPipeInputs[0].urlzip;
                    var checkPath = getProPipeInputs[0].checkpath;
                    insertSelectInput(rowID, firGnum, filePath, proPipeInputID, paraQualifier, collection, url, urlzip, checkPath); 
                }
                if (getProPipeInputs.length > 1) {
                    for (var k = 1; k < getProPipeInputs.length; k++) {
                        var removeInput = getValues({ "p": "removeProjectPipelineInput", id: getProPipeInputs[k].id });
                    }
                }
            }
            //check if run saved before
            if (pipeData[0].date_created == pipeData[0].date_modified) {
                //after filling, if "use default" button is exist, then click default option.
                clickUseDefault(rowID, paramDefVal);
            }
        }
        //outputsTable
        else if (rowType === 'output') {
            var outName = document.getElementById(prefix + second).getAttribute("name");
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
            outName = outName.replace(/\"/g, '');
            outName = outName.replace(/\'/g, '');
            outName = outName.replace(/\?/g, '')
            outName = outName.replace(/\${(.*)}/g, '*');
            outName = paramGivenName + "/" + outName;
            var outNameEl = '<span fName="' + outName + '">NA' + '</span>';
            var inRow = getRowTable(rowType, firGnum, secGnum, paramGivenName, paraIdentifier, paraFileType, paraQualifier, processName, outNameEl, show_setting);
            $('#' + rowType + 'sTable > tbody:last-child').append(inRow);
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
    d3.selectAll("#" + prefix + first).attr("connect", 'mate')
    d3.selectAll("#" + prefix + second).attr("connect", 'mate')
    pObj.inputParamLocF = first.indexOf("o-inPro") //-1: inputparam not exist //0: first click is done on the inputparam
    pObj.inputParamLocS = second.indexOf("o-inPro")
    pObj.outputParamLocF = first.indexOf("i-outPro") //-1: outputparam not exist //0: first click is done on the inputparam
    pObj.outputParamLocS = second.indexOf("i-outPro")


    if (pObj.inputParamLocS === 0 || pObj.outputParamLocS === 0) { //second click is done on the circle of inputparam//outputparam
        //swap elements and treat as fırst click was done on
        pObj.tem = second
        second = first
        first = pObj.tem
        pObj.inputParamLocF = 0
        pObj.outputParamLocF = 0
    }
    //first click is done on the circle of inputparam
    if (pObj.inputParamLocF === 0 || pObj.outputParamLocF === 0) {
        //update the class of inputparam based on selected second circle
        pObj.secClassName = updateSecClassName(prefix + second, pObj.inputParamLocF)
        d3.selectAll("#" + prefix + first).attr("class", pObj.secClassName)
        //update the parameter of the inputparam based on selected second circle
        var firGnum = document.getElementById(prefix + first).id.split("-")[4] //first g-number
        var secGnum = document.getElementById(prefix + second).id.split("-")[4] //first g-number
        pObj.secPI = document.getElementById(prefix + second).id.split("-")[3] //second parameter id
        var secProI = document.getElementById(prefix + second).id.split("-")[1] //second process id
        pObj.patt = /(.*)-(.*)-(.*)-(.*)-(.*)/
        pObj.secID = first.replace(pObj.patt, '$1-$2-$3-' + pObj.secPI + '-$5')

        d3.selectAll("#" + prefix + first).attr("id", prefix + pObj.secID)
        pObj.fClickOrigin = first
        pObj.fClick = pObj.secID
        pObj.sClick = second
        var rowType = '';
        //Pipeline details table 
        if (pObj.inputParamLocF === 0) {
            rowType = 'input';
        } else if (pObj.outputParamLocF === 0) {
            rowType = 'output';
        }
        if (pObj == window) {
            insertInputOutputRow(rowType, MainGNum, firGnum, secGnum, pObj, prefix, second);
        }

    } else { //process to process connection
        pObj.fClickOrigin = first
        pObj.fClick = first
        pObj.sClick = second
    }

    d3.select("#mainG" + MainGNum).append("line")
        .attr("id", prefix + pObj.fClick + "_" + prefix + pObj.sClick)
        .attr("class", "line")
        .attr("type", "standard")
        .style("stroke", "#B0B0B0").style("stroke-width", 4)
        .attr("x1", pObj.candidates[prefix + pObj.fClickOrigin][0])
        .attr("y1", pObj.candidates[prefix + pObj.fClickOrigin][1])
        .attr("x2", pObj.candidates[prefix + pObj.sClick][0])
        .attr("y2", pObj.candidates[prefix + pObj.sClick][1])
        .attr("g_from", pObj.candidates[prefix + pObj.fClickOrigin][2])
        .attr("g_to", pObj.candidates[prefix + pObj.sClick][2])
        .attr("IO_from", prefix + pObj.fClick)
        .attr("IO_to", prefix + pObj.sClick)
        .attr("stroke-width", 2)
        .attr("stroke", "black")

    pObj.edges.push(prefix + pObj.fClick + "_" + prefix + pObj.sClick)

}



function delMouseOver() {
    d3.select("#del" + this.id).attr('fill-opacity', 0.8)
    d3.select("#del--" + this.id.split("--")[1]).style("opacity", 0.8)
}

function delMouseOut() {
    d3.select("#del" + this.id).attr('fill-opacity', 0.4)
    d3.select("#del--" + this.id.split("--")[1]).style("opacity", 0.4)
}

function cancel() {
    if (binding) {
        d3.selectAll("circle[type ='I/O']").attr("status", "standard")
        binding = false
        showOptions()
    }
}

function download(text) {
    var filename = $('#run-title').val() + '.nf';
    downloadText(text, filename)
}

function createProcessPanelAutoFill(id, pObj, name, process_id) {
    if (pObj !== window) {
        name = pObj.lastPipeName + "_" + name;
    }
    var processData = JSON.parse(window.pipeObj["process_" + process_id]);
    if (processData) {
        var allProScript = "";
        if (processData[0].script_header !== "" && processData[0].script_header !== null) {
            allProScript = decodeHtml(processData[0].script_header);
        }
        if (processData[0].script !== "" && processData[0].script !== null){
            var script = decodeHtml(processData[0].script);
            allProScript = allProScript +"\n"+script;
        }
        if (allProScript){
            insertProPipePanel(allProScript, pObj.gNum, name, pObj, processData);
            //generate json for autofill by using script of process header
            var pro_autoFillJSON = parseAutofill(allProScript);
            // bind event handlers for autofill
            setTimeout(function () {
                if (pro_autoFillJSON !== null && pro_autoFillJSON !== undefined) {
                    $.each(pro_autoFillJSON, function (el) {
                        var stateObj = pro_autoFillJSON[el].statement;
                        $.each(stateObj, function (old_key) {
                            var new_key = old_key + "@" + id;
                            //add process id to each statement after @ sign (eg.$CPU@52) -> will effect only process specific execution parameters.
                            if (old_key !== new_key) {
                                Object.defineProperty(stateObj, new_key,
                                                      Object.getOwnPropertyDescriptor(stateObj, old_key));
                                delete stateObj[old_key];
                            }
                        });
                    });
                    bindEveHandler(pro_autoFillJSON);
                }
            }, 1000);
        }
    }
}

function loadPipeline(sDataX, sDataY, sDatapId, sDataName, processModules, gN, pObj) {
    var prefix = "";
    var MainGNum = "";
    if (pObj != window) {
        //load workflow of pipeline modules 
        MainGNum = pObj.MainGNum;
        prefix = "p" + MainGNum;
    }
    pObj.t = d3.transform(d3.select('#' + "mainG" + MainGNum).attr("transform")),
        pObj.x = pObj.t.translate[0]
    pObj.y = pObj.t.translate[1]
    pObj.z = pObj.t.scale[0]

    pObj.gNum = parseInt(gN)
    var name = sDataName
    var id = sDatapId
    var process_id = id
    var defVal = null;
    var dropDown = null;
    var pubWeb = null;
    var showSett = null;
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
        if (processModules.pubWeb) {
            pubWeb = processModules.pubWeb;
        }
    }

    //for input parameters
    if (id === "inPro") {
        ipR = 70 / 2
        ipIor = ipR / 3
        var kind = "input"

        //(A)if edges are not formed parameter_id data comes from default: process_parameter table "name" column
        var paramId = "inPara" //default
        var classtoparam = "connect_to_input output"
        var pName = "inputparam"
        var init = "o"
        var pColor = "orange"
        //(B)if edges are formed parameter_id data comes from biocorepipesave table "edges" column
        pObj.edgeIn = pObj.sData[0].edges
        pObj.edgeInP = JSON.parse(pObj.edgeIn.replace(/'/gi, "\""))["edges"] //i-10-0-9-1_o-inPro-1-9-0

        for (var ee = 0; ee < pObj.edgeInP.length; ee++) {
            pObj.patt = /(.*)-(.*)-(.*)-(.*)-(.*)_(.*)-(.*)-(.*)-(.*)-(.*)/
            pObj.edgeFirstPId = pObj.edgeInP[ee].replace(pObj.patt, '$2')
            pObj.edgeFirstGnum = pObj.edgeInP[ee].replace(pObj.patt, '$5')
            pObj.edgeSecondParID = pObj.edgeInP[ee].replace(pObj.patt, '$9')

            if (pObj.edgeFirstGnum === String(pObj.gNum) && pObj.edgeFirstPId === "inPro") {
                paramId = pObj.edgeSecondParID //if edge is found
                classtoparam = findType(paramId) + " output"
                pName = parametersData.filter(function (el) {
                    return el.id == paramId
                })[0].name
                break
            }
        }

        drawParam(name, process_id, id, kind, sDataX, sDataY, paramId, pName, classtoparam, init, pColor, defVal, dropDown, pubWeb, showSett, pObj)
        pObj.processList[("g" + MainGNum + "-" + pObj.gNum)] = name
        pObj.gNum = pObj.gNum + 1


    } else if (id === "outPro") {
        ipR = 70 / 2
        ipIor = ipR / 3
        var kind = "output"

        //(A)if edges are not formed parameter_id data comes from default: process_parameter table "name" column
        var paramId = "outPara" //default
        var classtoparam = "connect_to_output input"
        var pName = "outputparam"
        var init = "i"
        var pColor = "green"
        //(B)if edges are formed parameter_id data comes from biocorepipesave table "edges" column
        pObj.edgeOut = pObj.sData[0].edges
        pObj.edgeOutP = JSON.parse(pObj.edgeOut.replace(/'/gi, "\""))["edges"] //i-10-0-9-1_o-inPro-1-9-0

        for (var ee = 0; ee < pObj.edgeOutP.length; ee++) {
            pObj.patt = /(.*)-(.*)-(.*)-(.*)-(.*)_(.*)-(.*)-(.*)-(.*)-(.*)/
            pObj.edgeFirstPId = pObj.edgeOutP[ee].replace(pObj.patt, '$2')
            pObj.edgeFirstGnum = pObj.edgeOutP[ee].replace(pObj.patt, '$5')
            pObj.edgeSecondParID = pObj.edgeOutP[ee].replace(pObj.patt, '$9')

            if (pObj.edgeFirstGnum === String(pObj.gNum) && pObj.edgeFirstPId === "outPro") {
                paramId = pObj.edgeSecondParID //if edge is found
                classtoparam = findType(paramId) + " input"
                pName = parametersData.filter(function (el) {
                    return el.id == paramId
                })[0].name
                break
            }
        }
        drawParam(name, process_id, id, kind, sDataX, sDataY, paramId, pName, classtoparam, init, pColor, defVal, dropDown, pubWeb, showSett, pObj)
        pObj.processList[("g" + MainGNum + "-" + pObj.gNum)] = name
        pObj.gNum = pObj.gNum + 1

    } else {
        //--Pipeline details table ---
        addProPipeTab(id, pObj.gNum + prefix, name, pObj);
        //--ProcessPanel (where process options defined)
        createProcessPanelAutoFill(id, pObj, name, process_id);
        //create process circle
        pObj.inputs = JSON.parse(window.pipeObj["pro_para_inputs_" + id]);
        pObj.outputs = JSON.parse(window.pipeObj["pro_para_outputs_" + id]);

        //gnum uniqe, id same id (Written in class) in same type process
        pObj.g = d3.select("#mainG" + MainGNum).append("g")
            .attr("id", "g" + MainGNum + "-" + pObj.gNum)
            .attr("class", "g" + MainGNum + "-" + id)
            .attr("transform", "translate(" + (sDataX) + "," + (sDataY) + ")")

        //gnum(written in id): uniqe, id(Written in class): same id in same type process, bc(written in type): same at all bc
        pObj.g.append("circle").attr("id", "bc" + MainGNum + "-" + pObj.gNum)
            .attr("class", "bc" + MainGNum + "-" + id)
            .attr("type", "bc")
            .attr("cx", cx)
            .attr("cy", cy)
            .attr("r", r + ior)
            .attr('fill-opacity', 0.6)
            .attr("fill", "red")
            .transition()
            .delay(500)
            .duration(3000)
            .attr("fill", "#E0E0E0")
        //gnum(written in id): uniqe, id(Written in class): same id in same type process, sc(written in type): same at all bc
        pObj.g.append("circle")
            .datum([{
                cx: 0,
                cy: 0
            }])
            .attr("id", "sc" + MainGNum + "-" + pObj.gNum)
            .attr("class", "sc" + MainGNum + "-" + id)
            .attr("type", "sc")
            .attr("r", r - ior)
            .attr("fill", "#BEBEBE")
            .attr('fill-opacity', 0.6)

        //gnum(written in id): uniqe,
        pObj.g.append("text").attr("id", "text" + MainGNum + "-" + pObj.gNum)
            .datum([{
                cx: 0,
                cy: 0
            }])
            .attr('font-family', "FontAwesome, sans-serif")
            .attr('font-size', '1em')
            .attr('name', name)
            .attr('class', 'process')
            .text(truncateName(name, 'process'))
            .style("text-anchor", "middle")

        // I/O id naming:[0]i = input,o = output -[1]process database ID -[2]The number of I/O of the selected process -[3]Parameter database ID- [4]uniqe number
        for (var k = 0; k < pObj.inputs.length; k++) {
            d3.select("#g" + MainGNum + "-" + pObj.gNum).append("circle")
                .attr("id", prefix + "i-" + (id) + "-" + k + "-" + pObj.inputs[k].parameter_id + "-" + pObj.gNum)
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
                .attr('fill-opacity', 0.8)
                .on("mouseover", IOmouseOver)
                .on("mousemove", IOmouseMove)
                .on("mouseout", IOmouseOut)
            //                .on("mousedown", IOconnect)
        }

        for (var k = 0; k < pObj.outputs.length; k++) {
            d3.select("#g" + MainGNum + "-" + pObj.gNum).append("circle")
                .attr("id", prefix + "o-" + (id) + "-" + k + "-" + pObj.outputs[k].parameter_id + "-" + pObj.gNum)
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
                .attr("r", ior).attr("fill", "steelblue")
                .attr('fill-opacity', 0.8)
                .on("mouseover", IOmouseOver)
                .on("mousemove", IOmouseMove)
                .on("mouseout", IOmouseOut)
            //                .on("mousedown", IOconnect)
        }
        pObj.processList[("g" + MainGNum + "-" + pObj.gNum)] = name
        pObj.processListMain[("g" + MainGNum + "-" + pObj.gNum)] = name
        pObj.gNum = pObj.gNum + 1


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
    pObj.candidates = []
    pObj.candList = $('#mainG' + MainGNum).find("circle[type ='I/O']");
    pObj.sel = d3.select(("#" + prefix + fir))[0][0]
    pObj.candList.push(pObj.sel)
    for (var c = 0; c < pObj.candList.length; c++) {
        if (pObj.candList[c]) {
            pObj.currid = pObj.candList[c].id
            pObj.gid = document.getElementById(pObj.currid).parentElement.id;
            pObj.t = d3.transform(d3.select('#' + pObj.gid).attr("transform")),
                pObj.x = pObj.t.translate[0]
            pObj.y = pObj.t.translate[1]

            pObj.circx = pObj.candList[c].cx.baseVal.value + pObj.x
            pObj.circy = pObj.candList[c].cy.baseVal.value + pObj.y

            pObj.posList = [pObj.circx, pObj.circy, pObj.gid]
            pObj.candidates[pObj.currid] = pObj.posList
        }
    }
}



function loadPipelineDetails(pipeline_id, pipeData) {
    window.pipeObj = {};
    var getPipelineD = [];
    getPipelineD.push({ name: "id", value: pipeline_id });
    getPipelineD.push({ name: "p", value: 'exportPipeline' });

    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: getPipelineD,
        async: true,
        success: function (s) {
            window.pipeObj = s
            window.ajaxData.pipelineData = [window.pipeObj["main_pipeline_" + pipeline_id]];
            var pData = window.ajaxData.pipelineData
            $('#pipeline-title').text(pData[0].name);
            $('#pipeline-title').attr('href', 'index.php?np=1&id=' + pipeline_id);
            $('#pipeline-title2').html('<i class="fa fa-spinner "></i> Go to Pipeline: ' + pData[0].name);
            $('#pipeline-title2').attr('href', 'index.php?np=1&id=' + pipeline_id);
            $('#project-title').attr('href', 'index.php?np=2&id=' + project_id);
            $('#pipelineSum').val(decodeHtml(pData[0].summary));
            var script_pipe_header_config = ""
            if (pData[0].script_pipe_header !== null) {
                script_pipe_header_config += decodeHtml(pData[0].script_pipe_header) + "\n";
            }
            if (pData[0].script_pipe_config !== null) {
                script_pipe_header_config += decodeHtml(pData[0].script_pipe_config);
            }
            if (script_pipe_header_config) {
                pipeGnum = 0;
                //check if params.VARNAME is defined in the autofill section of pipeline header. Then return all VARNAMES to define as system inputs
                //##insertInputRowParams will add inputs rows and fill according to propipeinputs within insertProPipePanel
                var processData = ""
                insertProPipePanel(script_pipe_header_config, "pipe", "Pipeline", window, processData);
                //generate json for autofill by using script of pipeline header
                autoFillJSON = parseAutofill(script_pipe_header_config);
                // get Profile variables -> update library of $HOSTNAME conditions 
                autoFillJSON = addProfileVar(autoFillJSON);
                autoFillJSON = decodeGenericCond(autoFillJSON);

            }
            // first openPipeline  will create tables and forms
            // then loadProjectPipeline will load process options
            var sequentialCmd = function (pipeline_id, callback){
                openPipeline(pipeline_id);
                callback();
            }
            sequentialCmd(pipeline_id,function(){
                //## position where all inputs created and filled
                loadProjectPipeline(pipeData); // will load process options
            })

        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
};

// clean depricated project pipeline inputs(propipeinputs) in case it is not found in the inputs table.
function cleanDepProPipeInputs() {
    project_pipeline_id = $('#pipeline-title').attr('projectpipelineid');
    var getProPipeInputs = getValues({
        p: "getProjectPipelineInputs",
        project_pipeline_id: project_pipeline_id,
    });
    var numInputRows = $('#inputsTable > tbody').find('td[given_name]');
    var givenNameObj = {};
    $.each(numInputRows, function (el) {
        var inGName = $(numInputRows[el]).attr("given_name");
        givenNameObj[inGName] = "";
    });
    $.each(getProPipeInputs, function (el) {
        var givenName = getProPipeInputs[el].given_name;
        //clean inputs whose given_name is not found in numInputRows
        if (givenNameObj[givenName] == undefined) {
            var removeInput = getValues({ "p": "removeProjectPipelineInput", id: getProPipeInputs[el].id });
        }
    });
}


function updateCheckBox(check_id, status) {
    if ((check_id === '#exec_all' || check_id === '#exec_each' || check_id === '#singu_check' || check_id === '#docker_check' || check_id === '#publish_dir_check') && status === "true") {
        if ($(check_id).is(":checked") === false) {
            $(check_id).trigger("click");
            $(check_id).prop('checked', true);
        }
    }
    if (status === "true") {
        $(check_id).prop('checked', true);
    } else if (status === "false") {
        $(check_id).prop('checked', false);
    }
}

function refreshCreatorData(project_pipeline_id) {
    pipeData = getValues({ p: "getProjectPipelines", id: project_pipeline_id });
    if (pipeData && pipeData != "") {
        $('#creatorInfoPip').css('display', "block");
        $('#ownUserNamePip').text(pipeData[0].username);
        $('#datecreatedPip').text(pipeData[0].date_created);
        $('.lasteditedPip').text(pipeData[0].date_modified);
    }
}



function showHideColumnRunSett(colList, type) {
    for (var k = 0; k < colList.length; k++) {
        var processTableCol = colList[k] + 2;
        if (type == "hide") {
            $('#allProcessSettTable').find('th:nth-child(' + colList[k] + ')').hide();
            $('#allProcessSettTable').find('td:nth-child(' + colList[k] + ')').hide();
            var doCall = function (processTableCol) {
                setTimeout(function () {
                    $('#processTable').find('th:nth-child(' + processTableCol + ')').hide();
                    $('#processTable').find('tr >td:nth-child(' + processTableCol + ')').hide();
                }, 100);
            }
            doCall(processTableCol);
        } else {
            $('#allProcessSettTable').find('th:nth-child(' + colList[k] + ')').show();
            $('#allProcessSettTable').find('td:nth-child(' + colList[k] + ')').show();
            var doCall = function (processTableCol) {
                setTimeout(function () {
                    $('#processTable').find('th:nth-child(' + processTableCol + ')').show();
                    $('#processTable').find('tr >td:nth-child(' + processTableCol + ')').show();
                }, 100);
            }
            doCall(processTableCol);
        }
    }
}


function loadProjectPipeline(pipeData) {
    loadRunOptions("change");
    $('#creatorInfoPip').css('display', "block");
    $('#project-title').text(decodeHtml(pipeData[0].project_name));
    $('#run-title').changeVal(decodeHtml(pipeData[0].pp_name));
    $('#runSum').val(decodeHtml(pipeData[0].summary));
    $('#rOut_dir').val(pipeData[0].output_dir);
    $('#publish_dir').val(pipeData[0].publish_dir);
    $('#chooseEnv').val(pipeData[0].profile);
    $('#perms').val(pipeData[0].perms);
    $('#runCmd').val(pipeData[0].cmd);
    $('#docker_img').val(pipeData[0].docker_img);
    $('#docker_opt').val(pipeData[0].docker_opt);
    $('#singu_img').val(pipeData[0].singu_img);
    $('#singu_opt').val(pipeData[0].singu_opt);
    updateCheckBox('#publish_dir_check', pipeData[0].publish_dir_check);
    updateCheckBox('#intermeDel', pipeData[0].interdel);
    updateCheckBox('#exec_each', decodeHtml(pipeData[0].exec_each));
    updateCheckBox('#exec_all', decodeHtml(pipeData[0].exec_all));
    updateCheckBox('#docker_check', pipeData[0].docker_check);
    updateCheckBox('#singu_check', pipeData[0].singu_check);
    updateCheckBox('#singu_save', pipeData[0].singu_save);
    updateCheckBox('#withTrace', pipeData[0].withTrace);
    updateCheckBox('#withReport', pipeData[0].withReport);
    updateCheckBox('#withDag', pipeData[0].withDag);
    updateCheckBox('#withTimeline', pipeData[0].withTimeline);
    checkShub()
    //load process options 
    if (pipeData[0].process_opt) {
        //fill process options table
        loadProcessOpt(decodeHtml(pipeData[0].process_opt)); 
    }
    // bind event handlers for autofill
    setTimeout(function () {
        if (autoFillJSON !== null && autoFillJSON !== undefined) {
            bindEveHandler(autoFillJSON);
        }
    }, 1000);
    //load amazon keys for possible s3 connection
    loadAmzKeys();
    if (pipeData[0].amazon_cre_id !== "0") {
        $('#mRunAmzKey').val(pipeData[0].amazon_cre_id);
    } else {
        selectAmzKey();
    }

    //load user groups
    var allUserGrp = getValues({ p: "getUserGroups" });
    if (allUserGrp && allUserGrp != "") {
        for (var i = 0; i < allUserGrp.length; i++) {
            var param = allUserGrp[i];
            var optionGroup = new Option(param.name, param.id);
            $("#groupSel").append(optionGroup);
        }
    }
    if (pipeData[0].group_id !== "0") {
        $('#groupSel').val(pipeData[0].group_id);
    }

    var chooseEnv = $('#chooseEnv option:selected').val();
    $('#ownUserNamePip').text(pipeData[0].username);
    $('#datecreatedPip').text(pipeData[0].date_created);
    $('.lasteditedPip').text(pipeData[0].date_modified);

    // activate collapse icon for process options
    refreshCollapseIconDiv()
    $('#pipelineSum').attr('disabled', "disabled");
    fillRunVerOpt(["#runVerLog", "#runVerReport"])
    //hide system inputs
    $("#systemInputs").trigger("click");
    //insert icon for process_options according to show_setting attribute
    hideProcessOptionsAsIcons()
    //autofillEmptyInputs
    if (autoFillJSON !== undefined && autoFillJSON !== null) {
        autofillEmptyInputs(autoFillJSON)
    }
    // clean depricated project pipeline inputs(propipeinputs) in case it is not found in the inputs table.
    cleanDepProPipeInputs();
    // fill executor settings:
    if (pipeData[0].profile !== "" && chooseEnv && chooseEnv !== "") {
        var [allProSett, profileData] = getJobData("both");
        var executor_job = profileData[0].executor_job;
        if (executor_job === 'ignite') {
            showHideColumnRunSett([1, 4, 5], "show")
            showHideColumnRunSett([1, 4], "hide")
        } else if (executor_job === 'local') {
            showHideColumnRunSett([1, 4, 5], "hide")
        } else {
            showHideColumnRunSett([1, 4, 5], "show")
        }
        if (executor_job === "slurm"){
            $('#eachProcessQueue').text('Partition');
            $('#allProcessQueue').text('Partition');
        }else {
            $('#eachProcessQueue').text('Queue');
            $('#allProcessQueue').text('Queue');
        }
        $('#jobSettingsDiv').css('display', 'inline');
        //insert exec_all_settings data into allProcessSettTable table
        if (IsJsonString(decodeHtml(pipeData[0].exec_all_settings))) {
            var exec_all_settings = JSON.parse(decodeHtml(pipeData[0].exec_all_settings));
            fillForm('#allProcessSettTable', 'input', exec_all_settings);
        }
        //insert exec_each_settings data into #processtable
        if (IsJsonString(decodeHtml(pipeData[0].exec_each_settings))) {
            var exec_each_settings = JSON.parse(decodeHtml(pipeData[0].exec_each_settings));
            $.each(exec_each_settings, function (el) {
                var each_settings = exec_each_settings[el];
                //wait for the table to load
                fillForm('#' + el, 'input', each_settings); 
            });
        }
    } else {
        $('#jobSettingsDiv').css('display', 'none');
    }
    setTimeout(function () { checkReadytoRun(); }, 1000);

}


//click on "system inputs" button
$('#inputsTable').on('click', '#systemInputs', function (e) {
    var indx = $("#systemInputs").index();
    $("#inputsTable> tbody > tr:gt(" + indx + ")").toggle();
});

function refreshEnv() {
    loadRunOptions("change");
}

//type="change","silent"
function loadRunOptions(type) {
    var selectedOpt = $('#chooseEnv').find(":selected").val();
    $('#chooseEnv').find('option').not(':disabled').remove();
    //get profiles for user
    var proCluData = getValues({ p: "getProfileCluster" });
    var proAmzData = getValues({ p: "getProfileAmazon" });
    if (proCluData && proAmzData) {
        if (proCluData.length + proAmzData.length !== 0) {
            $.each(proCluData, function (el) {
                var option = new Option(proCluData[el].name + ' (Remote machine: ' + proCluData[el].username + '@' + proCluData[el].hostname + ')', 'cluster-' + proCluData[el].id)
                option.setAttribute("host", proCluData[el].hostname);
                $("#chooseEnv").append(option);
            });
            $.each(proAmzData, function (el) {
                var option = new Option(proAmzData[el].name + ' (Amazon: Status:' + proAmzData[el].status + ' Image id:' + proAmzData[el].image_id + ' Instance type:' + proAmzData[el].instance_type + ')', 'amazon-' + proAmzData[el].id)
                option.setAttribute("host", proAmzData[el].shared_storage_id);
                option.setAttribute("status", proAmzData[el].status);
                option.setAttribute("amz_key", proAmzData[el].amazon_cre_id);
                $("#chooseEnv").append(option);
            });
        }
    }
    if (selectedOpt) {
        if (selectedOpt != "") {
            $('#chooseEnv').val(selectedOpt);
            if (type == "silent"){
                checkReadytoRun();
            } else {
                $('#chooseEnv').trigger("change");
            }
        }
    }
}
//insert selected input to inputs table
function insertSelectInput(rowID, gNumParam, filePath, proPipeInputID, qualifier, collection, url, urlzip, checkPath) {
    var checkDropDown = $('#' + rowID).find('select[indropdown]')[0];
    if (checkDropDown) {
        $(checkDropDown).val(filePath)
        $('#' + rowID).attr('propipeinputid', proPipeInputID);
        $('#' + rowID).find('#defValUse').css('display', 'none');
    } else {
        if (qualifier === 'file' || qualifier === 'set') {
            var editIcon = getIconButtonModal('inputFile', 'Edit', 'fa fa-pencil');
            var deleteIcon = getIconButton('inputDel', 'Delete', 'fa fa-trash-o');
            $('#' + rowID).find('#inputFileEnter').css('display', 'none');
            $('#' + rowID).find('#defValUse').css('display', 'none');
        } else {
            var editIcon = getIconButtonModal('inputVal', 'Edit', 'fa fa-pencil');
            var deleteIcon = getIconButton('inputVal', 'Delete', 'fa fa-trash-o');
            $('#' + rowID).find('#inputValEnter').css('display', 'none');
            $('#' + rowID).find('#defValUse').css('display', 'none');
        }

        var showUrlIcon = "display:none;"
        var urlData = url || "";
        var urlzipData = urlzip || "";
        var checkPathData = checkPath || "";
        if (url || urlzip){
            showUrlIcon = "";
        }
        var urlIcon = '<button type="button" class="btn"  url="'+urlData+'" urlzip="'+urlzipData+'" checkpath="'+checkPathData+'" style="'+showUrlIcon+' padding:0px; margin-right:2px;" id="urlBut-'+rowID+'" ><a data-toggle="tooltip" data-placement="bottom" data-original-title="Download Info"><span><i style="font-size: 16px;" class="fa fa-cloud-download"></i></span></a></button>'

        filePath = escapeHtml(filePath);
        var collectionAttr = ' collection_id="" ';
        if (collection) {
            if (collection.collection_id && collection.collection_name) {
                collectionAttr = ' collection_id="' + collection.collection_id + '" ';
                filePath = '<i class="fa fa-database"></i> ' + collection.collection_name
            }
        }
        $('#' + rowID + '> :nth-child(6)').append('<span style="padding-right:7px;" id="filePath-' + gNumParam + '" ' + collectionAttr + '>' + filePath + '</span>' + urlIcon + editIcon + deleteIcon );
        $('#' + rowID).attr('propipeinputid', proPipeInputID);

    }
}
//remove for both dropdown and file/val options
function removeSelectFile(rowID, sType) {
    var checkDropDown = $('#' + rowID).find('select[indropdown]')[0];
    if (checkDropDown) {
        $('#' + rowID).find('#defValUse').css('display', 'inline');
        $('#' + rowID).removeAttr('propipeinputid');
    } else {
        if (sType === 'file' || sType === 'set') {
            $('#' + rowID).find('#inputFileEnter').css('display', 'inline');
            $('#' + rowID).find('#defValUse').css('display', 'inline');
        } else if (sType === 'val') {
            $('#' + rowID).find('#inputValEnter').css('display', 'inline');
            $('#' + rowID).find('#defValUse').css('display', 'inline');
        }
        $('#' + rowID + '> :nth-child(6) > span').remove();
        var buttonList = $('#' + rowID + '> :nth-child(6) > button');
        if (buttonList[3]) {
            buttonList[3].remove();
        }
        if (buttonList[2]) {
            buttonList[2].remove();
        }
        if (buttonList[1]) {
            var but1id = $(buttonList[1]).attr("id");
            if (but1id == "inputValEdit" || but1id == "inputFileEdit" || but1id.match(/^urlBut-/)) {
                buttonList[1].remove();
            }
        }
        $('#' + rowID).removeAttr('propipeinputid');
    }
}

function checkInputInsert(data, gNumParam, given_name, qualifier, rowID, sType, inputID, collection, url, urlzip, checkPath) {
    if (inputID === null) { inputID = "" }
    var nameInput = "";
    if (data) {
        nameInput = data[1].value;
    }
    var collection_id = ""
    var collection_name = ""
    if (collection) {
        if (collection.collection_id) {
            collection_id = collection.collection_id;
            collection_name = collection.collection_name;
        }
    }
    var urlData = url || "";
    var urlzipData = urlzip || "";
    var checkPathData = checkPath || "";
    var fillInput = getValues({
        p: "fillInput",
        inputID: inputID,
        collection_id: collection_id,
        inputName: nameInput,
        inputType: sType,
        project_id: project_id,
        "pipeline_id": pipeline_id,
        "project_pipeline_id": project_pipeline_id,
        "g_num": gNumParam,
        "given_name": given_name,
        "qualifier": qualifier,
        proPipeInputID: "",
        url: urlData,
        urlzip: urlzipData,
        checkpath: checkPathData
    });
    //insert into #inputsTab
    if (fillInput.projectPipelineInputID && collection_name) {
        insertSelectInput(rowID, gNumParam, collection_name, fillInput.projectPipelineInputID, sType, collection, url, urlzip, checkPath);
    } else if (fillInput.projectPipelineInputID && fillInput.inputName) {
        insertSelectInput(rowID, gNumParam, fillInput.inputName, fillInput.projectPipelineInputID, sType, collection, url, urlzip, checkPath);
    }
}

function checkInputEdit(data, gNumParam, given_name, qualifier, rowID, sType, proPipeInputID, inputID, collection, url, urlzip, checkPath) {
    if (inputID === null) { inputID = "" }
    var nameInput = "";
    if (data) {
        nameInput = data[1].value;
    }
    var collection_id = ""
    var collection_name = ""
    if (collection) {
        if (collection.collection_id && collection.collection_name) {
            collection_id = collection.collection_id;
            collection_name = collection.collection_name;
        }
    }
    var urlData = url || "";
    var urlzipData = urlzip || "";
    var checkPathData = checkPath || "";
    var fillInput = getValues({
        p: "fillInput",
        inputID: inputID,
        collection_id: collection_id,
        inputName: nameInput,
        inputType: sType,
        project_id: project_id,
        "pipeline_id": pipeline_id,
        "project_pipeline_id": project_pipeline_id,
        "g_num": gNumParam,
        "given_name": given_name,
        "qualifier": qualifier,
        proPipeInputID: proPipeInputID,
        url: urlData,
        urlzip: urlzipData,
        checkpath: checkPathData
    });
    //update #inputsTab
    if (fillInput.projectPipelineInputID && collection_name) {
        $('#filePath-' + gNumParam).html('<i class="fa fa-database"></i> ' + collection_name);
    } else if (fillInput.projectPipelineInputID && fillInput.inputName) {
        $('#filePath-' + gNumParam).text(fillInput.inputName);
    }
    $('#filePath-' + gNumParam).attr("collection_id", collection_id);
    //update urlBut settings inside #inputsTab
    var urlData = url || "";
    var urlzipData = urlzip || "";
    var checkPathData = checkPath || "";
    if (url || urlzip){
        $("#urlBut-" +rowID).css("display","inline-block")
    } else {
        $("#urlBut-" +rowID).css("display","none")
    }
    $("#urlBut-" +rowID).attr("url",urlData)
    $("#urlBut-" +rowID).attr("urlzip",urlzipData)
    $("#urlBut-" +rowID).attr("checkpath",checkPathData)
}

function saveFileSetValModal(data, sType, inputID, collection) {
    if (sType === 'file' || sType === 'set') {
        sType = 'file'; //for simplification 
        var rowID = $('#mIdFile').attr('rowID'); //the id of table-row to be updated #inputTa-3
    } else if (sType === 'val') {
        var rowID = $('#mIdVal').attr('rowID'); //the id of table-row to be updated #inputTa-3
    }
    var gNumParam = rowID.split("Ta-")[1];
    var given_name = $("#input-PName-" + gNumParam).text(); //input-PName-3
    var qualifier = $('#' + rowID + ' > :nth-child(4)').text(); //input-PName-3
    var url=null, urlzip=null, checkPath=null;
    //check database if file is exist, if not exist then insert
    checkInputInsert(data, gNumParam, given_name, qualifier, rowID, sType, inputID, collection, url, urlzip, checkPath);
    checkReadytoRun();
}

function editFileSetValModal(data, sType, inputID, collection) {
    if (sType === 'file' || sType === 'set') {
        sType = 'file';
        var rowID = $('#mIdFile').attr('rowID'); //the id of table-row to be updated #inputTa-3
    } else if (sType === 'val') {
        var rowID = $('#mIdVal').attr('rowID'); //the id of table-row to be updated #inputTa-3
    }
    var proPipeInputID = $('#' + rowID).attr('propipeinputid');
    var gNumParam = rowID.split("Ta-")[1];
    var given_name = $("#input-PName-" + gNumParam).text(); //input-PName-3
    var qualifier = $('#' + rowID + ' > :nth-child(4)').text(); //input-PName-3
    var url=null, urlzip=null, checkPath=null;
    //check database if file is exist, if not exist then insert
    checkInputEdit(data, gNumParam, given_name, qualifier, rowID, sType, proPipeInputID, inputID, collection, url, urlzip, checkPath);
    checkReadytoRun();
}
checkType = "";
//checkType become "rerun" or "resumerun" when rerun or resume button is clicked.
function checkReadytoRun(type) {
    console.log("checkReady")
    if (checkType === "") {
        checkType = type || "";
    }
    runStatus = getRunStatus(project_pipeline_id);
    project_pipeline_id = $('#pipeline-title').attr('projectpipelineid');
    var getProPipeInputs = getValues({
        p: "getProjectPipelineInputs",
        project_pipeline_id: project_pipeline_id,
    });
    var numInputRows = $('#inputsTable > tbody').find('tr[id*=input]').length; //find input rows
    var profileNext = $('#chooseEnv').find(":selected").val();
    var profileNextText = $('#chooseEnv').find(":selected").html();
    if (profileNextText) {
        if (profileNextText.match(/Amazon: Status:/)) {
            var patt = /(.*)Amazon: Status:(.*) Image(.*)/;
            var amzStatus = profileNextText.replace(patt, '$2');
        }
    }
    var output_dir = $.trim($('#rOut_dir').val());

    var publishReady = false;
    var publish_dir_check = $('#publish_dir_check').is(":checked").toString();
    if (publish_dir_check === "true") {
        var publish_dir = $('#publish_dir').val();
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
    var s3check = checkS3(publish_dir, getProPipeInputs);
    var s3value = $('#mRunAmzKey').val();
    if (s3check === true && s3value !== null) {
        var s3status = true;
    } else if (s3check === false) {
        var s3status = true;
    } else {
        var s3status = false;
    }
    //if ready and not running/waits/error
    if (publishReady && s3status && getProPipeInputs.length >= numInputRows && profileNext !== '' && output_dir !== '') {
        console.log("initial runStatus")
        console.log(runStatus)
        if (runStatus == "" || checkType === "rerun" || checkType === "newrun" || checkType === "resumerun") {
            if (amzStatus) {
                if (amzStatus === "running") {
                    console.log(checkType)
                    if (checkType === "rerun" || checkType === "resumerun") {
                        runStatus = "aboutToStart"
                        runProjectPipe(runProPipeCall, checkType);
                    } else if (checkType === "newrun") {
                        displayButton('runProPipe');
                    } else {
                        displayButton('runProPipe');
                    }
                } else {
                    displayButton('statusProPipe');
                }
            } else {
                console.log("checkType")
                console.log(checkType)
                if (checkType === "rerun" || checkType === "resumerun") {
                    runStatus = "aboutToStart"
                    runProjectPipe(runProPipeCall, checkType);
                } else if (checkType === "newrun") {
                    displayButton('runProPipe');
                } else {
                    displayButton('runProPipe');
                }
            }
        }
    } else {
        if (((runStatus !== "NextRun" && runStatus !== "Waiting" && runStatus !== "init") && (checkType === "rerun" || checkType === "newrun")) || runStatus === "") {
            displayButton('statusProPipe');
        }
    }
    //reset of checkType will be conducted in runProjectPipe as well 
    //if checkType rerun || resumerun come to this point, it means run not executed
    if (checkType === "rerun" || checkType === "resumerun") {
        checkType = "newrun";
    }
}

//check if singu image path contains shub:// pattern 
$("#singu_img").keyup(function () {
    autoCheckShub();
});
var timeoutCheckShub = 0;

function autoCheckShub() {
    if (timeoutCheckShub) clearTimeout(timeoutCheck);
    timeoutCheckShub = setTimeout(function () { checkShub() }, 2000);
}

//check if singu image path contains shub:// pattern then show "save over image" checkbox
function checkShub() {
    var singuPath = $("#singu_img").val()
    var shubpattern = 'shub://';
    var pathCheck = false;
    if (singuPath !== '') {
        if (singuPath.indexOf(shubpattern) > -1 || singuPath.indexOf('ftp:') > -1 || singuPath.indexOf('http:') > -1 || singuPath.indexOf('https:') > -1) {
            $("#singu_save_div").css('display', "block");
        } else {
            $("#singu_save_div").css('display', "none");
            $("#singu_save").prop('checked', false);
        }
    } else {
        $("#singu_save_div").css('display', "none");
        $("#singu_save").prop('checked', false);
    }
}


function checkWorkDir(){
    var showInfoM = false
    var infoModalText = "Please check your work directory:"
    var output_dir = $.trim($('#rOut_dir').val());
    if (output_dir){
        // full path should start with slash
        if (output_dir[0] != "/"){
            showInfoM = true
            infoModalText += "</br> * It should start with slash symbol (/)."
        }
        if (output_dir.indexOf(' ') >= 0){
            showInfoM = true
            infoModalText += "</br> * There shouldn't be a space in your path.";
        }
        if (showInfoM){
            showInfoModal("#infoModal", "#infoModalText", infoModalText)
        }    
    }  
}


//Autocheck the output,publish_dir,publish_dir_check for checkreadytorun
$("#rOut_dir").keyup(function () {
    autoCheck();
    autoCheckWorkDir()
});
$("#publish_dir").keyup(function () {
    autoCheck();
});
$("#publish_dir_check").click(function () {
    autoCheck();
});
//file import modal
$("#file_dir").keyup(function () {
    autoCheckS3("#file_dir", "#mRunAmzKeyS3Div");
});
$("#s3_archive_dir_geo").keyup(function () {
    autoCheckS3("#s3_archive_dir_geo", "#mArchAmzKeyS3Div_GEO");
});

$("#s3_archive_dir").keyup(function () {
    autoCheckS3("#s3_archive_dir", "#mArchAmzKeyS3Div");
});

var timeoutCheck = 0;
function autoCheck(type) {
    var autoCheckType = type || "";
    if (timeoutCheck) clearTimeout(timeoutCheck);
    if (autoCheckType == "fillstates") {
        timeoutCheck = setTimeout(function () {
            $("#inputsTab").loading('stop');
            checkReadytoRun();
            if (changeOnchooseEnv != undefined){
                if (changeOnchooseEnv == true){
                    //save run after all parameters loaded on change of chooseEnv
                    saveRun();
                } 
            }

        }, 2000);
    } else {
        timeoutCheck = setTimeout(function () { checkReadytoRun() }, 2000);
    }
}

var timeoutCheckS3 = 0;
function autoCheckS3(inputID,showDivId) {
    if (timeoutCheckS3) {
        clearTimeout(timeoutCheckS3);
    }
    timeoutCheckS3 = setTimeout(function () { checkS3filePath(inputID,showDivId) }, 2000);
}

var timeoutCheckWorkDir = 0;
function autoCheckWorkDir() {
    if (timeoutCheckWorkDir) {
        clearTimeout(timeoutCheckWorkDir);
    }
    timeoutCheckWorkDir = setTimeout(function () { checkWorkDir() }, 3000);
}

//check if file import path contains s3:// pattern and shows aws menu
function checkS3filePath(inputID,showDivId) {
    var file_path = $(inputID).val();
    var s3pattern = 's3:';
    var pathCheck = false;
    if (file_path !== '') {
        if (file_path.indexOf(s3pattern) > -1) {
            $(showDivId).css('display', "block");
            pathCheck = true;
        } 
    } 
    if (pathCheck === false) {
        $(showDivId).css('display', "none");
    } 
}


//check if path contains s3:// pattern and shows aws menu
function checkS3(path, getProPipeInputs) {
    //path part
    var s3pattern = 's3:';
    var pathCheck = false;
    if (path !== '') {
        if (path.indexOf(s3pattern) > -1) {
            $("#mRunAmzKeyDiv").css('display', "inline");
            pathCheck = true;
        } else {
            pathCheck = false;
        }
    } else {
        pathCheck = false;
    }
    //getProPipeInputs part
    var nameCheck = 0;
    $.each(getProPipeInputs, function (el) {
        var inputName = getProPipeInputs[el].name;
        if (inputName) {
            if (inputName.indexOf(s3pattern) > -1) {
                $("#mRunAmzKeyDiv").css('display', "inline");
                nameCheck = nameCheck + 1;
            }
        }
    });
    if (nameCheck === 0 && pathCheck === false) {
        $("#mRunAmzKeyDiv").css('display', "none");
        return false;
    } else {
        return true;
    }
}

function loadAmzKeys() {
    var data = getValues({ p: "getAmz" });
    if (data && data != "") {
        $.each(data, function (i, item) {
            $('#mRunAmzKey').append($('<option>', { value: item.id, text : item.name }));
            $('#mRunAmzKeyS3').append($('<option>', { value: item.id, text : item.name }));
            $('#mArchAmzKeyS3').append($('<option>', { value: item.id, text : item.name }));
            $('#mArchAmzKeyS3_GEO').append($('<option>', { value: item.id, text : item.name }));
        });
    }
}
//autoselect mRunAmzKey based on selected profile
function selectAmzKey() {
    var amzKeyId = $("#chooseEnv").find(":selected").attr('amz_key')
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
}

function configTextAllProcess(exec_all_settings, type, proName, executor_job) {
    if (type === "each") {
        for (var keyParam in exec_all_settings) {
            if (exec_all_settings[keyParam] !== '' && (keyParam === 'time' || keyParam === 'job_time') && executor_job != "ignite" && executor_job != "local") {
                window.configTextRaw += 'process.$' + proName + '.time' + ' = \'' + exec_all_settings[keyParam] + 'm\'\n';
            } else if (exec_all_settings[keyParam] !== '' && (keyParam === 'cpu' || keyParam === 'job_cpu')) {
                window.configTextRaw += 'process.$' + proName + '.cpus' + ' = ' + exec_all_settings[keyParam] + '\n';
            } else if (exec_all_settings[keyParam] !== '' && (keyParam === 'queue' || keyParam === 'job_queue') && executor_job != "ignite" && executor_job != "local") {
                window.configTextRaw += 'process.$' + proName + '.queue' + ' = \'' + exec_all_settings[keyParam] + '\'\n';
            } else if (exec_all_settings[keyParam] !== '' && (keyParam === 'memory' || keyParam === 'job_memory')) {
                window.configTextRaw += 'process.$' + proName + '.memory' + ' = \'' + exec_all_settings[keyParam] + ' GB\'\n';
            } else if (exec_all_settings[keyParam] !== '' && (keyParam === 'opt' || keyParam === 'job_clu_opt') && executor_job != "local") {
                window.configTextRaw += 'process.$' + proName + '.clusterOptions' + ' = \'' + exec_all_settings[keyParam] + '\'\n';
            }
        }

    } else {
        for (var keyParam in exec_all_settings) {
            if (exec_all_settings[keyParam] !== '' && (keyParam === 'time' || keyParam === 'job_time') && executor_job != "ignite" && executor_job != "local") {
                window.configTextRaw += 'process.' + 'time' + ' = \'' + exec_all_settings[keyParam] + 'm\'\n';
            } else if (exec_all_settings[keyParam] !== '' && (keyParam === 'cpu' || keyParam === 'job_cpu')) {
                window.configTextRaw += 'process.' + 'cpus' + ' = ' + exec_all_settings[keyParam] + '\n';
            } else if (exec_all_settings[keyParam] !== '' && (keyParam === 'queue' || keyParam === 'job_queue') && executor_job != "ignite" && executor_job != "local") {
                window.configTextRaw += 'process.' + 'queue' + ' = \'' + exec_all_settings[keyParam] + '\'\n';
            } else if (exec_all_settings[keyParam] !== '' && (keyParam === 'memory' || keyParam === 'job_memory')) {
                window.configTextRaw += 'process.' + 'memory' + ' = \'' + exec_all_settings[keyParam] + ' GB\'\n';
            } else if (exec_all_settings[keyParam] !== '' && (keyParam === 'opt' || keyParam === 'job_clu_opt') && executor_job != "local") {
                window.configTextRaw += 'process.' + 'clusterOptions' + ' = \'' + exec_all_settings[keyParam] + ' \'\n';
            }
        }
    }
}

function displayButton(idButton) {
    var buttonList = ['runProPipe', 'errorProPipe', 'completeProPipe', 'runningProPipe', 'waitingProPipe', 'statusProPipe', 'connectingProPipe', 'terminatedProPipe', "abortedProPipe"];
    for (var i = 0; i < buttonList.length; i++) {
        document.getElementById(buttonList[i]).style.display = "none";
    }
    document.getElementById(idButton).style.display = "inline";
}
//xxxxx
function terminateProjectPipe() {
    var proType = proTypeWindow;
    var proId = proIdWindow;
    var [allProSett, profileData] = getJobData("both");
    var executor = profileData[0].executor;
    if (runPid && executor != "local") {
        var terminateRun = getValues({ p: "terminateRun", project_pipeline_id: project_pipeline_id, profileType: proType, profileId: proId, executor: executor });
        console.log(terminateRun)
        var pidStatus = checkRunPid(runPid, proType, proId);
        if (pidStatus) { // if true, then it is exist in queue
            console.log("pid exist1")
        } else { //pid not exist
            console.log("give error1")
        }
    } else if (executor == "local") {
        var terminateRun = getValues({ p: "terminateRun", project_pipeline_id: project_pipeline_id, profileType: proType, profileId: proId, executor: executor });
        console.log(terminateRun)
    }

    var setStatus = getValues({ p: "updateRunStatus", run_status: "Terminated", project_pipeline_id: project_pipeline_id });
    if (setStatus) {
        displayButton('terminatedProPipe');
        //trigger saving newxtflow log file
        setTimeout(function () {
            clearIntNextLog(proType, proId)
        }, 3000);
        readPubWeb(proType, proId, "no_reload")
    }

}

function parseRunPid(serverLog) {
    runPid = "";
    //for lsf: Job <203477> is submitted to queue <long>.\n"
    //for sge: Your job 2259 ("run_bowtie2") has been submitted
    //for slurm: Submitted batch job 8748700
    if (serverLog.match(/Job <(.*)> is submitted/) || serverLog.match(/job (.*) \(.*\) .* submitted/) || serverLog.match(/Submitted batch job (.*)/)) {
        if (serverLog.match(/Job <(.*)> is submitted/)) {
            var regEx = /Job <(.*)> is submitted/g;
        } else if (serverLog.match(/job (.*) \(.*\) .* submitted/)) {
            var regEx = /job (.*) \(.*\) .* submitted/g;
        } else if (serverLog.match(/Submitted batch job (.*)/)) {
            var regEx = /Submitted batch job (.*)/g;
        }
        var runPidAr = getMultipleRegex(serverLog, regEx);
        if (runPidAr.length) {
            runPid = runPidAr[runPidAr.length - 1];
            runPid = $.trim(runPid);
        }
        if (runPid && runPid != "") {
            var updateRunPidComp = getValues({ p: "updateRunPid", pid: runPid, project_pipeline_id: project_pipeline_id });
        } else {
            runPid = null;
        }
    }else {
        runPid = null;
    }
    return runPid
}

function checkRunPid(runPid, proType, proId) {
    var checkPid = null;
    if (runPid) {
        checkPid = getValues({ p: "checkRunPid", pid: runPid, profileType: proType, profileId: proId, project_pipeline_id: project_pipeline_id });
        if (checkPid == "running") {
            checkPid = true;
        } else if (checkPid == "done") {
            checkPid = false;
        } else {
            checkPid = null;
        }
    }
    return checkPid
}

function parseMountPath(path) {
    if (path != null && path != "") {
        if (path.match(/\//)) {
            var allDir = path.split("/");
            if (allDir.length > 2) {
                return "/" + allDir[1] + "/" + allDir[2]
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
        newExecAll = oldExecOpt.replace(patt, '$1' + newPaths + '$3');
    }
    return newExecAll
}
function removeCollectionFromInputs(col_id){
    //get all input paths
    var inputPaths = $('#inputsTab > table > tbody >tr').find("span[id*='filePath']");
    if (inputPaths && inputPaths != null) {
        $.each(inputPaths, function (el) {
            var collection_id = $(inputPaths[el]).attr("collection_id");
            if (collection_id){
                if (collection_id == col_id){
                    var delButton = $(inputPaths[el]).parent().find("button[id*='inputDelDelete']")
                    $(delButton).trigger("click")
                    $('#mIdFile').val(""); //reset modal for insert new collection
                }
            }
        });
    }
}


//autofill for ghpcc06 cluster to mount all directories before run executed.
function autofillMountPath() {
    var pathArray = [];
    var workDir = $('#rOut_dir').val();
    workDir = parseMountPath(workDir);
    if (workDir) {
        pathArray.push(workDir);
    }
    //get all input paths
    var inputPaths = $('#inputsTab > table > tbody >tr').find("span[id*='filePath']");
    if (inputPaths && inputPaths != null) {
        $.each(inputPaths, function (el) {
            var collection_id = $(inputPaths[el]).attr("collection_id");
            if (collection_id){
                var colFiles = getValues({ "id": collection_id, "p": "getCollectionFiles" })
                for (var i = 0; i < colFiles.length; i++) {
                    if (colFiles[i].file_dir){
                        if (!colFiles[i].file_dir.match(/s3:/)){
                            var inputPath = colFiles[i].file_dir;
                            var parsedPath = parseMountPath(inputPath);
                            if (parsedPath) {
                                if (pathArray.indexOf(parsedPath) === -1) {
                                    pathArray.push(parsedPath)
                                }
                            }
                        }
                    }
                }
            } else {
                var inputPath = $(inputPaths[el]).text();
                var parsedPath = parseMountPath(inputPath);
                if (parsedPath) {
                    if (pathArray.indexOf(parsedPath) === -1) {
                        pathArray.push(parsedPath)
                    }
                }
            }
        });
    }
    //get form paths
    var formPaths = $("div[id^='addProcessRow-']").find("input");
    if (formPaths && formPaths != null) {
        $.each(formPaths, function (el) {
            var inputPath = $(formPaths[el]).val();
            var parsedPath = parseMountPath(inputPath);
            if (parsedPath) {
                if (pathArray.indexOf(parsedPath) === -1) {
                    pathArray.push(parsedPath)
                }
            }
        });
    }
    //turn into lsf command (use -E to define scripts which will be executed just before the main job)
    if (pathArray.length > 0) {
        var execOtherOpt = '-E "file ' + pathArray.join(' && file ') + '"';
    } else {
        var execOtherOpt = '';
    }

    //check if exec_all or exec_each checkboxes are clicked.
    if ($('#exec_all').is(":checked") === true) {
        var oldExecAll = $('#job_clu_opt').val();
        var newExecAll = getNewExecOpt(oldExecAll, execOtherOpt);
        $('#job_clu_opt').val(newExecAll);

    }
    if ($('#exec_each').is(":checked") === true) {
        var checkedBox = $('#processTable').find('input:checked');
        var checkedBoxArray = checkedBox.toArray();
        var formDataArr = {};
        $.each(checkedBoxArray, function (el) {
            var boxId = $(checkedBoxArray[el]).attr('id')
            var patt = /(.*)-(.*)/;
            var proGnum = boxId.replace(patt, '$2');
            var oldExecEachDiv = $('#procGnum-' + proGnum).find('input[name=opt]')[0];
            var oldExecEach = $(oldExecEachDiv).val();
            var newExecEach = getNewExecOpt(oldExecEach, execOtherOpt);
            $(oldExecEachDiv).val(newExecEach);
        });
    }
    return execOtherOpt
}

//callbackfunction to first change the status of button to connecting
function runProjectPipe(runProPipeCall, checkType) {
    //reset the checktype
    var keepCheckType = checkType;
    window['checkType'] = "";
    execOtherOpt = "";
    displayButton('connectingProPipe');
    //create uuid for run
    var uuid = getValues({ p: "updateRunAttemptLog", project_pipeline_id: project_pipeline_id });
    fillRunVerOpt(["#runVerLog", "#runVerReport"])
    $('#runLogArea').val("");
    //autofill for ghpcc06 cluster to mount all directories before run executed.
    var hostname = $('#chooseEnv').find('option:selected').attr('host');
    if (hostname === "ghpcc06.umassrc.org") {
        execOtherOpt = autofillMountPath()
    }
    // Call the callback
    setTimeout(function () { runProPipeCall(keepCheckType, uuid); }, 1000);
}

//click on run button (callback function)
function runProPipeCall(checkType, uuid) {
    saveRun();
    nxf_runmode = true;
    var nextTextRaw = createNextflowFile("run", uuid);
    nxf_runmode = false;
    var nextText = encodeURIComponent(nextTextRaw);
    var proVarObj = encodeURIComponent(JSON.stringify(window["processVarObj"]))
    var delIntermediate = '';
    var profileTypeId = $('#chooseEnv').find(":selected").val(); //local-32
    var patt = /(.*)-(.*)/;
    var proType = profileTypeId.replace(patt, '$1');
    var proId = profileTypeId.replace(patt, '$2');
    proTypeWindow = proType;
    proIdWindow = proId;
    configTextRaw = '';

    //check if s3 path is defined in output or file paths
    var checkAmzKeysDiv = $("#mRunAmzKeyDiv").css('display');
    if (checkAmzKeysDiv === "inline") {
        var amazon_cre_id = $("#mRunAmzKey").val();
    } else {
        var amazon_cre_id = "";
    }
    //check if Deletion for intermediate files  is checked
    if ($('#intermeDel').is(":checked") === true) {
        configTextRaw += "cleanup = true \n";
    }
    var [allProSett, profileData] = getJobData("both");
    if ($('#docker_check').is(":checked") === true) {
        var docker_img = $('#docker_img').val();
        var docker_opt = $('#docker_opt').val();
        configTextRaw += 'process.container = \'' + docker_img + '\'\n';
        configTextRaw += 'docker.enabled = true\n';
        if (docker_opt !== '') {
            configTextRaw += 'docker.runOptions = \'' + docker_opt + '\'\n';
        }
    }
    if ($('#singu_check').is(":checked") === true) {
        var singu_img = $('#singu_img').val();
        var patt = /^shub:\/\/(.*)/g;
        var singuPath = singu_img.replace(patt, '$1');
        var mntPath = "";
        if (profileData[0].shared_storage_mnt) {
            mntPath = profileData[0].shared_storage_mnt;
        } else {
            mntPath = "//$HOME";
        }
        if (profileData[0].singu_cache) {
            mntPath = profileData[0].singu_cache;
        }

        if (patt.test(singu_img)) {
            singuPath = singuPath.replace(/\//g, '-')
            var downSingu_img = mntPath + '/.dolphinnext/singularity/' + singuPath + '.simg';
        } else if (singu_img.match(/http:/) || singu_img.match(/https:/) || singu_img.match(/ftp:/)){
            var singuPathAr = singuPath.split('/')
            singuPath = singuPathAr[singuPathAr.length-1]
            var downSingu_img = mntPath + '/.dolphinnext/singularity/' + singuPath;
        } else {
            var downSingu_img = singu_img;
        }

        var singu_opt = $('#singu_opt').val();
        configTextRaw += 'process.container = \'' + downSingu_img + '\'\n';
        configTextRaw += 'singularity.enabled = true\n';
        if (singu_opt !== '') {
            configTextRaw += 'singularity.runOptions = \'' + singu_opt + '\'\n';
        }
    }
    var executor_job = profileData[0].executor_job;
    var executor = profileData[0].executor;
    //if executor is local check cpu and memory fields in profile.
    if (executor == "local") {
        var next_cpu = profileData[0].next_cpu;
        var next_memory = profileData[0].next_memory;
        if (next_cpu != null && next_cpu != "" && next_cpu != 0) {
            window.configTextRaw += 'executor.$local.cpus' + ' = ' + next_cpu + '\n';
        }
        if (next_memory != null && next_memory != "" && next_memory != 0) {
            window.configTextRaw += 'executor.$local.memory' + ' = \'' + next_memory + ' GB\'\n';
        }

    }
    configTextRaw += 'process.executor = \'' + executor_job + '\'\n';
    //all process settings eg. process.queue = 'short'
    if ($('#exec_all').is(":checked") === true) {
        var exec_all_settingsRaw = $('#allProcessSettTable').find('input');
        var exec_all_settings = formToJson(exec_all_settingsRaw);
        configTextAllProcess(exec_all_settings, "all", "", executor_job);
    } else {
        if (execOtherOpt != "" && execOtherOpt != null) {
            var oldJobCluOpt = allProSett.job_clu_opt;
            var newJobCluOpt = getNewExecOpt(oldJobCluOpt, execOtherOpt);
            if (newJobCluOpt != "" && newJobCluOpt != null) {
                allProSett.job_clu_opt = newJobCluOpt;
            }
        }
        configTextAllProcess(allProSett, "all", "", executor_job);
    }
    if ($('#exec_each').is(":checked") === true) {
        var exec_each_settings = decodeURIComponent(formToJsonEachPro());
        if (IsJsonString(exec_each_settings)) {
            var exec_each_settings = JSON.parse(exec_each_settings);
            $.each(exec_each_settings, function (el) {
                var each_settings = exec_each_settings[el];
                var processName = $("#" + el + " :nth-child(2)").text()
                //process.$hello.queue = 'long'
                configTextAllProcess(each_settings, "each", processName, executor_job);
            });
        }
    }
    console.log(configTextRaw);
    var configText = encodeURIComponent(configTextRaw);
    //save nextflow text as nextflow.nf and start job
    serverLog = '';
    var serverLogGet = getValues({
        p: "saveRun",
        nextText: nextText,
        proVarObj: proVarObj,
        configText: configText,
        profileType: proType,
        profileId: proId,
        amazon_cre_id: amazon_cre_id,
        project_pipeline_id: project_pipeline_id,
        runType: checkType,
        uuid: uuid
    });
    updateRunVerNavBar()
    $('.nav-tabs a[href="#logTab"]').tab('show');
    readNextflowLogTimer(proType, proId, "default");
}

//#########read nextflow log file for status  ################################################
function readNextflowLogTimer(proType, proId, type) {
    //to trigger fast loading for new page reload
    if (type === "reload") {
        setTimeout(function () { readNextLog(proType, proId, "no_reload") }, 3500);
    }
    interval_readNextlog = setInterval(function () {
        readNextLog(proType, proId, "no_reload")
    }, 13000);
    interval_readPubWeb = setInterval(function () {
        readPubWeb(proType, proId, "no_reload")
    }, 60000);
}

autoScrollLog = true;
$(document).on('click', '#runLogArea', function () {
    autoScrollLog = false;
});

function autoScrollLogArea() {
    if (autoScrollLog) {
        if (document.getElementById("runLogArea")) {
            document.getElementById("runLogArea").scrollTop = document.getElementById("runLogArea").scrollHeight
        }
    }
}

$('a[href="#logTab"]').on('shown.bs.tab', function (e) {
    //check if div is empty
    if (!$.trim($('#logContentDiv').html()).length) {
        $("#runVerLog").trigger("change");
    } else {
        autoScrollLogArea()
    }
});

$('a[href="#reportTab"]').on('shown.bs.tab', function (e) {
    //check if div is empty
    $("#runVerReport").trigger("change");
});


window.saveNextLog = false;

function callAsyncSaveNextLog(data) {
    getValuesAsync(data, function (d) {
        if (d == "logNotFound") {
            window.saveNextLog = "logNotFound"
        } else if (d == "nextflow log saved") {
            window.saveNextLog = true
        } else if (d == "pubweb is not defined") {
            if (typeof interval_readPubWeb !== 'undefined') {
                clearInterval(interval_readPubWeb);
            }
        }
    });
}

function readPubWeb(proType, proId, type) {
    console.log("savePubWeb")
    // save pubWeb files
    callAsyncSaveNextLog({ p: "savePubWeb", project_pipeline_id: project_pipeline_id, profileType: proType, profileId: proId, pipeline_id: pipeline_id })
}

function saveNexLg(proType, proId) {
    console.log("saveNextLog")
    callAsyncSaveNextLog({ p: "saveNextflowLog", project_pipeline_id: project_pipeline_id, profileType: proType, profileId: proId })
    //update log navbar after saving files
    setTimeout(function () { updateRunVerNavBar() }, 2500);
}

function clearIntPubWeb(proType, proId) {
    clearInterval(interval_readPubWeb);
    //last save call after run completed
    setTimeout(function () { readPubWeb(proType, proId, "no_reload") }, 4000);
}
//

function clearIntNextLog(proType, proId) {
    clearInterval(interval_readNextlog);
    //last save call after run completed
    setTimeout(function () { saveNexLg(proType, proId) }, 5000);
}
// type= reload for reload the page
function readNextLog(proType, proId, type) {
    if (projectpipelineOwn === "1") {
        var updateProPipeStatus = getValues({ p: "updateProPipeStatus", project_pipeline_id: project_pipeline_id });
        window.serverLog = "";
        window.nextflowLog = "";
        window.runStatus = "";
        if (updateProPipeStatus){
            window.serverLog = updateProPipeStatus.serverLog;
            window.nextflowLog = updateProPipeStatus.nextflowLog;
            window.runStatus = updateProPipeStatus.runStatus;
        } 
        if (serverLog && serverLog !== null && serverLog !== false) {
            var runPid = parseRunPid(serverLog);
        }
        var pidStatus = "";

        // check runStatus to get status //Available Run_status States: NextErr,NextSuc,NextRun,Error,Waiting,init,Terminated, Aborted
        // if runStatus equal to  Terminated, NextSuc, Error,NextErr, it means run already stopped. Show the status based on these status.
        if (runStatus === "Terminated" || runStatus === "NextSuc" || runStatus === "Error" || runStatus === "NextErr") {
            window["countFailRead"]=0;
            if (type !== "reload") {
                clearIntNextLog(proType, proId);
                clearIntPubWeb(proType, proId);
            }
            if (runStatus === "NextSuc") {
                displayButton('completeProPipe');
                //            showOutputPath();
            } else if (runStatus === "Error" || runStatus === "NextErr") {
                displayButton('errorProPipe');
            } else if (runStatus === "Terminated") {
                displayButton('terminatedProPipe');
            }
        }
        // when run hasn't finished yet and page reloads then show connecting button
        else if (type == "reload" || window.saveNextLog === false || window.saveNextLog === undefined) {
            window["countFailRead"]=0;
            displayButton('connectingProPipe');
            if (type === "reload") {
                readNextflowLogTimer(proType, proId, type);
            }
        }
        // when run hasn't finished yet and connection is down
        else if (window.saveNextLog == "logNotFound" && (runStatus !== "Waiting" && runStatus !== "init")) {
            if (window["countFailRead"] >3){
                displayButton('abortedProPipe');
                //log file might be deleted or couldn't read the log file
                var setStatus = getValues({ p: "updateRunStatus", run_status: "Aborted", project_pipeline_id: project_pipeline_id });
                if (nextflowLog !== null && nextflowLog !== undefined) {
                    nextflowLog += "\nConnection is lost.";
                } else {
                    serverLog += "\nConnection is lost.";
                } 
            } else {
                window["countFailRead"]++
            }

        } 
        // otherwise parse nextflow file to get status
        else if (runStatus === "Waiting" || runStatus === "init" || runStatus === "NextRun") {
            window["countFailRead"]=0;
            if (runStatus === "Waiting" || runStatus === "init") {
                displayButton('waitingProPipe');
            } else if (runStatus === "NextRun") {
                displayButton('runningProPipe');
            }
        }

        var lastrun = $('#runLogArea').attr('lastrun');
        if (lastrun) {
            $('#runLogArea').val(serverLog + "\n" + nextflowLog);
            autoScrollLogArea()
        }

        setTimeout(function () { saveNexLg(proType, proId) }, 8000);
    }
}



function showOutputPath() {
    var outTableRow = $('#outputsTable > tbody > >:last-child').find('span');
    var output_dir = $('#rOut_dir').val();
    //add slash if outputdir not ends with slash
    if (output_dir && output_dir.substr(-1) !== '/') {
        output_dir = output_dir + "/";
    }
    for (var i = 0; i < outTableRow.length; i++) {
        var fname = $(outTableRow[i]).attr('fname');
        $(outTableRow[i]).text(output_dir + fname);
    }
}

function addOutFileDb() {
    var rowIdAll = $('#outputsTable > tbody').find('tr');
    for (var i = 0; i < rowIdAll.length; i++) {
        var data = [];
        var rowID = $(rowIdAll[i]).attr('id');
        var outTableRow = $('#' + rowID + ' >:last-child').find('span');
        var filePath = $(outTableRow[0]).text();
        //	          var gNumParam = rowID.split("Ta-")[1];
        //	          var given_name = $("#input-PName-" + gNumParam).text(); //input-PName-3
        //	          var qualifier = $('#' + rowID + ' > :nth-child(4)').text(); //input-PName-3
        //	          data.push({ name: "id", value: "" });
        //	          data.push({ name: "name", value: filePath });
        //	          data.push({ name: "p", value: "saveInput" });
        //insert into input table
        //	          var inputGet = getValues(data);
        //	          if (inputGet) {
        //	              var input_id = inputGet.id;
        //	          }
        //insert into project_input table
        //bug: it adds NA named files after each run
        //	          var proInputGet = getValues({ "p": "saveProjectInput", "input_id": input_id, "project_id": project_id });
    }
}


function getServerLog(project_pipeline_id, name) {
    var logText = getValues({
        p: "getFileContent",
        project_pipeline_id: project_pipeline_id,
        filename: "run/" + name
    });
    if (logText && logText != "") {
        return $.trim(logText);
    } else {
        return "";
    }
}


function filterKeys(obj, filter) {
    var key, keys = [];
    for (key in obj) {
        if (obj.hasOwnProperty(key) && key.match(filter)) {
            keys.push(key);
        } 
    }
    return keys;
}

function formToJson(rawFormData, stringify) {
    var formDataSerial = rawFormData.serializeArray();
    var formDataArr = {};
    $.each(formDataSerial, function (el) {
        formDataArr[formDataSerial[el].name] = formDataSerial[el].value;
    });
    if (stringify && stringify === 'stringify') {
        return encodeURIComponent(JSON.stringify(formDataArr))
    } else {
        return formDataArr;
    }
}

//prepare JSON to save db
function getProcessOpt() {
    var processOptAll = {};
    var proOptDiv = $('#ProcessPanel').children();
    $.each(proOptDiv, function (el) {
        var boxId = $(proOptDiv[el]).attr('id')
        var patt = /(.*)-(.*)/;
        var proGnum = boxId.replace(patt, '$2');
        var formGroup = $('#addProcessRow-' + proGnum).find('.form-group');
        var formGroupArray = formGroup.toArray();
        var processOptEach = {};
        $.each(formGroupArray, function (el) {
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
            var labelDiv = $(formGroupArray[el]).find("label")[0];
            var inputDiv = $(formGroupArray[el]).find("input,textarea,select")[0];
            var inputDivType = $(inputDiv).attr("type");
            if (labelDiv && inputDiv) {
                // variable name stored at label
                var label = $.trim($(labelDiv).text());
                // if array exist then add _ind + copy Number
                if (outerDivNum) {
                    label = label + "_ind" + outerDivNum;
                }
                //userInput stored at inputDiv. If type of the input is checkbox different method is use to learn whether it is checked
                if (inputDivType === "checkbox") {
                    var input = $(inputDiv).is(":checked").toString();
                } else {
                    var input = $.trim($(inputDiv).val());
                }
                processOptEach[label] = input;
            }
        });
        processOptAll[proGnum] = processOptEach
    });
    return encodeURIComponent(JSON.stringify(processOptAll))
}

function fillEachProcessOpt(eachProcessOpt, label, inputDiv, inputDivType) {
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

// add array forms before fill the data
function addArrForms(allProcessOpt) {
    $.each(allProcessOpt, function (proGnum) {
        var eachProcessOpt = allProcessOpt[proGnum];
        // find all form-groups for each process by proGnum
        var formGroupArray = $('#addProcessRow-' + proGnum).find('.form-group').toArray();
        $.each(formGroupArray, function (elem) {
            var labelDiv = $(formGroupArray[elem]).find("label")[0];
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
                        if (!$('#addProcessRow-' + proGnum + " > #" + outerDivVarname + "_ind" + numAddedForm)[0]) {
                            var addButton = $(outerDiv.next().find("button[id*='Add'][defval*='" + outerDivVarname + "']")[0]);
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
    $.each(allProcessOpt, function (proGnum) {
        var eachProcessOpt = allProcessOpt[proGnum];
        // find all form-groups for each process by proGnum
        var formGroupArray = $('#addProcessRow-' + proGnum).find('.form-group').toArray();
        $.each(formGroupArray, function (elem) {
            var outerDiv = $(formGroupArray[elem]).parent();
            var outerDivId = outerDiv.attr("id");
            //check if visible arrayDiv is exist
            if (outerDivId) {
                if (outerDivId.match(/(.*)_ind(.*)/)) {
                    var outerDivIndId = outerDivId.match(/(.*)_ind(.*)$/)[2];
                    if (outerDivIndId >0){
                        if ($('#addProcessRow-' + proGnum + " > #" + outerDivId ).length){
                            $('#addProcessRow-' + proGnum + " > #" + outerDivId).remove();
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
        $.each(allProcessOpt, function (el) {
            var proGnum = el;
            var eachProcessOpt = allProcessOpt[el];
            // find all form-groups for each process by proGnum
            var formGroup = $('#addProcessRow-' + proGnum).find('.form-group');
            var formGroupArray = formGroup.toArray();
            $.each(formGroupArray, function (elem) {
                var labelDiv = $(formGroupArray[elem]).find("label")[0];
                var inputDiv = $(formGroupArray[elem]).find("input,textarea,select")[0];
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
                                fillEachProcessOpt(eachProcessOpt, newlabelArr, inputDiv, inputDivType);
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
    var checkedBox = $('#processTable').find('input:checked');
    var checkedBoxArray = checkedBox.toArray();
    var formDataArr = {};
    $.each(checkedBoxArray, function (el) {
        var boxId = $(checkedBoxArray[el]).attr('id')
        var patt = /(.*)-(.*)/;
        var proGnum = boxId.replace(patt, '$2');
        var selectedRow = $('#procGnum-' + proGnum).find('input');
        var selectedRowJson = formToJson(selectedRow, 'stringfy');
        formDataArr['procGnum-' + proGnum] = selectedRowJson;
    });
    return encodeURIComponent(JSON.stringify(formDataArr))

}

function saveRunIcon() {
    saveRun();
    checkReadytoRun();
}

function saveRun() {
    var data = [];
    var runSummary = encodeURIComponent($('#runSum').val());
    var run_name = $('#run-title').val();
    var newpipelineID = pipeline_id;
    if (dupliProPipe === false) {
        project_pipeline_id = $('#pipeline-title').attr('projectpipelineid');
    } else if (dupliProPipe === true) {
        old_project_pipeline_id = project_pipeline_id;
        project_pipeline_id = '';
        project_id = $('#userProject').val();
        run_name = run_name + '-copy'
        if (confirmNewRev) {
            newpipelineID = highestRevPipeId;
        }
    }
    //checkAmzKeysDiv
    var checkAmzKeysDiv = $("#mRunAmzKeyDiv").css('display');
    if (checkAmzKeysDiv === "inline") {
        var amazon_cre_id = $("#mRunAmzKey").val();
    } else {
        var amazon_cre_id = "";
    }
    var output_dir = $.trim($('#rOut_dir').val());
    var publish_dir = $('#publish_dir').val();
    var publish_dir_check = $('#publish_dir_check').is(":checked").toString();
    var profile = $('#chooseEnv').val();
    var perms = $('#perms').val();
    var interdel = $('#intermeDel').is(":checked").toString();
    var groupSel = $('#groupSel').val();
    var cmd = encodeURIComponent($('#runCmd').val());
    var exec_each = $('#exec_each').is(":checked").toString();
    var exec_all = $('#exec_all').is(":checked").toString();
    var exec_all_settingsRaw = $('#allProcessSettTable').find('input');
    var exec_all_settings = formToJson(exec_all_settingsRaw, 'stringify');
    var exec_each_settings = formToJsonEachPro();
    var docker_check = $('#docker_check').is(":checked").toString();
    var docker_img = $('#docker_img').val();
    var docker_opt = $('#docker_opt').val();
    var singu_check = $('#singu_check').is(":checked").toString();
    var singu_save = $('#singu_save').is(":checked").toString();
    var singu_img = $('#singu_img').val();
    var singu_opt = $('#singu_opt').val();
    var withReport = $('#withReport').is(":checked").toString();
    var withTrace = $('#withTrace').is(":checked").toString();
    var withTimeline = $('#withTimeline').is(":checked").toString();
    var withDag = $('#withDag').is(":checked").toString();
    var process_opt = getProcessOpt();
    if (run_name !== '') {
        data.push({ name: "id", value: project_pipeline_id });
        data.push({ name: "name", value: run_name });
        data.push({ name: "project_id", value: project_id });
        data.push({ name: "pipeline_id", value: newpipelineID });
        data.push({ name: "amazon_cre_id", value: amazon_cre_id });
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
        data.push({ name: "p", value: "saveProjectPipeline" });
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: data,
            async: true,
            success: function (s) {
                if (dupliProPipe === false) {
                    refreshCreatorData(project_pipeline_id);
                    updateSideBarProPipe("", project_pipeline_id, run_name, "edit")
                } else if (dupliProPipe === true) {
                    var duplicateProPipeIn = getValues({ p: "duplicateProjectPipelineInput", new_id: s.id, old_id: old_project_pipeline_id });
                    dupliProPipe = false;
                    if (duplicateProPipeIn) {
                        setTimeout(function () { window.location.replace("index.php?np=3&id=" + s.id); }, 0);
                    }
                }
            },
            error: function (errorThrown) {
                alert("Error: " + errorThrown);
            }
        });
    }
}

function getProfileData(proType, proId) {
    if (proType === 'cluster') {
        var profileData = getValues({ p: "getProfileCluster", id: proId });
    } else if (proType === 'amazon') {
        var profileData = getValues({ p: "getProfileAmazon", id: proId });
    }
    return profileData;
}

function getJobData(getType) {
    var chooseEnv = $('#chooseEnv option:selected').val();
    if (chooseEnv) {
        var patt = /(.*)-(.*)/;
        var proType = chooseEnv.replace(patt, '$1');
        var proId = chooseEnv.replace(patt, '$2');
        var profileData = getProfileData(proType, proId);
        var allProSett = {};
        if (profileData && profileData != '') {
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

function updateSideBarProPipe(project_id, project_pipeline_id, project_pipeline_name, type) {
    if (type === "edit") {
        $('#propipe-' + project_pipeline_id).html('<i class="fa fa-angle-double-right"></i>' + truncateName(project_pipeline_name, 'sidebarMenu'));
    }
}

function getRunStatus(project_pipeline_id) {
    var runStatusGet = getValues({ p: "getRunStatus", project_pipeline_id: project_pipeline_id });
    if (runStatusGet[0]) {
        runStatus = runStatusGet[0].run_status;
    } else {
        runStatus = '';
    }
    return runStatus;
}
dupliProPipe = false;

function checkNewRevision() {
    //getPipelineRevision will retrive pipeline revisions that user allows to use
    var checkNewRew = getValues({ p: "getPipelineRevision", pipeline_id: pipeline_id });
    var askNewRev = false;
    var highestRev = pipeData[0].rev_id;
    var highestRevPipeId = pipeline_id;
    if (checkNewRew) {
        $.each(checkNewRew, function (el) {
            if (checkNewRew[el].rev_id > highestRev) {
                askNewRev = true;
                highestRev = checkNewRew[el].rev_id;
                highestRevPipeId = checkNewRew[el].id;
            }
        });
    }
    return [highestRevPipeId, askNewRev]
}

function refreshProjectDropDown(id){
    $.ajax({
        type: "GET",
        url: "ajax/ajaxquery.php",
        data: {
            p: "getProjects"
        },
        async: false,
        success: function (s) {
            $(id).empty();
            for (var i = 0; i < s.length; i++) {
                var param = s[i];
                var optionGroup = new Option(decodeHtml(param.name), param.id);
                $(id).append(optionGroup);
            }
            $(id).val(project_id)
        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
}

function duplicateProPipe(type) {
    refreshProjectDropDown("#userProject");

    if (type == "copy"){
        dupliProPipe = true;
        confirmNewRev = false;
        [highestRevPipeId, askNewRev] = checkNewRevision();
        $('#copyRunBut').css("display","none");
        $('#moveRunBut').css("display","none");
        $('#duplicateKeepBtn').css("display","none");
        $('#duplicateNewBtn').css("display","none");
        if (askNewRev === true) {
            $('#duplicateKeepBtn').css("display","inline-block");
            $('#duplicateNewBtn').css("display","inline-block");
            $("#confirmDuplicateText").text('New revision of this pipeline is available. If you want to create a new run and keep your revision of pipeline, please click "Keep Existing Revision" button. If you wish to use same input parameters in new revision of pipeline then click "Use New Revision" button.');
        } else {
            $('#copyRunBut').css("display","inline-block");
            $("#confirmDuplicateText").text('Please select target project to copy your run.');
        }
        $("#confirmDuplicateTitle").text('Copy Run');
        $('#confirmDuplicate').modal("show");  
    } else if (type == "move"){
        dupliProPipe = false;
        saveRun();
        $('#copyRunBut').css("display","none");
        $('#duplicateKeepBtn').css("display","none");
        $('#duplicateNewBtn').css("display","none");
        $('#moveRunBut').css("display","inline-block");
        $("#confirmDuplicateText").text('Please select target project to move your run.');
        $("#confirmDuplicateTitle").text('Move Run');
    }
    $('#confirmDuplicate').modal("show");
}

$(function () {
    $(document).on('change', '#runVerLog', function (event) {
        console.log("runVerLog")
        var run_log_uuid = $(this).val();
        if (run_log_uuid) {
            var version = $('option:selected', this).attr('ver');
            if (version) {
                var runTitleLog = "Run Log " + version + ":"
                $('a[href="#logTab"]').css("display", "block")
                $('a[href="#reportTab"]').css("display", "block")
            } else {
                var runTitleLog = "";
                $('a[href="#logTab"]').css("display", "none")
                $('a[href="#reportTab"]').css("display", "none")
            }
            var lastrun = $('option:selected', this).attr('lastrun');
            if (lastrun) {
                lastrun = 'lastrun="yes"';
            } else {
                lastrun = "";
            }
            var activeTab = $("ul#logNavBar li.active > a")
            var activeID = "";
            if (activeTab[0]) {
                activeID = $(activeTab[0]).attr("href")
            }
            $("#runTitleLog").text(runTitleLog)
            $("#logContentDiv").empty();
            //to support outdated log directory system 
            if (run_log_uuid.match(/^run/)) {
                var path = ""
                } else {
                    var path = "run"
                    }
            var fileList = getValues({ "p": "getFileList", uuid: run_log_uuid, path: path })
            var fileListAr = getObjectValues(fileList);
            var order = ["log.txt", "timeline.html", "report.html", "dag.html", "trace.txt", ".nextflow.log", "nextflow.nf", "nextflow.config"]
            var logContentDivAttr = ["SHOW_RUN_LOG", "SHOW_RUN_TIMELINE", "SHOW_RUN_REPORT", "SHOW_RUN_DAG", "SHOW_RUN_TRACE", "SHOW_RUN_NEXTFLOWLOG", "SHOW_RUN_NEXTFLOWNF", "SHOW_RUN_NEXTFLOWCONFIG"]
            //hide serverlog.txt
            var pubWebPath = $("#basepathinfo").attr("pubweb")
            var navTabDiv = '<ul id="logNavBar" class="nav nav-tabs">';
            var k = 0;
            var tabDiv = [];
            var fileName = [];
            for (var j = 0; j < order.length; j++) {
                if ($("#logContentDiv").attr(logContentDivAttr[j]) == "true" && (fileListAr.includes(order[j]) || order[j] == "log.txt")) {
                    var exist = 'style="display:block;"';
                } else {
                    var exist = 'style="display:none;"';
                }
                k++
                var active = "";
                if (k == 1) {
                    active = 'class="active"';
                }
                var tabID = cleanProcessName(order[j]) + 'Tab';
                tabDiv.push(tabID);
                fileName.push(order[j]);
                navTabDiv += '<li id="' + tabID + '_Div"' + active + '><a class="nav-item sub updateIframe" ' + exist + ' data-toggle="tab"  href="#' + tabID + '">' + order[j] + '</a></li>'
            }
            navTabDiv += '</ul>';
            navTabDiv += '<div id="logNavCont" class="tab-content">';
            for (var n = 0; n < tabDiv.length; n++) {
                var link = pubWebPath + "/" + run_log_uuid + "/" + path + "/" + fileName[n];
                var active = "";
                if (n == 0) {
                    active = 'in active';
                }
                navTabDiv += '<div id = "' + tabDiv[n] + '" class = "tab-pane fade ' + active + '" >';
                if (fileName[n] == "log.txt") {
                    var serverlogText = "";
                    var logText = getValues({ p: "getFileContent", uuid: run_log_uuid, filename: path + "/log.txt" });
                    if (fileListAr.includes("serverlog.txt")) {
                        serverlogText = getValues({ p: "getFileContent", uuid: run_log_uuid, filename: path + "/serverlog.txt" });
                        //to support outdated log directory system 
                    } else if (fileListAr.includes("nextflow.log")) {
                        logText += getValues({ p: "getFileContent", uuid: run_log_uuid, filename: path + "/nextflow.log" });
                    }
                    if (fileListAr.includes("err.log")) {
                        serverlogText += getValues({ p: "getFileContent", uuid: run_log_uuid, filename: path + "/err.log" });
                    }
                    if (fileListAr.includes("initial.log")) {
                        serverlogText += getValues({ p: "getFileContent", uuid: run_log_uuid, filename: path + "/initial.log" });
                    }
                    navTabDiv += '<textarea ' + lastrun + ' readonly id="runLogArea" rows="25" style="overflow-y: scroll; min-width: 100%; max-width: 100%; border-color:lightgrey;" >' + serverlogText + logText + '</textarea>';
                } else {
                    navTabDiv += '<iframe frameborder="0"  style="width:100%; height:900px;" fillsrc="' + link + '"></iframe>';
                }
                navTabDiv += '<a href="' + link + '" class="btn btn-info" role="button" target="_blank">Open Web Link</a>'
                navTabDiv += '</div>';
            }
            navTabDiv += '</div>';
            $("#logContentDiv").append(navTabDiv)
            $('a[href="#log_txtTab"]').on('shown.bs.tab', function (e) {
                autoScrollLogArea()
            });
            if (activeID) {
                if ($('.nav-tabs a[href="' + activeID + '"]').css("display") != "none") {
                    $('.nav-tabs a[href="' + activeID + '"]').trigger("click")
                }
            }
        }
    });
});

function fillRunVerOpt(dropDownAr) {
    var runLogs = getValues({ "p": "getRunLog", project_pipeline_id: project_pipeline_id })
    //allow one outdated log directory
    var newRunLogs = [];
    var once = true
    $.each(runLogs, function (el) {
        var run_log_uuid = runLogs[el].run_log_uuid;
        var project_pipeline_id = runLogs[el].project_pipeline_id
        if (run_log_uuid) {
            newRunLogs.push(runLogs[el])
        } else if (!run_log_uuid && once) {
            once = false;
            newRunLogs.push(runLogs[el])
        }
    });
    var size = $(newRunLogs).size()
    for (var j = 0; j < dropDownAr.length; j++) {
        var n = 0;
        var lastItem = "";
        var dropDownId = dropDownAr[j];
        var runType = "";
        if (dropDownId == "#runVerReport") {
            runType = "Report"
        } else if (dropDownId == "#runVerLog") {
            runType = "Log"
        }
        $(dropDownId).empty();
        $.each(newRunLogs, function (el) {
            var run_log_uuid = newRunLogs[el].run_log_uuid;
            var date_created = newRunLogs[el].date_created
            var project_pipeline_id = newRunLogs[el].project_pipeline_id
            if (run_log_uuid || project_pipeline_id) {
                n++;
                if (n == size) {
                    lastItem = 'lastRun="yes"';
                } else {
                    lastItem = "";
                }
                if (run_log_uuid) {
                    $(dropDownId).prepend(
                        $('<option ' + lastItem + '></option>').attr("ver", n).val(run_log_uuid).html("Run " + runType + " " + n + " created at " + date_created)
                    );
                } else if (project_pipeline_id) {
                    $(dropDownId).prepend(
                        $('<option ' + lastItem + '></option>').attr("ver", n).val("run" + project_pipeline_id).html("Run Log " + n + " created at " + date_created)
                    );
                }
            }
            $(dropDownId).val($(dropDownId + ' option:first').val());
        });
        $(dropDownId).trigger("change");
    }
}


function updateRunVerNavBar() {
    console.log("updateRunVerNavBar")
    var run_log_uuid = $("#runVerLog").val();
    var lastrun = $('option:selected', "#runVerLog").attr('lastrun');
    if (lastrun) {
        lastrun = 'lastrun="yes"';
        var activeTab = $("ul#logNavBar li.active > a")
        var activeID = "";
        if (activeTab[0]) {
            activeID = $(activeTab[0]).attr("href")
        }
        var fileList = getValues({ "p": "getFileList", uuid: run_log_uuid, path: "run" })
        var fileListAr = getObjectValues(fileList);
        fileListAr.splice($.inArray("serverlog.txt", fileListAr), 1);
        var order = ["log.txt", "timeline.html", "report.html", "dag.html", "trace.txt", ".nextflow.log", "nextflow.nf", "nextflow.config"]
        var logContentDivAttr = ["SHOW_RUN_LOG", "SHOW_RUN_TIMELINE", "SHOW_RUN_REPORT", "SHOW_RUN_DAG", "SHOW_RUN_TRACE", "SHOW_RUN_NEXTFLOWLOG", "SHOW_RUN_NEXTFLOWNF", "SHOW_RUN_NEXTFLOWCONFIG"]

        if (fileListAr.length > 0) {
            for (var j = 0; j < fileListAr.length; j++) {
                var orderInd = order.indexOf(fileListAr[j]);
                if (orderInd > -1){
                    if ($("#logContentDiv").attr(logContentDivAttr[orderInd]) == "true"){
                        var tabID = cleanProcessName(fileListAr[j]) + 'Tab';
                        $('a[href="#' + tabID + '"]').css("display", "block")
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

$(document).ready(function () {
    project_pipeline_id = $('#pipeline-title').attr('projectpipelineid');
    pipeData = getValues({ p: "getProjectPipelines", id: project_pipeline_id });
    projectpipelineOwn = pipeData[0].own;
    pipeline_id = pipeData[0].pipeline_id;
    project_id = pipeData[0].project_id;
    runPid = "";
    countFailRead = 0; //count failed read amount if it reaches 5, show connection lost 
    changeOnchooseEnv = false;
    // if user not own it, cannot change or delete run
    if (projectpipelineOwn === "0") {
        $('#deleteRun').remove();
        $('#moveRun').remove();
        $('#delRun').remove();
        $('#saveRunIcon').remove();
        $('#pipeRunDiv').remove();
        $("#run-title").prop("disabled", true);
    }
    ///fixCollapseMenu checkboxes
    fixCollapseMenu('#allProcessDiv', '#exec_all');
    fixCollapseMenu('#eachProcessDiv', '#exec_each');
    fixCollapseMenu('#publishDirDiv', '#publish_dir_check');
    //not allow to check both docker and singularity
    $('#docker_imgDiv').on('show.bs.collapse', function () {
        if ($('#singu_check').is(":checked") && $('#docker_check').is(":checked")) {
            $('#singu_check').trigger("click");
        }
        $('#docker_check').attr('onclick', "return false;");
    });
    $('#singu_imgDiv').on('show.bs.collapse', function () {
        if ($('#singu_check').is(":checked") && $('#docker_check').is(":checked")) {
            $('#docker_check').trigger("click");
        }
        $('#singu_check').attr('onclick', "return false;");
    });
    $('#docker_imgDiv').on('shown.bs.collapse', function () {
        if ($('#singu_check').is(":checked") && $('#docker_check').is(":checked")) {
            $('#singu_check').trigger("click");
        }
        $('#docker_check').removeAttr('onclick');
    });
    $('#singu_imgDiv').on('shown.bs.collapse', function () {
        if ($('#singu_check').is(":checked") && $('#docker_check').is(":checked")) {
            $('#docker_check').trigger("click");
        }
        $('#singu_check').removeAttr('onclick');
    });
    $('#singu_imgDiv').on('hide.bs.collapse', function () {
        $('#singu_check').attr('onclick', "return false;");
    });
    $('#docker_imgDiv').on('hide.bs.collapse', function () {
        $('#docker_check').attr('onclick', "return false;");
    });
    $('#docker_imgDiv').on('hidden.bs.collapse', function () {
        $('#docker_check').removeAttr('onclick');
    });
    $('#singu_imgDiv').on('hidden.bs.collapse', function () {
        $('#singu_check').removeAttr('onclick');
    });

    //runStatus
    runStatus = "";
    if (projectpipelineOwn === "1") {
        runStatus = getRunStatus(project_pipeline_id);
    }
    var profileTypeId = pipeData[0].profile //local-32
    proTypeWindow = "";
    proIdWindow = "";
    if (profileTypeId) {
        if (profileTypeId.match(/-/)) {
            var patt = /(.*)-(.*)/;
            proTypeWindow = profileTypeId.replace(patt, '$1');
            proIdWindow = profileTypeId.replace(patt, '$2');
        }
    }

    if (runStatus !== "") {
        //Available Run_status States: NextErr,NextSuc,NextRun,Error,Waiting,init
        readNextLog(proTypeWindow, proIdWindow, "reload");
        readPubWeb(proTypeWindow, proIdWindow, "reload");
    } else {
        $('#statusProPipe').css('display', 'inline');
    }

    $('#pipeline-title').attr('pipeline_id', pipeline_id);
    if (project_pipeline_id !== '' && pipeline_id !== '') {
        projectPipeInputs = getValues({ p: "getProjectPipelineInputs", project_pipeline_id: project_pipeline_id });
        loadPipelineDetails(pipeline_id, pipeData);

    }
    //after loading pipeline disable all the inputs
    if (projectpipelineOwn === "0") {
        setTimeout(function () {
            $("#configTab :input").not( ":button[show_sett_but]" ).prop("disabled", true);
            $("#advancedTab :input").prop("disabled", true);
            $('.ui-dialog :input').prop("disabled", true);
        }, 1000);
    }




    //##################
    //Sample Modal
    initCompleteFunction = function (settings, json) {
        console.log("initCompleteFunction")
        var columnsToSearch = { 2: 'Collection', 3:"Host", 4:"Project" };
        for (var i in columnsToSearch) {
            var api = new $.fn.dataTable.Api(settings);
            $("#sampleTable_filter").css("display", "inline-block")
            $("#searchBarST").append('<div style="margin-bottom:20px; padding-left:8px; display:inline-block;" id="filter-' + columnsToSearch[i] + '"></div>')
            var select = $('<select id="select-' + columnsToSearch[i] + '" name="' + columnsToSearch[i] + '" multiple="multiple"></select>')
            .appendTo($('#filter-' + columnsToSearch[i]).empty())
            .attr('data-col', i)
            .on('change', function () {

                var vals = $(this).val();
                var valReg = "";
                for (var k = 0; k < vals.length; k++) {
                    var val = $.fn.dataTable.util.escapeRegex(vals[k]);
                    if (val) {
                        if (k + 1 !== vals.length) {
                            valReg += val + "|"
                        } else {
                            valReg += val
                        }
                    }
                }
                api.column($(this).attr('data-col'))
                    .search(valReg ? '(^|,)' + valReg + '(,|$)' : '', true, false)
                    .draw();

                //deselect rows that are selected but not visible
                var visibleRows = sampleTable.rows({ search: 'applied' })[0];
                var selectedRows = sampleTable.rows( '.selected' )[0];
                api.column($(this).attr('data-col')).rows( function ( idx, data, node ) { 
                    if($.inArray(idx, visibleRows) === -1 && $.inArray(idx, selectedRows) !== -1) {
                        sampleTable.row(idx).deselect(idx);
                    }
                    return false;
                });

            });
            var collectionList = []
            api.column(i).data().unique().sort().each(function (d, j) {
                if (d){
                    var multiCol = d.split(",");
                    for (var n = 0; n < multiCol.length; n++) {
                        if (collectionList.indexOf(multiCol[n]) == -1) {
                            collectionList.push(multiCol[n])
                            select.append('<option value="' + multiCol[n] + '">' + multiCol[n] + '</option>');
                        }
                    }  
                }

            });


            createMultiselect('#select-' + columnsToSearch[i])
            createMultiselectBinder('#filter-' + columnsToSearch[i])
            var selCollectionNameArr = $("#sampleTable").data("select")
            if (selCollectionNameArr) {
                if (selCollectionNameArr.length) {
                    $("#sampleTable").removeData("select");
                    selectMultiselect("#select-Collection", selCollectionNameArr);
                    sampleTable.rows({ search: 'applied' }).select();
                }
            }
        }
    };

    $(function () {
        $(document).on('xhr.dt', '#sampleTable', function (e, settings, json, xhr) {
            new $.fn.dataTable.Api(settings).one('draw', function () {
                initCompleteFunction(settings, json);
            });
        });
        //Prevent BODY from scrolling when a modal is opened
        $('#inputFilemodal').on('show.bs.modal', function () {
            $('body').css('overflow', 'hidden');
            $('body').css('position', 'fixed');
            $('body').css('width', '100%');
        }).on('hidden.bs.modal', function () {
            $('body').css('overflow', 'hidden auto');
            $('body').css('position', 'static');
        })

        $('#inputFilemodal').on('click', '#addSample', function (event) {
            event.preventDefault();
            if (proTypeWindow && proIdWindow) {
                $("#addFileModal").modal("show");
            } else {
                showInfoModal("#infoModal", "#infoModalText", "Please first select a run environment in the run page so that you can search files in the specified host.")
            }
        });


        $('#addFileModal').on('show.bs.modal', function () {
            $('#addFileModal').find('form').trigger('reset');
            $('.nav-tabs a[href="#hostFiles"]').tab('show');
            $("#viewDir").removeData("fileArr");
            $("#viewDir").removeData("fileDir");
            $("#viewDir").removeData("amzKey");
            fillArray2Select([], "#viewDir", true)
            resetPatternList()
            clearSelection()
            selectedGeoSamplesTable.fnClearTable();
            searchedGeoSamplesTable.fnClearTable();
            selectAmzKey()
            $('.forwardpatternDiv').css("display", "none")
            $('.reversepatternDiv').css("display", "none")
            $('.singlepatternDiv').css("display", "none")
            $('.patternButs').css("display", "none")
            $('.patternTable').css("display", "none")
            $("#viewDir").css("display", "none")
            $("#seaGeoSamplesDiv").css("display", "none")
            $("#selGeoSamplesDiv").css("display", "none")
            $('#mRunAmzKeyS3Div').css("display", "none")
            $('#mArchAmzKeyS3Div_GEO').css("display", "none")
            $('#mArchAmzKeyS3Div').css("display", "none")
            var renderMenu = {
                option: function (data, escape) {
                    return '<div class="option">' +
                        '<span class="title"><i>' + escape(data.name) + '</i></span>' +
                        '</div>';
                },
                item: function (data, escape) {
                    return '<div class="item" data-value="' + escape(data.id) + '">' + escape(data.name) + '</div>';
                }
            };
            var selectizeIDs = ['#collection_id', '#collection_id_geo']
            for (var i = 0; i < selectizeIDs.length; i++) {
                $(selectizeIDs[i]).selectize({
                    valueField: 'id',
                    searchField: ['name'],
                    createOnBlur: true,
                    render: renderMenu,
                    options: getValues({ p: "getCollection" }),
                    create: function (input, callback) {
                        callback({ id: "_newItm_" + input, name: input });
                    }
                });
                $(selectizeIDs[i])[0].selectize.clear()
            }
            //#uploadFiles tab:
            var workDir = $("#rOut_dir").val();
            if (workDir){
                $("#target_dir").val(workDir+"/run"+project_pipeline_id+"/upload")
            }
        });

        $('#viewDirBut').click(function () {
            var dir = $('#file_dir').val();
            var amazon_cre_id = "";
            var warnUser = false;
            if (dir) {
                if (dir.match(/s3:/)){
                    var lastChr = dir.slice(-1);
                    if (lastChr == "/"){
                        dir = dir.substring(0, dir.length - 1);
                    }
                    amazon_cre_id = $('#mRunAmzKeyS3').val()
                    if (!amazon_cre_id){
                        showInfoModal("#infoModal", "#infoModalText", "Please select Amazon Keys to search files in your S3 storage.");
                        warnUser = true;
                    } 
                } else if (dir.match(/:\/\//)){
                    var lastChr = dir.slice(-1);
                    if (lastChr == "/"){
                        dir = dir.substring(0, dir.length - 1);
                    }  
                }
                if (!warnUser){
                    var dirList = getValues({ "p": "getLsDir", dir: dir, profileType: proTypeWindow, profileId: proIdWindow, amazon_cre_id:amazon_cre_id });
                    if (dirList) {
                        dirList = $.trim(dirList)
                        console.log(dirList)
                        var fileArr = [];
                        var errorAr = [];
                        if (dir.match(/s3:/)){
                            var raw = dirList.split('\n');
                            for (var i = 0; i < raw.length; i++) {
                                var filePath = raw[i].split(" ").pop();
                                if (filePath){
                                    if (filePath.match(/s3:/)){
                                        var allBlock = filePath.split("/");
                                        if (filePath.substr(-1) == "/"){
                                            var lastBlock = allBlock[allBlock.length-2]
                                            } else {
                                                var lastBlock = allBlock[allBlock.length-1]
                                                }
                                        fileArr.push(lastBlock)
                                    } else {
                                        errorAr.push(raw[i])
                                    }
                                } else {
                                    errorAr.push(raw[i])
                                }
                            }
                        } else if (dir.match(/:\/\//)){
                            fileArr = dirList.split('\n');
                            errorAr = fileArr.filter(line => line.match(/:/));
                            fileArr = fileArr.filter(line => !line.match(/:/));
                        } else {
                            fileArr = dirList.split('\n');
                            errorAr = fileArr.filter(line => line.match(/ls:/));
                            fileArr = fileArr.filter(line => !line.match(/:/));
                        }
                        console.log(fileArr)
                        console.log(errorAr)
                        if (fileArr.length > 0) {
                            fillArray2Select(fileArr, "#viewDir", true)
                            $("#viewDir").data("fileArr", fileArr)
                            $("#viewDir").data("fileDir", dir)
                            var amzKey = ""
                            if (dir.match(/s3:/i)){
                                amzKey= $("#mRunAmzKeyS3").val()
                            }
                            $("#viewDir").data("amzKey", amzKey)

                            $('#collection_type').trigger("change");
                        } else {
                            if (errorAr.length > 0) {
                                var errTxt = errorAr.join(' ')
                                showInfoModal("#infoModal", "#infoModalText", errTxt)
                                resetPatternList()
                            } else {
                                fillArray2Select(["Files Not Found."], "#viewDir", true)
                                resetPatternList()
                            }
                        }
                    } else {
                        fillArray2Select(["Files Not Found."], "#viewDir", true)
                        resetPatternList()
                    }
                } else {
                    fillArray2Select(["Files Not Found."], "#viewDir", true)
                    resetPatternList()
                }
                $("#viewDir > option").attr("style", "pointer-events: none;");
                $("#viewDir").css("display", "inline")
            } else {
                showInfoModal("#infoModal", "#infoModalText", "Please enter 'File Directory' to search files in your host.")
            }
        });

        removeSRA = function (name, srr_id, collection_type, button) {
            var row = $(button).closest('tr');
            selectedGeoSamplesTable.fnDeleteRow(row);
            selectedGeoSamplesTable.fnDraw();
            //check table data before adding.
            var select_button = '<button class="btn btn-primary pull-right" type= "button" id="' + srr_id + '_select" onclick="selectSRA(\'' + name + '\',\'' + srr_id + '\', \'' + collection_type + '\', this)">Select</button>';
            //check table data before adding.
            var table_data = searchedGeoSamplesTable.fnGetData();
            var checkTableUniqueData = table_data.filter(function (el) { return el[0] == srr_id });
            if (checkTableUniqueData.length == 0) {
                searchedGeoSamplesTable.fnAddData([name, srr_id, collection_type, select_button]);
            }
        }

        selectSRA = function (name, srr_id, collection_type, button) {
            var row = $(button).closest('tr');
            searchedGeoSamplesTable.fnDeleteRow(row);
            searchedGeoSamplesTable.fnDraw();
            $("#selGeoSamplesDiv").css("display", "block");
            selectedGeoSamplesTable.fnAddData([
                '<input type="text" id="' + name + '" size="70" class="col-mid-12" onchange="updateNameTable(this)" value="' + name + '">',
                srr_id,
                collection_type,
                '<button class="btn btn-danger pull-right" id="' + srr_id + '_remove" onclick="removeSRA(\'' + name + '\',\'' + srr_id + '\', \'' + collection_type + '\', this)">Remove</button>'
            ])
        }

        selectAllSRA = function () {
            var table_nodes = searchedGeoSamplesTable.fnGetNodes()
            for (var x = 0; x < table_nodes.length; x++) {
                if (table_nodes[x].children[3].children[0].disabled == false) {
                    table_nodes[x].children[3].children[0].click()
                }
            }
        }

        $('#viewGeoBut').click(function () {
            var geo_id = $('#geo_id').val()
            if (geo_id) {
                var geoList = "";
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: { p: 'getGeoData', geo_id: geo_id },
                    beforeSend: function () { showLoadingDiv("viewGeoButDiv"); },
                    complete: function () {
                        $("#seaGeoSamplesDiv").css("display", "block");
                        hideLoadingDiv("viewGeoButDiv");
                    },
                    async: true,
                    success: function (geoList) {
                        if (!geoList) {
                            showInfoModal("#infoModal", "#infoModalText", "There was an error in your GEO query. Search term " + geo_id + " cannot be found")
                        } else if (geoList.length == 0) {
                            showInfoModal("#infoModal", "#infoModalText", "There was an error in your GEO query. Search term " + geo_id + " cannot be found")
                        } else if (geoList.length == 1 && $.isEmptyObject(geoList[0])) {
                            showInfoModal("#infoModal", "#infoModalText", "There was an error in your GEO query. Search term " + geo_id + " cannot be found")
                        } else {
                            var errCount = 0;
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
                                        }
                                        var select_button = '<button class="btn btn-primary pull-right" type= "button" id="' + srr_id + '_select" onclick="selectSRA(\'' + name + '\',\'' + srr_id + '\', \'' + collection_type + '\', this)">Select</button>';
                                        //check table data before adding.
                                        var selected_data = selectedGeoSamplesTable.fnGetData();
                                        var checkSelectedUniqueData = selected_data.filter(function (el) { return el[1] == srr_id });
                                        var table_data = searchedGeoSamplesTable.fnGetData();
                                        var checkTableUniqueData = table_data.filter(function (el) { return el[1] == srr_id });
                                        if (checkTableUniqueData.length == 0 && checkSelectedUniqueData.length == 0) {
                                            searchedGeoSamplesTable.fnAddData([name, srr_id, collection_type, select_button]);
                                        } else {
                                            if (errCount < 1) {
                                                showInfoModal("#infoModal", "#infoModalText", "Search term " + srr_id + " already added into table.")
                                            } else {
                                                var oldtext = $("#infoModalText").html();
                                                $("#infoModalText").html(oldtext + "</br>" + "Search term " + srr_id + " already added into table.");
                                            }
                                            errCount += 1
                                        }
                                    }
                                }
                            }
                        }
                    }
                });

            }
        });


        $('#addFileModal').on('click', '#mSaveFiles', function (event) {
            event.preventDefault();
            var checkTab = $('#addFileModal').find('.active.tab-pane')[0].getAttribute('id');
            var warnUser = false;
            if (checkTab === 'hostFiles') {
                var formValues = $('#hostFiles').find('input, select');
                var requiredFields = ["file_dir", "collection_type", "collection_id"];
                var ret = {};
                var infoModalText = ""
                ret = getTableSamples("selectedSamplesTable")
                var rowData = selectedSamplesTable.fnGetData();
                var fileDirArr = []
                for (var i = 0; i < rowData.length; i++) {
                    var file_dir = rowData[i][2];
                    var amzKey = rowData[i][4];
                    if (file_dir.match("s3:")){
                        file_dir = file_dir+"\t"+amzKey
                    }
                    fileDirArr.push(file_dir);
                }

                if (ret.warnUser) {
                    infoModalText += ret.warnUser;
                } 
                if (!ret.file_array.length) {
                    infoModalText += " * Please fill table by clicking 'Add All Files' or 'Add Selected Files' buttons."
                } 
                var s3_archive_dir  = $.trim($("#s3_archive_dir").val());
                var amzArchKey  = $("#mArchAmzKeyS3").val();

                if (!warnUser && s3_archive_dir.match(/s3:/)){
                    if (!amzArchKey){
                        infoModalText += " * Please select Amazon Archive Keys to save files into your S3 storage.";
                        warnUser = true;
                    } 
                }
                if (infoModalText){
                    showInfoModal("#infoModal", "#infoModalText", infoModalText);
                }

                var formObj = {};
                var stop = "";
                [formObj, stop] = createFormObj(formValues, requiredFields)
                if (stop === false && !ret.warnUser && ret.file_array.length && !warnUser) {
                    //new items come with prefix: _newItm_
                    var collection_name = $("#collection_id")[0].selectize.getItem(formObj.collection_id)[0].innerHTML;
                    if (formObj.collection_id.match(/^_newItm_(.*)/)) {
                        var collection_data = getValues({ p: "saveCollection", name: collection_name })
                        if (collection_data.id) {
                            formObj.collection_id = collection_data.id
                        }
                    }

                    formObj.file_dir = fileDirArr;

                    if (s3_archive_dir.match("s3:")){
                        formObj.s3_archive_dir = s3_archive_dir+"\t"+amzArchKey
                    }
                    formObj.file_array = ret.file_array;
                    formObj.run_env = $('#chooseEnv').find(":selected").val();
                    formObj.project_id = project_id;
                    formObj.p = "saveFile"
                    console.log(formObj)
                    $.ajax({
                        type: "POST",
                        url: "ajax/ajaxquery.php",
                        data: formObj,
                        async: true,
                        success: function (s) {
                            if (s.id) {
                                $("#sampleTable").data("select", [collection_name])
                                $("#sampleTable").DataTable().ajax.reload(null, false);
                                $('#addFileModal').modal('hide');
                            }
                        },
                        error: function (errorThrown) {
                            alert("Error: " + errorThrown);
                        }
                    });
                }
            } else if (checkTab === 'geoFiles') {
                var formValues = $('#geoFiles').find('input, select');
                var requiredFields = ["collection_id"];
                var ret = {};
                var infoModalText = ""
                ret = getTableSamples("selectedGeoSamplesTable")
                if (ret.warnUser) {
                    infoModalText += ret.warnUser;
                }
                if (!ret.file_array.length) {
                    infoModalText += " * Please fill 'Selected GEO Files' table by clicking 'Select' buttons in the 'Searched GEO Files' table."
                }
                var s3_archive_dir_geo  = $.trim($("#archive_dir_geo").val());
                var amzArchKey  = $("#mArchAmzKeyS3_GEO").val();
                if (s3_archive_dir_geo.match(/s3:/)){
                    if (!amzArchKey){
                        infoModalText += " * Please select Amazon Keys to save files into your S3 storage.";
                        warnUser = true;
                    } 
                }
                if (infoModalText){
                    showInfoModal("#infoModal", "#infoModalText", infoModalText);
                }

                var formObj = {};
                var stop = "";
                [formObj, stop] = createFormObj(formValues, requiredFields)
                if (stop === false && !ret.warnUser && ret.file_array.length && !warnUser) {
                    //new items come with prefix: _newItm_
                    var collection_name = $("#collection_id_geo")[0].selectize.getItem(formObj.collection_id)[0].innerHTML;
                    if (formObj.collection_id.match(/^_newItm_(.*)/)) {
                        var collection_data = getValues({ p: "saveCollection", name: collection_name })
                        if (collection_data.id) {
                            formObj.collection_id = collection_data.id
                        }
                    }
                    formObj.file_type = "fastq";
                    var collection_type = selectedGeoSamplesTable.fnGetData();
                    if (collection_type[0][2] == "Paired") {
                        formObj.collection_type = "pair";
                    } else if (collection_type[0][2] == "Single") {
                        formObj.collection_type = "single";
                    }
                    if (s3_archive_dir_geo.match("s3:")){
                        formObj.s3_archive_dir = s3_archive_dir_geo+"\t"+amzArchKey
                    }
                    formObj.file_array = ret.file_array
                    formObj.run_env = $('#chooseEnv').find(":selected").val();
                    formObj.project_id = project_id;
                    formObj.p = "saveFile"
                    $.ajax({
                        type: "POST",
                        url: "ajax/ajaxquery.php",
                        data: formObj,
                        async: true,
                        success: function (s) {
                            if (s.id) {
                                $("#sampleTable").data("select", [collection_name])
                                $("#sampleTable").DataTable().ajax.reload(null, false);
                                $('#addFileModal').modal('hide');
                            }
                        },
                        error: function (errorThrown) {
                            alert("Error: " + errorThrown);
                        }
                    });
                }
            }
        });

    });

    createMultiselect = function (id,columnToSearch, apiColumn) {
        $(id).multiselect({
            includeResetOption: true,
            resetText: "Clear filters",
            includeResetDivider: true,
            buttonText: function (options, select) {
                if (options.length == 0) {
                    return select.attr("name") + ": All";
                } else if (options.length > 2) {
                    return select.attr("name") + ": " + options.length + ' selected';
                } else {
                    var labels = [];
                    options.each(function () {
                        labels.push($(this).text());
                    });
                    return select.attr("name") + ": " + labels.join(', ') + '';
                }
            }
        });
    }
    createMultiselectBinder = function (id) {
        var resetBut = $(id).find("a.btn-block");
        resetBut.click(function () {
            $($(id).find("input")[0]).trigger("change")
        });
    }




    sampleTable = $('#sampleTable').DataTable({
        "dom": '<"#searchBarST.pull-left"f>rt<"pull-left"i><"bottom"p><"clear">',
        "destroy": true,
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getFile" },
            "dataSrc": ""
        },
        "hover": true,
        "columns": [{
            "data": "id",
            "checkboxes": {
                'targets': 0,
                'selectRow': true
            }
        }, {
            "data": "name"
        }, {
            "data": "collection_name"
        }, {
            "data": "run_env"
        }, {
            "data": "project_name"
        }, {
            "data": "date_created"
        },{
            "data": null,
            "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                $(nTd).html('<button type="button" class="btn btn-default btn-sm showDetailSample"> Details</button>');
            }
        }],
        'select': {
            'style': 'multi',
            selector: 'td:not(.no_select_row)'
        },
        'order': [[3, 'desc']],
        "columnDefs": [
            {
                'targets': [3,4],
                className: "disp_none"
            },
            {
                'targets': [6],
                className: "no_select_row"
            },
        ],
        initComplete: initCompleteFunction
    });

    selectedSamplesTable = $('#selectedSamples').dataTable({
        "columnDefs": [
            {
                'targets': [4],
                visible: false
            },
        ]
    });
    selectedGeoSamplesTable = $('#selectedGeoSamples').dataTable();
    searchedGeoSamplesTable = $('#searchedGeoSamples').dataTable();

    //xxx
    $(document).on('click', '.showDetailSample', function (e) {

        var getHeaderRow = function (text){
            if (!text){
                text = "";
            }
            return '<thead><tr><th>'+text+'</th></tr></thead>';
        }
        var getBodyRow = function (text){
            if (!text){
                text = "";
            }
            return '<tbody><tr><td>'+text+'</td></tr></tbody>';
        }
        var s3Clean = function (text){
            if (!text){
                text = "";
            } else if (text.match(/s3:/i)){
                var textPath = $.trim(text).split("\t")[0]
                if (textPath){
                    text = textPath;
                }
            }
            return text;
        }
        var insertDetailsTable = function (data){
            var tableRows = "";
            $("#details_of_file_table").empty()
            tableRows += getHeaderRow("Name:");
            tableRows += getBodyRow(data.name);
            if (data.file_dir){
                tableRows += getHeaderRow("Input File(s) Directory:")
                tableRows += getBodyRow(s3Clean(data.file_dir))
                tableRows += getHeaderRow("Input File(s):")
                tableRows += getBodyRow(data.files_used.replace(/\|/g, '<br/>'))
            } else {
                //geo files:
                tableRows += getHeaderRow("GEO ID:")
                tableRows += getBodyRow(data.files_used.replace(/\|/g, '<br/>'))
            }
            var collection_type ="";
            if (data.collection_type == "single"){
                collection_type = "Single/List"
            } else if (data.collection_type == "pair"){
                collection_type = "Paired List"
            }

            tableRows += getHeaderRow("Collection Type:")
            tableRows += getBodyRow(collection_type)
            tableRows += getHeaderRow("Local Archive Directory:")
            tableRows += getBodyRow(data.archive_dir)
            tableRows += getHeaderRow("Amazon S3 Backup:")
            tableRows += getBodyRow(s3Clean(data.s3_archive_dir))
            if (data.run_env){
                tableRows += getHeaderRow("Run Environment:")
                tableRows += getBodyRow(data.run_env) 
            }
            if (data.project_name){
                tableRows += getHeaderRow("Project(s):")
                tableRows += getBodyRow(data.project_name) 
            }
            $("#details_of_file_table").append(tableRows)
        }
        if ($("#detailsOfFileDiv").css("display") == "none"){
            $("#detailsOfFileDiv").css("display","block")
        } 
        var clickedRow = $(e.target).closest('tr');
        var rowData = sampleTable.row(clickedRow).data();
        insertDetailsTable(rowData)

    });

    // show file details if one file is selected 
    $('#sampleTable').on( 'select.dt deselect.dt', function ( e, dt, type, indexes ) {
        var selectedRows = sampleTable.rows({ selected: true }).data();
        if (selectedRows.length >0 ){
            $("#deleteSample").css("display","inline-block")
        } else {
            $("#deleteSample").css("display","none")
        }
    } );

    function resetPatternList() {
        fillArray2Select([], "#singleList", true)
        fillArray2Select([], "#reverseList", true)
        fillArray2Select([], "#forwardList", true)
    }

    $(function () {
        $(document).on('change', '#collection_type', function () {
            var collection_type = $(this).val();
            if (collection_type == "pair") {
                $('.forwardpatternDiv').css("display", "inline")
                $('.reversepatternDiv').css("display", "inline")
                $('.singlepatternDiv').css("display", "none")
                $('.patternButs').css("display", "inline")
                $('.patternTable').css("display", "inline")
                $('#forward_pattern').trigger("keyup");
                $('#reverse_pattern').trigger("keyup");
            } else if (collection_type == "single") {
                $('.patternButs').css("display", "inline")
                $('.patternTable').css("display", "inline")
                $('.singlepatternDiv').css("display", "inline")
                $('.forwardpatternDiv').css("display", "none")
                $('.reversepatternDiv').css("display", "none")
                $('#single_pattern').trigger("keyup");
            }
        });
        $(document).on('click', '#forwardList', function () {
            var allItems = [];
            $("#forwardList > option").each(function () { allItems.push(this.value); });
            var selItems = $('#forwardList').val();
            var reverseOpt = $("#reverseList > option");
            reverseOpt.prop('selected', false);
            for (var i = 0; i < selItems.length; i++) {
                var order = allItems.indexOf(selItems[i]);
                if (reverseOpt[order]) {
                    $(reverseOpt[order]).prop('selected', true);
                }

            }
        });
        $(document).on('click', '#reverseList', function () {
            var allItems = [];
            $("#reverseList > option").each(function () { allItems.push(this.value); });
            var selItems = $('#reverseList').val();
            var forwardOpt = $("#forwardList > option");
            forwardOpt.prop('selected', false);
            for (var i = 0; i < selItems.length; i++) {
                var order = allItems.indexOf(selItems[i]);
                if (forwardOpt[order]) {
                    $(forwardOpt[order]).prop('selected', true);
                }

            }
        });

    });



    function updateFileArea(selectId, pattern) {
        var fileOrj = $("#viewDir").data("fileArr")
        if (fileOrj) {
            var fileAr = fileOrj.slice(); //clone list
            var delArr = $(selectId).data("samples")
            if (delArr) {
                if (delArr.length) {
                    for (var i = 0; i < delArr.length; i++) {
                        var index = fileAr.indexOf(delArr[i]);
                        if (index > -1) {
                            fileAr.splice(index, 1);
                        }
                    }
                }
            }
            if (fileAr) {
                pattern = cleanRegEx(pattern);
                var reg = new RegExp(pattern)
                var filteredAr = fileAr.filter(line => line.match(reg));
                if (filteredAr.length > 0) {
                    fillArray2Select(filteredAr, selectId, true)
                } else {
                    fillArray2Select(["No file match with pattern."], selectId, true)
                }
            } else {
                fillArray2Select(["There is no file to match pattern"], selectId, true)
            }
        }
    }
    window.timeoutID = {};
    window.timeoutID['#forward_pattern'] = 0;
    window.timeoutID['#reverse_pattern'] = 0;
    window.timeoutID['#single_pattern'] = 0;

    function updateFileList(selectId, pattern) {
        if (window.timeoutID[selectId]) clearTimeout(window.timeoutID[selectId]);
        window.timeoutID[selectId] = setTimeout(function () { updateFileArea(selectId, pattern) }, 500);
    }
    $(function () {
        $(document).on('keyup', '#forward_pattern', function () {
            var pattern = $(this).val();
            updateFileList("#forwardList", pattern)
        });
        $(document).on('keyup', '#reverse_pattern', function () {
            var pattern = $(this).val();
            updateFileList("#reverseList", pattern)
        });
        $(document).on('keyup', '#single_pattern', function () {
            var pattern = $(this).val();
            updateFileList("#singleList", pattern)
        });
    });



    clearSelection = function () {
        selectedSamplesTable.fnClearTable();
        $('#forwardList').html("")
        $('#reverseList').html("")
        $('#singleList').html("")
        recordDelList("#forwardList", null, "reset")
        recordDelList("#reverseList", null, "reset")
        recordDelList("#singleList", null, "reset")
        $('#collection_type').trigger("change");

    }


    removeRowSelTable = function (button, collection_type) {
        var row = $(button).closest('tr');
        var files_used = row.children()[1].innerHTML.split(' | ');
        for (var x = 0; x < files_used.length; x++) {
            if (files_used[x].match(/,/)) {
                var forwardFile = files_used[x].split(",")[0]
                var reverseFile = files_used[x].split(",")[1]
                $("#forwardList > option").each(function () { if (this.value.match(/no file/i)) { $(this).remove() } });
                $("#reverseList > option").each(function () { if (this.value.match(/no file/i)) { $(this).remove() } });

                document.getElementById('forwardList').innerHTML += '<option value="' + forwardFile + '">' + forwardFile + '</option>'
                document.getElementById('reverseList').innerHTML += '<option value="' + reverseFile + '">' + reverseFile + '</option>'
                recordDelList("#forwardList", forwardFile, "add")
                recordDelList("#reverseList", reverseFile, "add")

            } else {
                $("#singleList > option").each(function () { if (this.value.match(/no file/i)) { $(this).remove() } });
                document.getElementById('singleList').innerHTML += '<option value="' + files_used[x] + '">' + files_used[x] + '</option>'
                recordDelList("#singleList", files_used[x], "add")
            }
        }
        selectedSamplesTable.fnDeleteRow(row);
        selectedSamplesTable.fnDraw();
    }
    updateNameTable = function (input) {
        input.id = input.value;
    }
    replaceCharacters = function (string) {
        string = string.replace(/\./g, "_");
        string = string.replace(/-/g, "_");
        return string;
    }

    //keep record of the deleted items from singleList, forwardList, reverseList
    //in case of new search don't show these items
    recordDelList = function (listDiv, value, type) {
        if (type == "reset") {
            $(listDiv).removeData("samples")
        } else {
            var delArr = $(listDiv).data("samples")
            if (delArr) {
                if (delArr.length) {
                    if (type !== "add") {
                        delArr.push(value)
                    } else {
                        var index = delArr.indexOf(value);
                        if (index > -1) {
                            delArr.splice(index, 1);
                        }
                    }
                    $(listDiv).data("samples", delArr)
                }
            } else {
                if (type !== "add") {
                    $(listDiv).data("samples", [value])
                }
            }
        }
    }

    smartSelection = function () {
        var collection_type = $('#collection_type').val();
        if (collection_type == "single") {
            var files_select = document.getElementById('singleList').options;
            var regex = $('#single_pattern').val();
        } else {
            var files_select = document.getElementById('forwardList').options;
            var files_selectRev = document.getElementById('reverseList').options;
            var regex1 = $('#forward_pattern').val();
            var regex2 = $('#reverse_pattern').val();
        }
        while (files_select.length != 0) {
            var file_string = '';
            //  var file_regex = new RegExp(regex_string);
            if (collection_type == "single") {
                //	use regex to find the values before the pivot
                if (regex === "") {
                    regex = '.';
                }
                var regex_string = files_select[0].value.split(regex)[0];
                for (var x = 0; x < files_select.length; x++) {
                    var prefix = files_select[x].value.split(regex)[0];
                    if (regex_string === prefix) {
                        file_string += files_select[x].value + ' | '
                        recordDelList("#singleList", files_select[x].value, "del")
                        $('#singleList option[value="' + files_select[x].value + '"]')[0].remove();
                        x--;
                    }
                }
            } else {
                var regex_string = files_select[0].value.split(regex1)[0];
                var regex_string2 = files_selectRev[0].value.split(regex2)[0];
                for (var x = 0; x < files_select.length; x++) {
                    var prefix1 = files_select[x].value.split(regex1)[0];
                    var prefix2 = files_selectRev[x].value.split(regex2)[0];
                    if (regex_string === prefix1 && regex_string2 === prefix2) {
                        file_string += files_select[x].value + ',' + files_selectRev[x].value + ' | '
                        recordDelList("#forwardList", files_select[x].value, "del")
                        recordDelList("#reverseList", files_selectRev[x].value, "del")
                        $('#forwardList option[value="' + files_select[x].value + '"]')[0].remove();
                        $('#reverseList option[value="' + files_selectRev[x].value + '"]')[0].remove();
                        x--;
                    }
                }
            }
            file_string = file_string.substring(0, file_string.length - 3);
            if (regex === "") {
                var name = file_string;
            } else {
                var name = file_string.split(regex)[0];
            }
            var name = name.split(' | ')[0].split('.')[0];
            var input = createElement('input', ['id', 'type', 'class', 'value', 'onChange'], [name, 'text', '', name, 'updateNameTable(this)'])
            var button_div = createElement('div', ['class'], ['text-center'])
            var remove_button = createElement('button', ['class', 'type', 'onclick'], ['btn-sm btn-danger text-center', 'button', 'removeRowSelTable(this,\'' + collection_type + '\')']);
            var icon = createElement('i', ['class'], ['fa fa-times']);
            remove_button.appendChild(icon);
            button_div.appendChild(remove_button);
            var fileDir = $("#viewDir").data("fileDir")
            var mRunAmzKeyS3 = "";
            if (fileDir.match(/s3:/)){
                mRunAmzKeyS3 = $("#viewDir").data("amzKey")
            }

            selectedSamplesTable.fnAddData([
                input.outerHTML,
                file_string,
                fileDir,
                button_div.outerHTML,
                mRunAmzKeyS3
            ]);
        }
    }

    addSelection = function () {
        var collection_type = $('#collection_type').val();
        if (collection_type == "single") {
            var current_selection = document.getElementById('singleList').options;
            var regex = $('#single_pattern').val();
            var file_string = '';
            for (var x = 0; x < current_selection.length; x++) {
                if (current_selection[x].selected) {
                    file_string += current_selection[x].value + ' | '
                    recordDelList("#singleList", current_selection[x].value, "del")
                    $('#singleList option[value="' + current_selection[x].value + '"]')[0].remove();
                    x--
                }
            }
        } else {
            var current_selectionF = document.getElementById('forwardList').options;
            var current_selectionR = document.getElementById('reverseList').options;
            var regex = $('#forward_pattern').val();
            var file_string = '';
            for (var x = 0; x < current_selectionF.length; x++) {
                if (current_selectionF[x].selected && current_selectionR[x].selected) {
                    file_string += current_selectionF[x].value + ',' + current_selectionR[x].value + ' | '
                    recordDelList("#forwardList", current_selectionF[x].value, "del")
                    recordDelList("#reverseList", current_selectionR[x].value, "del")
                    $('#forwardList option[value="' + current_selectionF[x].value + '"]')[0].remove();
                    $('#reverseList option[value="' + current_selectionR[x].value + '"]')[0].remove();
                    x--
                }
            }
        }
        if (file_string) {
            file_string = file_string.substring(0, file_string.length - 3);
            if (file_string != '') {
                if (regex == "") {
                    var name = file_string;
                } else {
                    var name = file_string.split(regex)[0];
                }
                var name = name.split(' | ')[0].split('.')[0];
                var input = createElement('input', ['id', 'type', 'class', 'value', 'onChange'], [name, 'text', '', name, 'updateNameTable(this)'])
                var button_div = createElement('div', ['class'], ['text-center'])
                var remove_button = createElement('button', ['class', 'type', 'onclick'], ['btn-sm btn-danger text-center', 'button', 'removeRowSelTable(this,\'' + collection_type + '\')']);
                var icon = createElement('i', ['class'], ['fa fa-times']);
                remove_button.appendChild(icon);
                button_div.appendChild(remove_button);
                var fileDir = $("#viewDir").data("fileDir")
                var mRunAmzKeyS3 = "";
                if (fileDir.match(/s3:/)){
                    mRunAmzKeyS3 = $("#viewDir").data("amzKey")
                }

                selectedSamplesTable.fnAddData([
                    input.outerHTML,
                    file_string,
                    fileDir,
                    button_div.outerHTML,
                    mRunAmzKeyS3
                ]);
            }
        }

    }



    //Sample Modal ENDs
    //##################

    //click on "use default" button
    $('#inputsTab').on('click', '#defValUse', function (e) {
        var button = $(this);
        var rowID = "";
        var gNumParam = "";
        var given_name = "";
        var qualifier = "";
        var sType = "";
        [rowID, gNumParam, given_name, qualifier, sType] = getInputVariables(button);
        var value = $(button).attr('defVal');
        var data = [];
        data.push({ name: "id", value: "" });
        data.push({ name: "name", value: value });
        var inputID = null;
        var url=null, urlzip=null, checkPath=null;
        //check database if file is exist, if not exist then insert
        checkInputInsert(data, gNumParam, given_name, qualifier, rowID, sType, inputID, null, url, urlzip, checkPath);
        button.css("display", "none");
        showHideSett(rowID)
        autoCheck()
    });
    //change on exec settings
    $(function () {
        $(document).on('keyup', '.form-control.execSetting', function () {
            var rowDiv = $(this).parent().parent();
            if (rowDiv) {
                var rowId = rowDiv.attr("id")
                if (rowId.match(/procGnum-(.*)/)) {
                    var checkId = rowId.match(/procGnum-(.*)/)[1];
                    var checkBoxId = "check-" + checkId
                    updateCheckBox("#" + checkBoxId, "true");
                    updateCheckBox('#exec_each', "true");
                }
            }
        });
    })
    //change on dropDown button
    $(function () {
        $(document).on('change', 'select[indropdown]', function () {
            var button = $(this);
            var value = $(this).val();
            var rowID = "";
            var gNumParam = "";
            var given_name = "";
            var qualifier = "";
            var sType = "";
            [rowID, gNumParam, given_name, qualifier, sType] = getInputVariables(button);
            var proPipeInputID = $('#' + rowID).attr('propipeinputid');
            // if proPipeInputID exist, then first remove proPipeInputID.
            if (proPipeInputID) {
                var removeInput = getValues({ "p": "removeProjectPipelineInput", id: proPipeInputID });
            }
            // insert into project pipeline input table
            if (value && value != "") {
                var data = [];
                data.push({ name: "id", value: "" });
                data.push({ name: "name", value: value });
                var inputID = null;
                var url=null, urlzip=null, checkPath=null;
                checkInputInsert(data, gNumParam, given_name, qualifier, rowID, sType, inputID, null, url, urlzip, checkPath);
            } else { // remove from project pipeline input table
                var removeInput = getValues({ "p": "removeProjectPipelineInput", id: proPipeInputID });
                removeSelectFile(rowID, qualifier);
            }
            checkReadytoRun();
        });
    });

    $(function () {
        $(document).on('change', '#mRunAmzKey', function () {
            checkReadytoRun();
        })
    });
    $(function () {
        $(document).on('change', '#chooseEnv', function () {
            //reset before autofill feature actived for #runCmd
            changeOnchooseEnv = true;
            $('#runCmd').val("");
            var [allProSett, profileData] = getJobData("both");
            var executor_job = profileData[0].executor_job;
            if (executor_job === 'ignite') {
                showHideColumnRunSett([1, 4, 5], "show")
                showHideColumnRunSett([1, 4], "hide")
            } else if (executor_job === 'local') {
                showHideColumnRunSett([1, 4, 5], "hide")
            } else {
                showHideColumnRunSett([1, 4, 5], "show")
            }
            if (executor_job === "slurm"){
                $('#eachProcessQueue').text('Partition');
                $('#allProcessQueue').text('Partition');
            }else {
                $('#eachProcessQueue').text('Queue');
                $('#allProcessQueue').text('Queue');
            }
            var profileTypeId = $('#chooseEnv').find(":selected").val();
            var patt = /(.*)-(.*)/;
            var proType = profileTypeId.replace(patt, '$1');
            var proId = profileTypeId.replace(patt, '$2');
            proTypeWindow = proType;
            proIdWindow = proId;
            $('#jobSettingsDiv').css('display', 'inline');
            fillForm('#allProcessSettTable', 'input', allProSett);
            selectAmzKey();
            checkShub()
            checkReadytoRun();
            //save run in change 
            saveRun();

        });
    });


    $('#inputFilemodal').on('show.bs.modal', function (e) {
        var button = $(e.relatedTarget);
        $(this).find('form').trigger('reset');
        $('#projectFileTable').DataTable().rows().deselect();
        $('.nav-tabs a[href="#importedFiles"]').trigger("click");
        selectMultiselect("#select-Collection", []);
        selectMultiselect("#select-Host", []);
        selectMultiselect("#select-Project", []);
        sampleTable.rows().deselect();
        var clickedRow = button.closest('tr');
        var rowID = clickedRow[0].id; //#inputTa-3
        var gNumParam = rowID.split("Ta-")[1];
        if (button.attr('id') === 'inputFileEnter') {
            $('#filemodaltitle').html('Select/Add Input File');
            $('#mIdFile').attr('rowID', rowID);
        } else if (button.attr('id') === 'inputFileEdit') {
            $('#filemodaltitle').html('Change Input File');
            $('#mIdFile').attr('rowID', rowID);
            var proPipeInputID = $('#' + rowID).attr('propipeinputid');
            $('#mIdFile').val(proPipeInputID);
            // Get the input id of proPipeInput;
            var proInputGet = getValues({ "p": "getProjectPipelineInputs", "id": proPipeInputID });
            if (proInputGet) {
                var input_id = proInputGet[0].input_id;
                var collection_id = proInputGet[0].collection_id;
                var collection_name = proInputGet[0].collection_name;
                if (collection_id && collection_id != "0" && collection_name) {
                    selectMultiselect("#select-Collection", [collection_name]);
                    sampleTable.rows({ search: 'applied' }).select();
                    $('.nav-tabs a[href="#importedFilesTab"]').tab('show');
                } else if (input_id) {
                    var inputGet = getValues({ "p": "getInputs", "id": input_id })[0];
                    if (inputGet) {
                        //insert data (input_id) into form
                        var formValues = $('#manualTab').find('input');
                        var keys = Object.keys(inputGet);
                        for (var i = 0; i < keys.length; i++) {
                            $(formValues[i]).val(inputGet[keys[i]]);
                        }
                    }
                }
            }
        }
    });
    $('#inputFilemodal').on('shown.bs.modal', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });


    getTableSamples = function (tableId) {
        var ret = {};
        var file_array = [];
        var warnUser = "";
        var table_data = window[tableId].fnGetData();
        var table_nodes = window[tableId].fnGetNodes();
        for (var y = 0; y < table_data.length; y++) {
            var name = $.trim(table_nodes[y].children[0].children[0].id)
            name = name.replace(/:/g, "_").replace(/,/g, "_").replace(/\$/g, "_").replace(/\!/g, "_").replace(/\</g, "_").replace(/\>/g, "_").replace(/\?/g, "_").replace(/\(/g, "-").replace(/\)/g, "-").replace(/\"/g, "_").replace(/\'/g, "_").replace(/\//g, "_").replace(/\\/g, "_");
            if (!name) {
                warnUser = 'Please fill all the filenames in the table.'
            }
            var files_used = table_data[y][1]
            file_array.push(name + " " + files_used)
        }
        ret.file_array = file_array
        ret.warnUser = warnUser
        return ret
    }

    checkOneCollection = function (selectedRows) {
        //get collection_id of first item. it can be space separated multiple ids
        var pushFeatureIntoArray = function (ar, column) {
            var vals = [];
            for (var i = 0; i < ar.length; i++) {
                vals.push(ar[i][column]);
            }
            return vals
        }
        var collectionIdAr = selectedRows[0].collection_id.split(",")
        var selRowsfileIdAr = [];
        selRowsfileIdAr = pushFeatureIntoArray(selectedRows, "id")
        for (var n = 0; n < collectionIdAr.length; n++) {
            var selColData = getValues({ "id": collectionIdAr[n], "p": "getCollectionFiles" })

            var selColfileIdAr = pushFeatureIntoArray(selColData, "id")
            var checkEq = checkArraysEqual(selColfileIdAr.sort(), selRowsfileIdAr.sort())
            if (checkEq === true) {
                return [collectionIdAr[n], selRowsfileIdAr]
                break;
            }
        }
        return [false, selRowsfileIdAr];
    }

    //xxxxxxxxxxx
    $('#inputFilemodal').on('click', '#savefile', function (e) {
        $('#inputFilemodal').loading({
            message: 'Working...'
        });
        e.preventDefault();
        var savetype = $('#mIdFile').val();
        var checkdata = $('#inputFilemodal').find("[searchTab='true'].active.tab-pane")[0].getAttribute('id');
        if (checkdata === 'importedFilesTab') {
            $('#inputFilemodal').loading("stop");
            var fillCollection = function (savetype, collection) {
                console.log(savetype)
                console.log(!savetype.length)
                console.log(collection)
                if (!savetype.length) { //add item
                    saveFileSetValModal(null, 'file', null, collection);
                } else {
                    editFileSetValModal(null, 'file', null, collection);
                }
                var insertFileProject = getValues({ p: "insertFileProject", collection_id: collection.collection_id, project_id:project_id });
                $("#sampleTable").DataTable().ajax.reload(null, false);
            }
            var selectedRows = sampleTable.rows({ selected: true }).data();
            if (selectedRows.length === 0) {
                showInfoModal("#infoModal", "#infoModalText", "None of the file is selected in the table. Please use checkboxes to select files.")
            } else if (selectedRows.length > 0) {
                //check if selected items belong to only one collection
                var collection_id = "";
                var selRowsfileIdAr = [];
                var checkOneCol = "";
                [checkOneCol, selRowsfileIdAr] = checkOneCollection(selectedRows)
                //if new collection required, ask for name
                if (!checkOneCol) {
                    $("#newCollectionModal").off();
                    $("#newCollectionModal").on('show.bs.modal', function (event) {
                        $(this).find('form').trigger('reset');
                    });
                    $('#newCollectionModal').on('click', '#saveNewCollect', function (e) {
                        e.preventDefault();
                        var newCollName = $('#newCollectionName').val();
                        if (newCollName != "") {
                            newCollName = newCollName.replace(/:/g, "_").replace(/,/g, "_").replace(/\$/g, "_").replace(/\!/g, "_").replace(/\</g, "_").replace(/\>/g, "_").replace(/\?/g, "_").replace(/\(/g, "_").replace(/\"/g, "_").replace(/\'/g, "_").replace(/\./g, "_").replace(/\//g, "_").replace(/\\/g, "_");
                            var collection_data = getValues({ p: "saveCollection", name: newCollName })
                            if (collection_data.id) {
                                collection_id = collection_data.id;
                                var savecollection = getValues({
                                    p: "insertFileCollection",
                                    file_array: selRowsfileIdAr,
                                    collection_id: collection_id
                                })
                                if (savecollection.id) {
                                    var collection = { collection_id: collection_id, collection_name: newCollName }
                                    fillCollection(savetype, collection)
                                    $("#sampleTable").DataTable().ajax.reload(null, false);
                                    $("#newCollectionModal").modal('hide');
                                    $('#inputFilemodal').modal('hide');
                                }
                            }
                        }
                    });
                    $("#newCollectionModal").modal('show');
                } else {
                    collection_id = checkOneCol;
                    var getcollection = getValues({ p: "getCollection", id: collection_id })
                    if (getcollection.length) {
                        if (getcollection[0].name) {
                            var collection = { collection_id: collection_id, collection_name: getcollection[0].name }
                            fillCollection(savetype, collection)
                            $('#inputFilemodal').modal('hide');
                        }
                    }
                }
            }
        } else {
            if (!savetype.length) { //add item
                if (checkdata === 'manualTab') {
                    var formValues = $('#manualTab').find('input');
                    var data = formValues.serializeArray(); // convert form to array
                    // check if name is entered
                    data[1].value = $.trim(data[1].value);
                    if (data[1].value !== '') {
                        saveFileSetValModal(data, 'file', null, null);
                        $('#inputFilemodal').loading("stop");
                        $('#inputFilemodal').modal('hide');
                    } else {
                        $('#inputFilemodal').loading("stop");
                        showInfoModal("#infoModal", "#infoModalText", "Please enter or select files from table to fill 'File Path' box.")
                    }
                } else if (checkdata === 'publicFileTab') {
                    var rows_selected = publicFileTable.column(0).checkboxes.selected();
                    if (rows_selected.length === 1) {
                        var input_id = rows_selected[0];
                        saveFileSetValModal(null, 'file', input_id, null);
                    }
                    $('#inputFilemodal').loading("stop");
                    $('#inputFilemodal').modal('hide');
                }
            } else { //edit item
                if (checkdata === 'manualTab') {
                    var formValues = $('#inputFilemodal').find('input');
                    var data = formValues.serializeArray(); // convert form to array
                    // check if file_path is entered 
                    data[1].value = $.trim(data[1].value);
                    if (data[1].value !== '') {
                        editFileSetValModal(data, 'file', null, null);
                        $('#inputFilemodal').loading("stop");
                        $('#inputFilemodal').modal('hide');
                    } else {
                        $('#inputFilemodal').loading("stop");
                        showInfoModal("#infoModal", "#infoModalText", "Please enter or select files from table to fill 'File Path' box.")
                    }
                } else if (checkdata === 'publicFileTab') {
                    var rows_selected = publicFileTable.column(0).checkboxes.selected();
                    if (rows_selected.length === 1) {
                        var input_id = rows_selected[0];
                        editFileSetValModal(null, 'file', input_id, null);
                        $('#inputFilemodal').loading("stop");
                        $('#inputFilemodal').modal('hide');
                    }
                }
            }
        }
        $('#inputFilemodal').loading("stop");
    });



    //clicking on top tabs of select files table
    $('a[data-toggle="tab"]').on('shown.bs.tab click', function (e) {
        // header fix of datatabes in add to files/values tab
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
        var activatedTab = $(e.target).attr("href")
        console.log(activatedTab)
        if (activatedTab === "#manualTab") {
            var projectRows = $('#projectListTable > tbody >');
            // if project is exist click on the first one to show files
            console.log(projectRows)
            if (projectRows && projectRows.length > 0) {
                $('#projectListTable > tbody > tr > td ').find('[projectid="' + project_id + '"]').trigger("click")
            }
        } else if (activatedTab === "#manualTabV") {
            var projectRows = $('#projectListTableVal > tbody >');
            console.log(projectRows)

            // if project is exist click on the first one to show files
            if (projectRows && projectRows.length > 0) {
                $('#projectListTableVal > tbody > tr > td ').find('[projectid="' + project_id + '"]').trigger("click")
            }
        } else if (activatedTab === "#publicFileTab") {
            var host = $('#chooseEnv').find(":selected").attr("host");
            if (host != undefined) {
                if (host != "") {
                    $("#publicFileTabWarn").html("")
                    $("#publicFileTable").show();
                    var table_id = "publicFileTable";
                    var ajax = { "host": host, "p": "getPublicFiles" }
                    $('#' + table_id).dataTable().fnDestroy();
                    createFileTable(table_id, ajax);
                }
            } else {
                $("#publicFileTabWarn").html("</br> Please select run environments to see public files.")
                $("#publicFileTable").hide();

            }
        } else if (activatedTab === "#publicValTab") {
            var host = $('#chooseEnv').find(":selected").attr("host");
            if (host != undefined) {
                if (host != "") {
                    $("#publicValTabWarn").html("")
                    $("#publicValTable").show();

                    var table_id = "publicValTable";
                    var ajax = { "host": host, "p": "getPublicValues" }
                    $('#' + table_id).dataTable().fnDestroy();
                    createFileTable(table_id, ajax);
                }
            } else {
                $("#publicValTabWarn").html("</br> Please select run environments to see public files.")
                $("#publicValTable").hide();

            }
        }
    });


    function createFileTable(table_id, ajax) {
        window[table_id] = $('#' + table_id).DataTable({
            //            scrollY: '42vh',
            "dom": '<"top"i>rt<"pull-left"f><"bottom"p><"clear">',
            "bInfo": false,
            "ajax": {
                url: "ajax/ajaxquery.php",
                data: ajax,
                "dataSrc": ""
            },
            "columns": [{
                "width": "25px",
                "data": "input_id",
                "checkboxes": {
                    'targets': 0,
                    'selectRow': true
                }
            }, {
                "data": "name"
            }, {
                "data": "date_modified",
                "width": "130px"
            }],
            'select': {
                'style': 'single'
            },
            'order': [[2, 'desc']]
        });

    }

    function createProjectListTable(table_id) {
        table_id = $('#' + table_id).DataTable({
            scrollY: '42vh',
            "pagingType": "simple",
            "dom": '<"top"i>rt<"pull-left"f><"bottom"p><"clear">',
            "bInfo": false,
            "searching": false,
            "ajax": {
                url: "ajax/ajaxquery.php",
                data: {
                    "p": "getProjects"
                },
                "dataSrc": ""
            },
            "columns": [{
                "data": "name",
                "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    $(nTd).html('<a class="clickproject" projectid="' + oData.id + '">' + oData.name + '</a>');
                }
            }],
            'select': {
                'style': 'single'
            }
        });
    }

    //clicking on rows of projectFileTable
    $('#projectFileTable').on('click', 'tr', function (event) {
        var a = $('#projectFileTable').dataTable().fnGetData(this);
        if (a) {
            if (a.name) {
                var name = a.name;
                $("#mFilePath").val(name)
            }
        }
    });
    //clicking on rows of projectValTable
    $('#projectValTable').on('click', 'tr', function (event) {
        var a = $('#projectValTable').dataTable().fnGetData(this);
        if (a) {
            if (a.name) {
                var name = a.name;
                $("#mValName").val(name)
            }
        }
    });

    //left side project list table on add File/value modals
    createProjectListTable('projectListTable');
    createProjectListTable('projectListTableVal');

    //add file modal projectListTable click on project name
    $('#projectListTable').on('click', 'td', function (e) {
        var sel_project_id = $(this).children().attr("projectid");
        var table_id = "projectFileTable";
        var ajax = { "project_id": sel_project_id, "p": "getProjectFiles" }
        $('#' + table_id).dataTable().fnDestroy();
        createFileTable(table_id, ajax);
    });

    //add val modal projectListTableVal click on project name
    $('#projectListTableVal').on('click', 'td', function (e) {
        var sel_project_id = $(this).children().attr("projectid");
        var table_id = "projectValTable";
        var ajax = { "project_id": sel_project_id, "p": "getProjectValues" }
        $('#' + table_id).dataTable().fnDestroy();
        createFileTable(table_id, ajax);
    });

    $('#inputsTab').on('click', '#inputDelDelete, #inputValDelete', function (e) {
        var clickedRow = $(this).closest('tr');
        var rowID = clickedRow[0].id; //#inputTa-3
        var gNumParam = rowID.split("Ta-")[1];
        var proPipeInputID = $('#' + rowID).attr('propipeinputid');
        var removeInput = getValues({ "p": "removeProjectPipelineInput", id: proPipeInputID });
        var qualifier = $('#' + rowID + ' > :nth-child(4)').text();
        removeSelectFile(rowID, qualifier);
        checkReadytoRun();
    });


    $('#inputValmodal').on('show.bs.modal', function (e) {
        var button = $(e.relatedTarget);
        $(this).find('form').trigger('reset');
        $('#projectValTable').DataTable().rows().deselect();
        $('.nav-tabs a[href="#manualTabV"]').trigger("click");
        var clickedRow = button.closest('tr');
        var rowID = clickedRow[0].id; //#inputTa-3
        var gNumParam = rowID.split("Ta-")[1];
        if (button.attr('id') === 'inputValEnter') {
            $('#valmodaltitle').html('Add Value');
            $('#mIdVal').attr('rowID', rowID);
        } else if (button.attr('id') === 'inputValEdit') {
            $('#valmodaltitle').html('Edit Value');
            $('#mIdVal').attr('rowID', rowID);
            var proPipeInputID = $('#' + rowID).attr('propipeinputid');
            $('#mIdVal').val(proPipeInputID);
            // Get the input id of proPipeInput;
            var proInputGet = getValues({ "p": "getProjectPipelineInputs", "id": proPipeInputID });
            if (proInputGet) {
                var input_id = proInputGet[0].input_id;
                var inputGet = getValues({ "p": "getInputs", "id": input_id })[0];
                if (inputGet) {
                    //insert data into form
                    var formValues = $('#inputValmodal').find('input');
                    var keys = Object.keys(inputGet);
                    for (var i = 0; i < keys.length; i++) {
                        $(formValues[i]).val(inputGet[keys[i]]);
                    }
                }
            }
        }
    });
    $('#inputValmodal').on('shown.bs.modal', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();

    });

    $('#inputValmodal').on('click', '#saveValue', function (e) {
        e.preventDefault();
        $('#inputValmodal').loading({
            message: 'Working...'
        });
        var savetype = $('#mIdVal').val();
        var checkdata = $('#inputValmodal').find('.active.tab-pane')[0].getAttribute('id');
        if (!savetype.length) { //add item
            if (checkdata === 'manualTabV') {
                var formValues = $('#inputValmodal').find('input');
                var data = formValues.serializeArray(); // convert form to array
                // check if name is entered
                data[1].value = $.trim(data[1].value);
                if (data[1].value !== '') {
                    saveFileSetValModal(data, 'val', null, null);
                    $('#inputValmodal').loading("stop");
                    $('#inputValmodal').modal('hide');
                } else {
                    $('#inputValmodal').loading("stop");
                    showInfoModal("#infoModal", "#infoModalText", "Please enter or select values from table to fill 'Value' box.")
                }
            } else if (checkdata === 'publicValTab') {
                var rows_selected = publicValTable.column(0).checkboxes.selected();
                if (rows_selected.length === 1) {
                    var input_id = rows_selected[0];
                    saveFileSetValModal(null, 'val', input_id, null);
                }
                $('#inputValmodal').loading("stop");
                $('#inputValmodal').modal('hide');
            }
        } else { //edit item
            if (checkdata === 'manualTabV') {
                var formValues = $('#inputValmodal').find('input');
                var data = formValues.serializeArray(); // convert form to array
                // check if file_path is entered 
                data[1].value = $.trim(data[1].value);
                if (data[1].value !== '') {
                    editFileSetValModal(data, 'val', null, null);
                    $('#inputValmodal').loading("stop");
                    $('#inputValmodal').modal('hide');
                } else {
                    $('#inputValmodal').loading("stop");
                    showInfoModal("#infoModal", "#infoModalText", "Please enter or select values from table to fill 'Value' box.")
                }
            } else if (checkdata === 'publicValTab') {
                var rows_selected = publicValTable.column(0).checkboxes.selected();
                if (rows_selected.length === 1) {
                    var input_id = rows_selected[0];
                    editFileSetValModal(null, 'val', input_id, null);
                    $('#inputValmodal').loading("stop");
                    $('#inputValmodal').modal('hide');
                }
            }
        }
    });



    $('#confirmModal').on('show.bs.modal', function (e) {
        var button = $(e.relatedTarget);
        $('#confirmModal').data("buttonID", button.attr('id'));
        if (button.attr('id') === 'deleteRun' || button.attr('id') === 'delRun') {
            $('#confirmModalText').html('Are you sure you want to delete this run?');
        } else if (button.attr('id') === 'deleteSample') {
            var selRows = sampleTable.rows({ selected: true }).data();
            var selRowsName =[];
            for (var i = 0; i < selRows.length; i++) {
                selRowsName.push(selRows[i].name);
            }
            var selRowsTxt = selRowsName.join("<br/>");
            $('#confirmModalText').html('Are you sure you want to delete selected '+selRows.length+' file(s)?<br/><br/>File List:<br/>' + selRowsTxt);
        }
    });

    $('#confirmModal').on('click', '#deleteBtn', function (e) {
        e.preventDefault();
        var buttonID = $('#confirmModal').data("buttonID");
        if (buttonID === 'deleteRun' || buttonID === 'delRun'){
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    id: project_pipeline_id,
                    p: "removeProjectPipeline"
                },
                async: true,
                success: function (s) {
                    window.location.replace("index.php?np=2&id=" + project_id);
                },
                error: function (errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });  
        } else if (buttonID === 'deleteSample') {
            var selRows = sampleTable.rows({ selected: true }).data();
            var selRowsId =[];
            for (var i = 0; i < selRows.length; i++) {
                selRowsId.push(selRows[i].id);
            }
            if (selRowsId.length >0){
                $.ajax({
                    type: "POST",
                    url: "ajax/ajaxquery.php",
                    data: {
                        file_array: selRowsId,
                        p: "removeFile"
                    },
                    async: true,
                    success: function (s) {
                        if (s.length >0){
                            var removedCollection = s;
                            for (var i = 0; i < removedCollection.length; i++) {
                                removeCollectionFromInputs(removedCollection[i]);
                            }
                        }
                        $("#sampleTable").DataTable().ajax.reload(null, false);
                        $("#detailsOfFileDiv").css("display","none")
                    },
                    error: function (errorThrown) {
                        alert("Error: " + errorThrown);
                    }
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
                if (typeof(arg) != "string") {
                    plupload.each(arg, function(value, key) {
                        // Convert items in File objects to human readable form
                        if (arg instanceof plupload.File) {
                            // Convert status to human readable
                            switch (value) {
                                case plupload.QUEUED:
                                    value = 'QUEUED';
                                    break;
                                case plupload.UPLOADING:
                                    value = 'UPLOADING';
                                    break;
                                case plupload.FAILED:
                                    value = 'FAILED';
                                    break;
                                case plupload.DONE:
                                    value = 'DONE';
                                    break;
                            }
                        }

                        if (typeof(value) != "function") {
                            row += (row ? ', ' : '') + key + '=' + value;
                        }
                    });

                    str += row + " ";
                } else {
                    str += arg + " ";
                }
            });
            var log = $('#pluploaderLog');
            log.append(str + "\n");
            log.scrollTop(log[0].scrollHeight);
        }

        function getTransferedFiles(){
            var done = 0
            if (window["plupload_transfer_obj"]){
                var obj = window["plupload_transfer_obj"];
                if (!jQuery.isEmptyObject(obj)){
                    $.each(obj, function (el) {
                        if (window["plupload_transfer_obj"][el]["status"]){
                            if (window["plupload_transfer_obj"][el]["status"] == "done"){
                                done ++
                            }
                        }
                    });
                }
            }
            return done;
        }
        function updateTransferedFiles(){
            var uploader = $("#pluploader").pluploadQueue()
            var totalFile=0;
            var transferedFile=0;
            var totalFile=uploader.files.length;
            var transferedFile=getTransferedFiles();
            console.log(totalFile)
            console.log(totalFile > 0)
            console.log(transferedFile)
            console.log(transferedFile == totalFile)
            if (transferedFile && totalFile){
                if (totalFile > 0 && transferedFile == totalFile){
                    $("#uploadSucDiv").css("display","inline");
                } 
            }
            if (totalFile){
                $('span.plupload_transfer_status').html('  Transfered '+transferedFile+'/'+totalFile+' files');

            }

        }
        //interval will decide the check period
        function checkRsyncTimer(up,fileName, fileId, interval) {
            window['interval_rsyncStatus_' + fileId] = setInterval(function () {
                runRsyncCheck(up, fileName, fileId);
            }, interval);
        }

        function upd_plupload_transfer_obj(fileId,status,transfer,rsyncPid){
            if (typeof window["plupload_transfer_obj"] == "undefined"){
                window["plupload_transfer_obj"] = {}
            }
            if (typeof window["plupload_transfer_obj"][fileId] == "undefined"){
                window["plupload_transfer_obj"][fileId] = {}
            }
            if (status){
                window["plupload_transfer_obj"][fileId]["status"]=status
                if (status == "error"){
                    $("#" + fileId).attr('class', "plupload_failed").find('a').css('display', 'block');
                } else if (status == "uploading"){
                    $("#" + fileId).attr('class', "plupload_rsync").find('a').css('display', 'block');
                } else if (status == "done"){
                    $("#" + fileId).attr('class', "plupload_done").find('a').css('display', 'block');
                }  
            }   
            if (transfer){
                window["plupload_transfer_obj"][fileId]["transfer"]=transfer
                $("#" + fileId +" > .plupload_file_transfer").text(transfer)
            }
            if (rsyncPid){
                window["plupload_transfer_obj"][fileId]["rsync"]=rsyncPid
            }
        }
        function runRsyncCheck(up, fileName, fileId) {
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    filename: fileName,
                    p: "getRsyncStatus"
                },
                async: true,
                success: function (s) {
                    if (s){
                        console.log(s)
                        log('[TransferFile]', s);
                        if (s.match(/cannot create directory/)){
                            upd_plupload_transfer_obj(fileId,"error","Error","");
                            clearInterval(window['interval_rsyncStatus_' + fileId]);
                        } else {
                            if (s.match(/\d+\%/)) {
                                var list = s.match(/\d+\%/g);
                                if (list.length >0){
                                    var percent = list[list.length-1];
                                    upd_plupload_transfer_obj(fileId,"uploading",percent,"");
                                    if (percent.match(/100%/)) {
                                        upd_plupload_transfer_obj(fileId,"done",percent,"");
                                        clearInterval(window['interval_rsyncStatus_' + fileId]);
                                        log('[TransferFile]', "Done");
                                    }
                                } else {
                                    var percent = "0"
                                    upd_plupload_transfer_obj(fileId,"uploading",percent,"");

                                }
                            } 
                        }
                        updateTransferedFiles()
                    }
                },
                error: function (errorThrown) {
                    console.log("Error: " + errorThrown);
                }
            });
        }

        var initPlupload = function (){

            $("#pluploader").pluploadQueue({
                runtimes : 'html5,html4', //flash,silverlight
                url : "ajax/upload.php",
                chunk_size : '3mb', 
                // to enable chunk_size larger than 2mb: "ajax/.htaccess file should have "php_value post_max_size 12M", "php_value upload_max_filesize 12M"
                // test for 320mb file : 
                // chunk_size=10mb :take 130sec
                // chunk_size=3-4-5mb :take 80sec
                // chunk_size=1-2mb :take 110sec
                max_retries: 3,
                unique_names : true,
                multiple_queues : true,
                rename : true,
                dragdrop: true,
                multipart : true,
                //multipart_params : {'target_dir': "old"},
                filters : {
                    // Maximum file size
                    max_file_size : '2gb'
                },
                // PreInit events, bound before any internal events
                preinit : {
                    Init: function(up, info) {
                        log('[Init]', 'Info:', info, 'Features:', up.features);
                    },
                    UploadFile: function(up, file) {
                        log('[UploadFile]', file);
                        //                        up.stop();
                        // You can override settings before the file is uploaded
                        // up.setOption('url', 'upload.php?id=' + file.id);
                    }
                },
                // Post init events, bound after the internal events
                init : {
                    PostInit: function(up) {
                        // Called after initialization is finished and internal event handlers bound
                        log('[PostInit]');
                    },
                    Browse: function(up) {
                        // Called when file picker is clicked
                        log('[Browse]');
                    },
                    Refresh: function(up) {
                        // Called when the position or dimensions of the picker change
                        log('[Refresh]');
                        updateTransferedFiles()
                    },
                    StateChanged: function(up) {
                        // Called when the state of the queue is changed
                        log('[StateChanged]', up.state == plupload.STARTED ? "STARTED" : "STOPPED");
                    },
                    QueueChanged: function(up) {
                        // Called when queue is changed by adding or removing files
                        log('[QueueChanged]');
                    },
                    OptionChanged: function(up, name, value, oldValue) {
                        // Called when one of the configuration options is changed
                        log('[OptionChanged]', 'Option Name: ', name, 'Value: ', value, 'Old Value: ', oldValue);
                    },
                    BeforeUpload: function(up, file) {
                        //Called right before the upload for a given file starts, can be used to cancel it if required
                        log('[BeforeUpload]', 'File: ', file);
                        updateTransferedFiles()
                        var target_dir = $("#target_dir").val();
                        var run_env = $('#chooseEnv').find(":selected").val();
                        if (target_dir && run_env){
                            up.settings.multipart_params.target_dir = target_dir;
                            up.settings.multipart_params.run_env = run_env;
                        } 
                    },
                    UploadProgress: function(up, file) {
                        // Called while file is being uploaded
                        log('[UploadProgress]', 'File:', file, "Total:", up.total);
                    },
                    FileFiltered: function(up, file) {
                        // Called when file successfully files all the filters
                        log('[FileFiltered]', 'File:', file);
                    },
                    FilesAdded: function(up, files) {
                        // Called when files are added to queue
                        log('[FilesAdded]');
                        //get files in the target directory
                        var target_dir = $("#target_dir").val();
                        var amazon_cre_id = "";
                        var dirList = getValues({ "p": "getLsDir", dir: target_dir, profileType: proTypeWindow, profileId: proIdWindow, amazon_cre_id:amazon_cre_id });
                        console.log(dirList)
                        var fileArr = [];
                        var errorAr = [];
                        if (dirList) {
                            dirList = $.trim(dirList)
                            fileArr = dirList.split('\n');
                            errorAr = fileArr.filter(line => line.match(/ls:/));
                            fileArr = fileArr.filter(line => !line.match(/:/));
                        }
                        var removedFiles = [];
                        var dupFiles = [];
                        var emptyFiles = [];
                        console.log(fileArr)
                        //check if file is found in the targetdir -> remove file and give warning
                        plupload.each(files, function(file) {
                            //remove files that has no size
                            if (file.size == 0){
                                emptyFiles.push(file.name)
                                up.removeFile(file); 
                            }
                            //remove duplicate file
                            var upfile = $.grep(up.files, function(v) { return v.name === file.name });
                            if (upfile.length > 0){
                                if (upfile[0] != file){
                                    dupFiles.push(file.name)
                                    up.removeFile(file);
                                }
                            }
                            //remove file found in the remote host
                            if ($.inArray(file.name, fileArr) !== -1){
                                removedFiles.push(file.name)
                                up.removeFile(file);
                            }
                            log('  File:', file);
                        });

                        var delRowsTxt = ""
                        var dupFilesTxt = ""
                        var emptyFilesTxt = ""
                        if (removedFiles.length>0){
                            var delRowsTxt = "Following file(s) already found in the target directory. Therefore, they removed from download queue.<br/><br/>File List:<br/>" + removedFiles.join("<br/>") + "<br/><br/>";
                        }
                        if (dupFiles.length >0){
                            var dupFilesTxt = "Following file(s) already found in the queue list. <br/><br/>File List:<br/>" + dupFiles.join("<br/>") + "<br/><br/>";
                        }
                        if (emptyFiles.length >0){
                            var emptyFilesTxt = "Following file(s) are empty and removed from the download queue. <br/><br/>File List:<br/>" + emptyFiles.join("<br/>") + "<br/><br/>";
                        }
                        if (removedFiles.length>0 || dupFiles.length >0 || emptyFiles.length >0){
                            showInfoModal("#infoModal", "#infoModalText",  delRowsTxt + dupFilesTxt + emptyFilesTxt)
                        }
                    },
                    FilesRemoved: function(up, files) {
                        // Called when files are removed from queue
                        log('[FilesRemoved]');
                        plupload.each(files, function(file) {
                            log('  File:', file);
                        });
                    },
                    FileUploaded: function(up, file, info) {
                        // Called when file has finished uploading
                        log('[FileUploaded] File:', file, "Info:", info);
                        console.log(file)
                        console.log(info)
                        console.log(up)
                        var fileName = file.name
                        var fileId = file.id
                        var fileState = file.state //5==done
                        if (fileState == 5 && fileName){
                            if (info){
                                console.log(info)
                                if (info.response){
                                    console.log(info.response)
                                    if (IsJsonString(info.response)) {
                                        var json = JSON.parse(info.response)
                                        console.log(json)
                                        if (json) {
                                            if (json.rsync_log){
                                                var pid = $.trim(json.rsync_log)
                                                upd_plupload_transfer_obj(fileId,"","",pid);
                                            }
                                            if (!json.error) {
                                                //start reading log from rsync each 10 sec.
                                                checkRsyncTimer(up,fileName,fileId, 10000);
                                            }
                                        }
                                    }
                                }
                            }

                        }
                    },
                    ChunkUploaded: function(up, file, info) {
                        // Called when file chunk has finished uploading
                        log('[ChunkUploaded] File:', file, "Info:", info);
                    },
                    UploadComplete: function(up, files) {
                        // Called when all files are either uploaded or failed
                        log('[UploadComplete]');
                    },
                    Destroy: function(up) {
                        // Called when uploader is destroyed
                        log('[Destroy] ');
                    },
                    Error: function(up, args) {
                        // Called when error occurs
                        log('[Error] ', args);
                    }
                }
            });
        }
        initPlupload();

        $('#addFileModal').on('click', '.plupload_start_dummy', function (e) {
            var target_dir = $("#target_dir").val();
            var run_env = $('#chooseEnv').find(":selected").val();
            var warning = ""
            if (target_dir && run_env){
                var  chkRmDirWritable = getValues({ p: "chkRmDirWritable", dir: target_dir, run_env:run_env  });
                console.log(chkRmDirWritable)
                if (chkRmDirWritable.match(/writeable/)){
                    $('.plupload_start').trigger("click");
                } else {
                    warning += "Write permission denied for your target directory." 
                    showInfoModal("#infoModal", "#infoModalText", warning)
                }
            } else {
                if (!target_dir && !run_env){
                    warning += "Please choose your run environment and enter target directory and try again." 
                } else if (!target_dir){
                    warning += "Please enter target directory and try again." 
                } else if (!run_env){
                    warning += "Please choose your run environment and try again." 
                }
                showInfoModal("#infoModal", "#infoModalText", warning)
            }
        });

        //xxxx
        $('#addFileModal').on('click', '.plupload_rsync > .plupload_file_action > a', function (e) {
            var clickedFileUID = $(e.target).closest('li').attr("id");
            var uploader = $("#pluploader").pluploadQueue();
            var files = uploader.files;
            var fileName = "";
            for (var i = 0; i < files.length; i++) {
                var fileID  = files[i].uid
                if (fileID == clickedFileUID){
                    fileName  = files[i].name
                    break;
                }
            }
            var target_dir = $("#target_dir").val();
            var run_env = $('#chooseEnv').find(":selected").val();
            if (fileName && target_dir && run_env){
                var retryRsync = getValues({ p: "retryRsync", dir: target_dir, run_env:run_env, filename: fileName  });
                console.log(retryRsync)
            }
        });

        $('#addFileModal').on('click', '#pluploaderReset', function (e) {
            var uploader = $("#pluploader").pluploadQueue();
            var files = uploader.files;
            for (var i = 0; i < files.length; i++) {
                var fileID  = files[i].uid
                var fileName  = files[i].name
                if (window["plupload_transfer_obj"]){
                    if (window["plupload_transfer_obj"][fileID]){
                        if (window['interval_rsyncStatus_' + fileID]){
                            clearInterval(window['interval_rsyncStatus_' + fileID]);
                        }
                        if (window["plupload_transfer_obj"][fileID]["status"] && window["plupload_transfer_obj"][fileID]["rsync"]){
                            if (window["plupload_transfer_obj"][fileID]["status"] != "done"){
                                var killRsync =  getValues({ p: "resetUpload", filename: fileName });
                            }
                        }
                    }
                }
            }
            uploader.splice(0);
            uploader.destroy();
            window["plupload_transfer_obj"]={}
            initPlupload();
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


    $('#addFileModal').on('click', '#showHostFiles', function (e) {
        var target_dir = $('#target_dir').val();
        $('#file_dir').val(target_dir);
        $('#viewDirBut').trigger("click");
        $('#addFileModal').find('.nav-tabs a[href="#hostFiles"]').tab('show');
    });


    //### pluplouder ends


    //######### copy/move runs
    $('#confirmDuplicate').on('click', '#moveRunBut', function (e) {
        var new_project_id = $('#userProject').val();
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: {
                old_project_id: project_id,
                new_project_id: new_project_id,
                project_pipeline_id: project_pipeline_id,
                p: "moveRun"
            },
            async: true,
            success: function (s) {
                setTimeout(function () { window.location.replace("index.php?np=3&id=" + project_pipeline_id); }, 0);
            },
            error: function (errorThrown) {
                alert("Error: " + errorThrown);
            }
        });
    });
    $('#confirmDuplicate').on('click', '#copyRunBut', function (e) {
        confirmNewRev = false;
        saveRun();
    });
    $('#confirmDuplicate').on('click', '#duplicateKeepBtn', function (e) {
        confirmNewRev = false;
        saveRun();
    });
    $('#confirmDuplicate').on('click', '#duplicateNewBtn', function (e) {
        confirmNewRev = true;
        saveRun();
    });


    $('#projectmodal').on('show.bs.modal', function (event) {
        $(this).find('form').trigger('reset');
    });

    $('#projectmodal').on('click', '#saveproject', function (event) {
        event.preventDefault();
        var projectName = $('#mProjectName').val();
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: {name:projectName, summary:"", p:"saveProject"},
            async: true,
            success: function (s) {
                refreshProjectDropDown("#userProject");
                $("#userProject").val(s.id);
                $('#projectmodal').modal('hide');
            },
            error: function (errorThrown) {
                alert("Error: " + errorThrown);
            }
        });
    });

    //######### copy/move runs end

    $(function () {
        $(document).on('click', '.updateIframe', function (event) {
            var href = $(this).attr("href");
            var iframe = $(href).find("iframe");
            //update iframe in case its a txt file
            if (iframe && iframe.attr("src") && !href.match(/_html/)) {
                iframe.attr('src', "");
                setTimeout(function () { iframe.attr("src", iframe.attr("fillsrc")) }, 100);
            }
            //load iframe when first time it is clicked
            if (iframe && iframe.attr("fillsrc") && !iframe.attr("src")) {
                iframe.attr("src", iframe.attr("fillsrc"))
            }
        })
    });




    //$(function () { allows to trigger when a.reportFile added later to DOM
    $(function () {
        $(document).on('shown.bs.tab click', 'a.reportFile', function (event) {
            var href = $(this).attr("href");
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            //check if div is empty
            if (!$.trim($(href).html()).length) {
                var uuid = $("#runVerReport").val();
                var visType = $(this).attr("visType");
                var filePath = $(this).attr("filepath");
                var split = filePath.split("/")
                var filename = "";
                var dir = "";
                if (split.length > 1) {
                    filename = split[split.length - 1];
                    dir = filePath.substring(0, filePath.indexOf(filename));
                }
                console.log(dir )
                var fileid = $(this).attr("fileid");
                var pubWebPath = $("#basepathinfo").attr("pubweb");
                var debrowserUrl = $("#basepathinfo").attr("debrowser");
                var bindEveHandlerIcon = function (fileid) {
                    $('[data-toggle="tooltip"]').tooltip();
                    $('#fullscr-' + fileid).on('click', function (event) {
                        var iconClass = $(this).children().attr("class");
                        if (iconClass == "fa fa-expand") {
                            $(this).children().attr("class", "fa fa-compress")
                            toogleFullSize(this, "expand");
                        } else {
                            $(this).children().attr("class", "fa fa-expand")
                            toogleFullSize(this, "compress");
                        }
                    });
                    $('#downUrl-' + fileid).on('click', function (event) {
                        var fileid = $(this).attr("fileid")
                        var filename = $("#" + fileid).attr("filename")
                        var filepath = $("#" + fileid).attr("filepath")
                        var a = document.createElement('A');
                        var url = pubWebPath + "/" + uuid + "/pubweb/" + filepath
                        download_file(url, filename);
                    });
                    $('#blankUrl-' + fileid).on('click', function (event) {
                        var fileid = $(this).attr("fileid")
                        var filename = $("#" + fileid).attr("filename")
                        var filepath = $("#" + fileid).attr("filepath")
                        var url = pubWebPath + "/" + uuid + "/pubweb/" + filepath
                        var w = window.open();
                        w.location = url;
                    });

                }
                var getHeaderIconDiv = function (fileid, visType) {
                    var blankUrlIcon = "";
                    var downloadIcon = "";
                    if (visType !== "table-percent" && visType !== "table" && visType !== "debrowser") {
                        blankUrlIcon = `<li role="presentation"><a fileid="` + fileid + `" id="blankUrl-` + fileid + `" data-toggle="tooltip" data-placement="bottom" data-original-title="Open in a New Window"><i style="font-size: 18px;" class="fa fa-external-link"></i></a></li>`;
                    }
                    if (visType !== "debrowser") {
                        downloadIcon = `<li role="presentation"><a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
<i style="font-size: 18px;" class="fa fa-download"></i> <span class="caret"></span></a>
<ul class="dropdown-menu dropdown-menu-right">
<li><a fileid="` + fileid + `" id="downUrl-` + fileid + `" href="#">Download</a></li>
</ul>
</li>`;
                    }
                    var content = `<ul style="float:inherit"  class="nav nav-pills panelheader">
` + blankUrlIcon + `
<li role="presentation"><a fileid="` + fileid + `" id="fullscr-` + fileid + `" data-toggle="tooltip" data-placement="bottom" data-original-title="Toogle Full Screen"><i style="font-size: 18px;" class="fa fa-expand"></i></a></li>` +
                        downloadIcon +
                        `</ul>`
                    var wrapDiv = '<div id="' + fileid + '-HeaderIconDiv" style="float:right; height:35px; width:100%;">' + content + '</div>';
                    return wrapDiv;
                }
                if (visType == "table" || visType == "table-percent") {
                    var headerStyle = "";
                    var tableStyle = "";
                    if (visType == "table-percent"){
                        headerStyle = "white-space: nowrap;";
                    } else {
                        tableStyle = "white-space: nowrap; table-layout:fixed;";
                    }
                    var contentDiv = getHeaderIconDiv(fileid, visType) + '<div style="margin-left:15px; margin-right:15px; margin-bottom:15px; overflow-x:auto; width:calc(100% - 35px);" class="table-responsive"><table style="'+headerStyle+' border:none;  width:100%;" class="table table-striped table-bordered" cellspacing="0"  dir="' + dir + '" filename="' + filename + '" filepath="' + filePath + '" id="' + fileid + '"><thead style="'+tableStyle+'" "></thead></table></div>';
                    $(href).append(contentDiv)
                    var data = getValues({ p: "getFileContent", uuid: uuid, filename: "pubweb/" + filePath });
                    if (visType == "table-percent") {
                        //by default based on second column data, calculate percentages for each row
                        data = tsvPercent(data)
                    }
                    var fixHeader = true;
                    var dataTableObj = tsvConvert(data, "json2", fixHeader)
                    //speed up the table loading
                    dataTableObj.deferRender = true
                    dataTableObj.scroller = true
                    dataTableObj.scrollCollapse = true
                    dataTableObj.scrollY = 395
                    dataTableObj.scrollX = true
                    dataTableObj.sScrollX = true
                    dataTableObj.columnDefs = [{"defaultContent": "-","targets": "_all"}]; //hides undefined error
                    $("#" + fileid).DataTable(dataTableObj);
                    bindEveHandlerIcon(fileid)
                } else if (visType == "rmarkdown") {
                    var contentDiv = '<div style="width:100%;" dir="' + dir + '" filename="' + filename + '" filepath="' + filePath + '" id="' + fileid + '"></div>';
                    $(href).append(contentDiv)
                    var data = getValues({ p: "getFileContent", uuid: uuid, filename: "pubweb/" + filePath });
                    $("#" + fileid).rMarkEditor({
                        ajax: {
                            url: "ajax/ajaxquery.php",
                            text: data,
                            uuid: uuid,
                            dir: dir,
                            filename: filename,
                            pubWebPath: pubWebPath
                        },
                        editorWidth: "60%",
                        reportWidth: "40%",
                        height: "565px",
                        theme: "monokai" //tomorrow
                    });
                } else if (visType == "html" || visType == "pdf" || visType == "text") {
                    var link = pubWebPath + "/" + uuid + "/" + "pubweb" + "/" + filePath;
                    if (visType == "html" || visType == "text") {
                        var iframe = '<iframe frameborder="0"  style="width:100%; height:100%;" src="' + link + '"></iframe>';
                    } else if (visType == "pdf") {
                        var iframe = '<object style="width:100%; height:100%;"  data="' + link + '" type="application/pdf"><embed src="' + link + '" type="application/pdf" /></object>';
                    }
                    var contentDiv = getHeaderIconDiv(fileid, visType) + '<div style="width:100%; height:calc(100% - 35px);" dir="' + dir + '" filename="' + filename + '" filepath="' + filePath + '" id="' + fileid + '">' + iframe + '</div>';
                    $(href).append(contentDiv);
                    bindEveHandlerIcon(fileid)
                } else if (visType == "debrowser") {
                    var filePathJson = getValues({ p: "callDebrowser", dir: dir, uuid: uuid, filename: filename });
                    var link = encodeURIComponent(pubWebPath + "/" + uuid + "/" + "pubweb" + "/" + filePathJson);
                    var debrowserlink = debrowserUrl + '/debrowser/R/?jsonobject=' + link;
                    console.log(debrowserlink)
                    var iframe = '<iframe id="deb-' + fileid + '" frameborder="0"  style="width:100%; height:100%;" src="' + debrowserlink + '"></iframe>';
                    var contentDiv = getHeaderIconDiv(fileid, visType) + '<div style="width:100%; height:calc(100% - 35px);" dir="' + dir + '" filename="' + filename + '" filepath="' + filePath + '" id="' + fileid + '">' + iframe + '</div>';
                    $(href).append(contentDiv);
                    bindEveHandlerIcon(fileid)

                    //                    $("#deb-" + fileid).load(function () {
                    //                        $("#deb-" + fileid).loading('stop');
                    //                    });
                    //                    $("#deb-" + fileid).loading('start');
                }
            }
        })
    });

    //left tab-pane collapse
    //fix dataTable column width in case, width of the page is changed while panel closed 
    $(function () {
        $(document).on('shown.bs.collapse', '.tab-pane', function (event) {
            $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
        });
    });
    //main row panel collapse
    $(function () {
        $(document).on('shown.bs.collapse', '.collapseRowBody', function (event) {
            $(this).find("li.active > a").trigger("click");
        });
    });

    //################################
    // --rMarkEditor jquery plugin --
    //################################

    (function ($) {
        $.fn.rMarkEditor = function (options) {
            var settings = $.extend({
                // default values.
                height: "500px",
                heightIconBar: "35px"
            }, options);
            var elems = $(this);
            elems.css("width", "100%")
            elems.css("height", "100%")
            var elemsID = $(this).attr("id");
            var getEditorIconDiv = function () {
                return `<ul style="float:inherit" class="nav nav-pills rmarkeditor">
<li role="presentation"><a class="rmarkeditorrun" data-toggle="tooltip" data-placement="bottom" data-original-title="Run Script"><i style="font-size: 18px;" class="fa fa-play"></i></a></li>
<li role="presentation"><a class="rmarkeditorsaveas" data-toggle="tooltip" data-placement="bottom" data-original-title="Save As">
<span class="glyphicon-stack">
<i class="fa fa-pencil glyphicon-stack-3x"></i>
<i style="font-size: 18px;" class="fa fa-save glyphicon-stack-1x"></i>
</span>
</a></li>
<li role="presentation"><a class="rmarkeditorsave" data-toggle="tooltip" data-placement="bottom" data-original-title="Save"><i style="font-size: 18px;" class="fa fa-save"></i></a></li>
<li role="presentation"><a class="rmarkeditorsett" data-toggle="tooltip" data-placement="bottom" data-original-title="Settings"><i style="font-size: 18px;" class="fa fa-gear"></i></a></li>
</ul>`
            }
            var getReportIconDiv = function () {
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


            var getDiv = function (settings, outputHtml) {
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
            var createEditor = function (settings) {
                var editorId = elemsID + "-editor";
                window[editorId] = ace.edit(editorId);
                window[editorId].setTheme("ace/theme/" + settings.theme);
                window[editorId].getSession().setMode("ace/mode/r");
                window[editorId].setFontSize("14px");
                window[editorId].$blockScrolling = Infinity;
                window[editorId].setValue(settings.ajax.text);
            }
            var createModal = function () {
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

            var progress = function (value) {
                var width; //percent
                var rate = 5;
                var n = 0;
                var bar = $("#" + elemsID + '-reportProgress');
                var maxWidthPx = bar.parent().width();
                if (value) {
                    width = value;
                    if (width == 100) {
                        setTimeout(function () { bar.width(0) }, 300);
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



            var getFileName = function () {
                var res = { filename: "", rest: "" };
                var filePath = elems.attr("filePath")
                var split = filePath.split("/")
                res.filename = split[split.length - 1]
                res.rest = split.slice(0, -1).join('/');
                return res
            }
            var saveCommand = function (editorId, filename) {
                var obj = getFileName();
                var newPath = obj.rest + "/" + filename
                var text = window[editorId].getValue();
                text = encodeURIComponent(text);
                var run_log_uuid = $("#runVerReport").val();
                var saveData = getValues({ p: "saveFileContent", text: text, uuid: run_log_uuid, filename: "pubweb/" + newPath });
                return saveData
            }

            var saveRmd = function (editorId, type) {
                var obj = getFileName();
                var newPath = obj.rest + "/" + obj.filename
                //check if readonly
                if (elems.attr("read_only") || type == "saveas") {
                    //ask new name  
                    $("#rMarkRename").attr("filename", obj.filename)
                    $("#rMarkRename").modal("show");
                } else {
                    var saveData = saveCommand(editorId, obj.filename)
                    if (saveData) {
                        updateLogText("All changes saved.", "clean")
                    }
                }
            }

            var openBlankPage = function (editorId) {
                var obj = getFileName();
                var newPath = obj.rest + "/" + obj.filename
                var url = settings.ajax.pubWebPath + "/" + settings.ajax.uuid + "/pubweb/" + settings.ajax.dir + "/.tmp/" + settings.ajax.filename + ".html"
                var w = window.open();
                w.location = url;
            }

            var toogleFullSize = function (editorId, type) {
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
                $.each(newCSS, function (el) {
                    elems.css(el, newCSS[el])
                });
            }

            var ajaxRq = function (settings, data) {
                var ret = null;
                $.ajax({
                    type: "POST",
                    url: settings.ajax.url,
                    data: data,
                    async: false,
                    cache: false,
                    success: function (results) {
                        ret = results;
                    },
                    error: function (jqXHR, exception) {
                        console.log("#Error:")
                        console.log(jqXHR.status)
                        console.log(exception)
                        updateLogText("Error occurred.")
                        progress(100)
                    }
                });
                return ret
            }

            var callback = function (settings, tmpPath, orgPath, pid, type) {
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

            var downloadText = function (text, filename) {
                var element = document.createElement('a');
                element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
                element.setAttribute('download', filename);
                element.style.display = 'none';
                document.body.appendChild(element);
                element.click();
                document.body.removeChild(element);
            }

            var downpdf = function (editorId) {
                var text = window[editorId].getValue();
                callData(text, settings, "rmdpdf", callback);
            }

            var downRmd = function (editorId) {
                var text = window[editorId].getValue();
                var filename = elems.attr("filename")
                downloadText(text, filename)
            }

            var update = function (editorId) {
                var text = window[editorId].getValue();
                callData(text, settings, "rmdtext", callback);
            }
            var timeoutId = 0;
            var autoUpdate = function (editorId) {
                if (timeoutId) clearTimeout(timeoutId);
                timeoutId = setTimeout(function () { update(editorId) }, 2000);
            }

            var checkAutoUpdateOut = function (editorId) {
                if ($('input.aUpdateOut').is(":checked")) {
                    $('#' + editorId).keyup(function () {
                        autoUpdate(editorId);
                    });
                } else {
                    $('#' + editorId).off("keyup");
                }
            }
            var checkAutoSave = function (editorId) {
                if ($('input.aSave').is(":checked")) {
                    window['interval_aSave_' + editorId] = setInterval(function () {
                        saveRmd(editorId, "autosave");
                    }, 30000);
                } else {
                    if (window['interval_aSave_' + editorId]) {
                        clearInterval(window['interval_aSave_' + editorId])
                    }
                }
            }

            var eventHandler = function (settings) {
                var editorId = elemsID + "-editor";

                $(function () {
                    $('[data-toggle="tooltip"]').tooltip();
                });
                $(function () {
                    $('a.rmarkeditorrun').on('click', function (event) {
                        if ($(this).parents("#" + elemsID).length) {
                            update(editorId);
                        }
                    });
                    $('a.rmarkreportdownpdf').on('click', function (event) {
                        if ($(this).parents("#" + elemsID).length) {
                            event.preventDefault();
                            downpdf(editorId);
                        }
                    });
                    $('a.rmarkeditordownrmd').on('click', function (event) {
                        if ($(this).parents("#" + elemsID).length) {
                            event.preventDefault();
                            downRmd(editorId);
                        }
                    });
                });
                $(function () {
                    //check current status on first creation
                    checkAutoUpdateOut(editorId)
                    $(document).on('change', 'input.aUpdateOut', function (event) {
                        checkAutoUpdateOut(editorId)
                    });
                    checkAutoSave(editorId)
                    $(document).on('change', 'input.aSave', function (event) {
                        checkAutoSave(editorId)
                    });
                });
                $(function () {
                    $('a.rmarkeditorsave').on('click', function (event) {
                        if ($(this).parents("#" + elemsID).length) {
                            saveRmd(editorId, "save")
                        }
                    });
                    $('a.rmarkeditorsaveas').on('click', function (event) {
                        if ($(this).parents("#" + elemsID).length) {
                            saveRmd(editorId, "saveas")
                        }
                    });
                    $('a.rmarkeditorsett').on('click', function (event) {
                        $("#rMarkSett").modal("show");
                    });
                    $('a.rmarkeditorfull').on('click', function (event) {
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
                    $('a.rmarkeditorlink').on('click', function (event) {
                        if ($(this).parents("#" + elemsID).length) {
                            openBlankPage(editorId)
                        }
                    });


                });
                $(function () {
                    $('#rMarkRename').on('show.bs.modal', function (event) {
                        var divOldName = elems.attr("filename")
                        var modalOldName = $("#rMarkRename").attr("filename")
                        if (divOldName === modalOldName) {
                            if ($('#rMarkRename').find("input.rmarkfilename")) {
                                $($('#rMarkRename').find("input.rmarkfilename")[0]).val(divOldName)
                            }
                        }
                    });
                    $("#rMarkRename").on('click', '.save', function (event) {
                        var divOldName = elems.attr("filename")
                        var divOldDir = elems.attr("dir")
                        var modalOldName = $("#rMarkRename").attr("filename")
                        if (divOldName === modalOldName) {
                            if ($('#rMarkRename').find("input.rmarkfilename")) {
                                var newName = $($('#rMarkRename').find("input.rmarkfilename")[0]).val();
                                var saveData = saveCommand(editorId, newName)
                                $("#reportRows").dynamicRows("fnRefresh", "columnsBody")
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


            var checkUrl = function (url) {
                var ret = null;
                $.ajax({
                    url: url,
                    type: 'GET',
                    async: false,
                    cache: false,
                    error: function () {
                        ret = false;
                    },
                    success: function () {
                        ret = true;
                    }
                });
                return ret;
            }

            var updateLogText = function (text, type) {
                $("#" + elemsID + '-log').text(text);
                if (type == "clean") {
                    setTimeout(function () {
                        if ($("#" + elemsID + '-log').text() == text) {
                            $("#" + elemsID + '-log').text("");
                        }
                    }, 2000);
                }
            }

            var getUrlContent = function (url) {
                return $.get(url);
            }

            var getUrl = function (settings, type, callback, pid) {
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
                window[elemsID + type] = setInterval(function () {
                    var checkExistUrl = checkUrl(tmpPath)
                    if (!checkExistUrl) {
                        var checkExistError = checkUrl(orgPath + ".err" + pid)
                        if (checkExistError) {
                            getUrlContent(orgPath + ".err" + pid).success(function (data) {
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
            var initialUrlCheck = function (settings, type) {
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

            var callData = function (editText, settings, type, callback) {
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
                    success: function (results) {
                        ret = results;
                        if (ret) {
                            getUrl(settings, type, callback, ret)
                        }
                    },
                    error: function (jqXHR, exception) {
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

    //################################
    // --dynamicRows jquery plugin --
    //################################

    (function ($) {
        var methods = {
            init: function (options) {
                var settings = $.extend({
                    // default values.
                    color: "#556b2f",
                    backgroundColor: "white",
                    heightHeader: "60px",
                    lineHeightHeader: "60px",
                    heightBody: "600px",
                    heightTitle: "50px",
                    lineHeightTitle: "50px"
                }, options);
                var elems = $(this);
                var elemsID = $(this).attr("id");
                elems.data('settings', settings);
                var data = getData(settings);
                if (data === undefined || data == null || data == "") {
                    elems.append('<div  style="font-weight:900; line-height:' + settings.lineHeightTitle + 'height:' + settings.heightTitle + ';">No data available to report</div>')
                } else {
                    var title = getTitle(data[0], settings);
                    if (title) {
                        elems.append(title);
                    }
                    $(data).each(function (i) {
                        elems.append(getPanel(data[i], settings, elemsID));
                    });
                    refreshHandler(settings)
                }
                return this;
            },
            fnRefresh: function (content) {
                var elems = $(this);
                var elemsID = $(this).attr("id");
                var settings = elems.data('settings');
                var data = getData(settings);
                if (content == "columnsBody") {
                    $(data).each(function (i) {
                        var id = data[i].id
                        var dataObj = data[i];
                        var existWrapBody = $("#" + elemsID + '-' + id);
                        if (existWrapBody) {
                            var existBodyDiv = existWrapBody.children().children()
                            var col = settings.columnsBody;
                            $.each(existBodyDiv, function (el) {
                                if (existBodyDiv[el]) {
                                    getColumnContent(dataObj, col[el], existBodyDiv[el])
                                }
                            })
                        }
                    });
                }
                return this;
            }
        };

        $.fn.dynamicRows = function (methodOrOptions) {
            if (methods[methodOrOptions]) {
                return methods[methodOrOptions].apply(this, Array.prototype.slice.call(arguments, 1));
            } else if (typeof methodOrOptions === 'object' || !methodOrOptions) {
                // Default to "init"
                return methods.init.apply(this, arguments);
            } else {
                $.error('Method ' + methodOrOptions + ' does not exist on jQuery.tooltip');
            }
        };



        var refreshHandler = function (settings) {
            $(function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
            $('.collapseRowDiv').on({
                mouseenter: function () {
                    $(this).css("background-color", settings.backgroundcolorenter)
                },
                mouseleave: function () {
                    $(this).css("background-color", settings.backgroundcolorleave)
                }
            });

            $('.collapseRowDiv').on('click', function (e) {
                var textClassPlus = $(this).find('.fa-plus-square-o')[0];
                var textClassMinus = $(this).find('.fa-minus-square-o')[0];
                if (textClassPlus) {
                    $(this).css("background-color", settings.backgroundcolorenter)
                    $(textClassPlus).removeClass('fa-plus-square-o');
                    $(textClassPlus).addClass('fa-minus-square-o');
                } else if (textClassMinus) {
                    $(this).css("background-color", settings.backgroundcolorleave)
                    $(textClassMinus).removeClass('fa-minus-square-o');
                    $(textClassMinus).addClass('fa-plus-square-o');
                }
            });
            $('.collapseIconItem').on('click', function (e) {
                var itemClass = $(this).attr("class")
                if (itemClass.match(/fa-plus-square-o/)) {
                    $(this).removeClass('fa-plus-square-o');
                    $(this).addClass('fa-minus-square-o');
                } else if (itemClass.match(/fa-minus-square-o/)) {
                    $(this).removeClass('fa-minus-square-o');
                    $(this).addClass('fa-plus-square-o');
                }
            });
        }

        var getColumnContent = function (dataObj, colObj, nTd) {
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

        var getColumnData = function (dataObj, settings, cols, height, lineHeight) {
            var columnPercent = 100;
            var clearFix = ""; //if its the first element of multicolumn
            var center = ""; //align="center" to div
            var columnCount = $(cols).size();
            var processParamDiv = ""
            var heightT = "";
            var lineHeightT = "";
            $.each(cols, function (el) {
                var overflowT = "";
                if (cols[el].overflow) {
                    overflowT = 'overflow:' + cols[el].overflow + '; ';
                }
                if (cols[el].colPercent) {
                    columnPercent = cols[el].colPercent;
                } else {
                    columnPercent = Math.floor(columnPercent / columnCount * 100) / 100;
                }
                if (el === 0) {
                    clearFix = " clear:both; "
                } else {
                    clearFix = ""
                }
                if (cols[el].className == "center") {
                    center = ' align="center"; '
                } else {
                    center = ""
                }
                if (height) {
                    heightT = 'height:' + height + '; ';
                }
                if (lineHeight) {
                    lineHeightT = 'line-height:' + lineHeight + '; ';
                }

                processParamDiv += '<div ' + center + ' style="' + heightT + lineHeightT + clearFix + overflowT + 'float:left;  width:' + columnPercent + '%; ">';
                processParamDiv += getColumnContent(dataObj, cols[el], null)
                processParamDiv += '</div>';
            });
            return processParamDiv
        }

        var getPanel = function (dataObj, settings, elemsID) {
            if (dataObj) {
                var id = dataObj.id
                var headerDiv = getColumnData(dataObj, settings, settings.columnsHeader, settings.heightHeader, settings.lineHeightHeader);
                var bodyDiv = getColumnData(dataObj, settings, settings.columnsBody, settings.heightBody, settings.lineHeightBody);
                var wrapHeader = '<div class="collapsible collapseRowDiv" data-toggle="collapse" style="height:' + settings.heightHeader + ';" href="#' + elemsID + '-' + id + '"><h3 class="panel-title">' + headerDiv + '</h3></div>';
                var wrapBody = '<div  id="' + elemsID + '-' + id + '" class="panel-collapse collapse collapseRowBody" style="word-break: break-all;"><div class="panel-body" style="background-color:white; height:' + settings.heightBody + '; padding:0px;">' + bodyDiv + '</div>';
                return '<div id="' + elemsID + 'PanelDiv-' + id + '" ><div class="panel" style="background-color:' + settings.backgroundcolorleave + '; margin-bottom:15px;">' + wrapHeader + wrapBody + '</div></div>'
            } else
                return ""
        }

        var getTitle = function (dataObj, settings) {
            if (settings.columnsTitle) {
                var titleDiv = getColumnData({}, settings, settings.columnsTitle, settings.heightTitle, settings.lineHeightTitle);
                return '<div  style="font-weight:900; height:' + settings.heightTitle + ';">' + titleDiv + '</div>'
            } else
                return ""
        }

        var getData = function (settings) {
            var res = null;
            if (settings.ajax.url) {
                $.ajax({
                    type: "POST",
                    url: settings.ajax.url,
                    data: settings.ajax.data,
                    datatype: "json",
                    async: false,
                    cache: false,
                    success: function (results) {
                        res = results
                    },
                    error: function (errorThrown) {
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
            return res
        }

        }(jQuery));


    $(function () {
        $(document).on('change', '#runVerReport', function (event) {
            var run_log_uuid = $(this).val();
            var reload = true
            if (run_log_uuid) {
                var prevUUID = $(this).attr("prev")
                $(this).attr("prev", run_log_uuid)
                var savedReportData = $.data(this, "reportData")
                var reportData = getValues({ "p": "getReportData", uuid: run_log_uuid, path: "pubweb", pipeline_id: pipeline_id })
                $.data(this, "reportData", reportData);
                if (prevUUID) {
                    if (prevUUID == run_log_uuid) {
                        if (savedReportData && reportData) {
                            if (savedReportData.length && reportData.length) {
                                if (savedReportData.length == reportData.length) {
                                    reload = false
                                }
                            }
                        }
                    }
                }
                var version = $('option:selected', this).attr('ver');
                if (version) {
                    var runTitleLog = "Run Report " + version + ":"
                    $('a[href="#reportTab"]').css("display", "block")
                } else {
                    var runTitleLog = "";
                    $('a[href="#reportTab"]').css("display", "none")
                }

                $("#runTitleReport").text(runTitleLog)
                if (reload) {
                    $("#reportRows").empty();
                    //add 'className: "center"' to center text in columns array
                    $("#reportRows").dynamicRows({
                        ajax: {
                            url: "ajax/ajaxquery.php",
                            data: { "p": "getReportData", uuid: run_log_uuid, path: "pubweb", pipeline_id: pipeline_id }
                        },
                        columnsBody: [{
                            //file list
                            data: null,
                            colPercent: "15",
                            overflow: "scroll",
                            fnCreatedCell: function (nTd, oData) {
                                var run_log_uuid = $("#runVerReport").val();
                                var pubWebPath = $("#basepathinfo").attr("pubweb");
                                var visType = oData.pubWeb
                                var icon = "fa-file-text-o";
                                if (visType == "table" || visType === "table-percent") {
                                    icon = "fa-table";
                                }
                                var fileList = oData.fileList;
                                var liText = "";
                                var active = "";
                                $.each(fileList, function (el) {
                                    if (fileList[el]) {
                                        if (el == 0) {
                                            active = "active"
                                        } else {
                                            active = "";
                                        }
                                        var filepath = oData.name + "/" + fileList[el];
                                        var link = pubWebPath + "/" + run_log_uuid + "/" + "pubweb" + "/" + filepath;
                                        var filenameCl = cleanProcessName(fileList[el])
                                        var tabID = 'reportTab' + oData.id + "_" + filenameCl;
                                        var fileID = oData.id + "_" + filenameCl;
                                        //remove directory str, only show filename in label
                                        var labelText = /[^/]*$/.exec(fileList[el])[0];
                                        liText += '<li class="' + active + '"><a  class="reportFile" data-toggle="tab" fileid="' + fileID + '" filepath="' + filepath + '" href="#' + tabID + '" visType="' + visType + '" fillsrc="' + link + '" ><i class="fa ' + icon + '"></i>' + labelText + '</a></li>';
                                    }
                                });
                                if (!liText) {
                                    liText = '<div style="margin:10px;"> No data available</div>';
                                }
                                $(nTd).html('<ul class="nav nav-pills nav-stacked">' + liText + '</ul>');
                            },
                        }, {
                            //file content
                            data: null,
                            colPercent: "85",
                            fnCreatedCell: function (nTd, oData) {
                                var fileList = oData.fileList;
                                if ($(nTd).is(':empty')) {
                                    var navTabDiv = "";
                                    navTabDiv += '<div style="height:inherit;" class="tab-content">';
                                    var liText = "";
                                    var active = "";
                                    $.each(fileList, function (el) {
                                        var filenameCl = cleanProcessName(fileList[el])
                                        var tabID = 'reportTab' + oData.id + "_" + filenameCl;
                                        var active = "";
                                        if (el == 0) {
                                            active = 'in active';
                                        }
                                        navTabDiv += '<div style="height:100%; width:100%;" id = "' + tabID + '" class = "tab-pane fade fullsize ' + active + '" ></div>';
                                    });
                                    navTabDiv += '</div>';
                                    $(nTd).html(navTabDiv);
                                } else {
                                    $.each(fileList, function (el) {
                                        var filenameCl = cleanProcessName(fileList[el])
                                        var tabID = 'reportTab' + oData.id + "_" + filenameCl;
                                        if (!$(nTd).find("div#" + tabID).length) {
                                            $(nTd).children().append('<div style="height:100%; width:100%;" id = "' + tabID + '" class = "tab-pane fade" ></div>')
                                        }
                                    });
                                }
                            },
                        }],
                        columnsHeader: [{
                            data: null,
                            colPercent: "4",
                            fnCreatedCell: function (nTd, oData) {
                                $(nTd).html('<span class="info-box-icon" style="height:60px; line-height:60px; width:30px; font-size:18px;  background:rgba(0,0,0,0.2);"><i class="fa fa-folder"></i></span>');
                            },
                        }, {
                            data: null,
                            fnCreatedCell: function (nTd, oData) {
                                var gNum = oData.id.split("_")[0].split("-")[1];
                                var rowID = "outputTa-" + gNum;
                                var processName = $('#' + rowID + ' > :nth-child(5)').text();
                                var processID = $('#' + rowID + ' > :nth-child(5)').text();
                                $(nTd).html('<span  gnum="' + gNum + '" processid="' + processID + '">' + createLabel(processName) + '</span>');
                            },
                            colPercent: "37"
                        }, {
                            data: null,
                            colPercent: "39",
                            fnCreatedCell: function (nTd, oData) {
                                $(nTd).html('<span>' + createLabel(oData.name) + '</span>');
                            }
                        }, {
                            data: null,
                            colPercent: "20",
                            fnCreatedCell: function (nTd, oData) {
                                var visType = oData.pubWeb
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
                                } else if (visType === "highcharts") {
                                    icon = "fa fa-line-chart";
                                    text = "Charts";
                                } else if (visType === "debrowser") {
                                    icon = "glyphicon glyphicon-stats";
                                    text = "DE-Browser";
                                }
                                $(nTd).html('<a data-toggle="tooltip" data-placement="bottom" data-original-title="View"><i class="' + icon + '"></i> ' + text + '</a>');
                            }
                        }],
                        columnsTitle: [{
                            data: null,
                            colPercent: "4"

                        }, {
                            data: null,
                            fnCreatedCell: function (nTd, oData) {
                                $(nTd).html('<span>PROCESS</span>');
                            },
                            colPercent: "37"
                        }, {
                            data: "name",
                            colPercent: "39",
                            fnCreatedCell: function (nTd, oData) {
                                $(nTd).html('<span>PUBLISHED DIRECTORY</span>');
                            },
                        }, {
                            data: null,
                            colPercent: "20",
                            fnCreatedCell: function (nTd, oData) {
                                $(nTd).html('<span>VIEW FORMAT</span>');
                            }
                        }],
                        backgroundcolorenter: "#ced9e3",
                        backgroundcolorleave: "#ECF0F4",
                        heightHeader: "60px"
                    });
                }
            }
        });
    });









});
