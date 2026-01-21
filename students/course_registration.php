<?php
session_start();

// Only allow logged-in students (role = 'user')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Include database connection
include '../config.php';

// Fetch ALL user details using the session user_id
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

// Build full name safely
$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
if (empty($full_name)) {
    $full_name = $user['email'] ?? 'User';
}
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

    
    <div class="main-content">
        <div class="header">
            Course Registration
        </div>

        <div class="registration-box">
            <h2>COURSE REGISTRATION</h2>
            <div class="semester-info">
                <strong>Registration Semester: <?php echo $currentSemester; ?></strong>
            </div>

            <div class="instructions">
                Click on the button "Get Units". Select your Courses and click button "Add Selected Courses to the Basket". 
                Confirm them then Complete registration by clicking button "Complete Registration". 
                You can view Proforma Invoice by clicking button "View Proforma Invoice".
            </div>

            <div class="filters">
                <label for="reg-type">Registration Type:</label>
                <select name="registration_type" id="reg-type">
                    <option value="">---</option>
                    <option value="regular">Regular</option>
                    <option value="supplementary">Supplementary</option>
                    <option value="special">Special/Repeat</option>
                </select>

                <div class="get-units-btn">
                    <button type="button" onclick="alert('Fetching available units... (Connect to backend for real functionality)')">
                        Get Units
                    </button>
                </div>
            </div>

            <div class="course-list-placeholder">
                (Available course units will be displayed here after selecting Registration Type and clicking "Get Units")
            </div>
        </div>
    </div>

    <?php
        include_once 'partials/footer.php' ;
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>   

</body>
</html>