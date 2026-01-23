<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch Pending Registrations
$stmt = $conn->prepare("
    SELECT cr.*, u.first_name, u.last_name, u.reg_no, cu.unit_code, cu.unit_name, s.semester_name 
    FROM course_registrations cr
    JOIN users u ON cr.user_id = u.id
    JOIN course_units cu ON cr.unit_id = cu.id
    JOIN semesters s ON cr.semester_id = s.id
    WHERE cr.registration_status = 'pending'
    ORDER BY cr.registration_date ASC
");
$stmt->execute();
$pending = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$msg = $_SESSION['message'] ?? '';
$msg_type = $_SESSION['msg_type'] ?? 'info';
unset($_SESSION['message'], $_SESSION['msg_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Registrations - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/main.css" rel="stylesheet"/>
</head>
<body>
    <?php include 'partials/top_navbar.php'; ?>
    <?php include 'partials/sidebar.php'; ?>
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="row mb-4"><div class="col-12"><h2>Course Registrations</h2></div></div>
            
            <?php if($msg): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show">
                    <?php echo $msg; ?>
                    <button class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">Pending Approvals</div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Student</th><th>Unit</th><th>Semester</th><th>Date</th><th>Action</th></tr></thead>
                        <tbody>
                            <?php if (count($pending) > 0): ?>
                                <?php foreach ($pending as $r): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($r['first_name'].' '.$r['last_name']); ?></strong><br>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($r['reg_no']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($r['unit_code']); ?><br><small><?php echo htmlspecialchars($r['unit_name']); ?></small></td>
                                    <td><?php echo htmlspecialchars($r['semester_name']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($r['registration_date'])); ?></td>
                                    <td>
                                        <form action="applications/approve.php" method="post" class="d-inline">
                                            <input type="hidden" name="reg_id" value="<?php echo $r['id']; ?>">
                                            <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i></button>
                                        </form>
                                        <form action="applications/reject.php" method="post" class="d-inline">
                                            <input type="hidden" name="reg_id" value="<?php echo $r['id']; ?>">
                                            <button class="btn btn-sm btn-danger"><i class="bi bi-x-lg"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-4">No pending registrations.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/main.js"></script>
</body>
</html>
