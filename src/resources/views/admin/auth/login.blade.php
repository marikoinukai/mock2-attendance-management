@extends('layouts.app')

@section('title', '管理者ログイン')

@section('content')
    <section class="auth-page">
        <h1 class="auth-page__title">管理者ログイン</h1>

        <form class="auth-form" method="POST" action="{{ route('admin.login') }}">
            @csrf

            <div class="auth-form__group">
                <label class="auth-form__label" for="email">メールアドレス</label>
                <input class="auth-form__input" type="text" id="email" name="email" value="{{ old('email') }}">

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

            <button class="auth-form__button" type="submit">管理者ログインする</button>
        </form>
    </section>
@endsection
