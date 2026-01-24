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
            <form class="d-none d-lg-flex search-form me-3 position-relative">
                <input class="form-control rounded-pill px-4" 
                       type="search" 
                       id="studentSearchInput"
                       placeholder="Search..." 
                       aria-label="Search"
                       autocomplete="off"
                       style="width: 280px; background-color: #495057; border: none; color: white;">
                <div id="searchSuggestions" class="list-group position-absolute w-100 shadow" style="top: 100%; z-index: 1050; display: none;"></div>
            </form>

            <style>
                #searchSuggestions .list-group-item {
                    background-color: #343a40;
                    color: #fff;
                    border-color: #495057;
                    cursor: pointer;
                }
                #searchSuggestions .list-group-item:hover {
                    background-color: #495057;
                }
                #searchSuggestions .small-text {
                    font-size: 0.85rem;
                    color: #adb5bd;
                }
            </style>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const searchInput = document.getElementById('studentSearchInput');
                const suggestionsBox = document.getElementById('searchSuggestions');

                if (searchInput) { // Ensure element exists
                    searchInput.addEventListener('input', function() {
                        const query = this.value.trim();

                        if (query.length < 2) {
                            suggestionsBox.style.display = 'none';
                            suggestionsBox.innerHTML = '';
                            return;
                        }

                        fetch('search_handler.php?query=' + encodeURIComponent(query))
                            .then(response => response.json())
                            .then(data => {
                                suggestionsBox.innerHTML = '';
                                if (data.length > 0) {
                                    data.forEach(item => {
                                        const suggestionItem = document.createElement('a');
                                        suggestionItem.classList.add('list-group-item', 'list-group-item-action');
                                        suggestionItem.href = '#'; // Or link to specific page if available
                                        suggestionItem.innerHTML = `
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1 text-white">${item.title}</h6>
                                                <small class="text-muted">${item.type}</small>
                                            </div>
                                            <p class="mb-1 small-text">${item.details}</p>
                                        `;
                                        suggestionItem.addEventListener('click', function(e) {
                                            e.preventDefault();
                                            searchInput.value = item.title;
                                            suggestionsBox.style.display = 'none';
                                            // Optional: Redirect or perform action
                                            // window.location.href = 'view_course.php?code=' + item.details;
                                            alert('You selected: ' + item.title + ' (' + item.type + ')');
                                        });
                                        suggestionsBox.appendChild(suggestionItem);
                                    });
                                    suggestionsBox.style.display = 'block';
                                } else {
                                    suggestionsBox.innerHTML = '<div class="list-group-item">No results found</div>';
                                    suggestionsBox.style.display = 'block';
                                }
                            })
                            .catch(error => console.error('Error fetching search results:', error));
                    });

                    // Hide suggestions when clicking outside
                    document.addEventListener('click', function(e) {
                        if (!searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                            suggestionsBox.style.display = 'none';
                        }
                    });
                }
            });
            </script>

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