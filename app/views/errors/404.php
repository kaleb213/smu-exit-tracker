<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Page Not Found | St. Mary's University</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #7f1d1d, #b91c1c);
               color: #fff; text-align: center; }
        .code { font-size: 90px; font-weight: bold; margin: 0; line-height: 1; }
        h1 { font-size: 26px; margin: 10px 0; }
        p  { color: #fecaca; margin-bottom: 28px; }
        a  { background: #fff; color: #b91c1c; padding: 12px 30px; border-radius: 8px;
             text-decoration: none; font-weight: bold; }
        a:hover { background: #fde8e8; }
        .uni { margin-top: 34px; font-size: 13px; letter-spacing: 2px; color: #fecaca;
               text-transform: uppercase; }
    </style>
<link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/css/form-ui.css">
<link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/css/polish.css">
</head>
<body>
    <div>
        <p class="code">404</p>
        <h1>Page Not Found</h1>
        <p>The page you are looking for does not exist or has been moved.</p>
        <a href="<?php echo BASE_URL; ?>/auth">Back to Login</a>
        <p class="uni">St. Mary's University &middot; Exit Exam Preparation &amp; Progress Tracker</p>
    </div>
<script src="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/js/form-ui.js" defer></script>
</body>
</html