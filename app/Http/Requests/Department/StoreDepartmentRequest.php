<?php

namespace App\Http\Requests\Department;

use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50', 'unique:departments,code'],
            'head_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(StatusEnum::class)],
        ];
    }
}
