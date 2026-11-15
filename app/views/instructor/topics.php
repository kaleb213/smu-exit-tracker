<?php
/** Instructor — weak topics detail page. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>Weak Topics</h1>
    <p>Topics where the cohort records its lowest accuracy. A topic is ranked once it has at least 3 recorded answers.</p>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Rank</th><th>Topic</th><th>Subject</th><th>Answers</th><th>Cohort accuracy</th><th>Recommended action</th></tr>
      <?php foreach ($weakTopics as $i => $t): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><b><?= View::e($t['topic']) ?></b></td>
          <td><?= View::e($t['subject']) ?></td>
          <td><?= (int) $t['answers'] ?></td>
          <td>
            <?php $a = (int) $t['accuracy']; ?>
            <span class="pill <?= $a < 50 ? 'pill-bad' : ($a < 75 ? 'pill-warn' : 'pill-ok') ?>"><?= $a ?>%</span>
          </td>
          <td><?= $a < 50 ? 'Targeted tutorial session + practice assignment'
                : ($a < 75 ? 'Quick review at the start of the next class' : 'Optional revision') ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($weakTopics === []): ?>
        <tr><td colspan="6" style="color:var(--muted)">No data yet — students need to complete attempts first.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>
