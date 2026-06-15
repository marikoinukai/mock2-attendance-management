<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin($name = '管理者')
    {
        return User::factory()->create([
            'name' => $name,
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);
    }

    private function createStaff($name = '山田太郎')
    {
        return User::factory()->create([
            'name' => $name,
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);
    }

    private function createAttendanceRecord(User $staff)
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '元の備考',
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        return $attendanceRecord;
    }

    public function test_admin_can_view_attendance_detail()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff('山田太郎');
        $attendanceRecord = $this->createAttendanceRecord($staff);

        $response = $this->actingAs($admin)->get('/admin/attendance/' . $attendanceRecord->id);

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026年6月15日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('元の備考');
    }

    public function test_clock_in_after_clock_out_is_invalid()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $attendanceRecord = $this->createAttendanceRecord($staff);
        $break = $attendanceRecord->breaks()->first();

        $response = $this->from('/admin/attendance/' . $attendanceRecord->id)
            ->actingAs($admin)
            ->patch('/admin/attendance/' . $attendanceRecord->id, [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'breaks' => [
                    $break->id => [
                        'break_start' => '12:00',
                        'break_end' => '13:00',
                    ],
                ],
                'new_break' => [
                    'break_start' => '',
                    'break_end' => '',
                ],
                'comment' => '修正理由です',
            ]);

        $response->assertRedirect('/admin/attendance/' . $attendanceRecord->id);
        $response->assertSessionHasErrors([
            'clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_break_start_before_clock_in_is_invalid()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $attendanceRecord = $this->createAttendanceRecord($staff);
        $break = $attendanceRecord->breaks()->first();

        $response = $this->from('/admin/attendance/' . $attendanceRecord->id)
            ->actingAs($admin)
            ->patch('/admin/attendance/' . $attendanceRecord->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    $break->id => [
                        'break_start' => '08:30',
                        'break_end' => '13:00',
                    ],
                ],
                'new_break' => [
                    'break_start' => '',
                    'break_end' => '',
                ],
                'comment' => '修正理由です',
            ]);

        $response->assertRedirect('/admin/attendance/' . $attendanceRecord->id);
        $response->assertSessionHasErrors([
            'breaks.' . $break->id . '.break_start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_break_end_after_clock_out_is_invalid()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $attendanceRecord = $this->createAttendanceRecord($staff);
        $break = $attendanceRecord->breaks()->first();

        $response = $this->from('/admin/attendance/' . $attendanceRecord->id)
            ->actingAs($admin)
            ->patch('/admin/attendance/' . $attendanceRecord->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    $break->id => [
                        'break_start' => '12:00',
                        'break_end' => '18:30',
                    ],
                ],
                'new_break' => [
                    'break_start' => '',
                    'break_end' => '',
                ],
                'comment' => '修正理由です',
            ]);

        $response->assertRedirect('/admin/attendance/' . $attendanceRecord->id);
        $response->assertSessionHasErrors([
            'breaks.' . $break->id . '.break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_comment_is_required()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $attendanceRecord = $this->createAttendanceRecord($staff);
        $break = $attendanceRecord->breaks()->first();

        $response = $this->from('/admin/attendance/' . $attendanceRecord->id)
            ->actingAs($admin)
            ->patch('/admin/attendance/' . $attendanceRecord->id, [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    $break->id => [
                        'break_start' => '12:00',
                        'break_end' => '13:00',
                    ],
                ],
                'new_break' => [
                    'break_start' => '',
                    'break_end' => '',
                ],
                'comment' => '',
            ]);

        $response->assertRedirect('/admin/attendance/' . $attendanceRecord->id);
        $response->assertSessionHasErrors([
            'comment' => '備考を記入してください',
        ]);
    }

    public function test_admin_can_update_attendance_detail()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $attendanceRecord = $this->createAttendanceRecord($staff);
        $break = $attendanceRecord->breaks()->first();

        $response = $this->actingAs($admin)
            ->patch('/admin/attendance/' . $attendanceRecord->id, [
                'clock_in' => '08:30',
                'clock_out' => '17:30',
                'breaks' => [
                    $break->id => [
                        'break_start' => '12:15',
                        'break_end' => '13:15',
                    ],
                ],
                'new_break' => [
                    'break_start' => '15:00',
                    'break_end' => '15:15',
                ],
                'comment' => '管理者が修正しました',
            ]);

        $response->assertRedirect('/admin/attendance/' . $attendanceRecord->id);
        $response->assertSessionHas('status', '勤怠情報を更新しました。');

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendanceRecord->id,
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
            'comment' => '管理者が修正しました',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'id' => $break->id,
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:15:00',
            'break_end' => '13:15:00',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '15:00:00',
            'break_end' => '15:15:00',
        ]);
    }

    public function test_admin_cannot_update_when_pending_correction_request_exists()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $attendanceRecord = $this->createAttendanceRecord($staff);
        $break = $attendanceRecord->breaks()->first();

        AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendanceRecord->id,
            'user_id' => $staff->id,
            'requested_clock_in' => '08:30:00',
            'requested_clock_out' => '17:30:00',
            'requested_comment' => '承認待ち申請',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->patch('/admin/attendance/' . $attendanceRecord->id, [
                'clock_in' => '08:30',
                'clock_out' => '17:30',
                'breaks' => [
                    $break->id => [
                        'break_start' => '12:15',
                        'break_end' => '13:15',
                    ],
                ],
                'new_break' => [
                    'break_start' => '',
                    'break_end' => '',
                ],
                'comment' => '管理者が修正しました',
            ]);

        $response->assertRedirect('/admin/attendance/' . $attendanceRecord->id);
        $response->assertSessionHas('status', '承認待ちのため修正はできません。');

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendanceRecord->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '元の備考',
        ]);
    }

    public function test_general_user_cannot_view_admin_attendance_detail()
    {
        $user = $this->createStaff();
        $staff = $this->createStaff('別ユーザー');
        $attendanceRecord = $this->createAttendanceRecord($staff);

        $response = $this->actingAs($user)->get('/admin/attendance/' . $attendanceRecord->id);

        $response->assertStatus(403);
    }
}
