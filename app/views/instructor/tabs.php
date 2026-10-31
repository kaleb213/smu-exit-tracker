<?php $active = $active ?? ''; ?>
<div style="display:flex;gap:8px;margin:18px 0;flex-wrap:wrap;">
    <a class="btn <?php echo $active === 'reports' ? 'btn-primary' : ''; ?>" href="<?php echo BASE_URL; ?>/instructor">Reports</a>
    <a class="btn <?php echo $active === 'trends' ? 'btn-primary' : ''; ?>" href="<?php echo BASE_URL; ?>/instructor/trends">Trends</a>
</div>