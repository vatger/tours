<?php

use App\Jobs\AwardBadges;
use App\Jobs\CheckTour;

Schedule::call(fn () => CheckTour::dispatch())
    ->name('check-tours')
    ->withoutOverlapping()
    ->everyFifteenMinutes();

Schedule::call(fn () => AwardBadges::dispatch())
    ->name('award-tour-badges')
    ->withoutOverlapping()
    ->everyFifteenMinutes();
