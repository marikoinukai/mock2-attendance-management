<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠登録</title>
</head>

<body>
    <h1>勤怠登録画面</h1>

    <p>{{ $user->name }} さん</p>
    <p>日付：{{ now()->format('Y年m月d日') }}</p>

    @if (!$attendanceRecord)
        <p>勤務外</p>

        <form method="POST" action="{{ route('attendance.clock_in') }}">
            @csrf
            <button type="submit">出勤</button>
        </form>
    @elseif ($attendanceRecord->clock_in && !$attendanceRecord->clock_out)
        <p>出勤中</p>
        <p>出勤時刻：{{ $attendanceRecord->clock_in }}</p>

        <form method="POST" action="{{ route('attendance.clock_out') }}">
            @csrf
            <button type="submit">退勤</button>
        </form>
    @else
        <p>退勤済み</p>
        <p>出勤時刻：{{ $attendanceRecord->clock_in }}</p>
        <p>退勤時刻：{{ $attendanceRecord->clock_out }}</p>
    @endif
</body>

</html>
