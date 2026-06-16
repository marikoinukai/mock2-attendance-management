<?php

namespace Database\Seeders;

use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DummyAttendanceSeeder extends Seeder
{
    public function run()
    {
        $emails = [
            'user1@example.com',
            'user2@example.com',
            'user3@example.com',
        ];

        $existingUserIds = User::whereIn('email', $emails)->pluck('id');

        if ($existingUserIds->isNotEmpty()) {
            $attendanceRecordIds = AttendanceRecord::whereIn('user_id', $existingUserIds)->pluck('id');

            $correctionRequestIds = AttendanceCorrectionRequest::whereIn('user_id', $existingUserIds)
                ->orWhereIn('attendance_record_id', $attendanceRecordIds)
                ->pluck('id');

            if ($correctionRequestIds->isNotEmpty()) {
                DB::table('attendance_correction_breaks')
                    ->whereIn('correction_request_id', $correctionRequestIds)
                    ->delete();

                AttendanceCorrectionRequest::whereIn('id', $correctionRequestIds)->delete();
            }

            if ($attendanceRecordIds->isNotEmpty()) {
                AttendanceBreak::whereIn('attendance_record_id', $attendanceRecordIds)->delete();
                AttendanceRecord::whereIn('id', $attendanceRecordIds)->delete();
            }
        }

        $user1 = User::updateOrCreate(
            ['email' => 'user1@example.com'],
            [
                'name' => 'ユーザー1',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => false,
            ]
        );

        $user2 = User::updateOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => 'ユーザー2',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => false,
            ]
        );

        $user3 = User::updateOrCreate(
            ['email' => 'user3@example.com'],
            [
                'name' => 'ユーザー3',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_admin' => true,
            ]
        );

        $this->createUser1Attendance($user1);
        $this->createGeneralDummyAttendance($user2);
        $this->createGeneralDummyAttendance($user3);
    }

    private function createUser1Attendance(User $user)
    {
        $currentMonth = now()->startOfMonth();

        for ($i = 5; $i >= 1; $i--) {
            $targetMonth = $currentMonth->copy()->subMonths($i);
            $workDates = $this->getWeekdays($targetMonth, 15);

            foreach ($workDates as $date) {
                $this->createAttendanceWithBreak(
                    $user,
                    $date,
                    '09:00:00',
                    '18:00:00',
                    '12:00:00',
                    '13:00:00',
                    '通常勤務'
                );
            }
        }

        $currentMonthWorkDates = $this->getWeekdays($currentMonth, 17);

        foreach ($currentMonthWorkDates as $index => $date) {
            if ($index < 10) {
                $this->createAttendanceWithBreak(
                    $user,
                    $date,
                    '09:00:00',
                    '18:00:00',
                    '12:00:00',
                    '13:00:00',
                    '通常勤務'
                );
            } elseif ($index < 13) {
                $this->createAttendanceWithBreak(
                    $user,
                    $date,
                    '09:00:00',
                    '20:00:00',
                    '12:00:00',
                    '13:00:00',
                    '残業'
                );
            } elseif ($index < 15) {
                $this->createAttendanceWithBreak(
                    $user,
                    $date,
                    '09:30:00',
                    '18:00:00',
                    '12:00:00',
                    '13:00:00',
                    '遅刻'
                );
            } elseif ($index < 16) {
                $this->createAttendanceWithBreak(
                    $user,
                    $date,
                    '09:00:00',
                    '17:00:00',
                    '12:00:00',
                    '13:00:00',
                    '早退'
                );
            } else {
                $this->createAttendanceWithBreak(
                    $user,
                    $date,
                    '08:00:00',
                    '21:00:00',
                    '12:00:00',
                    '13:00:00',
                    '長時間労働'
                );
            }
        }
    }

    private function createGeneralDummyAttendance(User $user)
    {
        $currentMonth = now()->startOfMonth();

        for ($i = 2; $i >= 0; $i--) {
            $targetMonth = $currentMonth->copy()->subMonths($i);
            $workDates = $this->getWeekdays($targetMonth, 10);

            foreach ($workDates as $index => $date) {
                if ($index % 5 === 0) {
                    $this->createAttendanceWithBreak(
                        $user,
                        $date,
                        '09:30:00',
                        '18:00:00',
                        '12:00:00',
                        '13:00:00',
                        '遅刻'
                    );
                } elseif ($index % 7 === 0) {
                    $this->createAttendanceWithBreak(
                        $user,
                        $date,
                        '09:00:00',
                        '19:00:00',
                        '12:00:00',
                        '13:00:00',
                        '残業'
                    );
                } else {
                    $this->createAttendanceWithBreak(
                        $user,
                        $date,
                        '09:00:00',
                        '18:00:00',
                        '12:00:00',
                        '13:00:00',
                        '通常勤務'
                    );
                }
            }
        }
    }

    private function createAttendanceWithBreak(
        User $user,
        Carbon $date,
        string $clockIn,
        string $clockOut,
        string $breakStart,
        string $breakEnd,
        string $comment
    ) {
        $attendanceRecord = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => $date->toDateString(),
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'comment' => $comment,
        ]);

        $attendanceRecord->breaks()->create([
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
        ]);
    }

    private function getWeekdays(Carbon $month, int $count)
    {
        $dates = collect();

        $period = CarbonPeriod::create(
            $month->copy()->startOfMonth(),
            $month->copy()->endOfMonth()
        );

        foreach ($period as $date) {
            if ($date->isWeekday()) {
                $dates->push($date->copy());
            }

            if ($dates->count() >= $count) {
                break;
            }
        }

        return $dates;
    }
}
