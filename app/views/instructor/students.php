<?php
/** Instructor — per-student performance table. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>Student Performance</h1>
    <p>Per-student averages across all completed attempts — sorted weakest first so nobody slips through.</p>
  </div>
  <a class="btn btn-ghost btn-sm" style="text-decoration:none" href="<?= View::url('reporting/export') ?>">⬇ Export CSV</a>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <tr><th>Student</th><th>ID Number</th><th>Attempts</th><th>Average</th><th>Weakest subject</th><th>Last attempt</th><th>Status</th></tr>
      <?php foreach ($rows as $r): $w = $weak[(int) $r['id']] ?? '—'; ?>
        <tr>
          <td><b><?= View::e($r['name']) ?></b></td>
          <td><?= View::e($r['id_number']) ?></td>
          <td><?= (int) $r['attempts'] ?></td>
          <td>
            <?php
              $avg = (float) $r['avg_score'];
              $cls = $avg >= 75 ? 'pill-ok' : ($avg >= 50 ? 'pill-warn' : 'pill-bad');
              echo '<span class="pill ' . $cls . '">' . (int) $avg . '%</span>';
            ?>
          </td>
          <td><?= View::e($w) ?></td>
          <td><?= $r['last_attempt'] !== null ? View::e(substr((string) $r['last_attempt'], 0, 16)) : '—' ?></td>
          <td><?= $r['status'] === 'active' ? '<span class="pill pill-ok">Active</span>' : '<span class="pill pill-bad">Deactivated</span>' ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if ($rows === []): ?>
        <tr><td colspan="7" style="color:var(--muted)">No students registered yet.</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>
