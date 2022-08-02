function getAllMarkdown(arr) {
    var allhtmlText = ""
    var arrText = ""
    if (arr.length) {
        for (var k = 0; k < 1; k++) {
            if (arr[k]) {
                var html = getMarkdown(arr[k])
                html = html.replace("<p>", '<p style="margin:0px;">')
                allhtmlText += html
            }
        }
    }
    return allhtmlText
}

function getMarkdown(text) {
    var converter = new showdown.Converter({ noHeaderId: true, tables: true, tables: true });
    var html = converter.makeHtml(text);
    return html
}

function prepSummary(summary) {
    var retObj = {}
    var labelArr = [];
    if (summary.match(/\[\!/)) {
        var sumArr = [];
        var lines = summary.split("\n");
        for (var i = 0; i < lines.length; i++) {
            var currline = lines[i];
            if (i < 10) {
                if (currline.match(/\[\!/)) {
                    var labels = currline.split("[!");
                    for (var k = 0; k < labels.length; k++) {
                        if ($.trim(labels[k])) {
                            labelArr.push("[!" + labels[k]);
                        }
                    }
                } else {
                    sumArr.push(currline)
                }
            } else {
                sumArr.push(currline)
            }
        }
        summary = sumArr.join('\n')
    }
    retObj.summary = summary
    retObj.labelArr = labelArr
    return retObj
}


//pagination and public page
$(function() {
    (function(name) {
        if (document.getElementById('pagination-' + name)) {
            var container = $('#pagination-' + name);
            var options = {
                dataSource: function(done) {
                    $.ajax({
                        beforeSend: function() {
                            container.prev().html('Loading data..');
                        },
                        data: { p: "getPublicPipelines" },
                        url: "ajax/ajaxquery.php",
                        success: function(response) {
                            //Sort the array by pin_order
                            var sorted = response.sort(function(a, b) {
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
                pageSize: 60,
                callback: function(response, pagination) {
                    //                window.console && console.log(response, pagination);
                    var dataHtml = '<section class="content" style="max-width: 1500px; "><h2 class="page-header">Public Pipelines</h2><div class="row">';
                    $.each(response, function(index, item) {
                        var retObj = {};
                        retObj = prepSummary(item.summary);
                        var summary = retObj.summary
                        var labelArr = retObj.labelArr
                        var labelhtml = getAllMarkdown(labelArr)
                        var href = 'index.php?np=1&id=' + item.id;
                        dataHtml += '<div style="min-width:25%; padding-right:30px; padding-bottom:25px;" class="col-md-4"><div style="cursor: pointer; height:300px;" class="movebox widget-user-2"  onclick="window.location=\'' + href + '\';"><div style="height:100px" class="widget-user-header "><div class="boxheader"><i style="font-size:30px; float:left; color:orange; padding:5px;" class="fa fa-spinner"></i><h4 style="text-align:center;">' + item.name + '</h4></div></div><div class="box-body"><p style="height:110px; overflow:hidden; word-break: break-all;">' + summary + '</p><div style="padding-top:10px;" class="pull-right"><a href="' + href + '" style="background-color:#508CB8;" class="btn btn-primary btn-sm ad-click-event">LEARN MORE</a></div><div style="padding-top:10px;" class="pull-left">' + labelhtml + '</div> </div></div></div>';
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
        }
        //        container.addHook('beforePageOnClick', function () {
        ////            window.console && console.log('beforePageOnClick...');
        //            //return false
        //        });
    })('public');
})