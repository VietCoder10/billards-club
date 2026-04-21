<!-- resources/views/forgot_password.blade.php -->
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu</title>
</head>

<body style="font-family: sans-serif; padding: 20px;">
    <h2>Quên mật khẩu</h2>
    <p>Nhấp vào liên kết dưới đây để đặt lại mật khẩu của bạn.</p>

    <p>
        <a href="{{ $data['url'] }}" style="color: #1a73e8; text-decoration: underline;">
            Liên kết đặt lại mật khẩu
        </a>
    </p>

    <p>Nếu bạn không có yêu cầu này, vui lòng bỏ qua email này.</p>
</body>

</html>