@extends('layouts.app')

@section('title', 'メール認証')

@section('content')
    <section class="verify-page">
        <div class="verify-card">
            <p class="verify-card__text">
                登録していただいたメールアドレスに認証メールを送付しました。
            </p>

            <p class="verify-card__text">
                メール認証を完了してください。
            </p>

            <div class="verify-card__button-area">
                <a class="verify-card__auth-link" href="http://localhost:8025" target="_blank">
                    認証はこちらから
                </a>
            </div>

            @if (session('status') === 'verification-link-sent')
                <p class="alert-message">認証メールを再送信しました。</p>
            @endif

            <form class="verify-card__form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button class="verify-card__resend-button" type="submit">
                    認証メールを再送する
                </button>
            </form>
        </div>
    </section>
@endsection
