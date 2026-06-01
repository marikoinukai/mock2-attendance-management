<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者 修正申請一覧</title>
</head>

<body>
    <h1>修正申請一覧</h1>

    <table border="1">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日</th>
                <th>申請内容</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($correctionRequests as $correctionRequest)
                <tr>
                    <td>
                        @if ($correctionRequest->status === 'pending')
                            承認待ち
                        @elseif ($correctionRequest->status === 'approved')
                            承認済み
                        @else
                            {{ $correctionRequest->status }}
                        @endif
                    </td>

                    <td>{{ $correctionRequest->user->name }}</td>

                    <td>
                        {{ $correctionRequest->attendanceRecord->work_date->format('Y年m月d日') }}
                    </td>

                    <td>
                        {{ $correctionRequest->requested_comment }}
                    </td>

                    <td>
                        {{ $correctionRequest->created_at->format('Y年m月d日 H:i') }}
                    </td>

                    <td>
                        <a href="{{ route('admin.stamp_correction_request.show', $correctionRequest->id) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">修正申請はありません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p>
        <a href="{{ route('admin.attendance.index') }}">管理者勤怠一覧へ戻る</a>
    </p>
</body>

</html>
