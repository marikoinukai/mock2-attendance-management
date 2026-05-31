<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者 勤怠詳細</title>
</head>

<body>
    <h1>勤怠詳細</h1>

    @if (session('status'))
        <p>{{ session('status') }}</p>
    @endif

    <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
        @csrf
        @method('PATCH')

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
                    <input type="time" name="clock_in"
                        value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">

                    @error('clock_in')
                        <p>{{ $message }}</p>
                    @enderror
                </td>
            </tr>

            <tr>
                <th>退勤</th>
                <td>
                    <input type="time" name="clock_out"
                        value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">

                    @error('clock_out')
                        <p>{{ $message }}</p>
                    @enderror
                </td>
            </tr>

            @foreach ($attendance->breaks as $break)
                <tr>
                    <th>休憩{{ $loop->iteration }}</th>
                    <td>
                        <input type="time" name="breaks[{{ $break->id }}][break_start]"
                            value="{{ old('breaks.' . $break->id . '.break_start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">

                        〜

                        <input type="time" name="breaks[{{ $break->id }}][break_end]"
                            value="{{ old('breaks.' . $break->id . '.break_end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">

                        @error('breaks.' . $break->id . '.break_start')
                            <p>{{ $message }}</p>
                        @enderror

                        @error('breaks.' . $break->id . '.break_end')
                            <p>{{ $message }}</p>
                        @enderror
                    </td>
                </tr>
            @endforeach

            <tr>
                <th>休憩追加</th>
                <td>
                    <input type="time" name="new_break[break_start]" value="{{ old('new_break.break_start') }}">

                    〜

                    <input type="time" name="new_break[break_end]" value="{{ old('new_break.break_end') }}">

                    @error('new_break.break_start')
                        <p>{{ $message }}</p>
                    @enderror

                    @error('new_break.break_end')
                        <p>{{ $message }}</p>
                    @enderror
                </td>
            </tr>

            <tr>
                <th>備考</th>
                <td>
                    <textarea name="comment">{{ old('comment', $attendance->comment) }}</textarea>

                    @error('comment')
                        <p>{{ $message }}</p>
                    @enderror
                </td>
            </tr>
        </table>

        <button type="submit">修正</button>
    </form>

    <p>
        <a href="{{ route('admin.attendance.index', ['date' => $attendance->work_date->format('Y-m-d')]) }}">
            勤怠一覧に戻る
        </a>
    </p>
</body>

</html>
