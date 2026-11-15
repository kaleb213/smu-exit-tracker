<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Router (Global Software Control).
 *
 * Implements the documented access-control flow:
 *   Incoming request -> valid session? -> role matches? -> controller
 *
 * URL format: /controller/action/param  (PATH_INFO after index.php)
 * Falls back to $_GET['r']=controller/action when PATH_INFO is not
 * available, so the app runs on any Apache/XAMPP configuration.
 */
final class App
{
    public function run(): void
    {
        Session::start();

        $route = $this->resolveRoute();

        $segments = array_values(array_filter(explode('/', trim((string) $route, '/')), fn ($s) => $s !== ''));

        $controllerName = $segments[0] ?? '';
        $action         = $segments[1] ?? 'index';
        $param          = $segments[2] ?? null;
        if ($controllerName === '') {
            $controllerName = 'home';
        }

        /* friendly URL aliases -> real controller file names */
        $aliases = ['mock' => 'mockexam', 'questionbank' => 'questionbank'];
        if (isset($aliases[strtolower($controllerName)])) {
            $controllerName = $aliases[strtolower($controllerName)];
        }

        $class = 'App\\Controllers\\' . ucfirst(preg_replace('/[^a-z]/i', '', strtolower($controllerName))) . 'Controller';
        $method = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $action));

        try {
            if (!class_exists($class)) {
                ErrorPage::show(404);
                return;
            }
            $controller = new $class();
            if (!method_exists($controller, $method)) {
                ErrorPage::show(404);
                return;
            }

            /* ----- access control ----- */
            if (!$controller->isPublic() && !Auth::check()) {
                if (Auth::check() === false && Session::wasExpired()) {
                    Session::flash('error', 'Your session expired after inactivity. Please sign in again.');
                    Session::clearExpired();
                }
                $this->redirect('auth/login');
                return;
            }

            /* POST requests must carry a valid CSRF token */
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
                $token = $_POST['_csrf'] ?? '';
                if (!Session::verifyCsrf(is_string($token) ? $token : null)) {
                    ErrorPage::show(403, 'Invalid or missing security token. Go back, refresh the page and try again.');
                    return;
                }
            }

            // normalize the optional /param segment to int|null for controllers
            if ($param !== null && ctype_digit((string) $param)) {
                $param = (int) $param;
            }
            $controller->{$method}($param);
        } catch (\Throwable $e) {
            if (APP_DEBUG) {
                http_response_code(500);
                echo '<pre style="padding:20px;background:#fff;color:#900">'
                    . 'Unhandled error: ' . htmlspecialchars($e->getMessage())
                    . "\n\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
            } else {
                ErrorPage::show(500, 'An unexpected error occurred. Please try again.');
            }
        }
    }

    /**
     * Determine the current route and the base directory that
     * contains index.php, then hand the base to the View helper.
     *
     * Supports every deployment style:
     *   /index.php/auth/login                (PATH_INFO, php -S router)
     *   /senior-project/public/index.php/... (XAMPP sub-folder)
     *   /auth/login                          (mod_rewrite pretty URLs)
     */
    private function resolveRoute(): string
    {
        $route = '';
        $uri   = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        if (isset($_GET['r']) && is_string($_GET['r']) && $_GET['r'] !== '') {
            $route = $_GET['r']; // explicit query routing fallback
        } elseif (($pos = strpos($uri, '/index.php')) !== false) {
            // URL embeds the front controller: base = everything before it
            View::setBase(substr($uri, 0, $pos));
            $route = substr($uri, $pos + strlen('/index.php'));
        } else {
            // pretty URL (mod_rewrite) or root deployment
            $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
            if ($base !== '/' && $base !== '' && str_starts_with($uri, $base)) {
                $uri = substr($uri, strlen($base));
                View::setBase($base);
            }
            $route = $uri;
        }

        return $route;
    }

    private function redirect(string $route): void
    {
        header('Location: ' . View::url($route));
        exit;
    }
}

/**
 * 403 / 404 / 500 error pages (documented error-handling requirement:
 * useful messages without exposing sensitive details).
 */
final class ErrorPage
{
    public static function show(int $code, string $message = ''): void
    {
        http_response_code($code);
        $title = $code === 403 ? 'Access denied'
            : ($code === 404 ? 'Page not found' : 'Something went wrong');
        $text = $message !== '' ? $message
            : ($code === 403 ? 'You do not have permission to access this resource.'
            : ($code === 404 ? 'The page you requested does not exist.' : 'Please try again later.'));
        View::render('errors/error', ['code' => $code, 'title' => $title, 'text' => $text, 'bare' => true]);
    }
}
