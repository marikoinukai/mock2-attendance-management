<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
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

    public function test_user_can_view_own_attendance_detail()
    {
        $user = $this->createUser('山田太郎');

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendanceRecord->id);

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('2026年6月15日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    public function test_user_cannot_view_other_users_attendance_detail()
    {
        $user = $this->createUser('山田太郎');
        $otherUser = $this->createUser('佐藤花子');

        $otherAttendanceRecord = AttendanceRecord::create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-06-15',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $otherAttendanceRecord->id);

        $response->assertStatus(404);
    }

    public function test_attendance_detail_has_correction_form_items()
    {
        $user = $this->createUser('山田太郎');

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/detail/' . $attendanceRecord->id);

        $response->assertStatus(200);
        $response->assertSee('出勤');
        $response->assertSee('退勤');
        $response->assertSee('備考');
        $response->assertSee('修正');
    }
}
