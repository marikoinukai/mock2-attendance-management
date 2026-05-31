<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $targetDate = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : now();

        $staffUsers = User::with([
            'attendanceRecords' => function ($query) use ($targetDate) {
                $query->with('breaks')
                    ->where('work_date', $targetDate->toDateString());
            },
        ])
            ->where('is_admin', false)
            ->orderBy('id')
            ->get();

        return view('admin.attendance.list', compact('targetDate', 'staffUsers'));
    }
}
