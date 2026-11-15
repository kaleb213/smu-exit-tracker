<?php
/**
 * ============================================================
 *  StudentController.php — Student module
 * ============================================================
 */

class StudentController extends Controller
{
    // Student dashboard: stats + subject grid
    public function index()
    {
        $this->requireRole(ROLE_STUDENT);

        $summary  = $this->model('ExamAttempt')->getSummary($_SESSION['user_id']);
        $subjects = $this->model('Subject')->getAll();

        $this->view('student/dashboard', [
            'summary'    => $summary,
            'subjects'   => $subjects,
            'activePage' => 'dashboard',
        ]);
    }
}