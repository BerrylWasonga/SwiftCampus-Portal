<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get active semester
$query = "SELECT s.*, ay.year_name 
          FROM semesters s 
          JOIN academic_years ay ON s.academic_year_id = ay.id 
          WHERE s.status = 'active' LIMIT 1";
$sem_result = $conn->query($query);
$current_semester = $sem_result->fetch_assoc();

// Get fee requirements for this student/semester (mock logic as per schema structure)
// We look at the fee_payments table to see if a record was generated effectively "invoicing" them
$invoice = null;
if ($current_semester) {
    $stmt = $conn->prepare("SELECT * FROM fee_payments WHERE user_id = ? AND semester_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $current_semester['id']);
    $stmt->execute();
    $invoice = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semester Proforma - Student Dashboard</title>
    <!-- favicon -->
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
    <style>
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            color: #555;
            background: #fff;
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
            <h2>Semester Proforma Invoice</h2>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>Print Invoice
            </button>
        </div>

        <?php if (!$current_semester): ?>
            <div class="alert alert-warning">No active semester found.</div>
        <?php elseif (!$invoice): ?>
            <div class="alert alert-info">
                No fee invoice has been generated for you for the current semester (<?php echo htmlspecialchars($current_semester['semester_name']); ?>). 
                Please contact the finance office.
            </div>
        <?php else: ?>
            <div class="invoice-box">
                <div class="row mb-5">
                    <div class="col-8">
                        <img src="Assets/images/logo.png" alt="Logo" style="height: 60px;">
                        <h4 class="mt-2 text-primary">Chuka University</h4>
                    </div>
                    <div class="col-4 text-end">
                        <h5 class="fw-bold">PROFORMA INVOICE</h5>
                        <p class="mb-0 small">Date: <?php echo date('d M Y'); ?></p>
                        <p class="mb-0 small">Sem: <?php echo htmlspecialchars($current_semester['semester_code']); ?></p>
                    </div>
                </div>

                <div class="row mb-4 border-bottom pb-3">
                    <div class="col-6">
                        <h6 class="fw-bold text-uppercase">Bill To:</h6>
                        <p class="mb-1"><?php echo htmlspecialchars($user['fullname']); ?></p>
                        <p class="mb-1"><?php echo htmlspecialchars($user['reg_no']); ?></p>
                        <p class="mb-0"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <h6 class="fw-bold text-uppercase">Details:</h6>
                        <p class="mb-1">Programme: <?php echo htmlspecialchars($user['programme']); ?></p>
                        <p class="mb-0">Year: <?php echo htmlspecialchars($current_semester['year_name']); ?></p>
                    </div>
                </div>

                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount (KES)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Breakdown would normally be in a separate table, but we only have total required -->
                        <tr>
                            <td>Tuition Fees & Administrative Charges</td>
                            <td class="text-end"><?php echo number_format($invoice['amount_required'], 2); ?></td>
                        </tr>
                        <tr class="fw-bold">
                            <td>TOTAL PAYABLE</td>
                            <td class="text-end"><?php echo number_format($invoice['amount_required'], 2); ?></td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-5 text-muted small">
                    <p class="fw-bold">Payment Instructions:</p>
                    <p>Please pay to the university bank accounts or via M-Pesa Paybill 123456. Use your Registration Number as the Account Number.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include_once 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
</body>
</html>