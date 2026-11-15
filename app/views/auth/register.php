<?php
/** Student self-registration (business rule: only students self-register). */
use App\Core\View;
?>
<div class="auth-wrap">
  <div class="auth-hero">
    <div class="crest-big"></div>
    <span class="badge">STUDENT SELF-REGISTRATION</span>
    <h1>Create your<br>student account</h1>
    <p>Registration requires only your full name, a valid Student ID and a password.
       Your Student ID is your unique login credential — duplicate IDs are rejected by the system.</p>
    <div class="feat"><span>✦ Instant account</span><span>✦ Free for SMU students</span></div>
    <div class="auth-foot"><?= View::e(APP_INSTITUTION) ?> · <?= View::e(APP_DEPARTMENT) ?></div>
  </div>
  <div class="auth-panel">
    <div class="auth-top"><div class="crest-sm"></div><div><b>Exit Exam Prep</b><small>Student Registration</small></div></div>
    <div class="eyebrow">NEW MEMBER</div>
    <h2>Register</h2>
    <div class="auth-sub">Account is created instantly</div>

    <div id="register-errors" style="display:none"></div>
    <?php if (!empty($errors)): ?>
      <div class="auth-errors">
        <?php foreach ($errors as $er): ?>• <?= View::e($er) ?><br><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form id="register-form" method="post" action="<?= View::url('auth/doregister') ?>">
      <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
      <div class="fld"><span class="ic">◉</span><input type="text" name="name" placeholder="Full name · e.g. Kaleb Aklilu"
        value="<?= View::e($old['name'] ?? '') ?>" required></div>
      <div class="fld"><span class="ic">◈</span><input type="text" name="id_number" placeholder="Student ID · e.g. SMU/CS/1234"
        value="<?= View::e($old['id_number'] ?? '') ?>" required></div>
      <div class="fld password-field"><span class="ic">🔒</span><input id="register-password" type="password" name="password" placeholder="Password · min 8 characters" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle="register-password" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button></div>
      <div class="fld password-field"><span class="ic">🔒</span><input id="register-password-confirm" type="password" name="password_confirm" placeholder="Confirm password" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle="register-password-confirm" aria-label="Show password" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></button></div>
      <div class="dash-sep"></div>
      <button class="btn btn-white" type="submit">Create account <span>⟶</span></button>
    </form>
    <div class="or">OR</div>
    <div class="center-link">Already registered? <a class="link" href="<?= View::url('auth/login') ?>">Sign in</a></div>
  </div>
</div>
