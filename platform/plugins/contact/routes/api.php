<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'api',
    'prefix' => 'api/v1',
    'namespace' => 'Botble\Contact\Http\Controllers\API',
], function (): void {
    Route::post('contacts', 'ContactController@store')->middleware('throttle:5,1');
});
