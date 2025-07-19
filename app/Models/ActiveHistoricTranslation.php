<?php

declare(strict_types=1);

namespace App\Models;

use LaravelLang\Models\Casts\TrimCast;
use LaravelLang\Models\Eloquent\Translation;

class ActiveHistoricTranslation extends Translation
{
    protected $fillable = [
        'locale',
        'motive',
    ];

    protected $casts = [
        'motive' => TrimCast::class,
    ];
}
