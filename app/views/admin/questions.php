<?php $pageTitle = 'Question Bank'; $activePage = 'admin'; require dirname(__DIR__) . '/layouts/header.php'; ?>
<?php require __DIR__ . '/tabs.php'; ?>

<div class="admin-toolbar">
    <form method="get" action="<?php echo htmlspecialchars(admin_url('admin/questions')); ?>" class="admin-filter">
        <select name="subject">
            <option value="">All subjects</option>
            <?php foreach ($subjects as $s): ?>
                <option value="<?php echo (int)$s['id']; ?>" <?php echo $fSubject === (int)$s['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$s['label']); ?></option>
            <?php endforeach; ?>
        </select>
        <select name="topic">
            <option value="">All topics</option>
            <?php foreach ($topics as $t): ?>
                <option value="<?php echo (int)$t['id']; ?>" <?php echo $fTopic === (int)$t['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars((string)$t['label']); ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="q" placeholder="Search question text..." value="<?php echo htmlspecialchars($fQ); ?>">
        <button type="submit" class="btn btn-sm">Filter</button>
        <a class="btn btn-sm btn-primary" href="<?php echo htmlspecialchars(admin_url('admin/question/0')); ?>">+ Add Question</a>
    </form>
</div>

<div class="admin-card">
    <h3>Questions (<?php echo (int)$total; ?> total)</h3>
    <table class="data-table">
        <thead><tr><th>ID</th><th>Question</th><th>Topic</th><th>Subject</th><th>Answer</th><th></th></tr></thead>
        <tbody>
        <?php if (empty($rows)): ?>
            <tr><td colspan="6" class="muted">No questions match this filter.</td></tr>
        <?php else: foreach ($rows as $r): ?>
            <tr>
                <td><?php echo (int)$r['id']; ?></td>
                <td><?php $txt = (string)$r['display_text']; echo htmlspecialchars(mb_strlen($txt) > 90 ? mb_substr($txt, 0, 90) . '...' : $txt); ?></td>
                <td><?php echo htmlspecialchars((string)($r['topic_name'] ?? '-')); ?></td>
                <td><?php echo htmlspecialchars((string)($r['subject_name'] ?? '-')); ?></td>
                <td><?php echo $answerCol ? htmlspecialchars((string)($r[$answerCol] ?? '-')) : '-'; ?></td>
                <td class="row-actions">
                    <a class="btn btn-sm" href="<?php echo htmlspecialchars(admin_url('admin/question/' . (int)$r['id'])); ?>">Edit</a>
                    <form method="post" action="<?php echo htmlspecialchars(admin_url('admin/question/' . (int)$r['id'])); ?>" onsubmit="return confirm('Delete this question?');">
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>

    <?php if ($pages > 1): ?>
    <div class="row-actions" style="margin-top:14px;">
        <?php
        $qs = ['subject' => $fSubject ?: '', 'topic' => $fTopic ?: '', 'q' => $fQ];
        for ($p = 1; $p <= $pages; $p++):
            $qs['page'] = $p;
            $link = admin_url('admin/questions') . '?' . http_build_query(array_filter($qs, function ($v) { return $v !== '' && $v !== null; }));
        ?>
            <a class="btn btn-sm<?php echo $p === (int)$page ? ' btn-primary' : ''; ?>" href="<?php echo htmlspecialchars($link); ?>"><?php echo $p; ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>