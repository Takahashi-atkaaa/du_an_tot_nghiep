<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kết quả thanh toán VNPay</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, {{ $success ? '#10b981 0%, #059669 100%' : '#ef4444 0%, #dc2626 100%' }});
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
            background: {{ $success ? '#d1fae5' : '#fee2e2' }};
            color: {{ $success ? '#059669' : '#dc2626' }};
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
        .details td:last-child { text-align: right; font-weight: 600; color: #111827; }
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
            background: {{ $success ? '#059669' : '#dc2626' }};
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
        <div class="icon">{{ $success ? '✓' : '✕' }}</div>

        @if($success)
            <h1>Thanh toán thành công!</h1>
            <p class="subtitle">Hóa đơn #{{ $payload['txn_ref'] ?? '' }} đã được thanh toán qua VNPay.</p>
        @else
            <h1>{{ $verified ? 'Thanh toán thất bại' : 'Chữ ký không hợp lệ' }}</h1>
            <p class="subtitle">
                {{ $verified ? 'VNPay không xác nhận giao dịch này. Vui lòng liên hệ nhân viên hỗ trợ.' : 'Có lỗi xảy ra trong quá trình xử lý. Vui lòng thử lại hoặc liên hệ nhân viên.' }}
            </p>
        @endif

        @if(!empty($payload))
            <div class="details">
                <table>
                    @if(!empty($payload['txn_ref']))
                    <tr>
                        <td>Mã hóa đơn</td>
                        <td>#{{ $payload['txn_ref'] }}</td>
                    </tr>
                    @endif
                    @if(!empty($payload['amount']))
                    <tr>
                        <td>Số tiền</td>
                        <td>{{ number_format($payload['amount'], 0, ',', '.') }} ₫</td>
                    </tr>
                    @endif
                    @if(!empty($payload['transaction_no']) && $payload['transaction_no'] !== '0')
                    <tr>
                        <td>Mã GD VNPay</td>
                        <td>{{ $payload['transaction_no'] }}</td>
                    </tr>
                    @endif
                    @if(!empty($payload['bank_code']))
                    <tr>
                        <td>Ngân hàng</td>
                        <td>{{ $payload['bank_code'] }}</td>
                    </tr>
                    @endif
                    @if(!empty($payload['pay_date']))
                    <tr>
                        <td>Thời gian</td>
                        <td>{{ preg_match('/^(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})$/', $payload['pay_date'], $m) ? "{$m[3]}/{$m[2]}/{$m[1]} {$m[4]}:{$m[5]}:{$m[6]}" : $payload['pay_date'] }}</td>
                    </tr>
                    @endif
                    @if(!empty($payload['response_code']))
                    <tr>
                        <td>Mã phản hồi</td>
                        <td>{{ $payload['response_code'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        @endif

        <div class="actions">
            <button class="btn-primary" onclick="window.close()">Đóng cửa sổ</button>
        </div>

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
                        try {
                            // Fallback: báo cho opener (POS) biết đã xong
                            if (window.opener && !window.opener.closed) {
                                window.opener.postMessage({
                                    type: 'vnpay-return',
                                    success: true,
                                    verified: true,
                                    hoa_don_id: '{{ $payload['txn_ref'] ?? '' }}',
                                }, '*');
                            }
                        } catch (_) {}
                    }
                }, 1000);
            </script>
        @endif
    </div>
</body>
</html>
