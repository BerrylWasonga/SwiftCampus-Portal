<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config.php';

$message = '';
$message_type = '';

// Handle File Upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_document'])) {
    $user_id = $_POST['student_id'];
    $document_name = trim($_POST['document_name']);
    $remarks = trim($_POST['remarks']);
    
    // File upload handling
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['document_file']['name'];
        $filetype = $_FILES['document_file']['type'];
        $filesize = $_FILES['document_file']['size'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // Create uploads directory if it doesn't exist
            $upload_dir = "../../uploads/documents/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // Generate unique filename
            $new_filename = time() . "_" . preg_replace('/[^a-zA-Z0-9]/', '_', $document_name) . "." . $ext;
            $destination = $upload_dir . $new_filename;
            
            // Path to store in DB (relative to project root usually, or relative to admin? 
            // Let's store relative to root so students can access it easily "../uploads/..." )
            // But wait, student/welcome.php is in `students/`. `uploads/` is in root.
            // So from `students/`, it is `../uploads/`.
            // From `admin/student/`, it is `../../uploads/`.
            // Let's store the relative path from root: "uploads/documents/filename"
            $db_file_path = "uploads/documents/" . $new_filename;

            if (move_uploaded_file($_FILES['document_file']['tmp_name'], $destination)) {
                $stmt = $conn->prepare("INSERT INTO student_documents (user_id, document_name, file_path, remarks) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $document_name, $db_file_path, $remarks);
                
                if ($stmt->execute()) {
                    $message = "Document uploaded successfully.";
                    $message_type = "success";
                } else {
                    $message = "Database error: " . $conn->error;
                    $message_type = "danger";
                }
                $stmt->close();
            } else {
                $message = "Failed to move uploaded file.";
                $message_type = "danger";
            }
        } else {
            $message = "Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG.";
            $message_type = "danger";
        }
    } else {
        $message = "Please select a file to upload.";
        $message_type = "danger";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $doc_id = $_GET['delete'];
    // Get file path to delete file
    $stmt = $conn->prepare("SELECT file_path FROM student_documents WHERE id = ?");
    $stmt->bind_param("i", $doc_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($res) {
        $file_to_delete = "../../" . $res['file_path'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
        }
        
        $del_stmt = $conn->prepare("DELETE FROM student_documents WHERE id = ?");
        $del_stmt->bind_param("i", $doc_id);
        if ($del_stmt->execute()) {
            $message = "Document deleted.";
            $message_type = "success";
        } else {
            $message = "Error deleting record.";
            $message_type = "danger";
        }
        $del_stmt->close();
    }
}

// Fetch Students for Dropdown
$students = $conn->query("SELECT id, first_name, last_name, reg_no FROM users WHERE role = 'user' ORDER BY first_name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch All Documents
$documents = $conn->query("SELECT sd.*, u.first_name, u.last_name, u.reg_no FROM student_documents sd JOIN users u ON sd.user_id = u.id ORDER BY sd.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Student Documents - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/main.css" rel="stylesheet">
</head>
<body>
    <?php include_once '../partials/top_navbar.php'; ?>
    <?php include_once '../partials/sidebar.php'; ?>
    
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Student Documents</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="bi bi-upload me-2"></i>Upload Document
                </button>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Student</th>
                                    <th>Document Name</th>
                                    <th>Remarks</th>
                                    <th>Uploaded Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($documents)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No documents uploaded yet.</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($documents as $index => $doc): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($doc['reg_no']); ?></small>
                                        </td>
                                        <td>
                                            <a href="../../<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="text-decoration-none">
                                                <i class="bi bi-file-earmark-text me-1"></i>
                                                <?php echo htmlspecialchars($doc['document_name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars($doc['remarks'] ?? '-'); ?></td>
                                        <td><?php echo date('d M Y', strtotime($doc['created_at'])); ?></td>
                                        <td>
                                            <a href="?delete=<?php echo $doc['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload New Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Student</label>
                            <select name="student_id" class="form-select" required>
                                <option value="">-- Select Student --</option>
                                        <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo htmlspecialchars($student['reg_no'] . " - " . $student['first_name'] . " " . $student['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document Name</label>
                            <input type="text" name="document_name" class="form-control" placeholder="e.g. Admission Letter" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File</label>
                            <input type="file" name="document_file" class="form-control" required>
                            <div class="form-text">Allowed: PDF, JPG, PNG, DOCX</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks (Optional)</label>
                            <textarea name="remarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="upload_document" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/main.js"></script>
</body>
</html>
