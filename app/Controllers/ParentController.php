<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\Guardian;

final class ParentController
{
    public function index(): void
    {
        Auth::requirePermission('students.view');
        $search = trim((string)Request::input('search', ''));
        View::render('parents/index', ['title' => 'Parents', 'parents' => Guardian::all($search), 'search' => $search]);
    }

    public function create(): void
    {
        Auth::requirePermission('students.manage');
        View::render('parents/form', ['title' => 'Create Parent', 'parent' => null, 'errors' => []]);
    }

    public function store(): void
    {
        $this->save();
    }

    public function edit(): void
    {
        Auth::requirePermission('students.manage');
        View::render('parents/form', ['title' => 'Edit Parent', 'parent' => Guardian::find((int)Request::input('id')), 'errors' => []]);
    }

    public function update(): void
    {
        $this->save((int)Request::input('id'));
    }

    public function destroy(): void
    {
        Auth::requirePermission('students.manage');
        Csrf::verify();
        $id = (int)Request::input('id');
        Guardian::delete($id);
        AuditLog::record(Auth::id(), 'delete', 'parents', $id);
        $_SESSION['flash_success'] = 'Parent deleted successfully.';
        redirect('/parents');
    }

    private function save(?int $id = null): void
    {
        Auth::requirePermission('students.manage');
        Csrf::verify();
        $data = Request::only(['first_name', 'last_name', 'relationship', 'email', 'phone', 'address']);
        $errors = [];
        foreach (['first_name', 'last_name', 'phone'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[$field] = 'Required.';
            }
        }
        if ($errors) {
            $data['id'] = $id;
            View::render('parents/form', ['title' => $id ? 'Edit Parent' : 'Create Parent', 'parent' => $data, 'errors' => $errors]);
            return;
        }
        $savedId = Guardian::save($data, $id);
        AuditLog::record(Auth::id(), $id ? 'update' : 'create', 'parents', $savedId);
        $_SESSION['flash_success'] = 'Parent saved successfully.';
        redirect('/parents');
    }
}
