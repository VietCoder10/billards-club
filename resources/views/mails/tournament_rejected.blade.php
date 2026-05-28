<!-- resources/views/mails/tournament_rejected.blade.php -->
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo đăng ký giải đấu</title>
</head>

<body style="font-family: sans-serif; padding: 20px;">
    <h2>Thông báo đăng ký giải đấu</h2>
    <p>Xin chào <strong>{{ $data['customer_name'] }}</strong>,</p>
    <p>Cảm ơn bạn đã quan tâm và đăng ký tham gia giải đấu <strong>{{ $data['tournament_name'] }}</strong>.</p>
    <p>Rất tiếc, chúng tôi phải thông báo rằng đơn đăng ký của bạn đã bị <strong>từ chối</strong> vì một số lý do khách quan.</p>
    <br>
    <p>Mong được gặp lại bạn ở các giải đấu tiếp theo của chúng tôi.</p>
    <p>Trân trọng,</p>
    <p>Ban tổ chức.</p>
</body>

</html>
