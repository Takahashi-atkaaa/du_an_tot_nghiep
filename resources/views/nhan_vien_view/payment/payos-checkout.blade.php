<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh toán PayOS #{{ $hoaDonId }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #0f172a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 20px;
            padding: 32px;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 25px 80px rgba(0,0,0,0.4);
            text-align: center;
        }
        .header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
        }
        .header img {
            width: 32px;
            height: 32px;
        }
        .header-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
        }
        .header-sub {
            font-size: 12px;
            color: #94a3b8;
        }
        .amount-box {
            background: #f0fdf4;
            border: 2px solid #22c55e;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .amount-label {
            font-size: 12px;
            color: #16a34a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .amount-value {
            font-size: 32px;
            font-weight: 800;
            color: #16a34a;
        }
        .order-id {
            font-size: 12px;
            color: #94a3b8;
            margin-bottom: 20px;
        }
        .qr-box {
            background: #fff;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 20px;
            display: inline-block;
            border: 1px solid #e2e8f0;
        }
        .qr-box img, .qr-box canvas {
            display: block;
            max-width: 220px;
        }
        .account-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
            text-align: left;
        }
        .account-info .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 14px;
        }
        .account-info .row:not(:last-child) {
            border-bottom: 1px solid #e2e8f0;
        }
        .account-info .label { color: #64748b; }
        .account-info .value { color: #1e293b; font-weight: 600; text-align: right; }
        .copy-btn {
            background: none;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 2px 8px;
            font-size: 11px;
            cursor: pointer;
            color: #64748b;
            margin-left: 4px;
        }
        .copy-btn:hover { background: #e2e8f0; }
        .status-bar {
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 600;
            display: none;
        }
        .status-bar.pending { background: #fef3c7; color: #92400e; display: block; }
        .status-bar.success { background: #d1fae5; color: #065f46; display: block; }
        .status-bar.failed { background: #fee2e2; color: #991b1b; display: block; }
        .status-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse 1.5s infinite;
        }
        .status-bar.pending .status-dot { background: #f59e0b; }
        .status-bar.success .status-dot { background: #22c55e; animation: none; }
        .status-bar.failed .status-dot { background: #ef4444; animation: none; }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.4; }
        }
        .test-btn {
            width: 100%;
            padding: 16px;
            border-radius: 12px;
            border: 2px dashed #f59e0b;
            background: #fffbeb;
            color: #b45309;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .test-btn:hover {
            background: #fef3c7;
            border-color: #d97706;
            transform: translateY(-1px);
        }
        .test-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .close-link {
            color: #94a3b8;
            font-size: 13px;
            text-decoration: none;
        }
        .close-link:hover { color: #64748b; text-decoration: underline; }
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fbbf24;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 6px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div style="width:32px;height:32px;background:#10b981;border-radius:8px;display:flex;align-items:center;justify-content:center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
            </div>
            <div>
                <div class="header-title">SmartMart PayOS</div>
                <div class="header-sub">Thanh toán VietQR</div>
            </div>
        </div>

        <div id="statusBar" class="status-bar pending">
            <span class="status-dot"></span>
            <span id="statusText">Chờ thanh toán...</span>
        </div>

        <div class="amount-box">
            <div class="amount-label">Số tiền cần thanh toán</div>
            <div class="amount-value" id="amountDisplay">{{ number_format($amount, 0, ',', '.') }}đ</div>
        </div>

        <div class="order-id">Mã đơn hàng: #{{ $hoaDonId }}</div>

        @if(!empty($qrCode) && $qrCode !== '00020101021238570010A000000727012700069704220113VQ')
            <div class="qr-box">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($qrCode) }}" alt="QR Code" />
            </div>
        @elseif(!empty($accountNumber) && !empty($bin))
            <div class="qr-box" id="vietqrBox">
                <div style="font-size:13px;color:#64748b;margin-bottom:8px">Quét mã bằng app ngân hàng</div>
            </div>
        @endif

        @if(!empty($accountNumber))
            <div class="account-info">
                <div class="row">
                    <span class="label">Ngân hàng</span>
                    <span class="value">
                        @if(!empty($bin) && $bin === '970422')
                            Ngân hàng TMCP Quân đội (MB Bank)
                        @elseif(!empty($bin))
                            BIN: {{ $bin }}
                        @else
                            N/A
                        @endif
                    </span>
                </div>
                <div class="row">
                    <span class="label">Số tài khoản</span>
                    <span class="value">
                        {{ $accountNumber }}
                        <button class="copy-btn" onclick="copyText('{{ $accountNumber }}')">Copy</button>
                    </span>
                </div>
                <div class="row">
                    <span class="label">Tên tài khoản</span>
                    <span class="value">{{ $accountName ?? 'NGUYEN TUNG ANH' }}</span>
                </div>
                <div class="row">
                    <span class="label">Nội dung CK</span>
                    <span class="value">
                        DH {{ $hoaDonId }}
                        <button class="copy-btn" onclick="copyText('DH {{ $hoaDonId }}')">Copy</button>
                    </span>
                </div>
            </div>
        @endif

        <button class="test-btn" id="testBtn" onclick="simulateSuccess()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04A7.49 7.49 0 0012 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 000 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM10 17l-3.5-3.5 1.41-1.41L10 14.17l4.59-4.59L16 11l-6 6z"/></svg>
            Test Thanh Toán (Dev Only)
        </button>

        <a href="#" class="close-link" onclick="window.close(); return false;">Hủy & đóng cửa sổ</a>
    </div>

    <script>
        const hoaDonId = {{ $hoaDonId }};
        let done = false;

        function copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                const btn = event.target;
                const orig = btn.textContent;
                btn.textContent = 'OK';
                setTimeout(() => btn.textContent = orig, 800);
            });
        }

        function setStatus(cls, text) {
            const bar = document.getElementById('statusBar');
            bar.className = 'status-bar ' + cls;
            document.getElementById('statusText').textContent = text;
        }

        async function pollStatus() {
            if (done) return;
            try {
                const res = await fetch('/nhan-vien/ban-hang/payos/check-status/' + hoaDonId);
                const data = await res.json();

                if (data.trang_thai === 'thanh_cong') {
                    done = true;
                    setStatus('success', 'Đã thanh toán thành công!');
                    document.getElementById('testBtn').disabled = true;
                    // Notify parent
                    if (window.opener && !window.opener.closed) {
                        window.opener.postMessage({ type: 'payos-return', success: true, hoa_don_id: hoaDonId }, '*');
                    }
                    setTimeout(() => {
                        try { window.close(); } catch (_) {}
                    }, 2000);
                    return;
                }
            } catch (_) {}
            setTimeout(pollStatus, 3000);
        }

        async function simulateSuccess() {
            const btn = document.getElementById('testBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span>Đang xử lý...';

            try {
                const res = await fetch('/payos/test-webhook', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                    },
                    body: JSON.stringify({
                        orderCode: hoaDonId,
                        amount: {{ $amount }},
                        success: true
                    })
                });
                const json = await res.json();

                if (json.code === '00') {
                    setStatus('success', 'Đã xác nhận thanh toán!');
                    btn.textContent = '✓ Thành công';
                    if (window.opener && !window.opener.closed) {
                        window.opener.postMessage({ type: 'payos-return', success: true, hoa_don_id: hoaDonId }, '*');
                    }
                    setTimeout(() => { try { window.close(); } catch (_) {} }, 2000);
                } else {
                    setStatus('failed', 'Thất bại: ' + JSON.stringify(json));
                    btn.disabled = false;
                    btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19.35 10.04A7.49 7.49 0 0012 4C9.11 4 6.6 5.64 5.35 8.04A5.994 5.994 0 000 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM10 17l-3.5-3.5 1.41-1.41L10 14.17l4.59-4.59L16 11l-6 6z"/></svg> Test Thanh Toán (Dev Only)';
                }
            } catch (e) {
                setStatus('failed', 'Lỗi: ' + e.message);
                btn.disabled = false;
            }
        }

        pollStatus();
    </script>
</body>
</html>
