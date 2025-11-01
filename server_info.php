<?php
session_start();

echo "<h2>Exploring \$_SERVER Superglobal</h2>";
echo "<p><a href='index.php'>← Back to Home</a></p>";

echo "<table border='1' cellpadding='6' cellspacing='0'>";
foreach ($_SERVER as $key => $value) {
    echo "<tr><td><strong>$key</strong></td><td>$value</td></tr>";
}
echo "</table>";
?>
