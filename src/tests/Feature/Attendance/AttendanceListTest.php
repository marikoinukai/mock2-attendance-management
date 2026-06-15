<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function createUser()
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);
    }

    public function test_user_can_view_own_attendance_list_for_current_month()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0));

        $user = $this->createUser();

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

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('詳細');
    }

    public function test_user_cannot_see_other_users_attendance_on_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0));

        $user = $this->createUser();
        $otherUser = $this->createUser();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        AttendanceRecord::create([
            'user_id' => $otherUser->id,
            'work_date' => '2026-06-15',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    public function test_user_can_view_previous_month_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0));

        $user = $this->createUser();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-20',
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    public function test_user_can_view_next_month_attendance_list()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0));

        $user = $this->createUser();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-07-10',
            'clock_in' => '09:15:00',
            'clock_out' => '18:15:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list?month=2026-07');

        $response->assertStatus(200);
        $response->assertSee('09:15');
        $response->assertSee('18:15');
    }
}
