<?php $pageTitle = 'Manage Users'; $activePage = 'admin'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<?php require __DIR__ . '/tabs.php'; ?>

<div class="admin-toolbar">
    <form method="get" action="<?php echo htmlspecialchars(admin_url('admin/users')); ?>" class="admin-filter">
        <select name="role">
            <option value="">All roles</option>
            <option value="student" <?php echo $filterRole === 'student' ? 'selected' : ''; ?>>Students</option>
            <option value="instructor" <?php echo $filterRole === 'instructor' ? 'selected' : ''; ?>>Instructors</option>
            <option value="admin" <?php echo $filterRole === 'admin' ? 'selected' : ''; ?>>Admins</option>
        </select>
        <input type="text" name="q" placeholder="Search ID number or name..." value="<?php echo htmlspecialchars($filterQ); ?>">
        <button type="submit" class="btn btn-sm">Filter</button>
    </form>
</div>

<div class="admin-two-col">
    <div class="admin-card">
        <h3>Add New User</h3>
        <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/users')); ?>">
            <input type="hidden" name="action" value="create">
            <label>ID Number<input type="text" name="id_number" required placeholder="e.g. SMU/001/2016"></label>
            <?php if (in_array('first_name', $nameCols, true) && in_array('last_name', $nameCols, true)): ?>
                <label>First name<input type="text" name="first_name"></label>
                <label>Last name<input type="text" name="last_name"></label>
            <?php elseif (in_array('full_name', $nameCols, true)): ?>
                <label>Full name<input type="text" name="full_name"></label>
            <?php elseif (in_array('name', $nameCols, true)): ?>
                <label>Name<input type="text" name="name"></label>
            <?php endif; ?>
            <label>Role
                <select name="role">
                    <option value="student">Student</option>
                    <option value="instructor">Instructor</option>
                    <option value="admin">Admin</option>
                </select>
            </label>
            <label>Password<input type="text" name="password" required minlength="4" placeholder="Set an initial password"></label>
            <button type="submit" class="btn btn-primary">Create User</button>
        </form>
    </div>

    <div class="admin-card">
        <h3>All Users (<?php echo count($rows); ?>)</h3>
        <table class="data-table">
            <thead><tr><th>ID</th><th>ID Number</th><th>Name</th><th>Role</th><th>Status</th><th>Attempts</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $u): ?>
                <tr>
                    <td><?php echo (int)$u['id']; ?></td>
                    <td><?php echo htmlspecialchars((string)$u['id_number']); ?></td>
                    <td><?php echo htmlspecialchars((string)($u['display_name'] ?? '')); ?></td>
                    <td><span class="badge badge-<?php echo htmlspecialchars((string)$u['role']); ?>"><?php echo htmlspecialchars(ucfirst((string)$u['role'])); ?></span></td>
                    <td><?php
                        $act = $u['is_active'] ?? $u['active'] ?? $u['status'] ?? 1;
                        $on = in_array(strtolower((string)$act), ['1', 'yes', 'active', 'true'], true);
                        echo $on ? '<span class="badge badge-on">Active</span>' : '<span class="badge badge-off">Inactive</span>';
                    ?></td>
                    <td><?php echo (int)($u['attempt_count'] ?? 0); ?></td>
                    <td><a class="btn btn-sm" href="<?php echo htmlspecialchars(admin_url('admin/user/' . (int)$u['id'])); ?>">Manage</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>