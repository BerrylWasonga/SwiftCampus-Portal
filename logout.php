<?php
session_start();
if (isset($_SESSION['user_id'])) {
    include 'config.php';
    $reset_stmt = $conn->prepare("UPDATE users SET last_login = NULL WHERE id = ?");
    $reset_stmt->bind_param("i", $_SESSION['user_id']);
    $reset_stmt->execute();
    $reset_stmt->close();
    $conn->close();
}
session_destroy();
header("Location: login.php");
exit();
?>
