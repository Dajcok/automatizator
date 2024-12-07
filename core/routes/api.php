<?php

use App\Http\Controllers\Acl\AclController;
use App\Http\Controllers\Core\ModelConfigController;
use App\Http\Controllers\Core\SubmissionController;
use App\Http\Controllers\Of\OFDataController;
use App\Http\Controllers\Of\OFDefinitionController;
use App\Http\Controllers\Of\OrbeonProxyController;
use Illuminate\Support\Facades\Route;

Route::withoutMiddleware([\Illuminate\Session\Middleware\StartSession::class])->prefix('api')->group(function () {
    Route::prefix('of')->group(function () {
        Route::prefix('definition')->group(function () {
            Route::get('/{app}', [OFDefinitionController::class, 'index']);
            Route::get('/{app}/new', [OFDefinitionController::class, 'newForm']);
            Route::get('/{app}/{form}/render', [OFDefinitionController::class, 'render']);
            Route::get('/{app}/{form}', [OFDefinitionController::class, 'show']);
            Route::get('/{app}/{form}/related-templates', [OFDefinitionController::class, 'getRelatedTemplateFormNames']);
            Route::delete('/{app}/{form}', [OFDefinitionController::class, 'destroy']);
            Route::get('/{app}/{docId}/edit', [OFDefinitionController::class, 'editForm']);
            Route::get('/{app}/{form}/{docId}/edit', [OFDefinitionController::class, 'renderEdit']);
        });

        Route::prefix('data')->group(function () {
            Route::get('/by-document-id/{documentId}', [OFDataController::class, 'showByDocumentId']);
            Route::put('/{app}/{form}/{document}', [OFDataController::class, 'save']);
            Route::get('/{app}/{form}', [OFDataController::class, 'index']);
            Route::delete('/{id}', [OFDataController::class, 'destroy']);
            Route::get('/{id}', [OFDataController::class, 'show']);
            Route::post('/{app}/{form}', [OFDataController::class, 'store']);
        });
    });

    Route::prefix("acl")->group(function () {
       Route::post("/migrate/{integrationName}", [AclController::class, "migrate"]);
    });

    Route::prefix("core")->group(function () {
        Route::prefix("model-config")->group(function () {
            Route::get("/", [ModelConfigController::class, "index"]);
            Route::post("/", [ModelConfigController::class, "store"]);
            Route::get("/{id}", [ModelConfigController::class, "show"]);
            Route::get("/{app}/{form}", [ModelConfigController::class, "showWithFormNameAndAppName"]);
            Route::put("/{id}", [ModelConfigController::class, "update"]);
            Route::delete("/{id}", [ModelConfigController::class, "destroy"]);
        });

        Route::prefix("submissions")->group(function () {
            Route::put("/{documentId}", [SubmissionController::class, "update"]);
        });
    });
});

Route::prefix('orbeon')->group(function () {
    Route::get('/{path}', [OrbeonProxyController::class, 'get'])
        ->where('path', '.*');
    Route::post('/{path}', [OrbeonProxyController::class, 'post'])
        ->where('path', '.*');
});
