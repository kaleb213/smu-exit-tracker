<?php $pageTitle = 'Dashboard'; require APP_PATH . '/views/layouts/header.php'; ?>

<h1 class="page-title">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
<p class="sub">Track your readiness for the national exit exam — practice by subject and topic.</p>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-num"><?php echo (int)$summary['attempts']; ?></div>
        <div class="stat-label">Total Attempts</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?php echo (float)$summary['avg_percentage']; ?>%</div>
        <div class="stat-label">Average Score</div>
    </div>
    <div class="stat-card">
        <div class="stat-num"><?php echo (float)$summary['best_percentage']; ?>%</div>
        <div class="stat-label">Best Score</div>
    </div>
</div>

<?php if ((int)$summary['attempts'] === 0): ?>
    <div class="alert alert-info">
        You have not taken any practice exam yet. Pick a subject below and start
        your first practice — it takes less than a minute.
    </div>
<?php endif; ?>

<div class="card">
    <h2 class="section-title">Choose a subject to practice</h2>
    <div class="grid-cards">
        <?php foreach ($subjects as $s): ?>
            <a class="subject-card" href="<?php echo BASE_URL; ?>/practice/subject/<?php echo (int)$s['id']; ?>">
                <h3><?php echo htmlspecialchars($s['name']); ?></h3>
                <p><?php echo htmlspecialchars($s['description'] ?? ''); ?></p>
                <span class="count-badge"><?php echo (int)$s['topic_count']; ?> topics</span>
            </a>
        <?php endforeach; ?>
        <?php if (!$subjects): ?>
            <p class="empty-note">No subjects have been added yet. An administrator will add them soon.</p>
        <?php endif; ?>
    </div>
</div>

<?php require APP_PATH . '/views/layouts/footer.php'; ?>