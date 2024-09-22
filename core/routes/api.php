<?php

use App\Http\Controllers\OFDataController;
use App\Http\Controllers\OFDefinitionController;
use App\Http\Controllers\OrbeonProxyController;
use Illuminate\Support\Facades\Route;


Route::prefix('api/of')->group(function () {
    Route::prefix('definition')->group(function () {
        Route::get('/', [OFDefinitionController::class, 'index']);
        Route::post('/{app}/{form}/render', [OFDefinitionController::class, 'render']);
    });

    Route::prefix('data')->group(function () {
        Route::put('/{app}/{form}/{document}', [OFDataController::class, 'save']);
        Route::get('/{app}/{form}', [OFDataController::class, 'index']);
    });
});

Route::prefix('orbeon')->group(function () {
    Route::get('/{path}', [OrbeonProxyController::class, 'get'])
        ->where('path', '.*');
    Route::post('/{path}', [OrbeonProxyController::class, 'post'])
        ->where('path', '.*');
});
