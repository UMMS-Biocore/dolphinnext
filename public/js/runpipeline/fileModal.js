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
                var dataSumUrl = url + "/api/v1/projects/vitiligo/data/file/summary"
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



var fillEditSampleModal = function (data, type, cb){
    var newObj = {} 
    console.log(data)
    newObj.name = data.name
    newObj.collection_type = data.collection_type
    newObj.s3_archive_dir = data.s3_archive_dir
    newObj.archive_dir = data.archive_dir
    newObj.gs_archive_dir = data.gs_archive_dir
    newObj.run_env = data.run_env
    newObj.project_name = data.project_name
    newObj.collection_id = data.collection_id
    newObj.file_type = data.file_type

    var cpData = $.extend(true, {}, data);

    var file_dir = cpData.file_dir
    var files_used = cpData.files_used
    if (file_dir.constructor === Array){
        file_dir = file_dir.join("\t");
    }
    newObj.file_dir = file_dir

    if (files_used.constructor === Array){
        for (var i = 0; i < files_used.length; i++) {
            files_used[i] = files_used[i].join(",");;
        }
        files_used = files_used.join(" | ");
    }
    if (type == "single"){
        $("#editSampleModal").find("[name=name]").closest(".form-group").css("display","block");
        //geo files:
        if (!data.file_dir){
            newObj.geo_used = files_used.replace(/ \| /g, "\n")
            $("#editSampleModal").find("[name=geo_used]").closest(".form-group").css("display","block");
            $("#editSampleModal").find("[name=files_used]").closest(".form-group").css("display","none");
            $("#editSampleModal").find("[name=file_dir]").closest(".form-group").css("display","none");
        } else {
            $("#editSampleModal").find("[name=geo_used]").closest(".form-group").css("display","none");
            $("#editSampleModal").find("[name=files_used]").closest(".form-group").css("display","block");
            $("#editSampleModal").find("[name=file_dir]").closest(".form-group").css("display","block");
            newObj.files_used = files_used.replace(/ \| /g, "\n")
        }
    } else if (type == "multi"){
        $("#editSampleModal").find("[name=geo_used]").closest(".form-group").css("display","none");
        $("#editSampleModal").find("[name=name]").closest(".form-group").css("display","none");
        $("#editSampleModal").find("[name=files_used]").closest(".form-group").css("display","none");
        $("#editSampleModal").find("[name=file_dir]").closest(".form-group").css("display","block");
    }
    selectizeCollection(["#collection_id_edit"], newObj.collection_id.split(","), true, cb);
    fillFormByName('#editSampleModal', 'input, select, textarea', newObj);
    //    

}




$(document).on('click', '.singleEditSample', function (e) {
    $('#editSampleModal').find("form").trigger("reset");
    removeMultiUpdateModal('#editSampleModal')
    var clickedRow = $(this).closest("tr");
    var selRows = $("#sampleTable").DataTable().rows({ selected: true })

    var clickedRowsData = $("#sampleTable").DataTable().rows(clickedRow).data();
    var selRowsIds = []
    for (var i = 0; i < clickedRowsData.length; i++) {
        selRowsIds.push(clickedRowsData[i].id);
    }
    $('#saveEditSampleModal').data('selRows', selRows[0]);
    $('#saveEditSampleModal').data('clickedrows', selRowsIds);
    $('#saveEditSampleModal').data('oldcollections', clickedRowsData[0].collection_id.split(","));
    if (clickedRowsData.length > 1){
        var cb = function(){
            prepareMultiUpdateModal('#editSampleModal', 'input, select, textarea')
        }
        fillEditSampleModal(clickedRowsData[0], "multi", cb)

    } else {
        fillEditSampleModal(clickedRowsData[0], "single", null)
    }
    $('#editSampleModal').modal("show");
});

$(document).on('click', '#editSample', function (e) {
    $('#editSampleModal').find("form").trigger("reset");
    removeMultiUpdateModal('#editSampleModal')
    var selRows = $("#sampleTable").DataTable().rows({ selected: true })
    var selRowsData = selRows.data();
    var selRowsIds = []
    for (var i = 0; i < selRowsData.length; i++) {
        selRowsIds.push(selRowsData[i].id);
    }
    $('#saveEditSampleModal').data('selRows', selRows[0]);
    $('#saveEditSampleModal').data('clickedrows', selRowsIds);
    $('#saveEditSampleModal').data('oldcollections', selRowsData[0].collection_id.split(","));
    if (selRowsData.length > 1){
        var cb = function(){
            prepareMultiUpdateModal('#editSampleModal', 'input, select, textarea')
        }
        fillEditSampleModal(selRowsData[0], "multi", cb)

    } else {
        fillEditSampleModal(selRowsData[0], "single", null)
    }
    $('#editSampleModal').modal("show");
})

$(document).on('click', '#saveEditSampleModal', async function (e) {
    e.preventDefault();
    var selRows = $('#saveEditSampleModal').data('selRows');
    var clickedRows = $('#saveEditSampleModal').data('clickedrows');
    var type = clickedRows.length > 1 ? "multi" : "single";
    var oldcollections = $('#saveEditSampleModal').data('oldcollections');
    var formValues = $('#editSampleModal').find('input, select, textarea');

    var requiredFields = [];
    if (type == "single") requiredFields = ["name"];
    var formObj = {};
    var stop = "";
    [formObj, stop] = createFormObj(formValues, requiredFields, {onlyVisible:true});
    formObj.file_id = clickedRows;
    formObj.files_used = $.trim(formObj.files_used);
    var removedCollections = []
    if (type == "single"){
        if (formObj.geo_used) formObj.files_used = formObj.geo_used
        formObj.files_used = formObj.files_used.replaceAll("\n"," | ")
    }
    if ( stop === false) {
        if (formObj.collection_id){
            removedCollections = $(oldcollections).not(formObj.collection_id).get();
            //new items come with prefix: _newItm_
            for (var i = 0; i < formObj.collection_id.length; i++) {
                var eachCollID = formObj.collection_id[i]
                var collection_name = $("#collection_id_edit")[0].selectize.getItem(
                    eachCollID
                )[0].innerHTML;
                collection_name = cleanSpecChar(collection_name);
                if (eachCollID.match(/^_newItm_(.*)/)) {
                    var collection_data = await doAjax({
                        p: "saveCollection",
                        name: collection_name,
                    });
                    if (collection_data.id) {
                        formObj.collection_id[i] = collection_data.id;
                    }
                }
            }
        }
        formObj.removedCollections = removedCollections;
        formObj.p = "updateFile";
        console.log(formObj);
        $.ajax({
            type: "POST",
            url: "ajax/ajaxquery.php",
            data: formObj,
            async: true,
            success: function (s) {
                if (s.length) {
                    var collectionNameArr = $("#select-Collection").val()
                    $("#sampleTable").data("select", collectionNameArr);
                    $("#sampleTable").data("selectIdx", selRows);
                    $("#sampleTable").DataTable().ajax.reload(null, false);
                    $("#editSampleModal").modal("hide");
                }
            },
            error: function (errorThrown) {
                alert("Error: " + errorThrown);
            },
        });
    }
})

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
        } else if (data.collection_type == "triple"){
            collection_type = "Triple List"
        } else if (data.collection_type == "quadruple"){
            collection_type = "Quadruple List"
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