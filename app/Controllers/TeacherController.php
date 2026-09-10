<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\Lookup;
use App\Models\Teacher;

final class TeacherController
{
    public function index(): void
    {
        Auth::requirePermission('teachers.view');
        $search = trim((string)Request::input('search', ''));
        View::render('teachers/index', ['title' => 'Teachers', 'teachers' => Teacher::all($search), 'search' => $search]);
    }

    public function create(): void
    {
        Auth::requirePermission('teachers.manage');
        View::render('teachers/form', ['title' => 'Create Teacher', 'teacher' => null, 'departments' => Lookup::departments(), 'errors' => []]);
    }

    public function store(): void
    {
        $this->save();
    }

    public function edit(): void
    {
        Auth::requirePermission('teachers.manage');
        $teacher = Teacher::find((int)Request::input('id'));
        View::render('teachers/form', ['title' => 'Edit Teacher', 'teacher' => $teacher, 'departments' => Lookup::departments(), 'errors' => []]);
    }

    public function update(): void
    {
        $this->save((int)Request::input('id'));
    }

    public function destroy(): void
    {
        Auth::requirePermission('teachers.manage');
        Csrf::verify();
        $id = (int)Request::input('id');
        Teacher::delete($id);
        AuditLog::record(Auth::id(), 'delete', 'teachers', $id);
        $_SESSION['flash_success'] = 'Teacher deleted successfully.';
        redirect('/teachers');
    }

    private function save(?int $id = null): void
    {
        Auth::requirePermission('teachers.manage');
        Csrf::verify();
        $data = Request::only(['department_id', 'staff_no', 'first_name', 'last_name', 'email', 'phone', 'gender', 'qualification', 'hire_date', 'status']);
        $errors = [];
        foreach (['staff_no', 'first_name', 'last_name', 'email', 'gender'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[$field] = 'Required.';
            }
        }
        if (($data['email'] ?? '') !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Use a valid email.';
        }
        if ($errors) {
            $data['id'] = $id;
            View::render('teachers/form', ['title' => $id ? 'Edit Teacher' : 'Create Teacher', 'teacher' => $data, 'departments' => Lookup::departments(), 'errors' => $errors]);
            return;
        }
        $savedId = Teacher::save($data, $id);
        AuditLog::record(Auth::id(), $id ? 'update' : 'create', 'teachers', $savedId, ['staff_no' => $data['staff_no']]);
        $_SESSION['flash_success'] = 'Teacher saved successfully.';
        redirect('/teachers');
    }
}
