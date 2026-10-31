<?php
/**
 * ============================================================
 *  App.php — The Router
 *  URL format: http://localhost/smu-exam-tracker/{controller}/{method}/{param}
 *  Examples:
 *    /auth/login        → AuthController::login()
 *    /practice/take/3   → PracticeController::take(3)
 *  Default route: /auth  →  AuthController::index()
 * ============================================================
 */

class App
{
    public function __construct()
    {
        $url = $this->getUrl();

        // ---- 1. Which controller? (default: auth = login page) ----
        $segment = (isset($url[0]) && $url[0] !== '') ? $url[0] : 'auth';
        if (!preg_match('/^[a-zA-Z]+$/', $segment)) {
            $this->show404();
        }
        $controllerName = ucfirst(strtolower($segment)) . 'Controller';

        // ---- 2. Does the controller file exist? ----
        $file = APP_PATH . '/controllers/' . $controllerName . '.php';
        if (!file_exists($file)) {
            $this->show404();
        }
        require_once $file;

        // ---- 3. Does the class exist inside the file? ----
        if (!class_exists($controllerName)) {
            $this->show404();
        }
        $controller = new $controllerName();

        // ---- 4. Which method? (default: index) ----
        $method = (isset($url[1]) && $url[1] !== '') ? $url[1] : 'index';

        // is_callable = the method exists AND is public (blocks private helpers)
        if (!is_callable([$controller, $method])) {
            $this->show404();
        }

        // ---- 5. Remaining URL parts become method parameters ----
        $params = array_values(array_slice($url, 2));
        call_user_func_array([$controller, $method], $params);
    }

    // Extract URL parts. Works with .htaccess rewriting AND without it.
    private function getUrl()
    {
        // Case 1: .htaccess rewrote the URL and passed it as ?url=...
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return ($url === '') ? [] : explode('/', $url);
        }

        // Case 2: fallback — parse the real URI (works even without .htaccess)
        $uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        if ($base !== '' && strpos($uri, $base) === 0) {
            $uri = substr($uri, strlen($base));
        }
        $uri = trim($uri, '/');
        return ($uri === '') ? [] : explode('/', $uri);
    }

    // Show the 404 page and stop
    private function show404()
    {
        http_response_code(404);
        $file = APP_PATH . '/views/errors/404.php';
        if (file_exists($file)) {
            require $file;
        } else {
            echo '404 — Page not found';
        }
        exit;
    }
}