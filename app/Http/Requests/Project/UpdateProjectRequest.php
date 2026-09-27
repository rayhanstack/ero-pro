<?php

namespace App\Http\Requests\Project;

use App\Enums\ProjectPriorityEnum;
use App\Enums\ProjectStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'client_id' => ['nullable', 'integer', 'exists:clients,id'],
            'description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'currency_id' => ['nullable', 'integer', 'exists:currencies,id'],
            'priority' => ['required', Rule::enum(ProjectPriorityEnum::class)],
            'status' => ['required', Rule::enum(ProjectStatusEnum::class)],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'manager_id' => ['nullable', 'integer', 'exists:users,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'members' => ['nullable', 'array'],
            'members.*' => ['integer', 'exists:users,id'],
            'team_ids' => ['nullable', 'array'],
            'team_ids.*' => ['integer', 'exists:teams,id'],
            'teams' => ['nullable', 'array'],
            'teams.*' => ['integer', 'exists:teams,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => _trans('common.Project name is required.'),
            'deadline.after_or_equal' => _trans('common.The deadline must be a date on or after the start date.'),
            'priority.required' => _trans('common.Priority is required.'),
            'status.required' => _trans('common.Status is required.'),
        ];
    }
}
