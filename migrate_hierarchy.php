<?php
include("config.php");

echo "--- Starting Hierarchy Migration (3-tier to 2-tier) ---\n";

// Disable foreign key checks for the structural changes
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// 1. Add faculty_id to courses table
if (!$conn->query("SHOW COLUMNS FROM courses LIKE 'faculty_id'")->num_rows) {
    if ($conn->query("ALTER TABLE courses ADD COLUMN faculty_id INT AFTER id")) {
        echo "Added faculty_id to courses table.\n";
    } else {
        die("Error adding faculty_id: " . $conn->error . "\n");
    }
}

// 2. Populate courses.faculty_id from programs
$res = $conn->query("SELECT c.id, p.faculty_id FROM courses c JOIN programs p ON c.program_id = p.id");
while ($row = $res->fetch_assoc()) {
    $conn->query("UPDATE courses SET faculty_id = {$row['faculty_id']} WHERE id = {$row['id']}");
}
echo "Populated faculty_id in courses table.\n";

// 3. Update foreign key for courses
$conn->query("ALTER TABLE courses DROP FOREIGN KEY courses_ibfk_program");
$conn->query("ALTER TABLE courses DROP COLUMN program_id");
$conn->query("ALTER TABLE courses ADD CONSTRAINT courses_ibfk_faculty FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE CASCADE");
echo "Updated courses table constraints.\n";

// 4. Update users table
if ($conn->query("SHOW COLUMNS FROM users LIKE 'program_id'")->num_rows) {
    $conn->query("ALTER TABLE users DROP FOREIGN KEY users_ibfk_program");
    $conn->query("ALTER TABLE users DROP COLUMN program_id");
    echo "Removed program_id from users table.\n";
}

// 5. Drop programs table
$conn->query("DROP TABLE IF EXISTS programs");
echo "Dropped programs table.\n";

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "Migration complete.\n";

$conn->close();
?>
