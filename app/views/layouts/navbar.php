<?php
/* Role-aware navigation: students, instructors and admins see their own links. */
 $navRole = strtolower($_SESSION['role'] ?? ($_SESSION['user_role'] ?? ''));
?>
<nav class="mainnav">
<?php if ($navRole === 'instructor'): ?>
    <a href="<?php echo BASE_URL; ?>/instructor"
       class="<?php echo $activePage === 'instructor' ? 'active' : ''; ?>">Reports</a>
    <a href="<?php echo BASE_URL; ?>/instructor/trends"
       class="<?php echo $activePage === 'trends' ? 'active' : ''; ?>">Trends</a>
<?php elseif ($navRole === 'admin'): ?>
    <a href="<?php echo BASE_URL; ?>/admin"
       class="<?php echo $activePage === 'admin' ? 'active' : ''; ?>">Admin</a>
<?php else: ?>
    <a href="<?php echo BASE_URL; ?>/student"
       class="<?php echo $activePage === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
    <a href="<?php echo BASE_URL; ?>/practice"
       class="<?php echo $activePage === 'practice' ? 'active' : ''; ?>">Practice</a>
    <a href="<?php echo BASE_URL; ?>/mock"
       class="<?php echo $activePage === 'mock' ? 'active' : ''; ?>">Mock Exam</a>
    <a href="<?php echo BASE_URL; ?>/progress"
       class="<?php echo $activePage === 'progress' ? 'active' : ''; ?>">Progress</a>
<?php endif; ?>
</nav>