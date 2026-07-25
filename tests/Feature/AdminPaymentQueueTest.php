<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPaymentQueueTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $customer;

    private Game $firstGame;

    private Game $secondGame;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->customer = User::factory()->create(['role' => 'customer']);
        $developer = Developer::create(['name' => 'Queue Studio', 'slug' => 'queue-studio']);
        $publisher = Publisher::create(['name' => 'Queue Publisher', 'slug' => 'queue-publisher']);
        $this->firstGame = $this->createGame($developer->id, $publisher->id, 'Queue Alpha', 'queue-alpha', 'images/games/queue-alpha.webp');
        $this->secondGame = $this->createGame($developer->id, $publisher->id, 'Queue Bravo', 'queue-bravo', 'images/games/queue-bravo.webp');
    }

    public function test_default_queue_only_shows_pending_payments_with_proof(): void
    {
        $ready = $this->createPayment('DG-READY-001', true, [$this->firstGame]);
        $waiting = $this->createPayment('DG-WAIT-001', false, [$this->secondGame]);

        $this->actingAs($this->admin)->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee($ready->order->order_code)
            ->assertDontSee($waiting->order->order_code)
            ->assertSee($this->firstGame->coverUrl(), false)
            ->assertSee('Siap diverifikasi');
    }

    public function test_waiting_filter_shows_only_payments_without_proof(): void
    {
        $ready = $this->createPayment('DG-READY-002', true, [$this->firstGame]);
        $waiting = $this->createPayment('DG-WAIT-002', false, [$this->secondGame]);

        $this->actingAs($this->admin)->get(route('admin.payments.index', ['queue' => 'waiting']))
            ->assertOk()
            ->assertSee($waiting->order->order_code)
            ->assertDontSee($ready->order->order_code)
            ->assertSee('Menunggu customer');
    }

    public function test_payment_detail_renders_cover_for_every_ordered_game(): void
    {
        $payment = $this->createPayment(
            'DG-COVERS-001',
            true,
            [$this->firstGame, $this->secondGame],
        );

        $response = $this->actingAs($this->admin)
            ->get(route('admin.payments.show', $payment))
            ->assertOk();

        foreach ([$this->firstGame, $this->secondGame] as $game) {
            $response->assertSee($game->coverUrl(), false)->assertSee($game->title);
        }
    }

    public function test_dashboard_separates_ready_and_waiting_payment_counts(): void
    {
        $this->createPayment('DG-READY-003', true, [$this->firstGame]);
        $this->createPayment('DG-WAIT-003', false, [$this->secondGame]);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Siap diverifikasi')
            ->assertSee('Menunggu customer');
    }

    public function test_admin_cannot_verify_payment_without_proof(): void
    {
        $payment = $this->createPayment('DG-NOPROOF-001', false, [$this->firstGame]);

        $this->actingAs($this->admin)
            ->post(route('admin.payments.verify', $payment))
            ->assertSessionHasErrors('payment');

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'pending']);
        $this->assertDatabaseHas('orders', ['id' => $payment->order_id, 'status' => 'pending']);
        $this->assertDatabaseCount('libraries', 0);
    }

    private function createPayment(string $code, bool $withProof, array $games): Payment
    {
        $total = count($games) * 75000;
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_code' => $code,
            'checkout_token' => (string) Str::uuid(),
            'subtotal_amount' => $total,
            'voucher_discount_amount' => 0,
            'total_amount' => $total,
            'status' => 'pending',
            'ordered_at' => now(),
            'payment_due_at' => now()->addDay(),
        ]);

        foreach ($games as $game) {
            $order->items()->create([
                'game_id' => $game->id,
                'game_title' => $game->title,
                'unit_price' => 100000,
                'discount_amount' => 25000,
                'subtotal' => 75000,
            ]);
        }

        return $order->payment()->create([
            'payment_method' => 'virtual_account',
            'virtual_account_number' => '8808'.str_pad((string) $order->id, 12, '0', STR_PAD_LEFT),
            'payment_proof' => $withProof ? 'storage/payment-proofs/'.$order->id.'.jpg' : null,
            'amount' => $total,
            'status' => 'pending',
            'paid_at' => $withProof ? now() : null,
        ])->load('order.items.game');
    }

    private function createGame(
        int $developerId,
        int $publisherId,
        string $title,
        string $slug,
        string $cover,
    ): Game {
        return Game::create([
            'developer_id' => $developerId,
            'publisher_id' => $publisherId,
            'title' => $title,
            'slug' => $slug,
            'short_description' => 'Deskripsi '.$title,
            'description' => 'Deskripsi lengkap '.$title,
            'original_price' => 100000,
            'discount_price' => 75000,
            'discount_percent' => 25,
            'price_is_demo' => true,
            'platform' => 'PC',
            'cover_image' => $cover,
            'status' => 'published',
            'is_featured' => false,
        ]);
    }
}
