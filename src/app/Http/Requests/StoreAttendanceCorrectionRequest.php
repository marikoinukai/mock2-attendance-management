<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'requested_clock_in' => ['required', 'date_format:H:i'],
            'requested_clock_out' => ['required', 'date_format:H:i'],

            'requested_breaks' => ['nullable', 'array'],
            'requested_breaks.*.requested_break_start' => ['nullable', 'date_format:H:i'],
            'requested_breaks.*.requested_break_end' => ['nullable', 'date_format:H:i'],

            'requested_new_break' => ['nullable', 'array'],
            'requested_new_break.requested_break_start' => ['nullable', 'date_format:H:i'],
            'requested_new_break.requested_break_end' => ['nullable', 'date_format:H:i'],

            'requested_comment' => ['required', 'string'],
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

            $clockIn = $toMinutes($this->input('requested_clock_in'));
            $clockOut = $toMinutes($this->input('requested_clock_out'));

            if (is_null($clockIn) || is_null($clockOut)) {
                return;
            }

            if ($clockIn > $clockOut) {
                $validator->errors()->add(
                    'requested_clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            $checkBreak = function ($breakStart, $breakEnd, $breakStartKey, $breakEndKey) use ($validator, $toMinutes, $clockIn, $clockOut) {
                $hasBreakStart = !is_null($breakStart) && $breakStart !== '';
                $hasBreakEnd = !is_null($breakEnd) && $breakEnd !== '';

                if (!$hasBreakStart && !$hasBreakEnd) {
                    return;
                }

                if ($hasBreakStart && !$hasBreakEnd) {
                    $validator->errors()->add(
                        $breakEndKey,
                        '休憩開始時刻と休憩終了時刻はセットで入力してください'
                    );

                    return;
                }

                if (!$hasBreakStart && $hasBreakEnd) {
                    $validator->errors()->add(
                        $breakStartKey,
                        '休憩開始時刻と休憩終了時刻はセットで入力してください'
                    );

                    return;
                }

                $breakStartMinutes = $toMinutes($breakStart);
                $breakEndMinutes = $toMinutes($breakEnd);

                if (is_null($breakStartMinutes) || is_null($breakEndMinutes)) {
                    return;
                }

                if ($breakStartMinutes < $clockIn || $breakStartMinutes > $clockOut) {
                    $validator->errors()->add(
                        $breakStartKey,
                        '休憩時間が不適切な値です'
                    );
                }

                if ($breakEndMinutes < $breakStartMinutes || $breakEndMinutes > $clockOut) {
                    $validator->errors()->add(
                        $breakEndKey,
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            };

            foreach ($this->input('requested_breaks', []) as $index => $break) {
                $checkBreak(
                    $break['requested_break_start'] ?? null,
                    $break['requested_break_end'] ?? null,
                    "requested_breaks.{$index}.requested_break_start",
                    "requested_breaks.{$index}.requested_break_end"
                );
            }

            $newBreak = $this->input('requested_new_break', []);

            $checkBreak(
                $newBreak['requested_break_start'] ?? null,
                $newBreak['requested_break_end'] ?? null,
                'requested_new_break.requested_break_start',
                'requested_new_break.requested_break_end'
            );
        });
    }

    public function messages()
    {
        return [
            'requested_clock_in.required' => '出勤時間を入力してください',
            'requested_clock_in.date_format' => '出勤時間はHH:MM形式で入力してください',

            'requested_clock_out.required' => '退勤時間を入力してください',
            'requested_clock_out.date_format' => '退勤時間はHH:MM形式で入力してください',

            'requested_breaks.*.requested_break_start.date_format' => '休憩開始時刻はHH:MM形式で入力してください',
            'requested_breaks.*.requested_break_end.date_format' => '休憩終了時刻はHH:MM形式で入力してください',

            'requested_new_break.requested_break_start.date_format' => '休憩開始時刻はHH:MM形式で入力してください',
            'requested_new_break.requested_break_end.date_format' => '休憩終了時刻はHH:MM形式で入力してください',

            'requested_comment.required' => '備考を記入してください',
        ];
    }
}
