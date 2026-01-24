<?php
// reset-password.php
require_once '../config/db.php'; // Ensure this defines $conn as a mysqli object

// 1. Get the raw token from the URL
$token = $_GET['token'] ?? '';

// 2. Hash it to match what we stored in the database
$tokenHash = hash('sha256', $token);

/**
 * 3. Prepare the query using MySQLi
 * We check if the hash exists AND if the expiry time is in the future
 */
$sql = "SELECT email FROM password_resets WHERE token_hash = ? AND expires_at > NOW() LIMIT 1";
$stmt = $conn->prepare($sql);

// "s" means the parameter is a string
$stmt->bind_param("s", $tokenHash);
$stmt->execute();
$result = $stmt->get_result();
$resetRequest = $result->fetch_assoc();

// 4. Verification Logic
if ($resetRequest) {
    /**
     * Token is valid! 
     * We pass the RAW token (not the hash) into a hidden field 
     * so update-password.php can verify it one last time.
     */
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Reset Your Password</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <h2>Create New Password</h2>
        <p>Email identified for: <?php echo htmlspecialchars($resetRequest['email']); ?></p>
        
        <form method="POST" action="update-password.php">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="form-group">
                <input type="password" name="new_password" placeholder="Enter new password" required minlength="8">
            </div>
            
            <div class="form-group">
                <button type="submit">Update Password</button>
            </div>
        </form>
    </body>
    </html>
    <?php
} else {
    // If the token was tampered with, used, or expired
    echo "<h2>Invalid or Expired Link</h2>";
    echo "<p>This password reset link is no longer valid. Please request a new one.</p>";
}

$stmt->close();
$conn->close();
?>