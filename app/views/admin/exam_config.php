<?php
/** Admin — exam configuration (settings table). */
use App\Core\View;
use App\Core\Session;
?>
<div class="page-head">
  <div>
    <h1>Exam Configuration</h1>
    <p>Admin-controlled exam engine settings — applied to every new session.</p>
  </div>
  <?php if (!empty($saved)): ?><span class="pill pill-ok" style="font-size:12px"><?= View::e($saved) ?></span><?php endif; ?>
</div>

<div class="note">
  <b>Business rules enforced by these settings:</b> Practice Mode is untimed with instant feedback;
  Mock Exam Mode replicates the timed national exam (default 50 questions / 60 minutes); sessions expire
  automatically after the configured inactivity timeout (security NFR: 30 minutes on shared computers).
</div>

<div class="card" style="max-width:640px">
  <form id="config-form" method="post" action="<?= View::url('admin/saveconfig') ?>">
    <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
    <div class="grid g2" style="gap:14px">
      <div>
        <label class="lbl">MOCK EXAM — NUMBER OF QUESTIONS (1–200)</label>
        <input class="box" type="number" name="mock_question_count" min="1" max="200" value="<?= (int) $config['mock_question_count'] ?>">
      </div>
      <div>
        <label class="lbl">MOCK EXAM — TIME LIMIT, MINUTES (1–600)</label>
        <input class="box" type="number" name="mock_time_limit_minutes" min="1" max="600" value="<?= (int) $config['mock_time_limit_minutes'] ?>">
      </div>
      <div>
        <label class="lbl">PRACTICE — QUESTIONS PER SESSION (1–100)</label>
        <input class="box" type="number" name="practice_question_count" min="1" max="100" value="<?= (int) $config['practice_question_count'] ?>">
      </div>
      <div>
        <label class="lbl">SESSION TIMEOUT — MINUTES OF INACTIVITY (1–240)</label>
        <input class="box" type="number" name="session_timeout_minutes" min="1" max="240" value="<?= (int) $config['session_timeout_minutes'] ?>">
      </div>
      <div>
        <label class="lbl">DEFAULT READINESS TARGET, % (0–100)</label>
        <input class="box" type="number" name="readiness_target" min="0" max="100" value="<?= (int) $config['readiness_target'] ?>">
      </div>
    </div>
    <button class="btn btn-white btn-sm" type="submit" style="margin-top:18px">Save configuration ⟶</button>
  </form>
</div>

<div class="card" style="margin-top:16px;max-width:640px">
  <h3>Reminder — account provisioning rules</h3>
  <table>
    <tr><td>Students</td><td>self-register with Student ID + password</td></tr>
    <tr><td>Instructors</td><td>created only by the Admin (Manage Users)</td></tr>
    <tr><td>Admin</td><td>seeded during installation (ADMIN001)</td></tr>
  </table>
</div>
