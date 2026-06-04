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
                    <a class="header__logo" href="{{ url('/admin/attendance/list') }}"> <img class="header__logo-image"
                            src="{{ asset('img/logo.png') }}" alt="COACHTECH"></a>
                @else
                    <a class="header__logo" href="{{ url('/attendance') }}"><img class="header__logo-image"
                            src="{{ asset('img/logo.png') }}" alt="COACHTECH"></a>
                @endif
            @else
                <div class="header__logo"><img class="header__logo-image" src="{{ asset('img/logo.png') }}" alt="COACHTECH">
                </div>
            @endauth

            @auth
                <nav class="header__nav">
                    @if (auth()->user()->is_admin)
                        <a class="header__link" href="{{ url('/admin/attendance/list') }}">勤怠一覧</a>
                        <a class="header__link" href="{{ url('/admin/staff/list') }}">スタッフ一覧</a>
                        <a class="header__link" href="{{ url('/stamp_correction_request/list') }}">申請一覧</a>
                    @else
                        <a class="header__link" href="{{ url('/attendance') }}">勤怠</a>
                        <a class="header__link" href="{{ url('/attendance/list') }}">勤怠一覧</a>
                        <a class="header__link" href="{{ url('/stamp_correction_request/list') }}">申請</a>
                    @endif

                    <form class="header__logout-form" action="{{ url('/logout') }}" method="POST">
                        @csrf
                        <button class="header__logout-button" type="submit">ログアウト</button>
                    </form>
                </nav>
            @endauth
        </div>
    </header>

    <main class="main">
        @yield('content')
    </main>
</body>

</html>
