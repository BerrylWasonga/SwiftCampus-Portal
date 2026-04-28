<?php
session_start();

// Only allow logged-in students (role = 'user')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
include __Dir__ .'/../config.php';

// Fetch ALL user details using the session user_id
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Build full name safely
$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
if (empty($full_name)) {
    $full_name = $user['email'] ?? 'User';
}

// Fetch documents BEFORE including any partials
$doc_stmt = $conn->prepare("SELECT * FROM student_documents WHERE user_id = ? ORDER BY created_at DESC");
$doc_stmt->bind_param("i", $_SESSION['user_id']);
$doc_stmt->execute();
$documents = $doc_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$doc_stmt->close();

include 'views/welcome.view.php';
?>


