<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalaryStructureRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('payroll.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'components' => ['nullable', 'array'],
            'components.*.enabled' => ['nullable', 'boolean'],
            'components.*.value' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'basic_salary.required' => _trans('common.Basic salary is required.'),
            'basic_salary.numeric' => _trans('common.Basic salary must be a valid number.'),
        ];
    }
}
