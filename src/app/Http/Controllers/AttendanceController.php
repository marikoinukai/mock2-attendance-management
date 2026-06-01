<?php

namespace App\Http\Controllers;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $attendanceRecord = AttendanceRecord::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        $currentBreak = null;

        if ($attendanceRecord) {
            $currentBreak = $attendanceRecord->breaks()
                ->whereNull('break_end')
                ->latest()
                ->first();
        }

        return view('attendance.index', compact('user', 'attendanceRecord', 'currentBreak'));
    }

    public function clockIn()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        AttendanceRecord::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $today,
            ],
            [
                'clock_in' => now()->format('H:i:s'),
            ]
        );

        return redirect()->route('attendance.index');
    }

    public function clockOut()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $attendanceRecord = AttendanceRecord::where('user_id', $user->id)
            ->where('work_date', $today)
            ->whereNull('clock_out')
            ->first();

        if ($attendanceRecord) {
            $attendanceRecord->update([
                'clock_out' => now()->format('H:i:s'),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    public function breakStart()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $attendanceRecord = AttendanceRecord::where('user_id', $user->id)
            ->where('work_date', $today)
            ->whereNull('clock_out')
            ->first();

        if ($attendanceRecord) {
            AttendanceBreak::create([
                'attendance_record_id' => $attendanceRecord->id,
                'break_start' => now()->format('H:i:s'),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    public function breakEnd()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $attendanceRecord = AttendanceRecord::where('user_id', $user->id)
            ->where('work_date', $today)
            ->whereNull('clock_out')
            ->first();

        if ($attendanceRecord) {
            $currentBreak = $attendanceRecord->breaks()
                ->whereNull('break_end')
                ->latest()
                ->first();

            if ($currentBreak) {
                $currentBreak->update([
                    'break_end' => now()->format('H:i:s'),
                ]);
            }
        }

        return redirect()->route('attendance.index');
    }
}
