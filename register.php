<?php
session_start();
require 'config.php';

$success = '';  // For success message
$error = '';    // For error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email already exists
        $check_sql = "SELECT id FROM users WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Hash password and insert
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'user')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $first_name, $last_name, $email, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION["success"] = "Registration successful. Please login.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
        $check_stmt->close();
    }
    $stmt->close();  // If stmt exists
}
$conn->close();
?>

<!DOCTYPE html>
<html>
<head><title>Register</title></head>
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