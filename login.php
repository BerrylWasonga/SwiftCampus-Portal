<?php
session_start();
include("config.php");

$error = ""; // Initialize error message variable

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["reg_no"]) && isset($_POST["password"])) {  // Changed from email to reg_no
        $reg_no = strtoupper(trim($_POST["reg_no"]));  // Uppercase for consistency
        $password = $_POST["password"];

        if (empty($reg_no) || empty($password)) {
            $error = "Registration Number and password are required.";
        } else {
            // Query by reg_no instead of email
            $sql = "SELECT id, first_name, last_name, email, password, role, reg_no FROM users WHERE BINARY reg_no = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $reg_no);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    $_SESSION["user_id"] = $user['id'];
                    $_SESSION["email"] = $user['email'];
                    $_SESSION["first_name"] = $user['first_name'];
                    $_SESSION["last_name"] = $user['last_name'];
                    $_SESSION["role"] = $user['role'];

                    $update_login = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                    $update_login->bind_param("i", $user['id']);
                    $update_login->execute();
                    $update_login->close();

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
                $error = "Registration Number not found.";
            }
            $stmt->close();
        }
    } else {
        $error = "Please fill out the form.";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            background-color: #0d1b2a;
            color: white;
            border-radius: 10px;
            padding: 40px 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .logo {
            width: 180px;
            margin-bottom: 30px;
        }
        .form-control {
            background-color: #1b263b;
            border: none;
            color: white;
        }
        .form-control::placeholder {
            color: #778da9;
        }
        .form-control:focus {
            background-color: #1b263b;
            color: white;
            box-shadow: none;
            border: 1px solid #415a77;
        }
        .btn-login {
            background-color: #40c4ff;
            border: none;
            font-weight: bold;
        }
        .btn-login:hover {
            background-color: #02b3e4;
        }
        .text-cyan {
            color: #40c4ff;
        }
        .footer-text {
            font-size: 0.85rem;
            color: #e0e1dd;
        }
    </style>
</head>
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