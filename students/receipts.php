<?php
session_start();

// Only allow logged-in students (role = 'user')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

// Fetch user details for context
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch successful payments (receipts)
// Assuming a 'paid' or 'partial' status implies a transaction occurred that can be receipted.
// Ideally, there would be a separate 'transactions' table, but we'll use fee_payments for now.
// Realistically, fee_payments is a summary per semester, not individual receipts. 
// However, based on the schema, this is the best we have.
// We will treat each semester record with > 0 paid as a "receipt" for now, 
// or if there was a separate receipts table we'd use that.
// The schema has `receipt_number` in `fee_payments`, suggesting one receipt per semester record (simplification)
// or that it updates. Let's list records where amount_paid > 0.

$query = "SELECT fp.*, s.semester_name, ay.year_name 
          FROM fee_payments fp 
          JOIN semesters s ON fp.semester_id = s.id 
          JOIN academic_years ay ON s.academic_year_id = ay.id 
          WHERE fp.user_id = ? AND fp.amount_paid > 0
          ORDER BY fp.payment_date DESC, s.start_date DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$receipts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts - Student Dashboard</title>
    <!-- favicon -->
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
</head>
<body>
    <?php 
        include_once 'partials/sidebar.php' ;
        include_once 'partials/top_navbar.php' ;
    ?>
    
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Payment Receipts</h2>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Receipt No.</th>
                                <th>Date</th>
                                <th>Semester</th>
                                <th class="text-end">Amount Paid</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($receipts)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-receipt-cutoff fs-1 text-muted d-block mb-3"></i>
                                    <span class="text-muted">No receipts found</span>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($receipts as $receipt): ?>
                                <tr>
                                    <td>
                                        <span class="font-monospace">
                                            <?php echo htmlspecialchars($receipt['receipt_number'] ?? 'N/A'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                            echo $receipt['payment_date'] 
                                                ? date('d M Y', strtotime($receipt['payment_date'])) 
                                                : '<span class="text-muted">N/A</span>'; 
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($receipt['semester_name']); ?>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($receipt['year_name']); ?></small>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        KES <?php echo number_format($receipt['amount_paid'], 2); ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" onclick="printReceipt('<?php echo $receipt['id']; ?>')">
                                            <i class="bi bi-printer me-1"></i>Print
                                        </button>
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
    
    <!-- Receipt Print Modal (Hidden, populated dynamically or just valid layout) -->
    <!-- For simplicity, we'll just alert for now, or open a new window. 
         A real implementation would open a dedicated print page. -->
    <script>
        function printReceipt(id) {
            // In a real app, this would open a printable view: window.open('print_receipt.php?id=' + id, '_blank');
            // For this demo, we'll simulate it.
            alert('Printing functionality would open a dedicated receipt view for ID: ' + id);
        }
    </script>
    
    <?php include_once 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
</body>
</html>