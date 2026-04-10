<?php

/** Dashboard View - cleaned layout for Chart.js in tabs  */
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

    @media (max-width: 991.98px) {
        .sfdc-dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div style="margin-bottom: 20px;">
            <label for="dashboardFiscalYear" class="form-label fw-semibold">Fiscal Year</label>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <select id="dashboardFiscalYear" class="form-select" style="max-width: 300px;">
                </select>
                <button id="dashboardRefresh" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                </button>
            </div>
        </div>

        <div id="dashboardLoading" style="display: none; text-align: center; padding: 40px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading dashboard data...</p>
        </div>

        <div id="dashboardContent" style="display: none; width: 100%;">
            <div class="sfdc-dashboard-grid">

                <div class="sfdc-dashboard-panel">
                    <h5>ICT — Revised AOV</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiIctAovTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiIctAovAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiIctAovDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-chart-wrap">
                        <canvas id="chartIctAov"></canvas>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>ICT — Revised NPV</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiIctNpvTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiIctNpvAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiIctNpvDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-chart-wrap">
                        <canvas id="chartIctNpv"></canvas>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>Fixed — Revised AOV</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiFixedAovTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiFixedAovAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiFixedAovDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-chart-wrap">
                        <canvas id="chartFixedAov"></canvas>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>Fixed — Revised NPV</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiFixedNpvTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiFixedNpvAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiFixedNpvDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-chart-wrap">
                        <canvas id="chartFixedNpv"></canvas>
                    </div>
                </div>

            </div>
        </div>

        <div id="dashboardError" style="display: none; color: #842029; background-color: #f8d7da; border: 1px solid #f5c2c7; padding: 12px; border-radius: 4px;">
            <strong>Error:</strong> <span id="dashboardErrorMessage">Failed to load dashboard data.</span>
        </div>
    </div>
</div>