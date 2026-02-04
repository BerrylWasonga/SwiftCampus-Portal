<?php
class Model {
    protected $conn;

    public function __construct() {
        // Simple singleton or global connection for now to bridge legacy
        // Assuming config.php sets up $conn
        global $conn; 
        if (!$conn) {
             // Fallback if not globally available (e.g. if loaded via router differently)
             include_once __DIR__ . '/../../config.php';
        }
        $this->conn = $conn;
    }
}
?>
