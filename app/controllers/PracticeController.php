<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ErrorPage;
use App\Models\ExamSession;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\QuestionBankService;
use App\Services\TrackerService;

/**
 * Practice Mode — untimed, topic-level questions with immediate
 * AJAX feedback (use case: Student Takes Practice Test).
 */
final class PracticeController extends Controller
{
    /* ---------- setup: choose subject + topic ---------- */

    public function index(): void
    {
        $this->requireRole('student');

        $subjectId = $this->inputInt('subject');
        $topics = $subjectId !== null ? Topic::bySubject($subjectId) : [];

        $this->view('student/practice_setup', [
            'subjects'   => Subject::allWithCounts(),
            'topics'     => $topics,
            'subjectId'  => $subjectId,
            'count'      => $this->setting('practice_question_count', 10),
        ]);
    }

    /* ---------- start a practice session ---------- */

    public function start(): void
    {
        $this->requireRole('student');

        $topicId = $this->inputInt('topic');
        if ($topicId === null) {
            $this->flash('error', 'Please choose a topic first.');
            $this->redirect('practice/index');
        }

        $topic = Topic::find($topicId);
        if ($topic === null) {
            ErrorPage::show(404);
            return;
        }

        $count = $this->setting('practice_question_count', 10);
        $questions = QuestionBankService::practiceSet($topicId, $count);

        if ($questions === []) {
            // documented alternative course: no questions exist
            $this->flash('error', 'No questions are available for this topic yet. Try another topic.');
            $this->redirect('practice/index?subject=' . (int) $topic['subject_id']);
        }

        $sessionId = TrackerService::startSession(
            Auth::id(), 'practice', (int) $topic['subject_id'], $topicId, $questions, null
        );

        $this->view('student/practice_run', [
            'sessionId'   => $sessionId,
            'topic'       => $topic,
            'total'       => count($questions),
            'subjectName' => $topic['subject_name'],
        ]);
    }

    /* ---------- AJAX: current question (answer hidden) ---------- */

    public function question(): void
    {
        $this->requireRole('student');
        $sessionId = $this->inputInt('session') ?? 0;
        if (!ExamSession::belongsToUser($sessionId, Auth::id())) {
            $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }
        $q = TrackerService::currentQuestion($sessionId);
        $this->json(['ok' => true, 'question' => $q, 'finished' => $q === null]);
    }

    /* ---------- AJAX: submit one answer, get instant feedback ---------- */

    public function answer(): void
    {
        $this->requireRole('student');
        $sessionId  = $this->inputInt('session');
        $questionId = $this->inputInt('question');
        $selected   = strtoupper((string) ($this->input('selected') ?? ''));

        if ($sessionId === null || $questionId === null || !in_array($selected, ['A', 'B', 'C', 'D'], true)) {
            $this->json(['ok' => false, 'error' => 'invalid input'], 400);
        }
        if (!ExamSession::belongsToUser($sessionId, Auth::id())) {
            $this->json(['ok' => false, 'error' => 'forbidden'], 403);
        }

        $feedback = TrackerService::recordAnswer($sessionId, $questionId, $selected);
        $this->json($feedback);
    }

    public function skip(): void
    {
        $this->requireRole('student');
        $sessionId = $this->inputInt('session');
        if ($sessionId !== null && ExamSession::belongsToUser($sessionId, Auth::id())) {
            TrackerService::skipCurrent($sessionId);
        }
        $this->json(['ok' => true]);
    }

    /* ---------- submit & redirect to results ---------- */

    public function submit(): void
    {
        $this->requireRole('student');
        $sessionId = $this->inputInt('session');
        if ($sessionId === null || !ExamSession::belongsToUser($sessionId, Auth::id())) {
            $this->redirect('practice/index');
        }
        TrackerService::submit($sessionId, 'practice');
        $this->redirect('progress/results/' . $sessionId);
    }
}
