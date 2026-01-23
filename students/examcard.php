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

// 1. Get Active Semester
$semester_query = "SELECT * FROM semesters WHERE status = 'active' LIMIT 1";
$sem_result = $conn->query($semester_query);
$current_semester = $sem_result->fetch_assoc();

$registered_units = [];
$fee_cleared = false;

if ($current_semester) {
    // 2. Get Approved Registered Units for active semester
    $units_query = "SELECT cu.unit_code, cu.unit_name, cr.registration_status, s.semester_name, ay.year_name
                    FROM course_registrations cr
                    JOIN course_units cu ON cr.unit_id = cu.id
                    JOIN semesters s ON cr.semester_id = s.id
                    JOIN academic_years ay ON s.academic_year_id = ay.id
                    WHERE cr.user_id = ? 
                    AND cr.semester_id = ? 
                    AND cr.registration_status = 'approved'";
    
    $stmt = $conn->prepare($units_query);
    $stmt->bind_param("ii", $_SESSION['user_id'], $current_semester['id']);
    $stmt->execute();
    $registered_units = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // 3. Check Fee Status for this semester
    // Assuming full payment is required for exam card
    $fee_query = "SELECT * FROM fee_payments WHERE user_id = ? AND semester_id = ?";
    $stmt = $conn->prepare($fee_query);
    $stmt->bind_param("ii", $_SESSION['user_id'], $current_semester['id']);
    $stmt->execute();
    $fee_result = $stmt->get_result();
    $fee_record = $fee_result->fetch_assoc();
    $stmt->close();

    if ($fee_record) {
        $balance = $fee_record['amount_required'] - $fee_record['amount_paid'];
        // Allow if balance is 0 or less (overpaid)
        // For testing, we might be lenient, but strict logic is balance <= 0
        $fee_cleared = ($balance <= 0);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Card - Student Dashboard</title>
    <!-- favicon -->
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
    
    <style>
        .exam-card-preview {
            border: 2px solid #000;
            padding: 2rem;
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 5rem;
            color: rgba(0,0,0,0.05);
            font-weight: bold;
            pointer-events: none;
            width: 100%;
            text-align: center;
        }
        .passport-photo {
            width: 120px;
            height: 120px;
            border: 1px solid #ddd;
            object-fit: cover;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .main-content {
                margin: 0 !important;
                padding: 0 !important;
            }
            #printableArea, #printableArea * {
                visibility: visible;
            }
            #printableArea {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
            }
            .no-print {
                display: none !important;
            }
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
            <h2>Examination Card</h2>
            <?php if ($fee_cleared && !empty($registered_units)): ?>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="bi bi-printer me-2"></i>Print Card
            </button>
            <?php endif; ?>
        </div>

        <?php if (!$current_semester): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i> No active semester found. Exam cards are only available during active semesters.
            </div>
        <?php elseif (empty($registered_units)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i> You have no approved course units registered for the current semester (<?php echo htmlspecialchars($current_semester['semester_name']); ?>).
                Please complete your course registration first.
            </div>
        <?php elseif (!$fee_cleared): ?>
            <div class="alert alert-danger">
                <i class="bi bi-bank me-2"></i> <strong>Fee Balance Outstanding.</strong> You cannot access your exam card until you have cleared your fees for the current semester.
                <br>
                Please visit the Finance Department or check your <a href="fee_stmnt.php" class="alert-link">Fee Statement</a>.
            </div>
        <?php else: ?>
            
            <div id="printableArea" class="exam-card-preview shadow-sm">
                <div class="watermark">OFFICIAL EXAM CARD</div>
                
                <!-- University Header -->
                <div class="text-center mb-4 border-bottom pb-3">
                    <img src="Assets/images/logo.png" alt="Logo" style="height: 60px;" class="mb-2">
                    <h3 class="text-uppercase mb-0">Chuka University</h3>
                    <p class="mb-0 fw-bold">Office of the Registrar (Academic Affairs)</p>
                    <h5 class="mt-2 text-decoration-underline">EXAMINATION CARD</h5>
                    <p class="mb-0"><?php echo htmlspecialchars($current_semester['semester_name']); ?></p>
                </div>

                <!-- Student Details -->
                <div class="row mb-4">
                    <div class="col-9">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td style="width: 120px;" class="fw-bold">Name:</td>
                                <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Reg. No:</td>
                                <td class="font-monospace fw-bold"><?php echo htmlspecialchars($user['reg_no']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Programme:</td>
                                <td><?php echo htmlspecialchars($user['programme']); ?></td>
                            </tr>
                            <tr>
                                <td class="fw-bold">Campus:</td>
                                <td><?php echo htmlspecialchars($user['campus']); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-3 text-end">
                        <!-- Placeholder for user photo -->
                        <div class="d-inline-block bg-light d-flex align-items-center justify-content-center passport-photo">
                            <i class="bi bi-person fs-1 text-secondary"></i>
                        </div>
                    </div>
                </div>

                <!-- Units Table -->
                <h6 class="fw-bold border-bottom mb-2 pb-1">REGISTERED COURSE UNITS</h6>
                <table class="table table-bordered table-sm mb-4">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 120px;">Unit Code</th>
                            <th>Unit Name</th>
                            <th style="width: 120px;">Invigilator Sign</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registered_units as $index => $unit): ?>
                        <tr>
                            <td class="text-center"><?php echo $index + 1; ?></td>
                            <td class="fw-bold"><?php echo htmlspecialchars($unit['unit_code']); ?></td>
                            <td><?php echo htmlspecialchars($unit['unit_name']); ?></td>
                            <td></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Footer Instructions -->
                <div class="small text-muted border-top pt-3 mt-5">
                    <p class="mb-1 fw-bold">Instructions to Candidates:</p>
                    <ol class="ps-3 mb-4">
                        <li>This card must be presented at every examination session.</li>
                        <li>Possession of any unauthorized material in the examination room is a serious offence.</li>
                        <li>Candidates must be seated 15 minutes before the start of the examination.</li>
                    </ol>
                    
                    <div class="row mt-4 align-items-end">
                        <div class="col-6">
                            <div class="border-top border-dark w-75 pt-1 text-center">
                                Student's Signature
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <div class="border-top border-dark w-75 ms-auto pt-1 text-center">
                                Registrar (AA) Signature & Stamp
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4 no-print">
                <small class="text-muted">Generated on <?php echo date('d M Y H:i:s'); ?></small>
            </div>
            
        <?php endif; ?>
    </div>
    
    <?php include_once 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>
</body>
</html>