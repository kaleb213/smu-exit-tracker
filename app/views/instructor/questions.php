<?php
/** Instructor — read-only question review with cohort success rates. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>Question Review</h1>
    <p>Read-only review of the question bank with cohort success rates — the hardest questions float to the top.</p>
  </div>
</div>

<div class="card">
  <form method="get" action="<?= View::url('reporting/questions') ?>" class="toolbar">
    <select class="box" name="subject" onchange="this.form.submit()">
      <option value="">All subjects</option>
      <?php foreach ($subjects as $s): ?>
        <option value="<?= (int) $s['id'] ?>" <?= $subjectId === (int) $s['id'] ? 'selected' : '' ?>><?= View::e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </form>
  <div class="table-wrap">
    <table>
      <tr><th>ID</th><th>Question</th><th>Topic</th><th>Difficulty</th><th>Answers</th><th>Success rate</th><th>Validated</th></tr>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>Q<?= (int) $r['id'] ?></td>
          <td style="max-width:420px"><?= View::e(mb_strimwidth((string) $r['question_text'], 0, 110, '…')) ?></td>
          <td><?= View::e($r['topic']) ?></td>
          <td><span class="tag"><?= View::e(ucfirst((string) $r['difficulty'])) ?></span></td>
          <td><?= (int) $r['answered'] ?></td>
          <td><?php $sr = (int) $r['success_rate']; ?>
              <span class="pill <?= $sr < 50 ? 'pill-bad' : ($sr < 75 ? 'pill-warn' : 'pill-ok') ?>"><?= $sr ?>%</span></td>
          <td><?= $r['is_validated'] == 1 ? '<span class="pill pill-ok">Yes</span>' : '<span class="pill pill-warn">Pending</span>' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($rows === []): ?>
        <tr><td colspan="7" style="color:var(--muted)">No questions found.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>
