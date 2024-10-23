<?php

use App\Http\Controllers\Core\ModelConfigController;
use App\Http\Controllers\Of\OFDataController;
use App\Http\Controllers\Of\OFDefinitionController;
use Illuminate\Support\Facades\Route;


Route::prefix('api')->group(function () {
    Route::prefix('of')->group(function () {
        Route::prefix('definition')->group(function () {
            Route::get('/{app}', [OFDefinitionController::class, 'index']);
            Route::get('/{app}/{form}/render', [OFDefinitionController::class, 'render']);
            Route::get('/{app}/{form}', [OFDefinitionController::class, 'show']);
            Route::get('/{app}/new', [OFDefinitionController::class, 'newForm']);
            Route::get('/{app}/{docId}/edit', [OFDefinitionController::class, 'editForm']);
        });

        Route::prefix('data')->group(function () {
            Route::put('/{app}/{form}/{document}', [OFDataController::class, 'save']);
            Route::get('/{app}/{form}', [OFDataController::class, 'index']);
        });
    });

    Route::prefix("core")->group(function () {
        Route::prefix("model-config")->group(function () {
            Route::get("/", [ModelConfigController::class, "index"]);
            Route::post("/", [ModelConfigController::class, "store"]);
            Route::get("/{id}", [ModelConfigController::class, "show"]);
            Route::put("/{id}", [ModelConfigController::class, "update"]);
            Route::delete("/{id}", [ModelConfigController::class, "destroy"]);
        });
    });
});
