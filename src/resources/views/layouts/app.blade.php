<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '勤怠管理')</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @stack('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            @auth
                @if (auth()->user()->is_admin)
                    <a class="header__logo" href="{{ route('admin.attendance.index') }}"> <img class="header__logo-image"
                            src="{{ asset('img/logo.png') }}" alt="COACHTECH"></a>
                @else
                    <a class="header__logo" href="{{ route('attendance.index') }}"><img class="header__logo-image"
                            src="{{ asset('img/logo.png') }}" alt="COACHTECH"></a>
                @endif
            @else
                <div class="header__logo"><img class="header__logo-image" src="{{ asset('img/logo.png') }}" alt="COACHTECH">
                </div>
            @endauth

            @auth
                @unless (request()->routeIs('verification.notice'))
                    <nav class="header__nav">
                        @if (auth()->user()->is_admin)
                            <a class="header__link" href="{{ route('admin.attendance.index') }}">勤怠一覧</a>
                            <a class="header__link" href="{{ route('admin.staff.index') }}">スタッフ一覧</a>
                            <a class="header__link" href="{{ route('attendance_correction_requests.index') }}">申請一覧</a>
                        @else
                            @if (request()->routeIs('attendance.index') &&
                                    isset($attendanceRecord) &&
                                    $attendanceRecord &&
                                    $attendanceRecord->clock_out)
                                <a class="header__link" href="{{ route('attendance.list') }}">今月の出勤一覧</a>
                                <a class="header__link" href="{{ route('attendance_correction_requests.index') }}">申請一覧</a>
                            @else
                                <a class="header__link" href="{{ route('attendance.index') }}">勤怠</a>
                                <a class="header__link" href="{{ route('attendance.list') }}">勤怠一覧</a>
                                <a class="header__link" href="{{ route('attendance_correction_requests.index') }}">申請</a>
                                <a class="header__link" href="{{ route('attendance.report') }}">レポート</a>
                            @endif
                        @endif

                        <form class="header__logout-form"
                            action="{{ Auth::user()->is_admin ? route('admin.logout') : route('user.logout') }}"
                            method="POST">
                            @csrf
                            <button class="header__logout-button" type="submit">ログアウト</button>
                        </form>
                    </nav>
                @endunless
            @endauth
        </div>
    </header>

    @if (request()->routeIs('attendance.report'))
        @yield('content')
    @else
        <main class="main">
            @yield('content')
        </main>
    @endif
</body>

</html>
