<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UserLoginSanctumRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'identifier' => 'required',
            'password' => 'required_without:pin_code',
            'pin_code' => 'required_without:password|digits:6',
        ];
    }

    public function bodyParameters()
    {
        return [
            'identifier' => [
                'description' => 'Informe E-Mail ou username',
                'example' => 'user@app.com',
            ],
            'password' => [
                'description' => 'Informe sua Senha de registro.',
                'example' => 'password',
            ],
            'pin_code' => [
                'description' => 'Informe seu PIN de 6 dígitos.',
                'example' => '123456',
            ],
        ];
    }
}
