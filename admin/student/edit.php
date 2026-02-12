<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: list.php");
    exit();
}

$message = ''; $error = '';

// Fetch Student Data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    header("Location: list.php");
    exit();
}

$faculties = $conn->query("SELECT id, faculty_name FROM faculties ORDER BY faculty_name ASC")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $gender = $_POST["gender"];
    $faculty_id = $_POST["faculty_id"];
    $course_id = $_POST["course_id"];
    $year_level = (int)$_POST["year_level"];
    $admission_year = (int)$_POST["admission_year"];
    $status = $_POST["status"];
    
    // Check if email already exists for other users
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->bind_param("si", $email, $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "Email already exists.";
    } else {
        // Get Faculty from Course
    $p_stmt = $conn->prepare("SELECT faculty_id, course_name FROM courses WHERE id = ?");
    $p_stmt->bind_param("i", $course_id);
    $p_stmt->execute();
    $res = $p_stmt->get_result()->fetch_assoc();
    
    if (!$res) die("Invalid Course Choice");
    
    $faculty_id = $res['faculty_id'];
    $prog_name = $res['course_name']; // Historically used for program name

    $upd = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, gender=?, faculty_id=?, course_id=?, programme=?, year_level=?, admission_year=?, status=? WHERE id=?");
    $upd->bind_param("ssssiisiiii", $first_name, $last_name, $email, $gender, $faculty_id, $course_id, $prog_name, $year_level, $admission_year, $status, $id);
    
    if ($upd->execute()) {
        $message = "Student updated successfully!";
        // Refresh student data
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $error = "Failed to update student: " . $conn->error;
    }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="../assets/main.css" rel="stylesheet"/>
    <style>
        .form-section { border-bottom: 2px solid #f8f9fa; padding-bottom: 1.5rem; margin-bottom: 1.5rem; }
        .form-section:last-child { border-bottom: none; }
        .section-title { font-size: 1.1rem; color: #0d6efd; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <?php include '../partials/top_navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2><i class="bi bi-pencil-square me-2"></i>Edit Student Profile</h2>
                    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back to Directory</a>
                </div>
            </div>
            
            <?php if($message) echo "<div class='alert alert-success alert-dismissible fade show'>$message<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger alert-dismissible fade show'>$error<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="post" id="editStudentForm">
                        <!-- Account Info -->
                        <div class="form-section">
                            <div class="section-title"><i class="bi bi-shield-lock me-2"></i>Account & Status</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Registration Number (Static)</label>
                                    <input type="text" class="form-control bg-light fw-bold" value="<?php echo htmlspecialchars($student['reg_no']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Enrollment Status</label>
                                    <select name="status" class="form-select" required>
                                        <option value="active" <?php echo ($student['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($student['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        <option value="suspended" <?php echo ($student['status'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Info -->
                        <div class="form-section">
                            <div class="section-title"><i class="bi bi-person me-2"></i>Personal Information</div>
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($student['first_name']); ?>" required></div>
                                <div class="col-md-4"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($student['last_name']); ?>" required></div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="Male" <?php echo ($student['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($student['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($student['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-12"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required></div>
                            </div>
                        </div>

                        <!-- Academic Info -->
                        <div class="form-section">
                            <div class="section-title"><i class="bi bi-mortarboard me-2"></i>Academic Assignment</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Faculty</label>
                                    <select name="faculty_id" id="faculty_id" class="form-select select2" required>
                                        <option value="">Select Faculty...</option>
                                        <?php foreach ($faculties as $f): ?>
                                            <option value="<?php echo $f['id']; ?>" <?php echo ($student['faculty_id'] == $f['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($f['faculty_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Course / Program</label>
                                    <select name="course_id" id="course_id" class="form-select select2" required>
                                        <option value="">Select Faculty First...</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Current Year Level</label>
                                    <select name="year_level" class="form-select" required>
                                        <?php for($i=1; $i<=4; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ($student['year_level'] == $i) ? 'selected' : ''; ?>>Year <?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Admission Year</label>
                                    <input type="number" name="admission_year" class="form-control" value="<?php echo $student['admission_year'] ?: date('Y'); ?>" min="2000" max="2099" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-5 shadow-sm"><i class="bi bi-check2-circle me-2"></i>Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../assets/main.js"></script>
    <script>
    $(document).ready(function() { 
        $('.select2').select2({ width: '100%' }); 

        const currentFaculty = '<?php echo $student['faculty_id']; ?>';
        const currentCourse = '<?php echo $student['course_id']; ?>';

        // Load Courses if faculty exists
        if (currentFaculty) {
            loadCoursesByFaculty(currentFaculty, currentCourse);
        }

        $('#faculty_id').change(function() {
            loadCoursesByFaculty($(this).val());
        });

        function loadCoursesByFaculty(facultyId, selectedId = null) {
            const courseSelect = $('#course_id');
            courseSelect.empty().append('<option value="">Loading...</option>').prop('disabled', true);

            if (facultyId) {
                $.get('ajax_hierarchy.php', { action: 'get_courses_by_faculty', faculty_id: facultyId }, function(data) {
                    courseSelect.empty().append('<option value="">Select Course/Program...</option>');
                    data.forEach(item => {
                        const selected = (selectedId && item.id == selectedId) ? 'selected' : '';
                        courseSelect.append(`<option value="${item.id}" ${selected}>${item.course_name} (${item.course_code})</option>`);
                    });
                    courseSelect.prop('disabled', false);
                });
            }
        }
    });
    </script>
</body>
</html>
