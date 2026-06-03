<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
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
            'clock_in' => [
                'required',
                'date_format:H:i',
            ],
            'clock_out' => [
                'required',
                'date_format:H:i',
                'after:clock_in',
            ],
            'comment' => [
                'required',
                'string',
                'max:255',
            ],

            'breaks' => [
                'nullable',
                'array',
            ],
            'breaks.*.break_start' => [
                'nullable',
                'required_with:breaks.*.break_end',
                'date_format:H:i',
                'after_or_equal:clock_in',
                'before_or_equal:clock_out',
            ],
            'breaks.*.break_end' => [
                'nullable',
                'required_with:breaks.*.break_start',
                'date_format:H:i',
                'after:breaks.*.break_start',
                'after_or_equal:clock_in',
                'before_or_equal:clock_out',
            ],

            'new_break' => [
                'nullable',
                'array',
            ],
            'new_break.break_start' => [
                'nullable',
                'required_with:new_break.break_end',
                'date_format:H:i',
                'after_or_equal:clock_in',
                'before_or_equal:clock_out',
            ],
            'new_break.break_end' => [
                'nullable',
                'required_with:new_break.break_start',
                'date_format:H:i',
                'after:new_break.break_start',
                'after_or_equal:clock_in',
                'before_or_equal:clock_out',
            ],
        ];
    }
}
