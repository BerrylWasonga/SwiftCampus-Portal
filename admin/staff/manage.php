<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config.php';

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    // Prevent deleting self or non-staff (extra safety, though query handles viewing)
    $del = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'staff'");
    $del->bind_param("i", $id);
    if ($del->execute()) {
        $msg = "Staff member deleted successfully.";
        $msg_type = "success";
    } else {
        $msg = "Error deleting staff member.";
        $msg_type = "danger";
    }
}

// Fetch Staff
$stmt = $conn->prepare("SELECT * FROM users WHERE role = 'staff' ORDER BY created_at DESC");
$stmt->execute();
$staff_members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff - Admin</title>
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
                <h2>Teaching Staff</h2>
                <a href="add.php" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Add Staff</a>
            </div>

            <?php if (isset($msg)): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show">
                    <?php echo $msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Reg/Staff No</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($staff_members)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No staff members found.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($staff_members as $idx => $staff): ?>
                                    <tr>
                                        <td><?php echo $idx + 1; ?></td>
                                        <td><?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['email']); ?></td>
                                        <td><?php echo htmlspecialchars($staff['reg_no'] ?? '-'); ?></td>
                                        <td><?php echo date('d M Y', strtotime($staff['created_at'])); ?></td>
                                        <td>
                                            <a href="?delete=<?php echo $staff['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this staff member?');">
                                                <i class="bi bi-trash"></i>
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
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/main.js"></script>
</body>
</html>
