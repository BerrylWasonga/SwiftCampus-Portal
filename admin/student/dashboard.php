<?php
session_start();
require_once '../../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

// Fetch Hierarchy Context
$faculty_id = $_GET['faculty_id'] ?? null;
$course_id = $_GET['course_id'] ?? null;
$year_level = $_GET['year_level'] ?? null;

// Summary Stats
$stats = [
    'faculties' => $conn->query("SELECT COUNT(*) FROM faculties")->fetch_row()[0],
    'courses' => $conn->query("SELECT COUNT(*) FROM courses")->fetch_row()[0],
    'students' => $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetch_row()[0]
];

// Breadcrumbs / Titles
$title = "Student Directory";
$breadcrumb = [];

if ($faculty_id) {
    $faculty = $conn->query("SELECT faculty_name FROM faculties WHERE id = $faculty_id")->fetch_assoc();
    $breadcrumb[] = ['name' => $faculty['faculty_name'], 'link' => "dashboard.php?faculty_id=$faculty_id"];
}
if ($course_id) {
    $course = $conn->query("SELECT course_name FROM courses WHERE id = $course_id")->fetch_assoc();
    $breadcrumb[] = ['name' => $course['course_name'], 'link' => "dashboard.php?faculty_id=$faculty_id&course_id=$course_id"];
}
if ($year_level) {
    $breadcrumb[] = ['name' => "Year $year_level", 'link' => "#"];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Directory - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/main.css" rel="stylesheet"/>
    <style>
        .hierarchy-card { cursor: pointer; transition: transform 0.2s; border-left: 4px solid #0d6efd; }
        .hierarchy-card:hover { transform: scale(1.02); background-color: #f8f9fa; }
        .stat-icon { font-size: 2rem; opacity: 0.3; }
        .student-row:hover { background-color: #f1f8ff !important; }
    </style>
</head>
<body>
    <?php include '../partials/top_navbar.php'; ?>
    <?php include '../partials/sidebar.php'; ?>
    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <!-- Header & Stats -->
            <div class="row mb-4 align-items-center">
                <div class="col">
                    <h2 class="mb-1"><i class="bi bi-diagram-3 me-2"></i>Student Hierarchy</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">All Faculties</a></li>
                            <?php foreach($breadcrumb as $b): ?>
                                <li class="breadcrumb-item"><a href="<?php echo $b['link']; ?>"><?php echo htmlspecialchars($b['name']); ?></a></li>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                </div>
                <div class="col-auto">
                    <a href="add.php" class="btn btn-primary"><i class="bi bi-person-plus me-2"></i>New Student</a>
                </div>
            </div>

            <?php if (!$faculty_id): ?>
                <!-- Level 1: Faculties -->
                <div class="row g-4">
                    <?php 
                    $facs = $conn->query("SELECT f.*, (SELECT COUNT(*) FROM users u WHERE u.faculty_id = f.id) as student_count FROM faculties f ORDER BY faculty_name");
                    while($f = $facs->fetch_assoc()):
                    ?>
                    <div class="col-md-4">
                        <div class="card hierarchy-card h-100 shadow-sm" onclick="location.href='dashboard.php?faculty_id=<?php echo $f['id']; ?>'">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($f['faculty_name']); ?></h5>
                                    <span class="badge bg-primary rounded-pill"><?php echo $f['student_count']; ?> Students</span>
                                </div>
                                <i class="bi bi-building stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

            <?php elseif ($faculty_id && !$course_id): ?>
                <!-- Level 2: Courses -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="alert alert-info py-2"><i class="bi bi-info-circle me-2"></i>Select a Course to view year levels</div>
                    </div>
                    <?php 
                    $courses = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM users u WHERE u.course_id = c.id) as student_count FROM courses c WHERE faculty_id = $faculty_id ORDER BY c.course_name");
                    while($c = $courses->fetch_assoc()):
                    ?>
                    <div class="col-md-6">
                        <div class="card hierarchy-card h-100 shadow-sm" onclick="location.href='dashboard.php?faculty_id=<?php echo $faculty_id; ?>&course_id=<?php echo $c['id']; ?>'">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1"><?php echo htmlspecialchars($c['course_code']); ?></h6>
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($c['course_name']); ?></h5>
                                    <span class="badge bg-success rounded-pill"><?php echo $c['student_count']; ?> Students</span>
                                </div>
                                <i class="bi bi-book stat-icon"></i>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

            <?php elseif ($course_id && !$year_level): ?>
                <!-- Level 3: Year Levels -->
                <div class="row g-4 text-center">
                    <?php for($i=1; $i<=4; $i++): 
                        $count = $conn->query("SELECT COUNT(*) FROM users WHERE course_id = $course_id AND year_level = $i")->fetch_row()[0];
                    ?>
                    <div class="col-md-3">
                        <div class="card hierarchy-card shadow-sm" onclick="location.href='dashboard.php?faculty_id=<?php echo $faculty_id; ?>&course_id=<?php echo $course_id; ?>&year_level=<?php echo $i; ?>'">
                            <div class="card-body">
                                <h3 class="fw-bold text-primary mb-1">Year <?php echo $i; ?></h3>
                                <div class="fs-5 mb-0 text-muted"><?php echo $count; ?> Students</div>
                            </div>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>

            <?php else: ?>
                <!-- Level 4: Student List -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0">Year <?php echo $year_level; ?> Students</h5>
                        <div class="input-group w-25">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" id="studentSearch" class="form-control border-start-0 ps-0" placeholder="Search students...">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="studentsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Reg No</th>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $students = $conn->query("SELECT * FROM users WHERE course_id = $course_id AND year_level = $year_level AND role='user' ORDER BY reg_no ASC");
                                    if($students->num_rows > 0):
                                        while($s = $students->fetch_assoc()):
                                    ?>
                                    <tr class="student-row">
                                        <td class="ps-4"><code><?php echo htmlspecialchars($s['reg_no']); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($s['first_name'].' '.$s['last_name']); ?></strong></td>
                                        <td><?php echo $s['gender'] ?: 'N/A'; ?></td>
                                        <td><small><?php echo htmlspecialchars($s['email']); ?></small></td>
                                        <td>
                                            <span class="badge rounded-pill bg-<?php echo ($s['status']=='active'?'success':'secondary'); ?>-subtle text-<?php echo ($s['status']=='active'?'success':'secondary'); ?>">
                                                <?php echo ucfirst($s['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="view.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-light" title="View"><i class="bi bi-eye"></i></a>
                                            <a href="edit.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-light text-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                                            <button class="btn btn-sm btn-light text-danger" title="Delete" onclick="deleteStudent(<?php echo $s['id']; ?>)"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr><td colspan="6" class="text-center py-5 text-muted">No students found in this year level.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/main.js"></script>
    <script>
    $(document).ready(function() {
        // Simple search filter
        $("#studentSearch").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("#studentsTable tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });

    function deleteStudent(id) {
        if(confirm('Are you sure you want to delete this student?')) {
            const form = $('<form method="POST" action="list.php"></form>');
            form.append('<input type="hidden" name="delete_student" value="1">');
            form.append('<input type="hidden" name="student_id" value="' + id + '">');
            $('body').append(form);
            form.submit();
        }
    }
    </script>
</body>
</html>
