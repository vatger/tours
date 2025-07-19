<?php

namespace App\Models;


use Spatie\Activitylog\LogOptions;
use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class BaseModel extends Model
{
    use LogsActivity;
    use ClearsResponseCache;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->dontSubmitEmptyLogs();
    }

    /**
     * The number of models to return for pagination.
     *
     * @var int
     */
    protected $perPage = 500;
}
