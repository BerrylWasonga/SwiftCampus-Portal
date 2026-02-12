<?php
/**
 * Course Registration Functions
 * Handles all backend logic for student course registration
 */

// Prevent direct access
if (!defined('REGISTRATION_FUNCTIONS')) {
    define('REGISTRATION_FUNCTIONS', true);
}

/**
 * Get current semester details
 */
function getCurrentSemester($conn) {
    $stmt = $conn->prepare("
        SELECT s.*, ay.year_name 
        FROM semesters s
        JOIN academic_years ay ON s.academic_year_id = ay.id
        WHERE s.is_current = TRUE AND s.status = 'active'
        LIMIT 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $semester = $result->fetch_assoc();
    $stmt->close();
    return $semester;
}

/**
 * Get student's academic information
 */
function getStudentAcademicInfo($conn, $user_id) {
    $stmt = $conn->prepare("
        SELECT * FROM student_academic_info 
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $info = $result->fetch_assoc();
    $stmt->close();
    return $info;
}

/**
 * Check if registration window is open for a specific type
 */
function isRegistrationOpen($conn, $semester_id, $registration_type) {
    $stmt = $conn->prepare("
        SELECT * FROM registration_windows 
        WHERE semester_id = ? 
        AND registration_type = ? 
        AND status = 'open'
        AND NOW() BETWEEN start_datetime AND end_datetime
    ");
    $stmt->bind_param("is", $semester_id, $registration_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $window = $result->fetch_assoc();
    $stmt->close();
    return $window ? true : false;
}

/**
 * Get all open registration windows for a semester
 */
function getOpenRegistrationWindows($conn, $semester_id) {
    $stmt = $conn->prepare("
        SELECT registration_type, start_datetime, end_datetime, status
        FROM registration_windows 
        WHERE semester_id = ? 
        AND status = 'open'
        AND NOW() BETWEEN start_datetime AND end_datetime
    ");
    $stmt->bind_param("i", $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $windows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $windows;
}

/**
 * Get registration window details
 */
function getRegistrationWindow($conn, $semester_id, $registration_type) {
    $stmt = $conn->prepare("
        SELECT * FROM registration_windows 
        WHERE semester_id = ? AND registration_type = ?
    ");
    $stmt->bind_param("is", $semester_id, $registration_type);
    $stmt->execute();
    $result = $stmt->get_result();
    $window = $result->fetch_assoc();
    $stmt->close();
    return $window;
}

/**
 * Check fee payment status
 */
function checkFeePaymentStatus($conn, $user_id, $semester_id) {
    $stmt = $conn->prepare("
        SELECT * FROM fee_payments 
        WHERE user_id = ? AND semester_id = ?
    ");
    $stmt->bind_param("ii", $user_id, $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();
    
    if (!$payment) {
        return ['status' => 'unpaid', 'paid' => 0, 'required' => 0];
    }
    
    return [
        'status' => $payment['payment_status'],
        'paid' => $payment['amount_paid'],
        'required' => $payment['amount_required']
    ];
}

/**
 * Get available units for registration
 */
function getAvailableUnits($conn, $user_id, $semester_id, $registration_type) {
    // Get student's course and year of study
    $student_info = $conn->prepare("
        SELECT u.course_id, sai.current_year_of_study
        FROM users u
        LEFT JOIN student_academic_info sai ON u.id = sai.user_id
        WHERE u.id = ?
    ");
    $student_info->bind_param("i", $user_id);
    $student_info->execute();
    $student_data = $student_info->get_result()->fetch_assoc();
    $student_info->close();
    
    if (!$student_data || !$student_data['course_id']) {
        return [];
    }
    
    $course_id = $student_data['course_id'];
    $year_of_study = $student_data['current_year_of_study'] ?? 1;
    
    $query = "";
    $params = [];
    $types = "";

    if ($registration_type === 'regular') {
        // Regular Registration: Units the student has never attempted before (success or pending/approved registration)
        // Show all units for the student's course to allow registration for other years if needed
        $query = "
            SELECT 
                cu.id as unit_id,
                cu.unit_code,
                cu.unit_name,
                cu.description,
                cu.credit_hours,
                cu.unit_type,
                cua.is_compulsory,
                cua.year_of_study,
                FALSE as already_registered
            FROM course_units cu
            JOIN course_unit_assignments cua ON cu.id = cua.unit_id
            WHERE cua.course_id = ?
            AND cua.semester_id = ?
            AND cu.status = 'active'
            AND cu.id NOT IN (
                SELECT unit_id FROM course_registrations 
                WHERE user_id = ? AND registration_status NOT IN ('rejected', 'cancelled')
            )
            AND cu.id NOT IN (
                SELECT unit_id FROM grades WHERE user_id = ?
            )
            ORDER BY cua.year_of_study ASC, cua.is_compulsory DESC, cu.unit_code ASC
        ";
        $params = [$course_id, $semester_id, $user_id, $user_id];
        $types = "iiii";
    } elseif ($registration_type === 'supplementary') {
        // Supplementary Registration: Units where the student's previous grade was a fail.
        $query = "
            SELECT 
                cu.id as unit_id,
                cu.unit_code,
                cu.unit_name,
                cu.description,
                cu.credit_hours,
                cu.unit_type,
                1 as is_compulsory,
                0 as year_of_study,
                FALSE as already_registered
            FROM course_units cu
            JOIN grades g ON cu.id = g.unit_id
            WHERE g.user_id = ? 
            AND g.status = 'Fail'
            AND cu.id NOT IN (
                SELECT unit_id FROM course_registrations 
                WHERE user_id = ? AND semester_id = ? AND registration_status NOT IN ('rejected', 'cancelled')
            )
            ORDER BY cu.unit_code ASC
        ";
        $params = [$user_id, $user_id, $semester_id];
        $types = "iii";
    } elseif ($registration_type === 'special') {
        // Special Exams: Units the student missed, but only if they have a 'Special Exam' status approved by an admin
        $query = "
            SELECT 
                cu.id as unit_id,
                cu.unit_code,
                cu.unit_name,
                cu.description,
                cu.credit_hours,
                cu.unit_type,
                1 as is_compulsory,
                0 as year_of_study,
                FALSE as already_registered
            FROM course_units cu
            JOIN requisitions r ON cu.id = r.unit_id
            AND cu.id NOT IN (
                SELECT unit_id FROM course_registrations 
                WHERE user_id = ? AND semester_id = ? AND registration_status NOT IN ('rejected', 'cancelled')
            )
            ORDER BY cu.unit_code ASC
        ";
        $params = [$user_id, $user_id, $semester_id];
        $types = "iii";
    } else {
        return [];
    }
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $units = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $units;
}

/**
 * Add unit to registration basket
 */
function addToBasket($conn, $user_id, $semester_id, $unit_id, $registration_type, $is_retake = false) {
    // Check if unit already in basket
    $check_stmt = $conn->prepare("
        SELECT id FROM registration_basket 
        WHERE user_id = ? AND semester_id = ? AND unit_id = ?
    ");
    $check_stmt->bind_param("iii", $user_id, $semester_id, $unit_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $check_stmt->close();
        return ['success' => false, 'message' => 'Unit already in basket'];
    }
    $check_stmt->close();
    
    // Check if already registered
    $reg_check = $conn->prepare("
        SELECT id FROM course_registrations 
        WHERE user_id = ? AND semester_id = ? AND unit_id = ?
        AND registration_status NOT IN ('rejected', 'cancelled')
    ");
    $reg_check->bind_param("iii", $user_id, $semester_id, $unit_id);
    $reg_check->execute();
    if ($reg_check->get_result()->num_rows > 0) {
        $reg_check->close();
        return ['success' => false, 'message' => 'Already registered for this unit'];
    }
    $reg_check->close();
    
    // Add to basket
    $stmt = $conn->prepare("
        INSERT INTO registration_basket (user_id, semester_id, unit_id, registration_type, is_retake)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iiisi", $user_id, $semester_id, $unit_id, $registration_type, $is_retake);
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Unit added to basket'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to add unit'];
    }
}

/**
 * Remove unit from basket
 */
function removeFromBasket($conn, $basket_id, $user_id) {
    $stmt = $conn->prepare("
        DELETE FROM registration_basket 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $basket_id, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Unit removed from basket'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to remove unit'];
    }
}

/**
 * Get basket items
 */
function getBasketItems($conn, $user_id, $semester_id) {
    $stmt = $conn->prepare("
        SELECT 
            rb.*,
            cu.unit_code,
            cu.unit_name,
            cu.credit_hours,
            cu.unit_type
        FROM registration_basket rb
        JOIN course_units cu ON rb.unit_id = cu.id
        WHERE rb.user_id = ? AND rb.semester_id = ?
        ORDER BY rb.added_at ASC
    ");
    $stmt->bind_param("ii", $user_id, $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $items;
}

/**
 * Clear basket
 */
function clearBasket($conn, $user_id, $semester_id) {
    $stmt = $conn->prepare("
        DELETE FROM registration_basket 
        WHERE user_id = ? AND semester_id = ?
    ");
    $stmt->bind_param("ii", $user_id, $semester_id);
    $stmt->execute();
    $stmt->close();
}

/**
 * Complete registration (move from basket to registrations)
 */
function completeRegistration($conn, $user_id, $semester_id) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get basket items
        $basket_items = getBasketItems($conn, $user_id, $semester_id);
        
        if (empty($basket_items)) {
            throw new Exception("Basket is empty");
        }
        
        // Insert each basket item into course_registrations
        $insert_stmt = $conn->prepare("
            INSERT INTO course_registrations 
            (user_id, semester_id, unit_id, registration_type, registration_status, credit_hours, is_retake)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($basket_items as $item) {
            // For regular registration, auto-approve. For special/supplementary, set to pending
            $status = ($item['registration_type'] === 'regular') ? 'approved' : 'pending';
            
            $insert_stmt->bind_param(
                "iiisiii",
                $user_id,
                $semester_id,
                $item['unit_id'],
                $item['registration_type'],
                $status,
                $item['credit_hours'],
                $item['is_retake']
            );
            
            if (!$insert_stmt->execute()) {
                throw new Exception("Failed to register unit: " . $item['unit_name']);
            }
        }
        $insert_stmt->close();
        
        // Clear basket after successful registration
        clearBasket($conn, $user_id, $semester_id);
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true, 
            'message' => 'Registration completed successfully',
            'units_count' => count($basket_items)
        ];
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get student's registered units
 */
function getRegisteredUnits($conn, $user_id, $semester_id) {
    $stmt = $conn->prepare("
        SELECT 
            cr.*,
            cu.unit_code,
            cu.unit_name,
            cu.credit_hours,
            cu.unit_type,
            u.first_name as approver_first_name,
            u.last_name as approver_last_name
        FROM course_registrations cr
        JOIN course_units cu ON cr.unit_id = cu.id
        LEFT JOIN users u ON cr.approved_by = u.id
        WHERE cr.user_id = ? AND cr.semester_id = ?
        ORDER BY cr.registration_date DESC
    ");
    $stmt->bind_param("ii", $user_id, $semester_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $units = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $units;
}

/**
 * Calculate total credit hours
 */
function calculateTotalCredits($units) {
    $total = 0;
    foreach ($units as $unit) {
        if (isset($unit['credit_hours'])) {
            $total += (int)$unit['credit_hours'];
        }
    }
    return $total;
}

/**
 * Admin: Get pending registrations for approval
 */
function getPendingRegistrations($conn, $semester_id = null) {
    $sql = "
        SELECT 
            cr.*,
            cu.unit_code,
            cu.unit_name,
            u.first_name,
            u.last_name,
            u.reg_no,
            u.email,
            c.course_name,
            s.semester_name
        FROM course_registrations cr
        JOIN course_units cu ON cr.unit_id = cu.id
        JOIN users u ON cr.user_id = u.id
        LEFT JOIN courses c ON u.course_id = c.id
        JOIN semesters s ON cr.semester_id = s.id
        WHERE cr.registration_status = 'pending'
    ";
    
    if ($semester_id) {
        $sql .= " AND cr.semester_id = ?";
        $stmt = $conn->prepare($sql . " ORDER BY cr.registration_date ASC");
        $stmt->bind_param("i", $semester_id);
    } else {
        $stmt = $conn->prepare($sql . " ORDER BY cr.registration_date ASC");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $registrations = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $registrations;
}

/**
 * Admin: Approve registration
 */
function approveRegistration($conn, $registration_id, $admin_id) {
    $stmt = $conn->prepare("
        UPDATE course_registrations 
        SET registration_status = 'approved',
            approval_date = NOW(),
            approved_by = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ii", $admin_id, $registration_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Registration approved'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to approve'];
    }
}

/**
 * Admin: Reject registration
 */
function rejectRegistration($conn, $registration_id, $admin_id, $reason) {
    $stmt = $conn->prepare("
        UPDATE course_registrations 
        SET registration_status = 'rejected',
            approval_date = NOW(),
            approved_by = ?,
            rejection_reason = ?
        WHERE id = ?
    ");
    $stmt->bind_param("isi", $admin_id, $reason, $registration_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        return ['success' => true, 'message' => 'Registration rejected'];
    } else {
        $stmt->close();
        return ['success' => false, 'message' => 'Failed to reject'];
    }
}
?>