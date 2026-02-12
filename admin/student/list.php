<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}


// Handle Delete
if (isset($_POST['delete_student'])) {
    $id = $_POST['student_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $msg = "Student deleted successfully.";
        $msg_type = "success";
    } else {
        $msg = "Error deleting student.";
        $msg_type = "danger";
    }
    $stmt->close();
}

$users = $conn->query("SELECT id, first_name, last_name, email, reg_no, programme, role, status, last_login FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student List - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/main.css" rel="stylesheet"/>
</head>
<body>
    <?php include '../partials/top_navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2>Student Management</h2>
                    <a href="add.php" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>Add New Student</a>
                </div>
            </div>

            <?php if (isset($msg)): ?>
                <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show">
                    <?php echo $msg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Reg No</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name']); ?></td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($u['reg_no']); ?></span></td>
                                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                                    <td><small><?php echo htmlspecialchars($u['programme']); ?></small></td>
                                    <td><span class="badge bg-<?php echo ($u['status']=='active'?'success':'secondary'); ?>"><?php echo ucfirst($u['status']); ?></span></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this student?');">
                                            <input type="hidden" name="delete_student" value="1">
                                            <input type="hidden" name="student_id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
