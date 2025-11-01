<?php
session_start();

if (!isset($_SESSION["username"])) {
    header("Location: login.php");// if i change it to index.php i get this
    exit();
}
?>

<!DOCTYPE html>
<html>
<head><title>Home</title></head>
<body>
<h2>Welcome, <?php echo $_SESSION["username"]; ?>!</h2>
<p>You are logged in.</p>
<a href="welcome.php">Go to Dashboard</a><br>
<a href="logout.php">Logout</a>
</body>
</html>
