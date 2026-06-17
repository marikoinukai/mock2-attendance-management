<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Resources\AttendanceRecordResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Http\Requests\Api\V1\IndexAttendanceRecordRequest;
use App\Http\Requests\Api\V1\StoreAttendanceRecordRequest;
use App\Http\Requests\Api\V1\UpdateAttendanceRecordRequest;

class AttendanceRecordController extends Controller
{
    /**
     * 勤怠一覧を取得する
     *
     * @param IndexAttendanceRecordRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(IndexAttendanceRecordRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $perPage = (int) ($validated['per_page'] ?? 20);

        $attendanceRecords = AttendanceRecord::with(['user', 'breaks'])
            ->when(isset($validated['user_id']), function ($query) use ($validated) {
                $query->where('user_id', $validated['user_id']);
            })
            ->when(isset($validated['date']), function ($query) use ($validated) {
                $query->whereDate('work_date', $validated['date']);
            })
            ->when(isset($validated['month']), function ($query) use ($validated) {
                $query->whereYear('work_date', substr($validated['month'], 0, 4))
                    ->whereMonth('work_date', substr($validated['month'], 5, 2));
            })
            ->orderByDesc('work_date')
            ->paginate($perPage);

        $attendanceRecords->appends($validated);

        return AttendanceRecordResource::collection($attendanceRecords);
    }

    /**
     * 勤怠を登録する
     *
     * @param StoreAttendanceRecordRequest $request
     * @return JsonResponse
     */
    public function store(StoreAttendanceRecordRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $attendanceRecord = $request->user()->attendanceRecords()->create([
            'work_date' => $validated['date'],
            'clock_in' => $validated['clock_in'],
            'clock_out' => $validated['clock_out'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ]);

        $attendanceRecord->load(['user', 'breaks']);

        return (new AttendanceRecordResource($attendanceRecord))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 勤怠詳細を取得する
     *
     * @param AttendanceRecord $attendanceRecord
     * @return AttendanceRecordResource
     */
    public function show(AttendanceRecord $attendanceRecord): AttendanceRecordResource
    {
        $attendanceRecord->load([
            'user',
            'breaks',
            'correctionRequests',
        ]);

        return new AttendanceRecordResource($attendanceRecord);
    }

    /**
     * 勤怠を更新する
     *
     * @param UpdateAttendanceRecordRequest $request
     * @param AttendanceRecord $attendanceRecord
     * @return JsonResponse
     */
    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $attendanceRecord): JsonResponse
    {
        $this->authorize('update', $attendanceRecord);
        
        $validated = $request->validated();

        $attendanceRecord->update([
            'work_date' => $validated['date'],
            'clock_in' => $validated['clock_in'],
            'clock_out' => $validated['clock_out'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ]);

        $attendanceRecord->load(['user', 'breaks']);

        return (new AttendanceRecordResource($attendanceRecord))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * 勤怠を削除する
     *
     * @param AttendanceRecord $attendanceRecord
     * @return JsonResponse
     */
    public function destroy(AttendanceRecord $attendanceRecord): JsonResponse
    {
        $this->authorize('delete', $attendanceRecord);
        
        $attendanceRecord->delete();

        return response()->json(null, 204);
    }
}