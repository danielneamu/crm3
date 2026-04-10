<?php

/**
 * Dashboard View - ULTRA MINIMAL
 */
?>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <!-- Fiscal Year Selector -->
        <div style="margin-bottom: 20px;">
            <label for="dashboardFiscalYear" class="form-label fw-semibold">Fiscal Year</label>
            <div style="display: flex; gap: 10px;">
                <select id="dashboardFiscalYear" class="form-select" style="max-width: 300px;">
                    <!-- Options populated by JS -->
                </select>
                <button id="dashboardRefresh" class="btn btn-primary btn-sm">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div id="dashboardLoading" style="display: none; text-align: center; padding: 40px;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-2">Loading dashboard data...</p>
        </div>

        <!-- Charts Container -->
        <div id="dashboardContent" style="display: none; width: 100%;">

            <!-- ICT AOV -->
            <div style="margin-bottom: 40px; border: 1px solid #ccc; padding: 15px;">
                <h5>ICT — Revised AOV</h5>
                <p style="font-size: 12px; background: #f5f5f5; padding: 10px; margin: 0 0 15px 0;">
                    <strong>Total:</strong> <span id="kpiIctAovTotal">—</span> |
                    <strong>Avg/Month:</strong> <span id="kpiIctAovAvg">—</span> |
                    <strong>Deals:</strong> <span id="kpiIctAovDeals">—</span>
                </p>
                <div style="width: 100%; height: 300px; border: 1px solid #ddd; min-width: 200px;">
                    <canvas id="chartIctAov" style="width: 100% !important; height: 100% !important;"></canvas>
                </div>
            </div>

            <!-- ICT NPV -->
            <div style="margin-bottom: 40px; border: 1px solid #ccc; padding: 15px;">
                <h5>ICT — Revised NPV</h5>
                <p style="font-size: 12px; background: #f5f5f5; padding: 10px; margin: 0 0 15px 0;">
                    <strong>Total:</strong> <span id="kpiIctNpvTotal">—</span> |
                    <strong>Avg/Month:</strong> <span id="kpiIctNpvAvg">—</span> |
                    <strong>Deals:</strong> <span id="kpiIctNpvDeals">—</span>
                </p>
                <div style="width: 100%; height: 300px; border: 1px solid #ddd; min-width: 200px;">
                    <canvas id="chartIctNpv" style="width: 100% !important; height: 100% !important;"></canvas>
                </div>
            </div>

            <!-- Fixed AOV -->
            <div style="margin-bottom: 40px; border: 1px solid #ccc; padding: 15px;">
                <h5>Fixed — Revised AOV</h5>
                <p style="font-size: 12px; background: #f5f5f5; padding: 10px; margin: 0 0 15px 0;">
                    <strong>Total:</strong> <span id="kpiFixedAovTotal">—</span> |
                    <strong>Avg/Month:</strong> <span id="kpiFixedAovAvg">—</span> |
                    <strong>Deals:</strong> <span id="kpiFixedAovDeals">—</span>
                </p>
                <div style="width: 100%; height: 300px; border: 1px solid #ddd; min-width: 200px;">
                    <canvas id="chartFixedAov" style="width: 100% !important; height: 100% !important;"></canvas>
                </div>
            </div>

            <!-- Fixed NPV -->
            <div style="margin-bottom: 40px; border: 1px solid #ccc; padding: 15px;">
                <h5>Fixed — Revised NPV</h5>
                <p style="font-size: 12px; background: #f5f5f5; padding: 10px; margin: 0 0 15px 0;">
                    <strong>Total:</strong> <span id="kpiFixedNpvTotal">—</span> |
                    <strong>Avg/Month:</strong> <span id="kpiFixedNpvAvg">—</span> |
                    <strong>Deals:</strong> <span id="kpiFixedNpvDeals">—</span>
                </p>
                <div style="width: 100%; height: 300px; border: 1px solid #ddd; min-width: 200px;">
                    <canvas id="chartFixedNpv" style="width: 100% !important; height: 100% !important;"></canvas>
                </div>
            </div>

        </div>

        <!-- Error State -->
        <div id="dashboardError" style="display: none; color: #842029; background-color: #f8d7da; border: 1px solid #f5c2c7; padding: 12px; border-radius: 4px;">
            <strong>Error:</strong> <span id="dashboardErrorMessage">Failed to load dashboard data.</span>
        </div>
    </div>
</div>