<?php

namespace App\Models\AbstractContracts;

use App\Services\DateService;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\ActiveHistoric;

use App\Models\ApproveHistoric;
use App\Models\ProfileType;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use App\Traits\ClearsResponseCache;

/**
 * @version V1
 */
abstract class User extends Model
{
    use HasFactory;

    use SoftDeletes;
    use CascadeSoftDeletes;
    use LogsActivity;
    use ClearsResponseCache;
    const IS_ACTIVE_TRUE = '1';
    const IS_ACTIVE_FALSE = '0';
    const IS_ACTIVE_TEXT = ['0' => 'INACTIVE', '1' => 'ACTIVE'];
    const APPROVED_STATUS_ANALISYS = '1';
    const APPROVED_STATUS_APPROVED = '2';
    const APPROVED_STATUS_UNAPPROVED = '3';
    const APPROVED_STATUS_BLOCKED = '4';
    const APPROVED_STATUS_CANCELED = '5';
    const APPROVED_STATUS_TEXT = ['1' => 'ANALISYS', '2' => 'APPROVED', '3' => 'UNAPPROVED', '4' => 'BLOCKED', '5' => 'CANCELED'];



    // CASCADE DELETES
    protected $cascadeDeletes = [];


    protected $fillable = [
        'name',
        'username',
        'nickname',
        'email',
        'email_verified_at',
        'password',
        'pin_code',
        'remember_token',
        'profile_type_id',
        'send_notifications',
        'use_term_accepted',
        'is_active',
        'approved_status',
        'deleted_by_parent',
    ];

    protected $casts = [
        'name' => 'string',
        'username' => 'string',
        'nickname' => 'string',
        'email' => 'string',
        'email_verified_at' => 'datetime',
        'password' => 'string',
        'pin_code' => 'string',
        'remember_token' => 'string',
        'profile_type_id' => 'integer',
        'send_notifications' => 'boolean',
        'use_term_accepted' => 'boolean',
        'is_active' => 'boolean',
        'approved_status' => 'string',
        'deleted_by_parent' => 'boolean',
    ];

    // APPENDS
    protected $appends = [
        'is_active_text',
        'approved_status_text'
    ];

    public function getIsActiveTextAttribute()
    {
        return  __(self::IS_ACTIVE_TEXT[$this->is_active]);
    }

    public function getApprovedStatusTextAttribute()
    {
        return  __(self::APPROVED_STATUS_TEXT[$this->approved_status]);
    }


    // INCLUDES
    public function profile_type()
    {
        return $this->belongsTo(ProfileType::class);
    }

    public function activeMotives()
    {
        return $this->morphMany(ActiveHistoric::class, 'subject');
    }


    public function approvedMotives()
    {
        return $this->morphMany(ApproveHistoric::class, 'subject');
    }

    /**
     * Get the user logins for the user.
     */
    public function userLogins()
    {
        return $this->hasMany(UserLogin::class);
    }

    /**
     * Get the login logs for the user.
     */
    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class, 'identifier', 'email')
            ->orWhere('identifier', $this->nickname);
    }

    /**
     * Get the user's most recent login.
     */
    public function latestLogin()
    {
        return $this->hasOne(UserLogin::class)->latestOfMany();
    }

    // SCOPES

    public function scopeSearch(Builder $builder, string $search)
    {
        $parsedDate = DateService::parseDate($search);

        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('email_verified_at', $parsedDate->format('Y-m-d'));
                $query->whereDate('created_at', $parsedDate->format('Y-m-d'));
                $query->whereDate('updated_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $builder->where(function ($query) use ($search) {
                // Busca parcial de datas (exemplo: dia/mês ou apenas mês)
                if (preg_match('/^\d{1,2}\/\d{1,2}$/', $search)) {
                    // Exemplo: 29/03 (dia/mês)
                    list($day, $month) = explode('/', $search);
                    $query->whereMonth('email_verified_at', $month)
                        ->whereDay('email_verified_at', $day);
                    $query->whereMonth('created_at', $month)
                        ->whereDay('created_at', $day);
                    $query->whereMonth('updated_at', $month)
                        ->whereDay('updated_at', $day);
                } elseif (preg_match('/^\d{1,2}\/\$/', $search)) {
                    // Exemplo: 29/ (somente dia)
                    $day = str_replace('/', '', $search);
                    $query->whereDay('email_verified_at', $day);
                    $query->whereDay('created_at', $day);
                    $query->whereDay('updated_at', $day);
                } elseif (preg_match('/^\/\d{1,2}$/', $search)) {
                    // Exemplo: /05 (somente mês)
                    $month = str_replace('/', '', $search);
                    $query->whereMonth('email_verified_at', $month);
                    $query->whereMonth('created_at', $month);
                    $query->whereMonth('updated_at', $month);
                } else {
                    $query->orWhere('name', 'LIKE', "%$search%");
                    $query->orWhere('username', 'LIKE', "%$search%");
                    $query->orWhere('nickname', 'LIKE', "%$search%");
                    $query->orWhere('email', 'LIKE', "%$search%");
                }
            });
        }
    }

    public function scopeSendNotifications(Builder $query): void
    {
        $query->where('send_notifications', 1);
    }


    public function scopeNotSendNotifications(Builder $query): void
    {
        $query->where('send_notifications', 0);
    }

    public function scopeUseTermAccepted(Builder $query): void
    {
        $query->where('use_term_accepted', 1);
    }


    public function scopeNotUseTermAccepted(Builder $query): void
    {
        $query->where('use_term_accepted', 0);
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
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('created_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('created_at', $parsedDate);
            });
        }
    }

    // Scope for dates before a given date
    public function scopeCreatedAtBefore(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('created_at', '<=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('created_at', $parsedDate);
            });
        }
    }

    // Scope for dates after a given date
    public function scopeCreatedAtAfter(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('created_at', '>=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('created_at', $parsedDate);
            });
        }
    }

    // Scope for dates between a start and end date
    public function scopeCreatedAtBetween(Builder $builder, $startDate, $endDate)
    {
        $parsedStartDate = DateService::parseDate($startDate);
        $parsedEndDate = DateService::parseDate($endDate);

        if ($parsedStartDate && $parsedEndDate) {
            $builder->where(function ($query) use ($parsedStartDate, $parsedEndDate) {
                $query->whereBetween('created_at', [
                    $parsedStartDate->startOfDay(),
                    $parsedEndDate->endOfDay()
                ]);
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('created_at', $parsedDate);
            });
        }
    }

    // Scope para filtrar por dia atual
    public function scopeCreatedAtCurrentDay(Builder $builder)
    {
        return $builder->whereDate('created_at', DateService::today()->format('Y-m-d'));
    }

    // Scope para filtrar por semana atual
    public function scopeCreatedAtCurrentWeek(Builder $builder)
    {
        return $builder->whereBetween('created_at', [DateService::currentStartOfWeek()->format('Y-m-d 00:00:00'), DateService::currentEndOfWeek()->format('Y-m-d 23:59:59')]);
    }

    // Scope para filtrar por mês atual
    public function scopeCreatedAtCurrentMonth(Builder $builder)
    {
        return $builder->whereMonth('created_at', DateService::currentMonth())
            ->whereYear('created_at', DateService::currentYear());
    }

    // Scope para filtrar por anteriores X dias
    public function scopeCreatedAtLastDays(Builder $builder, int $days)
    {
        return $builder->whereBetween('created_at', [DateService::currentLastDays($days)->format('Y-m-d 00:00:00'), DateService::today()->format('Y-m-d 23:59:59')]);
    }
    // Scope para filtrar por próximos X dias
    public function scopeCreatedAtNextDays(Builder $builder, int $days)
    {
        return $builder->whereBetween('created_at', [DateService::today()->format('Y-m-d 00:00:00'), DateService::currentNextDays($days)->format('Y-m-d 23:59:59')]);
    }

    public function scopeUpdatedAt(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('updated_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('updated_at', $parsedDate);
            });
        }
    }

    // Scope for dates before a given date
    public function scopeUpdatedAtBefore(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('updated_at', '<=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('updated_at', $parsedDate);
            });
        }
    }

    // Scope for dates after a given date
    public function scopeUpdatedAtAfter(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('updated_at', '>=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('updated_at', $parsedDate);
            });
        }
    }

    // Scope for dates between a start and end date
    public function scopeUpdatedAtBetween(Builder $builder, $startDate, $endDate)
    {
        $parsedStartDate = DateService::parseDate($startDate);
        $parsedEndDate = DateService::parseDate($endDate);

        if ($parsedStartDate && $parsedEndDate) {
            $builder->where(function ($query) use ($parsedStartDate, $parsedEndDate) {
                $query->whereBetween('updated_at', [
                    $parsedStartDate->startOfDay(),
                    $parsedEndDate->endOfDay()
                ]);
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('updated_at', $parsedDate);
            });
        }
    }

    // Scope para filtrar por dia atual
    public function scopeUpdatedAtCurrentDay(Builder $builder)
    {
        return $builder->whereDate('updated_at', DateService::today()->format('Y-m-d'));
    }

    // Scope para filtrar por semana atual
    public function scopeUpdatedAtCurrentWeek(Builder $builder)
    {
        return $builder->whereBetween('updated_at', [DateService::currentStartOfWeek()->format('Y-m-d 00:00:00'), DateService::currentEndOfWeek()->format('Y-m-d 23:59:59')]);
    }

    // Scope para filtrar por mês atual
    public function scopeUpdatedAtCurrentMonth(Builder $builder)
    {
        return $builder->whereMonth('updated_at', DateService::currentMonth())
            ->whereYear('updated_at', DateService::currentYear());
    }

    // Scope para filtrar por anteriores X dias
    public function scopeUpdatedAtLastDays(Builder $builder, int $days)
    {
        return $builder->whereBetween('updated_at', [DateService::currentLastDays($days)->format('Y-m-d 00:00:00'), DateService::today()->format('Y-m-d 23:59:59')]);
    }
    // Scope para filtrar por próximos X dias
    public function scopeUpdatedAtNextDays(Builder $builder, int $days)
    {
        return $builder->whereBetween('updated_at', [DateService::today()->format('Y-m-d 00:00:00'), DateService::currentNextDays($days)->format('Y-m-d 23:59:59')]);
    }

    public function scopeEmailVerifiedAt(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('email_verified_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('email_verified_at', $parsedDate);
            });
        }
    }

    // Scope for dates before a given date
    public function scopeEmailVerifiedAtBefore(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('email_verified_at', '<=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('email_verified_at', $parsedDate);
            });
        }
    }

    // Scope for dates after a given date
    public function scopeEmailVerifiedAtAfter(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('email_verified_at', '>=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('email_verified_at', $parsedDate);
            });
        }
    }

    // Scope for dates between a start and end date
    public function scopeEmailVerifiedAtBetween(Builder $builder, $startDate, $endDate)
    {
        $parsedStartDate = DateService::parseDate($startDate);
        $parsedEndDate = DateService::parseDate($endDate);

        if ($parsedStartDate && $parsedEndDate) {
            $builder->where(function ($query) use ($parsedStartDate, $parsedEndDate) {
                $query->whereBetween('email_verified_at', [$parsedStartDate->format('Y-m-d'), $parsedEndDate->format('Y-m-d')]);
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('email_verified_at', $parsedDate);
            });
        }
    }

    // Scope para filtrar por dia atual
    public function scopeEmailVerifiedAtCurrentDay(Builder $builder)
    {
        return $builder->whereDate('email_verified_at', DateService::today()->format('Y-m-d'));
    }

    // Scope para filtrar por semana atual
    public function scopeEmailVerifiedAtCurrentWeek(Builder $builder)
    {
        return $builder->whereBetween('email_verified_at', [DateService::currentStartOfWeek()->format('Y-m-d 00:00:00'), DateService::currentEndOfWeek()->format('Y-m-d 23:59:59')]);
    }

    // Scope para filtrar por mês atual
    public function scopeEmailVerifiedAtCurrentMonth(Builder $builder)
    {
        return $builder->whereMonth('email_verified_at', DateService::currentMonth())
            ->whereYear('email_verified_at', DateService::currentYear());
    }

    // Scope para filtrar por anteriores X dias
    public function scopeEmailVerifiedAtLastDays(Builder $builder, int $days)
    {
        return $builder->whereBetween('email_verified_at', [DateService::currentLastDays($days)->format('Y-m-d 00:00:00'), DateService::today()->format('Y-m-d 23:59:59')]);
    }
    // Scope para filtrar por próximos X dias
    public function scopeEmailVerifiedAtNextDays(Builder $builder, int $days)
    {
        return $builder->whereBetween('email_verified_at', [DateService::today()->format('Y-m-d 00:00:00'), DateService::currentNextDays($days)->format('Y-m-d 23:59:59')]);
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
     * id,name,username,nickname,email,EmailVerifiedAt,EmailVerifiedAtBefore,EmailVerifiedAtAfter,EmailVerifiedAtBetween,EmailVerifiedAtCurrentDay,EmailVerifiedAtCurrentWeek,EmailVerifiedAtCurrentMonth,EmailVerifiedAtLastDays,EmailVerifiedAtNextDays,profile_type_id,IsActive,IsNotActive,Analisys,Approved,Unapproved,Blocked,Canceled,CreatedAt,CreatedAtBefore,CreatedAtAfter,CreatedAtBetween,CreatedAtCurrentDay,CreatedAtCurrentWeek,CreatedAtCurrentMonth,CreatedAtLastDays,CreatedAtNextDays,UpdatedAt,UpdatedAtBefore,UpdatedAtAfter,UpdatedAtBetween,UpdatedAtCurrentDay,UpdatedAtCurrentWeek,UpdatedAtCurrentMonth,UpdatedAtLastDays,UpdatedAtNextDays,WithTrashed,OnlyTrashed,Search
     */
    public static function ALLOWEDFILTERS()
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('username'),
            AllowedFilter::partial('nickname'),
            AllowedFilter::partial('email'),
            AllowedFilter::scope('EmailVerifiedAt'),
            AllowedFilter::scope('EmailVerifiedAtBefore'),
            AllowedFilter::scope('EmailVerifiedAtAfter'),
            AllowedFilter::scope('EmailVerifiedAtBetween'),
            AllowedFilter::scope('EmailVerifiedAtCurrentDay'),
            AllowedFilter::scope('EmailVerifiedAtCurrentWeek'),
            AllowedFilter::scope('EmailVerifiedAtCurrentMonth'),
            AllowedFilter::scope('EmailVerifiedAtLastDays'),
            AllowedFilter::scope('EmailVerifiedAtNextDays'),
            AllowedFilter::exact('profile_type_id'),
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
     * The allowed includes attributes.
     * profile_type,userLogins,loginLogs,latestLogin,activeMotives,approvedMotives,activities
     */
    public static function ALLOWEDINCLUDES()
    {
        return ['profile_type', 'userLogins', 'loginLogs', 'latestLogin', 'latestLogin', 'activeMotives', 'approvedMotives', 'activities'];
    }
    /**
     * The allowed includes attributes.
     * profile_type,latestLogin
     */
    public static function DEFAULTINCLUDES()
    {
        return ['profile_type', 'latestLogin'];
    }


    /**
     * The allowed sorts attributes.
     * id,name,username,nickname,email,email_verified_at,is_active,created_at,updated_at
     */
    public static function ALLOWEDSORTS()
    {
        return ['id', 'name', 'username', 'nickname', 'email', 'email_verified_at', 'is_active', 'created_at', 'updated_at'];
    }

    /**
     * The Default sorts attributes.
     * name,username,nickname,email,email_verified_at
     */
    public static function DEFAULTSORT()
    {
        return ['name', 'username', 'nickname', 'email', 'email_verified_at'];
    }


    // ACTYVITY LOGS
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->dontSubmitEmptyLogs();
    }
}
