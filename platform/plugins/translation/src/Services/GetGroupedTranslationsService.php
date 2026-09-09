<?php

namespace Botble\Translation\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;

class GetGroupedTranslationsService
{
    public function handle(): Collection
    {
        $translations = [];

        foreach ($this->getGroups() as $group) {
            $path = $this->getSourcePath($group);

            if (! $path || ! File::exists($path)) {
                continue;
            }

            $trans = File::getRequire($path);

            if ($trans && is_array($trans)) {
                foreach (Arr::dot($trans) as $key => $value) {
                    if (empty($value)) {
                        continue;
                    }

                    $translations[$group][$key] = $value;
                }
            }
        }

        $translationsCollection = collect();

        foreach ($translations as $group => $items) {
            foreach (Arr::dot($items) as $key => $value) {
                $translationsCollection->push([
                    'group' => $group,
                    'key' => $key,
                    'value' => $value,
                ]);
            }
        }

        return $translationsCollection;
    }

    public function getSourcePath(string $group): ?string
    {
        if (! str_contains($group, DIRECTORY_SEPARATOR)) {
            return lang_path('en' . DIRECTORY_SEPARATOR . $group . '.php');
        }

        $namespace = Str::beforeLast($group, DIRECTORY_SEPARATOR);
        $file = Str::afterLast($group, DIRECTORY_SEPARATOR);

        $namespacePath = Arr::get(Lang::getLoader()->namespaces(), $namespace);

        if (! $namespacePath) {
            return null;
        }

        return $namespacePath . DIRECTORY_SEPARATOR . 'en' . DIRECTORY_SEPARATOR . $file . '.php';
    }

    public function getGroups(): array
    {
        $groups = [];

        if (File::isDirectory(lang_path('en'))) {
            foreach (File::allFiles(lang_path('en')) as $directory) {
                $group = File::name($directory);

                $groups[$group] = $group;
            }
        }

        foreach (Lang::getLoader()->namespaces() as $namespace => $langPath) {
            $defaultLanguage = $langPath . DIRECTORY_SEPARATOR . 'en';

            if (! File::isDirectory($defaultLanguage)) {
                continue;
            }

            foreach (File::allFiles($defaultLanguage) as $directory) {
                $group =  $namespace . DIRECTORY_SEPARATOR . File::name($directory);

                $groups[$group] = $group;
            }
        }

        ksort($groups);

        return $groups;
    }

    public function getSources(): array
    {
        return [
            'core' => trans('plugins/translation::translation.source_core'),
            'package' => trans('plugins/translation::translation.source_package'),
            'plugin' => trans('plugins/translation::translation.source_plugin'),
        ];
    }

    public function getSourceOf(string $group): ?string
    {
        return match (true) {
            Str::startsWith($group, 'core/') => 'core',
            Str::startsWith($group, 'packages/') => 'package',
            Str::startsWith($group, 'plugins/') => 'plugin',
            default => null,
        };
    }

    public function getModuleOf(string $group): ?string
    {
        if (! str_contains($group, '/')) {
            return null;
        }

        $segments = explode('/', $group);

        return $segments[1] ?? null;
    }

    public function getModules(?string $source = null): array
    {
        $modules = [];

        foreach ($this->getGroups() as $group) {
            if ($source !== null && $this->getSourceOf($group) !== $source) {
                continue;
            }

            $module = $this->getModuleOf($group);

            if ($module) {
                $modules[$module] = $module;
            }
        }

        ksort($modules);

        return $modules;
    }

    public function getGroupsFiltered(?string $source = null, ?string $module = null): array
    {
        $groups = [];

        foreach ($this->getGroups() as $group) {
            if ($source !== null && $this->getSourceOf($group) !== $source) {
                continue;
            }

            if ($module !== null && $this->getModuleOf($group) !== $module) {
                continue;
            }

            $groups[$group] = $group;
        }

        return $groups;
    }

    public function getModulesGroupedBySource(): array
    {
        $grouped = [];

        foreach ($this->getGroups() as $group) {
            $source = $this->getSourceOf($group);
            $module = $this->getModuleOf($group);

            if (! $source || ! $module) {
                continue;
            }

            $grouped[$source][$module] = $module;
        }

        foreach ($grouped as $source => $modules) {
            ksort($modules);
            $grouped[$source] = $modules;
        }

        return $grouped;
    }

    public function formatGroupLabel(string $group): string
    {
        if (Str::startsWith($group, 'core/') || Str::startsWith($group, 'packages/')) {
            return Str::headline(Str::slug(Str::afterLast($group, '/'))) . ' (core)';
        }

        if (Str::startsWith($group, 'plugins/')) {
            $plugin = Str::beforeLast(Str::after($group, 'plugins/'), '/');
            $name = Str::afterLast($group, '/');

            if ($plugin === $name) {
                return Str::headline(Str::slug($name));
            }

            return Str::headline(Str::slug($name)) . ' (' . $plugin . ')';
        }

        return $group;
    }
}
