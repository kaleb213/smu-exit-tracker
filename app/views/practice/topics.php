<?php require APP_PATH . '/views/layouts/header.php'; ?>
<p><a href="<?php echo BASE_URL; ?>/practice">&larr; All subjects</a></p>
<h1><?php echo htmlspecialchars($subject['name']); ?></h1>
<p class="muted">Choose a topic to practice.</p>

<?php if (empty($topics)): ?>
  <div class="alert alert-info">No topics have been added for this subject yet.</div>
<?php else: ?>
  <?php foreach ($topics as $t): ?>
    <div class="topic-row">
      <div>
        <strong><?php echo htmlspecialchars($t['name']); ?></strong>
        <?php if ((int)($t['question_count'] ?? 0) > 0): ?>
          <span class="muted"> — <?php echo (int)$t['question_count']; ?> question(s)</span>
        <?php else: ?>
          <span class="muted"> — no questions yet</span>
        <?php endif; ?>
      </div>
      <div>
        <?php if ((int)($t['question_count'] ?? 0) > 0): ?>
          <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/practice/take/<?php echo (int)$t['id']; ?>">Start practice</a>
        <?php else: ?>
          <span class="muted">Not available</span>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>