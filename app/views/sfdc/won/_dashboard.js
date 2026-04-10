/**
 * Dashboard Initialization Script
 *
 * Fetches dashboard data from /api/sfdc_won.php?action=get_dashboard_data
 * Renders 4 Chart.js stacked bar charts (ICT/Fixed x AOV/NPV)
 * Uses fiscal month order Apr -> Mar
 * Initializes charts only when Dashboard tab becomes visible
 * Resizes charts when returning to Dashboard tab
 */

(function (window, document) {
    'use strict';

    const DashboardConfig = {
        endpoint: '../api/sfdc_won.php?action=get_dashboard_data',
        charts: {},
        currentData: null,
        initialized: false,
        loaded: false,
        loading: false,
        teamColors: {
            'Team A': '#0d6efd',
            'Team B': '#198754',
            'Team C': '#ffc107',
            'Team D': '#dc3545',
            'Team E': '#0dcaf0',
            'Team F': '#6f42c1',
            'Team G': '#fd7e14',
            'Team H': '#20c997'
        },
        fiscalMonthLabels: ['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar']
    };

    function getTeamColor(team, index) {
        const palette = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#0dcaf0', '#6f42c1', '#fd7e14', '#20c997'];
        return DashboardConfig.teamColors[team] || palette[index % palette.length];
    }

    function formatCurrency(value) {
        const num = Number(value) || 0;
        const absVal = Math.abs(num);

        if (absVal >= 1000000) return '€' + (num / 1000000).toFixed(1) + 'M';
        if (absVal >= 1000) return '€' + (num / 1000).toFixed(0) + 'K';
        return '€' + num.toFixed(0);
    }

    function getCurrentFiscalYear() {
        const now = new Date();
        const month = now.getMonth() + 1;
        const year = now.getFullYear();
        return month >= 4 ? year + 1 : year;
    }

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
            if (fy === currentYear) option.selected = true;
            dropdown.appendChild(option);
        }
    }

    function getMonthLabels() {
        return DashboardConfig.fiscalMonthLabels.slice();
    }

    function toFiscalMonthOrder(values) {
        const calendar = Array.isArray(values) ? values : [];
        const padded = Array.from({ length: 12 }, function (_, i) {
            return Number(calendar[i] || 0);
        });

        return [
            padded[3],
            padded[4],
            padded[5],
            padded[6],
            padded[7],
            padded[8],
            padded[9],
            padded[10],
            padded[11],
            padded[0],
            padded[1],
            padded[2]
        ];
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function updateKpiDisplays(data) {
        if (!data || !data.data) return;

        const ictKpi = (data.data.ICT && data.data.ICT.kpi) ? data.data.ICT.kpi : {};
        const fixedKpi = (data.data.Fixed && data.data.Fixed.kpi) ? data.data.Fixed.kpi : {};

        setText('kpiIctAovTotal', formatCurrency(ictKpi.total_aov || 0));
        setText('kpiIctAovAvg', formatCurrency(ictKpi.avg_aov || 0));
        setText('kpiIctAovDeals', ictKpi.deal_count || 0);

        setText('kpiIctNpvTotal', formatCurrency(ictKpi.total_npv || 0));
        setText('kpiIctNpvAvg', formatCurrency(ictKpi.avg_npv || 0));
        setText('kpiIctNpvDeals', ictKpi.deal_count || 0);

        setText('kpiFixedAovTotal', formatCurrency(fixedKpi.total_aov || 0));
        setText('kpiFixedAovAvg', formatCurrency(fixedKpi.avg_aov || 0));
        setText('kpiFixedAovDeals', fixedKpi.deal_count || 0);

        setText('kpiFixedNpvTotal', formatCurrency(fixedKpi.total_npv || 0));
        setText('kpiFixedNpvAvg', formatCurrency(fixedKpi.avg_npv || 0));
        setText('kpiFixedNpvDeals', fixedKpi.deal_count || 0);
    }

    function buildChartDatasets(data, type, metric) {
        const metricKey = metric === 'aov' ? 'aov' : 'npv';
        const datasets = [];
        const teams = Array.isArray(data.teams) ? data.teams : [];

        teams.forEach(function (team, index) {
            const values =
                data.data &&
                    data.data[type] &&
                    data.data[type][metricKey] &&
                    data.data[type][metricKey][team]
                    ? data.data[type][metricKey][team]
                    : [];

            datasets.push({
                label: team,
                data: toFiscalMonthOrder(values),
                backgroundColor: getTeamColor(team, index),
                borderColor: 'rgba(0,0,0,0.12)',
                borderWidth: 0.5,
                stack: 'total'
            });
        });

        return datasets;
    }

    function showState(state, errorMessage) {
        const loadingEl = document.getElementById('dashboardLoading');
        const contentEl = document.getElementById('dashboardContent');
        const errorEl = document.getElementById('dashboardError');
        const errorMsgEl = document.getElementById('dashboardErrorMessage');

        if (loadingEl) loadingEl.style.display = state === 'loading' ? 'block' : 'none';
        if (contentEl) contentEl.style.display = state === 'content' ? 'block' : 'none';
        if (errorEl) errorEl.style.display = state === 'error' ? 'block' : 'none';

        if (state === 'error' && errorMsgEl) {
            errorMsgEl.textContent = errorMessage || 'Unknown error';
        }
    }

    function destroyAllCharts() {
        Object.keys(DashboardConfig.charts).forEach(function (canvasId) {
            if (DashboardConfig.charts[canvasId]) {
                DashboardConfig.charts[canvasId].destroy();
            }
        });
        DashboardConfig.charts = {};
    }

    function canRenderCanvas(canvas) {
        if (!canvas || !canvas.parentElement) return false;
        const rect = canvas.parentElement.getBoundingClientRect();
        return rect.width > 0 && rect.height > 0;
    }

    function renderChart(canvasId, data, type, metric) {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js is not loaded');
            return;
        }

        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            console.error('Canvas not found:', canvasId);
            return;
        }

        if (!canRenderCanvas(canvas)) {
            console.warn('Canvas container has no size yet:', canvasId);
            return;
        }

        if (DashboardConfig.charts[canvasId]) {
            DashboardConfig.charts[canvasId].destroy();
        }

        const ctx = canvas.getContext('2d');
        const datasets = buildChartDatasets(data, type, metric);

        DashboardConfig.charts[canvasId] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: getMonthLabels(),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    x: {
                        stacked: true,
                        ticks: {
                            font: { size: 11 }
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
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
                        labels: {
                            font: { size: 11 },
                            usePointStyle: true,
                            padding: 15
                        }
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
    }

    function renderAllCharts(data) {
        destroyAllCharts();
        renderChart('chartIctAov', data, 'ICT', 'aov');
        renderChart('chartIctNpv', data, 'ICT', 'npv');
        renderChart('chartFixedAov', data, 'Fixed', 'aov');
        renderChart('chartFixedNpv', data, 'Fixed', 'npv');
    }

    function resizeAllCharts() {
        Object.keys(DashboardConfig.charts).forEach(function (canvasId) {
            const chart = DashboardConfig.charts[canvasId];
            if (chart) {
                chart.resize();
                chart.update('none');
            }
        });
    }

    function isDashboardTabVisible() {
        const tab = document.getElementById('won-dashboard-tab');
        return !!(tab && tab.classList.contains('active') && tab.classList.contains('show'));
    }

    function waitForVisibleContent(callback, attempts) {
        const remaining = typeof attempts === 'number' ? attempts : 20;
        const content = document.getElementById('dashboardContent');

        if (!content || remaining <= 0) {
            callback(false);
            return;
        }

        const rect = content.getBoundingClientRect();
        if (rect.width > 0 && rect.height > 0) {
            callback(true);
            return;
        }

        requestAnimationFrame(function () {
            waitForVisibleContent(callback, remaining - 1);
        });
    }

    function fetchDashboardData(fiscalYear) {
        if (DashboardConfig.loading) return;

        DashboardConfig.loading = true;
        showState('loading');

        const url = DashboardConfig.endpoint + '&fiscal_year=' + encodeURIComponent(fiscalYear);

        fetch(url, {
            method: 'GET',
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('API returned status ' + response.status);
                }
                return response.text();
            })
            .then(function (text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    throw new Error('API returned invalid JSON: ' + e.message);
                }
            })
            .then(function (result) {
                if (!result.success) {
                    throw new Error(result.error || 'API returned success=false');
                }

                DashboardConfig.currentData = result.data;
                DashboardConfig.loaded = true;

                updateKpiDisplays(result.data);
                showState('content');

                if (isDashboardTabVisible()) {
                    waitForVisibleContent(function (ready) {
                        if (!ready) {
                            showState('error', 'Dashboard content could not be measured.');
                            return;
                        }
                        renderAllCharts(result.data);
                        resizeAllCharts();
                    });
                }
            })
            .catch(function (error) {
                console.error('Dashboard fetch error:', error);
                showState('error', error.message);
            })
            .finally(function () {
                DashboardConfig.loading = false;
            });
    }

    function onDashboardShown() {
        if (!DashboardConfig.loaded) {
            const fyDropdown = document.getElementById('dashboardFiscalYear');
            const fiscalYear = fyDropdown ? fyDropdown.value : getCurrentFiscalYear();
            fetchDashboardData(fiscalYear);
            return;
        }

        if (DashboardConfig.currentData) {
            showState('content');
            waitForVisibleContent(function (ready) {
                if (!ready) return;
                renderAllCharts(DashboardConfig.currentData);
                resizeAllCharts();
            });
        }
    }

    function bindEvents() {
        const yearDropdown = document.getElementById('dashboardFiscalYear');
        if (yearDropdown) {
            yearDropdown.addEventListener('change', function () {
                fetchDashboardData(this.value);
            });
        }

        const refreshBtn = document.getElementById('dashboardRefresh');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function () {
                const fyDropdown = document.getElementById('dashboardFiscalYear');
                const fiscalYear = fyDropdown ? fyDropdown.value : getCurrentFiscalYear();
                fetchDashboardData(fiscalYear);
            });
        }

        document.addEventListener('sfdcTabChanged', function (event) {
            if (!event.detail || event.detail.tab !== 'dashboard') return;
            onDashboardShown();
        });

        window.addEventListener('resize', function () {
            if (!isDashboardTabVisible()) return;
            setTimeout(resizeAllCharts, 100);
        });
    }

    function initDashboard() {
        if (DashboardConfig.initialized) return;

        const dashboardTab = document.getElementById('won-dashboard-tab');
        if (!dashboardTab) return;

        populateFiscalYears(getCurrentFiscalYear());
        bindEvents();
        DashboardConfig.initialized = true;

        if (isDashboardTabVisible()) {
            onDashboardShown();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        initDashboard();
    }

})(window, document);