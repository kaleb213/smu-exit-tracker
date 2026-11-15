<?php
/** Attempt history — paginated permanent record of every attempt. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>Results &amp; History</h1>
    <p><?= (int) $total ?> completed attempt<?= $total == 1 ? '' : 's' ?> — page <?= (int) $page ?> of <?= (int) $pages ?>.
       Every attempt is permanently stored in the database.</p>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>#</th><th>Mode</th><th>Scope</th><th>Score</th><th>Status</th><th>Date</th><th></th></tr>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td>#<?= (int) $r['id'] ?></td>
          <td><span class="tag"><?= View::e(ucfirst((string) $r['mode'])) ?></span></td>
          <td><?= $r['subject_name'] !== null ? View::e($r['subject_name']) : 'All subjects' ?>
              <?= $r['topic_name'] !== null ? ' › ' . View::e($r['topic_name']) : '' ?></td>
          <td><b><?= (int) $r['correct_count'] ?>/<?= (int) $r['total_questions'] ?></b>
              (<?= (float) $r['score_percent'] ?>%)</td>
          <td><?= $r['status'] === 'completed' ? '<span class="pill pill-ok">Completed</span>' : '<span class="pill pill-bad">Expired</span>' ?></td>
          <td><?= View::e((string) $r['submitted_at']) ?></td>
          <td style="text-align:right"><a class="a-edit" href="<?= View::url('progress/results/' . (int) $r['id']) ?>">Review ⟶</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($rows === []): ?>
        <tr><td colspan="7" style="color:var(--muted)">No attempts yet — start with
          <a class="link" href="<?= View::url('practice/index') ?>">Practice Mode</a>.</td></tr>
      <?php endif; ?>
    </table>
  </div>
  <div class="pager">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <?php if ($p !== $page): ?>
        <a href="<?= View::url('progress/history/' . $p) ?>"><?= $p ?></a>
      <?php else: ?>
        <span><b><?= $p ?></b></span>
      <?php endif; ?>
    <?php endfor; ?>
  </div>
</div>
