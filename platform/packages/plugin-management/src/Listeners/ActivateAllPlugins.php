<?php

namespace Botble\PluginManagement\Listeners;

use Botble\Base\Facades\BaseHelper;
use Botble\PluginManagement\Services\PluginService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class ActivateAllPlugins
{
    public function handle(): void
    {
        try {
            $plugins = array_values(BaseHelper::scanFolder(plugin_path()));

            if (empty($plugins)) {
                return;
            }

            $pluginService = app(PluginService::class);

            $plugins = $this->sortByDependencies($plugins, $pluginService);

            foreach ($plugins as $plugin) {
                $pluginService->activate($plugin);
            }

            Artisan::call('migrate', ['--force' => true]);
        } catch (Throwable $exception) {
            BaseHelper::logError($exception);
        }
    }

    protected function sortByDependencies(array $plugins, PluginService $pluginService): array
    {
        $dependencies = [];

        foreach ($plugins as $plugin) {
            $pluginInfo = $pluginService->getPluginInfo($plugin);
            $requires = $pluginInfo['require'] ?? [];

            $dependencies[$plugin] = array_map(
                fn ($require) => Arr::last(explode('/', $require)),
                $requires
            );
        }

        $sorted = [];
        $visited = [];

        $visit = function (string $plugin) use (&$visit, &$sorted, &$visited, $dependencies, $plugins): void {
            if (isset($visited[$plugin])) {
                return;
            }

            $visited[$plugin] = true;

            foreach ($dependencies[$plugin] ?? [] as $dependency) {
                if (in_array($dependency, $plugins)) {
                    $visit($dependency);
                }
            }

            $sorted[] = $plugin;
        };

        foreach ($plugins as $plugin) {
            $visit($plugin);
        }

        return $sorted;
    }
}
