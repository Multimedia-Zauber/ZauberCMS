<?php

declare(strict_types=1);

use ZauberCMS\Core\Application;
use ZauberCMS\Support\Config;
use ZauberCMS\Support\Env;

$rootPath = dirname(__DIR__);

$autoload = $rootPath . '/vendor/autoload.php';
if (!is_file($autoload)) {
    throw new RuntimeException('Composer dependencies are missing. Run composer install first.');
}

require $autoload;

Env::load($rootPath . '/.env');
Env::require([
    'APP_ENV',
    'APP_URL',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
]);

Config::loadDirectory($rootPath . '/config');

return new Application($rootPath);
