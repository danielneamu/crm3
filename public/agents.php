<?php
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
requireLogin();
?>

<?php require_once '../app/views/modals/agent_modal.php'; ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Agents - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/cr-2.1.2/cc-1.1.1/date-1.6.1/fc-5.0.5/fh-4.0.4/r-3.0.7/sc-2.4.3/sb-1.8.4/sp-2.3.5/sl-3.1.3/sr-1.4.3/datatables.min.css" rel="stylesheet">
    <link href="assets/css/agents.css" rel="stylesheet" />
    <!-- Your toaslt CSS -->
    <link href="assets/css/toast.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../includes/navbar.php'; ?>

    <div class="container-fluid">

        <!-- TOP BAR WITH FILTERS -->
        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-primary" id="btnAddAgent">
                        <i class="bi bi-plus-circle"></i> New Agent
                    </button>
                    <button class="btn btn-secondary" id="btnEditAgent" disabled>
                        <i class="bi bi-pencil"></i> Edit Agent
                    </button>

                    <!-- Team Filter -->
                    <select class="form-select form-select-sm" id="teamFilter" style="width: 180px;">
                        <option value="">All Teams</option>
                    </select>

                    <!-- Active Status Toggle Buttons -->
                    <div class="btn-group" role="group" aria-label="Active status filter">
                        <button type="button" class="btn btn-outline-success btn-sm active" id="filterActive">
                            <i class="bi bi-toggle-on"></i> Active
                        </button>
                        <button type="button" class="btn btn-outline-dark btn-sm" id="filterAll">
                            All
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="filterInactive">
                            <i class="bi bi-toggle-off"></i> Inactive
                        </button>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="position-relative" style="width: 300px;">
                    <input type="text" id="agentSearch" class="form-control form-control-sm pe-5" placeholder="Search agents...">
                    <button type="button" id="clearSearch" class="btn btn-link position-absolute top-50 end-0 translate-middle-y text-muted" style="display: none; padding: 0.375rem 0.75rem;">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <table id="agentsTable" class="table table-sm table-striped table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Agent Name</th>
                        <th>Code</th>
                        <th>Current Team</th>
                        <th>Projects</th>
                        <th>Member Since</th>
                        <th>Active</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>


    <?php
    require_once '../includes/footer.php';
    ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/cr-2.1.2/cc-1.1.1/date-1.6.1/fc-5.0.5/fh-4.0.4/r-3.0.7/sc-2.4.3/sb-1.8.4/sp-2.3.5/sl-3.1.3/sr-1.4.3/datatables.min.js"></script>
    <script src="assets/js/agents-datatable.js"></script>
    <script src="assets/js/agents-actions.js"></script>


</body>

</html>