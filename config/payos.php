<?php

$useTest = (bool) env('PAYOS_USE_TEST', false) && env('APP_ENV') === 'local';
$prefix = $useTest ? 'PAYOS_TEST_' : 'PAYOS_';

return [
    'client_id' => env($prefix.'CLIENT_ID'),
    'api_key' => env($prefix.'API_KEY'),
    'checksum_key' => env($prefix.'CHECKSUM_KEY'),
    'return_url' => env($prefix.'RETURN_URL'),
    'cancel_url' => env($prefix.'CANCEL_URL'),
    'webhook_url' => env($prefix.'WEBHOOK_URL'),
    'expire_minutes' => (int) env($prefix.'EXPIRE_MINUTES', 15),
    'api_base' => env($prefix.'API_BASE', 'https://api-merchant.payos.vn'),
    'order_code_prefix' => env($prefix.'ORDER_CODE_PREFIX', ''),
    'use_test' => $useTest,
];
