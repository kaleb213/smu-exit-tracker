<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

/**
 * Entry point "/" — sends each role to its own dashboard
 * (use case: the user is redirected to the appropriate dashboard).
 */
final class HomeController extends Controller
{
    public function index(): void
    {
        if (!Auth::check()) {
            $this->redirect('auth/login');
        }
        match (Auth::role()) {
            'instructor' => $this->redirect('reporting/dashboard'),
            'admin'      => $this->redirect('admin/dashboard'),
            default      => $this->redirect('progress/dashboard'),
        };
    }
}
