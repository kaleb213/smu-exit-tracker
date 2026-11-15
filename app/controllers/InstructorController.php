<?php
require_once APP_PATH . '/core/Controller.php';

class InstructorController extends Controller
{
    private function guard()
    {
        $role = defined('ROLE_INSTRUCTOR') ? ROLE_INSTRUCTOR : 'instructor';
        $this->requireRole($role);
    }

    /* /instructor - full reports dashboard */
    public function index()
    {
        $this->guard();
        $report = $this->model('Report');

        $this->view('instructor/index', [
            'overview' => $report->classOverview(),
            'subjects' => $report->subjectAverages(),
            'weak'     => $report->weakTopics(10),
            'students' => $report->studentSummaries(),
        ]);
    }

    /* /instructor/trends - charts */
    public function trends()
    {
        $this->guard();
        $report = $this->model('Report');

        $chartDaily = ['labels' => [], 'values' => []];
        foreach ($report->dailyAverages(14) as $d) {
            $chartDaily['labels'][] = $d['attempt_date'];
            $chartDaily['values'][] = (float)$d['avg_percentage'];
        }

        $chartSubjects = ['labels' => [], 'values' => []];
        foreach ($report->subjectAverages() as $s) {
            $chartSubjects['labels'][] = $s['name'];
            $chartSubjects['values'][] = (float)$s['avg_percentage'];
        }

        $this->view('instructor/trends', [
            'chartDaily'    => $chartDaily,
            'chartSubjects' => $chartSubjects,
        ]);
    }

    /* /instructor/student/5 - drill-down for one student */
    public function student($id = null)
    {
        $this->guard();

        $id = (int)$id;
        if ($id <= 0) {
            $this->redirect('/instructor');
            return;
        }

        $report   = $this->model('Report');
        $progress = $this->model('Progress');

        $info = $report->studentDetail($id);
        if (!$info || strtolower($info['role']) !== 'student') {
            $this->redirect('/instructor');
            return;
        }

        $subjects   = $progress->getSubjectBreakdown($id);
        $topicStats = $progress->getTopicStats($id);
        $strong     = array_reverse($topicStats);

        $chartHistory = [];
        foreach ($progress->getScoreHistory($id, 20) as $i => $h) {
            $chartHistory[] = [
                'label' => '#' . ($i + 1),
                'value' => (float)$h['percentage'],
                'type'  => $h['exam_type'],
            ];
        }

        $chartSubjects = ['labels' => [], 'values' => []];
        foreach ($subjects as $s) {
            $chartSubjects['labels'][] = $s['name'];
            $chartSubjects['values'][] = (float)$s['avg_percentage'];
        }

        $this->view('instructor/student', [
            'info'          => $info,
            'readiness'     => $progress->getReadiness($id),
            'chartHistory'  => $chartHistory,
            'chartSubjects' => $chartSubjects,
            'weaknesses'    => array_slice($topicStats, 0, 5),
            'strengths'     => array_slice($strong, 0, 5),
            'attempts'      => $progress->getRecentAttempts($id, 10),
        ]);
    }

    /* /instructor/export - CSV download of the students report */
    public function export()
    {
        $this->guard();

        $rows = $this->model('Report')->studentSummaries();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="smu_students_report.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Student', 'ID Number', 'Attempts', 'Average %', 'Best %', 'Last Attempt'], ',', '"', '\\');
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['student_name'],
                $r['id_number'],
                $r['attempts'],
                $r['avg_percentage'],
                $r['best_percentage'],
                $r['last_attempt'] ? $r['last_attempt'] : '',
            ], ',', '"', '\\');
        }
        fclose($out);
        exit;
    }
}