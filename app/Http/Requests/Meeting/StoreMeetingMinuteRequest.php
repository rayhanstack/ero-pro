<?php

namespace App\Http\Requests\Meeting;

use Illuminate\Foundation\Http\FormRequest;

class StoreMeetingMinuteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('meeting.edit') || $this->user()->can('meeting.record_minutes');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'discussion' => ['required', 'string'],
            'decisions' => ['nullable', 'string'],
            'action_items' => ['nullable', 'array'],
            'action_items.*.task' => ['required_with:action_items', 'string', 'max:255'],
            'action_items.*.assignee' => ['nullable', 'string', 'max:255'],
            'action_items.*.due_date' => ['nullable', 'date'],
            'mark_completed' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'discussion' => _trans('common.Discussion Summary'),
            'decisions' => _trans('common.Decisions Made'),
            'action_items' => _trans('common.Action Items'),
        ];
    }
}
