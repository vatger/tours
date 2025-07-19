<?php

namespace App\Http\Requests\Api\V1\Gender;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Gender;
use App\Services\DateService;
/**
 * @version V1
 */
class UpdateGenderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $supportedImageMimes = implode(',', config('image-file.supported_images'));
        
        
        return [
            'name' => 'sometimes|nullable|string|max:255|' . Rule::unique(Gender::class)->whereNull('deleted_at')->ignore($this->gender_id),
            'icon' => 'sometimes|nullable|image|mimes:' . $supportedImageMimes . '|max:255'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            
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