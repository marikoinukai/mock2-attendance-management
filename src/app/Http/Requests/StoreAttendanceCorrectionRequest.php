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
            'requested_clock_in' => [
                'required',
                'date_format:H:i',
            ],
            'requested_clock_out' => [
                'required',
                'date_format:H:i',
                'after:requested_clock_in',
            ],
            'requested_comment' => [
                'required',
                'string',
                'max:255',
            ],

            'requested_breaks' => [
                'nullable',
                'array',
            ],
            'requested_breaks.*.requested_break_start' => [
                'nullable',
                'required_with:requested_breaks.*.requested_break_end',
                'date_format:H:i',
                'after_or_equal:requested_clock_in',
                'before_or_equal:requested_clock_out',
            ],
            'requested_breaks.*.requested_break_end' => [
                'nullable',
                'required_with:requested_breaks.*.requested_break_start',
                'date_format:H:i',
                'after:requested_breaks.*.requested_break_start',
                'after_or_equal:requested_clock_in',
                'before_or_equal:requested_clock_out',
            ],

            'requested_new_break' => [
                'nullable',
                'array',
            ],
            'requested_new_break.requested_break_start' => [
                'nullable',
                'required_with:requested_new_break.requested_break_end',
                'date_format:H:i',
                'after_or_equal:requested_clock_in',
                'before_or_equal:requested_clock_out',
            ],
            'requested_new_break.requested_break_end' => [
                'nullable',
                'required_with:requested_new_break.requested_break_start',
                'date_format:H:i',
                'after:requested_new_break.requested_break_start',
                'after_or_equal:requested_clock_in',
                'before_or_equal:requested_clock_out',
            ],
        ];
    }
}
