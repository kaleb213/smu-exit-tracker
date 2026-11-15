<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base controller — every concrete controller extends this class.
 * Provides view rendering, redirects, JSON output, access control
 * (requireRole) and settings access.
 */
abstract class Controller
{
    /** Override to false for public pages (login, register). */
    public function isPublic(): bool
    {
        return false;
    }

    /* ---------- access control (role-based) ---------- */

    /** Redirect guest to login / wrong role to 403 (access-control flow). */
    protected function requireRole(string ...$roles): void
    {
        if (!Auth::check()) {
            $this->redirect('auth/login');
            exit;
        }
        if ($roles !== [] && !in_array(Auth::role(), $roles, true)) {
            ErrorPage::show(403);
            exit;
        }
    }

    protected function redirect(string $route): void
    {
        header('Location: ' . View::url($route));
        exit;
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* ---------- views ---------- */

    /** Render a view inside the main layout. */
    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    /** Render a partial view fragment (returns string). */
    protected function partial(string $view, array $data = []): string
    {
        return View::partial($view, $data);
    }

    /* ---------- input helpers ---------- */

    protected function input(string $key, ?string $default = null): ?string
    {
        $v = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($v) ? trim($v) : $default;
    }

    protected function inputInt(string $key, ?int $default = null): ?int
    {
        $v = $this->input($key);
        return ($v !== null && $v !== '' && ctype_digit($v)) ? (int) $v : $default;
    }

    /* ---------- flash ---------- */

    protected function flash(string $key, string $message): void
    {
        Session::flash($key, $message);
    }

    /* ---------- settings ---------- */

    /** Read an admin-configurable setting with a default fallback. */
    public static function setting(string $key, int $default): int
    {
        $row = Database::first('SELECT setting_value FROM settings WHERE setting_key = ?', [$key]);
        return $row !== null ? (int) $row['setting_value'] : $default;
    }
}
