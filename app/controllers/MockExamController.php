<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ErrorPage;
use App\Models\ExamSession;
use App\Services\QuestionBankService;
use App\Services\TrackerService;

/**
 * Mock Exam Mode — timed simulation of the national exit exam:
 * configurable question count and duration, deferred feedback,
 * automatic evaluation on submit or timeout.
 */
final class MockExamController extends Controller
{
    /* ---------- start page ---------- */

    public function index(): void
    {
        $this->requireRole('student');

        $this->view('student/mock_setup', [
            'count'  => $this->setting('mock_question_count', 50),
            'minutes' => $this->setting('mock_time_limit_minutes', 60),
        ]);
    }

    /* ---------- create session and show the exam ---------- */

    public function start(): void
    {
        $this->requireRole('student');

        $count   = $this->setting('mock_question_count', 50);
        $minutes = $this->setting('mock_time_limit_minutes', 60);

        $questions = QuestionBankService::mockSet($count);
        if ($questions === []) {
            $this->flash('error', 'The question bank is empty. Ask the administrator to add questions.');
            $this->redirect('mock/index');
        }

        $sessionId = TrackerService::startSession(Auth::id(), 'mock', null, null, $questions, $minutes);

        $this->view('student/mock_run', [
            'sessionId'   => $sessionId,
            'questions'   => $questions,
            'minutes'     => $minutes,
            'deadlineSec' => $minutes * 60,
        ]);
    }

    /* ---------- submit (manual or automatic on timeout) ---------- */

    public function submit(): void
    {
        $this->requireRole('student');
        $sessionId = $this->inputInt('session');

        if ($sessionId === null || !ExamSession::belongsToUser($sessionId, Auth::id())) {
            $this->redirect('mock/index');
        }

        $session = ExamSession::find($sessionId);
        if ($session === null || $session['status'] !== 'in_progress') {
            // already submitted (double submit / back button)
            $this->redirect('progress/results/' . $sessionId);
        }

        // evaluate exactly what was answered; unanswered ones are skipped
        $answers = $_POST['answers'] ?? [];
        if (is_array($answers)) {
            foreach ($answers as $qid => $selected) {
                $qid = (int) $qid;
                if (!in_array($selected, ['A', 'B', 'C', 'D'], true)) {
                    continue;
                }
                $q = \App\Models\Question::find($qid);
                if ($q === null) {
                    continue;
                }
                ExamSession::addAnswer($sessionId, $qid, $selected, $selected === $q['correct_option']);
            }
        }

        $result = TrackerService::submit($sessionId, 'mock');

        if (TrackerService::deadlinePassed($sessionId)) {
            ExamSession::expire($sessionId); // late submission: status expired
        }

        $this->redirect('progress/results/' . $sessionId);
    }
}
