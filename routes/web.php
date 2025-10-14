<?php

use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::prefix('/teams')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [TeamController::class, 'show'])->name('teams.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
