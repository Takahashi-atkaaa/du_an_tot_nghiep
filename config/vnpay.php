<?php

return [

    /*
     * Mã định danh merchant kết nối (Terminal Id) do VNPay cấp.
     */
    'tmn_code' => env('VNP_TMNCODE', ''),

    /*
     * Secret key dùng để ký và xác thực chữ ký bảo mật.
     */
    'hash_secret' => env('VNP_HASH_SECRET', ''),

    /*
     * URL cổng thanh toán VNPay.
     * - Sandbox: https://sandbox.vnpayment.vn/paymentv2/vpcpay.html
     * - Production: https://pay.vnpayment.vn/vpcpay.html
     */
    'pay_url' => env('VNP_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),

    /*
     * URL VNPay redirect khách về sau khi thanh toán xong (user-side return).
     */
    'return_url' => env('VNP_RETURN_URL', 'http://127.0.0.1:8000/vnpay/return'),

    /*
     * URL VNPay gọi server-to-server (IPN) để thông báo kết quả thanh toán.
     */
    'ipn_url' => env('VNP_IPN_URL', 'http://127.0.0.1:8000/vnpay/ipn'),

    /*
     * URL API truy vấn / hoàn tiền giao dịch.
     */
    'api_url' => env('VNP_API_URL', 'https://sandbox.vnpayment.vn/merchant_webapi/api/transaction'),

    /*
     * Phiên bản API VNPay.
     */
    'version' => env('VNP_VERSION', '2.1.0'),

    /*
     * Lệnh thanh toán.
     */
    'command' => env('VNP_COMMAND', 'pay'),

    /*
     * Loại tiền tệ (VND).
     */
    'currency' => env('VNP_CURRENCY', 'VND'),

    /*
     * Loại đơn hàng / sản phẩm.
     */
    'order_type' => env('VNP_ORDER_TYPE', 'other'),

    /*
     * Ngôn ngữ hiển thị cổng VNPay (vn / en).
     */
    'locale' => env('VNP_LOCALE', 'vn'),

    /*
     * Số phút hết hạn URL thanh toán kể từ lúc tạo.
     */
    'expire_minutes' => (int) env('VNP_EXPIRE_MINUTES', 15),
];
