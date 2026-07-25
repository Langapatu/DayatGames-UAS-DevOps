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

    public function test_owner_can_cancel_pending_order_before_proof_and_buy_game_again(): void
    {
        [$customer, $order] = $this->pendingOrder();

        $this->actingAs($customer)->post('/orders/'.$order->id.'/cancel')
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'failed']);

        $this->actingAs($customer)->post('/orders/'.$order->id.'/cancel')
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
            ->post('/orders/'.$order->id.'/cancel')
            ->assertNotFound();

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
    }

    public function test_order_with_submitted_proof_cannot_be_cancelled_by_customer(): void
    {
        [$customer, $order] = $this->pendingOrder();
        $order->payment()->update([
            'payment_proof' => 'storage/payment-proofs/existing.webp',
            'paid_at' => now(),
        ]);

        $this->actingAs($customer)->post('/orders/'.$order->id.'/cancel')
            ->assertSessionHasErrors('order');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'pending']);
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
