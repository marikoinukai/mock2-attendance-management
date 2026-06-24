@extends('layouts.app')

@section('title', '管理者 修正申請承認')

@section('content')
    <section class="attendance-detail">
        <h1 class="page-title">勤怠詳細</h1>

        @if (session('status'))
            <p class="alert-message">{{ session('status') }}</p>
        @endif

        <div class="detail-card">
            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td>{{ $correctionRequest->user->name }}</td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>
                        {{ $correctionRequest->attendanceRecord->work_date->format('Y年n月j日') }}
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
                    <th>備考</th>
                    <td class="detail-comment-cell">
                        {{ $correctionRequest->requested_comment }}
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

        <div class="detail-link-area">
            <a class="back-link"
                href="{{ route('attendance_correction_requests.index', ['status' => $correctionRequest->status]) }}">
                申請一覧へ戻る
            </a>
        </div>
    </section>
@endsection
