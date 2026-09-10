<?php

namespace App\Models;

use App\Core\Database;

final class Guardian
{
    public static function all(string $search = ''): array
    {
        $sql = 'SELECT * FROM parents';
        $params = [];
        if ($search !== '') {
            $sql .= ' WHERE first_name LIKE :search OR last_name LIKE :search OR phone LIKE :search OR email LIKE :search';
            $params['search'] = "%{$search}%";
        }
        $sql .= ' ORDER BY created_at DESC';
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM parents WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function save(array $data, ?int $id = null): int
    {
        $params = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'relationship' => $data['relationship'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'],
            'address' => $data['address'] ?: null,
        ];
        if ($id) {
            Database::connection()->prepare(
                'UPDATE parents SET first_name = :first_name, last_name = :last_name, relationship = :relationship,
                 email = :email, phone = :phone, address = :address WHERE id = :id'
            )->execute(['id' => $id] + $params);
            return $id;
        }
        Database::connection()->prepare(
            'INSERT INTO parents (first_name, last_name, relationship, email, phone, address)
             VALUES (:first_name, :last_name, :relationship, :email, :phone, :address)'
        )->execute($params);
        return (int)Database::connection()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM parents WHERE id = :id')->execute(['id' => $id]);
    }
}
