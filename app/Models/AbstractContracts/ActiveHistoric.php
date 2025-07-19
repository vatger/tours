<?php

namespace App\Models\AbstractContracts;

use App\Traits\ClearsResponseCache;
use Illuminate\Database\Eloquent\Model;
use LaravelLang\Models\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

abstract class ActiveHistoric extends Model
{
    use HasTranslations;
    use HasFactory;
    use ClearsResponseCache;

    const IS_ACTIVE_TEXT = ['1' => 'ACTIVE', '0' => 'INACTIVE'];

    public $guarded = [];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $fillable = [
    //     'causer_type',
    //     'causer_id',
    //     'is_active',
    //     'motive',
    // ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    //APPENDS
    protected $appends = [
        'is_active_text',
    ];

    public function getIsActiveTextAttribute()
    {
        if ($this->is_active) {
            return  __(self::IS_ACTIVE_TEXT[1]);
        }
        return  __(self::IS_ACTIVE_TEXT[0]);
    }

    //SCOPES
    public function scopeSubjectName(Builder $query, ?string $subjectName)
    {
        if ($subjectName) {
            return $query->whereHas('subject', function ($subQuery) use ($subjectName) {
                // Obter o que_subject_name a partir do LogSubjectType
                $subjectType = LogSubjectType::where('type', $this->subject_type)->first();
                $fieldName = $subjectType ? $subjectType->what_subject_name : 'name'; // Padrão para 'name'
                $subQuery->where($fieldName, 'like', "%{$subjectName}%");
            });
        }
        return $query;
    }

    // Escopo para filtrar por nome do causer (nome do responsável pela ação)
    public function scopeCauserName(Builder $query, $name)
    {
        return $query->whereHas('causer', function ($q) use ($name) {
            $q->where('name', 'like', "%{$name}%");
        });
    }

    //RELATIONS
    /**
     * Get all of the models that own comments.
     */
    // public function subject()
    // {
    //     return $this->morphTo();
    // }

    public function subject(): MorphTo
    {
        // if (config('activitylog.subject_returns_soft_deleted_models')) {
        //     return $this->morphTo()->withTrashed();
        // }

        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }
}
