<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>メール認証</title>
</head>

<body>
    <h1>メール認証をしてください</h1>

    <p>
        登録したメールアドレスに認証メールを送信しました。
        メール内のリンクをクリックして、認証を完了してください。
    </p>

    @if (session('status') === 'verification-link-sent')
        <p>認証メールを再送信しました。</p>
    @endif

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit">認証メールを再送信する</button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit">ログアウト</button>
    </form>
</body>

</html>
