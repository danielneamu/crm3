<nav class="navbar navbar-dark bg-danger mb-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="projects.php"><?= APP_NAME ?></a>
        <div class="d-flex gap-2">
            <a href="dashboard.php" class="btn btn-outline-light btn-sm <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">Home</a>
            <a href="projects.php" class="btn btn-outline-light btn-sm <?= basename($_SERVER['PHP_SELF']) == 'projects.php' ? 'active' : '' ?>">Projects</a>
            <a href="agents.php" class="btn btn-outline-light btn-sm <?= basename($_SERVER['PHP_SELF']) == 'agents.php' ? 'active' : '' ?>">Agents</a>
            <a href="companies.php" class="btn btn-outline-light btn-sm <?= basename($_SERVER['PHP_SELF']) == 'companies.php' ? 'active' : '' ?>">Companies</a>
            <a href="partners.php" class="btn btn-outline-light btn-sm <?= basename($_SERVER['PHP_SELF']) == 'partners.php' ? 'active' : '' ?>">Partners</a>
            <a href="users.php" class="btn btn-outline-light btn-sm <?= basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : '' ?>">Users</a>
            <span class="text-white"><?= $_SESSION['full_name'] ?? 'User' ?></span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</nav>