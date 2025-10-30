<?php

use App\Jobs\CheckTour;

Schedule::job(new CheckTour)->everyFifteenMinutes();
