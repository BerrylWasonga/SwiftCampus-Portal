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
    $target_type = $_POST['target_type'];
    $target_id = (int)$_POST['target_id'];
    
    $faculty_id = ($target_type === 'faculty') ? $target_id : null;
    $course_id = ($target_type === 'course') ? $target_id : null;
    
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
                $stmt = $conn->prepare("INSERT INTO student_documents (faculty_id, course_id, document_name, file_path, remarks) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $faculty_id, $course_id, $document_name, $db_file_path, $remarks);
                
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

// Handle Edit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_document'])) {
    $doc_id = (int)$_POST['edit_id'];
    $document_name = trim($_POST['edit_document_name']);
    $remarks = trim($_POST['edit_remarks']);
    $target_type = $_POST['edit_target_type'];
    $target_id = (int)$_POST['edit_target_id'];
    
    $faculty_id = ($target_type === 'faculty') ? $target_id : null;
    $course_id = ($target_type === 'course') ? $target_id : null;
    
    // Check if a new file is being uploaded
    if (isset($_FILES['edit_document_file']) && $_FILES['edit_document_file']['error'] == 0) {
        $allowed = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $filename = $_FILES['edit_document_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // Get old file path to delete it
            $stmt = $conn->prepare("SELECT file_path FROM student_documents WHERE id = ?");
            $stmt->bind_param("i", $doc_id);
            $stmt->execute();
            $old_doc = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($old_doc) {
                $old_file = "../../" . $old_doc['file_path'];
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            // Upload new file
            $upload_dir = "../../uploads/documents/";
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_filename = time() . "_" . preg_replace('/[^a-zA-Z0-9]/', '_', $document_name) . "." . $ext;
            $destination = $upload_dir . $new_filename;
            $db_file_path = "uploads/documents/" . $new_filename;

            if (move_uploaded_file($_FILES['edit_document_file']['tmp_name'], $destination)) {
                $stmt = $conn->prepare("UPDATE student_documents SET document_name = ?, remarks = ?, file_path = ?, faculty_id = ?, course_id = ? WHERE id = ?");
                $stmt->bind_param("sssiii", $document_name, $remarks, $db_file_path, $faculty_id, $course_id, $doc_id);
                
                if ($stmt->execute()) {
                    $message = "Document updated successfully.";
                    $message_type = "success";
                } else {
                    $message = "Database error: " . $conn->error;
                    $message_type = "danger";
                }
                $stmt->close();
            } else {
                $message = "Failed to upload new file.";
                $message_type = "danger";
            }
        } else {
            $message = "Invalid file type. Allowed: PDF, DOC, DOCX, JPG, PNG.";
            $message_type = "danger";
        }
    } else {
        // Update without changing file
        $stmt = $conn->prepare("UPDATE student_documents SET document_name = ?, remarks = ?, faculty_id = ?, course_id = ? WHERE id = ?");
        $stmt->bind_param("sssii", $document_name, $remarks, $faculty_id, $course_id, $doc_id);
        
        if ($stmt->execute()) {
            $message = "Document updated successfully.";
            $message_type = "success";
        } else {
            $message = "Database error: " . $conn->error;
            $message_type = "danger";
        }
        $stmt->close();
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

// Fetch Faculties and Courses for Dropdown
$faculties = $conn->query("SELECT id, faculty_name FROM faculties ORDER BY faculty_name ASC")->fetch_all(MYSQLI_ASSOC);
$courses = $conn->query("SELECT id, course_name, course_code FROM courses ORDER BY course_name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch All Documents
$documents = $conn->query("SELECT sd.*, f.faculty_name, c.course_name, c.course_code 
                           FROM student_documents sd 
                           LEFT JOIN faculties f ON sd.faculty_id = f.id 
                           LEFT JOIN courses c ON sd.course_id = c.id 
                           ORDER BY sd.created_at DESC")->fetch_all(MYSQLI_ASSOC);

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
                                    <th>Target Audience</th>
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
                                            <?php if ($doc['faculty_name']): ?>
                                                <span class="badge bg-primary">Faculty</span><br>
                                                <?php echo htmlspecialchars($doc['faculty_name']); ?>
                                            <?php elseif ($doc['course_name']): ?>
                                                <span class="badge bg-success">Course</span><br>
                                                <?php echo htmlspecialchars($doc['course_name']); ?>
                                                <small class="text-muted">(<?php echo htmlspecialchars($doc['course_code']); ?>)</small>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">General</span>
                                            <?php endif; ?>
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
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal" onclick="loadEditForm(<?php echo htmlspecialchars(json_encode($doc)); ?>)">
                                                <i class="bi bi-pencil"></i>
                                            </button>
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

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_id" id="editId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Upload To</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="edit_target_type" id="editTargetFaculty" value="faculty" onchange="toggleEditTarget()">
                                    <label class="form-check-label" for="editTargetFaculty">Faculty</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="edit_target_type" id="editTargetCourse" value="course" onchange="toggleEditTarget()">
                                    <label class="form-check-label" for="editTargetCourse">Course</label>
                                </div>
                            </div>
                            
                            <select name="edit_target_id" id="editSelectFaculty" class="form-select">
                                <option value="">-- Select Faculty --</option>
                                <?php foreach ($faculties as $f): ?>
                                    <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['faculty_name']); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select name="edit_target_id" id="editSelectCourse" class="form-select d-none" disabled>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>">
                                        <?php echo htmlspecialchars($c['course_name'] . " (" . $c['course_code'] . ")"); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Document Name</label>
                            <input type="text" name="edit_document_name" id="editDocumentName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current File</label>
                            <div id="currentFile" class="form-text mb-2"></div>
                            <label class="form-label">Replace With New File (Optional)</label>
                            <input type="file" name="edit_document_file" class="form-control">
                            <div class="form-text">Leave empty to keep current file. Allowed: PDF, JPG, PNG, DOCX</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remarks (Optional)</label>
                            <textarea name="edit_remarks" id="editRemarks" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="edit_document" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                            <label class="form-label">Upload To</label>
                            <div class="d-flex gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target_type" id="targetFaculty" value="faculty" checked onchange="toggleTarget()">
                                    <label class="form-check-label" for="targetFaculty">Faculty</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="target_type" id="targetCourse" value="course" onchange="toggleTarget()">
                                    <label class="form-check-label" for="targetCourse">Course</label>
                                </div>
                            </div>
                            
                            <select name="target_id" id="selectFaculty" class="form-select">
                                <option value="">-- Select Faculty --</option>
                                <?php foreach ($faculties as $f): ?>
                                    <option value="<?php echo $f['id']; ?>"><?php echo htmlspecialchars($f['faculty_name']); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <select name="target_id" id="selectCourse" class="form-select d-none" disabled>
                                <option value="">-- Select Course --</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>">
                                        <?php echo htmlspecialchars($c['course_name'] . " (" . $c['course_code'] . ")"); ?>
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
    <script>
    function toggleTarget() {
        const isFaculty = document.getElementById('targetFaculty').checked;
        const facultySelect = document.getElementById('selectFaculty');
        const courseSelect = document.getElementById('selectCourse');

        if (isFaculty) {
            facultySelect.classList.remove('d-none'); facultySelect.disabled = false;
            courseSelect.classList.add('d-none'); courseSelect.disabled = true;
        } else {
            facultySelect.classList.add('d-none'); facultySelect.disabled = true;
            courseSelect.classList.remove('d-none'); courseSelect.disabled = false;
        }
    }

    function toggleEditTarget() {
        const isFaculty = document.getElementById('editTargetFaculty').checked;
        const facultySelect = document.getElementById('editSelectFaculty');
        const courseSelect = document.getElementById('editSelectCourse');

        if (isFaculty) {
            facultySelect.classList.remove('d-none'); facultySelect.disabled = false;
            courseSelect.classList.add('d-none'); courseSelect.disabled = true;
        } else {
            facultySelect.classList.add('d-none'); facultySelect.disabled = true;
            courseSelect.classList.remove('d-none'); courseSelect.disabled = false;
        }
    }

    function loadEditForm(docData) {
        document.getElementById('editId').value = docData.id;
        document.getElementById('editDocumentName').value = docData.document_name;
        document.getElementById('editRemarks').value = docData.remarks || '';

        // Set target type
        const isFaculty = docData.faculty_id !== null;
        const isCourse = docData.course_id !== null;

        if (isFaculty) {
            document.getElementById('editTargetFaculty').checked = true;
            document.getElementById('editSelectFaculty').value = docData.faculty_id;
        } else if (isCourse) {
            document.getElementById('editTargetCourse').checked = true;
            document.getElementById('editSelectCourse').value = docData.course_id;
        }

        toggleEditTarget();

        // Display current file
        const fileName = docData.file_path.split('/').pop();
        document.getElementById('currentFile').innerHTML = '<a href="../../' + docData.file_path + '" target="_blank"><i class="bi bi-file-earmark-text"></i> ' + fileName + '</a>';
    }
    </script>
</body>
</html>
