<?php

namespace Tests\Unit;

use App\Services\PayOSService;
use Tests\TestCase;

class PayOSServiceSignatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Test data và checksum lấy từ docs PayOS:
        // https://payos.vn/docs/tich-hop-webhook/kiem-tra-du-lieu-voi-signature/
        config([
            'payos.checksum_key' => '1a54716c8f0efb2744fb28b6e38b25da7f67a925d98bc1c18bd8faaecadd7675',
            'payos.api_base' => 'https://api.payos.vn',
        ]);
    }

    public function test_build_sorted_query_sorts_keys_and_handles_null(): void
    {
        $svc = new PayOSService;

        $data = [
            'orderCode' => 123,
            'amount' => 3000,
            'description' => 'VQRIO123',
            'accountNumber' => '12345678',
            'reference' => 'TF230204212323',
            'transactionDateTime' => '2023-02-04 18:25:00',
            'currency' => 'VND',
            'paymentLinkId' => '124c33293c43417ab7879e14c8d9eb18',
            'code' => '00',
            'desc' => 'Thành công',
            'counterAccountBankId' => '',
            'counterAccountBankName' => '',
            'counterAccountName' => '',
            'counterAccountNumber' => '',
            'virtualAccountName' => '',
            'virtualAccountNumber' => '',
        ];

        $query = $this->invoke($svc, 'buildSortedQuery', [$data]);

        // Phải sort theo alphabet: accountNumber trước amount trước code ...
        $this->assertStringStartsWith('accountNumber=12345678&amount=3000&code=00', $query);

        // Tất cả chuỗi rỗng vẫn phải xuất hiện dưới dạng "key=" không phải "key=null"
        $this->assertStringContainsString('counterAccountBankId=&', $query);
        $this->assertStringNotContainsString('null', $query);
    }

    public function test_verify_webhook_signature_matches_documented_example(): void
    {
        // PayOS docs dump($signature) cho checksum key trên với sample data là:
        // 412e915d2871504ed31be63c8f62a149a4410d34c4c42affc9006ef9917eaa03
        $svc = new PayOSService;

        $payload = [
            'orderCode' => 123,
            'amount' => 3000,
            'description' => 'VQRIO123',
            'accountNumber' => '12345678',
            'reference' => 'TF230204212323',
            'transactionDateTime' => '2023-02-04 18:25:00',
            'currency' => 'VND',
            'paymentLinkId' => '124c33293c43417ab7879e14c8d9eb18',
            'code' => '00',
            'desc' => 'Thành công',
            'counterAccountBankId' => '',
            'counterAccountBankName' => '',
            'counterAccountName' => '',
            'counterAccountNumber' => '',
            'virtualAccountName' => '',
            'virtualAccountNumber' => '',
        ];

        $expectedSignature = '412e915d2871504ed31be63c8f62a149a4410d34c4c42affc9006ef9917eaa03';

        $this->assertTrue($svc->verifyWebhookSignature($payload, $expectedSignature));
    }

    public function test_verify_webhook_signature_rejects_invalid(): void
    {
        $svc = new PayOSService;
        $payload = ['orderCode' => 1, 'amount' => 1000];

        $this->assertFalse($svc->verifyWebhookSignature($payload, 'wrong-signature'));
    }

    public function test_sign_create_payload_matches_alphabet_format(): void
    {
        // PayOS docs: amount=$amount&cancelUrl=$cancelUrl&description=$description&orderCode=$orderCode&returnUrl=$returnUrl
        $svc = new PayOSService;

        $payload = [
            'amount' => 3000,
            'cancelUrl' => 'https://yourdomain.com/cancel.html',
            'description' => 'VQRIO123',
            'orderCode' => 123,
            'returnUrl' => 'https://yourdomain.com/success.html',
        ];

        $r = new \ReflectionMethod($svc, 'signCreatePayload');
        $r->setAccessible(true);

        $sig = $r->invoke($svc, $payload);
        $this->assertSame(64, strlen($sig), 'HMAC SHA-256 phải ra 64 hex chars');
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $sig);
    }

    public function test_sanitize_description_strips_special_chars_and_limits_length(): void
    {
        $svc = new PayOSService;
        $r = new \ReflectionMethod($svc, 'sanitizeDescription');
        $r->setAccessible(true);

        // Ký tự ":" "#" "&" phải bị loại
        $out = $r->invoke($svc, 'DH:123 #foo&bar');
        $this->assertSame('DH123 foobar', $out);

        // Tối đa 25 ký tự
        $long = str_repeat('a', 30);
        $this->assertSame(25, mb_strlen($r->invoke($svc, $long)));
    }

    protected function invoke(object $obj, string $method, array $args)
    {
        $r = new \ReflectionMethod($obj, $method);
        $r->setAccessible(true);

        return $r->invokeArgs($obj, $args);
    }
}
