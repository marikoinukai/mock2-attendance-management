<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceCorrectionRequest;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AttendanceCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return app(AdminCorrectionRequestController::class)->index($request);
        }

        $status = $request->input('status', 'pending');

        $correctionRequests = AttendanceCorrectionRequest::with([
            'attendanceRecord',
            'correctionBreaks',
        ])
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->latest()
            ->get();

        return view('attendance_correction_requests.index', compact(
            'user',
            'correctionRequests',
            'status'
        ));
    }

    public function store(StoreAttendanceCorrectionRequest $request, $id)
    {
        $attendanceRecord = AttendanceRecord::with('breaks')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if ($attendanceRecord->correctionRequests()
            ->where('status', 'pending')
            ->exists()
        ) {
            return redirect()
                ->route('attendance.detail', $attendanceRecord->id)
                ->with('status', '承認待ちのため修正はできません。');
        }

        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendanceRecord->id,
            'user_id' => auth()->id(),
            'requested_clock_in' => $request->input('requested_clock_in'),
            'requested_clock_out' => $request->input('requested_clock_out'),
            'requested_comment' => $request->input('requested_comment'),
            'status' => 'pending',
        ]);

        foreach ($request->input('requested_breaks', []) as $breakInput) {
            if (!empty($breakInput['requested_break_start']) && !empty($breakInput['requested_break_end'])) {
                $correctionRequest->correctionBreaks()->create([
                    'requested_break_start' => $breakInput['requested_break_start'],
                    'requested_break_end' => $breakInput['requested_break_end'],
                ]);
            }
        }

        $newBreak = $request->input('requested_new_break');

        if (
            !empty($newBreak['requested_break_start']) &&
            !empty($newBreak['requested_break_end'])
        ) {
            $correctionRequest->correctionBreaks()->create([
                'requested_break_start' => $newBreak['requested_break_start'],
                'requested_break_end' => $newBreak['requested_break_end'],
            ]);
        }

        return redirect()
            ->route('attendance.detail', $attendanceRecord->id)
            ->with('status', '修正申請を送信しました。');
    }
}
