<?php
/** Shared profile-photo page for all authenticated roles. */
use App\Core\View;
$name = trim((string) ($user['name'] ?? 'User'));
$initial = mb_strtoupper(mb_substr($name !== '' ? $name : 'U', 0, 1));
$roleLabel = ucfirst((string) ($user['role'] ?? 'user'));
$photo = (string) ($user['profile_photo'] ?? '');
?>
<div class="page-head">
  <div>
    <h1>My Profile</h1>
    <p>Manage your account identity and profile photo.</p>
  </div>
</div>

<?php if (!empty($saved)): ?><div class="flash ok" role="status"><?= View::e($saved) ?></div><?php endif; ?>
<?php if (!empty($error)): ?><div class="flash error" role="alert"><?= View::e($error) ?></div><?php endif; ?>

<div class="account-profile-grid">
  <section class="card profile-photo-card">
    <div class="profile-photo-heading">
      <div>
        <span class="profile-eyebrow">ACCOUNT IDENTITY</span>
        <h2>Profile photo</h2>
        <p>Use a clear, professional photo so your account is easy to recognise.</p>
      </div>
      <span class="profile-role-pill"><?= View::e($roleLabel) ?></span>
    </div>

    <div class="profile-photo-stage">
      <div class="profile-photo-large" id="profile-photo-preview">
        <?php if ($photo !== ''): ?>
          <span class="profile-photo-fallback" aria-hidden="true"><?= View::e($initial) ?></span>
          <img src="<?= View::e(View::base() . '/' . ltrim($photo, '/')) ?>" alt="" aria-hidden="true" onerror="this.remove(); this.parentElement.classList.add('photo-load-failed');">
        <?php else: ?>
          <span><?= View::e($initial) ?></span>
        <?php endif; ?>
        <i class="profile-presence-dot" aria-hidden="true"></i>
      </div>
      <div class="profile-photo-copy">
        <strong><?= View::e($name) ?></strong>
        <span><?= View::e($user['id_number'] ?? '') ?></span>
        <small>JPG, PNG or WebP · Maximum 2 MB</small>
      </div>
    </div>

    <form class="profile-upload-form" method="post" enctype="multipart/form-data" action="<?= View::url('profile/uploadphoto') ?>">
      <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
      <label class="lbl" for="profile-photo-input">CHOOSE A PHOTO</label>
      <input class="box profile-file-input" id="profile-photo-input" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" required>
      <p class="profile-upload-hint">On a phone, you can choose an image from your gallery. Some devices also offer the camera.</p>
      <div class="profile-actions">
        <button class="btn btn-primary btn-sm" type="submit">Upload photo</button>
        <?php if ($photo !== ''): ?>
          <button class="btn btn-ghost btn-sm" type="submit" form="remove-profile-photo">Remove photo</button>
        <?php endif; ?>
      </div>
    </form>
    <?php if ($photo !== ''): ?>
      <form id="remove-profile-photo" method="post" action="<?= View::url('profile/removephoto') ?>" data-confirm="Remove your current profile photo?">
        <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
      </form>
    <?php endif; ?>
  </section>

  <section class="card profile-account-card">
    <span class="profile-eyebrow">ACCOUNT INFORMATION</span>
    <h2>Account details</h2>
    <div class="profile-detail-row"><span>Full name</span><strong><?= View::e($name) ?></strong></div>
    <div class="profile-detail-row"><span>University ID</span><strong><?= View::e($user['id_number'] ?? '') ?></strong></div>
    <div class="profile-detail-row"><span>Role</span><strong><?= View::e($roleLabel) ?></strong></div>
    <div class="profile-detail-row"><span>Member since</span><strong><?= View::e(substr((string) ($user['created_at'] ?? ''), 0, 10)) ?></strong></div>
    <div class="profile-security-note">
      <span class="profile-security-icon" aria-hidden="true">✓</span>
      <p>Your photo is linked to your signed-in account and will appear on your profile on both desktop and mobile.</p>
    </div>
    <?php if (($user['role'] ?? '') === 'student'): ?>
      <a class="link profile-history-link" href="<?= View::url('progress/profile') ?>">Open account activity, password and results settings →</a>
    <?php endif; ?>

    <section class="account-danger-zone" aria-labelledby="account-close-title">
      <span class="profile-eyebrow">ACCOUNT CONTROL</span>
      <h3 id="account-close-title">Delete my account</h3>
      <p>Closing your account deactivates sign-in access. Your exam attempts and university reporting history are retained for academic records. This action signs you out.</p>
      <form method="post" action="<?= View::url('profile/deactivateaccount') ?>" class="account-close-form" data-confirm="Are you sure you want to deactivate your account? You will be signed out and cannot sign in again unless an administrator reactivates it.">
        <input type="hidden" name="_csrf" value="<?= View::e(\App\Core\Session::csrfToken()) ?>">
        <label class="lbl" for="close-account-password">Confirm with your current password</label>
        <input class="box" id="close-account-password" type="password" name="current_password" autocomplete="current-password" required>
        <button class="btn btn-danger btn-sm" type="submit">Deactivate my account</button>
      </form>
    </section>
  </section>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('profile-photo-input');
  const preview = document.getElementById('profile-photo-preview');
  if (!input || !preview) return;
  input.addEventListener('change', function () {
    const file = input.files && input.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    const old = preview.querySelector('img');
    if (old) URL.revokeObjectURL(old.src);
    const image = document.createElement('img');
    image.alt = 'Selected profile photo preview';
    image.src = URL.createObjectURL(file);
    const initial = preview.querySelector('span');
    if (initial) initial.remove();
    if (old) old.remove();
    preview.insertBefore(image, preview.firstChild);
  });
});
</script>
