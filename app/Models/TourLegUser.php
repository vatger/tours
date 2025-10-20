<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourLegUser extends Model
{
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }
}
