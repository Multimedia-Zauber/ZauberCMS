<?php

declare(strict_types=1);

use PDO;
use ZauberCMS\Core\Migration;

return new class implements Migration {
    public function up(PDO $database): void
    {
        $database->exec(
            'CREATE TABLE roles (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                slug VARCHAR(100) NOT NULL UNIQUE,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $database->exec(
            'CREATE TABLE permissions (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL,
                slug VARCHAR(150) NOT NULL UNIQUE,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $database->exec(
            'CREATE TABLE role_permissions (
                role_id BIGINT UNSIGNED NOT NULL,
                permission_id BIGINT UNSIGNED NOT NULL,
                PRIMARY KEY (role_id, permission_id),
                CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
                CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $database->exec('ALTER TABLE users ADD COLUMN role_id BIGINT UNSIGNED NULL AFTER role');
        $database->exec('ALTER TABLE users ADD CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL');

        $roleStatement = $database->prepare('INSERT INTO roles (name, slug) VALUES (:name, :slug)');
        foreach ([
            ['Administrator', 'admin'],
            ['Editor', 'editor'],
            ['Customer', 'customer'],
        ] as [$name, $slug]) {
            $roleStatement->execute(['name' => $name, 'slug' => $slug]);
        }

        $permissions = [
            ['Admin access', 'admin.access'],
            ['Manage pages', 'pages.manage'],
            ['Edit pages', 'pages.edit'],
            ['Upload media', 'media.upload'],
            ['Manage users', 'users.manage'],
            ['Manage settings', 'settings.manage'],
        ];

        $permissionStatement = $database->prepare('INSERT INTO permissions (name, slug) VALUES (:name, :slug)');
        foreach ($permissions as [$name, $slug]) {
            $permissionStatement->execute(['name' => $name, 'slug' => $slug]);
        }

        $adminId = (int) $database->query("SELECT id FROM roles WHERE slug = 'admin'")->fetchColumn();
        $editorId = (int) $database->query("SELECT id FROM roles WHERE slug = 'editor'")->fetchColumn();
        $customerId = (int) $database->query("SELECT id FROM roles WHERE slug = 'customer'")->fetchColumn();

        $allPermissionIds = $database->query('SELECT id FROM permissions')->fetchAll(PDO::FETCH_COLUMN);
        $linkStatement = $database->prepare('INSERT INTO role_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)');
        foreach ($allPermissionIds as $permissionId) {
            $linkStatement->execute(['role_id' => $adminId, 'permission_id' => $permissionId]);
        }

        $editorPermissionIds = $database->query("SELECT id FROM permissions WHERE slug IN ('admin.access', 'pages.manage', 'pages.edit', 'media.upload')")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($editorPermissionIds as $permissionId) {
            $linkStatement->execute(['role_id' => $editorId, 'permission_id' => $permissionId]);
        }

        $database->prepare("UPDATE users SET role_id = :role_id WHERE role = 'admin'")->execute(['role_id' => $adminId]);
        $database->prepare("UPDATE users SET role_id = :role_id WHERE role = 'editor'")->execute(['role_id' => $editorId]);
        $database->prepare("UPDATE users SET role_id = :role_id WHERE role = 'customer'")->execute(['role_id' => $customerId]);
    }

    public function down(PDO $database): void
    {
        $database->exec('ALTER TABLE users DROP FOREIGN KEY fk_users_role');
        $database->exec('ALTER TABLE users DROP COLUMN role_id');
        $database->exec('DROP TABLE IF EXISTS role_permissions');
        $database->exec('DROP TABLE IF EXISTS permissions');
        $database->exec('DROP TABLE IF EXISTS roles');
    }
};
