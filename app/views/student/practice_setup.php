<?php
/** Practice Mode setup — pick a subject, then a topic. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>Practice Mode</h1>
    <p>Untimed, topic-level questions with immediate feedback. Questions are randomized by subject and topic.</p>
  </div>
  <span class="pill pill-navy" style="font-size:12px">UNTIMED · <?= (int) $count ?> QUESTIONS PER SESSION</span>
</div>

<div class="note"><b>Setup:</b> choose a subject → choose a topic → the Question Bank Service fetches matching
  validated questions in randomized order. Every attempt is logged to your Progress Tracker.</div>

<div class="card">
  <h3>1 · Choose a subject</h3>
  <div class="grid g3" style="margin-top:6px">
    <?php foreach ($subjects as $s): ?>
      <a class="subj-card <?= $subjectId === (int) $s['id'] ? '' : '' ?>" href="<?= View::url('practice/index?subject=' . (int) $s['id']) ?>">
        <div class="ic">📘</div>
        <b><?= View::e($s['name']) ?></b>
        <div class="meta"><?= (int) $s['question_count'] ?> validated questions</div>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<?php if ($subjectId !== null): ?>
<div class="card" style="margin-top:16px">
  <h3>2 · Choose a topic</h3>
  <?php if ($topics === []): ?>
    <p style="color:var(--muted);font-size:13px">No topics exist for this subject yet.</p>
  <?php else: ?>
    <div class="table-wrap" style="margin-top:8px">
      <table>
        <tr><th>Topic</th><th>Available questions</th><th></th></tr>
        <?php foreach ($topics as $t): ?>
          <tr>
            <td><b><?= View::e($t['name']) ?></b></td>
            <td><?= (int) $t['question_count'] > 0
                  ? '<span class="pill pill-ok">' . (int) $t['question_count'] . ' available</span>'
                  : '<span class="pill pill-bad">none yet</span>' ?></td>
            <td style="text-align:right">
              <?php if ((int) $t['question_count'] > 0): ?>
                <form method="post" action="<?= View::url('practice/start') ?>">
                  <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
                  <input type="hidden" name="topic" value="<?= (int) $t['id'] ?>">
                  <input type="hidden" name="subject" value="<?= $subjectId ?>">
                  <button class="btn btn-primary btn-sm" type="submit">Start practice ⟶</button>
                </form>
              <?php else: ?>
                <span style="font-size:12px;color:var(--muted)">ask the admin to add questions</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>
