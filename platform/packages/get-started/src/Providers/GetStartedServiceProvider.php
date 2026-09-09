<?php

namespace Botble\GetStarted\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Dashboard\Events\RenderingDashboardWidgets;
use Botble\GetStarted\Supports\GetStartedHelper;
use Illuminate\Support\Facades\Auth;

class GetStartedServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this->setNamespace('packages/get-started')
            ->loadAndPublishConfigurations(['general'])
            ->loadAndPublishTranslations()
            ->publishAssets()
            ->loadRoutes()
            ->loadAndPublishViews();

        $this->app['events']->listen(RenderingDashboardWidgets::class, function (): void {
            add_action(DASHBOARD_ACTION_REGISTER_SCRIPTS, function (): void {
                if ($this->shouldShowGetStartedPopup()) {
                    Assets::addScriptsDirectly('vendor/core/packages/get-started/js/get-started.js')
                        ->addStylesDirectly('vendor/core/packages/get-started/css/get-started.css')
                        ->addScripts('jquery-ui');

                    add_filter(BASE_FILTER_FOOTER_LAYOUT_TEMPLATE, function ($html) {
                        return $html . view('packages/get-started::index', [
                            'shouldChangeAccount' => GetStartedHelper::shouldChangeDefaultAccount(),
                        ])->render();
                    }, 120);

                    add_filter(DASHBOARD_FILTER_ADMIN_NOTIFICATIONS, function ($html) {
                        return $html . view('packages/get-started::setup-wizard-notice')->render();
                    }, 4);
                }
            }, 234);
        });
    }

    protected function shouldShowGetStartedPopup(): bool
    {
        return $this->app['config']->get('packages.get-started.general.enabled', true) &&
            ! BaseHelper::hasDemoModeEnabled() &&
            is_in_admin(true) &&
            Auth::guard()->check() &&
            setting('admin_appearance_show_get_started', '1') == '1' &&
            setting('is_completed_get_started') != '1';
    }
}
