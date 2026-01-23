<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check Active Semester
$sem_query = "SELECT * FROM semesters WHERE status = 'active' LIMIT 1";
$sem_result = $conn->query($sem_query);
$current_semester = $sem_result->fetch_assoc();

$can_generate_pass = false;
$status_message = "";

if ($current_semester) {
    // Check fees
    $fee_stmt = $conn->prepare("SELECT payment_status FROM fee_payments WHERE user_id = ? AND semester_id = ?");
    $fee_stmt->bind_param("ii", $_SESSION['user_id'], $current_semester['id']);
    $fee_stmt->execute();
    $fee_res = $fee_stmt->get_result()->fetch_assoc();
    $fee_stmt->close();

    // Check course registration
    $reg_stmt = $conn->prepare("SELECT COUNT(*) as count FROM course_registrations WHERE user_id = ? AND semester_id = ? AND registration_status = 'approved'");
    $reg_stmt->bind_param("ii", $_SESSION['user_id'], $current_semester['id']);
    $reg_stmt->execute();
    $reg_count = $reg_stmt->get_result()->fetch_assoc()['count'];
    $reg_stmt->close();

    if ($fee_res && ($fee_res['payment_status'] == 'paid' || $fee_res['payment_status'] == 'overpaid')) {
        if ($reg_count > 0) {
            $can_generate_pass = true;
        } else {
            $status_message = "You must have approved course units to generate a gate pass.";
        }
    } else {
        $status_message = "You must clear your fees (100%) to generate a gate pass.";
    }
} else {
    $status_message = "No active semester found.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Pass - Student Dashboard</title>
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
    <style>
        .gate-pass {
            border: 2px dashed #000;
            padding: 20px;
            background: #f8f9fa;
            max-width: 600px;
            margin: 0 auto;
        }
        @media print {
            body * { visibility: hidden; }
            #printablePass, #printablePass * { visibility: visible; }
            #printablePass { position: absolute; top: 0; left: 0; width: 100%; border: 2px solid #000; }
        }
    </style>
</head>
<body>
    <?php 
        include_once 'partials/sidebar.php' ;
        include_once 'partials/top_navbar.php' ;
    ?>
    
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h2>Electronic Gate Pass</h2>
        </div>

        <?php if ($can_generate_pass): ?>
            <div id="printablePass" class="gate-pass text-center">
                <img src="Assets/images/logo.png" style="height: 50px;" class="mb-2">
                <h4>CHUKA UNIVERSITY</h4>
                <h6 class="text-uppercase mb-3">Gate Pass - <?php echo htmlspecialchars($current_semester['semester_name']); ?></h6>
                
                <div class="mb-3">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?php echo urlencode($user['reg_no'] . '-' . date('Ymd')); ?>" alt="QR Code">
                </div>
                
                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($user['fullname']); ?></h5>
                <p class="font-monospace mb-2"><?php echo htmlspecialchars($user['reg_no']); ?></p>
                <div class="badge bg-success fs-6 px-3 py-2 mb-3">CLEARED</div>
                
                <p class="small text-muted mb-0">Valid until: <?php echo date('d M Y', strtotime($current_semester['end_date'])); ?></p>
                <p class="small text-muted">Generated: <?php echo date('d M Y H:i'); ?></p>
            </div>
            
            <div class="text-center mt-4 no-print">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer me-2"></i>Print Gate Pass
                </button>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-circle fs-1 d-block mb-3"></i>
                <h5 class="alert-heading">Gate Pass Unavailable</h5>
                <p class="mb-0"><?php echo $status_message; ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include_once 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
</body>
</html>