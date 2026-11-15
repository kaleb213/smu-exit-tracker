<?php require APP_PATH . '/views/layouts/header.php'; ?>
<?php
 $pct     = $stats['percentage'];
 $band    = $pct >= 75 ? 'band-high' : ($pct >= 50 ? 'band-mid' : 'band-low');
 $verdict = $pct >= 75
    ? 'Excellent work! You have a strong grasp of this topic. Keep it up.'
    : ($pct >= 50
        ? 'Good effort! Read the explanations below — especially for the questions you got wrong — then try again.'
        : 'Keep practicing! Study the explanations below carefully, then retake this topic to improve.');
?>
<p><a href="<?php echo BASE_URL; ?>/practice/subject/<?php echo (int)$attempt['subject_id']; ?>">&larr; Back to topics</a></p>

<div class="score-hero <?php echo $band; ?>">
  <div class="score-pct"><?php echo number_format($pct, 2); ?>%</div>
  <h2><?php echo htmlspecialchars($attempt['subject_name'] ?? ''); ?> — <?php echo htmlspecialchars($attempt['topic_name'] ?? ''); ?></h2>
  <p>You answered <?php echo $stats['correct']; ?> of <?php echo $stats['total']; ?> questions correctly.</p>
</div>

<div class="stat-grid">
  <div class="card"><h3><?php echo $stats['correct']; ?></h3><p>Correct</p></div>
  <div class="card"><h3><?php echo $stats['wrong']; ?></h3><p>Wrong</p></div>
  <div class="card"><h3><?php echo $stats['missed']; ?></h3><p>Not answered</p></div>
</div>

<div class="alert alert-info"><?php echo $verdict; ?></div>

<h2>Review your answers</h2>
<?php $i = 0; foreach ($review as $r): $i++;
    $sel = $r['selected'] ?? ($r['selected_option'] ?? null);
    $cor = $r['correct_option'];
    $state = ((int)$r['is_correct'] === 1) ? 'correct' : ((($sel === null || $sel === '') ? 'missed' : 'wrong'));
?>
  <div class="review-item <?php echo $state; ?>">
    <div class="q-num">Question <?php echo $i; ?> — <?php echo ucfirst($state); ?></div>
    <div class="q-text"><?php echo htmlspecialchars($r['question_text']); ?></div>
    <p>
      Your answer:
      <?php if ($sel): ?>
        <strong><?php echo $sel; ?>. <?php echo htmlspecialchars($r['option_' . strtolower($sel)] ?? ''); ?></strong>
      <?php else: ?>
        <strong>Not answered</strong>
      <?php endif; ?>
      <?php if ((int)$r['is_correct'] !== 1): ?>
        &nbsp;|&nbsp; Correct answer:
        <strong><?php echo $cor; ?>. <?php echo htmlspecialchars($r['option_' . strtolower($cor)] ?? ''); ?></strong>
      <?php endif; ?>
    </p>
    <?php if (!empty($r['explanation'])): ?>
      <div class="explanation"><strong>Explanation:</strong> <?php echo htmlspecialchars($r['explanation']); ?></div>
    <?php endif; ?>
  </div>
<?php endforeach; ?>

<p>
  <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/practice/take/<?php echo (int)$attempt['topic_id']; ?>">Retry this topic</a>
  <a class="btn" href="<?php echo BASE_URL; ?>/student">Back to dashboard</a>
</p>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>