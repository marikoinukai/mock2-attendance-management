<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminStaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

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

    public function test_admin_can_view_selected_staff_monthly_attendance()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0));

        $admin = $this->createAdmin();
        $staff = $this->createStaff('山田太郎');

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $staff->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '?month=2026-06');

        $response->assertStatus(200);
        $response->assertSee('山田太郎');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('詳細');
        $response->assertSee('CSV');
    }

    public function test_staff_attendance_page_does_not_display_other_staff_attendance()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0));

        $admin = $this->createAdmin();
        $staff = $this->createStaff('山田太郎');
        $otherStaff = $this->createStaff('佐藤花子');

        AttendanceRecord::create([
            'user_id' => $staff->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        AttendanceRecord::create([
            'user_id' => $otherStaff->id,
            'work_date' => '2026-06-15',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '?month=2026-06');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertDontSee('10:00');
        $response->assertDontSee('19:00');
    }

    public function test_admin_can_view_previous_month_staff_attendance()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0));

        $admin = $this->createAdmin();
        $staff = $this->createStaff('山田太郎');

        AttendanceRecord::create([
            'user_id' => $staff->id,
            'work_date' => '2026-05-20',
            'clock_in' => '08:30:00',
            'clock_out' => '17:30:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('08:30');
        $response->assertSee('17:30');
    }

    public function test_admin_can_view_next_month_staff_attendance()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0));

        $admin = $this->createAdmin();
        $staff = $this->createStaff('山田太郎');

        AttendanceRecord::create([
            'user_id' => $staff->id,
            'work_date' => '2026-07-10',
            'clock_in' => '09:15:00',
            'clock_out' => '18:15:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '?month=2026-07');

        $response->assertStatus(200);
        $response->assertSee('09:15');
        $response->assertSee('18:15');
    }

    public function test_admin_can_download_staff_attendance_csv()
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0));

        $admin = $this->createAdmin();
        $staff = $this->createStaff('山田太郎');

        AttendanceRecord::create([
            'user_id' => $staff->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/staff/' . $staff->id . '/csv?month=2026-06');

        $response->assertStatus(200);
    }

    public function test_general_user_cannot_view_staff_attendance_page()
    {
        $user = $this->createStaff('一般ユーザー');
        $staff = $this->createStaff('山田太郎');

        $response = $this->actingAs($user)->get('/admin/attendance/staff/' . $staff->id);

        $response->assertStatus(403);
    }
}
