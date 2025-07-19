<?php

namespace App\Models\AbstractContracts;

use App\Services\DateService;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

abstract class DeletedRecord extends Model
{
    protected $guarded = [];

    //SCOPES
    public function scopeSearch(Builder $builder, string $search)
    {
        $parsedDate = DateService::parseDate($search);

        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('deleted_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $builder->where(function ($query) use ($search) {
                // Busca parcial de datas (exemplo: dia/mês ou apenas mês)
                if (preg_match('/^\d{1,2}\/\d{1,2}$/', $search)) {
                    // Exemplo: 29/03 (dia/mês)
                    list($day, $month) = explode('/', $search);
                    $query->whereMonth('deleted_at', $month)
                        ->whereDay('deleted_at', $day);
                } elseif (preg_match('/^\d{1,2}\/\$/', $search)) {
                    // Exemplo: 29/ (somente dia)
                    $day = str_replace('/', '', $search);
                    $query->whereDay('deleted_at', $day);
                } elseif (preg_match('/^\/\d{1,2}$/', $search)) {
                    // Exemplo: /05 (somente mês)
                    $month = str_replace('/', '', $search);
                    $query->whereMonth('deleted_at', $month);
                }
            });
        }
    }

    public function scopeDeletedAt(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('deleted_at', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('deleted_at', $parsedDate);
            });
        }
    }

    // Scope for dates before a given date
    public function scopeDeletedAtBefore(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('deleted_at', '<=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('deleted_at', $parsedDate);
            });
        }
    }

    // Scope for dates after a given date
    public function scopeDeletedAtAfter(Builder $builder, $date)
    {
        $parsedDate = DateService::parseDate($date);
        if ($parsedDate) {
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('deleted_at', '>=', $parsedDate->format('Y-m-d'));
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('deleted_at', $parsedDate);
            });
        }
    }

    // Scope for dates between a start and end date
    public function scopeDeletedAtBetween(Builder $builder, $startDate, $endDate)
    {
        $parsedStartDate = DateService::parseDate($startDate);
        $parsedEndDate = DateService::parseDate($endDate);

        if ($parsedStartDate && $parsedEndDate) {
            $builder->where(function ($query) use ($parsedStartDate, $parsedEndDate) {
                $query->whereBetween('deleted_at', [
                    $parsedStartDate->startOfDay(),
                    $parsedEndDate->endOfDay()
                ]);
            });
        } else {
            $parsedDate = DateService::parseDate('1912-01-01');
            $builder->where(function ($query) use ($parsedDate) {
                $query->whereDate('deleted_at', $parsedDate);
            });
        }
    }



    // public function scopeSubjectName(Builder $query, ?string $subjectName)
    // {
    //     if ($subjectName) {
    //         return $query->whereHas('subject', function ($subQuery) use ($subjectName) {
    //             // Obter o que_subject_name a partir do LogSubjectType
    //             $subjectType = LogSubjectType::where('type', $this->subject_type)->first();
    //             $fieldName = $subjectType ? $subjectType->what_subject_name : 'name'; // Padrão para 'name'
    //             $subQuery->where($fieldName, 'like', "%{$subjectName}%");
    //         });
    //     }
    //     return $query;
    // }

    // Escopo para filtrar por nome do causer (nome do responsável pela ação)
    public function scopeCauserName(Builder $query, $name)
    {
        return $query->whereHas('causer', function ($q) use ($name) {
            $q->where('name', 'like', "%{$name}%");
        });
    }

    public function subject()
    {
        return $this->morphTo();
    }
    public function causer()
    {
        return $this->morphTo();
    }

    // QUERY BUILDER

    /**
     * The allowed filters attributes.
     * DeletedAt,DeletedAtBefore,DeletedAtAfter,DeletedAtBetween,CauserName,Search
     */
    public static function ALLOWEDFILTERS()
    {
        return [
            AllowedFilter::scope('DeletedAt'),
            AllowedFilter::scope('DeletedAtBefore'),
            AllowedFilter::scope('DeletedAtAfter'),
            AllowedFilter::scope('DeletedAtBetween'),
            AllowedFilter::scope('CauserName'),
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
     * deleted_at
     */
    public static function ALLOWEDSORTS()
    {
        return ['deleted_at'];
    }

    /**
     * The Default sorts attributes.
     * deleted_at
     */
    public static function DEFAULTSORT()
    {
        return ['deleted_at'];
    }
}
