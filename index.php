<?php
/**
 * ============================================================
 *  index.php — Front controller (single entry point)
 *  Every request flows through here.
 *  Order matters: config → session → core classes → router
 * ============================================================
 */

// 1. Site configuration (constants)
require_once __DIR__ . '/config/config.php';

// 2. Database credentials
require_once __DIR__ . '/config/database.php';

// 3. Session handling (30-min timeout) — must load before any HTML
require_once __DIR__ . '/config/session_config.php';

// 4. Core MVC classes
require_once APP_PATH . '/core/Database.php';
require_once APP_PATH . '/core/Model.php';
require_once APP_PATH . '/core/Controller.php';
require_once APP_PATH . '/core/App.php';

// 5. Start the router
 $app = new App();