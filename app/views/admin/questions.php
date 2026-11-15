<?php
/** Admin — question bank list (filters, validation toggle, delete, edit). */
use App\Core\View;
use App\Core\Session;
?>
<div class="page-head">
  <div>
    <h1>Admin — Question Bank</h1>
    <p><?= (int) $total ?> question(s) · add, edit, validate and remove questions organised by subject and topic.</p>
  </div>
  <a class="btn btn-white btn-sm" style="text-decoration:none" href="<?= View::url('questionbank/create') ?>">＋ Add question</a>
</div>

<div class="card">
  <form method="get" action="<?= View::url('questionbank/index') ?>" class="toolbar" style="margin-bottom:0">
    <select class="box" name="subject" onchange="window.location='<?= View::url('') ?>?r=questionbank/index&subject='+this.value">
      <option value="">All subjects</option>
      <?php foreach ($subjects as $s): ?>
        <option value="<?= (int) $s['id'] ?>" <?= $subjectId === (int) $s['id'] ? 'selected' : '' ?>><?= View::e($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <select class="box" name="topic" onchange="window.location='<?= View::url('') ?>?r=questionbank/index&topic='+this.value">
      <option value="">All topics</option>
      <?php foreach ($topics as $t): ?>
        <option value="<?= (int) $t['id'] ?>" <?= $topicId === (int) $t['id'] ? 'selected' : '' ?>><?= View::e($t['subject_name']) ?> › <?= View::e($t['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <input class="box" type="text" name="search" placeholder="🔍 Search question text…" value="<?= View::e((string) $search) ?>">
    <button class="btn btn-ghost btn-sm" type="submit">Filter</button>
  </form>
</div>

<div class="card" style="margin-top:16px">
  <div class="table-wrap">
    <table>
      <tr><th>#</th><th>Question</th><th>Subject / Topic</th><th>Difficulty</th><th>Validated</th><th>Actions</th></tr>
      <?php foreach ($rows as $q): ?>
        <tr>
          <td>Q<?= (int) $q['id'] ?></td>
          <td style="max-width:420px"><?= View::e(mb_strimwidth((string) $q['question_text'], 0, 120, '…')) ?></td>
          <td><span class="tag"><?= View::e($q['subject_name']) ?> · <?= View::e($q['topic_name']) ?></span></td>
          <td><span class="tag"><?= View::e(ucfirst((string) $q['difficulty'])) ?></span></td>
          <td><?= $q['is_validated'] == 1 ? '<span class="pill pill-ok">Yes</span>' : '<span class="pill pill-warn">Pending</span>' ?></td>
          <td class="actions-cell">
            <a class="a-edit" href="<?= View::url('questionbank/edit/' . (int) $q['id']) ?>">Edit</a>
            <form method="post" action="<?= View::url('questionbank/togglevalidate/' . (int) $q['id']) ?>" style="display:inline">
              <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
              <button class="btn btn-ghost btn-sm" type="submit"><?= $q['is_validated'] == 1 ? 'Un-validate' : 'Validate' ?></button>
            </form>
            <form method="post" action="<?= View::url('questionbank/delete/' . (int) $q['id']) ?>" style="display:inline" data-confirm="Delete this question?">
              <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">
              <button class="btn btn-danger btn-sm" type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if ($rows === []): ?>
        <tr><td colspan="6" style="color:var(--muted)">No questions match the filter.</td></tr>
      <?php endif; ?>
    </table>
  </div>
  <div class="pager">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
      <?php if ($p !== $page): ?>
        <a href="<?= View::url('questionbank/index/' . $p) ?>"><?= $p ?></a>
      <?php else: ?><span><b><?= $p ?></b></span><?php endif; ?>
    <?php endfor; ?>
  </div>
</div>
