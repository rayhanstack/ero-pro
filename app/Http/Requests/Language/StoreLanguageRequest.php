<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', 'unique:languages,code'],
            'native' => ['nullable', 'string', 'max:100'],
            'rtl' => ['nullable'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
