<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

include '../../config.php';

$search_reg = '';
$student = null;
$academic_data = [];

if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_reg = trim($_GET['search']);
    
    // Check if student exists
    $u_stmt = $conn->prepare("SELECT * FROM users WHERE reg_no = ? AND role = 'user'");
    $u_stmt->bind_param("s", $search_reg);
    $u_stmt->execute();
    $student = $u_stmt->get_result()->fetch_assoc();
    $u_stmt->close();

    if ($student) {
        $student_id = $student['id'];
        // Fetch Academic Years and Semesters for this student's approved registrations
        $ay_query = "
            SELECT DISTINCT ay.id as year_id, ay.year_name, s.id as sem_id, s.semester_name 
            FROM course_registrations cr
            JOIN semesters s ON cr.semester_id = s.id
            JOIN academic_years ay ON s.academic_year_id = ay.id
            WHERE cr.user_id = ? AND cr.registration_status = 'approved'
            ORDER BY ay.year_name DESC, s.semester_name ASC
        ";
        $ay_stmt = $conn->prepare($ay_query);
        $ay_stmt->bind_param("i", $student_id);
        $ay_stmt->execute();
        $academic_periods = $ay_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $ay_stmt->close();

        // Organize data
        foreach ($academic_periods as $period) {
            $sem_key = $period['year_name'] . ' - ' . $period['semester_name'];
            
            // Get Units for this period
            $units_sql = "
                SELECT cu.unit_code, cu.unit_name, cu.credit_hours, cr.credit_hours as reg_credit
                FROM course_registrations cr
                JOIN course_units cu ON cr.unit_id = cu.id
                WHERE cr.user_id = ? AND cr.semester_id = ? AND cr.registration_status = 'approved'
            ";
            $u_stmt = $conn->prepare($units_sql);
            $u_stmt->bind_param("ii", $student_id, $period['sem_id']);
            $u_stmt->execute();
            $units = $u_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $u_stmt->close();

            $academic_data[$sem_key] = $units;
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Transcripts - Admin</title>
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
                <h2>Student Academic Transcripts</h2>
            </div>
            
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="search" class="form-control" placeholder="Enter Student Reg No" value="<?php echo htmlspecialchars($search_reg); ?>" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">View Transcript</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($search_reg && !$student): ?>
                <div class="alert alert-danger">Student with Reg No '<?php echo htmlspecialchars($search_reg); ?>' not found.</div>
            <?php endif; ?>

            <?php if ($student): ?>
                <div class="card mb-4" id="printArea">
                    <div class="card-header bg-white text-center py-4">
                        <h4 class="mb-0">ACADEMIC TRANSCRIPT (PROVISIONAL)</h4>
                        <p class="text-muted mb-0">Student: <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong> | Reg No: <strong><?php echo htmlspecialchars($student['reg_no']); ?></strong></p>
                    </div>
                    <div class="card-body">
                        <?php if (empty($academic_data)): ?>
                            <div class="alert alert-warning">No approved academic records found for this student.</div>
                        <?php else: ?>
                            <?php foreach ($academic_data as $period => $units): ?>
                                <div class="mb-4">
                                    <h5 class="text-primary border-bottom pb-2"><?php echo htmlspecialchars($period); ?></h5>
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Code</th>
                                                <th>Unit Title</th>
                                                <th>Credit Hours</th>
                                                <th>Grade</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($units as $unit): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($unit['unit_code']); ?></td>
                                                <td><?php echo htmlspecialchars($unit['unit_name']); ?></td>
                                                <td><?php echo $unit['reg_credit']; ?></td>
                                                <td><em>Pass</em></td> <!-- Placeholder Grade -->
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($academic_data)): ?>
                <div class="text-end">
                    <button onclick="window.print()" class="btn btn-success"><i class="bi bi-printer me-2"></i>Print Transcript</button>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/main.js"></script>
</body>
</html>
