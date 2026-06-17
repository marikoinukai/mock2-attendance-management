@extends('layouts.app')

@section('title', 'マイ勤怠レポート')

@section('content')
    <div class="report-page">
        <main class="report-page__inner">
            <h1 class="report__title">マイ勤怠レポート</h1>

            <p class="report__lead">過去６ヶ月の勤怠データから集計しています。</p>

            <section class="report__section">
                <h2 class="report__heading">基本サマリー</h2>

                <div class="report__summary-grid">
                    <div class="report__summary-card">
                        <p class="report__summary-label">総労働時間</p>
                        <p class="report__summary-value">{{ $totalWorkTime }}</p>
                    </div>

                    <div class="report__summary-card">
                        <p class="report__summary-label">総残業時間</p>
                        <p class="report__summary-value">{{ $totalOvertime }}</p>
                    </div>

                    <div class="report__summary-card">
                        <p class="report__summary-label">平均労働時間 / 日</p>
                        <p class="report__summary-value">{{ $averageWorkTime }}</p>
                    </div>
                </div>
            </section>

            <section class="report__section">
                <h2 class="report__heading">月次推移（過去６ヶ月）</h2>

                <table class="table report__table">
                    <thead>
                        <tr>
                            <th>月</th>
                            <th>労働時間</th>
                            <th>残業時間</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($monthlyTrend as $monthlyData)
                            <tr>
                                <td>{{ $monthlyData['month'] }}</td>
                                <td>{{ $monthlyData['totalWorkTime'] }}</td>
                                <td>{{ $monthlyData['totalOvertime'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>

            <section class="report__section">
                <h2 class="report__heading">今月の異常検知</h2>

                <p class="report__note">
                    基準: 始業 09:00 / 終業 18:00 / 長時間労働は１日10時間超過
                </p>

                <div class="report__summary-grid">
                    <div class="report__summary-card">
                        <p class="report__summary-label">遅刻回数</p>
                        <p class="report__summary-value">{{ $lateCount }} 回</p>
                    </div>

                    <div class="report__summary-card">
                        <p class="report__summary-label">早退回数</p>
                        <p class="report__summary-value">{{ $earlyLeaveCount }} 回</p>
                    </div>

                    <div class="report__summary-card">
                        <p class="report__summary-label">長時間労働日数</p>
                        <p class="report__summary-value">{{ $longWorkDayCount }} 日</p>
                    </div>
                </div>
            </section>
        </main>
    </div>
@endsection
