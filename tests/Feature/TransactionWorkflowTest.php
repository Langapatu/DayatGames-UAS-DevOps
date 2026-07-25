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

class TransactionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $admin;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $developer = Developer::create(['name' => 'Studio Test', 'slug' => 'studio-test']);
        $publisher = Publisher::create(['name' => 'Publisher Test', 'slug' => 'publisher-test']);
        $genre = Genre::create(['name' => 'Action', 'slug' => 'action']);
        $this->game = Game::create([
            'developer_id' => $developer->id,
            'publisher_id' => $publisher->id,
            'title' => 'Transaction Game',
            'slug' => 'transaction-game',
            'short_description' => 'Game untuk menguji transaksi.',
            'description' => 'Deskripsi game untuk menguji transaksi secara lengkap.',
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

    public function test_checkout_rejects_empty_cart(): void
    {
        $this->actingAs($this->customer)
            ->get(route('checkout.create'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error');

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'checkout_token' => (string) Str::uuid(),
                'payment_method' => 'virtual_account',
            ])
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_creates_snapshot_and_payment_using_server_price(): void
    {
        $this->addGameToCart();

        $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'checkout_token' => (string) Str::uuid(),
                'payment_method' => 'virtual_account',
                'price' => 1,
            ])
            ->assertRedirect();

        $order = Order::with(['items', 'payment'])->firstOrFail();

        $this->assertSame('75000.00', $order->total_amount);
        $this->assertSame('100000.00', $order->items->first()->unit_price);
        $this->assertSame('25000.00', $order->items->first()->discount_amount);
        $this->assertSame('75000.00', $order->items->first()->subtotal);
        $this->assertSame('75000.00', $order->payment->amount);
        $this->assertStringStartsWith('8808', $order->payment->virtual_account_number);
        $this->assertSame('pending', $order->payment->status);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_customer_cannot_read_another_customers_order(): void
    {
        $order = $this->createOrder();
        $other = User::factory()->create(['role' => 'customer']);

        $this->actingAs($other)
            ->get(route('orders.show', $order))
            ->assertNotFound();
    }

    public function test_admin_verification_adds_library_clears_cart_and_is_idempotent(): void
    {
        $order = $this->createOrder();
        $payment = $order->payment;

        $this->actingAs($this->customer)
            ->post(route('admin.payments.verify', $payment))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->post(route('admin.payments.verify', $payment))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'verified']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->assertDatabaseHas('libraries', [
            'user_id' => $this->customer->id,
            'game_id' => $this->game->id,
            'order_id' => $order->id,
        ]);
        $this->assertDatabaseCount('cart_items', 0);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.verify', $payment->refresh()))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('libraries', 1);
    }

    public function test_payment_rejection_cancels_order_without_library(): void
    {
        $order = $this->createOrder();

        $this->actingAs($this->admin)
            ->post(route('admin.payments.reject', $order->payment))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'failed']);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseCount('libraries', 0);
    }

    public function test_review_requires_library_and_owned_game_can_be_reviewed_once(): void
    {
        $this->actingAs($this->customer)
            ->post(route('reviews.store', $this->game), ['rating' => 5, 'comment' => 'Bagus'])
            ->assertForbidden();

        $order = $this->createOrder();
        $this->actingAs($this->admin)->post(route('admin.payments.verify', $order->payment));

        $this->actingAs($this->customer)
            ->post(route('reviews.store', $this->game), ['rating' => 5, 'comment' => 'Sangat menarik'])
            ->assertRedirect(route('library.index'));
        $this->actingAs($this->customer)
            ->post(route('reviews.store', $this->game), ['rating' => 4, 'comment' => 'Setelah dimainkan ulang'])
            ->assertRedirect(route('library.index'));

        $this->assertDatabaseCount('reviews', 1);
        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->customer->id,
            'game_id' => $this->game->id,
            'rating' => 4,
            'status' => 'pending',
        ]);

        $review = $this->customer->reviews()->firstOrFail();
        $this->actingAs($this->admin)
            ->put(route('admin.reviews.update', $review), ['status' => 'published'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'status' => 'published']);
    }

    private function addGameToCart(): void
    {
        $this->actingAs($this->customer)->post(route('cart.store', $this->game));
    }

    private function createOrder(): Order
    {
        $this->addGameToCart();
        $this->actingAs($this->customer)
            ->post(route('checkout.store'), [
                'checkout_token' => (string) Str::uuid(),
                'payment_method' => 'virtual_account',
            ]);

        $order = Order::with('payment')->firstOrFail();
        $order->payment->update([
            'payment_proof' => 'storage/payment-proofs/transaction.jpg',
            'paid_at' => now(),
        ]);

        return $order->fresh('payment');
    }
}
