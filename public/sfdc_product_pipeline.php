<?php

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

requireLogin();

require_once __DIR__ . '/../app/models/SfdcBaseModel.php';
require_once __DIR__ . '/../app/models/SfdcProductPipelineModel.php';

$db = new Database();
$conn = $db->getConnection();

$baseModel = new SfdcBaseModel($conn);
$productModel = new SfdcProductPipelineModel($conn);

$selectedFilters = [
    'team' => $_GET['team'] ?? '',
    'agent' => $_GET['agent'] ?? '',
    'month' => $_GET['month'] ?? '',
    'quarter' => $_GET['quarter'] ?? '',
    'year' => $_GET['year'] ?? '',
    'fiscal_period' => $_GET['fiscal_period'] ?? '',
    'product_family' => $_GET['product_family'] ?? '',
    'stage' => $_GET['stage'] ?? ''
];

$productRows = $productModel->getAll($selectedFilters);

$filterConfig = [
    'teams' => $baseModel->getTeams('main'),
    'agents' => $baseModel->getAgents('main'),
    'fiscalPeriods' => $baseModel->getFiscalPeriods('main'),
    'productFamilies' => $productModel->getProductFamilies(),
    'showFiscalPeriod' => true,
    'showProductFamily' => true,
    'showStage' => true,
    'showYear' => true,
    'selected' => $selectedFilters
];

$tabConfig = [
    'activeTab' => 'table',
    'tableId' => 'product-pipeline-table-tab',
    'dashboardId' => 'product-pipeline-dashboard-tab'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- jQuery: must load before any jQuery plugins -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SFDC Product Pipeline</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- DataTables core bundle CSS -->
    <link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/cr-2.1.2/cc-1.1.1/date-1.6.1/fc-5.0.5/fh-4.0.4/r-3.0.7/sc-2.4.3/sb-1.8.4/sp-2.3.5/sl-3.1.3/sr-1.4.3/datatables.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- RowGroup CSS -->
    <link href="https://cdn.datatables.net/rowgroup/1.5.0/css/rowGroup.bootstrap5.min.css" rel="stylesheet">

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">

    <!-- Bootstrap Datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css">

    <!-- Local app styles -->
    <link href="assets/css/projects.css" rel="stylesheet">
    <link href="assets/css/toast.css" rel="stylesheet">
</head>

<style>
    .tab-content {
        height: auto !important;
        min-height: 400px;
        overflow: visible !important;
    }

    .tab-content>.tab-pane {
        float: none !important;
        width: 100%;
    }

    #product-pipeline-dashboard-tab .card,
    #product-pipeline-dashboard-tab .card-body {
        display: block !important;
        width: 100%;
        height: auto !important;
    }

    #dashboardContentProductPipeline {
        display: block;
        width: 100%;
        min-height: 400px;
    }

    .tab-content {
        height: auto !important;
        min-height: 400px !important;
        max-height: none !important;
    }

    .tab-pane {
        height: auto !important;
        min-height: 0 !important;
    }

    #product-pipeline-dashboard-tab,
    #product-pipeline-dashboard-tab .card,
    #product-pipeline-dashboard-tab .card-body {
        height: auto !important;
        min-height: 0 !important;
        display: block !important;
    }
</style>

<body>
    <?php $skipNavbarBootstrapBundle = true; ?>
    <?php include __DIR__ . '/../includes/navbar.php'; ?>

    <div class="container-fluid py-4">
        <div class="page-header">
            <div class="d-flex align-items-baseline gap-1 flex-wrap">
                <h1 class="h3 mb-0">SFDC Product Pipeline</h1>
                <span class="page-subtitle text-muted">
                    Product line items with Annual Recurring Order Value aggregation.
                </span>
            </div>
        </div>

        <?php
        $tabConfig = [
            'activeTab' => 'table',
            'tableId' => 'product-pipeline-table-tab',
            'dashboardId' => 'product-pipeline-dashboard-tab'
        ];
        include __DIR__ . '/../app/views/sfdc/product_pipeline/_tabs.php';
        ?>

        <div class="tab-content">
            <div
                class="tab-pane fade show active"
                id="product-pipeline-table-tab"
                role="tabpanel"
                aria-labelledby="product-pipeline-table-tab-button">
                <?php include __DIR__ . '/../app/views/sfdc/product_pipeline/_filters.php'; ?>
                <?php include __DIR__ . '/../app/views/sfdc/product_pipeline/table.php'; ?>
            </div>

            <div
                class="tab-pane fade"
                id="product-pipeline-dashboard-tab"
                role="tabpanel"
                aria-labelledby="product-pipeline-dashboard-tab-button">
                <?php include __DIR__ . '/../app/views/sfdc/product_pipeline/dashboard.php'; ?>
            </div>
        </div>

    </div>

    <!-- Bootstrap JS bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- PDFMake for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- DataTables core bundle JS -->
    <script src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/cr-2.1.2/cc-1.1.1/date-1.6.1/fc-5.0.5/fh-4.0.4/r-3.0.7/sc-2.4.3/sb-1.8.4/sp-2.3.5/sl-3.1.3/sr-1.4.3/datatables.min.js"></script>

    <!-- RowGroup JS: must be loaded AFTER DataTables core -->
    <script src="https://cdn.datatables.net/rowgroup/1.5.0/js/dataTables.rowGroup.min.js"></script>

    <!-- Bootstrap Datepicker JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
            var dropdownList = dropdownElementList.map(function(dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl)
            });
        });
    </script>

    <!-- Product Pipeline DataTable script -->
    <script src="../app/views/sfdc/product_pipeline/_datatable.js"></script>

    <!-- Product Pipeline Dashboard script -->
    <script src="../app/views/sfdc/product_pipeline/_dashboard.js"></script>

    <script>
        document.addEventListener('shown.bs.tab', function(event) {
            const targetSelector = event.target.getAttribute('data-bs-target') || '';
            const tablePane = document.getElementById('product-pipeline-table-tab');
            const dashboardPane = document.getElementById('product-pipeline-dashboard-tab');

            if (!tablePane || !dashboardPane) return;

            if (targetSelector === '#product-pipeline-dashboard-tab') {
                dashboardPane.classList.add('active', 'show');
                tablePane.classList.remove('active', 'show');
            }

            if (targetSelector === '#product-pipeline-table-tab') {
                tablePane.classList.add('active', 'show');
                dashboardPane.classList.remove('active', 'show');
            }
        });
    </script>

    <div class="toast-container position-fixed top-0 start-50 p-3" style="z-index: 1080;">
        <div id="globalToast" class="toast shadow border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i id="toastIcon" class="bi bi-info-circle-fill text-info me-2"></i>
                <strong id="toastTitle" class="me-auto">Notice</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>

            <div class="toast-body">
                <div id="toastMessage">Message goes here.</div>
                <div class="mt-2" style="height: 4px; background: rgba(0,0,0,0.08); border-radius: 999px; overflow: hidden;">
                    <div id="toastProgressBar" style="height: 100%; width: 100%; background: #0dcaf0;"></div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>