<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者 勤怠一覧</title>
</head>

<body>
    <h1>管理者 勤怠一覧</h1>

    <p>{{ $targetDate->format('Y年m月d日') }}</p>

    <p>
        <a href="{{ route('admin.attendance.index', ['date' => $targetDate->copy()->subDay()->format('Y-m-d')]) }}">
            前日
        </a>

        <a href="{{ route('admin.attendance.index', ['date' => $targetDate->copy()->addDay()->format('Y-m-d')]) }}">
            翌日
        </a>
    </p>

    <table border="1">
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
                    <td>{{ $record?->clock_in ?? '' }}</td>
                    <td>{{ $record?->clock_out ?? '' }}</td>
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
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
</body>

</html>
