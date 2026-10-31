<?php $pageTitle = 'Admin Dashboard'; $activePage = 'admin'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<?php require __DIR__ . '/tabs.php'; ?>

<div class="admin-card admin-quick">
    <a class="btn btn-sm" href="<?php echo htmlspecialchars(admin_url('admin/question/0')); ?>">+ Add Question</a>
    <a class="btn btn-sm" href="<?php echo htmlspecialchars(admin_url('admin/subjects')); ?>">+ Manage Subjects</a>
    <a class="btn btn-sm" href="<?php echo htmlspecialchars(admin_url('admin/users')); ?>">+ Manage Users</a>
    <a class="btn btn-sm" href="<?php echo htmlspecialchars(admin_url('admin/settings')); ?>">Exam Settings</a>
</div>

<div class="admin-grid">
    <div class="admin-card center"><div class="admin-stat-num"><?php echo (int)$stats['students']; ?></div><div class="admin-stat-label">Students</div></div>
    <div class="admin-card center"><div class="admin-stat-num"><?php echo (int)$stats['instructors']; ?></div><div class="admin-stat-label">Instructors</div></div>
    <div class="admin-card center"><div class="admin-stat-num"><?php echo (int)$stats['subjects']; ?></div><div class="admin-stat-label">Subjects</div></div>
    <div class="admin-card center"><div class="admin-stat-num"><?php echo (int)$stats['topics']; ?></div><div class="admin-stat-label">Topics</div></div>
    <div class="admin-card center"><div class="admin-stat-num"><?php echo (int)$stats['questions']; ?></div><div class="admin-stat-label">Questions</div></div>
    <div class="admin-card center"><div class="admin-stat-num"><?php echo (int)$stats['attempts']; ?></div><div class="admin-stat-label">Attempts</div></div>
</div>

<div class="admin-card">
    <h3>Class Average: <?php echo $stats['class_avg'] !== null ? htmlspecialchars((string)$stats['class_avg']) . '%' : 'no attempts yet'; ?></h3>
</div>

<div class="admin-card">
    <h3>Latest Exam Attempts</h3>
    <table class="data-table">
        <thead><tr><th>#</th><th>Student</th><th>Mode</th><th>Score</th><th>Submitted</th></tr></thead>
        <tbody>
        <?php if (empty($recent)): ?>
            <tr><td colspan="5" class="muted">No exam attempts yet.</td></tr>
        <?php else: foreach ($recent as $r): ?>
            <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td><?php echo htmlspecialchars((string)($r['student_name'] ?? '')); ?></td>
                <td><?php echo htmlspecialchars(ucfirst((string)($r['mode'] ?? $r['exam_type'] ?? $r['type'] ?? 'exam'))); ?></td>
                <td><?php echo (isset($r['attempt_score']) && $r['attempt_score'] !== null) ? htmlspecialchars((string)round((float)$r['attempt_score'], 1)) . '%' : '-'; ?></td>
                <td><?php echo htmlspecialchars((string)($r['submitted_at'] ?? $r['created_at'] ?? '')); ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>