@extends('layouts.app')

@section('title', '勤怠登録')

@section('content')
    @php
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

        if (!$attendanceRecord) {
            $statusText = '勤務外';
        } elseif ($attendanceRecord->clock_out) {
            $statusText = '退勤済';
        } elseif ($currentBreak) {
            $statusText = '休憩中';
        } else {
            $statusText = '出勤中';
        }
    @endphp

    <section class="attendance-stamp">
        <div class="attendance-stamp__status">
            {{ $statusText }}
        </div>

        <p class="attendance-stamp__date">
            {{ $currentDateTime->format('Y年n月j日') }}({{ $weekdays[$currentDateTime->dayOfWeek] }})
        </p>

        <p class="attendance-stamp__time">
            <span class="visually-hidden">{{ $currentDateTime->format('H:i') }}</span>

            <span aria-hidden="true">{{ $currentDateTime->format('H') }}</span>
            <span class="attendance-stamp__colon" aria-hidden="true"></span>
            <span aria-hidden="true">{{ $currentDateTime->format('i') }}</span>
        </p>

        <div class="attendance-stamp__buttons">
            @if (!$attendanceRecord)
                <form method="POST" action="{{ route('attendance.clock_in') }}">
                    @csrf
                    <button class="attendance-stamp__button attendance-stamp__button--black" type="submit">
                        出勤
                    </button>
                </form>
            @elseif ($attendanceRecord->clock_out)
                <p class="attendance-stamp__message">お疲れ様でした。</p>
            @elseif ($currentBreak)
                <form method="POST" action="{{ route('attendance.break_end') }}">
                    @csrf
                    <button class="attendance-stamp__button attendance-stamp__button--white" type="submit">
                        休憩戻
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('attendance.clock_out') }}">
                    @csrf
                    <button class="attendance-stamp__button attendance-stamp__button--black" type="submit">
                        退勤
                    </button>
                </form>

                <form method="POST" action="{{ route('attendance.break_start') }}">
                    @csrf
                    <button class="attendance-stamp__button attendance-stamp__button--white" type="submit">
                        休憩入
                    </button>
                </form>
            @endif
        </div>
    </section>
@endsection
