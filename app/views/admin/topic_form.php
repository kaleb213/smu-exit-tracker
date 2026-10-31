<?php
 $pageTitle = $topic ? 'Edit Topic' : 'Add Topic'; $activePage = 'admin';
require APP_PATH . '/views/layouts/header.php';
 $active = 'topics'; require APP_PATH . '/views/admin/tabs.php';
?>
<h1><?php echo $topic ? 'Edit Topic' : 'Add Topic'; ?></h1>

<div class="card" style="padding:20px;margin:16px 0;max-width:560px;">
    <form method="post" action="<?php echo BASE_URL; ?>/admin/topicSave">
        <input type="hidden" name="id" value="<?php echo (int)($topic['id'] ?? 0); ?>">

        <div class="form-group">
            <label>Subject</label>
            <select name="subject_id">
                <?php $cur = (int)($topic['subject_id'] ?? 0); ?>
                <?php foreach ($subjects as $s): ?>
                    <option value="<?php echo (int)$s['id']; ?>" <?php echo $cur === (int)$s['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($s['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>Topic name</label>
            <input type="text" name="name" required value="<?php echo htmlspecialchars($topic['name'] ?? ''); ?>">
        </div>

        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary">Save topic</button>
            <a class="btn" href="<?php echo BASE_URL; ?>/admin/topics">Cancel</a>
        </div>
    </form>
</div>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>