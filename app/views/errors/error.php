<?php /** 403 / 404 / 500 error page. */
use App\Core\View;
?>
<div class="error-wrap">
  <div class="error-card">
    <div class="crest-sm" style="margin:0 auto 18px"></div>
    <div class="code"><?= View::e((string) $code) ?></div>
    <h2 style="margin:8px 0 10px"><?= View::e($title) ?></h2>
    <p style="font-size:13.5px;color:rgba(255,255,255,.75);line-height:1.7"><?= View::e($text) ?></p>
    <div class="dash-sep"></div>
    <a class="btn btn-white" style="width:100%;text-decoration:none" href="<?= View::url('home') ?>">Back to dashboard <span>⟶</span></a>
  </div>
</div>
