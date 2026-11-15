<?php
/** Layout footer — closes the page and loads the JS bundle. */
use App\Core\View;
?>
  <?php if (empty($bare) && empty($data['bare'])): ?>
  <div class="footer-strip">
    Web-Based Exit Exam Preparation and Progress Tracker · <?= View::e(APP_INSTITUTION) ?> · <?= View::e(APP_DEPARTMENT) ?>
  </div>
  </div><!-- /.main -->
</div><!-- /.shell -->
  <?php else: ?>
</div><!-- /.screen -->
  <?php endif; ?>
<script src="<?= View::asset('js/charts.js') ?>"></script>
<script src="<?= View::asset('js/ajax.js') ?>"></script>
<script src="<?= View::asset('js/timer.js') ?>"></script>
<script src="<?= View::asset('js/theme.js') ?>"></script>
<script src="<?= View::asset('js/validation.js') ?>"></script>
<script src="<?= View::asset('js/presence.js') ?>"></script>
</body>
</html>
