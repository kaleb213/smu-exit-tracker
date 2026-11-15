<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\View;
use App\Models\User;

/** Shared account profile for students, instructors and administrators. */
final class ProfileController extends Controller
{
    public function index(): void
    {
        $this->requireRole('student', 'instructor', 'admin');
        $user = User::find(Auth::id());
        if ($user === null) {
            Auth::logout();
            $this->redirect('auth/login');
        }

        $this->view('account/profile', [
            'user' => $user,
            'active' => 'profile',
            'title' => 'My Profile',
            'saved' => Session::pullFlash('profileSaved'),
            'error' => Session::pullFlash('profileError'),
        ]);
    }

    public function uploadphoto(): void
    {
        $this->requireRole('student', 'instructor', 'admin');
        if (!isset($_FILES['profile_photo']) || !is_array($_FILES['profile_photo'])) {
            $this->fail('Choose a photo before uploading.');
        }

        $file = $_FILES['profile_photo'];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $message = match ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE)) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The photo is too large. Choose an image under 2 MB.',
                UPLOAD_ERR_NO_FILE => 'Choose a photo before uploading.',
                default => 'The photo could not be uploaded. Please try again.',
            };
            $this->fail($message);
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);
        if ($tmp === '' || !is_uploaded_file($tmp) || $size < 1 || $size > 2 * 1024 * 1024) {
            $this->fail('Choose a valid image smaller than 2 MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($allowed[$mime])) {
            $this->fail('Use a JPG, PNG or WebP image.');
        }

        $image = @getimagesize($tmp);
        if ($image === false || (int) $image[0] < 1 || (int) $image[1] < 1
            || (int) $image[0] > 4000 || (int) $image[1] > 4000) {
            $this->fail('The image dimensions are invalid or too large (maximum 4000 × 4000).');
        }

        $relativeDir = 'uploads/profiles';
        $absoluteDir = BASE_PATH . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true) && !is_dir($absoluteDir)) {
            $this->fail('The profile photo folder could not be created. Check folder permissions.');
        }

        $filename = 'user_' . Auth::id() . '_' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
        $relativePath = $relativeDir . '/' . $filename;
        if (!move_uploaded_file($tmp, $absoluteDir . '/' . $filename)) {
            $this->fail('The photo could not be saved. Check the uploads folder permissions.');
        }

        $oldPath = User::find(Auth::id())['profile_photo'] ?? null;
        User::updateProfilePhoto(Auth::id(), $relativePath);
        $sessionUser = Auth::user() ?? [];
        $sessionUser['profile_photo'] = $relativePath;
        Session::set('user', $sessionUser);
        $this->deleteStoredPhoto(is_string($oldPath) ? $oldPath : null);

        $this->flash('profileSaved', 'Your profile photo has been updated.');
        $this->redirect('profile/index');
    }

    public function removephoto(): void
    {
        $this->requireRole('student', 'instructor', 'admin');
        $oldPath = User::find(Auth::id())['profile_photo'] ?? null;
        User::updateProfilePhoto(Auth::id(), null);
        $sessionUser = Auth::user() ?? [];
        $sessionUser['profile_photo'] = null;
        Session::set('user', $sessionUser);
        $this->deleteStoredPhoto(is_string($oldPath) ? $oldPath : null);

        $this->flash('profileSaved', 'Your profile photo has been removed.');
        $this->redirect('profile/index');
    }


    /**
     * Self-service account closure. The account is deactivated rather than
     * hard-deleted so exam attempts and reporting history remain intact.
     */
    public function deactivateaccount(): void
    {
        $this->requireRole('student', 'instructor', 'admin');
        $password = (string) ($this->input('current_password') ?? '');
        $user = User::find(Auth::id());

        if ($user === null || $password === '' || !password_verify($password, (string) $user['password_hash'])) {
            $this->flash('profileError', 'Your password was not correct. Your account has not been changed.');
            $this->redirect('profile/index');
        }

        if (($user['role'] ?? '') === 'admin' && User::countActiveAdmins() <= 1) {
            $this->flash('profileError', 'This is the only active administrator. Create another administrator before closing this account.');
            $this->redirect('profile/index');
        }

        User::updateStatus(Auth::id(), 'deactivated');
        User::updatePresence(Auth::id(), 'offline');
        Auth::logout();
        header('Location: ' . View::url('auth/login'));
        exit;
    }

    /** Lightweight heartbeat; only the signed-in user's own presence is updated. */
    public function presence(): void
    {
        $this->requireRole('student', 'instructor', 'admin');
        $status = (string) ($_POST['status'] ?? 'online');
        if (!in_array($status, ['online', 'away', 'offline'], true)) {
            $this->json(['ok' => false, 'message' => 'Invalid presence state.'], 400);
        }
        User::updatePresence(Auth::id(), $status);
        $this->json(['ok' => true, 'status' => $status]);
    }

    private function fail(string $message): never
    {
        $this->flash('profileError', $message);
        $this->redirect('profile/index');
    }

    private function deleteStoredPhoto(?string $relativePath): void
    {
        if ($relativePath === null || !str_starts_with($relativePath, 'uploads/profiles/')) {
            return;
        }
        $filename = basename($relativePath);
        if ($filename === '' || !preg_match('/^user_[0-9]+_[a-f0-9]{24}\.(jpg|png|webp)$/', $filename)) {
            return;
        }
        $path = BASE_PATH . '/public/uploads/profiles/' . $filename;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
