<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../Models/Faculty.php';
require_once __DIR__ . '/../../Models/Program.php';

class FacultyController extends Controller {
    private $facultyModel;
    private $programModel;

    public function __construct() {
        $this->facultyModel = new Faculty();
        $this->programModel = new Program();
    }

    public function index() {
        return $this->facultyModel->getAll();
    }

    public function view($id) {
        $faculty = $this->facultyModel->getById($id);
        if (!$faculty) return null;
        
        $faculty['programs'] = $this->programModel->getByFaculty($id);
        $faculty['staff'] = $this->facultyModel->getStaff($id);
        return $faculty;
    }

    public function store($data) {
        return $this->facultyModel->create($data['faculty_name'], $data['faculty_code']);
    }

    public function update($id, $data) {
        return $this->facultyModel->update($id, $data['faculty_name'], $data['faculty_code']);
    }

    public function delete($id) {
        return $this->facultyModel->delete($id);
    }

    public function addStaff($faculty_id, $data) {
        return $this->facultyModel->addStaff($faculty_id, $data['user_id'], $data['role']);
    }
}
