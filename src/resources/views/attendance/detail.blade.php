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
</body>

</html>
