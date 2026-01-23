<?php
session_start();
require_once '../../config.php';

// Security: Must be logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$message = '';
$error = '';

// Handle Add Semester
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_semester'])) {
    $semester_name = trim($_POST["semester_name"]);
    $start_date = $_POST["start_date"];
    $end_date = $_POST["end_date"];
    
    $insert_stmt = $conn->prepare("INSERT INTO semesters (semester_name, start_date, end_date, academic_year_id, status) VALUES (?, ?, ?, 1, 'active')");
    $insert_stmt->bind_param("sss", $semester_name, $start_date, $end_date);
    
    if ($insert_stmt->execute()) {
        $message = "Semester added successfully!";
    } else {
        $error = "Failed to add semester.";
    }
    $insert_stmt->close();
}

// Handle Update Semester
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_semester'])) {
    $id = $_POST["semester_id"];
    $name = trim($_POST["semester_name"]);
    $start = $_POST["start_date"];
    $end = $_POST["end_date"];
    $status = $_POST["status"];
    
    $stmt = $conn->prepare("UPDATE semesters SET semester_name = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $start, $end, $status, $id);
    
    if ($stmt->execute()) $message = "Semester updated successfully!";
    else $error = "Failed to update semester.";
    $stmt->close();
}

// Fetch Semesters
$semesters_stmt = $conn->prepare("SELECT * FROM semesters ORDER BY start_date DESC");
$semesters_stmt->execute();
$semesters = $semesters_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$semesters_stmt->close();

$admin_name = $_SESSION['first_name'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Semesters - Admin</title>
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
                <div class="col-12">
                    <h2>Manage Semesters</h2>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">Add Semester</div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="add_semester" value="1">
                                <div class="mb-3">
                                    <label class="form-label">Semester Name</label>
                                    <input type="text" name="semester_name" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">End Date</label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Add Semester</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">Existing Semesters</div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>Name</th><th>Date Range</th><th>Status</th><th>Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($semesters as $sem): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sem['semester_name']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($sem['start_date'])) . ' - ' . date('d M Y', strtotime($sem['end_date'])); ?></td>
                                        <td><span class="badge bg-<?php echo ($sem['status'] == 'active') ? 'success' : 'secondary'; ?>"><?php echo ucfirst($sem['status']); ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-semester-btn"
                                                data-bs-toggle="modal" data-bs-target="#editSemesterModal"
                                                data-id="<?php echo $sem['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($sem['semester_name']); ?>"
                                                data-start="<?php echo $sem['start_date']; ?>"
                                                data-end="<?php echo $sem['end_date']; ?>"
                                                data-status="<?php echo $sem['status']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Edit Semester Modal -->
    <div class="modal fade" id="editSemesterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header"><h5 class="modal-title">Edit Semester</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="update_semester" value="1">
                        <input type="hidden" name="semester_id" id="edit_semester_id">
                        <div class="mb-3"><label>Name</label><input type="text" name="semester_name" id="edit_semester_name" class="form-control" required></div>
                        <div class="mb-3"><label>Start Date</label><input type="date" name="start_date" id="edit_start_date" class="form-control" required></div>
                        <div class="mb-3"><label>End Date</label><input type="date" name="end_date" id="edit_end_date" class="form-control" required></div>
                        <div class="mb-3"><label>Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/main.js"></script>
    <script>
    document.querySelectorAll('.edit-semester-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_semester_id').value = this.dataset.id;
            document.getElementById('edit_semester_name').value = this.dataset.name;
            document.getElementById('edit_start_date').value = this.dataset.start;
            document.getElementById('edit_end_date').value = this.dataset.end;
            document.getElementById('edit_status').value = this.dataset.status;
        });
    });
    </script>
</body>
</html>
