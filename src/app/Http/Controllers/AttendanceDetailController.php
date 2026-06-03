<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectionRequest;

class AttendanceDetailController extends Controller
{
    public function show($id)
    {
        $user = auth()->user();

        $attendanceRecord = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $pendingCorrectionRequest = AttendanceCorrectionRequest::where('attendance_record_id', $attendanceRecord->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        return view('attendance.detail', compact(
            'user',
            'attendanceRecord',
            'pendingCorrectionRequest'
        ));
    }
}
