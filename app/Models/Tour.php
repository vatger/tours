<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    protected function casts(): array
    {
        return [
            'begins_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function legs(): HasMany
    {
        return $this->hasMany(TourLeg::class, 'tour_id');
    }
}
