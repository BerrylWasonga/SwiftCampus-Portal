<?php
// Start session if needed for user data
session_start();

// Example user data (replace with actual session or database data)
$fullName = "BERYL ADHIAMBO WASONGA";
$regNumber = "CS/12345/22"; // Adjust as needed
$currentSemester = "SEM1-25/26";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f8f0;
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 260px;
            background-color: #003366;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo img {
            width: 180px;
        }
        .sidebar h3 {
            text-transform: uppercase;
            font-size: 13px;
            color: #a0d0ff;
            margin: 25px 0 10px;
        }
        .sidebar ul {
            list-style: none;
        }
        .sidebar ul li a {
            display: block;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: 0.3s;
        }
        .sidebar ul li a:hover, .sidebar ul li a.active {
            background-color: #004488;
        }
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 20px;
        }
        .header {
            background-color: #00a86b;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .user-info {
            text-align: right;
            margin-bottom: 10px;
            color: #003366;
            font-weight: bold;
        }
        .registration-box {
            background-color: white;
            padding: 30px;
            border: 3px solid #4caf50;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .registration-box h2 {
            text-align: center;
            color: #003366;
            margin-bottom: 10px;
        }
        .semester-info {
            text-align: center;
            font-size: 18px;
            margin-bottom: 20px;
            color: #d32f2f;
        }
        .instructions {
            background-color: #fff0f0;
            border: 1px solid #ffcccc;
            padding: 15px;
            border-radius: 8px;
            color: #d32f2f;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        .filters {
            background-color: #e0e0e0;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .filters label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }
        .filters select {
            width: 100%;
            padding: 12px;
            border: 1px solid #aaa;
            border-radius: 5px;
            font-size: 16px;
        }
        .get-units-btn {
            text-align: center;
            margin-top: 20px;
        }
        .get-units-btn button {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 12px 40px;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .get-units-btn button:hover {
            background-color: #d32f2f;
        }
        .course-list-placeholder {
            text-align: center;
            color: #777;
            font-style: italic;
            padding: 40px;
            background-color: #f9f9f9;
            border: 1px dashed #ccc;
            border-radius: 8px;
        }
        footer {
            text-align: center;
            padding: 15px;
            background-color: #003366;
            color: white;
            margin-top: 40px;
            font-size: 13px;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo">
            <img src="https://www.chuka.ac.ke/wp-content/uploads/2018/06/chuka-university-logo.png" alt="University Logo">
        </div>

        <ul>
            <h3>Dashboard</h3>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="profile.php">Personal Profile</a></li>
            
            <h5>Academics</h5>
            <li><a href="course_registration.php" class="active">Course Registration</a></li>
            <li><a href="timetable.php">Time Table</a></li>
            <li><a href="requisition.php">Academic Requisition</a></li>
            <li><a href="evaluation.php">Course Evaluation</a></li>
            
            <h3>Request</h3>
            <li><a href="clearance.php">Clearance Request</a></li>
            
            <h3>Financials</h3>
            <li><a href="fee_statement.php">Fee Statement</a></li>
            <li><a href="legacy.php">Legacy Statement</a></li>
            <li><a href="receipts.php">Receipts</a></li>
            <li><a href="gatepass.php">Generate Gate Pass</a></li>
            <li><a href="proforma.php">Semester Proforma</a></li>
            
            <h3>Accommodation</h3>
            <li><a href="hostel.php">Hostel Booking</a></li>
            
            <h3>Examination</h3>
        </ul>
    </div>

    <div class="main-content">
        <div class="user-info">
            Welcome, <?php echo htmlspecialchars($fullName); ?> 
        </div>

        <div class="header">
            Course Registration
        </div>

        <div class="registration-box">
            <h2>COURSE REGISTRATION</h2>
            <div class="semester-info">
                <strong>Registration Semester: <?php echo $currentSemester; ?></strong>
            </div>

            <div class="instructions">
                Click on the button "Get Units". Select your Courses and click button "Add Selected Courses to the Basket". 
                Confirm them then Complete registration by clicking button "Complete Registration". 
                You can view Proforma Invoice by clicking button "View Proforma Invoice".
            </div>

            <div class="filters">
                <label for="reg-type">Registration Type:</label>
                <select name="registration_type" id="reg-type">
                    <option value="">---</option>
                    <option value="regular">Regular</option>
                    <option value="supplementary">Supplementary</option>
                    <option value="special">Special/Repeat</option>
                </select>

                <div class="get-units-btn">
                    <button type="button" onclick="alert('Fetching available units... (Connect to backend for real functionality)')">
                        Get Units
                    </button>
                </div>
            </div>

            <div class="course-list-placeholder">
                (Available course units will be displayed here after selecting Registration Type and clicking "Get Units")
            </div>
        </div>
    </div>

    <footer>
        © 2026 Designed by Berryl Wasonga
    </footer>

</body>
</html>