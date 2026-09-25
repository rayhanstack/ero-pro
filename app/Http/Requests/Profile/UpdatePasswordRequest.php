<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => _trans('common.Current password is required'),
            'current_password.current_password' => _trans('common.The provided current password does not match our records'),
            'password.required' => _trans('common.New password is required'),
            'password.min' => _trans('common.Password must be at least 6 characters'),
            'password.confirmed' => _trans('common.Password confirmation does not match'),
        ];
    }
}
