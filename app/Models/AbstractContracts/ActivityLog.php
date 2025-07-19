<?php

namespace App\Models\AbstractContracts;

use App\Models\LogSubjectType;
use App\Services\DateService;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

abstract class ActivityLog extends SpatieActivity
{
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
                    $query->orWhere('subject_type', 'LIKE', "%$search%");
                    $query->orWhere('subject_id', 'LIKE', "%$search%");
                    $query->orWhere('subject_name', 'LIKE', "%$search%");
                    $query->orWhere('event', 'LIKE', "%$search%");
                    $query->orWhere('causer_type', 'LIKE', "%$search%");
                    $query->orWhere('causer_id', 'LIKE', "%$search%");
                    $query->orWhereHas('causer', function ($q)  use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                    $query->orWhereHas('subject', function ($q) use ($search) {
                        // Obter o que_subject_name a partir do LogSubjectType
                        $subjectType = LogSubjectType::where('type', $this->subject_type)->first();
                        $fieldName = $subjectType ? $subjectType->what_subject_name : 'name'; // Padrão para 'name'
                        $q->where($fieldName, 'like', "%{$search}%");
                    });
                }
            });
        }
    }
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


    // Método para obter o nome ou título do sujeito
    public function getSubjectDisplayNameAttribute()
    {
        $subject = $this->subject;

        if ($subject) {
            // Retornar 'title' se existir, caso contrário, retornar 'name'
            return $subject->title ?? $subject->name ?? 'Unknown';
        }

        return 'Unknown';
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
            AllowedFilter::exact('event'),              // Filtra por evento (created, updated, deleted)
            AllowedFilter::exact('subject_type'),       // Filtra pelo tipo de subject (modelo afetado)
            AllowedFilter::exact('subject_id'),         // Filtra pelo ID do subject
            AllowedFilter::scope('subject_name'),        // Filtra pelo nome do subject
            AllowedFilter::exact('causer_id'),          // Filtra pelo ID do causer
            AllowedFilter::scope('causer_name'),        // Filtra pelo nome do causer
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
     * users,subject,causer
     */
    public static function ALLOWEDINCLUDES()
    {
        return ['subject', 'causer'];
    }

    /**
     * The allowed sorts attributes.
     * id,subject_type,subject_name,event,causer_type,causer_name,created_at,updated_at,deleted_at
     */
    public static function ALLOWEDSORTS()
    {
        return ['id', 'subject_type', 'subject_name', 'event', 'causer_type', 'causer_name', 'created_at', 'updated_at', 'deleted_at'];
    }

    /**
     * The Default sorts attributes.
     * name
     */
    public static function DEFAULTSORT()
    {
        return ['-created_at'];
    }

    /**
     * The default includes relationships.
     * users
     */
    public static function DEFAULTINCLUDES()
    {
        return ['subject', 'causer'];
    }
}
