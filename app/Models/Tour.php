<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tour extends Model
{
    public function legs(): HasMany
    {
        return $this->hasMany(TourLeg::class, 'tour_id');
    }
}
