<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申請一覧</title>
</head>

<body>
    <h1>申請一覧</h1>

    <p>{{ $user->name }} さんの修正申請一覧</p>

    <table border="1">
        <thead>
            <tr>
                <th>対象日</th>
                <th>申請出勤</th>
                <th>申請退勤</th>
                <th>備考</th>
                <th>状態</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($correctionRequests as $correctionRequest)
                <tr>
                    <td>
                        {{ $correctionRequest->attendanceRecord->work_date->format('Y/m/d') }}
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($correctionRequest->requested_clock_in)->format('H:i') }}
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($correctionRequest->requested_clock_out)->format('H:i') }}
                    </td>
                    <td>
                        {{ $correctionRequest->requested_comment }}
                    </td>
                    <td>
                        @if ($correctionRequest->status === 'pending')
                            承認待ち
                        @elseif ($correctionRequest->status === 'approved')
                            承認済み
                        @else
                            {{ $correctionRequest->status }}
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('attendance.detail', $correctionRequest->attendance_record_id) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">申請はありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p>
        <a href="{{ route('attendance.index') }}">勤怠登録画面へ戻る</a>
    </p>

    <p>
        <a href="{{ route('attendance.list') }}">勤怠一覧へ戻る</a>
    </p>
</body>

</html>
