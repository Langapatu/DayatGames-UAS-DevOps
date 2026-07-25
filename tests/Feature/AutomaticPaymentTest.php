<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Library;
use App\Models\Order;
use App\Models\Publisher;
use App\Models\User;
use App\Services\AutomaticPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AutomaticPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();

        $developer = Developer::create([
            'name' => 'Automatic Studio',
            'slug' => 'automatic-studio',
        ]);
        $publisher = Publisher::create([
            'name' => 'Automatic Publisher',
            'slug' => 'automatic-publisher',
        ]);
        $this->game = Game::create([
            'developer_id' => $developer->id,
            'publisher_id' => $publisher->id,
            'title' => 'Automatic Payment Game',
            'slug' => 'automatic-payment-game',
            'short_description' => 'Game untuk pembayaran simulasi otomatis.',
            'description' => 'Deskripsi lengkap game pembayaran simulasi otomatis.',
            'original_price' => 150000,
            'discount_price' => 120000,
            'discount_percent' => 20,
            'price_is_demo' => true,
            'release_date' => '2026-01-01',
            'platform' => 'PC',
            'status' => 'published',
            'is_featured' => false,
        ]);
    }

    public function test_each_payment_method_is_detected_and_adds_game_to_library(): void
    {
        foreach (['virtual_account', 'bank_transfer', 'e_wallet'] as $method) {
            [$customer, $order] = $this->pendingOrder($method);

            app(AutomaticPaymentService::class)->detect($order);

            $payment = $order->payment()->firstOrFail()->fresh();

            $this->assertSame('verified', $payment->status);
            $this->assertNotNull($payment->paid_at);
            $this->assertNotNull($payment->verified_at);
            $this->assertNull($payment->verified_by);
            $this->assertNull($payment->payment_proof);
            $this->assertMatchesRegularExpression(
                '/^SIM-\d{14}-\d+$/',
                (string) $payment->payment_reference,
            );
            $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'status' => 'completed',
            ]);
            $this->assertDatabaseHas('libraries', [
                'order_id' => $order->id,
                'user_id' => $customer->id,
                'game_id' => $this->game->id,
            ]);
        }
    }

    public function test_repeated_detection_does_not_duplicate_payment_or_library(): void
    {
        [$customer, $order] = $this->pendingOrder();

        app(AutomaticPaymentService::class)->detect($order);
        $firstReference = $order->payment()->firstOrFail()->payment_reference;

        app(AutomaticPaymentService::class)->detect($order);

        $this->assertSame(
            $firstReference,
            $order->payment()->firstOrFail()->payment_reference,
        );
        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('libraries', 1);
    }

    public function test_detection_keeps_existing_library_ownership_from_an_older_order(): void
    {
        [$customer, $order] = $this->pendingOrder();
        $olderOrder = Order::create([
            'user_id' => $customer->id,
            'order_code' => 'DG-AUTO-OLDER',
            'checkout_token' => (string) Str::uuid(),
            'subtotal_amount' => 120000,
            'voucher_discount_amount' => 0,
            'total_amount' => 120000,
            'status' => 'completed',
            'ordered_at' => now()->subDay(),
            'payment_due_at' => now()->subHours(12),
        ]);
        Library::create([
            'user_id' => $customer->id,
            'game_id' => $this->game->id,
            'order_id' => $olderOrder->id,
            'purchased_at' => now()->subDay(),
        ]);

        app(AutomaticPaymentService::class)->detect($order);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
        $this->assertDatabaseCount('libraries', 1);
        $this->assertDatabaseHas('libraries', [
            'user_id' => $customer->id,
            'game_id' => $this->game->id,
            'order_id' => $olderOrder->id,
        ]);
    }

    public function test_cancelled_order_cannot_be_detected_as_paid(): void
    {
        [, $order] = $this->pendingOrder();
        $order->update(['status' => 'cancelled']);
        $order->payment()->update(['status' => 'failed']);

        try {
            app(AutomaticPaymentService::class)->detect($order);
            $this->fail('Cancelled order should reject automatic detection.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('payment', $exception->errors());
        }

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'failed',
        ]);
        $this->assertDatabaseCount('libraries', 0);
    }

    public function test_expired_order_is_cancelled_without_library_ownership(): void
    {
        [, $order] = $this->pendingOrder();
        $order->update(['payment_due_at' => now()->subMinute()]);

        try {
            app(AutomaticPaymentService::class)->detect($order);
            $this->fail('Expired order should reject automatic detection.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('payment', $exception->errors());
        }

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'failed',
        ]);
        $this->assertDatabaseCount('libraries', 0);
    }

    private function pendingOrder(string $method = 'virtual_account'): array
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = Order::create([
            'user_id' => $customer->id,
            'order_code' => 'DG-AUTO-'.Str::upper(Str::random(8)),
            'checkout_token' => (string) Str::uuid(),
            'subtotal_amount' => 120000,
            'voucher_discount_amount' => 0,
            'total_amount' => 120000,
            'status' => 'pending',
            'ordered_at' => now(),
            'payment_due_at' => now()->addDay(),
        ]);
        $order->items()->create([
            'game_id' => $this->game->id,
            'game_title' => $this->game->title,
            'unit_price' => 150000,
            'discount_amount' => 30000,
            'subtotal' => 120000,
        ]);
        $order->payment()->create([
            'payment_method' => $method,
            'virtual_account_number' => $method === 'virtual_account'
                ? '8808'.str_pad((string) $order->id, 12, '0', STR_PAD_LEFT)
                : null,
            'amount' => 120000,
            'status' => 'pending',
        ]);

        return [$customer, $order->load('payment')];
    }
}
