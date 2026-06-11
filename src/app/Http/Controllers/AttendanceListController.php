<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $targetMonth = $request->input('month')
            ? Carbon::parse($request->input('month'))->startOfMonth()
            : now()->startOfMonth();

        $startOfMonth = $targetMonth->copy()->startOfMonth();
        $endOfMonth = $targetMonth->copy()->endOfMonth();

        $attendanceRecords = AttendanceRecord::with('breaks')
            ->where('user_id', $user->id)
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

        return view('attendance.list', compact(
            'user',
            'targetMonth',
            'attendanceRows'
        ));
    }
}
