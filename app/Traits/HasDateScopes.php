<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use App\Services\DateService;

trait HasDateScopes
{
    public function initializeHasDateScopes()
    {
        if (!isset($this->dateFields) || !is_array($this->dateFields)) {
            $this->dateFields = ['created_at', 'updated_at'];
        }
    }

    protected function applyDateScope(Builder $builder, $field, $method, ...$params)
    {
        if (in_array($field, $this->dateFields)) {
            return $this->{"scope{$method}"}($builder, $field, ...$params);
        }
        return $builder;
    }

    protected function scopeAt(Builder $builder, $field, $date)
    {
        $parsedDate = DateService::parseDate($date);
        return $builder->whereDate($field, $parsedDate->format('Y-m-d') ? $parsedDate->format('Y-m-d') : '1912-01-01');
    }

    protected function scopeAtBefore(Builder $builder, $field, $date)
    {
        $parsedDate = DateService::parseDate($date);
        return $builder->whereDate($field, '<=', $parsedDate ? $parsedDate->format('Y-m-d') : '1912-01-01');
    }

    protected function scopeAtAfter(Builder $builder, $field, $date)
    {
        $parsedDate = DateService::parseDate($date);
        return $builder->whereDate($field, '>=', $parsedDate ? $parsedDate->format('Y-m-d') : '1912-01-01');
    }

    protected function scopeAtBetween(Builder $builder, $field, $startDate, $endDate)
    {
        $parsedStartDate = DateService::parseDate($startDate);
        $parsedEndDate = DateService::parseDate($endDate);
        return $builder->whereBetween($field, [
            $parsedStartDate ? $parsedStartDate->startOfDay() : '1912-01-01',
            $parsedEndDate ? $parsedEndDate->endOfDay() : '1912-01-01'
        ]);
    }

    protected function scopeAtCurrentDay(Builder $builder, $field)
    {
        return $builder->whereDate($field, DateService::today()->format('Y-m-d'));
    }

    protected function scopeAtCurrentWeek(Builder $builder, $field)
    {
        return $builder->whereBetween($field, [
            DateService::currentStartOfWeek()->format('Y-m-d 00:00:00'),
            DateService::currentEndOfWeek()->format('Y-m-d 23:59:59')
        ]);
    }

    protected function scopeAtCurrentMonth(Builder $builder, $field)
    {
        return $builder->whereMonth($field, DateService::currentMonth())
            ->whereYear($field, DateService::currentYear());
    }

    protected function scopeAtLastDays(Builder $builder, $field, int $days)
    {
        return $builder->whereBetween($field, [
            DateService::currentLastDays($days)->format('Y-m-d 00:00:00'),
            DateService::today()->format('Y-m-d 23:59:59')
        ]);
    }

    protected function scopeAtNextDays(Builder $builder, $field, int $days)
    {
        return $builder->whereBetween($field, [
            DateService::today()->format('Y-m-d 00:00:00'),
            DateService::currentNextDays($days)->format('Y-m-d 23:59:59')
        ]);
    }
}
