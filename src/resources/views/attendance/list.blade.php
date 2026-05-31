<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠一覧</title>
</head>

<body>
    <h1>勤怠一覧</h1>

    <p>{{ $user->name }} さん</p>
    <p>{{ $targetMonth->format('Y年m月') }}</p>

    <p>
        <a href="{{ route('attendance.list', ['month' => $targetMonth->copy()->subMonth()->format('Y-m')]) }}">
            前月
        </a>

        <a href="{{ route('attendance.list', ['month' => $targetMonth->copy()->addMonth()->format('Y-m')]) }}">
            翌月
        </a>
    </p>

    <table border="1">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendanceRecords as $record)
                @php
                    $breakMinutes = 0;
                    $workDate = $record->work_date->format('Y-m-d');

                    foreach ($record->breaks as $break) {
                        if ($break->break_start && $break->break_end) {
                            $breakStart = \Carbon\Carbon::parse($workDate . ' ' . $break->break_start);
                            $breakEnd = \Carbon\Carbon::parse($workDate . ' ' . $break->break_end);
                            $breakMinutes += $breakStart->diffInMinutes($breakEnd);
                        }
                    }

                    $workMinutes = null;

                    if ($record->clock_in && $record->clock_out) {
                        $clockIn = \Carbon\Carbon::parse($workDate . ' ' . $record->clock_in);
                        $clockOut = \Carbon\Carbon::parse($workDate . ' ' . $record->clock_out);
                        $workMinutes = $clockIn->diffInMinutes($clockOut) - $breakMinutes;
                    }
                @endphp

                <tr>
                    <td>{{ $record->work_date->format('m/d') }}</td>
                    <td>{{ $record->clock_in }}</td>
                    <td>{{ $record->clock_out ?? '' }}</td>
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
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        <a href="{{ route('attendance.index') }}">勤怠登録画面へ戻る</a>
    </p>
</body>

</html>
