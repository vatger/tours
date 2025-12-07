<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourLegUser extends Model
{
    protected $table = 'tour_leg_users';
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

    public function leg(): BelongsTo
    {
        return $this->belongsTo(TourLeg::class, 'tour_leg_id');
    }
}
