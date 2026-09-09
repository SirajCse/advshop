<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Page\Http\Controllers\ExportPageController;
use Botble\Page\Http\Controllers\ImportPageController;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\Page\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group(['prefix' => 'pages', 'as' => 'pages.'], function (): void {
            Route::resource('', 'PageController')->parameters(['' => 'page']);

            // Visual builder routes
            Route::get('{page}/visual-builder', [
                'as' => 'visual-builder',
                'uses' => 'PageController@visualBuilder',
                'permission' => 'pages.edit',
            ]);

            Route::match(['get', 'post'], '{page}/preview', [
                'as' => 'preview',
                'uses' => 'PageController@preview',
                'permission' => 'pages.edit',
            ]);

            Route::post('{page}/visual-builder/save', [
                'as' => 'visual-builder.save',
                'uses' => 'PageController@saveVisualBuilder',
                'permission' => 'pages.edit',
            ]);

            Route::post('visual-builder/render-items', [
                'as' => 'visual-builder.render-items',
                'uses' => 'PageController@renderShortcodeItems',
                'permission' => 'pages.edit',
            ]);

            Route::post('visual-builder/render-types', [
                'as' => 'visual-builder.render-types',
                'uses' => 'PageController@renderShortcodeTypes',
                'permission' => 'pages.edit',
            ]);
        });

        Route::prefix('tools/data-synchronize')->name('tools.data-synchronize.')->group(function (): void {
            Route::prefix('export')->name('export.')->group(function (): void {
                Route::group(['prefix' => 'pages', 'as' => 'pages.', 'permission' => 'pages.export'], function (): void {
                    Route::get('/', [ExportPageController::class, 'index'])->name('index');
                    Route::post('/', [ExportPageController::class, 'store'])->name('store');
                });
            });

            Route::prefix('import')->name('import.')->group(function (): void {
                Route::group(['prefix' => 'pages', 'as' => 'pages.', 'permission' => 'pages.import'], function (): void {
                    Route::get('/', [ImportPageController::class, 'index'])->name('index');
                    Route::post('/', [ImportPageController::class, 'import'])->name('store');
                    Route::post('validate', [ImportPageController::class, 'validateData'])->name('validate');
                    Route::post('download-example', [ImportPageController::class, 'downloadExample'])->name('download-example');
                });
            });
        });
    });
});
