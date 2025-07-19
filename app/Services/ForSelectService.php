<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ForSelectService
{
    /**
     * Transforma uma coleção de modelos em um array formatado para selects
     *
     * @param Collection $models Coleção de modelos
     * @param string $idField Campo a ser usado como valor (id)
     * @param string $nameField Campo a ser usado como rótulo (nome)
     * @return array<array{id: mixed, name: mixed}>
     */
    public static function fromModels(Collection $models, string $idField = 'id', string $nameField = 'name'): array
    {
        return $models->map(function (Model $model) use ($idField, $nameField) {
            return [
                'id' => $model->{$idField},
                'name' => $model->{$nameField}
            ];
        })->all();
    }

    public static function fromQuery($query, string $idField = 'id', string $nameField = 'name'): array
    {
        return self::fromModels($query->get(), $idField, $nameField);
    }

    public static function fromArray(array $items, string $idKey = 'id', string $nameKey = 'name'): array
    {
        return array_map(function ($item) use ($idKey, $nameKey) {
            return [
                'id' => $item[$idKey],
                'name' => $item[$nameKey]
            ];
        }, $items);
    }
}
