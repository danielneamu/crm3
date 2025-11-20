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
    <title>Reports - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/cr-2.1.2/cc-1.1.1/date-1.6.1/fc-5.0.5/fh-4.0.4/r-3.0.7/sc-2.4.3/sb-1.8.4/sp-2.3.5/sl-3.1.3/sr-1.4.3/datatables.min.css" rel="stylesheet">
    <link href="assets/css/toast.css" rel="stylesheet">
    <link href="assets/css/reports.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col">
                <h2><i class="bi bi-file-earmark-bar-graph"></i> Reports</h2>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="predefined-tab" data-bs-toggle="tab" data-bs-target="#predefined" type="button" role="tab">
                    <i class="bi bi-bookmark-fill"></i> Predefined Reports
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link disabled" id="builder-tab" data-bs-toggle="tab" data-bs-target="#builder" type="button" role="tab">
                    <i class="bi bi-hammer"></i> Query Builder <span class="badge bg-secondary">Phase 2</span>
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="reportTabContent">
            <!-- TAB 1: Predefined Reports -->
            <div class="tab-pane fade show active" id="predefined" role="tabpanel">
                <!-- Report Selection Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card report-card h-100 cursor-pointer" data-report="agent_performance">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-people"></i> Agent Performance
                                </h5>
                                <p class="card-text text-muted small">
                                    Project metrics and revenue by agent. Includes active projects, signed contracts, and activity summary.
                                </p>
                                <small class="text-info">
                                    <i class="bi bi-info-circle"></i> Fiscal year or custom date range
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card report-card h-100 cursor-pointer" data-report="projects_since_april">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-folder"></i> Projects Since April 1st
                                </h5>
                                <p class="card-text text-muted small">
                                    All projects with company, team, agent, and financial details. Includes references (EFT, SD, PT, SFDC).
                                </p>
                                <small class="text-info">
                                    <i class="bi bi-info-circle"></i> Current or previous fiscal year
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card report-card h-100 cursor-pointer" data-report="project_timeline">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-calendar-event"></i> Project Timeline
                                </h5>
                                <p class="card-text text-muted small">
                                    Detailed project list with status history, timeline, and responsible parties. Track project journey.
                                </p>
                                <small class="text-info">
                                    <i class="bi bi-info-circle"></i> Custom date range
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card report-card h-100 cursor-pointer" data-report="contract_signed_analysis">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-file-check"></i> Contract Signed Analysis
                                </h5>
                                <p class="card-text text-muted small">
                                    Projects with "Contract Signed" status. Filter by date, SFDC coverage, AOV, and activity status.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Display Area -->
                <div id="reportDisplay" style="display: none;">
                    <!-- Filter Panel (Collapsible) -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <button class="btn btn-link text-start w-100" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                                <i class="bi bi-funnel"></i> <strong>Filters</strong>
                                <i class="bi bi-chevron-down float-end"></i>
                            </button>
                        </div>
                        <div class="collapse show" id="filterPanel">
                            <div class="card-body">
                                <form id="reportFilters">
                                    <div class="row g-3">
                                        <!-- Date Range -->
                                        <div class="col-md-3">
                                            <label class="form-label">Date From</label>
                                            <input type="date" class="form-control" id="filterDateFrom" name="dateFrom">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Date To</label>
                                            <input type="date" class="form-control" id="filterDateTo" name="dateTo">
                                        </div>

                                        <!-- Team Filter -->
                                        <div class="col-md-3">
                                            <label class="form-label">Team</label>
                                            <select class="form-select" id="filterTeam" name="team" multiple>
                                                <option value="">Loading...</option>
                                            </select>
                                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                        </div>

                                        <!-- Status Filter -->
                                        <div class="col-md-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" id="filterStatus" name="status" multiple>
                                                <option value="">Loading...</option>
                                            </select>
                                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                        </div>

                                        <!-- Project Type Filter -->
                                        <div class="col-md-3" id="projectTypeFilterContainer" style="display: none;">
                                            <label class="form-label">Project Type</label>
                                            <select class="form-select" id="filterProjectType" name="projectType" multiple>
                                                <option value="">Loading...</option>
                                            </select>
                                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                                        </div>

                                        <!-- Fiscal Year Filter (for Projects Since April) -->
                                        <div class="col-md-3" id="fiscalYearFilterContainer" style="display: none;">
                                            <label class="form-label">Fiscal Year</label>
                                            <select class="form-select" id="filterFiscalYear" name="fiscalYear">
                                                <option value="current">Current (Apr - Today)</option>
                                                <option value="previous">Previous (Apr - Mar)</option>
                                            </select>
                                        </div>

                                        <!-- Contract Signed Filters Container -->
                                        <div id="contractSignedFiltersContainer" style="display: none;">
                                            <div class="row g-3">
                                                <!-- Date Range -->
                                                <div class="col-md-3">
                                                    <label class="form-label">Date Range</label>
                                                    <select class="form-select" id="filterDateRange" name="dateRange">
                                                        <option value="april" selected>Fiscal Year (April 1st)</option>
                                                        <option value="last3months">Last 3 Months</option>
                                                    </select>
                                                </div>

                                                <!-- SFDC Filter -->
                                                <div class="col-md-3">
                                                    <label class="form-label">SFDC</label>
                                                    <select class="form-select" id="filterSfdc" name="sfdc">
                                                        <option value="all">All</option>
                                                        <option value="has">Has Value</option>
                                                        <option value="empty">Empty</option>
                                                    </select>
                                                </div>

                                                <!-- AOV Filter -->
                                                <div class="col-md-3">
                                                    <label class="form-label">AOV</label>
                                                    <select class="form-select" id="filterAov" name="aov">
                                                        <option value="all">All</option>
                                                        <option value="has">Has Value</option>
                                                        <option value="empty">Empty/Zero</option>
                                                    </select>
                                                </div>

                                                <!-- Active Filter -->
                                                <div class="col-md-3">
                                                    <label class="form-label">Status</label>
                                                    <select class="form-select" id="filterActive" name="active">
                                                        <option value="all">All</option>
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row g-2 mt-3">
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-primary" id="btnRefreshReport">
                                                <i class="bi bi-arrow-clockwise"></i> Run Report
                                            </button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-outline-secondary" id="btnResetFilters">
                                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                                            </button>
                                        </div>
                                        <div class="col-auto">
                                            <button type="button" class="btn btn-outline-success" id="btnExportCSV">
                                                <i class="bi bi-download"></i> Export CSV
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Results Table -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h5 class="mb-0" id="reportTitle">Report Results</h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="loadingSpinner" class="text-center py-5" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="text-muted mt-2">Loading report data...</p>
                            </div>

                            <div class="table-responsive" id="tableContainer">
                                <table id="reportTable" class="table table-sm table-hover mb-0" style="width: 100%">
                                    <thead>
                                        <tr></tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>

                            <div id="noDataMessage" class="alert alert-info m-3" style="display: none;">
                                <i class="bi bi-info-circle"></i> No data matches your filters. Try adjusting your selection.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Initial State (before report selected) -->
                <div id="initialState" class="alert alert-secondary text-center py-5">
                    <i class="bi bi-arrow-up" style="font-size: 2rem;"></i>
                    <p class="mt-3">Select a report above to get started</p>
                </div>
            </div>

            <!-- TAB 2: Query Builder (Phase 2) -->
            <div class="tab-pane fade" id="builder" role="tabpanel">
                <div class="alert alert-warning">
                    <i class="bi bi-wrench"></i> <strong>Coming Soon (Phase 2)</strong>
                    <p class="mt-2 mb-0">Advanced query builder for custom metric combinations. Create flexible reports without predefined templates.</p>
                </div>
            </div>
        </div>
    </div>

    <?php require_once '../includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/cr-2.1.2/cc-1.1.1/date-1.6.1/fc-5.0.5/fh-4.0.4/r-3.0.7/sc-2.4.3/sb-1.8.4/sp-2.3.5/sl-3.1.3/sr-1.4.3/datatables.min.js"></script>
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/reports-filters.js"></script>
    <script src="assets/js/reports-datatable.js"></script>
    <script src="assets/js/reports-export.js"></script>

</body>

</html>