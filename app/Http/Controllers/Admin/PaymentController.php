<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $queue = in_array(
            $request->string('queue')->toString(),
            ['ready', 'waiting', 'verified', 'failed', 'all'],
            true,
        ) ? $request->string('queue')->toString() : 'ready';

        $payments = Payment::query()
            ->with(['order.user', 'order.items.game', 'verifier'])
            ->when($queue === 'ready', fn ($query) => $query
                ->where('status', 'pending')
                ->whereNotNull('payment_proof'))
            ->when($queue === 'waiting', fn ($query) => $query
                ->where('status', 'pending')
                ->whereNull('payment_proof'))
            ->when($queue === 'verified', fn ($query) => $query
                ->where('status', 'verified'))
            ->when($queue === 'failed', fn ($query) => $query
                ->where('status', 'failed'))
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));
                $query->where(fn ($query) => $query
                    ->where('payment_reference', 'like', '%'.$search.'%')
                    ->orWhere('virtual_account_number', 'like', '%'.$search.'%')
                    ->orWhereHas('order', fn ($query) => $query
                        ->where('order_code', 'like', '%'.$search.'%')));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'queue'));
    }

    public function show(Payment $payment): View
    {
        $payment->load(['order.user', 'order.items.game', 'verifier']);

        return view('admin.payments.show', compact('payment'));
    }

    public function verify(Request $request, Payment $payment): RedirectResponse
    {
        $alreadyVerified = $payment->status === 'verified';

        DB::transaction(function () use ($request, $payment): void {
            $payment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $payment->load('order.items');

            if ($payment->status === 'verified') {
                return;
            }

            if (! $payment->payment_proof) {
                throw ValidationException::withMessages([
                    'payment' => 'Bukti pembayaran belum dikirim customer.',
                ]);
            }

            $payment->update([
                'status' => 'verified',
                'paid_at' => $payment->paid_at ?? now(),
                'verified_at' => now(),
                'verified_by' => $request->user()->id,
            ]);
            $payment->order->update(['status' => 'completed']);

            foreach ($payment->order->items as $item) {
                if (! $item->game_id) {
                    continue;
                }

                $payment->order->libraries()->updateOrCreate(
                    [
                        'user_id' => $payment->order->user_id,
                        'game_id' => $item->game_id,
                    ],
                    ['purchased_at' => now()],
                );
            }

        });

        return back()->with('success', $alreadyVerified
            ? 'Pembayaran sudah pernah diverifikasi; library tidak diduplikasi.'
            : 'Pembayaran diverifikasi dan game ditambahkan ke library.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        if ($payment->status === 'verified') {
            return back()->with('error', 'Pembayaran verified tidak dapat ditolak.');
        }

        DB::transaction(function () use ($request, $payment): void {
            $payment->update([
                'status' => 'failed',
                'verified_at' => now(),
                'verified_by' => $request->user()->id,
            ]);
            $payment->order()->update(['status' => 'cancelled']);
        });

        return back()->with('success', 'Pembayaran ditolak dan order dibatalkan.');
    }
}
