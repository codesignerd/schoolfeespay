<?php

namespace App\Models;

use App\Core\Database;

final class Teacher
{
    public static function all(string $search = ''): array
    {
        $sql = 'SELECT teachers.*, departments.name AS department_name
                FROM teachers
                LEFT JOIN departments ON departments.id = teachers.department_id';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE teachers.staff_no LIKE :search OR teachers.first_name LIKE :search OR teachers.last_name LIKE :search OR teachers.email LIKE :search';
            $params['search'] = "%{$search}%";
        }
        $sql .= ' ORDER BY teachers.created_at DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM teachers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function save(array $data, ?int $id = null): int
    {
        $params = [
            'department_id' => $data['department_id'] ?: null,
            'staff_no' => $data['staff_no'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'gender' => $data['gender'],
            'qualification' => $data['qualification'] ?: null,
            'hire_date' => $data['hire_date'] ?: null,
            'status' => $data['status'],
        ];
        if ($id) {
            Database::connection()->prepare(
                'UPDATE teachers SET department_id = :department_id, staff_no = :staff_no, first_name = :first_name,
                 last_name = :last_name, email = :email, phone = :phone, gender = :gender, qualification = :qualification,
                 hire_date = :hire_date, status = :status WHERE id = :id'
            )->execute(['id' => $id] + $params);
            return $id;
        }
        Database::connection()->prepare(
            'INSERT INTO teachers (department_id, staff_no, first_name, last_name, email, phone, gender, qualification, hire_date, status)
             VALUES (:department_id, :staff_no, :first_name, :last_name, :email, :phone, :gender, :qualification, :hire_date, :status)'
        )->execute($params);
        return (int)Database::connection()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM teachers WHERE id = :id')->execute(['id' => $id]);
    }
}
