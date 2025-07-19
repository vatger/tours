<?php

namespace App\Services;

use Spatie\QueryBuilder\QueryBuilder;

class QueryService
{
    /**
     * exists
     *
     * @param  mixed $model
     * @param  mixed $attribute attribute name for search
     * @param  mixed $value value in attribute to be serached
     * @return bool true it was found | false it was not found
     */
    public static function exists($model, string $attribute, string $value): bool
    {
        $object =  QueryBuilder::for($model::where($attribute, $value))->first();
        if ($object) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * query
     *
     * @param  mixed $model
     * @param  mixed $allowedFilters
     * @param  mixed $allowedIncludes
     * @param  mixed $allowedSorts
     * @param  array|string|\Spatie\QueryBuilder\AllowedSort $defaultSorts
     * @return Spatie\QueryBuilder\QueryBuilder
     */
    public static function query($model, mixed $allowedFilters = null, mixed $allowedIncludes = null, mixed $allowedSorts = null, $defaultSorts = null, $defaultIncludes = []): \Spatie\QueryBuilder\QueryBuilder
    {
        $query =
            QueryBuilder::for($model);

        if ($allowedFilters) {
            $query->allowedFilters($allowedFilters);
        }
        if ($allowedIncludes) {
            $query->allowedIncludes($allowedIncludes);
        }
        if ($allowedSorts) {
            $query->allowedSorts($allowedSorts);
        }
        if ($defaultSorts) {
            $query->defaultSorts($defaultSorts);
        }
        if ($defaultIncludes) {
            $query->with($defaultIncludes);
        }

        return $query;
    }

    public static function getOrFailById($model, string $value, array $allowedIncludes = [], $defaultIncludes = [])
    {
        return self::findOrFail($model, 'id', $value, $allowedIncludes, $defaultIncludes);
    }

    public static function getById($model, string $value, array $allowedIncludes = [], $defaultIncludes = [])
    {
        return self::find($model, 'id', $value, $allowedIncludes, $defaultIncludes);
    }


    public static function find($model, string $attribute, string $value, array $allowedIncludes = [], array $defaultIncludes = [])
    {
        $object =  QueryBuilder::for($model::where($attribute, $value));
        if ($allowedIncludes) {
            $object->allowedIncludes($allowedIncludes);
        }
        if ($defaultIncludes) {
            $object->with($defaultIncludes);
        }
        return $object->first();
    }

    public static function findOrFail($model, string $attribute, string $value, array $allowedIncludes = [], array $defaultIncludes = [])
    {
        $object =  QueryBuilder::for($model::where($attribute, $value));
        if ($allowedIncludes) {
            $object->allowedIncludes($allowedIncludes);
        }
        if ($defaultIncludes) {
            $object->with($defaultIncludes);
        }

        return $object->firstOrFail();
    }

    //TRASHED
    public static function getOrFailTrashedById($model, string $value, array $allowedIncludes = [], array $defaultIncludes = [])
    {
        return self::findOrFailTrashed($model, 'id', $value, $allowedIncludes, $defaultIncludes);
    }

    public static function getTrashedById($model, string $value, array $allowedIncludes = [], array $defaultIncludes = [])
    {
        return self::findTrashed($model, 'id', $value, $allowedIncludes, $defaultIncludes);
    }

    public static function findTrashed($model, string $attribute, string $value, array $allowedIncludes = [], array $defaultIncludes = [])
    {
        $object =  QueryBuilder::for($model::where($attribute, $value)->onlyTrashed());
        if ($allowedIncludes) {
            $object->allowedIncludes($allowedIncludes);
        }
        if ($defaultIncludes) {
            $object->with($defaultIncludes);
        }

        return $object->first();
    }

    public static function findOrFailTrashed($model, string $attribute, string $value, array $allowedIncludes = [], array $defaultIncludes = [])
    {
        $object =  QueryBuilder::for($model::where($attribute, $value)->onlyTrashed());
        if ($allowedIncludes) {
            $object->allowedIncludes($allowedIncludes);
        }
        if ($defaultIncludes) {
            $object->with($defaultIncludes);
        }

        return $object->firstOrFail();
    }
}
