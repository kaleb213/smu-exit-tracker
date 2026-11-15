<?php
/** Admin — manage topics (CRUD). */
use App\Core\View;
use App\Core\Session;
?>
<div class="page-head">
  <div>
    <h1>Admin — Topics</h1>
    <p>Topics belong to subjects and organise the question bank for targeted practice.</p>
  </div>
</div>

<div class="card">
  <h3>Add topic</h3>
  <form method="post" action="<?= View::url('admin/storetopic') ?>" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
    <div style="min-width:220px"><label class="lbl" style="margin-top:0">SUBJECT</label>
      <select class="box" name="subject_id" required>
        <?php foreach ($subjects as $s): ?>
          <option value="<?= (int) $s['id'] ?>"><?= View::e($s['name']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div style="flex:1;min-width:200px"><label class="lbl" style="margin-top:0">TOPIC NAME</label>
      <input class="box" type="text" name="name" placeholder="e.g. Sorting & Searching" required></div>
    <button class="btn btn-primary btn-sm" type="submit">＋ Add topic</button>
  </form>
</div>

<div class="card" style="margin-top:16px">
  <div class="table-wrap">
    <table>
      <tr><th>ID</th><th>Topic</th><th>Subject</th><th>Validated questions</th><th>Actions</th></tr>
      <?php foreach ($topics as $t): ?>
        <tr>
          <td><?= (int) $t['id'] ?></td>
          <td><b><?= View::e($t['name']) ?></b></td>
          <td><?= View::e($t['subject_name']) ?></td>
          <td><?= (int) $t['question_count'] ?></td>
          <td class="actions-cell">
            <form method="post" action="<?= View::url('admin/updatetopic') ?>" style="display:inline-flex;gap:6px;align-items:center">
              <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
              <input type="hidden" name="id" value="<?= (int) $t['id'] ?>">
              <input type="text" name="name" value="<?= View::e($t['name']) ?>" required style="width:170px;padding:6px 8px;border-radius:8px;border:1px solid rgba(255,255,255,.28);background:rgba(255,255,255,.1);color:#fff;font-size:12px">
              <button class="btn btn-ghost btn-sm" type="submit">Save</button>
            </form>
            <form method="post" action="<?= View::url('admin/deletetopic/' . (int) $t['id']) ?>" style="display:inline" data-confirm="Delete this topic?">
              <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
              <button class="btn btn-danger btn-sm" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>
</div>
