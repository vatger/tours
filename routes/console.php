<?php

use App\Jobs\AwardBadges;
use App\Jobs\CheckTour;

Schedule::job(new CheckTour)->everyFifteenMinutes()->withoutOverlapping();
Schedule::job(new AwardBadges())->hourly();
