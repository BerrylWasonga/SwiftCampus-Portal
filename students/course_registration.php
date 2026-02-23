<?php
session_start();

// Only allow logged-in students (role = 'user')
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Include database connection and functions
include '../config.php';
require_once 'registration_functions.php';

// Fetch user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Build full name
$full_name = trim($user['first_name'] . ' ' . $user['last_name']);
if (empty($full_name)) {
    $full_name = $user['email'] ?? 'User';
}

// Get current semester
$current_semester = getCurrentSemester($conn);
if (!$current_semester) {
    $error_message = "No active semester found. Please contact the administration.";
}

// Get student academic info
$student_info = getStudentAcademicInfo($conn, $_SESSION['user_id']);

// Get already registered units
$registered_units = [];
if ($current_semester) {
    $registered_units = getRegisteredUnits($conn, $_SESSION['user_id'], $current_semester['id']);
    $reg_windows = getOpenRegistrationWindows($conn, $current_semester['id']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Registration - Chuka University</title>
    <link rel="icon" type="image/png" href="Assets/images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Assets/main.css" rel="stylesheet">
    <style>
        .unit-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        .unit-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-left-color: #0d6efd;
        }
        .unit-card.selected {
            background-color: #e7f3ff;
            border-left-color: #0d6efd;
        }
        .basket-item {
            background-color: #f8f9fa;
            border-left: 3px solid #198754;
            margin-bottom: 0.5rem;
            padding: 0.75rem;
            border-radius: 4px;
        }
        .credit-badge {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }
        .status-approved { color: #198754; }
        .status-pending { color: #ffc107; }
        .status-rejected { color: #dc3545; }
        .empty-state {
            padding: 3rem;
            text-align: center;
            color: #6c757d;
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .stat-card {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .loading-overlay.show {
            display: flex;
        }
    </style>
</head>
<body>
    <?php 
        include_once 'partials/sidebar.php';
        include_once 'partials/top_navbar.php';
    ?>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="main-content">
        <!-- Page Header -->
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1"><i class="bi bi-pencil-square me-2"></i>Course Registration</h2>
                            <p class="text-muted mb-0">Select and register for your course units</p>
                        </div>
                        <?php if ($current_semester): ?>
                        <div class="text-end">
                            <span class="badge bg-primary fs-6">
                                <?php echo htmlspecialchars($current_semester['semester_name']); ?>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php else: ?>

            <!-- Alert Container -->
            <div id="alertContainer"></div>

            <!-- Student Info Summary -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card stat-card border-start border-primary border-4">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Registration Number</h6>
                            <h5 class="mb-0"><?php echo htmlspecialchars($user['reg_no'] ?? 'N/A'); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card border-start border-success border-4">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Programme</h6>
                            <h6 class="mb-0"><?php echo htmlspecialchars($user['programme'] ?? 'N/A'); ?></h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card stat-card border-start border-info border-4">
                        <div class="card-body">
                            <h6 class="text-muted mb-1">Year of Study</h6>
                            <h5 class="mb-0">Year <?php echo htmlspecialchars($user['year_level'] ?? ($student_info['current_year_of_study'] ?? 'N/A')); ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <i class="bi bi-list-check me-2"></i>Available Units
                        </div>
                        <div class="card-body">
                            <!-- Registration Type Selection -->
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="registrationType" class="form-label fw-bold">
                                        <i class="bi bi-tag me-1"></i>Registration Type
                                    </label>                     
                                    <select class="form-select" id="registrationType">
                                        <option value="">-- Select Registration Type --</option>
                                        <option value="regular">Regular Registration</option>
                                        <option value="supplementary">Supplementary Registration</option>
                                        <option value="special">Special/Repeat Registration</option>
                                        <?php if (!empty($reg_windows)): ?>
                                            <?php foreach ($reg_windows as $window): ?>
                                                <option value="<?php echo htmlspecialchars($window['registration_type']); ?>">
                                                    <?php echo ucfirst($window['registration_type']); ?> Registration
                                                </option>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <option value="" disabled>No registration periods currently open</option>
                                        <?php endif; ?>
                                    </select>
                                    <small class="text-muted">Select your registration type to view available units</small>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" id="getUnitsBtn" disabled>
                                        <i class="bi bi-search me-2"></i>Get Units
                                    </button>
                                </div>
                            </div>
                            <hr>

                            <!-- Units List -->
                            <div id="unitsContainer">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <p class="mb-0">Select registration type and click "Get Units" to view available course units</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registration Basket -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm sticky-top" style="top: 80px;">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-basket me-2"></i>Registration Basket</span>
                            <span class="badge bg-white text-success" id="basketCount">0</span>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div id="basketContainer">
                                <div class="empty-state py-4">
                                    <i class="bi bi-basket"></i>
                                    <p class="small mb-0">No units selected yet</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <strong>Total Credit Hours:</strong>
                                <span class="badge bg-info fs-6" id="totalCredits">0</span>
                            </div>
                            <button type="button" class="btn btn-success w-100 mb-2" id="completeRegistrationBtn" disabled>
                                <i class="bi bi-check-circle me-2"></i>Complete Registration
                            </button>
                            <button type="button" class="btn btn-outline-danger w-100" id="clearBasketBtn" disabled>
                                <i class="bi bi-trash me-2"></i>Clear Basket
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registered Units -->
            <?php if (!empty($registered_units)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <i class="bi bi-journal-check me-2"></i>Your Registered Units (<?php echo count($registered_units); ?>)
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Unit Code</th>
                                            <th>Unit Name</th>
                                            <th>Credits</th>
                                            <th>Type</th>
                                            <th>Reg. Type</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($registered_units as $index => $unit): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><strong><?php echo htmlspecialchars($unit['unit_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($unit['unit_name']); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo $unit['credit_hours']; ?> hrs</span></td>
                                            <td><span class="badge bg-primary"><?php echo ucfirst($unit['unit_type']); ?></span></td>
                                            <td><?php echo ucfirst($unit['registration_type']); ?></td>
                                            <td>
                                                <?php 
                                                $status_class = 'status-' . $unit['registration_status'];
                                                $status_icon = $unit['registration_status'] === 'approved' ? 'check-circle-fill' : 
                                                              ($unit['registration_status'] === 'pending' ? 'clock-fill' : 'x-circle-fill');
                                                ?>
                                                <i class="bi bi-<?php echo $status_icon; ?> <?php echo $status_class; ?>"></i>
                                                <strong class="<?php echo $status_class; ?>"><?php echo ucfirst($unit['registration_status']); ?></strong>
                                            </td>
                                            <td><small><?php echo date('d/m/Y', strtotime($unit['registration_date'])); ?></small></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

    <?php include_once 'partials/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="Assets/main.js"></script>

    <script>
    $(document).ready(function() {
        const semesterId = <?php echo $current_semester['id'] ?? 'null'; ?>;
        let selectedUnits = new window.Set();
        
        // Enable Get Units button when registration type is selected
        $('#registrationType').on('change', function() {
            $('#getUnitsBtn').prop('disabled', !$(this).val());
        });
        
        // Get Units
        $('#getUnitsBtn').on('click', function() {
            const regType = $('#registrationType').val();
            if (!regType || !semesterId) return;
            
            showLoading();
            $.post('ajax_registration.php', {
                action: 'get_units',
                semester_id: semesterId,
                registration_type: regType
            }, function(response) {
                hideLoading();
                if (response.success) {
                    displayUnits(response.units);
                    loadBasket();
                } else {
                    showAlert(response.message, 'danger');
                }
            }, 'json').fail(function() {
                hideLoading();
                showAlert('Failed to load units. Please try again.', 'danger');
            });
        });
        
        // Display Units
        function displayUnits(units) {
            const container = $('#unitsContainer');
            if (units.length === 0) {
                container.html('<div class="empty-state"><i class="bi bi-inbox"></i><p>No units available for this registration type</p></div>');
                return;
            }
            
            // Group units by Year Level
            const groupedUnits = {};
            units.forEach(unit => {
                const year = unit.year_of_study || 0;
                if (!groupedUnits[year]) groupedUnits[year] = [];
                groupedUnits[year].push(unit);
            });

            let html = '';
            
            // Iterate through years (sorted)
            Object.keys(groupedUnits).sort().forEach(year => {
                const yearLabel = year === '0' ? 'Other/Misc' : `Year ${year}`;
                const icon = year === '0' ? 'grid' : 'layers';
                
                html += `
                    <div class="year-section mb-4">
                        <h5 class="border-bottom pb-2 mb-3 text-primary d-flex align-items-center">
                            <i class="bi bi-${icon} me-2"></i> ${yearLabel}
                        </h5>
                        <div class="row g-3">
                `;
                
                groupedUnits[year].forEach(unit => {
                    const disabled = unit.already_registered ? 'disabled' : '';
                    const checkedClass = unit.already_registered ? 'opacity-50' : '';
                    const badge = unit.is_compulsory ? '<span class="badge bg-danger ms-2">Required</span>' : '<span class="badge bg-secondary ms-2">Elective</span>';
                    
                    html += `
                        <div class="col-12 col-md-6">
                            <div class="card unit-card h-100 ${checkedClass}" data-unit-id="${unit.unit_id}">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="form-check flex-grow-1">
                                            <input class="form-check-input unit-checkbox" type="checkbox" 
                                                   value="${unit.unit_id}" 
                                                   data-credits="${unit.credit_hours}"
                                                   id="unit_${unit.unit_id}" ${disabled}>
                                            <label class="form-check-label w-100" for="unit_${unit.unit_id}">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <strong>${unit.unit_code}</strong>
                                                        </h6>
                                                        <p class="mb-0 fw-bold small">${unit.unit_name}</p>
                                                        ${badge}
                                                    </div>
                                                    <span class="badge bg-info text-dark credit-badge">${unit.credit_hours} Cr</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    ${unit.already_registered ? '<small class="text-success"><i class="bi bi-check-circle-fill"></i> Registered</small>' : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                html += '</div></div>';
            });
            
            html += `
                <div class="mt-4 text-end sticky-bottom bg-white py-3 border-top">
                    <button type="button" class="btn btn-primary btn-lg shadow-sm" id="addToBasketBtn">
                        <i class="bi bi-cart-plus me-2"></i>Add Selected to Basket
                    </button>
                </div>
            `;
            
            container.html(html);
            
            // Add checkbox change handler
            $('.unit-checkbox').on('change', function() {
                const card = $(this).closest('.unit-card');
                if ($(this).is(':checked')) {
                    card.addClass('selected');
                    selectedUnits.add(parseInt($(this).val()));
                } else {
                    card.removeClass('selected');
                    selectedUnits.delete(parseInt($(this).val()));
                }
            });
            
            // Add to basket button
            $('#addToBasketBtn').on('click', addSelectedToBasket);
        }
        
        // Add Selected to Basket
        function addSelectedToBasket() {
            const regType = $('#registrationType').val();
            const checkedBoxes = $('.unit-checkbox:checked');
            
            if (checkedBoxes.length === 0) {
                showAlert('Please select at least one unit', 'warning');
                return;
            }
            
            showLoading();
            let promises = [];
            
            checkedBoxes.each(function() {
                const unitId = $(this).val();
                promises.push(
                    $.post('ajax_registration.php', {
                        action: 'add_to_basket',
                        semester_id: semesterId,
                        unit_id: unitId,
                        registration_type: regType
                    }, null, 'json')
                );
            });
            
            Promise.all(promises).then(() => {
                hideLoading();
                showAlert('Units added to basket successfully', 'success');
                loadBasket();
                // Uncheck all boxes
                checkedBoxes.prop('checked', false);
                $('.unit-card').removeClass('selected');
                selectedUnits.clear();
            }).catch(() => {
                hideLoading();
                showAlert('Some units could not be added', 'warning');
                loadBasket();
            });
        }
        
        // Load Basket
        function loadBasket() {
            $.post('ajax_registration.php', {
                action: 'get_basket',
                semester_id: semesterId
            }, function(response) {
                if (response.success) {
                    displayBasket(response.items, response.total_credits);
                }
            }, 'json');
        }
        
        // Display Basket
        function displayBasket(items, totalCredits) {
            const container = $('#basketContainer');
            $('#basketCount').text(items.length);
            $('#totalCredits').text(totalCredits);
            
            if (items.length === 0) {
                container.html('<div class="empty-state py-4"><i class="bi bi-basket"></i><p class="small mb-0">No units selected yet</p></div>');
                $('#completeRegistrationBtn, #clearBasketBtn').prop('disabled', true);
                return;
            }
            
            let html = '';
            items.forEach(item => {
                html += `
                    <div class="basket-item">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <strong class="small">${item.unit_code}</strong>
                            <button class="btn btn-sm btn-danger btn-sm remove-basket-item" data-basket-id="${item.id}">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                        <p class="mb-1 small">${item.unit_name}</p>
                        <small class="text-muted">${item.credit_hours} credit hours</small>
                    </div>
                `;
            });
            
            container.html(html);
            $('#completeRegistrationBtn, #clearBasketBtn').prop('disabled', false);
            
            // Remove from basket
            $('.remove-basket-item').on('click', function() {
                const basketId = $(this).data('basket-id');
                $.post('ajax_registration.php', {
                    action: 'remove_from_basket',
                    basket_id: basketId
                }, function(response) {
                    if (response.success) {
                        loadBasket();
                    }
                }, 'json');
            });
        }
        
        // Clear Basket
        $('#clearBasketBtn').on('click', function() {
            if (!confirm('Are you sure you want to clear your basket?')) return;
            
            $.post('ajax_registration.php', {
                action: 'clear_basket',
                semester_id: semesterId
            }, function(response) {
                if (response.success) {
                    showAlert('Basket cleared', 'info');
                    loadBasket();
                }
            }, 'json');
        });
        
        // Complete Registration
        $('#completeRegistrationBtn').on('click', function() {
            if (!confirm('Are you sure you want to complete your registration? This action cannot be undone.')) return;
            
            showLoading();
            $.post('ajax_registration.php', {
                action: 'complete_registration',
                semester_id: semesterId
            }, function(response) {
                hideLoading();
                if (response.success) {
                    showAlert(response.message + '. Refreshing page...', 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert(response.message, 'danger');
                }
            }, 'json').fail(function() {
                hideLoading();
                showAlert('Failed to complete registration. Please try again.', 'danger');
            });
        });
        
        // Helper Functions
        function showAlert(message, type) {
            const html = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'danger' ? 'x-circle' : 'info-circle'}-fill me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            $('#alertContainer').html(html);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
        
        function showLoading() {
            $('#loadingOverlay').addClass('show');
        }
        
        function hideLoading() {
            $('#loadingOverlay').removeClass('show');
        }
        
        // Initial load of basket and units
        if (semesterId) {
            loadBasket();
            
            // Auto-load first available registration type if any
            const firstRegType = $('#registrationType option:not([value=""]):not([disabled])').first().val();
            if (firstRegType) {
                $('#registrationType').val(firstRegType);
                $('#getUnitsBtn').prop('disabled', false).trigger('click');
            }
        }
    });
    </script>
</body>
</html>