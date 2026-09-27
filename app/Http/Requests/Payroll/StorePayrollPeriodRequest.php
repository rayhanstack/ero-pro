<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePayrollPeriodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('payroll.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'month' => [
                'required',
                'integer',
                'between:1,12',
                Rule::unique('payroll_periods')->where(function ($query) {
                    return $query->where('year', $this->year)->whereNull('deleted_at');
                }),
            ],
            'year' => ['required', 'integer', 'between:2020,2099'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'month.required' => _trans('common.Payroll month is required.'),
            'month.unique' => _trans('common.A payroll period already exists for the selected month and year.'),
            'year.required' => _trans('common.Payroll year is required.'),
        ];
    }
}
