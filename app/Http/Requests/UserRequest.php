<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $supportedImageMimes = implode(',', config('image-file.supported_images'));

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'avatar' => 'nullable|image|mimes:' . $supportedImageMimes . '|max:2048',
            'username' => 'nullable|string|max:255|unique:users,username',
            'pin_code' => 'nullable|numeric|max:999999|min:1',
            'locale' => 'nullable|string|max:5',
            'nickname' => 'nullable|string|max:20',
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['email'] = 'required|string|email|max:255|unique:users,email,' . $this->user->id;
            $rules['username'] = 'nullable|string|max:255|unique:users,username,' . $this->user->id;
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }
}
