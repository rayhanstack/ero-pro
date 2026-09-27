<?php

namespace App\Http\Requests\Team;

use App\Enums\TeamStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'unique:teams,name'],
            'lead_id' => ['nullable', 'integer', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::enum(TeamStatusEnum::class)],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => _trans('common.Team name is required.'),
            'name.unique' => _trans('common.A team with this name already exists.'),
            'lead_id.exists' => _trans('common.Selected team lead is invalid.'),
            'status.required' => _trans('common.Status is required.'),
            'member_ids.*.exists' => _trans('common.One or more selected members are invalid.'),
        ];
    }
}
