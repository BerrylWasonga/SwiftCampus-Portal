<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}
// Placeholder for Timetable
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Table - Student Dashboard</title>
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
</head>
<body>
    <?php 
        include_once 'partials/sidebar.php' ;
        include_once 'partials/top_navbar.php' ;
    ?>
    
    <div class="main-content d-flex flex-column justify-content-center align-items-center" style="min-height: 80vh;" id="mainContent">
        <div class="text-center">
            <i class="bi bi-calendar-range text-muted" style="font-size: 5rem;"></i>
            <h2 class="mt-4">Timetable Coming Soon</h2>
            <p class="lead text-muted">The semester timetable has not been uploaded yet.</p>
            <button onclick="history.back()" class="btn btn-primary mt-3">
                <i class="bi bi-arrow-left me-2"></i>Go Back
            </button>
        </div>
    </div>
    
    <?php
       include_once 'partials/footer.php'; 
    ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
</body>
</html>