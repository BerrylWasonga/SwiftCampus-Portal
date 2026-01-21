    <?php

// Only allow logged-in students (role = 'user')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../login.php");
    exit();
}

// Include database connection
include '../config.php';

// Fetch ALL user details using the session user_id
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

// Build full name safely
$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
if (empty($full_name)) {
    $full_name = $user['email'] ?? 'User';
}
?>
    <!-- ========== FOOTER ========== -->
    <footer class="footer text-center" id="footer">
        <div class="container">
            <p class="mb-1">&copy; <?php echo date('Y'); ?> Designed by
                <a href="https://bechimp-rose.vercel.app/" target="_blank" rel="noopener noreferrer">
                    Berryl Wasonga
                </a>
            </p>
        </div>
    </footer>