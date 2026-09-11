<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Request;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\Lookup;
use App\Models\Student;
use App\Models\User;

final class StudentController
{
    public function index(): void
    {
        if ((Auth::user()['role_slug'] ?? '') === 'student') {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Forbidden']);
            return;
        }

        Auth::requirePermission('students.view');
        $search = trim((string)Request::input('search', ''));
        $classId = trim((string)Request::input('class_id', ''));
        View::render('students/index', [
            'title' => 'Students',
            'students' => Student::paginate($search, $classId, max(1, (int)Request::input('page', 1))),
            'classes' => Lookup::classes(),
            'search' => $search,
            'classId' => $classId,
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission('students.manage');
        View::render('students/form', $this->formData(['admission_no' => Student::nextAdmissionNo()], 'Create Student'));
    }

    public function store(): void
    {
        Auth::requirePermission('students.manage');
        Csrf::verify();
        $data = $this->data();
        $errors = $this->validate($data);
        if ($errors) {
            View::render('students/form', $this->formData($data, 'Create Student', $errors));
            return;
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            // Initial password (default Student@12345 unless specified)
            $password = !empty($data['password']) ? $data['password'] : 'Student@12345';

            // 1. Create User account
            $userId = User::create([
                'role_id' => User::roleIdBySlug('student'),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $password,
                'status' => 'active',
            ]);

            // 2. Create Student record linked to user_id
            $data['user_id'] = $userId;
            if (empty($data['admission_no'])) {
                $data['admission_no'] = Student::nextAdmissionNo();
            }

            $studentId = Student::create($data);

            $pdo->commit();

            AuditLog::record(Auth::id(), 'create_student_unified', 'students', $studentId, [
                'admission_no' => $data['admission_no'],
                'user_id' => $userId
            ]);
            $_SESSION['flash_success'] = 'Student created successfully with user login account. (Default Password: Student@12345)';
            redirect('/students');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors['system'] = 'Failed to create student: ' . $e->getMessage();
            View::render('students/form', $this->formData($data, 'Create Student', $errors));
        }
    }

    public function edit(): void
    {
        Auth::requirePermission('students.manage');
        $student = Student::find((int)Request::input('id'));
        if (!$student) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Student not found']);
            return;
        }
        View::render('students/form', $this->formData($student, 'Edit Student'));
    }

    public function update(): void
    {
        Auth::requirePermission('students.manage');
        Csrf::verify();
        $id = (int)Request::input('id');
        $student = Student::find($id);
        if (!$student) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Student not found']);
            return;
        }

        $data = $this->data();
        $errors = $this->validate($data, $id);
        if ($errors) {
            $data['id'] = $id;
            View::render('students/form', $this->formData($data, 'Edit Student', $errors));
            return;
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $userId = $student['user_id'] ?? null;

            if ($userId) {
                // Update associated User account
                $userData = [
                    'role_id' => User::roleIdBySlug('student'),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'status' => 'active',
                ];
                if (!empty($data['password'])) {
                    $userData['password'] = $data['password'];
                }
                User::update((int)$userId, $userData);
            } else {
                // If orphan student without User, create User account now and link
                $password = !empty($data['password']) ? $data['password'] : 'Student@12345';
                $userId = User::create([
                    'role_id' => User::roleIdBySlug('student'),
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'] ?? null,
                    'password' => $password,
                    'status' => 'active',
                ]);
                $data['user_id'] = $userId;
            }

            Student::update($id, $data);

            $pdo->commit();

            AuditLog::record(Auth::id(), 'update_student_unified', 'students', $id, ['admission_no' => $data['admission_no']]);
            $_SESSION['flash_success'] = 'Student updated successfully.';
            redirect('/students');
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors['system'] = 'Failed to update student: ' . $e->getMessage();
            $data['id'] = $id;
            View::render('students/form', $this->formData($data, 'Edit Student', $errors));
        }
    }

    public function destroy(): void
    {
        Auth::requirePermission('students.manage');
        Csrf::verify();
        $id = (int)Request::input('id');
        Student::delete($id);
        AuditLog::record(Auth::id(), 'delete', 'students', $id);
        $_SESSION['flash_success'] = 'Student deleted successfully.';
        redirect('/students');
    }

    public function register(): void
    {
        if (Auth::check()) {
            redirect('/dashboard');
        }
        View::render('students/register', [
            'title' => 'Student Registration',
            'departments' => Lookup::departments(),
            'levels' => Lookup::levels(),
            'sessions' => Lookup::sessions(),
            'programmes' => Lookup::programmes(),
        ], 'auth');
    }

    public function profile(): void
    {
        Auth::requireLogin();
        $user = Auth::user();
        if (($user['role_slug'] ?? '') !== 'student') {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Forbidden']);
            return;
        }

        $student = Student::profileByUserId((int)$user['id']);
        if (!$student) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Forbidden']);
            return;
        }

        View::render('students/profile', ['title' => 'My Profile', 'student' => $student]);
    }

    public function storeRegistration(): void
    {
        Csrf::verify();
        $data = $this->registrationData();
        $errors = $this->registrationValidate($data);
        if ($errors !== []) {
            View::render('students/register', [
                'title' => 'Student Registration',
                'departments' => Lookup::departments(),
                'levels' => Lookup::levels(),
                'sessions' => Lookup::sessions(),
                'programmes' => Lookup::programmes(),
                'errors' => $errors,
                'student' => $data
            ], 'auth');
            return;
        }

        $userId = User::create([
            'role_id' => User::roleIdBySlug('student'),
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'status' => 'active',
        ]);

        $studentId = Student::create([
            'user_id' => $userId,
            'department_id' => $data['department_id'],
            'first_name' => $data['first_name'],
            'other_name' => $data['other_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'gender' => 'other',
            'programme' => $data['programme'],
            'level' => $data['level'],
            'academic_session' => $data['academic_session'],
            'status' => 'pending',
        ]);

        AuditLog::record($userId, 'register_pending', 'students', $studentId, ['email' => $data['email']]);
        $_SESSION['flash_success'] = 'Registration submitted successfully. Your account is currently awaiting administrative approval. You will be able to access the student portal after your registration has been approved.';
        redirect('/login');
    }

    public function pending(): void
    {
        Auth::requirePermission('students.manage');
        View::render('students/pending', [
            'title' => 'Pending Registrations',
            'students' => Student::pending(),
            'departments' => Lookup::departments(),
        ]);
    }

    public function approve(): void
    {
        Auth::requirePermission('students.manage');
        Csrf::verify();
        $id = (int)Request::input('id');
        $matriculationNo = trim((string)Request::input('matriculation_no'));

        try {
            Student::approve($id, $matriculationNo);
            AuditLog::record(Auth::id(), 'approve_student', 'students', $id, ['matriculation_no' => $matriculationNo]);
            $_SESSION['flash_success'] = 'Student approved successfully.';
        } catch (\Throwable $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
        }

        redirect('/students/pending');
    }

    public function reject(): void
    {
        Auth::requirePermission('students.manage');
        Csrf::verify();
        $id = (int)Request::input('id');
        try {
            Student::reject($id);
            AuditLog::record(Auth::id(), 'reject_student', 'students', $id);
            $_SESSION['flash_success'] = 'Student registration rejected.';
        } catch (\Throwable $exception) {
            $_SESSION['flash_error'] = $exception->getMessage();
        }
        redirect('/students/pending');
    }

    private function data(): array
    {
        $data = Request::only([
            'class_stream_id', 'department_id', 'programme', 'level', 'academic_session',
            'admission_no', 'matriculation_no', 'first_name', 'last_name', 'email', 'phone',
            'date_of_birth', 'gender', 'nationality', 'religion', 'medical_notes',
            'emergency_contact', 'previous_school', 'status', 'password'
        ]);
        $data['status'] = in_array($data['status'], ['active', 'transferred', 'graduated', 'suspended', 'inactive'], true) ? $data['status'] : 'active';
        $data['gender'] = in_array($data['gender'], ['male', 'female', 'other'], true) ? $data['gender'] : 'other';
        $data['parent_ids'] = $_POST['parent_ids'] ?? [];
        return $data;
    }

    private function registrationData(): array
    {
        $data = Request::only(['first_name', 'last_name', 'other_name', 'email', 'phone', 'department_id', 'programme', 'level', 'academic_session', 'password', 'password_confirmation']);
        $data['password'] = (string)($_POST['password'] ?? '');
        $data['password_confirmation'] = (string)($_POST['password_confirmation'] ?? '');
        return $data;
    }

    private function registrationValidate(array $data): array
    {
        $errors = [];
        foreach (['first_name', 'last_name', 'email', 'phone', 'department_id', 'programme', 'level', 'academic_session', 'password'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[$field] = 'Required.';
            }
        }
        if (($data['email'] ?? '') !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Use a valid email address.';
        }
        if (($data['password'] ?? '') !== '' && strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }
        if (($data['password'] ?? '') !== '' && ($data['password_confirmation'] ?? '') !== $data['password']) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }
        if (($data['email'] ?? '') !== '' && User::findByEmail($data['email']) !== null) {
            $errors['email'] = 'An account with this email already exists.';
        }
        return $errors;
    }

    private function validate(array $data, ?int $currentStudentId = null): array
    {
        $errors = [];
        foreach (['admission_no', 'first_name', 'last_name', 'gender', 'email'] as $field) {
            if (($data[$field] ?? '') === '') {
                $errors[$field] = 'Required.';
            }
        }

        if (($data['email'] ?? '') !== '' && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Use a valid email address.';
        }

        // Email uniqueness check across users table
        if (($data['email'] ?? '') !== '') {
            $existingUser = User::findByEmail($data['email']);
            if ($existingUser !== null) {
                $currentStudent = $currentStudentId ? Student::find($currentStudentId) : null;
                $currentUserId = $currentStudent['user_id'] ?? null;
                if ($currentUserId === null || (int)$existingUser['id'] !== (int)$currentUserId) {
                    $errors['email'] = 'An account with this email address already exists.';
                }
            }
        }

        // Admission No uniqueness check
        if (($data['admission_no'] ?? '') !== '') {
            $pdo = Database::connection();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE admission_no = :admission_no AND id != :id');
            $stmt->execute([
                'admission_no' => $data['admission_no'],
                'id' => $currentStudentId ?? 0
            ]);
            if ((int)$stmt->fetchColumn() > 0) {
                $errors['admission_no'] = 'This admission number is already in use.';
            }
        }

        // Matriculation No uniqueness check
        if (!empty($data['matriculation_no'])) {
            $pdo = Database::connection();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM students WHERE matriculation_no = :matriculation_no AND id != :id');
            $stmt->execute([
                'matriculation_no' => $data['matriculation_no'],
                'id' => $currentStudentId ?? 0
            ]);
            if ((int)$stmt->fetchColumn() > 0) {
                $errors['matriculation_no'] = 'This matriculation number is already in use.';
            }
        }

        return $errors;
    }

    private function formData(array $student, string $title, array $errors = []): array
    {
        return [
            'title' => $title,
            'student' => $student,
            'classes' => Lookup::classes(),
            'departments' => Lookup::departments(),
            'programmes' => Lookup::programmes(),
            'levels' => Lookup::levels(),
            'sessions' => Lookup::sessions(),
            'parents' => Lookup::parents(),
            'errors' => $errors
        ];
    }
}
