<?php
/**
 * Front controller — every HTTP request enters here.
 *
 * XAMPP usage: place the project in htdocs/senior-project and open
 *   http://localhost/senior-project/public/
 * Routes use PATH_INFO style URLs: index.php/controller/action/param
 */

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/app/config/config.php';

/* ---------- autoloader: App\Core\App -> app/core/App.php ----------
   Falls back to a case-insensitive file match so /mock resolves to
   app/controllers/MockExamController.php. */
spl_autoload_register(function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, 4)); // strip "App\"
    foreach (['Controllers' => 'controllers', 'Models' => 'models', 'Services' => 'services', 'Core' => 'core'] as $ns => $dir) {
        if (str_starts_with($relative, $ns . '/')) {
            $base = BASE_PATH . '/app/' . $dir . '/';
            $file = $base . substr($relative, strlen($ns) + 1) . '.php';
            if (is_file($file)) {
                require $file;
                return;
            }
            // case-insensitive match (e.g. Mockexam -> MockExam)
            foreach (glob($base . '*.php') ?: [] as $candidate) {
                if (strcasecmp(basename($candidate), basename($file)) === 0) {
                    require $candidate;
                    return;
                }
            }
        }
    }
});

/* ---------- run ---------- */
if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

$app = new App\Core\App();
$app->run();
