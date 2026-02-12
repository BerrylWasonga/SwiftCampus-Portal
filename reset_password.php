<?php
session_start();
include("config.php");

$message = "";
$message_type = "";
$show_form = false;

if (isset($_GET["email"])) {
    $email = $_GET["email"];
    $show_form = true;
} else {
    $message = "Please request an OTP first.";
    $message_type = "warning";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $show_form) {
    $otp = trim($_POST["otp"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (empty($otp) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required.";
        $message_type = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "danger";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
        $message_type = "danger";
    } else {
        // Verify OTP - using BINARY for exact match
        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND BINARY token = ? AND expiry > NOW()");
        $stmt->bind_param("ss", $email, $otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update password in users table
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update_stmt->bind_param("ss", $hashed_password, $email);
            
            if ($update_stmt->execute()) {
                // Delete used token/OTP
                $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $delete_stmt->bind_param("s", $email);
                $delete_stmt->execute();
                $delete_stmt->close();

                $message = "Your password has been successfully reset. <a href='login.php' class='alert-link'>Login now</a>";
                $message_type = "success";
                $show_form = false;
            } else {
                $message = "Something went wrong. Please try again later.";
                $message_type = "danger";
            }
            $update_stmt->close();
        } else {
            $message = "Invalid or expired OTP. Please check and try again.";
            $message_type = "danger";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Student Portal</title>
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
            margin-bottom: 20px;
        }
        .logo img {
            width: 200px;
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
        .btn-cyan {
            background-color: #40c4ff;
            border: none;
            font-weight: bold;
            color: #0d1b2a;
        }
        .btn-cyan:hover {
            background-color: #02b3e4;
            color: white;
        }
        .text-cyan {
            color: #40c4ff;
        }
        .toggle-password {
            background: transparent;
            border: none;
            color: #778da9;
            cursor: pointer;
            padding: 0 10px;
        }
        .toggle-password:hover {
            color: #40c4ff;
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <div class="logo">
        <img src="students/Assets/images/2.png" alt="University Logo">
    </div>
    
    <h4 class="mb-4">Reset Password</h4>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> small"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($show_form): ?>
        <form method="post" action="">
            <div class="mb-3 text-start">
                <label class="form-label">Email Address</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($email); ?>" readonly disabled>
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">6-Digit OTP</label>
                <input type="text" name="otp" class="form-control" placeholder="Enter 6-digit OTP" required maxlength="6" pattern="\d{6}">
            </div>

            <div class="mb-3 text-start">
                <label class="form-label">New Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password" required minlength="6">
                    <button class="toggle-password" type="button" data-target="#password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4 text-start">
                <label class="form-label">Confirm New Password</label>
                <div class="input-group">
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm new password" required minlength="6">
                    <button class="toggle-password" type="button" data-target="#confirm_password">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-cyan btn-lg w-100 mb-3">Update Password</button>
        </form>
    <?php endif; ?>
    
    <div class="mt-3">
        <a href="login.php" class="text-cyan text-decoration-none small">Back to Login</a>
    </div>
</div>

    <script>
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const target = document.querySelector(this.getAttribute('data-target'));
            const type = target.getAttribute('type') === 'password' ? 'text' : 'password';
            target.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    });
    </script>
</body>
</html>
