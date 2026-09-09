<?php

namespace Botble\PayPal\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Composer\Autoload\ClassLoader;
use Illuminate\Support\ServiceProvider;

class PayPalServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $libPath = plugin_path('paypal/lib');

        $loader = new ClassLoader();
        $loader->addPsr4('PayPalCheckoutSdk\\', $libPath . '/PayPalCheckoutSdk');
        $loader->addPsr4('PayPalHttp\\', $libPath . '/PayPalHttp');
        $loader->register(true);
    }

    public function boot(): void
    {
        if (! is_plugin_active('payment')) {
            return;
        }

        $this->setNamespace('plugins/paypal')
            ->loadHelpers()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->publishAssets();

        $this->app->register(HookServiceProvider::class);
    }
}
