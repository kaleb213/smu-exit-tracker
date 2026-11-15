<?php $pageTitle  = 'Class Trends'; $activePage = 'trends'; require APP_PATH . '/views/layouts/header.php'; ?>
<?php $active = 'trends'; require APP_PATH . '/views/instructor/tabs.php'; ?>

<h1>Class Trends</h1>
<p class="muted">Average scores over time and by subject.</p>

<?php if (empty($chartDaily['labels'])): ?>

    <div class="alert alert-info">No attempts yet — charts will appear after students start taking quizzes and exams.</div>

<?php else: ?>

    <div class="card" style="padding:16px;margin:20px 0;">
        <h2 style="margin-top:0;">Class average per day (last 14 active days)</h2>
        <canvas id="trendChart" height="110"></canvas>
    </div>

    <div class="card" style="padding:16px;margin:20px 0;">
        <h2 style="margin-top:0;">Average by subject</h2>
        <canvas id="subjectTrendChart" height="110"></canvas>
    </div>

    <script src="<?php echo PUBLIC_URL; ?>/lib/chart.umd.min.js"></script>
    <script>
        var TRENDS  = <?php echo json_encode($chartDaily); ?>;
        var SUBJAVG = <?php echo json_encode($chartSubjects); ?>;
    </script>
    <script src="<?php echo PUBLIC_URL; ?>/js/instructor-charts.js"></script>

<?php endif; ?>