<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class AddProjectMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['integer', 'exists:users,id'],
            'members' => ['nullable', 'array'],
            'members.*' => ['integer', 'exists:users,id'],
            'role' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_ids.required' => _trans('common.Please select at least one employee.'),
            'employee_ids.min' => _trans('common.Please select at least one employee.'),
        ];
    }
}
