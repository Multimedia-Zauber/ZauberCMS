<?php

declare(strict_types=1);

use ZauberCMS\Support\Env;

return [
    'name' => Env::get('APP_NAME', 'ZauberCMS'),
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => Env::get('APP_DEBUG', false),
    'url' => Env::get('APP_URL'),
];
