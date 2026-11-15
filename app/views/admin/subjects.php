<?php
/** Admin — manage subjects (CRUD). */
use App\Core\View;
use App\Core\Session;
?>
<div class="page-head">
  <div>
    <h1>Admin — Subjects</h1>
    <p>Exit exam subject areas. Deleting a subject also deletes its topics (blocked while questions exist).</p>
  </div>
</div>

<div class="card">
  <h3>Add subject</h3>
  <form method="post" action="<?= View::url('admin/storesubject') ?>" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
    <div style="flex:1;min-width:200px"><label class="lbl" style="margin-top:0">NAME</label>
      <input class="box" type="text" name="name" placeholder="e.g. Data Structures & Algorithms" required></div>
    <div style="flex:2;min-width:260px"><label class="lbl" style="margin-top:0">DESCRIPTION</label>
      <input class="box" type="text" name="description" placeholder="Short description"></div>
    <button class="btn btn-primary btn-sm" type="submit">＋ Add subject</button>
  </form>
</div>

<div class="card" style="margin-top:16px">
  <div class="table-wrap">
    <table>
      <tr><th>ID</th><th>Subject</th><th>Description</th><th>Topics</th><th>Validated questions</th><th>Actions</th></tr>
      <?php foreach ($subjects as $s): ?>
        <tr>
          <td><?= (int) $s['id'] ?></td>
          <td><b><?= View::e($s['name']) ?></b></td>
          <td style="max-width:340px;font-size:12.5px;color:var(--muted)"><?= View::e($s['description']) ?></td>
          <td><?= (int) $s['topic_count'] ?></td>
          <td><?= (int) $s['question_count'] ?></td>
          <td class="actions-cell">
            <form method="post" action="<?= View::url('admin/updatesubject') ?>" style="display:inline-flex;gap:6px;align-items:center">
              <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
              <input type="text" name="name" value="<?= View::e($s['name']) ?>" required style="width:170px;padding:6px 8px;border-radius:8px;border:1px solid rgba(255,255,255,.28);background:rgba(255,255,255,.1);color:#fff;font-size:12px">
              <button class="btn btn-ghost btn-sm" type="submit">Save</button>
            </form>
            <form method="post" action="<?= View::url('admin/deletesubject/' . (int) $s['id']) ?>" style="display:inline" data-confirm="Delete this subject and all its topics?">
              <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
              <button class="btn btn-danger btn-sm" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
