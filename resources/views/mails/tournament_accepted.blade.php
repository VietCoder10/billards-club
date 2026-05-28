<!-- resources/views/mails/tournament_accepted.blade.php -->
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký giải đấu thành công</title>
</head>

<body style="font-family: sans-serif; padding: 20px;">
    <h2>Thông báo đăng ký giải đấu</h2>
    <p>Xin chào <strong>{{ $data['customer_name'] }}</strong>,</p>
    <p>Cảm ơn bạn đã đăng ký tham gia giải đấu <strong>{{ $data['tournament_name'] }}</strong>.</p>
    <p>Chúng tôi xin trân trọng thông báo rằng đơn đăng ký của bạn đã được <strong>chấp nhận</strong>.</p>
    <p><strong>Thời gian thi đấu dự kiến:</strong> {{ \Carbon\Carbon::parse($data['start_date'])->format('d/m/Y H:i') }}</p>
    <br>
    <p>Chúc bạn có một giải đấu thành công và nhiều niềm vui!</p>
    <p>Trân trọng,</p>
    <p>Ban tổ chức.</p>
</body>

</html>
