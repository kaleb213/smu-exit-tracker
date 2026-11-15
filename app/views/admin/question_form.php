<?php
/** Admin — add / edit question form. */
use App\Core\View;
use App\Core\Session;
$isEdit = !empty($q['id']);
?>
<div class="page-head">
  <div>
    <h1><?= $isEdit ? 'Edit question Q' . (int) $q['id'] : 'Add a question' ?></h1>
    <p>All fields are validated server-side; missing fields prevent saving (use case rule).</p>
  </div>
  <a class="btn btn-ghost btn-sm" style="text-decoration:none" href="<?= View::url('questionbank/index') ?>">← Back to bank</a>
</div>

<?php if ($errors !== []): ?>
  <div class="flash error">
    <?php foreach ($errors as $er): ?>• <?= View::e($er) ?><br><?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="card" style="max-width:840px">
  <form method="post" action="<?= View::url($isEdit ? 'questionbank/update/' . (int) $q['id'] : 'questionbank/store') ?>">
    <input type="hidden" name="_csrf" value="<?= View::e(Session::csrfToken()) ?>">

    <label class="lbl">SUBJECT / TOPIC *</label>
    <select class="box" name="topic_id" required>
      <option value="">— choose —</option>
      <?php foreach ($topics as $t): ?>
        <option value="<?= (int) $t['id'] ?>" <?= (int) $q['topic_id'] === (int) $t['id'] ? 'selected' : '' ?>>
          <?= View::e($t['subject_name']) ?> › <?= View::e($t['name']) ?> (<?= (int) $t['question_count'] ?> questions)
        </option>
      <?php endforeach; ?>
    </select>

    <label class="lbl">QUESTION TEXT *</label>
    <textarea class="box" name="question_text" required><?= View::e($q['question_text']) ?></textarea>

    <div class="grid g2" style="margin-top:6px">
      <div><label class="lbl">OPTION A *</label><input class="box" type="text" name="option_a" value="<?= View::e($q['option_a']) ?>" required></div>
      <div><label class="lbl">OPTION B *</label><input class="box" type="text" name="option_b" value="<?= View::e($q['option_b']) ?>" required></div>
      <div><label class="lbl">OPTION C *</label><input class="box" type="text" name="option_c" value="<?= View::e($q['option_c']) ?>" required></div>
      <div><label class="lbl">OPTION D *</label><input class="box" type="text" name="option_d" value="<?= View::e($q['option_d']) ?>" required></div>
    </div>

    <div class="grid g4" style="margin-top:6px">
      <div><label class="lbl">CORRECT OPTION *</label>
        <select class="box" name="correct_option">
          <?php foreach (['A', 'B', 'C', 'D'] as $L): ?>
            <option value="<?= $L ?>" <?= $q['correct_option'] === $L ? 'selected' : '' ?>><?= $L ?></option>
          <?php endforeach; ?>
        </select></div>
      <div><label class="lbl">DIFFICULTY</label>
        <select class="box" name="difficulty">
          <?php foreach (['easy', 'medium', 'hard'] as $d): ?>
            <option value="<?= $d ?>" <?= $q['difficulty'] === $d ? 'selected' : '' ?>><?= ucfirst($d) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div><label class="lbl">VALIDATED</label>
        <select class="box" name="is_validated">
          <option value="1" <?= (int) $q['is_validated'] === 1 ? 'selected' : '' ?>>Yes — visible to students</option>
          <option value="0" <?= (int) $q['is_validated'] !== 1 ? 'selected' : '' ?>>Pending review</option>
        </select></div>
    </div>

    <label class="lbl">EXPLANATION (shown in the immediate feedback and answer review)</label>
    <textarea class="box" name="explanation"><?= View::e($q['explanation']) ?></textarea>

    <button class="btn btn-white btn-sm" type="submit" style="margin-top:18px"><?= $isEdit ? 'Save changes' : 'Add question' ?> ⟶</button>
  </form>
</div>
