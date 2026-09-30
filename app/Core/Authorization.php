<?php

declare(strict_types=1);

namespace ZauberCMS\Core;

use PDO;

final class Authorization
{
    public function __construct(private readonly PDO $database)
    {
    }

    public function userHasPermission(int $userId, string $permission): bool
    {
        $statement = $this->database->prepare(
            'SELECT COUNT(*)
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             INNER JOIN role_permissions rp ON rp.role_id = r.id
             INNER JOIN permissions p ON p.id = rp.permission_id
             WHERE u.id = :user_id
               AND u.active = 1
               AND p.slug = :permission'
        );
        $statement->execute([
            'user_id' => $userId,
            'permission' => $permission,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function userHasRole(int $userId, string $role): bool
    {
        $statement = $this->database->prepare(
            'SELECT COUNT(*)
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.id = :user_id
               AND u.active = 1
               AND r.slug = :role'
        );
        $statement->execute([
            'user_id' => $userId,
            'role' => $role,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function authorize(int $userId, string $permission): void
    {
        if (!$this->userHasPermission($userId, $permission)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}
