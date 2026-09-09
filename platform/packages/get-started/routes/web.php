<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\GetStarted\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group(['prefix' => 'get-started'], function (): void {
            Route::post('save', [
                'as' => 'get-started.save',
                'uses' => 'GetStartedController@save',
                'permission' => false,
                'middleware' => 'preventDemo',
            ]);

            Route::post('dismiss', [
                'as' => 'get-started.dismiss',
                'uses' => 'GetStartedController@dismiss',
                'permission' => false,
                'middleware' => 'preventDemo',
            ]);
        });
    });
});
