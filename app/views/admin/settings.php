<?php $pageTitle = 'Exam Settings'; $activePage = 'admin'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<?php require __DIR__ . '/tabs.php'; ?>

<div class="admin-card admin-narrow">
    <h3>Mock Exam Settings</h3>
    <p class="muted">These values drive the timed mock exam (timer + number of randomized questions). The MockController reads them from the database and falls back to its built-in constants (30 min / 50 questions) if a value is missing.</p>
    <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/settings')); ?>">
        <label>Mock exam duration (minutes, 5-180)
            <input type="number" name="mock_time_limit" min="5" max="180" value="<?php echo htmlspecialchars((string)$timeLimit); ?>" required>
        </label>
        <label>Questions per mock exam (5-100)
            <input type="number" name="mock_max_questions" min="5" max="100" value="<?php echo htmlspecialchars((string)$maxQuestions); ?>" required>
        </label>
        <button type="submit" class="btn btn-primary">Save Settings</button>
    </form>
</div>

<div class="admin-card admin-narrow">
    <h3>All Settings In Database</h3>
    <table class="data-table">
        <thead><tr><th>Key</th><th>Value</th><th>Last Updated</th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="3" class="muted">No settings rows yet.</td></tr>
        <?php else: foreach ($rows as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars((string)$s['setting_key']); ?></td>
                <td><?php echo htmlspecialchars((string)$s['setting_value']); ?></td>
                <td><?php echo htmlspecialchars((string)($s['updated_at'] ?? '-')); ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>