<?php
session_start();
include("config.php");

// Security: Must be logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$full_name = trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);
if (empty($full_name)) $full_name = $_SESSION['email'];
$admin_name = $full_name;

// Current year (short)
$current_year = date('y');

// Course prefixes mapped to faculty and full programme name
$course_prefixes = [
    // Faculty of Science & Technology
    'cs' => ['prefix' => 'EB1', 'name' => 'Bachelor of Science (Computer Science)'],
    'acs' => ['prefix' => 'EB2', 'name' => 'Bachelor of Science (Applied Computer Science)'],
    'bit' => ['prefix' => 'EB3', 'name' => 'Bachelor of Science (Business Information Technology)'],
    'bio' => ['prefix' => 'EB4', 'name' => 'Bachelor of Science (Biology)'],
    'math' => ['prefix' => 'EB5', 'name' => 'Bachelor of Science (Mathematics)'],

    // Faculty of Nursing & Public Health
    'nursing' => ['prefix' => 'CB1', 'name' => 'Bachelor of Science (Nursing)'],
    'public_health' => ['prefix' => 'CB2', 'name' => 'Bachelor of Public Health'],
    'nutrition' => ['prefix' => 'CB3', 'name' => 'Bachelor of Science (Human Nutrition & Dietetics)'],

    // Faculty of Business Studies
    'commerce' => ['prefix' => 'BB1', 'name' => 'Bachelor of Commerce'],
    'procurement' => ['prefix' => 'BB2', 'name' => 'Bachelor of Procurement & Logistics Management'],
    'entrepreneurship' => ['prefix' => 'BB3', 'name' => 'Bachelor of Entrepreneurship & Enterprise Management'],

    // Faculty of Agriculture
    'agriculture' => ['prefix' => 'AG1', 'name' => 'Bachelor of Science (Agriculture)'],
    'horticulture' => ['prefix' => 'AG2', 'name' => 'Bachelor of Science (Horticulture)'],
    'animal_science' => ['prefix' => 'AG3', 'name' => 'Bachelor of Science (Animal Science)'],

    // Add more as needed...
];

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_student'])) {
        $first_name = trim($_POST["first_name"]);
        $last_name = trim($_POST["last_name"]);
        $email = trim($_POST["email"]);
        $course_key = $_POST["course"];
        $gender = $_POST["gender"] ?? '';
        $dob = $_POST["dob"] ?? '';
        $address = trim($_POST["address"] ?? '');
        $campus = $_POST["campus"] ?? 'MAIN';

        if (empty($first_name) || empty($last_name) || empty($email) || empty($course_key)) {
            $error = "Required fields are missing.";
        } elseif (!array_key_exists($course_key, $course_prefixes)) {
            $error = "Invalid course selected.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Check duplicate email
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = "Email already registered.";
                $check_stmt->close();
            } else {
                $course_info = $course_prefixes[$course_key];
                $prefix = $course_info['prefix'];
                $programme = $course_info['name'];
                $year_short = $current_year;

                // Auto-generate next Reg. No
                $like_pattern = $prefix . '/%/' . $year_short;
                $max_stmt = $conn->prepare("SELECT reg_no FROM users WHERE reg_no LIKE ? ORDER BY reg_no DESC LIMIT 1");
                $max_stmt->bind_param("s", $like_pattern);
                $max_stmt->execute();
                $max_result = $max_stmt->get_result();

                $next_num = 1;
                if ($max_result->num_rows > 0) {
                    $last_reg = $max_result->fetch_assoc()['reg_no'];
                    preg_match('/\/(\d+)\//', $last_reg, $matches);
                    if (isset($matches[1])) $next_num = intval($matches[1]) + 1;
                }
                $max_stmt->close();

                $seq = str_pad($next_num, 5, '0', STR_PAD_LEFT);
                $reg_no = $prefix . '/' . $seq . '/' . $year_short;

                // Generate random password
                $default_password_plain = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$'), 0, 10);
                $hashed_password = password_hash($default_password_plain, PASSWORD_DEFAULT);

                // Insert student
                $insert_stmt = $conn->prepare("INSERT INTO users 
                    (first_name, last_name, email, password, reg_no, gender, dob, address, campus, programme, role, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'user', 'active')");
                $insert_stmt->bind_param("ssssssssss", $first_name, $last_name, $email, $hashed_password, $reg_no, $gender, $dob, $address, $campus, $programme);

                if ($insert_stmt->execute()) {
                    $message = "<div class='alert alert-success'>
                        <strong>Student added successfully!</strong><br>
                        Reg. No: <strong>$reg_no</strong><br>
                        Programme: <strong>$programme</strong><br>
                        Default Password: <strong>$default_password_plain</strong><br>
                        <small>Student has been notified via email (in production). Advise them to change password on first login.</small>
                    </div>";
                } else {
                    $error = "Failed to add student.";
                }
                $insert_stmt->close();
            }
        }
    }
}

// Fetch stats and users
$total_stmt = $conn->prepare("SELECT COUNT(*) FROM users");
$total_stmt->execute();
$total_users = $total_stmt->get_result()->fetch_row()[0];
$total_stmt->close();

$active_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE status = 'active'");
$active_stmt->execute();
$active_users = $active_stmt->get_result()->fetch_row()[0];
$active_stmt->close();

$users_stmt = $conn->prepare("SELECT id, first_name, last_name, email, reg_no, programme, role, status, last_login FROM users ORDER BY created_at DESC");
$users_stmt->execute();
$users_result = $users_stmt->get_result();
$users = $users_result->fetch_all(MYSQLI_ASSOC);
$users_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Chuka University</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .table th { background-color: #0d47a1; color: white; }
    </style>
</head>
<body class="p-4">

<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="mb-0">Admin Dashboard</h2>
        <p class="text-muted mb-0">Welcome, <?php echo htmlspecialchars($admin_name); ?>!</p>
    </div>
    <a href="logout.php" class="btn btn-danger btn-lg">Logout</a>
</div>

<?php if (!empty($message)) echo $message; ?>
<?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

<div class="row mb-5">
    <div class="col-md-4">
        <div class="card text-center p-4">
            <h5>Total Students & Staff</h5>
            <h2 class="text-primary"><?php echo $total_users; ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-4">
            <h5>Active Users</h5>
            <h2 class="text-success"><?php echo $active_users; ?></h2>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center p-4">
            <h5>New Registrations Today</h5>
            <h2 class="text-info"><?php echo $active_users; ?></h2> <!-- You can add query for today if needed -->
        </div>
    </div>
</div>

<!-- Add New Student -->
<div class="card mb-5">
    <div class="card-header bg-primary text-white">
        <h5><i class="bi bi-person-plus"></i> Add New Student</h5>
    </div>
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="add_student" value="1">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Programme *</label>

                    <select name="course" id="course" class="form-select" required>
                        <option value="">-- Select Programme --</option>

                        <!-- ================= CERTIFICATE PROGRAMMES ================= -->

                       <optgroup label="Certificate – Faculty of Science & Technology">
                            <option value="cert_it">Certificate in Information Technology (CST1)</option>
                            <option value="cert_comp">Certificate in Computer Applications (CST2)</option>
                       </optgroup>

                        <optgroup label="Certificate – Faculty of Business Studies">
                            <option value="cert_business">Certificate in Business Management (CBU1)</option>
                            <option value="cert_procurement">Certificate in Procurement & Logistics (CBU2)</option>
                        </optgroup>

                        <optgroup label="Certificate – Faculty of Agriculture">
                            <option value="cert_agriculture">Certificate in Agriculture (CAG1)</option>
                            <option value="cert_animal">Certificate in Animal Health & Production (CAG2)</option>
                        </optgroup>

                        <optgroup label="Certificate – Humanities & Social Sciences">
                            <option value="cert_community">Certificate in Community Development (CH1)</option>
                            <option value="cert_criminology">Certificate in Criminology & Security Studies (CH2)</option>
                        </optgroup>

                        <optgroup label="Certificate – Education">
                            <option value="cert_ecde">Certificate in Early Childhood Development (CED1)</option>
                        </optgroup>

                        <optgroup label="Certificate – Health Sciences">
                            <option value="cert_public_health">Certificate in Public Health (CHS1)</option>
                        </optgroup>

                        <!-- ================= DIPLOMA PROGRAMMES ================= -->

                        <optgroup label="Diploma – Faculty of Science & Technology">
                            <option value="dip_it">Diploma in Information Technology (DST1)</option>
                            <option value="dip_comp">Diploma in Computer Science (DST2)</option>
                        </optgroup>

                        <optgroup label="Diploma – Faculty of Business Studies">
                            <option value="dip_accounting">Diploma in Accounting (DBU1)</option>
                            <option value="dip_business">Diploma in Business Management (DBU2)</option>
                            <option value="dip_hr">Diploma in Human Resource Management (DBU3)</option>
                            <option value="dip_procurement">Diploma in Procurement & Logistics Management (DBU4)</option>
                        </optgroup>

                        <optgroup label="Diploma – Faculty of Agriculture">
                            <option value="dip_agriculture">Diploma in Agriculture (DAG1)</option>
                            <option value="dip_horticulture">Diploma in Horticulture (DAG2)</option>
                            <option value="dip_animal">Diploma in Animal Health & Production (DAG3)</option>
                        </optgroup>

                        <optgroup label="Diploma – Humanities & Social Sciences">
                            <option value="dip_community">Diploma in Community Development (DH1)</option>
                            <option value="dip_criminology">Diploma in Criminology & Security Studies (DH2)</option>
                            <option value="dip_public_admin">Diploma in Public Administration (DH3)</option>
                        </optgroup>

                        <optgroup label="Diploma – Education">
                            <option value="dip_ecde">Diploma in Early Childhood Education (DED1)</option>
                        </optgroup>

                        <optgroup label="Diploma – Health Sciences">
                            <option value="dip_nursing">Diploma in Community Health Nursing (DHS1)</option>
                            <option value="dip_health_records">Diploma in Health Records & Information Management (DHS2)</option>
                        </optgroup>

                        <!-- ================= DEGREE PROGRAMMES ================= -->

                        <optgroup label="Faculty of Science & Technology">
                            <option value="cs">B.Sc. Computer Science (EB1)</option>
                            <option value="acs">B.Sc. Applied Computer Science (EB2)</option>
                            <option value="bit">B.Sc. Business Information Technology (EB3)</option>
                            <option value="bio">B.Sc. Biology (EB4)</option>
                            <option value="math">B.Sc. Mathematics (EB5)</option>
                            <option value="chem">B.Sc. Industrial Chemistry (EB6)</option>
                            <option value="phy">B.Sc. Physics (EB7)</option>
                        </optgroup>

                        <optgroup label="School of Nursing & Public Health">
                            <option value="nursing">B.Sc. Nursing (CB1)</option>
                            <option value="public_health">Bachelor of Public Health (CB2)</option>
                            <option value="nutrition">B.Sc. Human Nutrition & Dietetics (CB3)</option>
                            <option value="health_records">B.Sc. Health Records & Information Management (CB4)</option>
                        </optgroup>

                        <optgroup label="Faculty of Business Studies">
                            <option value="commerce">Bachelor of Commerce (BB1)</option>
                            <option value="procurement">B. Procurement & Logistics Management (BB2)</option>
                            <option value="entrepreneurship">B. Entrepreneurship & Enterprise Management (BB3)</option>
                            <option value="coop">Bachelor of Co-operative Management (BB4)</option>
                            <option value="economics">B.Sc. Economics & Statistics (BB5)</option>
                        </optgroup>

                        <optgroup label="Faculty of Agriculture & Environmental Studies">
                            <option value="agriculture">B.Sc. Agriculture (AG1)</option>
                            <option value="horticulture">B.Sc. Horticulture (AG2)</option>
                            <option value="animal_science">B.Sc. Animal Science (AG3)</option>
                            <option value="agribusiness">Bachelor of Agribusiness Management (AG4)</option>
                            <option value="food_science">B.Sc. Food Science & Technology (AG5)</option>
                            <option value="tourism">Bachelor of Tourism Management (AG6)</option>
                            <option value="ecotourism">B.Sc. Ecotourism & Hospitality Management (AG7)</option>
                        </optgroup>

                        <optgroup label="Faculty of Humanities & Social Sciences">
                            <option value="sociology">Bachelor of Arts (Sociology) (HA1)</option>
                            <option value="criminology">B.A. Criminology & Security Studies (HA2)</option>
                            <option value="communication">B.A. Communication & Media Studies (HA3)</option>
                            <option value="community_dev">B.Sc. Community Development (HA4)</option>
                            <option value="psychology">B.A. Psychology (HA5)</option>
                        </optgroup>

                        <optgroup label="Faculty of Education & Resources Development">
                            <option value="bed_arts">Bachelor of Education (Arts) (ED1)</option>
                            <option value="bed_science">Bachelor of Education (Science) (ED2)</option>
                            <option value="ecde">B.Ed. Early Childhood Education (ED3)</option>
                        </optgroup>

                        <optgroup label="Faculty of Engineering">
                            <option value="eee">B.Sc. Electrical & Electronics Engineering (EN1)</option>
                        </optgroup>

                        <optgroup label="School of Law">
                            <option value="law">Bachelor of Laws (LL.B) (LW1)</option>
                        </optgroup>


                        <!-- ================= MASTERS PROGRAMMES ================= -->

                        <optgroup label="Masters – Science & Technology">
                            <option value="msc_cs" data-level="masters" data-kuccps="MSC501">
                                M.Sc. Computer Science (MSC501)
                            </option>
                            <option value="msc_it" data-level="masters" data-kuccps="MSC502">
                                M.Sc. Information Technology (MSC502)
                            </option>
                        </optgroup>

                        <optgroup label="Masters – Business Studies">
                            <option value="mba" data-level="masters" data-kuccps="MBA503">
                                Master of Business Administration (MBA503)
                            </option>
                        </optgroup>

                        <optgroup label="Masters – Education">
                            <option value="med" data-level="masters" data-kuccps="MED504">
                                Master of Education (MED504)
                            </option>
                        </optgroup>

                        <!-- ================= PHD PROGRAMMES ================= -->

                        <optgroup label="PhD – Science & Technology">
                            <option value="phd_cs" data-level="phd" data-kuccps="PHD701">
                                PhD in Computer Science (PHD701)
                            </option>
                        </optgroup>

                        <optgroup label="PhD – Business & Management">
                            <option value="phd_business" data-level="phd" data-kuccps="PHD702">
                                PhD in Business Administration (PHD702)
                            </option>
                        </optgroup>

                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-select">
                        <option value="">Select</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="dob" class="form-control">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Campus</label>
                    <select name="campus" class="form-select">
                        <option value="MAIN">MAIN</option>
                        <option value="TOWN">TOWN</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-success btn-lg w-100 mt-4">Add Student & Generate Credentials</button>
        </form>
    </div>
</div>

<!-- Users List -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5><i class="bi bi-people"></i> Registered Users</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Reg. No</th>
                        <th>Email</th>
                        <th>Programme</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $i => $u): ?>
                        <?php 
                        $u_name = trim($u['first_name'] . ' ' . $u['last_name']);
                        if (empty($u_name)) $u_name = $u['email'];
                        ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($u_name); ?></td>
                            <td><strong><?php echo htmlspecialchars($u['reg_no'] ?? 'N/A'); ?></strong></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['programme'] ?? 'N/A'); ?></td>
                            <td><?php echo $u['role']; ?></td>
                            <td><?php echo $u['status']; ?></td>
                            <td><?php echo $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : 'Never'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function () {
    $('#course').select2({
        placeholder: "-- Select Programme --",
        allowClear: true,
        width: '100%'
    });
});
</script>

</body>
</html>