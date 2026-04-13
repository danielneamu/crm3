<?php

/** Dashboard View for Product Pipeline - wireframe with containers
 *  Future: Will be populated with Chart.js stacked bar charts
 */
?>

<style>
    .sfdc-dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .sfdc-dashboard-panel {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        padding: 1rem;
        background: #fff;
        min-width: 0;
    }

    .sfdc-dashboard-chart-wrap {
        position: relative;
        width: 100%;
        height: 300px;
        min-width: 0;
    }

    .sfdc-dashboard-chart-wrap canvas {
        display: block;
        width: 100% !important;
        height: 100% !important;
    }

    .sfdc-dashboard-kpis {
        font-size: 12px;
        background: #f8f9fa;
        padding: 10px;
        margin: 0 0 15px 0;
        border-radius: 0.375rem;
    }

    .sfdc-dashboard-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 300px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 0.375rem;
        color: #999;
        font-style: italic;
        font-size: 14px;
    }

    @media (max-width: 991.98px) {
        .sfdc-dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div style="margin-bottom: 20px;">
            <label for="dashboardFiscalYearProduct" class="form-label fw-semibold">Fiscal Year</label>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <select id="dashboardFiscalYearProduct" class="form-select" style="max-width: 300px;">
                    <option value="2026">FY2026 (Apr 2025 – Mar 2026)</option>
                    <option value="2025">FY2025 (Apr 2024 – Mar 2025)</option>
                    <option value="2024">FY2024 (Apr 2023 – Mar 2024)</option>
                </select>
                <button id="dashboardRefreshProduct" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                </button>
            </div>
        </div>

        <div id="dashboardLoadingProduct" style="display: none; text-align: center; padding: 40px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading dashboard data...</p>
        </div>

        <div id="dashboardContentProduct" style="display: none; width: 100%;">
            <div class="sfdc-dashboard-grid">

                <div class="sfdc-dashboard-panel">
                    <h5>All Products — ARROV</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiAllArrovTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiAllArrovAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiAllArrovDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-placeholder">
                        Chart placeholder<br />
                        <small>(stacked bar chart — Team by Month)</small>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>By Product Family</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Families:</strong> <span id="kpiProductFamilyCount">—</span> |
                        <strong>Total:</strong> <span id="kpiProductFamilyTotal">—</span>
                    </p>
                    <div class="sfdc-dashboard-placeholder">
                        Chart placeholder<br />
                        <small>(breakdown by Product Family)</small>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>By Team</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Teams:</strong> <span id="kpiTeamCount">—</span> |
                        <strong>Total:</strong> <span id="kpiTeamTotal">—</span>
                    </p>
                    <div class="sfdc-dashboard-placeholder">
                        Chart placeholder<br />
                        <small>(breakdown by Team)</small>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>By Stage</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Stages:</strong> <span id="kpiStageCount">—</span> |
                        <strong>Total:</strong> <span id="kpiStageTotal">—</span>
                    </p>
                    <div class="sfdc-dashboard-placeholder">
                        Chart placeholder<br />
                        <small>(breakdown by Stage)</small>
                    </div>
                </div>

            </div>

            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #dee2e6;">
                <h5>Dashboard Notes</h5>
                <p class="text-muted" style="font-size: 12px; margin: 0;">
                    Dashboard is currently in wireframe mode. Charts and KPI calculations will be populated
                    in a follow-up phase using Chart.js with ARROV aggregation by Product Family, Team, and Month
                    within the selected fiscal year (April – March).
                </p>
            </div>
        </div>

        <div id="dashboardErrorProduct" style="display: none; color: #842029; background-color: #f8d7da; border: 1px solid #f5c2c7; padding: 12px; border-radius: 4px;">
            <strong>Error:</strong> <span id="dashboardErrorMessageProduct">Failed to load dashboard data.</span>
        </div>
    </div>
</div>

<script>
    // Dashboard wireframe - basic structure
    (function(window, document) {
        'use strict';

        const DashboardConfig = {
            endpoint: '../api/sfdc_product_pipeline.php?action=get_dashboard_data',
            currentData: null,
            initialized: false
        };

        function getCurrentFiscalYear() {
            const now = new Date();
            const month = now.getMonth() + 1;
            const year = now.getFullYear();
            return month >= 4 ? year + 1 : year;
        }

        function showState(state, errorMessage) {
            const loadingEl = document.getElementById('dashboardLoadingProduct');
            const contentEl = document.getElementById('dashboardContentProduct');
            const errorEl = document.getElementById('dashboardErrorProduct');
            const errorMsgEl = document.getElementById('dashboardErrorMessageProduct');

            if (loadingEl) loadingEl.style.display = state === 'loading' ? 'block' : 'none';
            if (contentEl) contentEl.style.display = state === 'content' ? 'block' : 'none';
            if (errorEl) errorEl.style.display = state === 'error' ? 'block' : 'none';

            if (state === 'error' && errorMsgEl) {
                errorMsgEl.textContent = errorMessage || 'Unknown error';
            }
        }

        function updateKpiDisplays(data) {
            if (!data || !data.data) {
                console.log('No data to display on dashboard.');
                return;
            }

            const allKpi = (data.data.All && data.data.All.kpi) ? data.data.All.kpi : {};

            // All products metrics
            document.getElementById('kpiAllArrovTotal').textContent =
                allKpi.total_arrov ? '€' + Number(allKpi.total_arrov).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) : '—';
            document.getElementById('kpiAllArrovAvg').textContent =
                allKpi.avg_arrov ? '€' + Number(allKpi.avg_arrov).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) : '—';
            document.getElementById('kpiAllArrovDeals').textContent = allKpi.deal_count || '—';

            // Family and team counts
            const families = (data.families || []).length;
            const teams = (data.teams || []).length;
            document.getElementById('kpiProductFamilyCount').textContent = families || '—';
            document.getElementById('kpiTeamCount').textContent = teams || '—';

            // Totals (same for all breakdowns in wireframe)
            document.getElementById('kpiProductFamilyTotal').textContent =
                allKpi.total_arrov ? '€' + Number(allKpi.total_arrov).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) : '—';
            document.getElementById('kpiTeamTotal').textContent =
                allKpi.total_arrov ? '€' + Number(allKpi.total_arrov).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) : '—';
            document.getElementById('kpiStageTotal').textContent =
                allKpi.total_arrov ? '€' + Number(allKpi.total_arrov).toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }) : '—';

            document.getElementById('kpiStageCount').textContent = '—'; // TODO: calculate unique stages
        }

        function fetchDashboardData(fiscalYear) {
            showState('loading');

            const url = DashboardConfig.endpoint + '&fiscal_year=' + encodeURIComponent(fiscalYear);

            fetch(url, {
                    method: 'GET',
                    credentials: 'same-origin'
                })
                .then(function(response) {
                    if (!response.ok) {
                        throw new Error('API returned status ' + response.status);
                    }
                    return response.json();
                })
                .then(function(result) {
                    if (!result.success) {
                        throw new Error(result.error || 'API returned success=false');
                    }

                    DashboardConfig.currentData = result.data;
                    updateKpiDisplays(result.data);
                    showState('content');
                })
                .catch(function(error) {
                    console.error('Dashboard fetch error:', error);
                    showState('error', error.message);
                });
        }

        function initDashboard() {
            if (DashboardConfig.initialized) return;

            const dashboardTab = document.getElementById('product-pipeline-dashboard-tab');
            if (!dashboardTab) return;

            const yearDropdown = document.getElementById('dashboardFiscalYearProduct');
            const refreshBtn = document.getElementById('dashboardRefreshProduct');

            if (yearDropdown) {
                yearDropdown.addEventListener('change', function() {
                    fetchDashboardData(this.value);
                });
            }

            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    const fy = yearDropdown ? yearDropdown.value : getCurrentFiscalYear();
                    fetchDashboardData(fy);
                });
            }

            document.addEventListener('sfdcTabChanged', function(event) {
                if (!event.detail || event.detail.tab !== 'dashboard') return;
                const fy = yearDropdown ? yearDropdown.value : getCurrentFiscalYear();
                fetchDashboardData(fy);
            });

            DashboardConfig.initialized = true;

            // Auto-load if dashboard is visible on page load
            if (dashboardTab.classList.contains('active')) {
                const fy = yearDropdown ? yearDropdown.value : getCurrentFiscalYear();
                fetchDashboardData(fy);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDashboard);
        } else {
            initDashboard();
        }

    })(window, document);
</script>