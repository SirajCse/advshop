<?php

use Botble\PayPal\Http\Controllers\PayPalController;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Theme::registerRoutes(function (): void {
    Route::get('payment/paypal/status', [PayPalController::class, 'getCallback'])->name('payments.paypal.status');
});
