<?php
session_start();

// Only allow logged-in students (role = 'user')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
include '../config.php';

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get fee payment details joined with semester info
$query = "SELECT fp.*, s.semester_name, ay.year_name 
          FROM fee_payments fp 
          JOIN semesters s ON fp.semester_id = s.id 
          JOIN academic_years ay ON s.academic_year_id = ay.id 
          WHERE fp.user_id = ? 
          ORDER BY s.start_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$fee_result = $stmt->get_result();
$fee_payments = $fee_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate totals
$total_billed = 0;
$total_paid = 0;
foreach ($fee_payments as $payment) {
    // Assuming amount_required is the billed amount
    $total_billed += $payment['amount_required'];
    $total_paid += $payment['amount_paid'];
}
$total_balance = $total_billed - $total_paid;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Statement - Student Dashboard</title>
    <!-- favicon -->
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
    <style>
        .balance-card {
            transition: transform 0.2s;
        }
        .balance-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <?php 
        include_once 'partials/sidebar.php' ;
        include_once 'partials/top_navbar.php' ;
    ?>
    
    <!-- ========== MAIN CONTENT ========== -->
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>Fee Statement</h2>
                <p class="text-muted mb-0">Track your financial status and transaction history</p>
            </div>
            <button class="btn btn-outline-primary" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>Print Statement
            </button>
        </div>

        <!-- Financial Summary Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card bg-primary text-white h-100 balance-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-title opacity-75 mb-0">Total Billed</h6>
                            </div>
                            <i class="bi bi-file-invoice fs-4"></i>
                        </div>
                        <h3 class="mb-0">KES <?php echo number_format($total_billed, 2); ?></h3>
                        <small class="opacity-75">Cumulative amount</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white h-100 balance-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-title opacity-75 mb-0">Total Paid</h6>
                            </div>
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                        <h3 class="mb-0">KES <?php echo number_format($total_paid, 2); ?></h3>
                        <small class="opacity-75">Cumulative payments</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card <?php echo $total_balance > 0 ? 'bg-danger' : 'bg-info'; ?> text-white h-100 balance-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="card-title opacity-75 mb-0">Outstanding Balance</h6>
                            </div>
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <h3 class="mb-0">KES <?php echo number_format($total_balance, 2); ?></h3>
                        <small class="opacity-75"><?php echo $total_balance > 0 ? 'Please clear your balance' : 'No outstanding dues'; ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Statement Info -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-0">Student Details</h5>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <small class="text-muted d-block">Student Name</small>
                        <strong><?php echo htmlspecialchars($user['fullname']); ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Registration Number</small>
                        <strong><?php echo htmlspecialchars($user['reg_no']); ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Programme</small>
                        <strong><?php echo htmlspecialchars($user['programme'] ?? 'N/A'); ?></strong>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted d-block">Campus</small>
                        <strong><?php echo htmlspecialchars($user['campus']); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card shadow-sm">
            <div class="card-header card-header-theme d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Transaction History</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>Academic Year</th>
                                <th>Semester</th>
                                <th class="text-end">Billed Amount</th>
                                <th class="text-end">Paid Amount</th>
                                <th class="text-end">Balance</th>
                                <th>Status</th>
                                <th>Last Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fee_payments)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                                    <span class="text-muted">No fee records found</span>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($fee_payments as $payment): 
                                    $sem_balance = $payment['amount_required'] - $payment['amount_paid'];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($payment['year_name']); ?></td>
                                    <td><?php echo htmlspecialchars($payment['semester_name']); ?></td>
                                    <td class="text-end fw-medium">KES <?php echo number_format($payment['amount_required'], 2); ?></td>
                                    <td class="text-end text-success fw-medium">KES <?php echo number_format($payment['amount_paid'], 2); ?></td>
                                    <td class="text-end text-danger fw-bold">KES <?php echo number_format($sem_balance, 2); ?></td>
                                    <td>
                                        <?php 
                                            $status_class = match($payment['payment_status']) {
                                                'paid', 'overpaid' => 'bg-success',
                                                'partial' => 'bg-warning text-dark',
                                                default => 'bg-danger'
                                            };
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo ucfirst($payment['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            echo $payment['payment_date'] 
                                                ? date('d M Y', strtotime($payment['payment_date'])) 
                                                : '<span class="text-muted">-</span>'; 
                                        ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <p class="text-muted small">
                <i class="bi bi-info-circle me-1"></i>
                For any discrepancies in your fee statement, please visit the Finance Department.
            </p>
        </div>
    </div>
    
    <?php include_once 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
</body>
</html>