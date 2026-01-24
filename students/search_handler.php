<?php
// search_handler.php
// Only allow logged-in students
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

include '../config.php';

header('Content-Type: application/json');

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit();
}

$results = [];
$searchTerm = "%" . $query . "%";

// 1. Search Courses
$sqlCourses = "SELECT course_name AS title, course_code AS details, 'Course' AS type FROM courses WHERE course_name LIKE ? OR course_code LIKE ? LIMIT 5";
$stmt = $conn->prepare($sqlCourses);
if ($stmt) {
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
}

// 2. Search Course Units
$sqlUnits = "SELECT unit_name AS title, unit_code AS details, 'Unit' AS type FROM course_units WHERE unit_name LIKE ? OR unit_code LIKE ? LIMIT 5";
$stmt = $conn->prepare($sqlUnits);
if ($stmt) {
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    $stmt->close();
}

echo json_encode($results);
$conn->close();
?>
