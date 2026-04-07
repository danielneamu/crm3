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
    <title>Dashboard - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
            min-height: 80px;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-value {
            font-weight: 700;
        }

        .stat-label {
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }


        .chart-container {
            position: relative;
            height: 300px;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
    </style>
</head>

<body>
    <?php require_once '../includes/navbar.php'; ?>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <h2 class="mb-4">Dashboard</h2>

        <!-- Statistics Cards Row -->
        <!-- Statistics Cards - All in One Row -->
        <div class="row g-2 mb-4">
            <!-- Total Projects -->
            <div class="col-lg col-md-3 col-sm-6">
                <div class="card stat-card border-primary h-100">
                    <div class="card-body p-3">
                        <p class="stat-label mb-1" style="font-size: 0.75rem;">Total Projects</p>
                        <p class="stat-value text-primary mb-0" style="font-size: 1.8rem;" id="totalProjects">0</p>
                    </div>
                </div>
            </div>

            <!-- Completed Projects -->
            <div class="col-lg col-md-3 col-sm-6">
                <div class="card stat-card border-success h-100">
                    <div class="card-body p-3">
                        <p class="stat-label mb-1" style="font-size: 0.75rem;">Completed</p>
                        <p class="stat-value text-success mb-0" style="font-size: 1.8rem;" id="completedProjects">0</p>
                    </div>
                </div>
            </div>

            <!-- Signed Projects -->
            <div class="col-lg col-md-3 col-sm-6">
                <div class="card stat-card border-info h-100">
                    <div class="card-body p-3">
                        <p class="stat-label mb-1" style="font-size: 0.75rem;">Signed</p>
                        <p class="stat-value text-info mb-0" style="font-size: 1.8rem;" id="signedProjects">0</p>
                    </div>
                </div>
            </div>

            <!-- Ongoing Projects -->
            <div class="col-lg col-md-3 col-sm-6">
                <div class="card stat-card border-warning h-100">
                    <div class="card-body p-3">
                        <p class="stat-label mb-1" style="font-size: 0.75rem;">Ongoing</p>
                        <p class="stat-value text-warning mb-0" style="font-size: 1.8rem;" id="ongoingProjects">0</p>
                    </div>
                </div>
            </div>

            <!-- Opened This Month -->
            <div class="col-lg col-md-4 col-sm-6">
                <div class="card stat-card border-primary h-100">
                    <div class="card-body p-3">
                        <p class="stat-label mb-1" style="font-size: 0.75rem;">Opened (Month)</p>
                        <div class="d-flex align-items-baseline">
                            <p class="stat-value text-primary mb-0 me-2" style="font-size: 1.8rem;" id="openedThisMonth">0</p>
                            <small class="text-muted" style="font-size: 0.7rem;" id="openedComparison"></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed This Month -->
            <div class="col-lg col-md-4 col-sm-6">
                <div class="card stat-card border-success h-100">
                    <div class="card-body p-3">
                        <p class="stat-label mb-1" style="font-size: 0.75rem;">Completed (Month)</p>
                        <div class="d-flex align-items-baseline">
                            <p class="stat-value text-success mb-0 me-2" style="font-size: 1.8rem;" id="completedThisMonth">0</p>
                            <small class="text-muted" style="font-size: 0.7rem;" id="completedComparison"></small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signed This Month -->
            <div class="col-lg col-md-4 col-sm-6">
                <div class="card stat-card border-info h-100">
                    <div class="card-body p-3">
                        <p class="stat-label mb-1" style="font-size: 0.75rem;">Signed (Month)</p>
                        <div class="d-flex align-items-baseline">
                            <p class="stat-value text-info mb-0 me-2" style="font-size: 1.8rem;" id="signedThisMonth">0</p>
                            <small class="text-muted" style="font-size: 0.7rem;" id="signedComparison"></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Full Width Monthly Status History Chart -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Monthly Status History</h5>
                        <select class="form-select form-select-sm" style="width: auto;" id="statusHistorySelector">
                            <option value="new" selected>New Projects Opened</option>
                            <option value="completed">Completed Projects</option>
                            <option value="signed">Contract Signed</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 350px;">
                            <canvas id="statusHistoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <!-- CHART: Fiscal Year Comparison (Projects Timeline) -->


        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Fiscal Year Comparison</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="timelineChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Projects by Team</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="teamChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Projects by Type</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="typeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h5 class="mb-0">Agent Status Summary</h5>
                        <select id="agentStatusPeriod" class="form-select form-select-sm" style="width:auto;">
                            <option value="fiscal" selected>This Fiscal Year</option>
                            <option value="all">All Time</option>
                        </select>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="statusAgent"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Top 10 Agents</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="agentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">In Progress By agent</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="inProgressByAgent"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Recent Projects Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Projects</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover" id="recentProjectsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Project Name</th>
                                        <th>Company</th>
                                        <th>Agent</th>
                                        <th>Created</th>
                                        <th>TCV</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Load Chart.js first, then stats, then charts -->


    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
    <script src="assets/js/dashboard-stats.js"></script>
    <script src="assets/js/dashboard-charts.js"></script>

</body>

</html>