@extends('layouts.app')

@section('title', '会員登録')

@section('content')
    <section class="auth-page">
        <h1 class="auth-page__title">会員登録</h1>

        <form class="auth-form" method="POST" action="{{ url('/register') }}">
            @csrf

            <div class="auth-form__group">
                <label class="auth-form__label" for="name">名前</label>
                <input class="auth-form__input" type="text" id="name" name="name" value="{{ old('name') }}">

                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="email">メールアドレス</label>
                <input class="auth-form__input" type="email" id="email" name="email" value="{{ old('email') }}">

                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="password">パスワード</label>
                <input class="auth-form__input" type="password" id="password" name="password">

                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="auth-form__group">
                <label class="auth-form__label" for="password_confirmation">パスワード確認</label>
                <input class="auth-form__input" type="password" id="password_confirmation" name="password_confirmation">
            </div>

            <button class="auth-form__button" type="submit">登録する</button>
        </form>

        <div class="auth-page__link-area">
            <a class="auth-page__link" href="{{ url('/login') }}">ログインはこちら</a>
        </div>
    </section>
@endsection
