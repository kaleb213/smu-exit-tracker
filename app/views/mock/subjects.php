<?php require APP_PATH . '/views/layouts/header.php'; ?>
<h1>Mock Exam Mode</h1>
<p class="muted">Simulates the timed conditions of the national exit exam. Questions are randomized, the countdown starts when you click Start, and the exam submits automatically at 00:00.</p>

<?php if (empty($subjects)): ?>
  <div class="alert alert-info">No subjects have been added yet.</div>
<?php else: ?>
  <div class="stat-grid">
    <?php foreach ($subjects as $s): ?>
      <div class="card subject-card">
        <h3><?php echo htmlspecialchars($s['name']); ?></h3>
        <?php if (!empty($s['description'])): ?>
          <p class="muted"><?php echo htmlspecialchars($s['description']); ?></p>
        <?php endif; ?>
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/mock/start/<?php echo (int)$s['id']; ?>">Start mock exam</a>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>