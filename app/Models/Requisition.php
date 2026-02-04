<?php
require_once 'Model.php';

class Requisition extends Model {
    
    public function getAll($limit, $offset, $status = null) {
        $sql = "SELECT r.*, u.first_name, u.last_name, u.reg_no 
                FROM requisitions r 
                JOIN users u ON r.student_id = u.id";
        
        if ($status) {
            $sql .= " WHERE r.status = ?";
        }
        
        $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($sql);
        if ($status) {
            $stmt->bind_param("sii", $status, $limit, $offset);
        } else {
            $stmt->bind_param("ii", $limit, $offset);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countAll($status = null) {
        $sql = "SELECT COUNT(*) as total FROM requisitions";
        if ($status) {
            $sql .= " WHERE status = ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        if ($status) {
            $stmt->bind_param("s", $status);
        }
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['total'];
    }
}
?>
