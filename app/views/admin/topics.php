<?php $pageTitle = 'Manage Topics'; $activePage = 'admin'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<?php require __DIR__ . '/tabs.php'; ?>

<div class="admin-toolbar">
    <form method="get" action="<?php echo htmlspecialchars(admin_url('admin/topics')); ?>" class="admin-filter">
        <select name="subject">
            <option value="">All subjects</option>
            <?php foreach ($subjects as $s): ?>
                <option value="<?php echo (int)$s['id']; ?>" <?php echo $filterSubject === (int)$s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$s['label']); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-sm">Filter</button>
    </form>
</div>

<div class="admin-two-col">
    <div class="admin-card">
        <h3><?php echo $edit ? 'Edit Topic' : 'Add Topic'; ?></h3>
        <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/topics')); ?>">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?php echo (int)($edit['id'] ?? 0); ?>">
            <label>Topic name<input type="text" name="name" required value="<?php echo htmlspecialchars((string)($edit['display_name'] ?? '')); ?>"></label>
            <label>Subject
                <select name="subject_id" required>
                    <option value="">- choose -</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?php echo (int)$s['id']; ?>" <?php echo (isset($edit) && $edit && (int)($edit['subject_id'] ?? 0) === (int)$s['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$s['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit" class="btn btn-primary"><?php echo $edit ? 'Update Topic' : 'Add Topic'; ?></button>
            <?php if ($edit): ?><a class="btn" href="<?php echo htmlspecialchars(admin_url('admin/topics')); ?>">Cancel</a><?php endif; ?>
        </form>
    </div>

    <div class="admin-card">
        <h3>All Topics (<?php echo count($rows); ?>)</h3>
        <table class="data-table">
            <thead><tr><th>ID</th><th>Topic</th><th>Subject</th><th></th></tr></thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr><td colspan="4" class="muted">No topics found.</td></tr>
            <?php else: foreach ($rows as $t): ?>
                <tr>
                    <td><?php echo (int)$t['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$t['display_name']); ?></td>
                    <td><?php echo htmlspecialchars((string)($t['subject_name'] ?? '-')); ?></td>
                    <td class="row-actions">
                        <a class="btn btn-sm" href="<?php echo htmlspecialchars(admin_url('admin/topics?edit=' . (int)$t['id'])); ?>">Edit</a>
                        <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/topics')); ?>" onsubmit="return confirm('Delete this topic?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
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