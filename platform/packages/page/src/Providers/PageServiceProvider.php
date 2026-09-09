<?php

namespace Botble\Page\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\DashboardMenuItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\DataSynchronize\PanelSections\ExportPanelSection;
use Botble\DataSynchronize\PanelSections\ImportPanelSection;
use Botble\Page\Models\Page;
use Botble\Page\Repositories\Eloquent\PageRepository;
use Botble\Page\Repositories\Interfaces\PageInterface;
use Botble\Shortcode\View\View;
use Botble\Theme\Events\RenderingAdminBar;
use Botble\Theme\Facades\AdminBar;
use Illuminate\Support\Facades\View as ViewFacade;

/**
 * @since 02/07/2016 09:50 AM
 */
class PageServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this->app->bind(PageInterface::class, function () {
            return new PageRepository(new Page());
        });

        $this
            ->setNamespace('packages/page')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadHelpers()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->publishAssets()
            ->loadMigrations();

        if (class_exists('ApiHelper')) {
            $this->loadRoutes(['api']);
        }

        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem(
                    DashboardMenuItem::make()
                        ->id('cms-core-page')
                        ->priority(2)
                        ->name('packages/page::pages.menu_name')
                        ->icon('ti ti-notebook')
                        ->route('pages.index')
                        ->permissions('pages.index')
                );
        });

        PanelSectionManager::setGroupId('data-synchronize')->beforeRendering(function (): void {
            PanelSectionManager::default()
                ->registerItem(
                    ExportPanelSection::class,
                    fn () => PanelSectionItem::make('pages')
                        ->setTitle(trans('packages/page::pages.pages'))
                        ->withDescription(trans('packages/page::pages.export.description'))
                        ->withPriority(99)
                        ->withPermission('pages.export')
                        ->withRoute('tools.data-synchronize.export.pages.index')
                )
                ->registerItem(
                    ImportPanelSection::class,
                    fn () => PanelSectionItem::make('pages')
                        ->setTitle(trans('packages/page::pages.pages'))
                        ->withDescription(trans('packages/page::pages.import.description'))
                        ->withPriority(99)
                        ->withPermission('pages.import')
                        ->withRoute('tools.data-synchronize.import.pages.index')
                );
        });

        $this->app['events']->listen(RenderingAdminBar::class, function (): void {
            AdminBar::registerLink(
                trans('packages/page::pages.menu_name'),
                route('pages.create'),
                'add-new',
                'pages.create'
            );
        });

        if (function_exists('shortcode')) {
            ViewFacade::composer(['packages/page::themes.page'], function (View $view): void {
                $view->withShortcodes();
            });
        }

        $this->app->booted(function (): void {
            $this->app->register(HookServiceProvider::class);
        });

        $this->app->register(EventServiceProvider::class);
    }
}
