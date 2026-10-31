<?php
/**
 * Shared top bar + navbar for all logged-in pages.
 * Views get $pageTitle / $activePage from their controller.
 */
 $pageTitle  = $pageTitle  ?? 'Dashboard';
 $activePage = $activePage ?? '';

/* Brand link goes to the home page of the logged-in role */
 $homePage = 'student';
switch (strtolower($_SESSION['role'] ?? '')) {
    case 'admin':      $homePage = 'admin';      break;
    case 'instructor': $homePage = 'instructor'; break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($pageTitle); ?> &mdash; <?php echo APP_NAME; ?></title>
<link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>/css/style.css">
<link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>/css/dashboard.css">
<link rel="icon" type="image/png" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/img/logo-t.png">
<link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/css/form-ui.css">
<link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/css/polish.css">
</head>
<body data-ui-role="<?php echo strtolower(trim((string)($_SESSION['role'] ?? ''))); ?>" class="app-body">
<div class="topbar">
    <a class="brand" href="<?php echo BASE_URL; ?>/<?php echo $homePage; ?>"><span class="logo-wrap"><img class="topbar-logo" src="<?php echo BASE_URL; ?>/public/img/logo-t.png" alt="SMU logo" onerror="var s=+(this.dataset.s||0)+1;this.dataset.s=s;if(s==1){this.src='<?php echo BASE_URL; ?>/img/logo-t.png';}else if(s==2){this.src='<?php echo BASE_URL; ?>/public/img/logo.png';}else if(s==3){this.src='<?php echo BASE_URL; ?>/img/logo.png';}else{this.style.display='none';var b=this.parentNode?this.parentNode.querySelector('.logo-badge'):null;if(b){b.style.display='flex';}}"><span class="logo-badge">SMU</span></span>St. Mary's University<small>Exit Exam Tracker</small></a>
    <?php require APP_PATH . '/views/layouts/navbar.php'; ?>
    <div class="topbar-right">
        <span class="user-chip"><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
        <a class="logout" href="<?php echo BASE_URL; ?>/auth/logout">Log Out</a>
    </div>
</div>
<div class="page">
<?php
foreach (['flash_error' => 'alert-error', 'flash_success' => 'alert-success', 'flash_info' => 'alert-info'] as $flashKey => $flashClass) {
    if (!empty($_SESSION[$flashKey])) {
        echo '<div class="alert ' . $flashClass . '">' . htmlspecialchars($_SESSION[$flashKey]) . '</div>';
        unset($_SESSION[$flashKey]);
    }
}
?>