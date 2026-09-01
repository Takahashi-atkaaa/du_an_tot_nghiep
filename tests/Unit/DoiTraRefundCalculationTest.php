<?php

namespace Tests\Unit;

use App\Services\DoiTraService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DoiTraRefundCalculationTest extends TestCase
{
    private function calculate(float $unitPrice, float $grossTotal, float $paidTotal): float
    {
        $method = new ReflectionMethod(DoiTraService::class, 'calculateRefundUnitPrice');
        $method->setAccessible(true);

        return $method->invoke(new DoiTraService(), $unitPrice, $grossTotal, $paidTotal);
    }

    public function test_refunds_full_unit_price_when_invoice_has_no_discount(): void
    {
        $this->assertSame(40_000.0, $this->calculate(40_000, 200_000, 200_000));
    }

    public function test_allocates_invoice_discount_to_refund_price(): void
    {
        $this->assertSame(34_782.61, $this->calculate(40_000, 230_000, 200_000));
    }

    public function test_never_refunds_more_than_original_unit_price(): void
    {
        $this->assertSame(40_000.0, $this->calculate(40_000, 200_000, 250_000));
    }

    public function test_returns_zero_for_free_or_invalid_invoice_amounts(): void
    {
        $this->assertSame(0.0, $this->calculate(40_000, 0, 0));
        $this->assertSame(0.0, $this->calculate(0, 200_000, 200_000));
    }
}
