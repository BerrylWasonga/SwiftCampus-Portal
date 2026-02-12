<?php
require_once 'Model.php';

class Course extends Model {
    public function getByFaculty($faculty_id) {
        $stmt = $this->conn->prepare("SELECT * FROM courses WHERE faculty_id = ? ORDER BY course_name ASC");
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT c.*, f.faculty_name, f.id as faculty_id 
                                     FROM courses c 
                                     JOIN faculties f ON c.faculty_id = f.id 
                                     WHERE c.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($faculty_id, $name, $code, $level) {
        $stmt = $this->conn->prepare("INSERT INTO courses (faculty_id, course_name, course_code, level) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $faculty_id, $name, $code, $level);
        if ($stmt->execute()) {
            $course_id = $this->conn->insert_id;
            $this->addDefaultYearLevels($course_id);
            return $course_id;
        }
        return false;
    }

    public function update($id, $name, $code, $level) {
        $stmt = $this->conn->prepare("UPDATE courses SET course_name = ?, course_code = ?, level = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $code, $level, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM courses WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    private function addDefaultYearLevels($course_id) {
        $stmt = $this->conn->prepare("INSERT INTO course_year_levels (course_id, year_level) VALUES (?, ?)");
        for ($i = 1; $i <= 4; $i++) {
            $stmt->bind_param("ii", $course_id, $i);
            $stmt->execute();
        }
    }

    public function getYearLevels($course_id) {
        $stmt = $this->conn->prepare("SELECT * FROM course_year_levels WHERE course_id = ? ORDER BY year_level ASC");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
