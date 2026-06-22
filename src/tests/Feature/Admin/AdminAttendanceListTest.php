<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceBreak;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
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

    private function createStaff($name = '一般ユーザー')
    {
        return User::factory()->create([
            'name' => $name,
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);
    }

    public function test_admin_can_view_staff_attendance_list_for_selected_date()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff('ユーザー1');

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

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-06-15');

        $response->assertStatus(200);
        $response->assertSee('ユーザー1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('詳細');
    }

    public function test_admin_attendance_list_displays_only_general_users()
    {
        $admin = $this->createAdmin('管理者除外テスト');
        $staff = $this->createStaff('一般ユーザー表示テスト');

        AttendanceRecord::create([
            'user_id' => $staff->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-06-15');

        $response->assertStatus(200);
        $response->assertSee('一般ユーザー表示テスト');
        $response->assertDontSee('管理者除外テスト');
    }

    public function test_general_user_cannot_view_admin_attendance_list()
    {
        $user = $this->createStaff();

        $response = $this->actingAs($user)->get('/admin/attendance/list');

        $response->assertStatus(403);
    }
}
