<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CheckoutRequest;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $cart = auth()->user()->cart;
        $cart?->load(['items.game.developer']);

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Cart kosong dan tidak dapat di-checkout.');
        }

        return view('customer.checkout.create', compact('cart'));
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $proofPath = $request->hasFile('payment_proof')
            ? 'storage/'.$request->file('payment_proof')->store('payment-proofs', 'public')
            : null;

        try {
            $order = DB::transaction(function () use ($request, $proofPath): Order {
                $cart = Cart::query()
                    ->where('user_id', $request->user()->id)
                    ->lockForUpdate()
                    ->first();

                if (! $cart) {
                    throw ValidationException::withMessages(['cart' => 'Cart kosong dan tidak dapat di-checkout.']);
                }

                $cart->load(['items.game']);
                if ($cart->items->isEmpty()) {
                    throw ValidationException::withMessages(['cart' => 'Cart kosong dan tidak dapat di-checkout.']);
                }

                $ownedGameIds = $request->user()->libraries()
                    ->whereIn('game_id', $cart->items->pluck('game_id'))
                    ->pluck('game_id');

                if ($ownedGameIds->isNotEmpty()) {
                    throw ValidationException::withMessages(['cart' => 'Cart memuat game yang sudah dimiliki. Hapus game tersebut terlebih dahulu.']);
                }

                if ($cart->items->contains(fn ($item) => ! $item->game || $item->game->status !== 'published')) {
                    throw ValidationException::withMessages(['cart' => 'Cart memuat game yang sudah tidak tersedia.']);
                }

                $total = $cart->items->sum(fn ($item) => (float) $item->game->currentPrice());
                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'order_code' => $this->newOrderCode(),
                    'total_amount' => $total,
                    'status' => 'pending',
                    'ordered_at' => now(),
                ]);

                foreach ($cart->items as $item) {
                    $currentPrice = (float) $item->game->currentPrice();
                    $originalPrice = (float) $item->game->original_price;
                    $order->items()->create([
                        'game_id' => $item->game->id,
                        'game_title' => $item->game->title,
                        'unit_price' => $originalPrice,
                        'discount_amount' => max(0, $originalPrice - $currentPrice),
                        'subtotal' => $currentPrice,
                    ]);
                }

                $method = $request->validated('payment_method');
                $order->payment()->create([
                    'payment_method' => $method,
                    'payment_reference' => $request->validated('payment_reference'),
                    'virtual_account_number' => $method === 'virtual_account'
                        ? '8808'.str_pad((string) $order->id, 12, '0', STR_PAD_LEFT)
                        : null,
                    'payment_proof' => $proofPath,
                    'amount' => $total,
                    'status' => 'pending',
                    'paid_at' => $proofPath ? now() : null,
                ]);

                return $order;
            });
        } catch (Throwable $exception) {
            if ($proofPath) {
                Storage::disk('public')->delete(substr($proofPath, strlen('storage/')));
            }

            throw $exception;
        }

        return redirect()->route('orders.show', $order)->with('success', 'Order berhasil dibuat dan menunggu verifikasi admin.');
    }

    private function newOrderCode(): string
    {
        do {
            $code = 'DG-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('order_code', $code)->exists());

        return $code;
    }
}
