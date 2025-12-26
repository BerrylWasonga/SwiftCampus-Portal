<?php
session_start();
require 'config.php';

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $reg_no = strtoupper(trim($_POST["reg_no"]));  // Uppercase for consistency
    $gender = $_POST["gender"];
    $dob = $_POST["dob"];  // YYYY-MM-DD from date input
    $address = trim($_POST["address"]);
    $campus = $_POST["campus"];
    $programme = trim($_POST["programme"]);

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || empty($reg_no)) {
        $error = "Required fields (marked *) are missing.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check for duplicate email OR reg_no
        $check_sql = "SELECT id FROM users WHERE email = ? OR reg_no = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $email, $reg_no);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Email or Registration Number already registered.";
            $check_stmt->close();
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert ALL fields
            $sql = "INSERT INTO users 
                    (first_name, last_name, email, password, reg_no, gender, dob, address, campus, programme, role, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', 'active')";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssss", $first_name, $last_name, $email, $hashed_password, $reg_no, $gender, $dob, $address, $campus, $programme);

            if ($stmt->execute()) {
                $success = "Registration successful! <a href='login.php'>Click here to login</a>";
            } else {
                $error = "Registration failed. Please try again.";
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white text-center">
                    <h3>Student Registration Form</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>First Name *</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label>Registration Number * <small>(e.g. EB1/56145/21)</small></label>
                            <input type="text" name="reg_no" class="form-control" required placeholder="EB1/56145/21">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Password *</label>
                                <input type="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="6">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Address</label>
                            <input type="text" name="address" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label>Campus</label>
                            <select name="campus" class="form-select">
                                <option value="MAIN">MAIN</option>
                                <option value="TOWN">TOWN</option>
                                <option value="OTHER">OTHER</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Programme <small>(e.g. Bachelor of Science (Computer Science))</small></label>
                            <input type="text" name="programme" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-success btn-lg w-100">Register</button>
                    </form>

                    <p class="text-center mt-4">
                        Already registered? <a href="login.php">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>