<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class TourLeg extends Model
{
    public function status(): HasOne
    {
        $user_id = Auth::user()->id;

        return $this->hasOne(TourLegUser::class, 'tour_leg_id', 'id')
            ->where('user_id', $user_id);
    }
}
