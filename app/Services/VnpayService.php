<?php

namespace App\Services;

class VnpayService
{
    /**
     * Tạo URL redirect sang cổng thanh toán VNPay.
     *
     * @param  int|string  $txnRef    Mã tham chiếu đơn hàng phía merchant (unique per attempt).
     * @param  int|float   $amount    Số tiền VND (sẽ nhân 100 theo chuẩn VNPay).
     * @param  string      $orderInfo Nội dung thanh toán hiển thị ở cổng VNPay.
     * @param  string|null $bankCode  Mã ngân hàng (để trống = cho khách chọn trên cổng).
     * @param  string|null $clientIp  IP khách hàng, dùng để ghi nhận ở VNPay.
     * @param  string|null $locale    'vn' hoặc 'en'.
     */
    public function buildPaymentUrl(
        int|string $txnRef,
        int|float $amount,
        string $orderInfo,
        ?string $bankCode = null,
        ?string $clientIp = null,
        ?string $locale = null
    ): string {
        $orderInfo = $this->sanitizeOrderInfo($orderInfo);

        $expireMinutes = (int) config('vnpay.expire_minutes', 15);
        $createDate = date('YmdHis');
        $expireDate = date('YmdHis', strtotime("+{$expireMinutes} minutes"));

        $inputData = [
            'vnp_Version'    => (string) config('vnpay.version', '2.1.0'),
            'vnp_TmnCode'    => (string) config('vnpay.tmn_code'),
            'vnp_Amount'     => (string) ((int) round((float) $amount * 100)),
            'vnp_Command'    => (string) config('vnpay.command', 'pay'),
            'vnp_CreateDate' => $createDate,
            'vnp_CurrCode'   => (string) config('vnpay.currency', 'VND'),
            'vnp_IpAddr'     => $clientIp ?: (request()?->ip() ?: '127.0.0.1'),
            'vnp_Locale'     => $locale ?: (string) config('vnpay.locale', 'vn'),
            'vnp_OrderInfo'  => $orderInfo,
            'vnp_OrderType'  => (string) config('vnpay.order_type', 'other'),
            'vnp_ReturnUrl'  => (string) config('vnpay.return_url'),
            'vnp_TxnRef'     => (string) $txnRef,
            'vnp_ExpireDate' => $expireDate,
        ];

        if (!empty($bankCode)) {
            $inputData['vnp_BankCode'] = $bankCode;
        }

        $hashSecret = (string) config('vnpay.hash_secret');
        $hashData = $this->buildHashData($inputData);
        $secureHash = hash_hmac('sha512', $hashData, $hashSecret);

        // URL query dùng chuẩn RFC1738 (application/x-www-form-urlencoded) để khớp với
        // cách VNPay Java SDK encode: khoảng trắng thành "+", ký tự đặc biệt thành %XX.
        $query = http_build_query($inputData, '', '&', PHP_QUERY_RFC1738);

        $payUrl = (string) config('vnpay.pay_url');

        return $payUrl . '?' . $query . '&vnp_SecureHash=' . $secureHash;
    }

    /**
     * Trả về HTML form auto-submit (POST) tới cổng VNPay, an toàn 100% về encoding vì
     * browser sẽ submit nguyên vẹn từng input đã được encode đúng.
     *
     * @param  array<string,string>  $params  Tham số đã build (không gồm vnp_SecureHash).
     */
    public function buildPaymentFormHtml(string $action, array $params): string
    {
        $secret = (string) config('vnpay.hash_secret');
        $hashData = $this->buildHashData($params);
        $secureHash = hash_hmac('sha512', $hashData, $secret);

        // #region agent log — session 379e58 lỗi 03
        $agentLogPath = __DIR__ . '/../../.cursor/debug-379e58.log';
        $logPayload = [
            'sessionId' => '379e58',
            'runId' => 'round3-loi-03',
            'hypothesisId' => 'H5',
            'location' => 'VnpayService::buildPaymentFormHtml',
            'message' => 'FORM_HTML_BUILT',
            'data' => [
                'method' => 'POST',
                'action' => $action,
                'param_keys' => array_keys($params),
                'orderInfo' => $params['vnp_OrderInfo'] ?? null,
                'txnRef' => $params['vnp_TxnRef'] ?? null,
                'amount' => $params['vnp_Amount'] ?? null,
                'secureHash_prefix' => substr($secureHash, 0, 16),
            ],
            'timestamp' => (int) (microtime(true) * 1000),
        ];
        @file_put_contents($agentLogPath, json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND);
        // #endregion agent log

        $html = '<form id="vnpayAutoForm" method="POST" action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '" style="display:none;">';
        foreach ($params as $k => $v) {
            $html .= '<input type="hidden" name="' . htmlspecialchars((string) $k, ENT_QUOTES, 'UTF-8') . '" value="' . htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8') . '">';
        }
        $html .= '<input type="hidden" name="vnp_SecureHash" value="' . htmlspecialchars($secureHash, ENT_QUOTES, 'UTF-8') . '">';
        $html .= '</form>';
        $html .= '<script>document.getElementById("vnpayAutoForm").submit();</script>';

        return $html;
    }

    /**
     * Làm sạch vnp_OrderInfo theo đúng chuẩn VNPay (Alphanumeric[1,255],
     * Tiếng Anh hoặc Tiếng Việt không dấu). Mọi ký tự ngoài chữ cái / số /
     * khoảng trắng / gạch ngang / gạch dưới sẽ bị thay bằng khoảng trắng.
     *
     * Lưu ý: KHÔNG được giữ lại ':' '#' '&' '=' '%' '+' '/' '\' '?'
     * — chúng gây lỗi 03 (Invalid data format) ở sandbox VNPay.
     */
    public function sanitizeOrderInfo(string $s): string
    {
        // VNPay docs: vnp_OrderInfo chỉ chấp nhận Alphanumeric[1,255]
        // (Tiếng Anh hoặc Tiếng Việt không dấu). Loại bỏ mọi ký tự ngoài:
        // chữ cái (có/không dấu), chữ số, khoảng trắng, gạch ngang, gạch dưới.
        // Đặc biệt: ':' '#' '&' '=' '%' '+' '/' '\' '?' đều gây lỗi 03.
        $cleaned = preg_replace('/[^A-Za-z0-9\s\-_À-ỹà-ỹ]/u', ' ', $s) ?? '';
        $cleaned = trim(preg_replace('/\s+/u', ' ', $cleaned) ?? '');

        // VNPay giới hạn OrderInfo 255 ký tự theo docs.
        if (mb_strlen($cleaned) > 255) {
            $cleaned = mb_substr($cleaned, 0, 255);
        }

        return $cleaned;
    }

    /**
     * Xác thực chữ ký bảo mật từ query parameters trả về từ VNPay.
     * Hỗ trợ cả khi input là Illuminate\Http\Request.
     */
    public function verifySignature($source): bool
    {
        $params = $this->extractVnpParams($source);

        if (empty($params['vnp_SecureHash'])) {
            return false;
        }

        $secureHash = $params['vnp_SecureHash'];
        unset($params['vnp_SecureHash']);

        $hashSecret = (string) config('vnpay.hash_secret');
        $hashData = $this->buildHashData($params);

        $calculated = hash_hmac('sha512', $hashData, $hashSecret);

        return hash_equals($calculated, $secureHash);
    }

    /**
     * Trích các tham số vnp_* từ request/array.
     *
     * @return array<string,string>
     */
    public function extractVnpParams($source): array
    {
        if ($source instanceof \Illuminate\Http\Request) {
            $all = $source->all();
        } elseif (is_array($source)) {
            $all = $source;
        } else {
            return [];
        }

        $params = [];
        foreach ($all as $key => $value) {
            if (is_string($key) && str_starts_with($key, 'vnp_')) {
                $params[$key] = (string) $value;
            }
        }

        return $params;
    }

    /**
     * Sinh chuỗi hashdata theo đúng chuẩn VNPay:
     * sort key theo alphabet, nối key=value (urlencode cả 2) bằng '&'.
     * Đây là PHP `urlencode()` chuẩn form (khoảng trắng → "+"), khớp với
     * cách VNPay Java server reconstruct lại hashdata khi verify.
     *
     * @param  array<string,string>  $params
     */
    public function buildHashData(array $params): string
    {
        ksort($params);

        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = urlencode((string) $key) . '=' . urlencode((string) $value);
        }

        $hashData = implode('&', $parts);

        // #region agent log — round 2 lỗi 03
        $agentLogPath = __DIR__ . '/../../.cursor/debug-9920af.log';
        $logPayload = [
            'sessionId' => '9920af',
            'runId' => 'round2-loi-03',
            'hypothesisId' => 'H1',
            'location' => 'VnpayService::buildHashData',
            'message' => 'HASHDATA_BUILT',
            'data' => [
                'param_keys' => array_keys($params),
                'orderInfo' => $params['vnp_OrderInfo'] ?? null,
                'orderInfo_encoded' => isset($params['vnp_OrderInfo']) ? urlencode($params['vnp_OrderInfo']) : null,
                'txnRef' => $params['vnp_TxnRef'] ?? null,
                'createDate' => $params['vnp_CreateDate'] ?? null,
                'expireDate' => $params['vnp_ExpireDate'] ?? null,
                'ipAddr' => $params['vnp_IpAddr'] ?? null,
                'returnUrl' => $params['vnp_ReturnUrl'] ?? null,
                'hashdata_full' => $hashData,
            ],
            'timestamp' => (int) (microtime(true) * 1000),
        ];
        @file_put_contents($agentLogPath, json_encode($logPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n", FILE_APPEND);
        // #endregion agent log

        return $hashData;
    }

    /**
     * Parse và chuẩn hoá payload IPN thành mảng dễ dùng.
     *
     * @return array<string,mixed>
     */
    public function parseIpnPayload($source): array
    {
        $params = $this->extractVnpParams($source);

        $vnpAmount = isset($params['vnp_Amount']) ? ((int) $params['vnp_Amount']) / 100 : 0;

        return [
            'txn_ref'             => $params['vnp_TxnRef'] ?? null,
            'amount'              => $vnpAmount,
            'order_info'          => $params['vnp_OrderInfo'] ?? null,
            'response_code'       => $params['vnp_ResponseCode'] ?? null,
            'transaction_no'      => $params['vnp_TransactionNo'] ?? null,
            'bank_code'           => $params['vnp_BankCode'] ?? null,
            'card_type'           => $params['vnp_CardType'] ?? null,
            'pay_date'            => $params['vnp_PayDate'] ?? null,
            'transaction_status'  => $params['vnp_TransactionStatus'] ?? null,
            'secure_hash'         => $params['vnp_SecureHash'] ?? null,
            'raw'                 => $params,
        ];
    }
}
