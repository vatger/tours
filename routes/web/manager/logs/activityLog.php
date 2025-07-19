<?php

use App\Http\Controllers\Api\V1\Log\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'verified', 'set_locale'])->group(function () {
    Route::prefix('activityLogs')->name('activityLogs.')->group(function () {
        Route::controller(ActivityLogController::class)->group(function () {

            // CRUD Routes
            Route::get('/', 'index')->name('index')->middleware(['permission:activityLogs.list']);
            Route::get('/{gender}',  'show')->name('show')->middleware(['permission:activityLogs.show']);
        });
    });
});
