<?php

namespace App\Services;

use App\Models\Library;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AutomaticPaymentService
{
    public function detect(Order $order): Order
    {
        $expired = false;

        $detectedOrder = DB::transaction(function () use ($order, &$expired): Order {
            $lockedOrder = Order::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($order->id);
            $payment = Payment::query()
                ->where('order_id', $lockedOrder->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status === 'completed' && $payment->status === 'verified') {
                return $lockedOrder->load('payment');
            }

            if ($lockedOrder->status === 'cancelled' || $payment->status === 'failed') {
                throw ValidationException::withMessages([
                    'payment' => 'Pesanan yang dibatalkan tidak dapat dibayar.',
                ]);
            }

            if ($lockedOrder->status !== 'pending' || $payment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran ini sudah tidak dapat diproses.',
                ]);
            }

            if ($lockedOrder->payment_due_at?->isPast()) {
                $lockedOrder->update(['status' => 'cancelled']);
                $payment->update(['status' => 'failed']);
                $expired = true;

                return $lockedOrder->load('payment');
            }

            $detectedAt = now();
            $payment->update([
                'status' => 'verified',
                'payment_reference' => $payment->payment_reference
                    ?: 'SIM-'.$detectedAt->format('YmdHis').'-'.$payment->id,
                'paid_at' => $payment->paid_at ?? $detectedAt,
                'verified_at' => $payment->verified_at ?? $detectedAt,
                'verified_by' => null,
            ]);
            $lockedOrder->update(['status' => 'completed']);

            foreach ($lockedOrder->items as $item) {
                if (! $item->game_id) {
                    continue;
                }

                Library::query()->firstOrCreate(
                    [
                        'user_id' => $lockedOrder->user_id,
                        'game_id' => $item->game_id,
                    ],
                    [
                        'order_id' => $lockedOrder->id,
                        'purchased_at' => $detectedAt,
                    ],
                );
            }

            return $lockedOrder->fresh(['payment', 'items']);
        });

        if ($expired) {
            throw ValidationException::withMessages([
                'payment' => 'Batas pembayaran sudah lewat. Silakan buat pesanan baru.',
            ]);
        }

        return $detectedOrder;
    }
}
