<?php
$host = "localhost";
$user = "root";      // your DB username
$pass = "";          // your DB password
$db = "auth"; // your database

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}




//close the connection
//$conn -> close();

// SMTP Configuration
/*
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_PORT', 587);
define('SMTP_FROM', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Student Portal');

*/

?>