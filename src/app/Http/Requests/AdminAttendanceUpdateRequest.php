<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],

            'breaks' => ['nullable', 'array'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i'],

            'new_break' => ['nullable', 'array'],
            'new_break.break_start' => ['nullable', 'date_format:H:i'],
            'new_break.break_end' => ['nullable', 'date_format:H:i'],

            'comment' => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $toMinutes = function ($time) {
                if (is_null($time) || $time === '') {
                    return null;
                }

                if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
                    return null;
                }

                [$hour, $minute] = explode(':', $time);

                return ((int) $hour * 60) + (int) $minute;
            };

            $clockIn = $toMinutes($this->input('clock_in'));
            $clockOut = $toMinutes($this->input('clock_out'));

            if (is_null($clockIn) || is_null($clockOut)) {
                return;
            }

            if ($clockIn > $clockOut) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            $checkBreak = function ($breakStart, $breakEnd, $breakStartKey, $breakEndKey) use ($validator, $toMinutes, $clockIn, $clockOut) {
                $breakStartMinutes = $toMinutes($breakStart);
                $breakEndMinutes = $toMinutes($breakEnd);

                if (!is_null($breakStartMinutes)) {
                    if ($breakStartMinutes < $clockIn || $breakStartMinutes > $clockOut) {
                        $validator->errors()->add(
                            $breakStartKey,
                            '休憩時間が不適切な値です'
                        );
                    }
                }

                if (!is_null($breakEndMinutes)) {
                    if ($breakEndMinutes > $clockOut) {
                        $validator->errors()->add(
                            $breakEndKey,
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            };

            foreach ($this->input('breaks', []) as $breakId => $break) {
                $checkBreak(
                    $break['break_start'] ?? null,
                    $break['break_end'] ?? null,
                    "breaks.{$breakId}.break_start",
                    "breaks.{$breakId}.break_end"
                );
            }

            $newBreak = $this->input('new_break', []);

            $checkBreak(
                $newBreak['break_start'] ?? null,
                $newBreak['break_end'] ?? null,
                'new_break.break_start',
                'new_break.break_end'
            );
        });
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください。',
            'clock_in.date_format' => '出勤時間はHH:MM形式で入力してください。',

            'clock_out.required' => '退勤時間を入力してください。',
            'clock_out.date_format' => '退勤時間はHH:MM形式で入力してください。',

            'breaks.*.break_start.date_format' => '休憩開始時刻はHH:MM形式で入力してください。',
            'breaks.*.break_end.date_format' => '休憩終了時刻はHH:MM形式で入力してください。',

            'new_break.break_start.date_format' => '休憩開始時刻はHH:MM形式で入力してください。',
            'new_break.break_end.date_format' => '休憩終了時刻はHH:MM形式で入力してください。',

            'comment.required' => '備考を記入してください',
        ];
    }
}
