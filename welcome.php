<?php
session_start();

// Only allow logged-in students (role = 'user')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'config.php';

// Fetch ALL user details using the session user_id
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

// Build full name safely
$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
if (empty($full_name)) {
    $full_name = $user['email'] ?? 'User';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-bg: #495057;
            --sidebar-dark: #343a40;
            --sidebar-hover: #343a40;
            --navbar-height: 60px;
            --navbar-height-lg: 70px;
        }

        body { 
            background-color: #f4f6f9; 
            overflow-x: hidden;
        }
        
        /* ========== DESKTOP SIDEBAR (lg and up) ========== */
        .sidebar-desktop { 
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            height: 100vh;
            background-color: var(--sidebar-bg);
            z-index: 1000;
            display: none; /* Hidden on mobile */
            flex-direction: column;
            overflow: hidden;
            transition: width 0.3s ease;
        }

        /* Collapsed state */
        .sidebar-desktop.sidebar-collapsed {
            width: 70px;
        }

        /* ========== TOOLTIP ANIMATIONS ========== */
        @keyframes tooltipFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50%) translateX(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(-50%) translateX(0);
            }
        }

        /* Show desktop sidebar on large screens */
        @media (min-width: 992px) {
            .sidebar-desktop {
                display: flex;
            }
        }
        
        .sidebar-desktop .profile-section { 
            position: relative;
            top: 0; 
            z-index: 20; 
            height: var(--navbar-height-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            background-color: var(--sidebar-bg);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Profile tooltip on hover when collapsed */
        .sidebar-desktop.sidebar-collapsed .profile-section:hover::after {
            content: 'Profile';
            position: absolute;
            left: 75px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #2c3133;
            color: #fff;
            padding: 0.6rem 0.9rem;
            border-radius: 0.35rem;
            font-size: 0.85rem;
            font-weight: 500;
            white-space: nowrap;
            z-index: 1100;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            pointer-events: none;
            letter-spacing: 0.3px;
            border-left: 3px solid #28a745;
            animation: tooltipFadeIn 0.2s ease;
        }
        
        .sidebar-desktop .sidebar-menu { 
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        /* Expanded sidebar scrollbar styles */
        .sidebar-desktop .sidebar-menu::-webkit-scrollbar {
            width: 8px;
        }

        .sidebar-desktop .sidebar-menu::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-desktop .sidebar-menu::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.4);
            border-radius: 4px;
            opacity: 0;
            transition: opacity 0.3s ease, background 0.3s ease;
        }

        .sidebar-desktop .sidebar-menu:hover::-webkit-scrollbar-thumb {
            opacity: 1;
            background: rgba(255, 255, 255, 0.6);
        }

        .sidebar-desktop .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.8);
        }

        /* Hide scrollbar when collapsed */
        .sidebar-desktop.sidebar-collapsed .sidebar-menu {
            scrollbar-width: none;
        }

        .sidebar-desktop.sidebar-collapsed .sidebar-menu::-webkit-scrollbar {
            display: none;
        }
        
        .profile-img { 
            width: 100px; 
            height: 100px; 
            object-fit: cover; 
            border: 3px solid #fff; 
        }

        .profile-section .profile-img {
            width: 50px;
            height: 50px;
            max-height: calc(var(--navbar-height-lg) - 10px);
        }

        .profile-img-small {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }
        
        /* ========== MOBILE MENU COLUMN (md and below) ========== */
        .mobile-menu-column {
            background-color: var(--sidebar-bg);
            max-width: 280px;
            height: calc(100vh - var(--navbar-height));
            overflow-y: auto;
            overflow-x: hidden;
            position: fixed;
            top: var(--navbar-height);
            left: 0;
            z-index: 999;
            transition: transform 0.3s ease;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2);
        }

        .mobile-menu-column.hidden {
            transform: translateX(-100%);
        }

        @media (min-width: 992px) {
            .mobile-menu-column {
                display: none !important;
            }
        }

        /* Custom Scrollbar for Mobile Menu */
        .mobile-menu-column::-webkit-scrollbar {
            width: 6px;
        }

        .mobile-menu-column::-webkit-scrollbar-track {
            background: transparent;
        }

        .mobile-menu-column::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .mobile-menu-column:hover::-webkit-scrollbar-thumb {
            opacity: 1;
        }

        .mobile-menu-column::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .mobile-menu-column {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.2) transparent;
        }

        .mobile-menu-column .sidebar-menu {
            padding: 1rem;
        }
        
        /* ========== NAVBAR ========== */
        .top-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--navbar-height);
            background-color: var(--sidebar-dark);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            z-index: 1030;
            transition: left 0.3s ease;
        }

        /* Desktop navbar - starts after sidebar */
        @media (min-width: 992px) {
            .top-navbar {
                left: 280px;
                height: var(--navbar-height-lg);
            }
            
            body.sidebar-collapsed .top-navbar {
                left: 70px;
            }
        }

        /* Hide search bar and user name on mobile */
        @media (max-width: 991px) {
            .top-navbar .search-form,
            .top-navbar .user-name {
                display: none !important;
            }
        }
        
        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-top: var(--navbar-height);
            padding: 1.5rem;
            min-height: calc(100vh - var(--navbar-height) - 60px);
            transition: margin-left 0.3s ease;
        }

        /* When mobile menu is visible, shift content on larger mobile/tablet screens */
        @media (min-width: 576px) and (max-width: 991px) {
            .main-content.shifted {
                margin-left: 280px;
            }
        }

        @media (min-width: 992px) {
            .main-content {
                margin-left: 280px;
                margin-top: var(--navbar-height-lg);
                min-height: calc(100vh - var(--navbar-height-lg) - 60px);
            }
            
            body.sidebar-collapsed .main-content {
                margin-left: 70px;
            }
        }
        
        /* ========== SIDEBAR NAV LINKS ========== */
        .nav-link { 
            color: #adb5bd !important;
            white-space: nowrap;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
            border-radius: 0.25rem;
            margin-bottom: 0.25rem;
            position: relative;
        }
        
        .nav-link:hover { 
            color: white !important; 
            background-color: var(--sidebar-hover);
        }

        .nav-link.active {
            color: white !important; 
            background-color: var(--sidebar-hover);
        }
        
        /* Hover label for collapsed state */
        .nav-label {
            display: none;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .sidebar-desktop.sidebar-collapsed .nav-item:hover .nav-label {
            position: absolute;
            left: 75px;
            top: 50%;
            transform: translateY(-50%);
            background-color: #2c3133;
            color: #fff;
            padding: 0.6rem 0.9rem;
            border-radius: 0.35rem;
            font-size: 0.85rem;
            font-weight: 500;
            white-space: nowrap;
            z-index: 1100;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4);
            display: block;
            pointer-events: none;
            opacity: 1;
            letter-spacing: 0.3px;
            border-left: 3px solid #28a745;
            animation: tooltipFadeIn 0.2s ease;
        }
        
        /* ========== SIDEBAR MENU ========== */
        .sidebar-menu .nav-link span:not(.nav-label),
        .sidebar-menu h5 {
            transition: opacity 0.3s ease;
        }

        .sidebar-desktop.sidebar-collapsed .nav-link span:not(.nav-label),
        .sidebar-desktop.sidebar-collapsed h5 {
            display: none;
        }

        .sidebar-desktop.sidebar-collapsed .nav-item {
            margin-bottom: 0.5rem;
        }

        .sidebar-desktop.sidebar-collapsed .nav-link {
            padding: 0.75rem;
            justify-content: center;
            display: flex;
            align-items: center;
        }

        .sidebar-desktop.sidebar-collapsed .nav-link i {
            margin-right: 0 !important;
        }
        
        .sidebar-menu .nav-item {
            position: relative;
        }

        /* ========== FOOTER ========== */
        .footer {
            background-color: var(--sidebar-dark);
            color: #adb5bd;
            padding: 1.5rem 0;
            margin-top: 3rem;
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 576px) and (max-width: 991px) {
            .footer.shifted {
                margin-left: 280px;
            }
        }

        @media (min-width: 992px) {
            .footer {
                margin-left: 280px;
            }
            
            body.sidebar-collapsed .footer {
                margin-left: 70px;
            }
        }
        
        /* ========== MOBILE SPECIFIC ========== */
        @media (max-width: 767px) {
            .main-content {
                padding: 1rem;
            }
            
            .card {
                margin-bottom: 1rem;
            }
            
            h2 {
                font-size: 1.5rem;
            }
            
            .card-header h5 {
                font-size: 1rem;
            }
        }
        
        @media (max-width: 575px) {
            .sidebar-menu h5 {
                font-size: 0.75rem;
            }
            
            .nav-link {
                font-size: 0.875rem;
                padding: 0.6rem 0.75rem;
            }

            .mobile-menu-column {
                max-width: 100%;
                width: 280px;
            }
        }
        
        /* ========== BUTTON STYLES ========== */
        .btn-view-doc {
            background-color: #E75B9E;
            border-color: #E75B9E;
            transition: all 0.3s ease;
        }

        .btn-view-doc:hover {
            background-color: #D63E7F;
            border-color: #D63E7F;
            color: white !important;
        }

        .btn-view-doc:focus {
            background-color: #D63E7F;
            border-color: #D63E7F;
            box-shadow: 0 0 0 0.25rem rgba(231, 91, 158, 0.5);
            color: white !important;
        }

        .btn-view-doc:active {
            background-color: #C21B64;
            border-color: #C21B64;
            color: white !important;
        }

        /* Center action buttons in table cells */
        table tbody td:last-child,
        table thead th:last-child {
            text-align: center;
        }

        table tbody td {
            height: 70px;
            vertical-align: middle;
        }

        table tbody td:last-child {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* ========== CARD HEADER THEME ========== */
        .card-header-theme {
            background-color: var(--sidebar-dark);
            color: white;
            border-color: var(--sidebar-dark);
        }

        .card-header-theme h5 {
            color: white;
        }

        /* ========== PRINT STYLES ========== */
        @media print {
            .sidebar-desktop,
            .mobile-menu-column,
            .top-navbar,
            .footer {
                display: none !important;
            }
            
            .main-content {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <!-- ========== DESKTOP SIDEBAR (lg and up) ========== -->
    <div class="sidebar-desktop">
        <div class="profile-section text-center">
            <img src="images/2.png" class="rounded-circle profile-img shadow" alt="Profile">
        </div>
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <h5>Dashboard</h5>
                <li class="nav-item">
                    <a href="Welcome.php" class="nav-link active" aria-label="Personal Profile">
                        <i class="bi bi-person me-2"></i><span>Personal Profile</span>
                        <span class="nav-label">Personal Profile</span>
                    </a>
                </li>

                <h5>Academics</h5>
                <li class="nav-item">
                    <a href="course_registration.php" class="nav-link" aria-label="Course Registration">
                        <i class="bi bi-r-circle-fill me-2"></i><span>Course Registration</span>
                        <span class="nav-label">Course Registration</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Time Table">
                        <i class="bi bi-calendar me-2"></i><span>Time Table</span>
                        <span class="nav-label">Time Table</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Academic Requisition">
                        <i class="bi bi-file-text me-2"></i><span>Academic Requisition</span>
                        <span class="nav-label">Academic Requisition</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Gown & Graduation Request">
                        <i class="bi bi-file-text me-2"></i><span>Gown & Graduation Request</span>
                        <span class="nav-label">Gown & Graduation Request</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Clearance Request">
                        <i class="bi bi-file-lock-fill me-2"></i><span>Clearance Request</span>
                        <span class="nav-label">Clearance Request</span>
                    </a>
                </li>

                <h5>Financials</h5>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Fee Statement">
                        <i class="bi bi-cash me-2"></i><span>Fee Statement</span>
                        <span class="nav-label">Fee Statement</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Legacy Statement">
                        <i class="bi bi-cash me-2"></i><span>Legacy Statement</span>
                        <span class="nav-label">Legacy Statement</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Receipts">
                        <i class="bi bi-receipt me-2"></i><span>Receipts</span>
                        <span class="nav-label">Receipts</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Generate Gate Pass">
                        <i class="bi bi-folder2 me-2"></i><span>Generate Gate Pass</span>
                        <span class="nav-label">Generate Gate Pass</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Semester Proforma">
                        <i class="bi bi-folder2 me-2"></i><span>Semester Proforma</span>
                        <span class="nav-label">Semester Proforma</span>
                    </a>
                </li>

                <h5>Accommodation</h5>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Hostel Booking">
                        <i class="bi bi-align-start me-2"></i><span>Hostel Booking</span>
                        <span class="nav-label">Hostel Booking</span>
                    </a>
                </li>

                <h5>Examination</h5>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Exam Card">
                        <i class="bi bi-download me-2"></i><span>Exam Card</span>
                        <span class="nav-label">Exam Card</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Transcript">
                        <i class="bi bi-cloud-arrow-down me-2"></i><span>Transcript</span>
                        <span class="nav-label">Transcript</span>
                    </a>
                </li>

                <h5>Setting</h5>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Change Password">
                        <i class="bi bi-gear me-2"></i><span>Change Password</span>
                        <span class="nav-label">Change Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger" aria-label="Logout">
                        <i class="bi bi-box-arrow-right me-2"></i><span>Logout</span>
                        <span class="nav-label">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- ========== MOBILE MENU COLUMN (md and below) ========== -->
    <div class="mobile-menu-column hidden d-lg-none" id="mobileMenu">
        <div class="sidebar-menu">
            <ul class="nav flex-column">
                <h5>Dashboard</h5>
                <li class="nav-item">
                    <a href="Welcome.php" class="nav-link active" aria-label="Personal Profile">
                        <i class="bi bi-person me-2"></i><span>Personal Profile</span>
                        <span class="nav-label">Personal Profile</span>
                    </a>
                </li>

                <h5>Academics</h5>
                <li class="nav-item">
                    <a href="course_registration.php" class="nav-link" aria-label="Course Registration">
                        <i class="bi bi-r-circle-fill me-2"></i><span>Course Registration</span>
                        <span class="nav-label">Course Registration</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Time Table">
                        <i class="bi bi-calendar me-2"></i><span>Time Table</span>
                        <span class="nav-label">Time Table</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Academic Requisition">
                        <i class="bi bi-file-text me-2"></i><span>Academic Requisition</span>
                        <span class="nav-label">Academic Requisition</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Gown & Graduation Request">
                        <i class="bi bi-file-text me-2"></i><span>Gown & Graduation Request</span>
                        <span class="nav-label">Gown & Graduation Request</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Clearance Request">
                        <i class="bi bi-file-lock-fill me-2"></i><span>Clearance Request</span>
                        <span class="nav-label">Clearance Request</span>
                    </a>
                </li>

                <h5>Financials</h5>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Fee Statement">
                        <i class="bi bi-cash me-2"></i><span>Fee Statement</span>
                        <span class="nav-label">Fee Statement</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Legacy Statement">
                        <i class="bi bi-cash me-2"></i><span>Legacy Statement</span>
                        <span class="nav-label">Legacy Statement</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Receipts">
                        <i class="bi bi-receipt me-2"></i><span>Receipts</span>
                        <span class="nav-label">Receipts</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Generate Gate Pass">
                        <i class="bi bi-folder2 me-2"></i><span>Generate Gate Pass</span>
                        <span class="nav-label">Generate Gate Pass</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Semester Proforma">
                        <i class="bi bi-folder2 me-2"></i><span>Semester Proforma</span>
                        <span class="nav-label">Semester Proforma</span>
                    </a>
                </li>

                <h5>Accommodation</h5>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Hostel Booking">
                        <i class="bi bi-align-start me-2"></i><span>Hostel Booking</span>
                        <span class="nav-label">Hostel Booking</span>
                    </a>
                </li>

                <h5>Examination</h5>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Exam Card">
                        <i class="bi bi-download me-2"></i><span>Exam Card</span>
                        <span class="nav-label">Exam Card</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Transcript">
                        <i class="bi bi-cloud-arrow-down me-2"></i><span>Transcript</span>
                        <span class="nav-label">Transcript</span>
                    </a>
                </li>

                <h5>Setting</h5>
                <li class="nav-item">
                    <a href="#" class="nav-link" aria-label="Change Password">
                        <i class="bi bi-gear me-2"></i><span>Change Password</span>
                        <span class="nav-label">Change Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link text-danger" aria-label="Logout">
                        <i class="bi bi-box-arrow-right me-2"></i><span>Logout</span>
                        <span class="nav-label">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- ========== TOP NAVBAR ========== -->
    <nav class="top-navbar">
        <div class="container-fluid d-flex align-items-center h-100">
            <!-- Hamburger Button (visible on lg-down) -->
            <button class="btn text-white d-lg-none" id="toggleMobileMenu" style="font-size: 24px; padding: 0.5rem;">
                <i class="bi bi-list"></i>
            </button>

            <!-- Hamburger for desktop (lg+) - optional toggle -->
            <button class="btn text-white d-none d-lg-block" id="toggleSidebarDesktop" style="font-size: 24px; padding: 0.5rem;">
                <i class="bi bi-list"></i>
            </button>

            <!-- Spacer -->
            <div class="flex-grow-1"></div>

            <!-- Search Bar (hidden on md and below) -->
            <form class="d-none d-lg-flex search-form me-3">
                <input class="form-control rounded-pill px-4" 
                       type="search" 
                       placeholder="Search..." 
                       aria-label="Search"
                       style="width: 280px; background-color: #495057; border: none; color: white;">
            </form>

            <!-- User Avatar (mobile: just avatar) -->
            <div class="d-lg-none">
                <img src="images/1.png" 
                     class="rounded-circle profile-img-small" 
                     alt="User">
            </div>

            <!-- Desktop: Avatar + Name + Dropdown -->
            <div class="dropdown d-none d-lg-block">
                <a class="dropdown-toggle d-flex align-items-center text-white text-decoration-none" 
                   href="#" 
                   role="button" 
                   data-bs-toggle="dropdown" 
                   aria-expanded="false">
                    <img src="images/1.png" 
                         class="rounded-circle profile-img-small me-2" 
                         alt="User">
                    <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="welcome.php">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ========== MAIN CONTENT ========== -->
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
            <h2>Dashboard</h2>    
        </div>

        <!-- Basic Information Card -->
        <div class="card mb-3 mb-md-4">
            <div class="card-header card-header-theme"><h5 class="mb-0">Basic Information</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-lg-4">
                        <strong class="d-block text-muted small">Reg. No</strong>
                        <span><?php echo htmlspecialchars($user['reg_no'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <strong class="d-block text-muted small">Name</strong>
                        <span><?php echo htmlspecialchars($full_name); ?></span>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <strong class="d-block text-muted small">Email</strong>
                        <span class="text-break"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <strong class="d-block text-muted small">Gender</strong>
                        <span><?php echo htmlspecialchars(ucfirst($user['gender'] ?? 'N/A')); ?></span>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <strong class="d-block text-muted small">Date of Birth</strong>
                        <span><?php echo $user['dob'] ? date('d/m/Y', strtotime($user['dob'])) : 'N/A'; ?></span>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <strong class="d-block text-muted small">Campus</strong>
                        <span><?php echo htmlspecialchars($user['campus'] ?? 'MAIN'); ?></span>
                    </div>
                    <div class="col-12">
                        <strong class="d-block text-muted small">Address</strong>
                        <span><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 g-md-4 mb-3 mb-md-4">
            <!-- Academic Information -->
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header card-header-theme"><h5 class="mb-0">Academic Information</h5></div>
                    <div class="card-body">
                        <p><strong>Current Programme:</strong><br class="d-md-none"> 
                        <?php echo htmlspecialchars($user['programme'] ?? 'N/A'); ?></p>
                        <p><strong>Attempted Units:</strong> <?php echo $user['attempted_units'] ?? 0; ?></p>
                        <p class="mb-0"><strong>Registered Units:</strong> <?php echo $user['registered_units'] ?? 0; ?></p>
                    </div>
                </div>
            </div>

            <!-- Fee Payment -->
            <div class="col-12 col-md-6">
                <div class="card h-100">
                    <div class="card-header card-header-theme"><h5 class="mb-0">Fee Payment</h5></div>
                    <div class="card-body text-center d-flex flex-column justify-content-center">
                        <button class="btn btn-success btn-lg mb-3 w-100">Make Payment</button>
                        <a href="#" class="btn btn-outline-primary w-100">Already Paid?</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Documents Section -->
        <div class="card mb-3 mb-md-5">
            <div class="card-header"><h5 class="mb-0">Important Documents</h5></div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th class="d-none d-md-table-cell" style="width: 5%;">#</th>
                                <th style="width: 50%;">File Name</th>
                                <th class="d-none d-lg-table-cell" style="width: 25%;">Remarks</th>
                                <th style="width: 10%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr style=" height: 70px; vertical-align: middle;">
                                <td class="d-none d-md-table-cell">1</td>
                                <td>
                                    <div class="d-md-none small text-muted mb-1">#1</div>
                                    Student-handbook-August-2024.docx
                                </td>
                                <td class="d-none d-lg-table-cell"></td>
                                <td>
                                    <a href="https://www.chuka.ac.ke/storage/2024/08/Student-handbook-August-2024.docx.pdf" 
                                       target="_blank" 
                                       class="btn btn-view-doc btn-sm text-white">
                                        <i class="bi bi-eye me-1 d-none d-sm-inline"></i>View Document
                                    </a>
                                </td>
                            </tr>
                            <tr style=" height: 70px; vertical-align: middle;">
                                <td class="d-none d-md-table-cell">2</td>
                                <td>
                                    <div class="d-md-none small text-muted mb-1">#2</div>
                                    Certificate Collection Clearance form
                                </td>
                                <td class="d-none d-lg-table-cell"></td>
                                <td>
                                    <a href="https://www.chuka.ac.ke/storage/2022/04/CLEARANCE-FORM-1.pdf" 
                                       target="_blank" 
                                       class="btn btn-view-doc btn-sm text-white">
                                        <i class="bi bi-eye me-1 d-none d-sm-inline"></i>View Document
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ========== FOOTER ========== -->
    <footer class="footer text-center" id="footer">
        <div class="container">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> Designed by
                <a href="https://bechimp-rose.vercel.app/" target="_blank" rel="noopener noreferrer">
                    Berryl Wasonga
                </a>
            </p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ========== DESKTOP SIDEBAR COLLAPSE TOGGLE ==========
        const toggleSidebarBtn = document.getElementById('toggleSidebarDesktop');
        const sidebarDesktop = document.querySelector('.sidebar-desktop');

        if (toggleSidebarBtn && sidebarDesktop) {
            // Load collapsed state from localStorage
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                document.body.classList.add('sidebar-collapsed');
                sidebarDesktop.classList.add('sidebar-collapsed');
            }

            toggleSidebarBtn.addEventListener('click', function() {
                // Toggle on both body and sidebar
                document.body.classList.toggle('sidebar-collapsed');
                sidebarDesktop.classList.toggle('sidebar-collapsed');
                
                // Save state to localStorage
                const collapsed = document.body.classList.contains('sidebar-collapsed');
                localStorage.setItem('sidebarCollapsed', collapsed);
            });
        }

        // ========== MOBILE MENU TOGGLE ==========
        const toggleMobileBtn = document.getElementById('toggleMobileMenu');
        const mobileMenu = document.getElementById('mobileMenu');
        const mainContent = document.getElementById('mainContent');
        const footer = document.getElementById('footer');

        if (toggleMobileBtn) {
            toggleMobileBtn.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
                
                // On tablet/landscape, shift content; on small portrait phones, content goes below
                if (window.innerWidth >= 576) {
                    mainContent.classList.toggle('shifted');
                    footer.classList.toggle('shifted');
                }
            });
        }

        // Handle window resize - hide menu if window becomes large
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                mobileMenu.classList.add('hidden');
                mainContent.classList.remove('shifted');
                footer.classList.remove('shifted');
            }
        });

        // Optional: Close mobile menu when clicking a link on very small screens
        const mobileMenuLinks = mobileMenu.querySelectorAll('.nav-link');
        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth < 576) {
                    mobileMenu.classList.add('hidden');
                }
            });
        });
    </script>
</body>
</html>