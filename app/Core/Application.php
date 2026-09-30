<?php

declare(strict_types=1);

namespace ZauberCMS\Core;

final class Application
{
    public function __construct(
        private readonly string $rootPath,
    ) {
    }

    public function rootPath(string $path = ''): string
    {
        return $path === ''
            ? $this->rootPath
            : $this->rootPath . DIRECTORY_SEPARATOR . ltrim($path, '/\\');
    }

    public function run(): void
    {
        http_response_code(200);
        header('Content-Type: text/html; charset=UTF-8');

        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ZauberCMS</title></head><body>';
        echo '<main><h1>ZauberCMS</h1><p>Core bootstrap is running.</p></main>';
        echo '</body></html>';
    }
}
