<?php

namespace App\Models\AbstractContracts;

use App\Services\DateService;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class CommandLog extends Model
{
    protected $table = 'command_logs';

    // Atributos que podem ser atribuídos em massa
    protected $fillable = [
        'command',
        'output',
        'status',
        'executed_at',
    ];

    //SCOPES
    public function scopeSearch(Builder $builder, string $search)
    {
        $parsedDate = DateService::parseDate($search);

        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('executed_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $builder->where(function ($query) use ($search) {
                // Busca parcial de datas (exemplo: dia/mês ou apenas mês)
                if (preg_match('/^\d{1,2}\/\d{1,2}$/', $search)) {
                    // Exemplo: 29/03 (dia/mês)
                    list($day, $month) = explode('/', $search);
                    $query->whereMonth('executed_at', $month)
                        ->whereDay('executed_at', $day);
                } elseif (preg_match('/^\d{1,2}\/\$/', $search)) {
                    // Exemplo: 29/ (somente dia)
                    $day = str_replace('/', '', $search);
                    $query->whereDay('executed_at', $day);
                } elseif (preg_match('/^\/\d{1,2}$/', $search)) {
                    // Exemplo: /05 (somente mês)
                    $month = str_replace('/', '', $search);
                    $query->whereMonth('executed_at', $month);
                } else {
                    $query->orWhere('command', 'LIKE', "%$search%");
                }
            });
        }
    }

    public function scopeExecutedAt(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('executed_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('executed_at', $parsedDate);
            });
        }
    }

    // Scope for dates before a given date
    public function scopeExecutedAtBefore(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('executed_at', '<=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('executed_at', $parsedDate);
            });
        }
    }

    // Scope for dates after a given date
    public function scopeExecutedAtAfter(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('executed_at', '>=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('executed_at', $parsedDate);
            });
        }
    }

    // Scope for dates between a start and end date
    public function scopeExecutedAtBetween(Builder $builder, $startDate, $endDate)
    {
        $parsedStartDate = DateService::parseDate($startDate);
        $parsedEndDate = DateService::parseDate($endDate);

        if ($parsedStartDate && $parsedEndDate) {
            $builder->where(function ($query) use ($parsedStartDate, $parsedEndDate) {
                $query->whereBetween('executed_at', [$parsedStartDate->format('Y-m-d'), $parsedEndDate->format('Y-m-d')]);
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('executed_at', $parsedDate);
            });
        }
    }


    // QUERY BUILDER

    /**
     * The allowed filters attributes.
     * command,status,ExecutedAt,ExecutedAtBefore,ExecutedAtAfter,ExecutedAtBetween,Search
     */
    public static function ALLOWEDFILTERS()
    {
        return [
            AllowedFilter::partial('command'),
            AllowedFilter::exact('status'),

            AllowedFilter::scope('ExecutedAt'),
            AllowedFilter::scope('ExecutedAtBefore'),
            AllowedFilter::scope('ExecutedAtAfter'),
            AllowedFilter::scope('ExecutedAtBetween'),
            AllowedFilter::scope('Search'),

        ];
    }

    /**
     * The allowed includes attributes.
     *
     */
    public static function ALLOWEDINCLUDES()
    {
        return [];
    }

    /**
     * The allowed sorts attributes.
     * executed_at
     */
    public static function ALLOWEDSORTS()
    {
        return ['executed_at'];
    }

    /**
     * The Default sorts attributes.
     * executed_at
     */
    public static function DEFAULTSORT()
    {
        return ['executed_at'];
    }
}
