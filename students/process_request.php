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

        // Check for empty fields
        if (empty($request_type) || empty($reason)) {
            $_SESSION['error'] = "All fields are required.";
            header("Location: academic_R.php");
            exit();
        }

        // Database operation would go here
        $_SESSION['success'] = "Request submitted successfully.";
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
