<?php
session_start();
include("../config.php");

// Security: Must be logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$full_name = trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
if (empty($full_name)) $full_name = $_SESSION['email'];
$admin_name = $full_name;

// Fetch Stats
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$active_users = $conn->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetch_row()[0];
$faculties_count = $conn->query("SELECT COUNT(*) FROM faculties")->fetch_row()[0];
$courses_count = $conn->query("SELECT COUNT(*) FROM courses")->fetch_row()[0];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Chuka University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/main.css" rel="stylesheet"/>
</head>
<body>
    <?php include 'partials/top_navbar.php'; ?>
    <?php include 'partials/sidebar.php'; ?>
    
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <!-- Dashboard Section -->
            <div id="section-dashboard" class="content-section">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-1">Admin Dashboard</h2>
                        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($admin_name); ?>! Manage your students and system.</p>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-4 col-md-6 mb-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3"><i class="bi bi-people-fill"></i></div>
                                <div><p class="stat-label">Total Users</p><h3 class="stat-value text-primary"><?php echo $total_users; ?></h3></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6 mb-3">
                        <div class="card stat-card success">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-success bg-opacity-10 text-success me-3"><i class="bi bi-check-circle-fill"></i></div>
                                <div><p class="stat-label">Active Users</p><h3 class="stat-value text-success"><?php echo $active_users; ?></h3></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6 mb-3">
                        <div class="card stat-card info">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-info bg-opacity-10 text-info me-3"><i class="bi bi-person-x-fill"></i></div>
                                <div><p class="stat-label">Inactive Users</p><h3 class="stat-value text-info"><?php echo $total_users - $active_users; ?></h3></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-building me-2"></i>Total Faculties</h5>
                                <h2 class="text-primary mb-0"><?php echo $faculties_count; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-book me-2"></i>Total Courses</h5>
                                <h2 class="text-success mb-0"><?php echo $courses_count; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/main.js"></script>
</body>
</html>