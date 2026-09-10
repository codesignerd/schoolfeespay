<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\Lookup;
use App\Models\SchoolClass;

final class ClassController
{
    public function index(): void
    {
        Auth::requirePermission('classes.view');
        View::render('classes/index', ['title' => 'Classes', 'classes' => SchoolClass::all()]);
    }

    public function create(): void
    {
        Auth::requirePermission('classes.manage');
        View::render('classes/form', ['title' => 'Create Class', 'class' => null, 'teachers' => Lookup::teachers(), 'errors' => []]);
    }

    public function store(): void
    {
        $this->save();
    }

    public function edit(): void
    {
        Auth::requirePermission('classes.manage');
        View::render('classes/form', ['title' => 'Edit Class', 'class' => SchoolClass::find((int)Request::input('id')), 'teachers' => Lookup::teachers(), 'errors' => []]);
    }

    public function update(): void
    {
        $this->save((int)Request::input('id'));
    }

    public function destroy(): void
    {
        Auth::requirePermission('classes.manage');
        Csrf::verify();
        $id = (int)Request::input('id');
        SchoolClass::delete($id);
        AuditLog::record(Auth::id(), 'delete', 'classes', $id);
        $_SESSION['flash_success'] = 'Class deleted successfully.';
        redirect('/classes');
    }

    private function save(?int $id = null): void
    {
        Auth::requirePermission('classes.manage');
        Csrf::verify();
        $data = Request::only(['name', 'stream', 'class_teacher_id', 'capacity', 'status']);
        $errors = [];
        foreach (['name', 'stream', 'capacity'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[$field] = 'Required.';
            }
        }
        if ($errors) {
            $data['id'] = $id;
            View::render('classes/form', ['title' => $id ? 'Edit Class' : 'Create Class', 'class' => $data, 'teachers' => Lookup::teachers(), 'errors' => $errors]);
            return;
        }
        $savedId = SchoolClass::save($data, $id);
        AuditLog::record(Auth::id(), $id ? 'update' : 'create', 'classes', $savedId);
        $_SESSION['flash_success'] = 'Class saved successfully.';
        redirect('/classes');
    }
}
