<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\ErrorPage;
use App\Core\Session;
use App\Models\ExamSession;
use App\Models\Result;
use App\Models\Subject;
use App\Models\User;
use App\Services\ScoreService;
use App\Services\UserService;

/**
 * Student dashboard, progress tracking, results & feedback and
 * profile management (Progress Tracking subsystem read-side).
 */
final class ProgressController extends Controller
{
    /* ---------- main student dashboard ---------- */

    public function dashboard(): void
    {
        $this->requireRole('student');
        $userId = Auth::id();

        $attempts     = ExamSession::countForUser($userId);
        $mockAttempts = ExamSession::countForUser($userId, 'mock');
        $history      = ExamSession::lastCompleted($userId, 8);
        $topics       = ExamSession::topicStatsForUser($userId);
        $subjects     = ExamSession::subjectStatsForUser($userId);

        $readiness = ScoreService::readinessForUser($userId);
        $trend     = ScoreService::trendForUser($userId);
        $goal      = ScoreService::goalForUser($userId);

        $weak = array_values(array_filter(
            $topics,
            fn ($t) => (int) $t['accuracy'] < 50
        ));

        $this->view('student/dashboard', [
            'user'         => Auth::user(),
            'readiness'    => $readiness,
            'goal'         => $goal,
            'trend'        => $trend,
            'attempts'     => $attempts,
            'mockAttempts' => $mockAttempts,
            'weakCount'    => count($weak),
            'history'      => $history,
            'topics'       => array_slice($topics, 0, 6),
            'subjects'     => $subjects,
            'subjectCards' => Subject::allWithCounts(),
            'goalSaved'    => Session::pullFlash('goalSaved'),
        ]);
    }

    /* ---------- full progress page (all analytics) ---------- */

    public function index(): void
    {
        $this->requireRole('student');
        $userId = Auth::id();

        $this->view('student/progress', [
            'history'      => ExamSession::lastCompleted($userId, 10),
            'topics'       => ExamSession::topicStatsForUser($userId),
            'subjects'     => ExamSession::subjectStatsForUser($userId),
            'readiness'    => ScoreService::readinessForUser($userId),
            'goal'         => ScoreService::goalForUser($userId),
            'goalSaved'    => Session::pullFlash('goalSaved'),
        ]);
    }

    /* ---------- goal-setting tool (POST) ---------- */

    public function goal(): void
    {
        $this->requireRole('student');
        $target = $this->inputInt('target', 80);
        ScoreService::saveGoal(Auth::id(), $target ?? 80);
        $this->flash('goalSaved', 'Your readiness target was saved.');
        $this->redirect('progress/index');
    }

    /* ---------- results & feedback of one attempt ---------- */

    public function results(?int $sessionId): void
    {
        $this->requireRole('student');
        if ($sessionId === null) {
            $this->redirect('progress/history');
        }
        $session = ExamSession::find($sessionId);
        if ($session === null || (int) $session['user_id'] !== Auth::id()) {
            ErrorPage::show(403);
            return;
        }

        $this->view('student/results', [
            'session'  => $session,
            'topics'   => Result::topicBreakdown($sessionId),
            'review'   => Result::answerReview($sessionId, 100),
            'band'     => ScoreService::band((float) $session['score_percent']),
        ]);
    }

    /* ---------- attempt history (paginated — boundary condition) ---------- */

    public function history(?int $page): void
    {
        $this->requireRole('student');
        $page = max(1, $page ?? 1);
        $perPage = PAGINATION_PER_PAGE;

        $data = ExamSession::paginateForUser(Auth::id(), $page, $perPage);

        $this->view('student/history', [
            'rows'    => $data['rows'],
            'total'   => $data['total'],
            'pages'   => $data['pages'],
            'page'    => $page,
            'perPage' => $perPage,
        ]);
    }

    /* ---------- profile ---------- */

    public function profile(): void
    {
        $this->requireRole('student');

        $data = ExamSession::paginateForUser(Auth::id(), 1, 5);

        $this->view('student/profile', [
            'user'      => Auth::user(),
            'created'   => User::find(Auth::id())['created_at'] ?? '',
            'recent'    => $data['rows'],
            'errors'    => [],
            'saved'     => Session::pullFlash('profileSaved'),
        ]);
    }

    public function updateprofile(): void
    {
        $this->requireRole('student');

        $result = UserService::updateProfile(Auth::id(), (string) ($this->input('name') ?? ''));
        if (!$result['ok']) {
            $this->flash('profileError', $result['errors'][0]);
        } else {
            $this->flash('profileSaved', 'Profile updated.');
        }
        $this->redirect('progress/profile');
    }

    public function changepassword(): void
    {
        $this->requireRole('student');

        $result = UserService::changePassword(
            Auth::id(),
            (string) ($this->input('current_password') ?? ''),
            (string) ($this->input('new_password') ?? ''),
            (string) ($this->input('confirm_password') ?? '')
        );
        if (!$result['ok']) {
            $this->flash('profileError', $result['errors'][0]);
        } else {
            $this->flash('profileSaved', 'Password changed successfully.');
        }
        $this->redirect('progress/profile');
    }

    /* ---------- delete own account (activity diagram) ---------- */

    public function deleteaccount(): void
    {
        $this->requireRole('student');

        $result = UserService::deleteOwnAccount(Auth::id(), (string) ($this->input('password') ?? ''));
        if (!$result['ok']) {
            $this->flash('profileError', $result['errors'][0]);
            $this->redirect('progress/profile');
        }
        header('Location: ' . \App\Core\View::url('auth/login'));
        exit;
    }
}
