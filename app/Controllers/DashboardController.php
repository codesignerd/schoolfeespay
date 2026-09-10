<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\Dashboard;
use App\Models\Fee;
use App\Models\Student;

final class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();

        if (($user = Auth::user()) && (($user['role_slug'] ?? '') === 'student')) {
            $student = Student::findByUserId((int)$user['id']);
            $summary = $student ? Fee::studentSummary((int)$student['id']) : ['total_fee' => 0.0, 'verified_amount' => 0.0, 'outstanding_balance' => 0.0, 'status' => 'Pending'];
            View::render('dashboard/student', [
                'title' => 'Student Dashboard',
                'student' => $student,
                'summary' => $summary,
                'payments' => $student ? Fee::studentPayments((int)$student['id']) : [],
            ]);
            return;
        }

        View::render('dashboard/index', [
            'title' => 'Dashboard',
            'metrics' => Dashboard::metrics(),
            'activities' => AuditLog::recent(),
        ]);
    }
}
