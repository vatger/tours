<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class TourLeg extends Model
{
    public function status(?int $user_id = null): ?HasOne
    {
        if (! $user_id) {
            $user_id = Auth::check() ? Auth::user()->id : null;
        }

        return $this->hasOne(TourLegUser::class, 'tour_leg_id', 'id')
            ->where('user_id', $user_id);
    }

    public function statuses(): HasMany
    {
        return $this->hasMany(TourLegUser::class, 'tour_leg_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tour_leg_users', 'tour_leg_id', 'user_id');
    }
}
