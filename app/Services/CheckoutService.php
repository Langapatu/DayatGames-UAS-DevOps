<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CheckoutService
{
    public function __construct(private readonly VoucherService $vouchers) {}

    public function create(
        User $user,
        string $checkoutToken,
        string $paymentMethod,
        ?string $voucherCode,
    ): Order {
        $existing = $user->orders()
            ->where('checkout_token', $checkoutToken)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $checkoutToken, $paymentMethod, $voucherCode): Order {
            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            $existing = $user->orders()
                ->where('checkout_token', $checkoutToken)
                ->first();

            if ($existing) {
                return $existing;
            }

            if (! $cart) {
                throw ValidationException::withMessages([
                    'cart' => 'Cart kosong dan tidak dapat di-checkout.',
                ]);
            }

            $cart->load('items.game');
            if ($cart->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Cart kosong dan tidak dapat di-checkout.',
                ]);
            }

            $gameIds = $cart->items->pluck('game_id');
            if ($cart->items->contains(fn ($item) => ! $item->game || $item->game->status !== 'published')) {
                throw ValidationException::withMessages([
                    'cart' => 'Cart memuat game yang sudah tidak tersedia.',
                ]);
            }

            if ($user->libraries()->whereIn('game_id', $gameIds)->exists()) {
                throw ValidationException::withMessages([
                    'cart' => 'Cart memuat game yang sudah dimiliki.',
                ]);
            }

            if ($user->orders()
                ->where('status', 'pending')
                ->whereHas('items', fn ($query) => $query->whereIn('game_id', $gameIds))
                ->exists()) {
                throw ValidationException::withMessages([
                    'cart' => 'Salah satu game masih menunggu pembayaran.',
                ]);
            }

            $subtotal = (float) $cart->items
                ->sum(fn ($item) => (float) $item->game->currentPrice());
            $voucher = $voucherCode ? $this->vouchers->findValid($voucherCode) : null;

            if ($voucherCode && ! $voucher) {
                throw ValidationException::withMessages([
                    'voucher_code' => 'Voucher tidak aktif atau sudah kedaluwarsa.',
                ]);
            }

            $voucherDiscount = $voucher
                ? $this->vouchers->discount($subtotal, $voucher)
                : 0.0;
            $orderedAt = now();
            $order = $user->orders()->create([
                'checkout_token' => $checkoutToken,
                'order_code' => $this->newOrderCode(),
                'subtotal_amount' => $subtotal,
                'voucher_id' => $voucher?->id,
                'voucher_code' => $voucher?->code,
                'voucher_discount_amount' => $voucherDiscount,
                'total_amount' => max(0, $subtotal - $voucherDiscount),
                'status' => 'pending',
                'ordered_at' => $orderedAt,
                'payment_due_at' => $orderedAt->copy()->addHours(24),
            ]);

            foreach ($cart->items as $item) {
                $currentPrice = (float) $item->game->currentPrice();
                $originalPrice = (float) $item->game->original_price;
                $order->items()->create([
                    'game_id' => $item->game_id,
                    'game_title' => $item->game->title,
                    'unit_price' => $originalPrice,
                    'discount_amount' => max(0, $originalPrice - $currentPrice),
                    'subtotal' => $currentPrice,
                ]);
            }

            $order->payment()->create([
                'payment_method' => $paymentMethod,
                'virtual_account_number' => $paymentMethod === 'virtual_account'
                    ? '8808'.str_pad((string) $order->id, 12, '0', STR_PAD_LEFT)
                    : null,
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);

            $cart->items()->delete();

            return $order;
        });
    }

    private function newOrderCode(): string
    {
        do {
            $code = 'DG-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_code', $code)->exists());

        return $code;
    }
}
