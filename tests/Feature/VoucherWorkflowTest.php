<?php

namespace Tests\Feature;

use App\Models\Voucher;
use App\Services\VoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_normalizes_validates_and_calculates_percentage_discount(): void
    {
        $voucher = Voucher::create([
            'code' => 'HEMAT20',
            'discount_percent' => 20,
            'expires_at' => today()->addDay(),
            'is_active' => true,
        ]);

        $service = app(VoucherService::class);

        $this->assertTrue($service->findValid(' hemat20 ')->is($voucher));
        $this->assertSame(15000.0, $service->discount(75000, $voucher));
    }

    public function test_service_rejects_inactive_expired_and_unknown_vouchers(): void
    {
        Voucher::create([
            'code' => 'NONAKTIF',
            'discount_percent' => 10,
            'expires_at' => today()->addDay(),
            'is_active' => false,
        ]);
        Voucher::create([
            'code' => 'KEDALUWARSA',
            'discount_percent' => 10,
            'expires_at' => today()->subDay(),
            'is_active' => true,
        ]);

        $service = app(VoucherService::class);

        $this->assertNull($service->findValid('NONAKTIF'));
        $this->assertNull($service->findValid('KEDALUWARSA'));
        $this->assertNull($service->findValid('TIDAKADA'));
        $this->assertNull($service->findValid(''));
    }
}
