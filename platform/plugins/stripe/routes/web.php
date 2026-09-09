<?php

use Botble\Stripe\Http\Controllers\StripeController;
use Botble\Theme\Facades\Theme;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::prefix('payment/stripe')
    ->name('payments.stripe.')
    ->group(function (): void {
        Route::post('webhook', [StripeController::class, 'webhook'])
            ->withoutMiddleware([VerifyCsrfToken::class])
            ->name('webhook');
    });

Theme::registerRoutes(function (): void {
    Route::prefix('payment/stripe')
        ->name('payments.stripe.')
        ->group(function (): void {
            Route::get('success', [StripeController::class, 'success'])->name('success');
            Route::get('error', [StripeController::class, 'error'])->name('error');
        });
});
