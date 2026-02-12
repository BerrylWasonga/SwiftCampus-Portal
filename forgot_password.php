<?php
session_start();
include("config.php");
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    if (empty($email)) {
        $message = "Email address is required.";
        $message_type = "danger";
    } else {
        // Check if email exists in users table
        $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user_data = $result->fetch_assoc();
            $first_name = $user_data['first_name'];

            // Generate 6-digit OTP
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

            // Store in password_resets table
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $delete_stmt->bind_param("s", $email);
            $delete_stmt->execute();
            $delete_stmt->close();

            $insert_stmt = $conn->prepare("INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $email, $otp, $expiry);
            
            if ($insert_stmt->execute()) {
                // Send OTP via PHPMailer
                $mail = new PHPMailer(true);

                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'samson2020odhiambo@gmail.com';
                    $mail->Password   = '37890362';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = '587';

                    // Recipients
                    $mail->setFrom('samson2020odhiambo@gmail.com', 'Prudence Wasonga');
                    $mail->addAddress($email, $first_name);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Your Password Reset OTP';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                            <h2 style='color: #0d1b2a;'>Password Reset Request</h2>
                            <p>Hello $first_name,</p>
                            <p>You requested to reset your password. Use the following 6-digit OTP to complete the process:</p>
                            <div style='font-size: 24px; font-weight: bold; background: #f4f4f4; padding: 10px; text-align: center; border-radius: 5px; margin: 20px 0;'>
                                $otp
                            </div>
                            <p>This OTP is valid for 15 minutes. If you did not request this, please ignore this email.</p>
                            <hr>
                            <p style='font-size: 12px; color: #777;'>Sent from Student Portal</p>
                        </div>";

                    $mail->send();
                    $message = "A 6-digit OTP has been sent to your email address.";
                    $message .= "<br><br><a href='reset_password.php?email=" . urlencode($email) . "' class='btn btn-cyan btn-sm mt-2'>Proceed to Reset Password</a>";
                    $message_type = "success";
                } catch (Exception $e) {
                    // Fallback for local testing if SMTP fails
                    $message = "OTP generated but email could not be sent. <br>Your 6-digit OTP is: <strong>$otp</strong> (Debug: " . $mail->ErrorInfo . ")";
                    $message .= "<br><br><a href='reset_password.php?email=" . urlencode($email) . "' class='btn btn-cyan btn-sm mt-2'>Proceed to Reset Password</a>";
                    $message_type = "warning";
                }
            } else {
                $message = "Something went wrong. Please try again later.";
                $message_type = "danger";
            }
            $insert_stmt->close();
        } else {
            $message = "Email address not found.";
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
    <title>Forgot Password - Student Portal</title>
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
        .separator {
            width: 100%;
            height: 1px;
            background-color: rgba(255, 255, 255, 0.3);
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="login-card text-center">
    <div class="logo">
        <img src="students/Assets/images/2.png" alt="University Logo">
    </div>
    
    <h4 class="mb-4">Forgot Password</h4>
    <p class="text-muted small mb-4">Enter your registered email address and we'll send you a link to reset your password.</p>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?> small"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-4 text-start">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" placeholder="e.g. student@example.com" required>
        </div>

        <button type="submit" class="btn btn-cyan btn-lg w-100 mb-3">Send Reset Link</button>
        
        <div class="mt-3">
            <a href="login.php" class="text-cyan text-decoration-none small"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
        </div>
    </form>
</div>

</body>
</html>
