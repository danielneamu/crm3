<style>
    /* Enhanced Active State */
    .navbar-nav .nav-link {
        position: relative;
        transition: all 0.3s ease;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
    }

    .navbar-nav .nav-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .navbar-nav .nav-link.active {
        background-color: rgba(255, 255, 255, 0.2);
        font-weight: 600;
    }

    .navbar-nav .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -0.5rem;
        left: 50%;
        transform: translateX(-50%);
        width: 50%;
        height: 3px;
        background-color: #fff;
        border-radius: 2px;
    }

    /* Dropdown Menu Enhancements */
    .dropdown-menu {
        border: none;
        min-width: 200px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .dropdown-header {
        font-size: 0.875rem;
        color: #495057;
        padding: 0.75rem 1rem;
    }

    .dropdown-item {
        padding: 0.5rem 1rem;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        padding-left: 1.25rem;
    }

    /* Navbar Brand Enhancement */
    .navbar-brand {
        font-size: 1.25rem;
        letter-spacing: 0.5px;
    }

    /* Center Navigation Layout */
    .navbar-nav-centered {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
    }

    /* Mobile Responsive Adjustments */
    @media (max-width: 991.98px) {
        .navbar-nav-centered {
            position: static;
            transform: none;
        }

        .navbar-nav {
            padding: 1rem 0;
        }

        .navbar-nav .nav-link {
            padding: 0.75rem 1rem;
        }

        .navbar-nav .nav-link.active::after {
            display: none;
        }
    }

    .navbar {
        overflow: visible !important;
        position: relative;
        z-index: 1001;
    }

    .dropdown-menu {
        z-index: 1050 !important;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <!-- Brand with Icon (Left Side) -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="projects.php">
            <i class="bi bi-briefcase-fill me-2"></i>
            CRM System
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible Navigation -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Centered Main Navigation -->
            <ul class="navbar-nav navbar-nav-centered mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-house-door me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="projects.php">
                        <i class="bi bi-folder me-1"></i>Projects
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="agents.php">
                        <i class="bi bi-people me-1"></i>Agents
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="companies.php">
                        <i class="bi bi-building me-1"></i>Companies
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="partners.php">
                        <i class="bi bi-handshake me-1"></i>Partners
                    </a>
                </li>

                <!-- ISSUE #1 FIX: Moved dropdown outside centered nav and use standard Bootstrap -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="linksDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-link-45deg me-1"></i>Links
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="linksDropdown">
                        <li><a class="dropdown-item" href="https://remedy-web.vodafone.ro/" target="_blank"><i class="bi bi-box-arrow-up-right me-2"></i>Remedy</a></li>
                        <li><a class="dropdown-item" href="https://salesforce.com" target="_blank"><i class="bi bi-box-arrow-up-right me-2"></i>Salesforce</a></li>
                        <li><a class="dropdown-item" href="https://example.com/demo" target="_blank"><i class="bi bi-box-arrow-up-right me-2"></i>Demo Portal</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="https://vodafone.com" target="_blank"><i class="bi bi-box-arrow-up-right me-2"></i>Vodafone Portal</a></li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="bi bi-person-badge me-1"></i>Users
                    </a>
                </li>
            </ul>

            <!-- ISSUE #2 FIX: User Dropdown - removed dropdown-menu-end, positioned correctly -->
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2"></i>
                        <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li>
                            <h6 class="dropdown-header"><i class="bi bi-person-circle me-2"></i><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></h6>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-question-circle me-2"></i>Help</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>




