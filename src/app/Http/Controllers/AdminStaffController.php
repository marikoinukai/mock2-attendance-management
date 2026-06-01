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
}
