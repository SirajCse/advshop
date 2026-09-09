<?php

use Botble\SslCommerz\Http\Controllers\SslCommerzPaymentController;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Theme::registerRoutes(function (): void {
    Route::group([
        'controller' => SslCommerzPaymentController::class,
        'prefix' => 'sslcommerz/payment',
    ], function (): void {
        Route::post('/success', 'success');
        Route::post('/fail', 'fail');
        Route::post('/cancel', 'cancel');
        Route::post('/ipn', 'ipn');
    });
}, ['core']);
