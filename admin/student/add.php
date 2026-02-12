<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$message = ''; $error = '';

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
    $password = $_POST["password"] ?: 'password123';
    
    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "Email already exists.";
    } else {
        // Course & Faculty Verification
        $stmt = $conn->prepare("SELECT c.course_code, c.course_name, c.faculty_id 
                                FROM courses c 
                                WHERE c.id = ?");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        if (!$res) die("Invalid Course Selected");
        
        $course_code = $res['course_code'];
        $faculty_id = $res['faculty_id'];
        // Historically keeping program name in 'programme' column
        $prog_name = $res['course_name']; // Using course name as program name for simplicity

        // Get Sequence Number using Course Code
        // Format: [COURSE]/Y[YEAR]/[ADMISSION_YEAR]/[XXXX]
        $prefix_like = $course_code . '/Y' . $year_level . '/' . $admission_year . '/%';
        $seq_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE reg_no LIKE ?");
        $seq_stmt->bind_param("s", $prefix_like);
        $seq_stmt->execute();
        $seq_res = $seq_stmt->get_result()->fetch_assoc();
        $next_seq = $seq_res['count'] + 1;
        
        $reg_no = $course_code . '/Y' . $year_level . '/' . $admission_year . '/' . str_pad($next_seq, 3, '0', STR_PAD_LEFT);
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, reg_no, password, role, faculty_id, course_id, programme, year_level, admission_year, gender, status) VALUES (?, ?, ?, ?, ?, 'user', ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->bind_param("sssssiisiss", $first_name, $last_name, $email, $reg_no, $hashed_password, $faculty_id, $course_id, $res['course_name'], $year_level, $admission_year, $gender);
        
        if ($stmt->execute()) {
            $message = "Student registered successfully! Registration Number: <strong>$reg_no</strong>";
        } else {
            $error = "Failed to register student: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Student - Admin</title>
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
            <div class="row mb-4"><div class="col-12"><h2><i class="bi bi-person-plus me-2"></i>Register New Student</h2></div></div>
            
            <?php if($message) echo "<div class='alert alert-success alert-dismissible fade show'>$message<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger alert-dismissible fade show'>$error<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>"; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="post" id="registrationForm">
                        <!-- Personal Info -->
                        <div class="form-section">
                            <div class section-title><i class="bi bi-info-circle me-2"></i>Personal Information</div>
                            <div class="row g-3">
                                <div class="col-md-4"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" required></div>
                                <div class="col-md-4"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" required></div>
                                <div class="col-md-4">
                                    <label class="form-label">Gender</label>
                                    <select name="gender" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" required></div>
                                <div class="col-md-6">
                                    <label class="form-label">Password (Default: password123)</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control" placeholder="Leave empty for default">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Info -->
                        <div class="form-section">
                            <div class="section-title"><i class="bi bi-mortarboard me-2"></i>Academic Hierarchy</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Faculty</label>
                                    <select name="faculty_id" id="faculty_id" class="form-select select2" required>
                                        <option value="">Select Faculty...</option>
                                        <?php foreach ($faculties as $f): ?>
                                            <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['faculty_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Course / Program</label>
                                    <select name="course_id" id="course_id" class="form-select select2" required disabled>
                                        <option value="">Select Faculty First...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Year of Study</label>
                                    <select name="year_level" class="form-select" required>
                                        <option value="">Select...</option>
                                        <option value="1">Year 1</option>
                                        <option value="2">Year 2</option>
                                        <option value="3">Year 3</option>
                                        <option value="4">Year 4</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Admission Year</label>
                                    <input type="number" name="admission_year" class="form-control" value="<?php echo date('Y'); ?>" min="2000" max="2099" required>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="dashboard.php" class="btn btn-light px-4 me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary px-5">Register Student</button>
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
        
        $('#togglePassword').click(function() {
            const type = $('#password').attr('type') === 'password' ? 'text' : 'password';
            $('#password').attr('type', type);
            $(this).find('i').toggleClass('bi-eye bi-eye-slash');
        });

        // Cascading Dropdowns
        $('#faculty_id').change(function() {
            const facultyId = $(this).val();
            const courseSelect = $('#course_id');
            
            courseSelect.empty().append('<option value="">Loading...</option>').prop('disabled', true);

            if (facultyId) {
                $.get('ajax_hierarchy.php', { action: 'get_courses_by_faculty', faculty_id: facultyId }, function(data) {
                    courseSelect.empty().append('<option value="">Select Course/Program...</option>');
                    data.forEach(item => {
                        courseSelect.append(`<option value="${item.id}">${item.course_name} (${item.course_code})</option>`);
                    });
                    courseSelect.prop('disabled', false);
                });
            }
        });
    });
    </script>
</body>
</html>
