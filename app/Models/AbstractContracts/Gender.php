<?php

namespace App\Models\AbstractContracts;

use App\Services\DateService;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasDateScopes;
use LaravelLang\Models\HasTranslations;
use App\Models\ActiveHistoric;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\ClearsResponseCache;
use Illuminate\Support\Facades\Storage;

/**
 * @version V1
 */
abstract class Gender extends Model
{
    use HasFactory;
    use HasTranslations;
    use SoftDeletes;
    use CascadeSoftDeletes;
    use LogsActivity;
    use ClearsResponseCache;
    const ICON_STORAGE = 'image/gender_icon';
    const IS_ACTIVE_TRUE = '1';
    const IS_ACTIVE_FALSE = '0';
    const IS_ACTIVE_TEXT = ['0' => 'INACTIVE', '1' => 'ACTIVE'];



    use HasDateScopes;
    protected $dateFields = ['created_at', 'updated_at', 'deleted_at'];
    protected $cascadeDeletes = [];


    protected $fillable = [
        'name',
        'icon',
        'icon_file_name',
        'icon_file_size',
        'icon_file_extension',
        'original_locale',
        'is_active',
        'deleted_by_parent',
    ];

    protected $casts = [
        'name' => 'string',
        'icon' => 'string',
        'original_locale' => 'string',
        'is_active' => 'boolean',
        'deleted_by_parent' => 'boolean',
    ];

    // APPENDS
    protected $appends = [
        'icon_url',
        'is_active_text'
    ];

    public function getIsActiveTextAttribute()
    {
        return  __(self::IS_ACTIVE_TEXT[$this->is_active]);
    }

    public function getIconUrlAttribute()
    {
        $storage = self::ICON_STORAGE . '/';
        if (!$this->icon) {
            return null; // asset('noimage.png');
        }
        return Storage::url($storage . $this->icon);
    }


    // INCLUDES

    public function activeMotives()
    {
        return $this->morphMany(ActiveHistoric::class, 'subject');
    }



    // SCOPES

    public function scopeSearch(Builder $builder, string $search)
    {
        $parsedDate = DateService::parseDate($search);

        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('created_at', $parsedDate->format('Y-m-d'));
                $query->whereDate('updated_at', $parsedDate->format('Y-m-d'));
                $query->whereDate('deleted_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $builder->where(function ($query) use ($search) {
                // Busca parcial de datas (exemplo: dia/mês ou apenas mês)
                if (preg_match('/^\d{1,2}\/\d{1,2}$/', $search)) {
                    // Exemplo: 29/03 (dia/mês)
                    list($day, $month) = explode('/', $search);
                    $query->whereMonth('created_at', $month)
                        ->whereDay('created_at', $day);
                    $query->whereMonth('updated_at', $month)
                        ->whereDay('updated_at', $day);
                    $query->whereMonth('deleted_at', $month)
                        ->whereDay('deleted_at', $day);
                } elseif (preg_match('/^\d{1,2}\/\$/', $search)) {
                    // Exemplo: 29/ (somente dia)
                    $day = str_replace('/', '', $search);
                    $query->whereDay('created_at', $day);
                    $query->whereDay('updated_at', $day);
                    $query->whereDay('deleted_at', $day);
                } elseif (preg_match('/^\/\d{1,2}$/', $search)) {
                    // Exemplo: /05 (somente mês)
                    $month = str_replace('/', '', $search);
                    $query->whereMonth('created_at', $month);
                    $query->whereMonth('updated_at', $month);
                    $query->whereMonth('deleted_at', $month);
                } else {
                    $query->orWhere('name', 'LIKE', "%$search%");
                    $query->orWhereHas('translations', fn($q) => $q->where('name_translated', 'LIKE', "%$search%"));
                }
            });
        }
    }


    public function scopeIsActive(Builder $query): void
    {
        $query->where('is_active', 1);
    }


    public function scopeIsNotActive(Builder $query): void
    {
        $query->where('is_active', 0);
    }


    public function scopeDeletedAt(Builder $builder, $date)
    {
        return $this->applyDateScope($builder, 'deleted_at', 'At', $date);
    }

    public function scopeDeletedAtBefore(Builder $builder, $date)
    {
        return $this->applyDateScope($builder, 'deleted_at', 'AtBefore', $date);
    }

    public function scopeDeletedAtAfter(Builder $builder, $date)
    {
        return $this->applyDateScope($builder, 'deleted_at', 'AtAfter', $date);
    }

    public function scopeDeletedAtBetween(Builder $builder, $startDate, $endDate)
    {
        return $this->applyDateScope($builder, 'deleted_at', 'AtBetween', $startDate, $endDate);
    }

    public function scopeDeletedAtCurrentDay(Builder $builder)
    {
        return $this->applyDateScope($builder, 'deleted_at', 'AtCurrentDay');
    }

    public function scopeDeletedAtCurrentWeek(Builder $builder)
    {
        return $this->applyDateScope($builder, 'deleted_at', 'AtCurrentWeek');
    }

    public function scopeDeletedAtCurrentMonth(Builder $builder)
    {
        return $this->applyDateScope($builder, 'deleted_at', 'AtCurrentMonth');
    }

    public function scopeDeletedAtLastDays(Builder $builder, int $days)
    {
        return $this->applyDateScope($builder, 'deleted_at', 'AtLastDays', $days);
    }

    public function scopeDeletedAtNextDays(Builder $builder, int $days)
    {
        return $this->applyDateScope($builder, 'deleted_at', 'AtNextDays', $days);
    }

    public function scopeCreatedAt(Builder $builder, $date)
    {
        return $this->applyDateScope($builder, 'created_at', 'At', $date);
    }

    public function scopeCreatedAtBefore(Builder $builder, $date)
    {
        return $this->applyDateScope($builder, 'created_at', 'AtBefore', $date);
    }

    public function scopeCreatedAtAfter(Builder $builder, $date)
    {
        return $this->applyDateScope($builder, 'created_at', 'AtAfter', $date);
    }

    public function scopeCreatedAtBetween(Builder $builder, $startDate, $endDate)
    {
        return $this->applyDateScope($builder, 'created_at', 'AtBetween', $startDate, $endDate);
    }

    public function scopeCreatedAtCurrentDay(Builder $builder)
    {
        return $this->applyDateScope($builder, 'created_at', 'AtCurrentDay');
    }

    public function scopeCreatedAtCurrentWeek(Builder $builder)
    {
        return $this->applyDateScope($builder, 'created_at', 'AtCurrentWeek');
    }

    public function scopeCreatedAtCurrentMonth(Builder $builder)
    {
        return $this->applyDateScope($builder, 'created_at', 'AtCurrentMonth');
    }

    public function scopeCreatedAtLastDays(Builder $builder, int $days)
    {
        return $this->applyDateScope($builder, 'created_at', 'AtLastDays', $days);
    }

    public function scopeCreatedAtNextDays(Builder $builder, int $days)
    {
        return $this->applyDateScope($builder, 'created_at', 'AtNextDays', $days);
    }

    public function scopeUpdatedAt(Builder $builder, $date)
    {
        return $this->applyDateScope($builder, 'updated_at', 'At', $date);
    }

    public function scopeUpdatedAtBefore(Builder $builder, $date)
    {
        return $this->applyDateScope($builder, 'updated_at', 'AtBefore', $date);
    }

    public function scopeUpdatedAtAfter(Builder $builder, $date)
    {
        return $this->applyDateScope($builder, 'updated_at', 'AtAfter', $date);
    }

    public function scopeUpdatedAtBetween(Builder $builder, $startDate, $endDate)
    {
        return $this->applyDateScope($builder, 'updated_at', 'AtBetween', $startDate, $endDate);
    }

    public function scopeUpdatedAtCurrentDay(Builder $builder)
    {
        return $this->applyDateScope($builder, 'updated_at', 'AtCurrentDay');
    }

    public function scopeUpdatedAtCurrentWeek(Builder $builder)
    {
        return $this->applyDateScope($builder, 'updated_at', 'AtCurrentWeek');
    }

    public function scopeUpdatedAtCurrentMonth(Builder $builder)
    {
        return $this->applyDateScope($builder, 'updated_at', 'AtCurrentMonth');
    }

    public function scopeUpdatedAtLastDays(Builder $builder, int $days)
    {
        return $this->applyDateScope($builder, 'updated_at', 'AtLastDays', $days);
    }

    public function scopeUpdatedAtNextDays(Builder $builder, int $days)
    {
        return $this->applyDateScope($builder, 'updated_at', 'AtNextDays', $days);
    }


    public function scopeWithTrashed($query)
    {
        return $query->withTrashed();
    }

    public function scopeOnlyTrashed($query)
    {
        return $query->onlyTrashed();
    }

    // QUERY BUILDER

    /**
     * The allowed filters attributes.
     * id,name,IsActive,IsNotActive,CreatedAt,CreatedAtBefore,CreatedAtAfter,CreatedAtBetween,CreatedAtCurrentDay,CreatedAtCurrentWeek,CreatedAtCurrentMonth,CreatedAtLastDays,CreatedAtNextDays,UpdatedAt,UpdatedAtBefore,UpdatedAtAfter,UpdatedAtBetween,UpdatedAtCurrentDay,UpdatedAtCurrentWeek,UpdatedAtCurrentMonth,UpdatedAtLastDays,UpdatedAtNextDays,DeletedAt,DeletedAtBefore,DeletedAtAfter,DeletedAtBetween,DeletedAtCurrentDay,DeletedAtCurrentWeek,DeletedAtCurrentMonth,DeletedAtLastDays,DeletedAtNextDays,WithTrashed,OnlyTrashed,Search
     */
    public static function ALLOWEDFILTERS()
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::partial('name'),
            AllowedFilter::scope('IsActive'),
            AllowedFilter::scope('IsNotActive'),
            AllowedFilter::scope('CreatedAt'),
            AllowedFilter::scope('CreatedAtBefore'),
            AllowedFilter::scope('CreatedAtAfter'),
            AllowedFilter::scope('CreatedAtBetween'),
            AllowedFilter::scope('CreatedAtCurrentDay'),
            AllowedFilter::scope('CreatedAtCurrentWeek'),
            AllowedFilter::scope('CreatedAtCurrentMonth'),
            AllowedFilter::scope('CreatedAtLastDays'),
            AllowedFilter::scope('CreatedAtNextDays'),
            AllowedFilter::scope('UpdatedAt'),
            AllowedFilter::scope('UpdatedAtBefore'),
            AllowedFilter::scope('UpdatedAtAfter'),
            AllowedFilter::scope('UpdatedAtBetween'),
            AllowedFilter::scope('UpdatedAtCurrentDay'),
            AllowedFilter::scope('UpdatedAtCurrentWeek'),
            AllowedFilter::scope('UpdatedAtCurrentMonth'),
            AllowedFilter::scope('UpdatedAtLastDays'),
            AllowedFilter::scope('UpdatedAtNextDays'),
            AllowedFilter::scope('DeletedAt'),
            AllowedFilter::scope('DeletedAtBefore'),
            AllowedFilter::scope('DeletedAtAfter'),
            AllowedFilter::scope('DeletedAtBetween'),
            AllowedFilter::scope('DeletedAtCurrentDay'),
            AllowedFilter::scope('DeletedAtCurrentWeek'),
            AllowedFilter::scope('DeletedAtCurrentMonth'),
            AllowedFilter::scope('DeletedAtLastDays'),
            AllowedFilter::scope('DeletedAtNextDays'),
            AllowedFilter::scope('WithTrashed'),
            AllowedFilter::scope('OnlyTrashed'),

            AllowedFilter::scope('Search'),

        ];
    }

    /**
     * The allowed includes relationships.
     * users,activeMotives,activities
     */
    public static function ALLOWEDINCLUDES()
    {
        return ['activeMotives', 'activities'];
    }

    /**
     * The allowed sorts attributes.
     * id,name,is_active,created_at,updated_at,deleted_at
     */
    public static function ALLOWEDSORTS()
    {
        return ['id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at'];
    }

    /**
     * The Default sorts attributes.
     * name
     */
    public static function DEFAULTSORT()
    {
        return ['name'];
    }

    /**
     * The default includes relationships.
     * users
     */
    public static function DEFAULTINCLUDES()
    {
        return ['activeMotives', 'activities'];
    }


    // ACTYVITY LOGS
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->dontSubmitEmptyLogs()->logOnlyDirty();
    }
}
