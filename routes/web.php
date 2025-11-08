<?php

use App\Http\Controllers\ConnectController;
use App\Http\Controllers\ToursDashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('tours/{id?}', [ToursDashboardController::class, 'index'])->middleware('auth')->name('tours');
Route::get('tours/{id?}/signup', [ToursDashboardController::class, 'signup'])->middleware('auth')->name('tours.signup');
Route::get('tours/{id?}/cancel', [ToursDashboardController::class, 'cancel'])->middleware('auth')->name('tours.cancel');

Route::get('login', [ConnectController::class, 'login'])->name('login');

Route::get('callback', [ConnectController::class, 'callback'])->name('callback');

Route::middleware('auth')->group(function () {
    Route::get('/logout', [ConnectController::class, 'logout'])->name('logout');
});

Route::get('api/delete/{user_id}', [UserController::class, 'delete']);
