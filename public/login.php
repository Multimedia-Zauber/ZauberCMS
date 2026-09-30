<?php

declare(strict_types=1);

use ZauberCMS\Auth\Auth;
use ZauberCMS\Auth\Csrf;
use ZauberCMS\Core\Database;

$app = require dirname(__DIR__) . '/bootstrap/app.php';
$auth = new Auth((new Database($app->config()))->connection());

if ($auth->check()) {
    header('Location: /admin');
    exit;
}

$error = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!Csrf::validate($_POST['_token'] ?? null)) {
        $error = 'Die Anfrage ist abgelaufen. Bitte erneut versuchen.';
    } elseif (!$auth->attempt((string) ($_POST['email'] ?? ''), (string) ($_POST['password'] ?? ''))) {
        $error = 'E-Mail-Adresse oder Passwort ist ungültig.';
    } else {
        header('Location: /admin');
        exit;
    }
}

$token = htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="de"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ZauberCMS Login</title></head>
<body><main><h1>ZauberCMS Login</h1><?php if ($error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><form method="post"><input type="hidden" name="_token" value="<?= $token ?>"><label>E-Mail <input type="email" name="email" required autocomplete="username"></label><label>Passwort <input type="password" name="password" required autocomplete="current-password"></label><button type="submit">Anmelden</button></form></main></body></html>
