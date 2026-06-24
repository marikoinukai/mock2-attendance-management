<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionBreak;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_pending_correction_requests()
    {
        $admin = $this->createAdminUser();

        $pendingRequest = $this->createCorrectionRequest('pending', '承認待ちの申請');
        $approvedRequest = $this->createCorrectionRequest('approved', '承認済みの申請');

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list');

        $response->assertOk();
        $response->assertSee('承認待ちの申請');
        $response->assertDontSee('承認済みの申請');
    }

    public function test_admin_can_view_approved_correction_requests()
    {
        $admin = $this->createAdminUser();

        $pendingRequest = $this->createCorrectionRequest('pending', '承認待ちの申請');
        $approvedRequest = $this->createCorrectionRequest('approved', '承認済みの申請');

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?status=approved');

        $response->assertOk();
        $response->assertSee('承認済みの申請');
        $response->assertDontSee('承認待ちの申請');
    }

    public function test_admin_can_view_correction_request_detail()
    {
        $admin = $this->createAdminUser();

        $correctionRequest = $this->createCorrectionRequest('pending', '修正申請の詳細確認');

        $response = $this->actingAs($admin)
            ->get('/stamp_correction_request/approve/' . $correctionRequest->id);

        $response->assertOk();
        $response->assertSee('ユーザー1');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertSee('12:30');
        $response->assertSee('13:30');
        $response->assertSee('修正申請の詳細確認');
    }

    public function test_admin_can_approve_correction_request()
    {
        Carbon::setTestNow(Carbon::parse('2026-06-20 10:00:00'));

        $admin = $this->createAdminUser();

        $correctionRequest = $this->createCorrectionRequest('pending', '承認後の備考');

        $response = $this->actingAs($admin)
            ->patch('/stamp_correction_request/approve/' . $correctionRequest->id);

        $response->assertRedirect('/stamp_correction_request/approve/' . $correctionRequest->id);

        $attendanceRecord = $correctionRequest->attendanceRecord->fresh();

        $this->assertSame('09:30:00', $attendanceRecord->clock_in);
        $this->assertSame('18:30:00', $attendanceRecord->clock_out);
        $this->assertSame('承認後の備考', $attendanceRecord->comment);

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:30:00',
            'break_end' => '13:30:00',
        ]);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $correctionRequest->id,
            'status' => 'approved',
            'approved_admin_id' => $admin->id,
        ]);

        Carbon::setTestNow();
    }

    private function createAdminUser(): User
    {
        $admin = User::create([
            'name' => 'ユーザー3',
            'email' => 'user3@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        $admin->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return $admin;
    }

   private function createGeneralUser(): User
    {
        $userNumber = User::where('is_admin', false)->count() + 1;

        $user = User::create([
            'name' => 'ユーザー' . $userNumber,
            'email' => 'user' . $userNumber . '@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    private function createCorrectionRequest(string $status, string $comment): AttendanceCorrectionRequest
    {
        $user = $this->createGeneralUser();

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-15',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '修正前の備考',
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendanceRecord->id,
            'user_id' => $user->id,
            'requested_clock_in' => '09:30:00',
            'requested_clock_out' => '18:30:00',
            'requested_comment' => $comment,
            'status' => $status,
        ]);

        AttendanceCorrectionBreak::create([
            'correction_request_id' => $correctionRequest->id,
            'requested_break_start' => '12:30:00',
            'requested_break_end' => '13:30:00',
        ]);

        return $correctionRequest;
    }
}