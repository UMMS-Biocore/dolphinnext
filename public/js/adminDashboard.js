//1. Which pipelines are being run the most often across the org? ( # of jobs) (vs. Which ones are not having much traction?) 
// 2. Which groups/departments are the heaviest users of DolphinNEXT? 
//   a.Which pipelines are they using? 
//   b.Who are the heaviest individual users? 
//her pipeline group icin hangi user ve group kac kere run etmis? 
//run tarihleri olursa zaman araligini daraltilbilir

$(document).ready(async function() {

    $s = { users: [], runStatsByPipeline: [], runStatsByPipelineChart: null };

    $s.runStatsByPipeline = await doAjax({
        p: "getRunStatsByPipeline"
    });
    $s.users = await doAjax({
        p: "getAllUsers"
    });
    console.log($s.users)
    console.log($s.runStatsByPipeline)


    const createMultiselect = function(id) {
        $(id).multiselect({
            enableFiltering: true,
            maxHeight: 400,
            includeResetOption: true,
            resetText: "Clear filters",
            includeResetDivider: true,
            includeSelectAllOption: true,
            buttonText: function(options, select) {
                if (options.length == 0) {
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
        });
        $(id).css("display", "inline-block")
    };



    const getData = (settings) => {
        const chartID = settings.chartID
        const dropdownOpts = settings.dropdownOpts
        const source = settings.source
        const groupby = settings.groupbyField
        const label = settings.labelField
        let obj = {}
        for (let n = 0; n < source.length; n++) {
            let groupbyVal = source[n][groupby]
            let skip = false;
            if (!obj[groupbyVal]) obj[groupbyVal] = { x: "", y: 0 }
            if (dropdownOpts.length) {
                for (let k = 0; k < dropdownOpts.length; k++) {
                    const opts = dropdownOpts[k];
                    const dropOpts = opts.options
                    const dropName = opts.name
                    let checkedVal = source[n][dropName];
                    // for multiselect
                    if (Array.isArray(dropOpts) && dropOpts.length && checkedVal && !dropOpts.includes(checkedVal)) {
                        skip = true;
                        // for regular dropdown;
                    } else if (!Array.isArray(dropOpts) && dropOpts && dropOpts !== checkedVal) {
                        skip = true;
                    }
                }
            }
            if (!skip) {
                obj[groupbyVal]["x"] = source[n][label]
                obj[groupbyVal]["y"]++
            }

        }
        let final = [];
        Object.keys(obj).forEach((k, i) => {
            final.push(obj[k])
        });
        final = sortByKey(final, "y", "desc")
        return final
    }


    const updateChart = (settings) => {
        const chartID = settings.chartID
        let data = getData(settings);
        console.log(data);
        // destroy first to update all data
        if ($s[chartID]) $s[chartID].destroy();
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

    // #############################################
    // ##############  CHARTS   ####################
    // #############################################
    // runsByPipelineUser chart

    const runsByPipelineUserID = "#runsByPipelineUser";
    for (var n = 0; n < $s.users.length; n++) {
        $(runsByPipelineUserID).append(
            '<option value="' +
            $s.users[n].id +
            '">' +
            $s.users[n].name +
            "</option>"
        );
    }

    $(runsByPipelineUserID).on("change", function() {
        var options = $(this).val();
        let dropdownOpts = [];
        // name should be the field in chart data
        dropdownOpts.push({ name: "own", options: options })
        updateChart({
            chartID: "runStatsByPipelineChart",
            dropdownOpts: dropdownOpts,
            source: $s.runStatsByPipeline,
            groupbyField: "gid",
            labelField: "pname"
        })
    })
    createMultiselect(runsByPipelineUserID)
    $(runsByPipelineUserID).trigger("change")

    // runsByUserID chart
    const runsByUserID = "#runsByUser";
    $(runsByUserID).on("change", function() {
        var options = $(this).val();
        // filtering based on selected options
        let dropdownOpts = [];
        // name should be the field in chart data
        // dropdownOpts.push({ name: "own", options: options })
        updateChart({
            chartID: "runStatsByUserChart",
            dropdownOpts: dropdownOpts,
            source: $s.runStatsByPipeline,
            groupbyField: options,
            labelField: options
        })
    })
    $(runsByUserID).trigger("change")


















})