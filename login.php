<?php
session_start();
include("config.php");

$error = ""; // Initialize error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if email and password are set
    if (isset($_POST["email"]) && isset($_POST["password"])) {
        $email = trim($_POST["email"]);
        $password = $_POST["password"];

        // Validate input to prevent empty submissions
        if (empty($email) || empty($password)) {
            $error = "Email and password are required.";
        } else {
            // Updated: SELECT includes role, first_name, last_name; use BINARY for case-sensitive email matching
            $sql = "SELECT id, first_name, last_name, email, password, role FROM users WHERE BINARY email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();  // Updated: Use get_result() for fetch_assoc()
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();  // Updated: Simpler than bind_result + fetch

                if (password_verify($password, $user['password'])) {
                    $_SESSION["user_id"] = $user['id'];
                    $_SESSION["email"] = $user['email'];
                    $_SESSION["first_name"] = $user['first_name'];
                    $_SESSION["last_name"] = $user['last_name'];
                    $_SESSION["role"] = $user['role'];  // New: Store role in session

                    $update_login = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $update_login->bind_param("i", $user['id']);
                    $update_login->execute();
                    $update_login->close();

                    // New: Conditional redirect based on role
                    if ($user['role'] === 'admin') {
                        header("Location: admin.php");
                    } else {
                        header("Location: welcome.php");
                    }
                    exit();
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "User not found.";
            }
            $stmt->close();
        }
    } else {
        $error = "Please fill out the form.";
    }
}
$conn->close(); // Close database connection
?>

<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>
<h2>Login</h2>
<?php if (!empty($error)) { ?>
    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
<?php } ?>
<form method="post" action="">
    Email: <input type="email" name="email" required><br><br>  <!-- Updated: Email input -->
    Password: <input type="password" name="password" required><br><br>
    <button type="submit">Login</button>
</form>
Don't have an account?<a href="register.php"> Register</a>
</body>
</html>