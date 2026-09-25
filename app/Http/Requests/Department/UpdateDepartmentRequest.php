<?php

namespace App\Http\Requests\Department;

use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $departmentId = $this->route('department')?->id ?? $this->route('department');

        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('departments', 'code')->ignore($departmentId),
            ],
            'head_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(StatusEnum::class)],
        ];
    }
}
