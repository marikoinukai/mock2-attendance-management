<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者 スタッフ別勤怠一覧</title>
</head>

<body>
    <h1>{{ $staff->name }}さんの勤怠一覧</h1>

    <p>{{ $targetMonth->format('Y年m月') }}</p>

    <p>
        <a
            href="{{ route('admin.staff.attendance', [
                'id' => $staff->id,
                'month' => $targetMonth->copy()->subMonth()->format('Y-m'),
            ]) }}">
            前月
        </a>

        <a
            href="{{ route('admin.staff.attendance', [
                'id' => $staff->id,
                'month' => $targetMonth->copy()->addMonth()->format('Y-m'),
            ]) }}">
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
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendanceRecords as $record)
                @php
                    $breakMinutes = 0;
                    $workMinutes = null;
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
                @endphp

                <tr>
                    <td>{{ $record->work_date->format('m/d') }}</td>
                    <td>{{ $record->clock_in ?? '' }}</td>
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
                    <td>
                        <a href="{{ route('admin.attendance.show', $record->id) }}">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">この月の勤怠はありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p>
        <a href="{{ route('admin.staff.index') }}">スタッフ一覧へ戻る</a>
    </p>
</body>

</html>
