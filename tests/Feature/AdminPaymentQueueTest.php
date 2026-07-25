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
        $developer = Developer::create(['name' => 'History Studio', 'slug' => 'history-studio']);
        $publisher = Publisher::create(['name' => 'History Publisher', 'slug' => 'history-publisher']);
        $this->firstGame = $this->createGame($developer->id, $publisher->id, 'History Alpha', 'history-alpha', 'images/games/history-alpha.webp');
        $this->secondGame = $this->createGame($developer->id, $publisher->id, 'History Bravo', 'history-bravo', 'images/games/history-bravo.webp');
    }

    public function test_default_history_shows_every_payment_status_and_game_cover(): void
    {
        $pending = $this->createPayment('DG-PENDING-001', 'pending', [$this->firstGame]);
        $verified = $this->createPayment('DG-DETECTED-001', 'verified', [$this->secondGame]);
        $failed = $this->createPayment('DG-FAILED-001', 'failed', [$this->firstGame]);

        $this->actingAs($this->admin)->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee('Riwayat payment')
            ->assertSee($pending->order->order_code)
            ->assertSee($verified->order->order_code)
            ->assertSee($failed->order->order_code)
            ->assertSee($this->firstGame->coverUrl(), false)
            ->assertSee($this->secondGame->coverUrl(), false)
            ->assertSee('Menunggu pembayaran')
            ->assertSee('Terdeteksi otomatis')
            ->assertSee('Gagal / dibatalkan')
            ->assertDontSee('Siap diverifikasi')
            ->assertDontSee('Menunggu customer');
    }

    public function test_verified_filter_only_shows_detected_payments(): void
    {
        $pending = $this->createPayment('DG-PENDING-002', 'pending', [$this->firstGame]);
        $verified = $this->createPayment('DG-DETECTED-002', 'verified', [$this->secondGame]);

        $this->actingAs($this->admin)
            ->get(route('admin.payments.index', ['status' => 'verified']))
            ->assertOk()
            ->assertSee($verified->order->order_code)
            ->assertDontSee($pending->order->order_code);
    }

    public function test_payment_detail_is_read_only_and_renders_every_game_cover(): void
    {
        $payment = $this->createPayment(
            'DG-COVERS-001',
            'verified',
            [$this->firstGame, $this->secondGame],
            'storage/payment-proofs/legacy.jpg',
        );

        $response = $this->actingAs($this->admin)
            ->get(route('admin.payments.show', $payment))
            ->assertOk()
            ->assertSee('Terdeteksi otomatis')
            ->assertDontSee('Buka bukti pembayaran')
            ->assertDontSee('Verify payment')
            ->assertDontSee('Reject');

        foreach ([$this->firstGame, $this->secondGame] as $game) {
            $response->assertSee($game->coverUrl(), false)->assertSee($game->title);
        }
    }

    public function test_dashboard_shows_payment_status_metrics(): void
    {
        $this->createPayment('DG-PENDING-003', 'pending', [$this->firstGame]);
        $this->createPayment('DG-DETECTED-003', 'verified', [$this->secondGame]);
        $this->createPayment('DG-FAILED-003', 'failed', [$this->firstGame]);

        $this->actingAs($this->admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Payment menunggu')
            ->assertSee('Payment terdeteksi')
            ->assertSee('Payment gagal')
            ->assertDontSee('Siap diverifikasi')
            ->assertDontSee('Menunggu customer');
    }

    public function test_manual_verification_and_rejection_endpoints_are_unavailable(): void
    {
        $payment = $this->createPayment('DG-NOMANUAL-001', 'pending', [$this->firstGame]);

        $this->actingAs($this->admin)
            ->post('/admin/payments/'.$payment->id.'/verify')
            ->assertNotFound();
        $this->actingAs($this->admin)
            ->post('/admin/payments/'.$payment->id.'/reject')
            ->assertNotFound();
    }

    private function createPayment(
        string $code,
        string $status,
        array $games,
        ?string $legacyProof = null,
    ): Payment {
        $total = count($games) * 75000;
        $order = Order::create([
            'user_id' => $this->customer->id,
            'order_code' => $code,
            'checkout_token' => (string) Str::uuid(),
            'subtotal_amount' => $total,
            'voucher_discount_amount' => 0,
            'total_amount' => $total,
            'status' => $status === 'verified' ? 'completed' : ($status === 'failed' ? 'cancelled' : 'pending'),
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
            'payment_reference' => $status === 'verified' ? 'SIM-20260725120000-'.$order->id : null,
            'payment_proof' => $legacyProof,
            'amount' => $total,
            'status' => $status,
            'paid_at' => $status === 'verified' ? now() : null,
            'verified_at' => $status === 'verified' ? now() : null,
            'verified_by' => null,
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
