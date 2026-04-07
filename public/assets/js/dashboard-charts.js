var timelineChart, typeChart, agentChart, teamChart, statusHistoryChart;


function initializeCharts(data) {
    createStatusHistoryChart('new');  // NEW CHART - initialize with "new" data

    createTimelineChart(data.fiscalYearComparison || {});  // CHANGED
    createTypeChart(data.projectsByType || []);
    createAgentChart(data.projectsByAgent || []);
    //createTeamChart(data.projectsByTeam || []);
    createTeamChart(data.projectsByTeamFiscalYear || []); // <--- Projects by Team

    loadStatusAgentData(data); // Agent Status Summary Chart

    createInProgressByAgentChart(data.inProgressByAgent || []); // <--- In Progress By agent chart

}

/**
 * Create fiscal year comparison chart
 */
function createTimelineChart(data) {
    console.log('createTimelineChart got:', data);
    if (!data || !Array.isArray(data.current_fy) || !Array.isArray(data.previous_fy)) {
        console.warn('No fiscal year comparison data');
        return;
    }

    if (!data || !data.current_fy || !data.previous_fy) {
        console.warn('No fiscal year comparison data');
        return;
    }

    const ctx = document.getElementById('timelineChart').getContext('2d');

    // Month labels (Apr to current month for current FY)
    const monthNames = ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];

    // Create arrays for all 12 months (Apr=4 to Mar=3)
    const currentData = new Array(12).fill(0);
    const previousData = new Array(12).fill(0);

    // Fill current FY data
    data.current_fy.forEach(item => {
        const monthIndex = item.month >= 4 ? item.month - 4 : item.month + 8; // Apr=0, May=1, ..., Mar=11
        currentData[monthIndex] = parseInt(item.count);
    });

    // Fill previous FY data
    data.previous_fy.forEach(item => {
        const monthIndex = item.month >= 4 ? item.month - 4 : item.month + 8;
        previousData[monthIndex] = parseInt(item.count);
    });

    if (timelineChart) timelineChart.destroy();

    timelineChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: monthNames,
            datasets: [
                {
                    label: data.current_fy_label || 'Current FY',
                    data: currentData,
                    backgroundColor: transparentize('#0d6efd', 0.5),
                    borderColor: '#0d6efd',
                    borderWidth: 2
                },
                {
                    label: data.previous_fy_label || 'Previous FY',
                    data: previousData,
                    backgroundColor: transparentize('#6c757d', 0.5),
                    borderColor: '#6c757d',
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 2 }
                }
            }
        }
    });
}

/**
 * Create Projects by type chart
 */
function createTypeChart(data) {
    if (!data || data.length === 0) {
        console.warn('No type data');
        return;
    }

    const ctx = document.getElementById('typeChart').getContext('2d');

    const labels = data.map(item => item.type);
    const counts = data.map(item => parseInt(item.count));

    const colors = [
        'rgba(13, 110, 253, 0.8)',
        'rgba(25, 135, 84, 0.8)',
        'rgba(255, 193, 7, 0.8)',
        'rgba(220, 53, 69, 0.8)',
        'rgba(13, 202, 240, 0.8)',
        'rgba(108, 117, 125, 0.8)'
    ];

    if (typeChart) typeChart.destroy();

    typeChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: counts,
                backgroundColor: colors.slice(0, data.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
}


/**
 * Create TOP 10 agents chart
 */
function createAgentChart(data) {
    if (!data || data.length === 0) {
        console.warn('No agent data');
        return;
    }

    const ctx = document.getElementById('agentChart').getContext('2d');

    const labels = data.map(item => item.agent);
    const counts = data.map(item => parseInt(item.count));

    if (agentChart) agentChart.destroy();

    agentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Projects',
                data: counts,
                backgroundColor: 'rgba(25, 135, 84, 0.8)',
                borderColor: 'rgba(25, 135, 84, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
}

/**
 * Create Projects by Team chart
 */
function createTeamChart(data) {
    const ctx = document.getElementById('teamChart').getContext('2d');
    const labels = data.map(item => item.team);
    const counts = data.map(item => parseInt(item.count));
    const colors = [
        'rgba(255,193,7,0.8)', 'rgba(13,110,253,0.8)',
        'rgba(220,53,69,0.8)', 'rgba(25,135,84,0.8)',
        'rgba(13,202,240,0.8)', 'rgba(108,117,125,0.8)'
    ];
    if (teamChart) teamChart.destroy();
    teamChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels,
            datasets: [{
                data: counts,
                backgroundColor: colors.slice(0, data.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
}

/**
 * Create full-width status history chart
 */
function createStatusHistoryChart(type) {
    const ctx = document.getElementById('statusHistoryChart');
    if (!ctx) {
        console.error('statusHistoryChart canvas not found');
        return;
    }

    let rawData = [];
    let label = '';
    let baseColor = '';

    switch (type) {
        case 'new':
            rawData = monthlyData.new || [];
            label = 'New';
            baseColor = '#5bc0de';
            break;
        case 'completed':
            rawData = monthlyData.completed || [];
            label = 'Completed';
            baseColor = '#f0ad4e';
            break;
        case 'signed':
            rawData = monthlyData.signed || [];
            label = 'Contract Signed';
            baseColor = '#5cb85c';
            break;
    }

    // Filter data from June 2021 onwards
    const data = rawData.filter(item => item.month >= '2021-06');

    if (!data || data.length === 0) {
        console.warn('No data for status history chart type:', type);
        return;
    }

    const labels = data.map(item => {
        const parts = item.month.split('-');
        const date = new Date(parts[0], parts[1] - 1);
        return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
    });

    const counts = data.map(item => parseInt(item.count));

    // Calculate average
    const average = counts.reduce((a, b) => a + b, 0) / counts.length;

    if (statusHistoryChart) statusHistoryChart.destroy();

    statusHistoryChart = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                // Bar dataset
                {
                    label: label,
                    data: counts,
                    backgroundColor: transparentize(baseColor, 0.5),
                    borderColor: baseColor,
                    borderWidth: 2,
                    order: 2  // Draw bars first (higher order = behind)
                },
                // Average line dataset
                {
                    label: 'Average',
                    data: Array(counts.length).fill(average),
                    type: 'line',
                    borderColor: '#e74c3c',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    fill: false,
                    pointRadius: 0,
                    pointHoverRadius: 0,
                    order: 1  // Draw line on top (lower order = in front)
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    color: '#444',
                    font: {
                        size: 10,
                        weight: 'bold'
                    },
                    formatter: function (value, context) {
                        // Only show labels for bar chart, not the average line
                        if (context.datasetIndex === 0) {
                            return value;
                        }
                        return '';
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 5 }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}

/** 
 *  Preparing data for Staus by Agent chart
 */
// Dedicated variables for the new horizontal bar chart
let statusAgentChart, statusAgentData = { fiscal: [], all: [] };

// Data loading and initialization
function loadStatusAgentData(allData) {
    statusAgentData.fiscal = allData.agentStatusFiscal || [];
    statusAgentData.all = allData.agentStatusAll || [];

    // Draw default (fiscal) on load
    drawStatusAgentChart('fiscal');
}

// Dropdown selection handler
$('#agentStatusPeriod').on('change', function () {
    drawStatusAgentChart(this.value);
});

/**
 * Create full-width status by agent chart
 */
function drawStatusAgentChart(period) {
    const data = statusAgentData[period] || [];
    const labels = data.map(item => item.agent);
    const contractSigned = data.map(item => parseInt(item.contract_signed));
    const completed = data.map(item => parseInt(item.completed));
    const inProgress = data.map(item => parseInt(item.in_progress));
    const cancelled = data.map(item => parseInt(item.cancelled));
    const totals = data.map(item => parseInt(item.total_projects));

    // Destroy previous instance if it exists
    if (statusAgentChart) statusAgentChart.destroy();

    // Create new Chart
    statusAgentChart = new Chart(document.getElementById('statusAgent').getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Contract Signed', data: contractSigned, backgroundColor: 'rgba(92,184,92,0.7)' },
                { label: 'Completed', data: completed, backgroundColor: 'rgba(240,173,78,0.7)' },
                { label: 'In Progress', data: inProgress, backgroundColor: 'rgba(91,192,222,0.7)' },
                { label: 'Cancelled/Other', data: cancelled, backgroundColor: 'rgba(217,83,79,0.7)' }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const total = totals[context.dataIndex];
                            const val = context.parsed.x;
                            const percent = total ? ((val / total) * 100).toFixed(1) : 0;
                            return `${context.dataset.label}: ${val} (${percent}%)`;
                        }
                    }
                }
            },
            scales: {
                x: { stacked: true, beginAtZero: true },
                y: { stacked: true }
            }
        }
    });
}

/**
 * Create In Progress By agent chart
 */
function createInProgressByAgentChart(data) {
    const ctx = document.getElementById('inProgressByAgent').getContext('2d');
    const labels = data.map(item => item.agent);
    const newData = data.map(i => parseInt(i.new_projects));
    const qualifyingData = data.map(i => parseInt(i.qualifying_projects));
    const designData = data.map(i => parseInt(i.design_projects));
    const pendingData = data.map(i => parseInt(i.pending_projects));

    if (window.inProgressByAgentChart) window.inProgressByAgentChart.destroy();

    window.inProgressByAgentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'New', data: newData, backgroundColor: 'rgba(91,192,222,0.7)' },
                { label: 'Qualifying', data: qualifyingData, backgroundColor: 'rgba(255,193,7,0.7)' },
                { label: 'Design', data: designData, backgroundColor: 'rgba(2,117,216,0.7)' },
                { label: 'Pending', data: pendingData, backgroundColor: 'rgba(217,83,79,0.7)' }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true },
                y: { stacked: true, beginAtZero: true }
            },
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: 'In Progress Projects per Active Agent' }
            }
        }
    });
}

/**
 * Helper function to add transparency to hex colors
 */
function transparentize(hexColor, opacity) {
    const r = parseInt(hexColor.slice(1, 3), 16);
    const g = parseInt(hexColor.slice(3, 5), 16);
    const b = parseInt(hexColor.slice(5, 7), 16);
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}


// Handle status history dropdown change
$(document).ready(function () {
    $('#statusHistorySelector').on('change', function () {
        const selectedType = $(this).val();
        createStatusHistoryChart(selectedType);
    });
});