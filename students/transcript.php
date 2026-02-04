<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

include '../config.php';

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all registered courses grouped by Academic Year and Semester
// Since there's no grades table yet, we'll just list the units and their status (e.g. Registered, Approved)
// In a real transcript, we'd join with a grades table.
$query = "SELECT cr.*, cu.unit_code, cu.unit_name, cu.credit_hours, s.semester_name, ay.year_name
          FROM course_registrations cr
          JOIN course_units cu ON cr.unit_id = cu.id
          JOIN semesters s ON cr.semester_id = s.id
          JOIN academic_years ay ON s.academic_year_id = ay.id
          WHERE cr.user_id = ? AND cr.registration_status = 'approved'
          ORDER BY s.start_date ASC, cu.unit_code ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Group records by Academic Year -> Semester
$transcript_data = [];
foreach ($records as $record) {
    $year = $record['year_name'];
    $sem = $record['semester_name'];
    
    if (!isset($transcript_data[$year])) {
        $transcript_data[$year] = [];
    }
    if (!isset($transcript_data[$year][$sem])) {
        $transcript_data[$year][$sem] = [];
    }
    
    $transcript_data[$year][$sem][] = $record;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provisional Transcript - Student Dashboard</title>
    <!-- favicon -->
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
    <style>
        .transcript-header {
            border-bottom: 2px solid #000;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
        }
        .semester-block {
            margin-bottom: 2rem;
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <?php 
        include_once 'partials/sidebar.php' ;
        include_once 'partials/top_navbar.php' ;
    ?>
    
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h2>Provisional Transcript</h2>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>Print Transcript
            </button>
        </div>

        <div class="card shadow-sm p-4 p-md-5">
            <!-- Header -->
            <div class="transcript-header text-center">
                <img src="Assets/images/29.png" alt="Logo" style="height: 80px;" class="mb-3">
                <h2 class="text-uppercase fw-bold mb-1">Otieno Academy</h2>
                <h4 class="text-uppercase mb-3">Academic Transcript (Provisional)</h4>
            </div>

            <!-- Student Info -->
            <div class="row mb-5">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
                    <p class="mb-1"><strong>Reg No:</strong> <?php echo htmlspecialchars($user['reg_no']); ?></p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1"><strong>Programme:</strong> <?php echo htmlspecialchars($user['programme']); ?></p>
                    <p class="mb-1"><strong>Date Issued:</strong> <?php echo date('d F Y'); ?></p>
                </div>
            </div>

            <!-- Academic History -->
            <?php if (empty($transcript_data)): ?>
                <div class="alert alert-info text-center">
                    No academic records found.
                </div>
            <?php else: ?>
                <?php foreach ($transcript_data as $year => $semesters): ?>
                    <div class="mb-4">
                        <h4 class="bg-light p-2 border-start border-4 border-primary"><?php echo htmlspecialchars($year); ?> Academic Year</h4>
                        
                        <?php foreach ($semesters as $semester => $units): ?>
                            <div class="semester-block ps-md-4">
                                <h5 class="text-muted mb-3 mt-3"><?php echo htmlspecialchars($semester); ?></h5>
                                <table class="table table-bordered table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 15%;">Unit Code</th>
                                            <th style="width: 50%;">Unit Title</th>
                                            <th style="width: 10%;" class="text-center">Credits</th>
                                            <th style="width: 15%;" class="text-center">Grade</th>
                                            <th style="width: 10%;" class="text-center">Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $sem_credits = 0;
                                        foreach ($units as $unit): 
                                            $sem_credits += $unit['credit_hours'];
                                        ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($unit['unit_code']); ?></td>
                                            <td><?php echo htmlspecialchars($unit['unit_name']); ?></td>
                                            <td class="text-center"><?php echo $unit['credit_hours']; ?></td>
                                            <td class="text-center text-muted">
                                                <!-- Placeholder for Grade -->
                                                -
                                            </td>
                                            <td class="text-center">
                                                <!-- Placeholder for Points/Result -->
                                                Pass
                                                <!-- Logic for Pass/Fail would go here based on marks -->
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="fw-bold bg-light">
                                            <td colspan="2" class="text-end">Semester Total:</td>
                                            <td class="text-center"><?php echo $sem_credits; ?></td>
                                            <td colspan="2"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                
                <div class="mt-5 pt-4 border-top">
                    <div class="row">
                        <div class="col-md-8 text-muted fst-italic">
                            <small>
                                Key to Grades: A (70-100) - Excellent; B (60-69) - Good; C (50-59) - Satisfactory; D (40-49) - Pass; F (0-39) - Fail.<br>
                                This is a provisional transcript and is not valid without the official University Seal.
                            </small>
                        </div>
                        <div class="col-md-4 text-center mt-4 mt-md-0">
                            <div class="border-bottom border-dark mb-2" style="height: 50px;"></div>
                            <p class="fw-bold">Registrar (Academic Affairs)</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include_once 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
</body>
</html>