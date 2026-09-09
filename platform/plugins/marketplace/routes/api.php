<?php

use Botble\Ecommerce\Http\Middleware\ApiCurrencyMiddleware;
use Botble\Marketplace\Http\Controllers\API\StoreController;
use Botble\Marketplace\Http\Controllers\API\VendorController;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['api', ApiCurrencyMiddleware::class],
    'prefix' => 'api/v1/marketplace',
], function (): void {
    // Public endpoints
    Route::get('stores', [StoreController::class, 'index']);
    Route::get('stores/{slug}', [StoreController::class, 'show']);
    Route::post('vendors/register', [VendorController::class, 'register']);

    // Optional auth - contact store (guest or logged in)
    Route::group(['middleware' => ['api.optional.auth']], function (): void {
        Route::post('stores/{id}/contact', [StoreController::class, 'contact'])->wherePrimaryKey();
    });

    // Required auth - become vendor (existing customer)
    Route::group(['middleware' => ['auth:sanctum']], function (): void {
        Route::post('become-vendor', [VendorController::class, 'becomeVendor']);
    });
});
