<?php $pageTitle = 'Manage Subjects'; $activePage = 'admin'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<?php require __DIR__ . '/tabs.php'; ?>

<div class="admin-two-col">
    <div class="admin-card">
        <h3><?php echo $edit ? 'Edit Subject' : 'Add Subject'; ?></h3>
        <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/subjects')); ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo (int)($edit['id'] ?? 0); ?>">
            <label>Subject name<input type="text" name="name" required value="<?php echo htmlspecialchars((string)($edit['display_name'] ?? '')); ?>"></label>
            <button type="submit" class="btn btn-primary"><?php echo $edit ? 'Update Subject' : 'Add Subject'; ?></button>
            <?php if ($edit): ?><a class="btn" href="<?php echo htmlspecialchars(admin_url('admin/subjects')); ?>">Cancel</a><?php endif; ?>
        </form>
    </div>

    <div class="admin-card">
        <h3>All Subjects (<?php echo count($rows); ?>)</h3>
        <table class="data-table">
            <thead><tr><th>ID</th><th>Subject</th><th>Topics</th><th></th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="4" class="muted">No subjects yet — add your first one.</td></tr>
            <?php else: foreach ($rows as $s): ?>
                <tr>
                    <td><?php echo (int)$s['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$s['display_name']); ?></td>
                    <td><?php echo (int)$s['topic_count']; ?></td>
                    <td class="row-actions">
                        <a class="btn btn-sm" href="<?php echo htmlspecialchars(admin_url('admin/subjects?edit=' . (int)$s['id'])); ?>">Edit</a>
                        <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/subjects')); ?>" onsubmit="return confirm('Delete this subject?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$s['id']; ?>">
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>