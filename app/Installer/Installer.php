<?php

declare(strict_types=1);

namespace ZauberCMS\Installer;

use PDO;
use RuntimeException;
use ZauberCMS\Core\Migrator;

final class Installer
{
    public function __construct(private readonly string $rootPath)
    {
    }

    public function isInstalled(): bool
    {
        return is_file($this->rootPath . '/storage/installed.lock');
    }

    /** @param array<string, string> $values */
    public function writeEnvironment(array $values): void
    {
        $required = ['APP_ENV', 'APP_URL', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME'];
        foreach ($required as $key) {
            if (!isset($values[$key]) || trim($values[$key]) === '') {
                throw new RuntimeException('Missing required installer value: ' . $key);
            }
        }

        $lines = [];
        foreach ($values as $key => $value) {
            if (!preg_match('/^[A-Z0-9_]+$/', $key)) {
                continue;
            }
            $escaped = str_replace(["\\", '"', "\n", "\r"], ["\\\\", '\\"', '\\n', ''], $value);
            $lines[] = $key . '="' . $escaped . '"';
        }

        if (file_put_contents($this->rootPath . '/.env', implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX) === false) {
            throw new RuntimeException('Unable to write .env file.');
        }
    }

    /** @param array<string, string> $database */
    public function testDatabase(array $database): void
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $database['host'] ?? '',
            (int) ($database['port'] ?? 3306),
            $database['database'] ?? ''
        );

        new PDO($dsn, $database['username'] ?? '', $database['password'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public function runMigrations(PDO $database): array
    {
        return (new Migrator($database, $this->rootPath . '/database/migrations'))->migrate();
    }

    public function lockInstallation(): void
    {
        $path = $this->rootPath . '/storage/installed.lock';
        if (file_put_contents($path, date(DATE_ATOM) . PHP_EOL, LOCK_EX) === false) {
            throw new RuntimeException('Unable to create installation lock.');
        }
    }
}
