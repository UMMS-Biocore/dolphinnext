function getContainerOptions(containerOwn, containerId) {
    if (containerOwn == "1") {
        viewButton = `<div style="display: inline-flex"><button type="button" class="btn btn-primary btn-sm viewDetails" title="View" onclick="location.href='index.php?np=7&id=${containerId}'" >Details</button> &nbsp;   `
        return viewButton + getTableButtons("container", EDIT | REMOVE);
    } else {
        return "";
    }
}




$(document).ready(function() {
    var containerTable = $('#containertable').DataTable({
        "scrollY": "500px",
        "scrollCollapse": true,
        "scrollX": true,
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getUserContainers" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "name",
            "fnCreatedCell": function(nTd, sData, oData, iRow, iCol) {
                $(nTd).html("<a href='index.php?np=7&id=" + oData.id + "'>" + oData.name + "</a>");
            }
        }, {
            "data": "summary"
        }, {
            "data": "image_name"
        }, {
            "data": "type"
        }, {
            "data": "status"
        }, {
            "data": "username"
        }, {
            "data": "date_created"
        }, {
            data: null,
            className: "center",
            fnCreatedCell: function(nTd, sData, oData, iRow, iCol) {
                $(nTd).html(getContainerOptions(oData.own, oData.id));
            }
        }]
    });
    var sharedcontainerTable = $('#sharedcontainertable').DataTable({
        "scrollY": "500px",
        "scrollCollapse": true,
        "scrollX": true,
        "ajax": {
            url: "ajax/ajaxquery.php",
            data: { "p": "getSharedContainers" },
            "dataSrc": ""
        },
        "columns": [{
            "data": "name",
            "fnCreatedCell": function(nTd, sData, oData, iRow, iCol) {
                $(nTd).html("<a href='index.php?np=7&id=" + oData.id + "'>" + oData.name + "</a>");
            }
        }, {
            "data": "summary"
        }, {
            "data": "image_name"
        }, {
            "data": "type"
        }, {
            "data": "status"
        }, {
            "data": "username"
        }, {
            "data": "date_created"
        }]
    });


    $('#containermodal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        $(this).find('form').trigger('reset');
        var allUserGrp = getValues({ p: "getUserGroups" });
        fillDropdownArrObj(allUserGrp, "id", "name", "#groupSel", true, '<option value="0"  selected>Choose group </option>')

        if (button.attr('id') === 'addcontainer') {
            $('#containermodaltitle').html('Add New Container');
        } else {
            $('#containermodaltitle').html('Edit Container');
            var clickedRow = button.closest('tr');
            var rowData = containerTable.row(clickedRow).data();
            $('#savecontainer').data('clickedrow', clickedRow);
            fillFormByName('#containermodal', 'input, select, textarea', rowData);
        }
    });

    $('#containermodal').on('click', '#savecontainer', function(event) {
        event.preventDefault();
        var formValues = $('#containermodal').find('input,select, textarea');
        var savetype = $('#mContainerID').val();
        var requiredFields = ["name"];
        var clickedRow = $('#savecontainer').data('clickedrow')
        var formObj = {};
        var stop = "";
        [formObj, stop] = createFormObj(formValues, requiredFields)
        console.log(formObj)
        if (stop === false) {
            formObj.name = encodeURIComponent(formObj.name);
            formObj.summary = encodeURIComponent(formObj.summary);
            formObj.p = "saveContainer"
            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: formObj,
                async: true,
                success: function(s) {
                    if (savetype.length) { //edit
                        var newData = getValues({ p: "getContainers", id: savetype })
                        if (newData[0]) {
                            containerTable.row(clickedRow).remove().draw();
                            containerTable.row.add(newData[0]).draw();
                        }
                    } else { //insert
                        var newData = getValues({ p: "getContainers", id: s.id })
                        if (newData[0]) {
                            containerTable.row.add(newData[0]).draw();
                        }
                    }
                    $('#containermodal').modal('hide');
                },
                error: function(errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
    });



    $('#containertable').on('click', '#containerremove', function(e) {
        e.preventDefault();
        var clickedRow = $(this).closest('tr');
        var rowData = containerTable.row(clickedRow).data();
        var container_id = rowData.id;
        var container_name = rowData.name;
        var text = 'Are you sure you want to delete this app: "' + container_name + '" ?';
        // var proPipeGet = getValues({
        //     "container_id": container_id,
        //     "p": "getcontainerPipelines"
        // });
        // if (proPipeGet.length){
        //     text += "</br></br>Following runs will be deleted as well:</br>";
        //     for (var i = 0; i < proPipeGet.length; i++) {
        //         text += "- "+ proPipeGet[i].pp_name + "</br>";
        //     }
        // }
        var savedData = clickedRow;
        var execFunc = function(savedData) {
            var containerTable = $('#containertable').DataTable();
            var rowData = containerTable.row(savedData).data();

            $.ajax({
                type: "POST",
                url: "ajax/ajaxquery.php",
                data: {
                    p: "removeContainer",
                    'id': rowData.id,
                    'name': rowData.name
                },
                async: true,
                success: function(s) {
                    if (s) {
                        if ($.isEmptyObject(s)) {
                            containerTable.row(rowData).remove().draw();
                            containerTable.ajax.reload(null, false);
                        } else {
                            showInfoModal("#infoMod", "#infoModText", s)
                        }
                    }

                },
                error: function(errorThrown) {
                    alert("Error: " + errorThrown);
                }
            });
        }
        showConfirmDeleteModal(text, savedData, execFunc)
    });



});