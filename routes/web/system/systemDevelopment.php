<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SystemDevelopment\ModelGenerateController;

Route::middleware(['web', 'auth', 'set_locale'])->group(function () {
    Route::prefix('models-generate')->name('models-generate.')->group(function () {
        Route::controller(ModelGenerateController::class)->group(function () {

            // CRUD Routes
            Route::get('/', 'index')->name('index')->middleware(['permission:system.models.list']);
            Route::get('/create',  'create')->name('create')->middleware(['permission:system.models.create']);
            Route::post('/',  'store')->name('store')->middleware(['permission:system.models.create']);
            Route::get('/{model}',  'show')->name('show')->middleware(['permission:system.models.show']);
            Route::get('/{model}/edit',  'edit')->name('edit')->middleware(['permission:system.models.edit']);
            Route::put('/{model}',  'update')->name('update')->middleware(['permission:system.models.edit']);
            Route::delete('/{model}',  'destroy')->name('destroy')->middleware(['permission:system.models.delete']);

            // Export Routes
            Route::get('/{model}/export/{format}',  'export')->name('export');
        });
    });
});
