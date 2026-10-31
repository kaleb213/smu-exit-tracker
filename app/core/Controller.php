<?php
/**
 * ============================================================
 *  Controller.php — Base controller
 *  Provides: view() / model() / redirect()
 *  Plus access control: requireLogin() / requireRole()
 *  (PDF security: users cannot access functions outside their role)
 * ============================================================
 */

class Controller
{
    // Load a model, e.g. $this->model('Question') → app/models/Question.php
    public function model($name)
    {
        $file = APP_PATH . '/models/' . $name . '.php';
        if (file_exists($file)) {
            require_once $file;
            return new $name();
        }
        die('Model not found: ' . htmlspecialchars($name));
    }

    // Load a view, e.g. $this->view('auth/login', ['title' => 'Login'])
    public function view($name, $data = [])
    {
        extract($data);   // array keys become variables inside the view
        $file = APP_PATH . '/views/' . $name . '.php';
        if (file_exists($file)) {
            require $file;
        } else {
            die('View not found: ' . htmlspecialchars($name));
        }
    }

    // Redirect inside the app, e.g. $this->redirect('auth/login')
    public function redirect($path = '')
    {
        header('Location: ' . BASE_URL . '/' . ltrim($path, '/'));
        exit;
    }

    // ---------- Access control helpers ----------

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    // Block pages that need a login
    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('auth');
        }
    }

    // Block pages that need a specific role (student / instructor / admin)
    public function requireRole($role)
    {
        $this->requireLogin();
        if (($_SESSION['role'] ?? '') !== $role) {
            $_SESSION['access_denied'] = true;   // login page shows a warning
            $this->redirect('auth');
        }
    }
}