<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendanceRecordPolicy
{
    use HandlesAuthorization;

    /**
     * 勤怠を更新できるか判定する
     *
     * @param User $user
     * @param AttendanceRecord $attendanceRecord
     * @return bool
     */
    public function update(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->is_admin || $user->id === $attendanceRecord->user_id;
    }

    /**
     * 勤怠を削除できるか判定する
     *
     * @param User $user
     * @param AttendanceRecord $attendanceRecord
     * @return bool
     */
    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->is_admin || $user->id === $attendanceRecord->user_id;
    }
}