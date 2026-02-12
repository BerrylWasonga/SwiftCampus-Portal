<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../Models/Course.php';
require_once __DIR__ . '/../../Models/Unit.php';

class CourseController extends Controller {
    private $courseModel;
    private $unitModel;

    public function __construct() {
        $this->courseModel = new Course();
        $this->unitModel = new Unit();
    }

    public function view($id) {
        $course = $this->courseModel->getById($id);
        if (!$course) return null;
        
        $course['units'] = $this->unitModel->getByCourse($id);
        $course['year_levels'] = $this->courseModel->getYearLevels($id);
        return $course;
    }

    public function store($data) {
        return $this->courseModel->create($data['faculty_id'], $data['course_name'], $data['course_code'], $data['level']);
    }

    public function update($id, $data) {
        return $this->courseModel->update($id, $data['course_name'], $data['course_code'], $data['level']);
    }

    public function delete($id) {
        return $this->courseModel->delete($id);
    }

    public function addUnit($data) {
        $unit_id = $this->unitModel->create($data['unit_name'], $data['unit_code'], $data['credit_hours']);
        if ($unit_id && isset($data['course_id'])) {
            $this->unitModel->assignToCourse($data['course_id'], $unit_id, $data['year_level_id'], $data['semester_id']);
        }
        return $unit_id;
    }

    public function updateUnit($data) {
        $unit_updated = $this->unitModel->update($data['unit_id'], $data['unit_name'], $data['unit_code'], $data['credit_hours']);
        $assignment_updated = $this->unitModel->updateAssignment($data['assignment_id'], $data['year_level_id'], $data['semester_id']);
        return $unit_updated && $assignment_updated;
    }

    public function assignLecturer($data) {
        return $this->unitModel->assignLecturer($data['unit_id'], $data['lecturer_id']);
    }
}
