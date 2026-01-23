<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostel Booking - Student Dashboard</title>
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'partials/sidebar.php'; include_once 'partials/top_navbar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <h2 class="mb-4">Hostel Booking</h2>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i> Hostel booking for the upcoming semester is currently closed.
        </div>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-building-lock fs-1 text-muted mb-3"></i>
                <h5>No Rooms Available</h5>
                <p class="text-muted">Please check back later or contact the Accommodation Department.</p>
            </div>
        </div>
    </div>
    
    <?php include_once 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
</body>
</html>