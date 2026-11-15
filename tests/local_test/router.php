<?php
/**
 * Router script for `php -S` used by the offline test profile.
 * Serves real asset files directly; everything else goes to index.php.
 *
 * Run (from project root):
 *   SMU_DB_DRIVER=sqlite php -S 127.0.0.1:8080 tests/local_test/router.php
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/../../public' . $path;

if ($path !== '/' && is_file($file)) {
    return false; // serve the static asset (css/js/images)
}

require __DIR__ . '/../../public/index.php';
