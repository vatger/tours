<?php

namespace App\Http\Requests\Api\V1\Gender;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @version V1 generated
 */
class ChangeGenderIconRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $supportedImageMimes = implode(',', config('image-file.supported_images'));

        return [
            'icon' => 'required|image|mimes:' . $supportedImageMimes . '|max:2048',
        ];
    }

    public function bodyParameters()
    {
        return [
            'icon' => [
                'description' => 'icon to represent',
            ],
        ];
    }
}