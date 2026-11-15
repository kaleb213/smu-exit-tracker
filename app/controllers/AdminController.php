<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\ErrorPage;
use App\Core\Session;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use App\Services\ReportService;
use App\Services\UserService;

/**
 * Administration module: users, subjects, topics, exam configuration
 * and system reports (full control, admin role only).
 */
final class AdminController extends Controller
{
    /* ---------- dashboard ---------- */

    public function dashboard(): void
    {
        $this->requireRole('admin');

        $this->view('admin/dashboard', [
            'stats' => ReportService::systemStats(),
        ]);
    }

    /* ================= USERS ================= */

    public function users(?int $page): void
    {
        $this->requireRole('admin');
        $page = max(1, $page ?? 1);

        $data = User::paginate($this->input('role'), $this->input('search'), $page, PAGINATION_PER_PAGE);

        $this->view('admin/users', [
            'rows'    => $data['rows'],
            'total'   => $data['total'],
            'pages'   => $data['pages'],
            'page'    => $page,
            'role'    => $this->input('role'),
            'search'  => $this->input('search'),
            'errors'  => [],
        ]);
    }

    /** Admin creates instructor accounts (no public registration). */
    public function createinstructor(): void
    {
        $this->requireRole('admin');

        $result = UserService::createInstructor(
            (string) ($this->input('name') ?? ''),
            (string) ($this->input('id_number') ?? ''),
            (string) ($this->input('password') ?? '')
        );

        if ($result['ok']) {
            $this->flash('ok', 'Instructor account created.');
        } else {
            $this->flash('error', implode(' ', $result['errors']));
        }
        $this->redirect('admin/users');
    }

    public function deactivate(?int $id): void
    {
        $this->requireRole('admin');
        $this->setStatus($id, 'deactivated');
    }

    public function activate(?int $id): void
    {
        $this->requireRole('admin');
        $this->setStatus($id, 'active');
    }

    private function setStatus(?int $id, string $status): void
    {
        if ($id !== null && $id !== Auth::id()) {
            $user = User::find($id);
            if ($user !== null && $user['role'] !== 'admin') {
                User::updateStatus($id, $status);
                $this->flash('ok', 'Account ' . ($status === 'active' ? 'activated' : 'deactivated') . '.');
            }
        }
        $this->redirect('admin/users');
    }

    public function resetpassword(?int $id): void
    {
        $this->requireRole('admin');
        if ($id !== null) {
            $result = UserService::resetPassword($id, (string) ($this->input('password') ?? ''));
            $this->flash($result['ok'] ? 'ok' : 'error', $result['ok']
                ? 'Password reset successfully.'
                : implode(' ', $result['errors']));
        }
        $this->redirect('admin/users');
    }

    public function deleteuser(?int $id): void
    {
        $this->requireRole('admin');
        if ($id !== null && $id !== Auth::id()) {
            $user = User::find($id);
            if ($user !== null && $user['role'] !== 'admin') {
                User::delete($id); // cascades their sessions/answers
                $this->flash('ok', 'User and all related records were removed.');
            }
        }
        $this->redirect('admin/users');
    }

    /* ================= SUBJECTS ================= */

    public function subjects(): void
    {
        $this->requireRole('admin');
        $this->view('admin/subjects', ['subjects' => Subject::allWithCounts()]);
    }

    public function storesubject(): void
    {
        $this->requireRole('admin');
        $name = (string) ($this->input('name') ?? '');
        $desc = (string) ($this->input('description') ?? '');
        if (mb_strlen($name) >= 3) {
            try {
                Subject::create($name, $desc);
                $this->flash('ok', 'Subject added.');
            } catch (\Throwable) {
                $this->flash('error', 'Could not add subject (name may already exist).');
            }
        } else {
            $this->flash('error', 'Subject name is too short.');
        }
        $this->redirect('admin/subjects');
    }

    public function updatesubject(): void
    {
        $this->requireRole('admin');
        $id = $this->inputInt('id');
        if ($id !== null) {
            try {
                Subject::update($id, (string) ($this->input('name') ?? ''), (string) ($this->input('description') ?? ''));
                $this->flash('ok', 'Subject updated.');
            } catch (\Throwable) {
                $this->flash('error', 'Could not update subject (name may already exist).');
            }
        }
        $this->redirect('admin/subjects');
    }

    public function deletesubject(?int $id): void
    {
        $this->requireRole('admin');
        if ($id !== null) {
            if (Subject::hasQuestions($id)) {
                $this->flash('error', 'This subject still has questions. Delete its questions first.');
            } else {
                Subject::delete($id); // cascades topics
                $this->flash('ok', 'Subject and its topics were removed.');
            }
        }
        $this->redirect('admin/subjects');
    }

    /* ================= TOPICS ================= */

    public function topics(): void
    {
        $this->requireRole('admin');
        $this->view('admin/topics', [
            'topics'   => Topic::allWithSubject(),
            'subjects' => Subject::all(),
        ]);
    }

    public function storetopic(): void
    {
        $this->requireRole('admin');
        $subjectId = $this->inputInt('subject_id');
        $name = (string) ($this->input('name') ?? '');
        if ($subjectId !== null && mb_strlen($name) >= 2) {
            try {
                Topic::create($subjectId, $name);
                $this->flash('ok', 'Topic added.');
            } catch (\Throwable) {
                $this->flash('error', 'Could not add topic (it may already exist for this subject).');
            }
        } else {
            $this->flash('error', 'Choose a subject and enter a topic name.');
        }
        $this->redirect('admin/topics');
    }

    public function updatetopic(): void
    {
        $this->requireRole('admin');
        $id = $this->inputInt('id');
        if ($id !== null) {
            try {
                Topic::update($id, (string) ($this->input('name') ?? ''));
                $this->flash('ok', 'Topic updated.');
            } catch (\Throwable) {
                $this->flash('error', 'Could not update topic.');
            }
        }
        $this->redirect('admin/topics');
    }

    public function deletetopic(?int $id): void
    {
        $this->requireRole('admin');
        if ($id !== null) {
            if (Topic::hasQuestions($id)) {
                $this->flash('error', 'This topic still has questions. Delete its questions first.');
            } else {
                Topic::delete($id);
                $this->flash('ok', 'Topic removed.');
            }
        }
        $this->redirect('admin/topics');
    }

    /* ================= EXAM CONFIGURATION ================= */

    public function config(): void
    {
        $this->requireRole('admin');

        $this->view('admin/exam_config', [
            'config' => [
                'mock_question_count'     => $this->setting('mock_question_count', 50),
                'mock_time_limit_minutes' => $this->setting('mock_time_limit_minutes', 60),
                'practice_question_count' => $this->setting('practice_question_count', 10),
                'session_timeout_minutes' => $this->setting('session_timeout_minutes', 30),
                'readiness_target'        => $this->setting('readiness_target', 80),
            ],
            'saved' => Session::pullFlash('configSaved'),
        ]);
    }

    public function saveconfig(): void
    {
        $this->requireRole('admin');

        $keys = [
            'mock_question_count'     => [1, 200],
            'mock_time_limit_minutes' => [1, 600],
            'practice_question_count' => [1, 100],
            'session_timeout_minutes' => [1, 240],
            'readiness_target'        => [0, 100],
        ];

        foreach ($keys as $key => [$min, $max]) {
            $value = $this->inputInt($key, $min) ?? $min;
            $value = max($min, min($max, $value));
            Database::execute(
                'UPDATE settings SET setting_value = ? WHERE setting_key = ?',
                [(string) $value, $key]
            );
        }

        $this->flash('configSaved', 'Exam configuration saved.');
        $this->redirect('admin/config');
    }
}
