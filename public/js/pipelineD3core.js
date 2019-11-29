// function that are used in both pipelineD3.js and runpipeline.js


//edges-> all edge list, nullId-> process input/output id that not exist in the d3 diagrams 
function getNewNodeId(edges, nullId, MainGNum) {
    //nullId: i-24-14-20-1
    var nullProcessInOut = nullId.split("-")[0];
    var nullProcessId = nullId.split("-")[1];
    var nullProcessParId = nullId.split("-")[3];
    var nullProcessGnum = nullId.split("-")[4];
    var nodes;
    var prefix = "";
    if (MainGNum) {
        prefix = "p" + MainGNum;
    }
    if (nullProcessId == "inPro" || nullProcessId == "outPro" ){
        //connect by using gNum of the input or output parameter 
        var newNodeId = $('#mainG' + MainGNum).find("circle[parentG =g-" + nullProcessGnum + "]").attr("id");
        if (newNodeId){
            nullIDList[prefix+nullId]=newNodeId
            return newNodeId;
        }
    } else {
        //check if parameter is unique for the process node:
        if (nullProcessInOut === "i") {
            if (window.pipeObj["pro_para_inputs_" + nullProcessId]){
                nodes = JSON.parse(window.pipeObj["pro_para_inputs_" + nullProcessId]);
            }
        } else if (nullProcessInOut === "o") {
            if (window.pipeObj["pro_para_inputs_" + nullProcessId]){
                nodes = JSON.parse(window.pipeObj["pro_para_outputs_" + nullProcessId]);
            }
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
                    if (newNodeId){
                        nullIDList[prefix+nullId]=newNodeId
                        return newNodeId;
                    }
                }
            }
        }
    }
    return "";
}