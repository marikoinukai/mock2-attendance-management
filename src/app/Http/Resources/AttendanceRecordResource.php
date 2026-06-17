<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceRecordResource extends JsonResource
{
    /**
     * リソースを配列に変換する
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $isDetail = $this->relationLoaded('correctionRequests');

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->when(
                $this->relationLoaded('user'),
                fn () => $this->user->name
            ),
            'user' => $this->when(
                $isDetail && $this->relationLoaded('user'),
                fn () => [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ]
            ),
            'date' => $this->formatDate($this->work_date),
            'clock_in' => $this->formatTime($this->clock_in),
            'clock_out' => $this->formatTime($this->clock_out),
            'total_time' => $this->formatMinutes($this->calculateWorkMinutes()),
            'total_break_time' => $this->formatMinutes($this->calculateBreakMinutes()),
            'comment' => $this->comment,
            'breaks' => $this->when(
                $isDetail && $this->relationLoaded('breaks'),
                fn () => $this->breaks->map(function ($break): array {
                    return [
                        'id' => $break->id,
                        'break_in' => $this->formatTime($break->break_start),
                        'break_out' => $this->formatTime($break->break_end),
                    ];
                })
            ),
            'applications' => $this->when(
                $isDetail && $this->relationLoaded('correctionRequests'),
                fn () => $this->correctionRequests->map(function ($request): array {
                    return [
                        'id' => $request->id,
                        'user_id' => $request->user_id,
                        'status' => $request->status,
                        'requested_clock_in' => $this->formatTime($request->requested_clock_in),
                        'requested_clock_out' => $this->formatTime($request->requested_clock_out),
                        'requested_comment' => $request->requested_comment,
                    ];
                })
            ),
        ];
    }

    /**
     * 日付を YYYY-MM-DD 形式に整える
     *
     * @param mixed $date
     * @return string|null
     */
    private function formatDate($date): ?string
    {
        if (is_null($date)) {
            return null;
        }

        return Carbon::parse($date)->format('Y-m-d');
    }

    /**
     * 時刻を HH:MM:SS 形式に整える
     *
     * @param mixed $time
     * @return string|null
     */
    private function formatTime($time): ?string
    {
        if (is_null($time)) {
            return null;
        }

        return Carbon::parse($time)->format('H:i:s');
    }

    /**
     * 実労働時間を分単位で計算する
     *
     * @return int|null
     */
    private function calculateWorkMinutes(): ?int
    {
        if (is_null($this->clock_in) || is_null($this->clock_out)) {
            return null;
        }

        $clockIn = Carbon::parse($this->clock_in);
        $clockOut = Carbon::parse($this->clock_out);

        return $clockIn->diffInMinutes($clockOut) - $this->calculateBreakMinutes();
    }

    /**
     * 休憩時間を分単位で計算する
     *
     * @return int
     */
    private function calculateBreakMinutes(): int
    {
        if (! $this->relationLoaded('breaks')) {
            return 0;
        }

        return $this->breaks->sum(function ($break): int {
            if (is_null($break->break_start) || is_null($break->break_end)) {
                return 0;
            }

            $breakStart = Carbon::parse($break->break_start);
            $breakEnd = Carbon::parse($break->break_end);

            return $breakStart->diffInMinutes($breakEnd);
        });
    }

    /**
     * 分を HH:MM 形式に整える
     *
     * @param int|null $minutes
     * @return string|null
     */
    private function formatMinutes(?int $minutes): ?string
    {
        if (is_null($minutes)) {
            return null;
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $remainingMinutes);
    }
}