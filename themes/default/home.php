<?php
/** @var string|null $content */
?>
<section>
<h1><?= htmlspecialchars((string) ($title ?? 'Willkommen bei ZauberCMS'), ENT_QUOTES, 'UTF-8') ?></h1>
<div><?= (string) ($content ?? '<p>Das Default-Theme ist aktiv.</p>') ?></div>
</section>
