<?php
session_start();
require_once '../../config.php';
require_once '../../app/Controllers/Admin/FacultyController.php';
require_once '../../app/Controllers/Admin/ProgramController.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$facultyId = $_GET['id'] ?? null;
if (!$facultyId) {
    header("Location: manage.php");
    exit();
}

$facultyController = new FacultyController();
$programController = new ProgramController();

$message = ''; $error = '';

// Handle Actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_program'])) {
        if ($programController->store(['faculty_id' => $facultyId, 'program_name' => $_POST['program_name'], 'program_code' => $_POST['program_code']])) {
            $message = "Program added successfully!";
        } else {
            $error = "Failed to add program.";
        }
    } elseif (isset($_POST['update_program'])) {
        if ($programController->update($_POST['program_id'], ['program_name' => $_POST['program_name'], 'program_code' => $_POST['program_code']])) {
            $message = "Program updated successfully!";
        } else {
            $error = "Failed to update program.";
        }
    } elseif (isset($_POST['delete_program'])) {
        if ($programController->delete($_POST['program_id'])) {
            $message = "Program deleted successfully!";
        } else {
            $error = "Failed to delete program.";
        }
    } elseif (isset($_POST['assign_staff'])) {
        if ($facultyController->addStaff($facultyId, ['user_id' => $_POST['user_id'], 'role' => $_POST['role']])) {
            $message = "Staff assigned successfully!";
        } else {
            $error = "Failed to assign staff.";
        }
    }
}

$faculty = $facultyController->view($facultyId);
if (!$faculty) {
    header("Location: manage.php");
    exit();
}

// Fetch users for staff assignment dropdown (simplified)
$users = $conn->query("SELECT id, first_name, last_name, email FROM users WHERE role IN ('admin', 'staff', 'lecturer') ORDER BY first_name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Faculty: <?php echo htmlspecialchars($faculty['faculty_name']); ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/main.css" rel="stylesheet"/>
</head>
<body>
    <?php include '../partials/top_navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>
    
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="manage.php">Faculties</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($faculty['faculty_name']); ?></li>
                </ol>
            </nav>

            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2>Faculty: <?php echo htmlspecialchars($faculty['faculty_name']); ?> (<?php echo htmlspecialchars($faculty['faculty_code']); ?>)</h2>
                </div>
            </div>

            <?php if($message) echo "<div class='alert alert-success alert-dismissible fade show'>$message<button class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger alert-dismissible fade show'>$error<button class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>

            <ul class="nav nav-tabs mb-4" id="facultyTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="programs-tab" data-bs-toggle="tab" data-bs-target="#programs" type="button">Programs</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button">Faculty Members</button>
                </li>
            </ul>

            <div class="tab-content" id="facultyTabsContent">
                <!-- Programs Tab -->
                <div class="tab-pane fade show active" id="programs" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">Add Program</div>
                                <div class="card-body">
                                    <form method="post">
                                        <input type="hidden" name="add_program" value="1">
                                        <div class="mb-3"><label class="form-label">Program Name</label><input type="text" name="program_name" class="form-control" placeholder="e.g. Computing Science" required></div>
                                        <div class="mb-3"><label class="form-label">Program Code</label><input type="text" name="program_code" class="form-control" placeholder="e.g. CS-01" required></div>
                                        <button class="btn btn-primary w-100">Add Program</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">Existing Programs</div>
                                <div class="card-body p-0">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Action</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($faculty['programs'] as $p): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($p['program_code']); ?></td>
                                                <td><?php echo htmlspecialchars($p['program_name']); ?></td>
                                                <td>
                                                    <a href="../programs/view.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i> View</a>
                                                    <button class="btn btn-sm btn-outline-primary edit-program-btn" 
                                                        data-bs-toggle="modal" data-bs-target="#editProgramModal"
                                                        data-id="<?php echo $p['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($p['program_name']); ?>"
                                                        data-code="<?php echo htmlspecialchars($p['program_code']); ?>"><i class="bi bi-pencil"></i></button>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Delete Program? All associated courses and units will be removed.');">
                                                        <input type="hidden" name="delete_program" value="1">
                                                        <input type="hidden" name="program_id" value="<?php echo $p['id']; ?>">
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

                <!-- Staff Tab -->
                <div class="tab-pane fade" id="staff" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-success text-white">Assign Staff Role</div>
                                <div class="card-body">
                                    <form method="post">
                                        <input type="hidden" name="assign_staff" value="1">
                                        <div class="mb-3">
                                            <label class="form-label">Select Staff</label>
                                            <select name="user_id" class="form-select" required>
                                                <option value="">-- Select --</option>
                                                <?php foreach ($users as $u): ?>
                                                    <option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['first_name'] . ' ' . $u['last_name'] . ' (' . $u['email'] . ')'); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Role</label>
                                            <select name="role" class="form-select" required>
                                                <option value="Dean">Dean</option>
                                                <option value="Program Coordinator">Program Coordinator</option>
                                                <option value="Lecturer">Lecturer</option>
                                                <option value="Administrator">Administrator</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-success w-100">Assign Role</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="card shadow-sm">
                                <div class="card-header bg-light">Faculty Members</div>
                                <div class="card-body p-0">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light"><tr><th>Name</th><th>Email</th><th>Role</th><th>Action</th></tr></thead>
                                        <tbody>
                                            <?php foreach ($faculty['staff'] as $s): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                                                <td><?php echo htmlspecialchars($s['email']); ?></td>
                                                <td><span class="badge bg-secondary"><?php echo $s['role']; ?></span></td>
                                                <td>
                                                    <form method="post" class="d-inline" onsubmit="return confirm('Remove this member from faculty?');">
                                                        <input type="hidden" name="remove_staff" value="1">
                                                        <input type="hidden" name="staff_id" value="<?php echo $s['id']; ?>">
                                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-person-x"></i></button>
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
            </div>
        </div>
    </main>

    <!-- Edit Program Modal -->
    <div class="modal fade" id="editProgramModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header"><h5 class="modal-title">Edit Program</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="update_program" value="1">
                        <input type="hidden" name="program_id" id="edit_program_id">
                        <div class="mb-3"><label class="form-label">Program Name</label><input type="text" name="program_name" id="edit_program_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Program Code</label><input type="text" name="program_code" id="edit_program_code" class="form-control" required></div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/main.js"></script>
    <script>
    document.querySelectorAll('.edit-program-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_program_id').value = this.dataset.id;
            document.getElementById('edit_program_name').value = this.dataset.name;
            document.getElementById('edit_program_code').value = this.dataset.code;
        });
    });
    </script>
</body>
</html>
