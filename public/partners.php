<?php
session_start();
if (!isset($_SESSION['username'])) {
    header('location: ../login/main/login.php');
    exit;
}
require_once '../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partners - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/cr-2.1.2/cc-1.1.1/date-1.6.1/fc-5.0.5/fh-4.0.4/r-3.0.7/sc-2.4.3/sb-1.8.4/sp-2.3.5/sl-3.1.3/sr-1.4.3/datatables.min.css" rel="stylesheet">
</head>

<body>
    <?php require_once '../includes/navbar.php'; ?>

    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">

            <div class="d-flex align-items-center gap-2">

                <!-- New / Edit Partner -->
                <button class="btn btn-primary" id="btnAddPartner">
                    <i class="bi bi-plus-circle"></i> New Partner
                </button>
                <button class="btn btn-secondary" id="btnEditPartner" disabled>
                    <i class="bi bi-pencil"></i> Edit Partner
                </button>

                <!-- Tag Filter -->
                <select class="form-select form-select-sm" id="tagFilter" style="width: 180px;">
                    <option value="">All Tags</option>
                </select>

                <!-- Manage Tags Button -->
                <button class="btn btn-outline-success btn-sm" id="btnManageTags">
                    <i class="bi bi-tags"></i> Manage Tags
                </button>

            </div>

            <!-- Search Box -->
            <div class="position-relative" style="width: 300px;">
                <input type="text" id="partnerSearch" class="form-control form-control-sm pe-5" placeholder="Search partners...">
                <button type="button" id="clearSearch" class="btn btn-link position-absolute top-50 end-0 translate-middle-y text-muted" style="display: none; padding: 0.375rem 0.75rem;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

        </div>


        <table id="partnersTable" class="table table-sm table-striped table-hover" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Partner Name</th>
                    <th>Type</th>
                    <th>Tags</th>
                    <th>Contacts</th>
                    <th>Created</th>
                </tr>
            </thead>
        </table>
    </div>

    <?php require_once '../app/views/modals/partner_modal.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.3.4/b-3.2.5/b-colvis-3.2.5/b-html5-3.2.5/b-print-3.2.5/cr-2.1.2/cc-1.1.1/date-1.6.1/fc-5.0.5/fh-4.0.4/r-3.0.7/sc-2.4.3/sb-1.8.4/sp-2.3.5/sl-3.1.3/sr-1.4.3/datatables.min.js"></script>
    <script src="assets/js/partners-datatable.js"></script>
    <script src="assets/js/partners-actions.js"></script>


    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3" style="z-index: 9999">
        <div id="globalToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>
</body>

</html>