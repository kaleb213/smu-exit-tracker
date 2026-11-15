<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Session management.
 *
 * NFR (documented): sessions automatically time out after 30 minutes
 * of inactivity to protect accounts on shared/public computers.
 * The timeout is admin-configurable via the `settings` table.
 *
 * Also provides flash messages and CSRF token helpers used by
 * every form in the system.
 */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        session_name('SMU_EXITEXAM_SESS');
        session_start();

        $timeout = self::timeoutMinutes();

        /* ----- idle timeout ----- */
        $last = $_SESSION['_last_activity'] ?? null;
        if ($last !== null && (time() - (int) $last) > $timeout * 60) {
            self::markExpired();
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['_last_activity'] = time();

        /* ----- rotate CSRF token ----- */
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
    }

    /** Timeout in minutes: settings table value, else config default. */
    public static function timeoutMinutes(): int
    {
        try {
            $row = Database::first('SELECT setting_value FROM settings WHERE setting_key = ?', ['session_timeout_minutes']);
            if ($row !== null && (int) $row['setting_value'] > 0) {
                return (int) $row['setting_value'];
            }
        } catch (\Throwable) {
            // database not reachable yet — fall back to config default
        }
        return SESSION_TIMEOUT_MINUTES;
    }

    public static function markExpired(): void
    {
        $_SESSION['_expired'] = true;
    }

    public static function wasExpired(): bool
    {
        return !empty($_SESSION['_expired']);
    }

    public static function clearExpired(): void
    {
        unset($_SESSION['_expired']);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    /* ---------- flash messages (shown once) ---------- */

    public static function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    public static function pullFlash(string $key): ?string
    {
        $msg = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    /* ---------- CSRF ---------- */

    public static function csrfToken(): string
    {
        return $_SESSION['_csrf'] ?? '';
    }

    public static function verifyCsrf(?string $token): bool
    {
        return is_string($token) && !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
    }
}
