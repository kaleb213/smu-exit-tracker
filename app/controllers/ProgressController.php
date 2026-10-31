<?php
require_once APP_PATH . '/core/Controller.php';

class ProgressController extends Controller
{
    public function index()
    {
        $this->requireRole(ROLE_STUDENT);

        $userId   = (int)$_SESSION['user_id'];
        $progress = $this->model('Progress');
        $attempts = $this->model('ExamAttempt');

        $readiness = $progress->getReadiness($userId);
        $summary   = $attempts->getSummary($userId);

        /* Line chart data: score history */
        $history      = $progress->getScoreHistory($userId, 20);
        $chartHistory = [];
        foreach ($history as $i => $h) {
            $chartHistory[] = [
                'label' => '#' . ($i + 1),
                'value' => (float)$h['percentage'],
                'type'  => $h['exam_type'],
            ];
        }

        /* Bar chart data: subject averages */
        $subjects = $progress->getSubjectBreakdown($userId);
        $chartSubjects = ['labels' => [], 'values' => []];
        foreach ($subjects as $s) {
            $chartSubjects['labels'][] = $s['name'];
            $chartSubjects['values'][] = (float)$s['avg_percentage'];
        }

        /* Weakest and strongest topics */
        $topicStats = $progress->getTopicStats($userId);
        $weak   = $topicStats; /* already weakest first */
        $strong = $topicStats;
        usort($strong, function ($a, $b) {
            return $b['avg_percentage'] <=> $a['avg_percentage'];
        });

        $this->view('progress/index', [
            'readiness'     => $readiness,
            'summary'       => $summary,
            'chartHistory'  => $chartHistory,
            'chartSubjects' => $chartSubjects,
            'weaknesses'    => array_slice($weak, 0, 5),
            'strengths'     => array_slice($strong, 0, 5),
            'attempts'      => $progress->getRecentAttempts($userId, 10),
        ]);
    }
}