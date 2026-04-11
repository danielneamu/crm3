<?php

/** Dashboard View for Pipeline - chart containers and KPI display  */
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
            <label for="dashboardFiscalYearPipeline" class="form-label fw-semibold">Fiscal Year</label>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <select id="dashboardFiscalYearPipeline" class="form-select" style="max-width: 300px;">
                </select>
                <button id="dashboardRefreshPipeline" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                </button>
            </div>
        </div>

        <div id="dashboardLoadingPipeline" style="display: none; text-align: center; padding: 40px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading dashboard data...</p>
        </div>

        <div id="dashboardContentPipeline" style="display: none; width: 100%;">
            <div class="sfdc-dashboard-grid">

                <div class="sfdc-dashboard-panel">
                    <h5>All — Amount</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiAllAmountTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiAllAmountAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiAllAmountDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-chart-wrap">
                        <canvas id="chartAllAmount"></canvas>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>All — Expected Revenue</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiAllExpRevTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiAllExpRevAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiAllExpRevDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-chart-wrap">
                        <canvas id="chartAllExpRev"></canvas>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>Fixed — Amount</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiFixedAmountTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiFixedAmountAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiFixedAmountDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-chart-wrap">
                        <canvas id="chartFixedAmount"></canvas>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>Fixed — Expected Revenue</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiFixedExpRevTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiFixedExpRevAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiFixedExpRevDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-chart-wrap">
                        <canvas id="chartFixedExpRev"></canvas>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>ICT — Amount</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiIctAmountTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiIctAmountAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiIctAmountDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-chart-wrap">
                        <canvas id="chartIctAmount"></canvas>
                    </div>
                </div>

                <div class="sfdc-dashboard-panel">
                    <h5>ICT — Expected Revenue</h5>
                    <p class="sfdc-dashboard-kpis">
                        <strong>Total:</strong> <span id="kpiIctExpRevTotal">—</span> |
                        <strong>Avg/Month:</strong> <span id="kpiIctExpRevAvg">—</span> |
                        <strong>Deals:</strong> <span id="kpiIctExpRevDeals">—</span>
                    </p>
                    <div class="sfdc-dashboard-chart-wrap">
                        <canvas id="chartIctExpRev"></canvas>
                    </div>
                </div>

            </div>
        </div>

        <div id="dashboardErrorPipeline" style="display: none; color: #842029; background-color: #f8d7da; border: 1px solid #f5c2c7; padding: 12px; border-radius: 4px;">
            <strong>Error:</strong> <span id="dashboardErrorMessagePipeline">Failed to load dashboard data.</span>
        </div>
    </div>
</div>