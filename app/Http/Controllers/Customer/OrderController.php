<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
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

        return view('customer.orders.show', compact('order'));
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
}
