<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExamSession;
use App\Models\Question;

/**
 * Practice Engine + Progress Tracker write-side.
 * Creates sessions, records answers and persists scores
 * (practice session control flow of the design document).
 */
final class TrackerService
{
    /** Start a session and remember the question IDs in the PHP session. */
    public static function startSession(int $userId, string $mode, ?int $subjectId, ?int $topicId, array $questions, ?int $timeLimit): int
    {
        $sessionId = ExamSession::create($userId, $mode, $subjectId, $topicId, count($questions), $timeLimit);
        SessionStorage::put('exam', [
            'session_id'  => $sessionId,
            'mode'        => $mode,
            'question_ids' => array_map(fn ($q) => (int) $q['id'], $questions),
            'position'    => 0,
        ]);
        return $sessionId;
    }

    /** Fetch the current question (without the answer) for the exam UI. */
    public static function currentQuestion(int $sessionId): ?array
    {
        $exam = SessionStorage::get('exam');
        if ($exam === null || (int) $exam['session_id'] !== $sessionId) {
            return null;
        }
        $ids = $exam['question_ids'];
        $pos = (int) $exam['position'];
        if ($pos >= count($ids)) {
            return null; // finished
        }
        $q = Question::find((int) $ids[$pos]);
        if ($q === null) {
            return null;
        }
        // strip the answer before sending to the browser
        unset($q['correct_option'], $q['explanation']);
        return $q;
    }

    /** Record one answer and return immediate feedback (Practice Mode). */
    public static function recordAnswer(int $sessionId, int $questionId, ?string $selected): array
    {
        $q = Question::find($questionId);
        if ($q === null) {
            return ['ok' => false];
        }
        $correct = $selected !== null && $selected === $q['correct_option'];
        ExamSession::addAnswer($sessionId, $questionId, $selected, $correct);

        // advance the pointer
        $exam = SessionStorage::get('exam');
        if ($exam !== null) {
            $ids = $exam['question_ids'];
            $idx = array_search($questionId, array_map('intval', $ids), true);
            if ($idx !== false && (int) $idx === (int) $exam['position']) {
                $exam['position'] = min(count($ids), (int) $exam['position'] + 1);
                SessionStorage::put('exam', $exam);
            }
        }

        return [
            'ok'            => true,
            'correct'       => $correct,
            'correctOption' => $q['correct_option'],
            'explanation'   => $q['explanation'],
        ];
    }

    /** Move to the next unanswered question without answering (skip). */
    public static function skipCurrent(int $sessionId): void
    {
        $exam = SessionStorage::get('exam');
        if ($exam === null || (int) $exam['session_id'] !== $sessionId) {
            return;
        }
        $exam['position'] = min(count($exam['question_ids']), (int) $exam['position'] + 1);
        SessionStorage::put('exam', $exam);
    }

    /** Submit and score the session. Unanswered mock questions = skipped. */
    public static function submit(int $sessionId, string $mode): array
    {
        if ($mode === 'mock') {
            // record every question of the mock that was never answered
            $exam = SessionStorage::get('exam');
            if ($exam !== null && (int) $exam['session_id'] === $sessionId) {
                $answered = array_map(fn ($r) => (int) $r, array_column(
                    \App\Core\Database::query(
                        'SELECT question_id FROM session_answers WHERE session_id = ?',
                        [$sessionId]
                    ),
                    'question_id'
                ));
                foreach ($exam['question_ids'] as $qid) {
                    if (!in_array((int) $qid, $answered, true)) {
                        ExamSession::addAnswer($sessionId, (int) $qid, null, false);
                    }
                }
            }
        }
        $result = ExamSession::complete($sessionId);
        SessionStorage::forget('exam');
        return $result;
    }

    /** Server-side deadline check for timed mock exams. */
    public static function deadlinePassed(int $sessionId): bool
    {
        $s = ExamSession::find($sessionId);
        if ($s === null || $s['time_limit_minutes'] === null) {
            return false;
        }
        $deadline = strtotime($s['started_at']) + ((int) $s['time_limit_minutes']) * 60;
        return time() > $deadline;
    }
}

/**
 * Small wrapper around the PHP session used to store in-flight exam
 * state (question order, pointer, mock deadline) server-side.
 */
final class SessionStorage
{
    public static function put(string $key, mixed $value): void
    {
        $_SESSION['storage'][$key] = $value;
    }

    public static function get(string $key): mixed
    {
        return $_SESSION['storage'][$key] ?? null;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION['storage'][$key]);
    }
}
