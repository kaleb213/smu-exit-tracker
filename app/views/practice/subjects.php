<?php require APP_PATH . '/views/layouts/header.php'; ?>
<h1>Practice Mode</h1>
<p class="muted">Pick a subject, then a topic. You get instant feedback with explanations after every test.</p>

<?php if (empty($subjects)): ?>
  <div class="alert alert-info">No subjects have been added yet. An administrator will add them soon.</div>
<?php else: ?>
  <div class="stat-grid">
    <?php foreach ($subjects as $s): ?>
      <div class="card subject-card">
        <h3><?php echo htmlspecialchars($s['name']); ?></h3>
        <?php if (!empty($s['description'])): ?>
          <p class="muted"><?php echo htmlspecialchars($s['description']); ?></p>
        <?php endif; ?>
        <p class="muted"><?php echo (int)($s['topic_count'] ?? 0); ?> topic(s)</p>
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/practice/subject/<?php echo (int)$s['id']; ?>">Browse topics</a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>