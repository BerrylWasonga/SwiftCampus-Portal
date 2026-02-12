<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../../Models/Program.php';
require_once __DIR__ . '/../../Models/Course.php';

class ProgramController extends Controller {
    private $programModel;
    private $courseModel;

    public function __construct() {
        $this->programModel = new Program();
        $this->courseModel = new Course();
    }

    public function view($id) {
        $program = $this->programModel->getById($id);
        if (!$program) return null;
        
        $program['courses'] = $this->courseModel->getByProgram($id);
        return $program;
    }

    public function store($data) {
        return $this->programModel->create($data['faculty_id'], $data['program_name'], $data['program_code']);
    }

    public function update($id, $data) {
        return $this->programModel->update($id, $data['program_name'], $data['program_code']);
    }

    public function delete($id) {
        return $this->programModel->delete($id);
    }
}
