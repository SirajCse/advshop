<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Base\Http\Middleware\RequiresJsonRequestMiddleware;
use Botble\SalePopup\Http\Controllers\SalePopupController;
use Botble\SalePopup\Http\Controllers\Settings\SalePopupSettingController;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::group(['prefix' => 'settings/sale-popup', 'as' => 'sale-popup.settings', 'permission' => 'sale-popup.settings'], function (): void {
        Route::get('/', [SalePopupSettingController::class, 'edit']);
        Route::put('/', [SalePopupSettingController::class, 'update'])->name('.edit');
    });
});

Theme::registerRoutes(function (): void {
    Route::get('ajax/sale-popup/products', [SalePopupController::class, 'ajaxSalePopup'])
        ->middleware(RequiresJsonRequestMiddleware::class)
        ->name('public.ajax.sale-popup');
});
