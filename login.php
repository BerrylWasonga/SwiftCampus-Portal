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
                        header("Location: admin/admin.php");
                    } else {
                        header("Location: students/welcome.php");
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
    <link rel="icon" href="students/Assets/images/favicon.png?v=2" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            margin-bottom: -75px;
        }
        .logo img {
            width: 220px;
            height: auto;
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
        #password {
            padding-right: 50px !important;  /* Ensures space for the toggle icon */
        }
        .separator {
            width: 100%;
            height: 1px;
            background-color: rgba(255, 255, 255, 0.3);
            margin: 30px 0 25px 0;
        }
        .separator2 {
            width: 100%;
            height: 1px;
            background-color: rgba(255, 255, 255, 0.7);
            margin: 0 0 30px 0;
        }
        .text-cyan {
            color: #40c4ff;
        }
         .toggle-password {
        position: absolute;
        right: 10px;                  /* Positions it nicely inside the input */
        top: 60%;
        transform: translateY(-30%);  /* Centers it vertically (adjust if needed) */
        background: transparent;      /* No background to blend with dark theme */
        border: none;
        color: #778da9;               /* Matches your placeholder color */
        cursor: pointer;
        padding: 0;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;           /* Makes it circular */
        transition: background 0.2s, color 0.2s;
        z-index: 10;
    }

    .toggle-password:hover {
        background: rgba(255, 255, 255, 0.1);  /* Subtle hover effect */
        color: #40c4ff;                        /* Matches your cyan accent */
    }

    .toggle-password i {
        font-size: 18px;
    }
        }
        .footer-text {
            font-size: 0.85rem;
            color: #e0e1dd;
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <div class="logo">
        <img src="students/Assets/images/2.png" alt="Chuka University Logo">
    </div>

    <div class="separator"></div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3 text-start">
            <label class="form-label">Reg. Number</label>
            <input type="text" name="reg_no" class="form-control" placeholder="e.g. EB1/56145/21" required>
        </div>

        <div class="mb-3 text-start position-relative">
            <label class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control pe-5" required>  <!-- Added id and pe-5 for padding -->
            <button type="button" class="toggle-password" onclick="togglePassword()">
                <i class="fas fa-eye"></i>
            </button>
        </div>

        <div class="mb-4 form-check text-start">
            <input type="checkbox" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-login btn-lg w-100">Log In</button>

        <div class="mt-4">
            <a href="forgot_password.php" class="text-cyan text-decoration-none">Forgot your password?</a>
        </div>
    
        <div class="separator2"></div>

        <div class="mt-5 footer-text">
            <?php echo date('j, F, Y'); ?> &copy; Designed by BerrylWasonga
        </div>
    </form>
</div>
<script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleBtn = document.querySelector('.toggle-password i');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleBtn.classList.remove('fa-eye');
                toggleBtn.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleBtn.classList.remove('fa-eye-slash');
                toggleBtn.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>