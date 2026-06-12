@extends('layouts.app')

@section('title', '勤怠一覧')

@section('content')
    <section class="attendance-list">
        <h1 class="page-title">勤怠一覧</h1>

        <div class="month-nav">
            <a class="month-nav__link"
                href="{{ route('attendance.list', ['month' => $targetMonth->copy()->subMonth()->format('Y-m')]) }}">
                ← 前月
            </a>

            <div class="month-nav__current">
                <span class="month-nav__calendar">📅</span>
                <span>{{ $targetMonth->format('Y/m') }}</span>
            </div>

            <a class="month-nav__link"
                href="{{ route('attendance.list', ['month' => $targetMonth->copy()->addMonth()->format('Y-m')]) }}">
                翌月 →
            </a>
        </div>

        <table class="table attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendanceRows as $row)
                    @php
                        $date = $row['date'];
                        $record = $row['record'];

                        $breakMinutes = 0;
                        $workDate = $date->format('Y-m-d');
                        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

                        if ($record) {
                            foreach ($record->breaks as $break) {
                                if ($break->break_start && $break->break_end) {
                                    $breakStart = \Carbon\Carbon::parse($workDate . ' ' . $break->break_start);
                                    $breakEnd = \Carbon\Carbon::parse($workDate . ' ' . $break->break_end);
                                    $breakMinutes += $breakStart->diffInMinutes($breakEnd);
                                }
                            }
                        }

                        $workMinutes = null;

                        if ($record && $record->clock_in && $record->clock_out) {
                            $clockIn = \Carbon\Carbon::parse($workDate . ' ' . $record->clock_in);
                            $clockOut = \Carbon\Carbon::parse($workDate . ' ' . $record->clock_out);
                            $workMinutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
                        }
                    @endphp

                    <tr>
                        <td>
                            {{ $date->format('m/d') }}({{ $weekdays[$date->dayOfWeek] }})
                        </td>
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
                                <a href="{{ route('attendance.detail', $record->id) }}">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </section>
@endsection
