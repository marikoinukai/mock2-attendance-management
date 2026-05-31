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
    @elseif ($attendanceRecord->clock_out)
        <p>退勤済み</p>
        <p>出勤時刻：{{ $attendanceRecord->clock_in }}</p>
        <p>退勤時刻：{{ $attendanceRecord->clock_out }}</p>
    @elseif ($currentBreak)
        <p>休憩中</p>
        <p>出勤時刻：{{ $attendanceRecord->clock_in }}</p>
        <p>休憩開始時刻：{{ $currentBreak->break_start }}</p>

        <form method="POST" action="{{ route('attendance.break_end') }}">
            @csrf
            <button type="submit">休憩戻</button>
        </form>
    @else
        <p>出勤中</p>
        <p>出勤時刻：{{ $attendanceRecord->clock_in }}</p>

        <form method="POST" action="{{ route('attendance.break_start') }}">
            @csrf
            <button type="submit">休憩入</button>
        </form>

        <form method="POST" action="{{ route('attendance.clock_out') }}">
            @csrf
            <button type="submit">退勤</button>
        </form>
    @endif

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
</body>

</html>
