<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠詳細</title>
</head>

<body>
    <h1>勤怠詳細</h1>

    @php
        $breakMinutes = 0;
        $workMinutes = null;
        $workDate = $attendanceRecord->work_date->format('Y-m-d');

        foreach ($attendanceRecord->breaks as $break) {
            if ($break->break_start && $break->break_end) {
                $breakStart = \Carbon\Carbon::parse($workDate . ' ' . $break->break_start);
                $breakEnd = \Carbon\Carbon::parse($workDate . ' ' . $break->break_end);
                $breakMinutes += $breakStart->diffInMinutes($breakEnd);
            }
        }

        if ($attendanceRecord->clock_in && $attendanceRecord->clock_out) {
            $clockIn = \Carbon\Carbon::parse($workDate . ' ' . $attendanceRecord->clock_in);
            $clockOut = \Carbon\Carbon::parse($workDate . ' ' . $attendanceRecord->clock_out);
            $workMinutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
        }
    @endphp

    <table border="1">
        <tr>
            <th>名前</th>
            <td>{{ $user->name }}</td>
        </tr>
        <tr>
            <th>日付</th>
            <td>{{ $attendanceRecord->work_date->format('Y年m月d日') }}</td>
        </tr>
        <tr>
            <th>出勤</th>
            <td>{{ $attendanceRecord->clock_in }}</td>
        </tr>
        <tr>
            <th>退勤</th>
            <td>{{ $attendanceRecord->clock_out ?? '' }}</td>
        </tr>
        <tr>
            <th>休憩合計</th>
            <td>
                @if ($breakMinutes > 0)
                    {{ floor($breakMinutes / 60) }}:{{ sprintf('%02d', $breakMinutes % 60) }}
                @endif
            </td>
        </tr>
        <tr>
            <th>勤務合計</th>
            <td>
                @if (!is_null($workMinutes))
                    {{ floor($workMinutes / 60) }}:{{ sprintf('%02d', $workMinutes % 60) }}
                @endif
            </td>
        </tr>
        <tr>
            <th>備考</th>
            <td>{{ $attendanceRecord->comment ?? '' }}</td>
        </tr>
    </table>

    <h2>休憩一覧</h2>

    <table border="1">
        <thead>
            <tr>
                <th>休憩開始</th>
                <th>休憩終了</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendanceRecord->breaks as $break)
                <tr>
                    <td>{{ $break->break_start }}</td>
                    <td>{{ $break->break_end ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        <a href="{{ route('attendance.list') }}">勤怠一覧へ戻る</a>
    </p>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <h2>修正申請</h2>

    <form method="POST" action="{{ route('attendance.correction.store', $attendanceRecord->id) }}">
        @csrf

        <div>
            <label for="requested_clock_in">出勤</label>
            <input type="time" id="requested_clock_in" name="requested_clock_in"
                value="{{ old('requested_clock_in', \Carbon\Carbon::parse($attendanceRecord->clock_in)->format('H:i')) }}">
            @error('requested_clock_in')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="requested_clock_out">退勤</label>
            <input type="time" id="requested_clock_out" name="requested_clock_out"
                value="{{ old('requested_clock_out', $attendanceRecord->clock_out ? \Carbon\Carbon::parse($attendanceRecord->clock_out)->format('H:i') : '') }}">
            @error('requested_clock_out')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <h3>休憩</h3>

        @foreach ($attendanceRecord->breaks as $index => $break)
            <div>
                <label>休憩開始</label>
                <input type="time" name="requested_breaks[{{ $index }}][requested_break_start]"
                    value="{{ old('requested_breaks.' . $index . '.requested_break_start', \Carbon\Carbon::parse($break->break_start)->format('H:i')) }}">

                <label>休憩終了</label>
                <input type="time" name="requested_breaks[{{ $index }}][requested_break_end]"
                    value="{{ old('requested_breaks.' . $index . '.requested_break_end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
            </div>
        @endforeach

        <div>
            <label for="requested_comment">備考</label>
            <textarea id="requested_comment" name="requested_comment">{{ old('requested_comment', $attendanceRecord->comment) }}</textarea>
            @error('requested_comment')
                <p>{{ $message }}</p>
            @enderror
        </div>

        <button type="submit">修正申請を送信</button>
    </form>
</body>

</html>
