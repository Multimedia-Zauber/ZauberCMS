<?php
/** @var array<string,mixed>|null $page */
$page ??= [];
?>
<article>
<h1><?= htmlspecialchars((string) ($page['title'] ?? $title ?? 'Seite'), ENT_QUOTES, 'UTF-8') ?></h1>
<div><?= (string) ($page['content'] ?? $content ?? '') ?></div>
</article>
