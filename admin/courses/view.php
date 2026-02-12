<?php
session_start();
require_once '../../config.php';
require_once '../../app/Controllers/Admin/CourseController.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$courseId = $_GET['id'] ?? null;
if (!$courseId) {
    header("Location: ../faculties/manage.php");
    exit();
}

$courseController = new CourseController();

$message = ''; $error = '';

// Handle Actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_unit'])) {
        if ($courseController->addUnit([
            'course_id' => $courseId,
            'unit_name' => $_POST['unit_name'],
            'unit_code' => $_POST['unit_code'],
            'credit_hours' => $_POST['credit_hours'],
            'year_level_id' => $_POST['year_level_id'],
            'semester_id' => $_POST['semester_id']
        ])) {
            $message = "Unit added and assigned successfully!";
        } else {
            $error = "Failed to add unit.";
        }
    } elseif (isset($_POST['update_unit'])) {
        if ($courseController->updateUnit([
            'unit_id' => $_POST['unit_id'],
            'assignment_id' => $_POST['assignment_id'],
            'unit_name' => $_POST['unit_name'],
            'unit_code' => $_POST['unit_code'],
            'credit_hours' => $_POST['credit_hours'],
            'year_level_id' => $_POST['year_level_id'],
            'semester_id' => $_POST['semester_id']
        ])) {
            $message = "Unit and assignment updated successfully!";
        } else {
            $error = "Failed to update unit.";
        }
    } elseif (isset($_POST['assign_lecturer'])) {
        if ($courseController->assignLecturer([
            'unit_id' => $_POST['unit_id'],
            'lecturer_id' => $_POST['lecturer_id']
        ])) {
            $message = "Lecturer assigned successfully!";
        } else {
            $error = "Failed to assign lecturer.";
        }
    }
}

$course = $courseController->view($courseId);
if (!$course) {
    header("Location: ../faculties/manage.php");
    exit();
}

// Fetch lecturers for assignment
$lecturers = $conn->query("SELECT id, first_name, last_name, email FROM users WHERE role = 'lecturer' ORDER BY first_name ASC")->fetch_all(MYSQLI_ASSOC);
// Fetch semesters for unit assignment
$semesters = $conn->query("SELECT id, semester_name FROM semesters ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Course: <?php echo htmlspecialchars($course['course_name']); ?> - Admin</title>
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
                    <li class="breadcrumb-item"><a href="../faculties/manage.php">Faculties</a></li>
                    <li class="breadcrumb-item"><a href="../faculties/view.php?id=<?php echo $course['faculty_id']; ?>"><?php echo htmlspecialchars($course['faculty_name']); ?></a></li>
                    <li class="breadcrumb-item"><a href="../programs/view.php?id=<?php echo $course['program_id']; ?>"><?php echo htmlspecialchars($course['program_name']); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($course['course_name']); ?></li>
                </ol>
            </nav>

            <div class="row mb-4">
                <div class="col-12">
                    <h2>Course: <?php echo htmlspecialchars($course['course_name']); ?> (<?php echo htmlspecialchars($course['course_code']); ?>)</h2>
                    <span class="badge bg-info text-dark"><?php echo $course['level']; ?></span>
                </div>
            </div>

            <?php if($message) echo "<div class='alert alert-success alert-dismissible fade show'>$message<button class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger alert-dismissible fade show'>$error<button class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">Add Unit</div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="add_unit" value="1">
                                <div class="mb-3"><label class="form-label">Unit Name</label><input type="text" name="unit_name" class="form-control" required></div>
                                <div class="mb-3"><label class="form-label">Unit Code</label><input type="text" name="unit_code" class="form-control" required></div>
                                <div class="mb-3"><label class="form-label">Credits</label><input type="number" name="credit_hours" class="form-control" value="3" required></div>
                                <div class="mb-3">
                                    <label class="form-label">Year Level</label>
                                    <select name="year_level_id" class="form-select" required>
                                        <?php foreach ($course['year_levels'] as $yl): ?>
                                            <option value="<?php echo $yl['id']; ?>">Year <?php echo $yl['year_level']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Semester</label>
                                    <select name="semester_id" class="form-select" required>
                                        <?php foreach ($semesters as $s): ?>
                                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['semester_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button class="btn btn-primary w-100">Add & Assign Unit</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">Course Units / Modules</div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Year/Sem</th><th>Lecturers</th><th>Action</th></tr></thead>
                                <tbody>
                                    <?php foreach ($course['units'] as $u): ?>
                                    <?php 
                                        $unitModel = new Unit();
                                        $assignedLecturers = $unitModel->getAssignments($u['unit_id']);
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($u['unit_code']); ?></td>
                                        <td><?php echo htmlspecialchars($u['unit_name']); ?></td>
                                        <td><small>Year <?php echo $u['year_level']; ?>, Sem <?php echo $u['semester_id']; ?></small></td>
                                        <td>
                                            <?php if (empty($assignedLecturers)): ?>
                                                <span class="text-muted small">Not assigned</span>
                                            <?php else: ?>
                                                <?php foreach ($assignedLecturers as $al): ?>
                                                    <span class="badge bg-secondary mb-1"><?php echo htmlspecialchars($al['first_name'] . ' ' . $al['last_name']); ?></span><br>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-unit-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editUnitModal"
                                                data-id="<?php echo $u['unit_id']; ?>"
                                                data-assignment="<?php echo $u['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($u['unit_name']); ?>"
                                                data-code="<?php echo htmlspecialchars($u['unit_code']); ?>"
                                                data-credits="<?php echo $u['credit_hours']; ?>"
                                                data-year="<?php echo $u['year_level_id']; ?>"
                                                data-semester="<?php echo $u['semester_id']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-success assign-lecturer-btn" 
                                                data-bs-toggle="modal" data-bs-target="#assignLecturerModal"
                                                data-id="<?php echo $u['unit_id']; ?>"
                                                data-name="<?php echo htmlspecialchars($u['unit_name']); ?>">
                                                <i class="bi bi-person-plus"></i>
                                            </button>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Remove unit from course?');">
                                                <input type="hidden" name="remove_unit" value="1">
                                                <input type="hidden" name="assignment_id" value="<?php echo $u['id']; ?>">
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

    <!-- Edit Unit Modal -->
    <div class="modal fade" id="editUnitModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header"><h5 class="modal-title">Edit Unit Details</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="update_unit" value="1">
                        <input type="hidden" name="unit_id" id="edit_unit_id">
                        <input type="hidden" name="assignment_id" id="edit_assignment_id">
                        <div class="mb-3"><label class="form-label">Unit Name</label><input type="text" name="unit_name" id="edit_unit_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Unit Code</label><input type="text" name="unit_code" id="edit_unit_code" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Credits</label><input type="number" name="credit_hours" id="edit_credit_hours" class="form-control" required></div>
                        <div class="mb-3">
                            <label class="form-label">Year Level</label>
                            <select name="year_level_id" id="edit_year_level_id" class="form-select" required>
                                <?php foreach ($course['year_levels'] as $yl): ?>
                                    <option value="<?php echo $yl['id']; ?>">Year <?php echo $yl['year_level']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Semester</label>
                            <select name="semester_id" id="edit_semester_id" class="form-select" required>
                                <?php foreach ($semesters as $s): ?>
                                    <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['semester_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Lecturer Modal -->
    <div class="modal fade" id="assignLecturerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header"><h5 class="modal-title">Assign Lecturer: <span id="modal_unit_name"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="assign_lecturer" value="1">
                        <input type="hidden" name="unit_id" id="assign_unit_id">
                        <div class="mb-3">
                            <label class="form-label">Select Lecturer</label>
                            <select name="lecturer_id" class="form-select" required>
                                <option value="">-- Select --</option>
                                <?php foreach ($lecturers as $l): ?>
                                    <option value="<?php echo $l['id']; ?>"><?php echo htmlspecialchars($l['first_name'] . ' ' . $l['last_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-success">Assign</button></div>
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
            document.getElementById('edit_assignment_id').value = this.dataset.assignment;
            document.getElementById('edit_unit_name').value = this.dataset.name;
            document.getElementById('edit_unit_code').value = this.dataset.code;
            document.getElementById('edit_credit_hours').value = this.dataset.credits;
            document.getElementById('edit_year_level_id').value = this.dataset.year;
            document.getElementById('edit_semester_id').value = this.dataset.semester;
        });
    });

    document.querySelectorAll('.assign-lecturer-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('assign_unit_id').value = this.dataset.id;
            document.getElementById('modal_unit_name').textContent = this.dataset.name;
        });
    });
    </script>
</body>
</html>
