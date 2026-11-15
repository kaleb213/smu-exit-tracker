<?php
/** Login page (glass panel, matches approved preview). */
use App\Core\View;
?>
<div class="auth-wrap">
  <div class="auth-hero">
    <div class="crest-big"></div>
    <span class="badge"><?= View::e(strtoupper(APP_INSTITUTION)) ?> · FACULTY OF INFORMATICS</span>
    <h1>Web-Based Exit Exam<br>Preparation &amp; Progress Tracker</h1>
    <p>Practice exit exam questions by subject and topic, simulate the timed national exam,
       and track your readiness with data-driven analytics — built for SMU undergraduate students.</p>
    <div class="feat"><span>✦ Practice Mode</span><span>✦ Mock Exam Mode</span><span>✦ Readiness Metric</span><span>✦ Instructor Reports</span></div>
    <div class="auth-foot">Senior Project · Edel Wondimeneh, Fitsum Abrham, Kaleb Aklilu, Solomon Dawit · Advisor: Inst. Tilahun Kebede</div>
  </div>
  <div class="auth-panel">
    <div class="auth-top"><div class="crest-sm"></div><div><b>Exit Exam Prep</b><small>Senior Project System</small></div></div>
    <div class="eyebrow">EXISTING MEMBER</div>
    <h2>Welcome back!</h2>
    <div class="auth-sub">Sign in with your Student / Staff ID</div>

    <?php if (!empty($notice)): ?><div class="auth-notice"><?= View::e($notice) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="auth-errors"><?= View::e($error) ?></div><?php endif; ?>

    <form method="post" action="<?= View::url('auth/dologin') ?>">
      <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
      <div class="fld"><span class="ic">◈</span><input type="text" name="id_number" placeholder="Identification number · e.g. SMU/CS/1234" required autofocus></div>
      <div class="fld password-field"><span class="ic">🔒</span><input id="login-password" type="password" name="password" placeholder="Enter password" autocomplete="current-password" required><button class="password-toggle" type="button" data-password-toggle="login-password" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button></div>
      <div class="row-rem"><label><input type="checkbox" name="remember"> Remember me</label></div>
      <div class="dash-sep"></div>
      <button class="btn btn-white" type="submit">Sign in <span>⟶</span></button>
    </form>
    <div class="or">OR</div>
    <div class="center-link">Don't have an account? <a class="link" href="<?= View::url('auth/register') ?>">Register Now</a></div>
    <div class="hint"><b>Demo roles:</b> students register themselves · instructor accounts are created by the Admin
      (default: <b>STAFF018 / Staff@123</b>) · Admin: <b>ADMIN001 / Admin@123</b> · sessions expire after 30 minutes of inactivity.</div>
  </div>
</div>
