<?php
session_start();

// Only allow logged-in students (role = 'user')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
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

// Build full name safely
$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
if (empty($full_name)) {
    $full_name = $user['email'] ?? 'User';
}

// Fetch documents BEFORE including any partials
$doc_stmt = $conn->prepare("SELECT * FROM student_documents WHERE user_id = ? ORDER BY created_at DESC");
$doc_stmt->bind_param("i", $_SESSION['user_id']);
$doc_stmt->execute();
$documents = $doc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$doc_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- favicon -->
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
</head>
<body>
    <?php 
        include_once 'partials/sidebar.php' ;
        include_once 'partials/top_navbar.php' ;
    ?>
    

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
                        <span><?php echo $user['dob'] ? date('d/m/Y', strtotime($user['dob'])) : 'N/A'; ?></span><!--? : (Ternary) If/Else -->
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
                                <?php if (empty($documents)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <i class="bi bi-folder2-open fs-1 text-muted d-block mb-3"></i>
                                            <span class="text-muted">No documents uploaded for you yet.</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($documents as $idx => $doc): ?>
                                    <tr style=" height: 70px; vertical-align: middle;">
                                        <td class="d-none d-md-table-cell"><?php echo $idx + 1; ?></td>
                                        <td>
                                            <div class="d-md-none small text-muted mb-1">#<?php echo $idx + 1; ?></div>
                                            <?php echo htmlspecialchars($doc['document_name']); ?>
                                            <small class="d-block text-muted d-md-none"><?php echo htmlspecialchars($doc['remarks']); ?></small>
                                        </td>
                                        <td class="d-none d-lg-table-cell"><?php echo htmlspecialchars($doc['remarks'] ?? '-'); ?></td>
                                        <td>
                                            <!-- Assuming file path is stored as 'uploads/documents/filename.ext' relative to root -->
                                            <!-- Current path is students/welcome.php, so we need to go up one level -->
                                            <a href="../<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                               target="_blank" 
                                               class="btn btn-view-doc btn-sm text-white">
                                                <i class="bi bi-eye me-1 d-none d-sm-inline"></i>View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>
    
    <?php
       include_once 'partials/footer.php' ;
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>

</body>
</html>
