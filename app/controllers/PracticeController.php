<?php
class PracticeController extends Controller
{
    // Page 1: pick a subject
    public function index()
    {
        $this->requireRole(ROLE_STUDENT);

        $subjects = $this->model('Subject')->getAll();

        $this->view('practice/subjects', [
            'subjects'   => $subjects,
            'activePage' => 'practice',
        ]);
    }

    // Page 2: pick a topic in a subject
    public function subject($subjectId = null)
    {
        $this->requireRole(ROLE_STUDENT);

        $subject = $this->model('Subject')->findById((int)$subjectId);
        if (!$subject) {
            $this->redirect('practice');
        }

        $topics = $this->model('Topic')->getBySubject((int)$subjectId);

        $this->view('practice/topics', [
            'subject'    => $subject,
            'topics'     => $topics,
            'activePage' => 'practice',
        ]);
    }

    // Page 3: answer the questions
    public function take($topicId = null)
    {
        $this->requireRole(ROLE_STUDENT);

        $topic = $this->model('Topic')->getById((int)$topicId);
        if (!$topic) {
            $this->redirect('practice');
        }

        $questions = $this->model('Question')->getByTopic((int)$topicId);
        $subject   = $this->model('Subject')->findById($topic['subject_id']);

        $this->view('practice/take', [
            'topic'      => $topic,
            'subject'    => $subject,
            'questions'  => $questions,
            'activePage' => 'practice',
        ]);
    }

    // Server-side scoring + save, then redirect to result (refresh-safe)
    public function submit($topicId = null)
    {
        $this->requireRole(ROLE_STUDENT);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('practice');
        }

        $topic = $this->model('Topic')->getById((int)$topicId);
        if (!$topic) {
            $this->redirect('practice');
        }

        $questions = $this->model('Question')->getByTopic((int)$topicId);
        if (empty($questions)) {
            $_SESSION['flash_error'] = 'This topic has no questions yet.';
            $this->redirect('practice/subject/' . (int)$topic['subject_id']);
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
            (int)$topic['subject_id'],
            (int)$topic['id'],
            'practice',
            $total,
            $score,
            $percentage
        );
        $attemptModel->saveAnswers($attemptId, $rows);

        // POST-Redirect-GET: refreshing can never submit twice
        $this->redirect('practice/result/' . (int)$attemptId);
    }

    // Page 4: instant feedback + full review with explanations
    public function result($attemptId = null)
    {
        $this->requireRole(ROLE_STUDENT);

        $attemptModel = $this->model('ExamAttempt');
        $attempt = $attemptModel->findDetailed((int)$attemptId);

        // Students may only see their own attempts
        if (!$attempt || (int)$attempt['user_id'] !== (int)$_SESSION['user_id']) {
            $_SESSION['flash_error'] = 'Result not found.';
            $this->redirect('practice');
        }

        $review = $attemptModel->getReview((int)$attemptId);

        // Count stats from the review rows themselves (always safe)
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

        $this->view('practice/result', [
            'attempt' => $attempt,
            'review'  => $review,
            'stats'   => [
                'total'      => count($review),
                'correct'    => $correct,
                'wrong'      => $wrong,
                'missed'     => $missed,
                'percentage' => (float)$attempt['percentage'],
            ],
            'activePage' => 'practice',
        ]);
    }
}