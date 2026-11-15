<?php
/** Mock Exam start page — explains the rules, then starts the attempt. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>Mock Exam Mode</h1>
    <p>Replicates the timed, high-pressure conditions of the national exit exam. Feedback is deferred until submission.</p>
  </div>
  <span class="pill pill-warn" style="font-size:12px">TIMED</span>
</div>

<div class="grid g2">
  <div class="card">
    <h3>Exam rules</h3>
    <table>
      <tr><td><b>Questions</b></td><td><?= (int) $count ?> validated questions from all subjects, randomized order</td></tr>
      <tr><td><b>Time limit</b></td><td><?= (int) $minutes ?> minutes — a countdown is shown during the exam</td></tr>
      <tr><td><b>Submission</b></td><td>Answers are evaluated when you submit, or automatically when time runs out</td></tr>
      <tr><td><b>Feedback</b></td><td>Deferred — score, explanations and weak-topic analysis appear after submission</td></tr>
      <tr><td><b>Skipped questions</b></td><td>Counted as incorrect (like the real exam)</td></tr>
    </table>
    <form method="post" action="<?= View::url('mock/start') ?>" style="margin-top:18px">
      <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
      <button class="btn btn-white" type="submit">Start mock exam <span>⟶</span></button>
    </form>
  </div>
  <div class="card">
    <h3>Why a mock exam?</h3>
    <p style="font-size:13px;line-height:1.8;color:rgba(255,255,255,.8)">
      The national exit exam is timed and high-pressure. Practising under the same conditions builds
      pacing skills and reduces exam anxiety. Take one mock exam each week and compare the scores on
      your Progress page — the readiness metric will show whether you are on track.
    </p>
    <p style="font-size:13px;line-height:1.8;color:var(--muted);margin-top:10px">
      Tip: finish topic-level Practice Mode for your weakest topics before attempting the next mock.
    </p>
  </div>
</div>
