<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$message = '';
$error = '';

// Handle Actions (Add, Edit, Delete) - Copied logic from admin.php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_unit'])) {
        $unit_name = trim($_POST["unit_name"]);
        $unit_code = trim($_POST["unit_code"]);
        $credit_hours = $_POST["credit_hours"];
        $stmt = $conn->prepare("INSERT INTO course_units (unit_name, unit_code, credit_hours) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $unit_name, $unit_code, $credit_hours);
        if ($stmt->execute()) $message = "Unit added successfully!";
        else $error = "Failed to add unit.";
        $stmt->close();
    }
    elseif (isset($_POST['update_unit'])) {
        $unit_id = $_POST["unit_id"];
        $unit_name = trim($_POST["unit_name"]);
        $unit_code = trim($_POST["unit_code"]);
        $credit_hours = $_POST["credit_hours"];
        $stmt = $conn->prepare("UPDATE course_units SET unit_name = ?, unit_code = ?, credit_hours = ? WHERE id = ?");
        $stmt->bind_param("ssii", $unit_name, $unit_code, $credit_hours, $unit_id);
        if ($stmt->execute()) $message = "Unit updated successfully!";
        else $error = "Failed to update unit.";
        $stmt->close();
    }
    elseif (isset($_POST['delete_unit'])) {
        $unit_id = $_POST["unit_id"];
        // Check usage
        $check = $conn->prepare("SELECT COUNT(*) FROM course_registrations WHERE unit_id = ?");
        $check->bind_param("i", $unit_id);
        $check->execute();
        if ($check->get_result()->fetch_row()[0] > 0) {
            $error = "Cannot delete unit: Active registrations exist.";
        } else {
            $stmt = $conn->prepare("DELETE FROM course_units WHERE id = ?");
            $stmt->bind_param("i", $unit_id);
            if ($stmt->execute()) $message = "Unit deleted successfully!";
            else $error = "Failed to delete unit.";
            $stmt->close();
        }
        $check->close();
    }
}

// Fetch Units
$units_stmt = $conn->prepare("SELECT * FROM course_units ORDER BY unit_code ASC");
$units_stmt->execute();
$units = $units_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$units_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Units - Admin</title>
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
                <div class="col-12"><h2>Manage Course Units</h2></div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show"><?php echo $message; ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show"><?php echo $error; ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">Add Unit</div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="add_unit" value="1">
                                <div class="mb-3"><label>Unit Code</label><input type="text" name="unit_code" class="form-control" required></div>
                                <div class="mb-3"><label>Unit Name</label><input type="text" name="unit_name" class="form-control" required></div>
                                <div class="mb-3"><label>Credits</label><input type="number" name="credit_hours" class="form-control" value="3" required></div>
                                <button type="submit" class="btn btn-primary w-100">Add Unit</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">Existing Units</div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>Code</th><th>Name</th><th>Credits</th><th>Actions</th></tr></thead>
                                <tbody>
                                    <?php foreach ($units as $unit): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($unit['unit_code']); ?></td>
                                        <td><?php echo htmlspecialchars($unit['unit_name']); ?></td>
                                        <td><?php echo $unit['credit_hours']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-unit-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editUnitModal"
                                                data-id="<?php echo $unit['id']; ?>"
                                                data-code="<?php echo htmlspecialchars($unit['unit_code']); ?>"
                                                data-name="<?php echo htmlspecialchars($unit['unit_name']); ?>"
                                                data-credits="<?php echo $unit['credit_hours']; ?>"><i class="bi bi-pencil"></i></button>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Delete this unit?');">
                                                <input type="hidden" name="delete_unit" value="1">
                                                <input type="hidden" name="unit_id" value="<?php echo $unit['id']; ?>">
                                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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

    <!-- Modal -->
    <div class="modal fade" id="editUnitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header"><h5 class="modal-title">Edit Unit</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="update_unit" value="1">
                        <input type="hidden" name="unit_id" id="edit_unit_id">
                        <div class="mb-3"><label>Code</label><input type="text" name="unit_code" id="edit_unit_code" class="form-control" required></div>
                        <div class="mb-3"><label>Name</label><input type="text" name="unit_name" id="edit_unit_name" class="form-control" required></div>
                        <div class="mb-3"><label>Credits</label><input type="text" name="credit_hours" id="edit_credit_hours" class="form-control" required></div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/main.js"></script>
    <script>
    document.querySelectorAll('.edit-unit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_unit_id').value = this.dataset.id;
            document.getElementById('edit_unit_code').value = this.dataset.code;
            document.getElementById('edit_unit_name').value = this.dataset.name;
            document.getElementById('edit_credit_hours').value = this.dataset.credits;
        });
    });
    </script>
</body>
</html>
