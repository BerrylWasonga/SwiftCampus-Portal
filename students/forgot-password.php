<?php
// forgot-password.php logic
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // 1. Generate a cryptographically secure random token
    $token = bin2hex(random_bytes(32)); 
    
    // 2. Hash the token for storage
    $tokenHash = hash('sha256', $token);
    
    // 3. Set expiration (e.g., 30 minutes from now)
    $expires = date("Y-m-d H:i:s", strtotime('+30 minutes'));

    // 4. Store in DB (Use PDO to prevent SQL Injection)
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token_hash, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$email, $tokenHash, $expires]);

    // 5. Send the email
    $resetLink = "http://localhost/auth-system/public/reset-password.php?token=" . $token;
    
    // Logic to send email goes here (e.g., mail($email, "Reset Password", $resetLink));
    echo "If that email exists, a reset link has been sent.";
}
?>