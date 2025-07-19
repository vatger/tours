<?php

namespace App\Http\Requests\SystemDevelopment;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class StoreModelRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z][a-zA-Z0-9]*$/',
                function ($attribute, $value, $fail) {
                    $filename = Str::slug($value) . '.json';
                    if (Storage::exists("models/{$filename}")) {
                        $fail('A model with this name already exists.');
                    }
                }
            ],
            'version' => 'required|string|max:10',
            'softDeletes' => 'boolean',
            'timestamps' => 'boolean',
            'useIsActive' => 'boolean',
            'useApprovedStatus' => 'boolean',
            'useScribe' => 'boolean',
            'authorize' => 'boolean',
            'logsActivity' => 'boolean',
            'clearsResponseCache' => 'boolean',
            'attributes' => 'required|array|min:1',
            'attributes.*.name' => 'required|string|max:255|distinct|regex:/^[a-z_][a-z0-9_]*$/',
            'attributes.*.type' => [
                'required',
                Rule::in([
                    // Tipos inteiros
                    'bigInteger',
                    'integer',
                    'tinyInteger',
                    'smallInteger',
                    'mediumInteger',
                    'unsignedBigInteger',
                    'unsignedInteger',
                    'unsignedMediumInteger',
                    'unsignedSmallInteger',
                    'unsignedTinyInteger',

                    // Tipos morphs
                    'morphs',
                    'nullableMorphs',
                    'numericMorphs',
                    'nullableNumericMorphs',
                    'uuidMorphs',
                    'nullableUuidMorphs',
                    'ulidMorphs',
                    'nullableUlidMorphs',

                    // Tipos booleanos
                    'boolean',

                    // Tipos decimais
                    'decimal',
                    'float',
                    'double',

                    // Tipos texto
                    'string',
                    'char',
                    'text',
                    'mediumText',
                    'longText',
                    'email',

                    // Tipos binários
                    'binary',

                    // Tipos data/hora
                    'date',
                    'datetime',
                    'dateTime',
                    'timestamp',
                    'timeStamp',
                    'time',
                    'year',

                    // Tipos JSON
                    'json',
                    'jsonb',

                    // Tipos UUID/IDs
                    'uuid',
                    'foreignId',
                    'foreignUuid',

                    // Enumerações
                    'enum',
                    'set',

                    // Tipos geométricos
                    'geometry',
                    'point',
                    'linestring',
                    'polygon',
                    'geometryCollection',
                    'multipoint',
                    'multilinestring',
                    'multipolygon',

                    // Tipos de endereço
                    'ipAddress',
                    'macAddress',

                    // Tipos especiais
                    'file',
                    'image',
                    'video'
                ])
            ],
            'attributes.*.length' => 'nullable|integer|min:0|max:65535',
            'attributes.*.precision' => 'nullable|integer|min:0|max:65',
            'attributes.*.scale' => 'nullable|integer|min:0|max:30',
            'attributes.*.values' => 'nullable|array',
            'attributes.*.values.*' => 'string|max:255',
            'attributes.*.max' => 'nullable|integer|min:0',
            'attributes.*.min' => 'nullable|integer|min:0',
            'attributes.*.validate' => 'boolean',

            'attributes.*.required' => 'boolean',
            'attributes.*.nullable' => 'boolean',
            'attributes.*.unique' => 'boolean',
            'attributes.*.translated' => 'boolean',

            'attributes.*.sortAble' => 'boolean',
            'attributes.*.filterAble' => 'boolean',
            'attributes.*.exactFilter' => 'boolean',
            'attributes.*.searchAble' => 'boolean',

            'attributes.*.description' => 'nullable|string|max:500',
            'attributes.*.example' => 'nullable|string|max:255',
            'relations' => 'nullable|array',
            'relations.*.name' => 'required_with:relations|string|max:255',
            'relations.*.type' => [
                'required_with:relations',
                Rule::in(['belongsTo', 'hasMany', 'belongsToMany', 'hasOne'])
            ],
            'relations.*.default' => 'boolean',

            'relations.*.related' => 'required_with:relations|string|max:255',
            'relations.*.description' => 'nullable|string|max:500',
        ];
    }
}
