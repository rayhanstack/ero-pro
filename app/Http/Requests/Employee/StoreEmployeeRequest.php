<?php

namespace App\Http\Requests\Employee;

use App\Enums\BloodGroupEnum;
use App\Enums\EmployeeStatusEnum;
use App\Enums\EmploymentTypeEnum;
use App\Enums\GenderEnum;
use App\Enums\MaritalStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('employee.create') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Account & Personal (Users Table)
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'time_zone' => ['nullable', 'string', 'max:50'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required_with:password'],

            // Status
            'status' => ['nullable', new Enum(EmployeeStatusEnum::class)],

            // Personal Detail (employee_details Table)
            'dob' => ['nullable', 'date', 'before:today'],
            'gender' => ['required', new Enum(GenderEnum::class)],
            'marital_status' => ['nullable', new Enum(MaritalStatusEnum::class)],
            'nid' => ['nullable', 'string', 'max:50'],
            'blood_group' => ['nullable', new Enum(BloodGroupEnum::class)],
            'country_id' => ['nullable', 'exists:countries,id'],
            'state_id' => ['nullable', 'exists:states,id'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'present_address' => ['nullable', 'string', 'max:500'],
            'permanent_address' => ['nullable', 'string', 'max:500'],

            // Job (employee_details Table)
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'joining_date' => ['nullable', 'date'],
            'confirmation_date' => ['nullable', 'date', 'after_or_equal:joining_date'],
            'employment_type' => ['nullable', new Enum(EmploymentTypeEnum::class)],

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

            // Initial Document
            'document_title' => ['nullable', 'string', 'max:150'],
            'document_file' => ['nullable', 'required_with:document_title', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'document_expiry_date' => ['nullable', 'date'],
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
            'first_name' => _trans('common.First Name'),
            'last_name' => _trans('common.Last Name'),
            'email' => _trans('common.Email Address'),
            'phone' => _trans('common.Phone Number'),
            'avatar' => _trans('common.Profile Photo'),
            'role' => _trans('common.Role'),
            'time_zone' => _trans('common.Timezone'),
            'password' => _trans('common.Password'),
            'password_confirmation' => _trans('common.Confirm Password'),
            'status' => _trans('common.Status'),
            'dob' => _trans('common.Date of Birth'),
            'gender' => _trans('common.Gender'),
            'marital_status' => _trans('common.Marital Status'),
            'nid' => _trans('common.National ID / Passport'),
            'blood_group' => _trans('common.Blood Group'),
            'country_id' => _trans('common.Country'),
            'state_id' => _trans('common.State'),
            'city_id' => _trans('common.City'),
            'present_address' => _trans('common.Present Address'),
            'permanent_address' => _trans('common.Permanent Address'),
            'department_id' => _trans('common.Department'),
            'designation_id' => _trans('common.Designation'),
            'shift_id' => _trans('common.Shift'),
            'manager_id' => _trans('common.Reporting Manager'),
            'joining_date' => _trans('common.Joining Date'),
            'confirmation_date' => _trans('common.Confirmation Date'),
            'employment_type' => _trans('common.Employment Type'),
            'basic_salary' => _trans('common.Basic Salary'),
            'bank' => _trans('common.Bank Name'),
            'branch' => _trans('common.Branch Name'),
            'account_name' => _trans('common.Account Holder Name'),
            'account_no' => _trans('common.Account Number'),
            'routing_number' => _trans('common.Routing Number'),
            'swift_code' => _trans('common.SWIFT Code'),
            'emergency_name' => _trans('common.Emergency Contact Name'),
            'emergency_relationship' => _trans('common.Emergency Relationship'),
            'emergency_phone' => _trans('common.Emergency Primary Phone'),
            'emergency_alt_phone' => _trans('common.Emergency Alternative Phone'),
            'emergency_address' => _trans('common.Emergency Contact Address'),
            'document_title' => _trans('common.Document Title'),
            'document_file' => _trans('common.Document File'),
            'document_expiry_date' => _trans('common.Document Expiry Date'),
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => _trans('common.Please enter the first name.'),
            'last_name.required' => _trans('common.Please enter the last name.'),
            'email.required' => _trans('common.Please enter a valid email address.'),
            'email.unique' => _trans('common.This email is already registered.'),
            'role.required' => _trans('common.Please select a role for this employee.'),
            'password.required' => _trans('common.Please enter a password.'),
            'password.confirmed' => _trans('common.The password confirmation does not match.'),
            'department_id.required' => _trans('common.Please select a department.'),
            'designation_id.required' => _trans('common.Please select a designation.'),
            'joining_date.required' => _trans('common.Please provide the joining date.'),
            'gender.required' => _trans('common.Please select a gender.'),
            'status.required' => _trans('common.Please select the employee status.'),
            'employment_type.required' => _trans('common.Please select an employment type.'),
        ];
    }
}
