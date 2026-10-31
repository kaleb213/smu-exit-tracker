<?php
/**
 * ============================================================
 *  AuthController.php — Login, Registration, Logout
 *  PDF rules: ONE login form for all roles (role read from the
 *  account record); students self-register; instructor/admin
 *  accounts are created by the administrator only.
 * ============================================================
 */

class AuthController extends Controller
{
    // GET /auth  → login page (or redirect if already logged in)
    public function index()
    {
        if ($this->isLoggedIn()) {
            $this->redirectByRole($_SESSION['role']);
        }
        $this->view('auth/login');
    }

    // POST /auth/login → check credentials
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('auth');
        }

        $idNumber = trim($_POST['id_number'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($idNumber === '' || $password === '') {
            $_SESSION['flash_error'] = 'Please enter both your ID number and password.';
            $this->redirect('auth');
        }

        $userModel = $this->model('User');
        $user = $userModel->findByIdNumber($idNumber);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Invalid ID number or password.';
            $this->redirect('auth');
        }

        // Login OK — protect against session fixation
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['id'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];

        // "Keep me signed in": session cookie lasts 30 days.
        // (The 30-minute inactivity timeout still applies — PDF requirement.)
        if (!empty($_POST['remember'])) {
            setcookie(session_name(), session_id(), [
                'expires'  => time() + 30 * 24 * 60 * 60,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        $_SESSION['flash_success'] = 'Welcome back, ' . $user['full_name'] . '!';
        $this->redirectByRole($user['role']);
    }

    // GET /auth/register → show form | POST → create student account
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processRegistration();
        }
        $this->view('auth/register');
    }

    // PDF use case: validate → check ID is unique → create account → success message
    private function processRegistration()
    {
        $fullName    = trim($_POST['full_name'] ?? '');
        $idNumber    = trim($_POST['id_number'] ?? '');
        $password    = $_POST['password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        $errors = [];

        if ($fullName === '' || mb_strlen($fullName) < 3) {
            $errors[] = 'Please enter your full name (at least 3 characters).';
        }
        if ($idNumber === '' || strlen($idNumber) < 4 || strlen($idNumber) > 30) {
            $errors[] = 'Please enter a valid Student ID (4–30 characters).';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }
        if ($password !== $confirmPass) {
            $errors[] = 'Passwords do not match.';
        }

        $userModel = $this->model('User');
        if (!$errors && $userModel->idNumberExists($idNumber)) {
            $errors[] = 'This Student ID is already registered. Please log in instead.';
        }

        if ($errors) {
            $_SESSION['flash_error']   = implode(' ', $errors);
            $_SESSION['old_full_name'] = $fullName;
            $_SESSION['old_id_number'] = $idNumber;
            $this->redirect('auth/register');
        }

        // Password is HASHED — plaintext is never stored (PDF security)
        $userModel->createStudent($fullName, $idNumber, password_hash($password, PASSWORD_DEFAULT));

        $_SESSION['flash_success'] = 'Registration successful! You can now log in with your Student ID.';
        $this->redirect('auth');
    }

    // GET /auth/forgot → guidance only (PDF has no self-service reset flow)
    public function forgot()
    {
        $_SESSION['flash_info'] = 'Password resets are handled by the system administrator (ICT Unit). Please contact them to reset your password.';
        $this->redirect('auth');
    }

    // GET /auth/logout → destroy session completely
    public function logout()
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        $this->redirect('auth');
    }

    // Send each role to its own dashboard
    private function redirectByRole($role)
    {
        switch ($role) {
            case ROLE_ADMIN:      $this->redirect('admin'); break;
            case ROLE_INSTRUCTOR: $this->redirect('instructor'); break;
            default:              $this->redirect('student');
        }
    }
}