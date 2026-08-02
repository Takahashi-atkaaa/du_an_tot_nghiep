<?php

return [

    /*
     * Client ID do PayOS cấp trên my.payos.vn.
     */
    'client_id' => env('PAYOS_CLIENT_ID', ''),

    /*
     * API Key dùng cho Authorization header khi gọi REST API.
     */
    'api_key' => env('PAYOS_API_KEY', ''),

    /*
     * Checksum Key dùng để ký dữ liệu gửi đi và xác minh webhook đến
     * (HMAC SHA-256, sort theo alphabet, value null/undefined → "").
     */
    'checksum_key' => env('PAYOS_CHECKSUM_KEY', ''),

    /*
     * PayOS hỗ trợ 2 kênh: Production (default) và Test (sandbox).
     * Khi chạy local hoặc bật PAYOS_USE_TEST=true, service sẽ tự động chuyển
     * sang bộ credentials test (lấy từ my.payos.vn → "Kênh Test"). Payment link
     * tạo từ kênh Test sẽ mở ra checkout URL có nút "Xác nhận thanh toán thành công"
     * giả lập để dev/test end-to-end.
     */
    'use_test' => env('PAYOS_USE_TEST', false),

    'test_client_id' => env('PAYOS_TEST_CLIENT_ID', ''),

    'test_api_key' => env('PAYOS_TEST_API_KEY', ''),

    'test_checksum_key' => env('PAYOS_TEST_CHECKSUM_KEY', ''),

    /*
     * URL nhận kết quả thanh toán khi khách quét VietQR/hoàn tất checkout.
     * Phải là URL công khai nếu chạy production; khi dev có thể dùng
     * ngrok/Cloudflare Tunnel và trỏ PayOS về URL đó.
     */
    'return_url' => env('PAYOS_RETURN_URL', 'http://127.0.0.1:8000/payos/return'),

    /*
     * URL nhận kết quả khi khách chọn Hủy đơn hàng.
     */
    'cancel_url' => env('PAYOS_CANCEL_URL', 'http://127.0.0.1:8000/payos/cancel'),

    /*
     * URL webhook server-to-server do PayOS gọi (không qua trình duyệt).
     * Phải PUBLIC (vd: https://abc.ngrok.io/payos/webhook) để PayOS gọi được.
     */
    'webhook_url' => env('PAYOS_WEBHOOK_URL', 'http://127.0.0.1:8000/payos/webhook'),

    /*
     * Thời gian hết hạn của link thanh toán (phút). PayOS yêu cầu expiredAt là
     * Unix timestamp kiểu Int32; mặc định 15 phút khớp với cấu hình VNPay cũ.
     */
    'expire_minutes' => (int) env('PAYOS_EXPIRE_MINUTES', 15),

    /*
     * Base URL API PayOS (production). PayOS chỉ có một môi trường; test dùng
     * chính endpoint này với tài khoản merchant thử nghiệm.
     */
    'api_base' => env('PAYOS_API_BASE', 'https://api-merchant.payos.vn'),

    /*
     * Tiền tố đơn hàng (orderCode). PayOS yêu cầu orderCode là integer duy nhất.
     * Ta dùng id_hoa_don làm orderCode (an toàn vì AUTO_INCREMENT).
     */
    'order_code_prefix' => env('PAYOS_ORDER_CODE_PREFIX', ''),
];