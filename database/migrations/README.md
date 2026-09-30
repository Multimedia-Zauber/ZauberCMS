# ZauberCMS migrations

Migration files are executed in lexicographical order and must return an object implementing `ZauberCMS\Core\Migration`.

Recommended filename format:

```text
2026_09_30_000001_create_users_table.php
```

Example:

```php
<?php

use PDO;
use ZauberCMS\Core\Migration;

return new class implements Migration {
    public function up(PDO $database): void
    {
        $database->exec('CREATE TABLE example (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY)');
    }

    public function down(PDO $database): void
    {
        $database->exec('DROP TABLE IF EXISTS example');
    }
};
```

Already executed migrations are stored in the `migrations` table and are not executed twice.
