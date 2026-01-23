<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Requisition - Student Dashboard</title>
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
</head>
<body>
    <?php include_once 'partials/sidebar.php'; include_once 'partials/top_navbar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <h2 class="mb-4">Academic Requisition</h2>
        <div class="card shadow-sm">
            <div class="card-body">
                <form>
                    <div class="mb-3">
                        <label class="form-label">Request Type</label>
                        <select class="form-select">
                            <option>Academic Leave</option>
                            <option>Deferment</option>
                            <option>Remarking</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" rows="4"></textarea>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="alert('Request submitted successfully!')">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include_once 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
</body>
</html>