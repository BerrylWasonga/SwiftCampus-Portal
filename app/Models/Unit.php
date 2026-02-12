<?php
require_once 'Model.php';

class Unit extends Model {
    public function getByCourse($course_id) {
        $stmt = $this->conn->prepare("SELECT cua.*, cu.unit_name, cu.unit_code, cu.credit_hours, cyl.year_level 
                                     FROM course_unit_assignments cua 
                                     JOIN course_units cu ON cua.unit_id = cu.id 
                                     LEFT JOIN course_year_levels cyl ON cua.year_level_id = cyl.id
                                     WHERE cua.course_id = ? 
                                     ORDER BY cyl.year_level ASC, cua.semester_id ASC");
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM course_units WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($name, $code, $credits) {
        $stmt = $this->conn->prepare("INSERT INTO course_units (unit_name, unit_code, credit_hours) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $code, $credits);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function assignToCourse($course_id, $unit_id, $year_level_id, $semester_id) {
        $stmt = $this->conn->prepare("INSERT INTO course_unit_assignments (course_id, unit_id, year_level_id, semester_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $course_id, $unit_id, $year_level_id, $semester_id);
        return $stmt->execute();
    }

    public function assignLecturer($unit_id, $lecturer_id) {
        $stmt = $this->conn->prepare("INSERT INTO unit_lecturer_assignments (unit_id, lecturer_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $unit_id, $lecturer_id);
        return $stmt->execute();
    }

    public function getAssignments($unit_id) {
        $stmt = $this->conn->prepare("SELECT ula.*, u.first_name, u.last_name, u.email 
                                     FROM unit_lecturer_assignments ula 
                                     JOIN users u ON ula.lecturer_id = u.id 
                                     WHERE ula.unit_id = ?");
        $stmt->bind_param("i", $unit_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function update($id, $name, $code, $credits) {
        $stmt = $this->conn->prepare("UPDATE course_units SET unit_name = ?, unit_code = ?, credit_hours = ? WHERE id = ?");
        $stmt->bind_param("ssii", $name, $code, $credits, $id);
        return $stmt->execute();
    }

    public function updateAssignment($assignment_id, $year_level_id, $semester_id) {
        $stmt = $this->conn->prepare("UPDATE course_unit_assignments SET year_level_id = ?, semester_id = ? WHERE id = ?");
        $stmt->bind_param("iii", $year_level_id, $semester_id, $assignment_id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM course_units WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
