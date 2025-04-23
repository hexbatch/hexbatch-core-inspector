<?php

use App\Http\Controllers\ThingTestController;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;


Route::prefix('users')->group(function () {
    Route::post('login', [Api\UserController::class, 'login'])->name('api.users.login');
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [Api\UserController::class, 'me'])->name('api.users.me');
    });
});



Route::middleware(['auth:sanctum','thing_owner'])->group(function () {
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
});
