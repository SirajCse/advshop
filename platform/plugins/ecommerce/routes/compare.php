<?php

use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Controllers\Fronts\CompareController;
use Botble\Ecommerce\Http\Middleware\CheckCompareEnabledMiddleware;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Theme::registerRoutes(function (): void {
    Route::middleware(CheckCompareEnabledMiddleware::class)
        ->controller(CompareController::class)
        ->prefix(EcommerceHelper::getPageSlug('compare'))
        ->name('public.')
        ->group(function (): void {
            Route::get('/', 'index')->name('compare');

            // Search + paste-URL are unauthenticated AJAX endpoints; throttle
            // them so they cannot be abused as a catalog scraper / DoS vector.
            Route::middleware('throttle:30,1')->group(function (): void {
                Route::get('search-products', 'searchProducts')->name('compare.search-products');
                Route::post('add-by-url', 'addByUrl')->name('compare.add-by-url');
            });

            Route::post('{productId}', 'store')->name('compare.add')->wherePrimaryKey('productId');
            Route::delete('{productId}', 'destroy')->name('compare.remove')->wherePrimaryKey('productId');
            Route::get('{slugs}', 'indexBySlugs')
                ->name('compare.shared')
                ->where('slugs', '.*-vs-.*');
        });
});
