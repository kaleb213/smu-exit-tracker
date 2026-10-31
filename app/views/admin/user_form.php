<?php
 $pageTitle = $user ? 'Edit User' : 'Add User'; $activePage = 'admin';
require APP_PATH . '/views/layouts/header.php';
 $active = 'users'; require APP_PATH . '/views/admin/tabs.php';
?>
<h1><?php echo $user ? 'Edit User' : 'Add User'; ?></h1>

<div class="card" style="padding:20px;margin:16px 0;max-width:560px;">
    <form method="post" action="<?php echo BASE_URL; ?>/admin/userSave">
        <input type="hidden" name="id" value="<?php echo (int)($user['id'] ?? 0); ?>">

        <div class="form-group">
            <label>Full name</label>
            <input type="text" name="full_name" required
                   value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label>ID number</label>
            <input type="text" name="id_number" required
                   value="<?php echo htmlspecialchars($user['id_number'] ?? ''); ?>">
        </div>

        <div class="form-group">
            <label>Role</label>
            <select name="role">
                <?php
                    $cur = strtolower($user['role'] ?? 'student');
                    foreach (['student', 'instructor', 'admin'] as $r) {
                        echo '<option value="' . $r . '"' . ($cur === $r ? ' selected' : '') . '>' . ucfirst($r) . '</option>';
                    }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label>Password <?php echo $user ? '(leave blank to keep current)' : ''; ?></label>
            <input type="password" name="password" <?php echo $user ? '' : 'required'; ?>>
        </div>

        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary">Save user</button>
            <a class="btn" href="<?php echo BASE_URL; ?>/admin/users">Cancel</a>
        </div>
    </form>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>