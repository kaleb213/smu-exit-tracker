<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Authentication & role-based authorization.
 *
 * Business rules implemented here:
 *  - login with ID number + password (all three roles)
 *  - deactivated accounts cannot log in
 *  - role check drives the access-control flow:
 *    no session -> redirect to login; wrong role -> 403 Forbidden.
 */
final class Auth
{
    public static function attempt(string $idNumber, string $password): bool
    {
        $user = User::findByLogin($idNumber);

        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        if ($user['status'] !== 'active') {
            Session::flash('error', 'This account has been deactivated. Contact the administrator.');
            return false;
        }

        session_regenerate_id(true); // prevent session fixation

        Session::set('user', [
            'id'        => (int) $user['id'],
            'name'      => $user['name'],
            'id_number' => $user['id_number'],
            'role'      => $user['role'],
            'profile_photo' => $user['profile_photo'] ?? null,
        ]);
        User::touchLogin((int) $user['id']);
        User::updatePresence((int) $user['id'], 'online');
        return true;
    }

    public static function check(): bool
    {
        return Session::get('user') !== null;
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function id(): int
    {
        return (int) (Session::get('user')['id'] ?? 0);
    }

    public static function role(): ?string
    {
        return Session::get('user')['role'] ?? null;
    }

    public static function logout(): void
    {
        if (self::check()) {
            User::updatePresence(self::id(), 'offline');
        }
        Session::destroy();
    }
}
