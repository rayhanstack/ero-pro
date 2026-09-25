<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'office_start' => ['required', 'string'],
            'office_end' => ['required', 'string'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'late_after' => ['required', 'string'],
            'half_day_hours' => ['required', 'numeric', 'min:1', 'max:24'],
            'overtime_enabled' => ['nullable'],
        ];
    }
}
