<?php
/** Admin dashboard — system-wide statistics. */
use App\Core\View;
?>
<div class="page-head">
  <div>
    <h1>Administrator Dashboard</h1>
    <p>System-wide overview — manage everything from the sidebar.</p>
  </div>
</div>

<div class="grid g4">
  <div class="card kpi"><span class="l">STUDENTS</span><span class="v"><?= (int) $stats['students'] ?></span></div>
  <div class="card kpi"><span class="l">INSTRUCTORS</span><span class="v"><?= (int) $stats['instructors'] ?></span></div>
  <div class="card kpi"><span class="l">SUBJECTS / TOPICS</span><span class="v"><?= (int) $stats['subjects'] ?> / <?= (int) $stats['topics'] ?></span></div>
  <div class="card kpi"><span class="l">QUESTIONS</span><span class="v"><?= (int) $stats['questions'] ?></span></div>
</div>

<div class="card" style="margin-top:16px">
  <h3>Recorded attempts</h3>
  <p style="font-size:14px;font-weight:800;color:var(--goldsoft);font-size:26px"><?= number_format((int) $stats['attempts']) ?></p>
  <p style="font-size:12.5px;color:var(--muted);margin-top:6px">
    Every student attempt is permanently recorded (data integrity NFR). Use the Question Bank and Exam
    Configuration pages to keep the platform ready for the cohort.
  </p>
</div>

<div class="grid g3" style="margin-top:16px">
  <a class="subj-card" href="<?= View::url('admin/users') ?>"><div class="ic">👥</div><b>Manage Users</b><div class="meta">Create instructors · deactivate · reset passwords</div></a>
  <a class="subj-card" href="<?= View::url('questionbank/index') ?>"><div class="ic">📝</div><b>Question Bank</b><div class="meta">Add, edit, validate and remove questions</div></a>
  <a class="subj-card" href="<?= View::url('admin/config') ?>"><div class="ic">⚙</div><b>Exam Configuration</b><div class="meta">Mock length, question count, session timeout</div></a>
</div>
