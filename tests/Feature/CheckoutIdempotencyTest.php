<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Order;
use App\Models\Publisher;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();

        $developer = Developer::create(['name' => 'Checkout Studio', 'slug' => 'checkout-studio']);
        $publisher = Publisher::create(['name' => 'Checkout Publisher', 'slug' => 'checkout-publisher']);
        $genre = Genre::create(['name' => 'Checkout Action', 'slug' => 'checkout-action']);
        $this->game = Game::create([
            'developer_id' => $developer->id,
            'publisher_id' => $publisher->id,
            'title' => 'Checkout Game',
            'slug' => 'checkout-game',
            'short_description' => 'Game untuk menguji checkout.',
            'description' => 'Deskripsi lengkap game untuk menguji checkout.',
            'original_price' => 100000,
            'discount_price' => 75000,
            'discount_percent' => 25,
            'price_is_demo' => true,
            'release_date' => '2026-01-01',
            'platform' => 'PC',
            'status' => 'published',
            'is_featured' => true,
        ]);
        $this->game->genres()->attach($genre);
    }

    public function test_checkout_page_creates_stable_session_token(): void
    {
        $customer = $this->customerWithCart();

        $response = $this->actingAs($customer)->get(route('checkout.create'))
            ->assertOk()
            ->assertSessionHas('checkout_token')
            ->assertSee('name="checkout_token"', false);

        $firstToken = session('checkout_token');
        $this->actingAs($customer)->get(route('checkout.create'))->assertOk();

        $this->assertSame($firstToken, session('checkout_token'));
        $response->assertSee((string) $firstToken, false);
    }

    public function test_each_payment_method_creates_order_without_proof_and_clears_cart(): void
    {
        foreach (['virtual_account', 'bank_transfer', 'e_wallet'] as $index => $method) {
            $customer = $this->customerWithCart();
            $token = (string) Str::uuid();

            $this->actingAs($customer)->post(route('checkout.store'), [
                'checkout_token' => $token,
                'payment_method' => $method,
            ])->assertRedirect();

            $order = Order::query()->where('checkout_token', $token)->with('payment')->firstOrFail();
            $this->assertSame($method, $order->payment->payment_method, "Method index {$index} failed.");
            $this->assertNull($order->payment->payment_proof);
            $this->assertSame('pending', $order->payment->status);
            $this->assertDatabaseMissing('cart_items', [
                'cart_id' => $customer->cart->id,
                'game_id' => $this->game->id,
            ]);
        }
    }

    public function test_reusing_checkout_token_returns_existing_order_without_duplicate_payment(): void
    {
        $customer = $this->customerWithCart();
        $token = (string) Str::uuid();
        $payload = [
            'checkout_token' => $token,
            'payment_method' => 'virtual_account',
        ];

        $first = $this->actingAs($customer)->post(route('checkout.store'), $payload);
        $order = Order::query()->where('checkout_token', $token)->firstOrFail();
        $first->assertRedirect(route('orders.show', $order));

        $this->actingAs($customer)->post(route('checkout.store'), $payload)
            ->assertRedirect(route('orders.show', $order));

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_checkout_uses_server_price_and_stores_voucher_snapshots(): void
    {
        $customer = $this->customerWithCart();
        Voucher::create([
            'code' => 'HEMAT20',
            'discount_percent' => 20,
            'expires_at' => today()->addDay(),
            'is_active' => true,
        ]);
        $token = (string) Str::uuid();

        $this->actingAs($customer)
            ->withSession(['checkout_voucher_code' => 'HEMAT20'])
            ->post(route('checkout.store'), [
                'checkout_token' => $token,
                'payment_method' => 'bank_transfer',
                'price' => 1,
            ])
            ->assertRedirect();

        $order = Order::query()->with(['items', 'payment'])->where('checkout_token', $token)->firstOrFail();
        $this->assertSame('75000.00', $order->subtotal_amount);
        $this->assertSame('15000.00', $order->voucher_discount_amount);
        $this->assertSame('60000.00', $order->total_amount);
        $this->assertSame('HEMAT20', $order->voucher_code);
        $this->assertSame('60000.00', $order->payment->amount);
        $this->assertSame('100000.00', $order->items->first()->unit_price);
        $this->assertSame('25000.00', $order->items->first()->discount_amount);
        $this->assertSame('75000.00', $order->items->first()->subtotal);
        $this->assertTrue($order->payment_due_at->equalTo($order->ordered_at->copy()->addHours(24)));
    }

    public function test_customer_can_apply_and_remove_valid_voucher_from_checkout_session(): void
    {
        $customer = $this->customerWithCart();
        Voucher::create([
            'code' => 'HEMAT20',
            'discount_percent' => 20,
            'expires_at' => today()->addDay(),
            'is_active' => true,
        ]);

        $this->actingAs($customer)->post('/checkout/voucher', ['voucher_code' => ' hemat20 '])
            ->assertRedirect(route('checkout.create'))
            ->assertSessionHas('checkout_voucher_code', 'HEMAT20');

        $this->actingAs($customer)->delete('/checkout/voucher')
            ->assertRedirect(route('checkout.create'))
            ->assertSessionMissing('checkout_voucher_code');
    }

    public function test_invalid_voucher_is_rejected_before_order_creation(): void
    {
        $customer = $this->customerWithCart();

        $this->actingAs($customer)->post('/checkout/voucher', ['voucher_code' => 'TIDAKADA'])
            ->assertSessionHasErrors('voucher_code');

        $this->actingAs($customer)
            ->withSession(['checkout_voucher_code' => 'TIDAKADA'])
            ->post(route('checkout.store'), [
                'checkout_token' => (string) Str::uuid(),
                'payment_method' => 'virtual_account',
            ])->assertSessionHasErrors('voucher_code');

        $this->assertDatabaseCount('orders', 0);
    }

    private function customerWithCart(): User
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $cart = $customer->cart()->create();
        $cart->items()->create([
            'game_id' => $this->game->id,
            'price' => $this->game->currentPrice(),
        ]);

        return $customer->refresh();
    }
}
