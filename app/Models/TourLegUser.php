<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourLegUser extends Model
{
    protected $fillable = [
        'tour_leg_id',
        'user_id',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }
}
