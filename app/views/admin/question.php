<?php $pageTitle = isset($q) && $q ? 'Edit Question' : 'Add Question'; $activePage = 'admin'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<?php require __DIR__ . '/tabs.php'; ?>

<div class="admin-card admin-narrow">
    <h3><?php echo isset($q) && $q ? 'Edit Question #' . (int)$q['id'] : 'Add New Question'; ?></h3>
    <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/question/' . (int)($q['id'] ?? 0))); ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?php echo (int)($q['id'] ?? 0); ?>">

        <?php if ($meta['topic'] && !empty($topics)): ?>
            <label>Topic
                <select name="f_<?php echo htmlspecialchars($meta['topic']); ?>" required>
                    <option value="">- none -</option>
                    <?php foreach ($topics as $t): ?>
                        <option value="<?php echo (int)$t['id']; ?>" <?php echo (isset($q) && $q && isset($q[$meta['topic']]) && (int)$q[$meta['topic']] === (int)$t['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$t['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endif; ?>

        <?php if ($meta['subject'] && !empty($subjects)): ?>
            <label>Subject
                <select name="f_<?php echo htmlspecialchars($meta['subject']); ?>">
                    <option value="">- none -</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?php echo (int)$s['id']; ?>" <?php echo (isset($q) && $q && isset($q[$meta['subject']]) && (int)$q[$meta['subject']] === (int)$s['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$s['label']); ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endif; ?>

        <label>Question text
            <textarea name="f_<?php echo htmlspecialchars($meta['text']); ?>" rows="4" required><?php echo htmlspecialchars((string)($q[$meta['text']] ?? '')); ?></textarea>
        </label>

        <?php if (!empty($meta['options'])): $letters = ['A', 'B', 'C', 'D']; foreach ($meta['options'] as $i => $oc): ?>
            <label>Option <?php echo $letters[$i]; ?>
                <input type="text" name="f_<?php echo htmlspecialchars($oc); ?>" value="<?php echo htmlspecialchars((string)($q[$oc] ?? '')); ?>">
            </label>
        <?php endforeach; endif; ?>

        <?php if ($meta['answer']): $cur = (string)($q[$meta['answer']] ?? ''); $seen = false; ?>
            <label>Correct answer
                <select name="f_<?php echo htmlspecialchars($meta['answer']); ?>">
                    <?php foreach ($answerChoices as $c): if ($cur !== '' && (string)$c === $cur) $seen = true; ?>
                        <option value="<?php echo htmlspecialchars((string)$c); ?>" <?php echo ($cur !== '' && (string)$c === $cur) ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$c); ?></option>
                    <?php endforeach; ?>
                    <?php if ($cur !== '' && !$seen): ?>
                        <option value="<?php echo htmlspecialchars($cur); ?>" selected><?php echo htmlspecialchars($cur); ?> (current)</option>
                    <?php endif; ?>
                </select>
            </label>
        <?php endif; ?>

        <?php if ($meta['explain']): ?>
            <label>Explanation
                <textarea name="f_<?php echo htmlspecialchars($meta['explain']); ?>" rows="3"><?php echo htmlspecialchars((string)($q[$meta['explain']] ?? '')); ?></textarea>
            </label>
        <?php endif; ?>

        <?php if ($meta['diff']): ?>
            <label>Difficulty
                <input type="text" name="f_<?php echo htmlspecialchars($meta['diff']); ?>" value="<?php echo htmlspecialchars((string)($q[$meta['diff']] ?? '')); ?>" placeholder="easy / medium / hard">
            </label>
        <?php endif; ?>

        <?php if ($meta['mock'] && $meta['mock_int']): ?>
            <label class="check">
                <input type="checkbox" name="f_<?php echo htmlspecialchars($meta['mock']); ?>" value="1" <?php echo (isset($q) && $q && !empty($q[$meta['mock']])) ? 'checked' : ''; ?>>
                Mock exam only
            </label>
        <?php endif; ?>

        <div class="row-actions">
            <button type="submit" class="btn btn-primary"><?php echo isset($q) && $q ? 'Save Question' : 'Add Question'; ?></button>
            <a class="btn" href="<?php echo htmlspecialchars(admin_url('admin/questions')); ?>">Cancel</a>
        </div>
    </form>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>