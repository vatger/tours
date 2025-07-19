<?php

namespace App\Http\Requests\SystemDevelopment;

class UpdateModelRequest extends StoreModelRequest
{
    public function rules()
    {
        $rules = parent::rules();

        // Remove unique validation for name
        $rules['name'] = array_filter($rules['name'], function ($rule) {
            return !is_callable($rule);
        });

        return $rules;
    }
}
