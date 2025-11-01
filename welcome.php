<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    header('Location: admin.php');  // Redirect admins away
    exit();
}

// Fetch user details (names) from DB for display
include 'config.php';
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$conn->close();

$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
if (empty($full_name)) $full_name = $user['email'];  // Fallback to email
?>

<!DOCTYPE html>
<html>
<head><title>Welcome</title></head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($full_name); ?>!</h1>
    <p>This is your user dashboard. Role: <?php echo $_SESSION['role']; ?></p>
    <a href="logout.php">Logout</a>
</body>
</html>