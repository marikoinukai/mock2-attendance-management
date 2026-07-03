@extends('layouts.app')

@section('title', '管理者 修正申請承認')

@section('content')
    <section class="attendance-detail attendance-detail--approval">
        <h1 class="page-title">勤怠詳細</h1>

        <div class="detail-card">
            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td>
                        <span class="detail-name-text">{{ $correctionRequest->user->name }}</span>
                    </td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>
                        <div class="detail-date-group">
                            <span>{{ $correctionRequest->attendanceRecord->work_date->format('Y年') }}</span>
                            <span>{{ $correctionRequest->attendanceRecord->work_date->format('n月j日') }}</span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        {{ \Carbon\Carbon::parse($correctionRequest->requested_clock_in)->format('H:i') }}
                        <span class="detail-table__separator">〜</span>
                        {{ \Carbon\Carbon::parse($correctionRequest->requested_clock_out)->format('H:i') }}
                    </td>
                </tr>

                @foreach ($correctionRequest->correctionBreaks as $break)
                    <tr>
                        <th>{{ $loop->first ? '休憩' : '休憩' . $loop->iteration }}</th>
                        <td>
                            {{ \Carbon\Carbon::parse($break->requested_break_start)->format('H:i') }}
                            <span class="detail-table__separator">〜</span>
                            {{ \Carbon\Carbon::parse($break->requested_break_end)->format('H:i') }}
                        </td>
                    </tr>
                @endforeach

                <tr>
                    <th>
                        {{ $correctionRequest->correctionBreaks->count() === 0 ? '休憩' : '休憩' . ($correctionRequest->correctionBreaks->count() + 1) }}
                    </th>
                    <td>
                        <span class="time-placeholder">00:00</span>
                        <span class="detail-table__separator">〜</span>
                        <span class="time-placeholder">00:00</span>
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td>
                        <span class="detail-comment-text">{{ $correctionRequest->requested_comment }}</span>
                    </td>
                </tr>
            </table>
        </div>

        @if ($correctionRequest->status === 'pending')
            <form class="approve-form" method="POST"
                action="{{ route('stamp_correction_request.approve.update', $correctionRequest->id) }}">
                @csrf
                @method('PATCH')

                <button class="form-button" type="submit">承認</button>
            </form>
        @else
            <div class="approved-label-area">
                <span class="approved-label">承認済み</span>
            </div>
        @endif
    </section>
@endsection
