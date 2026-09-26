<?php

namespace App\Http\Requests\Leave;

use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeaveTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('leave.manage') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $leaveTypeId = $this->route('leave_type')?->id ?? $this->route('leave_type');

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('leave_types', 'code')->ignore($leaveTypeId)],
            'days_per_year' => ['required', 'integer', 'min:0', 'max:365'],
            'is_paid' => ['nullable', 'boolean'],
            'carry_forward' => ['nullable', 'boolean'],
            'max_carry' => ['nullable', 'integer', 'min:0', 'max:365'],
            'status' => ['required', Rule::enum(StatusEnum::class)],
            'color' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }
}
