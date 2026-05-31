<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AttendanceDetailController extends Controller
{
    public function show($id)
    {
        $user = auth()->user();

        $attendanceRecord = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return view('attendance.detail', compact('user', 'attendanceRecord'));
    }
}
