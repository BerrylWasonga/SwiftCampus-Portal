<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') { header("Location: ../login.php"); exit(); }
include '../config.php';
require_once 'registration_functions.php';

// Fetch all active units for selection in special exams
$units_stmt = $conn->prepare("SELECT id, unit_code, unit_name FROM course_units WHERE status = 'active' ORDER BY unit_code ASC");
$units_stmt->execute();
$all_units = $units_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$units_stmt->close();
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
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form action="process_request.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label">Request Type</label>
                        <select class="form-select" name="request_type" required>
                            <option value="Request Type">Request Type</option>
                            <option value="Academic Leave">Academic Leave</option>
                            <option value="Deferment">Deferment</option>
                            <option value="Remarking">Remarking</option>
                            <option value="Special Exam">Special Exam</option>
                        </select>
                    </div>
                    <div class="mb-3" id="unitSelectionDiv" style="display: none;">
                        <label class="form-label">Select Unit</label>
                        <select class="form-select" name="unit_id">
                            <option value="">-- Select Unit --</option>
                            <?php foreach ($all_units as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>">
                                    <?php echo htmlspecialchars($unit['unit_code'] . ' - ' . $unit['unit_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Specify the unit you missed and require a special exam for.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" name="reason" rows="4" required placeholder="State your reason..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
    
    <?php include_once 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const requestType = document.querySelector('select[name="request_type"]');
        const unitDiv = document.getElementById('unitSelectionDiv');
        
        requestType.addEventListener('change', function() {
            if (this.value === 'Special Exam') {
                unitDiv.style.display = 'block';
                document.querySelector('select[name="unit_id"]').setAttribute('required', 'required');
            } else {
                unitDiv.style.display = 'none';
                document.querySelector('select[name="unit_id"]').removeAttribute('required');
            }
        });
    });
    </script>
</body>
</html>