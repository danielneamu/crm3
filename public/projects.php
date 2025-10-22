<?php
require_once '../config/config.php';
require_once '../includes/session.php';
require_once '../includes/auth.php';
requireLogin();

// Generate JSON if missing
if (!file_exists(__DIR__ . '/data/projects.json')) {
    require_once '../config/database.php';
    require_once '../app/controllers/ProjectController.php';
    $controller = new ProjectController((new Database())->getConnection());
    $controller->generateJson();
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Projects - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-dark bg-primary mb-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="projects.php"><?= APP_NAME ?></a>
            <div class="d-flex gap-2">
                <a href="users.php" class="btn btn-outline-light btn-sm">Users</a>
                <span class="text-white"><?= $_SESSION['full_name'] ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container-fluid">
        <table id="projectsTable" class="table table-sm table-hover" style="width:100%">
            <thead>
                <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Firma</th>
                    <th>Proiect</th>
                    <th>CUI</th>
                    <th>Agent</th>
                    <th>Team</th>
                    <th>PT</th>
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
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="assets/js/projects.js"></script>
</body>

</html>