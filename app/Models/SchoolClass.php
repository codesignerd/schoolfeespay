<?php

namespace App\Models;

use App\Core\Database;

final class SchoolClass
{
    public static function all(): array
    {
        return Lookup::classes();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM class_streams WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public static function save(array $data, ?int $id = null): int
    {
        $params = [
            'name' => $data['name'],
            'stream' => $data['stream'],
            'class_teacher_id' => $data['class_teacher_id'] ?: null,
            'capacity' => (int)$data['capacity'],
            'status' => $data['status'],
        ];
        if ($id) {
            Database::connection()->prepare(
                'UPDATE class_streams SET name = :name, stream = :stream, class_teacher_id = :class_teacher_id,
                 capacity = :capacity, status = :status WHERE id = :id'
            )->execute(['id' => $id] + $params);
            return $id;
        }
        Database::connection()->prepare(
            'INSERT INTO class_streams (name, stream, class_teacher_id, capacity, status)
             VALUES (:name, :stream, :class_teacher_id, :capacity, :status)'
        )->execute($params);
        return (int)Database::connection()->lastInsertId();
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM class_streams WHERE id = :id')->execute(['id' => $id]);
    }
}
