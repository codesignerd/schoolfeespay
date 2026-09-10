<?php

namespace App\Models;

use App\Core\Database;

final class Fee
{
    public static function invoices(): array
    {
        return Database::connection()->query(
            'SELECT fee_invoices.*, students.admission_no, CONCAT(students.first_name, " ", students.last_name) AS student_name,
                    terms.name AS term_name
             FROM fee_invoices
             JOIN students ON students.id = fee_invoices.student_id
             LEFT JOIN terms ON terms.id = fee_invoices.term_id
             ORDER BY fee_invoices.created_at DESC'
        )->fetchAll();
    }

    public static function payments(): array
    {
        return Database::connection()->query(
            'SELECT fee_payments.*, fee_invoices.invoice_no, CONCAT(students.first_name, " ", students.last_name) AS student_name
             FROM fee_payments
             JOIN fee_invoices ON fee_invoices.id = fee_payments.invoice_id
             JOIN students ON students.id = fee_invoices.student_id
             ORDER BY fee_payments.paid_at DESC
             LIMIT 50'
        )->fetchAll();
    }

    public static function createInvoice(array $data): int
    {
        Database::connection()->prepare(
            'INSERT INTO fee_invoices (student_id, term_id, invoice_no, description, amount, balance, due_date, status)
             VALUES (:student_id, :term_id, :invoice_no, :description, :amount, :balance, :due_date, "issued")'
        )->execute([
            'student_id' => $data['student_id'],
            'term_id' => $data['term_id'] ?: null,
            'invoice_no' => $data['invoice_no'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'balance' => $data['amount'],
            'due_date' => $data['due_date'],
        ]);
        return (int)Database::connection()->lastInsertId();
    }

    public static function createPayment(array $data, ?int $userId): int
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        $status = isset($data['payment_status']) && in_array($data['payment_status'], ['pending', 'verified', 'rejected'], true)
            ? $data['payment_status']
            : 'pending';

        $pdo->prepare(
            'INSERT INTO fee_payments (invoice_id, receipt_no, amount_paid, payment_method, payment_reference, payment_status, received_by)
             VALUES (:invoice_id, :receipt_no, :amount_paid, :payment_method, :payment_reference, :payment_status, :received_by)'
        )->execute([
            'invoice_id' => $data['invoice_id'],
            'receipt_no' => $data['receipt_no'],
            'amount_paid' => $data['amount_paid'],
            'payment_method' => $data['payment_method'],
            'payment_reference' => $data['payment_reference'] ?: null,
            'payment_status' => $status,
            'received_by' => $userId,
        ]);
        $id = (int)$pdo->lastInsertId();

        if ($status === 'verified') {
            $pdo->prepare(
                'UPDATE fee_invoices
                 SET balance = GREATEST(balance - :amount_paid, 0),
                     status = CASE
                        WHEN GREATEST(balance - :amount_for_status_zero, 0) = 0 THEN "paid"
                        WHEN GREATEST(balance - :amount_for_status_compare, 0) < amount THEN "part_paid"
                        ELSE status
                     END
                 WHERE id = :invoice_id'
            )->execute(['amount_paid' => $data['amount_paid'], 'amount_for_status_zero' => $data['amount_paid'], 'amount_for_status_compare' => $data['amount_paid'], 'invoice_id' => $data['invoice_id']]);
        }

        $pdo->commit();
        return $id;
    }

    public static function studentInvoices(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT *
             FROM fee_invoices
             WHERE student_id = :student_id
             ORDER BY created_at DESC'
        );
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    public static function invoiceForStudent(int $studentId, int $invoiceId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT *
             FROM fee_invoices
             WHERE id = :invoice_id AND student_id = :student_id
             LIMIT 1'
        );
        $stmt->execute(['invoice_id' => $invoiceId, 'student_id' => $studentId]);
        return $stmt->fetch() ?: null;
    }

    public static function invoice(int $invoiceId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM fee_invoices WHERE id = :invoice_id LIMIT 1');
        $stmt->execute(['invoice_id' => $invoiceId]);
        return $stmt->fetch() ?: null;
    }

    public static function availableBalance(int $invoiceId): float
    {
        $stmt = Database::connection()->prepare(
            'SELECT fi.balance - COALESCE(SUM(CASE WHEN fp.payment_status = "pending" THEN fp.amount_paid ELSE 0 END), 0)
             FROM fee_invoices fi
             LEFT JOIN fee_payments fp ON fp.invoice_id = fi.id
             WHERE fi.id = :invoice_id
             GROUP BY fi.id, fi.balance'
        );
        $stmt->execute(['invoice_id' => $invoiceId]);
        return max(0.0, (float)$stmt->fetchColumn());
    }

    public static function studentSummary(int $studentId): array
    {
        $pdo = Database::connection();
        $studentId = (int)$studentId;

        $totalFee = (float)$pdo->query('SELECT COALESCE(SUM(amount), 0) FROM fee_invoices WHERE student_id = ' . $studentId)->fetchColumn();
        $verified = (float)$pdo->query('SELECT COALESCE(SUM(fp.amount_paid), 0)
            FROM fee_payments fp
            JOIN fee_invoices fi ON fi.id = fp.invoice_id
            WHERE fi.student_id = ' . $studentId . ' AND fp.payment_status = "verified"')->fetchColumn();
        $outstanding = max(0.0, $totalFee - $verified);

        if ($totalFee <= 0) {
            $status = 'Pending';
        } elseif ($verified >= $totalFee) {
            $status = 'Paid';
        } elseif ($verified > 0) {
            $status = 'Part-paid';
        } else {
            $status = 'Pending';
        }

        return [
            'total_fee' => $totalFee,
            'verified_amount' => $verified,
            'outstanding_balance' => $outstanding,
            'status' => $status,
        ];
    }

    public static function studentPayments(int $studentId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT fp.*, fi.invoice_no, fi.amount, fi.description, fi.due_date
             FROM fee_payments fp
             JOIN fee_invoices fi ON fi.id = fp.invoice_id
             WHERE fi.student_id = :student_id
             ORDER BY fp.paid_at DESC'
        );
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll();
    }

    public static function pendingPayments(): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT fp.*, fi.invoice_no, CONCAT(students.first_name, " ", students.last_name) AS student_name
             FROM fee_payments fp
             JOIN fee_invoices fi ON fi.id = fp.invoice_id
             JOIN students ON students.id = fi.student_id
             WHERE fp.payment_status = "pending"
             ORDER BY fp.paid_at DESC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function verifyPayment(int $paymentId, int $verifiedBy, string $decision): void
    {
        $decision = in_array($decision, ['verified', 'rejected'], true) ? $decision : 'rejected';
        $pdo = Database::connection();
        $pdo->beginTransaction();

        $payment = $pdo->prepare(
            'SELECT fp.*, fi.balance, fi.amount
             FROM fee_payments fp
             JOIN fee_invoices fi ON fi.id = fp.invoice_id
             WHERE fp.id = :id
             LIMIT 1
             FOR UPDATE'
        );
        $payment->execute(['id' => $paymentId]);
        $row = $payment->fetch();

        if (!$row || $row['payment_status'] !== 'pending') {
            $pdo->rollBack();
            return;
        }

        $pdo->prepare(
            'UPDATE fee_payments
             SET payment_status = :payment_status,
                 verified_by = :verified_by,
                 verified_at = NOW()
             WHERE id = :id'
        )->execute([
            'payment_status' => $decision,
            'verified_by' => $verifiedBy,
            'id' => $paymentId,
        ]);

        if ($decision === 'verified') {
            $pdo->prepare(
                'UPDATE fee_invoices
                 SET balance = GREATEST(balance - :amount_paid, 0),
                     status = CASE
                        WHEN GREATEST(balance - :amount_for_status_zero, 0) = 0 THEN "paid"
                        WHEN GREATEST(balance - :amount_for_status_compare, 0) < amount THEN "part_paid"
                        ELSE status
                     END
                 WHERE id = :invoice_id'
            )->execute([
                'amount_paid' => $row['amount_paid'],
                'amount_for_status_zero' => $row['amount_paid'],
                'amount_for_status_compare' => $row['amount_paid'],
                'invoice_id' => $row['invoice_id'],
            ]);
        }

        $pdo->commit();
    }

    public static function payment(int $paymentId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT fp.*, fi.invoice_no, fi.description, fi.amount AS invoice_amount,
                    students.id AS student_id, students.first_name, students.other_name,
                    students.last_name, students.email, students.matriculation_no
             FROM fee_payments fp
             JOIN fee_invoices fi ON fi.id = fp.invoice_id
             JOIN students ON students.id = fi.student_id
             WHERE fp.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $paymentId]);
        return $stmt->fetch() ?: null;
    }

    public static function nextInvoiceNo(): string
    {
        return 'INV-' . date('Y') . '-' . str_pad((string)((int)Database::connection()->query('SELECT COUNT(*) FROM fee_invoices')->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);
    }

    public static function nextReceiptNo(): string
    {
        return 'RCT-' . date('Y') . '-' . str_pad((string)((int)Database::connection()->query('SELECT COUNT(*) FROM fee_payments')->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);
    }
}
