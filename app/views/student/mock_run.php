<?php
/** Mock Exam runner — all questions on one page with a live countdown. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>Mock Exam in progress</h1>
    <p><?= count($questions) ?> questions · all subjects · answers are hidden until you submit</p>
  </div>
  <div class="timer" id="mock-timer" data-remaining="<?= (int) $deadlineSec ?>">--:--</div>
</div>

<div class="exam-top">
  <div class="qmeta">CHOOSE ONE OPTION PER QUESTION · SKIPPED QUESTIONS COUNT AS INCORRECT · THE EXAM AUTO-SUBMITS WHEN TIME RUNS OUT</div>
  <span class="pill pill-warn"><?= (int) $minutes ?> MINUTES</span>
</div>

<form id="mock-form" method="post" action="<?= View::url('mock/submit') ?>">
  <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
  <input type="hidden" name="session" value="<?= (int) $sessionId ?>">

  <?php foreach ($questions as $i => $q): ?>
  <div class="qcard" style="margin-bottom:14px">
    <div class="qnum">QUESTION <?= $i + 1 ?> OF <?= count($questions) ?></div>
    <div class="qtext"><?= View::e($q['question_text']) ?></div>
    <?php foreach (['A', 'B', 'C', 'D'] as $L): ?>
      <label class="opt">
        <span class="k"><?= $L ?></span>
        <span style="flex:1"><?= View::e($q['option_' . strtolower($L)]) ?></span>
        <input type="radio" name="answers[<?= (int) $q['id'] ?>]" value="<?= $L ?>" style="accent-color:#f2c230;margin-top:4px">
      </label>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>

  <div class="qcard" style="text-align:center">
    <p style="font-size:13px;color:var(--muted);margin-bottom:14px">You can submit at any time — unanswered questions are marked incorrect.</p>
    <button class="btn btn-white" type="submit">Submit exam <span>⟶</span></button>
  </div>
</form>
