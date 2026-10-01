<?php

declare(strict_types=1);

return [
    'default_locale' => getenv('APP_LOCALE') ?: 'de',
    'fallback_locale' => getenv('APP_FALLBACK_LOCALE') ?: 'de',
    'supported_locales' => ['de', 'fr', 'en'],
];
