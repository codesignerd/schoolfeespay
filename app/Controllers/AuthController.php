<?php

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\User;

final class AuthController
{
    public function showLogin(): void
    {
        if (\App\Core\Auth::check()) {
            redirect('/dashboard');
        }
        View::render('auth/login', ['title' => 'Sign in'], 'auth');
    }

    public function login(): void
    {
        Csrf::verify();
        $email = trim((string)Request::input('email'));
        $password = (string)Request::input('password');
        $user = User::findByEmail($email);

        if (!$user || $user['status'] !== 'active' || !password_verify($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Invalid credentials or inactive account.';
            redirect('/login');
        }

        if (($user['role_slug'] ?? '') === 'student') {
            $studentStatus = $user['student_status'] ?? null;
            if ($studentStatus === 'pending') {
                $_SESSION['flash_error'] = 'Your registration is still awaiting administrative approval.';
                redirect('/login');
            }
            if ($studentStatus === 'rejected') {
                $_SESSION['flash_error'] = 'Your registration has been rejected. Please contact the administrator.';
                redirect('/login');
            }
            if ($studentStatus !== 'active') {
                $_SESSION['flash_error'] = 'Your account is not active yet.';
                redirect('/login');
            }
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'student_id' => $user['student_id'] ? (int)$user['student_id'] : null,
            'name' => trim($user['first_name'] . ' ' . $user['last_name']),
            'email' => $user['email'],
            'role' => $user['role_name'],
            'role_slug' => $user['role_slug'],
            'permissions' => User::permissions((int)$user['role_id']),
        ];

        User::touchLogin((int)$user['id']);
        AuditLog::record((int)$user['id'], 'login', 'users', (int)$user['id']);
        redirect('/dashboard');
    }

    public function logout(): void
    {
        if (\App\Core\Auth::check()) {
            AuditLog::record(\App\Core\Auth::id(), 'logout', 'users', \App\Core\Auth::id());
        }

        $_SESSION = [];
        session_destroy();
        redirect('/login');
    }
}
