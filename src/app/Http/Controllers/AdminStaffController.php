<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function index()
    {
        $staffUsers = User::where('is_admin', false)
            ->orderBy('id')
            ->get();

        return view('admin.staff.list', compact('staffUsers'));
    }

    public function attendance(Request $request, $id)
    {
        $staff = User::where('is_admin', false)->findOrFail($id);

        $targetMonth = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')->startOfMonth()
            : now()->startOfMonth();

        $attendanceRows = $this->makeAttendanceRows($staff, $targetMonth);

        return view('admin.staff.attendance', compact(
            'staff',
            'targetMonth',
            'attendanceRows'
        ));
    }

    public function csv(Request $request, $id)
    {
        $staff = User::where('is_admin', false)->findOrFail($id);

        $targetMonth = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')->startOfMonth()
            : now()->startOfMonth();

        $attendanceRows = $this->makeAttendanceRows($staff, $targetMonth);

        $fileName = $staff->name . '_' . $targetMonth->format('Y-m') . '_attendance.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($attendanceRows) {
            $file = fopen('php://output', 'w');

            echo "\xEF\xBB\xBF";

            fputcsv($file, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計',
            ]);

            foreach ($attendanceRows as $row) {
                $date = $row['date'];
                $record = $row['record'];

                $breakMinutes = 0;
                $workMinutes = null;
                $workDate = $date->format('Y-m-d');

                if ($record) {
                    foreach ($record->breaks as $break) {
                        if ($break->break_start && $break->break_end) {
                            $breakStart = Carbon::parse($workDate . ' ' . $break->break_start);
                            $breakEnd = Carbon::parse($workDate . ' ' . $break->break_end);
                            $breakMinutes += $breakStart->diffInMinutes($breakEnd);
                        }
                    }

                    if ($record->clock_in && $record->clock_out) {
                        $clockIn = Carbon::parse($workDate . ' ' . $record->clock_in);
                        $clockOut = Carbon::parse($workDate . ' ' . $record->clock_out);
                        $workMinutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
                    }
                }

                fputcsv($file, [
                    $date->format('Y/m/d'),
                    $record && $record->clock_in ? Carbon::parse($record->clock_in)->format('H:i') : '',
                    $record && $record->clock_out ? Carbon::parse($record->clock_out)->format('H:i') : '',
                    $breakMinutes > 0 ? floor($breakMinutes / 60) . ':' . sprintf('%02d', $breakMinutes % 60) : '',
                    !is_null($workMinutes) ? floor($workMinutes / 60) . ':' . sprintf('%02d', $workMinutes % 60) : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function makeAttendanceRows(User $staff, Carbon $targetMonth)
    {
        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();

        $attendanceRecords = $staff->attendanceRecords()
            ->with('breaks')
            ->whereBetween('work_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($record) {
                return $record->work_date->format('Y-m-d');
            });

        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        $attendanceRows = [];

        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');

            $attendanceRows[] = [
                'date' => $date->copy(),
                'record' => $attendanceRecords->get($dateKey),
            ];
        }

        return $attendanceRows;
    }
}
