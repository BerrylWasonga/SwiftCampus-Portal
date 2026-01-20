<?php
session_start();

// Only allow logged-in students (role = 'user')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Include database connection
include '../config.php';

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
    <!-- favicon -->
    <link rel="apple-touch-icon" href="images\2.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
    <style>
        
    </style>
</head>
<body>
    <!-- ========== DESKTOP SIDEBAR (lg and up) ========== -->
    <div class="sidebar-desktop">
        <div class="profile-section text-center">
            <img src="Assets/images/2.png" class="rounded-circle profile-img shadow" alt="Profile">
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
                <img src="Assets/images/1.png" 
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
                    <img src="Assets/images/1.png" 
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