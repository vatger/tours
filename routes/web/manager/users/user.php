<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Manager\User\UserController;



Route::middleware(['auth:sanctum', 'verified', 'set_locale'])->group(function () {
    Route::prefix('users')->name('users.')->group(function () {
        Route::controller(UserController::class)->group(function () {

            // CRUD Routes
            Route::get('/', 'index')->name('index')->middleware(['permission:users.list']);
            Route::get('/create',  'create')->name('create')->middleware(['permission:users.create']);
            Route::post('/',  'store')->name('store')->middleware(['permission:users.create']);
            Route::get('/{user}',  'show')->name('show')->middleware(['permission:users.show']);
            Route::get('/{user}/edit',  'edit')->name('edit')->middleware(['permission:users.edit']);
            Route::put('/{user}',  'update')->name('update')->middleware(['permission:users.edit']);
            Route::delete('/{user}',  'destroy')->name('destroy')->middleware(['permission:users.delete']);
            // Trashed Routes
            Route::get('/trashed/{user_id}', 'showTrashed')->name('trashed.show')->middleware(['permission:users.trashed.show']);
            Route::put('/trashed/{user_id}', 'restore')->name('trashed.restore')->middleware(['permission:users.trashed.restore']);
            Route::delete('/trashed/{user_id}', 'forceDelete')->name('trashed.destroy')->middleware(['permission:users.trashed.delete']);
            Route::post('/{user_id}/toggle-is-active', 'toggleIsActive')->name('toggle.is.active')->middleware(['permission:users.toggle.is.active']);
            Route::post('/{user_id}/{approve_status}/toggle-approve-status', 'toggleApproveStatus')->name('toggle.approve.status')->middleware(['permission:users.toggle.approve.status']);
        });
    });
});
