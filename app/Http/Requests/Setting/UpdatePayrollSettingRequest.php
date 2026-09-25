<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'working_days_mode' => ['required', 'string', 'in:monthly_fixed,calendar_days,working_days'],
            'overtime_rate' => ['required', 'numeric', 'min:0'],
        ];
    }
}
