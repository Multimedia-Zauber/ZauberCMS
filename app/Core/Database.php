<?php

declare(strict_types=1);

namespace ZauberCMS\Core;

use PDO;
use PDOException;
use RuntimeException;
use ZauberCMS\Support\Config;

final class Database
{
    private ?PDO $connection = null;

    public function connection(): PDO
    {
        if ($this->connection instanceof PDO) {
            return $this->connection;
        }

        $driver = (string) Config::get('database.driver', 'mysql');
        if ($driver !== 'mysql') {
            throw new RuntimeException(sprintf('Unsupported database driver: %s', $driver));
        }

        $host = (string) Config::get('database.host');
        $port = (int) Config::get('database.port', 3306);
        $database = (string) Config::get('database.database');
        $username = (string) Config::get('database.username');
        $password = (string) Config::get('database.password', '');
        $charset = (string) Config::get('database.charset', 'utf8mb4');

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset
        );

        try {
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('Database connection failed.', 0, $exception);
        }

        return $this->connection;
    }
}
