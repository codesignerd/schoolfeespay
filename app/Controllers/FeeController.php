<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\View;
use App\Models\AuditLog;
use App\Models\Fee;
use App\Models\Lookup;
use App\Models\Student;

final class FeeController
{
    public function index(): void
    {
        $user = Auth::user();
        if (($user['role_slug'] ?? '') === 'student') {
            $student = Student::findByUserId((int)$user['id']);
            if (!$student) {
                http_response_code(403);
                View::render('errors/403', ['title' => 'Forbidden']);
                return;
            }

            View::render('fees/student', [
                'title' => 'My Fees',
                'student' => $student,
                'invoices' => Fee::studentInvoices((int)$student['id']),
                'payments' => Fee::studentPayments((int)$student['id']),
                'summary' => Fee::studentSummary((int)$student['id']),
            ]);
            return;
        }

        Auth::requirePermission('finance.view');
        View::render('fees/index', [
            'title' => 'Fees',
            'invoices' => Fee::invoices(),
            'payments' => Fee::payments(),
            'pendingPayments' => Fee::pendingPayments(),
            'students' => Lookup::students(),
            'terms' => Lookup::terms(),
            'nextInvoiceNo' => Fee::nextInvoiceNo(),
            'nextReceiptNo' => Fee::nextReceiptNo(),
        ]);
    }

    public function invoice(): void
    {
        Auth::requirePermission('finance.manage');
        Csrf::verify();
        $data = Request::only(['student_id', 'term_id', 'invoice_no', 'description', 'amount', 'due_date']);
        $id = Fee::createInvoice($data);
        AuditLog::record(Auth::id(), 'create', 'fee_invoices', $id, ['invoice_no' => $data['invoice_no']]);
        $_SESSION['flash_success'] = 'Invoice created successfully.';
        redirect('/fees');
    }

    public function payment(): void
    {
        Csrf::verify();

        $user = Auth::user();
        if (($user['role_slug'] ?? '') === 'student') {
            $student = Student::findByUserId((int)$user['id']);
            if (!$student) {
                $_SESSION['flash_error'] = 'Your student profile is not available.';
                redirect('/dashboard');
                return;
            }

            $data = Request::only(['invoice_id', 'receipt_no', 'amount_paid', 'payment_method', 'payment_reference']);
            $invoice = Fee::invoiceForStudent((int)$student['id'], (int)($data['invoice_id'] ?? 0));

            if (!$invoice) {
                $_SESSION['flash_error'] = 'The selected invoice is invalid.';
                redirect('/fees');
                return;
            }

            $amount = (float)($data['amount_paid'] ?? 0);
            if ($amount <= 0 || !is_numeric($data['amount_paid'] ?? null)) {
                $_SESSION['flash_error'] = 'Enter a valid payment amount.';
                redirect('/fees');
                return;
            }

            if (trim($data['payment_reference'] ?? '') === '') {
                $_SESSION['flash_error'] = 'A payment reference is required.';
                redirect('/fees');
                return;
            }

            if ($amount > Fee::availableBalance((int)$invoice['id'])) {
                $_SESSION['flash_error'] = 'Payment amount exceeds the outstanding balance on this invoice.';
                redirect('/fees');
                return;
            }

            $data['payment_status'] = 'pending';
            $id = Fee::createPayment($data, null);
            AuditLog::record((int)$user['id'], 'student_payment_submitted', 'fee_payments', $id, ['receipt_no' => $data['receipt_no']]);
            $_SESSION['flash_success'] = 'Payment submitted successfully and is awaiting verification.';
            redirect('/fees');
            return;
        }

        Auth::requirePermission('finance.manage');
        $data = Request::only(['invoice_id', 'receipt_no', 'amount_paid', 'payment_method', 'payment_reference']);
        $invoice = Fee::invoice((int)$data['invoice_id']);
        $amount = (float)($data['amount_paid'] ?? 0);
        if (!$invoice || $amount <= 0 || !is_numeric($data['amount_paid'] ?? null)) {
            $_SESSION['flash_error'] = 'Enter a valid invoice and payment amount.';
            redirect('/fees');
            return;
        }
        if (!in_array($data['payment_method'], ['cash', 'bank', 'card'], true)) {
            $_SESSION['flash_error'] = 'Select a valid payment method.';
            redirect('/fees');
            return;
        }
        if (trim($data['payment_reference'] ?? '') === '') {
            $_SESSION['flash_error'] = 'A payment reference is required.';
            redirect('/fees');
            return;
        }
        if ($amount > Fee::availableBalance((int)$invoice['id'])) {
            $_SESSION['flash_error'] = 'Payment amount exceeds the outstanding balance on this invoice.';
            redirect('/fees');
            return;
        }
        $data['payment_status'] = 'pending';
        $id = Fee::createPayment($data, Auth::id());
        AuditLog::record(Auth::id(), 'create', 'fee_payments', $id, ['receipt_no' => $data['receipt_no']]);
        $_SESSION['flash_success'] = 'Payment recorded successfully and queued for verification.';
        redirect('/fees');
    }

    public function receipt(): void
    {
        Auth::requireLogin();
        $payment = Fee::payment((int)Request::input('id'));

        if (!$payment) {
            http_response_code(404);
            View::render('errors/404', ['title' => 'Receipt not found']);
            return;
        }

        $user = Auth::user();
        if (($user['role_slug'] ?? '') === 'student') {
            $student = Student::findByUserId((int)$user['id']);
            if (!$student || (int)$payment['student_id'] !== (int)$student['id']) {
                http_response_code(403);
                View::render('errors/403', ['title' => 'Forbidden']);
                return;
            }
        } elseif (!Auth::can('finance.view')) {
            http_response_code(403);
            View::render('errors/403', ['title' => 'Forbidden']);
            return;
        }

        View::render('fees/receipt', ['title' => 'Payment Receipt', 'payment' => $payment]);
    }

    public function verify(): void
    {
        Auth::requirePermission('finance.manage');
        Csrf::verify();

        $id = (int)Request::input('id');
        $decision = trim((string)Request::input('decision'));

        Fee::verifyPayment($id, Auth::id(), $decision);
        AuditLog::record(Auth::id(), 'verify_payment', 'fee_payments', $id, ['decision' => $decision]);
        $_SESSION['flash_success'] = $decision === 'verified' ? 'Payment verified successfully.' : 'Payment rejected.';

        redirect('/fees');
    }
}
