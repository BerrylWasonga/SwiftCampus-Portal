<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$message = ''; $error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_course'])) {
        $name = trim($_POST["course_name"]); 
        $code = trim($_POST["course_code"]);
        $faculty = $_POST["faculty_id"]; 
        $level = $_POST["level"];
        
        $stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, faculty_id, level) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $name, $code, $faculty, $level);
        if ($stmt->execute()) $message = "Course added successfully!";
        else $error = "Failed to add course.";
        $stmt->close();
    }
    elseif (isset($_POST['update_course'])) {
        $id = $_POST["course_id"];
        $name = trim($_POST["course_name"]);
        $code = trim($_POST["course_code"]);
        $faculty = $_POST["faculty_id"];
        $level = $_POST["level"];

        $stmt = $conn->prepare("UPDATE courses SET course_name = ?, course_code = ?, faculty_id = ?, level = ? WHERE id = ?");
        $stmt->bind_param("ssisi", $name, $code, $faculty, $level, $id);
        
        if ($stmt->execute()) $message = "Course updated successfully!";
        else $error = "Failed to update course.";
        $stmt->close();
    }
    elseif (isset($_POST['delete_course'])) {
        $id = $_POST["course_id"];
        $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) $message = "Course deleted successfully!";
        else $error = "Failed to delete course.";
        $stmt->close();
    }
}

// Fetch Courses
$courses = $conn->query("SELECT c.*, f.faculty_name FROM courses c LEFT JOIN faculties f ON c.faculty_id = f.id ORDER BY f.faculty_name, c.level, c.course_name")->fetch_all(MYSQLI_ASSOC);

// Fetch Faculties for dropdown
$faculties = $conn->query("SELECT * FROM faculties ORDER BY faculty_name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Courses - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/main.css" rel="stylesheet"/>
</head>
<body>
    <?php include '../partials/top_navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="row mb-4"><div class="col-12"><h2>Manage Courses</h2></div></div>
            <?php if($message) echo "<div class='alert alert-success'>$message</div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">Add New Course</div>
                        <div class="card-body">
                            <form method="post">
                                <input type="hidden" name="add_course" value="1">
                                <div class="mb-3"><label>Course Name</label><input type="text" name="course_name" class="form-control" required></div>
                                <div class="mb-3"><label>Course Code</label><input type="text" name="course_code" class="form-control" required></div>
                                <div class="mb-3">
                                    <label>Faculty</label>
                                    <select name="faculty_id" class="form-select" required>
                                        <option value="">-- Select --</option>
                                        <?php foreach ($faculties as $f): ?>
                                            <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['faculty_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label>Level</label>
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
                    <div class="card">
                        <div class="card-header">Existing Courses</div>
                        <div class="card-body p-0">
                            <table class="table table-hover">
                                <thead><tr><th>Code</th><th>Name</th><th>Faculty</th><th>Level</th><th>Action</th></tr></thead>
                                <tbody>
                                    <?php foreach ($courses as $c): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($c['course_code']); ?></td>
                                        <td><?php echo htmlspecialchars($c['course_name']); ?></td>
                                        <td><small><?php echo htmlspecialchars($c['faculty_name']); ?></small></td>
                                        <td><span class="badge bg-info"><?php echo $c['level']; ?></span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-course-btn" 
                                                data-bs-toggle="modal" data-bs-target="#editCourseModal"
                                                data-id="<?php echo $c['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($c['course_name']); ?>"
                                                data-code="<?php echo htmlspecialchars($c['course_code']); ?>"
                                                data-faculty="<?php echo $c['faculty_id']; ?>"
                                                data-level="<?php echo $c['level']; ?>">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <form method="post" class="d-inline" onsubmit="return confirm('Delete?');">
                                                <input type="hidden" name="delete_course" value="1">
                                                <input type="hidden" name="course_id" value="<?php echo $c['id']; ?>">
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
    
    <!-- Edit Course Modal -->
    <div class="modal fade" id="editCourseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="post">
                    <div class="modal-header"><h5 class="modal-title">Edit Course</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <input type="hidden" name="update_course" value="1">
                        <input type="hidden" name="course_id" id="edit_course_id">
                        <div class="mb-3"><label>Course Name</label><input type="text" name="course_name" id="edit_course_name" class="form-control" required></div>
                        <div class="mb-3"><label>Course Code</label><input type="text" name="course_code" id="edit_course_code" class="form-control" required></div>
                        <div class="mb-3">
                            <label>Faculty</label>
                            <select name="faculty_id" id="edit_faculty_id" class="form-select" required>
                                <?php foreach ($faculties as $f): ?>
                                    <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['faculty_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Level</label>
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
            document.getElementById('edit_faculty_id').value = this.dataset.faculty;
            document.getElementById('edit_level').value = this.dataset.level;
        });
    });
    </script>
</html>
