<?php

declare(strict_types=1);

namespace ZauberCMS\Modules;

use RuntimeException;

final class ModuleManager
{
    /** @var array<string, array<string, mixed>> */
    private array $modules = [];

    public function __construct(
        private readonly string $modulesPath,
        private readonly array $enabledModules = []
    ) {
    }

    /** @return array<string, array<string, mixed>> */
    public function discover(): array
    {
        if (!is_dir($this->modulesPath)) {
            return [];
        }

        foreach (glob(rtrim($this->modulesPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [] as $directory) {
            $manifest = $directory . DIRECTORY_SEPARATOR . 'module.json';
            if (!is_file($manifest)) {
                continue;
            }

            $data = json_decode((string) file_get_contents($manifest), true);
            if (!is_array($data)) {
                throw new RuntimeException('Invalid module manifest: ' . $manifest);
            }

            foreach (['name', 'slug', 'version', 'entry'] as $required) {
                if (!isset($data[$required]) || !is_string($data[$required]) || trim($data[$required]) === '') {
                    throw new RuntimeException(sprintf('Module manifest %s is missing "%s".', $manifest, $required));
                }
            }

            $slug = $data['slug'];
            if (!preg_match('/^[a-z0-9][a-z0-9-]*$/', $slug)) {
                throw new RuntimeException('Invalid module slug: ' . $slug);
            }

            $data['path'] = $directory;
            $data['enabled'] = in_array($slug, $this->enabledModules, true);
            $this->modules[$slug] = $data;
        }

        ksort($this->modules);
        return $this->modules;
    }

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return $this->modules === [] ? $this->discover() : $this->modules;
    }

    /** @return list<ModuleInterface> */
    public function bootEnabled(): array
    {
        $instances = [];

        foreach ($this->all() as $module) {
            if (($module['enabled'] ?? false) !== true) {
                continue;
            }

            $entry = $module['path'] . DIRECTORY_SEPARATOR . ltrim((string) $module['entry'], '/\\');
            $realModulePath = realpath((string) $module['path']);
            $realEntry = realpath($entry);

            if ($realModulePath === false || $realEntry === false || !str_starts_with($realEntry, $realModulePath . DIRECTORY_SEPARATOR)) {
                throw new RuntimeException('Module entry must stay inside its module directory.');
            }

            $instance = require $realEntry;
            if (!$instance instanceof ModuleInterface) {
                throw new RuntimeException(sprintf('Module "%s" must return an instance of %s.', $module['slug'], ModuleInterface::class));
            }

            $instance->register();
            $instances[] = $instance;
        }

        foreach ($instances as $instance) {
            $instance->boot();
        }

        return $instances;
    }
}
