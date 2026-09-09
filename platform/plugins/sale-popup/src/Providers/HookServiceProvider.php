<?php

namespace Botble\SalePopup\Providers;

use Botble\Base\Supports\ServiceProvider;
use Botble\Language\Facades\Language;
use Botble\Setting\Facades\Setting;
use Botble\Theme\Facades\Theme;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Route;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app['events']->listen(RouteMatched::class, function (): void {
            if (defined('THEME_FRONT_FOOTER')) {
                Theme::asset()
                    ->container('footer')
                    ->usePath(false)
                    ->add(
                        'sale-popup-js',
                        asset('vendor/core/plugins/sale-popup/js/sale-popup.js'),
                        ['jquery'],
                        [],
                        '1.2.1'
                    );

                add_filter(
                    THEME_FRONT_FOOTER,
                    function (?string $html) {
                        if (! setting('sale_popup_enabled', 1)) {
                            return $html;
                        }

                        $displayPages = setting('sale_popup_display_pages', '["public.index"]');

                        if (! $displayPages) {
                            return $html;
                        }

                        if (! in_array(Route::currentRouteName(), json_decode($displayPages, true))) {
                            return $html;
                        }

                        return $html . view('plugins/sale-popup::front', [
                            'show_on_mobile' => setting('sale_popup_show_on_mobile', false),
                        ])->render();
                    },
                    1457
                );
            }
        });

        add_filter('sale_popup_setting_key', function (string $key): string {
            if (! is_plugin_active('language') || ! is_plugin_active('language-advanced')) {
                return $key;
            }

            $currentLocale = is_in_admin(true) ? Language::getCurrentAdminLocale() : Language::getCurrentLocale();
            $locale = $currentLocale !== Language::getDefaultLocale() ? $currentLocale : null;

            if ($locale && in_array($locale, array_keys(Language::getSupportedLocales()))) {
                $key = "$key-$locale";

                return Setting::has("$key-$locale") ? "$key-$locale" : $key;
            }

            return $key;
        }, 55);
    }
}
