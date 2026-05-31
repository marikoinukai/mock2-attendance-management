<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者 勤怠詳細</title>
</head>

<body>
    <h1>勤怠詳細</h1>

    <table border="1">
        <tr>
            <th>名前</th>
            <td>{{ $attendance->user->name }}</td>
        </tr>

        <tr>
            <th>日付</th>
            <td>{{ $attendance->work_date->format('Y年m月d日') }}</td>
        </tr>

        <tr>
            <th>出勤</th>
            <td>
                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
            </td>
        </tr>

        <tr>
            <th>退勤</th>
            <td>
                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
            </td>
        </tr>

        @foreach ($attendance->breaks as $break)
            <tr>
                <th>休憩{{ $loop->iteration }}</th>
                <td>
                    {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}
                    〜
                    {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}
                </td>
            </tr>
        @endforeach
    </table>

    <p>
        <a href="{{ route('admin.attendance.index', ['date' => $attendance->work_date->format('Y-m-d')]) }}">
            勤怠一覧に戻る
        </a>
    </p>
</body>

</html>
