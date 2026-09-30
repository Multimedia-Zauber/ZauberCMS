<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if ($path === '/install' || $path === '/install/') {
    require __DIR__ . '/install.php';
    exit;
}

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->run();
