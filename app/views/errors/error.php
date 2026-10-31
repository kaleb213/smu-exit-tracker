<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something Went Wrong | St. Mary's University</title>
    <style>
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
               font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #7f1d1d, #b91c1c);
               color: #fff; text-align: center; }
        h1 { font-size: 28px; margin: 10px 0; }
        p  { color: #fecaca; margin-bottom: 28px; }
        a  { background: #fff; color: #b91c1c; padding: 12px 30px; border-radius: 8px;
             text-decoration: none; font-weight: bold; }
    </style>
<link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/css/form-ui.css">
<link rel="stylesheet" href="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/css/polish.css">
</head>
<body>
    <div>
        <h1>Something Went Wrong</h1>
        <p>An unexpected error occurred. Please try again.</p>
        <a href="<?php echo BASE_URL; ?>/auth">Back to Login</a>
    </div>
<script src="<?php echo (defined('BASE_URL') ? rtrim(BASE_URL,'/') : ''); ?>/public/js/form-ui.js" defer></script>
</body>
</html>