<?php

namespace App\Http\Requests\Project;

use App\Enums\MilestoneStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', Rule::enum(MilestoneStatusEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => _trans('common.Milestone title is required.'),
        ];
    }
}
