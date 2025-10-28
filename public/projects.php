<?php
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
requireLogin();

// Generate JSON if missing
//if (!file_exists(__DIR__ . '/data/projects.json')) {
require_once '../config/database.php';
require_once '../app/controllers/ProjectController.php';
$controller = new ProjectController((new Database())->getConnection());
$controller->generateJson();
//}
?>

<?php require_once '../app/views/modals/project_modal.php'; ?>
<?php require_once '../app/views/modals/status_modal.php'; ?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Projects - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables CSS with all extensions -->
    <link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/cr-2.1.2/cc-1.1.1/date-1.6.1/fc-5.0.5/fh-4.0.4/r-3.0.7/sc-2.4.3/sb-1.8.4/sp-2.3.5/sl-3.1.3/sr-1.4.3/datatables.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <!-- ADD Bootstrap Datepicker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/css/bootstrap-datepicker3.min.css">

    <link href="assets/css/projects.css" rel="stylesheet" />
    <!-- Your toaslt CSS -->
    <link href="assets/css/toast.css" rel="stylesheet">


</head>

<body>
    <?php require_once '../includes/navbar.php'; ?>

    <div class="container-fluid">

        <!-- TOP BAR WITH FILTERS AND SEARCH -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-primary" id="btnAddProject">
                    <i class="bi bi-plus-circle"></i> New Project
                </button>
                <button class="btn btn-secondary" id="btnEditProject" disabled>
                    <i class="bi bi-pencil"></i> Edit Project
                </button>

                <!-- Filter Dropdowns -->
                <select class="form-select" id="teamFilter" style="width: 200px;">
                </select>
                <select class="form-select" id="assignedFilter" style="width: 200px;">
                    <option value="">All Assigned</option>
                </select>
                <select class="form-select" id="statusFilter" style="width: 200px;">
                    <option value="">All Status</option>
                </select>

                <!-- Active Status Toggle Buttons -->
                <div class="btn-group" role="group" aria-label="Active status filter">
                    <button type="button" class="btn btn-outline-success btn-sm" id="filterActive">
                        <i class="bi bi-toggle-on"></i> Active
                    </button>
                    <button type="button" class="btn btn-outline-dark btn-sm active" id="filterAll">
                        All
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="filterInactive">
                        <i class="bi bi-toggle-off"></i> Inactive
                    </button>
                </div>
            </div>

            <div class="position-relative" style="width: 300px;">
                <input type="text" id="projectSearch" class="form-control pe-5" placeholder="Search projects...">
                <button type="button" id="clearSearch" class="btn btn-link position-absolute top-50 end-0 translate-middle-y text-muted" style="display: none; padding: 0.375rem 0.75rem;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>




        <table id="projectsTable" class="table table-sm table-striped table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Firma</th>
                    <th>Proiect</th>
                    <th>CUI</th>
                    <th>Agent</th>
                    <th>Team</th> <!-- Add this - will be hidden by JS -->
                    <th>Caz PT</th>
                    <th>SD</th>
                    <th>EFT</th>
                    <th>SFDC</th>
                    <th>Create Date</th>
                    <th>LastUpDate</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>DL</th>
                    <th>Type</th>
                    <th>AOV</th>
                    <th>ON</th>

                </tr>
            </thead>
        </table>
    </div>

    <?php require_once '../includes/footer.php';  ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- PDFMake for PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- DataTables JS with all extensions -->
    <script src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/cr-2.1.2/cc-1.1.1/date-1.6.1/fc-5.0.5/fh-4.0.4/r-3.0.7/sc-2.4.3/sb-1.8.4/sp-2.3.5/sl-3.1.3/sr-1.4.3/datatables.min.js"></script>


    <!-- ADD Bootstrap Datepicker JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.10.0/dist/js/bootstrap-datepicker.min.js"></script>

    <script src="assets/js/projects-datatable.js"></script>
    <script src="assets/js/projects-actions.js"></script>
    <script src="assets/js/status-actions.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>





</body>

</html>