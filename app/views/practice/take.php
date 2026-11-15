<?php require APP_PATH . '/views/layouts/header.php'; ?>
<p><a href="<?php echo BASE_URL; ?>/practice/subject/<?php echo (int)$topic['subject_id']; ?>">&larr; Back to topics</a></p>
<h1><?php echo htmlspecialchars($subject['name'] ?? ''); ?> — <?php echo htmlspecialchars($topic['name']); ?></h1>
<p class="muted">Answer all questions and submit. Your score and explanations appear instantly.</p>

<?php if (empty($questions)): ?>
  <div class="alert alert-info">There are no questions for this topic yet. Please pick another topic.</div>
<?php else: ?>
<form method="post" action="<?php echo BASE_URL; ?>/practice/submit/<?php echo (int)$topic['id']; ?>">
  <?php $i = 0; foreach ($questions as $q): $i++; ?>
    <div class="exam-q">
      <div class="q-num">Question <?php echo $i; ?> of <?php echo count($questions); ?></div>
      <div class="q-text"><?php echo htmlspecialchars($q['question_text']); ?></div>
      <?php foreach (['A', 'B', 'C', 'D'] as $letter):
            $optText = $q['option_' . strtolower($letter)] ?? null;
            if ($optText === null || $optText === '') { continue; } ?>
        <label class="option">
          <input type="radio" name="answers[<?php echo (int)$q['id']; ?>]" value="<?php echo $letter; ?>" required>
          <span><strong><?php echo $letter; ?>.</strong> <?php echo htmlspecialchars($optText); ?></span>
        </label>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-primary">Submit answers</button>
</form>
<?php endif; ?>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>