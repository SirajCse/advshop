<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Ecommerce\Http\Controllers\CustomerCartController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(
    function (): void {
        Route::group(['prefix' => 'customer-carts', 'as' => 'ecommerce.customer-carts.'], function (): void {
            Route::match(['GET', 'POST'], '/', [CustomerCartController::class, 'index'])
                ->name('index')
                ->permission('ecommerce.customer-carts.index');

            Route::delete('{identifier}/{instance}', [CustomerCartController::class, 'destroy'])
                ->name('destroy')
                ->permission('ecommerce.customer-carts.destroy');
        });
    }
);
