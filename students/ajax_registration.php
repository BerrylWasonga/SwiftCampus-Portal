<?php
/**
 * AJAX Handler for Course Registration
 * Handles all AJAX requests from the student registration page
 */

session_start();
header('Content-Type: application/json');

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config.php';
require_once 'registration_functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];


class RegistrationController {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Corresponds to your 'get_units' case
     * Route: GET /api/registration/units
     */
    public function getUnits($userId, $semesterId, $type = 'regular') {
        // 1. Validation Logic
        if (!isRegistrationOpen($this->db, $semesterId, $type)) {
            return [
                'success' => false, 
                'message' => 'Registration window is closed.'
            ];
        }

        // 2. Fetch Data
        $units = getAvailableUnits($this->db, $userId, $semesterId, $type);

        // 3. Return structured data
        return [
            'success' => true,
            'units' => $units,
            'count' => count($units)
        ];
    }
}

try {
    switch ($action) {
        case 'get_units':
            $semester_id = (int)($_POST['semester_id'] ?? 0);
            $registration_type = $_POST['registration_type'] ?? 'regular';
            
            if (!$semester_id) {
                echo json_encode(['success' => false, 'message' => 'Semester ID is required']);
                break;
            }

            // Check if registration is open
            if (!isRegistrationOpen($conn, $semester_id, $registration_type)) {
                $window = getRegistrationWindow($conn, $semester_id, $registration_type);
                $message = "Registration is not open for " . ucfirst($registration_type) . " registration.";
                
                if ($window) {
                    $start = date('F j, Y g:i A', strtotime($window['start_datetime']));
                    $end = date('F j, Y g:i A', strtotime($window['end_datetime']));
                    $message .= " Window: $start to $end";
                }
                
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
                break;
            }
            
            $units = getAvailableUnits($conn, $user_id, $semester_id, $registration_type);
            
            echo json_encode([
                'success' => true,
                'units' => $units,
                'count' => count($units)
            ]);
            break;
            
        case 'add_to_basket':
            $semester_id = (int)($_POST['semester_id'] ?? 0);
            $unit_id = (int)($_POST['unit_id'] ?? 0);
            $registration_type = $_POST['registration_type'] ?? 'regular';
            $is_retake = isset($_POST['is_retake']) ? (bool)$_POST['is_retake'] : false;
            
            if (!$semester_id || !$unit_id) {
                echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
                break;
            }

            $result = addToBasket($conn, $user_id, $semester_id, $unit_id, $registration_type, $is_retake);
            echo json_encode($result);
            break;
            
        case 'remove_from_basket':
            $basket_id = (int)($_POST['basket_id'] ?? 0);
            
            if (!$basket_id) {
                echo json_encode(['success' => false, 'message' => 'Basket ID is required']);
                break;
            }

            $result = removeFromBasket($conn, $basket_id, $user_id);
            echo json_encode($result);
            break;
            
        case 'get_basket':
            $semester_id = (int)($_POST['semester_id'] ?? 0);
            
            if (!$semester_id) {
                echo json_encode(['success' => false, 'message' => 'Semester ID is required']);
                break;
            }

            $items = getBasketItems($conn, $user_id, $semester_id);
            $total_credits = calculateTotalCredits($items);
            
            echo json_encode([
                'success' => true,
                'items' => $items,
                'count' => count($items),
                'total_credits' => $total_credits
            ]);
            break;
            
        case 'clear_basket':
            $semester_id = (int)($_POST['semester_id'] ?? 0);
            
            if (!$semester_id) {
                echo json_encode(['success' => false, 'message' => 'Semester ID is required']);
                break;
            }

            clearBasket($conn, $user_id, $semester_id);
            echo json_encode([
                'success' => true,
                'message' => 'Basket cleared'
            ]);
            break;
            
        case 'complete_registration':
            $semester_id = (int)($_POST['semester_id'] ?? 0);
            
            if (!$semester_id) {
                echo json_encode(['success' => false, 'message' => 'Semester ID is required']);
                break;
            }

            $result = completeRegistration($conn, $user_id, $semester_id);
            echo json_encode($result);
            break;
            
        case 'get_registered_units':
            $semester_id = (int)($_POST['semester_id'] ?? 0);
            
            if (!$semester_id) {
                echo json_encode(['success' => false, 'message' => 'Semester ID is required']);
                break;
            }

            $units = getRegisteredUnits($conn, $user_id, $semester_id);
            $total_credits = calculateTotalCredits($units);
            
            echo json_encode([
                'success' => true,
                'units' => $units,
                'count' => count($units),
                'total_credits' => $total_credits
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action: ' . $action
            ]);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

$conn->close();
?>