<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Order;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private Game $game;

    protected function setUp(): void
    {
        parent::setUp();

        $developer = Developer::create(['name' => 'Tracking Studio', 'slug' => 'tracking-studio']);
        $publisher = Publisher::create(['name' => 'Tracking Publisher', 'slug' => 'tracking-publisher']);
        $this->game = Game::create([
            'developer_id' => $developer->id,
            'publisher_id' => $publisher->id,
            'title' => 'Tracking Game',
            'slug' => 'tracking-game',
            'short_description' => 'Game untuk tracking.',
            'description' => 'Deskripsi lengkap game untuk tracking pesanan.',
            'original_price' => 100000,
            'discount_price' => 75000,
            'discount_percent' => 25,
            'price_is_demo' => true,
            'platform' => 'PC',
            'cover_image' => 'images/games/tracking.webp',
            'status' => 'published',
            'is_featured' => false,
        ]);
    }

    public function test_tracking_search_and_status_are_scoped_to_owner(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $pending = $this->createOrder($customer, 'DG-MINE-001', 'pending');
        $completed = $this->createOrder($customer, 'DG-MINE-002', 'completed');
        $other = $this->createOrder($otherCustomer, 'DG-MINE-OTHER', 'pending');

        $this->actingAs($customer)->get(route('orders.index', [
            'search' => 'MINE',
            'status' => 'pending',
        ]))->assertOk()
            ->assertSee($pending->order_code)
            ->assertDontSee($completed->order_code)
            ->assertDontSee($other->order_code)
            ->assertSee('data-order-card', false)
            ->assertSee($this->game->coverUrl(), false)
            ->assertSee('Menunggu pembayaran');
    }

    public function test_tracking_detail_renders_automatic_detection_stage_and_timeline(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->createOrder($customer, 'DG-TIMELINE-001', 'completed');

        $this->actingAs($customer)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Pembayaran terdeteksi')
            ->assertSee('Selesai')
            ->assertDontSee('Bukti pembayaran dikirim')
            ->assertSee('data-order-timeline', false);
    }

    public function test_completed_and_cancelled_orders_show_final_tracking_stage(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $completed = $this->createOrder($customer, 'DG-DONE-001', 'completed');
        $cancelled = $this->createOrder($customer, 'DG-CANCEL-001', 'cancelled');

        $this->actingAs($customer)->get(route('orders.index', ['status' => 'completed']))
            ->assertOk()
            ->assertSee($completed->order_code)
            ->assertSee('Selesai')
            ->assertDontSee($cancelled->order_code);

        $this->actingAs($customer)->get(route('orders.index', ['status' => 'cancelled']))
            ->assertOk()
            ->assertSee($cancelled->order_code)
            ->assertSee('Dibatalkan')
            ->assertDontSee($completed->order_code);
    }

    public function test_tracking_navigation_is_visible_and_foreign_order_is_hidden(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $order = $this->createOrder($otherCustomer, 'DG-PRIVATE-001', 'pending');

        $this->actingAs($customer)->get(route('home'))
            ->assertOk()
            ->assertSee('Lacak Pesanan');
        $this->actingAs($customer)->get(route('orders.show', $order))
            ->assertNotFound();
    }

    private function createOrder(
        User $customer,
        string $code,
        string $status,
    ): Order {
        $order = Order::create([
            'user_id' => $customer->id,
            'order_code' => $code,
            'checkout_token' => (string) Str::uuid(),
            'subtotal_amount' => 75000,
            'voucher_discount_amount' => 0,
            'total_amount' => 75000,
            'status' => $status,
            'ordered_at' => now(),
            'payment_due_at' => now()->addDay(),
        ]);
        $order->items()->create([
            'game_id' => $this->game->id,
            'game_title' => $this->game->title,
            'unit_price' => 100000,
            'discount_amount' => 25000,
            'subtotal' => 75000,
        ]);
        $order->payment()->create([
            'payment_method' => 'virtual_account',
            'virtual_account_number' => '8808'.str_pad((string) $order->id, 12, '0', STR_PAD_LEFT),
            'payment_reference' => $status === 'completed' ? 'SIM-20260725120000-'.$order->id : null,
            'amount' => 75000,
            'status' => $status === 'completed' ? 'verified' : ($status === 'cancelled' ? 'failed' : 'pending'),
            'paid_at' => $status === 'completed' ? now() : null,
            'verified_at' => $status === 'completed' ? now() : null,
        ]);

        return $order->fresh(['items.game', 'payment']);
    }
}
