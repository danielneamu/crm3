(function (window, document) {
    'use strict';

    var MULTISELECT_OPTS_BASE = {
        buttonClass: 'btn btn-light',
        numberDisplayed: 100,
        allSelectedText: false,
        includeSelectAllOption: true,
        buttonWidth: '100%'
    };

    var DashboardConfig = {
        endpoint: '../api/sfdc_product_pipeline.php?action=get_dashboard_data',
        initialized: false,
        currentData: null,
        charts: {},
        filters: {
            fiscalYear: null,
            productFamilies: [],
            productNames: []
        },
        palette: [
            '#0d6efd',
            '#20c997',
            '#ffc107',
            '#dc3545',
            '#6f42c1',
            '#fd7e14',
            '#198754',
            '#0dcaf0',
            '#6c757d',
            '#d63384'
        ]
    };

    function getEl(id) {
        return document.getElementById(id);
    }

    function formatCurrency(value) {
        var num = Number(value || 0);
        return '€' + num.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
    }

    function formatNumber(value, decimals) {
        return Number(value || 0).toLocaleString('en-US', {
            minimumFractionDigits: decimals || 0,
            maximumFractionDigits: decimals || 0
        });
    }

    function getCurrentFiscalYear() {
        var now = new Date();
        var month = now.getMonth() + 1;
        var year = now.getFullYear();
        return month >= 4 ? year + 1 : year;
    }

    function initMultiselect(selectEl, nonSelectedText) {
        if (!selectEl || typeof $ === 'undefined' || !$.fn.multiselect) return;

        var $sel = $(selectEl);

        try { $sel.multiselect('destroy'); } catch (e) { }

        $sel.multiselect({
            buttonClass: 'btn btn-light form-select',
            buttonWidth: '100%',
            numberDisplayed: 100,
            allSelectedText: false,
            includeSelectAllOption: true,
            nonSelectedText: nonSelectedText || 'Select...',
            templates: {
                button:
                    '<button type="button" class="multiselect dropdown-toggle btn btn-light form-select" data-bs-toggle="dropdown" aria-expanded="false">' +
                    '<span class="multiselect-selected-text"></span>' +
                    '</button>'
            }
        });
    }

    function ensureArray(value) {
        return Array.isArray(value) ? value : [];
    }

    function getSelectedValues(selectEl) {
        if (!selectEl) return [];
        return Array.from(selectEl.selectedOptions || [])
            .map(function (option) { return option.value; })
            .filter(function (value) { return value !== ''; });
    }

    function setMultiSelectOptions(selectEl, options, selectedValues) {
        if (!selectEl) return;

        var selectedSet = new Set(selectedValues || []);
        selectEl.innerHTML = '';

        options.forEach(function (value) {
            var option = document.createElement('option');
            option.value = value;
            option.textContent = value;
            if (selectedSet.has(value)) {
                option.selected = true;
            }
            selectEl.appendChild(option);
        });

        if (typeof $ !== 'undefined' && $.fn.multiselect) {
            try { $(selectEl).multiselect('rebuild'); } catch (e) { }
        }
    }

    function showState(state, errorMessage) {
        var loadingEl = getEl('dashboardLoadingProduct');
        var contentEl = getEl('dashboardContentProduct');
        var errorEl = getEl('dashboardErrorProduct');
        var emptyEl = getEl('dashboardEmptyProduct');
        var errorMsgEl = getEl('dashboardErrorMessageProduct');

        if (loadingEl) loadingEl.style.display = state === 'loading' ? 'block' : 'none';
        if (contentEl) contentEl.style.display = state === 'content' ? 'block' : 'none';
        if (errorEl) errorEl.style.display = state === 'error' ? 'block' : 'none';
        if (emptyEl) emptyEl.style.display = state === 'empty' ? 'block' : 'none';

        if (state === 'error' && errorMsgEl) {
            errorMsgEl.textContent = errorMessage || 'Unknown error';
        }
    }

    function destroyChart(key) {
        if (DashboardConfig.charts[key]) {
            DashboardConfig.charts[key].destroy();
            DashboardConfig.charts[key] = null;
        }
    }

    function destroyAllCharts() {
        Object.keys(DashboardConfig.charts).forEach(destroyChart);
    }

    function getChartColor(index) {
        return DashboardConfig.palette[index % DashboardConfig.palette.length];
    }

    function loadDashboardFilters(payloadFilters) {
        var familySelect = getEl('dashboardProductFamilyProduct');
        var nameSelect = getEl('dashboardProductNameProduct');

        var families = ensureArray(payloadFilters.productFamilies);

        setMultiSelectOptions(familySelect, families, DashboardConfig.filters.productFamilies);

        var validFamilySet = new Set(families);
        DashboardConfig.filters.productFamilies = DashboardConfig.filters.productFamilies.filter(function (family) {
            return validFamilySet.has(family);
        });

        if (familySelect) {
            Array.from(familySelect.options).forEach(function (option) {
                option.selected = DashboardConfig.filters.productFamilies.includes(option.value);
            });
            if (typeof $ !== 'undefined' && $.fn.multiselect) {
                try { $(familySelect).multiselect('rebuild'); } catch (e) { }
            }
        }

        var nextNames = syncProductNameOptions(payloadFilters);
        setMultiSelectOptions(nameSelect, nextNames.availableNames, nextNames.selectedNames);
        DashboardConfig.filters.productNames = nextNames.selectedNames;
    }

    function syncProductNameOptions(payloadFilters) {
        var selectedFamilies = DashboardConfig.filters.productFamilies.slice();
        var allNames = ensureArray(payloadFilters.allProductNames);
        var namesByFamily = payloadFilters.productNamesByFamily || {};

        var availableNames = [];

        if (selectedFamilies.length === 0) {
            availableNames = allNames.slice();
        } else {
            var merged = new Set();
            selectedFamilies.forEach(function (family) {
                ensureArray(namesByFamily[family]).forEach(function (name) {
                    merged.add(name);
                });
            });
            availableNames = Array.from(merged).sort();
        }

        var validNameSet = new Set(availableNames);
        var selectedNames = DashboardConfig.filters.productNames.filter(function (name) {
            return validNameSet.has(name);
        });

        return {
            availableNames: availableNames,
            selectedNames: selectedNames
        };
    }

    function updateFilterStateFromInputs() {
        var fiscalYearEl = getEl('dashboardFiscalYearProduct');
        var familySelect = getEl('dashboardProductFamilyProduct');
        var nameSelect = getEl('dashboardProductNameProduct');

        DashboardConfig.filters.fiscalYear = fiscalYearEl ? fiscalYearEl.value : String(getCurrentFiscalYear());
        DashboardConfig.filters.productFamilies = getSelectedValues(familySelect);
        DashboardConfig.filters.productNames = getSelectedValues(nameSelect);
    }

    function buildUrl() {
        var params = new URLSearchParams();
        params.set('fiscal_year', DashboardConfig.filters.fiscalYear || String(getCurrentFiscalYear()));

        if (DashboardConfig.filters.productFamilies.length > 0) {
            params.set('product_families', DashboardConfig.filters.productFamilies.join(','));
        }

        if (DashboardConfig.filters.productNames.length > 0) {
            params.set('product_names', DashboardConfig.filters.productNames.join(','));
        }

        return DashboardConfig.endpoint + '&' + params.toString();
    }

    function fetchDashboardData() {
        updateFilterStateFromInputs();
        showState('loading');

        fetch(buildUrl(), {
            method: 'GET',
            credentials: 'same-origin'
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('API returned status ' + response.status);
                }
                return response.json();
            })
            .then(function (result) {
                if (!result.success) {
                    throw new Error(result.error || 'API returned success=false');
                }

                var payload = result.data || {};
                var filters = payload.filters || {};
                var cards = payload.cards || {};
                var charts = payload.charts || {};

                DashboardConfig.currentData = payload;

                loadDashboardFilters(filters);

                var hasAnyData =
                    Number(cards.oppCount || 0) > 0 ||
                    (charts.stage && ensureArray(charts.stage.labels).length > 0) ||
                    (charts.productFamilyMix && ensureArray(charts.productFamilyMix.labels).length > 0);

                if (!hasAnyData) {
                    destroyAllCharts();
                    showState('empty');
                    return;
                }

                renderCards(cards);
                renderStageChart(charts.stage || {});
                renderTeamChart(charts.team || {});
                renderAgeAovChart(charts.ageAov || {});
                renderProbabilityChart(charts.probability || {});
                renderProductFamilyMixChart(charts.productFamilyMix || {});
                renderCloseTimelineChart(charts.closeTimeline || {});
                renderMonthlyTeamFiscalChart(charts.monthlyTeamFiscal || {});

                showState('content');
            })
            .catch(function (error) {
                console.error('Dashboard fetch error:', error);
                destroyAllCharts();
                showState('error', error.message);
            });
    }

    function renderCards(cards) {
        var totalPipelineEl = getEl('kpiTotalPipelineAovProduct');
        var weightedEl = getEl('kpiWeightedPipelineProduct');
        var avgAgeEl = getEl('kpiAvgAgeProduct');
        var oppCountEl = getEl('kpiOppCountProduct');

        if (totalPipelineEl) totalPipelineEl.textContent = formatCurrency(cards.totalPipelineAov || 0);
        if (weightedEl) weightedEl.textContent = formatCurrency(cards.weightedPipeline || 0);
        if (avgAgeEl) avgAgeEl.textContent = formatNumber(cards.avgAge || 0, 1);
        if (oppCountEl) oppCountEl.textContent = formatNumber(cards.oppCount || 0, 0);
    }

    function renderStageChart(chartData) {
        destroyChart('stage');

        var canvas = getEl('chartPipelineByStageProduct');
        if (!canvas) return;

        DashboardConfig.charts.stage = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: ensureArray(chartData.labels),
                datasets: [{
                    label: 'Pipeline AOV',
                    data: ensureArray(chartData.values),
                    backgroundColor: ensureArray(chartData.labels).map(function (_, index) {
                        return getChartColor(index);
                    })
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderTeamChart(chartData) {
        destroyChart('team');

        var canvas = getEl('chartPipelineByTeamProduct');
        if (!canvas) return;

        var datasets = ensureArray(chartData.datasets).map(function (dataset, index) {
            return {
                label: dataset.label,
                data: ensureArray(dataset.data),
                backgroundColor: getChartColor(index),
                stack: 'team-stage'
            };
        });

        DashboardConfig.charts.team = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: ensureArray(chartData.labels),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: { stacked: true },
                    y: {
                        stacked: true,
                        ticks: {
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderAgeAovChart(chartData) {
        destroyChart('ageAov');

        var canvas = getEl('chartAgeVsAovProduct');
        if (!canvas) return;

        var datasets = ensureArray(chartData.datasets).map(function (dataset, index) {
            return {
                label: dataset.label,
                data: ensureArray(dataset.data),
                backgroundColor: getChartColor(index),
                pointRadius: 5
            };
        });

        DashboardConfig.charts.ageAov = new Chart(canvas, {
            type: 'scatter',
            data: { datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                var point = context.raw || {};
                                return [
                                    'Age: ' + formatNumber(point.x || 0, 0),
                                    'AOV: ' + formatCurrency(point.y || 0),
                                    point.oppRef ? ('Opp Ref: ' + point.oppRef) : '',
                                    point.opportunityName ? ('Opp: ' + point.opportunityName) : ''
                                ].filter(Boolean);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Age'
                        }
                    },
                    y: {
                        type: 'logarithmic',
                        title: {
                            display: true,
                            text: 'AOV'
                        },
                        ticks: {
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderProbabilityChart(chartData) {
        destroyChart('probability');

        var canvas = getEl('chartProbabilityDistributionProduct');
        if (!canvas) return;

        var labels = ensureArray(chartData.labels);
        var countValues = Array.isArray(chartData.countValues)
            ? chartData.countValues
            : Object.values(chartData.countValues || {});
        var aovValues = Array.isArray(chartData.aovValues)
            ? chartData.aovValues
            : Object.values(chartData.aovValues || {});

        DashboardConfig.charts.probability = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Opp Count',
                        data: countValues,
                        backgroundColor: '#0d6efd',
                        borderColor: '#0d6efd',
                        yAxisID: 'yCount',
                        order: 1
                    },
                    {
                        label: 'Pipeline AOV',
                        data: aovValues,
                        backgroundColor: '#20c997',
                        borderColor: '#20c997',
                        yAxisID: 'yAov',
                        order: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                if (context.dataset.yAxisID === 'yCount') {
                                    return context.dataset.label + ': ' + formatNumber(context.raw, 0);
                                }
                                return context.dataset.label + ': ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    yCount: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Opp Count'
                        },
                        ticks: {
                            precision: 0,
                            callback: function (value) {
                                return formatNumber(value, 0);
                            }
                        }
                    },
                    yAov: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Pipeline AOV'
                        },
                        ticks: {
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderProductFamilyMixChart(chartData) {
        destroyChart('productFamilyMix');

        var canvas = getEl('chartProductFamilyMixProduct');
        if (!canvas) return;

        DashboardConfig.charts.productFamilyMix = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: ensureArray(chartData.labels),
                datasets: [{
                    label: 'Cleaned ARROV',
                    data: ensureArray(chartData.values),
                    backgroundColor: ensureArray(chartData.labels).map(function (_, index) {
                        return getChartColor(index);
                    })
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderCloseTimelineChart(chartData) {
        destroyChart('closeTimeline');

        var canvas = getEl('chartCloseTimelineProduct');
        if (!canvas) return;

        var points = ensureArray(chartData.points);
        var labels = points.map(function (point) {
            return point.x;
        });

        DashboardConfig.charts.closeTimeline = new Chart(canvas, {
            type: 'bubble',
            data: {
                datasets: [{
                    label: 'Close Date Timeline',
                    data: points.map(function (point, index) {
                        return {
                            x: index + 1,
                            y: point.y || 0,
                            r: point.r || 6,
                            closeDate: point.x,
                            oppRef: point.oppRef || '',
                            opportunityName: point.opportunityName || '',
                            stage: point.stage || ''
                        };
                    }),
                    backgroundColor: '#6f42c1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                var point = context.raw || {};
                                return [
                                    'Close Date: ' + (point.closeDate || ''),
                                    'AOV: ' + formatCurrency(point.y || 0),
                                    point.stage ? ('Stage: ' + point.stage) : '',
                                    point.oppRef ? ('Opp Ref: ' + point.oppRef) : '',
                                    point.opportunityName ? ('Opp: ' + point.opportunityName) : ''
                                ].filter(Boolean);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'linear',
                        ticks: {
                            callback: function (value) {
                                var index = Math.round(value) - 1;
                                return labels[index] || '';
                            }
                        }
                    },
                    y: {
                        type: 'logarithmic',
                        ticks: {
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function renderMonthlyTeamFiscalChart(chartData) {
        destroyChart('monthlyTeamFiscal');

        var canvas = getEl('chartMonthlyTeamFiscalProduct');
        if (!canvas) return;

        var datasets = ensureArray(chartData.datasets).map(function (dataset, index) {
            return {
                label: dataset.label,
                data: ensureArray(dataset.data),
                backgroundColor: getChartColor(index),
                stack: 'monthly-team'
            };
        });

        DashboardConfig.charts.monthlyTeamFiscal = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: ensureArray(chartData.labels),
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ' + formatCurrency(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    x: { stacked: true },
                    y: {
                        stacked: true,
                        ticks: {
                            callback: function (value) {
                                return formatCurrency(value);
                            }
                        }
                    }
                }
            }
        });
    }

    function handleFamilyChange() {
        var familySelect = getEl('dashboardProductFamilyProduct');
        DashboardConfig.filters.productFamilies = getSelectedValues(familySelect);

        if (DashboardConfig.currentData && DashboardConfig.currentData.filters) {
            var nameData = syncProductNameOptions(DashboardConfig.currentData.filters);
            setMultiSelectOptions(
                getEl('dashboardProductNameProduct'),
                nameData.availableNames,
                nameData.selectedNames
            );
            DashboardConfig.filters.productNames = nameData.selectedNames;
        }
    }

    function handleReset() {
        var fiscalYearEl = getEl('dashboardFiscalYearProduct');
        var familySelect = getEl('dashboardProductFamilyProduct');
        var nameSelect = getEl('dashboardProductNameProduct');

        DashboardConfig.filters = {
            fiscalYear: fiscalYearEl ? fiscalYearEl.value : String(getCurrentFiscalYear()),
            productFamilies: [],
            productNames: []
        };

        [familySelect, nameSelect].forEach(function (sel) {
            if (!sel) return;
            Array.from(sel.options).forEach(function (option) {
                option.selected = false;
            });
            if (typeof $ !== 'undefined' && $.fn.multiselect) {
                try { $(sel).multiselect('rebuild'); } catch (e) { }
            }
        });

        fetchDashboardData();
    }

    function initDashboard() {
        if (DashboardConfig.initialized) return;

        var dashboardTab = document.getElementById('product-pipeline-dashboard-tab');
        if (!dashboardTab) return;

        var yearDropdown = getEl('dashboardFiscalYearProduct');
        var refreshBtn = getEl('dashboardRefreshProduct');
        var resetBtn = getEl('dashboardResetProduct');
        var familySelect = getEl('dashboardProductFamilyProduct');
        var nameSelect = getEl('dashboardProductNameProduct');

        initMultiselect(familySelect, 'All families...');
        initMultiselect(nameSelect, 'All products...');

        if (yearDropdown) {
            var currentFy = String(getCurrentFiscalYear());
            var existingOption = Array.from(yearDropdown.options).find(function (option) {
                return option.value === currentFy;
            });

            if (existingOption) {
                yearDropdown.value = currentFy;
            }

            DashboardConfig.filters.fiscalYear = yearDropdown.value;

            yearDropdown.addEventListener('change', function () {
                DashboardConfig.filters.fiscalYear = this.value;
                DashboardConfig.filters.productFamilies = [];
                DashboardConfig.filters.productNames = [];
                fetchDashboardData();
            });
        }

        if (familySelect) {
            familySelect.addEventListener('change', handleFamilyChange);
        }

        if (refreshBtn) {
            refreshBtn.addEventListener('click', function () {
                fetchDashboardData();
            });
        }

        if (resetBtn) {
            resetBtn.addEventListener('click', handleReset);
        }

        document.addEventListener('sfdcTabChanged', function (event) {
            if (!event.detail || event.detail.tab !== 'dashboard') return;
            fetchDashboardData();
        });

        DashboardConfig.initialized = true;

        if (dashboardTab.classList.contains('active')) {
            fetchDashboardData();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDashboard);
    } else {
        initDashboard();
    }
})(window, document);