<?php

namespace Tests\Feature;

use App\Models\Developer;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Order;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_customer_uploads_payment_proof_after_order_creation(): void
    {
        Storage::fake('public');
        [$customer, $order] = $this->pendingOrder('e_wallet');

        $this->actingAs($customer)->post('/orders/'.$order->id.'/payment', [
            'payment_reference' => 'EW-20260725-001',
            'payment_proof' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success');

        $payment = $order->payment->fresh();
        $this->assertSame('EW-20260725-001', $payment->payment_reference);
        $this->assertNotNull($payment->paid_at);
        $this->assertNotNull($payment->payment_proof);
        Storage::disk('public')->assertExists(Str::after($payment->payment_proof, 'storage/'));
    }

    public function test_bank_and_wallet_require_reference_while_all_methods_require_proof(): void
    {
        Storage::fake('public');
        [$customer, $bankOrder] = $this->pendingOrder('bank_transfer');

        $this->actingAs($customer)->post('/orders/'.$bankOrder->id.'/payment', [
            'payment_proof' => UploadedFile::fake()->image('bank.jpg'),
        ])->assertSessionHasErrors('payment_reference');

        [$vaCustomer, $vaOrder] = $this->pendingOrder('virtual_account');
        $this->actingAs($vaCustomer)->post('/orders/'.$vaOrder->id.'/payment')
            ->assertSessionHasErrors('payment_proof');

        $this->assertNull($bankOrder->payment->fresh()->payment_proof);
        $this->assertNull($vaOrder->payment->fresh()->payment_proof);
    }

    public function test_payment_proof_rejects_invalid_file_and_non_owner(): void
    {
        Storage::fake('public');
        [$customer, $order] = $this->pendingOrder();

        $this->actingAs($customer)->post('/orders/'.$order->id.'/payment', [
            'payment_proof' => UploadedFile::fake()->create('script.exe', 10),
        ])->assertSessionHasErrors('payment_proof');

        $otherCustomer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($otherCustomer)->post('/orders/'.$order->id.'/payment', [
            'payment_proof' => UploadedFile::fake()->image('other.jpg'),
        ])->assertNotFound();

        $this->assertNull($order->payment->fresh()->payment_proof);
    }

    public function test_customer_can_replace_unverified_proof_and_old_file_is_deleted(): void
    {
        Storage::fake('public');
        [$customer, $order] = $this->pendingOrder();
        Storage::disk('public')->put('payment-proofs/old.jpg', 'old-proof');
        $order->payment()->update([
            'payment_proof' => 'storage/payment-proofs/old.jpg',
            'paid_at' => now()->subMinute(),
        ]);

        $this->actingAs($customer)->post('/orders/'.$order->id.'/payment', [
            'payment_proof' => UploadedFile::fake()->image('replacement.jpg'),
        ])->assertRedirect(route('orders.show', $order));

        $newPath = Str::after($order->payment->fresh()->payment_proof, 'storage/');
        Storage::disk('public')->assertMissing('payment-proofs/old.jpg');
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_verified_payment_rejects_replacement_proof(): void
    {
        Storage::fake('public');
        [$customer, $order] = $this->pendingOrder();
        $order->update(['status' => 'completed']);
        $order->payment()->update(['status' => 'verified']);

        $this->actingAs($customer)->post('/orders/'.$order->id.'/payment', [
            'payment_proof' => UploadedFile::fake()->image('late.jpg'),
        ])->assertSessionHasErrors('payment');

        $this->assertNull($order->payment->fresh()->payment_proof);
        $this->assertSame([], Storage::disk('public')->allFiles('payment-proofs'));
    }

    public function test_expired_payment_submission_cancels_order_and_discards_upload(): void
    {
        Storage::fake('public');
        [$customer, $order] = $this->pendingOrder();
        $order->update(['payment_due_at' => now()->subMinute()]);

        $this->actingAs($customer)->post('/orders/'.$order->id.'/payment', [
            'payment_proof' => UploadedFile::fake()->image('expired.jpg'),
        ])->assertSessionHasErrors('payment');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'failed']);
        $this->assertSame([], Storage::disk('public')->allFiles('payment-proofs'));
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
