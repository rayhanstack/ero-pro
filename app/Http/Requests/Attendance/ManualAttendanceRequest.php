<?php

namespace App\Http\Requests\Attendance;

use App\Enums\AttendanceSourceEnum;
use App\Enums\AttendanceStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ManualAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('attendance.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date'],
            'status' => ['required', new Enum(AttendanceStatusEnum::class)],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'late_minutes' => ['nullable', 'integer', 'min:0'],
            'overtime_minutes' => ['nullable', 'integer', 'min:0'],
            'work_minutes' => ['nullable', 'integer', 'min:0'],
            'source' => ['nullable', new Enum(AttendanceSourceEnum::class)],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'employee_id' => _trans('common.Employee'),
            'date' => _trans('common.Date'),
            'status' => _trans('common.Status'),
            'check_in' => _trans('common.Check In Time'),
            'check_out' => _trans('common.Check Out Time'),
            'note' => _trans('common.Note'),
        ];
    }
}
