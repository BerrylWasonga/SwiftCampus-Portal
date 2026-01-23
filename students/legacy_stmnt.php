<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legacy Statement - Student Dashboard</title>
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'partials/sidebar.php'; include_once 'partials/top_navbar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <h2 class="mb-4">Legacy Fee Statement</h2>
        <div class="alert alert-warning">
            <i class="bi bi-archive me-2"></i> No legacy data found relative to the previous system.
        </div>
        <p>Please refer to the <a href="fee_stmnt.php">Current Fee Statement</a> for up-to-date information.</p>
    </div>
    
    <?php include_once 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
</body>
</html>