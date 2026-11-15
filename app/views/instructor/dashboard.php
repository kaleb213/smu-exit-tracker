<?php
/** Instructor dashboard — class KPIs, averages by subject, weak topics, weekly trend. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>Instructor — Performance Dashboard</h1>
    <p>Aggregated, anonymized class-level trends — data to target tutorials where they matter most.</p>
  </div>
  <span class="pill pill-navy" style="font-size:12px">READ-ONLY · AGGREGATED DATA</span>
</div>

<div class="grid g4">
  <div class="card kpi"><span class="l">ACTIVE STUDENTS</span><span class="v"><?= (int) $kpis['active_students'] ?></span></div>
  <div class="card kpi"><span class="l">CLASS AVG (COMPLETED)</span>
    <span class="v"><?= (int) round($kpis['class_avg']) ?>%</span></div>
  <div class="card kpi"><span class="l">ATTEMPTS LOGGED</span><span class="v"><?= number_format((int) $kpis['attempts']) ?></span></div>
  <div class="card kpi"><span class="l">VALIDATED QUESTIONS</span><span class="v"><?= (int) $kpis['questions'] ?></span></div>
</div>

<div class="grid g2" style="margin-top:16px">
  <div class="card">
    <h3>Class average by subject</h3>
    <canvas data-chart="bar"
      data-labels='<?= View::e(json_encode(array_map(fn ($r) => (string) $r['subject'], $bySubject))) ?>'
      data-data='<?= View::e(json_encode(array_map(fn ($r) => (int) round((float) $r['avg_score']), $bySubject))) ?>'></canvas>
  </div>
  <div class="card">
    <h3>Weekly trend — class average</h3>
    <canvas data-chart="line"
      data-labels='<?= View::e(json_encode(array_map(fn ($r) => substr((string) $r['yw'], 4) . '/' . substr((string) $r['yw'], 0, 4), $trend))) ?>'
      data-data='<?= View::e(json_encode(array_map(fn ($r) => (int) $r['avg_score'], $trend))) ?>'></canvas>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <h3>Topics needing tutorial intervention (cohort accuracy)</h3>
  <div class="table-wrap">
    <table>
      <tr><th>Topic</th><th>Subject</th><th>Answers recorded</th><th>Accuracy</th><th>Signal</th></tr>
      <?php foreach ($weakTopics as $t): ?>
        <tr>
          <td><b><?= View::e($t['topic']) ?></b></td>
          <td><?= View::e($t['subject']) ?></td>
          <td><?= (int) $t['answers'] ?></td>
          <td><?= (int) $t['accuracy'] ?>%</td>
          <td><?= (int) $t['accuracy'] < 50
                ? '<span class="pill pill-bad">critical — schedule a tutorial</span>'
                : ((int) $t['accuracy'] < 75 ? '<span class="pill pill-warn">review in class</span>' : '<span class="pill pill-ok">healthy</span>') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($weakTopics === []): ?>
        <tr><td colspan="5" style="color:var(--muted)">Not enough attempt data yet — topics appear here once students start practising.</td></tr>
      <?php endif; ?>
    </table>
  </div>
  <div class="note" style="margin:14px 0 0">
    Example insight from the documentation: “the class needs to review <b>Computer Networks</b> and
    <b>Software Engineering</b>” → schedule targeted tutorials for the red topics above.
  </div>
</div>
