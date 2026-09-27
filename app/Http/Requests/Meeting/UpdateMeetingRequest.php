<?php

namespace App\Http\Requests\Meeting;

use App\Enums\MeetingStatusEnum;
use App\Enums\MeetingTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMeetingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('meeting.edit');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'agenda' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string', 'after:start_time'],
            'location' => ['nullable', 'string', 'max:255'],
            'meeting_link' => ['nullable', 'string', 'max:500'],
            'type' => ['required', Rule::enum(MeetingTypeEnum::class)],
            'organizer_id' => ['nullable', 'exists:users,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'status' => ['required', Rule::enum(MeetingStatusEnum::class)],
            'employee_attendees' => ['nullable', 'array'],
            'employee_attendees.*' => ['exists:users,id'],
            'client_attendees' => ['nullable', 'array'],
            'client_attendees.*' => ['exists:clients,id'],
            'ignore_conflicts' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => _trans('common.Meeting Title'),
            'date' => _trans('common.Date'),
            'start_time' => _trans('common.Start Time'),
            'end_time' => _trans('common.End Time'),
            'location' => _trans('common.Location / Room'),
            'meeting_link' => _trans('common.Meeting Link'),
            'type' => _trans('common.Meeting Type'),
            'organizer_id' => _trans('common.Organizer'),
            'project_id' => _trans('common.Project'),
            'status' => _trans('common.Status'),
            'employee_attendees' => _trans('common.Employee Attendees'),
            'client_attendees' => _trans('common.Client Attendees'),
        ];
    }
}
