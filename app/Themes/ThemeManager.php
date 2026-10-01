<?php

declare(strict_types=1);

namespace ZauberCMS\Themes;

use RuntimeException;

final class ThemeManager
{
    public function __construct(
        private readonly string $themesPath,
        private string $activeTheme = 'default',
    ) {
    }

    public function setActiveTheme(string $theme): void
    {
        $this->assertSafeName($theme);
        $this->activeTheme = $theme;
    }

    public function activeTheme(): string
    {
        return $this->activeTheme;
    }

    /** @return array<string, mixed> */
    public function metadata(?string $theme = null): array
    {
        $theme ??= $this->activeTheme;
        $this->assertSafeName($theme);

        $file = $this->themePath($theme) . DIRECTORY_SEPARATOR . 'theme.json';
        if (!is_file($file)) {
            throw new RuntimeException('Theme metadata not found: ' . $theme);
        }

        $data = json_decode((string) file_get_contents($file), true);
        if (!is_array($data) || !isset($data['name'], $data['version'])) {
            throw new RuntimeException('Invalid theme metadata: ' . $theme);
        }

        return $data;
    }

    public function render(string $template, array $data = []): string
    {
        $file = $this->resolveTemplate($template);
        extract($data, EXTR_SKIP);

        ob_start();
        require $file;
        return (string) ob_get_clean();
    }

    public function resolveTemplate(string $template): string
    {
        $this->assertSafeName($template);

        $active = $this->themePath($this->activeTheme) . DIRECTORY_SEPARATOR . $template . '.php';
        if (is_file($active)) {
            return $active;
        }

        $fallback = $this->themePath('default') . DIRECTORY_SEPARATOR . $template . '.php';
        if (is_file($fallback)) {
            return $fallback;
        }

        throw new RuntimeException('Theme template not found: ' . $template);
    }

    public function asset(string $path): string
    {
        $clean = ltrim(str_replace('\\', '/', $path), '/');
        if (str_contains($clean, '..')) {
            throw new RuntimeException('Invalid theme asset path.');
        }

        return '/themes/' . rawurlencode($this->activeTheme) . '/assets/' . $clean;
    }

    private function themePath(string $theme): string
    {
        $this->assertSafeName($theme);
        return rtrim($this->themesPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $theme;
    }

    private function assertSafeName(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $name)) {
            throw new RuntimeException('Invalid theme or template name.');
        }
    }
}
