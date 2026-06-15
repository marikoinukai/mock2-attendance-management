<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);
    }

    public function test_current_date_and_time_are_displayed()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 9, 30, 0));

        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('2026年6月15日');
        $response->assertSee('09:30');

        Carbon::setTestNow();
    }

    public function test_status_is_off_work_when_user_has_no_attendance_record()
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_status_is_working_when_user_has_clocked_in()
    {
        $user = $this->createUser();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_status_is_on_break_when_user_is_taking_break()
    {
        $user = $this->createUser();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_status_is_clocked_out_when_user_has_clocked_out()
    {
        $user = $this->createUser();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
