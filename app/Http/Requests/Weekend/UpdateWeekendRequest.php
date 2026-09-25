<?php

namespace App\Http\Requests\Weekend;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWeekendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'weekends' => ['nullable', 'array'],
            'weekends.*' => ['integer', 'between:0,6'],
        ];
    }
}
