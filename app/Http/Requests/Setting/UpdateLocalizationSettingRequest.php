<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocalizationSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'timezone' => ['required', 'string', 'max:100'],
            'date_format' => ['required', 'string', 'max:50'],
            'time_format' => ['required', 'string', 'max:50'],
            'default_currency' => ['required', 'string', 'max:10'],
            'default_language' => ['required', 'string', 'max:10'],
            'week_start_day' => ['required', 'string', 'in:sunday,monday,saturday'],
        ];
    }
}
