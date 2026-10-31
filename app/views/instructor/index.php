<?php $pageTitle  = 'Instructor Reports'; $activePage = 'instructor'; require APP_PATH . '/views/layouts/header.php'; ?>
<?php $active = 'reports'; require APP_PATH . '/views/instructor/tabs.php'; ?>

<h1>Instructor Reports</h1>
<p class="muted">Class-wide performance for the exit exam preparation.</p>

<?php if ((int)$overview['total_attempts'] === 0): ?>

    <div class="alert alert-info">
        No exam attempts recorded yet. Reports will appear as soon as students
        complete practice quizzes or mock exams.
    </div>

<?php else: ?>

    <div class="stat-grid" style="margin:20px 0;">
        <div class="card" style="padding:16px;">
            <h2 style="margin:0;"><?php echo (int)$overview['total_students']; ?></h2>
            <p class="muted" style="margin:4px 0 0;">Students</p>
        </div>
        <div class="card" style="padding:16px;">
            <h2 style="margin:0;"><?php echo (int)$overview['total_attempts']; ?></h2>
            <p class="muted" style="margin:4px 0 0;">Total attempts</p>
        </div>
        <div class="card" style="padding:16px;">
            <h2 style="margin:0;"><?php echo round($overview['class_avg']); ?>%</h2>
            <p class="muted" style="margin:4px 0 0;">Class average</p>
        </div>
        <div class="card" style="padding:16px;">
            <h2 style="margin:0;"><?php echo round($overview['mock_avg']); ?>%</h2>
            <p class="muted" style="margin:4px 0 0;">Mock exam average</p>
        </div>
    </div>

    <div class="card" style="padding:16px;margin:20px 0;">
        <h2 style="margin-top:0;">Average by subject</h2>
        <table class="data-table">
            <thead><tr><th>Subject</th><th>Attempts</th><th>Average %</th><th>Best %</th></tr></thead>
            <tbody>
            <?php foreach ($subjects as $s): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['name']); ?></td>
                    <td><?php echo (int)$s['attempts']; ?></td>
                    <td><?php echo $s['avg_percentage']; ?>%</td>
                    <td><?php echo round($s['best_percentage']); ?>%</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card" style="padding:16px;margin:20px 0;">
        <h2 style="margin-top:0;">Weakest topics (class-wide)</h2>
        <?php if (!$weak): ?>
            <p class="muted">No topic-level data yet — students build this by taking practice quizzes.</p>
        <?php else: ?>
            <?php foreach ($weak as $w): ?>
                <div class="topic-row">
                    <span><?php echo htmlspecialchars($w['topic_name']); ?>
                        <span class="muted">(<?php echo htmlspecialchars($w['subject_name']); ?>)</span>
                    </span>
                    <span><?php echo $w['avg_percentage']; ?>% — <?php echo (int)$w['attempts']; ?> attempt(s)</span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card" style="padding:16px;margin:20px 0;">
        <h2 style="margin-top:0;">Students</h2>
        <table class="data-table">
            <thead><tr><th>Student</th><th>ID</th><th>Attempts</th><th>Avg %</th><th>Best %</th><th>Last attempt</th></tr></thead>
            <tbody>
            <?php foreach ($students as $st): ?>
                <tr>
                    <td>
                        <a href="<?php echo BASE_URL; ?>/instructor/student/<?php echo (int)$st['id']; ?>">
                            <?php echo htmlspecialchars($st['student_name'] ?? ''); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($st['id_number'] ?? ''); ?></td>
                    <td><?php echo (int)$st['attempts']; ?></td>
                    <td><?php echo $st['avg_percentage']; ?>%</td>
                    <td><?php echo round($st['best_percentage']); ?>%</td>
                    <td><?php echo $st['last_attempt'] ? date('M j, Y H:i', strtotime($st['last_attempt'])) : '—'; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div style="display:flex;gap:8px;margin:20px 0;flex-wrap:wrap;">
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/instructor/trends">View trends charts →</a>
        <a class="btn" href="<?php echo BASE_URL; ?>/instructor/export">⬇ Export CSV</a>
    </div>

<?php endif; ?>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>