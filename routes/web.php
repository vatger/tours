<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;



Route::patch('/locale/set', function () {
    return back();
})->name('locale.set')->middleware(['web', 'set_locale']);;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'set_locale'])->name('dashboard');


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/web/system/systemDevelopment.php';
require __DIR__ . '/web/manager/logs/activityLog.php';
require __DIR__ . '/web/manager/params/gender.php';
require __DIR__ . '/web/manager/users/user.php';
