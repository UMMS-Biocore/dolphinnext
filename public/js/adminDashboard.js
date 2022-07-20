$(document).ready(async function() {
    $("#main-content-box-header").css("background-color", "#ecf0f5")

    $s = { users: [], runStatsByPipeline: [], runStatsByPipelineChart: null, fileCountStatsByPipeline: [], active_users: [] };

    let chartColors = {
        "red": "rgb(255, 99, 132)",
        "orange": "rgb(255, 159, 64)",
        "yellow": "rgb(255, 205, 86)",
        "green": "rgb(75, 192, 192)",
        "blue": "rgb(54, 162, 235)",
        "purple": "rgb(153, 102, 255)",
        "grey": "rgb(201, 203, 207)"
    }

    $s.runStatsByPipeline = await doAjax({
        p: "getRunStatsByPipeline",
        type: "runAttempt"
    });
    $s.runAttemptStatsByPipeline = await doAjax({
        p: "getRunStatsByPipeline",
        type: "run"
    });

    $s.users = await doAjax({
        p: "getAllUsers"
    });

    $s.active_users = await doAjax({
        p: "getRunStatsByPipeline",
        type: "active_user"
    });

    //clean null lab values
    for (var n = 0; n < $s.users.length; n++) {
        if ($s.users[n].lab == null || !$s.users[n].lab) $s.users[n].lab = "unknown"
    }
    for (var n = 0; n < $s.runStatsByPipeline.length; n++) {
        if ($s.runStatsByPipeline[n].olab == null || !$s.runStatsByPipeline[n].olab) $s.runStatsByPipeline[n].olab = "unknown"
    }
    for (var n = 0; n < $s.runAttemptStatsByPipeline.length; n++) {
        if ($s.runAttemptStatsByPipeline[n].olab == null || !$s.runAttemptStatsByPipeline[n].olab) $s.runAttemptStatsByPipeline[n].olab = "unknown"
    }

    console.log($s.users)
    console.log($s.runAttemptStatsByPipeline)
    console.log($s.runStatsByPipeline)
    console.log($s.active_users)


    const createMultiselect = function(id, selectAll) {
        $(id).multiselect({
            enableFiltering: true,
            maxHeight: 400,
            includeResetOption: true,
            resetText: "Clear Selections",
            includeResetDivider: true,
            includeSelectAllOption: true,
            enableCaseInsensitiveFiltering: true,
            buttonText: function(options, select) {
                const totalOptions = $(select).find("option").length
                if (options.length == 0) {
                    return select.attr("name") + ": None";
                } else if (options.length == totalOptions) {
                    return select.attr("name") + ": All";
                } else if (options.length > 2) {
                    return select.attr("name") + ": " + options.length + " selected";
                } else {
                    var labels = [];
                    options.each(function() {
                        labels.push($(this).text());
                    });
                    return select.attr("name") + ": " + labels.join(", ") + "";
                }
            },

        })
        if (selectAll) {
            $(id).multiselect('selectAll', false).multiselect('updateButtonText');
        }

        $(id).css("display", "inline-block")
    };



    const getData = (settings) => {
        const dropdownOpts = settings.dropdownOpts
        const source = settings.source
        const groupby = settings.groupbyField
        const label = settings.labelField
        let obj = {}
        for (let n = 0; n < source.length; n++) {
            let groupbyVal = source[n][groupby]
            let keepArr = [];
            let keep = true;
            if (!obj[groupbyVal]) obj[groupbyVal] = { label: "", total: 0, success: 0, error: 0 }
            if (dropdownOpts.length) {
                for (let k = 0; k < dropdownOpts.length; k++) {
                    keep = true;
                    const opts = dropdownOpts[k];
                    const dropOpts = opts.options
                    const dropName = opts.name
                    let checkedVal = source[n][dropName];
                    // for multiselect
                    if (Array.isArray(dropOpts)) {
                        if (dropOpts.length && (checkedVal && !dropOpts.includes(checkedVal) || checkedVal == "" || checkedVal == null)) {
                            keep = false;
                        } else if (dropOpts.length === 0) {
                            keep = false;
                        }
                        // for regular dropdown;
                    } else if (!Array.isArray(dropOpts) && dropOpts && dropOpts !== checkedVal) {
                        keep = false;
                    }
                    keepArr.push(keep)
                }
            } else {
                // for regular dropdown;
                keepArr.push(true)
            }
            if (keepArr.includes(true)) {
                //{x: 'RNA-seq Pipeline', y: 504}
                obj[groupbyVal]["label"] = source[n][label];
                if (source[n]["stat"] == "NextErr" || source[n]["stat"] == "Error") obj[groupbyVal]["error"]++;
                if (source[n]["stat"] == "NextSuc") obj[groupbyVal]["success"]++;
                if (source[n]["stat"] == "NextSuc" || source[n]["stat"] == "NextErr" || source[n]["stat"] == "Error") {
                    obj[groupbyVal]["total"]++;

                }
            }

        }
        let arrObj = [];
        Object.keys(obj).forEach((k, i) => {
            arrObj.push(obj[k])
        });
        console.log(arrObj)
        arrObj = sortByKey(arrObj, "total", { type: "desc" })
        console.log(arrObj)
        let total = []
        let success = []
        let error = []
        let labels = []
        for (let n = 0; n < arrObj.length; n++) {
            total.push(arrObj[n].total)
            labels.push(arrObj[n].label)
            success.push(arrObj[n].success)
            error.push(arrObj[n].error)
        }

        let datasets = [{
                label: 'Error',
                data: error,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
            },
            {
                label: 'Success',
                data: success,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
            },
        ]


        return [datasets, labels, total]
    }


    const createChart = (settings) => {
        const chartID = settings.chartID
        const datasets = settings.datasets
        const labels = settings.labels
        const legend = settings.legend
        const yAxes = settings.yAxes

        if ($s[chartID]) $s[chartID].destroy();
        $s[chartID] = new Chart(document.getElementById(chartID), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                scales: {
                    yAxes
                },
                responsive: true,
                plugins: {
                    legend: legend,
                    zoom: {
                        pan: {
                            enabled: true,
                        },
                        zoom: {
                            wheel: {
                                enabled: true
                            },
                            pinch: {
                                enabled: true
                            },
                            mode: 'x',
                        },
                    }
                },
            }
        });
    }

    const updateChart = (settings) => {
        const chartID = settings.chartID
        const stacked = settings.stacked
        let [datasets, labels, total] = getData(settings);
        // destroy first to update all data
        if ($s[chartID]) $s[chartID].destroy();
        if (stacked) {
            $s[chartID] = new Chart(document.getElementById(chartID), {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                footer: function(items) {
                                    console.log(items)
                                    let total = items[0].parsed._stacks.y[0] + items[0].parsed._stacks.y[1]
                                    return 'Total: ' + total;
                                }
                            }
                        },

                        // legend: {
                        //     display: false
                        // },
                        zoom: {
                            pan: {
                                enabled: true,
                            },
                            zoom: {
                                wheel: {
                                    enabled: true
                                },
                                pinch: {
                                    enabled: true
                                },
                                mode: 'x',
                            },
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                        },
                        y: {
                            beginAtZero: true,
                            stacked: true
                        }
                    }
                }
            });

        } else {
            $s[chartID] = new Chart(document.getElementById(chartID), {
                type: 'bar',
                data: {
                    datasets: [{
                        label: "# of runs",
                        data: data,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 206, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        },
                        zoom: {
                            pan: {
                                enabled: true,
                            },
                            zoom: {
                                wheel: {
                                    enabled: true
                                },
                                pinch: {
                                    enabled: true
                                },
                                mode: 'x',
                            },
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }



    }

    // #############################################
    // ##############  CHARTS   ####################
    // #############################################
    // runsByPipelineUser chart

    const runsByPipelineUserID = "#runsByPipelineUser";
    const runsByPipelineGroup = "#runsByPipelineGroup";
    const runsByPipelineDataID = "#runsByPipelineData";


    $s.users_sorted = sortByKey($s.users, "name", { caseinsensitive: true })
    $s.lab_sorted = sortByKey($s.users, "lab", { caseinsensitive: true })

    for (var n = 0; n < $s.users_sorted.length; n++) {
        $(runsByPipelineUserID).append(
            '<option value="' +
            $s.users_sorted[n].id +
            '">' +
            $s.users_sorted[n].name +
            "</option>"
        );
    }

    let labList = []
    for (var n = 0; n < $s.lab_sorted.length; n++) {
        if (!$s.lab_sorted[n].lab) $s.lab_sorted[n].lab = ""
        if (!labList.includes($s.lab_sorted[n].lab)) {
            label = $s.lab_sorted[n].lab
            if (!$s.lab_sorted[n].lab) {
                label = "Unknown"
            }
            $(runsByPipelineGroup).append(
                '<option value="' +
                $s.lab_sorted[n].lab +
                '">' +
                label +
                "</option>"
            );
            labList.push($s.lab_sorted[n].lab)
        }

    }

    $(`${runsByPipelineUserID},${runsByPipelineGroup},${runsByPipelineDataID}`).on("change", function() {
        var options = $(runsByPipelineUserID).val();
        var optionsGroup = $(runsByPipelineGroup).val();
        var dataSource = $(runsByPipelineDataID).val();
        let source = "";
        if (dataSource == "run") {
            source = $s.runAttemptStatsByPipeline;
            $("#runStatsByPipelineTitle").text("Total Runs by Pipeline")
        } else if (dataSource == "run_attempt") {
            source = $s.runStatsByPipeline;
            $("#runStatsByPipelineTitle").text("Total Run Attempts by Pipeline")
        }
        let dropdownOpts = [];
        // name should be the field in chart data
        dropdownOpts.push({ name: "own", options: options })
        dropdownOpts.push({ name: "olab", options: optionsGroup })
        updateChart({
            chartID: "runStatsByPipelineChart",
            dropdownOpts: dropdownOpts,
            source: source,
            groupbyField: "gid",
            labelField: "pname",
            stacked: true
        })
    });
    createMultiselect(runsByPipelineUserID, true);
    createMultiselect(runsByPipelineGroup, false);
    $(runsByPipelineUserID).trigger("change");

    // runsByUserID chart
    const runsByUserID = "#runsByUser";
    const runStatsByUserDataID = "#runStatsByUserData";
    $(`${runsByUserID},${runStatsByUserDataID}`).on("change", function() {
        var options = $(runsByUserID).val();
        var dataSource = $(runStatsByUserDataID).val();
        let source = "";
        if (dataSource == "run") {
            source = $s.runAttemptStatsByPipeline;
            $("#runStatsByUserTitle").text("Total Runs by User/Lab")
        } else if (dataSource == "run_attempt") {
            source = $s.runStatsByPipeline;
            $("#runStatsByUserTitle").text("Total Run Attempts by User/Lab")
        }
        // filtering based on selected options
        let dropdownOpts = [];
        // name should be the field in chart data
        // dropdownOpts.push({ name: "own", options: options })
        updateChart({
            chartID: "runStatsByUserChart",
            dropdownOpts: dropdownOpts,
            source: source,
            groupbyField: options,
            labelField: options,
            stacked: true
        })
    });
    $(runsByUserID).trigger("change")


    // chart-3: "runStatsTotalUsers"
    function sumArray(array) {
        let sum = 0;
        array.forEach(item => { sum += item; });
        return sum;
    }

    const getTotalUserData = () => {
        // prepare activeUsersObj
        let activeUsersObj = {}
        for (var n = 0; n < $s.active_users.length; n++) {
            let year = parseInt($s.active_users[n].year);
            let month = parseInt($s.active_users[n].month);
            let owner_id = $s.active_users[n].owner_id;
            let yearmonth = year + "-" + month;
            if (!activeUsersObj[yearmonth]) activeUsersObj[yearmonth] = {}
            activeUsersObj[yearmonth][owner_id] = 1;
        }
        console.log(activeUsersObj)

        let obj = {}
        for (var n = 0; n < $s.users.length; n++) {
            let yearmonthArr = $s.users[n].memberdate.split("-");
            let yearmonth = yearmonthArr[0] + "-" + parseInt(yearmonthArr[1]);
            if (!obj[yearmonth]) obj[yearmonth] = { label: "", total: 0 }
            obj[yearmonth]["label"] = yearmonth;
            obj[yearmonth]["total"]++;
        }

        let arrObj = [];
        Object.keys(activeUsersObj).forEach((k, i) => {
            let yearMonth = k;
            let activeUsers = 0
            if (!obj[k]) {
                obj[k] = { label: yearMonth, total: 0 }
            }
            if (activeUsersObj[k]) {
                activeUsers = Object.keys(activeUsersObj[k]).length
            }

            obj[k].active = activeUsers
            arrObj.push(obj[k])
        });

        arrObj = arrObj.sort(function(a, b) {
            a = a.label.split("-");
            b = b.label.split("-")
            return new Date(a[0], a[1], 1) - new Date(b[0], b[1], 1)
        });
        let each = []
        let total = []
        let labels = []
        let active = []


        for (let n = 0; n < arrObj.length; n++) {
            each.push(arrObj[n].total)
            total.push(sumArray(each))
            labels.push(arrObj[n].label)
            active.push(arrObj[n].active)
        }
        let datasets = [{
                label: 'New Users',
                data: each,
                borderColor: 'rgba(255, 99, 132, 0.2)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
            },
            {
                label: 'Total Users',
                data: total,
                borderColor: "#64d0e5",
                backgroundColor: "#64d0e5",
                type: 'line',
            },
            {
                label: 'Active Users',
                data: active,
                borderColor: chartColors.green,
                backgroundColor: chartColors.green,
                type: 'line',
            },
        ]
        return [datasets, labels]
    }

    let [userDataset, userlabels] = getTotalUserData()
    createChart({
        chartID: "runStatsTotalUsers",
        datasets: userDataset,
        labels: userlabels,
        legend: { position: "top" },
        yAxes: {}
    })


    // chart-4: "runStatsAvgRun"
    const getAvgRunTime = () => {
        let obj = {}
        for (var n = 0; n < $s.runStatsByPipeline.length; n++) {
            let status = $s.runStatsByPipeline[n].stat
            let duration = $s.runStatsByPipeline[n].dur
            let gid = $s.runStatsByPipeline[n].gid
            let pname = $s.runStatsByPipeline[n].pname
            if (status == "NextSuc") {
                //duration  9h 43m 18s;
                //duration  43m 18s;
                //duration  18s;
                duration = $.trim(duration);
                durations = duration.split(" ");
                totalSec = 0;
                for (var k = 0; k < durations.length; k++) {
                    if (durations[k].match(/s/)) {
                        totalSec += parseInt(durations[k].slice(0, -1));
                    } else if (durations[k].match(/m/)) {
                        totalSec += parseInt(durations[k].slice(0, -1)) * 60;
                    } else if (durations[k].match(/h/)) {
                        totalSec += parseInt(durations[k].slice(0, -1)) * 60 * 60;
                    } else if (durations[k].match(/d/)) {
                        totalSec += parseInt(durations[k].slice(0, -1)) * 60 * 60 * 24;
                    }
                }
                // exclude runs that are faster than 5 minutes (resumed jobs)
                if (totalSec > 300) {
                    if (!obj[gid]) obj[gid] = { label: "", total: [] }
                    obj[gid]["label"] = pname;
                    obj[gid]["total"].push(totalSec);
                }


            }

        }
        let arrObj = [];
        Object.keys(obj).forEach((k, i) => {
            arrObj.push(obj[k])
        });
        arrObj = sortByKey(arrObj, "total", { type: "desc" })
        let total = []
        let labels = []
        for (let n = 0; n < arrObj.length; n++) {
            total.push(sumArray(arrObj[n].total) / arrObj[n].total.length / 60 / 60)
            labels.push(arrObj[n].label)
        }
        let datasets = [{
            data: total,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
        }]
        return [datasets, labels]
    }

    let [avgTimeDataset, avgTimelabels] = getAvgRunTime();
    createChart({
        chartID: "runStatsAvgRun",
        datasets: avgTimeDataset,
        labels: avgTimelabels,
        legend: { display: false },
        yAxes: {
            title: {
                display: true,
                text: "Hours",
            }
        }
    });

    // chart-5: "fileStatsByPipeline"
    $s.fileCountStatsByPipeline = await doAjax({
        p: "getRunStatsByPipeline",
        type: "file_count"
    });
    console.log($s.fileCountStatsByPipeline)
    const getFileStatsByPipeline = () => {
        let obj = {}
        for (var n = 0; n < $s.fileCountStatsByPipeline.length; n++) {
            let fileCount = $s.fileCountStatsByPipeline[n].fileCount
            let gid = $s.fileCountStatsByPipeline[n].gid
            let pname = $s.fileCountStatsByPipeline[n].pname
            if (!obj[gid]) obj[gid] = { label: "", total: 0 }
            obj[gid]["label"] = pname;
            obj[gid]["total"] += parseInt(fileCount);

        }
        console.log(obj)
        let arrObj = [];
        Object.keys(obj).forEach((k, i) => {
            arrObj.push(obj[k])
        });
        arrObj = sortByKey(arrObj, "total", { type: "desc" })
        let total = []
        let labels = []
        for (let n = 0; n < arrObj.length; n++) {
            total.push(arrObj[n].total)
            labels.push(arrObj[n].label)
        }
        let datasets = [{
            data: total,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
        }]
        return [datasets, labels]
    }

    let [fileByPipelineDataset, fileByPipelineLabels] = getFileStatsByPipeline();
    createChart({
        chartID: "fileStatsByPipeline",
        datasets: fileByPipelineDataset,
        labels: fileByPipelineLabels,
        legend: { display: false },
        yAxes: {}
    });


    // after createMultiselects to trigger change on reset
    $('.multiselect-reset').on('click', function() { $(this).closest('.multiselect-native-select').find('select').trigger("change") });
















})