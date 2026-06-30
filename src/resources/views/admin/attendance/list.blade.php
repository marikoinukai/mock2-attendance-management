@extends('layouts.app')

@section('title', '管理者 勤怠一覧')

@section('content')
    <section class="admin-attendance-list">
        <h1 class="page-title">{{ $targetDate->format('Y年n月j日') }}の勤怠</h1>

        <div class="date-nav">
            <a class="date-nav__link"
                href="{{ route('admin.attendance.index', ['date' => $targetDate->copy()->subDay()->format('Y-m-d')]) }}">
                <span class="date-nav__arrow">←</span> 前日
            </a>

            <div class="date-nav__current">
                <img src="{{ asset('img/calendar-icon.svg') }}" alt="" class="date-icon">
                <span>{{ $targetDate->format('Y/m/d') }}</span>
            </div>

            <a class="date-nav__link"
                href="{{ route('admin.attendance.index', ['date' => $targetDate->copy()->addDay()->format('Y-m-d')]) }}">
                翌日 <span class="date-nav__arrow">→</span>
            </a>
        </div>

        <table class="table admin-attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($staffUsers as $staff)
                    @php
                        $record = $staff->attendanceRecords->first();
                        $breakMinutes = 0;
                        $workMinutes = null;

                        if ($record) {
                            $workDate = $record->work_date->format('Y-m-d');

                            foreach ($record->breaks as $break) {
                                if ($break->break_start && $break->break_end) {
                                    $breakStart = \Carbon\Carbon::parse($workDate . ' ' . $break->break_start);
                                    $breakEnd = \Carbon\Carbon::parse($workDate . ' ' . $break->break_end);
                                    $breakMinutes += $breakStart->diffInMinutes($breakEnd);
                                }
                            }

                            if ($record->clock_in && $record->clock_out) {
                                $clockIn = \Carbon\Carbon::parse($workDate . ' ' . $record->clock_in);
                                $clockOut = \Carbon\Carbon::parse($workDate . ' ' . $record->clock_out);
                                $workMinutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
                            }
                        }
                    @endphp

                    <tr>
                        <td>{{ $staff->name }}</td>
                        <td>
                            {{ $record && $record->clock_in ? \Carbon\Carbon::parse($record->clock_in)->format('H:i') : '' }}
                        </td>
                        <td>
                            {{ $record && $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '' }}
                        </td>
                        <td>
                            @if ($breakMinutes > 0)
                                {{ floor($breakMinutes / 60) }}:{{ sprintf('%02d', $breakMinutes % 60) }}
                            @endif
                        </td>
                        <td>
                            @if (!is_null($workMinutes))
                                {{ floor($workMinutes / 60) }}:{{ sprintf('%02d', $workMinutes % 60) }}
                            @endif
                        </td>
                        <td>
                            @if ($record)
                                <a href="{{ route('admin.attendance.show', $record->id) }}">詳細</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
