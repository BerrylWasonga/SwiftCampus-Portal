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

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_user'])) {
        // Add new user
        $first_name = trim($_POST["first_name"]);
        $last_name = trim($_POST["last_name"]);
        $email = trim($_POST["email"]);
        $password = $_POST["password"];
        $role = $_POST["role"];

        if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
            $error = "All fields are required.";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Check if email exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = "Email already registered.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, status, last_login) VALUES (?, ?, ?, ?, ?, 'active', NULL)");
                $insert_stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);
                if ($insert_stmt->execute()) {
                    $message = "User added successfully.";
                } else {
                    $error = "Failed to add user.";
                }
                if (isset($insert_stmt)) $insert_stmt->close();  // Fixed: Conditional close
            }
            $check_stmt->close();  // Always close check_stmt
        }
    } elseif (isset($_POST['toggle_status'])) {
        // Toggle active/inactive
        $user_id = intval($_POST["user_id"]);
        $new_status = ($_POST["current_status"] === 'active') ? 'inactive' : 'active';

        $update_stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
        $update_stmt->bind_param("si", $new_status, $user_id);
        if ($update_stmt->execute()) {
            $message = "User status updated.";
        } else {
            $error = "Failed to update status.";
        }
        $update_stmt->close();
    } elseif (isset($_POST['delete_user'])) {
        // Delete user
        $user_id = intval($_POST["user_id"]);

        $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $delete_stmt->bind_param("i", $user_id);
        if ($delete_stmt->execute()) {
            $message = "User deleted successfully.";
        } else {
            $error = "Failed to delete user.";
        }
        $delete_stmt->close();
    }
}

// Fetch data
// Total users count
$total_stmt = $conn->prepare("SELECT COUNT(*) FROM users");
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_users = $total_result->fetch_row()[0];
$total_stmt->close();

// Active users count
$active_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE status = 'active'");
$active_stmt->execute();
$active_result = $active_stmt->get_result();
$active_users = $active_result->fetch_row()[0];
$active_stmt->close();

// Logged-in users: Assume last_login within last 15 minutes
$logged_stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE last_login > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
$logged_stmt->execute();
$logged_result = $logged_stmt->get_result();
$logged_users = $logged_result->fetch_row()[0];
$logged_stmt->close();

// List all users
$users_stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, status, last_login FROM users ORDER BY created_at DESC");
$users_stmt->execute();
$users_result = $users_stmt->get_result();
$users = $users_result->fetch_all(MYSQLI_ASSOC);
$users_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        button { padding: 5px 10px; margin: 2px; cursor: pointer; }
        .active { background-color: #d4edda; }
        .inactive { background-color: #f8d7da; }
        form { margin-bottom: 20px; }
        input, select { margin: 5px; padding: 5px; }
        .message { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
   <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 10px 0; border-bottom: 2px solid #eee;">
        <h1 style="margin: 0; font-size: 1.8em;">
            Admin Dashboard - Welcome, <?php echo htmlspecialchars($admin_name); ?>!
        </h1>
        <a href="logout.php" style="color: #d00; font-weight: bold; text-decoration: none; font-size: 1.1em;">
            Logout
        </a>
   </div>

    <?php if (!empty($message)) { ?><p class="message"><?php echo htmlspecialchars($message); ?></p><?php } ?>
    <?php if (!empty($error)) { ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php } ?>

    <!-- Stats -->
    <h2>System Overview</h2>
    <p>Total Users: <?php echo $total_users; ?> | Active Users: <?php echo $active_users; ?> | Currently Logged In: <?php echo $logged_users; ?></p>

    <!-- Add User Form -->
    <h2>Add New User</h2>
    <form method="post">
        <input type="hidden" name="add_user" value="1">
        First Name: <input type="text" name="first_name" required><br>
        Last Name: <input type="text" name="last_name" required><br>
        Email: <input type="email" name="email" required><br>
        Password: <input type="password" name="password" required minlength="6"><br>
        Role: <select name="role">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select><br>
        <button type="submit">Add User</button>
    </form>

    <!-- Users List -->
    <h2>Users Management</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Last Login</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($users as $user): ?>
            <?php 
            $full_name = trim($user['first_name'] . ' ' . $user['last_name']);
            if (empty($full_name)) $full_name = $user['email'];
            $is_logged_in = ($user['last_login'] && strtotime($user['last_login']) > strtotime('-15 minutes'));
            $row_class = ($user['status'] === 'active') ? 'active' : 'inactive';
            ?>
            <tr class="<?php echo $row_class; ?>">
                <td><?php echo $user['id']; ?></td>
                <td><?php echo htmlspecialchars($full_name); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo $user['role']; ?></td>
                <td><?php echo $user['status']; ?></td>
                <td><?php echo $user['last_login'] ? date('Y-m-d H:i:s', strtotime($user['last_login'])) : 'Never'; ?></td>
                <td>
                    <?php if ($user['role'] !== 'admin'): ?>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="toggle_status" value="1">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="current_status" value="<?php echo $user['status']; ?>">
                            <button type="submit"><?php echo ($user['status'] === 'active') ? 'Inactive' : 'Active'; ?></button>
                        </form>
                        <form method="post" style="display: inline;" onsubmit="return confirm('Delete this user?');">
                            <input type="hidden" name="delete_user" value="1">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" style="background: red; color: white;">Delete</button>
                        </form>
                    <?php else: ?>
                        <em>Admin - No Actions</em>
                    <?php endif; ?>
                    <?php if ($is_logged_in): ?><span style="color: green;"> (Logged In)</span><?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>