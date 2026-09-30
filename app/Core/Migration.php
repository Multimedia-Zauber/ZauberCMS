<?php

declare(strict_types=1);

namespace ZauberCMS\Core;

use PDO;

interface Migration
{
    public function up(PDO $database): void;

    public function down(PDO $database): void;
}
