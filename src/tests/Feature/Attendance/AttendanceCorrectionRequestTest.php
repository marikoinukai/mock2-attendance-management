<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private function createUser($name = 'テストユーザー')
    {
        return User::factory()->create([
            'name' => $name,
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);
    }

    private function createAttendanceRecord(User $user)
    {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
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

    public function test_clock_in_after_clock_out_is_invalid()
    {
        $user = $this->createUser();
        $attendanceRecord = $this->createAttendanceRecord($user);

        $response = $this->from('/attendance/detail/' . $attendanceRecord->id)
            ->actingAs($user)
            ->post('/attendance/detail/' . $attendanceRecord->id . '/correction', [
                'requested_clock_in' => '19:00',
                'requested_clock_out' => '18:00',
                'requested_breaks' => [
                    [
                        'requested_break_start' => '12:00',
                        'requested_break_end' => '13:00',
                    ],
                ],
                'requested_new_break' => [
                    'requested_break_start' => '',
                    'requested_break_end' => '',
                ],
                'requested_comment' => '修正理由です',
            ]);

        $response->assertRedirect('/attendance/detail/' . $attendanceRecord->id);
        $response->assertSessionHasErrors([
            'requested_clock_in' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_break_start_before_clock_in_is_invalid()
    {
        $user = $this->createUser();
        $attendanceRecord = $this->createAttendanceRecord($user);

        $response = $this->from('/attendance/detail/' . $attendanceRecord->id)
            ->actingAs($user)
            ->post('/attendance/detail/' . $attendanceRecord->id . '/correction', [
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_breaks' => [
                    [
                        'requested_break_start' => '08:30',
                        'requested_break_end' => '13:00',
                    ],
                ],
                'requested_new_break' => [
                    'requested_break_start' => '',
                    'requested_break_end' => '',
                ],
                'requested_comment' => '修正理由です',
            ]);

        $response->assertRedirect('/attendance/detail/' . $attendanceRecord->id);
        $response->assertSessionHasErrors([
            'requested_breaks.0.requested_break_start' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_break_end_after_clock_out_is_invalid()
    {
        $user = $this->createUser();
        $attendanceRecord = $this->createAttendanceRecord($user);

        $response = $this->from('/attendance/detail/' . $attendanceRecord->id)
            ->actingAs($user)
            ->post('/attendance/detail/' . $attendanceRecord->id . '/correction', [
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_breaks' => [
                    [
                        'requested_break_start' => '12:00',
                        'requested_break_end' => '18:30',
                    ],
                ],
                'requested_new_break' => [
                    'requested_break_start' => '',
                    'requested_break_end' => '',
                ],
                'requested_comment' => '修正理由です',
            ]);

        $response->assertRedirect('/attendance/detail/' . $attendanceRecord->id);
        $response->assertSessionHasErrors([
            'requested_breaks.0.requested_break_end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_comment_is_required()
    {
        $user = $this->createUser();
        $attendanceRecord = $this->createAttendanceRecord($user);

        $response = $this->from('/attendance/detail/' . $attendanceRecord->id)
            ->actingAs($user)
            ->post('/attendance/detail/' . $attendanceRecord->id . '/correction', [
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',
                'requested_breaks' => [
                    [
                        'requested_break_start' => '12:00',
                        'requested_break_end' => '13:00',
                    ],
                ],
                'requested_new_break' => [
                    'requested_break_start' => '',
                    'requested_break_end' => '',
                ],
                'requested_comment' => '',
            ]);

        $response->assertRedirect('/attendance/detail/' . $attendanceRecord->id);
        $response->assertSessionHasErrors([
            'requested_comment' => '備考を記入してください',
        ]);
    }

    public function test_user_can_submit_attendance_correction_request()
    {
        $user = $this->createUser();
        $attendanceRecord = $this->createAttendanceRecord($user);

        $response = $this->actingAs($user)
            ->post('/attendance/detail/' . $attendanceRecord->id . '/correction', [
                'requested_clock_in' => '08:30',
                'requested_clock_out' => '17:30',
                'requested_breaks' => [
                    [
                        'requested_break_start' => '12:15',
                        'requested_break_end' => '13:15',
                    ],
                ],
                'requested_new_break' => [
                    'requested_break_start' => '15:00',
                    'requested_break_end' => '15:15',
                ],
                'requested_comment' => '電車遅延のため修正申請します',
            ]);

        $response->assertRedirect('/attendance/detail/' . $attendanceRecord->id);
        $response->assertSessionHas('status', '修正申請を送信しました');

        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_record_id' => $attendanceRecord->id,
            'user_id' => $user->id,
            'requested_clock_in' => '08:30:00',
            'requested_clock_out' => '17:30:00',
            'requested_comment' => '電車遅延のため修正申請します',
            'status' => 'pending',
        ]);

        $correctionRequest = AttendanceCorrectionRequest::where('attendance_record_id', $attendanceRecord->id)->first();

        $this->assertDatabaseHas('attendance_correction_breaks', [
            'correction_request_id' => $correctionRequest->id,
            'requested_break_start' => '12:15:00',
            'requested_break_end' => '13:15:00',
        ]);

        $this->assertDatabaseHas('attendance_correction_breaks', [
            'correction_request_id' => $correctionRequest->id,
            'requested_break_start' => '15:00:00',
            'requested_break_end' => '15:15:00',
        ]);
    }

    public function test_user_cannot_submit_correction_request_for_other_users_attendance()
    {
        $user = $this->createUser('ユーザー1');
        $otherUser = $this->createUser('ユーザー2');
        $otherAttendanceRecord = $this->createAttendanceRecord($otherUser);

        $response = $this->actingAs($user)
            ->post('/attendance/detail/' . $otherAttendanceRecord->id . '/correction', [
                'requested_clock_in' => '08:30',
                'requested_clock_out' => '17:30',
                'requested_breaks' => [
                    [
                        'requested_break_start' => '12:15',
                        'requested_break_end' => '13:15',
                    ],
                ],
                'requested_new_break' => [
                    'requested_break_start' => '',
                    'requested_break_end' => '',
                ],
                'requested_comment' => '他人の勤怠修正',
            ]);

        $response->assertStatus(404);

        $this->assertDatabaseMissing('attendance_correction_requests', [
            'attendance_record_id' => $otherAttendanceRecord->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_pending_correction_request_is_displayed_on_request_list()
    {
        $user = $this->createUser();
        $attendanceRecord = $this->createAttendanceRecord($user);

        AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendanceRecord->id,
            'user_id' => $user->id,
            'requested_clock_in' => '08:30:00',
            'requested_clock_out' => '17:30:00',
            'requested_comment' => '申請一覧確認',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=pending');

        $response->assertStatus(200);
        $response->assertSee('申請一覧確認');
        $response->assertSee('承認待ち');
    }

    public function test_approved_correction_request_is_displayed_on_request_list()
    {
        $user = $this->createUser();
        $attendanceRecord = $this->createAttendanceRecord($user);

        AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendanceRecord->id,
            'user_id' => $user->id,
            'requested_clock_in' => '08:30:00',
            'requested_clock_out' => '17:30:00',
            'requested_comment' => '承認済み一覧確認',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み一覧確認');
        $response->assertSee('承認済み');
    }
}
