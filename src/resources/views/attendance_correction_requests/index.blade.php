@extends('layouts.app')

@section('title', '申請一覧')

@section('content')
    <section class="request-list">
        <h1 class="page-title">申請一覧</h1>

        <div class="request-tabs">
            <a class="request-tabs__link {{ $status === 'pending' ? 'request-tabs__link--active' : '' }}"
                href="{{ route('attendance_correction_requests.index', ['status' => 'pending']) }}">
                承認待ち
            </a>

            <a class="request-tabs__link {{ $status === 'approved' ? 'request-tabs__link--active' : '' }}"
                href="{{ route('attendance_correction_requests.index', ['status' => 'approved']) }}">
                承認済み
            </a>
        </div>

        <table class="table request-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
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
                        <td>{{ $user->name }}</td>
                        <td>
                            {{ $correctionRequest->attendanceRecord->work_date->format('Y/m/d') }}
                        </td>
                        <td>
                            {{ $correctionRequest->requested_comment }}
                        </td>
                        <td>
                            {{ $correctionRequest->created_at->format('Y/m/d') }}
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
    </section>
@endsection
