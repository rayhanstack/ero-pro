<?php

namespace App\Http\Requests\Task;

use App\Enums\TaskStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MoveTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TaskStatusEnum::class)],
            'position' => ['required', 'integer', 'min:0'],
            'order' => ['nullable', 'array'],
            'order.*' => ['integer', 'exists:tasks,id'],
        ];
    }
}
