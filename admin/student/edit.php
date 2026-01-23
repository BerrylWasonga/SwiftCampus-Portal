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

// Fetch Courses for Dropdown
$courses = $conn->query("SELECT c.*, f.faculty_name FROM courses c LEFT JOIN faculties f ON c.faculty_id = f.id ORDER BY f.faculty_name, c.level, c.course_name")->fetch_all(MYSQLI_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $course_id = $_POST["course_id"];
    $status = $_POST["status"];
    
    // Check if email exists for other users
    $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $check->bind_param("si", $email, $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "Email already exists.";
    } else {
        // Update Course Name (Programme) if course changed
        $prog = $student['programme'];
        if ($course_id != $student['course_id']) {
            $c_stmt = $conn->prepare("SELECT course_name FROM courses WHERE id = ?");
            $c_stmt->bind_param("i", $course_id);
            $c_stmt->execute();
            $prog = $c_stmt->get_result()->fetch_assoc()['course_name'];
            $c_stmt->close();
        }

        $upd = $conn->prepare("UPDATE users SET first_name=?, last_name=?, email=?, course_id=?, programme=?, status=? WHERE id=?");
        $upd->bind_param("ssssssi", $first_name, $last_name, $email, $course_id, $prog, $status, $id);
        
        if ($upd->execute()) {
            $message = "Student updated successfully!";
            // Refresh data
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $student = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            $error = "Failed to update student.";
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
</head>
<body>
    <?php include '../partials/top_navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <h2>Edit Student</h2>
                    <a href="list.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back to List</a>
                </div>
            </div>
            
            <?php if($message) echo "<div class='alert alert-success'>$message</div>"; ?>
            <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

            <div class="card">
                <div class="card-body">
                    <form method="post">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Reg No (ReadOnly)</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($student['reg_no']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?php echo ($student['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($student['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="suspended" <?php echo ($student['status'] == 'suspended') ? 'selected' : ''; ?>>Suspended</option>
                                </select>
                            </div>
                            <div class="col-md-6"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($student['first_name']); ?>" required></div>
                            <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($student['last_name']); ?>" required></div>
                            <div class="col-md-12"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email']); ?>" required></div>
                            <div class="col-md-12">
                                <label class="form-label">Programme</label>
                                <select name="course_id" class="form-select select2" required>
                                    <option value="">Select...</option>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?php echo $c['id']; ?>" <?php echo ($student['course_id'] == $c['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($c['course_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button class="btn btn-primary mt-4">Update Student</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../assets/main.js"></script>
    <script>$(document).ready(function() { $('.select2').select2(); });</script>
</body>
</html>
