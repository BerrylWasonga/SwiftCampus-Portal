<?php
include("config.php");

echo "Verifying 'requisitions' table...\n";

// 1. Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'requisitions'");
if ($result->num_rows == 0) {
    die("FAIL: Table 'requisitions' does not exist.\n");
}
echo "PASS: Table exists.\n";

// 2. Insert Test Data
// Need a valid student_id. Let's pick one from users table or fail if empty.
$user_check = $conn->query("SELECT id FROM users LIMIT 1");
if ($user_check->num_rows == 0) {
    echo "SKIP: No users found to test FK constraint. Creating a temp user.\n";
    $conn->query("INSERT INTO users (first_name, last_name, email, password, role, reg_no) VALUES ('Test', 'User', 'test@test.com', 'pass', 'user', 'TEST1234')");
    $student_id = $conn->insert_id;
} else {
    $row = $user_check->fetch_assoc();
    $student_id = $row['id'];
}

$request_type = "Test Request";
$reason = "Test Reason";

$sql = "INSERT INTO requisitions (student_id, request_type, reason, status) VALUES (?, ?, ?, 'Pending')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $student_id, $request_type, $reason);

if ($stmt->execute()) {
    echo "PASS: Insert successful.\n";
    $inserted_id = $stmt->insert_id;
} else {
    die("FAIL: Insert failed: " . $stmt->error . "\n");
}

// 3. Read Data
$result = $conn->query("SELECT * FROM requisitions WHERE id = $inserted_id");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['status'] === 'Pending') {
        echo "PASS: Default status is 'Pending'.\n";
    } else {
        echo "FAIL: Default status is " . $row['status'] . "\n";
    }
} else {
    echo "FAIL: Could not read inserted row.\n";
}

// 4. Update Status (Admin simulation)
$conn->query("UPDATE requisitions SET status = 'Approved', admin_comment = 'OK' WHERE id = $inserted_id");
$result = $conn->query("SELECT * FROM requisitions WHERE id = $inserted_id");
$row = $result->fetch_assoc();
if ($row['status'] === 'Approved' && $row['admin_comment'] === 'OK') {
    echo "PASS: Admin update successful.\n";
} else {
    echo "FAIL: Admin update failed.\n";
}

// 5. Clean up
$conn->query("DELETE FROM requisitions WHERE id = $inserted_id");
// Optional: Delete temp user if created? Nah, keep it simple.

echo "Verification Complete.\n";
$conn->close();
?>
