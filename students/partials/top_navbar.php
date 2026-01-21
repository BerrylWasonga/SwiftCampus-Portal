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
<!-- ========== TOP NAVBAR ========== -->
    <nav class="top-navbar">
        <div class="container-fluid d-flex align-items-center h-100">
            <!-- Hamburger Button (visible on lg-down) -->
            <button class="btn text-white d-lg-none" id="toggleMobileMenu" style="font-size: 24px; padding: 0.5rem;">
                <i class="bi bi-list"></i>
            </button>

            <!-- Hamburger for desktop (lg+) - optional toggle -->
            <button class="btn text-white d-none d-lg-block" id="toggleSidebarDesktop" style="font-size: 24px; padding: 0.5rem;">
                <i class="bi bi-list"></i>
            </button>

            <!-- Spacer -->
            <div class="flex-grow-1"></div>

            <!-- Search Bar (hidden on md and below) -->
            <form class="d-none d-lg-flex search-form me-3">
                <input class="form-control rounded-pill px-4" 
                       type="search" 
                       placeholder="Search..." 
                       aria-label="Search"
                       style="width: 280px; background-color: #495057; border: none; color: white;">
            </form>

            <!-- User Avatar (mobile: just avatar) -->
            <div class="d-lg-none">
                <img src="Assets/images/1.png" 
                     class="rounded-circle profile-img-small" 
                     alt="User">
            </div>

            <!-- Desktop: Avatar + Name + Dropdown -->
            <div class="dropdown d-none d-lg-block">
                <a class="dropdown-toggle d-flex align-items-center text-white text-decoration-none" 
                   href="#" 
                   role="button" 
                   data-bs-toggle="dropdown" 
                   aria-expanded="false">
                    <img src="Assets/images/1.png" 
                         class="rounded-circle profile-img-small me-2" 
                         alt="User">
                    <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li><a class="dropdown-item" href="welcome.php">Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>