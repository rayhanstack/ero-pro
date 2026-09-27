<?php

namespace App\Http\Requests\Meeting;

use App\Enums\MeetingAttendeeResponseEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RsvpMeetingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('meeting.view');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'response' => ['required', Rule::enum(MeetingAttendeeResponseEnum::class)],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'response' => _trans('common.Response Status'),
            'notes' => _trans('common.Notes / Remarks'),
        ];
    }
}
