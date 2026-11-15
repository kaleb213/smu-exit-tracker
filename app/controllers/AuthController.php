<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Services\UserService;

/**
 * Authentication controller — login, register, logout.
 * Public pages (no session required) per the access-control flow.
 */
final class AuthController extends Controller
{
    public function isPublic(): bool
    {
        return true;
    }

    /* ---------- login ---------- */

    public function index(): void
    {
        $this->login();
    }

    public function login(): void
    {
        if (Auth::check()) {
            $this->redirect('home');
        }
        if (Session::wasExpired()) {
            $this->flash('error', 'Your session expired after 30 minutes of inactivity. Please sign in again.');
            Session::clearExpired();
        }
        $this->view('auth/login', [
            'error'  => Session::pullFlash('error'),
            'notice' => Session::pullFlash('notice'),
            'bare'   => true,
            'active' => 'login',
        ]);
    }

    public function dologin(): void
    {
        $idNumber = (string) ($this->input('id_number') ?? '');
        $password = (string) ($this->input('password') ?? '');

        if ($idNumber === '' || $password === '') {
            $this->flash('error', 'Please enter your ID number and password.');
            $this->redirect('auth/login');
        }

        if (Auth::attempt($idNumber, $password)) {
            $this->redirect('home');
        }
        if (Session::get('user') === null) {
            $this->flash('error', 'Invalid ID number or password. Please try again.');
        }
        $this->redirect('auth/login');
    }

    /* ---------- register (students only — business rule) ---------- */

    public function register(): void
    {
        if (Auth::check()) {
            $this->redirect('home');
        }
        $this->view('auth/register', [
            'errors' => [],
            'old'    => ['name' => '', 'id_number' => ''],
            'bare'   => true,
        ]);
    }

    public function doregister(): void
    {
        $name    = (string) ($this->input('name') ?? '');
        $idNumber = (string) ($this->input('id_number') ?? '');
        $password = (string) ($this->input('password') ?? '');
        $confirm  = (string) ($this->input('password_confirm') ?? '');

        $errors = UserService::validateRegistration($name, $idNumber, $password, $confirm);

        if ($errors !== []) {
            $this->view('auth/register', [
                'errors' => $errors,
                'old'    => ['name' => $name, 'id_number' => $idNumber],
                'bare'   => true,
            ]);
            return;
        }

        UserService::registerStudent($name, $idNumber, $password);
        $this->flash('notice', 'Account created successfully. You can now sign in.');
        $this->redirect('auth/login');
    }

    /* ---------- logout ---------- */

    public function logout(): void
    {
        Auth::logout();
        header('Location: ' . \App\Core\View::url('auth/login'));
        exit;
    }
}
