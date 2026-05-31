<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function show($id)
    {
        $attendance = AttendanceRecord::with(['user', 'breaks'])->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i'],
            'new_break.break_start' => ['nullable', 'date_format:H:i'],
            'new_break.break_end' => ['nullable', 'date_format:H:i'],
            'comment' => ['nullable', 'string', 'max:255'],
        ]);

        $attendance = AttendanceRecord::with('breaks')->findOrFail($id);

        DB::transaction(function () use ($request, $attendance) {
            $attendance->update([
                'clock_in' => $request->input('clock_in'),
                'clock_out' => $request->input('clock_out'),
                'comment' => $request->input('comment'),
            ]);

            foreach ($request->input('breaks', []) as $breakId => $breakInput) {
                $break = $attendance->breaks->firstWhere('id', (int) $breakId);

                if ($break) {
                    $break->break_start = $breakInput['break_start'] ?? null;
                    $break->break_end = $breakInput['break_end'] ?? null;
                    $break->save();
                }
            }

            if ($request->filled('new_break.break_start') || $request->filled('new_break.break_end')) {
                $attendance->breaks()->create([
                    'break_start' => $request->input('new_break.break_start'),
                    'break_end' => $request->input('new_break.break_end'),
                ]);
            }
        });

        return redirect()
            ->route('admin.attendance.show', $attendance->id)
            ->with('status', '勤怠情報を更新しました。');
    }
}
