<?php $pageTitle = 'Edit User'; $activePage = 'admin'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<?php require __DIR__ . '/tabs.php'; ?>

<div class="admin-card admin-narrow">
    <h3>Manage: <?php echo htmlspecialchars((string)($user['display_name'] ?: $user['id_number'])); ?>
        <span class="badge badge-<?php echo htmlspecialchars((string)$user['role']); ?>"><?php echo htmlspecialchars((string)$user['role']); ?></span>
    </h3>

    <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/user/' . (int)$user['id'])); ?>">
        <input type="hidden" name="action" value="update">
        <label>ID Number<input type="text" name="id_number" value="<?php echo htmlspecialchars((string)$user['id_number']); ?>" required></label>
        <?php if (in_array('first_name', $nameCols, true) && in_array('last_name', $nameCols, true)): ?>
            <label>First name<input type="text" name="first_name" value="<?php echo htmlspecialchars((string)($user['first_name'] ?? '')); ?>"></label>
            <label>Last name<input type="text" name="last_name" value="<?php echo htmlspecialchars((string)($user['last_name'] ?? '')); ?>"></label>
        <?php elseif (in_array('full_name', $nameCols, true)): ?>
            <label>Full name<input type="text" name="full_name" value="<?php echo htmlspecialchars((string)($user['full_name'] ?? '')); ?>"></label>
        <?php elseif (in_array('name', $nameCols, true)): ?>
            <label>Name<input type="text" name="name" value="<?php echo htmlspecialchars((string)($user['name'] ?? '')); ?>"></label>
        <?php endif; ?>
        <label>Role
            <select name="role">
                <?php foreach (['student', 'instructor', 'admin'] as $r): ?>
                    <option value="<?php echo $r; ?>" <?php echo strtolower((string)$user['role']) === $r ? 'selected' : ''; ?>><?php echo ucfirst($r); ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>

    <hr>
    <h4>Reset Password</h4>
    <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/user/' . (int)$user['id'])); ?>">
        <input type="hidden" name="action" value="resetpass">
        <label>New password<input type="text" name="new_password" required minlength="4" placeholder="Type a new password"></label>
        <button type="submit" class="btn">Reset Password</button>
    </form>

    <hr>
    <h4>Danger Zone</h4>
    <?php
    $act = $user['is_active'] ?? $user['active'] ?? $user['status'] ?? 1;
    $on = in_array(strtolower((string)$act), ['1', 'yes', 'active', 'true'], true);
    ?>
    <div class="row-actions">
        <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/user/' . (int)$user['id'])); ?>" onsubmit="return confirm('Change this account status?');">
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="to" value="<?php echo $on ? 'off' : 'on'; ?>">
            <button type="submit" class="btn"><?php echo $on ? 'Deactivate Account' : 'Activate Account'; ?></button>
        </form>
        <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/user/' . (int)$user['id'])); ?>" onsubmit="return confirm('Permanently delete this user? This cannot be undone.');">
            <input type="hidden" name="action" value="delete">
            <button type="submit" class="btn btn-danger">Delete User</button>
        </form>
    </div>
    <p class="muted">Deactivate keeps their exam history; delete only works for users with no attempts.</p>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>