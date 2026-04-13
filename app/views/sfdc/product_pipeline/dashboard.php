<?php

/**
 * Product Pipeline Dashboard View
 * Page-scoped dashboard only. Does not affect table flow.
 */
?>

<style>
    .sfdc-product-dashboard {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .sfdc-product-dashboard .dashboard-toolbar {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #fff;
        padding: 1rem;
    }

    .sfdc-product-dashboard .dashboard-toolbar .toolbar-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .sfdc-product-dashboard .dashboard-toolbar .toolbar-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-start;
        align-items: end;
        flex-wrap: wrap;
    }

    .sfdc-product-dashboard .dashboard-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
    }

    .sfdc-product-dashboard .dashboard-kpi-card,
    .sfdc-product-dashboard .dashboard-panel {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #fff;
        min-width: 0;
    }

    .sfdc-product-dashboard .dashboard-kpi-card {
        padding: 1rem;
    }

    .sfdc-product-dashboard .dashboard-kpi-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6c757d;
        margin-bottom: 0.35rem;
        font-weight: 600;
    }

    .sfdc-product-dashboard .dashboard-kpi-value {
        font-size: 1.5rem;
        line-height: 1.1;
        font-weight: 700;
        color: #212529;
    }

    .sfdc-product-dashboard .dashboard-panels {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .sfdc-product-dashboard .dashboard-panel.panel-full {
        grid-column: 1 / -1;
    }

    .sfdc-product-dashboard .dashboard-panel-header {
        padding: 0.9rem 1rem 0;
    }

    .sfdc-product-dashboard .dashboard-panel-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 600;
        color: #212529;
    }

    .sfdc-product-dashboard .dashboard-panel-subtitle {
        margin: 0.2rem 0 0;
        font-size: 0.75rem;
        color: #6c757d;
    }

    .sfdc-product-dashboard .dashboard-chart-wrap {
        position: relative;
        width: 100%;
        height: 320px;
        padding: 0.75rem 1rem 1rem;
    }

    .sfdc-product-dashboard .dashboard-chart-wrap.chart-tall {
        height: 380px;
    }

    .sfdc-product-dashboard .dashboard-chart-wrap canvas {
        display: block;
        width: 100% !important;
        height: 100% !important;
    }

    .sfdc-product-dashboard .dashboard-loading,
    .sfdc-product-dashboard .dashboard-error,
    .sfdc-product-dashboard .dashboard-empty {
        display: none;
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        background: #fff;
        padding: 2rem;
        text-align: center;
    }

    .sfdc-product-dashboard .dashboard-footnote {
        font-size: 0.8rem;
        color: #6c757d;
        margin: 0;
    }

    .sfdc-product-dashboard .dashboard-footnote strong {
        color: #495057;
    }

    @media (max-width: 1199.98px) {

        .sfdc-product-dashboard .dashboard-toolbar .toolbar-grid,
        .sfdc-product-dashboard .dashboard-kpis,
        .sfdc-product-dashboard .dashboard-panels {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .sfdc-product-dashboard .dashboard-panel.panel-full {
            grid-column: auto;
        }
    }

    @media (max-width: 767.98px) {

        .sfdc-product-dashboard .dashboard-toolbar .toolbar-grid,
        .sfdc-product-dashboard .dashboard-kpis,
        .sfdc-product-dashboard .dashboard-panels {
            grid-template-columns: 1fr;
        }
    }
</style>

<div id="sfdcProductDashboardRoot" class="sfdc-product-dashboard">

    <div class="dashboard-toolbar">
        <div class="toolbar-grid">
            <div>
                <label for="dashboardFiscalYearProduct" class="form-label fw-semibold">Fiscal Year</label>
                <select id="dashboardFiscalYearProduct" class="form-select">
                    <option value="2027">FY2027 (Apr 2026 – Mar 2027)</option>
                    <option value="2026" selected>FY2026 (Apr 2025 – Mar 2026)</option>
                    <option value="2025">FY2025 (Apr 2024 – Mar 2025)</option>
                    <option value="2024">FY2024 (Apr 2023 – Mar 2024)</option>
                </select>
            </div>

            <div>
                <label for="dashboardProductFamilyProduct" class="form-label fw-semibold">Product Family</label>
                <select id="dashboardProductFamilyProduct" class="form-select" multiple></select>
            </div>

            <div>
                <label for="dashboardProductNameProduct" class="form-label fw-semibold">Product Name</label>
                <select id="dashboardProductNameProduct" class="form-select" multiple></select>
            </div>

            <div class="toolbar-actions">
                <button id="dashboardRefreshProduct" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                </button>
                <button id="dashboardResetProduct" class="btn btn-outline-secondary">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <div id="dashboardLoadingProduct" class="dashboard-loading">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2 mb-0">Loading dashboard data...</p>
    </div>

    <div id="dashboardErrorProduct" class="dashboard-error">
        <strong class="text-danger d-block mb-2">Error</strong>
        <span id="dashboardErrorMessageProduct">Failed to load dashboard data.</span>
    </div>

    <div id="dashboardEmptyProduct" class="dashboard-empty">
        <strong class="d-block mb-2">No data found</strong>
        <span class="text-muted">Try changing the fiscal year or product filters.</span>
    </div>

    <div id="dashboardContentProduct" style="display:none;">
        <div class="dashboard-kpis">
            <div class="dashboard-kpi-card">
                <div class="dashboard-kpi-label">Total Pipeline AOV</div>
                <div class="dashboard-kpi-value" id="kpiTotalPipelineAovProduct">—</div>
            </div>

            <div class="dashboard-kpi-card">
                <div class="dashboard-kpi-label">Weighted Pipeline</div>
                <div class="dashboard-kpi-value" id="kpiWeightedPipelineProduct">—</div>
            </div>

            <div class="dashboard-kpi-card">
                <div class="dashboard-kpi-label">Avg Age</div>
                <div class="dashboard-kpi-value" id="kpiAvgAgeProduct">—</div>
            </div>

            <div class="dashboard-kpi-card">
                <div class="dashboard-kpi-label">Opp Count</div>
                <div class="dashboard-kpi-value" id="kpiOppCountProduct">—</div>
            </div>
        </div>

        <div class="dashboard-panels">
            <div class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <h5 class="dashboard-panel-title">Pipeline by Stage</h5>
                    <p class="dashboard-panel-subtitle">Deduplicated AOV Multi by stage</p>
                </div>
                <div class="dashboard-chart-wrap">
                    <canvas id="chartPipelineByStageProduct"></canvas>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <h5 class="dashboard-panel-title">Pipeline by Team</h5>
                    <p class="dashboard-panel-subtitle">Deduplicated AOV Multi, split by stage</p>
                </div>
                <div class="dashboard-chart-wrap">
                    <canvas id="chartPipelineByTeamProduct"></canvas>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <h5 class="dashboard-panel-title">Age vs AOV</h5>
                    <p class="dashboard-panel-subtitle">Opportunity-level scatter</p>
                </div>
                <div class="dashboard-chart-wrap">
                    <canvas id="chartAgeVsAovProduct"></canvas>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <h5 class="dashboard-panel-title">Probability Distribution</h5>
                    <p class="dashboard-panel-subtitle">Deduplicated opportunities by probability bucket</p>
                </div>
                <div class="dashboard-chart-wrap">
                    <canvas id="chartProbabilityDistributionProduct"></canvas>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <h5 class="dashboard-panel-title">Product Family Mix</h5>
                    <p class="dashboard-panel-subtitle">Cleaned ARROV by Product Family</p>
                </div>
                <div class="dashboard-chart-wrap">
                    <canvas id="chartProductFamilyMixProduct"></canvas>
                </div>
            </div>

            <div class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <h5 class="dashboard-panel-title">Close Date Timeline</h5>
                    <p class="dashboard-panel-subtitle">Deduplicated AOV Multi by close date</p>
                </div>
                <div class="dashboard-chart-wrap">
                    <canvas id="chartCloseTimelineProduct"></canvas>
                </div>
            </div>

            <div class="dashboard-panel panel-full">
                <div class="dashboard-panel-header">
                    <h5 class="dashboard-panel-title">Monthly Team AOV</h5>
                    <p class="dashboard-panel-subtitle">Fiscal month order Apr → Mar, deduplicated AOV Multi</p>
                </div>
                <div class="dashboard-chart-wrap chart-tall">
                    <canvas id="chartMonthlyTeamFiscalProduct"></canvas>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <p class="dashboard-footnote">
                <strong>Opportunity-level charts and KPIs</strong> use deduplicated <strong>AOV Multi</strong> counted once per Opp Ref.
                <strong>Product Family Mix</strong> uses row-level <strong>cleaned_ARROV</strong>.
            </p>
        </div>
    </div>
</div>