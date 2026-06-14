@extends('layouts.app')

@section('title', '管理者 勤怠詳細')

@section('content')
    <section class="attendance-detail">
        <h1 class="page-title">勤怠詳細</h1>

        @if (session('status'))
            <p class="alert-message">{{ session('status') }}</p>
        @endif

        @if ($pendingCorrectionRequest)
            <div class="detail-card">
                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td>
                            {{ $attendance->work_date->format('Y年n月j日') }}
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                            <span class="detail-table__separator">〜</span>
                            {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                        </td>
                    </tr>

                    @foreach ($attendance->breaks as $break)
                        <tr>
                            <th>休憩{{ $loop->iteration }}</th>
                            <td>
                                {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}
                                <span class="detail-table__separator">〜</span>
                                {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <th>備考</th>
                        <td>{{ $attendance->comment ?? '' }}</td>
                    </tr>
                </table>
            </div>

            <p class="pending-message">*承認待ちのため修正はできません。</p>

            <div class="detail-link-area">
                <a class="approve-link"
                    href="{{ route('stamp_correction_request.approve.show', $pendingCorrectionRequest->id) }}">
                    修正申請の承認画面へ
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
                @csrf
                @method('PATCH')

                <div class="detail-card">
                    <table class="detail-table">
                        <tr>
                            <th>名前</th>
                            <td>{{ $attendance->user->name }}</td>
                        </tr>

                        <tr>
                            <th>日付</th>
                            <td>
                                {{ $attendance->work_date->format('Y年n月j日') }}
                            </td>
                        </tr>

                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                <div class="time-input-group">
                                    <input class="time-input" type="time" name="clock_in"
                                        value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">

                                    <span class="detail-table__separator">〜</span>

                                    <input class="time-input" type="time" name="clock_out"
                                        value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                                </div>

                                @error('clock_in')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror

                                @error('clock_out')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>

                        @foreach ($attendance->breaks as $break)
                            <tr>
                                <th>休憩{{ $loop->iteration }}</th>
                                <td>
                                    <div class="time-input-group">
                                        <input class="time-input" type="time"
                                            name="breaks[{{ $break->id }}][break_start]"
                                            value="{{ old('breaks.' . $break->id . '.break_start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">

                                        <span class="detail-table__separator">〜</span>

                                        <input class="time-input" type="time"
                                            name="breaks[{{ $break->id }}][break_end]"
                                            value="{{ old('breaks.' . $break->id . '.break_end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                                    </div>

                                    @error('breaks.' . $break->id . '.break_start')
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror

                                    @error('breaks.' . $break->id . '.break_end')
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <th>休憩{{ $attendance->breaks->count() + 1 }}</th>
                            <td>
                                <div class="time-input-group">
                                    <input class="time-input" type="time" name="new_break[break_start]"
                                        value="{{ old('new_break.break_start') }}">

                                    <span class="detail-table__separator">〜</span>

                                    <input class="time-input" type="time" name="new_break[break_end]"
                                        value="{{ old('new_break.break_end') }}">
                                </div>

                                @error('new_break.break_start')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror

                                @error('new_break.break_end')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <th>備考</th>
                            <td>
                                <textarea class="comment-input" name="comment">{{ old('comment', $attendance->comment) }}</textarea>

                                @error('comment')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="detail-button-area">
                    <button class="form-button" type="submit">修正</button>
                </div>
            </form>
        @endif

        <div class="detail-link-area">
            <a class="back-link"
                href="{{ route('admin.attendance.index', ['date' => $attendance->work_date->format('Y-m-d')]) }}">
                勤怠一覧に戻る
            </a>
        </div>
    </section>
@endsection
