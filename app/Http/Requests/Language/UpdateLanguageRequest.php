<?php

namespace App\Http\Requests\Language;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $langId = $this->route('language')?->id ?? $this->route('language');

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('languages', 'code')->ignore($langId)],
            'native' => ['nullable', 'string', 'max:100'],
            'rtl' => ['nullable'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
