<?php

namespace Botble\Theme\Providers;

use Botble\Base\Facades\BaseHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Move base routes to a service provider to make sure all filters & actions can hook to base routes
     */
    public function boot(): void
    {
        $this->app->booted(function (): void {
            if (config('core.base.general.disable_front_theme')) {
                $this->registerApiModeRoutes();

                return;
            }

            $this->loadRoutesFromTheme(Theme::getThemeName());

            if (Theme::hasInheritTheme()) {
                $this->loadRoutesFromTheme(Theme::getInheritTheme());
            }
        });
    }

    protected function loadRoutesFromTheme(string $theme): void
    {
        $routeFilePath = theme_path($theme . '/routes/web.php');

        if ($routeFilePath && $this->app['files']->exists($routeFilePath)) {
            $this->loadRoutesFrom($routeFilePath);
        }
    }

    protected function registerApiModeRoutes(): void
    {
        Route::middleware(['web', 'core'])->group(function (): void {
            Route::get('/', function () {
                if (empty(BaseHelper::getAdminPrefix())) {
                    return redirect()->route('access.login');
                }

                return response()->view('core/base::errors.403-api-mode', [], 403);
            })->name('public.index');

            Route::fallback(function () {
                if (empty(BaseHelper::getAdminPrefix())) {
                    return redirect()->route('access.login');
                }

                return response()->view('core/base::errors.403-api-mode', [], 403);
            });
        });
    }
}
