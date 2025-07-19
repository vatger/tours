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
abstract class LoginLog extends Model
{
    use HasFactory;
    
    
    
    
    
    
    

    // CASCADE DELETES
    protected $cascadeDeletes = [];

    
    protected $fillable = [
        'identifier',
        'ip_address',
        'user_agent',
        'status',
        'error_message',
        'user_id',
        'deleted_by_parent',    
    ];

    protected $casts = [
        'identifier' => 'string',
        'ip_address' => 'string',
        'user_agent' => 'string',
        'status' => 'string',
        'error_message' => 'string',
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
                $query->whereDate('created_at', $parsedDate->format('Y-m-d'));
                $query->whereDate('updated_at', $parsedDate->format('Y-m-d'));
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
                } elseif (preg_match('/^\d{1,2}\/\$/', $search)) {
                    // Exemplo: 29/ (somente dia)
                    $day = str_replace('/', '', $search);
                    $query->whereDay('created_at', $day);
                    $query->whereDay('updated_at', $day);
                } elseif (preg_match('/^\/\d{1,2}$/', $search)) {
                    // Exemplo: /05 (somente mês)
                    $month = str_replace('/', '', $search);
                    $query->whereMonth('created_at', $month);
                    $query->whereMonth('updated_at', $month);
                } else {
                    $query->orWhere('identifier', 'LIKE', "%$search%");
                    $query->orWhere('ip_address', 'LIKE', "%$search%");
                    $query->orWhere('user_agent', 'LIKE', "%$search%");
                    $query->orWhere('status', 'LIKE', "%$search%");
                    $query->orWhere('error_message', 'LIKE', "%$search%");
                }
            });
        }
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
        

    // QUERY BUILDER
    
    /**
     * The allowed filters attributes.
     * id,identifier,ip_address,user_agent,status,error_message,user_id,CreatedAt,CreatedAtBefore,CreatedAtAfter,CreatedAtBetween,CreatedAtCurrentDay,CreatedAtCurrentWeek,CreatedAtCurrentMonth,CreatedAtLastDays,CreatedAtNextDays,UpdatedAt,UpdatedAtBefore,UpdatedAtAfter,UpdatedAtBetween,UpdatedAtCurrentDay,UpdatedAtCurrentWeek,UpdatedAtCurrentMonth,UpdatedAtLastDays,UpdatedAtNextDays,Search
     */
    public static function ALLOWEDFILTERS()
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::partial('identifier'),
            AllowedFilter::partial('ip_address'),
            AllowedFilter::partial('user_agent'),
            AllowedFilter::partial('status'),
            AllowedFilter::partial('error_message'),
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
     * id,identifier,ip_address,user_agent,status,error_message,created_at,updated_at
     */
    public static function ALLOWEDSORTS()
    {
        return ['id', 'identifier', 'ip_address', 'user_agent', 'status', 'error_message', 'created_at', 'updated_at'];
    }
    
    /**
     * The Default sorts attributes.
     * identifier,ip_address,user_agent,status,error_message
     */
    public static function DEFAULTSORT()
    {
        return ['identifier', 'ip_address', 'user_agent', 'status', 'error_message'];
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
