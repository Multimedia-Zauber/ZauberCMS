<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars((string) ($title ?? 'ZauberCMS'), ENT_QUOTES, 'UTF-8') ?></title>
<link rel="stylesheet" href="<?= htmlspecialchars((string) ($themeAsset ?? '/themes/default/assets/css/app.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
<header><strong>ZauberCMS</strong></header>
<main>
