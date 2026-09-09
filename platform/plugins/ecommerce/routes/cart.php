<?php

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Controllers\Fronts\PublicCartController;
use Botble\Ecommerce\Http\Middleware\CheckCartEnabledMiddleware;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Theme::registerRoutes(function (): void {
    Route::middleware(CheckCartEnabledMiddleware::class)
        ->controller(PublicCartController::class)
        ->prefix(EcommerceHelper::getPageSlug('cart'))
        ->name('public.')
        ->group(function (): void {
            Route::get('/', 'index')->name('cart');
            Route::get('add/{product}', 'addByUrl')->name('cart.add-by-url');
            Route::post('add-to-cart', 'store')->name('cart.add-to-cart');
            Route::post('update', 'update')->name('cart.update');
            Route::get('remove/{id}', 'destroy')->name('cart.remove');
            Route::get('destroy', 'empty')->name('cart.destroy');
        });

    Route::controller(PublicCartController::class)
        ->prefix('abandoned-cart')
        ->name('public.abandoned-cart.')
        ->group(function (): void {
            Route::get('unsubscribe/{token}', 'unsubscribe')->name('unsubscribe');
        });
});
