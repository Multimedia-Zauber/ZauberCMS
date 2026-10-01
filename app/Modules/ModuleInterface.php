<?php

declare(strict_types=1);

namespace ZauberCMS\Modules;

interface ModuleInterface
{
    public function register(): void;

    public function boot(): void;
}
