/**
 * Dashboard Initialization Script
 * 
 * Fetches dashboard data from /api/sfdc_won.php?action=get_dashboard_data
 * Renders 4 Chart.js stacked bar charts (ICT/Fixed x AOV/NPV)
 * Manages fiscal year selection and chart redraw
 * 
 * Standalone: no dependencies on table filters or existing chart code
 */

(function (window, document) {
    'use strict';

    const DashboardConfig = {
        endpoint: '../api/sfdc_won.php?action=get_dashboard_data',
        charts: {},
        currentData: null,

        // Color palette for teams (adjust as needed)
        teamColors: {
            'Team A': '#0d6efd',
            'Team B': '#198754',
            'Team C': '#ffc107',
            'Team D': '#dc3545',
            'Team E': '#0dcaf0',
            'Team F': '#6f42c1',
            'Team G': '#fd7e14',
            'Team H': '#20c997'
        }
    };

    /**
     * Get color for team (cycling through palette)
     */
    function getTeamColor(team, index) {
        return DashboardConfig.teamColors[team] ||
            ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1', '#fd7e14', '#20c997'][index % 8];
    }

    /**
     * Format number as currency (€)
     */
    function formatCurrency(value) {
        const absVal = Math.abs(value);
        let formatted;

        if (absVal >= 1000000) {
            formatted = (value / 1000000).toFixed(1) + 'M';
        } else if (absVal >= 1000) {
            formatted = (value / 1000).toFixed(0) + 'K';
        } else {
            formatted = value.toFixed(0);
        }

        return '€' + formatted;
    }

    /**
     * Populate fiscal year dropdown with range (current year ± 3 years)
     */
    function populateFiscalYears(currentYear) {
        const dropdown = document.getElementById('dashboardFiscalYear');
        if (!dropdown) return;

        const startYear = currentYear - 3;
        const endYear = currentYear + 1;

        dropdown.innerHTML = '';
        for (let fy = endYear; fy >= startYear; fy--) {
            const option = document.createElement('option');
            option.value = fy;
            option.textContent = 'FY' + fy + ' (Apr ' + (fy - 1) + ' – Mar ' + fy + ')';
            if (fy === currentYear) {
                option.selected = true;
            }
            dropdown.appendChild(option);
        }
    }

    /**
     * Update KPI displays for all 4 combinations
     * data.data = { 'ICT': { kpi: {...}, aov: {...}, npv: {...} }, 'Fixed': {...} }
     */
    function updateKpiDisplays(data) {
        console.log('updateKpiDisplays called with data:', data);

        // Update ICT AOV & NPV (share same KPI data)
        const ictKpi = data.data['ICT'].kpi;
        document.getElementById('kpiIctAovTotal').textContent = formatCurrency(ictKpi.total_aov);
        document.getElementById('kpiIctAovAvg').textContent = formatCurrency(ictKpi.avg_aov);
        document.getElementById('kpiIctAovDeals').textContent = ictKpi.deal_count;

        document.getElementById('kpiIctNpvTotal').textContent = formatCurrency(ictKpi.total_npv);
        document.getElementById('kpiIctNpvAvg').textContent = formatCurrency(ictKpi.avg_npv);
        document.getElementById('kpiIctNpvDeals').textContent = ictKpi.deal_count;

        // Update Fixed AOV & NPV (share same KPI data)
        const fixedKpi = data.data['Fixed'].kpi;
        document.getElementById('kpiFixedAovTotal').textContent = formatCurrency(fixedKpi.total_aov);
        document.getElementById('kpiFixedAovAvg').textContent = formatCurrency(fixedKpi.avg_aov);
        document.getElementById('kpiFixedAovDeals').textContent = fixedKpi.deal_count;

        document.getElementById('kpiFixedNpvTotal').textContent = formatCurrency(fixedKpi.total_npv);
        document.getElementById('kpiFixedNpvAvg').textContent = formatCurrency(fixedKpi.avg_npv);
        document.getElementById('kpiFixedNpvDeals').textContent = fixedKpi.deal_count;

        console.log('KPI displays updated');
    }

    /**
     * Build Chart.js dataset for stacked bar chart
     */
    function buildChartDatasets(data, type, metric) {
        const metricKey = metric === 'aov' ? 'aov' : 'npv';
        const datasets = [];

        data.teams.forEach((team, index) => {
            const values = data.data[type][metricKey][team] || [];

            datasets.push({
                label: team,
                data: values,
                backgroundColor: getTeamColor(team, index),
                borderColor: 'rgba(0,0,0,0.1)',
                borderWidth: 0.5
            });
        });

        return datasets;
    }

    /**
     * Month labels (Jan – Dec)
     */
    function getMonthLabels() {
        return ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    }

    /**
     * Render a stacked bar chart
     */
    function renderChart(canvasId, data, type, metric) {
        console.log('renderChart called for:', canvasId, type, metric);

        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded!');
            return;
        }

        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error('Canvas element not found:', canvasId);
            return;
        }

        // Destroy existing chart if it exists
        if (DashboardConfig.charts[canvasId]) {
            DashboardConfig.charts[canvasId].destroy();
        }

        // Get parent container size
        const container = canvas.parentElement;
        let width = container.clientWidth;
        let height = container.clientHeight;

        console.log('Direct parent size:', width, 'x', height);

        if (width === 0 || height === 0) {
            const grandparent = container.parentElement;
            width = grandparent.clientWidth || 1200;
            height = grandparent.clientHeight || 300;
            console.log('Using grandparent size:', width, 'x', height);
        }

        if (width === 0) width = 600;
        if (height === 0) height = 300;

        console.log('Final canvas size will be:', width, 'x', height);

        // Set canvas native resolution
        canvas.width = width;
        canvas.height = height;

        // CRITICAL: Remove CSS sizing so native size is used
        canvas.style.width = '';
        canvas.style.height = '';
        canvas.style.maxWidth = '100%';
        canvas.style.display = 'block';

        const ctx = canvas.getContext('2d');
        const datasets = buildChartDatasets(data, type, metric);
        const monthLabels = getMonthLabels();

        console.log('Building chart', canvasId, 'with', datasets.length, 'datasets');

        try {
            DashboardConfig.charts[canvasId] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: datasets
                },
                options: {
                    responsive: false,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            ticks: { font: { size: 11 } }
                        },
                        y: {
                            stacked: true,
                            ticks: {
                                font: { size: 10 },
                                callback: function (value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: { font: { size: 11 }, usePointStyle: true, padding: 15 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return context.dataset.label + ': ' + formatCurrency(context.parsed.y || 0);
                                }
                            }
                        }
                    }
                }
            });

            console.log('✅ Chart rendered:', canvasId, 'with size', canvas.width, 'x', canvas.height);
        } catch (e) {
            console.error('❌ Chart creation error for', canvasId, ':', e.message);
        }
    }

    /**
     * Render all 4 charts
     */
    function renderAllCharts(data) {
        console.log('renderAllCharts called');
        renderChart('chartIctAov', data, 'ICT', 'aov');
        renderChart('chartIctNpv', data, 'ICT', 'npv');
        renderChart('chartFixedAov', data, 'Fixed', 'aov');
        renderChart('chartFixedNpv', data, 'Fixed', 'npv');
        console.log('All charts rendered');
        // Add this after ALL charts are rendered, in renderAllCharts function
        setTimeout(function () {
            console.log('=== CHART DATA DEBUG ===');
            Object.keys(DashboardConfig.charts).forEach(canvasId => {
                const chart = DashboardConfig.charts[canvasId];
                if (chart) {
                    console.log('Chart:', canvasId);
                    console.log('  Datasets:', chart.data.datasets.length);
                    chart.data.datasets.forEach((ds, idx) => {
                        const values = ds.data.slice(0, 3);
                        console.log(`  Dataset ${idx} (${ds.label}):`, values, 'backgroundColor:', ds.backgroundColor);
                    });
                }
            });

            // Force Chart.js to redraw
            Object.values(DashboardConfig.charts).forEach(chart => {
                if (chart) {
                    console.log('Forcing chart redraw...');
                    chart.resize();
                    chart.update('none');
                }
            });
        }, 500);
    }

    /**
     * Fetch dashboard data from API
     */
    function fetchDashboardData(fiscalYear) {
        const loadingEl = document.getElementById('dashboardLoading');
        const contentEl = document.getElementById('dashboardContent');
        const errorEl = document.getElementById('dashboardError');

        // Show content
        if (loadingEl) loadingEl.style.display = 'none';
        if (contentEl) {
            contentEl.style.display = 'block';
            contentEl.style.width = '100%';
            contentEl.style.minHeight = '400px';
            console.log('dashboardContent shown, size:', contentEl.offsetWidth, 'x', contentEl.offsetHeight);
        }
        if (errorEl) errorEl.style.display = 'none';

        
        if (loadingEl) loadingEl.style.display = 'block';
        if (contentEl) contentEl.style.display = 'none';
        if (errorEl) errorEl.style.display = 'none';

        const url = DashboardConfig.endpoint + '&fiscal_year=' + encodeURIComponent(fiscalYear);

        console.log('Fetching dashboard data from:', url);

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin'
        })
            .then(response => {
                console.log('API response status:', response.status);

                if (!response.ok) {
                    throw new Error('API returned status ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                // DEBUG: Log raw response to see what API returns
                console.log('Raw API response (first 300 chars):', text.substring(0, 300));

                try {
                    const result = JSON.parse(text);
                    console.log('Parsed JSON successfully:', result);
                    return result;
                } catch (e) {
                    console.error('JSON parse error:', e.message);
                    console.error('Full response text:', text);
                    throw new Error('API returned invalid JSON: ' + e.message);
                }
            })
            .then(result => {
                if (!result.success) {
                    throw new Error(result.error || 'API returned success=false');
                }

                console.log('Dashboard data loaded successfully');
                DashboardConfig.currentData = result.data;

                // Update displays
                updateKpiDisplays(result.data);
                renderAllCharts(result.data);

                // Show content
                if (loadingEl) loadingEl.style.display = 'none';
                if (contentEl) contentEl.style.display = 'block';
                if (errorEl) errorEl.style.display = 'none';
            })
            .catch(error => {
                console.error('Dashboard fetch error:', error);

                if (loadingEl) loadingEl.style.display = 'none';
                if (contentEl) contentEl.style.display = 'none';
                if (errorEl) {
                    errorEl.style.display = 'block';
                    document.getElementById('dashboardErrorMessage').textContent = error.message;
                }
            });
    }

    /**
     * Initialize dashboard on page load
     */
    function initDashboard() {
        // Only init if we're on the dashboard tab
        const dashboardTab = document.getElementById('won-dashboard-tab');
        if (!dashboardTab) {
            console.log('Dashboard tab not found, skipping init');
            return;
        }

        console.log('Initializing dashboard...');

        // Populate fiscal year dropdown with current fiscal year
        const currentFy = getCurrentFiscalYear();
        populateFiscalYears(currentFy);

        // Initial fetch
        fetchDashboardData(currentFy);

        // Event listeners
        const yearDropdown = document.getElementById('dashboardFiscalYear');
        if (yearDropdown) {
            yearDropdown.addEventListener('change', function () {
                console.log('Fiscal year changed to:', this.value);
                fetchDashboardData(this.value);
            });
        }

        const refreshBtn = document.getElementById('dashboardRefresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function () {
                const dropdown = document.getElementById('dashboardFiscalYear');
                console.log('Refresh clicked for fiscal year:', dropdown.value);
                fetchDashboardData(dropdown.value);
            });
        }
    }

    /**
     * Calculate current fiscal year (April 1 – March 31)
     */
    function getCurrentFiscalYear() {
        const now = new Date();
        const month = now.getMonth() + 1; // 1-12
        const year = now.getFullYear();

        // If month >= 4 (April), we're in FY of next calendar year
        const fy = month >= 4 ? year + 1 : year;
        console.log('Current fiscal year calculated:', fy, '(month:', month, 'year:', year + ')');
        return fy;
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        console.log('DOM loading, waiting for DOMContentLoaded...');
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        console.log('DOM already loaded, initializing dashboard...');
        initDashboard();
    }


    // DEBUG: Visual inspection
    setTimeout(function () {
        const canvas = document.getElementById('chartIctAov');
        if (canvas) {
            console.log('=== CHART DEBUG ===');
            console.log('Canvas element:', canvas);
            console.log('Canvas size:', canvas.width, 'x', canvas.height);
            console.log('Canvas display size:', canvas.offsetWidth, 'x', canvas.offsetHeight);
            console.log('Canvas visible?', canvas.offsetParent !== null);
            console.log('Canvas style:', canvas.getAttribute('style'));
            console.log('Parent element:', canvas.parentElement);
            console.log('Parent style:', canvas.parentElement.getAttribute('style'));
            console.log('Parent display:', window.getComputedStyle(canvas.parentElement).display);
            console.log('Parent visibility:', window.getComputedStyle(canvas.parentElement).visibility);
            console.log('dashboardContent display:', window.getComputedStyle(document.getElementById('dashboardContent')).display);

            // Try to force it visible
            canvas.style.display = 'block !important';
            canvas.style.border = '2px solid red';
            canvas.parentElement.style.display = 'block !important';
            canvas.parentElement.style.overflow = 'visible';
        }
    }, 1000);

})(window, document);