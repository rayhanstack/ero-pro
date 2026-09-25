<?php

namespace App\Http\Requests\Setting;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeaveSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'annual_leave_quota' => ['required', 'numeric', 'min:0'],
            'sick_leave_quota' => ['required', 'numeric', 'min:0'],
            'casual_leave_quota' => ['required', 'numeric', 'min:0'],
            'leave_approval_level' => ['nullable', 'string'],
        ];
    }
}
