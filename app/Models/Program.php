<?php
require_once 'Model.php';

class Program extends Model {
    public function getByFaculty($faculty_id) {
        $stmt = $this->conn->prepare("SELECT id, course_name as program_name, course_code as program_code FROM courses WHERE faculty_id = ? ORDER BY course_name ASC");
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT c.*, c.course_name as program_name, c.course_code as program_code, f.faculty_name 
                                FROM courses c 
                                JOIN faculties f ON c.faculty_id = f.id 
                                WHERE c.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($faculty_id, $name, $code) {
        $stmt = $this->conn->prepare("INSERT INTO courses (faculty_id, course_name, course_code, level) VALUES (?, ?, ?, 'Bachelor')");
        $stmt->bind_param("iss", $faculty_id, $name, $code);
        return $stmt->execute();
    }

    public function update($id, $name, $code) {
        $stmt = $this->conn->prepare("UPDATE courses SET course_name = ?, course_code = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $code, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
