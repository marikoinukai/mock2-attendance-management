<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者 スタッフ一覧</title>
</head>

<body>
    <h1>スタッフ一覧</h1>

    <table border="1">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($staffUsers as $staff)
                <tr>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->email }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">スタッフはいません。</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p>
        <a href="{{ route('admin.attendance.index') }}">管理者勤怠一覧へ戻る</a>
    </p>
</body>

</html>
