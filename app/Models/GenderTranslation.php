<?php

namespace App\Models;

use LaravelLang\Models\Casts\TrimCast;
use LaravelLang\Models\Eloquent\Translation;

/**
 * @version V1
 */
class GenderTranslation extends Translation
{
    
    protected $fillable = [
        'locale',
        'name_translated',    
    ];

    protected $casts = [
        'name_translated' => TrimCast::class,
    ];
}
