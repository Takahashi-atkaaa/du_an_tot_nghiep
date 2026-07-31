<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayOSService
{
    protected Client $http;

    /**
     * Cờ runtime xác định đang dùng kênh Test (sandbox) hay Production.
     * ServiceProvider có thể set qua {@see useTestChannel()}; nếu không,
     * sẽ tự đọc config 'payos.use_test' ở constructor.
     */
    protected bool $isTestChannel = false;

    public function __construct()
    {
        $this->isTestChannel = (bool) config('payos.use_test', false);

        $this->http = new Client([
            'base_uri' => rtrim((string) config('payos.api_base', 'https://api-merchant.payos.vn'), '/').'/',
            'timeout' => 20,
            'connect_timeout' => 10,
            'http_errors' => false,
        ]);
    }

    /**
     * Bật/tắt chế độ test channel (sandbox). Trả về instance để chain.
     * Khi bật, các call tới PayOS sẽ dùng bộ credentials PAYOS_TEST_* và
     * checkout URL trả về sẽ nằm trong môi trường sandbox (có nút giả lập).
     */
    public function useTestChannel(bool $on = true): self
    {
        $this->isTestChannel = $on;

        return $this;
    }

    /**
     * Đang chạy ở kênh Test (sandbox) hay không?
     */
    public function isTestChannel(): bool
    {
        return $this->isTestChannel;
    }

    /**
     * Lấy bộ credentials hiện tại theo channel đang chọn.
     *
     * @return array{client_id:string,api_key:string,checksum_key:string}
     */
    protected function credentials(): array
    {
        if ($this->isTestChannel) {
            return [
                'client_id' => (string) config('payos.test_client_id', ''),
                'api_key' => (string) config('payos.test_api_key', ''),
                'checksum_key' => (string) config('payos.test_checksum_key', ''),
            ];
        }

        return [
            'client_id' => (string) config('payos.client_id', ''),
            'api_key' => (string) config('payos.api_key', ''),
            'checksum_key' => (string) config('payos.checksum_key', ''),
        ];
    }

    /**
     * Tạo payment link trên PayOS. Trả về mảng `data` từ phản hồi PayOS:
     *   - checkoutUrl: URL redirect khách sang trang thanh toán PayOS
     *   - qrCode: chuỗi EMV QR (VietQR) để hiển thị trong popup/UI
     *   - paymentLinkId, orderCode, amount, status...
     *
     * @param  array<string,mixed>  $overrides  override các trường tùy chọn (buyerName, items, ...)
     */
    public function createPaymentLink(int $orderCode, int $amount, string $description, array $overrides = []): array
    {
        $payload = array_merge([
            'orderCode' => $orderCode,
            'amount' => $amount,
            'description' => $this->sanitizeDescription($description),
            'returnUrl' => (string) config('payos.return_url'),
            'cancelUrl' => (string) config('payos.cancel_url'),
            'expiredAt' => $this->expiredAtTimestamp(),
        ], $overrides);

        $payload['signature'] = $this->signCreatePayload($payload);

        $response = $this->request('POST', 'v2/payment-requests', $payload);
        $body = $this->decode($response);

        if (($body['code'] ?? null) !== '00') {
            Log::error('PayOS createPaymentLink failed', [
                'code' => $body['code'] ?? null,
                'desc' => $body['desc'] ?? null,
                'orderCode' => $orderCode,
            ]);
            throw new RuntimeException('PayOS createPaymentLink thất bại: '.($body['desc'] ?? 'unknown'));
        }

        return $body['data'] ?? [];
    }

    /**
     * Truy vấn trạng thái payment link. Trả về mảng `data` (status, amount, amountPaid, transactions...).
     */
    public function getPaymentLink(string|int $id): array
    {
        $response = $this->request('GET', 'v2/payment-requests/'.$id);
        $body = $this->decode($response);

        if (($body['code'] ?? null) !== '00') {
            throw new RuntimeException('PayOS getPaymentLink thất bại: '.($body['desc'] ?? 'unknown'));
        }

        return $body['data'] ?? [];
    }

    /**
     * Hủy payment link (dùng khi nhân viên hủy giao dịch tại POS).
     */
    public function cancelPaymentLink(string|int $id, string $reason = 'Merchant cancelled'): array
    {
        $response = $this->request('POST', 'v2/payment-requests/'.$id.'/cancel', [
            'cancellationReason' => $reason,
        ]);
        $body = $this->decode($response);

        if (($body['code'] ?? null) !== '00') {
            throw new RuntimeException('PayOS cancelPaymentLink thất bại: '.($body['desc'] ?? 'unknown'));
        }

        return $body['data'] ?? [];
    }

    /**
     * Đăng ký/xác nhận webhook URL với PayOS (PayOS sẽ gửi payload mẫu để verify).
     * Trả về true nếu PayOS phản hồi 200.
     */
    public function confirmWebhook(string $webhookUrl): bool
    {
        $response = $this->request('POST', 'v2/webhooks/confirm', [
            'webhookUrl' => $webhookUrl,
        ]);

        return $response->getStatusCode() === 200;
    }

    /**
     * Xác minh chữ ký HMAC SHA-256 của webhook payload.
     * PayOS sort theo alphabet, value null/undefined → ""; với mảng → JSON sort các object.
     */
    public function verifyWebhookSignature(array $data, string $signature): bool
    {
        $expected = $this->signForVerify($data);

        return hash_equals($expected, $signature);
    }

    /**
     * Ký payload theo chuẩn PayOS cho createPaymentLink.
     * Data: amount=$amount&cancelUrl=$cancelUrl&description=$description&orderCode=$orderCode&returnUrl=$returnUrl
     */
    public function signCreatePayload(array $payload): string
    {
        $fields = [
            'amount' => (string) ($payload['amount'] ?? ''),
            'cancelUrl' => (string) ($payload['cancelUrl'] ?? ''),
            'description' => (string) ($payload['description'] ?? ''),
            'orderCode' => (string) ($payload['orderCode'] ?? ''),
            'returnUrl' => (string) ($payload['returnUrl'] ?? ''),
        ];

        return $this->hmac($this->buildSortedQuery($fields));
    }

    /**
     * Build chuỗi query sort alphabet từ 1 cấp array (key1=value1&key2=value2).
     * Xử lý null/undefined → "", array → JSON đã sort key.
     */
    public function buildSortedQuery(array $data): string
    {
        ksort($data);

        $parts = [];
        foreach ($data as $key => $value) {
            if ($value === null || (is_string($value) && in_array(strtolower($value), ['null', 'undefined'], true))) {
                $value = '';
            } elseif (is_array($value)) {
                $value = json_encode(
                    array_map(fn ($el) => is_array($el) ? $this->sortKeysRecursive($el) : $el, $value),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            } else {
                $value = (string) $value;
            }
            $parts[] = $key.'='.$value;
        }

        return implode('&', $parts);
    }

    protected function sortKeysRecursive(array $arr): array
    {
        ksort($arr);
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = $this->sortKeysRecursive($v);
            }
        }

        return $arr;
    }

    /**
     * Sinh timestamp Unix cho expiredAt (Int32). Tính theo timezone Asia/Ho_Chi_Minh
     * để khớp với giờ Việt Nam, tránh lệch so với sandbox PayOS.
     */
    protected function expiredAtTimestamp(): int
    {
        $minutes = (int) config('payos.expire_minutes', 15);

        return (int) round((microtime(true) + $minutes * 60));
    }

    /**
     * Làm sạch description (PayOS giới hạn 9 ký tự với một số tài khoản liên kết
     * không qua PayOS). Tuy nhiên hầu hết merchant đều dùng qua PayOS, ta vẫn giữ
     * mô tả tiếng Việt không dấu và tối đa 25 ký tự để an toàn.
     */
    protected function sanitizeDescription(string $s): string
    {
        $cleaned = preg_replace('/[^A-Za-z0-9\s\-_À-ỹà-ỹ]/u', '', $s) ?? '';
        $cleaned = trim($cleaned);

        if (mb_strlen($cleaned) > 25) {
            $cleaned = mb_substr($cleaned, 0, 25);
        }

        return $cleaned;
    }

    protected function signForVerify(array $data): string
    {
        return $this->hmac($this->buildSortedQuery($data));
    }

    protected function hmac(string $data): string
    {
        return hash_hmac('sha256', $data, $this->credentials()['checksum_key']);
    }

    /**
     * Gửi HTTP request với header Authorization PayOS.
     */
    protected function request(string $method, string $path, array $payload = [])
    {
        $creds = $this->credentials();
        $apiKey = $creds['api_key'];
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if ($apiKey !== '') {
            // PayOS chấp nhận cả 'x-client-id' + 'x-api-key' hoặc 'Authorization: Bearer'.
            // Dùng header chuẩn để tương thích rộng.
            $headers['x-client-id'] = $creds['client_id'];
            $headers['x-api-key'] = $apiKey;
        }

        $options = ['headers' => $headers];
        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $options['json'] = $payload;
        } else {
            $options['query'] = $payload;
        }

        try {
            return $this->http->request($method, $path, $options);
        } catch (GuzzleException $e) {
            Log::error('PayOS HTTP error', [
                'method' => $method,
                'path' => $path,
                'channel' => $this->isTestChannel ? 'test' : 'production',
                'error' => $e->getMessage(),
            ]);
            throw new RuntimeException('Không thể kết nối PayOS: '.$e->getMessage(), 0, $e);
        }
    }

    protected function decode($response): array
    {
        $raw = (string) $response->getBody();

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
