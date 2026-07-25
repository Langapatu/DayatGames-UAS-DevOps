<?php

namespace Tests\Feature;

use App\Models\Voucher;
use App\Models\User;
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

    public function test_admin_can_create_filter_update_and_delete_voucher(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/vouchers', [
            'code' => ' hemat20 ',
            'discount_percent' => 20,
            'expires_at' => today()->addMonth()->toDateString(),
            'is_active' => '1',
        ])->assertRedirect('/admin/vouchers');

        $voucher = Voucher::query()->firstOrFail();
        $this->assertSame('HEMAT20', $voucher->code);
        $this->assertTrue($voucher->is_active);

        $this->actingAs($admin)->get('/admin/vouchers?state=active')
            ->assertOk()
            ->assertSee('HEMAT20');

        $this->actingAs($admin)->put('/admin/vouchers/'.$voucher->id, [
            'code' => ' hemat25 ',
            'discount_percent' => 25,
            'expires_at' => today()->addMonths(2)->toDateString(),
            'is_active' => '0',
        ])->assertRedirect('/admin/vouchers');

        $this->assertDatabaseHas('vouchers', [
            'id' => $voucher->id,
            'code' => 'HEMAT25',
            'discount_percent' => 25,
            'is_active' => false,
        ]);

        $this->actingAs($admin)->delete('/admin/vouchers/'.$voucher->id)
            ->assertRedirect('/admin/vouchers');
        $this->assertDatabaseMissing('vouchers', ['id' => $voucher->id]);
    }

    public function test_voucher_validation_rejects_invalid_percentage_and_duplicate_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Voucher::create([
            'code' => 'HEMAT20',
            'discount_percent' => 20,
            'expires_at' => today()->addDay(),
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post('/admin/vouchers', [
            'code' => 'hemat20',
            'discount_percent' => 101,
            'expires_at' => today()->addDay()->toDateString(),
            'is_active' => '1',
        ])->assertSessionHasErrors(['code', 'discount_percent']);

        $this->assertDatabaseCount('vouchers', 1);
    }

    public function test_customer_cannot_access_voucher_management(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer)->get('/admin/vouchers')->assertForbidden();
        $this->actingAs($customer)->post('/admin/vouchers', [
            'code' => 'HEMAT10',
            'discount_percent' => 10,
            'expires_at' => today()->addDay()->toDateString(),
            'is_active' => '1',
        ])->assertForbidden();
    }
}
