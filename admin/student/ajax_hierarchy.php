<?php
session_start();
require_once '../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

if ($action === 'get_courses_by_faculty') {
    $faculty_id = (int)($_GET['faculty_id'] ?? 0);
    $stmt = $conn->prepare("SELECT id, course_name, course_code FROM courses WHERE faculty_id = ?");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
