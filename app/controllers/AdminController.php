<?php
/**
 * AdminController — the admin panel.
 * URLs: /admin, /admin/users, /admin/user/{id}, /admin/subjects, /admin/topics,
 *       /admin/questions, /admin/question/{id}, /admin/settings
 */
if (!class_exists('Admin')) {
    require_once dirname(__DIR__) . '/models/Admin.php';
}

if (!function_exists('admin_url')) {
    function admin_url($path = '')
    {
        if (defined('BASE_URL')) {
            $base = rtrim((string)BASE_URL, '/');
        } else {
            $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
            $base = preg_replace('#/(public/)?index\.php$#', '', $script);
        }
        return $base . '/' . ltrim($path, '/');
    }
}

class AdminController
{
    private $model;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) { @session_start(); }
        $this->model = new Admin();
    }

    /* ---------- helpers ---------- */

    private function guard()
    {
        if (empty($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Please log in first.';
            header('Location: ' . admin_url('login'));
            exit;
        }
        $role = strtolower(trim((string)($_SESSION['role'] ?? '')));
        $isAdmin = ($role === 'admin');
        if (!$isAdmin && defined('ROLE_ADMIN') && strcasecmp($role, strtolower((string)ROLE_ADMIN)) === 0) {
            $isAdmin = true;
        }
        if (!$isAdmin) {
            $_SESSION['flash_error'] = 'That page is for administrators only.';
            header('Location: ' . admin_url($role === 'instructor' ? 'instructor' : 'student'));
            exit;
        }
    }

    private function render($view, array $data = [])
    {
        extract($data, EXTR_SKIP);
        require dirname(__DIR__) . '/views/admin/' . $view . '.php';
    }

    private function redirect($path) { header('Location: ' . admin_url($path)); exit; }

    private function post($key, $default = '') { return isset($_POST[$key]) ? trim((string)$_POST[$key]) : $default; }
    private function get($key, $default = '')  { return isset($_GET[$key]) ? trim((string)$_GET[$key]) : $default; }
    private function isPost() { return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }

    /* ---------- dashboard ---------- */

    public function index()
    {
        $this->guard();
        $this->render('index', [
            'pageTitle' => 'Admin Dashboard',
            'activeTab' => '',
            'stats'  => $this->model->stats(),
            'recent' => $this->model->recentAttempts(8),
        ]);
    }

    /* ---------- users ---------- */

    public function users()
    {
        $this->guard();
        if ($this->isPost()) {
            $idNumber = $this->post('id_number');
            $password = $this->post('password');
            $role = $this->post('role', 'student');
            if (!in_array($role, ['student', 'instructor', 'admin'], true)) $role = 'student';
            if ($idNumber === '' || $password === '') {
                $_SESSION['flash_error'] = 'ID number and password are required.';
            } elseif ($this->model->idNumberTaken($idNumber)) {
                $_SESSION['flash_error'] = 'That ID number is already registered.';
            } else {
                $data = ['id_number' => $idNumber, 'role' => $role];
                foreach ($this->model->userNameCols() as $col) {
                    if (isset($_POST[$col])) $data[$col] = $this->post($col);
                }
                $data[$this->model->passwordCol()] = password_hash($password, PASSWORD_DEFAULT);
                $ac = $this->model->activeCol();
                if ($ac) $data[$ac] = 1;
                try {
                    $this->model->createUser($data);
                    $_SESSION['flash_success'] = 'User created: ' . $idNumber . ' (' . $role . ')';
                } catch (Throwable $e) {
                    $_SESSION['flash_error'] = 'Could not create user: ' . $e->getMessage();
                }
            }
            $this->redirect('admin/users');
        }
        $this->render('users', [
            'pageTitle'  => 'Manage Users',
            'activeTab'  => 'users',
            'rows'       => $this->model->allUsers($this->get('role'), $this->get('q')),
            'filterRole' => $this->get('role'),
            'filterQ'    => $this->get('q'),
            'nameCols'   => $this->model->userNameCols(),
        ]);
    }

    public function user($id = 0)
    {
        $this->guard();
        $id = (int)$id;
        $user = $this->model->findUser($id);
        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirect('admin/users');
        }
        if ($this->isPost()) {
            $action = $this->post('action', 'update');
            try {
                if ($action === 'update') {
                    $newId = $this->post('id_number');
                    if ($newId === '') {
                        $_SESSION['flash_error'] = 'ID number cannot be empty.';
                    } elseif ($this->model->idNumberTaken($newId, $id)) {
                        $_SESSION['flash_error'] = 'Another account already uses that ID number.';
                    } else {
                        $data = ['id_number' => $newId, 'role' => $this->post('role', $user['role'])];
                        if (!in_array($data['role'], ['student', 'instructor', 'admin'], true)) {
                            $data['role'] = $user['role'];
                        }
                        foreach ($this->model->userNameCols() as $col) {
                            if (isset($_POST[$col])) $data[$col] = $this->post($col);
                        }
                        $this->model->updateUser($id, $data);
                        $_SESSION['flash_success'] = 'User updated.';
                    }
                } elseif ($action === 'resetpass') {
                    $pw = $this->post('new_password');
                    if (strlen($pw) < 4) {
                        $_SESSION['flash_error'] = 'New password must be at least 4 characters.';
                    } else {
                        $this->model->resetPassword($id, $pw);
                        $_SESSION['flash_success'] = 'Password reset — give the new password to the user.';
                    }
                } elseif ($action === 'toggle') {
                    $on = ($this->post('to') === 'on');
                    if ($this->model->setUserActive($id, $on)) {
                        $_SESSION['flash_success'] = $on ? 'Account activated.' : 'Account deactivated.';
                    } else {
                        $_SESSION['flash_error'] = 'This users table has no active/status column to toggle.';
                    }
                } elseif ($action === 'delete') {
                    $res = $this->model->deleteUser($id);
                    if ($res === 'ok') {
                        $_SESSION['flash_success'] = 'User deleted.';
                        $this->redirect('admin/users');
                    } elseif ($res === 'has_attempts') {
                        $_SESSION['flash_error'] = 'This user has exam attempts — deactivate instead of deleting.';
                    } else {
                        $_SESSION['flash_error'] = 'Could not delete user.';
                    }
                }
            } catch (Throwable $e) {
                $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
            }
            $this->redirect('admin/user/' . $id);
        }
        $this->render('user', [
            'pageTitle' => 'Edit User',
            'activeTab' => 'users',
            'user'      => $user,
            'nameCols'  => $this->model->userNameCols(),
        ]);
    }

    /* ---------- subjects ---------- */

    public function subjects()
    {
        $this->guard();
        if ($this->isPost()) {
            $action = $this->post('action', 'save');
            $id = (int)$this->post('id');
            $name = $this->post('name');
            try {
                if ($action === 'delete') {
                    $res = $this->model->deleteSubject($id);
                    $_SESSION[$res === 'ok' ? 'flash_success' : 'flash_error'] =
                        $res === 'ok' ? 'Subject deleted.' : 'Cannot delete: this subject still has topics.';
                } else {
                    if ($name === '') {
                        $_SESSION['flash_error'] = 'Subject name is required.';
                    } else {
                        $this->model->saveSubject($id, $name);
                        $_SESSION['flash_success'] = $id > 0 ? 'Subject updated.' : 'Subject added.';
                    }
                }
            } catch (Throwable $e) {
                $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
            }
            $this->redirect('admin/subjects');
        }
        $editId = (int)$this->get('edit');
        $this->render('subjects', [
            'pageTitle' => 'Manage Subjects',
            'activeTab' => 'subjects',
            'rows'      => $this->model->allSubjects(),
            'edit'      => $editId > 0 ? $this->model->findSubject($editId) : null,
        ]);
    }

    /* ---------- topics ---------- */

    public function topics()
    {
        $this->guard();
        if ($this->isPost()) {
            $action = $this->post('action', 'save');
            $id = (int)$this->post('id');
            $name = $this->post('name');
            $subjectId = (int)$this->post('subject_id');
            try {
                if ($action === 'delete') {
                    $res = $this->model->deleteTopic($id);
                    $_SESSION[$res === 'ok' ? 'flash_success' : 'flash_error'] =
                        $res === 'ok' ? 'Topic deleted.' : 'Cannot delete: questions belong to this topic.';
                } else {
                    if ($name === '' || $subjectId <= 0) {
                        $_SESSION['flash_error'] = 'Topic name and subject are required.';
                    } else {
                        $this->model->saveTopic($id, $name, $subjectId);
                        $_SESSION['flash_success'] = $id > 0 ? 'Topic updated.' : 'Topic added.';
                    }
                }
            } catch (Throwable $e) {
                $_SESSION['flash_error'] = 'Database error: ' . $e->getMessage();
            }
            $this->redirect('admin/topics');
        }
        $editId = (int)$this->get('edit');
        $subjectFilter = (int)$this->get('subject');
        $this->render('topics', [
            'pageTitle'     => 'Manage Topics',
            'activeTab'     => 'topics',
            'rows'          => $this->model->allTopics($subjectFilter),
            'subjects'      => $this->model->subjectOptions(),
            'edit'          => $editId > 0 ? $this->model->findTopic($editId) : null,
            'filterSubject' => $subjectFilter,
        ]);
    }

    /* ---------- questions ---------- */

    public function questions()
    {
        $this->guard();
        $page = max(1, (int)$this->get('page', 1));
        $fSubject = (int)$this->get('subject');
        $fTopic = (int)$this->get('topic');
        $fQ = $this->get('q');
        $res = $this->model->searchQuestions($fSubject, $fTopic, $fQ, $page, 15);
        $this->render('questions', [
            'pageTitle' => 'Question Bank',
            'activeTab' => 'questions',
            'rows'      => $res['rows'],
            'total'     => $res['total'],
            'page'      => $res['page'],
            'pages'     => $res['pages'],
            'subjects'  => $this->model->subjectOptions(),
            'topics'    => $this->model->topicOptions(),
            'fSubject'  => $fSubject,
            'fTopic'    => $fTopic,
            'fQ'        => $fQ,
            'answerCol' => $this->model->qAnswerCol(),
        ]);
    }

    public function question($id = 0)
    {
        $this->guard();
        $id = (int)$id;
        if ($this->isPost()) {
            $action = $this->post('action', 'save');
            $qid = (int)$this->post('id');
            if ($action === 'delete') {
                $res = $this->model->deleteQuestion($qid);
                $_SESSION[$res === 'ok' ? 'flash_success' : 'flash_error'] =
                    $res === 'ok' ? 'Question deleted.' : 'Cannot delete: students have already answered this question.';
                $this->redirect('admin/questions');
            }
            $data = [];
            foreach ($this->model->questionEditableCols() as $col) {
                if (isset($_POST['f_' . $col])) $data[$col] = $this->post('f_' . $col);
            }
            $tc = $this->model->topicQuestionCol();
            $sc = $this->model->questionSubjectCol();
            foreach ([$tc, $sc] as $c) {
                if ($c && array_key_exists($c, $data)) {
                    if ($data[$c] === '') unset($data[$c]);
                    else $data[$c] = (int)$data[$c];
                }
            }
            $mock = $this->model->qMockCol();
            if ($mock && stripos($this->model->colType('questions', $mock), 'int') !== false) {
                $data[$mock] = isset($_POST['f_' . $mock]) ? 1 : 0;
            }
            try {
                $this->model->saveQuestion($qid, $data);
                $_SESSION['flash_success'] = $qid > 0 ? 'Question updated.' : 'Question added to the bank.';
                $this->redirect('admin/questions');
            } catch (Throwable $e) {
                $_SESSION['flash_error'] = 'Could not save question: ' . $e->getMessage();
                $this->redirect('admin/question/' . $qid);
            }
        }
        $meta = [
            'text'     => $this->model->qTextCol(),
            'options'  => $this->model->qOptionCols(),
            'answer'   => $this->model->qAnswerCol(),
            'explain'  => $this->model->qExplainCol(),
            'diff'     => $this->model->qDiffCol(),
            'mock'     => $this->model->qMockCol(),
            'topic'    => $this->model->topicQuestionCol(),
            'subject'  => $this->model->questionSubjectCol(),
        ];
        $meta['mock_int'] = ($meta['mock'] && stripos($this->model->colType('questions', $meta['mock']), 'int') !== false);
        $this->render('question', [
            'pageTitle'     => $id > 0 ? 'Edit Question' : 'Add Question',
            'activeTab'     => 'questions',
            'q'             => $id > 0 ? $this->model->findQuestion($id) : null,
            'topics'        => $this->model->topicOptions(),
            'subjects'      => $this->model->subjectOptions(),
            'answerChoices' => $this->model->answerChoices(),
            'meta'          => $meta,
        ]);
    }

    /* ---------- exam settings ---------- */

    public function settings()
    {
        $this->guard();
        if ($this->isPost()) {
            $tl = (int)$this->post('mock_time_limit', 30);
            $mq = (int)$this->post('mock_max_questions', 50);
            $tl = min(180, max(5, $tl));
            $mq = min(100, max(5, $mq));
            try {
                $this->model->setSetting('mock_time_limit', (string)$tl);
                $this->model->setSetting('mock_max_questions', (string)$mq);
                $_SESSION['flash_success'] = 'Exam settings saved: mock exam = ' . $tl . ' minutes, ' . $mq . ' questions.';
            } catch (Throwable $e) {
                $_SESSION['flash_error'] = 'Could not save settings: ' . $e->getMessage();
            }
            $this->redirect('admin/settings');
        }
        $this->render('settings', [
            'pageTitle'    => 'Exam Settings',
            'activeTab'    => 'settings',
            'rows'         => $this->model->allSettings(),
            'timeLimit'    => $this->model->getSetting('mock_time_limit', '30'),
            'maxQuestions' => $this->model->getSetting('mock_max_questions', '50'),
        ]);
    }
}