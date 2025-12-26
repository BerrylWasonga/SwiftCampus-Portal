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
    $stmt->close();  // If stmt exists
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
<h2>Register</h2>
<?php if (!empty($error)) { ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php } ?>
<form method="post" action="">
    First Name: <input type="text" name="first_name" required minlength="2"><br><br>
    Last Name: <input type="text" name="last_name" required minlength="2"><br><br>
    Email: <input type="email" name="email" required><br><br>
    Password: <input type="password" name="password" required minlength="6"><br><br>
    Confirm Password: <input type="password" name="confirm_password" required minlength="6"><br><br>
    <button type="submit">Register</button>
</form>
<a href="login.php">Already have an account? Login</a>
</body>
</html>