<?php

declare(strict_types=1);

namespace ZauberCMS\Core;

use PDO;
use RuntimeException;

final class Migrator
{
    public function __construct(
        private readonly PDO $database,
        private readonly string $migrationPath
    ) {
    }

    public function migrate(): array
    {
        $this->ensureMigrationTable();

        $executed = $this->executedMigrations();
        $files = glob(rtrim($this->migrationPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php') ?: [];
        sort($files, SORT_STRING);
        $batch = $this->nextBatchNumber();

        $applied = [];
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (in_array($name, $executed, true)) {
                continue;
            }

            $migration = require $file;
            if (!$migration instanceof Migration) {
                throw new RuntimeException(sprintf('Migration %s must return an instance of %s.', $name, Migration::class));
            }

            $this->database->beginTransaction();
            try {
                $migration->up($this->database);

                $statement = $this->database->prepare(
                    'INSERT INTO migrations (migration, batch, executed_at) VALUES (:migration, :batch, NOW())'
                );
                $statement->execute([
                    'migration' => $name,
                    'batch' => $batch,
                ]);

                $this->database->commit();
                $applied[] = $name;
            } catch (\Throwable $exception) {
                if ($this->database->inTransaction()) {
                    $this->database->rollBack();
                }
                throw $exception;
            }
        }

        return $applied;
    }

    private function ensureMigrationTable(): void
    {
        $this->database->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL UNIQUE,
                batch INT UNSIGNED NOT NULL,
                executed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private function executedMigrations(): array
    {
        $statement = $this->database->query('SELECT migration FROM migrations ORDER BY id ASC');
        return $statement ? array_column($statement->fetchAll(), 'migration') : [];
    }

    private function nextBatchNumber(): int
    {
        $statement = $this->database->query('SELECT COALESCE(MAX(batch), 0) AS batch FROM migrations');
        $row = $statement ? $statement->fetch() : ['batch' => 0];

        return ((int) ($row['batch'] ?? 0)) + 1;
    }
}
