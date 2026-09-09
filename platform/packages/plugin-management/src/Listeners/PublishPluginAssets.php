<?php

namespace Botble\PluginManagement\Listeners;

use Botble\PluginManagement\Services\PluginService;

class PublishPluginAssets
{
    public function __construct(protected PluginService $pluginService)
    {
    }

    public function handle(): void
    {
        foreach (PluginService::getInstalledPlugins() as $plugin) {
            $this->pluginService->publishAssets($plugin);
        }
    }
}
