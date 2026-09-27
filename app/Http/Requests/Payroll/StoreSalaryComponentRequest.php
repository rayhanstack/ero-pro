<?php

namespace App\Http\Requests\Payroll;

use App\Enums\SalaryComponentCalcTypeEnum;
use App\Enums\SalaryComponentStatusEnum;
use App\Enums\SalaryComponentTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreSalaryComponentRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', new Enum(SalaryComponentTypeEnum::class)],
            'calc_type' => ['required', new Enum(SalaryComponentCalcTypeEnum::class)],
            'value' => ['required', 'numeric', 'min:0'],
            'is_taxable' => ['nullable', 'boolean'],
            'status' => ['required', new Enum(SalaryComponentStatusEnum::class)],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_taxable' => $this->boolean('is_taxable'),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => _trans('common.Component name is required.'),
            'value.required' => _trans('common.Component default value or rate is required.'),
            'value.numeric' => _trans('common.Value must be a valid number.'),
        ];
    }
}
