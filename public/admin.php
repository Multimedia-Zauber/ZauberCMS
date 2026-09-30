<?php

declare(strict_types=1);

use ZauberCMS\Auth\Auth;
use ZauberCMS\Auth\Csrf;
use ZauberCMS\Core\Authorization;
use ZauberCMS\Core\Database;

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$database = (new Database($app->config()))->connection();
$auth = new Auth($database);

if (!$auth->check()) {
    header('Location: /login');
    exit;
}

$user = $auth->user();
$userId = (int) ($user['id'] ?? 0);
(new Authorization($database))->authorize($userId, 'admin.access');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    if (Csrf::validate($_POST['_token'] ?? null)) {
        $auth->logout();
    }
    header('Location: /login');
    exit;
}

$token = htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ZauberCMS Admin</title></head><body><main><h1>ZauberCMS Admin</h1><p>Angemeldet als <?= htmlspecialchars((string) ($user['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></p><form method="post"><input type="hidden" name="_token" value="<?= $token ?>"><input type="hidden" name="action" value="logout"><button type="submit">Abmelden</button></form></main></body></html>
