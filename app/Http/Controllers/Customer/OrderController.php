<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AutomaticPaymentService;
use App\Services\PaymentInstructionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly PaymentInstructionService $paymentInstructions,
        private readonly AutomaticPaymentService $automaticPayments,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $allowedStatuses = ['pending', 'paid', 'completed', 'cancelled'];

        $orders = $request->user()
            ->orders()
            ->with(['payment', 'items.game'])
            ->withCount('items')
            ->when($request->filled('search'), fn ($query) => $query
                ->where(
                    'order_code',
                    'like',
                    '%'.trim((string) $request->string('search')).'%',
                ))
            ->when(in_array($status, $allowedStatuses, true), fn ($query) => $query
                ->where('status', $status))
            ->latest('ordered_at')
            ->paginate(8)
            ->withQueryString();

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

            if ($locked->status !== 'pending' || $locked->payment?->status !== 'pending') {
                throw ValidationException::withMessages([
                    'order' => 'Pesanan tidak dapat dibatalkan setelah pembayaran terdeteksi.',
                ]);
            }

            $locked->update(['status' => 'cancelled']);
            $locked->payment()->update(['status' => 'failed']);
        });

        return redirect()->route('orders.show', $order)
            ->with('success', 'Pesanan dibatalkan. Game dapat ditambahkan ke Cart kembali.');
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);

        $alreadyCompleted = $order->status === 'completed'
            && $order->payment?->status === 'verified';

        $this->automaticPayments->detect($order);

        return redirect()->route('library.index')->with(
            'success',
            $alreadyCompleted
                ? 'Pembayaran sudah terdeteksi sebelumnya. Library tetap aman tanpa duplikasi.'
                : 'Pembayaran simulasi terdeteksi. Game sudah masuk ke Library.',
        );
    }
}
