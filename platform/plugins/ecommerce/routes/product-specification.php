<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Ecommerce\Http\Controllers\ExportProductSpecificationController;
use Botble\Ecommerce\Http\Controllers\ImportProductSpecificationController;
use Botble\Ecommerce\Http\Controllers\SpecificationAttributeController;
use Botble\Ecommerce\Http\Controllers\SpecificationGroupController;
use Botble\Ecommerce\Http\Controllers\SpecificationTableController;
use Botble\Ecommerce\Http\Middleware\CheckProductSpecificationEnabledMiddleware;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::prefix('ecommerce')
        ->name('ecommerce.')
        ->middleware(CheckProductSpecificationEnabledMiddleware::class)
        ->group(function (): void {
            Route::prefix('specification-groups')->name('specification-groups.')->group(function (): void {
                Route::resource('/', SpecificationGroupController::class)->parameters(['' => 'group']);
            });
            Route::prefix('specification-attributes')->name('specification-attributes.')->group(function (): void {
                Route::resource('/', SpecificationAttributeController::class)->parameters(['' => 'attribute']);
            });
            Route::prefix('specification-tables')->name('specification-tables.')->group(function (): void {
                Route::resource('/', SpecificationTableController::class)->parameters(['' => 'table']);
            });
        });

    Route::middleware(CheckProductSpecificationEnabledMiddleware::class)->group(function (): void {
        Route::group(['prefix' => 'tools/data-synchronize/import/product-specifications', 'as' => 'ecommerce.product-specifications.import.', 'permission' => 'ecommerce.product-specifications.import'], function (): void {
            Route::get('/', [ImportProductSpecificationController::class, 'index'])->name('index');
            Route::post('validate', [ImportProductSpecificationController::class, 'validateData'])->name('validate');
            Route::post('import', [ImportProductSpecificationController::class, 'import'])->name('store');
            Route::post('download-example', [ImportProductSpecificationController::class, 'downloadExample'])->name('download-example');
        });

        Route::group(['prefix' => 'tools/data-synchronize/export/product-specifications', 'as' => 'ecommerce.product-specifications.export.', 'permission' => 'ecommerce.product-specifications.export'], function (): void {
            Route::get('/', [ExportProductSpecificationController::class, 'index'])->name('index');
            Route::post('/', [ExportProductSpecificationController::class, 'store'])->name('store');
        });
    });
});
