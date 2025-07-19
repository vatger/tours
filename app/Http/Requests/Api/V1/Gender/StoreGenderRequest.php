<?php

namespace App\Http\Requests\Api\V1\Gender;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Gender;
use App\Services\DateService;
/**
 * @version V1
 */
class StoreGenderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $supportedImageMimes = implode(',', config('image-file.supported_images'));
        
        
        return [
            'name' => 'required|string|max:255|' . Rule::unique(Gender::class,'name'),
            'icon' => 'nullable|image|mimes:' . $supportedImageMimes . '|max:255'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => 0,
            
        ]);

        
    }

    public function bodyParameters()
    {
        return [
            'name' => [
                        'description' => 'Determina o gênero',
                        'example' => 'male'
                ],
            'icon' => [
                    'description' => 'icon to represent',
                ],
            
        ];
    }
}