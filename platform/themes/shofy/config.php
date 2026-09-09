<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Shortcode\View\View;
use Botble\Theme\Theme;
use Illuminate\View\View as IlluminateView;

return [
    'inherit' => null,

    'events' => [
        'beforeRenderTheme' => function (Theme $theme): void {
            $version = get_cms_version() . '.6';

            if (BaseHelper::isRtlEnabled()) {
                $theme->asset()->usePath()->add('bootstrap', 'plugins/bootstrap/bootstrap.rtl.min.css');
                $theme->asset()->usePath()->add('theme-rtl', 'css/theme-rtl.css', ['theme'], version: $version);
            } else {
                $theme->asset()->usePath()->add('bootstrap', 'plugins/bootstrap/bootstrap.min.css');
            }

            if (is_plugin_active('ecommerce')) {
                EcommerceHelper::registerThemeAssets();
            }

            // animate.css only provides keyframes for entrance animations that run on
            // scroll / slide-change (sticky header, product & banner reveals) or have an
            // explicit opacity:1 fallback (hero slider). None are needed at first paint,
            // so load it from the footer to keep it off the critical render path.
            $theme->asset()->container('footer')->usePath()->add('animate', 'css/animate.css');
            $theme->asset()->usePath()->add('swiper', 'plugins/swiper/swiper-bundle.css');
            $theme->asset()->add('slick-js', 'vendor/core/plugins/ecommerce/libraries/slick/slick.css');
            $theme->asset()->usePath()->add('theme', 'css/theme.css', version: $version);

            $theme->asset()->container('footer')->usePath()->add('jquery', 'js/jquery-3.7.1.min.js');
            $theme->asset()->container('footer')->usePath()->add('bootstrap', 'plugins/bootstrap/bootstrap.min.js', attributes: ['defer']);
            $theme->asset()->container('footer')->usePath()->add('meanmenu', 'js/meanmenu.js', attributes: ['defer']);
            $theme->asset()->container('footer')->usePath()->add('swiper', 'plugins/swiper/swiper-bundle.js', attributes: ['defer']);
            $theme->asset()->container('footer')->add('slick-js', 'vendor/core/plugins/ecommerce/libraries/slick/slick.min.js', attributes: ['defer']);
            $theme->asset()->container('footer')->usePath()->add('countdown', 'js/countdown.js', attributes: ['defer']);
            $theme->asset()->container('footer')->usePath()->add('theme', 'js/theme.js', attributes: ['defer'], version: $version);
            $theme->asset()->container('footer')->usePath()->add('ecommerce', 'js/ecommerce.js', ['front-ecommerce-js'], attributes: ['defer'], version: $version);

            $theme->asset()->container('footer')->remove('language-public-js');
            $theme->asset()->remove('language-css');

            if (function_exists('shortcode')) {
                $theme->composer([
                    'page',
                    'post',
                    'ecommerce.product',
                    'ecommerce.products',
                    'ecommerce.product-category',
                    'ecommerce.product-tag',
                    'ecommerce.brand',
                    'ecommerce.search',
                    'ecommerce.cart',
                    'marketplace.stores',
                    'marketplace.store',
                ], function (View $view): void {
                    $view->withShortcodes();
                });
            }

            $theme->partialComposer('header.*', function (IlluminateView $view): void {
                $headerTopBackgroundColor = theme_option('header_top_background_color', '#010f1c');
                $headerTopTextColor = theme_option('header_top_text_color', '#fff');
                $headerMainBackgroundColor = theme_option('header_main_background_color', '#fff');
                $headerMainTextColor = theme_option('header_main_text_color', '#010f1c');

                $view->with(compact(
                    'headerTopBackgroundColor',
                    'headerTopTextColor',
                    'headerMainBackgroundColor',
                    'headerMainTextColor'
                ));
            });
        },
    ],
];
