<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者 修正申請承認</title>
</head>

<body>
    <h1>修正申請承認</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <table border="1">
        <tr>
            <th>状態</th>
            <td>
                @if ($correctionRequest->status === 'pending')
                    承認待ち
                @elseif ($correctionRequest->status === 'approved')
                    承認済み
                @else
                    {{ $correctionRequest->status }}
                @endif
            </td>
        </tr>

        <tr>
            <th>名前</th>
            <td>{{ $correctionRequest->user->name }}</td>
        </tr>

        <tr>
            <th>対象日</th>
            <td>{{ $correctionRequest->attendanceRecord->work_date->format('Y年m月d日') }}</td>
        </tr>

        <tr>
            <th>現在の出勤</th>
            <td>{{ $correctionRequest->attendanceRecord->clock_in }}</td>
        </tr>

        <tr>
            <th>申請後の出勤</th>
            <td>{{ \Carbon\Carbon::parse($correctionRequest->requested_clock_in)->format('H:i') }}</td>
        </tr>

        <tr>
            <th>現在の退勤</th>
            <td>{{ $correctionRequest->attendanceRecord->clock_out }}</td>
        </tr>

        <tr>
            <th>申請後の退勤</th>
            <td>{{ \Carbon\Carbon::parse($correctionRequest->requested_clock_out)->format('H:i') }}</td>
        </tr>

        <tr>
            <th>申請理由</th>
            <td>{{ $correctionRequest->requested_comment }}</td>
        </tr>
    </table>

    <h2>現在の休憩</h2>

    <table border="1">
        <thead>
            <tr>
                <th>休憩開始</th>
                <th>休憩終了</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($correctionRequest->attendanceRecord->breaks as $break)
                <tr>
                    <td>{{ $break->break_start }}</td>
                    <td>{{ $break->break_end }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>申請後の休憩</h2>

    <table border="1">
        <thead>
            <tr>
                <th>休憩開始</th>
                <th>休憩終了</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($correctionRequest->correctionBreaks as $break)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($break->requested_break_start)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($break->requested_break_end)->format('H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($correctionRequest->status === 'pending')
        <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $correctionRequest->id) }}">
            @csrf
            @method('PATCH')

            <button type="submit">承認</button>
        </form>
    @else
        <p>この申請は承認済みです。</p>
    @endif

    <p>
        <a href="{{ route('admin.stamp_correction_request.index') }}">修正申請一覧へ戻る</a>
    </p>
</body>

</html>
