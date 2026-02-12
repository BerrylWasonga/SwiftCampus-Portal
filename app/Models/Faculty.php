<?php
require_once 'Model.php';

class Faculty extends Model {
    public function getAll() {
        return $this->conn->query("SELECT * FROM faculties ORDER BY faculty_name ASC")->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM faculties WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($name, $code) {
        $stmt = $this->conn->prepare("INSERT INTO faculties (faculty_name, faculty_code) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $code);
        return $stmt->execute();
    }

    public function update($id, $name, $code) {
        $stmt = $this->conn->prepare("UPDATE faculties SET faculty_name = ?, faculty_code = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $code, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM faculties WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getStaff($faculty_id) {
        $stmt = $this->conn->prepare("SELECT fs.*, u.first_name, u.last_name, u.email FROM faculty_staff fs JOIN users u ON fs.user_id = u.id WHERE fs.faculty_id = ?");
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function addStaff($faculty_id, $user_id, $role) {
        $stmt = $this->conn->prepare("INSERT INTO faculty_staff (faculty_id, user_id, role) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $faculty_id, $user_id, $role);
        return $stmt->execute();
    }
}
