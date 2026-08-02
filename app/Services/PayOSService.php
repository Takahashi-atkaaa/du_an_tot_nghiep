<?php

namespace App\Services;

use PayOS\PayOS;
use PayOS\Models\V2\PaymentRequests\CreatePaymentLinkRequest;
use PayOS\Models\V2\PaymentRequests\CreatePaymentLinkResponse;
use PayOS\Models\V2\PaymentRequests\PaymentLink;
use PayOS\Models\Webhooks\WebhookData;

class PayOSService
{
    private PayOS $client;

    public function __construct(
        private readonly string $clientId,
        private readonly string $apiKey,
        private readonly string $checksumKey,
        private readonly string $returnUrl,
        private readonly string $cancelUrl,
        private readonly string $webhookUrl,
        private readonly int $expireMinutes,
        private readonly string $orderCodePrefix,
        private readonly string $apiBase,
    ) {
        $this->client = new PayOS(
            $this->clientId,
            $this->apiKey,
            $this->checksumKey,
            null,
            rtrim($this->apiBase, '/'),
        );
    }

    public function createPaymentLink(
        int $orderCode,
        int $amount,
        string $description,
        ?string $buyerName = null,
        ?string $buyerPhone = null,
        ?string $buyerEmail = null,
    ): CreatePaymentLinkResponse {
        $expiredAt = time() + ($this->expireMinutes * 60);

        $request = new CreatePaymentLinkRequest(
            orderCode: $orderCode,
            amount: $amount,
            description: $description,
            cancelUrl: $this->cancelUrl,
            returnUrl: $this->returnUrl,
            buyerName: $buyerName,
            buyerEmail: $buyerEmail,
            buyerPhone: $buyerPhone,
            expiredAt: $expiredAt,
        );

        return $this->client->paymentRequests->create($request);
    }

    public function getPaymentInfo(string|int $orderCode): PaymentLink
    {
        $result = $this->client->paymentRequests->get($orderCode);

        if (is_array($result)) {
            $result = \PayOS\Core\ObjectSerializer::fromArray($result, PaymentLink::class);
        }

        return $result;
    }
    public function cancelPaymentLink(string|int $orderCode, ?string $cancellationReason = null): PaymentLink
    {
        $result = $this->client->paymentRequests->cancel($orderCode, $cancellationReason);

        if (is_array($result)) {
            $result = \PayOS\Core\ObjectSerializer::fromArray($result, PaymentLink::class);
        }

        return $result;
    }

    public function verifyWebhook(array $payload): WebhookData
    {
        $result = $this->client->webhooks->verify($payload);

        if (is_array($result)) {
            $result = \PayOS\Core\ObjectSerializer::fromArray($result, WebhookData::class);
        }

        return $result;
    }

    public function confirmWebhook(string $webhookUrl): mixed
    {
        return $this->client->webhooks->confirm($webhookUrl);
    }

    public function buildOrderCode(int $hoaDonId): int
    {
        $prefix = (string) $this->orderCodePrefix;
        $base = $prefix . $hoaDonId . random_int(10, 99);

        $orderCode = (int) $base;

        if ($orderCode <= 0) {
            $orderCode = abs($orderCode) + 100000;
        }

        return $orderCode;
    }

    public function client(): PayOS
    {
        return $this->client;
    }
}
