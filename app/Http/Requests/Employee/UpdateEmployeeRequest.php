<?php

namespace App\Http\Requests\Employee;

use App\Enums\BloodGroupEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('employee.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = $this->route('employee');
        $employeeId = is_object($employee) ? $employee->id : $employee;

        return [
            // Personal
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('employees', 'email')->ignore($employeeId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'dob' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', new Enum(GenderEnum::class)],
            'marital_status' => ['nullable', new Enum(MaritalStatusEnum::class)],
            'nid' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', new Enum(BloodGroupEnum::class)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'present_address' => ['nullable', 'string', 'max:500'],
            'permanent_address' => ['nullable', 'string', 'max:500'],

            // Job
            'department_id' => ['required', 'exists:departments,id'],
            'designation_id' => ['required', 'exists:designations,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'manager_id' => ['nullable', 'exists:employees,id', Rule::notIn([$employeeId])],
            'joining_date' => ['required', 'date'],
            'confirmation_date' => ['nullable', 'date', 'after_or_equal:joining_date'],
            'employment_type' => ['required', new Enum(EmploymentTypeEnum::class)],
            'status' => ['required', new Enum(EmployeeStatusEnum::class)],

            // Salary
            'basic_salary' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],

            // Bank Information
            'bank' => ['nullable', 'string', 'max:150'],
            'branch' => ['nullable', 'string', 'max:150'],
            'account_name' => ['nullable', 'string', 'max:150'],
            'account_no' => ['nullable', 'required_with:bank', 'string', 'max:50'],
            'routing_number' => ['nullable', 'string', 'max:50'],
            'swift_code' => ['nullable', 'string', 'max:50'],

            // Emergency Contact
            'emergency_name' => ['nullable', 'string', 'max:150'],
            'emergency_relationship' => ['nullable', 'string', 'max:50'],
            'emergency_phone' => ['nullable', 'required_with:emergency_name', 'string', 'max:30'],
            'emergency_alt_phone' => ['nullable', 'string', 'max:30'],
            'emergency_address' => ['nullable', 'string', 'max:500'],
        ];
    }
}
