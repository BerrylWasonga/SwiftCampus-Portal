<?php
// update-password.php
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Collect inputs
    $token = $_POST['token'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    if (empty($token) || empty($newPassword)) {
        die("Invalid request.");
    }

    // 2. Hash the token from the form to match the DB version
    $tokenHash = hash('sha256', $token);

    // 3. Verify the token is still valid and get the associated email
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token_hash = ? AND expires_at > NOW() LIMIT 1");
    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $result = $stmt->get_result();
    $request = $result->fetch_assoc();

    if ($request) {
        $email = $request['email'];

        // 4. Hash the new password using the industry-standard bcrypt
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // 5. Update the users table
        // We use a transaction or sequential statements to ensure integrity
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $updateStmt->bind_param("ss", $hashedPassword, $email);
        
        if ($updateStmt->execute()) {
            // 6. SUCCESS! Now delete the used token (Crucial for security)
            $deleteStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $deleteStmt->bind_param("s", $email);
            $deleteStmt->execute();

            echo "<h2>Success!</h2>";
            echo "<p>Your password has been updated. You can now <a href='index.php'>Login</a>.</p>";
        } else {
            echo "Error updating password. Please try again.";
        }
        $updateStmt->close();
    } else {
        echo "Invalid or expired session. Please request a new password reset.";
    }
    
    $stmt->close();
    $conn->close();
}
?>