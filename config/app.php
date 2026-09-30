<?php

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'ZauberCMS',
    'env' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
    'url' => getenv('APP_URL') ?: null,
];
