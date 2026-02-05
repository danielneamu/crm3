<?php
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
requireLogin();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tools - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/toast.css" rel="stylesheet">
    <link href="assets/css/tools.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col">
                <h2><i class="bi bi-tools"></i> Business Tools</h2>
                <p class="text-muted">Pricing calculator and business case generator</p>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="toolsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pricing-tab" data-bs-toggle="tab" data-bs-target="#pricing" type="button" role="tab">
                    <i class="bi bi-calculator"></i> Pricing Calculator
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link disabled" id="forecast-tab" data-bs-toggle="tab" data-bs-target="#forecast" type="button" role="tab">
                    <i class="bi bi-graph-up"></i> Revenue Forecast <span class="badge bg-secondary">Phase 2</span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="toolsTabContent">
            <!-- TAB 1: Pricing Calculator -->
            <div class="tab-pane fade show active" id="pricing" role="tabpanel">

                <!-- Instructions Card -->
                <div class="card mb-4 border-info">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-info-circle"></i> How to Use
                    </div>
                    <div class="card-body">
                        <ol class="mb-0">
                            <li>Enter OTC (One-Time Cost) items with unit costs and quantities</li>
                            <li>Enter Recurrent items with monthly costs and quantities</li>
                            <li>Review business cases showing markup across different contract periods and margins</li>
                            <li>Copy summary table for email proposals</li>
                        </ol>
                    </div>
                </div>

                <!-- Input Section -->
                <div class="row">
                    <!-- OTC Items -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-coin"></i> OTC Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="otcItemsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="35%">Description</th>
                                                <th width="20%">Unit Cost (€)</th>
                                                <th width="15%">Qty</th>
                                                <th width="20%">Total (€)</th>
                                                <th width="5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="otcItemsBody">
                                            <!-- Dynamic rows -->
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addOTCRow()">
                                    <i class="bi bi-plus-circle"></i> Add Line
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Recurrent Items -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Recurrent Items</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="recurrentItemsTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="35%">Description</th>
                                                <th width="20%">Unit Cost (€)</th>
                                                <th width="15%">Qty</th>
                                                <th width="20%">Total (€)</th>
                                                <th width="5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="recurrentItemsBody">
                                            <!-- Dynamic rows -->
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success" onclick="addRecurrentRow()">
                                    <i class="bi bi-plus-circle"></i> Add Line
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mb-4">
                    <div class="col">
                        <button type="button" class="btn btn-primary btn-lg" onclick="calculateAllBusinessCases()">
                            <i class="bi bi-calculator-fill"></i> Calculate Business Cases
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetCalculator()">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset All
                        </button>
                    </div>
                </div>

                <!-- Results Section -->
                <div id="resultsSection" style="display: none;">
                    <!-- Summary Section (Grand Total + Email Format) -->
                    <div class="mb-4">
                        <h4 class="mb-3"><i class="bi bi-clipboard-data"></i> Pricing Summary</h4>
                        <div id="summaryResults">
                            <!-- Summary table and email format (side by side) -->
                        </div>
                    </div>

                    <!-- Line Item Business Cases -->
                    <div class="mb-4">
                        <h4 class="mb-3"><i class="bi bi-list-ol"></i> Line Item Business Cases</h4>
                        <div id="lineItemResults">
                            <!-- Dynamic business case tables -->
                        </div>
                    </div>
                </div>

            </div>

            <!-- TAB 2: Revenue Forecast (Phase 2) -->
            <div class="tab-pane fade" id="forecast" role="tabpanel">
                <div class="alert alert-warning">
                    <i class="bi bi-wrench"></i> <strong>Coming Soon (Phase 2)</strong>
                    <p class="mt-2 mb-0">Revenue forecasting tools based on pipeline data.</p>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/pricing-calculator.js"></script>

</body>

</html>