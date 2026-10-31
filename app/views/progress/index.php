<?php require APP_PATH . '/views/layouts/header.php'; ?>

<h1>My Progress</h1>
<p class="muted">How ready are you for the exit exam? This page updates itself after every practice quiz and mock exam.</p>

<?php if ($readiness === null): ?>

    <div class="alert alert-info">
        You have no attempts yet. Start with a
        <a href="<?php echo BASE_URL; ?>/practice">practice quiz</a> or a
        <a href="<?php echo BASE_URL; ?>/mock/start">mock exam</a> —
        your charts will appear here after your first attempt.
    </div>

<?php else: ?>

    <?php
        $band = 'band-low';
        if ($readiness >= 75)      { $band = 'band-high'; }
        elseif ($readiness >= 50)  { $band = 'band-mid'; }
    ?>
    <div class="score-hero <?php echo $band; ?>" style="display:flex;align-items:center;gap:20px;margin:20px 0;">
        <div style="font-size:44px;font-weight:800;"><?php echo round($readiness); ?>%</div>
        <div>
            <h2 style="margin:0;">Exam Readiness</h2>
            <p class="muted" style="margin:4px 0 0;">Average of your last 5 attempts (practice + mock).</p>
        </div>
    </div>

    <div class="stat-grid" style="margin:20px 0;">
        <div class="card" style="padding:16px;">
            <h2 style="margin:0;"><?php echo (int)$summary['attempts']; ?></h2>
            <p class="muted" style="margin:4px 0 0;">Total attempts</p>
        </div>
        <div class="card" style="padding:16px;">
            <h2 style="margin:0;"><?php echo round($summary['avg_percentage']); ?>%</h2>
            <p class="muted" style="margin:4px 0 0;">Average score</p>
        </div>
        <div class="card" style="padding:16px;">
            <h2 style="margin:0;"><?php echo round($summary['best_percentage']); ?>%</h2>
            <p class="muted" style="margin:4px 0 0;">Best score</p>
        </div>
    </div>

    <div class="card" style="padding:16px;margin:20px 0;">
        <h2 style="margin-top:0;">Score history (last 20 attempts)</h2>
        <canvas id="historyChart" height="110"></canvas>
    </div>

    <div class="card" style="padding:16px;margin:20px 0;">
        <h2 style="margin-top:0;">Average score by subject</h2>
        <canvas id="subjectChart" height="110"></canvas>
    </div>

    <div class="card" style="padding:16px;margin:20px 0;">
        <h2 style="margin-top:0;">Weakest topics</h2>
        <?php if (!$weaknesses): ?>
            <p class="muted">No topic data yet — practice quizzes build this list.</p>
        <?php else: ?>
            <?php foreach ($weaknesses as $w): ?>
                <div class="topic-row">
                    <span><?php echo htmlspecialchars($w['topic_name']); ?>
                        <span class="muted">(<?php echo htmlspecialchars($w['subject_name']); ?>)</span>
                    </span>
                    <span><?php echo $w['avg_percentage']; ?>% — <?php echo (int)$w['attempts']; ?> attempt(s)</span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card" style="padding:16px;margin:20px 0;">
        <h2 style="margin-top:0;">Strongest topics</h2>
        <?php if (!$strengths): ?>
            <p class="muted">No topic data yet.</p>
        <?php else: ?>
            <?php foreach ($strengths as $s): ?>
                <div class="topic-row">
                    <span><?php echo htmlspecialchars($s['topic_name']); ?>
                        <span class="muted">(<?php echo htmlspecialchars($s['subject_name']); ?>)</span>
                    </span>
                    <span><?php echo $s['avg_percentage']; ?>% — <?php echo (int)$s['attempts']; ?> attempt(s)</span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="card" style="padding:16px;margin:20px 0;">
        <h2 style="margin-top:0;">Recent attempts</h2>
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>Mode</th><th>Subject / Topic</th><th>Score</th><th>%</th></tr>
            </thead>
            <tbody>
            <?php foreach ($attempts as $a): ?>
                <tr>
                    <td><?php echo date('M j, Y H:i', strtotime($a['created_at'])); ?></td>
                    <td>
                        <?php if ($a['exam_type'] === 'mock'): ?>
                            <span style="color:#8a5a00;font-weight:700;">Mock</span>
                        <?php else: ?>
                            <span style="color:#1a6b3c;font-weight:700;">Practice</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php echo htmlspecialchars($a['subject_name'] ?? '—'); ?>
                        <?php if (!empty($a['topic_name'])): ?>
                            <span class="muted">/ <?php echo htmlspecialchars($a['topic_name']); ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo (int)$a['score']; ?> / <?php echo (int)$a['total_questions']; ?></td>
                    <td><?php echo round($a['percentage']); ?>%</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="<?php echo PUBLIC_URL; ?>/lib/chart.umd.min.js"></script>
    <script>
        var HISTORY  = <?php echo json_encode($chartHistory); ?>;
        var SUBJECTS = <?php echo json_encode($chartSubjects); ?>;
    </script>
    <script src="<?php echo PUBLIC_URL; ?>/js/charts.js"></script>

<?php endif; ?>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>