<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Order;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();

        $developer = Developer::create(['name' => 'Payment Studio', 'slug' => 'payment-studio']);
        $publisher = Publisher::create(['name' => 'Payment Publisher', 'slug' => 'payment-publisher']);
        $genre = Genre::create(['name' => 'Payment Action', 'slug' => 'payment-action']);
        $this->game = Game::create([
            'developer_id' => $developer->id,
            'publisher_id' => $publisher->id,
            'title' => 'Payment Game',
            'slug' => 'payment-game',
            'short_description' => 'Game untuk menguji pembayaran.',
            'description' => 'Deskripsi lengkap game untuk menguji pembayaran.',
            'original_price' => 100000,
            'discount_price' => 75000,
            'discount_percent' => 25,
            'price_is_demo' => true,
            'release_date' => '2026-01-01',
            'platform' => 'PC',
            'status' => 'published',
            'is_featured' => false,
        ]);
        $this->game->genres()->attach($genre);
    }

    public function test_game_in_pending_order_cannot_be_added_to_cart_again(): void
    {
        [$customer] = $this->pendingOrder();

        $this->actingAs($customer)->post(route('cart.store', $this->game))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_owner_can_cancel_pending_order_and_buy_game_again(): void
    {
        [$customer, $order] = $this->pendingOrder();

        $this->actingAs($customer)->post(route('orders.cancel', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'failed']);

        $this->actingAs($customer)->post(route('orders.cancel', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');
        $this->actingAs($customer)->post(route('cart.store', $this->game))
            ->assertSessionHas('success');
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_customer_cannot_cancel_another_customers_order(): void
    {
        [, $order] = $this->pendingOrder();
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($otherCustomer)
            ->post(route('orders.cancel', $order))
            ->assertNotFound();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }

    public function test_completed_order_cannot_be_cancelled(): void
    {
        [$customer, $order] = $this->pendingOrder();
        $order->update(['status' => 'completed']);
        $order->payment()->update(['status' => 'verified']);

        $this->actingAs($customer)->post(route('orders.cancel', $order))
            ->assertSessionHasErrors('order');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'verified']);
    }

    public function test_order_page_shows_instructions_for_each_payment_method(): void
    {
        $expectations = [
            'virtual_account' => ['Virtual Account', 'Nomor Virtual Account'],
            'bank_transfer' => ['Transfer Bank', 'Rekening demo Bank Dayat'],
            'e_wallet' => ['E-Wallet', 'DayatPay Demo'],
        ];

        foreach ($expectations as $method => [$label, $destination]) {
            [$customer, $order] = $this->pendingOrder($method);

            $this->actingAs($customer)->get(route('orders.show', $order))
                ->assertOk()
                ->assertSee($label)
                ->assertSee($destination)
                ->assertSee('Tidak perlu mengirim uang sungguhan');
        }
    }

    public function test_owner_can_trigger_automatic_payment_detection(): void
    {
        [$customer, $order] = $this->pendingOrder('e_wallet');

        $this->actingAs($customer)
            ->post(route('orders.payment.submit', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'verified',
        ]);
        $this->assertDatabaseHas('libraries', [
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'game_id' => $this->game->id,
        ]);
    }

    public function test_customer_cannot_trigger_another_customers_payment(): void
    {
        [, $order] = $this->pendingOrder();
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($otherCustomer)
            ->post(route('orders.payment.submit', $order))
            ->assertNotFound();

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseCount('libraries', 0);
    }

    public function test_order_page_uses_automatic_action_without_proof_or_reference_fields(): void
    {
        [$customer, $order] = $this->pendingOrder();

        $this->actingAs($customer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Bayar sekarang')
            ->assertSee('data-auto-payment', false)
            ->assertSee('Mendeteksi pembayaran')
            ->assertDontSee('name="payment_proof"', false)
            ->assertDontSee('name="payment_reference"', false)
            ->assertDontSee('menunggu verifikasi admin');
    }

    private function pendingOrder(string $method = 'virtual_account'): array
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $cart = $customer->cart()->create();
        $cart->items()->create([
            'game_id' => $this->game->id,
            'price' => $this->game->currentPrice(),
        ]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'checkout_token' => (string) Str::uuid(),
            'payment_method' => $method,
        ])->assertRedirect();
        session()->forget(['success', 'error']);

        return [$customer->refresh(), Order::query()->with('payment')->latest('id')->firstOrFail()];
    }
}
