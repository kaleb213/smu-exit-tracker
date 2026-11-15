<?php
/** Results & Feedback — score, topic breakdown and answer review. */
use App\Core\View;
$bandPill = ['green' => 'pill-ok', 'amber' => 'pill-warn', 'red' => 'pill-bad'][$band] ?? 'pill-navy';
?>
<div class="page-head">
  <div>
    <h1>Results &amp; Feedback</h1>
    <p>Attempt #<?= (int) $session['id'] ?> · <?= View::e(ucfirst((string) $session['mode'])) ?>
      <?= $session['subject_name'] !== null ? '· ' . View::e($session['subject_name']) : '' ?>
      <?= $session['topic_name'] !== null ? ' › ' . View::e($session['topic_name']) : '' ?>
      · submitted <?= View::e((string) $session['submitted_at']) ?></p>
  </div>
  <span class="pill <?= $bandPill ?>" style="font-size:12px">SCORE SAVED TO DATABASE</span>
</div>

<div class="grid g4">
  <div class="card kpi"><span class="l">SCORE</span>
    <span class="v" style="color:var(--goldsoft)"><?= (int) $session['correct_count'] ?> / <?= (int) $session['total_questions'] ?></span></div>
  <div class="card kpi"><span class="l">PERCENTAGE</span><span class="v"><?= (float) $session['score_percent'] ?>%</span></div>
  <div class="card kpi"><span class="l">CORRECT</span><span class="v" style="color:var(--ok)"><?= (int) $session['correct_count'] ?></span></div>
  <div class="card kpi"><span class="l">INCORRECT / SKIPPED</span>
    <span class="v" style="color:var(--bad)"><?= max(0, (int) $session['total_questions'] - (int) $session['correct_count']) ?></span></div>
</div>

<div class="grid g2" style="margin-top:16px">
  <div class="card">
    <h3>Strengths &amp; weaknesses by topic</h3>
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
    <?php if ($topics === []): ?><p style="color:var(--muted);font-size:13px">No topic data for this attempt.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3>Constructive feedback</h3>
    <div style="font-size:13px;line-height:1.8;color:rgba(255,255,255,.85)">
      <?php if ($topics !== []):
        $weakest = $topics[0];
        $strongest = end($topics);
      ?>
        <p><b>What went well:</b> your strongest topic here is
          <span class="pill pill-ok"><?= View::e($strongest['topic']) ?></span> at <?= (int) $strongest['accuracy'] ?>%.</p>
        <p style="margin-top:10px"><b>Focus next:</b>
          <span class="pill pill-bad"><?= View::e($weakest['topic']) ?></span> scored <?= (int) $weakest['accuracy'] ?>%.
          A practice session on this topic is recommended before your next mock exam.</p>
        <p style="margin-top:10px;color:var(--muted)">
          Every attempt is permanently recorded — check <b>My Progress</b> to see the trend over time.</p>
        <a class="btn btn-primary btn-sm" style="text-decoration:none;margin-top:14px;display:inline-block"
           href="<?= View::url('progress/dashboard') ?>">Open dashboard</a>
      <?php else: ?>
        <p style="color:var(--muted)">No answers were recorded for this attempt.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="card" style="margin-top:16px">
  <h3>Answer review — every question with its explanation</h3>
  <?php foreach ($review as $i => $r): ?>
    <div style="border-top:1px dashed rgba(255,255,255,.2);padding:14px 0 <?= $i === count($review) - 1 ? '0' : '14px' ?>">
      <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap">
        <div><span class="tag">#<?= $i + 1 ?></span> <span class="tag"><?= View::e($r['topic']) ?></span></div>
        <?= (int) $r['is_correct'] === 1
            ? '<span class="pill pill-ok">Correct</span>'
            : ($r['selected_option'] === null ? '<span class="pill pill-warn">Skipped</span>' : '<span class="pill pill-bad">Incorrect</span>') ?>
      </div>
      <p style="font-size:13.5px;font-weight:600;margin:8px 0"><?= View::e($r['question_text']) ?></p>
      <div style="font-size:12.5px;color:var(--muted);line-height:1.7">
        Your answer: <b style="color:rgba(255,255,255,.85)"><?= $r['selected_option'] !== null ? View::e($r['selected_option']) : '—' ?></b>
        &nbsp;·&nbsp; Correct answer: <b style="color:#9ff0bf"><?= View::e($r['correct_option']) ?></b><br>
        <?= View::e($r['explanation']) ?>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if ($review === []): ?><p style="color:var(--muted);font-size:13px">Nothing to review yet.</p><?php endif; ?>
</div>
