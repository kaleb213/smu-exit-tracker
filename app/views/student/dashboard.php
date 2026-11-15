<?php
/** Student dashboard — readiness KPIs, score history, subject cards. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>Dashboard</h1>
    <p>Welcome back, <?= View::e($user['name']) ?> — your exam readiness at a glance.</p>
  </div>
  <a class="btn btn-primary btn-sm" style="text-decoration:none" href="<?= View::url('practice/index') ?>">＋ Start practice</a>
</div>

<div class="grid g4">
  <div class="card kpi"><span class="l">READINESS METRIC</span>
    <span class="v" style="color:var(--goldsoft)"><?= (int) $readiness ?>%</span>
    <span class="d <?= $trend >= 0 ? 'up' : 'down' ?>"><?= $trend >= 0 ? '▲' : '▼' ?> <?= abs((int) $trend) ?>% vs previous attempt</span></div>
  <div class="card kpi"><span class="l">TOTAL ATTEMPTS</span><span class="v"><?= (int) $attempts ?></span><span class="d"><?= (int) $mockAttempts ?> mock exam<?= $mockAttempts == 1 ? '' : 's' ?> taken</span></div>
  <div class="card kpi"><span class="l">READINESS TARGET</span><span class="v"><?= (int) $goal ?>%</span><span class="d"><?= $readiness >= $goal ? '<span class="up">target reached ✓</span>' : '<span class="down">' . max(0, $goal - (int) $readiness) . '% to go</span>' ?></span></div>
  <div class="card kpi"><span class="l">WEAK TOPICS FLAGGED</span><span class="v" style="color:var(--bad)"><?= (int) $weakCount ?></span><span class="d down">below 50% accuracy</span></div>
</div>

<div class="grid g2" style="margin-top:16px">
  <div class="card">
    <h3>Score history — last attempts</h3>
    <canvas data-chart="line"
      data-labels='<?= View::e(json_encode(array_map(fn ($h) => '#'.$h['id'], $history))) ?>'
      data-data='<?= View::e(json_encode(array_map(fn ($h) => (float) $h['score_percent'], $history))) ?>'></canvas>
  </div>
  <div class="card">
    <h3>Exam readiness</h3>
    <div class="donut-wrap">
      <canvas data-chart="donut" data-value="<?= (int) $readiness ?>"></canvas>
      <div style="font-size:12.5px;line-height:2">
        <div><span class="pill <?= $readiness >= $goal ? 'pill-ok' : 'pill-warn' ?>"><?= (int) $readiness ?>%</span> overall readiness</div>
        <div style="color:var(--muted)">Target: ≥ <?= (int) $goal ?>% before exam day</div>
        <div style="color:var(--muted)">Formula: 65% last 5 attempts + 35% overall</div>
      </div>
    </div>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <h3>Subjects — exit exam syllabus</h3>
  <div class="grid g3" style="margin-top:6px">
    <?php foreach ($subjectCards as $s): ?>
      <a class="subj-card" href="<?= View::url('practice/index?subject=' . (int) $s['id']) ?>">
        <div class="ic">📘</div>
        <b><?= View::e($s['name']) ?></b>
        <div class="meta"><?= (int) $s['question_count'] ?> questions · <?= (int) $s['topic_count'] ?> topics</div>
      </a>
    <?php endforeach; ?>
    <a class="subj-card" href="<?= View::url('mock/index') ?>" style="border-style:dashed">
      <div class="ic">⏱</div>
      <b>Full Mock Exam</b>
      <div class="meta">Timed · all subjects · deferred feedback</div>
    </a>
  </div>
</div>
