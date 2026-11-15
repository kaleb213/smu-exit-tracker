<?php
/**
 * Layout header — opens the page. Two modes:
 *   $bare = true  → auth/error pages (no app shell)
 *   otherwise     → topbar + role sidebar + <main>
 *
 * Expected data (optional): 'title', 'active' (nav key), 'bare'.
 */
use App\Core\Auth;
use App\Core\Session;
use App\Core\View;

$bare   = $data['bare'] ?? $bare ?? false;
$active = $data['active'] ?? $active ?? '';
$title  = $data['title'] ?? $title ?? 'Dashboard';

/*
 * Resolve the selected sidebar item from the current route when a
 * controller has not explicitly supplied an active key. This keeps
 * navigation highlighting consistent across every page and role.
 */
if ($active === '') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $routePath = '';
    if (($routePos = strpos($requestPath, '/index.php')) !== false) {
        $routePath = trim(substr($requestPath, $routePos + strlen('/index.php')), '/');
    } else {
        $routePath = trim($requestPath, '/');
        $scriptDir = trim(str_replace('\\\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        if ($scriptDir !== '' && str_starts_with($routePath, $scriptDir . '/')) {
            $routePath = substr($routePath, strlen($scriptDir) + 1);
        }
    }
    $segments = array_values(array_filter(explode('/', strtolower($routePath))));
    $controller = $segments[0] ?? '';
    $action = $segments[1] ?? 'index';

    $active = match ($controller) {
        'admin' => match ($action) {
            'dashboard' => 'dashboard',
            'users', 'createinstructor', 'deactivate', 'activate', 'resetpassword', 'deleteuser' => 'users',
            'subjects', 'storesubject', 'updatesubject', 'deletesubject' => 'subjects',
            'topics', 'storetopic', 'updatetopic', 'deletetopic' => 'topics',
            'config', 'examconfig', 'settings' => 'config',
            default => '',
        },
        'questionbank' => 'questions',
        'reporting' => match ($action) {
            'dashboard' => 'dashboard',
            'students' => 'students',
            'topics' => 'topics',
            'questions' => 'questions',
            'export' => 'export',
            default => '',
        },
        'progress' => match ($action) {
            'dashboard' => 'dashboard',
            'history', 'results' => 'results',
            'index' => 'progress',
            default => '',
        },
        'practice' => 'practice',
        'mock', 'mockexam' => 'mock',
        'profile' => 'profile',
        default => '',
    };
}

$flashes = [];
if (class_exists(Session::class)) {
    $ok = Session::pullFlash('ok');    if ($ok)    { $flashes[] = ['ok', $ok]; }
    $er = Session::pullFlash('error'); if ($er)    { $flashes[] = ['error', $er]; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= View::e($title) ?> · <?= View::e(APP_NAME) ?></title>
<link rel="stylesheet" href="<?= View::asset('css/style.css') ?>">
<link rel="stylesheet" href="<?= View::asset('css/responsive.css') ?>">
</head>
<body data-baseurl="<?= View::e(View::base()) ?>"
      data-csrf="<?= View::e(Session::csrfToken()) ?>"
      data-presence-url="<?= View::e(View::url('profile/presence')) ?>"
      data-presence-enabled="<?= Auth::check() ? '1' : '0' ?>">
<div class="orb orb1"></div><div class="orb orb2"></div>

<?php if ($bare): ?>
<button type="button" class="theme-btn float" id="theme-toggle" title="Toggle light / dark mode">☀️</button>
<div class="screen active">
<?php else: ?>
<div class="shell">
  <div class="topbar">
    <a class="brand" href="<?= View::url('home') ?>">
      <div class="crest-sm"></div> <?= View::e(APP_NAME) ?>
    </a>
    <div class="user">
      <button type="button" class="theme-btn" id="theme-toggle" title="Toggle light / dark mode" aria-label="Toggle light and dark theme">☀️</button>
      <div class="user-identity">
        <span class="user-name"><?= View::e(Auth::user()['name'] ?? '') ?></span>
        <span class="user-id"><?= View::e(Auth::user()['id_number'] ?? '') ?></span>
      </div>
      <a class="avatar avatar-link" href="<?= View::url('profile/index') ?>" title="Open my profile" aria-label="Open my profile">
        <?php if (!empty(Auth::user()['profile_photo'])): ?>
          <img src="<?= View::e(View::base() . '/' . ltrim((string) Auth::user()['profile_photo'], '/')) ?>" alt="" aria-hidden="true" onerror="this.remove(); this.parentElement.classList.add('avatar-photo-failed');">
        <?php else: ?>
          <?= View::e(mb_strtoupper(mb_substr(trim(Auth::user()['name'] ?? 'U'), 0, 1))) ?>
        <?php endif; ?>
        <span class="avatar-status" data-presence-dot data-status="online" aria-label="Online" title="Online"></span>
      </a>
      <a class="logout-btn" href="<?= View::url('auth/logout') ?>" title="Sign out" aria-label="Sign out">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M10 17l5-5-5-5M15 12H3m9-8h6a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6"/></svg>
        <span>Sign out</span>
      </a>
    </div>
  </div>
  <aside class="sidebar">
  <?php
    $role = Auth::role();
    if ($role === 'instructor') { include BASE_PATH . '/app/views/partials/nav_instructor.php'; }
    elseif ($role === 'admin') { include BASE_PATH . '/app/views/partials/nav_admin.php'; }
    else { include BASE_PATH . '/app/views/partials/nav_student.php'; }
  ?>
  </aside>
  <div class="main">
<?php endif; ?>

<?php foreach ($flashes as [$kind, $msg]): ?>
  <div class="flash <?= View::e($kind) ?>"><?= View::e($msg) ?></div>
<?php endforeach; ?>
