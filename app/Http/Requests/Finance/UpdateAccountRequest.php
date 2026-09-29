<?php

namespace App\Http\Requests\Finance;

use App\Enums\AccountStatusEnum;
use App\Enums\AccountTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(AccountTypeEnum::class)],
            'account_number' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', new Enum(AccountStatusEnum::class)],
            'note' => ['nullable', 'string'],
        ];
    }
}
