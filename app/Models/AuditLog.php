<?php

namespace App\Models;

use App\Core\Database;

final class AuditLog
{
    public static function record(?int $userId, string $action, string $entity, ?int $entityId = null, array $meta = []): void
    {
        $sql = 'INSERT INTO audit_logs (user_id, action, entity, entity_id, ip_address, user_agent, metadata)
                VALUES (:user_id, :action, :entity, :entity_id, :ip_address, :user_agent, :metadata)';

        Database::connection()->prepare($sql)->execute([
            'user_id' => $userId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            'metadata' => $meta === [] ? null : json_encode($meta),
        ]);
    }

    public static function recent(int $limit = 8): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT audit_logs.*, CONCAT(users.first_name, " ", users.last_name) AS user_name
             FROM audit_logs
             LEFT JOIN users ON users.id = audit_logs.user_id
             ORDER BY audit_logs.created_at DESC
             LIMIT :limit'
        );
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
