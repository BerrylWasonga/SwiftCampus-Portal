<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $reg_no = trim($_POST['reg_no']); // Staff ID
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'staff';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if email or reg_no already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? OR reg_no = ?");
        $check->bind_param("ss", $email, $reg_no);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            $error = "User with this Email or Staff ID already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, reg_no, password, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $reg_no, $hashed_password, $role);
            
            if ($stmt->execute()) {
                $success = "Staff added successfully.";
            } else {
                $error = "Database Error: " . $conn->error;
            }
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Staff - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/main.css" rel="stylesheet">
</head>
<body>
    <?php include_once '../partials/top_navbar.php'; ?>
    <?php include_once '../partials/sidebar.php'; ?>
    
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Add Teaching Staff</h2>
                <a href="manage.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back to List</a>
            </div>

            <div class="card shadow-sm" style="max-width: 800px; margin: 0 auto;">
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Staff ID / Reg No</label>
                            <input type="text" name="reg_no" class="form-control" placeholder="e.g. STF001" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#confirm_password">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Create Staff Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/main.js"></script>
    <script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-target'));
            const type = target.getAttribute('type') === 'password' ? 'text' : 'password';
            target.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });
    });
    </script>
</body>
</html>
