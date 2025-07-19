<?php

namespace App\Models\AbstractContracts;

use App\Traits\ClearsResponseCache;

use Illuminate\Database\Eloquent\Model;

use LaravelLang\Models\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

abstract class ApproveHistoric extends Model
{
    use HasTranslations;
    use HasFactory;
    use ClearsResponseCache;

    // use SoftDeletes;
    const APPROVED_STATUS_ANALISYS = '1';
    const APPROVED_STATUS_APPROVED = '2';
    const APPROVED_STATUS_UNAPPROVED = '3';
    const APPROVED_STATUS_BLOCKED = '4';
    const APPROVED_STATUS_CANCELED = '5';
    const APPROVED_STATUS_TEXT = ['1' => 'ANALISYS', '2' => 'APPROVED', '3' => 'UNAPPROVED', '4' => 'BLOCKED', '5' => 'CANCELED'];
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

    //     'approved_status',
    //     'motive',
    // ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        //
    ];


    //APPENDS
    protected $appends = [
        'approved_status_text',
    ];

    public function getApprovedStatusTextAttribute()
    {
        return  __(self::APPROVED_STATUS_TEXT[$this->approved_status]);
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

    public function scopeAnalisys(Builder $query): void
    {
        $query->where('approved_status', $this::APPROVED_STATUS_ANALISYS);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('approved_status', $this::APPROVED_STATUS_APPROVED);
    }

    public function scopeUnapproved(Builder $query): void
    {
        $query->where('approved_status', $this::APPROVED_STATUS_UNAPPROVED);
    }

    public function scopeBlocked(Builder $query): void
    {
        $query->where('approved_status', $this::APPROVED_STATUS_BLOCKED);
    }


    //RELATIONS
    /**
     * Get all of the models that own comments.
     */
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
