<?php /** Administrator navigation. */
use App\Core\View;
$nav = [
    ['dashboard', 'admin/dashboard', '🛡', 'Dashboard'],
    ['users', 'admin/users', '👥', 'Manage Users'],
    ['subjects', 'admin/subjects', '🗂', 'Subjects'],
    ['topics', 'admin/topics', '📚', 'Topics'],
    ['questions', 'questionbank/index', '📝', 'Question Bank'],
    ['config', 'admin/config', '⚙', 'Exam Configuration'],
    ['profile', 'profile/index', '👤', 'My Profile'],
];
?>
<div class="side-label">ADMINISTRATOR</div>
<?php foreach ($nav as [$key, $route, $ic, $label]): ?>
  <a class="nav-item <?= $active === $key ? 'active' : '' ?>" href="<?= View::url($route) ?>">
    <span class="ic"><?= $ic ?></span> <?= $label ?>
  </a>
<?php endforeach; ?>
