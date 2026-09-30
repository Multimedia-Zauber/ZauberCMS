<?php

declare(strict_types=1);

use PDO;
use ZauberCMS\Installer\Installer;
use ZauberCMS\Installer\SystemCheck;

$rootPath = dirname(__DIR__);
$autoload = $rootPath . '/vendor/autoload.php';
if (!is_file($autoload)) {
    http_response_code(500);
    exit('Composer dependencies are missing. Run composer install first.');
}
require $autoload;

$installer = new Installer($rootPath);
if ($installer->isInstalled()) {
    http_response_code(403);
    exit('ZauberCMS is already installed.');
}

$checks = (new SystemCheck())->run();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($checks as $check) {
        if (!$check['ok']) {
            $errors[] = 'System requirements are not met.';
            break;
        }
    }

    $adminName = trim((string) ($_POST['admin_name'] ?? ''));
    $adminEmail = mb_strtolower(trim((string) ($_POST['admin_email'] ?? '')));
    $adminPassword = (string) ($_POST['admin_password'] ?? '');

    if ($adminName === '' || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL) || strlen($adminPassword) < 12) {
        $errors[] = 'Please provide a valid admin name, email address and a password with at least 12 characters.';
    }

    if ($errors === []) {
        $db = [
            'host' => trim((string) ($_POST['db_host'] ?? '127.0.0.1')),
            'port' => trim((string) ($_POST['db_port'] ?? '3306')),
            'database' => trim((string) ($_POST['db_database'] ?? '')),
            'username' => trim((string) ($_POST['db_username'] ?? '')),
            'password' => (string) ($_POST['db_password'] ?? ''),
        ];

        try {
            $installer->testDatabase($db);
            $installer->writeEnvironment([
                'APP_NAME' => trim((string) ($_POST['app_name'] ?? 'ZauberCMS')),
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'APP_URL' => trim((string) ($_POST['app_url'] ?? '')),
                'DB_HOST' => $db['host'],
                'DB_PORT' => $db['port'],
                'DB_DATABASE' => $db['database'],
                'DB_USERNAME' => $db['username'],
                'DB_PASSWORD' => $db['password'],
            ]);

            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], (int) $db['port'], $db['database']),
                $db['username'],
                $db['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
            );
            $installer->runMigrations($pdo);

            $statement = $pdo->prepare(
                'INSERT INTO users (name, email, password, role, is_active) VALUES (:name, :email, :password, :role, 1)'
            );
            $statement->execute([
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => password_hash($adminPassword, PASSWORD_DEFAULT),
                'role' => 'admin',
            ]);

            $installer->lockInstallation();
            $success = true;
        } catch (Throwable $exception) {
            $errors[] = 'Installation failed. Please verify the database settings, admin details and file permissions.';
        }
    }
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install ZauberCMS</title>
<style>
body{font-family:system-ui,sans-serif;max-width:760px;margin:40px auto;padding:0 20px;background:#f6f7fb;color:#17181c}main{background:#fff;padding:28px;border-radius:16px;box-shadow:0 8px 30px rgba(0,0,0,.08)}label{display:block;margin:14px 0 6px;font-weight:600}input{width:100%;box-sizing:border-box;padding:11px;border:1px solid #ccd0d8;border-radius:8px}button{margin-top:20px;padding:12px 18px;border:0;border-radius:8px;cursor:pointer}.ok{color:#16743c}.bad,.error{color:#a51d2d}.notice{padding:12px;border-radius:8px;background:#eef2f7;margin:12px 0}</style>
</head>
<body><main>
<h1>ZauberCMS Installer</h1>
<h2>System check</h2>
<ul>
<?php foreach ($checks as $name => $check): ?>
<li class="<?= $check['ok'] ? 'ok' : 'bad' ?>"><?= htmlspecialchars($name) ?>: <?= $check['ok'] ? 'OK' : htmlspecialchars($check['message']) ?></li>
<?php endforeach; ?>
</ul>
<?php if ($success): ?>
<div class="notice ok"><strong>Installation completed.</strong> The installer is now locked. <a href="/login">Open admin login</a>.</div>
<?php else: ?>
<?php foreach ($errors as $error): ?><div class="notice error"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
<form method="post" autocomplete="off">
<label>Website name</label><input name="app_name" value="ZauberCMS" required>
<label>Website URL</label><input name="app_url" placeholder="https://example.com" required>
<label>Database host</label><input name="db_host" value="127.0.0.1" required>
<label>Database port</label><input name="db_port" value="3306" required>
<label>Database name</label><input name="db_database" required>
<label>Database username</label><input name="db_username" required>
<label>Database password</label><input type="password" name="db_password">
<h2>Administrator</h2>
<label>Admin name</label><input name="admin_name" required autocomplete="name">
<label>Admin email</label><input type="email" name="admin_email" required autocomplete="email">
<label>Admin password</label><input type="password" name="admin_password" minlength="12" required autocomplete="new-password">
<button type="submit">Install ZauberCMS</button>
</form>
<?php endif; ?>
</main></body></html>
