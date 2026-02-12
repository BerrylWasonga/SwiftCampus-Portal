<?php
session_start();
require_once '../../config.php';
require_once '../../app/Controllers/Admin/ProgramController.php';
require_once '../../app/Controllers/Admin/CourseController.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$programId = $_GET['id'] ?? null;
if (!$programId) {
    header("Location: ../faculties/manage.php");
    exit();
}

$programController = new ProgramController();
$courseController = new CourseController();

$message = ''; $error = '';

// Handle Actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_course'])) {
        if ($courseController->store([
            'program_id' => $programId,
            'course_name' => $_POST['course_name'],
            'course_code' => $_POST['course_code'],
            'level' => $_POST['level']
        ])) {
            $message = "Course added successfully!";
        } else {
            $error = "Failed to add course.";
        }
    } elseif (isset($_POST['update_course'])) {
        if ($courseController->update($_POST['course_id'], [
            'course_name' => $_POST['course_name'],
            'course_code' => $_POST['course_code'],
            'level' => $_POST['level']
        ])) {
            $message = "Course updated successfully!";
        } else {
            $error = "Failed to update course.";
        }
    } elseif (isset($_POST['delete_course'])) {
        if ($courseController->delete($_POST['course_id'])) {
            $message = "Course deleted successfully!";
        } else {
            $error = "Failed to delete course.";
        }
    }
}

$program = $programController->view($programId);
if (!$program) {
    header("Location: ../faculties/manage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Program: <?php echo htmlspecialchars($program['program_name']); ?> - Admin</title>
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
                    <li class="breadcrumb-item"><a href="../faculties/view.php?id=<?php echo $program['faculty_id']; ?>"><?php echo htmlspecialchars($program['faculty_name']); ?></a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($program['program_name']); ?></li>
                </ol>
            </nav>

            <div class="row mb-4">
                <div class="col-12">
                    <h2>Program: <?php echo htmlspecialchars($program['program_name']); ?> (<?php echo htmlspecialchars($program['program_code']); ?>)</h2>
                </div>
            </div>

            <?php if($message) echo "<div class='alert alert-success alert-dismissible fade show'>$message<button class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger alert-dismissible fade show'>$error<button class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">Add Course</div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="add_course" value="1">
                                <div class="mb-3"><label class="form-label">Course Name</label><input type="text" name="course_name" class="form-control" required></div>
                                <div class="mb-3"><label class="form-label">Course Code</label><input type="text" name="course_code" class="form-control" required></div>
                                <div class="mb-3">
                                    <label class="form-label">Level</label>
                                    <select name="level" class="form-select" required>
                                        <option value="Certificate">Certificate</option>
                                        <option value="Diploma">Diploma</option>
                                        <option value="Bachelor">Bachelor</option>
                                        <option value="Masters">Masters</option>
                                        <option value="PhD">PhD</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary w-100">Add Course</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">Courses under this Program</div>
                        <div class="card-body p-0">
                            <table class="table table-hover mb-0">
                                <thead class="table-light"><tr><th>Code</th><th>Name</th><th>Level</th><th>Action</th></tr></thead>
                                <tbody>
                                    <?php foreach ($program['courses'] as $c): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($c['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars($c['course_name']); ?></td>
                                        <td><span class="badge bg-info text-dark"><?php echo $c['level']; ?></span></td>
                                        <td>
                                            <a href="../courses/view.php?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i> View</a>
                                            <button class="btn btn-sm btn-outline-primary edit-course-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editCourseModal"
                                                data-id="<?php echo $c['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($c['course_name']); ?>"
                                                data-code="<?php echo htmlspecialchars($c['course_code']); ?>"
                                                data-level="<?php echo $c['level']; ?>"><i class="bi bi-pencil"></i></button>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Delete Course? All associated units will be removed.');">
                                                <input type="hidden" name="delete_course" value="1">
                                                <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
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

    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header"><h5 class="modal-title">Edit Course</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="update_course" value="1">
                        <input type="hidden" name="course_id" id="edit_course_id">
                        <div class="mb-3"><label class="form-label">Course Name</label><input type="text" name="course_name" id="edit_course_name" class="form-control" required></div>
                        <div class="mb-3"><label class="form-label">Course Code</label><input type="text" name="course_code" id="edit_course_code" class="form-control" required></div>
                        <div class="mb-3">
                            <label class="form-label">Level</label>
                            <select name="level" id="edit_level" class="form-select" required>
                                <option value="Certificate">Certificate</option>
                                <option value="Diploma">Diploma</option>
                                <option value="Bachelor">Bachelor</option>
                                <option value="Masters">Masters</option>
                                <option value="PhD">PhD</option>
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
    document.querySelectorAll('.edit-course-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_course_id').value = this.dataset.id;
            document.getElementById('edit_course_name').value = this.dataset.name;
            document.getElementById('edit_course_code').value = this.dataset.code;
            document.getElementById('edit_level').value = this.dataset.level;
        });
    });
    </script>
</body>
</html>
