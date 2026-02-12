<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$message = ''; $error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_faculty'])) {
        $name = trim($_POST["faculty_name"]); $code = trim($_POST["faculty_code"]);
        $stmt = $conn->prepare("INSERT INTO faculties (faculty_name, faculty_code) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $code);
        if ($stmt->execute()) $message = "Faculty added successfully!";
        else $error = "Failed to add faculty.";
        $stmt->close();
    }
    elseif (isset($_POST['update_faculty'])) {
        $id = $_POST["faculty_id"];
        $name = trim($_POST["faculty_name"]);
        $code = trim($_POST["faculty_code"]);
        
        $stmt = $conn->prepare("UPDATE faculties SET faculty_name = ?, faculty_code = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $code, $id);
        if ($stmt->execute()) $message = "Faculty updated successfully!";
        else $error = "Failed to update faculty.";
        $stmt->close();
    }
    elseif (isset($_POST['delete_faculty'])) {
        $id = $_POST["faculty_id"];
        $check = $conn->prepare("SELECT COUNT(*) FROM programs WHERE faculty_id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        if ($check->get_result()->fetch_row()[0] > 0) $error = "Cannot delete faculty with existing courses.";
        else {
            $stmt = $conn->prepare("DELETE FROM faculties WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) $message = "Faculty deleted.";
            else $error = "Failed to delete.";
            $stmt->close();
        }
        $check->close();
    }
}

$faculties = $conn->query("SELECT * FROM faculties ORDER BY faculty_name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Faculties - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/main.css" rel="stylesheet"/>
</head>
<body>
    <?php include '../partials/top_navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="row mb-4"><div class="col-12"><h2>Manage Faculties</h2></div></div>
            <?php if($message) echo "<div class='alert alert-success'>$message</div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">Add Faculty</div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="add_faculty" value="1">
                                <div class="mb-3"><label>Name</label><input type="text" name="faculty_name" class="form-control" required></div>
                                <div class="mb-3"><label>Code</label><input type="text" name="faculty_code" class="form-control" required></div>
                                <button class="btn btn-primary w-100">Add</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">Faculties</div>
                        <div class="card-body p-0">
                            <table class="table table-hover">
                                <thead><tr><th>Name</th><th>Code</th><th>Action</th></tr></thead>
                                <tbody>
                                    <?php foreach ($faculties as $f): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($f['faculty_name']); ?></td>
                                        <td><?php echo htmlspecialchars($f['faculty_code']); ?></td>
                                        <td>
                                            <a href="view.php?id=<?php echo $f['id']; ?>" class="btn btn-sm btn-outline-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <button class="btn btn-sm btn-outline-primary edit-faculty-btn"
                                                data-bs-toggle="modal" data-bs-target="#editFacultyModal"
                                                data-id="<?php echo $f['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($f['faculty_name']); ?>"
                                                data-code="<?php echo htmlspecialchars($f['faculty_code']); ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Delete? This will remove all programs, courses, and units under this faculty.');">
                                                <input type="hidden" name="delete_faculty" value="1">
                                                <input type="hidden" name="faculty_id" value="<?php echo $f['id']; ?>">
                                                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
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
        </div>
    </main>

    <!-- Edit Faculty Modal -->
    <div class="modal fade" id="editFacultyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header"><h5 class="modal-title">Edit Faculty</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="update_faculty" value="1">
                        <input type="hidden" name="faculty_id" id="edit_faculty_id">
                        <div class="mb-3"><label>Name</label><input type="text" name="faculty_name" id="edit_faculty_name" class="form-control" required></div>
                        <div class="mb-3"><label>Code</label><input type="text" name="faculty_code" id="edit_faculty_code" class="form-control" required></div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/main.js"></script>
    <script>
    document.querySelectorAll('.edit-faculty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_faculty_id').value = this.dataset.id;
            document.getElementById('edit_faculty_name').value = this.dataset.name;
            document.getElementById('edit_faculty_code').value = this.dataset.code;
        });
    });
    </script>
</html>
