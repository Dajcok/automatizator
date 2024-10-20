<?php

use App\Http\Controllers\OFDataController;
use App\Http\Controllers\OFDefinitionController;
use Illuminate\Support\Facades\Route;


Route::prefix('api/of')->group(function () {
    Route::prefix('definition')->group(function () {
        Route::get('/{app}', [OFDefinitionController::class, 'index']);
        Route::get('/{app}/{form}/render', [OFDefinitionController::class, 'render']);
        Route::get('/{app}/new', [OFDefinitionController::class, 'newForm']);
    });

    Route::prefix('data')->group(function () {
        Route::put('/{app}/{form}/{document}', [OFDataController::class, 'save']);
        Route::get('/{app}/{form}', [OFDataController::class, 'index']);
    });
});
