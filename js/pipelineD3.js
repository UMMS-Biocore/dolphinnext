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
    if (window.lastMG) {
        translateSVG([Mainx, Mainy, Mainz, window.lastMG[3], window.lastMG[4]], window)
    }
    //for pipeline modules
    for (var j = 0; j < piGnumList.length; j++) {
        if (d3.select('#' + "mainG" + piGnumList[j])) {
            var MaintP = d3.transform(d3.select('#' + "mainG" + piGnumList[j]).attr("transform"));
            var MainxP = MaintP.translate[0]
            var MainyP = MaintP.translate[1]
            var MainzP = MaintP.scale[0]
            if (window["pObj" + piGnumList[j]].lastMG) {
                translateSVG([MainxP, MainyP, MainzP, window["pObj" + piGnumList[j]].lastMG[3], window["pObj" + piGnumList[j]].lastMG[4]], window["pObj" + piGnumList[j]]);
            }
        }
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


function drop(event) {
    event.preventDefault();
    var processDat = event.dataTransfer.getData("Text");
    var posX = 0;
    var posY = 0;
    var svgA = document.getElementById("svg")
    var pt = svgA.createSVGPoint();
    pt.x = event.clientX
    pt.y = event.clientY
    var svgGlobal = pt.matrixTransform(svgA.getScreenCTM().inverse())
    posX = svgGlobal.x - 50
    posY = svgGlobal.y - 70
    t = d3.transform(d3.select('#' + "mainG").attr("transform"));
    x = (posX - t.translate[0]);
    y = (posY - t.translate[1]);
    z = t.scale[0]
    var xPos = (-30 + x + rP + ior) / z; //position of circle
    var yPos = (-10 + y + rP + ior) / z;
    var piID = 0;
    if (processDat.match(/pipeline-(.*)/)) {
        piID = processDat.match(/pipeline-(.*)/)[1];
        var newMainGnum = "pObj" + gNum;
        window[newMainGnum] = {};
        window[newMainGnum].piID = piID;
        window[newMainGnum].MainGNum = gNum;
        window[newMainGnum].lastGnum = gNum;
        window[newMainGnum].sData = getValues({ p: "loadPipeline", id: piID })
        var proName = window[newMainGnum].sData[0].name;
        window[newMainGnum].lastPipeName = proName;
        // create new SVG workplace inside panel
        openSubPipeline(piID, window[newMainGnum]);
        // add pipeline circle to main workplace
        addPipeline(piID, xPos, yPos, proName, window, window[newMainGnum]);
    } else {
        addProcess(processDat, posX, posY);
    }

    autosave();
    event.stopPropagation();
    return false;
}

refreshDataset()

function refreshDataset() {
    processData = getValues({
        p: "getProcessData"
    })
    parametersData = getValues({
        p: "getAllParameters"
    })

}
var sData = "";
var svg = "";
var mainG = "";

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
    ccIDList = {} //pipeline module match id list
    processListNoOutput = {}
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
    $('#inputsTable').find("tr:gt(0)").remove();
    $('#outputsTable').find("tr:gt(0)").remove();
    $('#processTable').find("tr:gt(0)").remove();

    svg = d3.select("#container").append("svg")
        .attr("id", "svg")
        .attr("width", w)
        .attr("height", h)
        .on("mousedown", startzoom)
        .on("mouseup", autosave)
    mainG = d3.select("#container").select("svg").append("g")
        .attr("id", "mainG")
        .attr("transform", "translate(" + 0 + "," + 0 + ")")
}

function startzoom() {
    d3.select("#container").call(zoom)
}
$('#editorPipeHeader').keyup(function () {
    autosave();
});
$('#editorPipeFooter').keyup(function () {
    autosave();
});
$('#pipelineSum').keyup(function () {
    autosaveDetails();
});
$('#groupSelPipe').change(function () {
    autosaveDetails();
});
$('#pin').click(function () {
    autosaveDetails();
});
$('#pipeGroupAll').change(function () {
    var id = $("#pipeline-title").attr('pipelineid');
    if (id !== "") {
        autosaveDetails();
    } else {
        autosave();
    }
});
$('#publishPipe').change(function () {
    autosaveDetails();
});
$("#pipeline-title").keyup(function () { //Click outside of the field or enter
    autosave();
});

var timeoutId = 0;
pipelineOwn = '';
pipelinePerm = '';

function autosave() {
    if ((pipelineOwn === '' || pipelineOwn === "1") && pipelinePerm !== "63") {
        var pipName = $('#pipeline-title').val()
        var pipGroup = $('#pipeGroupAll').val()
        if (pipName !== '' && pipGroup != '') {
            $('#autosave').text('Saving...');
            if (timeoutId) clearTimeout(timeoutId);
            timeoutId = setTimeout(function () { save() }, 2000);
        }
    }
}

function autosaveDetails() {
    if (((pipelineOwn === '' || pipelineOwn === "1") && pipelinePerm !== "63") || usRole === "admin") {
        var pipName = $('#pipeline-title').val()
        var pipGroup = $('#pipeGroupAll').val()
        if (pipName !== '' && pipGroup != '') {
            $('#autosave').text('Saving...');
            if (timeoutId) clearTimeout(timeoutId);
            timeoutId = setTimeout(function () { saveDetails() }, 1300);
        }
    }
}

function duplicatePipeline() {
    dupliPipe = true
    save()
}

function delPipeline() {
    var pipeID = $('#pipeline-title').attr('pipelineid');
    var s = getValues({
        p: "removePipelineById",
        'id': pipeID
    });
    window.location.replace("index.php?np=1");
}

//resets input/output param if its single
function resetSingleParam(paramId) {
    if ($('#' + paramId).attr("connect") === "single") {
        if ($('#' + paramId).parent().attr("class") === "g-inPro") {
            resetOriginal("inPro", paramId)
            return true
        } else if ($('#' + paramId).parent().attr("class") === "g-outPro") {
            resetOriginal("outPro", paramId)
            return true
        }
    }
    return false
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
        var nodes = getValues({ p: "getInputsPP", "process_id": nullProcessId })
    } else if (nullProcessInOut === "o") {
        var nodes = getValues({ p: "getOutputsPP", "process_id": nullProcessId })
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
                return newNodeId;
            }
        }
    }
}

function openSubPipeline(piID, pObj) {
    var sData = pObj.sData[0];
    var MainGNum = pObj.MainGNum;
    var lastGnum = pObj.lastGnum;
    var prefix = "p" + MainGNum;
    pObj.processList = {};
    pObj.edges = [];
    pObj.processListNoOutput = {};
    var hideModule = false;
    if ($("#subPipelinePanelTitle").find('div[pipeid*=' + piID + ']').length > 0) {
        hideModule = true;
    }
    var hideModuleText = '';
    if (hideModule) {
        hideModuleText = 'style="display:none;"';
    }
    var pipeName = sData.name;
    var dispTitle = $('#subPipelinePanelTitle').css("display");
    if (dispTitle == "none") {
        $('#subPipelinePanelTitle').css("display", "inline");
    }
    var processHeader = '<div class="panel-heading collapsible collapseIconDiv" data-toggle="collapse" href="#collapse-' + MainGNum + '"><h4 class="panel-title">' + pipeName + ' Module <i data-toggle="tooltip" data-placement="bottom" data-original-title="Expand/Collapse"><a style="font-size:15px; padding-left:10px;" class="fa collapseIcon fa-plus-square-o"></a></i></h4></div>';
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
            pObj.name = pObj.nodes[key][3]
            var processModules = pObj.nodes[key][4];
            pObj.gNum = key.split("-")[1]
            if (pObj.pId.match(/p(.*)/)) {
                var newPiID = pObj.pId.match(/p(.*)/)[1];
                var newMainGnum = "pObj" + MainGNum + "_" + pObj.gNum;
                window[newMainGnum] = {};
                window[newMainGnum].piID = newPiID;
                window[newMainGnum].MainGNum = MainGNum + "_" + pObj.gNum;
                window[newMainGnum].lastGnum = pObj.gNum;
                window[newMainGnum].sData = getValues({ p: "loadPipeline", id: newPiID })
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

function translateSVG(mG, pObj) {
    var MainGNum = "";
    if (pObj != window) {
        // pipeline modules 
        var MainGNum = pObj.MainGNum;
    }
    if (!mG[3]) {
        mG[3] = 1378; //default width of container if its not defined before
    }
    var widthC = $("#container").width();
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


function openPipeline(id) {
    createSVG()
    sData = getValues({
        p: "loadPipeline",
        id: id
    }) //all data from biocorepipe_save table
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
                name = nodes[key][3]
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
                    window[newMainGnum].sData = getValues({ p: "loadPipeline", id: piID })
                    window[newMainGnum].lastPipeName = name;
                    // create new SVG workplace inside panel
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
function drawParam(name, process_id, id, kind, sDataX, sDataY, paramid, pName, classtoparam, init, pColor, defVal, dropDown, pObj) {
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
        .on("mouseover", mouseOverG)
        .on("mouseout", mouseOutG)

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
        .on("mouseover", scMouseOver)
        .on("mouseout", scMouseOut)
        .call(drag)

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
        .on("mousedown", IOconnect)

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
        .on("mouseover", scMouseOver)
        .on("mouseout", scMouseOut)
        .call(drag)
    if (defVal) {
        $("#text-" + pObj.gNum).attr('defVal', defVal)
    }
    if (dropDown) {
        $("#text-" + pObj.gNum).attr('dropDown', dropDown)
    }

    if (pObj == window) {
        pObj.g.append("text").attr("id", "text" + MainGNum + "-" + gNum)
            .datum([{
                cx: 0,
                cy: 0
                }])
            .attr('font-family', "FontAwesome, sans-serif")
            .attr('font-size', '0.9em')
            .attr("x", -40)
            .attr("y", 5)
            .text('\uf040')
            .on("mousedown", rename)
        //gnum(written in id): unique,
        pObj.g.append("text")
            .attr("id", "del" + MainGNum + "-" + pObj.gNum)
            .attr('font-family', "FontAwesome, sans-serif")
            .attr('font-size', '1em')
            .attr("x", +30)
            .attr("y", 5)
            .text('\uf014')
            .style("opacity", 0.2)
            .on("mousedown", removeElement)
    }
}

function insertRowTable(rowType, firGnum, secGnum, paramGivenName, paraIdentifier, paraFileType, paraQualifier, processName) {
    if (paraQualifier !== "val") {
        return '<tr id=' + rowType + 'Ta-' + firGnum + '><td id="' + rowType + '-PName-' + firGnum + '" scope="row">' + paramGivenName + '</td><td>' + paraIdentifier + '</td><td>' + paraFileType + '</td><td>' + paraQualifier + '</td><td> <span id="proGName-' + secGnum + '">' + processName + '</span></td></tr>'
    } else {
        return '<tr id=' + rowType + 'Ta-' + firGnum + '><td id="' + rowType + '-PName-' + firGnum + '" scope="row">' + paramGivenName + '</td><td>' + paraIdentifier + '</td><td>' + '-' + '</td><td>' + paraQualifier + '</td><td> <span id="proGName-' + secGnum + '">' + processName + '</span></td></tr>'
    }
}

function insertProRowTable(process_id, procName, procDesc, procRev) {
    return '<tr id=procTa-' + process_id + '><td scope="row">' + procName + '</td><td>' + procRev + '</td><td>' + procDesc + '</td></tr>'
}

//--Pipeline details table --
function addProPipeTab(id) {
    var procData = processData.filter(function (el) { return el.id == id });
    if (procData && procData[0]) {
        var procName = procData[0].name;
        var procDesc = truncateName(decodeHtml(procData[0].summary), 'processTable');
        var procRev = procData[0].rev_id;
        var proRow = insertProRowTable(id, procName, procDesc, procRev);
        var rowExistPro = '';
        var rowExistPro = document.getElementById('procTa-' + id);
        if (!rowExistPro) {
            $('#processTable > tbody:last-child').append(proRow);
        }
    }
}

function removeProPipeTab(id) {
    var proExist = '';
    var proExist = $(".g-" + id)[1];
    //there should be at least 2 process before delete, otherwise delete
    if (!proExist) {
        $('#procTa-' + id).remove();
    }
}

function addProcess(processDat, xpos, ypos) {
    t = d3.transform(d3.select('#' + "mainG").attr("transform")),
        x = (xpos - t.translate[0])
    y = (ypos - t.translate[1])
    z = t.scale[0]
    var defVal = null;
    var dropDown = null;
    //var process_id = processData[index].id;
    //for input parameters:  
    if (processDat === "inputparam@inPro") {
        var name = processDat.split('@')[0]
        var process_id = processDat.split('@')[1]
        var id = process_id
        ipR = 70 / 2
        ipIor = ipR / 3
        var kind = "input"
        var sDataX = (5 + x + ipR + ipIor) / z
        var sDataY = (20 + y + ipR + ipIor) / z
        var pName = pName || "inputparam"
        var paramId = paramId || "inPara"
        var classtoparam = classtoparam || "connect_to_input output"
        var init = "o"
        var pColor = "orange"

        drawParam(name, process_id, id, kind, sDataX, sDataY, paramId, pName, classtoparam, init, pColor, defVal, dropDown, window)
        processList[("g-" + gNum)] = name
        processListNoOutput[("g-" + gNum)] = name
        gNum = gNum + 1
    }
    //for output parameters:  
    else if (processDat === "outputparam@outPro") {
        var name = processDat.split('@')[0]
        var process_id = processDat.split('@')[1]
        var id = process_id
        ipR = 70 / 2
        ipIor = ipR / 3
        var kind = "output"
        var sDataX = (5 + x + ipR + ipIor) / z
        var sDataY = (20 + y + ipR + ipIor) / z
        var pName = pName || "outputparam"
        var paramId = paramId || "outPara"
        var classtoparam = classtoparam || "connect_to_output input"
        var init = "i"
        var pColor = "green"
        drawParam(name, process_id, id, kind, sDataX, sDataY, paramId, pName, classtoparam, init, pColor, defVal, dropDown, window)

        processList[("g-" + gNum)] = name
        gNum = gNum + 1
    }
    //for processes:
    else {
        var name = processDat.split('@')[0]
        var process_id = processDat.split('@')[1]
        var id = process_id

        //--Pipeline details table add process--
        addProPipeTab(id)

        var inputs = getValues({
            p: "getInputsPP",
            "process_id": process_id
        })

        var outputs = getValues({
            p: "getOutputsPP",
            "process_id": process_id
        })
        //gnum uniqe, id same id (Written in class) in same type process
        g = d3.select("#mainG").append("g")
            .attr("id", "g-" + gNum)
            .attr("class", "g-" + id)
            .attr("transform", "translate(" + (-30 + x + r + ior) / z + "," + (-10 + y + r + ior) / z + ")")

            .on("mouseover", mouseOverG)
            .on("mouseout", mouseOutG)
        //gnum(written in id): uniqe, id(Written in class): same id in same type process, bc(written in type): same at all bc
        g.append("circle").attr("id", "bc-" + gNum)
            .attr("class", "bc-" + id)
            .attr("type", "bc")
            .attr("cx", cx)
            .attr("cy", cy)
            .attr("r", r + ior)
            //  .attr('fill-opacity', 0.6)
            .attr("fill", "red")
            .transition()
            .delay(500)
            .duration(3000)
            .attr("fill", "#E0E0E0")
        //gnum(written in id): uniqe, id(Written in class): same id in same type process, sc(written in type): same at all bc
        g.append("circle")
            .datum([{
                cx: 0,
                cy: 0
                }])
            .attr("id", "sc-" + gNum)
            .attr("class", "sc-" + id)
            .attr("type", "sc")
            .attr("r", r - ior)
            .attr("fill", "#BEBEBE")
            .attr('fill-opacity', 0.6)
            .on("mouseover", scMouseOver)
            .on("mouseout", scMouseOut)
            .call(drag)
        //gnum(written in id): uniqe,
        g.append("text").attr("id", "text-" + gNum)
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
            .on("mouseover", scMouseOver)
            .on("mouseout", scMouseOut)
            .call(drag)

        g.append("text").attr("id", "text-" + gNum)
            .datum([{
                cx: 0,
                cy: 0
                }])
            .attr('font-family', "FontAwesome, sans-serif")
            .attr('font-size', '0.9em')
            .attr("x", -6)
            .attr("y", 15)
            .text('\uf040')
            .on("mousedown", rename)

        //gnum(written in id): uniqe,
        g.append("text")
            .attr("id", "del-" + gNum)
            .attr('font-family', "FontAwesome, sans-serif")
            .attr('font-size', '1em')
            .attr("x", -6)
            .attr("y", r + ior / 2)
            .text('\uf014')
            .style("opacity", 0.2)
            .on("mousedown", removeElement)

        g.append("text")
            .attr("id", "info-" + gNum)
            .attr("class", "info-" + id)
            .attr('font-family', "FontAwesome, sans-serif")
            .attr('font-size', '1em')
            .attr("x", -6)
            .attr("y", -1 * (r + ior / 2 - 10))
            .text('\uf013')
            .style("opacity", 0.2)
            .on("mousedown", getInfo)

        // I/O id naming:[0]i = input,o = output -[1]process database ID -[2]The number of I/O of the selected process -[3]Parameter database ID- [4]uniqe number
        for (var k = 0; k < inputs.length; k++) {
            d3.select("#g-" + gNum).append("circle")
                .attr("id", "i-" + (id) + "-" + k + "-" + inputs[k].parameter_id + "-" + gNum)
                .attr("type", "I/O")
                .attr("kind", "input")
                .attr("parentG", "g-" + gNum)
                .attr("name", inputs[k].sname)
                .attr("operator", inputs[k].operator)
                .attr("closure", inputs[k].closure)
                .attr("status", "standard")
                .attr("connect", "single")
                .attr("class", findType(inputs[k].parameter_id) + " input")
                .attr("cx", calculatePos(inputs.length, k, "cx", "inputs"))
                .attr("cy", calculatePos(inputs.length, k, "cy", "inputs"))
                .attr("r", ior)
                .attr("fill", "tomato")
                .attr('fill-opacity', 0.8)
                .on("mouseover", IOmouseOver)
                .on("mousemove", IOmouseMove)
                .on("mouseout", IOmouseOut)
                .on("mousedown", IOconnect)
        }
        for (var k = 0; k < outputs.length; k++) {
            d3.select("#g-" + gNum).append("circle")
                .attr("id", "o-" + (id) + "-" + k + "-" + outputs[k].parameter_id + "-" + gNum)
                .attr("type", "I/O")
                .attr("kind", "output")
                .attr("parentG", "g-" + gNum)
                .attr("name", outputs[k].sname)
                .attr("operator", outputs[k].operator)
                .attr("closure", outputs[k].closure)
                .attr("reg_ex", outputs[k].reg_ex)
                .attr("status", "standard")
                .attr("connect", "single")
                .attr("class", findType(outputs[k].parameter_id) + " output")
                .attr("cx", calculatePos(outputs.length, k, "cx", "outputs"))
                .attr("cy", calculatePos(outputs.length, k, "cy", "outputs"))
                .attr("r", ior).attr("fill", "steelblue")
                .attr('fill-opacity', 0.8)
                .on("mouseover", IOmouseOver)
                .on("mousemove", IOmouseMove)
                .on("mouseout", IOmouseOut)
                .on("mousedown", IOconnect)
        }
        processList[("g-" + gNum)] = name
        processListNoOutput[("g-" + gNum)] = name
        gNum = gNum + 1
    }

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
    if (pObjOrigin == window) {
        pObjOrigin.g.append("text").attr("id", "text" + MainGNum + "-" + pObjOrigin.gNum)
            .datum([{
                cx: 0,
                cy: 0
                }])
            .attr('font-family', "FontAwesome, sans-serif")
            .attr('font-size', '0.9em')
            .attr("x", -6)
            .attr("y", 15)
            .text('\uf040')
            .on("mousedown", rename)

        //gnum(written in id): uniqe,
        pObjOrigin.g.append("text")
            .attr("id", "del" + MainGNum + "-" + pObjOrigin.gNum)
            .attr('font-family', "FontAwesome, sans-serif")
            .attr('font-size', '1em')
            .attr("x", -6)
            .attr("y", rP + ior / 2)
            .text('\uf014')
            .style("opacity", 0.2)
            .on("mousedown", removeElement)
    }
    pObjOrigin.g.append("text")
        .attr("id", "info" + MainGNum + "-" + pObjOrigin.gNum)
        .attr("class", "info" + MainGNum + "-" + id)
        .attr('font-family', "FontAwesome, sans-serif")
        .attr('font-size', '1em')
        .attr("x", -6)
        .attr("y", -1 * (rP + ior / 2 - 10))
        .text('\uf013')
        .style("opacity", 0.2)
        .on("mousedown", getInfoPipe)


    //get process list of pipeline
    if (pObjSub.sData) {
        if (Object.keys(pObjSub.sData).length > 0) {
            //--Pipeline details table add process--
            pObjSub.nodesOrg = pObjSub.sData[0].nodes
            pObjSub.nodesOrg = JSON.parse(pObjSub.nodesOrg.replace(/'/gi, "\""));
            for (var key in pObjSub.nodesOrg) {
                var proId = pObjSub.nodesOrg[key][2];
                if (proId != "inPro" && proId != "outPro") {
                    addProPipeTab(proId) //
                }
            }
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
            //merge inNodes to one if they connected to same input param
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
                        .on("mousedown", IOconnect)
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
                        .on("mousedown", IOconnect)
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
                    .on("mousedown", IOconnect)
            }
        }
    }
    pObjOrigin.processList[("g" + MainGNum + "-" + pObjOrigin.gNum)] = name
    pObjOrigin.processListNoOutput[("g" + MainGNum + "-" + pObjOrigin.gNum)] = name
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

function remove(delID) {
    if (delID !== undefined) {
        deleteID = delID;
    }
    if (!binding) {
        g = document.getElementById(deleteID).parentElement.id //g-5
        //--delete pipeline module object and SVG panel
        var pipeModule = document.getElementById(deleteID).parentElement;
        if (pipeModule.className.baseVal.match(/g-p.*/)) {
            var gNumModule = g.split('-')[1];
            var pipeid = $("#proPanelDiv-" + gNumModule).attr("pipeid")
            $("#proPanelDiv-" + gNumModule).remove();
            delete window["pObj" + gNumModule];
            //delete all subModules
            createPiGnumList()
            $.each(piGnumList, function (el) {
                if (piGnumList[el].indexOf(gNumModule + "_") === 0) {
                    delete window["pObj" + piGnumList[el]];
                    $("#proPanelDiv-" + piGnumList[el]).remove();
                }
            });
            //check if hidden subModule is exist 
            var existSubPipe = $("#subPipelinePanelTitle").find('div[pipeid*=' + pipeid + ']');
            if (existSubPipe.length > 0) {
                $(existSubPipe[0]).css("display", "inline")
            }
            createPiGnumList()
            if (piGnumList.length === 0) {
                $('#subPipelinePanelTitle').css("display", "none");
            }
        }
        //--delete pipeline details
        var gNum = g.split('-')[1];
        var proClass = $('#' + g).attr('class') //
        var proID = $('#' + g).attr('class').split('-')[1] //
        if (proClass === 'g-inPro') { // input param is deleted
            $('#inputTa-' + gNum).remove();
        } else if (proClass === 'g-outPro') { // output param is deleted
            $('#outputTa-' + gNum).remove();
        } else { //process is deleted
            removeProPipeTab(proID)
        }
        //--delete pipeline details ends
        d3.select("#" + g).remove()
        delete processList[g]
        delete processListNoOutput[g]
        removeLines(g)
    }
}

function removeLines(g) {

    allLines = d3.selectAll("line")[0]
    for (var line = 0; line < allLines.length; line++) {
        from = allLines[line].getAttribute("g_from")
        to = allLines[line].getAttribute("g_to")

        if (from == g || to == g) {
            lineid = allLines[line].id
            removeEdge('c--' + lineid)
        }
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
    .style("opacity", .75)
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
            if (ccID.match(/,/)) {
                ccID = ccID.split(",");
                var processNameAll = "";
                var times = ccID.length;
                var threeDot = false;
                if (times > 3) {
                    times = 3;
                    threeDot = true;
                }
                for (var i = 0; i < times; i++) {
                    if (processNameAll !== "") {
                        processNameAll += ","
                    }
                    var parentID = $("#" + ccID[i]).parent().attr("id"); //g73-4
                    var textID = parentID.replace("g", "text"); //text73-4
                    var processName = $("#" + textID).attr("name");
                    processNameAll += processName
                }
                if (threeDot) {
                    processNameAll += "..."
                }

            } else {
                var parentID = $("#" + ccID).parent().attr("id"); //g73-4
                var textID = parentID.replace("g", "text"); //text73-4
                var processNameAll = $("#" + textID).attr("name");
            }
            processTag = 'Process: <em>' + processNameAll + '</em><br/>';
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

function IOconnect() {
    selectedIO = this.id //first click
    parent = document.getElementById(this.id).parentElement.id;
    //deactive for pipeline modules
    if (parent.match(/g(.*)-.*/)[1] === "") {
        className = document.getElementById(selectedIO).className.baseVal.split(" ")
        cand = searchedType(className[1])
        candParam = searchedTypeParam(className[1]);
        var givenNamePP = document.getElementById(this.id).getAttribute("name")
        if (givenNamePP === 'outputparam' && className[0] !== 'connect_to_output') {
            //If output parameter already connected , do nothing
        } else {
            if (binding) {
                stopBinding(className, cand, candParam, selectedIO)
            } else {
                startBinding(className, cand, candParam, selectedIO)
            }
        }
    }
}

function conToInput() {
    d3.selectAll("circle.input:not([id*=i-outPro])").attr("status", "candidate");
}

function conToOutput() {
    d3.selectAll("circle.output:not([id*=o-inPro])").attr("status", "candidate");
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

function updateSecClassName(second, inputParamLocF) {
    if (inputParamLocF === 0) {
        var candi = "output"
    } else {
        var candi = "input"
    }

    secClassName = document.getElementById(second).className.baseVal.split("-")[0].split(" ")[0] + " " + candi
    return secClassName
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
        var paramGivenName = document.getElementById('text' + MainGNum + "-" + firGnum).getAttribute("name");
        var paraData = parametersData.filter(function (el) { return el.id == pObj.secPI });
        //        var procData = processData.filter(function (el) { return el.id == secProI });
        var paraFileType = "";
        var paraQualifier = "";
        var paraIdentifier = "";
        if (paraData && paraData != '') {
            var paraFileType = paraData[0].file_type;
            var paraQualifier = paraData[0].qualifier;
            var paraIdentifier = paraData[0].name;
        }
        var processName = $('#text' + MainGNum + "-" + secGnum).attr('name');
        var rowExist = ''
        rowExist = document.getElementById(rowType + 'Ta-' + firGnum);
        if (rowExist) {
            var preProcess = '';
            $('#' + rowType + 'Ta-' + firGnum + '> :last-child').append('<span id=proGcomma-' + secGnum + '>, </span>');
            $('#' + rowType + 'Ta-' + firGnum + '> :last-child').append('<span id=proGName-' + secGnum + '>' + processName + '</span>');
        } else {
            var inRow = insertRowTable(rowType, firGnum, secGnum, paramGivenName, paraIdentifier, paraFileType, paraQualifier, processName);
            $('#' + rowType + 'sTable > tbody:last-child').append(inRow);
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
    if (pObj == window) {
        d3.select("#mainG").append("g")
            .attr("id", "c--" + fClick + "_" + sClick)
            .attr("transform", "translate(" + (candidates[fClickOrigin][0] + candidates[sClick][0]) / 2 + "," + (candidates[fClickOrigin][1] + candidates[sClick][1]) / 2 + ")")
            .attr("g_from", candidates[fClickOrigin][2])
            .attr("g_to", candidates[sClick][2])
            .attr("IO_from", fClick)
            .attr("IO_to", sClick)
            .on("mousedown", removeElement)
            .on("mouseover", delMouseOver)
            .on("mouseout", delMouseOut)
            .append("circle")
            .attr("id", "delc--" + fClick + "_" + sClick)
            .attr("class", "del")
            .attr("cx", 0)
            .attr("cy", 0)
            .attr("r", ior)
            .attr("fill", "#E0E0E0")
            .attr('fill-opacity', 0.4)

        d3.select("#c--" + fClick + "_" + sClick)
            .append("text")
            .attr("id", "del--" + fClick + "_" + sClick)
            .attr('font-family', "FontAwesome, sans-serif")
            .attr('font-size', '1em')
            .attr("x", -5)
            .attr("y", 5)
            .text('\uf014')
            .style("opacity", 0.4)
    }

    pObj.edges.push(prefix + pObj.fClick + "_" + prefix + pObj.sClick)

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

function removeEdge(delID) {
    if (delID !== undefined) {
        deleteID = delID;
    }

    d3.select("#" + deleteID).remove() //eg. c--o-inPro-1-9-0_i-10-0-9-1
    d3.select("#" + deleteID.split("--")[1]).remove()
    edges.splice(edges.indexOf(deleteID.split("--")[1]), 1);
    var firstParamId = deleteID.split("--")[1].split("_")[0];
    var secondParamId = deleteID.split("--")[1].split("_")[1];
    var paramType = firstParamId.split("-")[1] //inPro or outPro
    var delsecGnum = secondParamId.split("-")[4] //gNum
    var delGnum = firstParamId.split("-")[4] //gNum

    //input/output param has still edge/edges
    //remove process name from pipeline details table
    if (edges.searchFor(firstParamId)) {
        if (paramType === 'inPro') {
            //$('#inputTa-' + delGnum + '> :last-child').append('<span id=proGName-' + secGnum + '>' + processName + '</span>');
            $('#inputTa-' + delGnum + '> :last-child > ' + '#proGName-' + delsecGnum).remove();
            if ($('#inputTa-' + delGnum + '> :last-child > ' + '#proGcomma-' + delsecGnum)[0]) {
                $('#inputTa-' + delGnum + '> :last-child > ' + '#proGcomma-' + delsecGnum).remove();
            } else {
                $('#inputTa-' + delGnum + '> :last-child > :first-child').remove();
            }

        }
    }

    //input/output param has no edge any more
    if (!edges.searchFor(firstParamId)) {
        d3.selectAll("#" + firstParamId).attr("connect", 'single')
        //remove row from pipeline details table
        if (paramType === 'inPro') {
            $('#inputTa-' + delGnum).remove() //gNum
            resetOriginal(paramType, firstParamId); //
        } else if (paramType === 'outPro') {
            $('#outputTa-' + delGnum).remove() //gNum
            resetOriginal(paramType, firstParamId);
        }
    }
    //process has no edge any more
    if (!edges.searchFor(secondParamId)) {
        d3.selectAll("#" + secondParamId).attr("connect", 'single')
    }


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

function rename() {
    renameTextID = this.id;
    renameText = d3.select("#" + this.id).attr('name');
    renameTextClassType = d3.select("#" + this.id).attr('classType');
    renameTextDefVal = d3.select("#" + this.id).attr('defVal');
    renameTextDropDown = d3.select("#" + this.id).attr('dropDown');
    body = document.body;
    bodyW = body.offsetWidth;
    bodyH = body.scrollHeight;
    $('#renameModal').modal("show");
}


function changeName() {
    newName = document.getElementById("mRenName").value;
    newName = cleanProcessName(newName);
    d3.select("#" + renameTextID).attr('name', newName)
    //save renamed pipeline circle name
    var pipeModule = document.getElementById(renameTextID).parentElement;
    var pipeModulePipeId = pipeModule.className.baseVal;
    if (pipeModulePipeId.match(/g-p(.*)/) && pipeModule.id.match(/g-(.*)/)) {
        window["pObj" + pipeModule.id.match(/g-(.*)/)[1]].lastPipeName = newName
        newNameShow = truncateName(newName, 'pipelineModule');
    } else{
        newNameShow = truncateName(newName, d3.select("#" + renameTextID).attr('class'));
    }
    d3.select("#" + renameTextID).text(newNameShow)

    //update pipeline details table
    proType = $('#' + renameTextID).parent().attr('class').split('-')[1];
    var gNumP = renameTextID.split('-')[1];
    $('span[id="proGName-' + gNumP + '\"]').text(newName);
    if (proType === 'inPro') {
        $('#input-PName-' + renameTextID.split('-')[1]).text(newName); //id=input-PName-0
    } else if (proType === 'outPro') {
        $('#output-PName-' + renameTextID.split('-')[1]).text(newName); //id=output-PName-0
    }


    processList[document.getElementById(renameTextID).parentElement.id] = newName
    processListNoOutput[document.getElementById(renameTextID).parentElement.id] = newName
    document.getElementById(renameTextID).parentElement.id
}


function getInfo() {
    className = document.getElementById(this.id).className.baseVal.split("-");
    infoID = className[1];
    if (this.id.match(/info-/)){
        gNumInfo = this.id.replace("info-", "");
    }else { //for pipeline module windows
        gNumInfo = this.id.replace("info", "");
    }
    console.log(gNumInfo)
    $('#addProcessModal').modal("show");
}

function getInfoPipe() {
    className = document.getElementById(this.id).className.baseVal.split("-");
    infoID = className[1];
    if (this.id.match(/info-/)){
        gNumInfo = this.id.replace("info-", "");
    }else {
        gNumInfo = this.id.replace("info", "");
    }
    $('#selectPipelineModal').modal("show");
}

function removeElement(delID) {
    if (delID !== undefined) {
        deleteID = delID;
    } else {
        deleteID = this.id;
    }
    body = document.body
    bodyW = body.offsetWidth
    bodyH = body.offsetHeight

    if (!binding) {
        $('#confirmD3Modal').modal("show");

    }
}


function download(text) {
    var filename = $('#pipeline-title').val() + '.nf';
    var element = document.createElement('a');
    element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
    element.setAttribute('download', filename);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
}


function refreshCreatorData(pipeline_id) {
    var getPipelineD = [];
    getPipelineD.push({ name: "id", value: pipeline_id });
    getPipelineD.push({ name: "p", value: 'loadPipeline' });
    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: getPipelineD,
        async: true,
        success: function (s) {
            $('#creatorInfoPip').css('display', "block");
            if (s[0]) {
                $('#ownUserNamePip').text(s[0].username);
                $('#datecreatedPip').text(s[0].date_created);
                $('.lasteditedPip').text(s[0].date_modified);
            }
        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
}


//Revision is not required for advanced options, description
function saveDetails() {
    var id = $("#pipeline-title").attr('pipelineid');
    var summary = encodeURIComponent($('#pipelineSum').val());
    var group_id = $('#groupSelPipe').val();
    var perms = $('#permsPipe').val();
    var pin = $('#pin').is(":checked").toString();
    var pin_order = $('#pin_order').val();
    var publish = $('#publishPipe').val();
    var pipGroup = $('#pipeGroupAll').val()
    createSaveNodes();
    var data = {
        p: "savePipelineDetails",
        id: id,
        summary: summary,
        group_id: group_id,
        perms: perms,
        pin: pin,
        pin_order: pin_order,
        publish: publish,
        pipeline_group_id: pipGroup,
        nodes: saveNodes
    };
    if (id !== '') {
        var saveDetails = getValues(data);
        if (saveDetails) {
            $('#autosave').text('Details are saved');
        }
    }
}

function hasDuplicates(array) {
    var valuesSoFar = [];
    var duplicatesSoFar = [];
    for (var i = 0; i < array.length; ++i) {
        var value = array[i];
        if (valuesSoFar.indexOf(value) !== -1) {
            duplicatesSoFar.push(value);
        }
        valuesSoFar.push(value);
    }
    //no duplicates
    if (duplicatesSoFar.length === 0) {
        return [false, duplicatesSoFar];
    } else {
        return [true, duplicatesSoFar];
    }
}

function checkNameUnique(processList) {
    var warnUserUnique = false;
    var warnUserText = '';
    var processListArray = [];
    var keys = Object.keys(processList);
    for (var i = 0; i < keys.length; i++) {
        processListArray.push(processList[keys[i]])
    }
    var duplicatesSoFar = [];
    var duplicates = false;
	      [duplicates, duplicatesSoFar] = hasDuplicates(processListArray);
    if (duplicates === true) {
        var warnUserText = "Process and input parameter names should be unique in pipeline. Please modify following names: ";
        $.each(duplicatesSoFar, function (element) {
            if (element !== 0) {
                warnUserText = warnUserText + ", ";
            }
            warnUserText = warnUserText + duplicatesSoFar[element];
        });
        $('#warnSection').css('display', 'inline');
        $('#warnArea').html(warnUserText);
    } else {
        $('#warnSection').css('display', 'none');
    }
}

//if checkvalue not exist then create red border 
function warnUserRedBorder(borderId, checkValue) {
    if (!checkValue || checkValue == "") {
        if (!$(borderId).hasClass('borderClass')) {
            $(borderId).addClass('borderClass');
        }
    } else {
        if ($(borderId).hasClass('borderClass')) {
            $(borderId).removeClass('borderClass');
        }
    }
}
//if checkvalue exist clean red border 
function cleanRedBorder(borderId, checkValue) {
    if (checkValue || checkValue != "") {
        if ($(borderId).hasClass('borderClass')) {
            $(borderId).removeClass('borderClass');
        }
    }
}
//clean border in case value is entered
$('#pipeGroupAll').change(function () {
    var checkVal = $('#pipeGroupAll').val();
    cleanRedBorder('#pipeGroupAll', checkVal)
});
$('#pipeline-title').change(function () {
    var checkVal = $('#pipeline-title').val();
    cleanRedBorder('#pipeline-title', checkVal)
});

function createSaveNodes(){
    saveNodes = {}
    processListDb = [];
    pipelineListDb = [];
    for (var key in processList) {
        t = d3.transform(d3.select('#' + key).attr("transform")),
            x = t.translate[0]
        y = t.translate[1]
        gClass = document.getElementById(key).className.baseVal
        prosessID = gClass.split("-")[1];
        //if it is pipeline module prosessID will start with p(pipeNum) (eg.p73) otherwise it is (proNum) (eg.11)
        var gNum = key.split("-")[1];
        //fix bug while saving on dragging
        if (prosessID.match(/(.*) dragging/)) {
            prosessID = prosessID.match(/(.*) dragging/)[1];
        }
        // save defVal and dropDown of input parameters if exist
        var defVal = $("#text-" + gNum).attr("defVal");
        var dropDown = $("#text-" + gNum).attr("dropDown");
        var processModule = {};
        if (defVal) {
            processModule["defVal"] = defVal;
        }
        if (dropDown) {
            processModule["dropDown"] = dropDown;
        }
        processName = processList[key]
        saveNodes[key] = [x, y, prosessID, processName, processModule]
        if (prosessID.match(/^p(.*)/)) {
            var pipeID= prosessID.match(/^p(.*)/)[1];
            pipelineListDb.push(pipeID);
        } else if (!prosessID.match(/(.*)Pro/)) {
            processListDb.push(prosessID);
        }
    }
}


//Save pipeline
function save() {
    saveMainG = {}
    //check if process and parameter names are unique in pipeline
    checkNameUnique(processListNoOutput);
    createSaveNodes();
    Maint = d3.transform(d3.select('#' + "mainG").attr("transform")),
        Mainx = Maint.translate[0]
    Mainy = Maint.translate[1]
    Mainz = Maint.scale[0]
    var svgW = $("#container").width();
    var svgH = $("#container").height();
    sName = document.getElementById("pipeline-title").value;
    warnUserRedBorder('#pipeline-title', sName)
    var pipelineSummary = encodeURIComponent($('#pipelineSum').val());
    var group_id = $('#groupSelPipe').val();
    var perms = $('#permsPipe').val();
    var pin = $('#pin').is(":checked").toString();
    var pin_order = $('#pin_order').val();
    var publish = $('#publishPipe').val();
    var script_mode_header = $('#script_mode_pipe_header').val();
    var script_mode_footer = $('#script_mode_pipe_footer').val();
    var script_pipe_header = getScriptEditor('editorPipeHeader');
    var script_pipe_footer = getScriptEditor('editorPipeFooter');
    pipeline_group_id = $('#pipeGroupAll').val();
    var pipeGroupWarn = false;
    if (!pipeline_group_id || pipeline_group_id == "") {
        var pipeGroupWarn = true;
    }
    warnUserRedBorder('#pipeGroupAll', pipeline_group_id)
    id = 0
    if (sName !== "" && dupliPipe === false) {
        id = $("#pipeline-title").attr('pipelineid');
    } else if (sName !== "" && dupliPipe === true) {
        id = '';
        sName = sName + '-copy'
        perms = "3";
    }

    saveMainG["mainG"] = [Mainx, Mainy, Mainz, svgW, svgH]
    savedList = [{
        "name": sName
	      }, {
        "id": id
	      }, {
        "nodes": saveNodes
	      }, saveMainG, {
        "edges": edges
	      }, {
        "summary": pipelineSummary
	      }, {
        "group_id": group_id
	      }, {
        "perms": perms
	      }, {
        "pin": pin
	      }, {
        "pin_order": pin_order
	      }, {
        "publish": publish
	      }, {
        "script_pipe_header": script_pipe_header
	      }, {
        "script_pipe_footer": script_pipe_footer
	      }, {
        "script_mode_header": script_mode_header
	      }, {
        "script_mode_footer": script_mode_footer
	      }, {
        "pipeline_group_id": pipeline_group_id
	      }, {
        "process_list": processListDb.toString()
	      }, {
        "pipeline_list": pipelineListDb.toString()
	      }];
    if (createPipeRev === "true") {
        return [savedList, id, sName];
    } else {
        //A. Add new pipeline
        if (sName !== "" && id === '' && !pipeGroupWarn) {
            var maxPipeline_gid = getValues({ p: "getMaxPipeline_gid" })[0].pipeline_gid;
            var newPipeline_gid = parseInt(maxPipeline_gid) + 1;
            savedList.push({ "pipeline_gid": newPipeline_gid });
            savedList.push({ "rev_id": 0 });
            savedList.push({ "rev_comment": "" });
            sl = JSON.stringify(savedList);
            var ret = getValues({ p: "saveAllPipeline", dat: sl });
            $("#pipeline-title").attr('pipelineid', ret.id);
            pipeline_id = $('#pipeline-title').attr('pipelineid'); //refresh pipeline_id
            $('#pipeGr-' + pipeline_group_id).append('<li><a href="index.php?np=1&id=' + ret.id + '" class="pipelineItems" draggable="false" id="pipeline-' + ret.id + '"><i class="fa fa-angle-double-right"></i>' + truncateName(sName, 'sidebarMenu') + '</a></li>');
            //keep record for last group_id
            $('#pipeGroupAll').attr("pipe_group_id", pipeline_group_id);
            if (dupliPipe === true) {
                setTimeout(function () { window.location.replace("index.php?np=1&id=" + ret.id); }, 0);
                dupliPipe = false;
            }
            $('#autosave').text('All changes saved');
        }
        //B. pipeline already exist
        else if (sName !== "" && id !== '' && !pipeGroupWarn) {
            var warnUserPipe = false;
            var warnPipeText = '';
            var numOfProject = '';
            var numOfProjectPublic = '';
              [warnUserPipe, warnPipeText, numOfProject, numOfProjectPublic] = checkRevisionPipe(id);
            //B.1 allow updating on existing pipeline
            if (warnUserPipe === false || saveOnExist === true) {
                sl = JSON.stringify(savedList);
                var ret = getValues({ p: "saveAllPipeline", dat: sl });
                pipeline_id = $('#pipeline-title').attr('pipelineid'); //refresh pipeline_id
                refreshCreatorData(pipeline_id);
                var oldPipeGroupId = $('#pipeGroupAll').attr("pipe_group_id");
                if (oldPipeGroupId != pipeline_group_id) {
                    $('#pipeline-' + pipeline_id).parent().remove();
                    $('#pipeGr-' + pipeline_group_id).append('<li><a href="index.php?np=1&id=' + pipeline_id + '" class="pipelineItems" draggable="false" id="pipeline-' + pipeline_id + '"><i class="fa fa-angle-double-right"></i>' + truncateName(sName, 'sidebarMenu') + '</a></li>');
                }
                //keep track of latest pipeline_group_id
                $('#pipeGroupAll').attr("pipe_group_id", pipeline_group_id);

                var numRev = $("#pipeRev option").length;
                if (numRev === 0 || numRev === 1) { //sidebar name change
                    document.getElementById('pipeline-' + pipeline_id).innerHTML = '<i class="fa fa-angle-double-right"></i>' + truncateName(sName, 'sidebarMenu');
                }
                $('#autosave').text('All changes saved');
            }
            //B.2 allow save on new revision
            else if (warnUserPipe === true) {
                // ConfirmYesNo pipeline modal 
                $('#confirmRevision').off();
                $('#confirmRevision').on('show.bs.modal', function (event) {
                    $(this).find('form').trigger('reset');
                    $('#confirmYesNoText').html(warnPipeText);
                    if (numOfProjectPublic === 0 || usRole === "admin") {
                        $('#saveOnExist').css('display', 'inline');
                        if (usRole == "admin" && !(numOfProjectPublic === 0)){
                            $('#saveOnExist').attr('class', 'btn btn-danger');
                        }
                    }
                });
                $('#confirmRevision').on('hide.bs.modal', function (event) {
                    $('#saveOnExist').css('display', 'none');
                    $('#saveOnExist').attr('class', 'btn btn-warning');
                });

                $('#confirmRevision').on('click', '.cancelRev', function (event) {
                    $('#autosave').text('Changes not saved!');
                });
                $('#confirmRevision').on('click', '#saveOnExist', function (event) {
                    sl = JSON.stringify(savedList);
                    var ret = getValues({ p: "saveAllPipeline", dat: sl });
                    pipeline_id = $('#pipeline-title').attr('pipelineid'); //refresh pipeline_id
                    refreshCreatorData(pipeline_id);
                    var oldPipeGroupId = $('#pipeGroupAll').attr("pipe_group_id");
                    if (oldPipeGroupId != pipeline_group_id) {
                        $('#pipeline-' + pipeline_id).parent().remove();
                        $('#pipeGr-' + pipeline_group_id).append('<li><a href="index.php?np=1&id=' + pipeline_id + '" class="pipelineItems" draggable="false" id="pipeline-' + pipeline_id + '"><i class="fa fa-angle-double-right"></i>' + truncateName(sName, 'sidebarMenu') + '</a></li>');

                    }
                    //keep track of latest pipeline_group_id
                    $('#pipeGroupAll').attr("pipe_group_id", pipeline_group_id);
                    var numRev = $("#pipeRev option").length;
                    if (numRev === 0 || numRev === 1) { //sidebar name change
                        document.getElementById('pipeline-' + pipeline_id).innerHTML = '<i class="fa fa-angle-double-right"></i>' + truncateName(sName, 'sidebarMenu');
                    }
                    saveOnExist = true;
                    $('#autosave').text('All changes saved');
                    $('#confirmRevision').modal('hide');

                });


                $('#confirmRevision').on('click', '#saveRev', function (event) {
                    var confirmformValues = $('#confirmRevision').find('input');
                    var revCommentData = confirmformValues.serializeArray();
                    var revComment = revCommentData[0].value;
                    if (revComment === '') { //xxx warn user to enter comment
                    } else if (revComment !== '') {
                        var pipeline_gid = getValues({ p: "getPipeline_gid", "pipeline_id": id })[0].pipeline_gid;
                        var maxPipRev_id = getValues({ p: "getMaxPipRev_id", "pipeline_gid": pipeline_gid })[0].rev_id;
                        var newPipRev_id = parseInt(maxPipRev_id) + 1;
                        savedList[1].id = ''
                        savedList[7].perms = '3';
                        savedList.push({ "pipeline_gid": pipeline_gid });
                        savedList.push({ "rev_comment": revComment });
                        savedList.push({ "rev_id": newPipRev_id });
                        sl = JSON.stringify(savedList);
                        var ret = getValues({ p: "saveAllPipeline", dat: sl });
                        $('#confirmRevision').modal('hide');
                        $('#autosave').text('Changes saved on new revision');
                        setTimeout(function () { window.location.replace("index.php?np=1&id=" + ret.id); }, 700);
                    }
                });
                $('#confirmRevision').modal('show');
                pipeline_id = $('#pipeline-title').attr('pipelineid'); //refresh pipeline_id
                refreshCreatorData(pipeline_id);
            }
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
    var process_id = id;
    var defVal = null;
    var dropDown = null;
    if (processModules != null && processModules != {} && processModules != "") {
        if (processModules.defVal) {
            defVal = processModules.defVal;
        }
        if (processModules.dropDown) {
            dropDown = processModules.dropDown;
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
        drawParam(name, process_id, id, kind, sDataX, sDataY, paramId, pName, classtoparam, init, pColor, defVal, dropDown, pObj)
        pObj.processList[("g" + MainGNum + "-" + pObj.gNum)] = name
        pObj.processListNoOutput[("g" + MainGNum + "-" + pObj.gNum)] = name
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
        drawParam(name, process_id, id, kind, sDataX, sDataY, paramId, pName, classtoparam, init, pColor, defVal, dropDown, pObj)
        pObj.processList[("g" + MainGNum + "-" + pObj.gNum)] = name
        pObj.gNum = pObj.gNum + 1

    } else {
        addProPipeTab(id)
        //--Pipeline details table ends---
        pObj.inputs = getValues({ p: "getInputsPP", "process_id": id })
        pObj.outputs = getValues({ p: "getOutputsPP", "process_id": id })
        //gnum uniqe, id same id (Written in class) in same type process
        pObj.g = d3.select("#mainG" + MainGNum).append("g")
            .attr("id", "g" + MainGNum + "-" + pObj.gNum)
            .attr("class", "g" + MainGNum + "-" + id)
            .attr("transform", "translate(" + (sDataX) + "," + (sDataY) + ")")
            .on("mouseover", mouseOverG)
            .on("mouseout", mouseOutG)
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
            .on("mouseover", scMouseOver)
            .on("mouseout", scMouseOut)
            .call(drag)
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
            .on("mouseover", scMouseOver)
            .on("mouseout", scMouseOut)
            .call(drag)
        if (pObj == window) {
            pObj.g.append("text").attr("id", "text" + MainGNum + "-" + pObj.gNum)
                .datum([{
                    cx: 0,
                    cy: 0
			        }])
                .attr('font-family', "FontAwesome, sans-serif")
                .attr('font-size', '0.9em')
                .attr("x", -6)
                .attr("y", 15)
                .text('\uf040')
                .on("mousedown", rename)
            //gnum(written in id): uniqe,
            pObj.g.append("text")
                .attr("id", "del" + MainGNum + "-" + pObj.gNum)
                .attr('font-family', "FontAwesome, sans-serif")
                .attr('font-size', '1em')
                .attr("x", -6)
                .attr("y", r + ior / 2)
                .text('\uf014')
                .style("opacity", 0.2)
                .on("mousedown", removeElement)
        }

        pObj.g.append("text")
            .attr("id", "info" + MainGNum + "-" + pObj.gNum)
            .attr("class", "info" + MainGNum + "-" + id)
            .attr('font-family', "FontAwesome, sans-serif")
            .attr('font-size', '1em')
            .attr("x", -6)
            .attr("y", -1 * (r + ior / 2 - 10))
            .text('\uf013')
            .style("opacity", 0.2)
            .on("mousedown", getInfo)
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
                .on("mousedown", IOconnect)
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
                .on("mousedown", IOconnect)
        }
        pObj.processList[("g" + MainGNum + "-" + pObj.gNum)] = name
        pObj.processListNoOutput[("g" + MainGNum + "-" + pObj.gNum)] = name
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
