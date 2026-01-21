<?php
if (isset($_SESSION['user_id'])) {
    include '../config.php';
    $reset_stmt = $conn->prepare("UPDATE users SET last_login = NULL WHERE id = ?");
    $reset_stmt->bind_param("i", $_SESSION['user_id']);
    $reset_stmt->execute();
    $reset_stmt->close();
    $conn->close();
}
session_destroy();
header("Location: ../login.php");
exit();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <!-- favicon -->
    <link rel="apple-touch-icon" href="images\2.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
</head>
<body>
     <?php 
        include_once 'partials/sidebar.php' ;
        include_once 'partials/top_navbar.php' ;
    ?>