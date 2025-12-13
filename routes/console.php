<?php

use App\Jobs\AwardBadges;
use App\Jobs\CheckTour;

Schedule::job(new CheckTour)->everyFifteenMinutes();
Schedule::job(new AwardBadges())->hourly();
