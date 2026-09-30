<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

$routes = [
    '/install' => __DIR__ . '/install.php',
    '/install/' => __DIR__ . '/install.php',
    '/login' => __DIR__ . '/login.php',
    '/login/' => __DIR__ . '/login.php',
    '/admin' => __DIR__ . '/admin.php',
    '/admin/' => __DIR__ . '/admin.php',
];

if (isset($routes[$path])) {
    require $routes[$path];
    exit;
}

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$app->run();
