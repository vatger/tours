<?php

use App\Http\Controllers\Api\V1\Gender\GenderController;
use Illuminate\Support\Facades\Route;




Route::middleware(['auth:sanctum', 'verified', 'set_locale'])->group(function () {
    Route::prefix('genders')->name('genders.')->group(function () {
        Route::controller(GenderController::class)->group(function () {

            // CRUD Routes
            Route::get('/', 'index')->name('index')->middleware(['permission:genders.list']);
            Route::get('/create',  'create')->name('create')->middleware(['permission:genders.create']);
            Route::post('/',  'store')->name('store')->middleware(['permission:genders.create']);
            Route::get('/{gender}',  'show')->name('show')->middleware(['permission:genders.show']);
            Route::get('/{gender}/edit',  'edit')->name('edit')->middleware(['permission:genders.edit']);
            Route::put('/{gender_id}',  'update')->name('update')->middleware(['permission:genders.edit']);
            Route::delete('/{gender}',  'destroy')->name('destroy')->middleware(['permission:genders.delete']);
            // Trashed Routes
            Route::get('/trashed/{gender_id}', 'showTrashed')->name('trashed.show')->middleware(['permission:genders.trashed.show']);
            Route::put('/trashed/{gender_id}', 'restore')->name('trashed.restore')->middleware(['permission:genders.trashed.restore']);
            Route::delete('/trashed/{gender_id}', 'forceDelete')->name('trashed.destroy')->middleware(['permission:genders.trashed.delete']);
            Route::post('/{gender_id}/toggle-is-active', 'toggleIsActive')->name('toggle.is.active')->middleware(['permission:genders.toggle.is.active']);
        });
    });
});
