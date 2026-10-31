<?php
class MockController extends Controller
{
    /* ==== Admin exam settings (added by admin module) ==== */
    private function adminSetting($key, $fallback)
    {
        try {
            $row = Database::getInstance()->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
            if ($row && isset($row['setting_value']) && $row['setting_value'] !== '' && is_numeric($row['setting_value'])) {
                return (int)$row['setting_value'];
            }
        } catch (Throwable $e) {
            // fall back to the built-in constant
        }
        return $fallback;
    }

    private function cfgTimeLimit()
    {
        return $this->adminSetting('mock_time_limit', defined('MockController::TIME_LIMIT_MINUTES') ? constant('MockController::TIME_LIMIT_MINUTES') : 30);
    }

    private function cfgMaxQuestions()
    {
        return $this->adminSetting('mock_max_questions', defined('MockController::MAX_QUESTIONS') ? constant('MockController::MAX_QUESTIONS') : 50);
    }
    // Implementation decisions (PDF does not fix these numbers):
    const TIME_LIMIT_MINUTES = 30;   // set to 1 for quick testing
    const MAX_QUESTIONS      = 50;

    // Page 1: pick a subject for the mock exam
    public function index()
    {
        $this->requireRole(ROLE_STUDENT);

        $subjects = $this->model('Subject')->getAll();

        $this->view('mock/subjects', [
            'subjects'   => $subjects,
            'activePage' => 'mock',
        ]);
    }

    // Start the exam: build the question set and store the deadline in the session
    public function start($subjectId = null)
    {
        $this->requireRole(ROLE_STUDENT);

        $subject = $this->model('Subject')->findById((int)$subjectId);
        if (!$subject) {
            $this->redirect('mock');
        }

        $questions = array_values($this->gatherSubjectQuestions((int)$subjectId));

        if (empty($questions)) {
            $_SESSION['flash_error'] = 'This subject has no questions yet, so a mock exam is not available.';
            $this->redirect('mock');
        }

        shuffle($questions);   // randomized order (PDF requirement)
        if (count($questions) > $this->cfgMaxQuestions()) {
            $questions = array_slice($questions, 0, $this->cfgMaxQuestions());
        }

        $_SESSION['mock_exam'] = [
            'subject_id'   => (int)$subjectId,
            'subject_name' => $subject['name'],
            'deadline'     => time() + ($this->cfgTimeLimit() * 60),
            'question_ids' => array_map(function ($q) { return (int)$q['id']; }, $questions),
        ];

        $this->redirect('mock/take');
    }

    // Page 2: the timed exam itself
    public function take()
    {
        $this->requireRole(ROLE_STUDENT);

        if (empty($_SESSION['mock_exam'])) {
            $this->redirect('mock');
        }
        $mock = $_SESSION['mock_exam'];

        $map = $this->gatherSubjectQuestions((int)$mock['subject_id']);

        $questions = [];
        foreach ($mock['question_ids'] as $qid) {
            if (isset($map[$qid])) {
                $questions[] = $map[$qid];
            }
        }

        if (empty($questions)) {
            unset($_SESSION['mock_exam']);
            $_SESSION['flash_error'] = 'This mock exam has no questions anymore. Please start again.';
            $this->redirect('mock');
        }

        $this->view('mock/take', [
            'mock'       => $mock,
            'questions'  => $questions,
            'activePage' => 'mock',
        ]);
    }

    // Server-side scoring + deadline enforcement, then save
    public function submit()
    {
        $this->requireRole(ROLE_STUDENT);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['mock_exam'])) {
            $this->redirect('mock');
        }

        $mock = $_SESSION['mock_exam'];

        // The timer cannot be cheated: late submissions are rejected (60s grace for the auto-submit)
        if (time() > ($mock['deadline'] + 60)) {
            unset($_SESSION['mock_exam']);
            $_SESSION['flash_error'] = 'Time was up before the exam reached the server, so this attempt was not recorded. Please start the mock again.';
            $this->redirect('mock');
        }

        $map = $this->gatherSubjectQuestions((int)$mock['subject_id']);

        $questions = [];
        foreach ($mock['question_ids'] as $qid) {
            if (isset($map[$qid])) {
                $questions[] = $map[$qid];
            }
        }

        if (empty($questions)) {
            unset($_SESSION['mock_exam']);
            $_SESSION['flash_error'] = 'The questions for this mock exam are no longer available.';
            $this->redirect('mock');
        }

        $answers = (isset($_POST['answers']) && is_array($_POST['answers'])) ? $_POST['answers'] : [];

        $total = count($questions);
        $score = 0;
        $rows  = [];

        foreach ($questions as $q) {
            $qid      = (int)$q['id'];
            $selected = isset($answers[$qid]) ? strtoupper(substr(trim($answers[$qid]), 0, 1)) : null;
            if ($selected === '') {
                $selected = null;
            }

            $isCorrect = ($selected !== null && $selected === strtoupper($q['correct_option'])) ? 1 : 0;
            if ($isCorrect === 1) {
                $score++;
            }

            $rows[] = [
                'question_id' => $qid,
                'selected'    => $selected,
                'is_correct'  => $isCorrect,
            ];
        }

        $percentage = round(($score / $total) * 100, 2);

        $attemptModel = $this->model('ExamAttempt');
        $attemptId = $attemptModel->save(
            $_SESSION['user_id'],
            (int)$mock['subject_id'],
            null,            // mock spans the whole subject — no single topic
            'mock',
            $total,
            $score,
            $percentage
        );
        $attemptModel->saveAnswers($attemptId, $rows);

        unset($_SESSION['mock_exam']);   // one submission per started exam

        $this->redirect('mock/result/' . (int)$attemptId);
    }

    // Page 3: result + review
    public function result($attemptId = null)
    {
        $this->requireRole(ROLE_STUDENT);

        $attemptModel = $this->model('ExamAttempt');
        $attempt = $attemptModel->findDetailed((int)$attemptId);

        if (!$attempt || (int)$attempt['user_id'] !== (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Result not found.';
            $this->redirect('mock');
        }

        $review = $attemptModel->getReview((int)$attemptId);

        $correct = 0; $wrong = 0; $missed = 0;
        foreach ($review as $r) {
            $sel = $r['selected'] ?? ($r['selected_option'] ?? null);
            if ((int)$r['is_correct'] === 1) {
                $correct++;
            } elseif ($sel === null || $sel === '') {
                $missed++;
            } else {
                $wrong++;
            }
        }

        $this->view('mock/result', [
            'attempt' => $attempt,
            'review'  => $review,
            'stats'   => [
                'total'      => count($review),
                'correct'    => $correct,
                'wrong'      => $wrong,
                'missed'     => $missed,
                'percentage' => (float)$attempt['percentage'],
            ],
            'activePage' => 'mock',
        ]);
    }

    // All active questions of a subject, keyed by id
    private function gatherSubjectQuestions($subjectId)
    {
        $map = [];
        foreach ($this->model('Topic')->getBySubject((int)$subjectId) as $t) {
            foreach ($this->model('Question')->getByTopic((int)$t['id']) as $q) {
                $map[(int)$q['id']] = $q;
            }
        }
        return $map;
    }
}