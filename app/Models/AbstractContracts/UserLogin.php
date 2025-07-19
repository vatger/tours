<?php

namespace App\Models\AbstractContracts;

use App\Services\DateService;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;



use App\Models\User;







/**
 * @version V1
 */
abstract class UserLogin extends Model
{
    use HasFactory;
    
    
    
    
    
    
    

    // CASCADE DELETES
    protected $cascadeDeletes = [];

    
    protected $fillable = [
        'token_id',
        'ip_address',
        'browser',
        'browser_version',
        'platform',
        'device_type',
        'is_mobile',
        'is_tablet',
        'is_desktop',
        'logout_at',
        'session_duration',
        'user_id',
        'deleted_by_parent',    
    ];

    protected $casts = [
        'token_id' => 'string',
        'ip_address' => 'string',
        'browser' => 'string',
        'browser_version' => 'string',
        'platform' => 'string',
        'device_type' => 'string',
        'is_mobile' => 'boolean',
        'is_tablet' => 'boolean',
        'is_desktop' => 'boolean',
        'logout_at' => 'datetime',
        'session_duration' => 'integer',
        'user_id' => 'integer',
        'deleted_by_parent' => 'boolean',
    ];

    // APPENDS
    protected $appends = [
        
    ];


    // INCLUDES
    public function user() { return $this->belongsTo(User::class); }
        
    // SCOPES
    
    public function scopeSearch(Builder $builder, string $search)
    {
        $parsedDate = DateService::parseDate($search);

        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('logout_at', $parsedDate->format('Y-m-d'));
                $query->whereDate('created_at', $parsedDate->format('Y-m-d'));
                $query->whereDate('updated_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $builder->where(function ($query) use ($search) {
                // Busca parcial de datas (exemplo: dia/mês ou apenas mês)
                if (preg_match('/^\d{1,2}\/\d{1,2}$/', $search)) {
                    // Exemplo: 29/03 (dia/mês)
                    list($day, $month) = explode('/', $search);
                    $query->whereMonth('logout_at', $month)
                            ->whereDay('logout_at', $day);
                    $query->whereMonth('created_at', $month)
                            ->whereDay('created_at', $day);
                    $query->whereMonth('updated_at', $month)
                            ->whereDay('updated_at', $day);
                } elseif (preg_match('/^\d{1,2}\/\$/', $search)) {
                    // Exemplo: 29/ (somente dia)
                    $day = str_replace('/', '', $search);
                    $query->whereDay('logout_at', $day);
                    $query->whereDay('created_at', $day);
                    $query->whereDay('updated_at', $day);
                } elseif (preg_match('/^\/\d{1,2}$/', $search)) {
                    // Exemplo: /05 (somente mês)
                    $month = str_replace('/', '', $search);
                    $query->whereMonth('logout_at', $month);
                    $query->whereMonth('created_at', $month);
                    $query->whereMonth('updated_at', $month);
                } else {
                    $query->orWhere('token_id', 'LIKE', "%$search%");
                    $query->orWhere('ip_address', 'LIKE', "%$search%");
                    $query->orWhere('browser', 'LIKE', "%$search%");
                    $query->orWhere('browser_version', 'LIKE', "%$search%");
                    $query->orWhere('platform', 'LIKE', "%$search%");
                    $query->orWhere('device_type', 'LIKE', "%$search%");
                }
            });
        }
    }
    
    public function scopeIsMobile(Builder $query): void
    {
        $query->where('is_mobile', 1);
    }

    
    public function scopeNotIsMobile(Builder $query): void
    {
        $query->where('is_mobile', 0);
    }
        
    public function scopeIsTablet(Builder $query): void
    {
        $query->where('is_tablet', 1);
    }

    
    public function scopeNotIsTablet(Builder $query): void
    {
        $query->where('is_tablet', 0);
    }
        
    public function scopeIsDesktop(Builder $query): void
    {
        $query->where('is_desktop', 1);
    }

    
    public function scopeNotIsDesktop(Builder $query): void
    {
        $query->where('is_desktop', 0);
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
        
    public function scopeLogoutAt(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('logout_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('logout_at', $parsedDate);
            });
        }
    }

    // Scope for dates before a given date
    public function scopeLogoutAtBefore(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('logout_at', '<=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('logout_at', $parsedDate);
            });
        }
    }

    // Scope for dates after a given date
    public function scopeLogoutAtAfter(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('logout_at', '>=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('logout_at', $parsedDate);
            });
        }
    }

    // Scope for dates between a start and end date
    public function scopeLogoutAtBetween(Builder $builder, $startDate, $endDate)
    {
        $parsedStartDate = DateService::parseDate($startDate);
        $parsedEndDate = DateService::parseDate($endDate);

        if ($parsedStartDate && $parsedEndDate) {
            $builder->where(function ($query) use ($parsedStartDate, $parsedEndDate) {
                $query->whereBetween('logout_at', [
                    $parsedStartDate->startOfDay(),
                    $parsedEndDate->endOfDay()
                ]);
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('logout_at', $parsedDate);
            });
        }
    }

    // Scope para filtrar por dia atual
    public function scopeLogoutAtCurrentDay(Builder $builder)
    {
        return $builder->whereDate('logout_at', DateService::today()->format('Y-m-d'));
    }

    // Scope para filtrar por semana atual
    public function scopeLogoutAtCurrentWeek(Builder $builder)
    {
        return $builder->whereBetween('logout_at', [DateService::currentStartOfWeek()->format('Y-m-d 00:00:00'), DateService::currentEndOfWeek()->format('Y-m-d 23:59:59')]);
    }

    // Scope para filtrar por mês atual
    public function scopeLogoutAtCurrentMonth(Builder $builder)
    {
        return $builder->whereMonth('logout_at', DateService::currentMonth())
            ->whereYear('logout_at', DateService::currentYear());
    }

    // Scope para filtrar por anteriores X dias
    public function scopeLogoutAtLastDays(Builder $builder, int $days)
    {
        return $builder->whereBetween('logout_at', [DateService::currentLastDays($days)->format('Y-m-d 00:00:00'), DateService::today()->format('Y-m-d 23:59:59')]);
    }
    // Scope para filtrar por próximos X dias
    public function scopeLogoutAtNextDays(Builder $builder, int $days)
    {
        return $builder->whereBetween('logout_at', [DateService::today()->format('Y-m-d 00:00:00'), DateService::currentNextDays($days)->format('Y-m-d 23:59:59')]);
    }
        

    // QUERY BUILDER
    
    /**
     * The allowed filters attributes.
     * id,token_id,ip_address,browser,browser_version,platform,device_type,is_mobile,IsMobile,NotIsMobile,is_tablet,IsTablet,NotIsTablet,is_desktop,IsDesktop,NotIsDesktop,LogoutAt,LogoutAtBefore,LogoutAtAfter,LogoutAtBetween,LogoutAtCurrentDay,LogoutAtCurrentWeek,LogoutAtCurrentMonth,LogoutAtLastDays,LogoutAtNextDays,user_id,CreatedAt,CreatedAtBefore,CreatedAtAfter,CreatedAtBetween,CreatedAtCurrentDay,CreatedAtCurrentWeek,CreatedAtCurrentMonth,CreatedAtLastDays,CreatedAtNextDays,UpdatedAt,UpdatedAtBefore,UpdatedAtAfter,UpdatedAtBetween,UpdatedAtCurrentDay,UpdatedAtCurrentWeek,UpdatedAtCurrentMonth,UpdatedAtLastDays,UpdatedAtNextDays,Search
     */
    public static function ALLOWEDFILTERS()
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::partial('token_id'),
            AllowedFilter::partial('ip_address'),
            AllowedFilter::partial('browser'),
            AllowedFilter::partial('browser_version'),
            AllowedFilter::partial('platform'),
            AllowedFilter::partial('device_type'),
            AllowedFilter::exact('is_mobile'),
            AllowedFilter::scope('IsMobile'),
            AllowedFilter::scope('NotIsMobile'),
            AllowedFilter::exact('is_tablet'),
            AllowedFilter::scope('IsTablet'),
            AllowedFilter::scope('NotIsTablet'),
            AllowedFilter::exact('is_desktop'),
            AllowedFilter::scope('IsDesktop'),
            AllowedFilter::scope('NotIsDesktop'),
            AllowedFilter::scope('LogoutAt'),
            AllowedFilter::scope('LogoutAtBefore'),
            AllowedFilter::scope('LogoutAtAfter'),
            AllowedFilter::scope('LogoutAtBetween'),
            AllowedFilter::scope('LogoutAtCurrentDay'),
            AllowedFilter::scope('LogoutAtCurrentWeek'),
            AllowedFilter::scope('LogoutAtCurrentMonth'),
            AllowedFilter::scope('LogoutAtLastDays'),
            AllowedFilter::scope('LogoutAtNextDays'),
            AllowedFilter::exact('user_id'),
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
            AllowedFilter::scope('Search'),
    
        ];
    }
    
    /**
     * The allowed includes relationships.
     * user
     */
    public static function ALLOWEDINCLUDES()
    {
        return ['user'];
    }
    
    /**
     * The allowed sorts attributes.
     * id,token_id,ip_address,browser,browser_version,platform,device_type,is_mobile,is_tablet,is_desktop,logout_at,created_at,updated_at
     */
    public static function ALLOWEDSORTS()
    {
        return ['id', 'token_id', 'ip_address', 'browser', 'browser_version', 'platform', 'device_type', 'is_mobile', 'is_tablet', 'is_desktop', 'logout_at', 'created_at', 'updated_at'];
    }
    
    /**
     * The Default sorts attributes.
     * token_id,ip_address,browser,browser_version,platform,device_type,is_mobile,is_tablet,is_desktop,logout_at
     */
    public static function DEFAULTSORT()
    {
        return ['token_id', 'ip_address', 'browser', 'browser_version', 'platform', 'device_type', 'is_mobile', 'is_tablet', 'is_desktop', 'logout_at'];
    }
    
    /**
     * The default includes relationships.
     * user
     */
    public static function DEFAULTINCLUDES()
    {
        return ['user'];
    }

}
