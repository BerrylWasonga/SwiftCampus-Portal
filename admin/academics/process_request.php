<?php
session_start();
include("../../config.php");

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['req_id']) && isset($_POST['status']) && isset($_POST['admin_comment'])) {
        $req_id = intval($_POST['req_id']);
        $status = $_POST['status'];
        $admin_comment = trim($_POST['admin_comment']);
        $admin_id = $_SESSION['user_id'];
        $decision_date = date('Y-m-d H:i:s');

        // Validation
        if (!in_array($status, ['Approved', 'Rejected'])) {
             $_SESSION['error'] = "Invalid status selected.";
             header("Location: requisitions.php");
             exit();
        }

        if (empty($admin_comment)) {
             $_SESSION['error'] = "Comment is required.";
             header("Location: requisitions.php");
             exit();
        }

        // Update database
        $sql = "UPDATE requisitions SET status = ?, admin_comment = ?, admin_id = ?, decision_date = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssisi", $status, $admin_comment, $admin_id, $decision_date, $req_id);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Requisition processed successfully.";
            } else {
                $_SESSION['error'] = "Error updating record: " . $stmt->error;
            }
            $stmt->close();
        } else {
             $_SESSION['error'] = "Database error: " . $conn->error;
        }

    } else {
        $_SESSION['error'] = "Missing form data.";
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}

$conn->close();
header("Location: requisitions.php");
exit();
?>