<?php

use App\Http\Controllers\AdminTourController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\ConnectController;
use App\Http\Controllers\LegController;
use App\Http\Controllers\ToursDashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('api/airports/coordinates', [AirportController::class, 'index'])
    ->middleware('throttle:60,1')
    ->name('airports.index');

Route::get('tours', [ToursDashboardController::class, 'dashboard'])->middleware('auth')->name('dashboard');
Route::get('tours/{id}', [ToursDashboardController::class, 'show'])->middleware('auth')->name('tours');

Route::middleware(['auth', 'admin.role'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('tours', [AdminTourController::class, 'index'])->name('tours.index');
    Route::get('tours/create', [AdminTourController::class, 'create'])->name('tours.create');
    Route::post('tours', [AdminTourController::class, 'store'])->name('tours.store');
    Route::get('tours/{tour}/edit', [AdminTourController::class, 'edit'])->name('tours.edit');
    Route::put('tours/{tour}', [AdminTourController::class, 'update'])->name('tours.update');
    Route::delete('tours/{tour}', [AdminTourController::class, 'destroy'])->name('tours.destroy');
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}/tours', [AdminUserController::class, 'tours'])->name('users.tours');
    Route::post('users/{user}/tours/{tour}/completion', [AdminUserController::class, 'setTourCompletion'])->name('users.tour-completion');
    Route::post('users/{user}/tours/{tour}/legs/{leg}/completion', [AdminUserController::class, 'setLegCompletion'])->name('users.leg-completion');
    Route::get('users/{user}/tours/{tour}/legs/{leg}/flight', [AdminUserController::class, 'flight'])->name('users.flight');
});
Route::get('tours/{id?}/signup', [ToursDashboardController::class, 'signup'])->middleware('auth')->name('tours.signup');
Route::get('tours/{id?}/cancel', [ToursDashboardController::class, 'cancel'])->middleware('auth')->name('tours.cancel');

Route::post('legs/check', [LegController::class, 'check'])->middleware('auth')->name('legs.check');

Route::get('login', [ConnectController::class, 'login'])->name('login');

Route::get('callback', [ConnectController::class, 'callback'])->name('callback');

Route::middleware('auth')->group(function () {
    Route::get('/logout', [ConnectController::class, 'logout'])->name('logout');
});

Route::get('api/delete/{user_id}', [UserController::class, 'delete']);
