// function that are used in pipelineD3.js, runpipeline.js, nextflowText.js

//edges-> all edge list, nullId-> process input/output id that not exist in the d3 diagrams 
function getNewNodeId(nullId, pObj) {
    var MainGNum = "";
    var prefix = "";
    if (pObj != window) {
        //load workflow of pipeline modules 
        MainGNum = pObj.MainGNum;
        prefix = "p" + MainGNum;
    }
    //nullId: i-24-14-20-1
    console.log("nullId",nullId)
    //for pipeline modules: po-475-0-74-36
    var nullProcessInOut = nullId.split("-")[0];
    var nullProcessId = nullId.split("-")[1];
    var nullProcessParId = nullId.split("-")[3];
    var nullProcessGnum = nullId.split("-")[4];
    var nodes =[];

    var checkUniqueAddNullList = function (newNode){
        if (newNode.length === 1) {
            var newNodeId = newNode.attr("id");
            if (newNodeId){
                nullIDList[prefix+nullId]=newNodeId;
                return newNodeId;
            }
        }
        return "";
    }
    
    var getNewNodeIgnoreOrder = function(nullId, MainGNum, prefix, nullProcessGnum){
        var patt = /(.*)-(.*)-(.*)-(.*)-(.*)/;
        var nullIdRegEx = new RegExp(nullId.replace(patt, '$1-$2-' + '(.*)' + '-$4-$5'), 'g');
        var newNode = $('#g' + MainGNum + "-" + nullProcessGnum).find("circle").filter(function () {
            return this.id.match(nullIdRegEx);
        });
        return checkUniqueAddNullList(newNode);
    }

    var getNewNodeIgnoreOrderProcessRev = function(nullId, MainGNum, prefix, nullProcessGnum, nullProcessId){
        var patt = /(.*)-(.*)-(.*)-(.*)-(.*)/;
        var nullIdRegEx = new RegExp(nullId.replace(patt, '$1-' + '(.*)' + '(.*)' + '-$4-$5'), 'g');
        var newNode = $('#g' + MainGNum + "-" + nullProcessGnum).find("circle").filter(function () {
            return this.id.match(nullIdRegEx);
        });
        if (newNode.length === 1) {
            return checkUniqueAddNullList(newNode);
        } else {
            var proRevs = [];
            var proRevData = getValues({ p: "getProcessRevision", "process_id": nullProcessId });
            if (proRevData) {
                if (!$.isEmptyObject(proRevData)) {
                    for (var i = 0; i < proRevData.length; i++) {
                        proRevs.push(proRevData[i].id);
                    }
                    if (!$.isEmptyObject(proRevs)) {
                        newNode = $('#g' + MainGNum + "-" + nullProcessGnum).find("circle").filter(function () {
                            var newProId = this.id.split("-")[1];
                            return proRevs.includes(newProId) && this.id.match(nullIdRegEx);
                        });
                        return checkUniqueAddNullList(newNode);
                    }
                }
            }

        }
        return "";
    }


    //A)input/output parameters
    if (nullProcessId == "inPro" || nullProcessId == "outPro" ){
        //connect by using gNum of the input or output parameter 
        var newNodeId = $('#mainG' + MainGNum).find("circle[parentG =g-" + nullProcessGnum + "]").attr("id");
        if (newNodeId){
            nullIDList[prefix+nullId]=newNodeId
            return newNodeId;
        }
    } else {
        //B)pipeline modules
        if (nullProcessInOut === "pi" || nullProcessInOut === "po") {
            var newNode = getNewNodeIgnoreOrder(nullId,MainGNum,prefix, nullProcessGnum);
            if (newNode){
                return newNode;
            } else {
                var newNode = getNewNodeIgnoreOrderProcessRev(nullId,MainGNum,prefix, nullProcessGnum, nullProcessId);
                return newNode;
            }
            //C)processes
        } else {
            //check if parameter is unique for the process node:
            if (nullProcessInOut === "i") {
                nodes = getValues({ p: "getInputsPP", "process_id": nullProcessId });
            } else if (nullProcessInOut === "o") {
                nodes = getValues({ p: "getOutputsPP", "process_id": nullProcessId });
            }  
            if (nodes) {
                if (!$.isEmptyObject(nodes)) {
                    var paraData = nodes.filter(function (el) { return el.parameter_id == nullProcessParId });
                    //get newNodeID  
                    if (paraData.length === 1) {
                        var newNode = getNewNodeIgnoreOrder(nullId,MainGNum,prefix, nullProcessGnum);
                        return newNode;
                    }
                }
            }    
        }

    }
    return "";
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

//resets input/output param if its single
function resetSingleParam() {
    var types = ["inPro", "outPro"]
    for (var i = 0; i < types.length; i++) {
        var singleNodes = $('#mainG').find("g.g-"+types[i]+" > circle[parentG]").filter(function () {
            return $(this).attr("connect") == "single"
        });
        for (var k = 0; k < singleNodes.length; k++) {
            resetOriginal(types[i], singleNodes[k].id)
        }
    }
}

function splitEdges(edge) {
    //p2_7o-48-2-47-12_p2_7i-51-0-47-8 separate into p2_7o-48-2-47-12 and p2_7i-51-0-47-8 by ungreedy regex
    var patt = /(.*)-(.*)-(.*)-(.*)-(.*?)_(.*)-(.*)-(.*)-(.*)-(.*)/
    var first = edge.replace(patt, '$1-$2-$3-$4-$5')
    var sec = edge.replace(patt, '$6-$7-$8-$9-$10')
    return [first, sec]
}

function recoverEdges(firstGNum, lastProID, lastGNum){
    if (window["garbageLines"]){
        var patt = /(.*)-(.*)-(.*)-(.*)-(.*)/;
        for (var i = 0; i < garbageLines.length; i++) {
            var line = garbageLines[i];
            var fNode = "";
            var sNode = "";
            [fNode, sNode] = splitEdges(garbageLines[i]);
            var fProId = fNode.split("-")[1];
            var fProGnum = fNode.split("-")[4];
            var sProId = sNode.split("-")[1];
            var sProGnum = sNode.split("-")[4];

            // replace process-id and gnum for processes
            // replace only gnum for pipeline modules
            if (fProGnum == firstGNum){
                if (lastProID){
                    fNode = fNode.replace(patt, '$1-'+lastProID+'-$3-$4-'+lastGNum)
                } else {
                    //for pipeline modules
                    fNode = fNode.replace(patt, '$1-$2-$3-$4-'+lastGNum)
                }
            }
            if (sProGnum == firstGNum){
                if (lastProID){
                    sNode = sNode.replace(patt, '$1-'+lastProID+'-$3-$4-'+lastGNum);
                } else {
                    //for pipeline modules
                    sNode = sNode.replace(patt, '$1-$2-$3-$4-'+lastGNum);
                }
            }
            if (fProId == "inPro" || fProId == "outPro"){
                //id's of input/output parameter are changing after connection, so get currect id based on gnum.
                fNode = d3.select("#g-" + fProGnum).selectAll("circle[type ='I/O']")[0][0].id;
            }
            if (sProId == "inPro" || sProId == "outPro"){
                sNode = d3.select("#g-" + sProGnum).selectAll("circle[type ='I/O']")[0][0].id;
            }
            createEdges(fNode, sNode, window)
        }
    }
} 