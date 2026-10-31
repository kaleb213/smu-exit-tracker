<div class="admin-tabs">
    <?php
    $adminTabs = [
        '' => 'Dashboard',
        'users' => 'Users',
        'subjects' => 'Subjects',
        'topics' => 'Topics',
        'questions' => 'Questions',
        'settings' => 'Exam Settings',
    ];
    $currentTab = isset($activeTab) ? $activeTab : '';
    foreach ($adminTabs as $key => $label): ?>
        <a class="admin-tab<?php echo $currentTab === $key ? ' active' : ''; ?>"
           href="<?php echo htmlspecialchars(admin_url('admin' . ($key !== '' ? '/' . $key : ''))); ?>"><?php echo htmlspecialchars($label); ?></a>
    <?php endforeach; ?>
</div>