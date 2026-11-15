<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php $remaining = max(0, (int)$mock['deadline'] - time()); ?>
<style>
  .timer-bar { position: sticky; top: 10px; z-index: 50; display: flex; justify-content: space-between;
               align-items: center; background: #7f1d1d; color: #fff; padding: 12px 18px;
               border-radius: 10px; margin-bottom: 18px; }
  #countdown { font-size: 1.4rem; font-weight: 700; letter-spacing: 1px;
               background: rgba(255,255,255,.15); padding: 4px 14px; border-radius: 8px; }
  #countdown.time-warning { background: #fcd34d; color: #111; }
  #countdown.time-up { background: #ef4444; color: #fff; }
</style>

<p><a href="<?php echo BASE_URL; ?>/mock">&larr; Cancel and back to mock exams</a></p>

<div class="timer-bar">
  <span><strong>Mock Exam:</strong> <?php echo htmlspecialchars($mock['subject_name']); ?>
        — <?php echo count($questions); ?> questions</span>
  <span id="countdown" data-remaining="<?php echo $remaining; ?>">--:--</span>
</div>

<form method="post" action="<?php echo BASE_URL; ?>/mock/submit" id="exam-form"
      onsubmit="return confirm('Submit your mock exam now?');">
  <?php $i = 0; foreach ($questions as $q): $i++; ?>
    <div class="exam-q">
      <div class="q-num">Question <?php echo $i; ?> of <?php echo count($questions); ?></div>
      <div class="q-text"><?php echo htmlspecialchars($q['question_text']); ?></div>
      <?php foreach (['A', 'B', 'C', 'D'] as $letter):
            $optText = $q['option_' . strtolower($letter)] ?? null;
            if ($optText === null || $optText === '') { continue; } ?>
        <label class="option">
          <input type="radio" name="answers[<?php echo (int)$q['id']; ?>]" value="<?php echo $letter; ?>">
          <span><strong><?php echo $letter; ?>.</strong> <?php echo htmlspecialchars($optText); ?></span>
        </label>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-primary">Submit mock exam</button>
</form>

<script src="<?php echo PUBLIC_URL; ?>/js/exam.js"></script>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>