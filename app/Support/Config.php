<?php

declare(strict_types=1);

namespace ZauberCMS\Support;

use RuntimeException;

final class Config
{
    /** @var array<string, mixed> */
    private static array $items = [];

    public static function loadDirectory(string $path): void
    {
        if (!is_dir($path)) {
            throw new RuntimeException('Configuration directory not found: ' . $path);
        }

        foreach (glob(rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            $key = basename($file, '.php');
            $value = require $file;

            if (!is_array($value)) {
                throw new RuntimeException('Configuration file must return an array: ' . $file);
            }

            self::$items[$key] = $value;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$items;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
