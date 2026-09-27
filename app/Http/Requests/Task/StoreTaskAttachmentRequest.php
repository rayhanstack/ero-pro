<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:20480'], // max 20MB
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => _trans('common.Please choose a file to upload.'),
            'file.max' => _trans('common.The file size cannot exceed 20MB.'),
        ];
    }
}
