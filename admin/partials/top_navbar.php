    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark top-navbar">
        <div class="container-fluid">
            <button class="btn btn-link text-white me-2" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>
            <a class="navbar-brand" href="#">
                <i class="bi bi-mortarboard-fill me-2"></i>OTIENO ACADEMY
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-2 fs-5"></i>
                            <span class="d-none d-md-inline"><?php echo htmlspecialchars($admin_name ?? 'Admin'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/mini-auth-project/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
