<?php
session_start();
require_once '../../config.php';

// Security: Must be logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_id = $_POST["reg_id"];
    $admin_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("UPDATE course_registrations SET registration_status = 'approved', approved_by = ?, approval_date = NOW() WHERE id = ?");
    $stmt->bind_param("ii", $admin_id, $reg_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Registration approved successfully.";
        $_SESSION['msg_type'] = "success";
    } else {
        $_SESSION['message'] = "Failed to approve registration.";
        $_SESSION['msg_type'] = "danger";
    }
    $stmt->close();
}

header("Location: ../registrations.php");
exit();
?>
