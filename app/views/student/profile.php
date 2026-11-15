<?php
/** Student profile — account details, activity history, password change, delete account. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>My Profile</h1>
    <p>Account activity history — practice attempts and performance records.</p>
  </div>
</div>

<?php if (!empty($saved)): ?><div class="flash ok"><?= View::e($saved) ?></div><?php endif; ?>
<?php $error = $error ?? \App\Core\Session::pullFlash('profileError'); ?>
<?php if (!empty($error)): ?><div class="flash error"><?= View::e($error) ?></div><?php endif; ?>

<div class="grid g2">
  <div class="card">
    <h3>Account details</h3>
    <form method="post" action="<?= View::url('progress/updateprofile') ?>">
      <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
      <label class="lbl">FULL NAME</label>
      <input class="box" type="text" name="name" value="<?= View::e($user['name']) ?>" required>
      <label class="lbl">STUDENT ID (login credential — cannot be changed)</label>
      <input class="box" type="text" value="<?= View::e($user['id_number']) ?>" disabled>
      <label class="lbl">ROLE</label>
      <p><span class="pill pill-navy">Student</span> <span style="font-size:12px;color:var(--muted)">registered <?= View::e(substr((string) $created, 0, 10)) ?></span></p>
      <button class="btn btn-ghost btn-sm" type="submit" style="margin-top:14px">Save changes</button>
    </form>

    <h3 style="margin-top:24px">Change password</h3>
    <form method="post" action="<?= View::url('progress/changepassword') ?>">
      <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
      <label class="lbl" style="margin-top:0">CURRENT PASSWORD</label>
      <input class="box" type="password" name="current_password" required>
      <label class="lbl">NEW PASSWORD (min 8 characters)</label>
      <input class="box" type="password" name="new_password" required>
      <label class="lbl">CONFIRM NEW PASSWORD</label>
      <input class="box" type="password" name="confirm_password" required>
      <button class="btn btn-ghost btn-sm" type="submit" style="margin-top:14px">Update password</button>
    </form>

    <h3 style="margin-top:24px">Danger zone</h3>
    <form method="post" action="<?= View::url('progress/deleteaccount') ?>" data-confirm="This permanently deletes your account and all recorded attempts. Continue?">
      <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
      <label class="lbl" style="margin-top:0">CONFIRM WITH PASSWORD TO DELETE ACCOUNT</label>
      <input class="box" type="password" name="password" required>
      <button class="btn btn-danger btn-sm" type="submit" style="margin-top:14px">Delete my account</button>
    </form>
  </div>

  <div class="card">
    <h3>Recent activity</h3>
    <div class="table-wrap">
      <table>
        <tr><th>Date</th><th>Activity</th><th>Score</th><th></th></tr>
        <?php foreach ($recent as $r): ?>
          <tr>
            <td><?= View::e(substr((string) $r['submitted_at'], 0, 16)) ?></td>
            <td><?= View::e(ucfirst((string) $r['mode'])) ?> ·
              <?= $r['subject_name'] !== null ? View::e($r['subject_name']) : 'All subjects' ?>
              <?= $r['topic_name'] !== null ? ' › ' . View::e($r['topic_name']) : '' ?></td>
            <td><?php
              $pct = (float) $r['score_percent'];
              $cls = $pct >= 75 ? 'pill-ok' : ($pct >= 50 ? 'pill-warn' : 'pill-bad');
              echo '<span class="pill ' . $cls . '">' . (int) round($pct) . '%</span>';
            ?></td>
            <td><a class="a-edit" href="<?= View::url('progress/results/' . (int) $r['id']) ?>">Review</a></td>
          </tr>
        <?php endforeach; ?>
        <?php if ($recent === []): ?>
          <tr><td colspan="4" style="color:var(--muted)">No attempts recorded yet.</td></tr>
        <?php endif; ?>
      </table>
    </div>
    <p style="margin-top:14px;font-size:12.5px;color:var(--muted)">
      The full history is on the <a class="link" href="<?= View::url('progress/history') ?>">Results &amp; History</a> page.
    </p>
  </div>
</div>
