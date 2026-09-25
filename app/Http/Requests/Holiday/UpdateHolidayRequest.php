<?php

namespace App\Http\Requests\Holiday;

use App\Enums\HolidayTypeEnum;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateHolidayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'type' => ['required', Rule::enum(HolidayTypeEnum::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(StatusEnum::class)],
        ];
    }
}
