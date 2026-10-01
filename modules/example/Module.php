<?php

declare(strict_types=1);

use ZauberCMS\Modules\ModuleInterface;

return new class implements ModuleInterface {
    public function register(): void
    {
        // Register routes, permissions, migrations or services here.
    }

    public function boot(): void
    {
        // Run module startup logic here.
    }
};
