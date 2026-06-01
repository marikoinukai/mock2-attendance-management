<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCorrectionRequestController extends Controller
{
    public function index()
    {
        $correctionRequests = AttendanceCorrectionRequest::with([
            'user',
            'attendanceRecord',
            'correctionBreaks',
        ])
            ->latest()
            ->get();

        return view('admin.stamp_correction_request.list', compact('correctionRequests'));
    }

    public function show($id)
    {
        $correctionRequest = AttendanceCorrectionRequest::with([
            'user',
            'attendanceRecord.breaks',
            'correctionBreaks',
        ])->findOrFail($id);

        return view('admin.stamp_correction_request.approve', compact('correctionRequest'));
    }

    public function approve($id)
    {
        $correctionRequest = AttendanceCorrectionRequest::with([
            'attendanceRecord.breaks',
            'correctionBreaks',
        ])->findOrFail($id);

        if ($correctionRequest->status === 'approved') {
            return redirect()
                ->route('admin.stamp_correction_request.show', $correctionRequest->id)
                ->with('status', 'この申請はすでに承認済みです。');
        }

        DB::transaction(function () use ($correctionRequest) {
            $attendanceRecord = $correctionRequest->attendanceRecord;

            $attendanceRecord->update([
                'clock_in' => $correctionRequest->requested_clock_in,
                'clock_out' => $correctionRequest->requested_clock_out,
                'comment' => $correctionRequest->requested_comment,
            ]);

            $attendanceRecord->breaks()->delete();

            foreach ($correctionRequest->correctionBreaks as $correctionBreak) {
                $attendanceRecord->breaks()->create([
                    'break_start' => $correctionBreak->requested_break_start,
                    'break_end' => $correctionBreak->requested_break_end,
                ]);
            }

            $correctionRequest->update([
                'status' => 'approved',
                'approved_admin_id' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.stamp_correction_request.show', $correctionRequest->id)
            ->with('status', '修正申請を承認しました。');
    }
}
