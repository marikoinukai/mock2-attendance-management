<?php

namespace Tests\Feature\Api;

use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class AttendanceRecordApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 勤怠一覧をJSONで取得できる
     *
     * @return void
     */
    public function test_can_get_attendance_record_list(): void
    {
        $user = User::create([
            'name' => 'ユーザー1',
            'email' => 'api-user1@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'API一覧テスト',
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        $response = $this->getJson('/api/v1/attendance-records?per_page=10');

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'user_id',
                    'user_name',
                    'date',
                    'clock_in',
                    'clock_out',
                    'total_time',
                    'total_break_time',
                    'comment',
                ],
            ],
            'links',
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        $response->assertJsonPath('data.0.user_name', 'ユーザー1');
        $response->assertJsonPath('data.0.date', '2026-07-01');
        $response->assertJsonPath('data.0.total_time', '08:00');
        $response->assertJsonPath('data.0.total_break_time', '01:00');
    }

    /**
     * 勤怠詳細をJSONで取得できる
     *
     * @return void
     */
    public function test_can_get_attendance_record_detail(): void
    {
        $user = User::create([
            'name' => 'ユーザー1',
            'email' => 'api-user1@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'API詳細テスト',
        ]);

        AttendanceBreak::create([
            'attendance_record_id' => $attendanceRecord->id,
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendanceRecord->id,
            'user_id' => $user->id,
            'requested_clock_in' => '09:30:00',
            'requested_clock_out' => '18:30:00',
            'requested_comment' => '修正申請テスト',
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $response->assertOk();

        $response->assertJsonStructure([
            'data' => [
                'id',
                'user_id',
                'user_name',
                'user' => [
                    'id',
                    'name',
                ],
                'date',
                'clock_in',
                'clock_out',
                'total_time',
                'total_break_time',
                'comment',
                'breaks' => [
                    [
                        'id',
                        'break_in',
                        'break_out',
                    ],
                ],
                'applications' => [
                    [
                        'id',
                        'user_id',
                        'status',
                        'requested_clock_in',
                        'requested_clock_out',
                        'requested_comment',
                    ],
                ],
            ],
        ]);

        $response->assertJsonPath('data.user.name', 'ユーザー1');
        $response->assertJsonPath('data.breaks.0.break_in', '12:00:00');
        $response->assertJsonPath('data.applications.0.status', 'pending');
    }

    /**
     * 存在しない勤怠IDの場合は404 JSONを返す
     *
     * @return void
     */
    public function test_returns_404_when_attendance_record_not_found(): void
    {
        $response = $this->getJson('/api/v1/attendance-records/999999');

        $response->assertNotFound();

        $response->assertJson([
            'error' => '勤怠情報が見つかりませんでした。',
        ]);
    }

    /**
     * 未認証では勤怠を登録できない
     *
     * @return void
     */
    public function test_guest_cannot_create_attendance_record(): void
    {
        $response = $this->postJson('/api/v1/attendance-records', [
            'date' => '2026-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '未認証登録テスト',
        ]);

        $response->assertUnauthorized();

        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    /**
     * 認証済みユーザーは勤怠を登録できる
     *
     * @return void
     */
    public function test_authenticated_user_can_create_attendance_record(): void
    {
        $user = User::create([
            'name' => 'ユーザー1',
            'email' => 'api-user1@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/attendance-records', [
            'date' => '2026-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'API登録テスト',
        ]);

        $response->assertCreated();

        $response->assertJsonPath('data.user_id', $user->id);
        $response->assertJsonPath('data.date', '2026-07-01');
        $response->assertJsonPath('data.clock_in', '09:00:00');
        $response->assertJsonPath('data.comment', 'API登録テスト');

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'work_date' => '2026-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => 'API登録テスト',
        ]);
    }

    /**
     * 勤怠登録時に不正な値の場合は422を返す
     *
     * @return void
     */
    public function test_create_attendance_record_validation_error(): void
    {
        $user = User::create([
            'name' => 'ユーザー1',
            'email' => 'api-user1@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/attendance-records', [
            'date' => '2026/07/01',
            'clock_in' => '',
            'clock_out' => '08:00:00',
            'comment' => str_repeat('あ', 256),
        ]);

        $response->assertStatus(422);

        $response->assertJsonStructure([
            'message',
            'errors',
        ]);
    }

    /**
     * 認証済みユーザーは自分の勤怠を更新できる
     *
     * @return void
     */
    public function test_authenticated_user_can_update_own_attendance_record(): void
    {
        $user = User::create([
            'name' => 'ユーザー1',
            'email' => 'api-user1@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '更新前',
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/v1/attendance-records/{$attendanceRecord->id}", [
            'date' => '2026-07-01',
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'comment' => 'API更新テスト',
        ]);

        $response->assertOk();

        $response->assertJsonPath('data.clock_in', '09:30:00');
        $response->assertJsonPath('data.clock_out', '18:30:00');
        $response->assertJsonPath('data.comment', 'API更新テスト');

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendanceRecord->id,
            'clock_in' => '09:30:00',
            'clock_out' => '18:30:00',
            'comment' => 'API更新テスト',
        ]);
    }

    /**
     * 認証済みユーザーは自分の勤怠を削除できる
     *
     * @return void
     */
    public function test_authenticated_user_can_delete_own_attendance_record(): void
    {
        $user = User::create([
            'name' => 'ユーザー1',
            'email' => 'api-user1@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => '2026-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '削除テスト',
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('attendance_records', [
            'id' => $attendanceRecord->id,
        ]);
    }

    /**
     * 他ユーザーの勤怠は更新・削除できない
     *
     * @return void
     */
    public function test_user_cannot_update_or_delete_other_users_attendance_record(): void
    {
        $owner = User::create([
            'name' => 'ユーザー1',
            'email' => 'api-owner@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $otherUser = User::create([
            'name' => 'ユーザー2',
            'email' => 'api-other@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'email_verified_at' => now(),
        ]);

        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $owner->id,
            'work_date' => '2026-07-01',
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'comment' => '他ユーザー操作テスト',
        ]);

        Sanctum::actingAs($otherUser);

        $updateResponse = $this->putJson("/api/v1/attendance-records/{$attendanceRecord->id}", [
            'date' => '2026-07-01',
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
            'comment' => '他ユーザー更新',
        ]);

        $updateResponse->assertForbidden();

        $updateResponse->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);

        $deleteResponse = $this->deleteJson("/api/v1/attendance-records/{$attendanceRecord->id}");

        $deleteResponse->assertForbidden();

        $deleteResponse->assertJson([
            'error' => 'この操作を実行する権限がありません。',
        ]);

        $this->assertDatabaseHas('attendance_records', [
            'id' => $attendanceRecord->id,
            'user_id' => $owner->id,
        ]);
    }
}