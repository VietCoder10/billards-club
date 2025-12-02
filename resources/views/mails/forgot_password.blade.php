<!-- resources/views/forgot_password.blade.php -->
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>パスワード再設定</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">
    <h2>パスワードをお忘れですか？</h2>
    <p>以下のリンクをクリックして、パスワードを再設定してください。</p>

    <p>
        <a href="{{ $data['url'] }}" style="color: #1a73e8; text-decoration: underline;">
            パスワード再設定リンク
        </a>
    </p>

    <p>もしこのリクエストに心当たりがない場合は、このメールを無視してください。</p>
</body>
</html>
