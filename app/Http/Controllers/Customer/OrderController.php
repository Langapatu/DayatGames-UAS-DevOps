<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\PaymentProofRequest;
use App\Models\Order;
use App\Services\PaymentInstructionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        private readonly PaymentInstructionService $paymentInstructions,
    ) {}

    public function index(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with('payment')
            ->withCount('items')
            ->latest('ordered_at')
            ->paginate(10);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        $order->load(['items.game', 'payment']);
        $instructions = $this->paymentInstructions->for($order);

        return view('customer.orders.show', compact('order', 'instructions'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        DB::transaction(function () use ($order): void {
            $locked = Order::query()
                ->with('payment')
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($locked->status === 'cancelled') {
                return;
            }

            if ($locked->status !== 'pending' || $locked->hasSubmittedProof()) {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan tidak dapat dibatalkan setelah bukti pembayaran dikirim.',
                ]);
            }

            $locked->update(['status' => 'cancelled']);
            $locked->payment()->update(['status' => 'failed']);
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan dibatalkan. Game dapat ditambahkan ke Cart kembali.');
    }

    public function submitPayment(PaymentProofRequest $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $storedPath = $request->file('payment_proof')->store('payment-proofs', 'public');
        $newProofPath = 'storage/'.$storedPath;
        $oldManagedPath = null;
        $expired = false;

        try {
            DB::transaction(function () use (
                $request,
                $order,
                $newProofPath,
                &$oldManagedPath,
                &$expired,
            ): void {
                $locked = Order::query()
                    ->with('payment')
                    ->lockForUpdate()
                    ->findOrFail($order->id);
                $payment = $locked->payment;

                if (! $payment || $locked->status !== 'pending' || $payment->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'payment' => 'Pembayaran ini sudah tidak dapat diubah.',
                    ]);
                }

                if ($locked->payment_due_at?->isPast()) {
                    $locked->update(['status' => 'cancelled']);
                    $payment->update(['status' => 'failed']);
                    $expired = true;

                    return;
                }

                $oldManagedPath = $payment->payment_proof;
                $payment->update([
                    'payment_reference' => $request->validated('payment_reference'),
                    'payment_proof' => $newProofPath,
                    'paid_at' => $payment->paid_at ?? now(),
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPath);
            throw $exception;
        }

        if ($expired) {
            Storage::disk('public')->delete($storedPath);
            throw ValidationException::withMessages([
                'payment' => 'Batas pembayaran sudah lewat. Pesanan dibatalkan dan dapat dibuat kembali.',
            ]);
        }

        $this->deleteManagedProof($oldManagedPath);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi admin.');
    }

    private function deleteManagedProof(?string $path): void
    {
        if ($path && str_starts_with($path, 'storage/')) {
            Storage::disk('public')->delete(substr($path, strlen('storage/')));
        }
    }
}
