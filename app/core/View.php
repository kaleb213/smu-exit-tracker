<?php

declare(strict_types=1);

namespace App\Core;

/**
 * View layer — renders PHP templates inside the shared layout
 * and provides URL/escaping helpers used by every template.
 */
final class View
{
    /** Directory that contains index.php, e.g. '' or '/senior-project/public'. */
    private static string $base = '';

    public static function setBase(string $base): void
    {
        self::$base = rtrim($base, '/');
    }

    /** Directory that contains index.php (used by client-side JS). */
    public static function base(): string
    {
        return self::$base;
    }

    /** Full page render: layout wraps [partials/header, view, partials/footer]. */
    public static function render(string $view, array $data = []): void
    {
        echo self::partial('partials/header', $data);
        echo self::partial($view, $data);
        echo self::partial('partials/footer', $data);
    }

    /** Render a single template file and return its output. */
    public static function partial(string $view, array $data = []): string
    {
        $file = BASE_PATH . '/app/views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException('View not found: ' . $view);
        }
        extract($data, EXTR_SKIP);
        ob_start();
        include $file;
        return (string) ob_get_clean();
    }

    /** Build a URL to a route: {base}/index.php/route */
    public static function url(string $route = ''): string
    {
        $route = trim($route, '/');
        return self::$base . '/index.php' . ($route !== '' ? '/' . $route : '');
    }

    /** URL to a static asset: {base}/assets/... */
    public static function asset(string $path): string
    {
        return self::$base . '/assets/' . ltrim($path, '/');
    }

    /** HTML-escape helper (XSS protection for all output). */
    public static function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
