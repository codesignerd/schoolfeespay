<?php

namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    public static function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT users.*, roles.name AS role_name, roles.slug AS role_slug,
                    students.id AS student_id, students.status AS student_status,
                    students.matriculation_no, students.department_id, students.programme, students.level, students.academic_session
             FROM users
             JOIN roles ON roles.id = users.role_id
             LEFT JOIN students ON students.user_id = users.id
             WHERE users.email = :email
             LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function roleIdBySlug(string $slug): ?int
    {
        $stmt = Database::connection()->prepare('SELECT id FROM roles WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $role = $stmt->fetch();
        return $role ? (int)$role['id'] : null;
    }

    public static function permissions(int $roleId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT permissions.slug
             FROM permissions
             JOIN role_permissions ON role_permissions.permission_id = permissions.id
             WHERE role_permissions.role_id = :role_id'
        );
        $stmt->execute(['role_id' => $roleId]);
        return array_column($stmt->fetchAll(), 'slug');
    }

    public static function roles(): array
    {
        return Database::connection()->query('SELECT id, name FROM roles ORDER BY name')->fetchAll();
    }

    public static function paginate(string $search = '', string $roleId = '', int $page = 1, int $perPage = 10): array
    {
        $where = ['1 = 1'];
        $params = [];

        if ($search !== '') {
            $where[] = '(users.first_name LIKE :search OR users.last_name LIKE :search OR users.email LIKE :search)';
            $params['search'] = "%{$search}%";
        }

        if ($roleId !== '') {
            $where[] = 'users.role_id = :role_id';
            $params['role_id'] = $roleId;
        }

        $whereSql = implode(' AND ', $where);
        $count = Database::connection()->prepare("SELECT COUNT(*) FROM users WHERE {$whereSql}");
        $count->execute($params);
        $total = (int)$count->fetchColumn();
        $offset = max(0, ($page - 1) * $perPage);

        $stmt = Database::connection()->prepare(
            "SELECT users.id, users.first_name, users.last_name, users.email, users.phone, users.status,
                    users.last_login_at, users.created_at, roles.name AS role_name
             FROM users
             JOIN roles ON roles.id = users.role_id
             WHERE {$whereSql}
             ORDER BY users.created_at DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(),
            'total' => $total,
            'pages' => max(1, (int)ceil($total / $perPage)),
            'page' => $page,
        ];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO users (role_id, first_name, last_name, email, phone, password_hash, status)
             VALUES (:role_id, :first_name, :last_name, :email, :phone, :password_hash, :status)'
        );
        $stmt->execute([
            'role_id' => $data['role_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'status' => $data['status'],
        ]);
        return (int)Database::connection()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $fields = 'role_id = :role_id, first_name = :first_name, last_name = :last_name,
                   email = :email, phone = :phone, status = :status';
        $params = [
            'id' => $id,
            'role_id' => $data['role_id'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?: null,
            'status' => $data['status'],
        ];

        if (($data['password'] ?? '') !== '') {
            $fields .= ', password_hash = :password_hash';
            $params['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        Database::connection()->prepare("UPDATE users SET {$fields} WHERE id = :id")->execute($params);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM users WHERE id = :id')->execute(['id' => $id]);
    }

    public static function touchLogin(int $id): void
    {
        Database::connection()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute(['id' => $id]);
    }
}
