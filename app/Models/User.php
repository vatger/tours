<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    protected $fillable = [
        'id',
        'firstname',
        'lastname',
    ];

    public function tourUsers(): HasMany
    {
        return $this->hasMany(TourUser::class, 'user_id');
    }
}
