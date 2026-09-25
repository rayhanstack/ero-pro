<?php

namespace App\Http\Requests\Shift;

use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i,H:i:s'],
            'end_time' => ['required', 'date_format:H:i,H:i:s'],
            'grace_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(StatusEnum::class)],
        ];
    }
}
