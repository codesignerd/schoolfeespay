<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Validator;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\User;

final class UserController
{
    public function index(): void
    {
        Auth::requirePermission('users.view');
        $search = trim((string)Request::input('search', ''));
        $role = trim((string)Request::input('role_id', ''));
        $page = max(1, (int)Request::input('page', 1));

        View::render('users/index', [
            'title' => 'Users',
            'users' => User::paginate($search, $role, $page),
            'roles' => User::roles(),
            'search' => $search,
            'roleId' => $role,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('users.manage');
        View::render('users/form', [
            'title' => 'Create User',
            'roles' => User::roles(),
            'user' => null,
            'errors' => [],
        ]);
    }

    public function store(): void
    {
        Auth::requirePermission('users.manage');
        Csrf::verify();
        $data = $this->data();
        $errors = Validator::user($data, true);

        if ($errors !== []) {
            View::render('users/form', ['title' => 'Create User', 'roles' => User::roles(), 'user' => $data, 'errors' => $errors]);
            return;
        }

        $id = User::create($data);
        AuditLog::record(Auth::id(), 'create', 'users', $id, ['email' => $data['email']]);
        $_SESSION['flash_success'] = 'User created successfully.';
        redirect('/users');
    }

    public function edit(): void
    {
        Auth::requirePermission('users.manage');
        $user = User::find((int)Request::input('id'));
        if (!$user) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'User not found']);
            return;
        }

        View::render('users/form', [
            'title' => 'Edit User',
            'roles' => User::roles(),
            'user' => $user,
            'errors' => [],
        ]);
    }

    public function update(): void
    {
        Auth::requirePermission('users.manage');
        Csrf::verify();
        $id = (int)Request::input('id');
        $data = $this->data();
        $errors = Validator::user($data, false);

        if ($errors !== []) {
            $data['id'] = $id;
            View::render('users/form', ['title' => 'Edit User', 'roles' => User::roles(), 'user' => $data, 'errors' => $errors]);
            return;
        }

        User::update($id, $data);
        AuditLog::record(Auth::id(), 'update', 'users', $id, ['email' => $data['email']]);
        $_SESSION['flash_success'] = 'User updated successfully.';
        redirect('/users');
    }

    public function destroy(): void
    {
        Auth::requirePermission('users.manage');
        Csrf::verify();
        $id = (int)Request::input('id');

        if ($id === Auth::id()) {
            $_SESSION['flash_error'] = 'You cannot delete your own account.';
            redirect('/users');
        }

        User::delete($id);
        AuditLog::record(Auth::id(), 'delete', 'users', $id);
        $_SESSION['flash_success'] = 'User deleted successfully.';
        redirect('/users');
    }

    private function data(): array
    {
        $data = Request::only(['first_name', 'last_name', 'email', 'phone', 'password', 'role_id', 'status']);
        $data['status'] = in_array($data['status'], ['active', 'inactive', 'suspended'], true) ? $data['status'] : 'active';
        return $data;
    }
}
