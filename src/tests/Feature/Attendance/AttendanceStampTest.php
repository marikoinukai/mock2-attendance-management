<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceStampTest extends TestCase
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

    public function test_user_can_clock_in()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 9, 0, 0));

        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/attendance/clock-in');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);
    }

    public function test_user_cannot_clock_in_twice_on_same_day()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 9, 0, 0));

        $user = $this->createUser();

        $this->actingAs($user)->post('/attendance/clock-in');

        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0));

        $this->actingAs($user)->post('/attendance/clock-in');

        $this->assertEquals(1, AttendanceRecord::where('user_id', $user->id)
            ->where('work_date', '2026-06-15')
            ->count());

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
        ]);
    }

    public function test_user_can_start_break()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 12, 0, 0));

        $user = $this->createUser();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/break-start');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);
    }

    public function test_user_can_end_break()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 13, 0, 0));

        $user = $this->createUser();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/break-end');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);
    }

    public function test_user_can_take_break_multiple_times()
    {
        $user = $this->createUser();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        Carbon::setTestNow(Carbon::create(2026, 6, 15, 12, 0, 0));
        $this->actingAs($user)->post('/attendance/break-start');

        Carbon::setTestNow(Carbon::create(2026, 6, 15, 13, 0, 0));
        $this->actingAs($user)->post('/attendance/break-end');

        Carbon::setTestNow(Carbon::create(2026, 6, 15, 15, 0, 0));
        $this->actingAs($user)->post('/attendance/break-start');

        Carbon::setTestNow(Carbon::create(2026, 6, 15, 15, 30, 0));
        $this->actingAs($user)->post('/attendance/break-end');

        $this->assertEquals(2, AttendanceBreak::where('attendance_record_id', $attendanceRecord->id)->count());

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '15:00:00',
            'break_end' => '15:30:00',
        ]);
    }

    public function test_user_can_clock_out()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 18, 0, 0));

        $user = $this->createUser();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/clock-out');

        $response->assertRedirect('/attendance');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);
    }

    public function test_status_is_clocked_out_after_clock_out()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 18, 0, 0));

        $user = $this->createUser();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => null,
        ]);

        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした');
    }
}
