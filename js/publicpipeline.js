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

function dragStart(event) {
    event.dataTransfer.setData("Text", event.target.id);
}

function dragging(event) {
    event.preventDefault();
}

function allowDrop(event) {
    event.preventDefault();
}


refreshDataset()

function refreshDataset() {
    processData = getValues({
        p: "getProcessData"
    })
    savedData = getValues({
        p: "getSavedPipelines"
    })
    parametersData = getValues({
        p: "getAllParameters"
    })

}
var sData = "";
var svg = "";
var mainG = "";

function createSVG() {
    edges = []
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
    edges = []
    candidates = []
    saveNodes = []

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

    mainG = d3.select("#container").select("svg").append("g")
        .attr("id", "mainG")
        .attr("transform", "translate(" + 0 + "," + 0 + ")")


}

function startzoom() {
    d3.select("#container").call(zoom)
}

var timeoutId = 0;

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

function newPipeline() {
    createSVG()
    $('#pipeline-title').val('');
    $('#pipeline-title').attr('pipelineid', '');
    resizeForText.call($inputText, $inputText.attr('placeholder'));
}


function openPipeline(id) {
    createSVG()
    sData = getValues({
        p: "loadPipeline",
        id: id
    }) //all data from biocorepipe_save table

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
                window[newMainGnum].gNum = "";
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
                var newID = getNewNodeId(ed, eds[0])
                if (newID) {
                    eds[0] = newID;
                    addCandidates2DictForLoad(eds[0])
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
    //        .on("mouseover", scMouseOver)
    //        .on("mouseout", scMouseOut)
    //        .call(drag)
    if (defVal) {
        $("#text-" + pObj.gNum).attr('defVal', defVal)
    }
    if (dropDown) {
        $("#text-" + pObj.gNum).attr('dropDown', dropDown)
    }
}

function insertRowTable(rowType, firGnum, secGnum, paramGivenName, paraIdentifier, paraFileType, paraQualifier, processName) {
    return '<tr id=' + rowType + 'Ta-' + firGnum + '><td id="' + rowType + '-PName-' + firGnum + '" scope="row">' + paramGivenName + '</td><td>' + paraIdentifier + '</td><td>' + paraFileType + '</td><td>' + paraQualifier + '</td><td> <span id="proGName-' + secGnum + '">' + processName + '</span></td></tr>'
}

function insertProRowTable(process_id, procName, procDesc, procRev) {
    return '<tr id=procTa-' + process_id + '><td scope="row">' + procName + '</td><td>' + procRev + '</td><td>' + procDesc + '</td></tr>'
}

//--Pipeline details table --
function addProPipeTab(id) {
    var procData = processData.filter(function (el) { return el.id == id });
    var procName = procData[0].name;
    var procDesc = truncateName(procData[0].summary, 'processTable');
    var procRev = procData[0].rev_id;
    var proRow = insertProRowTable(id, procName, procDesc, procRev);
    var rowExistPro = '';
    var rowExistPro = document.getElementById('procTa-' + id);
    if (!rowExistPro) {
        $('#processTable > tbody:last-child').append(proRow);
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



function findType(id) {
    parameter = parametersData.filter(function (el) {
        return el.id == id
    })
    return parameter[0].file_type
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

function searchedTypeParam(type) {
    if (type == "input") {
        return "connect_to_input"
    } else {
        return "connect_to_output"
    }
}

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
            if (className[0]) {
                $('#mainG' + MainGNum).find("." + className[0]).filter("." + cand).attr("status", "candidate")
            }
            $('#mainG' + MainGNum).find("." + candParam).attr("status", "candidate")

            var givenNamePP = document.getElementById(this.id).getAttribute("name")
            var paraID = document.getElementById(this.id).id.split("-")[3]
            var paraData = parametersData.filter(function (el) {
                return el.id == paraID
            })
            var paraFileType = paraData[0].file_type
            var paraQualifier = paraData[0].qualifier
            var paraName = paraData[0].name
            var givenNamePPText = '';
            if (givenNamePP) {
                var givenNamePPText = 'Name: <em>' + givenNamePP + '</em><br/>';
            }
            if (paraQualifier !== 'val') {
                tooltip.html(processTag + 'Identifier: <em>' + paraName + '</em><br/>' + givenNamePPText + 'File Type: <em>' + paraFileType + '</em><br/>Qualifier: <em>' + paraQualifier + '</em>')
            } else {
                tooltip.html(processTag + 'Identifier: <em>' + paraName + '</em><br/>' + givenNamePPText + 'Qualifier: <em>' + paraQualifier + '</em>')
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

function startBinding(clasNames, cand, selectedIO) {
    parentg = d3.select("#" + selectedIO).attr("parentG")

    d3.selectAll("circle[type ='I/O']").attr("status", "noncandidate")

    if (className[0] === "connect_to_input") {
        conToInput()
    } else if (className[0] === "connect_to_output") {
        conToOutput()
    } else {
        d3.selectAll("." + className[0]).filter("." + cand).attr("status", "candidate")
    }

    d3.selectAll("circle[parentG =" + parentg + "]").attr("status", "noncandidate")
    d3.selectAll("#" + selectedIO).attr("status", "selected")
    d3.selectAll("line").attr("status", "hide")
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
function stopBinding() {
    firstid = d3.select("circle[status ='selected']")[0][0].id
    d3.selectAll("line").attr("status", "standard")
    if (selectedIO === firstid) {
        firstid = d3.select("#" + firstid).attr("status", "mouseon")
        d3.selectAll("." + className[0]).filter("." + cand).attr("status", "candidate")
        d3.select("#del-" + selectedIO.split("-")[4]).style("opacity", 1)
    } else {
        secondid = d3.select("circle[status ='posCandidate']")[0][0].id
        createEdges(firstid, secondid)

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
        //--Pipeline details table ---
        addProPipeTab(id, pObj.gNum + prefix, name, pObj);
        //--ProcessPanel (where process options defined)
        //create process circle
        pObj.inputs = getValues({ p: "getInputsPP", "process_id": id })
        pObj.outputs = getValues({ p: "getOutputsPP", "process_id": id })

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


function loadPipelineDetails(pipeline_id) {
    var getPipelineD = [];
    getPipelineD.push({ name: "id", value: pipeline_id });
    getPipelineD.push({ name: "p", value: 'loadPipeline' });
    $.ajax({
        type: "POST",
        url: "ajax/ajaxquery.php",
        data: getPipelineD,
        async: true,
        success: function (s) {
            if (s[0]) {
                $('#pipeline-title').text(s[0].name);
                $('#pipelineSum').val(s[0].summary);
                $('#creatorInfoPip').css('display', "block");
                $('#ownUserNamePip').text(s[0].username);
                $('#datecreatedPip').text(s[0].date_created);
                $('.lasteditedPip').text(s[0].date_modified);
                openPipeline(pipeline_id);
                $('#pipelineSum').attr('disabled', "disabled");
            }
        },
        error: function (errorThrown) {
            alert("Error: " + errorThrown);
        }
    });
};



$(document).ready(function () {
    var pipeline_id = $('#pipeline-title').attr('pipelineid');
    if (pipeline_id !== '') {
        loadPipelineDetails(pipeline_id);
    }





});
