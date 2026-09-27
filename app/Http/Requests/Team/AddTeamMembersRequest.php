<?php

namespace App\Http\Requests\Team;

use Illuminate\Foundation\Http\FormRequest;

class AddTeamMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => ['required', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'member_ids.required' => _trans('common.Please select at least one team member.'),
            'member_ids.min' => _trans('common.Please select at least one team member.'),
            'member_ids.*.exists' => _trans('common.One or more selected members are invalid.'),
        ];
    }
}
