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
        body { background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #343a40; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: white; background-color: #495057; }
        .profile-img { width: 100px; height: 100px; object-fit: cover; border: 3px solid #fff; }
        .card-header { background-color: #28a745; color: white; }
        .btn-view-doc { background-color: #e91e63; border: none; }
        .btn-view-doc:hover { background-color: #c2185b; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar p-3">
            <div class="text-center mb-4">
                <img src="images/1.png" class="rounded-circle profile-img shadow" alt="Profile">
                <h5 class="text-white mt-3"><?php echo htmlspecialchars($full_name); ?></h5>
                <small class="text-muted d-block"><?php echo htmlspecialchars($user['reg_no'] ?? 'N/A'); ?></small>
            </div>
            <hr class="bg-secondary">
            <ul class="nav flex-column">
                <li class="nav-item"><a href="#" class="nav-link active"><i class="bi bi-house-door"></i> Dashboard</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-person"></i> Personal Profile</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-book"></i> Course Registration</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-calendar"></i> Time Table</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-file-text"></i> Academic Requisition</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-cash"></i> Fee Statement</a></li>
                <li class="nav-item"><a href="#" class="nav-link"><i class="bi bi-receipt"></i> Receipts</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Dashboard</h2>
                <div>
                    <input type="search" class="form-control d-inline w-auto" placeholder="Search...">
                </div>
            </div>

            <!-- Basic Information Card -->
            <div class="card mb-4">
                <div class="card-header"><h5>Basic Information</h5></div>
                <div class="card-body">
                    <div class="row text-center text-md-start">
                        <div class="col-md-9">
                            <div class="row g-3">
                                <div class="col-sm-4 fw-bold">Reg. No</div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($user['reg_no'] ?? 'N/A'); ?></div>

                                <div class="col-sm-4 fw-bold">Name</div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($full_name); ?></div>

                                <div class="col-sm-4 fw-bold">Email</div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></div>

                                <div class="col-sm-4 fw-bold">Gender</div>
                                <div class="col-sm-8"><?php echo htmlspecialchars(ucfirst($user['gender'] ?? 'N/A')); ?></div>

                                <div class="col-sm-4 fw-bold">Date of Birth</div>
                                <div class="col-sm-8"><?php echo $user['dob'] ? date('d/m/Y', strtotime($user['dob'])) : 'N/A'; ?></div>

                                <div class="col-sm-4 fw-bold">Campus</div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($user['campus'] ?? 'MAIN'); ?></div>

                                <div class="col-sm-4 fw-bold">Address</div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($user['address'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <!-- Academic Information -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h5>Academic Information</h5></div>
                        <div class="card-body">
                            <p><strong>Current Programme:</strong> <?php echo htmlspecialchars($user['programme'] ?? 'N/A'); ?></p>
                            <p><strong>Attempted Units:</strong> <?php echo $user['attempted_units'] ?? 0; ?></p>
                            <p><strong>Registered Units:</strong> <?php echo $user['registered_units'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Fee Payment -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h5>Fee Payment</h5></div>
                        <div class="card-body text-center">
                            <button class="btn btn-success btn-lg mb-3">Make Payment</button>
                            <br>
                            <a href="#" class="btn btn-outline-primary">Already Paid?</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Important Documents Section -->
            <div class="card mb-5">
                <div class="card-header"><h5>Important Documents</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>File Name</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Student-handbook-August-2024.docx</td>
                                    <td></td>
                                    <td>
                                        <a href="https://www.chuka.ac.ke/storage/2024/08/Student-handbook-August-2024.docx.pdf" 
                                           target="_blank" class="btn btn-view-doc btn-sm text-white">
                                            View Document
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>Certificate Collection Clearance form</td>
                                    <td></td>
                                    <td>
                                        <a href="https://www.chuka.ac.ke/storage/2022/04/CLEARANCE-FORM-1.pdf" 
                                           target="_blank" class="btn btn-view-doc btn-sm text-white">
                                            View Document
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>