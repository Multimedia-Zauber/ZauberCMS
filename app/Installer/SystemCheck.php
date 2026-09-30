<?php

declare(strict_types=1);

namespace ZauberCMS\Installer;

final class SystemCheck
{
    /** @return array<string, array{ok: bool, message: string}> */
    public function run(): array
    {
        return [
            'php' => [
                'ok' => version_compare(PHP_VERSION, '8.4.0', '>='),
                'message' => 'PHP 8.4 or newer is required. Current: ' . PHP_VERSION,
            ],
            'pdo' => [
                'ok' => extension_loaded('pdo'),
                'message' => 'PDO extension must be enabled.',
            ],
            'pdo_mysql' => [
                'ok' => extension_loaded('pdo_mysql'),
                'message' => 'pdo_mysql extension must be enabled.',
            ],
            'storage_writable' => [
                'ok' => is_writable(dirname(__DIR__, 2) . '/storage'),
                'message' => 'The storage directory must be writable.',
            ],
        ];
    }

    public function passes(): bool
    {
        foreach ($this->run() as $check) {
            if (!$check['ok']) {
                return false;
            }
        }

        return true;
    }
}
