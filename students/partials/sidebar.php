<?php

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
                    <a href="time_table.php" class="nav-link" aria-label="Time Table">
                        <i class="bi bi-calendar me-2"></i><span>Time Table</span>
                        <span class="nav-label">Time Table</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="academic_R.php" class="nav-link" aria-label="Academic Requisition">
                        <i class="bi bi-file-text me-2"></i><span>Academic Requisition</span>
                        <span class="nav-label">Academic Requisition</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="GGR.php" class="nav-link" aria-label="Gown & Graduation Request">
                        <i class="bi bi-file-text me-2"></i><span>Gown & Graduation Request</span>
                        <span class="nav-label">Gown & Graduation Request</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="clearance_request.php" class="nav-link" aria-label="Clearance Request">
                        <i class="bi bi-file-lock-fill me-2"></i><span>Clearance Request</span>
                        <span class="nav-label">Clearance Request</span>
                    </a>
                </li>

                <h5>Financials</h5>
                <li class="nav-item">
                    <a href="fee_stmnt.php" class="nav-link" aria-label="Fee Statement">
                        <i class="bi bi-cash me-2"></i><span>Fee Statement</span>
                        <span class="nav-label">Fee Statement</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="legacy_stmnt.php" class="nav-link" aria-label="Legacy Statement">
                        <i class="bi bi-cash me-2"></i><span>Legacy Statement</span>
                        <span class="nav-label">Legacy Statement</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="receipts.php" class="nav-link" aria-label="Receipts">
                        <i class="bi bi-receipt me-2"></i><span>Receipts</span>
                        <span class="nav-label">Receipts</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="gatepass.php" class="nav-link" aria-label="Generate Gate Pass">
                        <i class="bi bi-folder2 me-2"></i><span>Generate Gate Pass</span>
                        <span class="nav-label">Generate Gate Pass</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="sem_proforma.php" class="nav-link" aria-label="Semester Proforma">
                        <i class="bi bi-folder2 me-2"></i><span>Semester Proforma</span>
                        <span class="nav-label">Semester Proforma</span>
                    </a>
                </li>

                <h5>Accommodation</h5>
                <li class="nav-item">
                    <a href="hostel_booking.php" class="nav-link" aria-label="Hostel Booking">
                        <i class="bi bi-align-start me-2"></i><span>Hostel Booking</span>
                        <span class="nav-label">Hostel Booking</span>
                    </a>
                </li>

                <h5>Examination</h5>
                <li class="nav-item">
                    <a href="examcard.php" class="nav-link" aria-label="Exam Card">
                        <i class="bi bi-download me-2"></i><span>Exam Card</span>
                        <span class="nav-label">Exam Card</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="transcript.php" class="nav-link" aria-label="Transcript">
                        <i class="bi bi-cloud-arrow-down me-2"></i><span>Transcript</span>
                        <span class="nav-label">Transcript</span>
                    </a>
                </li>

                <h5>Setting</h5>
                <li class="nav-item">
                    <a href="change_passwd.php" class="nav-link" aria-label="Change Password">
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