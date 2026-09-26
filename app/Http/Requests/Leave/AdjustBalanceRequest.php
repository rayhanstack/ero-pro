<?php

namespace App\Http\Requests\Leave;

use Illuminate\Foundation\Http\FormRequest;

class AdjustBalanceRequest extends FormRequest
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
        return [
            'employee_id' => ['required', 'exists:users,id'],
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'year' => ['required', 'integer', 'min:2020', 'max:2099'],
            'allocated' => ['required', 'numeric', 'min:0', 'max:365'],
            'carried' => ['required', 'numeric', 'min:0', 'max:365'],
            'used' => ['required', 'numeric', 'min:0', 'max:365'],
        ];
    }
}
