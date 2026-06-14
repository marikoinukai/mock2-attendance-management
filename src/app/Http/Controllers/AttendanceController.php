<?php

namespace App\Http\Controllers;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentDateTime = now();
        $today = $currentDateTime->toDateString();

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

        return view('attendance.index', compact('user', 'attendanceRecord', 'currentBreak', 'currentDateTime'));
    }

    public function clockIn()
    {
        $user = auth()->user();
        $currentDateTime = now();
        $today = $currentDateTime->toDateString();

        AttendanceRecord::firstOrCreate(
            [
                'user_id' => $user->id,
                'work_date' => $today,
            ],
            [
                'clock_in' => $currentDateTime->format('H:i:s'),
            ]
        );

        return redirect()->route('attendance.index');
    }

    public function clockOut()
    {
        $user = auth()->user();
        $currentDateTime = now();
        $today = $currentDateTime->toDateString();

        $attendanceRecord = AttendanceRecord::where('user_id', $user->id)
            ->where('work_date', $today)
            ->whereNull('clock_out')
            ->first();

        if ($attendanceRecord) {
            $attendanceRecord->update([
                'clock_out' => $currentDateTime->format('H:i:s'),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    public function breakStart()
    {
        $user = auth()->user();
        $currentDateTime = now();
        $today = $currentDateTime->toDateString();

        $attendanceRecord = AttendanceRecord::where('user_id', $user->id)
            ->where('work_date', $today)
            ->whereNull('clock_out')
            ->first();

        if ($attendanceRecord) {
            AttendanceBreak::create([
                'attendance_record_id' => $attendanceRecord->id,
                'break_start' => $currentDateTime->format('H:i:s'),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    public function breakEnd()
    {
        $user = auth()->user();
        $currentDateTime = now();
        $today = $currentDateTime->toDateString();

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
                    'break_end' => $currentDateTime->format('H:i:s'),
                ]);
            }
        }

        return redirect()->route('attendance.index');
    }
}
