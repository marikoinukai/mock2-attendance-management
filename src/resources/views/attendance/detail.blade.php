@extends('layouts.app')

@section('title', '勤怠詳細')

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
                        <td>
                            <span class="detail-name-text">{{ $user->name }}</span>
                        </td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="detail-date-group">
                                <span>{{ $attendanceRecord->work_date->format('Y年') }}</span>
                                <span>{{ $attendanceRecord->work_date->format('n月j日') }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            {{ $attendanceRecord->clock_in ? \Carbon\Carbon::parse($attendanceRecord->clock_in)->format('H:i') : '' }}
                            <span class="detail-table__separator">〜</span>
                            {{ $attendanceRecord->clock_out ? \Carbon\Carbon::parse($attendanceRecord->clock_out)->format('H:i') : '' }}
                        </td>
                    </tr>

                    @foreach ($attendanceRecord->breaks as $index => $break)
                        <tr>
                            <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                            <td>
                                {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}
                                <span class="detail-table__separator">〜</span>
                                {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}
                            </td>
                        </tr>
                    @endforeach

                    <tr>
                        <th>備考</th>
                        <td>{{ $attendanceRecord->comment ?? '' }}</td>
                    </tr>
                </table>
            </div>

            <p class="pending-message">*承認待ちのため修正はできません。</p>
        @else
            <form method="POST" action="{{ route('attendance.correction.store', $attendanceRecord->id) }}">
                @csrf

                <div class="detail-card">
                    <table class="detail-table">
                        <tr>
                            <th>名前</th>
                            <td>
                                <span class="detail-name-text">{{ $user->name }}</span>
                            </td>
                        </tr>

                        <tr>
                            <th>日付</th>
                            <td>
                                <div class="detail-date-group">
                                    <span>{{ $attendanceRecord->work_date->format('Y年') }}</span>
                                    <span>{{ $attendanceRecord->work_date->format('n月j日') }}</span>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                <div class="time-input-group">
                                    <input class="time-input" type="text" name="requested_clock_in"
                                        value="{{ old('requested_clock_in', $attendanceRecord->clock_in ? \Carbon\Carbon::parse($attendanceRecord->clock_in)->format('H:i') : '') }}">

                                    <span class="detail-table__separator">〜</span>

                                    <input class="time-input" type="text" name="requested_clock_out"
                                        value="{{ old('requested_clock_out', $attendanceRecord->clock_out ? \Carbon\Carbon::parse($attendanceRecord->clock_out)->format('H:i') : '') }}">
                                </div>

                                @error('requested_clock_in')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror

                                @error('requested_clock_out')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>

                        @foreach ($attendanceRecord->breaks as $index => $break)
                            <tr>
                                <th>{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                                <td>
                                    <div class="time-input-group">
                                        <input class="time-input" type="text"
                                            name="requested_breaks[{{ $index }}][requested_break_start]"
                                            value="{{ old('requested_breaks.' . $index . '.requested_break_start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">

                                        <span class="detail-table__separator">〜</span>

                                        <input class="time-input" type="text"
                                            name="requested_breaks[{{ $index }}][requested_break_end]"
                                            value="{{ old('requested_breaks.' . $index . '.requested_break_end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                                    </div>

                                    @error('requested_breaks.' . $index . '.requested_break_start')
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror

                                    @error('requested_breaks.' . $index . '.requested_break_end')
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                </td>
                            </tr>
                        @endforeach

                        <tr>
                            <th>休憩{{ $attendanceRecord->breaks->count() + 1 }}</th>
                            <td>
                                <div class="time-input-group">
                                    <input class="time-input" type="text"
                                        name="requested_new_break[requested_break_start]"
                                        value="{{ old('requested_new_break.requested_break_start') }}">

                                    <span class="detail-table__separator">〜</span>

                                    <input class="time-input" type="text" name="requested_new_break[requested_break_end]"
                                        value="{{ old('requested_new_break.requested_break_end') }}">
                                </div>

                                @error('requested_new_break.requested_break_start')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror

                                @error('requested_new_break.requested_break_end')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <th>備考</th>
                            <td>
                                <textarea class="comment-input" name="requested_comment">{{ old('requested_comment', $attendanceRecord->comment) }}</textarea>

                                @error('requested_comment')
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
    </section>
@endsection
