<?php
session_start();
include("../config.php");

// Security: Must be logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$full_name = trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
if (empty($full_name)) $full_name = $_SESSION['email'];
$admin_name = $full_name;

// Current year (short)
$current_year = date('y');

// Handle form submissions
$message = '';
$error = '';

// Add Faculty
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_faculty'])) {
    $faculty_name = trim($_POST["faculty_name"]);
    $faculty_code = trim($_POST["faculty_code"]);
    
    if (empty($faculty_name) || empty($faculty_code)) {
        $error = "Faculty name and code are required.";
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM faculties WHERE faculty_code = ?");
        $check_stmt->bind_param("s", $faculty_code);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Faculty code already exists.";
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO faculties (faculty_name, faculty_code) VALUES (?, ?)");
            $insert_stmt->bind_param("ss", $faculty_name, $faculty_code);
            if ($insert_stmt->execute()) {
                $message = "<div class='alert alert-success alert-dismissible fade show'><i class='bi bi-check-circle-fill me-2'></i>Faculty added successfully!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            } else {
                $error = "Failed to add faculty.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// Add Course/Programme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_course'])) {
    $course_name = trim($_POST["course_name"]);
    $course_code = trim($_POST["course_code"]);
    $faculty_id = $_POST["faculty_id"];
    $level = $_POST["level"];
    
    if (empty($course_name) || empty($course_code) || empty($faculty_id) || empty($level)) {
        $error = "All course fields are required.";
    } else {
        $check_stmt = $conn->prepare("SELECT id FROM courses WHERE course_code = ?");
        $check_stmt->bind_param("s", $course_code);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Course code already exists.";
        } else {
            $insert_stmt = $conn->prepare("INSERT INTO courses (course_name, course_code, faculty_id, level) VALUES (?, ?, ?, ?)");
            $insert_stmt->bind_param("ssis", $course_name, $course_code, $faculty_id, $level);
            if ($insert_stmt->execute()) {
                $message = "<div class='alert alert-success alert-dismissible fade show'><i class='bi bi-check-circle-fill me-2'></i>Course added successfully!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            } else {
                $error = "Failed to add course.";
            }
            $insert_stmt->close();
        }
        $check_stmt->close();
    }
}

// Delete Faculty
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_faculty'])) {
    $faculty_id = $_POST["faculty_id"];
    
    // Check if faculty has courses
    $check_stmt = $conn->prepare("SELECT COUNT(*) FROM courses WHERE faculty_id = ?");
    $check_stmt->bind_param("i", $faculty_id);
    $check_stmt->execute();
    $count = $check_stmt->get_result()->fetch_row()[0];
    $check_stmt->close();
    
    if ($count > 0) {
        $error = "Cannot delete faculty with existing courses. Please delete courses first.";
    } else {
        $delete_stmt = $conn->prepare("DELETE FROM faculties WHERE id = ?");
        $delete_stmt->bind_param("i", $faculty_id);
        if ($delete_stmt->execute()) {
            $message = "<div class='alert alert-success alert-dismissible fade show'><i class='bi bi-check-circle-fill me-2'></i>Faculty deleted successfully!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
        } else {
            $error = "Failed to delete faculty.";
        }
        $delete_stmt->close();
    }
}

// Delete Course
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_course'])) {
    $course_id = $_POST["course_id"];
    
    $delete_stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $delete_stmt->bind_param("i", $course_id);
    if ($delete_stmt->execute()) {
        $message = "<div class='alert alert-success alert-dismissible fade show'><i class='bi bi-check-circle-fill me-2'></i>Course deleted successfully!<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
    } else {
        $error = "Failed to delete course.";
    }
    $delete_stmt->close();
}

// Add Student
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $course_id = $_POST["course_id"];
    $gender = $_POST["gender"] ?? '';
    $dob = $_POST["dob"] ?? '';
    $address = trim($_POST["address"] ?? '');
    $campus = $_POST["campus"] ?? 'MAIN';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($course_id)) {
        $error = "Required fields are missing.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check duplicate email
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Email already registered.";
            $check_stmt->close();
        } else {
            // Get course details
            $course_stmt = $conn->prepare("SELECT course_code, course_name FROM courses WHERE id = ?");
            $course_stmt->bind_param("i", $course_id);
            $course_stmt->execute();
            $course_result = $course_stmt->get_result();
            
            if ($course_result->num_rows == 0) {
                $error = "Invalid course selected.";
            } else {
                $course_data = $course_result->fetch_assoc();
                $prefix = $course_data['course_code'];
                $programme = $course_data['course_name'];
                $year_short = $current_year;

                // Auto-generate next Reg. No
                $like_pattern = $prefix . '/%/' . $year_short;
                $max_stmt = $conn->prepare("SELECT reg_no FROM users WHERE reg_no LIKE ? ORDER BY reg_no DESC LIMIT 1");
                $max_stmt->bind_param("s", $like_pattern);
                $max_stmt->execute();
                $max_result = $max_stmt->get_result();

                $next_num = 1;
                if ($max_result->num_rows > 0) {
                    $last_reg = $max_result->fetch_assoc()['reg_no'];
                    preg_match('/\/(\d+)\//', $last_reg, $matches);
                    if (isset($matches[1])) $next_num = intval($matches[1]) + 1;
                }
                $max_stmt->close();

                $seq = str_pad($next_num, 5, '0', STR_PAD_LEFT);
                $reg_no = $prefix . '/' . $seq . '/' . $year_short;

                // Generate random password
                $default_password_plain = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$'), 0, 10);
                $hashed_password = password_hash($default_password_plain, PASSWORD_DEFAULT);

                // Insert student
                $insert_stmt = $conn->prepare("INSERT INTO users 
                    (first_name, last_name, email, password, reg_no, gender, dob, address, campus, programme, course_id, role, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', 'active')");
                $insert_stmt->bind_param("ssssssssssi", $first_name, $last_name, $email, $hashed_password, $reg_no, $gender, $dob, $address, $campus, $programme, $course_id);

                if ($insert_stmt->execute()) {
                    $message = "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                        <i class='bi bi-check-circle-fill me-2'></i><strong>Student added successfully!</strong><br>
                        <div class='mt-2'>
                            <strong>Registration Number:</strong> <span class='badge bg-primary fs-6'>$reg_no</span><br>
                            <strong>Programme:</strong> $programme<br>
                            <strong>Default Password:</strong> <code class='text-danger'>$default_password_plain</code>
                        </div>
                        <small class='d-block mt-2 text-muted'>
                            <i class='bi bi-info-circle'></i> Student has been notified via email (in production). Advise them to change password on first login.
                        </small>
                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                    </div>";
                } else {
                    $error = "Failed to add student.";
                }
                $insert_stmt->close();
            }
            $course_stmt->close();
        }
    }
}

// Fetch stats and users
$total_stmt = $conn->prepare("SELECT COUNT(*) FROM users");
$total_stmt->execute();
$total_users = $total_stmt->get_result()->fetch_row()[0];
$total_stmt->close();

$active_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE status = 'active'");
$active_stmt->execute();
$active_users = $active_stmt->get_result()->fetch_row()[0];
$active_stmt->close();

$users_stmt = $conn->prepare("SELECT id, first_name, last_name, email, reg_no, programme, role, status, last_login FROM users ORDER BY created_at DESC");
$users_stmt->execute();
$users_result = $users_stmt->get_result();
$users = $users_result->fetch_all(MYSQLI_ASSOC);
$users_stmt->close();

// Fetch faculties
$faculties_stmt = $conn->prepare("SELECT * FROM faculties ORDER BY faculty_name ASC");
$faculties_stmt->execute();
$faculties = $faculties_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$faculties_stmt->close();

// Fetch courses with faculty info
$courses_stmt = $conn->prepare("SELECT c.*, f.faculty_name FROM courses c LEFT JOIN faculties f ON c.faculty_id = f.id ORDER BY f.faculty_name, c.level, c.course_name");
$courses_stmt->execute();
$courses = $courses_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$courses_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Chuka University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet"/>
    <link href="assets/main.css" rel="stylesheet"/>
</head>
<body>
    <?php
        require_once 'partials/top_navbar.php';
        require_once 'partials/sidebar.php';

?>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <!-- Alerts -->
            <?php if (!empty($message)): ?>
                <div class="row">
                    <div class="col-12">
                        <?php echo $message; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                            <i class='bi bi-exclamation-triangle-fill me-2'></i><strong>Error:</strong> <?php echo $error; ?>
                            <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Dashboard Section -->
            <div id="section-dashboard" class="content-section">
                <!-- Page Header -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-1">Admin Dashboard</h2>
                        <p class="text-muted">Welcome back, <?php echo htmlspecialchars($admin_name); ?>! Manage your students and system.</p>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row">
                    <div class="col-xl-4 col-md-6 mb-3">
                        <div class="card stat-card">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <div>
                                    <p class="stat-label">Total Students & Staff</p>
                                    <h3 class="stat-value text-primary"><?php echo $total_users; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6 mb-3">
                        <div class="card stat-card success">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div>
                                    <p class="stat-label">Active Users</p>
                                    <h3 class="stat-value text-success"><?php echo $active_users; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4 col-md-6 mb-3">
                        <div class="card stat-card info">
                            <div class="card-body d-flex align-items-center">
                                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                                    <i class="bi bi-person-x-fill"></i>
                                </div>
                                <div>
                                    <p class="stat-label">Inactive Users</p>
                                    <h3 class="stat-value text-info"><?php echo $total_users - $active_users; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-building me-2"></i>Total Faculties</h5>
                                <h2 class="text-primary mb-0"><?php echo count($faculties); ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-book me-2"></i>Total Courses</h5>
                                <h2 class="text-success mb-0"><?php echo count($courses); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students Section -->
            <div id="section-students" class="content-section" style="display:none;">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-1">Student Management</h2>
                        <p class="text-muted">View and manage all registered students</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-people-fill me-2"></i>Registered Users
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Reg. No</th>
                                                <th>Email</th>
                                                <th>Programme</th>
                                                <th>Role</th>
                                                <th>Status</th>
                                                <th>Last Login</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($users) > 0): ?>
                                                <?php foreach ($users as $i => $u): ?>
                                                    <?php 
                                                    $u_name = trim($u['first_name'] . ' ' . $u['last_name']);
                                                    if (empty($u_name)) $u_name = $u['email'];
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $i + 1; ?></td>
                                                        <td><strong><?php echo htmlspecialchars($u_name); ?></strong></td>
                                                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($u['reg_no'] ?? 'N/A'); ?></span></td>
                                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                                        <td><small><?php echo htmlspecialchars($u['programme'] ?? 'N/A'); ?></small></td>
                                                        <td>
                                                            <?php if ($u['role'] == 'admin'): ?>
                                                                <span class="badge bg-danger">Admin</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-info">User</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($u['status'] == 'active'): ?>
                                                                <span class="badge bg-success">Active</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-secondary">Inactive</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><small class="text-muted"><?php echo $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : 'Never'; ?></small></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="8" class="text-center py-5">
                                                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                                        <p class="text-muted mt-3 mb-0">No users found</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Student Section -->
            <div id="section-add-student" class="content-section" style="display:none;">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-1">Add New Student</h2>
                        <p class="text-muted">Register a new student in the system</p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-person-plus-fill me-2"></i>Student Registration Form
                            </div>
                            <div class="card-body">
                                <form method="post" id="studentForm">
                                    <input type="hidden" name="add_student" value="1">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                                            <input type="text" name="first_name" class="form-control" placeholder="Enter first name" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                            <input type="text" name="last_name" class="form-control" placeholder="Enter last name" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" placeholder="student@example.com" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label">Programme <span class="text-danger">*</span></label>
                                            <select name="course_id" id="courseSelect" class="form-select" required>
                                                <option value="">-- Select Programme --</option>
                                                <?php
                                                $current_faculty = '';
                                                $current_level = '';
                                                foreach ($courses as $course):
                                                    $faculty_changed = ($current_faculty != $course['faculty_name']);
                                                    $level_changed = ($current_level != $course['level']);
                                                    
                                                    if ($faculty_changed && $current_faculty != '') {
                                                        echo '</optgroup>';
                                                    }
                                                    
                                                    if ($faculty_changed) {
                                                        echo '<optgroup label="' . htmlspecialchars($course['faculty_name']) . '">';
                                                        $current_faculty = $course['faculty_name'];
                                                        $current_level = '';
                                                    }
                                                ?>
                                                    <option value="<?php echo $course['id']; ?>">
                                                        <?php echo htmlspecialchars($course['course_name']) . ' (' . htmlspecialchars($course['course_code']) . ')'; ?>
                                                    </option>
                                                <?php
                                                    $current_level = $course['level'];
                                                endforeach;
                                                if ($current_faculty != '') echo '</optgroup>';
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Gender</label>
                                            <select name="gender" class="form-select">
                                                <option value="">Select Gender</option>
                                                <option value="Male">Male</option>
                                                <option value="Female">Female</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Date of Birth</label>
                                            <input type="date" name="dob" class="form-control">
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label">Address</label>
                                            <input type="text" name="address" class="form-control" placeholder="Enter physical address">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Campus</label>
                                            <select name="campus" class="form-select">
                                                <option value="MAIN">MAIN Campus</option>
                                                <option value="TOWN">TOWN Campus</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg w-100">
                                            <i class="bi bi-plus-circle me-2"></i>Add Student & Generate Credentials
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Faculties Section -->
            <div id="section-faculties" class="content-section" style="display:none;">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-1">Manage Faculties</h2>
                        <p class="text-muted">Add, view, and manage university faculties</p>
                    </div>
                </div>

                <div class="row">
                    <!-- Add Faculty Form -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-plus-circle me-2"></i>Add New Faculty
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <input type="hidden" name="add_faculty" value="1">
                                    <div class="mb-3">
                                        <label class="form-label">Faculty Name <span class="text-danger">*</span></label>
                                        <input type="text" name="faculty_name" class="form-control" placeholder="e.g., Faculty of Science & Technology" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Faculty Code <span class="text-danger">*</span></label>
                                        <input type="text" name="faculty_code" class="form-control" placeholder="e.g., FST" required>
                                        <small class="text-muted">Unique identifier for the faculty</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-plus-circle me-2"></i>Add Faculty
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Faculties List -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-building me-2"></i>Existing Faculties
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Faculty Name</th>
                                                <th>Code</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($faculties) > 0): ?>
                                                <?php foreach ($faculties as $i => $faculty): ?>
                                                    <tr>
                                                        <td><?php echo $i + 1; ?></td>
                                                        <td><strong><?php echo htmlspecialchars($faculty['faculty_name']); ?></strong></td>
                                                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($faculty['faculty_code']); ?></span></td>
                                                        <td>
                                                            <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this faculty? This action cannot be undone.');">
                                                                <input type="hidden" name="delete_faculty" value="1">
                                                                <input type="hidden" name="faculty_id" value="<?php echo $faculty['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="bi bi-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center py-5">
                                                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                                        <p class="text-muted mt-3 mb-0">No faculties found. Add your first faculty above.</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Courses Section -->
            <div id="section-courses" class="content-section" style="display:none;">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2 class="mb-1">Manage Courses/Programmes</h2>
                        <p class="text-muted">Add, view, and manage university courses and programmes</p>
                    </div>
                </div>

                <div class="row">
                    <!-- Add Course Form -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-plus-circle me-2"></i>Add New Course
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <input type="hidden" name="add_course" value="1">
                                    <div class="mb-3">
                                        <label class="form-label">Course Name <span class="text-danger">*</span></label>
                                        <input type="text" name="course_name" class="form-control" placeholder="e.g., Bachelor of Science (Computer Science)" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Course Code <span class="text-danger">*</span></label>
                                        <input type="text" name="course_code" class="form-control" placeholder="e.g., EB1" required>
                                        <small class="text-muted">Used for registration number generation</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Faculty <span class="text-danger">*</span></label>
                                        <select name="faculty_id" class="form-select" required>
                                            <option value="">-- Select Faculty --</option>
                                            <?php foreach ($faculties as $faculty): ?>
                                                <option value="<?php echo $faculty['id']; ?>">
                                                    <?php echo htmlspecialchars($faculty['faculty_name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Level <span class="text-danger">*</span></label>
                                        <select name="level" class="form-select" required>
                                            <option value="">-- Select Level --</option>
                                            <option value="Certificate">Certificate</option>
                                            <option value="Diploma">Diploma</option>
                                            <option value="Bachelor">Bachelor's Degree</option>
                                            <option value="Masters">Master's Degree</option>
                                            <option value="PhD">PhD</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-plus-circle me-2"></i>Add Course
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Courses List -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <i class="bi bi-book me-2"></i>Existing Courses/Programmes
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Course Name</th>
                                                <th>Code</th>
                                                <th>Faculty</th>
                                                <th>Level</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (count($courses) > 0): ?>
                                                <?php foreach ($courses as $i => $course): ?>
                                                    <tr>
                                                        <td><?php echo $i + 1; ?></td>
                                                        <td><strong><?php echo htmlspecialchars($course['course_name']); ?></strong></td>
                                                        <td><span class="badge bg-success"><?php echo htmlspecialchars($course['course_code']); ?></span></td>
                                                        <td><small><?php echo htmlspecialchars($course['faculty_name'] ?? 'N/A'); ?></small></td>
                                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($course['level']); ?></span></td>
                                                        <td>
                                                            <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this course? This action cannot be undone.');">
                                                                <input type="hidden" name="delete_course" value="1">
                                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="bi bi-trash"></i> Delete
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-5">
                                                        <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                                                        <p class="text-muted mt-3 mb-0">No courses found. Add your first course above.</p>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
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
   
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script scr="assets/main.js"></script>
</body>
</html>