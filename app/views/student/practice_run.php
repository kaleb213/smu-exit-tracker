<?php
/** Practice Mode runner — AJAX-driven question flow with instant feedback. */
use App\Core\View;
?>
<div id="practice-run" data-session="<?= (int) $sessionId ?>" data-total="<?= (int) $total ?>"></div>

<div class="page-head">
  <div>
    <h1>Practice — <?= View::e($topic['name']) ?></h1>
    <p><?= View::e($subjectName) ?> · untimed · instant feedback after every answer</p>
  </div>
  <span class="pill pill-navy" id="practice-counter">0 / <?= (int) $total ?> answered</span>
</div>

<div class="exam-top">
  <div class="qmeta">ANSWER EACH QUESTION — YOU WILL SEE THE CORRECT ANSWER AND AN EXPLANATION IMMEDIATELY</div>
  <div style="flex:1;max-width:320px">
    <div class="progress-track"><div class="progress-fill" id="practice-progress" style="width:0%"></div></div>
  </div>
</div>

<div class="qcard" id="practice-card">
  <div class="qnum" id="q-num">Loading…</div>
  <div class="qtext" id="q-text"></div>
  <div id="q-options"></div>
  <div class="feedback" id="q-feedback"></div>
  <div class="exam-actions">
    <button class="btn btn-ghost btn-sm" id="btn-skip" type="button">Skip question</button>
    <div>
      <button class="btn btn-ghost btn-sm" id="btn-next" type="button" style="display:none">Next question ⟶</button>
      <button class="btn btn-primary btn-sm" id="btn-submit" type="button">Finish &amp; see results</button>
    </div>
  </div>
</div>
