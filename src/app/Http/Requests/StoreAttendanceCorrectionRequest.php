<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'requested_clock_in' => ['required', 'date_format:H:i'],
            'requested_clock_out' => ['required', 'date_format:H:i', 'after:requested_clock_in'],
            'requested_comment' => ['required', 'string', 'max:255'],

            'requested_breaks' => ['nullable', 'array'],
            'requested_breaks.*.requested_break_start' => ['nullable', 'date_format:H:i'],
            'requested_breaks.*.requested_break_end' => ['nullable', 'date_format:H:i'],
        ];
    }
}
