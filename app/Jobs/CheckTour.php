<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckTour implements ShouldQueue
{
    use Queueable;

    public int $tour_id;

    public function __construct(int $tour_id)
    {
        $this->tour_id = $tour_id;
    }

    public function handle(): void
    {
        //
    }
}
