<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;

class Tour extends Model
{
    protected function casts(): array
    {
        return [
            'begins_at' => 'datetime',
            'ends_at' => 'datetime',
            'require_order' => 'boolean',
        ];
    }

    public function legs(): HasMany
    {
        return $this->hasMany(TourLeg::class, 'tour_id');
    }

    public function status(?int $user_id = null): ?HasOne
    {
        if (! $user_id) {
            $user_id = Auth::check() ? Auth::user()->id : null;
        }

        return $this->hasOne(TourUser::class, 'tour_id', 'id')
            ->where('user_id', $user_id);
    }
}
