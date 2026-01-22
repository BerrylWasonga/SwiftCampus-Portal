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
 
    <!-- Sidebar Overlay (Mobile) -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#" data-section="dashboard">
                        <i class="bi bi-speedometer2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-section="students">
                        <i class="bi bi-people"></i>Students
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-section="add-student">
                        <i class="bi bi-person-plus"></i>Add Student
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-section="faculties">
                        <i class="bi bi-building"></i>Manage Faculties
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#" data-section="courses">
                        <i class="bi bi-book"></i>Manage Courses
                    </a>
                </li>
            </ul>
        </nav>
    </aside>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script scr="assets/main.js"></script>
</body>
