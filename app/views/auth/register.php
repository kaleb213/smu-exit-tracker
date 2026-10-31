<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account &mdash; <?php echo APP_NAME; ?></title>
<link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>/css/auth.css">
<link rel="icon" type="image/png" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/img/logo.png">
<link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/css/form-ui.css">
<link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/css/polish.css">
</head>
<body class="auth-body">
<div class="auth-bg" aria-hidden="true"></div>
<?php
 $flashError   = $_SESSION['flash_error'] ?? null;
 $oldFullName  = $_SESSION['old_full_name'] ?? '';
 $oldIdNumber  = $_SESSION['old_id_number'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['old_full_name'], $_SESSION['old_id_number']);
?>
<main class="auth-shell">

    <header class="auth-top">
        <div class="auth-logo">
    <img class="auth-logo-img" src="<?php echo BASE_URL; ?>/public/img/logo-t.png" alt="SMU logo" onerror="var s=+(this.dataset.s||0)+1;this.dataset.s=s;if(s==1){this.src='<?php echo BASE_URL; ?>/img/logo-t.png';}else if(s==2){this.src='<?php echo BASE_URL; ?>/public/img/logo.png';}else if(s==3){this.src='<?php echo BASE_URL; ?>/img/logo.png';}else{this.remove();}">
    <span class="auth-logo-text">SMU</span>
</div>
        <div class="auth-uniname">St. Mary's University<small>Exit Exam Tracker</small></div>
    </header>

    <h1 class="auth-title">Join SMU Exit Exam Tracker</h1>
    <p class="auth-tagline">Join the St. Mary's University exit exam preparation platform. Practice by subject and topic, take timed mock exams, and watch your readiness grow.</p>
    <p class="auth-motto-inline">Learn &middot; Grow &middot; Serve</p>

    <section class="auth-card">
        <h2>Student Registration</h2>
        <p class="sub">Fill in your details to create your account.</p>

        <?php if ($flashError): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($flashError); ?></div>
        <?php endif; ?>

        <form method="POST" action="<?php echo BASE_URL; ?>/auth/register">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name"
                       placeholder="e.g. Edel Wondimeneh" required
                       value="<?php echo htmlspecialchars($oldFullName); ?>">
            </div>
            <div class="form-group">
                <label for="id_number">Student ID</label>
                <input type="text" id="id_number" name="id_number"
                       placeholder="Enter your Student ID" required
                       value="<?php echo htmlspecialchars($oldIdNumber); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="At least 6 characters" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Repeat your password" required>
            </div>
            <button type="submit" class="btn-primary">Create Account</button>
        </form>

        <p class="auth-alt">Already registered? <a href="<?php echo BASE_URL; ?>/auth">Log in</a></p>
        <p class="auth-note">Registration is for students only.<br>Instructor and Admin accounts are created by the system administrator.</p>
    </section>

    <p class="auth-footer">&copy; <?php echo date('Y'); ?> <b>St. Mary's University</b> &nbsp;|&nbsp; Knowledge &middot; Character &middot; Service</p>
</main>

<script src="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/js/form-ui.js" defer></script>
</body>
</html>