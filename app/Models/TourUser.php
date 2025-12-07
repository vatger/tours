<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourUser extends Model
{
    protected $table = 'tour_users';
    protected $fillable = [
        'tour_id',
        'user_id',
    ];

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'badge_given' => 'boolean',
        ];
    }
}
