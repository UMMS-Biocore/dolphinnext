var refreshDmetaTable = function(data, id){
    var TableID = '#'+id+"Table";
    var searchBarID = '#'+id+"SearchBar";

    if ( ! $.fn.DataTable.isDataTable( TableID ) ) {
        var initCompDmeta= function (settings, json) {
            var dmetaTable = $(TableID).DataTable();
            console.log("initCompDmeta")
            var columnsToSearch = { 2: 'Collection', 3:"Host", 4:"Project" };
            for (var i in columnsToSearch) {
                var selectID = id+'select-' + columnsToSearch[i];
                var filterID = id+'filter-' + columnsToSearch[i];
                var selectCollectionID = id+'select-' + "Collection";

                var api = new $.fn.dataTable.Api(settings);
                $(TableID+"_filter").css("display", "inline-block")
                $(searchBarID).append('<div style="margin-bottom:20px; padding-left:8px; display:inline-block;" id="' + filterID + '"></div>')
                var select = $('<select id="'+ selectID + '" name="' + columnsToSearch[i] + '" multiple="multiple"></select>')
                .appendTo($('#' + filterID).empty())
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
                    var visibleRows = dmetaTable.rows({ search: 'applied' })[0];
                    var selectedRows = dmetaTable.rows( '.selected' )[0];
                    api.column($(this).attr('data-col')).rows( function ( idx, data, node ) { 
                        if($.inArray(idx, visibleRows) === -1 && $.inArray(idx, selectedRows) !== -1) {
                            dmetaTable.row(idx).deselect(idx);
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
                createMultiselect('#' + selectID)
                createMultiselectBinder('#' + filterID)
                var selCollectionNameArr = $(TableID).data("select")
                if (selCollectionNameArr) {
                    if (selCollectionNameArr.length) {
                        $(TableID).removeData("select");
                        selectMultiselect("#"+selectCollectionID, selCollectionNameArr);
                        dmetaTable.rows({ search: 'applied' }).select();
                    }
                }
            }
        };
        var dataTableObj ={
            "columns": [{
                "data": "_id",
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
                {"defaultContent": "-","targets": "_all"}  //hides undefined error
            ],
            initComplete: initCompDmeta
        };
        dataTableObj.dom = '<"'+searchBarID+'.pull-left"f>rt<"pull-left"i><"bottom"p><"clear">'
        dataTableObj.destroy = true
        dataTableObj.data = data.data.data
        dataTableObj.hover = true
        // speed up the table loading
        dataTableObj.deferRender = true
        dataTableObj.scroller = true
        dataTableObj.scrollCollapse = true
        // dataTableObj.scrollY = 395
        dataTableObj.scrollX = true
        dataTableObj.sScrollX = true
        var dmetaTable = $(TableID).DataTable(dataTableObj);
        console.log(TableID)
    }
    //    else {
    //        var dmetaTable = $(TableID).DataTable();
    //        projectTable.ajax.reload(null, false);
    //    }
    //    dmetaTable.column(0).checkboxes.deselect();
}


$('#inputFilemodal').on('click', '#dmetaFiles', function (e) {
    var loadDmeta = function(url, id){
        getValuesAsync({p:"getSSOAccessTokenByUserID"}, function (s) {
            if (s && s[0] && s[0].accessToken){
                var accToken = s[0].accessToken;
                var dataSumUrl = url + "/api/v1/data/file/summary"
                $.ajax({
                    url: dataSumUrl,
                    method: "GET",
                    timeout: 0,
                    beforeSend: function (xhr) {
                        xhr.setRequestHeader('Authorization', `Bearer ${accToken}`);
                    },
                    success: function (data) {
                        console.log(data)
                        refreshDmetaTable(data, id)
                    },
                    error: function (errorThrown) {
                        console.log("AJAX Error occured.",errorThrown)
                        toastr.error("Error occured.");
                    }
                });
            }
        });
    }
    var dmetaurl = $("#dmetaFileTab").attr("dmetaurl")
    var dmetaid = $("#dmetaFileTab").attr("dmetaid")
    var dURLs = dmetaurl.split(",")
    var dIDs = dmetaid.split(",")
    for (var i = 0; i < dURLs.length; i++) {
        loadDmeta(dURLs[i],dIDs[i])
    }

});


$(document).on('click', '.showDetailSample', function (e) {
    var getHeaderRow = function (text){
        if (!text) text = "";
        return '<thead><tr><th>'+text+'</th></tr></thead>';
    }
    var getBodyRow = function (text){
        if (!text){
            text = "";
        }
        return '<tbody><tr><td>'+text+'</td></tr></tbody>';
    }
    var pattClean = function (text){
        if (!text){
            text = "";
        } else if (text.match(/s3:/i) || text.match(/gs:/i) ){
            var textPath = $.trim(text).split("\t")[0]
            if (textPath){
                text = textPath;
            }
        }
        return text;
    }
    var insertDetailsTable = function (data, type){
        console.log(data)
        // type: meta or file
        var tableRows = "";
        var detTableID = "#details_of_"+type+"_table"
        $(detTableID).empty()
        tableRows += getHeaderRow("Name:");
        tableRows += getBodyRow(data.name);
        var cpData = $.extend(true, {}, data);
        var file_dir = cpData.file_dir
        var files_used = cpData.files_used
        // convert dmeta format (Array) to dnext format
        if (file_dir.constructor === Array){
            file_dir = file_dir.join("\t");
        }
        if (files_used.constructor === Array){
            for (var i = 0; i < files_used.length; i++) {
                files_used[i] = files_used[i].join(",");;
            }
            files_used = files_used.join(" | ");
        }
        if (data.file_dir){
            tableRows += getHeaderRow("Input File(s) Directory:")
            tableRows += getBodyRow(pattClean(file_dir))
            tableRows += getHeaderRow("Input File(s):")
            tableRows += getBodyRow(files_used.replace(/\|/g, '<br/>'))
        } else {
            //geo files:
            tableRows += getHeaderRow("GEO ID:")
            tableRows += getBodyRow(files_used.replace(/\|/g, '<br/>'))
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
        tableRows += getBodyRow(pattClean(data.s3_archive_dir))
        tableRows += getHeaderRow("Google Storage Backup:")
        tableRows += getBodyRow(pattClean(data.gs_archive_dir))
        if (data.run_env){
            tableRows += getHeaderRow("Run Environment:")
            tableRows += getBodyRow(data.run_env) 
        }
        if (data.project_name){
            tableRows += getHeaderRow("Project(s):")
            tableRows += getBodyRow(data.project_name) 
        }
        $(detTableID).append(tableRows)
    }
    var clickedRow = $(e.target).closest('tr');
    var tableID =$(e.target).closest('table').attr('id');
    var selTable = $("#"+tableID).DataTable();
    var rowData = selTable.row(clickedRow).data();
    var type;
    if (tableID == "sampleTable"){
        type = "file"
    } else {
        type = "meta"
    }
    if ($("#detailsOf"+type+"Div").css("display") == "none"){
        $("#detailsOf"+type+"Div").css("display","block")
    }
    insertDetailsTable(rowData, type)
});