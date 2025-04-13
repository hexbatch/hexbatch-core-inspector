<?php

use App\Http\Controllers\ThingTestController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('things')->group(function () {

    Route::prefix('actions')->group(function () {
        Route::post('create', [ThingTestController::class, 'create_action'])->name('api.things.actions.create');
        Route::post('create_canned', [ThingTestController::class, 'create_canned_action'])->name('api.things.actions.create_canned');
        Route::prefix('{test_action}')->group(function () {
            Route::post('clone', [ThingTestController::class, 'create_cloned_action'])->name('api.things.actions.clone');
            Route::post('make_thing', [ThingTestController::class, 'make_thing'])->name('api.things.actions.make_thing');
            Route::get('show', [ThingTestController::class, 'show_action'])->name('api.things.actions.show');
            Route::put('update', [ThingTestController::class, 'update_action'])->name('api.things.actions.update');
            Route::delete('destroy', [ThingTestController::class, 'destroy_action'])->name('api.things.actions.destroy');
        });
    });



});
