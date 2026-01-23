<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config.php';

$search_reg = '';
$where_clause = "WHERE 1=1";
$params = [];
$types = "";

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_reg = trim($_GET['search']);
    $where_clause .= " AND u.reg_no LIKE ?";
    $params[] = "%$search_reg%";
    $types .= "s";
}

// Fetch Payments with Student Info and Semester Info
$query = "SELECT fp.*, u.first_name, u.last_name, u.reg_no, s.semester_name 
          FROM fee_payments fp 
          JOIN users u ON fp.user_id = u.id 
          JOIN semesters s ON fp.semester_id = s.id 
          $where_clause 
          ORDER BY fp.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Finance - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/main.css" rel="stylesheet">
</head>
<body>
    <?php include_once '../partials/top_navbar.php'; ?>
    <?php include_once '../partials/sidebar.php'; ?>
    
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Student Finance Records</h2>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search by Reg. No" value="<?php echo htmlspecialchars($search_reg); ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Search</button>
                        </div>
                        <div class="col-md-2">
                            <a href="payments.php" class="btn btn-outline-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Semester</th>
                                    <th>Required</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($payments)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No payment records found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($payments as $index => $pay): ?>
                                    <?php 
                                        $balance = $pay['amount_required'] - $pay['amount_paid'];
                                        $status_class = match($pay['payment_status']) {
                                            'paid' => 'success',
                                            'overpaid' => 'primary',
                                            'partial' => 'warning',
                                            'unpaid' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($pay['first_name'] . ' ' . $pay['last_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($pay['reg_no']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($pay['semester_name']); ?></td>
                                        <td>KES <?php echo number_format($pay['amount_required'], 2); ?></td>
                                        <td class="text-success fw-bold">KES <?php echo number_format($pay['amount_paid'], 2); ?></td>
                                        <td class="text-danger fw-bold">KES <?php echo number_format($balance, 2); ?></td>
                                        <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($pay['payment_status']); ?></span></td>
                                        <td><?php echo $pay['created_at'] ? date('d M Y', strtotime($pay['created_at'])) : '-'; ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/main.js"></script>
</body>
</html>
