<?php
/** Admin — manage users (create instructor, activate/deactivate, reset password, delete). */
use App\Core\View;
use App\Core\Session;
?>
<div class="page-head">
  <div>
    <h1>Admin — Manage Users</h1>
    <p>Instructor accounts are created here (no public registration — business rule). Students self-register.</p>
  </div>
</div>

<div class="grid g2">
  <div class="card">
    <h3>Create instructor account</h3>
    <form method="post" action="<?= View::url('admin/createinstructor') ?>">
      <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
      <label class="lbl" style="margin-top:0">FULL NAME</label>
      <input class="box" type="text" name="name" placeholder="e.g. Tilahun Kebede" required>
      <label class="lbl">STAFF ID (login credential)</label>
      <input class="box" type="text" name="id_number" placeholder="e.g. STAFF018" required>
      <label class="lbl">PASSWORD (min 8 characters)</label>
      <input class="box" type="text" name="password" placeholder="e.g. Staff@123" required>
      <button class="btn btn-primary btn-sm" type="submit" style="margin-top:14px">＋ Create instructor</button>
    </form>
  </div>
  <div class="card">
    <h3>Find users</h3>
    <form method="get" action="<?= View::url('admin/users') ?>" class="toolbar" style="margin-bottom:0">
      <input class="box" type="text" name="search" placeholder="🔍 Name or ID…" value="<?= View::e((string) $search) ?>">
      <select class="box" name="role" onchange="this.form.submit()">
        <option value="">All roles</option>
        <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Student</option>
        <option value="instructor" <?= $role === 'instructor' ? 'selected' : '' ?>>Instructor</option>
        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
      <button class="btn btn-ghost btn-sm" type="submit">Search</button>
    </form>
    <p style="font-size:12px;color:var(--muted);margin-top:10px"><?= (int) $total ?> user(s) found · page <?= (int) $page ?> of <?= (int) $pages ?></p>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <div class="table-wrap">
    <table>
      <tr><th>ID</th><th>Name</th><th>Role</th><th>Status</th><th>Created</th><th>Last login</th><th>Actions</th></tr>
      <?php foreach ($rows as $u): ?>
        <tr>
          <td><?= View::e($u['id_number']) ?></td>
          <td><b><?= View::e($u['name']) ?></b><?= (int) $u['id'] === \App\Core\Auth::id() ? ' <span class="tag">you</span>' : '' ?></td>
          <td><span class="pill <?= $u['role'] === 'admin' ? 'pill-bad' : ($u['role'] === 'instructor' ? 'pill-warn' : 'pill-navy') ?>"><?= View::e(ucfirst((string) $u['role'])) ?></span></td>
          <td><?= $u['status'] === 'active' ? '<span class="pill pill-ok">Active</span>' : '<span class="pill pill-bad">Deactivated</span>' ?></td>
          <td><?= View::e(substr((string) $u['created_at'], 0, 10)) ?></td>
          <td><?= $u['last_login'] !== null ? View::e(substr((string) $u['last_login'], 0, 16)) : '—' ?></td>
          <td class="actions-cell">
            <?php if ((int) $u['id'] !== \App\Core\Auth::id() && $u['role'] !== 'admin'): ?>
              <form method="post" action="<?= View::url('admin/resetpassword/' . (int) $u['id']) ?>" style="display:inline">
                <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                <input type="password" name="password" placeholder="new pw" required style="width:110px;padding:6px 8px;border-radius:8px;border:1px solid rgba(255,255,255,.28);background:rgba(255,255,255,.1);color:#fff;font-size:12px">
                <button class="btn btn-ghost btn-sm" type="submit">Reset pw</button>
              </form>
              <?php if ($u['status'] === 'active'): ?>
                <form method="post" action="<?= View::url('admin/deactivate/' . (int) $u['id']) ?>" style="display:inline" data-confirm="Deactivate this account?">
                  <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                  <button class="btn btn-ghost btn-sm" type="submit">Deactivate</button>
                </form>
              <?php else: ?>
                <form method="post" action="<?= View::url('admin/activate/' . (int) $u['id']) ?>" style="display:inline">
                  <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                  <button class="btn btn-ghost btn-sm" type="submit">Activate</button>
                </form>
              <?php endif; ?>
              <form method="post" action="<?= View::url('admin/deleteuser/' . (int) $u['id']) ?>" style="display:inline" data-confirm="Permanently delete this user and ALL their attempt records?">
                <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            <?php else: ?>
              <span style="font-size:12px;color:var(--muted)">protected</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if ($rows === []): ?>
        <tr><td colspan="7" style="color:var(--muted)">No users match the filter.</td></tr>
      <?php endif; ?>
    </table>
  </div>
  <div class="pager">
    <?php $qs = ($role !== null && $role !== '' ? '&role=' . View::e($role) : '') . ($search !== null && $search !== '' ? '&search=' . urlencode($search) : ''); ?>
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <?php if ($p !== $page): ?>
        <a href="<?= View::url('') ?>?r=admin/users/<?= $p . $qs ?>"><?= $p ?></a>
      <?php else: ?><span><b><?= $p ?></b></span><?php endif; ?>
    <?php endfor; ?>
  </div>
</div>
