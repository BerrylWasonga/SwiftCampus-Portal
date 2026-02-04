<?php
session_start();
include("../../config.php");

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$success_msg = "";
$error_msg = "";

if (isset($_SESSION['success'])) {
    $success_msg = $_SESSION['success'];
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $error_msg = $_SESSION['error'];
    unset($_SESSION['error']);
}

// Fetch requisitions
$sql = "SELECT r.*, u.first_name, u.last_name, u.reg_no 
        FROM requisitions r 
        JOIN users u ON r.student_id = u.id 
        ORDER BY r.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Requisitions - Admin</title>
    <link rel="icon" type="image/png" href="../../students/Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/main.css" rel="stylesheet">
    <style>
        .status-badge {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 20px;
        }
        .status-Pending { background-color: #ffd700; color: #000; }
        .status-Approved { background-color: #28a745; color: #fff; }
        .status-Rejected { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <?php include_once '../partials/top_navbar.php'; ?>
    <?php include_once '../partials/sidebar.php'; ?>
    
    <div class="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Academic Requisitions</h2>
            
            <?php if ($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th>Reg. No</th>
                                    <th>Type</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['reg_no']); ?></td>
                                            <td><?php echo htmlspecialchars($row['request_type']); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-outline-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#viewModal<?php echo $row['id']; ?>">
                                                    View
                                                </button>
                                            
                                                <!-- View Modal -->
                                                <div class="modal fade" id="viewModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Requisition Details</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><strong>Reason:</strong></p>
                                                                <p><?php echo nl2br(htmlspecialchars($row['reason'])); ?></p>
                                                                <?php if($row['admin_comment']): ?>
                                                                    <hr>
                                                                    <p><strong>Admin Comment:</strong></p>
                                                                    <p><?php echo nl2br(htmlspecialchars($row['admin_comment'])); ?></p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge status-<?php echo $row['status']; ?>">
                                                    <?php echo $row['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($row['status'] === 'Pending'): ?>
                                                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#actionModal<?php echo $row['id']; ?>" data-action="Approved">Approve</button>
                                                    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#actionModal<?php echo $row['id']; ?>" data-action="Rejected">Reject</button>

                                                    <!-- Action Modal -->
                                                    <div class="modal fade" id="actionModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <form action="process_request.php" method="POST">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title">Process Request</h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="req_id" value="<?php echo $row['id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Action</label>
                                                                            <select name="status" class="form-select" required>
                                                                                <option value="Approved">Approve</option>
                                                                                <option value="Rejected">Reject</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Comment / Feedback</label>
                                                                            <textarea name="admin_comment" class="form-control" rows="3" required placeholder="Enter reason for decision..."></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Processed</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No requisitions found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Optional: Pre-select the action in the modal based on which button was clicked
        const actionModals = document.querySelectorAll('.modal');
        actionModals.forEach(modal => {
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const action = button.getAttribute('data-action');
                if (action) {
                    const select = modal.querySelector('select[name="status"]');
                    if (select) {
                        select.value = action;
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
