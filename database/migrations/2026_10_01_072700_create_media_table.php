<?php

declare(strict_types=1);

use PDO;
use ZauberCMS\Core\Migration;

return new class implements Migration {
    public function up(PDO $database): void
    {
        $database->exec(
            "CREATE TABLE media (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                original_name VARCHAR(255) NOT NULL,
                stored_name VARCHAR(255) NOT NULL UNIQUE,
                path VARCHAR(500) NOT NULL,
                mime_type VARCHAR(150) NOT NULL,
                extension VARCHAR(20) NULL,
                size_bytes BIGINT UNSIGNED NOT NULL,
                width INT UNSIGNED NULL,
                height INT UNSIGNED NULL,
                title VARCHAR(255) NULL,
                alt_text VARCHAR(255) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_media_mime_type (mime_type),
                INDEX idx_media_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );

        $permissions = [
            ['media.view', 'Medien anzeigen'],
            ['media.upload', 'Medien hochladen'],
            ['media.delete', 'Medien löschen'],
        ];

        $insertPermission = $database->prepare(
            'INSERT IGNORE INTO permissions (name, label) VALUES (:name, :label)'
        );
        foreach ($permissions as [$name, $label]) {
            $insertPermission->execute(['name' => $name, 'label' => $label]);
        }

        $database->exec(
            "INSERT IGNORE INTO role_permissions (role_id, permission_id)
             SELECT roles.id, permissions.id
             FROM roles
             JOIN permissions ON permissions.name IN ('media.view','media.upload','media.delete')
             WHERE roles.name = 'admin'"
        );
    }

    public function down(PDO $database): void
    {
        $database->exec('DROP TABLE IF EXISTS media');
        $database->exec("DELETE FROM permissions WHERE name IN ('media.view','media.upload','media.delete')");
    }
};
