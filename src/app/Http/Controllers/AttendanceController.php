<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->toDateString();

        $attendanceRecord = AttendanceRecord::where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        return view('attendance.index', compact('user', 'attendanceRecord'));
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
}
