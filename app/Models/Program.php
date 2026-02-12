<?php
require_once 'Model.php';

class Program extends Model {
    public function getByFaculty($faculty_id) {
        $stmt = $this->conn->prepare("SELECT * FROM programs WHERE faculty_id = ? ORDER BY program_name ASC");
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT p.*, f.faculty_name FROM programs p JOIN faculties f ON p.faculty_id = f.id WHERE p.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($faculty_id, $name, $code) {
        $stmt = $this->conn->prepare("INSERT INTO programs (faculty_id, program_name, program_code) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $faculty_id, $name, $code);
        return $stmt->execute();
    }

    public function update($id, $name, $code) {
        $stmt = $this->conn->prepare("UPDATE programs SET program_name = ?, program_code = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $code, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM programs WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
