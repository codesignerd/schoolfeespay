<?php

namespace App\Models;

use App\Core\Database;
use PDO;

final class Student
{
    public static function paginate(string $search = '', string $classId = '', int $page = 1, int $perPage = 12): array
    {
        $where = ['1 = 1'];
        $params = [];
        if ($search !== '') {
            $where[] = '(students.admission_no LIKE :search OR students.first_name LIKE :search OR students.last_name LIKE :search OR students.email LIKE :search)';
            $params['search'] = "%{$search}%";
        }
        if ($classId !== '') {
            $where[] = 'students.class_stream_id = :class_id';
            $params['class_id'] = $classId;
        }
        $whereSql = implode(' AND ', $where);
        $count = Database::connection()->prepare("SELECT COUNT(*) FROM students WHERE {$whereSql}");
        $count->execute($params);
        $total = (int)$count->fetchColumn();
        $offset = max(0, ($page - 1) * $perPage);
        $stmt = Database::connection()->prepare(
            "SELECT students.*, CONCAT(class_streams.name, ' ', class_streams.stream) AS class_name
             FROM students
             LEFT JOIN class_streams ON class_streams.id = students.class_stream_id
             WHERE {$whereSql}
             ORDER BY students.created_at DESC
             LIMIT :limit OFFSET :offset"
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['items' => $stmt->fetchAll(), 'total' => $total, 'pages' => max(1, (int)ceil($total / $perPage)), 'page' => $page];
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM students WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $student = $stmt->fetch();
        if (!$student) {
            return null;
        }
        $links = Database::connection()->prepare('SELECT parent_id FROM student_parent WHERE student_id = :id');
        $links->execute(['id' => $id]);
        $student['parent_ids'] = array_column($links->fetchAll(), 'parent_id');
        return $student;
    }

    public static function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO students (user_id, class_stream_id, department_id, admission_no, matriculation_no, first_name, other_name, last_name, email, phone, date_of_birth, gender, nationality, religion, programme, level, academic_session, medical_notes, emergency_contact, previous_school, status)
             VALUES (:user_id, :class_stream_id, :department_id, :admission_no, :matriculation_no, :first_name, :other_name, :last_name, :email, :phone, :date_of_birth, :gender, :nationality, :religion, :programme, :level, :academic_session, :medical_notes, :emergency_contact, :previous_school, :status)'
        );
        $stmt->execute(self::params($data));
        $id = (int)Database::connection()->lastInsertId();
        self::syncParents($id, $data['parent_ids'] ?? []);
        return $id;
    }

    public static function findByUserId(int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM students WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $student = $stmt->fetch();
        if (!$student) {
            return null;
        }
        $links = Database::connection()->prepare('SELECT parent_id FROM student_parent WHERE student_id = :id');
        $links->execute(['id' => (int)$student['id']]);
        $student['parent_ids'] = array_column($links->fetchAll(), 'parent_id');
        return $student;
    }

    public static function profileByUserId(int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT students.*, departments.name AS department_name
             FROM students
             LEFT JOIN departments ON departments.id = students.department_id
             WHERE students.user_id = :user_id
             LIMIT 1'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public static function pending(): array
    {
        return Database::connection()->query(
            'SELECT students.*, departments.name AS department_name
             FROM students
             LEFT JOIN departments ON departments.id = students.department_id
             WHERE students.status = "pending"
             ORDER BY students.created_at DESC'
        )->fetchAll();
    }

    public static function approve(int $id, string $matriculationNo): void
    {
        $matriculationNo = trim($matriculationNo);
        if ($matriculationNo === '') {
            throw new \InvalidArgumentException('A matriculation number is required.');
        }

        $pdo = Database::connection();
        $count = $pdo->prepare('SELECT COUNT(*) FROM students WHERE matriculation_no = :matriculation_no AND id != :id');
        $count->execute(['matriculation_no' => $matriculationNo, 'id' => $id]);
        if ((int)$count->fetchColumn() > 0) {
            throw new \InvalidArgumentException('This matriculation number is already in use.');
        }

        $update = $pdo->prepare('UPDATE students SET matriculation_no = :matriculation_no, status = "active" WHERE id = :id AND status = "pending"');
        $update->execute(['matriculation_no' => $matriculationNo, 'id' => $id]);
        if ($update->rowCount() !== 1) {
            throw new \InvalidArgumentException('Only pending registrations can be approved.');
        }
    }

    public static function reject(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE students SET status = "rejected" WHERE id = :id AND status = "pending"');
        $stmt->execute(['id' => $id]);
        if ($stmt->rowCount() !== 1) {
            throw new \InvalidArgumentException('Only pending registrations can be rejected.');
        }
    }

    public static function update(int $id, array $data): void
    {
        Database::connection()->prepare(
            'UPDATE students SET user_id = :user_id, class_stream_id = :class_stream_id, department_id = :department_id, admission_no = :admission_no, matriculation_no = :matriculation_no,
             first_name = :first_name, other_name = :other_name, last_name = :last_name, email = :email, phone = :phone, date_of_birth = :date_of_birth, gender = :gender,
             nationality = :nationality, religion = :religion, programme = :programme, level = :level, academic_session = :academic_session, medical_notes = :medical_notes,
             emergency_contact = :emergency_contact, previous_school = :previous_school, status = :status WHERE id = :id'
        )->execute(['id' => $id] + self::params($data));
        self::syncParents($id, $data['parent_ids'] ?? []);
    }

    public static function delete(int $id): void
    {
        Database::connection()->prepare('DELETE FROM students WHERE id = :id')->execute(['id' => $id]);
    }

    public static function nextAdmissionNo(): string
    {
        $year = date('Y');
        $count = (int)Database::connection()->query('SELECT COUNT(*) FROM students')->fetchColumn() + 1;
        return sprintf('ADM-%s-%04d', $year, $count);
    }

    private static function params(array $data): array
    {
        return [
            'user_id' => $data['user_id'] ?? null,
            'class_stream_id' => !empty($data['class_stream_id']) ? $data['class_stream_id'] : null,
            'department_id' => !empty($data['department_id']) ? $data['department_id'] : null,
            'admission_no' => $data['admission_no'] ?? null,
            'matriculation_no' => $data['matriculation_no'] ?? null,
            'first_name' => $data['first_name'] ?? '',
            'other_name' => $data['other_name'] ?? null,
            'last_name' => $data['last_name'] ?? '',
            'email' => !empty($data['email']) ? $data['email'] : null,
            'phone' => !empty($data['phone']) ? $data['phone'] : null,
            'date_of_birth' => !empty($data['date_of_birth']) ? $data['date_of_birth'] : null,
            'gender' => $data['gender'] ?? 'other',
            'nationality' => !empty($data['nationality']) ? $data['nationality'] : null,
            'religion' => !empty($data['religion']) ? $data['religion'] : null,
            'programme' => !empty($data['programme']) ? $data['programme'] : null,
            'level' => !empty($data['level']) ? $data['level'] : null,
            'academic_session' => !empty($data['academic_session']) ? $data['academic_session'] : null,
            'medical_notes' => !empty($data['medical_notes']) ? $data['medical_notes'] : null,
            'emergency_contact' => !empty($data['emergency_contact']) ? $data['emergency_contact'] : null,
            'previous_school' => !empty($data['previous_school']) ? $data['previous_school'] : null,
            'status' => $data['status'] ?? 'pending',
        ];
    }

    private static function syncParents(int $studentId, array $parentIds): void
    {
        $pdo = Database::connection();
        $pdo->prepare('DELETE FROM student_parent WHERE student_id = :student_id')->execute(['student_id' => $studentId]);
        $stmt = $pdo->prepare('INSERT INTO student_parent (student_id, parent_id, is_primary) VALUES (:student_id, :parent_id, :is_primary)');
        foreach (array_values(array_filter($parentIds)) as $index => $parentId) {
            $stmt->execute(['student_id' => $studentId, 'parent_id' => $parentId, 'is_primary' => $index === 0 ? 1 : 0]);
        }
    }
}
