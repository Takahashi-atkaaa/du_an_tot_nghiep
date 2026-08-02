<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán PayOS</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, {{ $success ? '#10b981 0%, #059669 100%' : ($cancelled ?? false ? '#f59e0b 0%, #d97706 100%' : '#ef4444 0%, #dc2626 100%') }});
            color: #fff;
            padding: 20px;
        }
        .card {
            background: #fff;
            color: #1f2937;
            border-radius: 16px;
            padding: 40px 32px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            text-align: center;
        }
        .icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin: 0 auto 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            background: {{ $success ? '#d1fae5' : (($cancelled ?? false) ? '#fef3c7' : '#fee2e2') }};
            color: {{ $success ? '#059669' : (($cancelled ?? false) ? '#d97706' : '#dc2626') }};
        }
        h1 {
            font-size: 24px;
            margin-bottom: 12px;
            color: #111827;
        }
        .subtitle {
            color: #6b7280;
            font-size: 15px;
            margin-bottom: 24px;
        }
        .details {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            text-align: left;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .details td {
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .details tr:last-child td { border-bottom: none; }
        .details td:first-child { color: #6b7280; }
        .details td:last-child { text-align: right; font-weight: 600; color: #111827; word-break: break-all; }
        .actions { display: flex; gap: 12px; justify-content: center; }
        button {
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: {{ $success ? '#059669' : (($cancelled ?? false) ? '#d97706' : '#dc2626') }};
            color: #fff;
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .countdown {
            margin-top: 16px;
            font-size: 13px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            @if($success) ✓
            @elseif($cancelled ?? false) !
            @else ✕
            @endif
        </div>

        @if($success)
            <h1>Thanh toán thành công!</h1>
            <p class="subtitle">Hóa đơn #{{ $payload['orderCode'] ?? '' }} đã được thanh toán qua PayOS VietQR.</p>
        @elseif($cancelled ?? false)
            <h1>Đã hủy thanh toán</h1>
            <p class="subtitle">Khách hàng đã hủy thanh toán PayOS. Hóa đơn sẽ được đánh dấu thất bại.</p>
        @else
            <h1>{{ $verified ? 'Thanh toán thất bại' : 'Có lỗi xảy ra' }}</h1>
            <p class="subtitle">
                {{ $verified ? 'PayOS không xác nhận giao dịch này. Vui lòng liên hệ nhân viên hỗ trợ.' : 'Có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại hoặc liên hệ nhân viên.' }}
            </p>
        @endif

        @if(!empty($payload))
            <div class="details">
                <table>
                    @if(!empty($payload['orderCode']))
                    <tr>
                        <td>Mã hóa đơn</td>
                        <td>#{{ $payload['orderCode'] }}</td>
                    </tr>
                    @endif
                    @if(!empty($payload['amount']))
                    <tr>
                        <td>Số tiền</td>
                        <td>{{ number_format((float) $payload['amount'], 0, ',', '.') }} ₫</td>
                    </tr>
                    @endif
                    @if(!empty($payload['amountPaid']) && $payload['amountPaid'] !== $payload['amount'])
                    <tr>
                        <td>Đã thanh toán</td>
                        <td>{{ number_format((float) $payload['amountPaid'], 0, ',', '.') }} ₫</td>
                    </tr>
                    @endif
                    @if(!empty($payload['paymentLinkId']) || !empty($payload['reference']))
                    <tr>
                        <td>Mã GD PayOS</td>
                        <td>{{ $payload['paymentLinkId'] ?? $payload['reference'] }}</td>
                    </tr>
                    @endif
                    @if(!empty($payload['accountNumber']))
                    <tr>
                        <td>STK nhận</td>
                        <td>{{ $payload['accountNumber'] }}</td>
                    </tr>
                    @endif
                    @if(!empty($payload['status']))
                    <tr>
                        <td>Trạng thái</td>
                        <td>{{ $payload['status'] }}</td>
                    </tr>
                    @endif
                    @if(!empty($payload['code']))
                    <tr>
                        <td>Mã phản hồi</td>
                        <td>{{ $payload['code'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        @endif

        <div class="actions">
            <button class="btn-primary" onclick="window.close()">Đóng cửa sổ</button>
        </div>

        <script>
            (function () {
                const success = @json($success);
                const cancelled = @json($cancelled ?? false);
                const orderCode = @json((string) ($payload['orderCode'] ?? ''));
                const verified = @json($verified);

                if (window.opener && !window.opener.closed) {
                    try {
                        window.opener.postMessage({
                            type: 'payos-return',
                            success,
                            cancelled,
                            verified,
                            hoa_don_id: orderCode,
                            orderCode,
                        }, '*');
                    } catch (_) {}
                }
            })();
        </script>

        @if($success)
            <p class="countdown">Cửa sổ sẽ tự đóng sau <span id="countdown">5</span> giây...</p>
            <script>
                let n = 5;
                const el = document.getElementById('countdown');
                const timer = setInterval(() => {
                    n--;
                    if (el) el.textContent = n;
                    if (n <= 0) {
                        clearInterval(timer);
                        try { window.close(); } catch (_) {}
                    }
                }, 1000);
            </script>
        @endif
    </div>
</body>
</html>