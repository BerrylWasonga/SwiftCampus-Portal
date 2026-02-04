<?php
include("config.php");

$sql = "CREATE TABLE IF NOT EXISTS requisitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    request_type VARCHAR(50) NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    admin_comment TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'requisitions' created successfully or already exists.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
