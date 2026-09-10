<?php

namespace App\Models;

use App\Core\Database;

final class Dashboard
{
    public static function metrics(): array
    {
        $pdo = Database::connection();

        return [
            'students' => (int)$pdo->query('SELECT COUNT(*) FROM students')->fetchColumn(),
            'pending_students' => (int)$pdo->query('SELECT COUNT(*) FROM students WHERE status = "pending"')->fetchColumn(),
            'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'fees_collected' => (float)$pdo->query('SELECT COALESCE(SUM(amount_paid), 0) FROM fee_payments WHERE payment_status = "verified"')->fetchColumn(),
            'outstanding_fees' => (float)$pdo->query('SELECT COALESCE(SUM(balance), 0) FROM fee_invoices')->fetchColumn(),
        ];
    }
}
