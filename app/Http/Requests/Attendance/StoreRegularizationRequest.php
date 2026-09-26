<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegularizationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('attendance.view') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date', 'before_or_equal:today'],
            'requested_in' => ['nullable', 'date_format:H:i'],
            'requested_out' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'date' => _trans('common.Date'),
            'requested_in' => _trans('common.Requested Check-In'),
            'requested_out' => _trans('common.Requested Check-Out'),
            'reason' => _trans('common.Reason for Regularization'),
        ];
    }
}
