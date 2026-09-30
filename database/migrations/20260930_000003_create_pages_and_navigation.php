<?php

declare(strict_types=1);

use PDO;
use ZauberCMS\Core\Migration;

return new class implements Migration {
    public function up(PDO $database): void
    {
        $database->exec("CREATE TABLE IF NOT EXISTS pages (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content LONGTEXT NULL,
            status ENUM('draft','published') NOT NULL DEFAULT 'draft',
            seo_title VARCHAR(255) NULL,
            seo_description VARCHAR(500) NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_pages_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $database->exec("CREATE TABLE IF NOT EXISTS menus (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            location VARCHAR(120) NOT NULL UNIQUE,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $database->exec("CREATE TABLE IF NOT EXISTS menu_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            menu_id BIGINT UNSIGNED NOT NULL,
            page_id BIGINT UNSIGNED NULL,
            label VARCHAR(255) NOT NULL,
            url VARCHAR(500) NULL,
            position INT UNSIGNED NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_menu_items_menu FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
            CONSTRAINT fk_menu_items_page FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE SET NULL,
            INDEX idx_menu_items_order (menu_id, position)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $permissions = ['pages.view', 'pages.edit', 'navigation.manage'];
        $statement = $database->prepare('INSERT IGNORE INTO permissions (name) VALUES (:name)');
        foreach ($permissions as $permission) {
            $statement->execute(['name' => $permission]);
        }

        $database->exec("INSERT IGNORE INTO role_permissions (role_id, permission_id)
            SELECT r.id, p.id FROM roles r CROSS JOIN permissions p
            WHERE r.name = 'admin' AND p.name IN ('pages.view','pages.edit','navigation.manage')");
    }

    public function down(PDO $database): void
    {
        $database->exec('DROP TABLE IF EXISTS menu_items');
        $database->exec('DROP TABLE IF EXISTS menus');
        $database->exec('DROP TABLE IF EXISTS pages');
    }
};
