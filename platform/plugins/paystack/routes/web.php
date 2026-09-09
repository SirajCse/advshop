<?php

use Botble\Paystack\Http\Controllers\PaystackController;
use Botble\Theme\Facades\Theme;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::post('paystack/webhook', [PaystackController::class, 'webhook'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('paystack.webhook');

Theme::registerRoutes(function (): void {
    Route::group(['namespace' => 'Botble\Paystack\Http\Controllers'], function (): void {
        Route::get('paystack/payment/callback', [
            'as' => 'paystack.payment.callback',
            'uses' => 'PaystackController@getPaymentStatus',
        ]);
    });
});
