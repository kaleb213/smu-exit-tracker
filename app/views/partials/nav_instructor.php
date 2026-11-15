<?php /** Instructor navigation. */
use App\Core\View;
$nav = [
    ['dashboard', 'reporting/dashboard', '📊', 'Performance Dashboard'],
    ['students', 'reporting/students', '👥', 'Student Performance'],
    ['topics', 'reporting/topics', '⚠', 'Weak Topics'],
    ['questions', 'reporting/questions', '✅', 'Question Review'],
    ['export', 'reporting/export', '⬇', 'Export CSV'],
    ['profile', 'profile/index', '👤', 'My Profile'],
];
?>
<div class="side-label">INSTRUCTOR</div>
<?php foreach ($nav as [$key, $route, $ic, $label]): ?>
  <a class="nav-item <?= $active === $key ? 'active' : '' ?>" href="<?= View::url($route) ?>">
    <span class="ic"><?= $ic ?></span> <?= $label ?>
  </a>
<?php endforeach; ?>
