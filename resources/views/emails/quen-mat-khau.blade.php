<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #0d6efd; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h2 style="margin: 0;">SmartMart Admin</h2>
        <p style="margin: 5px 0 0;">Yêu cầu đặt lại mật khẩu</p>
    </div>

    <div style="background: #fff; padding: 30px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 8px 8px;">
        <p>Xin chào,</p>

        <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản SmartMart Admin của mình.</p>

        <p>Click vào nút bên dưới để đặt lại mật khẩu:</p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $resetUrl }}"
               style="background: #0d6efd; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;">
                Đặt lại mật khẩu
            </a>
        </p>

        <p style="background: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 14px;">
            <strong>Lưu ý:</strong><br>
            - Link này có hiệu lực trong <strong>60 phút</strong>.<br>
            - Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.<br>
            - Không chia sẻ link này với bất kỳ ai.
        </p>

        <p>Trân trọng,<br>SmartMart Admin</p>
    </div>

    <div style="text-align: center; padding: 15px; color: #666; font-size: 12px;">
        <p>Email này được gửi tự động từ hệ thống SmartMart Admin.</p>
    </div>
</body>
</html>
