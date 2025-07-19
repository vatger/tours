<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\DateService;
use App\Traits\CascadeSoftDeletes;
use App\Traits\HasDateScopes;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\CausesActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use SoftDeletes;
    use CascadeSoftDeletes;
    use HasRoles;
    use HasDateScopes;
    use LogsActivity;
    use CausesActivity;
    // ACTYVITY LOGS
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->dontSubmitEmptyLogs()->logExcept(['password', 'remember_token'])->logOnlyDirty();
    }

    const AVATAR_STORAGE = 'image/user_avatar';
    const APPROVED_STATUS_ANALISYS = '1';
    const APPROVED_STATUS_APPROVED = '2';
    const APPROVED_STATUS_UNAPPROVED = '3';
    const APPROVED_STATUS_BLOCKED = '4';
    const APPROVED_STATUS_CANCELED = '5';
    const APPROVED_STATUS_TEXT = ['1' => 'ANALISYS', '2' => 'APPROVED', '3' => 'UNAPPROVED', '4' => 'BLOCKED', '5' => 'CANCELED'];
    const IS_ACTIVE_TEXT = ['1' => 'ACTIVE', '0' => 'INACTIVE'];
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'pin_code',
        'locale',
        'nickname',
        'is_active',
        'approved_status',
        'registered_by',
        'avatar',
        'original_file_name',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'pin_code' => 'hashed',
            'is_active' => 'boolean',
            'approved_status' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'registered_by' => 'integer',
            'avatar' => 'string',
            'original_file_name' => 'string',
            'locale' => 'string',
            'nickname' => 'string',
        ];
    }

    protected $dateFields = ['created_at', 'updated_at'];


    // APPENDS
    protected $appends = [
        'avatar_url',
        'approved_status_text',
        'is_active_text'
    ];

    public function getIsActiveTextAttribute()
    {
        if ($this->is_active) {
            return  __(self::IS_ACTIVE_TEXT[1]);
        }
        return  __(self::IS_ACTIVE_TEXT[0]);
    }

    public function getAvatarUrlAttribute()
    {
        $storage = self::AVATAR_STORAGE . '/';

        if (!$this->avatar) {
            return null; //asset('noimage.png');
        }
        return   Storage::url($storage)  . $this->avatar;
    }

    public function getApprovedStatusTextAttribute()
    {
        return  __(self::APPROVED_STATUS_TEXT[$this->approved_status]);
    }

    // RELATIONSHIPS
    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
    public function activeMotives()
    {
        return $this->morphMany(ActiveHistoric::class, 'subject');
    }

    public function approvedMotives()
    {
        return $this->morphMany(ApproveHistoric::class, 'subject');
    }


    // SCOPES

    public function scopeSearch(Builder $builder, string $search)
    {
        Log::debug("Search term", ['search' => $search]);

        $parsedDate = DateService::parseDate($search);

        if ($parsedDate) {
            Log::debug("Valid date detected", ['parsedDate' => $parsedDate->format('Y-m-d')]);
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('email_verified_at', $parsedDate)
                    ->orWhereDate('created_at', $parsedDate)
                    ->orWhereDate('updated_at', $parsedDate);
            });
        } else {
            $builder->where(function ($query) use ($search) {
                // Busca parcial de datas (dia/mês)
                if (preg_match('/^\d{1,2}\/\d{1,2}$/', $search)) {
                    list($day, $month) = explode('/', $search);
                    $query->where(function ($q) use ($day, $month) {
                        $q->whereMonth('email_verified_at', $month)
                            ->whereDay('email_verified_at', $day)
                            ->orWhereMonth('created_at', $month)
                            ->whereDay('created_at', $day)
                            ->orWhereMonth('updated_at', $month)
                            ->whereDay('updated_at', $day);
                    });
                }
                // Somente dia
                elseif (preg_match('/^\d{1,2}\/$/', $search)) {
                    $day = str_replace('/', '', $search);
                    $query->where(function ($q) use ($day) {
                        $q->whereDay('email_verified_at', $day)
                            ->orWhereDay('created_at', $day)
                            ->orWhereDay('updated_at', $day);
                    });
                }
                // Somente mês
                elseif (preg_match('/^\/\d{1,2}$/', $search)) {
                    $month = str_replace('/', '', $search);
                    $query->where(function ($q) use ($month) {
                        $q->whereMonth('email_verified_at', $month)
                            ->orWhereMonth('created_at', $month)
                            ->orWhereMonth('updated_at', $month);
                    });
                }
                // Busca textual
                else {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('username', 'like', "%$search%")
                        ->orWhere('nickname', 'like', "%$search%");
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

    public function scopeCanceled(Builder $query): void
    {
        $query->where('approved_status', $this::APPROVED_STATUS_CANCELED);
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
     * id,name,email,username,nickname,IsActive,IsNotActive,Analisys,Approved,Unapproved,Blocked,Canceled,CreatedAt,CreatedAtBefore,CreatedAtAfter,CreatedAtBetween,CreatedAtCurrentDay,CreatedAtCurrentWeek,CreatedAtCurrentMonth,CreatedAtLastDays,CreatedAtNextDays,UpdatedAt,UpdatedAtBefore,UpdatedAtAfter,UpdatedAtBetween,UpdatedAtCurrentDay,UpdatedAtCurrentWeek,UpdatedAtCurrentMonth,UpdatedAtLastDays,UpdatedAtNextDays,WithTrashed,OnlyTrashed,Search
     */
    public static function ALLOWEDFILTERS()
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('email'),
            AllowedFilter::partial('username'),
            AllowedFilter::partial('nickname'),
            AllowedFilter::scope('IsActive'),
            AllowedFilter::scope('IsNotActive'),
            AllowedFilter::scope('Analisys'),
            AllowedFilter::scope('Approved'),
            AllowedFilter::scope('Unapproved'),
            AllowedFilter::scope('Blocked'),
            AllowedFilter::scope('Canceled'),
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
            AllowedFilter::scope('WithTrashed'),
            AllowedFilter::scope('OnlyTrashed'),

            AllowedFilter::scope('Search'),

        ];
    }

    /**
     * The allowed includes relationships.
     * registeredBy,roles,actions,activities
     */
    public static function ALLOWEDINCLUDES()
    {
        return ['registeredBy', 'roles', 'actions', 'activities', 'approvedMotives', 'activeMotives'];
    }

    /**
     * The allowed sorts attributes.
     * id,name,email,created_at,updated_at
     */
    public static function ALLOWEDSORTS()
    {
        return ['id', 'name', 'email', 'nickname', 'username', 'created_at', 'updated_at', 'deleted_at', 'is_active', 'approved_status', 'registered_by'];
    }

    /**
     * The Default sorts attributes.
     * -created_at,name,id
     */
    public static function DEFAULTSORT()
    {
        return ['-created_at', 'name', 'id'];
    }

    /**
     * The default includes relationships.
     * registeredBy,roles,actions,activities
     */
    public static function DEFAULTINCLUDES()
    {
        return ['registeredBy', 'roles', 'actions', 'activities', 'approvedMotives', 'activeMotives'];
    }
}
