<?php /** Student navigation. $active = current nav key. */
use App\Core\View;
$nav = [
    ['dashboard', 'progress/dashboard', '◉', 'Dashboard'],
    ['practice', 'practice/index', '✎', 'Practice Mode'],
    ['mock', 'mock/index', '⏱', 'Mock Exam Mode'],
    ['results', 'progress/history', '☑', 'Results &amp; History'],
    ['progress', 'progress/index', '📈', 'My Progress'],
    ['profile', 'profile/index', '👤', 'My Profile'],
];
?>
<div class="side-label">STUDENT</div>
<?php foreach ($nav as [$key, $route, $ic, $label]): ?>
  <a class="nav-item <?= $active === $key ? 'active' : '' ?>" href="<?= View::url($route) ?>">
    <span class="ic"><?= $ic ?></span> <?= $label ?>
  </a>
<?php endforeach; ?>
