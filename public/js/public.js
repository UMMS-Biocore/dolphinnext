//open the pipelines sidebar on entrance.
//var tagElems = $('#autocompletes1').children();
//$(tagElems).closest('li').addClass('menu-open');
//$(tagElems).closest('li').children('ul.treeview-menu').show();

//pagination and public page
$(function () {
    (function (name) {
        var container = $('#pagination-' + name);
        var options = {
            dataSource: function (done) {
                $.ajax({
                    beforeSend: function () {
                        container.prev().html('Loading data..');
                    },
                    data: { p: "getPublicPipelines" },
                    url: "ajax/ajaxquery.php",
                    success: function (response) {
                        //Sort the array by pin_order
                        var sorted = response.sort(function (a, b) {
                            if (a.pin_order === "0") {
                                return 1;
                            } else if (b.pin_order === "0") {
                                return -1;
                            } else if (b.pin_order !== "0" && a.pin_order !== "0") {
                                if (a.pin_order > b.pin_order) {
                                    return 1;
                                }
                                if (a.pin_order < b.pin_order) {
                                    return -1;
                                }
                            }
                            return 0;
                        });
                        done(sorted);
                    }
                });
            },
            pageSize: 6,
            callback: function (response, pagination) {
                //                window.console && console.log(response, pagination);
                var dataHtml = '<section class="content" style="max-width: 1500px; "><h2 class="page-header">Public Pipelines</h2><div class="row">';
                $.each(response, function (index, item) {
                    dataHtml += '<div style="min-width:25%; padding-right:30px; padding-bottom:25px;" class="col-md-4"><div style=" height:300px;" class="movebox widget-user-2"><div style="height:100px" class="widget-user-header "><div class="boxheader"><i style="font-size:30px; float:left; color:orange; padding:5px;" class="fa fa-spinner"></i><h4 style="text-align:center;">' + item.name + '</h4></div></div><div class="box-body"><p style="height:110px; overflow:hidden; word-break: break-all;">' + item.summary + '</p><div style="padding-top:10px;" class="pull-right"><a href="index.php?np=1&id=' + item.id + '" style="background-color:#508CB8;" class="btn btn-primary btn-sm ad-click-event">LEARN MORE</a></div></div></div></div>';
                });
                dataHtml += '</div></section>';
                container.prev().html(dataHtml);
            }
        };
        //$.pagination(container, options);
        //        container.addHook('beforeInit', function () {
        ////            window.console && console.log('beforeInit...');
        //        });
        container.pagination(options);
        //        container.addHook('beforePageOnClick', function () {
        ////            window.console && console.log('beforePageOnClick...');
        //            //return false
        //        });
    })('public');
})


