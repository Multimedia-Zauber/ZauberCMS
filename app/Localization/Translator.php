<?php

declare(strict_types=1);

namespace ZauberCMS\Localization;

use RuntimeException;

final class Translator
{
    /** @var array<string, array<string, array<string, mixed>>> */
    private array $catalogues = [];

    public function __construct(
        private readonly string $langPath,
        private readonly string $defaultLocale = 'de',
        private readonly string $fallbackLocale = 'de',
        private readonly array $supportedLocales = ['de', 'fr', 'en'],
        private string $locale = 'de',
    ) {
        $this->locale = $this->normalizeLocale($locale);
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $this->normalizeLocale($locale);
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function translate(string $key, array $replace = []): string
    {
        $value = $this->resolve($this->locale, $key)
            ?? $this->resolve($this->fallbackLocale, $key)
            ?? $key;

        if (!is_string($value)) {
            return $key;
        }

        foreach ($replace as $name => $replacement) {
            $value = str_replace(':' . $name, (string) $replacement, $value);
        }

        return $value;
    }

    private function normalizeLocale(string $locale): string
    {
        $locale = strtolower(trim($locale));

        return in_array($locale, $this->supportedLocales, true)
            ? $locale
            : $this->defaultLocale;
    }

    private function resolve(string $locale, string $key): mixed
    {
        [$group, $item] = array_pad(explode('.', $key, 2), 2, null);
        if ($group === '' || $item === null || $item === '') {
            return null;
        }

        $catalogue = $this->loadGroup($locale, $group);
        $value = $catalogue;

        foreach (explode('.', $item) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return null;
            }
            $value = $value[$segment];
        }

        return $value;
    }

    private function loadGroup(string $locale, string $group): array
    {
        if (isset($this->catalogues[$locale][$group])) {
            return $this->catalogues[$locale][$group];
        }

        if (!preg_match('/^[A-Za-z0-9_-]+$/', $group)) {
            throw new RuntimeException('Invalid translation group.');
        }

        $file = rtrim($this->langPath, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . $locale
            . DIRECTORY_SEPARATOR . $group . '.php';

        if (!is_file($file)) {
            return $this->catalogues[$locale][$group] = [];
        }

        $translations = require $file;
        if (!is_array($translations)) {
            throw new RuntimeException('Translation file must return an array: ' . $file);
        }

        return $this->catalogues[$locale][$group] = $translations;
    }
}
