<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\User;

/**
 * User Management subsystem.
 *
 * Business rules (documented):
 *  - students register with Name + Student ID + password;
 *    the Student ID is the unique login credential and duplicates
 *    are rejected;
 *  - instructor accounts are created ONLY by the admin;
 *  - passwords are stored hashed (bcrypt via password_hash).
 */
final class UserService
{
    /** @return string[] list of validation errors (empty = ok) */
    public static function validateRegistration(string $name, string $idNumber, string $password, string $confirm): array
    {
        $errors = [];
        if (mb_strlen($name) < 2) {
            $errors[] = 'Please enter your full name.';
        }
        if (mb_strlen($idNumber) < 4) {
            $errors[] = 'Please enter a valid Student ID (at least 4 characters).';
        }
        if (strlen($password) < MIN_PASSWORD_LENGTH) {
            $errors[] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters long.';
        }
        if ($password !== $confirm) {
            $errors[] = 'Passwords do not match.';
        }
        if ($errors === [] && User::idNumberExists($idNumber)) {
            $errors[] = 'This Student ID is already registered. Try signing in instead.';
        }
        return $errors;
    }

    public static function registerStudent(string $name, string $idNumber, string $password): int
    {
        return User::createStudent($name, $idNumber, password_hash($password, PASSWORD_DEFAULT));
    }

    /** Admin creates an instructor account (no public registration). */
    public static function createInstructor(string $name, string $idNumber, string $password): array
    {
        $errors = [];
        if (mb_strlen($name) < 2 || mb_strlen($idNumber) < 3) {
            $errors[] = 'Name and staff ID are required.';
        }
        if (strlen($password) < MIN_PASSWORD_LENGTH) {
            $errors[] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters long.';
        }
        if ($errors === [] && User::idNumberExists($idNumber)) {
            $errors[] = 'That ID number already exists.';
        }
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }
        $id = User::createInstructor($name, $idNumber, password_hash($password, PASSWORD_DEFAULT));
        return ['ok' => true, 'id' => $id];
    }

    /** Change own password after verifying the current one. */
    public static function changePassword(int $userId, string $current, string $new, string $confirm): array
    {
        $user = User::find($userId);
        if ($user === null || !password_verify($current, $user['password_hash'])) {
            return ['ok' => false, 'errors' => ['Current password is incorrect.']];
        }
        if (strlen($new) < MIN_PASSWORD_LENGTH) {
            return ['ok' => false, 'errors' => ['New password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.']];
        }
        if ($new !== $confirm) {
            return ['ok' => false, 'errors' => ['New passwords do not match.']];
        }
        User::updatePassword($userId, password_hash($new, PASSWORD_DEFAULT));
        return ['ok' => true];
    }

    public static function updateProfile(int $userId, string $name): array
    {
        if (mb_strlen($name) < 2) {
            return ['ok' => false, 'errors' => ['Name is too short.']];
        }
        User::updateName($userId, $name);
        $u = Session::get('user');
        $u['name'] = $name;
        Session::set('user', $u);
        return ['ok' => true];
    }

    /** Admin: reset an account password to a new value. */
    public static function resetPassword(int $userId, string $newPassword): array
    {
        if (strlen($newPassword) < MIN_PASSWORD_LENGTH) {
            return ['ok' => false, 'errors' => ['Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters.']];
        }
        User::updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
        return ['ok' => true];
    }

    /** Student self-delete (activity diagram: User Deletes Account). */
    public static function deleteOwnAccount(int $userId, string $password): array
    {
        $user = User::find($userId);
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return ['ok' => false, 'errors' => ['Password is incorrect. Account not deleted.']];
        }
        User::delete($userId);
        Session::destroy();
        return ['ok' => true];
    }
}
