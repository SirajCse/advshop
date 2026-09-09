<?php

namespace Botble\Sitemap\Providers;

use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\PanelSections\SettingCommonPanelSection;
use Botble\Sitemap\Commands\IndexNowSubmissionCommand;
use Botble\Sitemap\Services\IndexNowService;
use Botble\Sitemap\Sitemap;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Foundation\Application;

class SitemapServiceProvider extends ServiceProvider implements DeferrableProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->bind('sitemap', function (Application $app) {
            $config = $app['config']->get('packages.sitemap.config', []);

            return new Sitemap(
                $config,
                $app[Repository::class],
                $app['config'],
                $app['files'],
                $app[ResponseFactory::class],
                $app['view']
            );
        });

        $this->app->alias('sitemap', Sitemap::class);

        $this->app->singleton(IndexNowService::class);
    }

    public function boot(): void
    {
        $this
            ->setNamespace('packages/sitemap')
            ->loadAndPublishConfigurations(['config'])
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->publishAssets();

        if ($this->app->runningInConsole()) {
            $this->commands([
                IndexNowSubmissionCommand::class,
            ]);

            $this->app->afterResolving(Schedule::class, function (Schedule $schedule): void {
                $schedule
                    ->command('sitemap:indexnow')
                    ->dailyAt('02:00')
                    ->when(fn () => app(IndexNowService::class)->isEnabled());
            });
        }

        PanelSectionManager::default()->beforeRendering(function (): void {
            PanelSectionManager::registerItem(
                SettingCommonPanelSection::class,
                function () {
                    return PanelSectionItem::make('sitemap')
                        ->setTitle(trans('packages/sitemap::sitemap.settings.title'))
                        ->withIcon('ti ti-sitemap')
                        ->withDescription(trans('packages/sitemap::sitemap.settings.description'))
                        ->withPriority(1000)
                        ->withRoute('sitemap.settings');
                }
            );
        });
    }

    public function provides(): array
    {
        return ['sitemap', Sitemap::class];
    }
}
