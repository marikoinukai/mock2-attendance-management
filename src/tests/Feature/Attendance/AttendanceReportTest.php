<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceReportTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_cannot_access_attendance_report_page()
    {
        $response = $this->get('/attendance/report');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_see_correct_attendance_report_statistics()
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 12:00:00'));

        $user = $this->createGeneralUser();

        $this->createAttendanceRecord($user, '2026-06-03', '09:00:00', '18:00:00');
        $this->createAttendanceRecord($user, '2026-06-04', '09:30:00', '18:00:00');
        $this->createAttendanceRecord($user, '2026-06-05', '09:00:00', '17:00:00');
        $this->createAttendanceRecord($user, '2026-06-06', '08:00:00', '21:00:00');

        $response = $this->actingAs($user)->get('/attendance/report');

        $response->assertOk();
        $response->assertSee('マイ勤怠レポート');
        $response->assertSee('総労働時間');
        $response->assertSee('34h 30m');
        $response->assertSee('総残業時間');
        $response->assertSee('4h 0m');
        $response->assertSee('平均労働時間 / 日');
        $response->assertSee('8h 38m');
        $response->assertSee('遅刻回数');
        $response->assertSee('1 回');
        $response->assertSee('早退回数');
        $response->assertSee('1 回');
        $response->assertSee('長時間労働日数');
        $response->assertSee('1 日');
    }

    public function test_attendance_report_page_is_displayed_safely_when_user_has_no_records()
    {
        Carbon::setTestNow(Carbon::parse('2026-06-15 12:00:00'));

        $user = $this->createGeneralUser();

        $response = $this->actingAs($user)->get('/attendance/report');

        $response->assertOk();
        $response->assertSee('マイ勤怠レポート');
        $response->assertSee('総労働時間');
        $response->assertSee('0h 0m');
        $response->assertSee('総残業時間');
        $response->assertSee('平均労働時間 / 日');
        $response->assertSee('遅刻回数');
        $response->assertSee('0 回');
        $response->assertSee('早退回数');
        $response->assertSee('長時間労働日数');
        $response->assertSee('0 日');
    }

    private function createGeneralUser(): User
    {
        $user = User::create([
        'name' => 'ユーザー1',
        'email' => 'user1@example.com',
        'password' => Hash::make('password'),
        'is_admin' => false,
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    private function createAttendanceRecord(
        User $user,
        string $workDate,
        string $clockIn,
        string $clockOut
    ): AttendanceRecord {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => $workDate,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'comment' => 'レポート確認用',
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        return $attendanceRecord;
    }
}