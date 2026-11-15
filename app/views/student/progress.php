<?php
/** Full progress page — history chart, subject averages, topic diagnostics, goal setting. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>My Progress</h1>
    <p>Every attempt is recorded permanently — this is your visual history of improvement over time.</p>
  </div>
</div>

<div class="grid g2">
  <div class="card">
    <h3>Score history (last <?= count($history) ?> completed attempts)</h3>
    <canvas data-chart="line"
      data-labels='<?= View::e(json_encode(array_map(fn ($h) => '#'.$h['id'], $history))) ?>'
      data-data='<?= View::e(json_encode(array_map(fn ($h) => (float) $h['score_percent'], $history))) ?>'></canvas>
  </div>
  <div class="card">
    <h3>Average score by subject</h3>
    <canvas data-chart="bar"
      data-labels='<?= View::e(json_encode(array_map(fn ($s) => (string) $s['subject'], $subjects))) ?>'
      data-data='<?= View::e(json_encode(array_map(fn ($s) => (int) round((float) $s['avg_score']), $subjects))) ?>'></canvas>
  </div>
</div>

<div class="grid g2" style="margin-top:16px">
  <div class="card">
    <h3>Topic-level diagnostics</h3>
    <?php foreach ($topics as $t): ?>
      <div class="bar-row">
        <div class="t">
          <span><?= View::e($t['topic']) ?> (<?= View::e($t['subject']) ?>)</span>
          <b class="<?= (int) $t['accuracy'] >= 50 ? 'up' : 'down' ?>"><?= (int) $t['accuracy'] ?>%</b>
        </div>
        <div class="track"><div class="fill <?= (int) $t['accuracy'] >= 75 ? 'g' : ((int) $t['accuracy'] >= 50 ? 'y' : 'r') ?>"
          style="width:<?= (int) $t['accuracy'] ?>%"></div></div>
      </div>
    <?php endforeach; ?>
    <?php if ($topics === []): ?>
      <p style="color:var(--muted);font-size:13px">Take your first practice session to generate topic diagnostics.</p>
    <?php endif; ?>
  </div>
  <div class="card">
    <h3>Readiness metric &amp; goal setting</h3>
    <div class="donut-wrap">
      <canvas data-chart="donut" data-value="<?= (int) $readiness ?>"></canvas>
      <div style="font-size:12.5px;line-height:2">
        <div><span class="pill <?= $readiness >= $goal ? 'pill-ok' : 'pill-warn' ?>"><?= (int) $readiness ?>%</span> ready</div>
        <div style="color:var(--muted)">Goal-setting tool: choose your target below</div>
        <div style="color:var(--muted)">Weakest topic: <?= $topics !== [] ? View::e($topics[0]['topic']) . ' ' . (int) $topics[0]['accuracy'] . '%' : '—' ?></div>
      </div>
    </div>
    <form method="post" action="<?= View::url('progress/goal') ?>" style="margin-top:16px;display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
      <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
      <div>
        <label class="lbl" style="margin-top:0">TARGET READINESS (%)</label>
        <input class="box" type="number" name="target" min="0" max="100" value="<?= (int) $goal ?>" style="width:120px">
      </div>
      <button class="btn btn-primary btn-sm" type="submit">Save goal</button>
    </form>
    <?php if (!empty($goalSaved)): ?><div class="flash ok"><?= View::e($goalSaved) ?></div><?php endif; ?>
    <p style="margin-top:14px;font-size:12.5px;color:var(--muted)">Scores below 50% are shown in red, 50–74% amber, ≥75% green.</p>
  </div>
</div>
