<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
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
            ? Carbon::parse($request->input('month') . '-01')
            : now();

        $attendanceRecords = $staff->attendanceRecords()
            ->with('breaks')
            ->whereBetween('work_date', [
                $targetMonth->copy()->startOfMonth()->toDateString(),
                $targetMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->orderBy('work_date')
            ->get();

        return view('admin.staff.attendance', compact(
            'staff',
            'targetMonth',
            'attendanceRecords'
        ));
    }

    public function csv(Request $request, $id)
    {
        $staff = User::where('is_admin', false)->findOrFail($id);

        $targetMonth = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : now();

        $attendanceRecords = $staff->attendanceRecords()
            ->with('breaks')
            ->whereBetween('work_date', [
                $targetMonth->copy()->startOfMonth()->toDateString(),
                $targetMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->orderBy('work_date')
            ->get();

        $fileName = $staff->name . '_' . $targetMonth->format('Y-m') . '_attendance.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($attendanceRecords) {
            $file = fopen('php://output', 'w');

            // Excelで文字化けしにくくするためのBOM
            echo "\xEF\xBB\xBF";

            fputcsv($file, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計',
            ]);

            foreach ($attendanceRecords as $record) {
                $breakMinutes = 0;
                $workMinutes = null;
                $workDate = $record->work_date->format('Y-m-d');

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

                fputcsv($file, [
                    $record->work_date->format('Y/m/d'),
                    $record->clock_in,
                    $record->clock_out,
                    $breakMinutes > 0 ? floor($breakMinutes / 60) . ':' . sprintf('%02d', $breakMinutes % 60) : '',
                    !is_null($workMinutes) ? floor($workMinutes / 60) . ':' . sprintf('%02d', $workMinutes % 60) : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
