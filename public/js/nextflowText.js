//----------------------------------------------------------------
//creates nextflow text. Requires pipelineD3.js or runpipeline.js
//----------------------------------------------------------------
function gFormat(gText) {
    gPatt = /(.*)-(.*)/
    gText = gText.replace(gPatt, '$1_$2')
    return gText
}

function findFinalSubNode(node) {
    if (ccIDList[node]) {
        return findFinalSubNode(ccIDList[node]);
    } else {
        return node
    }
}

var fillJsonPattern = function(tx, run_log_uuid) {
    var lines = tx.split("\n");
    var fi = "";
    for (var i = 0; i < lines.length; i++) {
        if (lines[i].match(/(.*)\{\{(.*)\}\}(.*)/)) {
            var reg = lines[i].match(/(.*)\{\{(.*)\}\}(.*)/);
            var before = reg[1];
            var patt = reg[2];
            var after = reg[3];
            var relaxedjson = "{" + patt + "}";
            var correctJson = relaxedjson.replace(/(['"])?([a-z0-9A-Z_]+)(['"])?:/g, '"$2": ');
            var res = ""
            if (IsJsonString(correctJson)) {
                var json = JSON.parse(correctJson)
                if (json) {
                    if (json.webpath) {
                        var pubWebPath = $("#basepathinfo").attr("ocpupubweb");
                        if (!pubWebPath) {
                            pubWebPath = "";
                        }
                        if (!run_log_uuid) {
                            run_log_uuid = $("#runVerLog").val();
                        }
                        var link = pubWebPath + "/" + run_log_uuid + "/" + "pubweb" + "/" + json.webpath;
                        res = '"' + link + '"';
                    }
                }
            }
            fi += before + res + after + "\n";
        } else {
            fi += lines[i] + "\n";
        }
    }
    return fi
}


//check if pipeline Module Id is defined as ccID
function checkCopyId(edgelist) {
    var edges = edgelist.slice()
    var trashIdx = [];
    for (var e = 0; e < edges.length; ++e) {
        var firstNode = "";
        var secNode = "";
        [firstNode, secNode] = splitEdges(edges[e]);
        if (ccIDList[firstNode]) {
            if (!trashIdx.includes(e)) {
                trashIdx.push(e)
            }
            var ccIDNode = $("#" + firstNode).attr("ccID");
            if (ccIDNode) {
                if (ccIDNode.match(/,/)) {
                    var ccIDNodeAr = ccIDNode.split(',');
                    //first insert all ccIDs, in the next runs if they dont have more ccID, ccIDList[firstNode] will be executed.
                    for (var i = 0; i < ccIDNodeAr.length; ++i) {
                        edges.push(ccIDNodeAr[i] + "_" + secNode)
                    }
                } else {
                    edges.push(ccIDList[firstNode] + "_" + secNode)
                }
            }
        } else if (ccIDList[secNode]) {
            if (!trashIdx.includes(e)) {
                trashIdx.push(e)
            }
            var ccIDNode = $("#" + secNode).attr("ccID");
            if (ccIDNode) {
                if (ccIDNode.match(/,/)) {
                    var ccIDNodeAr = ccIDNode.split(',');
                    for (var i = 0; i < ccIDNodeAr.length; ++i) {
                        edges.push(firstNode + "_" + ccIDNodeAr[i])
                    }
                } else {
                    edges.push(firstNode + "_" + ccIDList[secNode])
                }
            }
        }
        //        if (!ccIDList[firstNode] && !ccIDList[secNode]) {
        //            edges.push(firstNode + "_" + secNode)
        //        }
    }
    //clean array
    for (var i = trashIdx.length - 1; i >= 0; i--)
        edges.splice(trashIdx[i], 1);

    //check equality
    if (JSON.stringify(edges) == JSON.stringify(edgelist)) {
        return edges
    } else {
        return checkCopyId(edges)
    }
}






function getMainEdges(pObj) {
    //remove inPro, outPro from edges
    var allEdges = pObj.edges.slice();
    var mainEdges = [];
    for (var e = 0; e < allEdges.length; e++) {
        if (allEdges[e].indexOf("inPro") === -1 && allEdges[e].indexOf("outPro") === -1) { //if not exist -1,
            //swap to make sure, first node is output (for simplification)
            var firstNode = "";
            var secNode = "";
            [firstNode, secNode] = splitEdges(allEdges[e]);
            if (firstNode.match(/i/)) {
                mainEdges.push(secNode + '_' + firstNode);
            } else {
                mainEdges.push(allEdges[e]);
            }
        }
    }
    return mainEdges;
}

function getMergedProcessList() {
    var proList = $.extend(true, {}, window.processList);
    for (var p = 0; p < piGnumList.length; p++) {
        var piGnum = piGnumList[p];
        var piGnums = piGnum.split("_")
        var lastpipeName = "";
        for (var k = 0; k < piGnums.length; k++) {
            var selectedGnums = piGnums.slice(0, k + 1);
            var mergedGnum = selectedGnums.join("_")
            if (lastpipeName) lastpipeName += "_"
            lastpipeName += window["pObj" + mergedGnum].lastPipeName;
        }
        var proListPipe = $.extend(true, {}, window["pObj" + piGnum].processList);
        for (var key in proListPipe) {
            proListPipe[key] = lastpipeName + "_" + proListPipe[key]
        }
        proList = $.extend({}, proList, proListPipe);
    }
    return proList
}

//takes edges array and replaces nodes that defined in nullIDList and return same array
function replaceNullIDEdges(edges) {
    for (var i = 0; i < edges.length; ++i) {
        var firstNode = "";
        var secNode = "";
        [firstNode, secNode] = splitEdges(edges[i]);
        if (nullIDList[firstNode]) {
            edges[i] = nullIDList[firstNode] + "_" + secNode;
        } else if (nullIDList[secNode]) {
            edges[i] = firstNode + "_" + nullIDList[secNode];
        }
    }
    return edges
}
//merge Edges for each pipelineModule type=main or all
function getMergedEdges(type) {
    if (type === "main") {
        var edges = getMainEdges(window);
        for (var p = 0; p < piGnumList.length; p++) {
            edges = edges.concat(getMainEdges(window["pObj" + piGnumList[p]]));
        }
        //all edges instead of input params in pipeline modules.    
    } else if (type === "all") {
        var edges = window.edges.slice();
        for (var p = 0; p < piGnumList.length; p++) {
            var tmpEdges = window["pObj" + piGnumList[p]].edges.slice();;
            var tmpEdgesFinal = [];
            for (var e = 0; e < tmpEdges.length; e++) {
                if (tmpEdges[e].indexOf("inPro") === -1) { //if not exist -1,
                    tmpEdgesFinal.push(tmpEdges[e]);
                }
            }
            edges = edges.concat(tmpEdgesFinal);
        }
    }
    //replace edges if the found in the nullIDList
    edges = replaceNullIDEdges(edges)
    return edges
}

//for pipeline modules append pipeline id to gnum
function getInOutGnum(edge) {
    var outGnum = '';
    var inGnum = '';
    var patt = /(.*)-(.*)-(.*)-(.*)-(.*?)_(.*)-(.*)-(.*)-(.*)-(.*)/;
    if (edge.replace(patt, '$1').match(/p(.*)(i|o)/)) {
        var pipeID = edge.replace(patt, '$1').match(/p(.*)(i|o)/)[1];
        var outGnum = edge.replace(patt, '$5' + "p" + pipeID);
    } else {
        var outGnum = edge.replace(patt, '$5');
    }
    if (edge.replace(patt, '$6').match(/p(.*)(i|o)/)) {
        var pipeID = edge.replace(patt, '$6').match(/p(.*)(i|o)/)[1];
        var inGnum = edge.replace(patt, '$10' + "p" + pipeID);
    } else {
        var inGnum = edge.replace(patt, '$10');
    }
    return [inGnum, outGnum]
}

function sortProcessList(processList, gnumList) {
    //get merged mainEdges for each pipelineModule and window
    var mainEdgeList = getMergedEdges("main");
    //replace process nodes with ccID's for pipeline modules
    var mainEdges = checkCopyId(mainEdgeList);
    mainEdges = replaceNullIDEdges(mainEdges)

    if (gnumList == null) {
        var sortGnum = [];
    } else {
        var sortGnum = gnumList.slice();
    }
    if (mainEdges.length > 0) {
        for (var e = 0; e < mainEdges.length; e++) { //mainEdges.length
            var patt = /(.*)-(.*)-(.*)-(.*)-(.*?)_(.*)-(.*)-(.*)-(.*)-(.*)/;
            var outGnum = '';
            var inGnum = '';
            //for pipeline modules append pipeline id to gnum
            [inGnum, outGnum] = getInOutGnum(mainEdges[e]);
            //for first raw insert both values
            //first can be added by push but other should be splice
            if (!sortGnum.includes(outGnum)) {
                //if output of input is exist in the array, insert before it.
                if (sortGnum.includes(inGnum)) {
                    var index = sortGnum.indexOf(inGnum);
                    sortGnum.splice(index, 0, outGnum);
                    var index = sortGnum.indexOf(outGnum);
                } else {
                    sortGnum.push(outGnum);
                    var index = sortGnum.indexOf(outGnum);

                }
            } else {
                //check if the position of outGnum if inGnum is also exist in array
                //cut inGnum and paste to indexOut position
                if (sortGnum.includes(inGnum) && sortGnum.includes(outGnum)) {
                    var indexIn = sortGnum.indexOf(inGnum);
                    var indexOut = sortGnum.indexOf(outGnum);
                    if (indexOut > indexIn) {
                        sortGnum.splice(indexIn, 1);
                        sortGnum.splice(indexOut, 0, inGnum);
                    }
                }
                var index = sortGnum.indexOf(outGnum);

            }
            if (!sortGnum.includes(inGnum)) {
                sortGnum.splice(index + 1, 0, inGnum);
                var index = sortGnum.indexOf(inGnum); //last index after insertion
            } else {
                var index = sortGnum.indexOf(inGnum);
            }
            //stop for final edge
            if (e + 1 < mainEdges.length) {
                for (var k = e + 1; k < mainEdges.length; k++) {
                    var outGnum2 = '';
                    var inGnum2 = '';
                    [inGnum2, outGnum2] = getInOutGnum(mainEdges[k]);
                    if (inGnum === outGnum2) {
                        if (!sortGnum.includes(inGnum2)) {
                            sortGnum.splice(index + 1, 0, inGnum2);
                        }
                    }
                }
            }
        }
    }
    var sortProcessList = [];
    $.each(sortGnum, function(el) {
        //convert 12p67 to g67-12 for pipeline modules
        if (sortGnum[el].match(/.*p.*/)) {
            var pipID = sortGnum[el].match(/(.*)p(.*)/)[2];
            var gID = sortGnum[el].match(/(.*)p(.*)/)[1];
            sortProcessList.push("g" + pipID + "-" + gID);
        } else {
            sortProcessList.push("g-" + sortGnum[el]);
        }
    });
    //add remaining input and output params by using processlist
    for (var key in processList) {
        if (!sortProcessList.includes(key)) {
            sortProcessList.push(key);
        }
    }
    //add remaining standalone processes in pipeline modules by using processListMain
    for (var p = 0; p < piGnumList.length; p++) {
        var processListPipeMod = {};
        processListPipeMod = window["pObj" + piGnumList[p]].processListMain;
        if (processListPipeMod) {
            for (var key in processListPipeMod) {
                if (!sortProcessList.includes(key)) {
                    sortProcessList.push(key);
                }
            }
        }
    }
    return { processList: sortProcessList, gnumList: sortGnum };
}

function getNewLocalVarLine(line, process_opt, gNum, varName, quoteType, pattern, tmpParams) {
    var checkArrFill = false;
    if (process_opt && gNum) {
        if (process_opt[gNum]) {
            //check if varName_indx is defined in eachProcessOpt for form arrays
            //then fill as array format
            var re = new RegExp(varName + "_ind" + "(.*)$", "g");
            var filt_keys = filterKeys(process_opt[gNum], re);
            var num_filt_keys = parseInt(filt_keys.length);
            if (num_filt_keys > 0) {
                var fillValArray = [];
                for (var k = 0; k < num_filt_keys; k++) {
                    fillValArray.push(process_opt[gNum][filt_keys[k]]);
                }
                var fillVal = "[" + quoteType + fillValArray.join(quoteType + "," + quoteType) + quoteType + "]";
                fillVal = fillVal.replace(/(\r\n|\n|\r)/gm, "\\n");
                var newLine = line.replace(pattern, tmpParams + ' = ' + fillVal + ' //*' + '$3');
                return newLine;
                checkArrFill = true;
            }
            //fill original format when not filled as array format
            if (!checkArrFill) {
                if (process_opt[gNum][varName] != undefined) {
                    var fillVal = process_opt[gNum][varName];
                    //if integer is entered blank, make it empty string
                    if (fillVal === "" && quoteType === "") {
                        quoteType = '"';
                    }
                    if (Array.isArray(fillVal)) {
                        fillVal = JSON.stringify(fillVal);
                        quoteType = '';
                    }
                    fillVal = fillVal.replace(/(\r\n|\n|\r)/gm, "\\n");
                    var newLine = line.replace(pattern, tmpParams + ' = ' + quoteType + fillVal + quoteType + ' //*' + '$3');
                    return newLine;
                }
            }
        }
    }
    //replace new line with tmpParams
    var newLine = line.replace(pattern, tmpParams + ' = ' + '$2' + ' //*' + '$3');
    return newLine;
}

// gnum numbers used for process header.  gNum="pipe" is used for pipeline header
function getNewScriptHeader(script_header, process_opt, gNum, mergedProcessName, currgid, mainPipeEdges) {
    var newScriptHeader = "";
    if (script_header.match(/\/\/\*/)) {
        var lines = script_header.split('\n');
        for (var i = 0; i < lines.length; i++) {
            var varName = null;
            // check if process opt is defined in line
            var pattern = /(.*)=(.*)\/\/\*(.*)/;
            var re = new RegExp(pattern);
            var checkParam = lines[i].match(re);
            if (checkParam && checkParam[0] && checkParam[1]) {
                var checkArrFill = false;
                var newLine = "";
                var varName = $.trim(checkParam[1]);
                var oldDefVal = $.trim(checkParam[2]);
                var quoteType = '';
                if (oldDefVal[0] === "'") {
                    quoteType = "'";
                } else if (oldDefVal[0] === '"') {
                    quoteType = '"';
                } else if (oldDefVal[0] === '[') {
                    quoteType = '"';
                }
                if (!varName.match(/^params\./) && mergedProcessName) {
                    var tmpParams = "params." + mergedProcessName + "." + varName;
                    newLine = getNewLocalVarLine(lines[i], process_opt, gNum, varName, quoteType, pattern, tmpParams);
                    newScriptHeader += varName + " = " + tmpParams + "\n";
                    if (!window["processVarObj"][mergedProcessName]) {
                        window["processVarObj"][mergedProcessName] = {}
                    }
                    window["processVarObj"][mergedProcessName][varName] = newLine
                } else {
                    newLine = getNewLocalVarLine(lines[i], process_opt, gNum, varName, quoteType, pattern, varName);
                    newScriptHeader += "//* " + newLine + "\n";
                }
            } else {
                newScriptHeader += lines[i] + "\n";
            }
        }
    } else {
        newScriptHeader = script_header
    }

    if (currgid && mainPipeEdges && newScriptHeader.match(/\{\{publishdir:(.*)\}\}/g)) {
        var checkParam = newScriptHeader.match(/\{\{publishdir:(.*)\}\}/g);
        if (checkParam.length > 0) {
            for (var i = 0; i < checkParam.length; i++) {
                var split = checkParam[i].split("publishdir:")
                if (split && split[1]) {
                    var outputPattern = $.trim(split[1].replace(/\}\}/, ""))
                    var publishDirectory = getPublishDirWithOutputPattern(outputPattern, currgid, mainPipeEdges);
                    if (publishDirectory) {
                        newScriptHeader = newScriptHeader.replace(checkParam[i], publishDirectory);
                    } else {
                        newScriptHeader = newScriptHeader.replace(checkParam[i], "{{Output Pattern Not Found!}}");
                    }
                }
            }

        }
    }
    return newScriptHeader
}

// in case error occured, use recursiveCounter to stop recursive loop
var recursiveCounter = 0;

function recursiveSort(sortedProcessList, initGnumList) {
    recursiveCounter++;
    var sortResult = {};
    sortResult = sortProcessList(processList, initGnumList);
    if (checkArraysEqual(initGnumList, sortResult.gnumList)) {
        recursiveCounter = 0;
        return { processList: sortResult.processList, gnumList: sortResult.gnumList }
    } else {
        if (recursiveCounter > 50) {
            return { processList: sortResult.processList, gnumList: sortResult.gnumList }
        } else {
            return recursiveSort(sortResult.processList, sortResult.gnumList)
        }
    }
}


function createNextflowFile(nxf_runmode, uuid) {
    //keep process specific (local) variable info (which doesn't start with params.)
    window["processVarObj"] = {} // it will be appended to the nextflow.config file 
    var run_uuid = uuid || false; //default false
    nextText = "";


    createPiGnumList();
    if (nxf_runmode === "run") {
        var chooseEnv = $('#chooseEnv option:selected').val();
        var hostname = $('#chooseEnv').find('option:selected').attr('host');
        if (hostname) {
            nextText += '$HOSTNAME = "' + hostname + '"\n';
        }

        var proOptDiv = $('#ProcessPanel').children()[0];
        if (proOptDiv) {
            var process_opt = getProcessOpt();
            process_opt = JSON.parse(decodeURIComponent(process_opt))
        }
        //get all the proPipeInputs to check glob(*,?{,} characters)
        var getProPipeInputs = getValues({ p: "getProjectPipelineInputs", project_pipeline_id: project_pipeline_id });
    } else {
        nextText += '$HOSTNAME = ""\n';
        nextText += "params.outdir = 'results' " + " \n\n";
        var getProPipeInputs = null;
    }

    //pipeline scripts
    var header_pipe_script = "";
    var footer_pipe_script = "";
    [header_pipe_script, footer_pipe_script] = getPipelineScript(pipeline_id);
    header_pipe_script = getNewScriptHeader(header_pipe_script, process_opt, "pipe", "pipeline", "", "");
    nextText += header_pipe_script + "\n";


    //sortProcessList
    var initialSort = {};
    var lastSort = {};
    // recursive sorting to sort distant nodes.
    initialSort = sortProcessList(processList, null);
    lastSort = recursiveSort(initialSort.processList, initialSort.gnumList)
    var sortedProcessList = lastSort.processList;
    //get mainEdges for each pipelineModule
    var allEdgesList = getMergedEdges("all");
    //replace process nodes with ccID's for pipeline modules
    var allEdges = checkCopyId(allEdgesList);
    allEdges = replaceNullIDEdges(allEdges)

    var mainPipeEdgesList = edges.slice();
    //replace process nodes with ccID's for main pipeline edge list
    var mainPipeEdges = checkCopyId(mainPipeEdgesList);
    mainPipeEdges = replaceNullIDEdges(mainPipeEdges)


    //initial input data added
    var iniTextSecond = ""
    window.channelTypeDict = {};
    for (var i = 0; i < sortedProcessList.length; i++) {
        var inParamCheck = $("#" + sortedProcessList[i] + ".g-inPro");
        if (inParamCheck.length) {
            var iniText = {}
            iniText = InputParameters(sortedProcessList[i], getProPipeInputs, allEdges);
            iniTextSecond += iniText.secPart;
            nextText += iniText.firstPart;
        }

    };
    var maxOptionalInputNum = getValues({ p: "getMaxOptionalInputNum", "pipeline_id": pipeline_id })
    if (maxOptionalInputNum > 0) {
        nextText += '// Stage empty file to be used as an optional input where required\n';
    }
    for (var k = 1; k < maxOptionalInputNum + 1; k++) {
        nextText += `ch_empty_file_${k} = file("$baseDir/.emptyfiles/NO_FILE_${k}", hidden:true)\n`;
    }
    nextText += "\n" + iniTextSecond + "\n";



    for (var k = 0; k < sortedProcessList.length; k++) {
        className = document.getElementById(sortedProcessList[k]).getAttribute("class");
        mainProcessId = className.split("-")[1]
        var gNum = sortedProcessList[k].match(/g(.*)/)[1]; //g7 or g4-3 for pipeline module
        if (gNum[0] === "-") {
            gNum = gNum.substring(1); //eq 7
        }
        if (gNum.match(/-/)) {
            gNum = gNum.replace("-", "_") //eq 4_3
        }
        if (mainProcessId !== "inPro" && mainProcessId !== "outPro" && !mainProcessId.match(/p.*/)) { //if it is not input parameter print process data
            var body = "";
            var script_header = "";
            var script_footer = "";

            [body, script_header, script_footer] = IOandScriptForNf(mainProcessId, sortedProcessList[k], allEdges, nxf_runmode, run_uuid, mainPipeEdges);
            var mergedProcessList = getMergedProcessList();
            var mergedProcessName = mergedProcessList[sortedProcessList[k]].replace(/ /g, '_');
            var publishDirText = publishDir(mainProcessId, sortedProcessList[k], mainPipeEdges);
            // add process options after the process script_header to overwite them
            //check if parameter comment  //* and process_opt[gNum] are exist:
            script_header = getNewScriptHeader(script_header, process_opt, gNum, mergedProcessName, sortedProcessList[k], mainPipeEdges);
            body = getNewScriptHeader(body, process_opt, gNum, mergedProcessName, sortedProcessList[k], mainPipeEdges);


            proText = script_header + "\nprocess " + mergedProcessName + " {\n\n" + publishDirText + body + "\n}\n" + script_footer + "\n"
            nextText += proText;
        }
    };

    var endText = footer_pipe_script + '\nworkflow.onComplete {\n';
    endText += 'println "##Pipeline execution summary##"\n';
    endText += 'println "---------------------------"\n';
    endText += 'println "##Completed at: $workflow.complete"\n';
    endText += 'println "##Duration: ${workflow.duration}"\n';
    endText += 'println "##Success: ${workflow.success ? \'OK\' : \'failed\' }"\n';
    endText += 'println "##Exit status: ${workflow.exitStatus}"\n';
    endText += '}\n';

    return nextText + endText
}


function getChannelSetInto(channelNameAll) {
    var text = "";
    if (channelNameAll.match(/;/)) {
        text = ".into{" + channelNameAll + "}";
    } else {
        text = ".set{" + channelNameAll + "}";
    }
    return text
}

function addFormat2chnObj(chnObj, channelFormat, newChn) {
    if (chnObj[channelFormat]) {
        var tmp = chnObj[channelFormat];
        chnObj[channelFormat] = tmp + ";" + newChn;
    } else {
        chnObj[channelFormat] = newChn;
    }
    return chnObj;
}

// channelTypeDict
function getInputParamContent(chnObj, getProPipeInputs, inGID, inputParamName, secParQual, secParName, secNodeName, sNodeProId, chnName, optionalInputID) {
    var channelFormat = "";
    var secPartTemp = "";
    var ret = "";
    //check if input has glob(*,?{,}) characters -> use in the file section
    var checkRegex = false;
    if (getProPipeInputs) {
        var inputName = getProPipeInputs.filter(function(el) {
            return el.given_name == inputParamName
        })[0]
        if (inputName) {
            checkRegex = /(\{|\*|\?|\})/.test(inputName.name);
            if (inputName.collection_name) checkRegex = true;
        }
    }
    if (secParQual === "file") {
        if (checkRegex === false) {
            channelFormat = "f1";
            chnObj = addFormat2chnObj(chnObj, channelFormat, chnName);
            var channelNameAll = chnObj[channelFormat];
            var chanList = channelNameAll.split(";");
            for (var e = 0; e < chanList.length; e++) {
                //g_18_genome_url_g_17 = params.inputparam && file(params.inputparam, type: 'any').exists() ? file(params.inputparam, type: 'any') : ch_empty_file_3
                if (optionalInputID) {
                    secPartTemp += chanList[e] + " = " + `params.${inputParamName} && file(params.${inputParamName}, type: 'any').exists() ? file(params.${inputParamName}, type: 'any') : ch_empty_file_${optionalInputID}\n`;
                } else {
                    secPartTemp += chanList[e] + " = " + `file(params.${inputParamName}, type: 'any')\n`;
                }
            }
        } else if (checkRegex === true) {
            channelFormat = "f2";
            chnObj = addFormat2chnObj(chnObj, channelFormat, chnName);
            secPartTemp = "Channel.fromPath(params." + inputParamName + ", type: 'any')" + getChannelSetInto(chnObj[channelFormat]) + "\n";
        }
    } else if (secParQual === "set") {
        //check if sNodeProId had a mate inputparameter
        var inputParAll = getValues({ p: "getInputsPP", "process_id": sNodeProId });
        var inputParMate = inputParAll.filter(function(el) { return el.sname == "mate" }).length > 0;
        //if mate defined in process use fromFilePairs
        if (inputParMate) {
            channelFormat = "f3";
            chnObj = addFormat2chnObj(chnObj, channelFormat, chnName);
            var emptyChannelText = "";
            var emptyChannels = chnObj[channelFormat]
            emptyChannelText = `\t${emptyChannels} = Channel.empty()\n`;
            if (emptyChannels.includes(";")) {
                emptyChannelText = ""
                var emptyChannelsAr = emptyChannels.split(";")
                for (var e = 0; e < emptyChannelsAr.length; e++) {
                    emptyChannelText += `\t${emptyChannelsAr[e]} = Channel.empty()\n`;
                }
            }

            secPartTemp = "if (params." + inputParamName + "){\nChannel\n\t.fromFilePairs( params." + inputParamName + " , size: params.mate == \"single\" ? 1 : params.mate == \"pair\" ? 2 : params.mate == \"triple\" ? 3 : params.mate == \"quadruple\" ? 4 : -1 )\n\t.ifEmpty { error \"Cannot find any " + secParName + " matching: ${params." + inputParamName + "}\" }\n\t" + getChannelSetInto(chnObj[channelFormat]) + "\n } else {  \n" + emptyChannelText + " }\n\n";
            //if mate not defined in process use fromPath
        } else {
            //if val(name), file(read) format -> turn into set input
            if (secNodeName.match(/.*val\(.*\).*file\(.*\).*/)) {
                channelFormat = "f4";
                chnObj = addFormat2chnObj(chnObj, channelFormat, chnName);
                secPartTemp = "Channel.fromPath(params." + inputParamName + ", type: 'any').map{ file -> tuple(file.baseName, file) }" + getChannelSetInto(chnObj[channelFormat]) + "\n"
                    //or other formats eg. file(fastq1), file(fastq2), file(fastq3)    
            } else {
                channelFormat = "f5";
                chnObj = addFormat2chnObj(chnObj, channelFormat, chnName);
                secPartTemp = "Channel.fromPath(params." + inputParamName + ", type: 'any').toSortedList()" + getChannelSetInto(chnObj[channelFormat]) + "\n";
            }
        }
    } else if (secParQual === "val") {
        channelFormat = "f6";
        chnObj = addFormat2chnObj(chnObj, channelFormat, chnName);
        secPartTemp = "Channel.value(params." + inputParamName + ")" + getChannelSetInto(chnObj[channelFormat]) + "\n"
    }
    chnObj[channelFormat + "text"] = secPartTemp;
    window.channelTypeDict[chnName] = channelFormat
    return chnObj
}

function getOptionalInputID(sNode, sNodeProId) {
    var id = 1;
    var process = $(document.getElementById(sNode)).parent()
    var processInputs = process.find(`circle[kind=input][optional=true]`);
    var count = 0
    for (var c = 0; c < processInputs.length; c++) {
        var inId = $(processInputs[c]).attr("id")
        var inIdSplit = inId.split("-");
        var inIdParam = parametersData.filter(function(el) { return el.id == inIdSplit[3] })[0];
        var inIdParamQual = inIdParam.qualifier;
        if (inIdParamQual != 'val') count++
            if (inId == sNode) id = count
    }
    return id
}

//Input parameters and channels with file paths
function InputParameters(inGNum, getProPipeInputs, allEdges) {
    //input parameter info:
    //inId  : o-inPro-1-144-4
    //inGNum: g-8
    //inGID : 8
    var inId = d3.select("#" + inGNum).selectAll("circle[kind ='input']")[0][0].id;
    var inGID = inGNum.split("-")[1];
    var inputIdSplit = inId.split("-")
    var proId = inputIdSplit[1]
    var userEntryId = "text-" + inputIdSplit[4];
    var inputParamName = document.getElementById(userEntryId).getAttribute('name');
    var firstPart = 'if (!params.' + inputParamName + '){params.' + inputParamName + ' = ""} \n';
    var secPart = "";
    var optionalInputID = "";
    //inputIdSplit[3] === "inPara" means node is not connected.
    if (proId === "inPro" && inputIdSplit[3] !== "inPara") {
        //group channels based on their formats
        var chnObj = {};
        for (var c = 0; c < allEdges.length; c++) {
            if (allEdges[c].indexOf(inId) !== -1) {

                var fNode = "";
                var sNode = "";
                var secPartTemp = "";
                //inPro and outPro only found in fNode
                [fNode, sNode] = splitEdges(allEdges[c]);
                var secNodeSplit = sNode.split("-");
                var sNodeProId = secNodeSplit[1];
                var sNodeOrder = secNodeSplit[2];
                var secNodeName = $(document.getElementById(sNode)).attr("name");
                var secParam = parametersData.filter(function(el) { return el.id == secNodeSplit[3] })[0];
                var secParName = secParam.name;
                var secParQual = secParam.qualifier;
                var isSecNodeOptional = $(document.getElementById(sNode)).attr("optional") == "true";
                if (isSecNodeOptional && secParQual != "val") {
                    optionalInputID = getOptionalInputID(sNode, sNodeProId);
                }
                //g_3_reads_g_0
                var chnName = gFormat(inGNum) + "_" + secParName + sNodeOrder + "_" + gFormat(document.getElementById(sNode).getAttribute("parentG"));
                chnObj = getInputParamContent(chnObj, getProPipeInputs, inGID, inputParamName, secParQual, secParName, secNodeName, sNodeProId, chnName, optionalInputID);
            }
        }
        //combine chnObj text
        $.each(chnObj, function(el) {
            if (el.match("text")) {
                secPart += chnObj[el];
            }
        });
    }
    var iText = {};
    iText.firstPart = firstPart;
    iText.secPart = secPart;
    return iText;
}

function getParamOutdir(outParUserEntry) {
    return '"' + outParUserEntry + '/$filename"';
}

//if name contains regular expression with curly brackets: {a,b,c} then turn into (a|b|c) format
function fixCurlyBrackets(outputName) {
    if (outputName.match(/(.*){(.*?),(.*?)}(.*)/)) {
        var patt = /(.*){(.*?),(.*?)}(.*)/;
        var insideBrackets = outputName.replace(patt, '$2' + "," + '$3');
        insideBrackets = insideBrackets.replace(/\,/g, '|')
        var outputNameFix = outputName.replace(patt, '$1' + "(" + insideBrackets + ")" + '$4');
        if (outputNameFix.match(/(.*){(.*?),(.*?)}(.*)/)) {
            return fixCurlyBrackets(outputNameFix);
        } else {
            return outputNameFix;
        }
    } else {
        return outputName;
    }
}

//eg. set val(name), file("${params.wdir}/validfastq/*.fastq") then take inside of the file(x)

//eg. set library, name, file("*/*.fastq"), file("*/*.fa")
//->/(.*\/.*.fastq|.*\/.*.fa)$/

function getPublishDirRegex(outputName) {
    // multiple file group
    if (outputName.match(/file\((.*?)\)/ig) && outputName.match(/file\((.*?)\)/ig).length > 1) {
        var groupsArr = []
        var groups = outputName.match(/file\((.*?)\)/ig)
        for (var i = 0; i < groups.length; i++) {
            groupsArr.push(groups[i].match(/file\((.*)\)/i)[1])
        }
        var outputName = "(" + groupsArr.join("|") + ")";

    } else if (outputName.match(/file\((.*)\)/)) {
        var outputName = outputName.match(/file\((.*)\)/i)[1];
    }
    //if name contains path and separated by '/' then replace with escape character '\/'
    outputName = outputName.replace(/\//g, '\\\/')
        //if name contains regular expression with curly brackets: {a,b,c} then turn into (a|b|c) format
    var outputName = fixCurlyBrackets(outputName);
    outputName = outputName.replace(/\*/g, '.*')
    outputName = outputName.replace(/\?/g, '.?')
    outputName = outputName.replace(/\'/g, '')
    outputName = outputName.replace(/\"/g, '')
    outputName = outputName + "$";
    return outputName;
}

function getPublishDirWithOutputPattern(outputPattern, currgid, mainPipeEdges) {
    oText = ""
    var closePar = false
    var oList = d3.select("#" + currgid).selectAll("circle[kind ='output']")[0]
    for (var i = 0; i < oList.length; i++) {
        oId = oList[i].id
        for (var e = 0; e < mainPipeEdges.length; e++) {
            if (mainPipeEdges[e].indexOf(oId) > -1) {
                var fNode = "";
                var sNode = "";
                [fNode, sNode] = splitEdges(mainPipeEdges[e]);
                var sNode_Split = sNode.split("-")
                var sNode_paramData = parametersData.filter(function(el) { return el.id == sNode_Split[3] })[0]
                var sNode_qual = sNode_paramData.qualifier
                    //publishDir Section
                if (fNode.split("-")[1] === "outPro") {
                    closePar = true
                        //outPro node : get userEntryId and userEntryText
                    var parId = fNode.split("-")[4]
                    var userEntryId = "text-" + fNode.split("-")[4]
                    var outParUserEntry = document.getElementById(userEntryId).getAttribute('name');
                    var outputName = document.getElementById(oId).getAttribute("name")
                    if (outputName === outputPattern) {
                        return outParUserEntry
                    }
                }
            }
        }
    }
    return "";
}

//overwrite: true, removed-> it was opening lot of threads on resumed runs.
//publishDir params.outdir, mode: 'copy',
//	saveAs: {filename ->
//	if (filename =~ /${basedir}\/${newDirName}\/$/) "star_build_index/$filename"
//}
function publishDir(id, currgid, mainPipeEdges) {
    var oText = "";
    var oList = d3.select("#" + currgid).selectAll("circle[kind ='output']")[0];
    for (var i = 0; i < oList.length; i++) {
        var oId = oList[i].id
        for (var e = 0; e < mainPipeEdges.length; e++) {
            if (mainPipeEdges[e].indexOf(oId) > -1) {
                var fNode = "";
                var sNode = "";
                [fNode, sNode] = splitEdges(mainPipeEdges[e]);
                var sNode_Split = sNode.split("-")
                var sNode_paramData = parametersData.filter(function(el) { return el.id == sNode_Split[3] })[0]
                var sNode_qual = sNode_paramData.qualifier
                    //publishDir Section
                if (fNode.split("-")[1] === "outPro" && sNode_qual != "val") {
                    //outPro node : get userEntryId and userEntryText
                    var parId = fNode.split("-")[4]
                    var userEntryId = "text-" + fNode.split("-")[4]
                    outParUserEntry = document.getElementById(userEntryId).getAttribute('name');

                    reg_ex = document.getElementById(oId).getAttribute("reg_ex");
                    if (reg_ex !== "" && reg_ex !== null) {
                        reg_ex = decodeHtml(reg_ex);
                        if (reg_ex.length > 0) {
                            var lastLetter = reg_ex.length - 1;
                            if (reg_ex[0] === "\/" && reg_ex[lastLetter] === "\/") {
                                outputName = reg_ex;
                            } else {
                                outputName = "\/" + reg_ex + "\/";
                            }
                        }

                    } else {
                        outputName = document.getElementById(oId).getAttribute("name")
                        outputName = "/" + getPublishDirRegex(outputName) + "/";

                    }
                    if (!outputName && !reg_ex) {
                        oText += "publishDir \"${params.outdir}/" + outParUserEntry + "\", mode: 'copy'\n\n";
                    } else {
                        oText += "publishDir params.outdir, mode: 'copy', saveAs: {filename -> if \(filename =~ " + outputName + "\) " + getParamOutdir(outParUserEntry) + "}\n"
                    }
                }
            }
        }
    }

    return oText;
}

function getPipelineScript(pipeline_id) {
    var script_pipe_footer = "";
    var script_pipe_header = "";
    var pipelineData = getValues({ p: "loadPipeline", "id": pipeline_id });
    if (pipelineData[0] && pipelineData[0].script_pipe_header !== null) {
        script_pipe_header = decodeHtml(pipelineData[0].script_pipe_header);
    }
    if (pipelineData[0] && pipelineData[0].script_pipe_footer !== null) {
        script_pipe_footer = decodeHtml(pipelineData[0].script_pipe_footer);
    }
    return [script_pipe_header, script_pipe_footer]
}

function getWhenCond(script) {
    var whenCond = null;
    if (script.match(/when:/)) {
        if (script.match(/when:(.*)\n(.*)\n/)) {
            var whenCond = script.match(/when:(.*)\n(.*)\n/)[2];
            if (whenCond == undefined) {
                whenCond = null;
            }
        }
    }
    return whenCond;
}

//g164_9_outputFileTSV_g_165 = g164_9_outputFileTSV_g_165.ifEmpty(file('tophatSum')) // depricated
//g164_9_outputFileTSV_g_165 = g164_9_outputFileTSV_g_165.ifEmpty([])
function getOptionalInText(optionalInAr, optionalInQualAr, optionalInChannelNameAr) {
    var optText = "";
    for (var i = 0; i < optionalInAr.length; i++) {
        if (optionalInQualAr[i] == "val") {
            optText += optionalInAr[i] + "= " + optionalInAr[i] + ".ifEmpty(\"\") \n";
        } else {
            // optional input line for file(params.staticfile)
            // prevents Unknown method invocation `ifEmpty` on UnixPath type
            if (window.channelTypeDict[optionalInChannelNameAr[i]] && window.channelTypeDict[optionalInChannelNameAr[i]] == "f1") {
                //                optText += optionalInAr[i] + "= " + optionalInAr[i] + ".exists() ? " + optionalInAr[i] + ` : ch_empty_file_${i+1} \n`;
            } else {
                optText += optionalInAr[i] + "= " + optionalInAr[i] + ".ifEmpty([\"\"]) \n";
            }
        }
    }
    return optText;
}

function getWhenText(whenCond, whenInLib, whenOutLib) {
    var whenText = ""
    var pairList = [];
    var dummyOutList = [];
    $.each(whenOutLib, function(el) {
        if (whenInLib[el]) {
            pairList.push({ inChl: whenInLib[el], outChl: whenOutLib[el] })
        } else {
            dummyOutList.push(whenOutLib[el])
        }
    });
    if (pairList.length > 0) {
        whenText = "if (!(" + $.trim(whenCond) + ")){\n";
        for (var i = 0; i < pairList.length; i++) {
            var inChn = pairList[i].inChl;
            var outChn = pairList[i].outChl;
            outChn = outChn.join(";")
            outChn = outChn.replace(/,/g, ';')
            for (var k = 0; k < inChn.length; k++) {
                if (outChn.match(/;/)) {
                    whenText += inChn[k] + ".into{" + outChn + "}\n"
                } else {
                    whenText += inChn[k] + ".set{" + outChn + "}\n"
                }
            }
        }
        for (var n = 0; n < dummyOutList.length; n++) {
            var tmp = dummyOutList[n];
            if (tmp.length) {
                tmp = tmp.join(",").split(",")
                for (var k = 0; k < tmp.length; k++) {
                    whenText += $.trim(tmp[k]) + " = Channel.empty()\n";
                }
            } else {
                whenText += tmp + " = Channel.empty()\n";
            }
        }
        whenText += "} else {";
    }
    return whenText;
}

function addChannelName(whenCond, whenLib, file_type, channelName, param_name, qual) {
    var libName = file_type + "_" + param_name + "_" + qual;
    if (whenCond && whenLib[libName]) {
        whenLib[libName].push(channelName)
    } else if (whenCond && !whenLib[libName]) {
        whenLib[libName] = [];
        whenLib[libName].push(channelName)
    }
    return whenLib;
}

function IOandScriptForNf(id, currgid, allEdges, nxf_runmode, run_uuid, mainPipeEdges) {
    var processData = getValues({ p: "getProcessData", "process_id": id });
    script = decodeHtml(processData[0].script);
    //check input and output channels in case of a when condition
    //if any of the pattern of input and out matches then make the process conditional according to entered when condition
    var whenCond = getWhenCond(script);
    var whenInLib = {};
    var whenOutLib = {};
    var whenText = '';
    //check optional inputs
    var optionalInAr = [];
    var optionalInChannelNameAr = [];
    var optionalInQualAr = [];

    if (processData[0].script_header !== null) {
        var script_header = decodeHtml(processData[0].script_header);
    } else {
        var script_header = "";
    }
    if (processData[0].script_footer !== null) {
        var script_footer = decodeHtml(processData[0].script_footer);
    } else {
        var script_footer = "";
    }
    var lastLetter = script.length - 1;
    if (script[0] === '"' && script[lastLetter] === '"') {
        script = script.substring(1, script.length - 1); //remove first and last duble quote
    }
    //insert """ for script if not exist
    if (script.search('"""') === -1 && script.search('\'\'\'') === -1 && script.search('when:') === -1 && script.search('script:') === -1 && script.search('shell:') === -1 && script.search('exec:') === -1) {
        script = '"""\n' + script + '\n"""'
    }
    bodyInput = ""
    bodyOutput = ""
    var IList = d3.select("#" + currgid).selectAll("circle[kind ='input']")[0]
    var OList = d3.select("#" + currgid).selectAll("circle[kind ='output']")[0]

    if (nxf_runmode == "run") {
        // find rMarkdown Output and replace path in script
        for (var h = 0; h < OList.length; h++) {
            var mainPipeOut = mainPipeEdges.filter(function(el) { return el.indexOf(OList[h].id) > -1 })
            if (mainPipeOut.length > 0) {
                var mainPipeOutNodeId = mainPipeOut[0];
                var fNo = "";
                var sNo = "";
                [fNo, sNo] = splitEdges(mainPipeOutNodeId);
                if (fNo.split("-")[1] === "outPro") {
                    var parId = fNo.split("-")[4]
                    var userEntryId = "text-" + fNo.split("-")[4]
                    var outParUserEntry = document.getElementById(userEntryId).getAttribute('pubWeb');
                    if (outParUserEntry) {
                        if (outParUserEntry == "rmarkdown") {
                            script = fillJsonPattern(script, run_uuid);
                            break
                        }
                    }
                }

            }
        }
    }

    for (var i = 0; i < IList.length; i++) {
        if (bodyInput == "") {
            bodyInput = "input:\n"
        }
        Iid = IList[i].id //i-11-0-9-0
        var inputIdSplit = Iid.split("-")
        var paramData = parametersData.filter(function(el) { return el.id == inputIdSplit[3] })[0]
        var qual = paramData.qualifier
        var file_type = paramData.file_type
        var param_name = paramData.name
        var inputName = document.getElementById(Iid).getAttribute("name");
        var inputClosure = document.getElementById(Iid).getAttribute("closure");
        var inputOperator = document.getElementById(Iid).getAttribute("operator");
        var inputOptional = document.getElementById(Iid).getAttribute("optional");
        inputClosure = decodeHtml(inputClosure);
        var inputOperatorText = '';
        if (inputOperator === 'mode flatten') {
            inputOperatorText = ' ' + inputOperator + inputClosure;
        } else if (inputOperator !== '') {
            if (inputClosure !== '') {
                inputOperatorText = '.' + inputOperator + inputClosure;
            } else if (inputClosure === '') {
                inputOperatorText = '.' + inputOperator + "()";
            }
        }
        find = false
        for (var e = 0; e < allEdges.length; e++) {
            if (allEdges[e].indexOf(Iid) > -1) {
                find = true
                var fNode = "";
                var sNode = "";
                [fNode, sNode] = splitEdges(allEdges[e]);
                var node1 = "";
                var node2 = "";
                //output node clicked first
                if (fNode.indexOf('o') > -1) {
                    node1 = fNode;
                    node2 = sNode;
                } else {
                    node1 = sNode;
                    node2 = fNode;
                }
                // find connected process of the input and find oIndex of the output process. 
                // oIndex and iIndex prevents duplicate channel error
                var connectedOGnum = document.getElementById(node1).getAttribute("parentG");
                var connectedOList = d3.select("#" + connectedOGnum).selectAll("circle[kind ='output']")[0]
                var oIndex = connectedOList.findIndex(x => $(x).attr("id") === node1);
                if (oIndex === -1) oIndex = ""
                var iIndex = i;
                var inputIdSplit = node1.split("-")
                var filteredParamData = parametersData.filter(function(el) { return el.id == inputIdSplit[3] })[0];
                var genParName = filteredParamData.name;
                var channelName = gFormat(document.getElementById(node1).getAttribute("parentG")) + "_" + genParName + oIndex + iIndex + "_" + gFormat(document.getElementById(node2).getAttribute("parentG"));

                whenInLib = addChannelName(whenCond, whenInLib, file_type, channelName, param_name, qual)
                if (inputOptional == "true") {
                    optionalInAr.push(channelName)
                    optionalInQualAr.push(qual)
                    optionalInChannelNameAr.push(channelName)
                }
                bodyInput = bodyInput + " " + qual + " " + inputName + " from " + channelName + inputOperatorText + "\n";
            }
        }
    }

    for (var o = 0; o < OList.length; o++) {
        if (bodyOutput == "") {
            bodyOutput = "output:\n"
        }
        Oid = OList[o].id
        outputIdSplit = Oid.split("-")
        var paramData = parametersData.filter(function(el) { return el.id == outputIdSplit[3] })[0];
        var qual = paramData.qualifier;
        var file_type = paramData.file_type;
        var param_name = paramData.name;
        outputName = document.getElementById(Oid).getAttribute("name");
        var outputClosure = document.getElementById(Oid).getAttribute("closure");
        var outputOperator = document.getElementById(Oid).getAttribute("operator");
        var outputOptional = document.getElementById(Oid).getAttribute("optional");
        var outputOptionalText = ' ';
        if (outputOptional == "true") {
            outputOptionalText = " optional true ";
        }
        outputClosure = decodeHtml(outputClosure);
        var outputOperatorText = '';
        if (outputOperator === 'mode flatten') {
            outputOperatorText = ' ' + outputOperator + outputClosure;
        } else if (outputOperator !== '') {
            if (outputClosure !== '') {
                outputOperatorText = '.' + outputOperator + outputClosure;
            } else if (outputClosure === '') {
                outputOperatorText = '.' + outputOperator + "()";
            }
        }
        genParName = parametersData.filter(function(el) {
            return el.id == outputIdSplit[3]
        })[0].name;
        var oIndex = o
            // +o prevents duplicate channel name error
        channelName = gFormat(document.getElementById(Oid).getAttribute("parentG")) + "_" + genParName + oIndex;
        var channelNameAll = "";
        for (var c = 0; c < allEdges.length; c++) {
            if (allEdges[c].indexOf(Oid) == 0) {
                var fNode = "";
                var secNode = "";
                [fNode, secNode] = splitEdges(allEdges[c]);
                var secProType = secNode.split("-")[1];
                var secProOrder = secNode.split("-")[2];
                if (secProType !== "outPro") {
                    if (channelNameAll === "") {
                        channelNameAll = channelNameAll + channelName + secProOrder + "_" + gFormat(document.getElementById(secNode).getAttribute("parentG"));
                    } else {
                        channelNameAll = channelNameAll + ", " + channelName + secProOrder + "_" + gFormat(document.getElementById(secNode).getAttribute("parentG"));
                    }
                }
            } else if (allEdges[c].indexOf(Oid) > 0) {
                var fstNode = "";
                var secNode = "";
                [fstNode, secNode] = splitEdges(allEdges[c]);
                var fstProType = fstNode.split("-")[1];
                var fstProOrder = fstNode.split("-")[2];

                if (fstProType !== "outPro") {
                    if (channelNameAll === "") {
                        channelNameAll = channelNameAll + channelName + fstProOrder + "_" + gFormat(document.getElementById(fstNode).getAttribute("parentG"));
                    } else {
                        channelNameAll = channelNameAll + ", " + channelName + fstProOrder + "_" + gFormat(document.getElementById(fstNode).getAttribute("parentG"));
                    }
                }
            }
        }
        // if output node is not connected to input node.
        if (channelNameAll === '') {
            // o prevents duplicate channel name error
            var oIndex = o
            channelNameAll = channelName + oIndex;
        }
        whenOutLib = addChannelName(whenCond, whenOutLib, file_type, channelNameAll, param_name, qual)
        bodyOutput = bodyOutput + " " + qual + " " + outputName + outputOptionalText + " into " + channelNameAll + outputOperatorText + "\n"

    }
    if (optionalInAr.length > 0) {
        var optionalInText = getOptionalInText(optionalInAr, optionalInQualAr, optionalInChannelNameAr)
        script_header = optionalInText + "\n" + script_header
    }

    if (whenCond) {
        whenText = getWhenText(whenCond, whenInLib, whenOutLib);
        if (whenText && whenText !== "") {
            script_header = script_header + "\n" + whenText + "\n";
            script_footer = "}" + "\n" + script_footer + "\n";
        }
    }
    body = bodyInput + "\n" + bodyOutput + "\n" + script
    return [body, script_header, script_footer]
}