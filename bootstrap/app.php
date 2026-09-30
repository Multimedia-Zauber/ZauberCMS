<?php

declare(strict_types=1);

use ZauberCMS\Core\Application;

$rootPath = dirname(__DIR__);

$autoload = $rootPath . '/vendor/autoload.php';
if (!is_file($autoload)) {
    throw new RuntimeException('Composer dependencies are missing. Run composer install first.');
}

require $autoload;

return new Application($rootPath);
