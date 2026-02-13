<?php
session_start();
include("../config.php");

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

// Check for POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION["user_id"];
    
    // Validate inputs
    if (isset($_POST["request_type"]) && isset($_POST["reason"])) {
        $request_type = trim($_POST["request_type"]);
        $reason = trim($_POST["reason"]);
        $unit_id = isset($_POST["unit_id"]) && !empty($_POST["unit_id"]) ? (int)$_POST["unit_id"] : null;

        // Check for empty fields
        if (empty($request_type) || empty($reason) || $request_type === "Request Type") {
            $_SESSION['error'] = "All fields are required and must be valid.";
            header("Location: academic_R.php");
            exit();
        }

        // Insert into database
        $sql = "INSERT INTO requisitions (student_id, request_type, unit_id, reason, status) 
                VALUES (?, ?, ?, ?, 'Pending')";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $_SESSION['error'] = "Database error: " . $conn->error;
            header("Location: academic_R.php");
            exit();
        }
        
        $stmt->bind_param("isis", $student_id, $request_type, $unit_id, $reason);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Request submitted successfully.";
        } else {
            $_SESSION['error'] = "Error submitting request: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Invalid form submission.";
    }
} else {
    $_SESSION['error'] = "Invalid request method.";
}

$conn->close();
header("Location: academic_R.php");
exit();
?>
