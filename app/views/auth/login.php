<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login &mdash; <?php echo APP_NAME; ?></title>
<link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>/css/auth.css">
<link rel="icon" type="image/png" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/img/logo.png">
<link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/css/form-ui.css">
<link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/css/polish.css">
</head>
<body class="auth-body">
<div class="auth-bg" aria-hidden="true"></div>
<?php
 $flashError     = $_SESSION['flash_error']   ?? null;
 $flashSuccess   = $_SESSION['flash_success'] ?? null;
 $flashInfo      = $_SESSION['flash_info']    ?? null;
 $sessionExpired = !empty($_SESSION['session_expired']);
 $accessDenied   = !empty($_SESSION['access_denied']);
unset($_SESSION['flash_error'], $_SESSION['flash_success'], $_SESSION['flash_info'],
      $_SESSION['session_expired'], $_SESSION['access_denied']);
?>
<main class="auth-shell">

    <header class="auth-top">
        <div class="auth-logo">
    <img class="auth-logo-img" src="<?php echo BASE_URL; ?>/public/img/logo-t.png" alt="SMU logo" onerror="var s=+(this.dataset.s||0)+1;this.dataset.s=s;if(s==1){this.src='<?php echo BASE_URL; ?>/img/logo-t.png';}else if(s==2){this.src='<?php echo BASE_URL; ?>/public/img/logo.png';}else if(s==3){this.src='<?php echo BASE_URL; ?>/img/logo.png';}else{this.remove();}">
    <span class="auth-logo-text">SMU</span>
</div>
        <div class="auth-uniname">St. Mary's University<small>Exit Exam Tracker</small></div>
    </header>

    <h1 class="auth-title">Your Journey to Success Starts Here</h1>
    <p class="auth-tagline">Prepare for the national university exit examination with structured practice exams, instant feedback, and progress tracking &mdash; all in one place.</p>

    <section class="auth-card">
        <h2>Welcome Back!</h2>
        <p class="sub">Log in with your Student / Staff ID to continue.</p>

        <?php if ($sessionExpired): ?>
            <div class="alert alert-info">Your session expired after 30 minutes of inactivity. Please log in again.</div>
        <?php endif; ?>
        <?php if ($accessDenied): ?>
            <div class="alert alert-error">You are not authorized to access that page.</div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>
        <?php if ($flashSuccess): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($flashSuccess); ?></div>
        <?php endif; ?>
        <?php if ($flashInfo): ?>
            <div class="alert alert-info"><?php echo htmlspecialchars($flashInfo); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/auth/login">
            <div class="form-group">
                <label for="id_number">Student / Staff ID</label>
                <input type="text" id="id_number" name="id_number" placeholder="Enter your ID number" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="pw-field"><input type="password" id="password" name="password" placeholder="Enter your password" required data-smu-eye="1"><button type="button" class="pw-eye" aria-label="Show password" title="Show password" tabindex="-1"><svg class="i-eye" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg><svg class="i-eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg></button></div>
            </div>
            <div class="form-row">
                <label class="remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                <a class="forgot" href="<?php echo BASE_URL; ?>/auth/forgot">Forgot password?</a>
            </div>
            <button type="submit" class="btn-primary">Sign In</button>
        </form>

        <p class="auth-alt">New student? <a href="<?php echo BASE_URL; ?>/auth/register">Create an account</a></p>
        <p class="auth-note">Instructor and Admin accounts are created by the system administrator.</p>
    </section>

    <section class="auth-features">
        <div class="feature">
            <span class="f-ico"><svg viewBox="0 0 24 24" fill="none" stroke="#f0c75e" stroke-width="2" stroke-linecap="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h4"/></svg></span>
            <span class="f-txt"><b>Practice Exams</b><small>Instant feedback on every question</small></span>
        </div>
        <div class="feature">
            <span class="f-ico"><svg viewBox="0 0 24 24" fill="none" stroke="#f0c75e" stroke-width="2" stroke-linecap="round"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg></span>
            <span class="f-txt"><b>Mock Exams</b><small>Timed, auto-graded, real exam feel</small></span>
        </div>
        <div class="feature">
            <span class="f-ico"><svg viewBox="0 0 24 24" fill="none" stroke="#f0c75e" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/></svg></span>
            <span class="f-txt"><b>Track Progress</b><small>Watch your readiness grow</small></span>
        </div>
    </section>

    <p class="auth-footer">&copy; <?php echo date('Y'); ?> <b>St. Mary's University</b> &nbsp;|&nbsp; Knowledge &middot; Character &middot; Service</p>
</main>

<script src="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/js/form-ui.js" defer></script>
<script id="smu-pw-eye">
(function () {
  var field = document.querySelector('.pw-field');
  var input = document.getElementById('password');
  if (!field || !input) { return; }
  var btn = field.querySelector('.pw-eye');
  if (!btn) { return; }
  btn.addEventListener('mousedown', function (e) { e.preventDefault(); });
  btn.addEventListener('click', function (e) {
    e.preventDefault();
    var show = (input.type === 'password');
    input.type = show ? 'text' : 'password';
    field.classList.toggle('show-pw', show);
    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    btn.setAttribute('title', show ? 'Hide password' : 'Show password');
  });
})();
</script>
</body>
</html>