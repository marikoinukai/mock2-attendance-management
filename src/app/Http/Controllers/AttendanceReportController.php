<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AttendanceReportController extends Controller
{
    /**
     * マイ勤怠レポート画面を表示する
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $startDate = Carbon::today()->subMonths(5)->startOfMonth();
        $endDate = Carbon::today()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $startDate->toDateString(),
                $endDate->toDateString(),
            ])
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->get();

        $workMinutesList = $attendanceRecords->map(function (AttendanceRecord $record): int {
            return $this->calculateWorkMinutes($record);
        });

        $totalWorkMinutes = $workMinutesList->sum();

        $totalOvertimeMinutes = $workMinutesList
            ->map(function (int $workMinutes): int {
                return max($workMinutes - 480, 0);
            })
            ->sum();

        $averageWorkMinutes = $attendanceRecords->count() > 0
            ? (int) round($totalWorkMinutes / $attendanceRecords->count())
            : 0;

        $monthlyTrend = $this->createMonthlyTrend($attendanceRecords, $startDate);

        $currentMonthStart = Carbon::today()->startOfMonth();
        $currentMonthEnd = Carbon::today()->endOfMonth();

        $currentMonthRecords = $attendanceRecords->filter(function (AttendanceRecord $record) use ($currentMonthStart, $currentMonthEnd): bool {
            $workDate = Carbon::parse($record->work_date);

            return $workDate->between($currentMonthStart, $currentMonthEnd);
        });

        $lateCount = $currentMonthRecords->filter(function (AttendanceRecord $record): bool {
            return Carbon::parse($record->clock_in)->gt(Carbon::parse('09:00'));
        })->count();

        $earlyLeaveCount = $currentMonthRecords->filter(function (AttendanceRecord $record): bool {
            return Carbon::parse($record->clock_out)->lt(Carbon::parse('18:00'));
        })->count();

        $longWorkDayCount = $currentMonthRecords->filter(function (AttendanceRecord $record): bool {
            return $this->calculateWorkMinutes($record) > 600;
        })->count();

        return view('attendance.report', [
            'totalWorkTime' => $this->formatMinutes($totalWorkMinutes),
            'totalOvertime' => $this->formatMinutes($totalOvertimeMinutes),
            'averageWorkTime' => $this->formatMinutes($averageWorkMinutes),
            'monthlyTrend' => $monthlyTrend,
            'lateCount' => $lateCount,
            'earlyLeaveCount' => $earlyLeaveCount,
            'longWorkDayCount' => $longWorkDayCount,
        ]);
    }

    /**
     * 過去6ヶ月分の月別集計を作成する
     *
     * @param Collection $attendanceRecords
     * @param Carbon $startDate
     * @return Collection
     */
    private function createMonthlyTrend(Collection $attendanceRecords, Carbon $startDate): Collection
    {
        $recordsByMonth = $attendanceRecords->groupBy(function (AttendanceRecord $record): string {
            return Carbon::parse($record->work_date)->format('Y-m');
        });

        $monthlyTrend = collect(range(0, 5))->map(function (int $monthOffset) use ($startDate, $recordsByMonth): array {
            $targetMonth = $startDate->copy()->addMonths($monthOffset);
            $monthKey = $targetMonth->format('Y-m');

            $monthlyRecords = $recordsByMonth->get($monthKey, collect());

            $workMinutesList = $monthlyRecords->map(function (AttendanceRecord $record): int {
                return $this->calculateWorkMinutes($record);
            });

            $totalWorkMinutes = $workMinutesList->sum();

            $totalOvertimeMinutes = $workMinutesList
                ->map(function (int $workMinutes): int {
                    return max($workMinutes - 480, 0);
                })
                ->sum();

            return [
                'month' => $targetMonth->format('Y-m'),
                'totalWorkMinutes' => $totalWorkMinutes,
                'totalOvertimeMinutes' => $totalOvertimeMinutes,
                'totalWorkTime' => $this->formatMinutes($totalWorkMinutes),
                'totalOvertime' => $this->formatMinutes($totalOvertimeMinutes),
            ];
        });

        $maxWorkMinutes = $monthlyTrend->max('totalWorkMinutes') ?: 1;

        return $monthlyTrend->map(function (array $monthlyData) use ($maxWorkMinutes): array {
            $monthlyData['workBarWidth'] = (int) round($monthlyData['totalWorkMinutes'] / $maxWorkMinutes * 100);

            $monthlyData['overtimeBarWidth'] = $monthlyData['totalOvertimeMinutes'] > 0
                ? max((int) round($monthlyData['totalOvertimeMinutes'] / $maxWorkMinutes * 100), 2)
                : 0;

            return $monthlyData;
        });
    }

    /**
     * 1日の実労働時間を分単位で計算する
     *
     * @param AttendanceRecord $record
     * @return int
     */
    private function calculateWorkMinutes(AttendanceRecord $record): int
    {
        $workDate = Carbon::parse($record->work_date)->format('Y-m-d');

        $clockIn = Carbon::parse($workDate . ' ' . $record->clock_in);
        $clockOut = Carbon::parse($workDate . ' ' . $record->clock_out);

        $breakMinutes = $record->breaks->sum(function ($break) use ($workDate): int {
            if (is_null($break->break_start) || is_null($break->break_end)) {
                return 0;
            }

            $breakStart = Carbon::parse($workDate . ' ' . $break->break_start);
            $breakEnd = Carbon::parse($workDate . ' ' . $break->break_end);

            return $breakStart->diffInMinutes($breakEnd);
        });

        return $clockIn->diffInMinutes($clockOut) - $breakMinutes;
    }

    /**
     * 分を「○h ○m」の形式に変換する
     *
     * @param int $minutes
     * @return string
     */
    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $hours . 'h ' . $remainingMinutes . 'm';
    }
}
